<?php
declare(strict_types=1);

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_login();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: /ecommerce/public/cart.php");
    exit;
}

$cart = $_SESSION["cart"] ?? [];
if (!$cart) {
    header("Location: /ecommerce/public/cart.php");
    exit;
}

// Clean the cart before using it to create an order.
$sanitizedCart = [];
foreach ($cart as $pid => $qty) {
    $pid = (int)$pid;
    $qty = (int)$qty;
    if ($pid > 0 && $qty > 0) {
        $sanitizedCart[$pid] = $qty;
    }
}

if (empty($sanitizedCart)) {
    header("Location: /ecommerce/public/cart.php");
    exit;
}

$validPromoCodes = [
    "SAVE10" => 0.10,
    "SAVE20" => 0.20,
];
$promoCode = strtoupper(trim($_POST["promo_code"] ?? ""));
$discountRate = $validPromoCodes[$promoCode] ?? 0.0;

try {
    $pdo->beginTransaction();

    // Lock each product row while stock is checked and updated.
    $stockCheckStmt = $pdo->prepare(
        "SELECT id, name, price, stock FROM products WHERE id = ? FOR UPDATE"
    );
    $products = [];
    $insufficientStock = [];
    $total = 0.0;

    foreach ($sanitizedCart as $pid => $qty) {
        $stockCheckStmt->execute([$pid]);
        $product = $stockCheckStmt->fetch();

        if (!$product) {
            throw new Exception("Product ID {$pid} not found.");
        }

        $availableStock = (int)$product["stock"];
        if ($availableStock < $qty) {
            $insufficientStock[] = [
                "id" => $pid,
                "name" => $product["name"],
                "requested" => $qty,
                "available" => $availableStock,
            ];
        }

        $products[$pid] = $product;
        $total += (float)$product["price"] * $qty;
    }

    if ($insufficientStock) {
        $pdo->rollBack();

        $error = "Stock too low 📦\n\n";
        foreach ($insufficientStock as $item) {
            $error .= "- {$item['name']}: Only {$item['available']} available (you requested {$item['requested']})\n";
        }

        $_SESSION["checkout_error"] = $error;
        header("Location: /ecommerce/public/checkout.php");
        exit;
    }

    $discountAmount = $discountRate > 0 ? $total * $discountRate : 0.0;
    $finalTotal = $total - $discountAmount;

    // Save the order, then save each product inside the order.
    $orderStmt = $pdo->prepare("INSERT INTO orders (user_id, total, created_at) VALUES (?, ?, NOW())");
    $orderStmt->execute([$_SESSION["user"]["id"], $finalTotal]);
    $orderId = (int)$pdo->lastInsertId();

    $insertItemStmt = $pdo->prepare(
        "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)"
    );
    $updateStockStmt = $pdo->prepare(
        "UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?"
    );

    foreach ($sanitizedCart as $pid => $qty) {
        $price = (float)$products[$pid]["price"];

        $insertItemStmt->execute([$orderId, $pid, $qty, $price]);

        $updateStockStmt->execute([$qty, $pid, $qty]);
        if ($updateStockStmt->rowCount() === 0) {
            throw new Exception("Stock too low 📦 for product ID {$pid}.");
        }
    }

    $pdo->commit();

    unset($_SESSION["cart"]);
    unset($_SESSION["checkout_error"]);

    header("Location: /ecommerce/public/order_success.php?id=" . $orderId);
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Order placement error: " . $e->getMessage());

    $_SESSION["checkout_error"] = "Order failed: " . $e->getMessage();
    header("Location: /ecommerce/public/checkout.php");
    exit;
}
