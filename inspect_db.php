<?php
require_once 'config/db.php';

try {
    $pdo = Database::getInstance()->getConnection();
} catch (Exception $e) {
    die("❌ Erreur de connexion : " . $e->getMessage() . "\n");
}

echo "📊 VUE D'ENSEMBLE DE LA BASE DE DONNÉES\n";
echo "========================================\n\n";

try {
    // 1. Get List of Tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($tables)) {
        echo "❌ Aucune table trouvée. La base de données est vide.\n";
        exit;
    }

    echo "✅ Nombre de tables trouvées : " . count($tables) . "\n\n";

    foreach ($tables as $table) {
        echo "🔹 TABLE : $table\n";
        echo str_repeat("-", 50) . "\n";
        
        // Structure
        $stmtDesc = $pdo->query("DESCRIBE `$table`");
        $columns = $stmtDesc->fetchAll(PDO::FETCH_ASSOC);
        
        $colDefs = [];
        foreach ($columns as $col) {
            $colDefs[] = $col['Field'] . "(" . $col['Type'] . ")";
        }
        echo "   📒 Colonnes : " . implode(', ', $colDefs) . "\n";
        
        // Data Count
        $stmtCount = $pdo->query("SELECT COUNT(*) FROM `$table`");
        $count = $stmtCount->fetchColumn();
        echo "   📊 Lignes   : $count\n";

        // Sample Data (first 3 rows)
        if ($count > 0) {
            echo "   👀 Aperçu (3 premières lignes) :\n";
            $stmtData = $pdo->query("SELECT * FROM `$table` LIMIT 3");
            $rows = $stmtData->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                // Shorten long strings for display
                $displayRow = array_map(function($val) {
                    if (is_string($val) && strlen($val) > 50) {
                        return substr($val, 0, 47) . '...';
                    }
                    return $val;
                }, $row);
                echo "      " . json_encode($displayRow, JSON_UNESCAPED_UNICODE) . "\n";
            }
        } else {
            echo "      (Table vide)\n";
        }
        echo "\n";
    }
    
    echo "========================================\n";
    echo "✅ Fin de l'inspection.\n";

} catch (PDOException $e) {
    echo "❌ Erreur SQL pendant l'inspection : " . $e->getMessage() . "\n";
}
