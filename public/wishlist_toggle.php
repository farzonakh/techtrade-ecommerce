<?php
declare(strict_types=1);

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";

require_login();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $product_id = (int)($_POST["product_id"] ?? 0);
  $user_id = (int)($_SESSION["user"]["id"] ?? 0);

  if ($product_id > 0 && $user_id > 0) {
    // Check if exists
    $stmt = $pdo->prepare("SELECT user_id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $exists = $stmt->fetch();

    if ($exists) {
        $del = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $del->execute([$user_id, $product_id]);
    } else {
        $ins = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $ins->execute([$user_id, $product_id]);
    }
  }
}

// Redirect back to where they came from, or index
$redirect = $_SERVER["HTTP_REFERER"] ?? "/ecommerce/public/index.php";
header("Location: " . $redirect);
exit;
