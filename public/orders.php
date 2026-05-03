<?php
declare(strict_types=1);

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";

require_login();

$user = current_user();
$userId = (int)($user["id"] ?? 0);

// Get all orders for the current user.
$stmt = $pdo->prepare("SELECT id, total, created_at FROM orders WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Reuse this query for each order card.
$itemStmt = $pdo->prepare("
    SELECT oi.quantity, oi.price, p.name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
    ORDER BY oi.id ASC
");

include __DIR__ . "/../includes/header.php";
?>

<div class="sectionHeader">
    <div>
        <h2 class="sectionTitle"><?= t("my_orders") ?></h2>
        <p class="sectionSub"><?= t("order_history") ?></p>
    </div>
    <a href="/ecommerce/public/index.php" class="btn"><?= t("continue_shopping") ?></a>
</div>

<?php if (!$orders): ?>
    <div class="card p-4 text-center">
        <div class="empty-state">
            <p class="heroSub empty-state-text"><?= t("no_orders") ?></p>
            <a href="/ecommerce/public/index.php" class="btn btnPrimary"><?= t("start_shopping") ?></a>
        </div>
    </div>
<?php else: ?>
    <div class="flex flex-column gap-lg">
        <?php foreach ($orders as $order): ?>
            <?php
            $orderId = (int)$order["id"];
            $itemStmt->execute([$orderId]);
            $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <section class="card table-card">
                <div class="cardBody flex justify-between gap-md align-start flex-wrap">
                    <div>
                        <h3 class="cardTitle mb-sm"><?= t("order") ?> #<?= $orderId ?></h3>
                        <div class="flex gap-sm flex-wrap">
                            <span class="badge"><?= t("date") ?>: <?= $order["created_at"] ? htmlspecialchars(date("M j, Y H:i", strtotime((string)$order["created_at"]))) : "-" ?></span>
                            <span class="badge"><?= t("total") ?>: €<?= number_format((float)$order["total"], 2) ?></span>
                        </div>
                    </div>
                    <div class="flex gap-sm flex-wrap">
                        <a href="/ecommerce/public/invoice.php?id=<?= $orderId ?>" class="btn"><?= t("invoice") ?></a>
                        <a href="/ecommerce/public/order_details.php?id=<?= $orderId ?>" class="btn"><?= t("view_details") ?></a>
                    </div>
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th><?= t("product") ?></th>
                            <th class="text-center"><?= t("quantity") ?></th>
                            <th class="text-right"><?= t("price") ?></th>
                            <th class="text-right"><?= t("item_total") ?></th>
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
            </section>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
include __DIR__ . "/../includes/footer.php";
?>
