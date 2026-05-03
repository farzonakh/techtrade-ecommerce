<?php
declare(strict_types=1);

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_admin();

// Fetch orders with user details.
$stmt = $pdo->query("
    SELECT o.id, o.total, o.created_at, u.full_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
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
    <div class="admin-empty">
        <p class="text-muted">No orders found.</p>
    </div>
  <?php else: ?>
    <table class="admin-table">
        <thead>
        <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Date</th>
            <th class="text-right">Total</th>
            <th class="text-right">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
            <td class="admin-id">#<?= (int)$order["id"] ?></td>
            <td class="admin-name"><?= htmlspecialchars($order["full_name"] ?? "Unknown User") ?></td>
            <td><?= $order["created_at"] ? htmlspecialchars(date("M j, Y H:i", strtotime((string)$order["created_at"]))) : "-" ?></td>
            <td class="text-right admin-total">€<?= number_format((float)$order["total"], 2) ?></td>
            <td class="text-right">
                <a href="/ecommerce/admin/order_details.php?id=<?= (int)$order["id"] ?>" class="btn admin-action-small">View Details</a>
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
