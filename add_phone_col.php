<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Core\Model;

try {
    $db = Model::connect();
    $sql = "ALTER TABLE users ADD COLUMN phone VARCHAR(20) UNIQUE AFTER email";
    $db->exec($sql);
    echo "Phone column added successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
