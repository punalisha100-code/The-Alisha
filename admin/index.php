<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../includes/functions.php';

$pdo = getPDO();
// Stats
$totalUsers = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalProducts = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalOrders = (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$totalSales = (float)$pdo->query('SELECT COALESCE(SUM(total_amount),0) FROM orders')->fetchColumn();
$pendingOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'")->fetchColumn();
$lowStock = (int)$pdo->query('SELECT COUNT(*) FROM products WHERE stock < 5')->fetchColumn();

// Monthly sales (last 6 months)
$stmt = dbPrepare("SELECT DATE_FORMAT(created_at, '%Y-%m') as ym, COALESCE(SUM(total_amount),0) as total FROM orders GROUP BY ym ORDER BY ym DESC LIMIT 6");
$stmt->execute();
$monthlySales = array_reverse($stmt->fetchAll());

// Recent orders
$stmt = dbPrepare('SELECT id, order_number, customer_name, total_amount, status, created_at FROM orders ORDER BY created_at DESC LIMIT 6');
$stmt->execute();
$recentOrders = $stmt->fetchAll();

// Recent customers
$stmt = dbPrepare('SELECT id, full_name, email, created_at FROM users ORDER BY created_at DESC LIMIT 6');
$stmt->execute();
$recentCustomers = $stmt->fetchAll();

// Low stock products
$stmt = dbPrepare('SELECT id, name, stock FROM products WHERE stock < 6 ORDER BY stock ASC LIMIT 6');
$stmt->execute();
$lowStockProducts = $stmt->fetchAll();

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Dashboard — The-Alisha</title>
  <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="admin-page">
  <aside class="admin-sidebar">
    <div class="brand">The-Alisha</div>
    <nav>
      <a href="/admin/index.php" class="active">Dashboard</a>
      <a href="/admin/customers.php">Customers</a>
      <a href="/admin/orders.php">Orders</a>
      <a href="/admin/reports.php">Reports</a>
      <a href="/admin/logout.php">Logout</a>
    </nav>
  </aside>
  <main class="admin-main">
    <header class="admin-top">
      <h1>Dashboard</h1>
      <p class="muted">Overview of store activity and quick actions</p>
    </header>

    <section class="admin-cards">
      <div class="card">
        <h4>Total Users</h4>
        <p class="large"><?php echo $totalUsers; ?></p>
        <small class="muted">Registered customers</small>
      </div>
      <div class="card">
        <h4>Total Products</h4>
        <p class="large"><?php echo $totalProducts; ?></p>
        <small class="muted">Active product listings</small>
      </div>
      <div class="card">
        <h4>Total Orders</h4>
        <p class="large"><?php echo $totalOrders; ?></p>
        <small class="muted">All orders placed</small>
      </div>
      <div class="card">
        <h4>Total Sales</h4>
        <p class="large"><?php echo 'Rs. ' . number_format((float)$totalSales,2); ?></p>
        <small class="muted">Lifetime sales</small>
      </div>
      <div class="card">
        <h4>Pending Orders</h4>
        <p class="large"><?php echo $pendingOrders; ?></p>
        <small class="muted">Awaiting processing</small>
      </div>
      <div class="card">
        <h4>Low Stock</h4>
        <p class="large"><?php echo $lowStock; ?></p>
        <small class="muted">Products below threshold</small>
      </div>
    </section>

    <section class="dashboard-grid">
      <div class="panel panel-chart">
        <h3>Sales (last 6 months)</h3>
        <canvas id="salesChart" width="600" height="240" aria-label="Monthly sales chart"></canvas>
      </div>

      <div class="panel panel-list">
        <h3>Recent Orders</h3>
        <table class="compact-table">
          <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach ($recentOrders as $o): ?>
            <tr>
              <td><?php echo esc($o['order_number']); ?></td>
              <td><?php echo esc($o['customer_name']); ?></td>
              <td><?php echo formatPrice($o['total_amount']); ?></td>
              <td><?php echo esc($o['status']); ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="panel panel-list">
        <h3>Recent Customers</h3>
        <ul class="compact-list">
          <?php foreach ($recentCustomers as $c): ?>
            <li><?php echo esc($c['full_name']); ?> — <small class="muted"><?php echo esc($c['email']); ?></small></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div class="panel panel-list">
        <h3>Low Stock Products</h3>
        <ul class="compact-list">
          <?php if (count($lowStockProducts) === 0): ?>
            <li>No low stock products.</li>
          <?php else: ?>
            <?php foreach ($lowStockProducts as $p): ?>
              <li><?php echo esc($p['name']); ?> — <strong><?php echo (int)$p['stock']; ?></strong> left</li>
            <?php endforeach; ?>
          <?php endif; ?>
        </ul>
      </div>
    </section>

    <script>
      // Embed monthly sales data for charting
      window.__TA_MONTHLY_SALES = <?php echo json_encode(array_map(function($r){return ['month'=>$r['ym'],'total'=> (float)$r['total']];}, $monthlySales)); ?>;
    </script>
    <script src="/assets/js/admin.js"></script>
  </main>
</body>
</html>
