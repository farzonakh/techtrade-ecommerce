<?php
declare(strict_types=1);



require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../includes/product_card.php";

$storeCountry = "Italy";

// Get the product categories used by the filter menu.
$categories = $pdo->query("SELECT DISTINCT category FROM products ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);
if (empty($categories)) {
    $categories = [];
}
array_unshift($categories, "All");

// Read the selected category from the URL and fall back to "All".
$selected = trim((string)($_GET["category"] ?? "All"));
if (!in_array($selected, $categories, true)) {
  $selected = "All";
}

// Get products for the selected category.
if ($selected === "All") {
  $stmt = $pdo->query("
    SELECT id, name, description, price, image_url, category, item_condition, stock
    FROM products
    ORDER BY id DESC
  ");
} else {
  $stmt = $pdo->prepare("
    SELECT id, name, description, price, image_url, category, item_condition, stock
    FROM products
    WHERE category = ?
    ORDER BY id DESC
  ");
  $stmt->execute([$selected]);
}
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);


$loggedIn = is_logged_in();

// Get wishlist product IDs so the heart button shows the correct state.
$wishIds = [];
if ($loggedIn) {
  $stmtW = $pdo->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
  $stmtW->execute([$_SESSION["user"]["id"]]);
  $wishIds = array_map(fn($r) => (int)$r["product_id"], $stmtW->fetchAll(PDO::FETCH_ASSOC));
}

include __DIR__ . "/../includes/header.php";
?>

<!-- Page hero -->
<section class="hero">
  <div class="heroBg"></div>
  <div class="heroOverlay"></div>

  <div class="heroContent">
    <h2 class="heroTitle">Next Gen Tech.</h2>
    <p class="heroSub">
      Upgrade your lifestyle with the latest phones, laptops, and gear.
    </p>
    <div>
      <a class="btn btnPrimary" href="#products"><?= t("shop") ?></a>
    </div>
  </div>
</section>

<!-- Feature highlight -->
<div class="features">
  <div class="feature text-center">
    <p class="featureText">Experience a new level of comfort and precision. Mechanical keyboards offer durability, speed, and a tactile feel designed for people who spend hours at their keyboard.</p>
  </div>
</div>

<!-- Product catalog -->
<div class="sectionHeader" id="products">
    <div>
        <h2 class="sectionTitle"><?= t("catalog") ?></h2>
        <p class="sectionSub"><?= t("filter_by") ?>: <b><?= htmlspecialchars($selected) ?></b></p>
    </div>
    
    <form class="filter" method="GET">
        <input type="hidden" name="lang" value="<?= htmlspecialchars($lang) ?>">
        <select name="category" aria-label="Category" class="btn">
          <?php foreach ($categories as $c): ?>
            <option value="<?= htmlspecialchars($c) ?>" <?= $selected === $c ? "selected" : "" ?>>
              <?= htmlspecialchars($c) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button class="btn btnPrimary" type="submit"><?= t("go") ?></button>
    </form>
</div>

<?php if (empty($products)): ?>
  <div class="card p-4 text-center">
    <p class="cardDesc"><?= t("no_products") ?></p>
    <a href="?category=All" class="btn"><?= t("view_all") ?></a>
  </div>
<?php else: ?>
  <div class="grid">
    <?php foreach ($products as $p): ?>
      <?php render_product_card($p, $loggedIn, $wishIds); ?>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<!-- About TechTrade -->
<div class="mt-lg"></div>
<div class="sectionHeader">
    <h2 class="sectionTitle">About TechTrade</h2>
</div>

<div class="card about-card">
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
    <ul class="list-disc list-indent list-muted list-spaced mb-md">
        <li>Guaranteed original products — 100% protection against counterfeit electronics.</li>
        <li>Competitive prices across <?= $storeCountry ?> for smartphones and gadgets.</li>
        <li>Installment payment options with convenient terms and no initial down payment (subject to approval).</li>
        <li>Nationwide delivery across <?= $storeCountry ?>, with flexible shipping options.</li>
        <li>Promotions and discounts available regularly on smartphones and electronics, including special offers for loyal customers.</li>
        <li>Warranty service through the TechTrade service center, with coverage of up to one year on all electronics.</li>
        <li>Trade-In service, allowing customers to exchange old smartphones (such as Apple or Samsung devices) for newer models.</li>
    </ul>

    <div class="mt-sm"></div>

    <h3 class="cardTitle">Customer Support</h3>
    <p class="cardDesc">Another advantage of TechTrade is our team of professional consultants. You can contact our support team by phone or online messaging services.</p>
    <p class="cardDesc">Our specialists will:</p>
    <ul class="list-disc list-indent list-muted">
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
