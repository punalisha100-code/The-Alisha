<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

if (empty($_SESSION['user'])) {
    jsonResponse(['success' => false, 'authenticated' => false, 'user' => null]);
}

jsonResponse([
    'success' => true,
    'authenticated' => true,
    'user' => $_SESSION['user'],
]);
