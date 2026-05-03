# TechTrade

TechTrade is a PHP e-commerce demo project for buying and managing technology products. It includes a public storefront, user accounts, cart and checkout flow, wishlist, order history, invoices, stock validation, multilingual UI support, and an admin panel.

## Features

- Product catalog with category filtering
- User registration and login
- Shopping cart with stock checks
- Checkout with promo codes and simple currency display
- Wishlist system
- Order history, order details, and invoice page
- Admin dashboard with revenue, orders, products, and customers
- Admin product create, edit, and delete
- Admin users and orders pages
- English and Italian translations using simple PHP arrays
- Clean CSS structure split into global, layout, components, and admin files

## Technologies

- PHP
- MySQL

- PDO prepared statements
- HTML
- CSS
- XAMPP local server

## Project Structure

```text
ecommerce/
├── admin/                  # Admin dashboard and management pages
├── config/                 # Database, authentication, and language config
│   ├── auth.php
│   ├── db.php
│   └── lang.php
├── includes/               # Shared public layout and components
│   ├── header.php
│   ├── footer.php
│   └── product_card.php
├── public/                 # Public storefront pages
│   ├── assets/
│   │   ├── style.css       # Variables, base styles, typography
│   │   ├── layout.css      # Grid, flex, spacing, layout helpers
│   │   ├── components.css  # Buttons, cards, forms, tables, UI components
│   │   ├── admin.css       # Admin-only styles
│   │   └── hero.jpg
│   ├── index.php
│   ├── cart.php
│   ├── checkout.php
│   ├── orders.php
│   └── wishlist.php
└── README.md
```

## Database Configuration

Database settings are in:

```text
config/db.php
```

Default local settings:

```php
$DB_HOST = "127.0.0.1";
$DB_PORT = "3307";
$DB_NAME = "ecommerce_db";
$DB_USER = "root";
$DB_PASS = "";
```

Update these values if your MySQL port, database name, or password is different.

## Main Database Tables

The project expects these main tables:

- `users`
- `products`
- `orders`
- `order_items`
- `wishlist`

## How To Run Locally

1. Start Apache and MySQL in XAMPP.
2. Place the project in:

```text
/Applications/XAMPP/xamppfiles/htdocs/ecommerce
```

3. Create a MySQL database named:

```text
ecommerce_db
```

4. Import or create the required tables.
5. Open the storefront:

```text
http://localhost/ecommerce/public/index.php
```

6. Open the admin panel after logging in with an admin user:

```text
http://localhost/ecommerce/admin/dashboard.php
```

## Language Support

Translations are stored in:

```text
config/lang.php
```

The project supports:

- English
- Italian

The language switch is shown in the header using flag links.


