<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$method = getRequestMethod();
$products = readJsonFile(PRODUCTS_FILE);

if ($method === 'GET') {
    $id = trim((string)($_GET['id'] ?? ''));
    if ($id !== '') {
        foreach ($products as $p) {
            if ($p['id'] === $id) {
                jsonResponse(['success' => true, 'product' => $p]);
            }
        }
        jsonResponse(['success' => false, 'message' => 'Product not found.'], 404);
    }

    $category = strtolower(trim((string)($_GET['category'] ?? '')));
    $search = strtolower(trim((string)($_GET['search'] ?? ($_GET['q'] ?? ''))));
    $sort = strtolower(trim((string)($_GET['sort'] ?? '')));
    $badge = strtolower(trim((string)($_GET['badge'] ?? '')));
    $minPrice = floatval($_GET['min_price'] ?? 0);
    $maxPrice = floatval($_GET['max_price'] ?? 0);

    $filtered = array_values(array_filter($products, function ($p) use ($category, $search, $badge, $minPrice, $maxPrice) {
        if ($category !== '' && $category !== 'all') {
            $pCat = strtolower((string)($p['category'] ?? ''));
            if ($pCat !== $category && strpos($pCat, $category) === false) {
                return false;
            }
        }

        if ($search !== '') {
            $name = strtolower((string)($p['name'] ?? ''));
            $desc = strtolower((string)($p['description'] ?? ''));
            $cat = strtolower((string)($p['categoryName'] ?? ''));
            if (strpos($name, $search) === false && strpos($desc, $search) === false && strpos($cat, $search) === false) {
                return false;
            }
        }

        if ($badge !== '') {
            $pBadge = strtolower((string)($p['badge'] ?? ''));
            if (strpos($pBadge, $badge) === false) {
                return false;
            }
        }

        $price = floatval($p['price'] ?? 0);
        if ($minPrice > 0 && $price < $minPrice) {
            return false;
        }
        if ($maxPrice > 0 && $price > $maxPrice) {
            return false;
        }

        return true;
    }));

    if ($sort === 'price_asc' || $sort === 'low_high') {
        usort($filtered, fn($a, $b) => ($a['price'] ?? 0) <=> ($b['price'] ?? 0));
    } elseif ($sort === 'price_desc' || $sort === 'high_low') {
        usort($filtered, fn($a, $b) => ($b['price'] ?? 0) <=> ($a['price'] ?? 0));
    } elseif ($sort === 'rating') {
        usort($filtered, fn($a, $b) => ($b['rating'] ?? 0) <=> ($a['rating'] ?? 0));
    } elseif ($sort === 'name_asc') {
        usort($filtered, fn($a, $b) => strcmp((string)$a['name'], (string)$b['name']));
    }

    $categories = [
        ['slug' => 'all', 'name' => 'All Products'],
        ['slug' => 'dresses', 'name' => 'Dresses'],
        ['slug' => 'fashion', 'name' => 'Fashion'],
        ['slug' => 'beauty', 'name' => 'Beauty'],
        ['slug' => 'accessories', 'name' => 'Accessories'],
        ['slug' => 'new-arrivals', 'name' => 'New Arrivals'],
        ['slug' => 't-shirts', 'name' => 'T-Shirts'],
        ['slug' => 'jeans', 'name' => 'Jeans & Pants'],
    ];

    jsonResponse([
        'success' => true,
        'count' => count($filtered),
        'products' => $filtered,
        'categories' => $categories,
    ]);
}

if ($method === 'POST') {
    // Admin product addition or management
    $user = requireAdmin();
    $body = getRequestBody();

    $name = trim((string)($body['name'] ?? ''));
    $price = floatval($body['price'] ?? 0);
    $category = trim((string)($body['category'] ?? 'fashion'));
    $image = trim((string)($body['image'] ?? ''));
    $description = trim((string)($body['description'] ?? ''));
    $stock = max(1, intval($body['stock'] ?? 10));

    if ($name === '' || $price <= 0 || $image === '') {
        jsonResponse(['success' => false, 'message' => 'Please provide valid product name, price, and image.'], 422);
    }

    $newProduct = [
        'id' => 'prod-' . bin2hex(random_bytes(4)),
        'name' => $name,
        'price' => $price,
        'oldPrice' => floatval($body['oldPrice'] ?? round($price * 1.25)),
        'category' => $category,
        'categoryName' => ucfirst($category),
        'image' => $image,
        'description' => $description ?: 'Premium quality ' . $name . ' crafted for style and everyday comfort.',
        'stock' => $stock,
        'rating' => 5.0,
        'reviewsCount' => 1,
        'badge' => trim((string)($body['badge'] ?? 'New')),
    ];

    $products[] = $newProduct;
    writeJsonFile(PRODUCTS_FILE, $products);

    jsonResponse([
        'success' => true,
        'message' => 'Product added successfully.',
        'product' => $newProduct
    ], 201);
}
