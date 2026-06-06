<?php
// functions/notifications.php

/**
 * Système de notifications automatiques pour izyboost
 * Incluez ce fichier dans vos autres scripts pour utiliser les fonctions
 */

/**
 * Envoie une notification à un utilisateur
 */
function sendNotification($user_id, $type, $title, $message, $action_url = null, $action_text = null) {
    require_once __DIR__ . '/../config/db.php';
    $pdo = Database::getInstance()->getConnection();
    
    // Déterminer l'icône et les couleurs selon le type
    $notification_config = [
        'system' => ['icon' => 'fas fa-cog', 'icon_color' => 'text-gray-600', 'bg_color' => 'bg-gray-100'],
        'investment' => ['icon' => 'fas fa-chart-line', 'icon_color' => 'text-green-600', 'bg_color' => 'bg-green-100'],
        'withdrawal' => ['icon' => 'fas fa-download', 'icon_color' => 'text-blue-600', 'bg_color' => 'bg-blue-100'],
        'referral' => ['icon' => 'fas fa-users', 'icon_color' => 'text-yellow-600', 'bg_color' => 'bg-yellow-100'],
        'video' => ['icon' => 'fas fa-video', 'icon_color' => 'text-purple-600', 'bg_color' => 'bg-purple-100'],
        'promotion' => ['icon' => 'fas fa-gift', 'icon_color' => 'text-red-600', 'bg_color' => 'bg-red-100'],
        'security' => ['icon' => 'fas fa-shield-alt', 'icon_color' => 'text-orange-600', 'bg_color' => 'bg-orange-100'],
        'update' => ['icon' => 'fas fa-sync-alt', 'icon_color' => 'text-indigo-600', 'bg_color' => 'bg-indigo-100'],
        'deposit' => ['icon' => 'fas fa-plus-circle', 'icon_color' => 'text-green-600', 'bg_color' => 'bg-green-100'],
        'login' => ['icon' => 'fas fa-sign-in-alt', 'icon_color' => 'text-blue-600', 'bg_color' => 'bg-blue-100'],
        'kyc' => ['icon' => 'fas fa-id-card', 'icon_color' => 'text-purple-600', 'bg_color' => 'bg-purple-100']
    ];
    
    $config = $notification_config[$type] ?? $notification_config['system'];
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO notifications 
            (user_id, type, title, message, icon, icon_color, bg_color, action_url, action_text, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $user_id,
            $type,
            $title,
            $message,
            $config['icon'],
            $config['icon_color'],
            $config['bg_color'],
            $action_url,
            $action_text
        ]);
        
        return $pdo->lastInsertId();
    } catch (Exception $e) {
        error_log("Erreur notification: " . $e->getMessage());
        return false;
    }
}

/**
 * NOTIFICATIONS D'INSCRIPTION
 */
function notifyUserRegistration($user_id, $user_email) {
    $title = "Bienvenue sur izyboost !";
    $message = "Votre compte a été créé avec succès. Commencez à investir dès maintenant !";
    
    return sendNotification(
        $user_id,
        'system',
        $title,
        $message,
        '?page=profile',
        'Compléter mon profil'
    );
}

function notifyReferredUserRegistration($parrain_id, $filleul_email) {
    $title = "Nouveau filleul inscrit !";
    $message = "$filleul_email s'est inscrit avec votre code de parrainage. Vous recevrez un bonus sur ses investissements.";
    
    return sendNotification(
        $parrain_id,
        'referral',
        $title,
        $message,
        '?page=parrainage',
        'Voir mes filleuls'
    );
}

/**
 * NOTIFICATIONS DE PARRAINAGE
 */
function notifyReferralBonus($parrain_id, $filleul_nom, $montant) {
    $title = "Bonus de parrainage !";
    $message = "Vous avez reçu $montant FCFA de bonus grâce à l'investissement de $filleul_nom";
    
    return sendNotification(
        $parrain_id,
        'referral',
        $title,
        $message,
        '?page=parrainage',
        'Voir le détail'
    );
}

function notifyReferralValidation($parrain_id, $filleul_nom) {
    $title = "Parrainage validé !";
    $message = "Votre filleul $filleul_nom a validé son compte. Vous pouvez maintenant recevoir des bonus.";
    
    return sendNotification(
        $parrain_id,
        'referral',
        $title,
        $message,
        '?page=parrainage',
        'Voir mes filleuls'
    );
}

/**
 * NOTIFICATIONS FINANCIÈRES
 */
function notifyDepositSuccess($user_id, $montant, $methode) {
    $title = "Dépôt réussi !";
    $message = "Votre dépôt de $montant FCFA via $methode a été crédité avec succès.";
    
    return sendNotification(
        $user_id,
        'deposit',
        $title,
        $message,
        '?page=wallet',
        'Voir mon solde'
    );
}

function notifyWithdrawalRequest($user_id, $montant, $methode) {
    $title = "Demande de retrait envoyée";
    $message = "Votre demande de retrait de $montant FCFA via $methode est en cours de traitement (24-48h).";
    
    return sendNotification(
        $user_id,
        'withdrawal',
        $title,
        $message,
        '?page=transactions',
        'Suivre ma demande'
    );
}

function notifyWithdrawalApproved($user_id, $montant) {
    $title = "Retrait approuvé !";
    $message = "Votre retrait de $montant FCFA a été approuvé et sera crédité sous 24h.";
    
    return sendNotification(
        $user_id,
        'withdrawal',
        $title,
        $message,
        '?page=transactions',
        'Voir la transaction'
    );
}

function notifyROIReceived($user_id, $montant, $plan_nom) {
    $title = "Nouveau ROI disponible !";
    $message = "Votre investissement \"$plan_nom\" a généré un ROI de $montant FCFA";
    
    return sendNotification(
        $user_id,
        'investment',
        $title,
        $message,
        '?page=wallet',
        'Voir mon portefeuille'
    );
}

/**
 * NOTIFICATIONS D'INVESTISSEMENT
 */
function notifyInvestmentStarted($user_id, $montant, $plan_nom, $duree) {
    $title = "Investissement démarré !";
    $message = "Vous avez investi $montant FCFA dans le plan \"$plan_nom\" pour $duree jours";
    
    return sendNotification(
        $user_id,
        'investment',
        $title,
        $message,
        '?page=investissement',
        'Suivre mon investissement'
    );
}

function notifyInvestmentCompleted($user_id, $plan_nom, $gain_total) {
    $title = "Investissement terminé !";
    $message = "Votre investissement \"$plan_nom\" est terminé. Gain total : $gain_total FCFA";
    
    return sendNotification(
        $user_id,
        'investment',
        $title,
        $message,
        '?page=wallet',
        'Retirer mes gains'
    );
}

/**
 * NOTIFICATIONS DE SÉCURITÉ
 */
function notifyNewLogin($user_id, $device, $location = "inconnue") {
    $title = "Nouvelle connexion détectée";
    $message = "Une connexion a été détectée depuis un $device à $location";
    
    return sendNotification(
        $user_id,
        'security',
        $title,
        $message,
        '?page=settings&section=securite',
        'Vérifier la sécurité'
    );
}

function notifyPasswordChanged($user_id) {
    $title = "Mot de passe modifié";
    $message = "Votre mot de passe a été modifié avec succès";
    
    return sendNotification(
        $user_id,
        'security',
        $title,
        $message,
        '?page=settings&section=securite',
        'Vérifier mon compte'
    );
}

function notifyEmailChanged($user_id, $new_email) {
    $title = "Email modifié";
    $message = "Votre adresse email a été changée pour $new_email";
    
    return sendNotification(
        $user_id,
        'security',
        $title,
        $message,
        '?page=settings',
        'Vérifier mes paramètres'
    );
}

/**
 * NOTIFICATIONS KYC
 */
function notifyKYCSubmitted($user_id) {
    $title = "Document KYC soumis";
    $message = "Votre document d'identité a été soumis et sera vérifié sous 24-48h";
    
    return sendNotification(
        $user_id,
        'kyc',
        $title,
        $message,
        '?page=settings&section=kyc',
        'Suivre la vérification'
    );
}

function notifyKYCApproved($user_id) {
    $title = "KYC approuvé !";
    $message = "Votre identité a été vérifiée. Vos limites de retrait ont été augmentées.";
    
    return sendNotification(
        $user_id,
        'kyc',
        $title,
        $message,
        '?page=settings&section=kyc',
        'Voir mes nouvelles limites'
    );
}

function notifyKYCRejected($user_id, $raison = "") {
    $title = "KYC rejeté";
    $message = "Votre document n'a pas été approuvé. $raison";
    
    return sendNotification(
        $user_id,
        'kyc',
        $title,
        $message,
        '?page=settings&section=kyc',
        'Soumettre à nouveau'
    );
}

/**
 * NOTIFICATIONS VIDÉOS
 */
function notifyNewVideosAvailable($user_id, $nombre) {
    $title = "Nouvelles vidéos disponibles";
    $message = "$nombre nouvelles vidéos rémunérées sont disponibles aujourd'hui";
    
    return sendNotification(
        $user_id,
        'video',
        $title,
        $message,
        '?page=videos',
        'Regarder maintenant'
    );
}

function notifyVideoReward($user_id, $montant) {
    $title = "Récompense vidéo !";
    $message = "Vous avez gagné $montant FCFA en regardant une vidéo";
    
    return sendNotification(
        $user_id,
        'video',
        $title,
        $message,
        '?page=wallet',
        'Voir mon solde'
    );
}

/**
 * NOTIFICATIONS PROMOTIONNELLES
 */
function notifyPromotion($user_id, $titre, $message_text) {
    return sendNotification(
        $user_id,
        'promotion',
        $titre,
        $message_text,
        '?page=investissement',
        'Profiter de l\'offre'
    );
}

/**
 * NOTIFICATIONS SYSTÈME
 */
function notifySystemUpdate($user_id, $version) {
    $title = "Mise à jour disponible";
    $message = "Une nouvelle version $version de l'application est disponible avec de nouvelles fonctionnalités";
    
    return sendNotification(
        $user_id,
        'update',
        $title,
        $message,
        '#',
        'En savoir plus'
    );
}

function notifyMaintenance($user_id, $heure_debut, $heure_fin) {
    $title = "Maintenance programmée";
    $message = "Une maintenance est prévue de $heure_debut à $heure_fin. Le service pourra être interrompu.";
    
    return sendNotification(
        $user_id,
        'system',
        $title,
        $message,
        '#',
        'En savoir plus'
    );
}

/**
 * NOTIFICATIONS POUR TOUS LES UTILISATEURS
 */
function notifyAllUsers($type, $title, $message, $action_url = null, $action_text = null) {
    require_once __DIR__ . '/../config/db.php';
    $pdo = Database::getInstance()->getConnection();
    
    // Récupérer tous les utilisateurs actifs
    $stmt = $pdo->prepare("SELECT id FROM users WHERE statut = 'active'");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $notifications_sent = 0;
    
    foreach ($users as $user) {
        if (sendNotification($user['id'], $type, $title, $message, $action_url, $action_text)) {
            $notifications_sent++;
        }
    }
    
    return $notifications_sent;
}

/**
 * Marquer une notification comme lue
 */
function markNotificationAsRead($notification_id, $user_id) {
    require_once __DIR__ . '/../config/db.php';
    $pdo = Database::getInstance()->getConnection();
    
    try {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$notification_id, $user_id]);
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Supprimer une notification
 */
function deleteNotification($notification_id, $user_id) {
    require_once __DIR__ . '/../config/db.php';
    $pdo = Database::getInstance()->getConnection();
    
    try {
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
        $stmt->execute([$notification_id, $user_id]);
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Récupérer les notifications non lues d'un utilisateur
 */
function getUnreadNotificationsCount($user_id) {
    require_once __DIR__ . '/../config/db.php';
    $pdo = Database::getInstance()->getConnection();
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['count'] : 0;
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Marquer toutes les notifications comme lues
 */
function markAllNotificationsAsRead($user_id) {
    require_once __DIR__ . '/../config/db.php';
    $pdo = Database::getInstance()->getConnection();
    
    try {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->rowCount();
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Supprimer toutes les notifications lues
 */
function deleteAllReadNotifications($user_id) {
    require_once __DIR__ . '/../config/db.php';
    $pdo = Database::getInstance()->getConnection();
    
    try {
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ? AND is_read = 1");
        $stmt->execute([$user_id]);
        return $stmt->rowCount();
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Obtenir les dernières notifications (pour affichage)
 */
function getRecentNotifications($user_id, $limit = 10, $unread_only = false) {
    require_once __DIR__ . '/../config/db.php';
    $pdo = Database::getInstance()->getConnection();
    
    try {
        $query = "SELECT * FROM notifications WHERE user_id = ?";
        
        if ($unread_only) {
            $query .= " AND is_read = 0";
        }
        
        $query .= " ORDER BY created_at DESC LIMIT ?";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}
?>