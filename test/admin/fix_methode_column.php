<?php
// fix_methode_column.php
require_once __DIR__ . '/config/database.php';

try {
    echo "Connecting to database...\n";

    // 1. Check and Fix 'methode'
    echo "Checking 'methode' column...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM transactions LIKE 'methode'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r($column);

    echo "Modifying 'methode' column to VARCHAR(50)...\n";
    $pdo->exec("ALTER TABLE transactions MODIFY COLUMN methode VARCHAR(50) NULL");
    
    // 2. Proactively Check and Fix 'type' (just in case)
    echo "Checking 'type' column...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM transactions LIKE 'type'");
    $typeCol = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r($typeCol);
    
    // If it's an enum or small varchar, lets safeproof it too if needed. 
    // Usually 'depot', 'retrait', 'bonus' fits in enum, but let's be safe if we see it's tight.
    // For now, let's just stick to methode as requested to minimize side effects, 
    // but I'll print 'type' to see if it needs attention next.

    echo "Column 'methode' modified successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
