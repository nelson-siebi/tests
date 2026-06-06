<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Model;
use App\Core\Env;

// Load Env
Env::load(__DIR__ . '/.env');

try {
    $db = Model::connect();

    // Create community_messages table
    $sql = "CREATE TABLE IF NOT EXISTS community_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        message TEXT NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $db->exec($sql);
    echo "Table 'community_messages' created successfully.\n";

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
