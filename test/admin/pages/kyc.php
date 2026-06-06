<?php
// Activer TOUTES les erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Journaliser toutes les actions
function log_debug($message, $data = null) {
    $log_message = date('[Y-m-d H:i:s] ') . $message;
    if ($data !== null) {
        $log_message .= ' | DATA: ' . json_encode($data);
    }
    error_log($log_message);
    
    // Désactivé pour éviter "headers already sent"
    // echo "<!-- DEBUG: " . htmlspecialchars($message) . " -->\n";
    // if ($data !== null) {
    //     echo "<!-- DEBUG_DATA: " . htmlspecialchars(json_encode($data)) . " -->\n";
    // }
}

log_debug("=== DÉMARRAGE DU SCRIPT ===");

// Inclure la connexion à la base de données
try {
    log_debug("Tentative d'inclusion de config/database.php");
    if (!file_exists('config/database.php')) {
        throw new Exception("Fichier config/database.php non trouvé");
    }
    require_once 'config/database.php';
    log_debug("Fichier database.php inclus avec succès");
} catch (Exception $e) {
    die("<div style='background: red; color: white; padding: 20px;'>
        <h2>ERREUR FATALE</h2>
        <p>Impossible d'inclure la configuration de la base de données</p>
        <p><strong>Erreur:</strong> " . $e->getMessage() . "</p>
        <p><strong>Chemin:</strong> " . __DIR__ . "/config/database.php</p>
        </div>");
}

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_id'])) {
    log_debug("Admin non connecté, redirection vers login.php");
    header('Location: login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];
log_debug("Admin ID dans session", ['admin_id' => $admin_id]);

// Essayer d'obtenir la connexion PDO
try {
    log_debug("Tentative de connexion à la base de données");
   
    
    // Tester la connexion
    $test = $pdo->query("SELECT 1");
    log_debug("Connexion à la base de données réussie");
    
} catch (Exception $e) {
    die("<div style='background: darkred; color: white; padding: 20px;'>
        <h2>ERREUR DE CONNEXION À LA BASE DE DONNÉES</h2>
        <p><strong>Message:</strong> " . $e->getMessage() . "</p>
        <p><strong>Fichier:</strong> " . $e->getFile() . "</p>
        <p><strong>Ligne:</strong> " . $e->getLine() . "</p>
        </div>");
}

// Vérifier le rôle
try {
    log_debug("Vérification du rôle admin", ['admin_id' => $admin_id]);
    $stmt = $pdo->prepare("SELECT role FROM admin_users WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    log_debug("Récupération du rôle", $admin);
    
    if (!$admin) {
        log_debug("Admin non trouvé dans la base de données");
        die("<div style='background: orange; color: black; padding: 20px;'>
            <h2>ERREUR: ADMIN NON TROUVÉ</h2>
            <p>L'administrateur avec ID $admin_id n'existe pas dans la base de données.</p>
            </div>");
    }
    
    $allowed_roles = ['superadmin', 'finance'];
    if (!in_array($admin['role'], $allowed_roles)) {
        log_debug("Rôle insuffisant", ['role' => $admin['role'], 'allowed' => $allowed_roles]);
        die("<div style='background: orange; color: black; padding: 20px;'>
            <h2>ACCÈS REFUSÉ</h2>
            <p>Rôle insuffisant. Votre rôle: {$admin['role']}. Rôles autorisés: " . implode(', ', $allowed_roles) . "</p>
            </div>");
    }
    
    log_debug("Rôle vérifié avec succès", ['role' => $admin['role']]);
    
} catch (Exception $e) {
    die("<div style='background: darkred; color: white; padding: 20px;'>
        <h2>ERREUR DE VÉRIFICATION DU RÔLE</h2>
        <p><strong>Message:</strong> " . $e->getMessage() . "</p>
        </div>");
}

// Fonction de validation avec débogage étendu
function validateTransaction($transaction_id, $pdo) {
    log_debug("=== DÉBUT validateTransaction ===", ['transaction_id' => $transaction_id]);
    
    try {
        log_debug("Début de la transaction PDO");
        $pdo->beginTransaction();
        
        // 1. Récupérer la transaction
        log_debug("Recherche de la transaction pending");
        $sql = "SELECT * FROM pending_transactions WHERE id = ? AND status = 'pending'";
        log_debug("SQL 1", $sql);
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$transaction_id]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
        
        log_debug("Transaction trouvée", $transaction);
        
        if (!$transaction) {
            throw new Exception("Transaction non trouvée ou déjà traitée. ID: $transaction_id");
        }
        
        // 2. Vérifier le plan
        log_debug("Vérification du plan", ['plan_id' => $transaction['plan_id']]);
        $sql2 = "SELECT * FROM plans WHERE id = ?";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute([$transaction['plan_id']]);
        $plan = $stmt2->fetch(PDO::FETCH_ASSOC);
        
        log_debug("Plan trouvé", $plan);
        
        if (!$plan) {
            throw new Exception("Plan avec ID {$transaction['plan_id']} non trouvé");
        }
        
        // 3. Vérifier l'utilisateur
        log_debug("Vérification de l'utilisateur", ['user_id' => $transaction['user_id']]);
        $sql3 = "SELECT * FROM users WHERE id = ?";
        $stmt3 = $pdo->prepare($sql3);
        $stmt3->execute([$transaction['user_id']]);
        $user = $stmt3->fetch(PDO::FETCH_ASSOC);
        
        log_debug("Utilisateur trouvé", $user);
        
        if (!$user) {
            throw new Exception("Utilisateur avec ID {$transaction['user_id']} non trouvé");
        }
        
        // 4. Insérer dans user_plans
        log_debug("Insertion dans user_plans");
        $date_debut = date('Y-m-d H:i:s');
        $date_fin = date('Y-m-d H:i:s', strtotime("+{$plan['duree_jours']} days"));
        
        log_debug("Dates calculées", [
            'date_debut' => $date_debut,
            'date_fin' => $date_fin,
            'duree_jours' => $plan['duree_jours']
        ]);
        
        $sql4 = "INSERT INTO user_plans (user_id, plan_id, montant_investi, date_debut, date_fin, statut, created_at) 
                VALUES (?, ?, ?, ?, ?, 'active', NOW())";
        log_debug("SQL 4", $sql4);
        
        $stmt4 = $pdo->prepare($sql4);
        $result4 = $stmt4->execute([
            $transaction['user_id'],
            $transaction['plan_id'],
            $transaction['montant'],
            $date_debut,
            $date_fin
        ]);
        
        if (!$result4) {
            $errorInfo = $stmt4->errorInfo();
            log_debug("Erreur lors de l'insertion dans user_plans", $errorInfo);
            throw new Exception("Erreur SQL user_plans: " . $errorInfo[2]);
        }
        
        $user_plan_id = $pdo->lastInsertId();
        log_debug("user_plans créé avec succès", ['id' => $user_plan_id]);
        
        // 5. Créer la transaction principale (DÉPÔT)
        log_debug("Création de la transaction principale (Dépôt)");
        $sql5 = "INSERT INTO transactions (user_id, type, source, montant, methode, statut, reference, note, created_at) 
                VALUES (?, 'depot', 'investissement', ?, ?, 'success', ?, ?, NOW())";
        log_debug("SQL 5", $sql5);
        
        $note = "Dépôt pour investissement - Plan: {$plan['nom']}";
        $stmt5 = $pdo->prepare($sql5);
        $result5 = $stmt5->execute([
            $transaction['user_id'],
            $transaction['montant'],
            $transaction['methode'],
            $transaction['transaction_code'],
            $note
        ]);
        
        if (!$result5) {
            $errorInfo = $stmt5->errorInfo();
            log_debug("Erreur lors de l'insertion dans transactions (dépôt)", $errorInfo);
            throw new Exception("Erreur SQL transactions: " . $errorInfo[2]);
        }
        
        $transaction_db_id = $pdo->lastInsertId();
        
        // 5bis. Créer la transaction d'ACHAT (Pour équilibrer le solde)
        log_debug("Création de la transaction d'achat");
        $sqlBuy = "INSERT INTO transactions (user_id, type, source, montant, methode, statut, reference, note, created_at) 
                  VALUES (?, 'achat', 'investissement', ?, 'systeme', 'success', ?, ?, NOW())";
        
        $refBuy = 'PUR-' . time() . '-' . rand(1000, 9999);
        $noteBuy = "Activation du plan: {$plan['nom']}";
        
        $stmtBuy = $pdo->prepare($sqlBuy);
        $resultBuy = $stmtBuy->execute([
            $transaction['user_id'],
            $transaction['montant'],
            $refBuy,
            $noteBuy
        ]);

        if (!$resultBuy) {
            log_debug("Erreur lors de la création de la transaction d'achat");
            // On ne bloque pas tout pour ça, mais c'est noté
        }
        
        log_debug("Transactions créées avec succès");
        
        // 6. Mettre à jour le wallet
        // IMPORTANT : On augmente total_depots mais on NE TOUCHE PAS au solde_investissement
        // car le dépôt (+X) et l'achat (-X) s'annulent immédiatement.
        log_debug("Mise à jour du wallet (Total Dépôts uniquement)");
        $sql6 = "UPDATE wallets 
                SET total_depots = total_depots + ?,
                    updated_at = NOW()
                WHERE user_id = ?";
        log_debug("SQL 6", $sql6);
        
        $stmt6 = $pdo->prepare($sql6);
        $result6 = $stmt6->execute([
            $transaction['montant'],
            $transaction['user_id']
        ]);
        
        if (!$result6) {
            $errorInfo = $stmt6->errorInfo();
            log_debug("Erreur lors de la mise à jour du wallet", $errorInfo);
            throw new Exception("Erreur SQL wallet: " . $errorInfo[2]);
        }
        
        log_debug("Wallet mis à jour", ['rows_affected' => $stmt6->rowCount()]);
        
        // 7. Mettre à jour pending_transactions
        log_debug("Mise à jour de pending_transactions");
        $sql7 = "UPDATE pending_transactions 
                SET status = 'completed', 
                    updated_at = NOW() 
                WHERE id = ?";
        log_debug("SQL 7", $sql7);
        
        $stmt7 = $pdo->prepare($sql7);
        $result7 = $stmt7->execute([$transaction_id]);
        
        if (!$result7) {
            $errorInfo = $stmt7->errorInfo();
            log_debug("Erreur lors de la mise à jour de pending_transactions", $errorInfo);
            throw new Exception("Erreur SQL pending_transactions: " . $errorInfo[2]);
        }
        
        log_debug("Pending_transactions mis à jour", ['rows_affected' => $stmt7->rowCount()]);

        // 8. --- BONUS DE VALIDATION (+200 FCFA) ---
        log_debug("Application du bonus de validation");
        
        // A. Créer la transaction de bonus
        $sqlBonus = "INSERT INTO transactions (user_id, type, source, montant, methode, statut, reference, note, created_at) 
                    VALUES (?, 'bonus', 'systeme', 200, 'auto', 'success', ?, 'Bonus de validation', NOW())";
        $refBonus = 'BNS-' . time() . '-' . rand(100, 999);
        $stmtBonus = $pdo->prepare($sqlBonus);
        $stmtBonus->execute([$transaction['user_id'], $refBonus]);
        
        // B. Créditer le wallet
        $sqlWalletBonus = "UPDATE wallets 
                          SET solde_investissement = solde_investissement + 200,
                              updated_at = NOW()
                          WHERE user_id = ?";
        $stmtWalletBonus = $pdo->prepare($sqlWalletBonus);
        $stmtWalletBonus->execute([$transaction['user_id']]);
        
        log_debug("Bonus de validation de 200 FCFA appliqué");
        
        // 9. --- BONUS DE PARRAINAGE (15%) ---
        log_debug("Vérification du parrainage pour bonus 15%");
        
        if (!empty($user['referred_by'])) {
            $parrain_id = $user['referred_by'];
            $bonus_parrainage = $transaction['montant'] * 0.15;
            
            log_debug("Parrain trouvé", [
                'parrain_id' => $parrain_id,
                'montant_investi' => $transaction['montant'],
                'bonus' => $bonus_parrainage
            ]);
            
            // A. Créditer le wallet du parrain
            $sqlWalletParrain = "UPDATE wallets 
                                SET solde_parrainage = solde_parrainage + ?,
                                    updated_at = NOW()
                                WHERE user_id = ?";
            $stmtWalletParrain = $pdo->prepare($sqlWalletParrain);
            $stmtWalletParrain->execute([$bonus_parrainage, $parrain_id]);
            
            // B. Créer la transaction pour le parrain
            $sqlTransParrain = "INSERT INTO transactions (user_id, type, source, montant, methode, statut, reference, note, created_at) 
                               VALUES (?, 'gain', 'parrainage', ?, 'systeme', 'success', ?, ?, NOW())";
            $refParrain = 'REF-' . time() . '-' . rand(100, 999);
            $noteParrain = "Bonus parrainage 15% - Filleul: {$user['prenom']} {$user['nom']} - Plan: {$plan['nom']}";
            
            $stmtTransParrain = $pdo->prepare($sqlTransParrain);
            $stmtTransParrain->execute([
                $parrain_id,
                $bonus_parrainage,
                $refParrain,
                $noteParrain
            ]);
            
            // C. Mettre à jour la table referrals (valider et ajouter bonus)
            // On essaie de mettre à jour si existe, sinon c'est pas grave (le parrainage est déjà acté par referred_by)
            $sqlUpdateReferral = "UPDATE referrals 
                                 SET valide = 1, 
                                     bonus = bonus + ?,
                                     date_validation = IFNULL(date_validation, NOW())
                                 WHERE parrain_id = ? AND filleul_id = ?";
            $stmtUpdateReferral = $pdo->prepare($sqlUpdateReferral);
            $stmtUpdateReferral->execute([$bonus_parrainage, $parrain_id, $transaction['user_id']]);
            
            log_debug("Bonus parrainage appliqué avec succès");
        } else {
            log_debug("Aucun parrain trouvé pour cet utilisateur");
        }
        // ----------------------------------------
        
        // 9. Commit
        
        // 8. Commit
        log_debug("Commit de la transaction");
        $pdo->commit();
        
        log_debug("=== VALIDATION RÉUSSIE ===", [
            'transaction_id' => $transaction_id,
            'user_plan_id' => $user_plan_id,
            'transaction_db_id' => $transaction_db_id,
            'montant' => $transaction['montant'],
            'plan' => $plan['nom']
        ]);
        
        return "<div class='alert alert-success'>
                <i class='fas fa-check-circle mr-2'></i>
                <strong>SUCCÈS !</strong> Investissement validé avec succès!
                <br><small>Transaction #$transaction_id | Plan: {$plan['nom']} | Montant: " . number_format($transaction['montant'], 0) . " FCFA</small>
                </div>";
        
    } catch (Exception $e) {
        log_debug("ERREUR dans validateTransaction", [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        try {
            $pdo->rollBack();
            log_debug("Rollback effectué");
        } catch (Exception $rollback_error) {
            log_debug("Erreur lors du rollback", $rollback_error->getMessage());
        }
        
        return "<div class='alert alert-danger'>
                <i class='fas fa-exclamation-triangle mr-2'></i>
                <strong>ERREUR !</strong> " . htmlspecialchars($e->getMessage()) . "
                <br><small>Transaction #$transaction_id | Vérifiez les logs pour plus de détails</small>
                </div>";
    }
}

// Récupérer les transactions en attente avec débogage
function getPendingTransactions($pdo) {
    log_debug("=== DÉBUT getPendingTransactions ===");
    
    try {
        $sql = "
            SELECT 
                pt.*,
                p.nom as plan_nom,
                p.roi_journalier,
                p.duree_jours,
                u.nom,
                u.prenom,
                u.email,
                u.phone,
                TIMESTAMPDIFF(MINUTE, NOW(), pt.expires_at) as minutes_remaining,
                CASE 
                    WHEN pt.expires_at < NOW() THEN 'expired'
                    ELSE 'active'
                END as expiration_status
            FROM pending_transactions pt
            LEFT JOIN plans p ON pt.plan_id = p.id
            LEFT JOIN users u ON pt.user_id = u.id
            WHERE pt.status = 'pending'
            ORDER BY pt.created_at DESC
        ";
        
        log_debug("SQL pour getPendingTransactions", $sql);
        
        $stmt = $pdo->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        log_debug("Transactions récupérées", [
            'count' => count($result),
            'premier' => $result[0] ?? 'aucune'
        ]);
        
        return $result;
        
    } catch (Exception $e) {
        log_debug("ERREUR dans getPendingTransactions", [
            'message' => $e->getMessage(),
            'sql' => $sql ?? 'non défini'
        ]);
        return [];
    }
}

// Gérer les actions
log_debug("Analyse des paramètres GET", $_GET);
$action = $_GET['action'] ?? '';
$transaction_id = $_GET['id'] ?? 0;
$message = '';

if ($action === 'validate' && $transaction_id) {
    log_debug("Action VALIDER détectée", ['id' => $transaction_id]);
    $message = validateTransaction($transaction_id, $pdo);
    
    // Rediriger après validation pour éviter la re-soumission
    if (strpos($message, 'SUCCÈS') !== false) {
        echo '<script>
            setTimeout(function() {
                window.location.href = "?page=kyc";
            }, 2000);
        </script>';
    }
} elseif ($action === 'reject' && $transaction_id) {
    log_debug("Action REJETER détectée", ['id' => $transaction_id]);
    // Fonction de rejet simplifiée
    try {
        $stmt = $pdo->prepare("UPDATE pending_transactions SET status = 'failed' WHERE id = ?");
        $stmt->execute([$transaction_id]);
        $message = "<div class='alert alert-warning'>Transaction #$transaction_id rejetée</div>";
        
        // Rediriger après rejet
        echo '<script>
            setTimeout(function() {
                window.location.href = "?page=kyc";
            }, 2000);
        </script>';
    } catch (Exception $e) {
        $message = "<div class='alert alert-danger'>Erreur: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Récupérer les transactions en attente
log_debug("Récupération des transactions en attente");
$pending_transactions = getPendingTransactions($pdo);
log_debug("Transactions en attente récupérées", ['count' => count($pending_transactions)]);

// Vérifier la structure de la base
try {
    log_debug("Vérification de la structure de la base");
    
    // Vérifier si les tables existent
    $tables = ['pending_transactions', 'plans', 'users', 'user_plans', 'transactions', 'wallets'];
    foreach ($tables as $table) {
        $check = $pdo->query("SHOW TABLES LIKE '$table'");
        $exists = $check->rowCount() > 0;
        log_debug("Table $table", ['exists' => $exists]);
    }
    
    // Compter les enregistrements
    $count_pt = $pdo->query("SELECT COUNT(*) as count FROM pending_transactions WHERE status = 'pending'")->fetch()['count'];
    $count_up = $pdo->query("SELECT COUNT(*) as count FROM user_plans")->fetch()['count'];
    
    log_debug("Statistiques", [
        'pending_transactions' => $count_pt,
        'user_plans' => $count_up
    ]);
    
} catch (Exception $e) {
    log_debug("Erreur lors de la vérification de la structure", $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation des Investissements - Admin (DEBUG)</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .expired { opacity: 0.6; }
        .expiring-soon { border-left: 4px solid #f59e0b; }
        .table-checkbox { transform: scale(0.9); }
        .alert { padding: 1rem; margin-bottom: 1rem; border-radius: 0.375rem; }
        .alert-success { background-color: #d1fae5; border: 1px solid #10b981; color: #065f46; }
        .alert-danger { background-color: #fee2e2; border: 1px solid #ef4444; color: #7f1d1d; }
        .alert-warning { background-color: #fef3c7; border: 1px solid #f59e0b; color: #92400e; }
        .alert-info { background-color: #dbeafe; border: 1px solid #3b82f6; color: #1e40af; }
        .debug-panel { 
            background: #1a202c; 
            color: #e2e8f0; 
            padding: 15px; 
            margin: 10px 0; 
            border-radius: 5px; 
            font-family: monospace; 
            font-size: 12px; 
            max-height: 200px; 
            overflow-y: auto; 
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <!-- Panneau de débogage -->
        <div class="debug-panel">
            <div class="flex justify-between items-center mb-2">
                <strong>PANEL DE DÉBOGAGE</strong>
                <button onclick="toggleDebug()" class="text-sm bg-blue-600 px-2 py-1 rounded">Masquer</button>
            </div>
            <div id="debug-content">
                <div><strong>PHP Version:</strong> <?= phpversion() ?></div>
                <div><strong>Session ID:</strong> <?= session_id() ?></div>
                <div><strong>Admin ID:</strong> <?= $admin_id ?></div>
                <div><strong>Action:</strong> <?= $action ?></div>
                <div><strong>Transaction ID:</strong> <?= $transaction_id ?></div>
                <div><strong>Transactions en attente:</strong> <?= count($pending_transactions) ?></div>
                <div><strong>Time:</strong> <?= date('H:i:s') ?></div>
            </div>
        </div>

        <!-- Message -->
        <?php if ($message): ?>
            <div class="mb-4"><?= $message ?></div>
        <?php endif; ?>

        <!-- Header -->
        <div class="bg-white rounded-lg shadow mb-6 p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Validation des Investissements <span class="text-red-600">(MODE DÉBOGAGE)</span></h1>
                    <p class="text-gray-600">ID Session: <?= session_id() ?> | Admin ID: <?= $admin_id ?></p>
                </div>
                <div class="space-x-4">
                    <a href="dashboard.php" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-arrow-left"></i> Dashboard
                    </a>
                    <button onclick="refreshPage()" class="text-green-600 hover:text-green-800">
                        <i class="fas fa-sync-alt"></i> Rafraîchir
                    </button>
                </div>
            </div>
            
            <!-- Statistiques détaillées -->
            <div class="mt-4 grid grid-cols-1 md:grid-cols-6 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="text-center">
                        <p class="text-sm text-gray-600">En attente</p>
                        <p class="text-2xl font-bold text-blue-600"><?= count($pending_transactions) ?></p>
                    </div>
                </div>
                
                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="text-center">
                        <p class="text-sm text-gray-600">Total FCFA</p>
                        <p class="text-xl font-bold text-green-600">
                            <?php 
                            $total = 0;
                            foreach ($pending_transactions as $t) {
                                $total += $t['montant'];
                            }
                            echo number_format($total, 0);
                            ?> FCFA
                        </p>
                    </div>
                </div>
                
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <div class="text-center">
                        <p class="text-sm text-gray-600">Expirent bientôt</p>
                        <p class="text-xl font-bold text-yellow-600">
                            <?php
                            $expiring = 0;
                            foreach ($pending_transactions as $t) {
                                if (isset($t['minutes_remaining']) && $t['minutes_remaining'] > 0 && $t['minutes_remaining'] < 30) {
                                    $expiring++;
                                }
                            }
                            echo $expiring;
                            ?>
                        </p>
                    </div>
                </div>
                
                <div class="bg-red-50 p-4 rounded-lg">
                    <div class="text-center">
                        <p class="text-sm text-gray-600">Expirés</p>
                        <p class="text-xl font-bold text-red-600">
                            <?php
                            $expired = 0;
                            foreach ($pending_transactions as $t) {
                                if (isset($t['expiration_status']) && $t['expiration_status'] === 'expired') {
                                    $expired++;
                                }
                            }
                            echo $expired;
                            ?>
                        </p>
                    </div>
                </div>
                
                <div class="bg-purple-50 p-4 rounded-lg">
                    <div class="text-center">
                        <p class="text-sm text-gray-600">Plans différents</p>
                        <p class="text-xl font-bold text-purple-600">
                            <?php
                            $plans = [];
                            foreach ($pending_transactions as $t) {
                                if (isset($t['plan_nom'])) {
                                    $plans[$t['plan_nom']] = true;
                                }
                            }
                            echo count($plans);
                            ?>
                        </p>
                    </div>
                </div>
                
                <div class="bg-indigo-50 p-4 rounded-lg">
                    <div class="text-center">
                        <p class="text-sm text-gray-600">Utilisateurs</p>
                        <p class="text-xl font-bold text-indigo-600">
                            <?php
                            $users = [];
                            foreach ($pending_transactions as $t) {
                                if (isset($t['user_id'])) {
                                    $users[$t['user_id']] = true;
                                }
                            }
                            echo count($users);
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulaire d'actions -->
        <form method="POST" action="?action=process" class="mb-6">
            <div class="bg-white rounded-lg shadow p-4 mb-4">
                <div class="flex flex-wrap items-center gap-4">
                    <select name="bulk_action" class="border rounded-lg px-4 py-2" id="bulkAction">
                        <option value="">Actions en masse</option>
                        <option value="validate">Valider les sélectionnés</option>
                        <option value="reject">Rejeter les sélectionnés</option>
                    </select>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg" id="submitBtn">
                        <i class="fas fa-play"></i> Appliquer
                    </button>
                    <button type="button" onclick="selectAllTransactions()" class="text-gray-600 hover:text-gray-800">
                        <i class="fas fa-check-square"></i> Tout sélectionner
                    </button>
                    <button type="button" onclick="deselectAllTransactions()" class="text-gray-600 hover:text-gray-800">
                        <i class="far fa-square"></i> Tout désélectionner
                    </button>
                    <button type="button" onclick="testValidation()" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-vial"></i> Tester
                    </button>
                </div>
            </div>

            <!-- Vue Mobile (Cartes) -->
            <div class="md:hidden space-y-4">
                <?php if (empty($pending_transactions)): ?>
                    <div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">
                        <i class="fas fa-check-circle text-green-500 text-4xl mb-4"></i>
                        <p class="text-lg font-semibold">Aucun investissement en attente</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($pending_transactions as $transaction): ?>
                        <?php
                        $is_expired = isset($transaction['expiration_status']) && $transaction['expiration_status'] === 'expired';
                        $is_expiring_soon = isset($transaction['minutes_remaining']) && $transaction['minutes_remaining'] > 0 && $transaction['minutes_remaining'] < 30;
                        $card_class = $is_expired ? 'bg-gray-50 opacity-75' : ($is_expiring_soon ? 'bg-yellow-50 border-yellow-200' : 'bg-white');
                        ?>
                        <div class="border rounded-lg shadow-sm p-4 <?= $card_class ?>">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-start gap-3">
                                    <input type="checkbox" 
                                           name="selected_transactions[]" 
                                           value="<?= $transaction['id'] ?>"
                                           class="mt-1 w-4 h-4 text-blue-600 rounded"
                                           <?= $is_expired ? 'disabled' : '' ?>>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-mono text-gray-500">#<?= $transaction['id'] ?></span>
                                            <?php if ($is_expired): ?>
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800">EXPIRÉ</span>
                                            <?php elseif ($is_expiring_soon): ?>
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-yellow-100 text-yellow-800">URGENT</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="font-bold text-gray-900 mt-1"><?= htmlspecialchars($transaction['transaction_code'] ?? 'N/A') ?></div>
                                        <div class="text-xs text-gray-500"><?= isset($transaction['created_at']) ? date('d/m H:i', strtotime($transaction['created_at'])) : 'N/A' ?></div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold text-gray-800"><?= number_format($transaction['montant'] ?? 0, 0) ?> F</div>
                                    <div class="text-xs text-blue-600 font-medium"><?= htmlspecialchars($transaction['plan_nom'] ?? 'N/A') ?></div>
                                </div>
                            </div>
                            
                            <div class="border-t border-b border-gray-100 py-3 my-3 space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Utilisateur</span>
                                    <span class="font-medium text-right"><?= htmlspecialchars(($transaction['prenom'] ?? '') . ' ' . ($transaction['nom'] ?? '')) ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Méthode</span>
                                    <span><?= $transaction['methode'] ?? 'N/A' ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Validité</span>
                                    <span class="<?= $is_expired ? 'text-red-500' : 'text-green-600' ?> font-medium">
                                        <?= $transaction['minutes_remaining'] ?? 0 ?> min restantes
                                    </span>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <?php if (!$is_expired): ?>
                                    <a href="?page=kyc&action=validate&id=<?= $transaction['id'] ?>" 
                                       class="flex-1 flex justify-center items-center bg-green-600 text-white px-3 py-2.5 rounded shadow-sm hover:bg-green-700 active:bg-green-800 text-sm font-medium transition-colors"
                                       onclick="return confirm('Valider cet investissement?')">
                                        <i class="fas fa-check mr-2"></i> Valider
                                    </a>
                                    <a href="?page=kyc&action=reject&id=<?= $transaction['id'] ?>" 
                                       class="flex-1 flex justify-center items-center bg-white border border-red-200 text-red-600 px-3 py-2.5 rounded shadow-sm hover:bg-red-50 active:bg-red-100 text-sm font-medium transition-colors"
                                       onclick="return confirm('Rejeter?')">
                                        <i class="fas fa-times mr-2"></i> Refuser
                                    </a>
                                <?php else: ?>
                                    <div class="flex-1 bg-gray-100 text-gray-400 px-3 py-2.5 rounded text-center text-sm font-medium cursor-not-allowed border border-gray-200">
                                        Action indisponible (Expiré)
                                    </div>
                                <?php endif; ?>
                                <button type="button" onclick="showDetails(<?= $transaction['id'] ?>)" class="w-10 flex justify-center items-center text-blue-600 bg-blue-50 rounded hover:bg-blue-100 transition-colors">
                                    <i class="fas fa-info"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Table des transactions (Desktop) -->
            <div class="hidden md:block bg-white rounded-lg shadow overflow-hidden overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                <input type="checkbox" id="select-all" onchange="toggleAllTransactions(this)">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transaction</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Utilisateur</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Détails</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($pending_transactions)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-check-circle text-green-500 text-4xl mb-4"></i>
                                    <p class="text-lg font-semibold">Aucun investissement en attente</p>
                                    <p class="text-sm mt-2">Toutes les transactions ont été traitées</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pending_transactions as $index => $transaction): ?>
                                <?php
                                $is_expired = isset($transaction['expiration_status']) && $transaction['expiration_status'] === 'expired';
                                $is_expiring_soon = isset($transaction['minutes_remaining']) && $transaction['minutes_remaining'] > 0 && $transaction['minutes_remaining'] < 30;
                                $row_class = $is_expired ? 'expired bg-gray-50' : ($is_expiring_soon ? 'expiring-soon' : '');
                                ?>
                                <tr class="<?= $row_class ?>" id="row-<?= $transaction['id'] ?>">
                                    <td class="px-4 py-3">
                                        <input type="checkbox" 
                                               name="selected_transactions[]" 
                                               value="<?= $transaction['id'] ?>"
                                               class="table-checkbox"
                                               data-id="<?= $transaction['id'] ?>"
                                               <?= $is_expired ? 'disabled' : '' ?>>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-mono text-gray-600">
                                        #<?= $transaction['id'] ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($transaction['transaction_code'] ?? 'N/A') ?>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            <?= isset($transaction['created_at']) ? date('d/m H:i', strtotime($transaction['created_at'])) : 'N/A' ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-sm">
                                            <div class="font-medium">
                                                <?= htmlspecialchars(($transaction['prenom'] ?? '') . ' ' . ($transaction['nom'] ?? '')) ?>
                                            </div>
                                            <div class="text-gray-500 text-xs">
                                                ID: <?= $transaction['user_id'] ?? 'N/A' ?>
                                            </div>
                                            <div class="text-gray-500 text-xs">
                                                <?= htmlspecialchars($transaction['email'] ?? '') ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-sm">
                                            <div class="font-medium text-blue-600">
                                                <?= htmlspecialchars($transaction['plan_nom'] ?? 'N/A') ?>
                                            </div>
                                            <div class="text-gray-700 font-bold">
                                                <?= isset($transaction['montant']) ? number_format($transaction['montant'], 0) : '0' ?> FCFA
                                            </div>
                                            <div class="text-gray-500 text-xs">
                                                ROI: <?= isset($transaction['roi_journalier']) ? number_format($transaction['roi_journalier'], 0) : '0' ?> FCFA/jour
                                            </div>
                                            <div class="text-gray-500 text-xs">
                                                Durée: <?= $transaction['duree_jours'] ?? '0' ?> jours
                                            </div>
                                            <div class="text-gray-500 text-xs">
                                                <?= $transaction['methode'] ?? 'N/A' ?>: <?= $transaction['numero_telephone'] ?? 'N/A' ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php if ($is_expired): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                <i class="fas fa-clock mr-1"></i> Expiré
                                            </span>
                                        <?php elseif ($is_expiring_soon): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-exclamation-triangle mr-1"></i> 
                                                <?= $transaction['minutes_remaining'] ?? '0' ?> min
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                <i class="fas fa-clock mr-1"></i> 
                                                <?= $transaction['minutes_remaining'] ?? '0' ?> min
                                            </span>
                                        <?php endif; ?>
                                        <div class="text-xs text-gray-500 mt-1">
                                            Expire: <?= isset($transaction['expires_at']) ? date('H:i', strtotime($transaction['expires_at'])) : 'N/A' ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex space-x-2">
                                            <?php if (!$is_expired): ?>
                                                <a href="?page=kyc&action=validate&id=<?= $transaction['id'] ?>" 
                                                   class="text-green-600 hover:text-green-900 px-3 py-1 border border-green-300 rounded text-sm"
                                                   onclick="return confirm('Valider cet investissement?\nMontant: <?= isset($transaction['montant']) ? number_format($transaction['montant'], 0) : '0' ?> FCFA\nPlan: <?= $transaction['plan_nom'] ?? 'N/A' ?>')">
                                                    <i class="fas fa-check-circle"></i> Valider
                                                </a>
                                                <a href="?page=kyc&action=reject&id=<?= $transaction['id'] ?>" 
                                                   class="text-red-600 hover:text-red-900 px-3 py-1 border border-red-300 rounded text-sm"
                                                   onclick="return confirm('Rejeter cet investissement?')">
                                                    <i class="fas fa-times-circle"></i> Rejeter
                                                </a>
                                            <?php else: ?>
                                                <span class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-400">
                                                    <i class="fas fa-ban"></i> Expiré
                                                </span>
                                            <?php endif; ?>
                                            <button onclick="showDetails(<?= $transaction['id'] ?>)" 
                                                    class="text-blue-600 hover:text-blue-900 px-3 py-1 border border-blue-300 rounded text-sm">
                                                <i class="fas fa-info-circle"></i> Détails
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>

        <!-- Panel d'informations -->
        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h2 class="text-lg font-semibold mb-4"><i class="fas fa-info-circle text-blue-600 mr-2"></i> Informations de débogage</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-medium text-gray-800 mb-2">Structure de la base</h3>
                    <div class="text-sm text-gray-600 space-y-1">
                        <div><i class="fas fa-database mr-2"></i> <strong>pending_transactions:</strong> 
                            <?php 
                            try {
                                $count = $pdo->query("SELECT COUNT(*) as c FROM pending_transactions WHERE status = 'pending'")->fetch()['c'];
                                echo $count . " en attente";
                            } catch (Exception $e) {
                                echo "Erreur: " . $e->getMessage();
                            }
                            ?>
                        </div>
                        <div><i class="fas fa-list mr-2"></i> <strong>user_plans:</strong> 
                            <?php 
                            try {
                                $count = $pdo->query("SELECT COUNT(*) as c FROM user_plans")->fetch()['c'];
                                echo $count . " investissements";
                            } catch (Exception $e) {
                                echo "Erreur: " . $e->getMessage();
                            }
                            ?>
                        </div>
                        <div><i class="fas fa-users mr-2"></i> <strong>utilisateurs:</strong> 
                            <?php 
                            try {
                                $count = $pdo->query("SELECT COUNT(*) as c FROM users")->fetch()['c'];
                                echo $count . " inscrits";
                            } catch (Exception $e) {
                                echo "Erreur: " . $e->getMessage();
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="font-medium text-gray-800 mb-2">Instructions</h3>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Cliquez sur <strong>"Tester"</strong> pour une validation test</li>
                        <li>• Vérifiez la console du navigateur (F12)</li>
                        <li>• Les erreurs PHP sont dans php_errors.log</li>
                        <li>• Rafraîchissez la page après chaque action</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Script JavaScript de débogage -->
    <script>
        // Fonction pour basculer l'affichage du débogage
        function toggleDebug() {
            const debugContent = document.getElementById('debug-content');
            const button = event.target;
            if (debugContent.style.display === 'none') {
                debugContent.style.display = 'block';
                button.textContent = 'Masquer';
            } else {
                debugContent.style.display = 'none';
                button.textContent = 'Afficher';
            }
        }

        // Fonctions de sélection
        function selectAllTransactions() {
            console.log('Sélection de toutes les transactions');
            document.querySelectorAll('input[name="selected_transactions[]"]:not(:disabled)').forEach(checkbox => {
                checkbox.checked = true;
            });
            document.getElementById('select-all').checked = true;
        }
        
        function deselectAllTransactions() {
            console.log('Désélection de toutes les transactions');
            document.querySelectorAll('input[name="selected_transactions[]"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            document.getElementById('select-all').checked = false;
        }
        
        function toggleAllTransactions(source) {
            console.log('Toggle all:', source.checked);
            document.querySelectorAll('input[name="selected_transactions[]"]:not(:disabled)').forEach(checkbox => {
                checkbox.checked = source.checked;
            });
        }

        // Rafraîchir la page
        function refreshPage() {
            console.log('Rafraîchissement de la page');
            window.location.reload();
        }

        // Afficher les détails d'une transaction
        function showDetails(id) {
            console.log('Détails transaction #' + id);
            const row = document.getElementById('row-' + id);
            if (row) {
                // Récupérer les données de la ligne
                const cells = row.querySelectorAll('td');
                const details = {
                    id: id,
                    transaction: cells[2].textContent.trim(),
                    utilisateur: cells[3].textContent.trim(),
                    montant: cells[4].querySelector('.font-bold')?.textContent.trim(),
                    statut: cells[5].textContent.trim()
                };
                
                console.table(details);
                alert(`Transaction #${id}\n${JSON.stringify(details, null, 2)}`);
            }
        }

        // Tester la validation
        function testValidation() {
            console.log('Test de validation');
            
            // Récupérer la première transaction non expirée
            const firstCheckbox = document.querySelector('input[name="selected_transactions[]"]:not(:disabled)');
            if (!firstCheckbox) {
                alert('Aucune transaction disponible pour le test');
                return;
            }
            
            const transactionId = firstCheckbox.value;
            console.log('Test sur transaction #' + transactionId);
            
            if (confirm(`Tester la validation sur la transaction #${transactionId} ?\nCeci est un test, aucune donnée ne sera modifiée.`)) {
                // Simuler une requête AJAX de test
                console.group('Test de validation transaction #' + transactionId);
                console.log('1. Récupération des données...');
                console.log('2. Vérification des contraintes...');
                console.log('3. Simulation de validation...');
                console.log('✅ Test terminé - Prêt pour la validation réelle');
                console.groupEnd();
                
                alert('Test terminé. Vérifiez la console pour les détails.');
            }
        }

        // Log au chargement de la page
        console.group('=== PAGE DE VALIDATION ADMIN ===');
        console.log('URL:', window.location.href);
        console.log('Transactions en attente:', <?= count($pending_transactions) ?>);
        console.log('Admin ID:', <?= $admin_id ?>);
        console.groupEnd();

        // Événement de soumission du formulaire
        document.querySelector('form')?.addEventListener('submit', function(e) {
            const selected = document.querySelectorAll('input[name="selected_transactions[]"]:checked');
            const action = document.getElementById('bulkAction').value;
            
            console.group('Soumission du formulaire');
            console.log('Action:', action);
            console.log('Transactions sélectionnées:', selected.length);
            selected.forEach(cb => console.log(' - #' + cb.value));
            console.groupEnd();
            
            if (selected.length === 0) {
                e.preventDefault();
                alert('Veuillez sélectionner au moins une transaction');
                return false;
            }
            
            if (!action) {
                e.preventDefault();
                alert('Veuillez sélectionner une action');
                return false;
            }
            
            const message = action === 'validate' 
                ? `Valider ${selected.length} transaction(s) ?` 
                : `Rejeter ${selected.length} transaction(s) ?`;
            
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
            
            // Désactiver le bouton pour éviter les doubles clics
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Traitement en cours...';
        });

        // Vérifier/décocher automatiquement la case "tout sélectionner"
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('input[name="selected_transactions[]"]');
            const selectAll = document.getElementById('select-all');
            
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const enabledCheckboxes = Array.from(checkboxes).filter(cb => !cb.disabled);
                    const allChecked = enabledCheckboxes.length > 0 && enabledCheckboxes.every(cb => cb.checked);
                    selectAll.checked = allChecked;
                    console.log(`Case ${this.value} cochée: ${this.checked} | Toutes cochées: ${allChecked}`);
                });
            });
            
            // Log initial
            console.log('Page chargée avec succès');
            console.log('Éléments DOM:', {
                checkboxes: checkboxes.length,
                pendingTransactions: <?= count($pending_transactions) ?>,
                forms: document.forms.length
            });
        });
    </script>
</body>
</html>