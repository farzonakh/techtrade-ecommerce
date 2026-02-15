<?php
declare(strict_types=1);

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_admin();

// Fetch orders with user details
$stmt = $pdo->query("
    SELECT o.id, o.total, o.created_at, u.full_name, u.email 
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.id DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . "/includes/header.php";
?>

<div class="action-bar">
    <div class="page-title">
        <h2>Orders</h2>
        <p class="text-muted">View and manage customer orders</p>
    </div>
</div>

<div class="admin-table-container">
  <?php if (count($orders) === 0): ?>
    <div style="padding: 3rem; text-align:center;">
        <p class="text-muted">No orders found.</p>
    </div>
  <?php else: ?>
    <table class="admin-table">
        <thead>
        <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Email</th>
            <th class="text-right">Total</th>
            <th class="text-right">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
            <td style="color:var(--text-muted); font-family:monospace;">#<?= (int)$order["id"] ?></td>
            <td style="font-weight:600;">
                <?= htmlspecialchars($order["full_name"] ?? "Unknown User") ?>
                <div style="font-size:0.8rem; color:var(--text-muted); font-weight:400;">
                    <?= $order['created_at'] ? date('M j, Y H:i', strtotime($order['created_at'])) : '' ?>
                </div>
            </td>
            <td><?= htmlspecialchars($order["email"] ?? "-") ?></td>
            <td class="text-right" style="font-weight:600; color:var(--text-main);">€<?= number_format((float)$order["total"], 2) ?></td>
            <td class="text-right">
                <a href="/ecommerce/admin/order_details.php?id=<?= (int)$order["id"] ?>" class="btn" style="padding: 0.25rem 0.75rem; font-size:0.8rem;">View Details</a>
            </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
  <?php endif; ?>
</div>

<?php
include __DIR__ . "/includes/footer.php";
?>
