<?php
declare(strict_types=1);

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";

require_login();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  header("Location: /ecommerce/public/index.php");
  exit;
}

$productId = (int)($_POST["product_id"] ?? 0);
if ($productId <= 0) {
  header("Location: /ecommerce/public/index.php");
  exit;
}

// Create the cart in the session if this is the first item.
if (!isset($_SESSION["cart"]) || !is_array($_SESSION["cart"])) {
  $_SESSION["cart"] = [];
}

// Get the latest stock before adding the product to the cart.
$stmt = $pdo->prepare("SELECT id, name, stock FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
  $_SESSION["cart_error"] = "Oops 😅 Product not found";
  header("Location: /ecommerce/public/index.php");
  exit;
}

$stock = (int)$product["stock"];
$productName = (string)$product["name"];
$inCartQty = (int)($_SESSION["cart"][$productId] ?? 0);

// Only add the item when there is enough stock available.
if ($stock > 0 && $inCartQty < $stock) {
  $_SESSION["cart"][$productId] = $inCartQty + 1;
  $_SESSION["cart_message"] = "{$productName} added to cart ✅";
} else if ($stock == 0) {
  $_SESSION["cart_error"] = "Oops 😅 {$productName} is out of stock 📦";
} else {
  $_SESSION["cart_error"] = "Stock too low 📦 Only {$stock} {$productName} available.";
}

header("Location: /ecommerce/public/cart.php");
exit;
