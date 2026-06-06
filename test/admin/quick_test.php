<?php
//jjjjjjjjjj
session_start();
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_id'] = 1;

// Copiez ici le code de validate_investments.php
// Mais remplacez le début par :
echo "<h1>Test de validation</h1>";
echo "<pre>";

// Testez chaque fonction
require_once 'config/database.php';

// Test 1 : Connexion
echo "Test connexion : ";
try {
    $pdo->query("SELECT 1");
    echo "OK\n";
} catch(Exception $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
}

// Test 2 : Fonction getPendingTransactions
echo "Test getPendingTransactions : ";
try {
    $transactions = getPendingTransactions();
    echo count($transactions) . " transactions trouvées\n";
} catch(Exception $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    echo "Trace : " . $e->getTraceAsString() . "\n";
}

echo "</pre>";

// Affichez les définitions des fonctions pour vérifier
highlight_file(__FILE__);
?>