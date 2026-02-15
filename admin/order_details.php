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

// Fetch Order Info
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

// Fetch Order Items
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

<div class="grid" style="grid-template-columns: 2fr 1fr; gap:var(--space-lg);">

    <!-- Items List -->
    <div class="card" style="padding:0; overflow:hidden;">
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
                    <td style="font-weight:600;"><?= htmlspecialchars($item['name']) ?></td>
                    <td class="text-right">€<?= number_format((float)$item['price'], 2) ?></td>
                    <td class="text-center">
                        <span class="badge" style="margin:0;"><?= (int)$item['quantity'] ?></span>
                    </td>
                    <td class="text-right" style="color:var(--text-main);">
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
                <div style="margin-bottom:var(--space-md);">
                    <div style="color:var(--text-muted); font-size:0.9rem;">Name</div>
                    <div style="font-weight:600;"><?= htmlspecialchars($order['full_name']) ?></div>
                </div>
                <div style="margin-bottom:var(--space-md);">
                    <div style="color:var(--text-muted); font-size:0.9rem;">Email</div>
                    <div><?= htmlspecialchars($order['email']) ?></div>
                </div>

                <hr style="border:0; border-top:1px solid rgba(255,255,255,0.1); margin:var(--space-md) 0;">

                <h3 class="cardTitle">Payment Info</h3>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="color:var(--text-muted);">Total Paid</span>
                    <span style="font-size:1.5rem; font-weight:700; color:var(--accent);">€<?= number_format((float)$order['total'], 2) ?></span>
                </div>
                
                <div style="margin-top:var(--space-lg); padding:var(--space-sm); background:rgba(34, 197, 94, 0.1); border-radius:var(--radius-sm); color:var(--success); text-align:center; font-weight:600;">
                    Paid ✅
                </div>
            </div>
        </div>
    </div>

</div>

<?php
include __DIR__ . "/includes/footer.php";
?>
