<?php
// functions/manual_roi.php

/**
 * Valide manuellement tous les ROI journaliers éligibles.
 * Cette fonction doit être appelée par l'administrateur via le panneau d'administration.
 *
 * @param PDO $pdo Instance de connexion à la base de données
 * @return array Résumé détaillé de la validation (succès, échecs, montant total)
 */
function validateAllDailyRoi($pdo) {
    $today = date('Y-m-d');
    $totalPaid = 0;
    $countSuccess = 0;
    $countSkipped = 0;
    $errors = [];
    
    // Log de début
    error_log("Manual ROI Validation - Start - Date: $today");

    try {
        // 1. Récupérer tous les plans actifs éligibles au ROI du jour
        // Vérifications :
        // - Plan actif (statut = 'active')
        // - Date actuelle dans la période du plan
        // - ROI pas encore crédité aujourd'hui
        // - Solde utilisateur >= 0 (on accepte 0 mais pas négatif)
        $sql = "
            SELECT 
                up.id as user_plan_id,
                up.user_id,
                up.plan_id,
                p.roi_journalier,
                p.nom as plan_nom,
                u.nom as user_nom,
                u.prenom as user_prenom,
                w.solde_investissement
            FROM user_plans up
            JOIN plans p ON up.plan_id = p.id
            JOIN users u ON up.user_id = u.id
            JOIN wallets w ON up.user_id = w.user_id
            WHERE up.statut = 'active'
            AND CURDATE() BETWEEN DATE(up.date_debut) AND DATE(up.date_fin)
            AND w.solde_investissement >= 0
            AND NOT EXISTS (
                SELECT 1 FROM roi_history rh 
                WHERE rh.user_plan_id = up.id 
                AND DATE(rh.date_versement) = CURDATE()
            )
            ORDER BY u.nom, u.prenom
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        
        $eligiblePlans = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalPlans = count($eligiblePlans);
        
        error_log("Manual ROI Validation - Found $totalPlans eligible plans");

        if (empty($eligiblePlans)) {
            return [
                'success' => true,
                'summary' => [
                    'total_plans' => 0,
                    'validated' => 0,
                    'skipped' => 0,
                    'total_amount' => 0,
                    'errors' => [],
                    'message' => 'Aucun plan éligible au ROI aujourd\'hui. Tous les ROI ont peut-être déjà été crédités.'
                ]
            ];
        }

        // 2. Commencer une transaction pour garantir l'atomicité
        $pdo->beginTransaction();

        foreach ($eligiblePlans as $plan) {
            $amount = floatval($plan['roi_journalier']);
            $userId = $plan['user_id'];
            $userPlanId = $plan['user_plan_id'];
            $planName = $plan['plan_nom'];
            $userName = $plan['user_prenom'] . ' ' . $plan['user_nom'];
            
            try {
                // Vérification supplémentaire : le plan existe toujours et est actif
                $checkPlan = $pdo->prepare("
                    SELECT statut, date_fin 
                    FROM user_plans 
                    WHERE id = ? AND statut = 'active' AND date_fin >= CURDATE()
                ");
                $checkPlan->execute([$userPlanId]);
                $planStatus = $checkPlan->fetch();
                
                if (!$planStatus) {
                    $countSkipped++;
                    $errors[] = [
                        'user_id' => $userId,
                        'user_name' => $userName,
                        'plan_name' => $planName,
                        'reason' => 'Plan non actif ou expiré'
                    ];
                    continue;
                }

                // Vérification : ROI pas déjà crédité (double sécurité)
                $checkRoi = $pdo->prepare("
                    SELECT COUNT(*) 
                    FROM roi_history 
                    WHERE user_plan_id = ? AND DATE(date_versement) = ?
                ");
                $checkRoi->execute([$userPlanId, $today]);
                $alreadyCredited = $checkRoi->fetchColumn();
                
                if ($alreadyCredited > 0) {
                    $countSkipped++;
                    $errors[] = [
                        'user_id' => $userId,
                        'user_name' => $userName,
                        'plan_name' => $planName,
                        'reason' => 'ROI déjà crédité aujourd\'hui'
                    ];
                    continue;
                }

                // A. Insérer dans l'historique ROI
                $insertRoi = $pdo->prepare("
                    INSERT INTO roi_history (user_id, user_plan_id, montant, date_versement, note)
                    VALUES (?, ?, ?, NOW(), ?)
                ");
                $insertRoi->execute([
                    $userId, 
                    $userPlanId, 
                    $amount, 
                    "ROI Manuel - $today - Plan: $planName - Validé par Admin"
                ]);
                
                // B. Créer une transaction pour visibilité utilisateur
                $insertTx = $pdo->prepare("
                    INSERT INTO transactions (user_id, type, source, montant, methode, statut, reference, note, created_at)
                    VALUES (?, 'gain', 'investissement', ?, 'systeme', 'success', ?, ?, NOW())
                ");
                $ref = 'ROI-' . $userPlanId . '-' . date('Ymd');
                $insertTx->execute([
                    $userId,
                    $amount,
                    $ref,
                    "Gain journalier : $planName (Validation manuelle)"
                ]);
                
                // C. Mettre à jour le portefeuille
                $updateWallet = $pdo->prepare("
                    UPDATE wallets 
                    SET solde_investissement = solde_investissement + ?,
                        updated_at = NOW()
                    WHERE user_id = ?
                ");
                $updateWallet->execute([$amount, $userId]);
                
                // D. Envoyer une notification (Désactivé sur demande)
                // require_once __DIR__ . '/notifications.php';
                // notifyROIReceived($userId, $amount, $planName);
                
                $totalPaid += $amount;
                $countSuccess++;
                
                error_log("Manual ROI Validation - Success for User $userId ($userName) - Plan: $planName - Amount: $amount FCFA");
                
            } catch (Exception $e) {
                $countSkipped++;
                $errors[] = [
                    'user_id' => $userId,
                    'user_name' => $userName,
                    'plan_name' => $planName,
                    'reason' => 'Erreur technique: ' . $e->getMessage()
                ];
                error_log("Manual ROI Validation - Error for User $userId: " . $e->getMessage());
            }
        }

        // 3. Valider la transaction
        $pdo->commit();
        
        error_log("Manual ROI Validation - Completed - Success: $countSuccess, Skipped: $countSkipped, Total: " . number_format($totalPaid, 0) . " FCFA");

        return [
            'success' => true,
            'summary' => [
                'total_plans' => $totalPlans,
                'validated' => $countSuccess,
                'skipped' => $countSkipped,
                'total_amount' => $totalPaid,
                'errors' => $errors,
                'message' => "$countSuccess ROI validés avec succès pour un montant total de " . number_format($totalPaid, 0, ',', ' ') . " FCFA"
            ]
        ];

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Manual ROI Validation - Critical Error: " . $e->getMessage());
        
        return [
            'success' => false,
            'summary' => [
                'total_plans' => 0,
                'validated' => 0,
                'skipped' => 0,
                'total_amount' => 0,
                'errors' => [['reason' => 'Erreur critique: ' . $e->getMessage()]],
                'message' => 'Erreur lors de la validation des ROI'
            ]
        ];
    }
}

/**
 * Récupère la liste des plans éligibles au ROI du jour (pour affichage)
 *
 * @param PDO $pdo Instance de connexion à la base de données
 * @return array Liste des plans éligibles avec détails
 */
function getEligibleRoiPlans($pdo) {
    $sql = "
        SELECT 
            up.id as user_plan_id,
            up.user_id,
            u.nom as user_nom,
            u.prenom as user_prenom,
            u.email,
            p.nom as plan_nom,
            p.roi_journalier,
            up.montant_investi,
            up.date_debut,
            up.date_fin,
            w.solde_investissement,
            DATEDIFF(CURDATE(), up.date_debut) as jours_actifs
        FROM user_plans up
        JOIN plans p ON up.plan_id = p.id
        JOIN users u ON up.user_id = u.id
        JOIN wallets w ON up.user_id = w.user_id
        WHERE up.statut = 'active'
        AND CURDATE() BETWEEN DATE(up.date_debut) AND DATE(up.date_fin)
        AND w.solde_investissement >= 0
        AND NOT EXISTS (
            SELECT 1 FROM roi_history rh 
            WHERE rh.user_plan_id = up.id 
            AND DATE(rh.date_versement) = CURDATE()
        )
        ORDER BY u.nom, u.prenom, p.nom
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère les statistiques de validation ROI du jour
 *
 * @param PDO $pdo Instance de connexion à la base de données
 * @return array Statistiques (nombre validés, montant total, etc.)
 */
function getTodayRoiStats($pdo) {
    $sql = "
        SELECT 
            COUNT(*) as count_validated,
            COALESCE(SUM(montant), 0) as total_amount
        FROM roi_history
        WHERE DATE(date_versement) = CURDATE()
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
