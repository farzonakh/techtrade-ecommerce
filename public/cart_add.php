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

// Ensure cart exists
if (!isset($_SESSION["cart"]) || !is_array($_SESSION["cart"])) {
  $_SESSION["cart"] = [];
}

// OPTIONAL (but great): Check stock from DB so you can't add more than stock
$stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
$stmt->execute([$productId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
  header("Location: /ecommerce/public/index.php");
  exit;
}

$stock = (int)$row["stock"];
$inCartQty = (int)($_SESSION["cart"][$productId] ?? 0);

if ($stock > 0 && $inCartQty < $stock) {
  $_SESSION["cart"][$productId] = $inCartQty + 1;
}

header("Location: /ecommerce/public/cart.php");
exit;
