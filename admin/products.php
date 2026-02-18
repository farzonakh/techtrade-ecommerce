<?php
declare(strict_types=1);

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_admin();

$products = $pdo->query("SELECT id, name, price, stock, category, image_url FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . "/includes/header.php";
?>

<div class="action-bar">
    <div class="page-title">
        <h2>Products</h2>
        <p class="text-muted">Manage your catalog items</p>
    </div>
    <a href="/ecommerce/admin/product_create.php" class="btn btnPrimary">+ Add Product</a>
</div>

<div class="admin-table-container">
  <?php if (count($products) === 0): ?>
    <div style="padding: 3rem; text-align:center;">
        <p class="text-muted">No products found.</p>
        <a href="/ecommerce/admin/product_create.php" class="btn btnPrimary mt-sm">Create One</a>
    </div>
  <?php else: ?>
    <table class="admin-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Name</th>
            <th>Category</th>
            <th class="text-right">Price</th>
            <th class="text-center">Stock</th>
            <th class="text-right">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $p): ?>
            <tr>
            <td style="color:var(--text-muted); font-family:monospace;">#<?= (int)$p["id"] ?></td>
            <td>
                <?php if (!empty($p["image_url"])): ?>
                    <img  style="height:600px; width: 600px" src="<?= htmlspecialchars($p["image_url"]) ?>" alt="" class="product-thumb">
                <?php else: ?>
                    <div class="product-thumb" style="display:flex;align-items:center;justify-content:center;color:white;opacity:0.5;font-size:0.8rem">No Img</div>
                <?php endif; ?>
            </td>
            <td style="font-weight:600;"><?= htmlspecialchars($p["name"]) ?></td>
            <td><span class="badge"><?= htmlspecialchars($p["category"]) ?></span></td>
            <td class="text-right">€<?= number_format((float)$p["price"], 2) ?></td>
            <td class="text-center">
                <span class="<?= $p['stock'] < 5 ? 'text-danger' : 'text-success' ?>">
                    <?= (int)$p["stock"] ?>
                </span>
            </td>
            <td class="text-right">
                <a href="/ecommerce/admin/product_edit.php?id=<?= (int)$p["id"] ?>" class="btn" style="padding: 0.25rem 0.75rem; font-size:0.8rem;">Edit</a>
                <a href="/ecommerce/admin/product_delete.php?id=<?= (int)$p["id"] ?>" 
                    class="btn btnDanger" 
                    style="padding: 0.25rem 0.75rem; font-size:0.8rem;"
                    onclick="return confirm('Delete <?= htmlspecialchars($p["name"]) ?>?')">Delete</a>
            </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
  <?php endif; ?>
</div>

<?php
include __DIR__ . "/includes/footer.php";
?>
