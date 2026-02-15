<?php
declare(strict_types=1);


require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";

require_login();

$user_id = (int)$_SESSION["user"]["id"];

// Fetch wishlist items
$stmt = $pdo->prepare("
    SELECT p.id, p.name, p.description, p.price, p.image_url, p.category, p.stock
    FROM wishlist w
    JOIN products p ON w.product_id = p.id
    WHERE w.user_id = ?
    ORDER BY p.id DESC
");
$stmt->execute([$user_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get IDs for render_product_card (all these are in wishlist by definition, but for compatibility)
$wishIds = array_map(fn($p) => (int)$p['id'], $products);

/* ---------- HELPERS (Duplicated from index.php for now, ideally should be in a common file) ---------- */
function short_text(string $text, int $limit = 140): string {
  $text = trim($text);
  if ($text === "") return "";
  return (mb_strlen($text) > $limit) ? (mb_substr($text, 0, $limit) . "…") : $text;
}

function product_image(?string $url): string {
  $url = trim((string)$url);
  return ($url !== "") ? $url : "/ecommerce/public/assets/placeholder.png";
}

function render_product_card(array $p, bool $loggedIn, array $wishIds): void {
  $id = (int)$p["id"];
  $liked = true; // Always true on wishlist page
  $name = (string)($p["name"] ?? "");
  $desc = short_text((string)($p["description"] ?? ""), 100);
  $price = (float)($p["price"] ?? 0);
  $category = (string)($p["category"] ?? "Other");
  $stock = (int)($p["stock"] ?? 0);
  $img = product_image($p["image_url"] ?? null);
  
  $item_condition = (string)($p["item_condition"] ?? "new");
  $conditionBadge = ucfirst($item_condition);
  
  ?>
  <div class="card">
    <div class="cardThumb">
      <img
        src="<?= htmlspecialchars($img) ?>"
        alt="<?= htmlspecialchars($name) ?>"
        class="lazy"
        loading="lazy"
        onerror="this.onerror=null;this.src='/ecommerce/public/assets/placeholder.png';"
      >
    </div>

    <div class="cardBody">
        <div class="mb-2">
            <span class="badge"><?= htmlspecialchars($category) ?></span>
            <span class="badge" style="color:var(--text-main); font-size:0.7rem; opacity:0.8"><?= htmlspecialchars($conditionBadge) ?></span>
        </div>

        <h3 class="cardTitle"><?= htmlspecialchars($name) ?></h3>
        <div class="cardPrice">€<?= number_format($price, 2) ?></div>
        
        <p class="cardDesc"><?= htmlspecialchars($desc) ?></p>

        <div class="cardActions">
            <!-- Remove from Wishlist -->
            <form method="POST" action="/ecommerce/public/wishlist_toggle.php" style="display:inline;">
                <input type="hidden" name="product_id" value="<?= $id ?>">
                <button class="btn btnDanger" type="submit" title="Remove from wishlist">
                ♥
                </button>
            </form>

            <!-- Add to Cart -->
            <?php if ($stock > 0): ?>
                <form method="POST" action="/ecommerce/public/cart_add.php" style="flex:1;">
                <input type="hidden" name="product_id" value="<?= $id ?>">
                <button class="btn btnPrimary" style="width:100%" type="submit">Add to Cart</button>
                </form>
            <?php else: ?>
                <button class="btn" disabled style="flex:1; cursor:not-allowed; opacity:0.5;">Out of Stock</button>
            <?php endif; ?>
        </div>
    </div>
  </div>
  <?php
}

include __DIR__ . "/../includes/header.php";
?>

<div class="sectionHeader">
    <div>
        <h2 class="sectionTitle">My Wishlist ❤️</h2>
        <p class="sectionSub">Your curated collection</p>
    </div>
    <a href="/ecommerce/public/index.php" class="btn">← Continue Shopping</a>
</div>

<?php if (empty($products)): ?>
  <div class="card p-4 text-center">
    <div style="padding: 3rem 0;">
        <p class="heroSub" style="color:var(--text-muted); margin-bottom:1.5rem;">Your wishlist is empty.</p>
        <a href="/ecommerce/public/index.php" class="btn btnPrimary">Explore Products</a>
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
