<?php
// Script de configuration automatique pour Investian

$host = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'investian_db';

try {
    // 1. Connexion sans base de données
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Création de la base de données
    echo "Réinitialisation de la base de données '$dbName'...\n";
    $pdo->exec("DROP DATABASE IF EXISTS `$dbName` ");
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Base de données réinitialisée avec succès.\n\n";

    // 3. Connexion à la nouvelle base
    $pdo->exec("USE `$dbName` ");

    // 4. Lecture et exécution du fichier schema.sql
    $sqlFile = __DIR__ . '/schema.sql';
    if (file_exists($sqlFile)) {
        echo "Exécution du schéma SQL...\n";
        $sql = file_get_contents($sqlFile);

        // Suppression des commentaires et exécution
        $pdo->exec($sql);
        echo "Tables créées et données initiales insérées avec succès.\n";
    } else {
        echo "Erreur : Fichier schema.sql introuvable.\n";
    }

    echo "\nInstallation terminée. Vous pouvez maintenant accéder au site.\n";

} catch (PDOException $e) {
    die("\nErreur lors de l'installation : " . $e->getMessage());
}
