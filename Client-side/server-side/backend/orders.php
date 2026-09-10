<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$method = getRequestMethod();
$orders = readJsonFile(ORDERS_FILE);

switch ($method) {
    case 'GET':
        $orderId = trim((string)($_GET['id'] ?? ($_GET['orderId'] ?? '')));
        $orderNumber = trim((string)($_GET['orderNumber'] ?? ''));

        if ($orderId !== '' || $orderNumber !== '') {
            foreach ($orders as $o) {
                if ($o['id'] === $orderId || $o['orderNumber'] === $orderNumber) {
                    jsonResponse(['success' => true, 'order' => $o]);
                }
            }
            jsonResponse(['success' => false, 'message' => 'Order not found.'], 404);
        }

        $user = getAuthenticatedUser();
        $adminView = isset($_GET['all']) || isset($_GET['admin']);

        if ($adminView) {
            if (!$user || ($user['role'] ?? '') !== 'admin') {
                jsonResponse(['success' => false, 'message' => 'Admin authorization required.'], 403);
            }
            jsonResponse([
                'success' => true,
                'count' => count($orders),
                'orders' => array_reverse($orders)
            ]);
        }

        // Return user's orders or recent orders
        if ($user) {
            $userOrders = array_values(array_filter($orders, function ($o) use ($user) {
                return (string)($o['userId'] ?? '') === (string)$user['id'] ||
                       strtolower((string)($o['customerEmail'] ?? '')) === strtolower((string)$user['email']);
            }));
            jsonResponse([
                'success' => true,
                'count' => count($userOrders),
                'orders' => array_reverse($userOrders)
            ]);
        }

        // If guest, check session order history
        $guestOrders = $_SESSION['guest_orders'] ?? [];
        $matched = array_values(array_filter($orders, fn($o) => in_array($o['id'], $guestOrders, true)));
        jsonResponse([
            'success' => true,
            'count' => count($matched),
            'orders' => array_reverse($matched)
        ]);
        break;

    case 'POST':
        $body = getRequestBody();
        $fullName = trim((string)($body['fullName'] ?? ($body['name'] ?? '')));
        $email = strtolower(trim((string)($body['email'] ?? '')));
        $phone = trim((string)($body['phone'] ?? ''));
        $address = trim((string)($body['address'] ?? ($body['street'] ?? '')));
        $city = trim((string)($body['city'] ?? 'Kathmandu'));
        $paymentMethod = trim((string)($body['paymentMethod'] ?? 'Cash on Delivery'));
        $notes = trim((string)($body['notes'] ?? ''));
        $couponCode = strtoupper(trim((string)($body['couponCode'] ?? '')));

        if ($fullName === '' || $email === '' || $phone === '' || $address === '') {
            jsonResponse([
                'success' => false,
                'message' => 'Please fill in all required delivery fields (Full Name, Email, Phone, Address).'
            ], 422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['success' => false, 'message' => 'Enter a valid email address.'], 422);
        }

        // Pull items
        $items = $body['items'] ?? [];
        $ownerKey = getCartOwnerKey();
        $cartData = readJsonFile(CARTS_FILE);

        if (empty($items) || !is_array($items)) {
            $userCart = $cartData[$ownerKey] ?? [];
            $items = $userCart['items'] ?? [];
        }

        if (empty($items)) {
            jsonResponse(['success' => false, 'message' => 'Your shopping cart is empty.'], 400);
        }

        $subtotal = 0;
        $cleanItems = [];
        foreach ($items as $item) {
            $qty = max(1, intval($item['quantity'] ?? 1));
            $price = floatval($item['price'] ?? 0);
            $lineTotal = $price * $qty;
            $subtotal += $lineTotal;
            $cleanItems[] = [
                'productId' => (string)($item['productId'] ?? ($item['id'] ?? '')),
                'name' => (string)($item['name'] ?? ($item['title'] ?? 'Product')),
                'price' => $price,
                'quantity' => $qty,
                'lineTotal' => $lineTotal,
                'image' => (string)($item['image'] ?? ($item['imageUrl'] ?? '')),
                'size' => (string)($item['size'] ?? 'M'),
                'color' => (string)($item['color'] ?? 'Standard'),
            ];
        }

        // Discount calculation
        $discount = 0.0;
        if ($couponCode !== '') {
            $coupons = readJsonFile(COUPONS_FILE);
            if (isset($coupons[$couponCode])) {
                $cpn = $coupons[$couponCode];
                if ($subtotal >= floatval($cpn['minSpend'] ?? 0)) {
                    if ($cpn['type'] === 'percent') {
                        $discount = round(($subtotal * floatval($cpn['value'])) / 100, 2);
                    } elseif ($cpn['type'] === 'flat') {
                        $discount = min($subtotal, floatval($cpn['value']));
                    }
                }
            }
        }

        $shipping = ($subtotal >= 2000 || (isset($cpn) && ($cpn['type'] ?? '') === 'free_shipping')) ? 0 : 150;
        $totalAmount = max(0, $subtotal - $discount + $shipping);

        $user = getAuthenticatedUser();
        $orderId = 'ord_' . bin2hex(random_bytes(6));
        $orderNumber = 'AL-' . date('Y') . '-' . str_pad((string)random_int(1000, 9999), 4, '0', STR_PAD_LEFT);

        $newOrder = [
            'id' => $orderId,
            'orderNumber' => $orderNumber,
            'userId' => $user ? (string)$user['id'] : 'guest',
            'customerName' => $fullName,
            'customerEmail' => $email,
            'customerPhone' => $phone,
            'shippingAddress' => $address,
            'city' => $city,
            'paymentMethod' => $paymentMethod,
            'paymentStatus' => ($paymentMethod === 'Cash on Delivery') ? 'Unpaid (COD)' : 'Paid',
            'status' => 'Pending',
            'items' => $cleanItems,
            'itemCount' => count($cleanItems),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'couponCode' => $couponCode ?: null,
            'shipping' => $shipping,
            'totalAmount' => $totalAmount,
            'formattedSubtotal' => formatPrice($subtotal),
            'formattedDiscount' => formatPrice($discount),
            'formattedShipping' => $shipping === 0 ? 'FREE' : formatPrice($shipping),
            'formattedTotal' => formatPrice($totalAmount),
            'notes' => $notes,
            'createdAt' => date('c'),
            'estimatedDelivery' => date('Y-m-d', strtotime('+3 days')),
        ];

        $orders[] = $newOrder;
        writeJsonFile(ORDERS_FILE, $orders);

        // Clear cart
        $cartData[$ownerKey] = ['items' => []];
        writeJsonFile(CARTS_FILE, $cartData);

        if (!isset($_SESSION['guest_orders'])) {
            $_SESSION['guest_orders'] = [];
        }
        $_SESSION['guest_orders'][] = $orderId;

        jsonResponse([
            'success' => true,
            'message' => 'Thank you! Your order ' . $orderNumber . ' has been placed successfully.',
            'order' => $newOrder
        ], 201);
        break;

    case 'PUT':
    case 'PATCH':
        // Admin status updates
        $user = requireAdmin();
        $body = getRequestBody();
        $orderId = trim((string)($body['id'] ?? ($_GET['id'] ?? '')));
        $newStatus = trim((string)($body['status'] ?? ''));
        $newPaymentStatus = trim((string)($body['paymentStatus'] ?? ''));

        if ($orderId === '') {
            jsonResponse(['success' => false, 'message' => 'Order ID is required.'], 422);
        }

        $found = false;
        foreach ($orders as &$order) {
            if ($order['id'] === $orderId || $order['orderNumber'] === $orderId) {
                if ($newStatus !== '') {
                    $order['status'] = $newStatus;
                }
                if ($newPaymentStatus !== '') {
                    $order['paymentStatus'] = $newPaymentStatus;
                }
                $order['updatedAt'] = date('c');
                $found = true;
                $updatedOrder = $order;
                break;
            }
        }
        unset($order);

        if (!$found) {
            jsonResponse(['success' => false, 'message' => 'Order not found.'], 404);
        }

        writeJsonFile(ORDERS_FILE, $orders);
        jsonResponse([
            'success' => true,
            'message' => 'Order status updated successfully.',
            'order' => $updatedOrder
        ]);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
}
