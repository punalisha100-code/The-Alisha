<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $errors[] = 'Email and password are required.';
    } else {
        try {
            $pdo = getDatabaseConnection();
            ensureDatabaseSchema($pdo);
            $stmt = $pdo->prepare('SELECT id, full_name, email, password_hash, role FROM users WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();
            if ($user && ($user['role'] ?? '') === 'admin' && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id' => (int)$user['id'],
                    'fullName' => $user['full_name'] ?? $user['fullName'] ?? '',
                    'email' => $user['email'],
                    'role' => $user['role'] ?? 'admin',
                ];
                header('Location: /admin/index.php');
                exit;
            }
        } catch (Throwable $e) {
            error_log('Admin login database error: ' . $e->getMessage());
        }
        $errors[] = 'Invalid credentials.';
    }
}
?>
