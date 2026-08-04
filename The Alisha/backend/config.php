<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

define('DATA_DIR', __DIR__ . '/data');
define('USERS_FILE', DATA_DIR . '/users.json');
define('CARTS_FILE', DATA_DIR . '/carts.json');

if (!is_dir(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

if (!file_exists(USERS_FILE)) {
    file_put_contents(USERS_FILE, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

if (!file_exists(CARTS_FILE)) {
    file_put_contents(CARTS_FILE, json_encode(new stdClass(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function readJsonFile(string $file): array {
    $content = @file_get_contents($file);
    if ($content === false) {
        return [];
    }
    $data = json_decode($content, true);
    return is_array($data) ? $data : [];
}

function writeJsonFile(string $file, array $data): bool {
    return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

function jsonResponse(array $payload, int $status = 200): void {
    http_response_code($status);
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

function getRequestBody(): array {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    return is_array($data) ? $data : $_POST;
}

function getCurrentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

function requireAuthentication(): array {
    $user = getCurrentUser();
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Authentication required'], 401);
    }
    return $user;
}

function findUserByEmail(string $email): ?array {
    $users = readJsonFile(USERS_FILE);
    foreach ($users as $user) {
        if (strtolower($user['email']) === strtolower($email)) {
            return $user;
        }
    }
    return null;
}
