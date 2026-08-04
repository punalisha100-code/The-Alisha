<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Use POST to register'], 405);
}

$body = getRequestBody();
$fullName = trim((string)($body['fullName'] ?? ''));
$email = trim((string)($body['email'] ?? ''));
$phone = trim((string)($body['phone'] ?? ''));
$password = (string)($body['password'] ?? '');
$confirmPassword = (string)($body['confirmPassword'] ?? '');

if (strlen($fullName) < 2) {
    jsonResponse(['success' => false, 'message' => 'Full name must be at least 2 characters.'], 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'message' => 'Enter a valid email address.'], 422);
}

if (!preg_match('/^\+?[0-9\s\-()]{7,15}$/', $phone)) {
    jsonResponse(['success' => false, 'message' => 'Enter a valid phone number.'], 422);
}

if (strlen($password) < 8) {
    jsonResponse(['success' => false, 'message' => 'Password must be at least 8 characters.'], 422);
}

if ($password !== $confirmPassword) {
    jsonResponse(['success' => false, 'message' => 'Passwords do not match.'], 422);
}

if (findUserByEmail($email) !== null) {
    jsonResponse(['success' => false, 'message' => 'Email is already registered.'], 409);
}

$users = readJsonFile(USERS_FILE);
$userId = bin2hex(random_bytes(8));
$newUser = [
    'id' => $userId,
    'fullName' => $fullName,
    'email' => $email,
    'phone' => $phone,
    'role' => 'user',
    'passwordHash' => password_hash($password, PASSWORD_DEFAULT),
    'createdAt' => date('c'),
];

$users[] = $newUser;
writeJsonFile(USERS_FILE, $users);
$sessionUser = $newUser;
unset($sessionUser['passwordHash']);
$_SESSION['user'] = $sessionUser;

jsonResponse([
    'success' => true,
    'message' => 'Registration successful. You are now signed in.',
    'user' => $sessionUser,
]);
