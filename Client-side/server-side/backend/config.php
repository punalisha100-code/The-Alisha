<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    session_start();
}

if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
}

define('DATA_DIR', __DIR__ . '/data');
define('USERS_FILE', DATA_DIR . '/users.json');
define('CARTS_FILE', DATA_DIR . '/carts.json');
define('PRODUCTS_FILE', DATA_DIR . '/products.json');
define('ORDERS_FILE', DATA_DIR . '/orders.json');
define('COUPONS_FILE', DATA_DIR . '/coupons.json');

function getRequestMethod(): string
{
    return $_SERVER['REQUEST_METHOD'] ?? 'GET';
}

function getRequestBody(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return $_POST ?? [];
    }

    $decoded = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        return $decoded;
    }

    parse_str($raw, $parsed);
    return $parsed ?: [];
}

function jsonResponse(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function readJsonFile(string $path): array
{
    if (!is_file($path)) {
        return [];
    }
    $raw = file_get_contents($path);
    if ($raw === false || trim($raw) === '') {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function writeJsonFile(string $path, array $data): bool
{
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $tmp = $path . '.tmp.' . bin2hex(random_bytes(4));
    $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($encoded === false) {
        return false;
    }
    $written = file_put_contents($tmp, $encoded, LOCK_EX);
    if ($written === false) {
        return false;
    }
    return rename($tmp, $path);
}

function findUserByEmail(string $email): ?array
{
    $email = strtolower(trim($email));

    try {
        $pdo = getDatabaseConnection();
        ensureDatabaseSchema($pdo);
        $stmt = $pdo->prepare('SELECT id, full_name, email, phone, role, is_active, password_hash FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        if ($user) {
            return [
                'id' => (string)$user['id'],
                'fullName' => (string)($user['full_name'] ?? ''),
                'email' => (string)$user['email'],
                'phone' => (string)($user['phone'] ?? ''),
                'role' => (string)($user['role'] ?? 'user'),
                'isActive' => (bool)$user['is_active'],
                'passwordHash' => (string)($user['password_hash'] ?? ''),
                'createdAt' => '',
            ];
        }
    } catch (Throwable $e) {
        error_log('findUserByEmail database error: ' . $e->getMessage());
    }

    $users = readJsonFile(USERS_FILE);
    foreach ($users as $user) {
        if (isset($user['email']) && strtolower($user['email']) === $email) {
            return $user;
        }
    }
    return null;
}

function getAuthenticatedUser(): ?array
{
    if (!empty($_SESSION['user']) && !empty($_SESSION['user']['id'])) {
        return $_SESSION['user'];
    }
    return null;
}

if (!function_exists('requireAuthentication')) {
    function requireAuthentication(): array
    {
        $user = getAuthenticatedUser();
        if (!$user) {
            jsonResponse(['success' => false, 'message' => 'Authentication required.'], 401);
        }
        return $user;
    }
}

if (!function_exists('requireAdmin')) {
    function requireAdmin(): array
    {
        $user = requireAuthentication();
        if (($user['role'] ?? '') !== 'admin') {
            jsonResponse(['success' => false, 'message' => 'Admin access required.'], 403);
        }
        return $user;
    }
}


/**
 * Returns a persistent key for the cart owner.
 * If user is logged in, returns user's ID.
 * If user is a guest, returns/creates a guest session cart ID.
 */
function getCartOwnerKey(): string
{
    $user = getAuthenticatedUser();
    if ($user && !empty($user['id'])) {
        return (string)$user['id'];
    }

    if (empty($_SESSION['guest_cart_id'])) {
        $clientHeaderId = $_SERVER['HTTP_X_CART_SESSION'] ?? ($_COOKIE['alisha_cart_id'] ?? '');
        if (!empty($clientHeaderId) && preg_match('/^[a-zA-Z0-9_-]{8,64}$/', $clientHeaderId)) {
            $_SESSION['guest_cart_id'] = $clientHeaderId;
        } else {
            $_SESSION['guest_cart_id'] = 'guest_' . bin2hex(random_bytes(8));
        }
    }

    if (!headers_sent()) {
        setcookie('alisha_cart_id', $_SESSION['guest_cart_id'], time() + (86400 * 30), '/');
    }

    return (string)$_SESSION['guest_cart_id'];
}

function formatPrice($price): string
{
    return 'Rs. ' . number_format((float)$price, 2);
}

function esc(?string $str): string
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Initial directory check
if (!is_dir(DATA_DIR)) {
    @mkdir(DATA_DIR, 0755, true);
}

try {
    $defaultDb = getDatabaseConnection();
    ensureDatabaseSchema($defaultDb);
} catch (Throwable $e) {
    error_log('Database initialization failed: ' . $e->getMessage());
}

// Initialize seed data if missing
if (!is_file(USERS_FILE) || count(readJsonFile(USERS_FILE)) === 0) {
    $defaultUsers = [
        [
            'id' => 'admin_1',
            'fullName' => 'Alisha Store Admin',
            'email' => 'admin@alisha.com',
            'phone' => '9828176055',
            'role' => 'admin',
            'passwordHash' => password_hash('admin123', PASSWORD_DEFAULT),
            'createdAt' => date('c'),
            'isActive' => true
        ],
        [
            'id' => 'user_1',
            'fullName' => 'Alisha Magar',
            'email' => 'punalisha100@gmail.com',
            'phone' => '9828176055',
            'role' => 'user',
            'passwordHash' => password_hash('password123', PASSWORD_DEFAULT),
            'createdAt' => date('c'),
            'isActive' => true
        ]
    ];
    writeJsonFile(USERS_FILE, $defaultUsers);
}

if (!is_file(CARTS_FILE)) {
    writeJsonFile(CARTS_FILE, []);
}

if (!is_file(ORDERS_FILE)) {
    writeJsonFile(ORDERS_FILE, []);
}

if (!is_file(COUPONS_FILE)) {
    $defaultCoupons = [
        'ALISHA10' => ['type' => 'percent', 'value' => 10, 'minSpend' => 500, 'description' => '10% discount on entire order'],
        'SUMMER50' => ['type' => 'flat', 'value' => 200, 'minSpend' => 1000, 'description' => 'Rs. 200 flat discount'],
        'FREESHIP' => ['type' => 'free_shipping', 'value' => 0, 'minSpend' => 0, 'description' => 'Free shipping on any order'],
    ];
    writeJsonFile(COUPONS_FILE, $defaultCoupons);
}

// Initialize product catalog if missing
if (!is_file(PRODUCTS_FILE) || count(readJsonFile(PRODUCTS_FILE)) === 0) {
    $seedProducts = [
        [
            'id' => 'prod-pink-dress',
            'name' => 'Pretty onepice Dress',
            'price' => 4000,
            'oldPrice' => 4800,
            'category' => 'dresses',
            'categoryName' => 'Dresses',
            'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR3yOf4AkP65aj5lC_400AvRc7G-2plv2hs5YbyMrpq-w&s',
            'description' => 'A chic and breezy pink summer onepiece dress designed with premium breathable fabric and a flattering silhouette.',
            'stock' => 15,
            'rating' => 4.9,
            'reviewsCount' => 38,
            'badge' => 'Best Seller'
        ],
        [
            'id' => 'prod-quilted-bag',
            'name' => 'Elegant Quilted Bag',
            'price' => 1600,
            'oldPrice' => 2200,
            'category' => 'accessories',
            'categoryName' => 'Accessories',
            'image' => 'https://coach.scene7.com/is/image/Coach/ccx04_b4mpl_a0?$mobileProductTile$',
            'description' => 'High-quality quilted leatherette crossbody bag featuring golden accents and spacious multi-compartment interior.',
            'stock' => 24,
            'rating' => 4.8,
            'reviewsCount' => 45,
            'badge' => 'Popular'
        ],
        [
            'id' => 'prod-rosy-lipstick',
            'name' => 'Rosy Velvet Lipstick',
            'price' => 300,
            'oldPrice' => 450,
            'category' => 'beauty',
            'categoryName' => 'Beauty',
            'image' => 'https://static-01.daraz.com.np/p/1f496b9c4414cc8d9ed8f1fdbdd3e2bb.jpg',
            'description' => 'Ultra-matte velvety long-lasting lipstick enriched with vitamin E and nourishing botanical oils.',
            'stock' => 60,
            'rating' => 4.7,
            'reviewsCount' => 82,
            'badge' => 'Hot'
        ],
        [
            'id' => 'prod-kurthi',
            'name' => 'Embroidered Kurthi',
            'price' => 2000,
            'oldPrice' => 2600,
            'category' => 'fashion',
            'categoryName' => 'Fashion',
            'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSi-AzqnZErD3BL4UEBWRuKh5VIglaKnmptb_euF0wf0g&s',
            'description' => 'Hand-crafted traditional embroidery kurthi with contemporary cut and comfortable pure cotton feel.',
            'stock' => 18,
            'rating' => 4.9,
            'reviewsCount' => 29,
            'badge' => 'Trending'
        ],
        [
            'id' => 'prod-heels',
            'name' => 'Classic Block Heels',
            'price' => 1500,
            'oldPrice' => 1950,
            'category' => 'accessories',
            'categoryName' => 'Accessories',
            'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQtSUwGjkyFQNU1Hp78k5SGEiFM7Gs4n2qQ0ysJle4HYiPk5ajbUdSEwXuU&s=10',
            'description' => 'Versatile cushioned block heels providing all-day comfort for office, parties, and everyday styling.',
            'stock' => 12,
            'rating' => 4.6,
            'reviewsCount' => 19,
            'badge' => 'Popular'
        ],
        [
            'id' => 'prod-tshirt',
            'name' => 'Casual Cotton T-shirt',
            'price' => 700,
            'oldPrice' => 950,
            'category' => 't-shirts',
            'categoryName' => 'T-Shirts',
            'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSLh9z9iQyStGqWEBw6_tpNNpzDUL45V2txdAuZOKwMiMALVqCAvzWlmuHh&s=10',
            'description' => 'Super-soft combed cotton tee with ribbed crew neck and relaxed effortless fit.',
            'stock' => 40,
            'rating' => 4.8,
            'reviewsCount' => 64,
            'badge' => 'Essential'
        ],
        [
            'id' => 'prod-pants',
            'name' => 'Tailored Slim Pants',
            'price' => 1300,
            'oldPrice' => 1700,
            'category' => 'jeans',
            'categoryName' => 'Jeans & Pants',
            'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRm1cXLAx0b2tJxWlcIRkU8IFVoyPdiEdOUDhHwxzJW-J3IDi0-5n02oZxk&s=10',
            'description' => 'Stretch-comfort tailored pants featuring mid-rise waistband and clean modern taper.',
            'stock' => 22,
            'rating' => 4.7,
            'reviewsCount' => 31,
            'badge' => 'Best Seller'
        ],
        [
            'id' => 'prod-linen-blazer',
            'name' => 'Tailored Linen Blazer',
            'price' => 3500,
            'oldPrice' => 4200,
            'category' => 'fashion',
            'categoryName' => 'Fashion',
            'image' => 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?auto=format&fit=crop&w=800&q=80',
            'description' => 'Relaxed silhouette crafted from breathable organic linen with sleek notched lapels.',
            'stock' => 14,
            'rating' => 4.9,
            'reviewsCount' => 28,
            'badge' => 'New'
        ],
        [
            'id' => 'prod-knit-sweater',
            'name' => 'Oversized Knit Sweater',
            'price' => 2999,
            'oldPrice' => 3600,
            'category' => 'fashion',
            'categoryName' => 'Fashion',
            'image' => 'https://images.unsplash.com/photo-1576995853123-5a10305d93c0?auto=format&fit=crop&w=800&q=80',
            'description' => 'Heavyweight merino wool blend featuring ribbed trim and cozy dropped shoulders.',
            'stock' => 18,
            'rating' => 4.8,
            'reviewsCount' => 36,
            'badge' => 'Trending'
        ],
        [
            'id' => 'prod-pleated-trousers',
            'name' => 'Pleated Wide Trousers',
            'price' => 3500,
            'oldPrice' => 4100,
            'category' => 'fashion',
            'categoryName' => 'Fashion',
            'image' => 'https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&w=800&q=80',
            'description' => 'High-waisted fit with deep pleats, wide leg profile, and premium fluid drape.',
            'stock' => 10,
            'rating' => 4.9,
            'reviewsCount' => 22,
            'badge' => 'New'
        ],
        [
            'id' => 'prod-dewy-serum',
            'name' => 'Dewy Glow Skin Serum',
            'price' => 1200,
            'oldPrice' => 1500,
            'category' => 'beauty',
            'categoryName' => 'Beauty',
            'image' => 'https://images.unsplash.com/photo-1512496015851-a90fb38ba796?auto=format&fit=crop&w=800&q=80',
            'description' => 'Hydrating hyaluronic acid and niacinamide facial serum for a luminous glass-skin glow.',
            'stock' => 35,
            'rating' => 4.9,
            'reviewsCount' => 74,
            'badge' => 'Editor Pick'
        ],
        [
            'id' => 'prod-satin-dress',
            'name' => 'Satin Slip Evening Dress',
            'price' => 3200,
            'oldPrice' => 3900,
            'category' => 'dresses',
            'categoryName' => 'Dresses',
            'image' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=800&q=80',
            'description' => 'Glossy silky satin slip dress with cowl neck and adjustable delicate spaghetti straps.',
            'stock' => 16,
            'rating' => 4.8,
            'reviewsCount' => 41,
            'badge' => 'Glam'
        ],
        [
            'id' => 'prod-leather-belt',
            'name' => 'Minimalist Leather Belt',
            'price' => 500,
            'oldPrice' => 750,
            'category' => 'accessories',
            'categoryName' => 'Accessories',
            'image' => 'https://images.unsplash.com/photo-1624222247344-550fb60583dc?auto=format&fit=crop&w=600&q=80',
            'description' => 'Full-grain leather belt finished with a brushed solid brass buckle and clean stitch lines.',
            'stock' => 50,
            'rating' => 4.7,
            'reviewsCount' => 33,
            'badge' => 'New Arrival'
        ],
        [
            'id' => 'prod-sunglasses',
            'name' => 'UV400 Polarized Sunglasses',
            'price' => 855,
            'oldPrice' => 1200,
            'category' => 'accessories',
            'categoryName' => 'Accessories',
            'image' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=600&q=80',
            'description' => 'Classic acetate frame offering 100% UV400 solar protection and ultralight comfort.',
            'stock' => 28,
            'rating' => 4.8,
            'reviewsCount' => 52,
            'badge' => 'Trending'
        ],
        [
            'id' => 'prod-silk-scarf',
            'name' => 'Silk Patterned Scarf',
            'price' => 620,
            'oldPrice' => 890,
            'category' => 'accessories',
            'categoryName' => 'Accessories',
            'image' => 'https://images.unsplash.com/photo-1601924994987-69e26d50dc26?auto=format&fit=crop&w=600&q=80',
            'description' => '100% mulberry silk scarf featuring hand-rolled edges and vibrant designer botanical prints.',
            'stock' => 30,
            'rating' => 4.9,
            'reviewsCount' => 27,
            'badge' => 'Limited'
        ],
        [
            'id' => 'prod-long-onepiece-1',
            'name' => 'Floral Long Onepiece',
            'price' => 2500,
            'oldPrice' => 3100,
            'category' => 'new-arrivals',
            'categoryName' => 'New Arrivals',
            'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQePizqT3-jra4cnzS0WnnJZzbfecXn_5Z5mW0Y0wDB5g&s=10',
            'description' => 'Flowy ankle-length floral maxi onepiece dress with cinched waist and flutter sleeves.',
            'stock' => 20,
            'rating' => 4.9,
            'reviewsCount' => 15,
            'badge' => 'New'
        ],
        [
            'id' => 'prod-long-onepiece-2',
            'name' => 'Pastel Tiered Onepiece',
            'price' => 2400,
            'oldPrice' => 2950,
            'category' => 'new-arrivals',
            'categoryName' => 'New Arrivals',
            'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSQzJal3ISYePvUWMuXNhZ5-HVd1MI9WdFPBnB9HMnXCh91PRdJ2Q9ghQ0&s=10',
            'description' => 'Pastel layered tiered maxi onepiece dress crafted from lightweight georgette fabric.',
            'stock' => 18,
            'rating' => 4.8,
            'reviewsCount' => 19,
            'badge' => 'New'
        ],
        [
            'id' => 'prod-long-onepiece-3',
            'name' => 'Boho Printed Onepiece',
            'price' => 1700,
            'oldPrice' => 2200,
            'category' => 'new-arrivals',
            'categoryName' => 'New Arrivals',
            'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQvm-1mpeBBTR2jZ2TItXQRhYkPcqg49jRtvV8w29H1IvOQviRPEcj6UjE&s=10',
            'description' => 'Boho chic printed midi dress featuring breathable rayon and flattering smocked bodice.',
            'stock' => 25,
            'rating' => 4.7,
            'reviewsCount' => 23,
            'badge' => 'New'
        ],
        [
            'id' => 'prod-long-onepiece-4',
            'name' => 'Luxe Summer Onepiece',
            'price' => 1700,
            'oldPrice' => 2100,
            'category' => 'new-arrivals',
            'categoryName' => 'New Arrivals',
            'image' => 'https://assets.myntassets.com/assets/images/2026/JUNE/29/gZ5nVj1X_33f40317bd864bca88a3e97dd520f8f5.jpg',
            'description' => 'Sleek summer fit-and-flare onepiece dress with modern minimal accents.',
            'stock' => 21,
            'rating' => 4.8,
            'reviewsCount' => 12,
            'badge' => 'New'
        ],
        [
            'id' => 'prod-onepiece-5',
            'name' => 'Classic Everyday Onepiece',
            'price' => 2000,
            'oldPrice' => 2500,
            'category' => 'new-arrivals',
            'categoryName' => 'New Arrivals',
            'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQf5ZFcKB3NWWhxIzpX8g-7C7XF9BCIXM7ZlBwcnYKCVA&s=10',
            'description' => 'Effortlessly stylish everyday knee-length onepiece dress with side pockets.',
            'stock' => 19,
            'rating' => 4.9,
            'reviewsCount' => 17,
            'badge' => 'New'
        ]
    ];
    writeJsonFile(PRODUCTS_FILE, $seedProducts);
}

