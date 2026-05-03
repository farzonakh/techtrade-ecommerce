<?php
declare(strict_types=1);

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_admin();

$orderId = (int)($_GET['id'] ?? 0);
if ($orderId <= 0) {
    header("Location: /ecommerce/admin/orders.php");
    exit;
}

// Fetch order information.
$stmt = $pdo->prepare("
    SELECT o.id, o.total, u.full_name, u.email, o.created_at
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Order not found");
}

// Fetch products inside this order.
$stmtItems = $pdo->prepare("
    SELECT oi.quantity, oi.price, p.name 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmtItems->execute([$orderId]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . "/includes/header.php";
?>

<div class="action-bar">
    <div class="page-title">
        <h2>Order #<?= $order['id'] ?></h2>
        <p class="text-muted">Placed by <?= htmlspecialchars($order['full_name']) ?></p>
    </div>
    <a href="/ecommerce/admin/orders.php" class="btn">← Back to Orders</a>
</div>

<div class="grid grid-2-1 gap-lg">

    <!-- Items List -->
    <div class="card table-card">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th class="text-right">Price</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td class="admin-name"><?= htmlspecialchars($item['name']) ?></td>
                    <td class="text-right">€<?= number_format((float)$item['price'], 2) ?></td>
                    <td class="text-center">
                        <span class="badge badge-compact"><?= (int)$item['quantity'] ?></span>
                    </td>
                    <td class="text-right text-main">
                        €<?= number_format((float)$item['price'] * (int)$item['quantity'], 2) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Order Summary -->
    <div>
        <div class="card">
            <div class="cardBody">
                <h3 class="cardTitle">Customer Info</h3>
                <div class="mb-md">
                    <div class="summary-label">Name</div>
                    <div class="admin-name"><?= htmlspecialchars($order['full_name']) ?></div>
                </div>
                <div class="mb-md">
                    <div class="summary-label">Email</div>
                    <div><?= htmlspecialchars($order['email']) ?></div>
                </div>
                <div class="mb-md">
                    <div class="summary-label">Order Date</div>
                    <div><?= $order['created_at'] ? htmlspecialchars(date('M j, Y H:i', strtotime((string)$order['created_at']))) : '-' ?></div>
                </div>

                <hr class="divider">

                <h3 class="cardTitle">Payment Info</h3>
                <div class="summary-total-bar">
                    <span class="text-muted">Total Paid</span>
                    <span class="summary-total-value">€<?= number_format((float)$order['total'], 2) ?></span>
                </div>
                
                <div class="delivery-note">
                    Paid ✅
                </div>
            </div>
        </div>
    </div>

</div>

<?php
include __DIR__ . "/includes/footer.php";
?>
