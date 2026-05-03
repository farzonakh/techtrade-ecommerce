<?php
require_once __DIR__ . "/../config/lang.php";

if (session_status() === PHP_SESSION_NONE) {
    // Start the session so the menu can show login, cart, and user links.
    session_start();
}

// Mark the current navigation link as active.
function isActive(string $path): string {
    return strpos($_SERVER['REQUEST_URI'], $path) !== false ? 'active' : '';
}

$user = $_SESSION['user'] ?? null;
$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
$styleVersion = filemtime(__DIR__ . "/../public/assets/style.css");
$layoutVersion = filemtime(__DIR__ . "/../public/assets/layout.css");
$componentsVersion = filemtime(__DIR__ . "/../public/assets/components.css");
?>
<!doctype html>
<html lang="<?= htmlspecialchars($lang ?? "en") ?>">
<head>
    <meta charset="utf-8">
    <title>TechTrade | Premium Tech Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Preconnect for fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/style.css?v=<?= $styleVersion ?>">
    <link rel="stylesheet" href="assets/layout.css?v=<?= $layoutVersion ?>">
    <link rel="stylesheet" href="assets/components.css?v=<?= $componentsVersion ?>">
</head>
<body>

<!-- TOP BAR -->
<header class="topbar">
    <a href="/ecommerce/public/index.php" class="brand">
        <h1>TechTrade</h1>
    </a>

    <nav class="nav">
        <a href="/ecommerce/public/index.php" class="<?= isActive('index.php') ?>"><?= t("shop") ?></a>
        
        <?php if ($user): ?>
            <span class="badge"><?= t("hi") ?>, <?= htmlspecialchars($user["full_name"]) ?></span>
            
            <a href="/ecommerce/public/cart.php" class="btn <?= isActive('cart.php') ?>">
                <?= t("cart") ?> (<?= $cartCount ?>)
            </a>

            <a href="/ecommerce/public/orders.php" class="btn <?= isActive('orders.php') || isActive('order_details.php') ?>">
                <?= t("my_orders") ?>
            </a>
            
            <a href="/ecommerce/public/wishlist.php" class="btn <?= isActive('wishlist.php') ?>">
                <?= t("wishlist") ?>
            </a>
            
            <?php if (($user["role"] ?? "user") === "admin"): ?>
                <a href="/ecommerce/admin/products.php"><?= t("admin") ?></a>
            <?php endif; ?>
            
            <a href="/ecommerce/public/logout.php" class="text-danger"><?= t("logout") ?></a>
        <?php else: ?>
            <a href="/ecommerce/public/login.php" class="btn <?= isActive('login.php') ?>"><?= t("login") ?></a>
            <a href="/ecommerce/public/register.php" class="btn btnPrimary <?= isActive('register.php') ?>"><?= t("register") ?></a>
        <?php endif; ?>

        <a href="<?= htmlspecialchars(language_url("en")) ?>" title="English">🇬🇧</a>
        <a href="<?= htmlspecialchars(language_url("it")) ?>" title="Italiano">🇮🇹</a>
    </nav>
</header>

<main class="container main-content">
