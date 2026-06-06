<?php
require_once 'config/db.php';

try {
    $pdo = Database::getInstance()->getConnection();
    echo "Connected to database.\n";

    // Check if column exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM transactions LIKE 'numero_telephone'");
    $stmt->execute();
    $column = $stmt->fetch();

    if ($column) {
        echo "Column 'numero_telephone' already exists in 'transactions' table.\n";
    } else {
        echo "Adding 'numero_telephone' column to 'transactions' table...\n";
        $sql = "ALTER TABLE transactions ADD COLUMN numero_telephone VARCHAR(50) DEFAULT NULL AFTER methode";
        $pdo->exec($sql);
        echo "Column 'numero_telephone' added successfully.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
