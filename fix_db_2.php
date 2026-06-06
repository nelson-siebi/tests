<?php
require_once 'config/db.php';
$pdo = Database::getInstance()->getConnection();

echo "Correction de la colonne 'methode'...\n";

try {
    // 2. Ajouter 'systeme' à la colonne 'methode'
    // 'type' est déjà fait.
    // On ajoute 'systeme' (sans accent) pour éviter les doublons de collation
    $pdo->exec("ALTER TABLE transactions MODIFY COLUMN methode ENUM('orange', 'mtn', 'visa', 'mobile_money', 'autre', 'systeme') NOT NULL DEFAULT 'autre'");
    echo "✅ Colonne 'methode' mise à jour.\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
?>
