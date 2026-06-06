<?php
require_once 'config/db.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "=== Sum by Type & Status ===\n";
    $sql = "SELECT type, statut, COUNT(*) as count, SUM(montant) as total 
            FROM transactions 
            GROUP BY type, statut
            ORDER BY type, statut";
    
    $stmt = $db->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    printf("%-15s | %-15s | %-5s | %-15s\n", "Type", "Statut", "Count", "Total");
    echo str_repeat("-", 55) . "\n";
    
    foreach ($results as $row) {
        printf("%-15s | %-15s | %-5d | %-15s\n", 
            $row['type'], 
            $row['statut'], 
            $row['count'], 
            number_format($row['total'], 2)
        );
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
