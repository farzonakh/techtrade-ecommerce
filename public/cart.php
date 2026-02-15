<?php
declare(strict_types=1);

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";

require_login();

$cart = $_SESSION["cart"] ?? [];
$items = [];
$total = 0.0;

if (!empty($cart)) {
  $ids = array_map("intval", array_keys($cart));
  $placeholders = implode(",", array_fill(0, count($ids), "?"));

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
    if ($qty <= 0) continue;

    $price = (float)$p["price"];
    $sum = $price * $qty;

    $items[] = [
      "id" => $pid,
      "name" => (string)$p["name"],
      "price" => $price,
      "qty" => $qty,
      "sum" => $sum
    ];

    $total += $sum;
  }
}

include __DIR__ . "/../includes/header.php";
?>

<div class="sectionHeader">
    <div>
        <h2 class="sectionTitle">Shopping Cart 🛒</h2>
        <p class="sectionSub">Review your items</p>
    </div>
    <a href="/ecommerce/public/index.php" class="btn">← Continue Shopping</a>
</div>

<?php if (empty($items)): ?>
  <div class="card p-4 text-center">
    <div style="padding: 3rem 0;">
        <p class="heroSub" style="color:var(--text-muted); margin-bottom:1.5rem;">Your cart is empty.</p>
        <a href="/ecommerce/public/index.php" class="btn btnPrimary">Start Shopping</a>
    </div>
  </div>
<?php else: ?>
  <div class="card" style="padding: 0; overflow:hidden;">
    <table class="table">
      <thead>
        <tr>
          <th>Product</th>
          <th class="text-right">Price</th>
          <th class="text-center">Qty</th>
          <th class="text-right">Sum</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $it): ?>
          <tr>
            <td style="font-weight:600;"><?= htmlspecialchars($it["name"]) ?></td>
            <td class="text-right">€<?= number_format($it["price"], 2) ?></td>
            <td class="text-center">
                <span class="badge" style="margin:0; background:rgba(255,255,255,0.05);"><?= (int)$it["qty"] ?></span>
            </td>
            <td class="text-right" style="color:var(--accent); font-weight:600;">€<?= number_format($it["sum"], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div style="display:flex; justify-content:flex-end; align-items:center; margin-top:2rem; gap:2rem;">
    <div style="text-align:right;">
        <span style="color:var(--text-muted); display:block;">Total Amount</span>
        <span style="font-size:2rem; font-weight:800; color:var(--text-main);">€<?= number_format($total, 2) ?></span>
    </div>
    
    <form action="/ecommerce/public/checkout.php" method="GET">
         <button type="submit" class="btn btnPrimary" style="padding: 1rem 2rem; font-size:1.1rem;">Proceed to Checkout</button>
    </form>
  </div>
  
  <p class="text-right mt-sm" style="font-size:0.8rem; color:var(--text-muted);">
    * By clicking Checkout, you agree that this is a demo.
  </p>

<?php endif; ?>

<?php
include __DIR__ . "/../includes/footer.php";
?>
