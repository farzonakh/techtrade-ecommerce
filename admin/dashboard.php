<?php
declare(strict_types=1);

require_once __DIR__ . "/../config/auth.php";
require_admin();

require_once __DIR__ . "/../config/db.php";

// Get dashboard totals.
$revenue = $pdo->query("SELECT SUM(total) FROM orders")->fetchColumn() ?: 0;
$ordersCount = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$productsCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$customersCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();

// Get the latest orders for the dashboard list.
$recentOrders = $pdo->query("
    SELECT o.id, o.total, o.created_at, u.full_name 
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.id DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . "/includes/header.php";
?>

<div class="admin-page-heading">
    <h2>Overview</h2>
</div>

<!-- Dashboard totals -->
<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-title">Total Revenue</div>
        <div class="stat-value">€<?= number_format((float)$revenue, 2) ?></div>
    </div>

    <div class="stat-card">
        <div class="stat-title">Orders</div>
        <div class="stat-value"><?= (int)$ordersCount ?></div>
    </div>

    <div class="stat-card">
        <div class="stat-title">Products</div>
        <div class="stat-value"><?= (int)$productsCount ?></div>
    </div>

    <div class="stat-card">
        <div class="stat-title">Customers</div>
        <div class="stat-value"><?= (int)$customersCount ?></div>
    </div>
</div>

<!-- Recent activity and quick actions -->
<div class="grid grid-2-1 gap-lg">
    <div>
        <div class="admin-panel-header">
            <h3 class="admin-panel-title">Recent Orders</h3>
            <a href="/ecommerce/admin/orders.php" class="admin-panel-link">View All</a>
        </div>

        <?php if (!$recentOrders): ?>
            <div class="admin-panel-empty">
                No orders yet.
            </div>
        <?php else: ?>
            <div class="admin-list-panel">
                <?php foreach ($recentOrders as $ro): ?>
                    <div class="admin-list-row">
                        <div>
                            <div class="admin-list-title"><?= htmlspecialchars($ro['full_name'] ?? 'Guest') ?></div>
                            <div class="admin-list-subtitle">
                                Order #<?= $ro['id'] ?> • <?= $ro['created_at'] ? date('M j', strtotime($ro['created_at'])) : '' ?>
                            </div>
                        </div>
                        <div class="admin-total">€<?= number_format((float)$ro['total'], 2) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div>
        <h3 class="admin-panel-title mb-md">Quick Actions</h3>
        <div class="quick-actions">
            <a href="/ecommerce/admin/product_create.php" class="btn quick-action-btn">+ Add New Product</a>
            <a href="/ecommerce/admin/users.php" class="btn quick-action-btn">Manage Users</a>
            <a href="/ecommerce/public/index.php" class="btn quick-action-btn">View Storefront</a>
        </div>
    </div>

</div>

<?php
include __DIR__ . "/includes/footer.php";
?>
