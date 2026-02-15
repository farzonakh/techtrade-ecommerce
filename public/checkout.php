<?php
declare(strict_types=1);

/**
 * TechTrade - Checkout Page
 * 
 * This file handles the checkout process. It verifies the user is logged in,
 * checks if the cart is not empty, calculates totals, and displays the order summary
 * and payment form.
 */

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";

// Ensure user is logged in before accessing checkout
require_login();

/* 
 * ============================================================
 * 1. CART VALIDATION
 * ============================================================
 * If cart is empty, redirect back to cart page.
 */
$cart = $_SESSION["cart"] ?? [];
if (empty($cart)) {
    header("Location: /ecommerce/public/cart.php");
    exit;
}

$items = [];
$total = 0.0;

/* 
 * ============================================================
 * 2. ORDER CALCULATION
 * ============================================================
 * Fetch product details for items in the cart and calculate totals.
 */
$ids = array_map("intval", array_keys($cart));
$placeholders = implode(",", array_fill(0, count($ids), "?"));

// Fetch only necessary product fields
$stmt = $pdo->prepare("
SELECT id, name, price
FROM products
WHERE id IN ($placeholders)
");
$stmt->execute($ids);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Process each item to build the order summary
foreach ($rows as $p) {
    $pid = (int)$p["id"];
    $qty = (int)($cart[$pid] ?? 0);
    
    // Skip invalid quantities
    if ($qty <= 0) continue;

    $price = (float)$p["price"];
    $sum = $price * $qty;

    $items[] = [
        "name" => (string)$p["name"],
        "price" => $price,
        "qty" => $qty,
        "sum" => $sum
    ];

    $total += $sum;
}

include __DIR__ . "/../includes/header.php";
?>

<div class="sectionHeader">
    <div>
        <h2 class="sectionTitle">Checkout 🛍️</h2>
        <p class="sectionSub">Secure Checkpoint</p>
    </div>
    <a href="/ecommerce/public/cart.php" class="btn">← Back to Cart</a>
</div>

<div class="grid" style="grid-template-columns: 2fr 1fr; gap: var(--space-lg);">

    <!-- Left: Shipping & Payment -->
    <div class="card">
        <div class="cardBody">
            <h3 class="cardTitle" style="margin-bottom:var(--space-md);">Shipping Details</h3>
            
            <form id="checkoutForm" action="/ecommerce/public/order_place.php" method="POST">
                <div class="grid" style="grid-template-columns: 1fr 1fr; gap:var(--space-md);">
                    <div class="form-group">
                        <label class="form-label">First Name</label>
                        <input class="form-input" name="first_name" required placeholder="John">
                    </div>
                     <div class="form-group">
                        <label class="form-label">Last Name</label>
                        <input class="form-input" name="last_name" required placeholder="Doe">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Address</label>
                    <input class="form-input" name="address" required placeholder="123 Tech Street">
                </div>

                <div class="grid" style="grid-template-columns: 1fr 1fr 1fr; gap:var(--space-md);">
                    <div class="form-group">
                        <label class="form-label">City</label>
                        <input class="form-input" name="city" required placeholder="New York">
                    </div>
                     <div class="form-group">
                        <label class="form-label">Zip Code</label>
                        <input class="form-input" name="zip" required placeholder="10001">
                    </div>
<?php
                    // Editable Country List
                    $countries = [
                        "Italy",
                        "United States",
                        "Germany",
                        "United Kingdom",
                        "France",
                        "Spain",
                        "Netherlands"
                    ];
                    ?>
                    <div class="form-group">
                        <label class="form-label">Country</label>
                        <select class="form-input" name="country">
                            <?php foreach ($countries as $country): ?>
                                <option value="<?= htmlspecialchars($country) ?>"><?= htmlspecialchars($country) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <hr style="border:0; border-top:1px solid rgba(255,255,255,0.1); margin:var(--space-lg) 0;">

                <h3 class="cardTitle" style="margin-bottom:var(--space-md);">Payment Details</h3>
                
                <div class="form-group">
                    <label class="form-label">Card Number</label>
                    <input class="form-input" placeholder="0000 0000 0000 0000">
                </div>

                <div class="grid" style="grid-template-columns: 1fr 1fr; gap:var(--space-md);">
                    <div class="form-group">
                        <label class="form-label">Expiry</label>
                        <input class="form-input" placeholder="MM/YY">
                    </div>
                     <div class="form-group">
                        <label class="form-label">CVC</label>
                        <input class="form-input" placeholder="123">
                    </div>
                </div>

                <div class="mt-lg">
                    <button type="submit" class="btn btnPrimary" style="width:100%; padding:1rem; font-size:1.1rem;">Pay €<?= number_format($total, 2) ?></button>
                    <p class="text-center mt-sm text-muted" style="font-size:0.8rem;">🔒 Secure Encrypted Transaction</p>
                </div>
            </form>
        </div>
    </div>

    <!-- Right: Order Summary -->
    <div>
        <div class="card" style="position:sticky; top:100px;">
            <div class="cardBody">
                <h3 class="cardTitle">Order Summary</h3>
                <ul style="margin-top:var(--space-md);">
                    <?php foreach ($items as $it): ?>
                    <li style="display:flex; justify-content:space-between; margin-bottom:var(--space-sm); font-size:0.9rem;">
                        <span><?= (int)$it['qty'] ?>x <?= htmlspecialchars($it['name']) ?></span>
                        <span>€<?= number_format($it['sum'], 2) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <hr style="border:0; border-top:1px solid rgba(255,255,255,0.1); margin:var(--space-sm) 0;">
                <div style="display:flex; justify-content:space-between; font-weight:700; font-size:1.2rem;">
                    <span>Total</span>
                    <span style="color:var(--accent);">€<?= number_format($total, 2) ?></span>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
include __DIR__ . "/../includes/footer.php";
?>
