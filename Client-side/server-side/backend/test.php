<?php

$host = "localhost";
$dbname = "shop_database";
$username = "root";
$password = "";

try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✅ Database connected successfully!";
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage();
}

?>