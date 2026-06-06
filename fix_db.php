<?php
require_once 'config/db.php';
$pdo = Database::getInstance()->getConnection();

echo "Correction de la structure de la table 'transactions'...\n";

try {
    // 1. Ajouter 'gain' à la colonne 'type'
    // On conserve les valeurs existantes : 'depot', 'retrait' et on ajoute 'gain', 'achat', 'bonus'
    $pdo->exec("ALTER TABLE transactions MODIFY COLUMN type ENUM('depot', 'retrait', 'gain', 'achat', 'bonus') NOT NULL");
    echo "✅ Colonne 'type' mise à jour.\n";

    // 2. Ajouter 'système' à la colonne 'methode'
    // On conserve : 'orange', 'mtn', 'visa', 'mobile_money', 'autre' et on ajoute 'système'
    // Note: 'système' contains special char, better to use 'systeme' or handle charset carefully.
    // Let's use 'systeme' in DB and update code if needed, OR force matching charset.
    // Given existing code sends 'système', let's try to support it or assume strict mapping.
    // Actually safe bet: add 'systeme' (no accent) and update code to use 'systeme', OR add 'autre' fallback.
    // 'autre' exists.
    
    // Let's check what auto_roi uses: 'système'.
    // Let's update auto_roi to use 'autre' or 'systeme' AND update DB to allow it.
    // For now, let's just make the DB explicitly allow 'systeme' and 'système'.
    
    $pdo->exec("ALTER TABLE transactions MODIFY COLUMN methode ENUM('orange', 'mtn', 'visa', 'mobile_money', 'autre', 'systeme', 'système') NOT NULL DEFAULT 'autre'");
    echo "✅ Colonne 'methode' mise à jour.\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
?>
