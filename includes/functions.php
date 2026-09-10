<?php
declare(strict_types=1);

$configCandidates = [
    __DIR__ . '/../Client-side/server-side/backend/config.php',
    __DIR__ . '/../server-side/backend/config.php',
];

foreach ($configCandidates as $candidate) {
    if (is_file($candidate)) {
        require_once $candidate;
        break;
    }
}

function getPDO(): PDO {
    return getDatabaseConnection();
}

function dbPrepare(string $sql): PDOStatement {
    $pdo = getDatabaseConnection();
    return $pdo->prepare($sql);
}
