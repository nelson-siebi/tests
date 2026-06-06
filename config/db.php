<?php
// config/db.php

// Configuration de la base de données
// define('DB_HOST', 'sql310.infinityfree.com');
// define('DB_NAME', 'if0_40911094_freecash');
// define('DB_USER', 'if0_40911094');      
// define('DB_PASS', 'uUrowIt3dVuf');         
// define('DB_CHARSET', 'utf8mb4');
define('DB_HOST', 'localhost');
define('DB_NAME', 'investian_db');
define('DB_USER', 'root');      
define('DB_PASS', '');         
define('DB_CHARSET', 'utf8mb4');
class Database
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->connection = new PDO($dsn, DB_USER, DB_PASS);

            // Configuration PDO
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            // Définir le fuseau horaire
            $this->connection->exec("SET time_zone = '+00:00'");

        } catch (PDOException $e) {
            // En production, logger l'erreur
            error_log("Erreur DB: " . $e->getMessage());

            // Afficher un message générique (en développement, afficher l'erreur)
            if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
                die("Erreur de connexion à la base de données : " . $e->getMessage());
            } else {
                die("Erreur de connexion à la base de données. Veuillez réessayer plus tard.");
            }
        }
    }

    // Singleton pattern
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Récupérer la connexion PDO
    public function getConnection()
    {
        return $this->connection;
    }
}

// Exporter une instance globale si nécessaire
// $db = Database::getInstance()->getConnection();