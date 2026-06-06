<?php
/**
 * Configuration de la base de données pour l'administration
 * Ce fichier inclut la configuration principale et exporte la variable $pdo
 */

// Activer l'affichage des erreurs en mode développement
if ($_SERVER['SERVER_NAME'] === 'sql100.infinityfree.com' || $_SERVER['SERVER_NAME'] === 'sql100.infinityfree.com') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../php_errors.log');
} else {
    // En production, logger les erreurs mais ne pas les afficher
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../php_errors.log');
}

// Inclure la configuration principale de la base de données
require_once __DIR__ . '/../../../config/db.php';

// Créer une connexion PDO en utilisant la classe Database
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Vérifier que la connexion est établie
    if (!$pdo) {
        throw new Exception("Impossible d'obtenir la connexion à la base de données");
    }
    
} catch (Exception $e) {
    // Logger l'erreur
    error_log("Erreur de connexion DB Admin: " . $e->getMessage());
    
    // En développement, afficher l'erreur
    if ($_SERVER['SERVER_NAME'] === 'sql100.infinityfree.com' || $_SERVER['SERVER_NAME'] === 'sql100.infinityfree.com') {
        die("<div style='background: #fee; border: 2px solid #c00; padding: 20px; margin: 20px; border-radius: 8px;'>
            <h2 style='color: #c00; margin-top: 0;'>⚠ Erreur de connexion à la base de données</h2>
            <p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
            <p><strong>Fichier:</strong> " . __FILE__ . "</p>
            <p style='margin-bottom: 0;'><em>Vérifiez la configuration dans config/db.php</em></p>
        </div>");
    } else {
        die("Erreur de connexion à la base de données. Veuillez contacter l'administrateur.");
    }
}

// Fonction helper pour afficher les erreurs de manière formatée (en développement uniquement)
function displayError($message, $file = '', $line = 0, $trace = '') {
    if ($_SERVER['SERVER_NAME'] === 'sql100.infinityfree.com' || $_SERVER['SERVER_NAME'] === 'sql100.infinityfree.com') {
        echo "<div style='background: #fee; border-left: 4px solid #c00; padding: 15px; margin: 10px 0; font-family: monospace;'>";
        echo "<strong style='color: #c00;'>⚠ Erreur:</strong> " . htmlspecialchars($message);
        if ($file) {
            echo "<br><small><strong>Fichier:</strong> " . htmlspecialchars($file);
            if ($line) echo " <strong>Ligne:</strong> " . $line;
            echo "</small>";
        }
        if ($trace) {
            echo "<details style='margin-top: 10px;'><summary style='cursor: pointer; color: #666;'>Stack Trace</summary>";
            echo "<pre style='background: #f5f5f5; padding: 10px; overflow-x: auto;'>" . htmlspecialchars($trace) . "</pre>";
            echo "</details>";
        }
        echo "</div>";
    }
}

// Gestionnaire d'erreurs personnalisé
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $error_message = "[$errno] $errstr in $errfile on line $errline";
    error_log($error_message);
    
    // Afficher l'erreur en développement
    if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
        displayError($errstr, $errfile, $errline);
    }
    
    return false; // Laisser le gestionnaire d'erreurs PHP par défaut continuer
});

// Gestionnaire d'exceptions personnalisé
set_exception_handler(function($exception) {
    $error_message = "Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();
    error_log($error_message);
    
    // Afficher l'exception en développement
    if ($_SERVER['SERVER_NAME'] === 'sql100.infinityfree.com' || $_SERVER['SERVER_NAME'] === 'sql100.infinityfree.com') {
        displayError($exception->getMessage(), $exception->getFile(), $exception->getLine(), $exception->getTraceAsString());
    } else {
        echo "Une erreur est survenue. Veuillez réessayer plus tard.";
    }
});