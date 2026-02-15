<?php
require_once __DIR__ . "/config/db.php";

echo "Checking Orders Table...\n";
$count = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
echo "Total Orders: $count\n";

echo "Checking Users Table...\n";
$users = $pdo->query("SELECT id, full_name FROM users LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
print_r($users);

echo "Checking Join...\n";
$join = $pdo->query("SELECT o.id, o.user_id, u.full_name FROM orders o LEFT JOIN users u ON o.user_id = u.id LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
print_r($join);
