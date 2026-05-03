<?php
declare(strict_types=1);

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";

require_login();

$orderId = (int)($_GET["id"] ?? 0);
if ($orderId <= 0) {
    header("Location: /ecommerce/public/orders.php");
    exit;
}

$user = current_user();
$userId = (int)($user["id"] ?? 0);

// Get only this user's order so users cannot view each other's orders.
$stmt = $pdo->prepare("
    SELECT id, total, created_at
    FROM orders
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    http_response_code(404);
    die("Order not found");
}

// Get the products that belong to this order.
$stmtItems = $pdo->prepare("
    SELECT oi.quantity, oi.price, p.name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
    ORDER BY oi.id ASC
");
$stmtItems->execute([$orderId]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . "/../includes/header.php";
?>

<div class="sectionHeader">
    <div>
        <h2 class="sectionTitle">Order #<?= (int)$order["id"] ?></h2>
        <p class="sectionSub">Placed <?= $order["created_at"] ? htmlspecialchars(date("M j, Y H:i", strtotime((string)$order["created_at"]))) : "" ?></p>
    </div>
    <div class="flex gap-sm flex-wrap">
        <a href="/ecommerce/public/invoice.php?id=<?= (int)$order["id"] ?>" class="btn btnPrimary">View Invoice</a>
        <a href="/ecommerce/public/orders.php" class="btn">Back to My Orders</a>
    </div>
</div>

<div class="grid grid-2-1 gap-lg">
    <div class="card table-card">
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th class="text-center">Quantity</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Item Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <?php $lineTotal = (float)$item["price"] * (int)$item["quantity"]; ?>
                    <tr>
                        <td class="table-product-name"><?= htmlspecialchars((string)$item["name"]) ?></td>
                        <td class="text-center">
                            <span class="badge badge-compact"><?= (int)$item["quantity"] ?></span>
                        </td>
                        <td class="text-right">€<?= number_format((float)$item["price"], 2) ?></td>
                        <td class="text-right table-money">€<?= number_format($lineTotal, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div>
        <div class="card">
            <div class="cardBody">
                <h3 class="cardTitle">Order Summary</h3>
                <div class="mb-md">
                    <div class="summary-label">Order ID</div>
                    <div class="summary-value">#<?= (int)$order["id"] ?></div>
                </div>
                <div class="mb-md">
                    <div class="summary-label">Order Date</div>
                    <div><?= $order["created_at"] ? htmlspecialchars(date("M j, Y H:i", strtotime((string)$order["created_at"]))) : "-" ?></div>
                </div>
                <hr class="divider">
                <div class="summary-total-bar">
                    <span class="text-muted">Total</span>
                    <span class="summary-total-value">€<?= number_format((float)$order["total"], 2) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include __DIR__ . "/../includes/footer.php";
?>
