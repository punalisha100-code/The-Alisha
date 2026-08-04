<?php
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$user = requireAuthentication();
$cartData = readJsonFile(CARTS_FILE);
$userCart = $cartData[$user['id']] ?? ['items' => []];

switch ($method) {
    case 'GET':
        jsonResponse(['success' => true, 'cart' => $userCart]);
        break;

    case 'POST':
        $body = getRequestBody();
        $productId = trim((string)($body['productId'] ?? ''));
        $name = trim((string)($body['name'] ?? ''));
        $price = floatval($body['price'] ?? 0);
        $quantity = max(1, intval($body['quantity'] ?? 1));

        if ($productId === '' || $name === '' || $price <= 0) {
            jsonResponse(['success' => false, 'message' => 'Invalid cart item payload.'], 422);
        }

        $existingIndex = null;
        foreach ($userCart['items'] as $index => $item) {
            if ($item['productId'] === $productId) {
                $existingIndex = $index;
                break;
            }
        }

        if ($existingIndex !== null) {
            $userCart['items'][$existingIndex]['quantity'] += $quantity;
            $userCart['items'][$existingIndex]['updatedAt'] = date('c');
        } else {
            $userCart['items'][] = [
                'productId' => $productId,
                'name' => $name,
                'price' => $price,
                'quantity' => $quantity,
                'addedAt' => date('c'),
            ];
        }

        $cartData[$user['id']] = $userCart;
        writeJsonFile(CARTS_FILE, $cartData);

        jsonResponse(['success' => true, 'message' => 'Item added to cart.', 'cart' => $userCart]);
        break;

    case 'PUT':
    case 'PATCH':
        $body = getRequestBody();
        $productId = trim((string)($body['productId'] ?? ''));
        $quantity = intval($body['quantity'] ?? 0);

        if ($productId === '' || $quantity < 0) {
            jsonResponse(['success' => false, 'message' => 'Invalid update request.'], 422);
        }

        $updated = false;
        foreach ($userCart['items'] as $index => $item) {
            if ($item['productId'] === $productId) {
                if ($quantity === 0) {
                    array_splice($userCart['items'], $index, 1);
                } else {
                    $userCart['items'][$index]['quantity'] = $quantity;
                    $userCart['items'][$index]['updatedAt'] = date('c');
                }
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            jsonResponse(['success' => false, 'message' => 'Product not found in cart.'], 404);
        }

        $cartData[$user['id']] = $userCart;
        writeJsonFile(CARTS_FILE, $cartData);
        jsonResponse(['success' => true, 'message' => 'Cart updated.', 'cart' => $userCart]);
        break;

    case 'DELETE':
        $userCart['items'] = [];
        $cartData[$user['id']] = $userCart;
        writeJsonFile(CARTS_FILE, $cartData);
        jsonResponse(['success' => true, 'message' => 'Cart cleared.', 'cart' => $userCart]);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
}
