<?php
declare(strict_types=1);

// Database configuration. Update these values for your MySQL server.
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'your_database');
define('DB_USER', getenv('DB_USER') ?: 'your_username');
define('DB_PASS', getenv('DB_PASS') ?: 'your_password');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

define('DB_DSN', sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET));

/**
 * Create and return a PDO connection to the MySQL database.
 *
 * @return PDO
 */
function getDatabaseConnection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . DB_CHARSET,
    ];

    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, $options);
    } catch (PDOException $exception) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed.',
            'error' => $exception->getMessage(),
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    return $pdo;
}
