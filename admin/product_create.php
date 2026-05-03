<?php
declare(strict_types=1);

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_admin();

$error = "";
$name = "";
$description = "";
$price = "";
$image_url = "";
$category = "Accessories";
$item_condition = "new";
$stock = "10";
$featured = 0;

$categories = $pdo->query("SELECT DISTINCT category FROM products ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);
$conditions = $pdo->query("SELECT DISTINCT item_condition FROM products ORDER BY item_condition ASC")->fetchAll(PDO::FETCH_COLUMN);

// Defaults if empty
if (empty($categories)) $categories = ["Phones", "Headphones", "Laptops", "Accessories"];
if (empty($conditions)) $conditions = ["new", "used"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name = trim($_POST["name"] ?? "");
  $description = trim($_POST["description"] ?? "");
  $price = trim($_POST["price"] ?? "");
  $image_url = trim($_POST["image_url"] ?? "");
  $category = trim($_POST["category"] ?? "Accessories");
  $item_condition = trim($_POST["item_condition"] ?? "new");
  $stock = trim($_POST["stock"] ?? "10");
  $featured = isset($_POST["featured"]) ? 1 : 0;

  if ($name === "") {
    $error = "Oops 😅 Name is required";
  } elseif ($price === "") {
    $error = "Oops 😅 Price is required";
  } elseif (!is_numeric($price) || (float)$price <= 0) {
    $error = "Price must be valid 💶";
  } elseif ($category === "") {
    $error = "Oops 😅 Category is required";
  } elseif ($item_condition === "") {
    $error = "Oops 😅 Condition is required";
  } elseif (!ctype_digit($stock) || (int)$stock < 0) {
    $error = "Stock too low 📦";
  } else {
    $stmt = $pdo->prepare("
      INSERT INTO products (name, description, price, image_url, category, item_condition, stock, featured)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
      $name,
      $description,
      (float)$price,
      $image_url,
      $category,
      $item_condition,
      (int)$stock,
      (int)$featured
    ]);

    header("Location: /ecommerce/admin/products.php");
    exit;
  }
}

include __DIR__ . "/includes/header.php";
?>

<div class="action-bar">
    <div class="page-title">
        <h2>Add Product</h2>
        <p class="text-muted">Create a new item for your store</p>
    </div>
    <a href="/ecommerce/admin/products.php" class="btn">← Back to Products</a>
</div>

<?php if ($error): ?>
<div class="card alert alert-danger">
    <div class="cardBody">
        <p class="alert-title text-danger">⚠️ <?= htmlspecialchars($error) ?></p>
    </div>
</div>
<?php endif; ?>

<div class="form-box admin-form-wide">
  <form method="POST">
    <div class="grid grid-2-1 gap-lg">
        
        <!-- Left Column -->
        <div>
            <div class="form-group">
                <label class="form-label">Product Name</label>
                <input class="form-input" name="name" value="<?= htmlspecialchars($name) ?>" placeholder="e.g. iPhone 15 Pro">
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea class="form-input" name="description" rows="6" placeholder="Product details..."><?= htmlspecialchars($description) ?></textarea>
            </div>

            <div class="grid grid-2-cols">
                 <div class="form-group">
                    <label class="form-label">Price (€)</label>
                    <input class="form-input" name="price" value="<?= htmlspecialchars($price) ?>" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label class="form-label">Stock</label>
                    <input class="form-input" name="stock" value="<?= htmlspecialchars($stock) ?>" placeholder="Quantity">
                </div>
            </div>
            
            <div class="form-group">
                 <label class="form-label">Image URL</label>
                 <input class="form-input" name="image_url" value="<?= htmlspecialchars($image_url) ?>" placeholder="https://...">
            </div>
        </div>

        <!-- Right Column -->
        <div>
             <div class="form-group">
                <label class="form-label">Category</label>
                <input class="form-input" list="category_list" name="category" value="<?= htmlspecialchars($category) ?>">
                <datalist id="category_list">
                  <?php foreach ($categories as $c): ?>
                    <option value="<?= htmlspecialchars($c) ?>">
                  <?php endforeach; ?>
                </datalist>
            </div>

            <div class="form-group">
                <label class="form-label">Condition</label>
                <input class="form-input" list="condition_list" name="item_condition" value="<?= htmlspecialchars($item_condition) ?>">
                <datalist id="condition_list">
                  <?php foreach ($conditions as $cond): ?>
                    <option value="<?= htmlspecialchars($cond) ?>">
                  <?php endforeach; ?>
                </datalist>
            </div>

            <div class="form-group">
                <label class="form-label admin-checkbox-label">
                  <input type="checkbox" name="featured" value="1" <?= $featured ? "checked" : "" ?> class="admin-checkbox">
                  Featured ⭐
                </label>
                <p class="admin-help-text">Show this product at the top of the list.</p>
            </div>

            <div class="mt-lg">
                <button type="submit" class="btn btnPrimary w-100">Create Product</button>
            </div>
        </div>
        
    </div>
  </form>
</div>

<?php
include __DIR__ . "/includes/footer.php";
?>
