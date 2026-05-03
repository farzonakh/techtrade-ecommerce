<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure admin access if not already checked
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? 'user') !== 'admin') {
    header('Location: /ecommerce/public/login.php');
    exit;
}

$user = $_SESSION['user'];
$styleVersion = filemtime(__DIR__ . "/../../public/assets/style.css");
$layoutVersion = filemtime(__DIR__ . "/../../public/assets/layout.css");
$componentsVersion = filemtime(__DIR__ . "/../../public/assets/components.css");
$adminStyleVersion = filemtime(__DIR__ . "/../../public/assets/admin.css");

function isAdminActive($path) {
    return strpos($_SERVER['REQUEST_URI'], $path) !== false ? 'active' : '';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Dashboard | TechTrade</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="/ecommerce/public/assets/style.css?v=<?= $styleVersion ?>">
    <link rel="stylesheet" href="/ecommerce/public/assets/layout.css?v=<?= $layoutVersion ?>">
    <link rel="stylesheet" href="/ecommerce/public/assets/components.css?v=<?= $componentsVersion ?>">
    <link rel="stylesheet" href="/ecommerce/public/assets/admin.css?v=<?= $adminStyleVersion ?>">
</head>
<body class="admin-body">

    <!-- Top Navigation -->
    <header class="admin-header">
        <div class="admin-nav-container">
            <div class="admin-brand-row">
                <a href="/ecommerce/admin/dashboard.php" class="admin-brand">
                    <h1>TechTrade Admin</h1>
                </a>
                
                <nav class="admin-nav">
                    <a href="/ecommerce/admin/products.php" class="admin-nav-item <?= isAdminActive('products.php') ?>">
                        Products
                    </a>
                    <a href="/ecommerce/admin/users.php" class="admin-nav-item <?= isAdminActive('users.php') ?>">
                        Users
                    </a>
                    <a href="/ecommerce/admin/orders.php" class="admin-nav-item <?= (isAdminActive('orders.php') || isAdminActive('order_details.php')) ? 'active' : '' ?>">
                        Orders
                    </a>
                </nav>
            </div>

            <div class="admin-user-menu">
                <a href="/ecommerce/public/index.php" class="admin-nav-item">
                    View Shop
                </a>
                <a href="/ecommerce/public/logout.php" class="admin-nav-item text-danger">
                    Logout
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="admin-main">
