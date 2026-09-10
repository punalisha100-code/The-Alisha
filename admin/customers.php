<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../includes/functions.php';

$pdo = getPDO();
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$stmt = dbPrepare('SELECT id, full_name, email, phone, role, is_active, created_at FROM users ORDER BY created_at DESC LIMIT :offset, :limit');
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();
$total = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$pages = (int)ceil($total / $perPage);

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Customers — Admin</title>
  <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="admin-page">
  <aside class="admin-sidebar">
    <div class="brand">The-Alisha</div>
    <nav>
      <a href="/admin/index.php">Dashboard</a>
      <a href="/admin/customers.php" class="active">Customers</a>
      <a href="/admin/orders.php">Orders</a>
      <a href="/admin/reports.php">Reports</a>
      <a href="/admin/logout.php">Logout</a>
    </nav>
  </aside>
  <main class="admin-main">
    <header class="admin-top">
      <h1>Customers</h1>
    </header>

    <section>
      <table class="admin-table">
        <thead>
          <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Active</th><th>Joined</th></tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?php echo (int)$u['id']; ?></td>
            <td><?php echo esc($u['full_name']); ?></td>
            <td><?php echo esc($u['email']); ?></td>
            <td><?php echo esc($u['phone']); ?></td>
            <td><?php echo esc($u['role']); ?></td>
            <td><?php echo $u['is_active'] ? 'Yes' : 'No'; ?></td>
            <td><?php echo esc($u['created_at']); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>

      <div class="pagination">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
          <a class="page-link <?php echo $i === $page ? 'active' : ''; ?>" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
      </div>
    </section>
  </main>
</body>
</html>
