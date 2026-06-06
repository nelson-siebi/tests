<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Core\Model;

try {
    $db = Model::connect();
    $stmt = $db->query("DESCRIBE plans");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Current Table Structure:\n";
    foreach ($columns as $col) {
        echo "{$col['Field']} - {$col['Type']}\n";
    }

    $stmt = $db->query("SELECT * FROM plans LIMIT 5");
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\nSample Data:\n";
    print_r($plans);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
