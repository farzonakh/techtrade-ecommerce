<?php
declare(strict_types=1);

/**
 * TechTrade - Homepage (Index)
 * 
 * This file handles the display of the main product catalog, feature highlights,
 * and the "About Us" section. It supports category filtering and wishlist interactions.
 */

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";

/* 
 * ============================================================
 * 1. CATEGORY FILTERING LOGIC
 * ============================================================
 * Fetch all unique categories from the database to populate the filter dropdown.
 */
$categories = $pdo->query("SELECT DISTINCT category FROM products ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);
if (empty($categories)) {
    $categories = [];
}
// Prepend "All" option for the default view
array_unshift($categories, "All");

// Determine the currently selected category from the URL query parameter
$selected = trim((string)($_GET["category"] ?? "All"));
if (!in_array($selected, $categories, true)) {
  $selected = "All"; // Fallback if invalid category
}

/* 
 * ============================================================
 * 2. PRODUCT FETCHING LOGIC
 * ============================================================
 * Fetch products based on the selected category.
 */
if ($selected === "All") {
  // Fetch all products
  $stmt = $pdo->query("
    SELECT id, name, description, price, image_url, category, item_condition, stock
    FROM products
    ORDER BY id DESC
  ");
} else {
  // Fetch products for a specific category
  $stmt = $pdo->prepare("
    SELECT id, name, description, price, image_url, category, item_condition, stock
    FROM products
    WHERE category = ?
    ORDER BY id DESC
  ");
  $stmt->execute([$selected]);
}
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* 
 * ============================================================
 * 3. USER STATE & WISHLIST
 * ============================================================
 * Check if user is logged in and fetch their wishlist for heart icon status.
 */
$loggedIn = is_logged_in();

// Wishlist IDs for the current user
$wishIds = [];
if ($loggedIn) {
  $stmtW = $pdo->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
  $stmtW->execute([$_SESSION["user"]["id"]]);
  $wishIds = array_map(fn($r) => (int)$r["product_id"], $stmtW->fetchAll(PDO::FETCH_ASSOC));
}

/* ---------- HELPERS ---------- */
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
  $liked = $loggedIn && in_array($id, $wishIds, true);
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
            <?php if ($loggedIn): ?>
                <!-- Wishlist -->
                <form method="POST" action="/ecommerce/public/wishlist_toggle.php" style="display:inline;">
                  <input type="hidden" name="product_id" value="<?= $id ?>">
                  <button class="btn <?= $liked ? 'btnDanger' : '' ?>" type="submit" title="<?= $liked ? 'Remove from wishlist' : 'Add to wishlist' ?>">
                    <?= $liked ? "♥" : "♡" ?>
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
            <?php else: ?>
                <a href="/ecommerce/public/login.php" class="btn" style="width:100%">Login to Buy</a>
            <?php endif; ?>
        </div>
    </div>
  </div>
  <?php
}

include __DIR__ . "/../includes/header.php";
?>

<!-- HERO -->
<section class="hero">
  <div class="heroBg" style="background-image:url('/ecommerce/public/assets/hero.jpg');"></div>
  <div class="heroOverlay"></div>

  <div class="heroContent">
    <h2 class="heroTitle">Next Gen Tech.</h2>
    <p class="heroSub">
      Upgrade your lifestyle with the latest phones, laptops, and gear.
    </p>
    <div>
      <a class="btn btnPrimary" href="#products">Shop Now</a>
    </div>
  </div>
</section>

<!-- FEATURES -->
<div class="features">
  <div class="feature text-center">
    <p class="featureTitle"></p>
    <p class="featureText">Experience a new level of comfort and precision. Mechanical keyboards offer durability, speed, and a tactile feel designed for people who spend hours at their keyboard.</p>
  </div>
</div>



<!-- ALL PRODUCTS SECTION -->
<div class="sectionHeader" id="products">
    <div>
        <h2 class="sectionTitle">Catalog</h2>
        <p class="sectionSub">Filter by: <b><?= htmlspecialchars($selected) ?></b></p>
    </div>
    
    <form class="filter" method="GET" style="display:flex; gap:0.5rem;">
        <select name="category" aria-label="Category" class="btn">
          <?php foreach ($categories as $c): ?>
            <option value="<?= htmlspecialchars($c) ?>" <?= $selected === $c ? "selected" : "" ?>>
              <?= htmlspecialchars($c) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button class="btn btnPrimary" type="submit">Go</button>
    </form>
</div>

<?php if (empty($products)): ?>
  <div class="card p-4 text-center">
    <p class="cardDesc">No products found in this category.</p>
    <a href="?category=All" class="btn">View All</a>
  </div>
<?php else: ?>
  <div class="grid">
    <?php foreach ($products as $p): ?>
      <?php render_product_card($p, $loggedIn, $wishIds); ?>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<!-- ABOUT SECTION -->
<?php $storeCountry = "Italy"; // Change country here ?>
<div class="mt-lg"></div>
<div class="sectionHeader">
    <h2 class="sectionTitle">About TechTrade</h2>
</div>

<div class="card p-4" style="padding: var(--space-lg);">
    <h3 class="cardTitle">Welcome to TechTrade</h3>
    <p class="cardDesc">The online store for smartphones, tablets, and other gadgets from the world’s leading manufacturers in <?= $storeCountry ?>.</p>
    <p class="cardDesc">Discover the opportunities our online phone store offers customers across <?= $storeCountry ?>. Here you can also find answers to the most frequently asked questions from TechTrade customers and learn more about our services.</p>

    <div class="mt-sm"></div>
    
    <h3 class="cardTitle">What We Offer: Online Store Selection</h3>
    <p class="cardDesc">With many years of experience in supplying and selling modern gadgets, our company has built strong partnerships with leading global manufacturers. One of the main advantages of buying smartphones from TechTrade is our wide and constantly expanding selection.</p>
    <p class="cardDesc">The latest innovations in mobile devices and accessories appear in our catalog as soon as they enter the global market.</p>

    <div class="mt-sm"></div>

    <h3 class="cardTitle">Why Customers Choose TechTrade</h3>
    <p class="cardDesc">A diverse assortment of smartphones, tablets, laptops, and other digital electronics from top brands is just one reason customers choose us. When shopping at TechTrade, customers enjoy the following benefits:</p>
    <ul style="list-style: disc; margin-left: 1.5rem; color: var(--text-muted); margin-bottom: var(--space-md);">
        <li style="margin-bottom: 0.5rem;">Guaranteed original products — 100% protection against counterfeit electronics.</li>
        <li style="margin-bottom: 0.5rem;">Competitive prices across <?= $storeCountry ?> for smartphones and gadgets.</li>
        <li style="margin-bottom: 0.5rem;">Installment payment options with convenient terms and no initial down payment (subject to approval).</li>
        <li style="margin-bottom: 0.5rem;">Nationwide delivery across <?= $storeCountry ?>, with flexible shipping options.</li>
        <li style="margin-bottom: 0.5rem;">Promotions and discounts available regularly on smartphones and electronics, including special offers for loyal customers.</li>
        <li style="margin-bottom: 0.5rem;">Warranty service through the TechTrade service center, with coverage of up to one year on all electronics.</li>
        <li style="margin-bottom: 0.5rem;">Trade-In service, allowing customers to exchange old smartphones (such as Apple or Samsung devices) for newer models.</li>
    </ul>

    <div class="mt-sm"></div>

    <h3 class="cardTitle">Customer Support</h3>
    <p class="cardDesc">Another advantage of TechTrade is our team of professional consultants. You can contact our support team by phone or online messaging services.</p>
    <p class="cardDesc">Our specialists will:</p>
    <ul style="list-style: disc; margin-left: 1.5rem; color: var(--text-muted);">
        <li>Answer your questions</li>
        <li>Help you choose the best smartphone, tablet, or gadget</li>
        <li>Explain technical specifications of products from brands like Apple, Samsung, and Xiaomi</li>
        <li>Assist you in placing your order</li>
    </ul>
</div>

<div class="mt-lg"></div>

<?php
include __DIR__ . "/../includes/footer.php";
?>
