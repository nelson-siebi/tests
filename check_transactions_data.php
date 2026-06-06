<?php
require_once 'config/db.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "=== Transaction Types ===\n";
    $stmt = $db->query("SELECT DISTINCT type, COUNT(*) as count FROM transactions GROUP BY type");
    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($types);
    
    echo "\n=== Transaction Statuses ===\n";
    $stmt = $db->query("SELECT DISTINCT statut, COUNT(*) as count FROM transactions GROUP BY statut");
    $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($statuses);

    echo "\n=== Sample Deposits ===\n";
    $stmt = $db->query("SELECT * FROM transactions WHERE type LIKE '%depot%' LIMIT 3");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

     echo "\n=== Sample Withdrawals ===\n";
    $stmt = $db->query("SELECT * FROM transactions WHERE type LIKE '%retrait%' LIMIT 3");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
