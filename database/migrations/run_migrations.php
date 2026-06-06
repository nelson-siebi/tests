<?php
/**
 * Database Migration Runner
 * 
 * Ce fichier exécute toutes les migrations SQL dans le dossier database/migrations/
 * en suivant l'ordre numérique (001_, 002_, etc.)
 */

namespace Database\Migrations;

use PDO;
use PDOException;

class MigrationRunner
{
    private $db;
    private $migrationsDir;
    private $errors = [];
    private $executed = [];

    public function __construct($db)
    {
        $this->db = $db;
        $this->migrationsDir = __DIR__;
        $this->ensureMigrationsTable();
    }

    /**
     * Créer la table de suivi des migrations si elle n'existe pas
     */
    private function ensureMigrationsTable()
    {
        try {
            $this->db->query("SELECT 1 FROM migrations LIMIT 1");
        } catch (PDOException $e) {
            // La table n'existe pas, la créer
            $sql = "CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration_name VARCHAR(255) NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                success BOOLEAN DEFAULT TRUE,
                error_message TEXT
            )";
            $this->db->exec($sql);
        }
    }

    /**
     * Vérifier si une migration a déjà été exécutée
     */
    private function isExecuted($migrationName)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM migrations WHERE migration_name = ?");
        $stmt->execute([$migrationName]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Marquer une migration comme exécutée
     */
    private function markExecuted($migrationName, $success = true, $errorMsg = null)
    {
        $stmt = $this->db->prepare("INSERT INTO migrations (migration_name, success, error_message) VALUES (?, ?, ?)");
        $stmt->execute([$migrationName, $success ? 1 : 0, $errorMsg]);
    }

    /**
     * Récupérer toutes les migrations SQL ordonnées
     */
    private function getMigrationFiles()
    {
        $files = glob($this->migrationsDir . '/*.sql');
        sort($files); // Trier par ordre alphabétique (001_, 002_, etc.)
        return $files;
    }

    /**
     * Exécuter toutes les migrations
     */
    public function run()
    {
        $files = $this->getMigrationFiles();
        
        if (empty($files)) {
            return [
                'success' => true,
                'message' => 'Aucune migration à exécuter',
                'executed' => [],
                'errors' => []
            ];
        }

        foreach ($files as $file) {
            $migrationName = basename($file);
            
            // Vérifier si déjà exécutée
            if ($this->isExecuted($migrationName)) {
                continue;
            }

            try {
                // Lire et exécuter le fichier SQL
                $sql = file_get_contents($file);
                
                // Exécuter chaque instruction (séparées par ;)
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                
                foreach ($statements as $statement) {
                    if (empty($statement) || strpos($statement, '--') === 0) {
                        continue;
                    }
                    $this->db->exec($statement);
                }
                
                $this->markExecuted($migrationName, true);
                $this->executed[] = $migrationName;
                
            } catch (PDOException $e) {
                $this->errors[] = [
                    'file' => $migrationName,
                    'error' => $e->getMessage()
                ];
                $this->markExecuted($migrationName, false, $e->getMessage());
            }
        }

        return [
            'success' => empty($this->errors),
            'message' => empty($this->errors) 
                ? count($this->executed) . ' migration(s) exécutée(s)' 
                : 'Erreurs lors des migrations',
            'executed' => $this->executed,
            'errors' => $this->errors
        ];
    }

    /**
     * Obtenir le statut des migrations
     */
    public function getStatus()
    {
        $files = $this->getMigrationFiles();
        $executed = [];
        
        $stmt = $this->db->query("SELECT migration_name, executed_at, success FROM migrations ORDER BY id");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $executed[$row['migration_name']] = $row;
        }

        $pending = [];
        foreach ($files as $file) {
            $name = basename($file);
            if (!isset($executed[$name])) {
                $pending[] = $name;
            }
        }

        return [
            'total' => count($files),
            'executed' => count($executed),
            'pending' => $pending,
            'executed_files' => array_keys($executed)
        ];
    }
}
