<?php
// pages/profile.php

require_once 'config/db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$userId = $_SESSION['user_id'];
$pdo = Database::getInstance()->getConnection();

$error = '';
$success = '';
// $pdo = null;

try {
   
    
    // Récupérer les informations de l'utilisateur
    $stmt = $pdo->prepare("
      SELECT 
    u.id,
    u.nom,
    u.prenom,
    u.email,
    u.phone as telephone,
    u.referral_code,
    u.statut as statut_compte,
    u.created_at as date_inscription,
    DATE(u.created_at) as date_inscription_format,
    (w.solde_investissement + w.solde_publicite + w.solde_parrainage) as solde_total,
    (SELECT COALESCE(SUM(montant_investi), 0) FROM user_plans WHERE user_id = u.id) as investissement_total,
    (w.total_retrait_invest + w.total_retrait_pub + w.total_retrait_parrain) as retraits_totaux,
    COUNT(k.id) as kyc_completed
FROM users u
LEFT JOIN wallets w ON u.id = w.user_id
LEFT JOIN kyc k ON u.id = k.user_id AND k.statut = 'approved'
WHERE u.id = ?
GROUP BY 
    u.id, 
    u.nom,
    u.prenom,
    u.email,
    u.phone,
    u.referral_code,
    u.statut,
    u.created_at,
    w.solde_investissement,
    w.solde_publicite,
    w.solde_parrainage,
    w.total_retrait_invest,
    w.total_retrait_pub,
    w.total_retrait_parrain
LIMIT 1;
");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception("Utilisateur non trouvé");
    }
    
    // Formater les données de l'utilisateur
    $user['nom'] = htmlspecialchars($user['nom'] ?? '');
    $user['prenom'] = htmlspecialchars($user['prenom'] ?? '');
    $user['email'] = htmlspecialchars($user['email'] ?? '');
    $user['telephone'] = htmlspecialchars($user['telephone'] ?? 'Non renseigné');
    $user['referral_code'] = htmlspecialchars($user['referral_code'] ?? 'Investian' . rand(1000, 9999));
    $user['statut_compte'] = $user['statut_compte'] == 'active' ? 'Actif' : 'Inactif';
    $user['niveau_verification'] = $user['kyc_completed'] > 0 ? 'Vérifié' : 'À vérifier';
    
    // Valeurs par défaut pour les champs manquants
    $user['pays'] = 'Non spécifié';
    $user['ville'] = 'Non spécifiée';
    $user['date_naissance'] = 'Non spécifiée';
    $user['genre'] = 'Non spécifié';
    
    // Récupérer les statistiques avancées
    $stats = [];
    
    // Solde actuel
    $stmt = $pdo->prepare("
        SELECT 
            solde_investissement + solde_publicite + solde_parrainage as solde_actuel
        FROM wallets 
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $stats['solde_actuel'] = $stmt->fetch(PDO::FETCH_ASSOC)['solde_actuel'] ?? 0;
    
    // Investissement total
    $stats['investissement_total'] = $user['investissement_total'] ?? 0;
    
    // Gains totaux (ROI + Vidéos + Parrainage)
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(montant), 0) as gains_totaux
        FROM (
            SELECT montant FROM roi_history WHERE user_id = ?
            UNION ALL
            SELECT gain as montant FROM ads_views WHERE user_id = ? AND valide = 1
            UNION ALL
            SELECT bonus as montant FROM referrals WHERE parrain_id = ? AND valide = 1
        ) as gains
    ");
    $stmt->execute([$user_id, $user_id, $user_id]);
    $stats['gains_totaux'] = $stmt->fetch(PDO::FETCH_ASSOC)['gains_totaux'] ?? 0;
    
    // Retraits totaux
    $stats['retraits_totaux'] = $user['retraits_totaux'] ?? 0;
    
    // Plans actifs
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as investissements_actifs 
        FROM user_plans 
        WHERE user_id = ? AND statut = 'active'
    ");
    $stmt->execute([$user_id]);
    $stats['investissements_actifs'] = $stmt->fetch(PDO::FETCH_ASSOC)['investissements_actifs'] ?? 0;
    
    // Nombre de filleuls
    $stmt = $pdo->prepare("SELECT COUNT(*) as parrains_total FROM referrals WHERE parrain_id = ?");
    $stmt->execute([$user_id]);
    $stats['parrains_total'] = $stmt->fetch(PDO::FETCH_ASSOC)['parrains_total'] ?? 0;
    
    // Vidéos vues
    $stmt = $pdo->prepare("SELECT COUNT(*) as videos_vues FROM ads_views WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $stats['videos_vues'] = $stmt->fetch(PDO::FETCH_ASSOC)['videos_vues'] ?? 0;
    
    // Score de fiabilité (basé sur KYC, ancienneté, activité)
   
     $stmt = $pdo->prepare("
    SELECT 
        (
            CASE 
                WHEN COUNT(k.id) > 0 THEN 40
                ELSE 0
            END
            +
            CASE 
                WHEN DATEDIFF(CURRENT_DATE, u.created_at) > 90 THEN 30
                WHEN DATEDIFF(CURRENT_DATE, u.created_at) > 30 THEN 20
                ELSE 10
            END
            +
            CASE 
                WHEN w.total_depots > 50000 THEN 30
                WHEN w.total_depots > 10000 THEN 20
                ELSE 10
            END
        ) AS score_fiabilite
    FROM users u
    LEFT JOIN wallets w ON u.id = w.user_id
    LEFT JOIN kyc k ON u.id = k.user_id AND k.statut = 'approved'
    WHERE u.id = ?
    GROUP BY u.id, u.created_at, w.total_depots
");

    $stmt->execute([$user_id]);
    $stats['score_fiabilite'] = min(100, $stmt->fetch(PDO::FETCH_ASSOC)['score_fiabilite'] ?? 50);
    
    // Récupérer les plans actifs
    $stmt = $pdo->prepare("
        SELECT 
            p.nom,
            up.montant_investi as montant,
            up.date_debut,
            up.date_fin,
            ROUND((DATEDIFF(CURRENT_DATE, up.date_debut) / p.duree_jours) * 100, 0) as progression
        FROM user_plans up
        JOIN plans p ON up.plan_id = p.id
        WHERE up.user_id = ? AND up.statut = 'active'
        ORDER BY up.date_debut DESC
        LIMIT 3
    ");
    $stmt->execute([$user_id]);
    $plans_actifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupérer les dernières activités
    $stmt = $pdo->prepare("
        (
            SELECT 
                'ROI' as type,
                'ROI Plan' as description,
                montant,
                date_versement as date,
                'green' as color
            FROM roi_history 
            WHERE user_id = ? 
            ORDER BY date_versement DESC 
            LIMIT 2
        )
        UNION ALL
        (
            SELECT 
                'VIDEO' as type,
                'Visionnage vidéo' as description,
                gain as montant,
                date_view as date,
                'blue' as color
            FROM ads_views 
            WHERE user_id = ? AND valide = 1 
            ORDER BY date_view DESC 
            LIMIT 2
        )
        UNION ALL
        (
            SELECT 
                'PARRAINAGE' as type,
                CONCAT('Bonus parrainage') as description,
                bonus as montant,
                date_validation as date,
                'purple' as color
            FROM referrals 
            WHERE parrain_id = ? AND valide = 1 
            ORDER BY date_validation DESC 
            LIMIT 2
        )
        ORDER BY date DESC
        LIMIT 6
    ");
    $stmt->execute([$user_id, $user_id, $user_id]);
    $activites_recentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Traitement des formulaires
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'update_profile':
                $nom = trim($_POST['nom'] ?? '');
                $prenom = trim($_POST['prenom'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                
                if (!empty($nom) && !empty($prenom)) {
                    $stmt = $pdo->prepare("UPDATE users SET nom = ?, prenom = ?, phone = ? WHERE id = ?");
                    $stmt->execute([$nom, $prenom, $phone, $user_id]);
                    $success = "Profil mis à jour avec succès !";
                    
                    // Rafraîchir les données utilisateur
                    $stmt = $pdo->prepare("SELECT nom, prenom, phone FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $updated = $stmt->fetch(PDO::FETCH_ASSOC);
                    $user['nom'] = htmlspecialchars($updated['nom']);
                    $user['prenom'] = htmlspecialchars($updated['prenom']);
                    $user['telephone'] = htmlspecialchars($updated['phone']);
                } else {
                    $error = "Le nom et le prénom sont obligatoires";
                }
                break;
                
            case 'update_email':
                $email = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    // Vérifier si l'email est déjà utilisé
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                    $stmt->execute([$email, $user_id]);
                    if ($stmt->fetch()) {
                        $error = "Cet email est déjà utilisé par un autre compte";
                    } else {
                        // Vérifier le mot de passe
                        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                        $stmt->execute([$user_id]);
                        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if (password_verify($password, $user_data['password'])) {
                            $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
                            $stmt->execute([$email, $user_id]);
                            $success = "Email mis à jour avec succès !";
                            $user['email'] = htmlspecialchars($email);
                        } else {
                            $error = "Mot de passe incorrect";
                        }
                    }
                } else {
                    $error = "Email invalide";
                }
                break;
                
            case 'change_password':
                $current_password = $_POST['current_password'] ?? '';
                $new_password = $_POST['new_password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                
                // Vérifier l'ancien mot de passe
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($current_password, $user_data['password'])) {
                    if ($new_password === $confirm_password) {
                        if (strlen($new_password) >= 6) {
                            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                            $stmt->execute([$hashed_password, $user_id]);
                            $success = "Mot de passe modifié avec succès !";
                        } else {
                            $error = "Le mot de passe doit contenir au moins 6 caractères";
                        }
                    } else {
                        $error = "Les nouveaux mots de passe ne correspondent pas";
                    }
                } else {
                    $error = "Mot de passe actuel incorrect";
                }
                break;
                
            case 'upload_photo':
                if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                    $max_size = 5 * 1024 * 1024; // 5MB
                    
                    if (in_array($_FILES['profile_photo']['type'], $allowed_types)) {
                        if ($_FILES['profile_photo']['size'] <= $max_size) {
                            $upload_dir = '../uploads/profile_photos/';
                            if (!is_dir($upload_dir)) {
                                mkdir($upload_dir, 0755, true);
                            }
                            
                            $extension = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
                            $filename = 'user_' . $user_id . '_' . time() . '.' . $extension;
                            $filepath = $upload_dir . $filename;
                            
                            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $filepath)) {
                                // Supprimer l'ancienne photo si elle existe
                                if (!empty($user['photo_url'])) {
                                    $old_file = '../uploads/profile_photos/' . basename($user['photo_url']);
                                    if (file_exists($old_file)) {
                                        unlink($old_file);
                                    }
                                }
                                
                                $user['photo_url'] = 'uploads/profile_photos/' . $filename;
                                $success = "Photo de profil mise à jour avec succès !";
                            } else {
                                $error = "Erreur lors de l'upload de la photo";
                            }
                        } else {
                            $error = "La photo est trop volumineuse (max 5MB)";
                        }
                    } else {
                        $error = "Format de fichier non supporté. Formats acceptés: JPG, PNG, GIF";
                    }
                } else {
                    $error = "Aucune photo sélectionnée ou erreur d'upload";
                }
                break;
                
            case 'initiate_withdrawal':
                $amount = floatval($_POST['withdrawal_amount'] ?? 0);
                $method = $_POST['withdrawal_method'] ?? '';
                
                if ($amount > 0) {
                    // Vérifier le solde
                    if ($amount <= $stats['solde_actuel']) {
                        // Créer une transaction en attente
                        $stmt = $pdo->prepare("
                            INSERT INTO transactions 
                            (user_id, type, source, montant, methode, statut, reference, note) 
                            VALUES (?, 'retrait', 'investissement', ?, ?, 'attente', NULL, 'Retrait depuis le profil')
                        ");
                        $stmt->execute([$user_id, $amount, $method]);
                        
                        $success = "Demande de retrait de " . number_format($amount, 0, ',', ' ') . " FCFA envoyée ! Elle sera traitée sous 24h.";
                    } else {
                        $error = "Solde insuffisant";
                    }
                } else {
                    $error = "Montant invalide";
                }
                break;
        }
    }
    
} catch (Exception $e) {
    $error = "Erreur: " . $e->getMessage();
    $user = [];
    $stats = [];
    $plans_actifs = [];
    $activites_recentes = [];
}

// Déterminer les icônes et couleurs pour les activités
function getActivityIcon($type) {
    switch ($type) {
        case 'ROI': return ['fa-coins', 'green'];
        case 'VIDEO': return ['fa-video', 'blue'];
        case 'PARRAINAGE': return ['fa-user-plus', 'purple'];
        default: return ['fa-circle', 'gray'];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Investian</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .page-transition { animation: fadeIn 0.5s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }
        .animate-slideIn { animation: slideIn 0.3s ease-out; }
        .animate-slideOut { animation: slideOut 0.3s ease-in; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Navigation -->
       
        
        <!-- Messages d'alerte -->
        <?php if ($error): ?>
        <div class="fixed top-4 right-4 z-50 px-6 py-3 bg-red-600 text-white rounded-lg shadow-lg font-medium animate-slideIn">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="fixed top-4 right-4 z-50 px-6 py-3 bg-green-600 text-white rounded-lg shadow-lg font-medium animate-slideIn">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="container mx-auto px-4 py-8">
            <div class="page-transition">
                <!-- En-tête du profil -->
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-8">
                    <!-- Bannière du profil -->
                    <div class="h-32 bg-gradient-to-r from-green-500 to-green-600 relative">
                        <form id="bannerForm" method="POST" enctype="multipart/form-data" class="absolute top-4 right-4">
                            <input type="hidden" name="action" value="upload_banner">
                            <label class="cursor-pointer bg-white bg-opacity-20 text-white p-2 rounded-lg hover:bg-opacity-30 transition">
                                <i class="fas fa-camera"></i>
                                <input type="file" name="profile_banner" class="hidden" accept="image/*" onchange="document.getElementById('bannerForm').submit()">
                            </label>
                        </form>
                    </div>
                    
                    <!-- Informations principales -->
                    <div class="px-6 pb-6">
                        <div class="flex flex-col md:flex-row md:items-end -mt-16">
                            <!-- Photo de profil -->
                            <div class="relative mb-4 md:mb-0">
                                <form id="photoForm" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="upload_photo">
                                    <div class="w-32 h-32 bg-white rounded-full border-4 border-white shadow-lg flex items-center justify-center">
                                        <?php if (isset($user['photo_url']) && $user['photo_url']): ?>
                                        <img src="<?php echo htmlspecialchars($user['photo_url']); ?>" 
                                             alt="Photo de profil" 
                                             class="w-full h-full rounded-full object-cover">
                                        <?php else: ?>
                                        <div class="w-full h-full bg-green-100 rounded-full flex items-center justify-center">
                                            <span class="text-5xl font-bold text-green-600">
                                                <?php echo strtoupper(substr($user['prenom'] ?? '?', 0, 1) . substr($user['nom'] ?? '?', 0, 1)); ?>
                                            </span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <label class="cursor-pointer absolute bottom-2 right-2 w-10 h-10 bg-green-600 rounded-full flex items-center justify-center border-2 border-white hover:bg-green-700 transition">
                                        <i class="fas fa-camera text-white text-sm"></i>
                                        <input type="file" name="profile_photo" class="hidden" accept="image/*" onchange="document.getElementById('photoForm').submit()">
                                    </label>
                                </form>
                            </div>
                            
                            <!-- Nom et actions -->
                            <div class="md:ml-6 flex-1">
                                <div class="flex flex-col md:flex-row md:items-center justify-between mb-4">
                                    <div>
                                        <h1 class="text-2xl md:text-3xl font-bold text-gray-800">
                                            <?php echo htmlspecialchars($user['prenom'] ?? '') . ' ' . htmlspecialchars($user['nom'] ?? ''); ?>
                                        </h1>
                                        <div class="flex items-center mt-2 space-x-3">
                                            <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                                                <i class="fas fa-user-check mr-1"></i>
                                                <?php echo htmlspecialchars($user['niveau_verification'] ?? 'Non vérifié'); ?>
                                            </span>
                                            <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                                                <i class="fas fa-circle text-xs mr-1"></i>
                                                <?php echo htmlspecialchars($user['statut_compte'] ?? 'Inactif'); ?>
                                            </span>
                                            <span class="text-gray-600">
                                                <i class="fas fa-calendar-alt mr-1"></i>
                                                Membre depuis <?php echo date('d/m/Y', strtotime($user['date_inscription_format'] ?? date('Y-m-d'))); ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4 md:mt-0 flex space-x-3">
                                        <a href="#edit-profile-modal" 
                                           onclick="openModal('edit-profile')"
                                           class="inline-flex items-center bg-green-600 text-white font-medium px-4 py-2 rounded-lg hover:bg-green-700 transition">
                                            <i class="fas fa-edit mr-2"></i>
                                            Modifier
                                        </a>
                                        <button onclick="shareProfile()" 
                                                class="inline-flex items-center border border-gray-300 text-gray-700 font-medium px-4 py-2 rounded-lg hover:bg-gray-50 transition">
                                            <i class="fas fa-share-alt mr-2"></i>
                                            Partager
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Code de parrainage -->
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                    <div class="flex flex-col md:flex-row md:items-center justify-between">
                                        <div>
                                            <p class="text-sm text-gray-500 mb-1">Code de parrainage</p>
                                            <p class="text-xl font-bold text-green-600"><?php echo htmlspecialchars($user['referral_code'] ?? ''); ?></p>
                                        </div>
                                        <div class="mt-2 md:mt-0">
                                            <button onclick="copyReferralCode('<?php echo htmlspecialchars($user['referral_code'] ?? ''); ?>')" 
                                                    class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded-lg transition">
                                                <i class="fas fa-copy mr-2"></i>
                                                Copier le code
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistiques principales -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Solde actuel -->
                    <div class="bg-white rounded-xl shadow-sm p-5">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <p class="text-sm text-gray-500">Solde actuel</p>
                                <p class="text-2xl font-bold text-green-600">
                                    <?php echo number_format($stats['solde_actuel'] ?? 0, 0, ',', ' '); ?> 
                                    <span class="text-lg">FCFA</span>
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-wallet text-green-600 text-xl"></i>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500">
                            <i class="fas fa-coins text-yellow-500 mr-1"></i>
                            Disponible pour retrait
                        </p>
                    </div>
                    
                    <!-- Investissement total -->
                    <div class="bg-white rounded-xl shadow-sm p-5">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <p class="text-sm text-gray-500">Investissement total</p>
                                <p class="text-2xl font-bold text-gray-800">
                                    <?php echo number_format($stats['investissement_total'] ?? 0, 0, ',', ' '); ?> 
                                    <span class="text-lg">FCFA</span>
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-chart-line text-blue-600 text-xl"></i>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500">
                            <i class="fas fa-bolt text-yellow-500 mr-1"></i>
                            <?php echo $stats['investissements_actifs'] ?? 0; ?> plan(s) actif(s)
                        </p>
                    </div>
                    
                    <!-- Gains totaux -->
                    <div class="bg-white rounded-xl shadow-sm p-5">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <p class="text-sm text-gray-500">Gains totaux</p>
                                <p class="text-2xl font-bold text-purple-600">
                                    <?php echo number_format($stats['gains_totaux'] ?? 0, 0, ',', ' '); ?> 
                                    <span class="text-lg">FCFA</span>
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-coins text-purple-600 text-xl"></i>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500">
                            <i class="fas fa-arrow-up text-green-500 mr-1"></i>
                            Cumul depuis l'inscription
                        </p>
                    </div>
                    
                    <!-- Parrains total -->
                    <div class="bg-white rounded-xl shadow-sm p-5">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <p class="text-sm text-gray-500">Personnes parrainées</p>
                                <p class="text-2xl font-bold text-gray-800"><?php echo $stats['parrains_total'] ?? 0; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-users text-yellow-600 text-xl"></i>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500">
                            <i class="fas fa-user-plus text-purple-500 mr-1"></i>
                            Programme de parrainage
                        </p>
                    </div>
                </div>

                <!-- Sections d'informations -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Informations personnelles -->
                    <div class="lg:col-span-2">
                        <!-- Informations de base -->
                        <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
                            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                                <i class="fas fa-user-circle text-green-600 mr-2"></i>
                                Informations personnelles
                            </h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-5">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Nom complet</label>
                                        <div class="text-lg font-medium text-gray-800">
                                            <?php echo htmlspecialchars($user['prenom'] ?? '') . ' ' . htmlspecialchars($user['nom'] ?? ''); ?>
                                        </div>
                                    </div>
                                   
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                                        <div class="text-lg font-medium text-gray-800">
                                            <?php echo htmlspecialchars($user['email'] ?? ''); ?>
                                            <span class="ml-2 text-green-600 text-sm">
                                                <i class="fas fa-check-circle"></i> Vérifié
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Téléphone</label>
                                        <div class="text-lg font-medium text-gray-800">
                                            <?php echo htmlspecialchars($user['telephone'] ?? 'Non renseigné'); ?>
                                            <?php if ($user['telephone'] != 'Non renseigné'): ?>
                                            <span class="ml-2 text-green-600 text-sm">
                                                <i class="fas fa-check-circle"></i> Vérifié
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="space-y-5">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Date de naissance</label>
                                        <div class="text-lg font-medium text-gray-800">
                                            <?php echo htmlspecialchars($user['date_naissance'] ?? 'Non spécifiée'); ?>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Genre</label>
                                        <div class="text-lg font-medium text-gray-800">
                                            <?php echo htmlspecialchars($user['genre'] ?? 'Non spécifié'); ?>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Localisation</label>
                                        <div class="text-lg font-medium text-gray-800">
                                            <?php echo htmlspecialchars($user['ville'] ?? '') . ', ' . htmlspecialchars($user['pays'] ?? ''); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-8 pt-6 border-t border-gray-200">
                                <a href="#edit-profile-modal" 
                                   onclick="openModal('edit-profile')"
                                   class="inline-flex items-center text-green-600 hover:text-green-700 font-medium">
                                    <i class="fas fa-edit mr-2"></i>
                                    Modifier les informations personnelles
                                </a>
                            </div>
                        </div>
                        
                        <!-- Plans d'investissement actifs -->
                        <div class="bg-white rounded-2xl shadow-sm p-6">
                            <div class="flex justify-between items-center mb-6">
                                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                                    <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                                    Plans d'investissement actifs
                                </h2>
                                <a href="investissement" class="text-green-600 hover:text-green-700 text-sm font-medium">
                                    Voir tous <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                            
                            <div class="space-y-4">
                                <?php if (!empty($plans_actifs)): ?>
                                    <?php foreach ($plans_actifs as $plan): ?>
                                    <div class="border border-gray-200 rounded-xl p-4 hover:bg-gray-50 transition-colors">
                                        <div class="flex justify-between items-center mb-3">
                                            <div>
                                                <h3 class="font-bold text-gray-800 text-lg"><?php echo htmlspecialchars($plan['nom']); ?></h3>
                                                <p class="text-sm text-gray-500">
                                                    Investi: <?php echo number_format($plan['montant'], 0, ',', ' '); ?> FCFA
                                                </p>
                                            </div>
                                            <span class="bg-green-100 text-green-800 text-xs font-medium px-3 py-1 rounded-full">
                                                <?php echo $plan['progression']; ?>% complété
                                            </span>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <div class="flex justify-between text-sm mb-1">
                                                <span class="text-gray-600">Période</span>
                                                <span class="font-medium">
                                                    <?php echo date('d/m', strtotime($plan['date_debut'])); ?> - 
                                                    <?php echo date('d/m/Y', strtotime($plan['date_fin'])); ?>
                                                </span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-green-500 h-2 rounded-full" style="width: <?php echo min(100, $plan['progression']); ?>%"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <p class="text-gray-500">Date de début</p>
                                                <p class="font-medium"><?php echo date('d/m/Y', strtotime($plan['date_debut'])); ?></p>
                                            </div>
                                            <div>
                                                <p class="text-gray-500">Date de fin</p>
                                                <p class="font-medium"><?php echo date('d/m/Y', strtotime($plan['date_fin'])); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <div class="text-center py-8">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-chart-line text-gray-400 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500 mb-4">Aucun plan d'investissement actif</p>
                                    <a href="investissement" 
                                       class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-lg transition">
                                        <i class="fas fa-plus mr-2"></i>
                                        Commencer à investir
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sidebar avec informations complémentaires -->
                    <div class="space-y-6">
                        <!-- Statut du compte -->
                        <div class="bg-white rounded-2xl shadow-sm p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Statut du compte</h3>
                            
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-shield-alt text-green-600"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-800">Niveau de vérification</p>
                                            <p class="text-sm text-gray-500">KYC complété</p>
                                        </div>
                                    </div>
                                    <span class="text-green-600 font-bold"><?php echo $stats['score_fiabilite'] ?? 50; ?>%</span>
                                </div>
                                
                                <div class="pt-4 border-t">
                                    <div class="flex justify-between text-sm mb-2">
                                        <span class="text-gray-600">Limite de retrait</span>
                                        <span class="font-bold text-green-600">500,000 FCFA/jour</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-500 h-2 rounded-full" 
                                             style="width: <?php echo min(100, (($stats['retraits_totaux'] ?? 0)/100000)*100); ?>%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Actions rapides -->
                        <div class="bg-white rounded-2xl shadow-sm p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Actions rapides</h3>
                            
                            <div class="grid grid-cols-2 gap-3">
                                <a href="investissement" class="quick-action-btn">
                                    <div class="quick-action-icon bg-green-100">
                                        <i class="fas fa-plus text-green-600"></i>
                                    </div>
                                    <span class="quick-action-text">Investir</span>
                                </a>
                                
                                <button  class="quick-action-btn">
                                    <a href="retrais">
                                    <div class="quick-action-icon bg-blue-100">
                                        <i class="fas fa-download text-blue-600"></i>
                                    </div>
                                    <span class="quick-action-text">Retrait</span>
                                    </a>
                                </button>
                                
                                <a href="videos" class="quick-action-btn">
                                    <div class="quick-action-icon bg-purple-100">
                                        <i class="fas fa-video text-purple-600"></i>
                                    </div>
                                    <span class="quick-action-text">Vidéos</span>
                                </a>
                                
                                <a href="parainage" class="quick-action-btn">
                                    <div class="quick-action-icon bg-yellow-100">
                                        <i class="fas fa-user-plus text-yellow-600"></i>
                                    </div>
                                    <span class="quick-action-text">Parrainer</span>
                                </a>
                                
                                <a href="#edit-profile-modal" onclick="openModal('edit-profile')" class="quick-action-btn">
                                    <div class="quick-action-icon bg-gray-100">
                                        <i class="fas fa-cog text-gray-600"></i>
                                    </div>
                                    <span class="quick-action-text">Paramètres</span>
                                </a>
                                
                                <a href="logout" class="quick-action-btn">
                                    <div class="quick-action-icon bg-red-100">
                                        <i class="fas fa-sign-out-alt text-red-600"></i>
                                    </div>
                                    <span class="quick-action-text">Déconnexion</span>
                                </a>
                            </div>
                        </div>
                        
                        <!-- Dernières activités -->
                        <div class="bg-white rounded-2xl shadow-sm p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Dernières activités</h3>
                            
                            <div class="space-y-3">
                                <?php if (!empty($activites_recentes)): ?>
                                    <?php foreach ($activites_recentes as $activite): 
                                        $icon = getActivityIcon($activite['type']);
                                    ?>
                                    <div class="activity-item">
                                        <div class="activity-icon bg-<?php echo $icon[1]; ?>-100">
                                            <i class="fas <?php echo $icon[0]; ?> text-<?php echo $icon[1]; ?>-600"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-800"><?php echo htmlspecialchars($activite['description']); ?></p>
                                            <p class="text-sm text-gray-500">
                                                <?php echo date('d/m', strtotime($activite['date'])); ?>, 
                                                <?php echo date('H:i', strtotime($activite['date'])); ?>
                                            </p>
                                        </div>
                                        <span class="text-green-600 font-bold">
                                            +<?php echo number_format($activite['montant'], 0, ',', ' '); ?> FCFA
                                        </span>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <p class="text-gray-500">Aucune activité récente</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <a href="transactions" class="text-green-600 hover:text-green-700 text-sm font-medium">
                                    <i class="fas fa-history mr-1"></i>
                                    Voir tout l'historique
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Modifier le profil -->
    <div id="edit-profile-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-2xl transform transition-all max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-800">Modifier le profil</h3>
                    <button onclick="closeModal('edit-profile')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- Onglets -->
                <div class="flex border-b border-gray-200 mb-6">
                    <button onclick="switchTab('profile-tab', 'info')" 
                            class="tab-btn active px-4 py-2 font-medium text-green-600 border-b-2 border-green-600">
                        <i class="fas fa-user mr-2"></i>
                        Informations
                    </button>
                    <button onclick="switchTab('profile-tab', 'email')" 
                            class="tab-btn px-4 py-2 font-medium text-gray-500 hover:text-gray-700">
                        <i class="fas fa-envelope mr-2"></i>
                        Email
                    </button>
                    <button onclick="switchTab('profile-tab', 'password')" 
                            class="tab-btn px-4 py-2 font-medium text-gray-500 hover:text-gray-700">
                        <i class="fas fa-lock mr-2"></i>
                        Mot de passe
                    </button>
                </div>
                
                <!-- Informations personnelles -->
                <div id="tab-info" class="tab-content active">
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                                <input type="text" name="prenom" value="<?php echo htmlspecialchars($user['prenom'] ?? ''); ?>" 
                                       required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                                <input type="text" name="nom" value="<?php echo htmlspecialchars($user['nom'] ?? ''); ?>" 
                                       required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['telephone'] ?? ''); ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" onclick="closeModal('edit-profile')" 
                                    class="px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                                Annuler
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                                Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Changer l'email -->
                <div id="tab-email" class="tab-content hidden">
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="update_email">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nouvel email</label>
                            <input type="email" name="email" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe actuel</label>
                            <input type="password" name="password" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" onclick="closeModal('edit-profile')" 
                                    class="px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                                Annuler
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                                Changer l'email
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Changer le mot de passe -->
                <div id="tab-password" class="tab-content hidden">
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe actuel</label>
                            <input type="password" name="current_password" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe</label>
                            <input type="password" name="new_password" required minlength="6"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe</label>
                            <input type="password" name="confirm_password" required minlength="6"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" onclick="closeModal('edit-profile')" 
                                    class="px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                                Annuler
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                                Changer le mot de passe
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de retrait -->
    <div id="retraitModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md transform transition-all">
            <form method="POST">
                <input type="hidden" name="action" value="initiate_withdrawal">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-gray-800">Effectuer un retrait</h3>
                        <button type="button" onclick="closeRetraitModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Montant (FCFA)</label>
                            <input type="number" name="withdrawal_amount" min="1000" max="100000" required
                                   placeholder="Ex: 50000"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                            <p class="text-xs text-gray-500 mt-1">Minimum: 1,000 FCFA - Maximum: 100,000 FCFA</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Méthode de retrait</label>
                            <select name="withdrawal_method" required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                                <option value="">Sélectionner une méthode</option>
                                <option value="orange">Orange Money</option>
                                <option value="mtn">MTN Mobile Money</option>
                                <option value="mobile_money">Mobile Money (Autre)</option>
                                <option value="visa">Carte Visa/Mastercard</option>
                            </select>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-700">
                                <i class="fas fa-info-circle text-green-600 mr-2"></i>
                                Le traitement prend généralement 24h. Vérifiez vos informations de paiement avant de confirmer.
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex space-x-3 mt-8">
                        <button type="button" onclick="closeRetraitModal()" 
                                class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                            Annuler
                        </button>
                        <button type="submit" 
                                class="flex-1 px-4 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                            <i class="fas fa-download mr-2"></i>
                            Confirmer le retrait
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Variables
    const referralCode = '<?php echo htmlspecialchars($user["referral_code"] ?? ""); ?>';
    const userEmail = '<?php echo htmlspecialchars($user["email"] ?? ""); ?>';
    const userName = '<?php echo htmlspecialchars($user["prenom"] ?? "") . " " . htmlspecialchars($user["nom"] ?? ""); ?>';

    // Fonctions pour les modals
    function openModal(modalName) {
        document.getElementById(modalName + '-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalName) {
        document.getElementById(modalName + '-modal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Onglets
    function switchTab(tabGroup, tabId) {
        // Désactiver tous les onglets
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active', 'text-green-600', 'border-green-600');
            btn.classList.add('text-gray-500');
        });
        
        // Activer l'onglet cliqué
        const activeBtn = event.currentTarget;
        activeBtn.classList.add('active', 'text-green-600', 'border-green-600');
        activeBtn.classList.remove('text-gray-500');
        
        // Masquer tous les contenus
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
            content.classList.add('hidden');
        });
        
        // Afficher le contenu correspondant
        document.getElementById('tab-' + tabId).classList.remove('hidden');
        document.getElementById('tab-' + tabId).classList.add('active');
    }

    // Fonctions de profil
    function shareProfile() {
        const profileLink = window.location.origin + '/?page=register&ref=' + referralCode;
        if (navigator.share) {
            navigator.share({
                title: 'Mon profil Investian - ' + userName,
                text: 'Découvrez mon profil sur Investian',
                url: profileLink
            });
        } else {
            navigator.clipboard.writeText(profileLink).then(() => {
                showToast('✅ Lien du profil copié dans le presse-papier !', 'success');
            });
        }
    }

    function copyReferralCode(code) {
        navigator.clipboard.writeText(code).then(() => {
            showToast('✅ Code de parrainage copié !', 'success');
        });
    }

    function openRetraitModal() {
        document.getElementById('retraitModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeRetraitModal() {
        document.getElementById('retraitModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Fonction utilitaire pour les notifications
    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-medium animate-slideIn ${
            type === 'success' ? 'bg-green-600' : 'bg-red-600'
        }`;
        toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle mr-2"></i>
                ${message}
            </div>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('animate-slideOut');
            setTimeout(() => {
                if (toast.parentNode) {
                    document.body.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }

    // Validation des formulaires
    document.addEventListener('DOMContentLoaded', function() {
        // Validation du formulaire de retrait
        const retraitForm = document.querySelector('#retraitModal form');
        if (retraitForm) {
            retraitForm.addEventListener('submit', function(e) {
                const amount = parseFloat(this.querySelector('[name="withdrawal_amount"]').value);
                const method = this.querySelector('[name="withdrawal_method"]').value;
                
                if (!method) {
                    e.preventDefault();
                    showToast('Veuillez sélectionner une méthode de retrait', 'error');
                    return;
                }
                
                if (amount < 1000 || amount > 100000) {
                    e.preventDefault();
                    showToast('Le montant doit être entre 1,000 et 100,000 FCFA', 'error');
                    return;
                }
                
                if (!confirm(`Confirmer le retrait de ${amount.toLocaleString('fr-FR')} FCFA via ${getMethodName(method)} ?`)) {
                    e.preventDefault();
                }
            });
        }
        
        // Auto-hide les messages d'erreur/succès
        setTimeout(() => {
            const alerts = document.querySelectorAll('.fixed.top-4.right-4');
            alerts.forEach(alert => {
                alert.classList.add('animate-slideOut');
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.parentNode.removeChild(alert);
                    }
                }, 300);
            });
        }, 5000);
    });

    function getMethodName(method) {
        const methods = {
            'orange': 'Orange Money',
            'mtn': 'MTN Mobile Money',
            'mobile_money': 'Mobile Money',
            'visa': 'Carte Visa/Mastercard'
        };
        return methods[method] || method;
    }

    // Animation CSS supplémentaire
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        .animate-slideIn {
            animation: slideIn 0.3s ease-out;
        }
        
        .animate-slideOut {
            animation: slideOut 0.3s ease-in;
        }
        
        .quick-action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            transition: background-color 0.2s;
            cursor: pointer;
        }
        
        .quick-action-btn:hover {
            background-color: #f9fafb;
        }
        
        .quick-action-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
        }
        
        .quick-action-text {
            font-size: 14px;
            font-weight: 500;
            color: #1f2937;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            padding: 8px;
            border-radius: 8px;
            transition: background-color 0.2s;
        }
        
        .activity-item:hover {
            background-color: #f9fafb;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .tab-btn {
            transition: all 0.2s;
        }
        
        .tab-btn.active {
            color: #059669;
            border-bottom-color: #059669;
        }
        
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        #edit-profile-modal > div,
        #retraitModal > div {
            animation: modalFadeIn 0.3s ease-out;
        }
    `;
    document.head.appendChild(style);

    // Fermer les modals en cliquant à l'extérieur
    document.getElementById('edit-profile-modal').addEventListener('click', (e) => {
        if (e.target.id === 'edit-profile-modal') {
            closeModal('edit-profile');
        }
    });

    document.getElementById('retraitModal').addEventListener('click', (e) => {
        if (e.target.id === 'retraitModal') {
            closeRetraitModal();
        }
    });
    </script>
</body>
</html>