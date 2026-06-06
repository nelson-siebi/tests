<?php
// ajax_controller.php
session_start();
require_once __DIR__ . '/config/database.php';
// require_once __DIR__ . '/../../functions/notifications.php';

// Vérifier l'authentification
if(!isset($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

// Headers pour JSON
header('Content-Type: application/json');

// Fonction pour générer une réponse standard
function jsonResponse($success = true, $data = null, $message = '', $error = null) {
    return json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'error' => $error,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

// Gestion des erreurs PDO
function handlePDOError($e) {
    error_log("PDO Error: " . $e->getMessage());
    return jsonResponse(false, null, 'Erreur de base de données', $e->getMessage());
}

try {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch($action) {
        // GESTION DES ADMINISTRATEURS
        case 'add_admin':
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $role = $_POST['role'];
            
            // Vérifier si le nom d'utilisateur existe déjà
            $check = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE username = ?");
            $check->execute([$username]);
            
            if($check->fetchColumn() > 0) {
                echo jsonResponse(false, null, 'Ce nom d\'utilisateur existe déjà');
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO admin_users (username, password, role, email, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                
                $stmt->execute([$username, $password, $role, $email]);
                $admin_id = $pdo->lastInsertId();
                
                echo jsonResponse(true, ['admin_id' => $admin_id], 'Administrateur ajouté avec succès');
            }
            break;
            
        case 'update_admin':
            $admin_id = (int)$_POST['admin_id'];
            $email = trim($_POST['email']);
            $role = $_POST['role'];
            $new_password = $_POST['new_password'] ?? '';
            
            $update_fields = ['email = ?, role = ?, updated_at = NOW()'];
            $params = [$email, $role];
            
            if(!empty($new_password)) {
                $update_fields[] = 'password = ?';
                $params[] = password_hash($new_password, PASSWORD_DEFAULT);
            }
            
            $params[] = $admin_id;
            
            $sql = "UPDATE admin_users SET " . implode(', ', $update_fields) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            echo jsonResponse(true, null, 'Administrateur modifié avec succès');
            break;
            
        case 'delete_admin':
            $admin_id = (int)$_POST['admin_id'];
            
            // Ne pas permettre de supprimer son propre compte
            if($admin_id == $_SESSION['admin_id']) {
                echo jsonResponse(false, null, 'Vous ne pouvez pas supprimer votre propre compte');
            } else {
                $pdo->prepare("DELETE FROM admin_users WHERE id = ?")->execute([$admin_id]);
                echo jsonResponse(true, null, 'Administrateur supprimé avec succès');
            }
            break;
            
        case 'get_admins':
            $admins = $pdo->query("SELECT * FROM admin_users ORDER BY role, created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
            echo jsonResponse(true, $admins, 'Liste des administrateurs');
            break;
            
        // GESTION DES UTILISATEURS
        case 'get_user':
            $user_id = (int)$_GET['id'];
            $stmt = $pdo->prepare("
                SELECT u.*, w.*,
                       (SELECT COUNT(*) FROM user_plans WHERE user_id = u.id) as total_investments,
                       (SELECT COUNT(*) FROM referrals WHERE parrain_id = u.id) as referrals_count
                FROM users u
                LEFT JOIN wallets w ON u.id = w.user_id
                WHERE u.id = ?
            ");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($user) {
                echo jsonResponse(true, $user, 'Détails utilisateur');
            } else {
                echo jsonResponse(false, null, 'Utilisateur non trouvé');
            }
            break;
            
        case 'update_user_status':
            $user_id = (int)$_POST['user_id'];
            $status = $_POST['status'];
            
            $pdo->prepare("UPDATE users SET statut = ?, updated_at = NOW() WHERE id = ?")
                ->execute([$status, $user_id]);
                
            echo jsonResponse(true, null, 'Statut utilisateur mis à jour');
            break;
            
        // GESTION DES INVESTISSEMENTS
        case 'add_roi':
            $user_plan_id = (int)$_POST['user_plan_id'];
            $amount = (float)$_POST['amount'];
            $note = $_POST['note'] ?? '';
            $user_id = (int)$_POST['user_id'];
            
            // Commencer une transaction
            $pdo->beginTransaction();
            
            try {
                // Ajouter le ROI
                $stmt1 = $pdo->prepare("
                    INSERT INTO roi_history (user_id, user_plan_id, montant, date_versement, note)
                    VALUES (?, ?, ?, NOW(), ?)
                ");
                $stmt1->execute([$user_id, $user_plan_id, $amount, $note]);
                
                // Mettre à jour le solde investissement
                $stmt2 = $pdo->prepare("
                    UPDATE wallets 
                    SET solde_investissement = solde_investissement + ?,
                        updated_at = NOW()
                    WHERE user_id = ?
                ");
                $stmt2->execute([$amount, $user_id]);
                
                $pdo->commit();
                echo jsonResponse(true, null, 'ROI ajouté avec succès');
                
            } catch(Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
            
        case 'terminate_investment':
            $investment_id = (int)$_POST['investment_id'];
            
            $pdo->prepare("UPDATE user_plans SET statut = 'termine', date_fin = NOW() WHERE id = ?")
                ->execute([$investment_id]);
                
            echo jsonResponse(true, null, 'Investissement terminé avec succès');
            break;
            
        // GESTION DES RETRAITS
        case 'process_withdrawal':
            $transaction_id = (int)$_POST['transaction_id'];
            $action_type = $_POST['action_type']; // 'approve' ou 'reject'
            $note = $_POST['note'] ?? '';
            
            $pdo->beginTransaction();
            
            try {
                // Récupérer la transaction
                $stmt = $pdo->prepare("
                    SELECT t.*, u.id as user_id, t.montant, t.methode, t.source
                    FROM transactions t 
                    JOIN users u ON t.user_id = u.id 
                    WHERE t.id = ?
                ");
                $stmt->execute([$transaction_id]);
                $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if(!$transaction) {
                    throw new Exception('Transaction non trouvée');
                }
                
                if($action_type == 'approve') {
                    $new_status = 'success';
                    
                    // Déduire du solde approprié
                    $balance_field = '';
                    switch($transaction['source']) {
                        case 'investissement': $balance_field = 'solde_investissement'; break;
                        case 'publicite': $balance_field = 'solde_publicite'; break;
                        case 'parrainage': $balance_field = 'solde_parrainage'; break;
                        default: $balance_field = 'solde_investissement';
                    }
                    
                    $pdo->prepare("UPDATE wallets SET $balance_field = $balance_field - ? WHERE user_id = ?")
                        ->execute([$transaction['montant'], $transaction['user_id']]);
                        
                    // Ajouter aux totaux retrait
                    $total_field = 'total_retrait_' . ($balance_field == 'solde_investissement' ? 'invest' : 
                                 ($balance_field == 'solde_publicite' ? 'pub' : 'parrain'));
                    
                    $pdo->prepare("UPDATE wallets SET $total_field = $total_field + ? WHERE user_id = ?")
                        ->execute([$transaction['montant'], $transaction['user_id']]);
                        
                } else {
                    $new_status = 'annule';
                }
                
                // Mettre à jour le statut
                $pdo->prepare("UPDATE transactions SET statut = ?, note = CONCAT(IFNULL(note, ''), ' | ', ?), updated_at = NOW() WHERE id = ?")
                    ->execute([$new_status, $note, $transaction_id]);
                    
                // Envoyer la notification
                if ($action_type == 'approve') {
                    notifyWithdrawalApproved($transaction['user_id'], $transaction['montant']);
                } else {
                    sendNotification(
                        $transaction['user_id'],
                        'withdrawal',
                        'Retrait refusé',
                        "Votre retrait de " . number_format($transaction['montant'], 0) . " FCFA a été refusé. " . ($note ? "Raison: $note" : ""),
                        '?page=transactions',
                        'Voir'
                    );
                }
                    
                $pdo->commit();
                echo jsonResponse(true, null, $action_type == 'approve' ? 'Retrait approuvé' : 'Retrait refusé');
                
            } catch(Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
            
        // VALIDATION DES INVESTISSEMENTS (KYC)
        case 'validate_investment':
            $transaction_id = (int)$_POST['transaction_id'];
            
            // Utiliser votre fonction validateTransaction existante
            require_once 'kyc.php'; // Pour utiliser la fonction
            
            try {
                $result = validateTransaction($transaction_id, $pdo);
                echo jsonResponse(true, null, 'Investissement validé avec succès');
            } catch(Exception $e) {
                echo jsonResponse(false, null, 'Erreur lors de la validation', $e->getMessage());
            }
            break;
            
        // STATISTIQUES
        case 'get_stats':
            $stats = [
                'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
                'total_investments' => $pdo->query("SELECT COUNT(*) FROM user_plans WHERE statut = 'active'")->fetchColumn(),
                'total_deposits' => $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM transactions WHERE type = 'depot' AND statut = 'success'")->fetchColumn(),
                'total_withdrawals' => $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM transactions WHERE type = 'retrait' AND statut = 'success'")->fetchColumn(),
                'pending_kyc' => $pdo->query("SELECT COUNT(*) FROM pending_transactions WHERE status = 'pending'")->fetchColumn(),
                'pending_withdrawals' => $pdo->query("SELECT COUNT(*) FROM transactions WHERE type = 'retrait' AND statut = 'attente'")->fetchColumn(),
                'daily_stats' => $pdo->query("
                    SELECT 
                        DATE(created_at) as date,
                        COUNT(*) as new_users,
                        SUM(CASE WHEN type = 'depot' AND statut = 'success' THEN montant ELSE 0 END) as deposits,
                        SUM(CASE WHEN type = 'retrait' AND statut = 'success' THEN montant ELSE 0 END) as withdrawals
                    FROM transactions
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    GROUP BY DATE(created_at)
                    ORDER BY date DESC
                ")->fetchAll(PDO::FETCH_ASSOC)
            ];
            
            echo jsonResponse(true, $stats, 'Statistiques récupérées');
            break;
            
        // VALIDATION MANUELLE DES ROI
        case 'validate_all_roi':
            require_once __DIR__ . '/../../functions/manual_roi.php';
            
            try {
                $result = validateAllDailyRoi($pdo);
                
                if ($result['success']) {
                    echo jsonResponse(true, $result['summary'], $result['summary']['message']);
                } else {
                    echo jsonResponse(false, $result['summary'], 'Erreur lors de la validation', $result['summary']['errors'][0]['reason'] ?? 'Erreur inconnue');
                }
            } catch (Exception $e) {
                echo jsonResponse(false, null, 'Erreur lors de la validation des ROI', $e->getMessage());
            }
            break;
            
        default:
            echo jsonResponse(false, null, 'Action non reconnue');
            break;
    }
    
} catch(PDOException $e) {
    echo handlePDOError($e);
} catch(Throwable $e) {
    error_log("Critical Error: " . $e->getMessage());
    echo jsonResponse(false, null, 'Une erreur critique est survenue', $e->getMessage());
} catch(Exception $e) {
    error_log("General Error: " . $e->getMessage());
    echo jsonResponse(false, null, 'Une erreur est survenue', $e->getMessage());
}
