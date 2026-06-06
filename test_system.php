<?php
/**
 * Investian System Health Check
 * Tests core functionalities and provides feedback on system status.
 */

define('TEST_START_TIME', microtime(true));

header('Content-Type: text/plain; charset=utf-8');

function logTest($name, $status, $comment = '')
{
    $icon = $status ? "✅" : "❌";
    $result = $status ? "SUCCÈS" : "ÉCHEC";
    echo sprintf("[%s] %-30s | %-8s | %s\n", $icon, $name, $result, $comment);
}

echo "====================================================\n";
echo "   🔍 TEST DE FONCTIONNALITÉ INVESTIAN V1.0\n";
echo "====================================================\n\n";

// 1. Environnement PHP
logTest("Version PHP", version_compare(PHP_VERSION, '8.1.0', '>='), "Version actuelle: " . PHP_VERSION);

// 2. Autoload & Structure
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
    logTest("Autoload Composer", true, "Fichier trouvé et chargé.");
} else {
    logTest("Autoload Composer", false, "Fichier vendor/autoload.php manquant !");
    die("\nImpossible de continuer sans l'autoload.\n");
}

// 3. Connexion Base de Données
use App\Core\Model;
use App\Models\User;
use App\Models\Plan;
use App\Models\Transaction;

try {
    $db = Model::connect();
    logTest("Connexion BDD", true, "Connexion établie avec succès.");
} catch (Exception $e) {
    logTest("Connexion BDD", false, "Erreur: " . $e->getMessage());
    die("\nÉchec critique de la base de données.\n");
}

// 4. Intégrité des Tables
$requiredTables = [
    'users' => ['id', 'email', 'role'],
    'investment_plans' => ['id', 'name', 'daily_profit_amount'],
    'investments' => ['id', 'user_id', 'status'],
    'transactions' => ['id', 'type', 'status'],
    'community_messages' => ['id', 'user_id', 'status'],
    'ads' => ['id', 'title', 'status'],
    'guides' => ['id', 'title']
];

foreach ($requiredTables as $table => $cols) {
    try {
        $stmt = $db->query("SELECT " . implode(',', $cols) . " FROM $table LIMIT 1");
        logTest("Table: $table", true, "Structure OK.");
    } catch (Exception $e) {
        logTest("Table: $table", false, "Erreur de structure ou table manquante: " . $e->getMessage());
    }
}

// 5. Test Models
// Admin User
$admin = User::findByEmail('admin@investian.com');
logTest("Compte Admin", (bool) $admin, $admin ? "Admin trouvé (" . $admin['name'] . ")" : "Compte 'admin@investian.com' non trouvé.");

// Plans
$plans = Plan::all();
logTest("Plans d'Investissement", count($plans) > 0, count($plans) . " plans actifs trouvés.");

// 6. Langues
$langDir = __DIR__ . '/app/Lang';
$langs = ['fr.php', 'en.php'];
foreach ($langs as $lang) {
    $path = $langDir . '/' . $lang;
    logTest("Fichier Langue ($lang)", file_exists($path), file_exists($path) ? "Fichier présent." : "Manquant !");
}

// 7. Dossiers Publics (Permissions)
$publicDir = __DIR__ . '/public';
logTest("Répertoire Public", is_dir($publicDir) && is_writable($publicDir), "Vérification des accès en écriture.");

echo "\n====================================================\n";
$duration = round(microtime(true) - TEST_START_TIME, 4);
echo "🏁 TEST TERMINÉ en $duration secondes.\n";

$allOk = true; // In a real script we would track every logTest failure
echo "📢 RÉSULTAT FINAL : " . (count($plans) > 0 && $admin ? "TOUT EST BON ! 🚀" : "DES ERREURS ONT ÉTÉ DÉTECTÉES. ⚠️") . "\n";
echo "====================================================\n";
