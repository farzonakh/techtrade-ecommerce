<?php
declare(strict_types=1);


require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";

// Only logged-in users can reach checkout.
require_login();

$validPromoCodes = [
    "SAVE10" => 0.10,
    "SAVE20" => 0.20,
];

// Countries shown in the shipping form.
$countries = [
    "Italy",
    "United States",
    "Germany",
    "United Kingdom",
    "France",
    "Spain",
    "Netherlands",
];

// Simple demo currency settings.
$currencyRates = [
    "EUR" => 1.0,
    "USD" => 1.1,
];
$currencySymbols = [
    "EUR" => "€",
    "USD" => "$",
];

$currency = strtoupper(trim($_GET["currency"] ?? ($_SESSION["currency"] ?? "EUR")));
if (!isset($currencyRates[$currency])) {
    $currency = "EUR";
}
$_SESSION["currency"] = $currency;

$symbol = $currencySymbols[$currency];
$rate = $currencyRates[$currency];
$promoCode = strtoupper(trim($_GET["promo_code"] ?? ""));
$discountRate = $validPromoCodes[$promoCode] ?? 0.0;
$promoMessage = "";

if ($promoCode !== "") {
    if ($discountRate > 0) {
        $promoMessage = "Promo code {$promoCode} applied 🎉";
    } else {
        $promoMessage = "Invalid promo code ❌";
    }
}

$cart = $_SESSION["cart"] ?? [];
if (empty($cart)) {
    header("Location: /ecommerce/public/cart.php");
    exit;
}

$items = [];
$total = 0.0;
$stockWarnings = [];
$checkoutError = $_SESSION["checkout_error"] ?? "";

// Show checkout errors once, then remove them from the session.
if ($checkoutError !== "") {
    unset($_SESSION["checkout_error"]);
}

$ids = array_map("intval", array_keys($cart));
$placeholders = implode(",", array_fill(0, count($ids), "?"));

// Get current product details before showing the checkout page.
$stmt = $pdo->prepare("
    SELECT id, name, price, stock
    FROM products
    WHERE id IN ($placeholders)
");
$stmt->execute($ids);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Build the order summary and warn if stock has changed.
foreach ($rows as $p) {
    $pid = (int)$p["id"];
    $qty = (int)($cart[$pid] ?? 0);
    $currentStock = (int)($p["stock"] ?? 0);
    
    if ($qty <= 0) continue;

    $price = (float)$p["price"];
    $sum = $price * $qty;

    if ($currentStock < $qty) {
        $stockWarnings[] = [
            "id" => $pid,
            "name" => (string)$p["name"],
            "requested" => $qty,
            "available" => $currentStock
        ];
    }

    $items[] = [
        "id" => $pid,
        "name" => (string)$p["name"],
        "price" => $price,
        "qty" => $qty,
        "current_stock" => $currentStock,
        "sum" => $sum
    ];

    $total += $sum;
}

$discountAmount = $discountRate > 0 ? $total * $discountRate : 0.0;
$finalTotal = $total - $discountAmount;

function money(float $amount, string $symbol, float $rate): string
{
    return $symbol . number_format($amount * $rate, 2);
}

include __DIR__ . "/../includes/header.php";
?>

<?php if ($checkoutError !== ""): ?>
    <div class="card alert alert-danger">
        <div class="cardBody">
            <p class="alert-title text-danger">⚠️ Order Failed</p>
            <p class="alert-text text-danger pre-wrap">
                <?= htmlspecialchars($checkoutError) ?>
            </p>
        </div>
    </div>
<?php endif; ?>

<?php if ($stockWarnings): ?>
    <div class="card alert alert-warning">
        <div class="cardBody">
            <p class="alert-title text-stock-warning">⚠️ <?= t("stock_changed") ?></p>
            <p class="alert-text text-warning">
                <?= t("stock_too_low") ?> 📦:
            </p>
            <ul class="alert-list">
                <?php foreach ($stockWarnings as $w): ?>
                    <li class="text-warning">
                        <strong><?= htmlspecialchars($w["name"]) ?></strong> - 
                        <?= t("available") ?>: <?= (int)$w["available"] ?>, 
                        <?= t("requested") ?>: <?= (int)$w["requested"] ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <p class="alert-text text-warning">
                You can still click Pay; stock will be checked again before the order is saved.
            </p>
        </div>
    </div>
<?php endif; ?>

<div class="sectionHeader">
    <div>
        <h2 class="sectionTitle"><?= t("checkout") ?> 🛍️</h2>
        <p class="sectionSub"><?= t("secure_checkpoint") ?></p>
    </div>
    <a href="/ecommerce/public/cart.php" class="btn">← <?= t("back_to_cart") ?></a>
</div>

<div class="grid grid-2-1 gap-lg">

    <!-- Left: Shipping & Payment -->
    <div class="card">
        <div class="cardBody">
            <h3 class="cardTitle mb-md"><?= t("shipping_details") ?></h3>
            
            <form id="checkoutForm" action="/ecommerce/public/order_place.php" method="POST">
                <input type="hidden" name="promo_code" value="<?= htmlspecialchars($promoCode) ?>">
                <input type="hidden" name="currency" value="<?= htmlspecialchars($currency) ?>">
                <div class="grid grid-2-cols gap-md">
                    <div class="form-group">
                        <label class="form-label"><?= t("first_name") ?></label>
                        <input class="form-input" name="first_name" placeholder="John">
                    </div>
                     <div class="form-group">
                        <label class="form-label"><?= t("last_name") ?></label>
                        <input class="form-input" name="last_name" placeholder="Doe">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label"><?= t("address") ?></label>
                    <input class="form-input" name="address" placeholder="123 Tech Street">
                </div>

                <div class="grid grid-3-cols gap-md">
                    <div class="form-group">
                        <label class="form-label"><?= t("city") ?></label>
                        <input class="form-input" name="city" placeholder="New York">
                    </div>
                     <div class="form-group">
                        <label class="form-label"><?= t("zip_code") ?></label>
                        <input class="form-input" name="zip" placeholder="10001">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= t("country") ?></label>
                        <select class="form-input" name="country">
                            <?php foreach ($countries as $country): ?>
                                <option value="<?= htmlspecialchars($country) ?>"><?= htmlspecialchars($country) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <hr class="divider">

                <h3 class="cardTitle mb-md"><?= t("payment_details") ?></h3>
                
                <div class="form-group">
                    <label class="form-label"><?= t("card_number") ?></label>
                    <input class="form-input" placeholder="0000 0000 0000 0000">
                </div>

                <div class="grid grid-2-cols gap-md">
                    <div class="form-group">
                        <label class="form-label"><?= t("expiry") ?></label>
                        <input class="form-input" placeholder="MM/YY">
                    </div>
                     <div class="form-group">
                        <label class="form-label">CVC</label>
                        <input class="form-input" placeholder="123">
                    </div>
                </div>

                <div class="mt-lg">
                    <button type="submit" class="btn btnPrimary w-100 checkout-button"><?= t("pay") ?> <?= money($finalTotal, $symbol, $rate) ?></button>
                    <p class="text-center mt-sm text-muted text-xs"><?= t("demo_payment") ?></p>
                </div>
            </form>
        </div>
    </div>

    <!-- Right: Order Summary -->
    <div>
        <div class="card card-sticky">
            <div class="cardBody">
                <h3 class="cardTitle"><?= t("order_summary") ?></h3>
                <form method="GET" class="summary-form">
                    <input type="hidden" name="lang" value="<?= htmlspecialchars($lang) ?>">
                    <div class="form-group form-group-compact">
                        <label class="form-label"><?= t("currency") ?></label>
                        <select class="form-input" name="currency" onchange="this.form.submit()">
                            <option value="EUR" <?= $currency === "EUR" ? "selected" : "" ?>>EUR (€)</option>
                            <option value="USD" <?= $currency === "USD" ? "selected" : "" ?>>USD ($)</option>
                        </select>
                    </div>
                    <div class="form-group form-group-compact">
                        <label class="form-label"><?= t("promo_code") ?></label>
                        <div class="promo-row">
                            <input class="form-input flex-1" name="promo_code" value="<?= htmlspecialchars($promoCode) ?>" placeholder="SAVE10">
                            <button type="submit" class="btn"><?= t("apply") ?></button>
                        </div>
                    </div>
                    <?php if ($promoMessage): ?>
                        <p class="m-0 text-xs font-semibold <?= $discountRate > 0 ? 'text-success' : 'text-danger' ?>">
                            <?= htmlspecialchars($promoMessage) ?>
                        </p>
                    <?php endif; ?>
                </form>
                <ul class="order-summary-list">
                    <?php foreach ($items as $it): ?>
                    <li class="order-summary-item">
                        <div>
                            <div><?= (int)$it['qty'] ?>x <?= htmlspecialchars($it['name']) ?></div>
                            <div class="text-xs text-muted mt-xs">
                                <?= t("stock") ?>: <?= (int)$it['current_stock'] ?>
                            </div>
                        </div>
                        <span><?= money((float)$it['sum'], $symbol, $rate) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <hr class="divider divider-sm">
                <div class="summary-row text-muted">
                    <span><?= t("subtotal") ?></span>
                    <span><?= money($total, $symbol, $rate) ?></span>
                </div>
                <?php if ($discountAmount > 0): ?>
                    <div class="summary-row text-success">
                        <span><?= t("discount") ?> <?= htmlspecialchars($promoCode) ?></span>
                        <span>-<?= money($discountAmount, $symbol, $rate) ?></span>
                    </div>
                <?php endif; ?>
                <div class="order-summary-total">
                    <span><?= t("total") ?></span>
                    <span class="text-accent"><?= money($finalTotal, $symbol, $rate) ?></span>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
include __DIR__ . "/../includes/footer.php";
?>
