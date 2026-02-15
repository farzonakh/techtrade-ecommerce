<?php
require_once __DIR__ . "/config/db.php";
$stmt = $pdo->query("DESCRIBE orders");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
