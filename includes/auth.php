<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('getCurrentUser')) {
    function getCurrentUser(): ?array {
        return $_SESSION['user'] ?? null;
    }
}

if (!function_exists('isLoggedIn')) {
    function isLoggedIn(): bool {
        return !empty($_SESSION['user']);
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin(): bool {
        $user = getCurrentUser();
        if (!$user) {
            return false;
        }
        return ($user['role'] ?? '') === 'admin';
    }
}

if (!function_exists('requireLogin')) {
    function requireLogin(string $redirectTo = '/The-Alisha/Client-side/Home/Login/login.html'): void {
        if (!isLoggedIn()) {
            header('Location: ' . $redirectTo);
            exit;
        }
    }
}

if (!function_exists('requireAdmin')) {
    function requireAdmin(string $redirectTo = '/The-Alisha/admin/login.php'): void {
        if (!isAdmin()) {
            if (empty($_SESSION['user'])) {
                header('Location: ' . $redirectTo);
                exit;
            }
            if (($_SESSION['user']['role'] ?? '') !== 'admin') {
                http_response_code(403);
                echo 'Access denied. Administrator privileges required.';
                exit;
            }
        }
    }
}

