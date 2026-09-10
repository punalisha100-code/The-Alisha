<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$method = getRequestMethod();
$ownerKey = getCartOwnerKey();
$cartData = readJsonFile(CARTS_FILE);
$userCart = $cartData[$ownerKey] ?? ['items' => []];

if (!isset($userCart['items']) || !is_array($userCart['items'])) {
    $userCart['items'] = [];
}

function calculateCartSummary(array &$cart): array
{
    $subtotal = 0;
    $itemCount = 0;
    foreach ($cart['items'] as &$item) {
        $qty = max(1, intval($item['quantity'] ?? 1));
        $item['quantity'] = $qty;
        $price = floatval($item['price'] ?? 0);
        $item['lineTotal'] = $price * $qty;
        $item['formattedLineTotal'] = formatPrice($item['lineTotal']);
        $item['formattedPrice'] = formatPrice($price);
        $subtotal += $item['lineTotal'];
        $itemCount += $qty;
    }
    unset($item);

    $cart['subtotal'] = $subtotal;
    $cart['formattedSubtotal'] = formatPrice($subtotal);
    $cart['itemCount'] = $itemCount;
    // Free shipping if subtotal >= 2000, else Rs 150
    $shipping = ($subtotal >= 2000 || $subtotal === 0) ? 0 : 150;
    $cart['shipping'] = $shipping;
    $cart['formattedShipping'] = $shipping === 0 ? 'FREE' : formatPrice($shipping);
    $cart['total'] = $subtotal + $shipping;
    $cart['formattedTotal'] = formatPrice($cart['total']);

    return $cart;
}

calculateCartSummary($userCart);

switch ($method) {
    case 'GET':
        jsonResponse([
            'success' => true,
            'owner' => $ownerKey,
            'cart' => $userCart,
        ]);
        break;

    case 'POST':
        $body = getRequestBody();
        $action = trim((string)($body['action'] ?? 'add'));

        if ($action === 'delete') {
            $productId = trim((string)($body['productId'] ?? ''));
            if ($productId === '') {
                jsonResponse(['success' => false, 'message' => 'Product ID is required.'], 422);
            }
            $userCart['items'] = array_values(array_filter(
                $userCart['items'],
                static fn(array $item): bool => (string)$item['productId'] !== $productId
            ));
            calculateCartSummary($userCart);
            $cartData[$ownerKey] = $userCart;
            writeJsonFile(CARTS_FILE, $cartData);
            jsonResponse(['success' => true, 'message' => 'Item removed from cart.', 'cart' => $userCart]);
        }

        if ($action === 'clear') {
            $userCart['items'] = [];
            calculateCartSummary($userCart);
            $cartData[$ownerKey] = $userCart;
            writeJsonFile(CARTS_FILE, $cartData);
            jsonResponse(['success' => true, 'message' => 'Cart cleared.', 'cart' => $userCart]);
        }

        $productId = trim((string)($body['productId'] ?? ($body['id'] ?? '')));
        $name = trim((string)($body['name'] ?? ($body['title'] ?? '')));
        $price = floatval($body['price'] ?? 0);
        $quantity = max(1, intval($body['quantity'] ?? 1));
        $image = trim((string)($body['image'] ?? ($body['imageUrl'] ?? '')));
        $size = trim((string)($body['size'] ?? 'M'));
        $color = trim((string)($body['color'] ?? 'Standard'));

        if ($productId === '' || $name === '' || $price <= 0) {
            jsonResponse(['success' => false, 'message' => 'Invalid product details.'], 422);
        }

        $existingIndex = null;
        foreach ($userCart['items'] as $index => $item) {
            if ($item['productId'] === $productId && ($item['size'] ?? '') === $size) {
                $existingIndex = $index;
                break;
            }
        }

        if ($existingIndex !== null) {
            $userCart['items'][$existingIndex]['quantity'] += $quantity;
            $userCart['items'][$existingIndex]['updatedAt'] = date('c');
            if ($image && empty($userCart['items'][$existingIndex]['image'])) {
                $userCart['items'][$existingIndex]['image'] = $image;
            }
        } else {
            $userCart['items'][] = [
                'productId' => $productId,
                'name' => $name,
                'price' => $price,
                'quantity' => $quantity,
                'image' => $image,
                'size' => $size,
                'color' => $color,
                'addedAt' => date('c'),
            ];
        }

        calculateCartSummary($userCart);
        $cartData[$ownerKey] = $userCart;
        if (!writeJsonFile(CARTS_FILE, $cartData)) {
            jsonResponse(['success' => false, 'message' => 'Unable to save cart.'], 500);
        }

        jsonResponse([
            'success' => true,
            'message' => 'Added ' . $name . ' to your cart.',
            'cart' => $userCart
        ]);
        break;

    case 'PUT':
    case 'PATCH':
        $body = getRequestBody();
        $productId = trim((string)($body['productId'] ?? ($body['id'] ?? '')));
        $quantity = intval($body['quantity'] ?? 0);

        if ($productId === '' || $quantity < 0) {
            jsonResponse(['success' => false, 'message' => 'Invalid update payload.'], 422);
        }

        $updated = false;
        foreach ($userCart['items'] as $index => $item) {
            if ($item['productId'] === $productId) {
                if ($quantity <= 0) {
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
            jsonResponse(['success' => false, 'message' => 'Product not in cart.'], 404);
        }

        calculateCartSummary($userCart);
        $cartData[$ownerKey] = $userCart;
        if (!writeJsonFile(CARTS_FILE, $cartData)) {
            jsonResponse(['success' => false, 'message' => 'Unable to update cart.'], 500);
        }

        jsonResponse([
            'success' => true,
            'message' => 'Cart updated.',
            'cart' => $userCart
        ]);
        break;

    case 'DELETE':
        $body = getRequestBody();
        $productId = trim((string)($_GET['productId'] ?? ($body['productId'] ?? '')));
        $clearAll = isset($_GET['all']) || !empty($body['all']) || empty($productId);

        if ($clearAll) {
            $userCart['items'] = [];
            $msg = 'Cart cleared successfully.';
        } else {
            $userCart['items'] = array_values(array_filter(
                $userCart['items'],
                static fn(array $item): bool => (string)$item['productId'] !== $productId
            ));
            $msg = 'Item removed from cart.';
        }

        calculateCartSummary($userCart);
        $cartData[$ownerKey] = $userCart;
        if (!writeJsonFile(CARTS_FILE, $cartData)) {
            jsonResponse(['success' => false, 'message' => 'Unable to save cart.'], 500);
        }

        jsonResponse([
            'success' => true,
            'message' => $msg,
            'cart' => $userCart
        ]);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
}

