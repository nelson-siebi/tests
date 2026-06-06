<?php
/**
 * Page de diagnostic des données
 * Affiche les vraies données de la base pour vérifier les statistiques
 */

require_once 'config/database.php';

// Vérifier l'authentification
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

echo "<style>
body { font-family: monospace; padding: 20px; background: #1a202c; color: #e2e8f0; }
table { width: 100%; border-collapse: collapse; margin: 20px 0; background: #2d3748; }
th, td { padding: 12px; text-left; border: 1px solid #4a5568; }
th { background: #374151; color: #60a5fa; }
h2 { color: #60a5fa; border-bottom: 2px solid #60a5fa; padding-bottom: 10px; }
.success { color: #10b981; }
.error { color: #ef4444; }
.warning { color: #f59e0b; }
.info { color: #3b82f6; }
</style>";

echo "<h1>🔍 Diagnostic des Données - Dashboard Admin</h1>";
echo "<p>Généré le : " . date('d/m/Y H:i:s') . "</p>";

// 1. TRANSACTIONS
echo "<h2>1. Analyse des Transactions</h2>";

$all_transactions = $pdo->query("
    SELECT 
        type,
        statut,
        source,
        COUNT(*) as count,
        SUM(montant) as total_montant
    FROM transactions
    GROUP BY type, statut, source
    ORDER BY type, statut, source
")->fetchAll(PDO::FETCH_ASSOC);

echo "<table>";
echo "<tr><th>Type</th><th>Statut</th><th>Source</th><th>Nombre</th><th>Total (FCFA)</th></tr>";
foreach ($all_transactions as $row) {
    echo "<tr>";
    echo "<td>" . ($row['type'] ?? 'NULL') . "</td>";
    echo "<td class='" . ($row['statut'] == 'success' ? 'success' : ($row['statut'] == 'failed' ? 'error' : 'warning')) . "'>" . ($row['statut'] ?? 'NULL') . "</td>";
    echo "<td>" . ($row['source'] ?? 'NULL') . "</td>";
    echo "<td>" . $row['count'] . "</td>";
    echo "<td>" . number_format($row['total_montant'], 0) . "</td>";
    echo "</tr>";
}
echo "</table>";

// 2. STATISTIQUES CALCULÉES
echo "<h2>2. Statistiques Calculées (comme dans le dashboard)</h2>";

$stats_queries = [
    'Total Dépôts (success)' => "SELECT COALESCE(SUM(montant), 0) FROM transactions WHERE type = 'depot' AND statut = 'success'",
    'Total Retraits (success)' => "SELECT COALESCE(SUM(montant), 0) FROM transactions WHERE type = 'retrait' AND statut = 'success'",
    'Total Utilisateurs' => "SELECT COUNT(*) FROM users",
    'Investissements Actifs' => "SELECT COUNT(*) FROM user_plans WHERE statut = 'active'",
    'KYC Pending' => "SELECT COUNT(*) FROM pending_transactions WHERE status = 'pending'",
    'Retraits en attente' => "SELECT COUNT(*) FROM transactions WHERE type = 'retrait' AND statut = 'attente'"
];

echo "<table>";
echo "<tr><th>Statistique</th><th>Valeur</th><th>Requête SQL</th></tr>";
foreach ($stats_queries as $label => $query) {
    try {
        $value = $pdo->query($query)->fetchColumn();
        echo "<tr>";
        echo "<td><strong>$label</strong></td>";
        echo "<td class='success'>" . number_format($value, 0) . "</td>";
        echo "<td style='font-size: 10px;'>" . htmlspecialchars($query) . "</td>";
        echo "</tr>";
    } catch (Exception $e) {
        echo "<tr>";
        echo "<td><strong>$label</strong></td>";
        echo "<td class='error'>ERREUR</td>";
        echo "<td class='error'>" . $e->getMessage() . "</td>";
        echo "</tr>";
    }
}
echo "</table>";

// 3. DERNIÈRES TRANSACTIONS
echo "<h2>3. Dernières 20 Transactions (toutes)</h2>";

$recent = $pdo->query("
    SELECT 
        id,
        user_id,
        type,
        source,
        montant,
        methode,
        statut,
        created_at
    FROM transactions
    ORDER BY created_at DESC
    LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);

echo "<table>";
echo "<tr><th>ID</th><th>User ID</th><th>Type</th><th>Source</th><th>Montant</th><th>Méthode</th><th>Statut</th><th>Date</th></tr>";
foreach ($recent as $row) {
    echo "<tr>";
    echo "<td>#" . $row['id'] . "</td>";
    echo "<td>" . $row['user_id'] . "</td>";
    echo "<td>" . $row['type'] . "</td>";
    echo "<td>" . ($row['source'] ?? 'NULL') . "</td>";
    echo "<td>" . number_format($row['montant'], 0) . " FCFA</td>";
    echo "<td>" . ($row['methode'] ?? 'NULL') . "</td>";
    echo "<td class='" . ($row['statut'] == 'success' ? 'success' : ($row['statut'] == 'failed' ? 'error' : 'warning')) . "'>" . $row['statut'] . "</td>";
    echo "<td>" . date('d/m/Y H:i', strtotime($row['created_at'])) . "</td>";
    echo "</tr>";
}
echo "</table>";

// 4. USER_PLANS
echo "<h2>4. Investissements (user_plans)</h2>";

$plans_stats = $pdo->query("
    SELECT 
        statut,
        COUNT(*) as count,
        SUM(montant_investi) as total_investi
    FROM user_plans
    GROUP BY statut
")->fetchAll(PDO::FETCH_ASSOC);

echo "<table>";
echo "<tr><th>Statut</th><th>Nombre</th><th>Total Investi (FCFA)</th></tr>";
foreach ($plans_stats as $row) {
    echo "<tr>";
    echo "<td>" . $row['statut'] . "</td>";
    echo "<td>" . $row['count'] . "</td>";
    echo "<td>" . number_format($row['total_investi'], 0) . "</td>";
    echo "</tr>";
}
echo "</table>";

// 5. PENDING_TRANSACTIONS
echo "<h2>5. Transactions en Attente (pending_transactions)</h2>";

$pending_stats = $pdo->query("
    SELECT 
        status,
        COUNT(*) as count,
        SUM(montant) as total_montant
    FROM pending_transactions
    GROUP BY status
")->fetchAll(PDO::FETCH_ASSOC);

echo "<table>";
echo "<tr><th>Status</th><th>Nombre</th><th>Total (FCFA)</th></tr>";
foreach ($pending_stats as $row) {
    echo "<tr>";
    echo "<td>" . $row['status'] . "</td>";
    echo "<td>" . $row['count'] . "</td>";
    echo "<td>" . number_format($row['total_montant'], 0) . "</td>";
    echo "</tr>";
}
echo "</table>";

// 6. VÉRIFICATION DES TABLES
echo "<h2>6. Structure des Tables</h2>";

$tables = ['users', 'transactions', 'user_plans', 'pending_transactions', 'plans', 'wallets'];
echo "<table>";
echo "<tr><th>Table</th><th>Nombre de lignes</th><th>Colonnes</th></tr>";
foreach ($tables as $table) {
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        $columns = $pdo->query("SHOW COLUMNS FROM $table")->fetchAll(PDO::FETCH_COLUMN);
        echo "<tr>";
        echo "<td><strong>$table</strong></td>";
        echo "<td class='success'>$count lignes</td>";
        echo "<td style='font-size: 10px;'>" . implode(', ', $columns) . "</td>";
        echo "</tr>";
    } catch (Exception $e) {
        echo "<tr>";
        echo "<td><strong>$table</strong></td>";
        echo "<td class='error'>ERREUR</td>";
        echo "<td class='error'>" . $e->getMessage() . "</td>";
        echo "</tr>";
    }
}
echo "</table>";

// 7. RECOMMANDATIONS
echo "<h2>7. 🎯 Diagnostic et Recommandations</h2>";

$total_deposits_success = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM transactions WHERE type = 'depot' AND statut = 'success'")->fetchColumn();
$total_pending = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM pending_transactions WHERE status = 'pending'")->fetchColumn();
$total_user_plans = $pdo->query("SELECT COALESCE(SUM(montant_investi), 0) FROM user_plans")->fetchColumn();

echo "<div style='background: #2d3748; padding: 20px; border-left: 4px solid #60a5fa; margin: 20px 0;'>";
echo "<h3 class='info'>Analyse :</h3>";
echo "<ul>";
echo "<li><strong>Total Dépôts (success) :</strong> " . number_format($total_deposits_success, 0) . " FCFA</li>";
echo "<li><strong>Total Pending (en attente de validation) :</strong> " . number_format($total_pending, 0) . " FCFA</li>";
echo "<li><strong>Total Investi (user_plans) :</strong> " . number_format($total_user_plans, 0) . " FCFA</li>";
echo "</ul>";

if ($total_deposits_success == 0 && $total_pending > 0) {
    echo "<p class='warning'>⚠️ <strong>PROBLÈME DÉTECTÉ :</strong> Vous avez des transactions en attente mais aucun dépôt avec statut 'success'.</p>";
    echo "<p class='info'>💡 <strong>Solution :</strong> Validez les investissements en attente dans la page KYC pour qu'ils apparaissent dans les statistiques.</p>";
} elseif ($total_deposits_success == 0) {
    echo "<p class='warning'>⚠️ <strong>ATTENTION :</strong> Aucune transaction avec statut 'success' trouvée.</p>";
    echo "<p class='info'>💡 <strong>Explication :</strong> Le dashboard affiche uniquement les transactions avec statut 'success'. Les transactions en attente ou échouées ne sont pas comptées.</p>";
} else {
    echo "<p class='success'>✅ <strong>OK :</strong> Des transactions avec statut 'success' existent dans la base.</p>";
}

echo "</div>";

echo "<hr style='margin: 40px 0; border-color: #4a5568;'>";
echo "<p><a href='?page=dashboard' style='color: #60a5fa;'>← Retour au Dashboard</a> | ";
echo "<a href='?page=kyc' style='color: #10b981;'>Valider des investissements →</a></p>";
?>
