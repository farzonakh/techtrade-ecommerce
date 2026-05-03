<?php
declare(strict_types=1);

// Keep long product descriptions short inside product cards.
function short_text(string $text, int $limit = 140): string
{
    $text = trim($text);

    if ($text === "") {
        return "";
    }

    return mb_strlen($text) > $limit ? mb_substr($text, 0, $limit) . "..." : $text;
}

// Use a placeholder image when a product has no image URL.
function product_image(?string $url): string
{
    $url = trim((string)$url);

    return $url !== "" ? $url : "/ecommerce/public/assets/placeholder.png";
}

// Render one product card for the shop and wishlist pages.
function render_product_card(array $product, bool $loggedIn, array $wishlistIds): void
{
    $productId = (int)$product["id"];
    $isLiked = $loggedIn && in_array($productId, $wishlistIds, true);
    $productName = (string)($product["name"] ?? "");
    $description = short_text((string)($product["description"] ?? ""), 100);
    $productPrice = (float)($product["price"] ?? 0);
    $category = (string)($product["category"] ?? "Other");
    $stock = (int)($product["stock"] ?? 0);
    $imageUrl = product_image($product["image_url"] ?? null);
    $condition = ucfirst((string)($product["item_condition"] ?? "new"));
    ?>
    <div class="card product-card">
        <div class="cardThumb">
            <img
                src="<?= htmlspecialchars($imageUrl) ?>"
                alt="<?= htmlspecialchars($productName) ?>"
                class="lazy"
                loading="lazy"
                onerror="this.onerror=null;this.src='/ecommerce/public/assets/placeholder.png';"
            >
        </div>

        <div class="cardBody">
            <div class="product-meta">
                <span class="badge"><?= htmlspecialchars($category) ?></span>
                <span class="badge badge-condition">
                    <?= htmlspecialchars($condition) ?>
                </span>
            </div>

            <h3 class="cardTitle"><?= htmlspecialchars($productName) ?></h3>
            <div class="cardPrice">€<?= number_format($productPrice, 2) ?></div>

            <p class="cardDesc"><?= htmlspecialchars($description) ?></p>

            <p class="cardDesc product-status <?= $stock > 0 ? 'available' : 'unavailable' ?>">
                <?= $stock > 0 ? t("stock") . ": " . $stock : t("out_of_stock") ?>
            </p>

            <div class="cardActions">
                <?php if ($loggedIn): ?>
                    <form method="POST" action="/ecommerce/public/wishlist_toggle.php" class="form-inline">
                        <input type="hidden" name="product_id" value="<?= $productId ?>">
                        <button
                            class="btn <?= $isLiked ? 'btnDanger' : '' ?>"
                            type="submit"
                            title="<?= $isLiked ? t('remove_wishlist') : t('add_wishlist') ?>"
                        >
                            <?= $isLiked ? "♥" : "♡" ?>
                        </button>
                    </form>

                    <?php if ($stock > 0): ?>
                        <form method="POST" action="/ecommerce/public/cart_add.php" class="flex-1">
                            <input type="hidden" name="product_id" value="<?= $productId ?>">
                            <button class="btn btnPrimary w-100" type="submit"><?= t("add_to_cart") ?></button>
                        </form>
                    <?php else: ?>
                        <button class="btn flex-1 cursor-not-allowed opacity-50" disabled><?= t("out_of_stock") ?></button>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="/ecommerce/public/login.php" class="btn w-100"><?= t("login_to_buy") ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}
