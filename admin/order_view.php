<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../includes/functions.php';

$pdo = getPDO();
$orderNum = trim((string)($_GET['num'] ?? ''));
$orderId = (int)($_GET['id'] ?? 0);

if ($orderNum !== '') {
    $stmt = dbPrepare('SELECT * FROM orders WHERE order_number = :num LIMIT 1');
    $stmt->execute([':num' => $orderNum]);
} else {
    $stmt = dbPrepare('SELECT * FROM orders WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $orderId]);
}
$order = $stmt->fetch();

$allOrders = readJsonFile(ORDERS_FILE);
$jsonOrder = null;
if ($order) {
    foreach ($allOrders as $o) {
        if (($o['orderNumber'] ?? '') === $order['order_number'] || ($o['id'] ?? '') === $order['id']) {
            $jsonOrder = $o;
            break;
        }
    }
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $order) {
    $newStatus = trim((string)($_POST['status'] ?? ''));
    $newPaymentStatus = trim((string)($_POST['payment_status'] ?? ''));

    if ($newStatus) {
        $updateStmt = dbPrepare('UPDATE orders SET status = :st, payment_status = :ps WHERE order_number = :num');
        $updateStmt->execute([':st' => $newStatus, ':ps' => $newPaymentStatus, ':num' => $order['order_number']]);

        // Sync back to JSON
        foreach ($allOrders as &$o) {
            if (($o['orderNumber'] ?? '') === $order['order_number']) {
                $o['status'] = $newStatus;
                $o['paymentStatus'] = $newPaymentStatus;
                break;
            }
        }
        unset($o);
        writeJsonFile(ORDERS_FILE, $allOrders);

        $order['status'] = $newStatus;
        $order['payment_status'] = $newPaymentStatus;
        $message = 'Order updated successfully!';
    }
}

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Order Details — Admin</title>
  <link rel="stylesheet" href="/assets/css/admin.css">
  <link rel="stylesheet" href="/The-Alisha/assets/css/admin.css">
  <style>
    .order-view-card { background: #fff; border-radius: 18px; padding: 24px; box-shadow: var(--shadow); margin-bottom: 24px; }
    .status-select { padding: 8px 14px; border-radius: 8px; border: 1px solid #cbd5e1; font-weight: 600; font-size: 0.95rem; }
    .btn-save { background: #4f46e5; color: #fff; border: none; padding: 9px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; }
    .alert-msg { background: #dcfce7; color: #15803d; padding: 12px 16px; border-radius: 8px; margin-bottom: 18px; font-weight: 600; }
  </style>
</head>
<body class="admin-page">
  <aside class="admin-sidebar">
    <div class="brand">The-Alisha</div>
    <nav>
      <a href="/The-Alisha/admin/index.php">Dashboard</a>
      <a href="/The-Alisha/admin/customers.php">Customers</a>
      <a href="/The-Alisha/admin/orders.php" class="active">Orders</a>
      <a href="/The-Alisha/admin/reports.php">Reports</a>
      <a href="/The-Alisha/admin/logout.php">Logout</a>
    </nav>
  </aside>

  <main class="admin-main">
    <header class="admin-top">
      <div style="display:flex; justify-content:space-between; align-items:center;">
        <div>
          <h1>Order #<?php echo esc($order['order_number'] ?? 'Not Found'); ?></h1>
          <p class="muted">Placed on <?php echo esc($order['created_at'] ?? 'N/A'); ?></p>
        </div>
        <a href="/The-Alisha/admin/orders.php" class="btn-save" style="background:#64748b; text-decoration:none;">&larr; Back to Orders</a>
      </div>
    </header>

    <?php if ($message): ?>
      <div class="alert-msg"><?php echo esc($message); ?></div>
    <?php endif; ?>

    <?php if (!$order): ?>
      <div class="order-view-card">
        <p>Order not found or has been removed.</p>
      </div>
    <?php else: ?>
      <div class="dashboard-grid">
        <section class="order-view-card">
          <h3>Customer & Shipping Details</h3>
          <p><strong>Customer Name:</strong> <?php echo esc($order['customer_name']); ?></p>
          <p><strong>Email:</strong> <?php echo esc($order['customer_email']); ?></p>
          <p><strong>Phone:</strong> <?php echo esc($order['customer_phone']); ?></p>
          <p><strong>Delivery Address:</strong> <?php echo esc($order['shipping_address'] . ', ' . $order['city']); ?></p>
          <p><strong>Payment Method:</strong> <?php echo esc($order['payment_method']); ?></p>
          <?php if (!empty($order['notes'])): ?>
            <p><strong>Order Notes:</strong> <?php echo esc($order['notes']); ?></p>
          <?php endif; ?>

          <h3 style="margin-top: 24px;">Items Ordered</h3>
          <table class="compact-table">
            <thead>
              <tr><th>Item</th><th>Price</th><th>Qty</th><th>Line Total</th></tr>
            </thead>
            <tbody>
              <?php 
                $items = $jsonOrder['items'] ?? [];
                if (empty($items)):
              ?>
                <tr><td colspan="4">Standard Product Bundle</td></tr>
              <?php else: ?>
                <?php foreach ($items as $it): ?>
                  <tr>
                    <td><strong><?php echo esc($it['name']); ?></strong> (Size: <?php echo esc($it['size'] ?? 'M'); ?>)</td>
                    <td><?php echo formatPrice($it['price']); ?></td>
                    <td><?php echo (int)$it['quantity']; ?></td>
                    <td><?php echo formatPrice($it['lineTotal'] ?? ($it['price'] * $it['quantity'])); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </section>

        <section class="order-view-card">
          <h3>Order Status & Financials</h3>
          
          <form method="post" style="margin-bottom: 24px;">
            <div style="margin-bottom: 14px;">
              <label style="display:block; font-weight:700; margin-bottom:6px;">Fulfillment Status:</label>
              <select name="status" class="status-select">
                <option value="Pending" <?php echo ($order['status'] ?? '') === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="Processing" <?php echo ($order['status'] ?? '') === 'Processing' ? 'selected' : ''; ?>>Processing</option>
                <option value="Shipped" <?php echo ($order['status'] ?? '') === 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                <option value="Delivered" <?php echo ($order['status'] ?? '') === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                <option value="Cancelled" <?php echo ($order['status'] ?? '') === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
              </select>
            </div>

            <div style="margin-bottom: 18px;">
              <label style="display:block; font-weight:700; margin-bottom:6px;">Payment Status:</label>
              <select name="payment_status" class="status-select">
                <option value="Pending" <?php echo ($order['payment_status'] ?? '') === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="Paid" <?php echo ($order['payment_status'] ?? '') === 'Paid' ? 'selected' : ''; ?>>Paid</option>
                <option value="Refunded" <?php echo ($order['payment_status'] ?? '') === 'Refunded' ? 'selected' : ''; ?>>Refunded</option>
              </select>
            </div>

            <button type="submit" class="btn-save">Update Order</button>
          </form>

          <hr style="border:none; border-top:1px solid #e2e8f0; margin: 18px 0;" />

          <div style="font-size:0.95rem; line-height: 1.8;">
            <div style="display:flex; justify-content:space-between;"><span>Subtotal:</span> <span><?php echo formatPrice((float)($order['subtotal'] ?? 0)); ?></span></div>
            <div style="display:flex; justify-content:space-between;"><span>Discount:</span> <span>-<?php echo formatPrice((float)($order['discount'] ?? 0)); ?></span></div>
            <div style="display:flex; justify-content:space-between;"><span>Shipping:</span> <span><?php echo formatPrice((float)($order['shipping'] ?? 0)); ?></span></div>
            <div style="display:flex; justify-content:space-between; font-size:1.2rem; font-weight:800; color:#4f46e5; margin-top:8px;">
              <span>Grand Total:</span>
              <span><?php echo formatPrice((float)($order['total_amount'] ?? 0)); ?></span>
            </div>
          </div>
        </section>
      </div>
    <?php endif; ?>
  </main>
</body>
</html>
