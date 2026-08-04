<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Use POST to login'], 405);
}

$body = getRequestBody();
$email = trim((string)($body['email'] ?? ''));
$password = (string)($body['password'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'message' => 'Enter a valid email address.'], 422);
}

if ($password === '') {
    jsonResponse(['success' => false, 'message' => 'Password is required.'], 422);
}

$user = findUserByEmail($email);
if ($user === null || !password_verify($password, $user['passwordHash'])) {
    jsonResponse(['success' => false, 'message' => 'Email or password is incorrect.'], 401);
}

unset($user['passwordHash']);
$_SESSION['user'] = $user;
jsonResponse([
    'success' => true,
    'message' => 'Login successful.',
    'user' => $user,
]);
