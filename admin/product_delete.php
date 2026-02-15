<?php
declare(strict_types=1);

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_admin();

$id = (int)($_GET["id"] ?? 0);
if ($id > 0) {
  $stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
  $stmt->execute([$id]);
}
header("Location: /ecommerce/admin/products.php");
exit;
