<?php

declare(strict_types=1);

require_once __DIR__ . '/../db.php';

/**
 * Escape a table or column name for MySQL.
 */
function quoteIdentifier(string $identifier): string
{
    return '`' . str_replace('`', '``', $identifier) . '`';
}

/**
 * Connect to MySQL and fetch rows from a table.
 *
 * Example:
 * $users = getTableRows('users', ['id', 'full_name', 'email'], [], 'id DESC');
 */
function getTableRows(string $tableName, array $columns = ['*'], array $where = [], string $orderBy = ''): array
{
    $pdo = getDatabaseConnection();

    $selectColumns = [];
    foreach ($columns as $column) {
        $selectColumns[] = $column === '*' ? '*' : quoteIdentifier($column);
    }

    $sql = 'SELECT ' . implode(', ', $selectColumns) . ' FROM ' . quoteIdentifier($tableName);

    if ($where !== []) {
        $conditions = [];
        foreach ($where as $column => $value) {
            $conditions[] = quoteIdentifier((string) $column) . ' = :' . $column;
        }
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    if ($orderBy !== '') {
        $sql .= ' ORDER BY ' . $orderBy;
    }

    $statement = $pdo->prepare($sql);

    foreach ($where as $column => $value) {
        $statement->bindValue(':' . $column, $value);
    }

    $statement->execute();

    return $statement->fetchAll();
}

/**
 * Insert a new row into a MySQL table.
 */
function insertIntoTable(string $tableName, array $data): int
{
    $pdo = getDatabaseConnection();

    $columns = array_keys($data);
    $placeholders = [];
    foreach ($columns as $column) {
        $placeholders[] = ':' . $column;
    }

    $sql = 'INSERT INTO ' . quoteIdentifier($tableName)
        . ' (' . implode(', ', array_map(static fn (string $column): string => quoteIdentifier($column), $columns)) . ')'
        . ' VALUES (' . implode(', ', $placeholders) . ')';

    $statement = $pdo->prepare($sql);

    foreach ($data as $column => $value) {
        $statement->bindValue(':' . $column, $value);
    }

    $statement->execute();

    return (int) $pdo->lastInsertId();
}
