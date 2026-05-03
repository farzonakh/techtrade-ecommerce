<?php
declare(strict_types=1);

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";

require_login();

$cart = $_SESSION["cart"] ?? [];
$items = [];
$total = 0.0;
$stockWarnings = [];
$cartMessage = $_SESSION["cart_message"] ?? "";
$cartError = $_SESSION["cart_error"] ?? "";

// Show one-time cart messages, then remove them from the session.
if ($cartMessage !== "") {
    unset($_SESSION["cart_message"]);
}

if ($cartError !== "") {
    unset($_SESSION["cart_error"]);
}

if (!empty($cart)) {
  $ids = array_map("intval", array_keys($cart));
  $placeholders = implode(",", array_fill(0, count($ids), "?"));

  // Get current product details for all items in the cart.
  $stmt = $pdo->prepare("
    SELECT id, name, price, stock
    FROM products
    WHERE id IN ($placeholders)
  ");
  $stmt->execute($ids);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($rows as $p) {
    $pid = (int)$p["id"];
    $qty = (int)($cart[$pid] ?? 0);
    $currentStock = (int)($p["stock"] ?? 0);
    
    if ($qty <= 0) continue;

    $price = (float)$p["price"];
    $sum = $price * $qty;

    // Show a warning when stock changed after the item was added.
    if ($currentStock == 0) {
        $stockWarnings[] = [
            "name" => (string)$p["name"],
            "status" => "out_of_stock"
        ];
    } elseif ($currentStock < $qty) {
        $stockWarnings[] = [
            "name" => (string)$p["name"],
            "status" => "insufficient",
            "available" => $currentStock,
            "requested" => $qty
        ];
    }

    $items[] = [
      "id" => $pid,
      "name" => (string)$p["name"],
      "price" => $price,
      "qty" => $qty,
      "stock" => $currentStock,
      "sum" => $sum
    ];

    $total += $sum;
  }
}

include __DIR__ . "/../includes/header.php";
?>

<?php if ($cartMessage !== ""): ?>
    <div class="card alert alert-success">
        <div class="cardBody">
            <p class="alert-title">✓ <?= htmlspecialchars($cartMessage) ?></p>
        </div>
    </div>
<?php endif; ?>

<?php if ($cartError !== ""): ?>
    <div class="card alert alert-danger">
        <div class="cardBody">
            <p class="alert-title text-danger">⚠️ <?= htmlspecialchars($cartError) ?></p>
        </div>
    </div>
<?php endif; ?>

<div class="sectionHeader">
    <div>
        <h2 class="sectionTitle"><?= t("shopping_cart") ?> 🛒</h2>
        <p class="sectionSub"><?= t("review_items") ?></p>
    </div>
    <a href="/ecommerce/public/index.php" class="btn">← <?= t("continue_shopping") ?></a>
</div>

<?php if (!empty($stockWarnings)): ?>
  <div class="card alert alert-warning">
    <div class="cardBody">
      <p class="alert-title text-stock-warning">⚠️ <?= t("stock_issues") ?></p>
      <ul class="alert-list">
        <?php foreach ($stockWarnings as $w): ?>
          <li class="text-warning">
            <strong><?= htmlspecialchars($w["name"]) ?></strong>
            <?php if ($w["status"] === "out_of_stock"): ?>
              - <span class="text-danger"><?= t("out_of_stock") ?> 📦</span>
            <?php elseif ($w["status"] === "insufficient"): ?>
              - <?= t("stock_too_low") ?> 📦 <?= t("only_available") ?> <?= (int)$w["available"] ?> <?= t("available") ?> (<?= (int)$w["requested"] ?> <?= t("in_cart") ?>)
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
<?php endif; ?>

<?php if (empty($items)): ?>
  <div class="card p-4 text-center">
    <div class="empty-state">
        <p class="heroSub empty-state-text"><?= t("cart_empty") ?></p>
        <a href="/ecommerce/public/index.php" class="btn btnPrimary"><?= t("start_shopping") ?></a>
    </div>
  </div>
<?php else: ?>
  <div class="card table-card">
    <table class="table">
      <thead>
        <tr>
          <th><?= t("product") ?></th>
          <th class="text-center"><?= t("stock") ?></th>
          <th class="text-right"><?= t("price") ?></th>
          <th class="text-center"><?= t("quantity") ?></th>
          <th class="text-right"><?= t("sum") ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $it): ?>
          <tr>
            <td class="table-product-name"><?= htmlspecialchars($it["name"]) ?></td>
            <td class="text-center">
                <?php if ($it["stock"] == 0): ?>
                    <span class="text-danger font-semibold"><?= t("out_of_stock") ?></span>
                <?php elseif ($it["stock"] < $it["qty"]): ?>
                    <span class="text-stock-warning font-semibold">⚠️ <?= (int)$it["stock"] ?></span>
                <?php else: ?>
                    <span class="text-success font-semibold">✓ <?= (int)$it["stock"] ?></span>
                <?php endif; ?>
            </td>
            <td class="text-right">€<?= number_format($it["price"], 2) ?></td>
            <td class="text-center">
                <span class="badge badge-compact badge-soft"><?= (int)$it["qty"] ?></span>
            </td>
            <td class="text-right table-money">€<?= number_format($it["sum"], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="cart-total-bar">
    <div class="cart-total-box">
        <span class="cart-total-label"><?= t("total_amount") ?></span>
        <span class="cart-total-value">€<?= number_format($total, 2) ?></span>
    </div>
    
    <form action="/ecommerce/public/checkout.php" method="GET">
         <button type="submit" class="btn btnPrimary checkout-button"><?= t("proceed_to_checkout") ?></button>
    </form>
  </div>
  
  <p class="text-right mt-sm text-xs text-muted">
    * <?= t("demo_checkout_note") ?>
  </p>

<?php endif; ?>

<?php
include __DIR__ . "/../includes/footer.php";
?>
