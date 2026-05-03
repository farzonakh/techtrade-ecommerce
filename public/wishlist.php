<?php
declare(strict_types=1);


require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../includes/product_card.php";

require_login();

$userId = (int)$_SESSION["user"]["id"];

// Get the products saved by the current user.
$stmt = $pdo->prepare("
    SELECT p.id, p.name, p.description, p.price, p.image_url, p.category, p.item_condition, p.stock
    FROM wishlist w
    JOIN products p ON w.product_id = p.id
    WHERE w.user_id = ?
    ORDER BY p.id DESC
");
$stmt->execute([$userId]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Every product on this page is already liked.
$wishIds = array_map(fn($product) => (int)$product["id"], $products);

include __DIR__ . "/../includes/header.php";
?>

<div class="sectionHeader">
    <div>
        <h2 class="sectionTitle"><?= t("my_wishlist") ?> ❤️</h2>
        <p class="sectionSub"><?= t("wishlist_subtitle") ?></p>
    </div>
    <a href="/ecommerce/public/index.php" class="btn">← <?= t("continue_shopping") ?></a>
</div>

<?php if (empty($products)): ?>
  <div class="card p-4 text-center">
    <div class="empty-state">
        <p class="heroSub empty-state-text"><?= t("wishlist_empty") ?></p>
        <a href="/ecommerce/public/index.php" class="btn btnPrimary"><?= t("explore_products") ?></a>
    </div>
  </div>
<?php else: ?>
  <div class="grid">
    <?php foreach ($products as $p): ?>
      <?php render_product_card($p, true, $wishIds); ?>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php
include __DIR__ . "/../includes/footer.php";
?>
