<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Simuler une session admin pour le test
session_start();
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_id'] = 1; // ID de l'admin dans votre base

// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=invest;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Créer la table audit_logs si elle n'existe pas
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `audit_logs` (
          `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
          `user_id` int UNSIGNED DEFAULT NULL,
          `admin_id` int UNSIGNED DEFAULT NULL,
          `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
          `table_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
          `record_id` bigint UNSIGNED DEFAULT NULL,
          `description` text COLLATE utf8mb4_unicode_ci,
          `old_values` json DEFAULT NULL,
          `new_values` json DEFAULT NULL,
          `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `user_agent` text COLLATE utf8mb4_unicode_ci,
          `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Inclure le fichier à tester avec capture des sorties
ob_start();
include 'validate_investments.php'; // Remplacez par le chemin réel
$output = ob_get_clean();

// Afficher les erreurs si elles existent
if (!empty($output)) {
    echo "<h2>Sortie du fichier :</h2>";
    echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>" . $output . "</div>";
}

// Vérifier s'il y a des transactions en attente
$stmt = $pdo->query("SELECT COUNT(*) FROM pending_transactions WHERE status = 'pending'");
$pending_count = $stmt->fetchColumn();

echo "<h2>Statistiques de test :</h2>";
echo "<ul>";
echo "<li>Transactions en attente : " . $pending_count . "</li>";

// Vérifier les données de test
echo "<h2>Données de test dans les tables :</h2>";

// 1. Vérifier pending_transactions
$stmt = $pdo->query("
    SELECT pt.*, u.nom, u.prenom, p.nom as plan_nom 
    FROM pending_transactions pt
    LEFT JOIN users u ON pt.user_id = u.id
    LEFT JOIN plans p ON pt.plan_id = p.id
    ORDER BY pt.id DESC
    LIMIT 5
");
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Dernières transactions pending_transactions :</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Code</th><th>Montant</th><th>Status</th><th>Utilisateur</th><th>Plan</th></tr>";
foreach ($transactions as $t) {
    echo "<tr>";
    echo "<td>" . ($t['id'] ?? 'N/A') . "</td>";
    echo "<td>" . ($t['transaction_code'] ?? 'N/A') . "</td>";
    echo "<td>" . ($t['montant'] ?? 'N/A') . "</td>";
    echo "<td>" . ($t['status'] ?? 'N/A') . "</td>";
    echo "<td>" . (($t['prenom'] ?? '') . ' ' . ($t['nom'] ?? '')) . "</td>";
    echo "<td>" . ($t['plan_nom'] ?? 'N/A') . "</td>";
    echo "</tr>";
}
echo "</table>";

// 2. Vérifier user_plans
$stmt = $pdo->query("SELECT COUNT(*) FROM user_plans");
echo "<li>Total user_plans : " . $stmt->fetchColumn() . "</li>";

// 3. Vérifier wallets
$stmt = $pdo->query("SELECT COUNT(*) FROM wallets");
echo "<li>Total wallets : " . $stmt->fetchColumn() . "</li>";

echo "</ul>";

// Tester les fonctions individuellement
echo "<h2>Test des fonctions :</h2>";

// Tester la fonction getPendingTransactions
try {
    $pending = getPendingTransactions();
    echo "<div style='color: green;'>✓ Fonction getPendingTransactions() : OK (" . count($pending) . " résultats)</div>";
} catch (Exception $e) {
    echo "<div style='color: red;'>✗ Fonction getPendingTransactions() : " . $e->getMessage() . "</div>";
}

// Tester la fonction de validation manuellement
echo "<h3>Test manuel de validation :</h3>";
echo "<form method='get' action=''>";
echo "<input type='hidden' name='test' value='1'>";
echo "ID de transaction à tester : <input type='number' name='test_id' value=''> ";
echo "<button type='submit'>Tester validation</button>";
echo "</form>";

if (isset($_GET['test']) && !empty($_GET['test_id'])) {
    $test_id = $_GET['test_id'];
    
    echo "<h4>Test de validation pour ID = $test_id</h4>";
    
    // Tester validateTransaction
    try {
        $result = validateTransaction($test_id);
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
        echo "Résultat : " . $result;
        echo "</div>";
    } catch (Exception $e) {
        echo "<div style='color: red;'>Erreur : " . $e->getMessage() . "</div>";
    }
}

// Test de connexion et structure
echo "<h2>Vérification de la structure :</h2>";

$tables = ['pending_transactions', 'plans', 'users', 'wallets', 'transactions', 'user_plans'];
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<div style='color: green;'>✓ Table $table existe (" . count($columns) . " colonnes)</div>";
    } catch (Exception $e) {
        echo "<div style='color: red;'>✗ Table $table : " . $e->getMessage() . "</div>";
    }
}

// Afficher toutes les erreurs PHP
echo "<h2>Logs d'erreurs PHP :</h2>";
$errors = error_get_last();
if ($errors) {
    echo "<pre style='color: red;'>";
    print_r($errors);
    echo "</pre>";
} else {
    echo "<div style='color: green;'>Aucune erreur PHP récente</div>";
}
?>