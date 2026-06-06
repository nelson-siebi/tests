<?php
require_once 'config/db.php';

// Augmenter le temps d'exécution et la mémoire pour les gros fichiers
set_time_limit(300);
ini_set('memory_limit', '256M');

$sqlFile = 'db.sql';

if (!file_exists($sqlFile)) {
    die("❌ Erreur : Le fichier SQL '$sqlFile' est introuvable.");
}

try {
    $db = Database::getInstance()->getConnection();
    
    echo "🔄 Démarrage de la réinitialisation de la base de données...\n";
    
    // 1. Désactiver les vérifications de clés étrangères
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // 2. Récupérer la liste des tables
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // 3. Supprimer toutes les tables
    if (count($tables) > 0) {
        echo "🗑️  Suppression de " . count($tables) . " tables existantes...\n";
        foreach ($tables as $table) {
            $db->exec("DROP TABLE IF EXISTS `$table`");
            echo "   - Table `$table` supprimée.\n";
        }
    } else {
        echo "ℹ️  Aucune table à supprimer.\n";
    }
    
    // 4. Lire et exécuter le fichier SQL
    echo "📂 Lecture et importation du fichier '$sqlFile'...\n";
    $sqlContent = file_get_contents($sqlFile);
    
    // Pour éviter les problèmes avec les commentaires et les délimiteurs, 
    // l'exécution directe via PDO est souvent plus robuste pour les dumps phpMyAdmin
    // tant que la configuration le permet.
    $db->exec($sqlContent);
    
    // 5. Réactiver les clés étrangères
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "✅ Base de données réinitialisée et importée avec succès !\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur SQL : " . $e->getMessage() . "\n";
    die();
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
    die();
}
