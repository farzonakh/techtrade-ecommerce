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

// Get the order and customer name for the invoice.
$stmt = $pdo->prepare("
    SELECT o.id, o.total, o.created_at, u.full_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    http_response_code(404);
    die("Invoice not found");
}

// Get all purchased products shown on the invoice.
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
        <h2 class="sectionTitle"><?= t("invoice") ?> #<?= (int)$order["id"] ?></h2>
        <p class="sectionSub"><?= t("thank_purchase") ?> 🧾</p>
    </div>
    <button class="btn btnPrimary" onclick="window.print()"><?= t("print_invoice") ?></button>
</div>

<div class="grid grid-2-1 gap-lg">
    <div class="card table-card">
        <table class="table">
            <thead>
                <tr>
                    <th><?= t("product") ?></th>
                    <th class="text-center"><?= t("quantity") ?></th>
                    <th class="text-right"><?= t("price") ?></th>
                    <th class="text-right"><?= t("total") ?></th>
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
                <h3 class="cardTitle"><?= t("invoice_details") ?></h3>
                <div class="mb-md">
                    <div class="summary-label"><?= t("customer") ?></div>
                    <div class="summary-value"><?= htmlspecialchars((string)$order["full_name"]) ?></div>
                </div>
                <div class="mb-md">
                    <div class="summary-label"><?= t("order_id") ?></div>
                    <div>#<?= (int)$order["id"] ?></div>
                </div>
                <div class="mb-md">
                    <div class="summary-label"><?= t("date") ?></div>
                    <div><?= $order["created_at"] ? htmlspecialchars(date("M j, Y H:i", strtotime((string)$order["created_at"]))) : "-" ?></div>
                </div>
                <hr class="divider">
                <div class="summary-total-bar">
                    <span class="text-muted"><?= t("total_amount") ?></span>
                    <span class="summary-total-value">€<?= number_format((float)$order["total"], 2) ?></span>
                </div>
                <div class="delivery-note">
                    <?= t("delivery") ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include __DIR__ . "/../includes/footer.php";
?>
