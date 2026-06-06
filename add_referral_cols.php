<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Model;
use App\Core\Env;

// Load Env
Env::load(__DIR__ . '/.env');

try {
    $db = Model::connect();

    // Add referral_code
    try {
        $db->exec("ALTER TABLE users ADD COLUMN referral_code VARCHAR(10) UNIQUE AFTER phone");
        echo "Column 'referral_code' added successfully.\n";
    } catch (PDOException $e) {
        echo "Column 'referral_code' might already exist or error: " . $e->getMessage() . "\n";
    }

    // Add referred_by
    try {
        $db->exec("ALTER TABLE users ADD COLUMN referred_by INT NULL AFTER referral_code");
        echo "Column 'referred_by' added successfully.\n";
    } catch (PDOException $e) {
        echo "Column 'referred_by' might already exist or error: " . $e->getMessage() . "\n";
    }

    // Add transaction type enum 'commission' if not exists (usually handled in code logic for type string)
    // But let's check if we need to update any ENUM columns. 
    // In our schema, we use VARCHAR for transaction type, so no need to alter ENUM.

    echo "Database schema updated for Referrals.\n";

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
