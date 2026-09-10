<?php

require_once "config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Password सुरक्षित तरिकाले hash गर्ने
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {

        $sql = "INSERT INTO users (name, email, password)
                VALUES (:name, :email, :password)";

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            ":name" => $name,
            ":email" => $email,
            ":password" => $hashedPassword
        ]);

        echo "Registration successful!";

    } catch (PDOException $e) {

        echo "Error: " . $e->getMessage();
    }
}
?>