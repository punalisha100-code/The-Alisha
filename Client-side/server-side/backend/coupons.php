<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$method = getRequestMethod();
$coupons = readJsonFile(COUPONS_FILE);

if ($method === 'POST') {
    $body = getRequestBody();
    $code = strtoupper(trim((string)($body['code'] ?? '')));
    $subtotal = floatval($body['subtotal'] ?? 0);

    if ($code === '') {
        jsonResponse(['success' => false, 'message' => 'Please enter a coupon code.'], 422);
    }

    if (!isset($coupons[$code])) {
        jsonResponse(['success' => false, 'message' => 'Invalid promo code. Try ALISHA10 or SUMMER50.'], 404);
    }

    $coupon = $coupons[$code];
    $minSpend = floatval($coupon['minSpend'] ?? 0);

    if ($subtotal < $minSpend) {
        jsonResponse([
            'success' => false,
            'message' => 'This coupon requires a minimum spend of ' . formatPrice($minSpend) . '.'
        ], 422);
    }

    $discount = 0.0;
    if ($coupon['type'] === 'percent') {
        $discount = round(($subtotal * floatval($coupon['value'])) / 100, 2);
    } elseif ($coupon['type'] === 'flat') {
        $discount = min($subtotal, floatval($coupon['value']));
    } elseif ($coupon['type'] === 'free_shipping') {
        $discount = 0.0; // Handled as shipping waiver
    }

    jsonResponse([
        'success' => true,
        'message' => 'Coupon code ' . $code . ' applied successfully!',
        'coupon' => [
            'code' => $code,
            'type' => $coupon['type'],
            'value' => $coupon['value'],
            'discount' => $discount,
            'formattedDiscount' => formatPrice($discount),
            'description' => $coupon['description'] ?? '',
        ]
    ]);
}

jsonResponse(['success' => true, 'coupons' => array_keys($coupons)]);
