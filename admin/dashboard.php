<?php
declare(strict_types=1);//enabled strict typing to reduce unexpected type conversion bugs

require_once __DIR__ . "/../config/auth.php";
require_admin(); //ensures authorization before any admin page logic runs, preventing URL bypass

// DB Stats
require_once __DIR__ . "/../config/db.php";

$revenue = $pdo->query("SELECT SUM(total) FROM orders")->fetchColumn() ?: 0;
$ordersCount = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$productsCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$customersCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();

// Recent Orders
$recentOrders = $pdo->query("
    SELECT o.id, o.total, o.created_at, u.full_name 
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.id DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . "/includes/header.php";
?>

<div style="margin-bottom:var(--space-lg);">
    <h2 style="font-size:1.5rem; font-weight:700;">Overview</h2>
</div>

<!-- Minimal Stats Grid -->
<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:var(--space-md); margin-bottom:var(--space-xl);">
    
    <div style="background:var(--bg-card); padding:1.5rem; border-radius:var(--radius-md); border:1px solid rgba(255,255,255,0.05);">
        <div style="color:var(--text-muted); font-size:0.85rem; font-weight:500; margin-bottom:0.5rem;">Total Revenue</div>
        <div style="font-size:1.75rem; font-weight:700;">€<?= number_format((float)$revenue, 2) ?></div>
    </div>

    <div style="background:var(--bg-card); padding:1.5rem; border-radius:var(--radius-md); border:1px solid rgba(255,255,255,0.05);">
        <div style="color:var(--text-muted); font-size:0.85rem; font-weight:500; margin-bottom:0.5rem;">Orders</div>
        <div style="font-size:1.75rem; font-weight:700;"><?= (int)$ordersCount ?></div>
    </div>

    <div style="background:var(--bg-card); padding:1.5rem; border-radius:var(--radius-md); border:1px solid rgba(255,255,255,0.05);">
        <div style="color:var(--text-muted); font-size:0.85rem; font-weight:500; margin-bottom:0.5rem;">Products</div>
        <div style="font-size:1.75rem; font-weight:700;"><?= (int)$productsCount ?></div>
    </div>

    <div style="background:var(--bg-card); padding:1.5rem; border-radius:var(--radius-md); border:1px solid rgba(255,255,255,0.05);">
        <div style="color:var(--text-muted); font-size:0.85rem; font-weight:500; margin-bottom:0.5rem;">Customers</div>
        <div style="font-size:1.75rem; font-weight:700;"><?= (int)$customersCount ?></div>
    </div>

</div>

<!-- Recent Activity & Quick Actions -->
<div class="grid" style="grid-template-columns: 2fr 1fr; gap:var(--space-lg);">
    
    <!-- Recent Orders List -->
    <div>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-md);">
            <h3 style="font-size:1.1rem; font-weight:600;">Recent Orders</h3>
            <a href="/ecommerce/admin/orders.php" style="font-size:0.85rem; color:var(--primary);">View All</a>
        </div>

        <?php if (!$recentOrders): ?>
            <div style="background:var(--bg-card); padding:2rem; text-align:center; border-radius:var(--radius-md); border:1px solid rgba(255,255,255,0.05); color:var(--text-muted);">
                No orders yet.
            </div>
        <?php else: ?>
            <div style="background:var(--bg-card); border-radius:var(--radius-md); border:1px solid rgba(255,255,255,0.05); overflow:hidden;">
                <?php foreach ($recentOrders as $ro): ?>
                    <div style="padding:1rem; border-bottom:1px solid rgba(255,255,255,0.05); display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <div style="font-weight:500;"><?= htmlspecialchars($ro['full_name'] ?? 'Guest') ?></div>
                            <div style="font-size:0.8rem; color:var(--text-muted);">
                                Order #<?= $ro['id'] ?> • <?= $ro['created_at'] ? date('M j', strtotime($ro['created_at'])) : '' ?>
                            </div>
                        </div>
                        <div style="font-weight:600;">€<?= number_format((float)$ro['total'], 2) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Quick Actions -->
    <div>
        <h3 style="font-size:1.1rem; font-weight:600; margin-bottom:var(--space-md);">Quick Actions</h3>
        <div style="display:flex; flex-direction:column; gap:var(--space-sm);">
            <a href="/ecommerce/admin/product_create.php" class="btn" style="width:100%; justify-content:flex-start;">+ Add New Product</a>
            <a href="/ecommerce/admin/users.php" class="btn" style="width:100%; justify-content:flex-start;">Manage Users</a>
            <a href="/ecommerce/public/index.php" class="btn" style="width:100%; justify-content:flex-start;">View Storefront</a>
        </div>
    </div>

</div>

<?php
include __DIR__ . "/includes/footer.php";
?>
