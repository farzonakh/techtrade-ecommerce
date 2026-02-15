<?php
declare(strict_types=1);

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_login();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // If accessed directly without submitting the checkout form
    header("Location: /ecommerce/public/cart.php");
    exit;
}

$cart = $_SESSION["cart"] ?? [];
if (!$cart) {
  header("Location: /ecommerce/public/cart.php");
  exit;
}

$pdo->beginTransaction();

$total = 0.0;
foreach ($cart as $pid => $qty) {
  $price = $pdo->query("SELECT price FROM products WHERE id=".(int)$pid)->fetchColumn();
  $total += $price * $qty;
}

$stmt = $pdo->prepare("INSERT INTO orders (user_id, total) VALUES (?,?)");
$stmt->execute([$_SESSION["user"]["id"], $total]);
$orderId = $pdo->lastInsertId();

$ins = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?,?,?,?)");
foreach ($cart as $pid => $qty) {
  $price = $pdo->query("SELECT price FROM products WHERE id=".(int)$pid)->fetchColumn();
  $ins->execute([$orderId, $pid, $qty, $price]);
}

$pdo->commit();
unset($_SESSION["cart"]);

header("Location: /ecommerce/public/index.php");
exit;
