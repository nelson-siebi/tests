<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Config/database.php';

try {
    $config = require __DIR__ . '/app/Config/database.php';
    $dsn = "mysql:host={$config['host']};dbname={$config['db_name']};charset=utf8mb4";
    $db = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    echo "Connected to: {$config['db_name']}\n";
    $stmt = $db->query("DESCRIBE investment_plans");
    print_r($stmt->fetchAll());

    $stmt = $db->query("SELECT * FROM investment_plans LIMIT 1");
    print_r($stmt->fetch());

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
