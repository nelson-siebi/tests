<?php
require_once 'config/db.php';

try {
    $pdo = Database::getInstance()->getConnection();
    
    // Vérifier si la colonne fake_name existe
    $stmt = $pdo->query("SHOW COLUMNS FROM community_messages LIKE 'fake_name'");
    $column = $stmt->fetch();
    
    if ($column) {
        echo "✅ Colonne 'fake_name' existe :\n";
        print_r($column);
    } else {
        echo "❌ Colonne 'fake_name' N'EXISTE PAS !\n";
        echo "\nExécute ce SQL pour la créer :\n";
        echo "ALTER TABLE community_messages ADD COLUMN fake_name VARCHAR(255) NULL DEFAULT NULL AFTER message;\n";
    }
    
    // Vérifier les derniers messages
    echo "\n--- Derniers messages ---\n";
    $stmt = $pdo->query("SELECT * FROM community_messages ORDER BY id DESC LIMIT 3");
    $messages = $stmt->fetchAll();
    foreach ($messages as $msg) {
        echo "ID: {$msg['id']}, User: {$msg['user_id']}, Fake: " . ($msg['fake_name'] ?? 'NULL') . ", Msg: " . substr($msg['message'], 0, 50) . "\n";
    }
    
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
