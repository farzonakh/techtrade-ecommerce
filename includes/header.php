<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); //Without session → navigation logic fails 
}
// Helper to get active class
function isActive($path) {
    return strpos($_SERVER['REQUEST_URI'], $path) !== false ? 'active' : '';
}

$user = $_SESSION['user'] ?? null;
$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>TechTrade | Premium Tech Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Preconnect for fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="/ecommerce/public/assets/style.css">
</head>
<body>

<!-- TOP BAR -->
<header class="topbar">
    <a href="/ecommerce/public/index.php" class="brand">
        <h1>TechTrade</h1>
    </a>

    <nav class="nav">
        <a href="/ecommerce/public/index.php" class="<?= isActive('index.php') ?>">Shop</a>
        
        <?php if ($user): ?>
            <span class="badge">Hi, <?= htmlspecialchars($user["full_name"]) ?></span>
            
            <a href="/ecommerce/public/cart.php" class="btn <?= isActive('cart.php') ?>">
                Cart (<?= $cartCount ?>)
            </a>
            
            <a href="/ecommerce/public/wishlist.php" class="btn <?= isActive('wishlist.php') ?>">
                Wishlist
            </a>
            
            <?php if (($user["role"] ?? "user") === "admin"): ?>
                <a href="/ecommerce/admin/products.php">Admin</a>
            <?php endif; ?>
            
            <a href="/ecommerce/public/logout.php" class="text-danger">Logout</a>
        <?php else: ?>
            <a href="/ecommerce/public/login.php" class="btn <?= isActive('login.php') ?>">Login</a>
            <a href="/ecommerce/public/register.php" class="btn btnPrimary <?= isActive('register.php') ?>">Register</a>
        <?php endif; ?>
    </nav>
</header>

<main class="container main-content">
