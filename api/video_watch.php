<?php
// api/video_watch.php

// Définir que la réponse est du JSON
header('Content-Type: application/json');

require_once '../config/db.php';

session_start();

// Vérifier connexion
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit();
}

$userId = $_SESSION['user_id'];
$db = Database::getInstance()->getConnection();

// Fonction helper copiée de pages/videos.php
function getRealVideoQuotaCheck($userId, $db) {
    // Récupérer le nombre maximum de vidéos par jour depuis le plan actif
    $sql = "SELECT SUM(p.videos_par_jour) as total 
            FROM user_plans up 
            JOIN plans p ON up.plan_id = p.id 
            WHERE up.user_id = :user_id 
            AND up.statut = 'active' 
            AND CURDATE() BETWEEN DATE(up.date_debut) AND DATE(up.date_fin)";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    $result = $stmt->fetch();
    $total = $result['total'] ?? 0;
    
    // Compter les vidéos déjà vues aujourd'hui
    $sql = "SELECT COUNT(*) as vues 
            FROM ads_views 
            WHERE user_id = :user_id 
            AND DATE(date_view) = CURDATE()";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    $result = $stmt->fetch();
    $vues = $result['vues'] ?? 0;
    
    return [
        'total' => $total,
        'restant' => max(0, $total - $vues)
    ];
}

// TRAITEMENT DU POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'watch_video') {
        $videoId = (int)($_POST['video_id'] ?? 0);
        
        // Vérifier le quota
        $quota = getRealVideoQuotaCheck($userId, $db);
        if ($quota['restant'] <= 0) {
            echo json_encode(['success' => false, 'message' => 'Quota journalier épuisé']);
            exit();
        }
        
        // Vérifier si déjà vue aujourd'hui
        $sqlCheck = "SELECT COUNT(*) as count FROM ads_views 
                    WHERE user_id = :user_id 
                    AND video_id = :video_id 
                    AND DATE(date_view) = CURDATE()";
        $stmtCheck = $db->prepare($sqlCheck);
        $stmtCheck->execute([
            ':user_id' => $userId,
            ':video_id' => $videoId
        ]);
        $check = $stmtCheck->fetch();
        
        if ($check['count'] > 0) {
            echo json_encode(['success' => false, 'message' => 'Déjà regardée aujourd\'hui']);
            exit();
        }
        
        // Récupérer le gain
        $sqlGain = "SELECT p.gain_par_video 
                   FROM user_plans up 
                   JOIN plans p ON up.plan_id = p.id 
                   WHERE up.user_id = :user_id 
                   AND up.statut = 'active'
                   LIMIT 1";
        $stmtGain = $db->prepare($sqlGain);
        $stmtGain->execute([':user_id' => $userId]);
        $gainResult = $stmtGain->fetch();
        $gain = $gainResult['gain_par_video'] ?? 10;
        
        try {
            $db->beginTransaction();
            
            // Enregistrer la vue
            $sqlInsert = "INSERT INTO ads_views (user_id, video_id, gain, ip_addr, user_agent, valide) 
                         VALUES (:user_id, :video_id, :gain, :ip, :agent, 1)";
            $stmtInsert = $db->prepare($sqlInsert);
            $stmtInsert->execute([
                ':user_id' => $userId,
                ':video_id' => $videoId,
                ':gain' => $gain,
                ':ip' => $_SERVER['REMOTE_ADDR'],
                ':agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);
            
            // Mettre à jour le solde publicité
            $sqlWallet = "UPDATE wallets 
                         SET solde_publicite = solde_publicite + :gain,
                             updated_at = NOW()
                         WHERE user_id = :user_id";
            $stmtWallet = $db->prepare($sqlWallet);
            $stmtWallet->execute([
                ':gain' => $gain,
                ':user_id' => $userId
            ]);
            
            $db->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Gain de ' . $gain . ' FCFA ajouté à votre solde',
                'gain' => $gain
            ]);
            
        } catch (Exception $e) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
        exit();
    }
}

// Si on arrive ici, c'est une requête invalide
echo json_encode(['success' => false, 'message' => 'Requête invalide']);
