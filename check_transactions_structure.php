<?php
require_once 'config/db.php';
$pdo = Database::getInstance()->getConnection();

echo "=== Structure de la table transactions ===\n\n";

$stmt = $pdo->query("DESCRIBE transactions");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($columns as $col) {
    echo "Colonne: {$col['Field']}\n";
    echo "  Type: {$col['Type']}\n";
    echo "  Null: {$col['Null']}\n";
    echo "  Default: {$col['Default']}\n";
    echo "\n";
}

// Vérifier spécifiquement la colonne source
echo "\n=== Détails de la colonne 'source' ===\n";
$stmt = $pdo->query("SHOW COLUMNS FROM transactions LIKE 'source'");
$sourceCol = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($sourceCol);
?>
