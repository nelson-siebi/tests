<?php
require_once __DIR__ . '/vendor/autoload.php';

// Manually include config if autoload fails or just to be safe
if (file_exists(__DIR__ . '/app/Core/Model.php')) {
    require_once __DIR__ . '/app/Core/Model.php';
}
if (file_exists(__DIR__ . '/app/Config/database.php')) {
    require_once __DIR__ . '/app/Config/database.php';
}

use App\Core\Model;

try {
    // Re-implement connect since autoload might fail in this standalone script context depending on how it's set up
    $config = require __DIR__ . '/app/Config/database.php';
    $dsn = "mysql:host={$config['host']};dbname={$config['db_name']};charset=utf8mb4";
    $db = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    echo "Connected to database: {$config['db_name']}\n";

    // Debug: List all tables
    echo "Tables in database:\n";
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    print_r($tables);

    if (!in_array('investment_plans', $tables)) {
        echo "Table 'investment_plans' NOT found.\n";
        exit;
    }

    // 1. Check if column exists
    $stmt = $db->query("DESCRIBE investment_plans");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('daily_profit_amount', $columns)) {
        echo "Adding 'daily_profit_amount' column...\n";
        $db->exec("ALTER TABLE investment_plans ADD COLUMN daily_profit_amount DECIMAL(15, 2) DEFAULT 0 AFTER price");
        echo "Column added.\n";

        // Optional: Populate it based on percentage for existing plans
        if (in_array('daily_profit_percent', $columns)) {
            echo "Migrating data from percentage...\n";
            $db->exec("UPDATE investment_plans SET daily_profit_amount = (price * daily_profit_percent) / 100 WHERE daily_profit_amount = 0");
            echo "Data migrated.\n";
        }
    } else {
        echo "'daily_profit_amount' already exists.\n";
    }

    // Verify
    $stmt = $db->query("SELECT id, name, price, daily_profit_percent, daily_profit_amount FROM investment_plans LIMIT 5");
    $plans = $stmt->fetchAll();
    print_r($plans);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
