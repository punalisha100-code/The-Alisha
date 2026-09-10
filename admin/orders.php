<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../includes/functions.php';

$pdo = getPDO();
$stmt = dbPrepare('SELECT o.id, o.order_number, o.customer_name, o.total_amount, o.status, o.created_at FROM orders o ORDER BY o.created_at DESC LIMIT 200');
$stmt->execute();
$orders = $stmt->fetchAll();

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Orders — Admin</title>
  <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="admin-page">
  <aside class="admin-sidebar">
    <div class="brand">The-Alisha</div>
    <nav>
      <a href="/admin/index.php">Dashboard</a>
      <a href="/admin/customers.php">Customers</a>
      <a href="/admin/orders.php" class="active">Orders</a>
      <a href="/admin/reports.php">Reports</a>
      <a href="/admin/logout.php">Logout</a>
    </nav>
  </aside>
  <main class="admin-main">
    <header class="admin-top">
      <h1>Orders</h1>
    </header>

    <section>
      <table class="admin-table">
        <thead>
          <tr><th>Order #</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td><?php echo esc($o['order_number']); ?></td>
            <td><?php echo esc($o['customer_name']); ?></td>
            <td><?php echo formatPrice($o['total_amount']); ?></td>
            <td><?php echo esc($o['status']); ?></td>
            <td><?php echo esc($o['created_at']); ?></td>
            <td><a href="/admin/order_view.php?id=<?php echo (int)$o['id']; ?>">View</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </section>
  </main>
</body>
</html>
