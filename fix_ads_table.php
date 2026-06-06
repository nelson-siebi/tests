<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Config/database.php';

try {
    $config = require __DIR__ . '/app/Config/database.php';
    $dsn = "mysql:host={$config['host']};dbname={$config['db_name']};charset=utf8mb4";
    $db = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "Vérification de la table 'ads'...\n";

    // Check if column exists
    $stmt = $db->query("DESCRIBE ads");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('status', $columns)) {
        echo "Ajout de la colonne 'status' à la table 'ads'...\n";
        $db->exec("ALTER TABLE ads ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active' AFTER reward");
        echo "Colonne ajoutée avec succès !\n";
    } else {
        echo "La colonne 'status' existe déjà.\n";
    }

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
