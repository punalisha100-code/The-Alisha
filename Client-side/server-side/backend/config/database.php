<?php

declare(strict_types=1);

function getDatabaseConfig(): array
{
    $socketPath = '/opt/lampp/var/mysql/mysql.sock';

    return [
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'shop-database',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'socket' => file_exists($socketPath) ? $socketPath : null,
    ];
}

function getDatabaseConnection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = getDatabaseConfig();
    $dsnParts = [];
    if (!empty($config['socket'])) {
        $dsnParts[] = 'unix_socket=' . $config['socket'];
    } else {
        $dsnParts[] = 'host=' . $config['host'];
        $dsnParts[] = 'port=' . (string)$config['port'];
    }
    $dsnParts[] = 'dbname=' . $config['database'];
    $dsnParts[] = 'charset=' . $config['charset'];
    $dsn = 'mysql:' . implode(';', $dsnParts);

    try {
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
        ]);
    } catch (Throwable $e) {
        throw new RuntimeException('Database connection failed: ' . $e->getMessage());
    }

    return $pdo;
}

function ensureDatabaseSchema(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            phone VARCHAR(45) DEFAULT NULL,
            role ENUM('user','admin') NOT NULL DEFAULT 'user',
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS products (
            id VARCHAR(100) PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            price DECIMAL(10,2) NOT NULL DEFAULT 0,
            old_price DECIMAL(10,2) DEFAULT NULL,
            category VARCHAR(120) DEFAULT NULL,
            category_name VARCHAR(120) DEFAULT NULL,
            image TEXT,
            description TEXT,
            stock INT NOT NULL DEFAULT 0,
            rating DECIMAL(3,2) DEFAULT 5.00,
            reviews_count INT DEFAULT 0,
            badge VARCHAR(120) DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS orders (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            order_number VARCHAR(80) NOT NULL UNIQUE,
            user_id VARCHAR(100) DEFAULT NULL,
            customer_name VARCHAR(255) NOT NULL,
            customer_email VARCHAR(255) NOT NULL,
            customer_phone VARCHAR(45) DEFAULT NULL,
            shipping_address TEXT NOT NULL,
            city VARCHAR(120) DEFAULT NULL,
            payment_method VARCHAR(80) DEFAULT NULL,
            payment_status VARCHAR(80) DEFAULT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'Pending',
            subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
            discount DECIMAL(10,2) NOT NULL DEFAULT 0,
            shipping DECIMAL(10,2) NOT NULL DEFAULT 0,
            total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
            notes TEXT,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
    );

    $hasAdmin = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE email = 'admin@alisha.com'")->fetchColumn();
    if ($hasAdmin === 0) {
        $stmt = $pdo->prepare(
            "INSERT INTO users (full_name, email, phone, role, is_active, password_hash, created_at)
             VALUES (:full_name, :email, :phone, :role, :is_active, :password_hash, NOW())"
        );
        $stmt->execute([
            ':full_name' => 'Alisha Store Admin',
            ':email' => 'admin@alisha.com',
            ':phone' => '9828176055',
            ':role' => 'admin',
            ':is_active' => 1,
            ':password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
        ]);
    }

    $hasSampleUser = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE email = 'punalisha100@gmail.com'")->fetchColumn();
    if ($hasSampleUser === 0) {
        $stmt = $pdo->prepare(
            "INSERT INTO users (full_name, email, phone, role, is_active, password_hash, created_at)
             VALUES (:full_name, :email, :phone, :role, :is_active, :password_hash, NOW())"
        );
        $stmt->execute([
            ':full_name' => 'Alisha Magar',
            ':email' => 'punalisha100@gmail.com',
            ':phone' => '9828176055',
            ':role' => 'user',
            ':is_active' => 1,
            ':password_hash' => password_hash('password123', PASSWORD_DEFAULT),
        ]);
    }
}
