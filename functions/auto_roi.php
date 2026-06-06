<?php
// functions/auto_roi.php

/**
 * Traite le versement automatique des ROI pour un utilisateur donné.
 * Cette fonction doit être appelée au chargement du tableau de bord.
 *
 * @param int $userId ID de l'utilisateur concerné
 * @param PDO $pdo Instance de connexion à la base de données
 * @return array Résultat du traitement (nombre de versements, montant total)
 */
function processDailyRoi($userId, $pdo) {
    if (empty($userId) || !$pdo) {
        return ['count' => 0, 'total' => 0];
    }

    $totalPaid = 0;
    $countPaid = 0;
    $today = date('Y-m-d');
    
    // DEBUG: Trace execution
    error_log("AutoROI [User $userId] - Start processing");

    // 1. Récupérer les plans actifs qui n'ont pas encore reçu leur ROI aujourd'hui
    // On vérifie que la date actuelle est comprise dans la période du plan
    // ET qu'il n'y a pas d'entrée dans roi_history pour ce plan et cette date
    $sql = "
        SELECT up.id as user_plan_id, up.user_id, p.roi_journalier, p.nom as plan_nom
        FROM user_plans up
        JOIN plans p ON up.plan_id = p.id
        WHERE up.user_id = :user_id
        AND up.statut = 'active'
        AND :today BETWEEN DATE(up.date_debut) AND DATE(up.date_fin)
        AND NOT EXISTS (
            SELECT 1 FROM roi_history rh 
            WHERE rh.user_plan_id = up.id 
            AND rh.date_versement > DATE_SUB(NOW(), INTERVAL 2 MINUTE)
        )
    ";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':today' => $today
        ]);
        
        $pendingRois = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("AutoROI [User $userId] - Found " . count($pendingRois) . " plans to pay.");

        if (empty($pendingRois)) {
            // Debug: Pourquoi vide ? Vérifions s'il a des plans tout court
            $check = $pdo->query("SELECT COUNT(*) FROM user_plans WHERE user_id = $userId AND statut = 'active'")->fetchColumn();
            error_log("AutoROI [User $userId] - Total active plans in DB: " . $check);
            return ['count' => 0, 'total' => 0];
        }

        // 2. Pour chaque ROI en attente, effectuer le versement
        $pdo->beginTransaction();

        foreach ($pendingRois as $roi) {
            $amount = floatval($roi['roi_journalier']);
            $planName = $roi['plan_nom'];
            
            // A. Insérer dans l'historique ROI (Preuve technique)
            $insertRoi = $pdo->prepare("
                INSERT INTO roi_history (user_id, user_plan_id, montant, date_versement, note)
                VALUES (?, ?, ?, NOW(), ?)
            ");
            $insertRoi->execute([
                $userId, 
                $roi['user_plan_id'], 
                $amount, 
                "ROI Automatique - $today - Plan: $planName"
            ]);
            
            // B. Créer une transaction (Visibilité utilisateur)
            // On utilise 'gain' comme type pour distinguer des dépôts d'argent frais
            $insertTx = $pdo->prepare("
                INSERT INTO transactions (user_id, type, source, montant, methode, statut, reference, note, created_at)
                VALUES (?, 'gain', 'investissement', ?, 'systeme', 'success', ?, ?, NOW())
            ");
            $ref = 'ROI-' . $roi['user_plan_id'] . '-' . date('Ymd');
            $insertTx->execute([
                $userId,
                $amount,
                $ref,
                "Gain journalier : $planName"
            ]);
            
            // C. Mettre à jour le portefeuille (Argent réel)
            $updateWallet = $pdo->prepare("
                UPDATE wallets 
                SET solde_investissement = solde_investissement + ?,
                    updated_at = NOW()
                WHERE user_id = ?
            ");
            $updateWallet->execute([$amount, $userId]);
            
            // D. Envoyer une notification
            require_once __DIR__ . '/notifications.php';
            notifyROIReceived($userId, $amount, $planName);
            
            $totalPaid += $amount;
            $countPaid++;
        }

        $pdo->commit();

        // 3. Gestion du Cycle de Vie : Terminer les plans expirés
        // Si la date de fin est passée (strictement inférieure à aujourd'hui), le plan est terminé.
        // Cela permet de garder la table propre et d'éviter les incohérences dans l'admin.
        try {
            $sqlTerminate = "
                UPDATE user_plans 
                SET statut = 'termine' 
                WHERE user_id = :user_id 
                AND statut = 'active' 
                AND date_fin < :today
            ";
            $stmtTerminate = $pdo->prepare($sqlTerminate);
            $stmtTerminate->execute([':user_id' => $userId, ':today' => $today]);
        } catch (Exception $e) {
            // Non critique
        }
        
        return [
            'count' => $countPaid,
            'total' => $totalPaid,
            'message' => "Félicitations ! Vous avez reçu vos gains journaliers : +" . number_format($totalPaid, 0, ',', ' ') . " FCFA"
        ];

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // Log l'erreur silencieusement pour ne pas bloquer l'utilisateur
        error_log("Auto ROI Error User $userId: " . $e->getMessage());
        return ['count' => 0, 'total' => 0, 'error' => $e->getMessage()];
    }
}
?>
