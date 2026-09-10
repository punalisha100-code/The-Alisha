<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

if (getRequestMethod() !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Use POST to log in.'], 405);
}

$body = getRequestBody();
$email = strtolower(trim((string)($body['email'] ?? '')));
$password = (string)($body['password'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    jsonResponse(['success' => false, 'message' => 'Enter a valid email and password.'], 422);
}

$user = findUserByEmail($email);
if ($user === null || empty($user['passwordHash']) || !password_verify($password, $user['passwordHash'])) {
    jsonResponse(['success' => false, 'message' => 'Invalid email or password.'], 401);
}

if (($user['role'] ?? '') === 'admin' || ($user['isActive'] ?? true) === false) {
    // allow admin but keep account enabled check if the row is inactive
    if (($user['isActive'] ?? true) === false) {
        jsonResponse(['success' => false, 'message' => 'This account is inactive.'], 403);
    }
}

// Merge guest cart if exists
$guestKey = $_SESSION['guest_cart_id'] ?? ($_COOKIE['alisha_cart_id'] ?? '');
if (!empty($guestKey)) {
    $cartData = readJsonFile(CARTS_FILE);
    if (!empty($cartData[$guestKey]['items'])) {
        $guestItems = $cartData[$guestKey]['items'];
        $userKey = (string)$user['id'];
        $userCart = $cartData[$userKey] ?? ['items' => []];
        if (!isset($userCart['items']) || !is_array($userCart['items'])) {
            $userCart['items'] = [];
        }

        foreach ($guestItems as $gItem) {
            $found = false;
            foreach ($userCart['items'] as &$uItem) {
                if ($uItem['productId'] === $gItem['productId'] && ($uItem['size'] ?? '') === ($gItem['size'] ?? '')) {
                    $uItem['quantity'] += $gItem['quantity'];
                    $found = true;
                    break;
                }
            }
            unset($uItem);
            if (!$found) {
                $userCart['items'][] = $gItem;
            }
        }

        $cartData[$userKey] = $userCart;
        unset($cartData[$guestKey]);
        writeJsonFile(CARTS_FILE, $cartData);
    }
}

$sessionUser = [
    'id' => (string)($user['id'] ?? ''),
    'fullName' => (string)($user['fullName'] ?? $user['full_name'] ?? 'User'),
    'email' => (string)($user['email'] ?? ''),
    'phone' => (string)($user['phone'] ?? ''),
    'role' => (string)($user['role'] ?? 'user'),
    'isActive' => (bool)($user['isActive'] ?? true),
];

session_regenerate_id(true);
$_SESSION['user'] = $sessionUser;

jsonResponse([
    'success' => true,
    'message' => 'Welcome back, ' . ($sessionUser['fullName'] ?? 'User') . '!',
    'user' => $sessionUser,
]);