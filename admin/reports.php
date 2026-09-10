<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../includes/functions.php';

$pdo = getPDO();
// Simple monthly sales report (last 6 months)
$stmt = dbPrepare("SELECT DATE_FORMAT(created_at, '%Y-%m') as ym, SUM(total_amount) as total FROM orders GROUP BY ym ORDER BY ym DESC LIMIT 6");
$stmt->execute();
$sales = $stmt->fetchAll();

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Reports — Admin</title>
  <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="admin-page">
  <aside class="admin-sidebar">
    <div class="brand">The-Alisha</div>
    <nav>
      <a href="/admin/index.php">Dashboard</a>
      <a href="/admin/customers.php">Customers</a>
      <a href="/admin/orders.php">Orders</a>
      <a href="/admin/reports.php" class="active">Reports</a>
      <a href="/admin/logout.php">Logout</a>
    </nav>
  </aside>
  <main class="admin-main">
    <header class="admin-top">
      <h1>Reports</h1>
    </header>

    <section>
      <h2>Monthly Sales (last 6 months)</h2>
      <table class="admin-table">
        <thead><tr><th>Month</th><th>Sales</th></tr></thead>
        <tbody>
        <?php foreach ($sales as $s): ?>
          <tr>
            <td><?php echo esc($s['ym']); ?></td>
            <td><?php echo formatPrice($s['total']); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </section>
  </main>
</body>
</html>
