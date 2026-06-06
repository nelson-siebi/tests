<?php
// pages/home.php

require_once 'config/db.php';

// Vérifier connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: login');
    exit();
}

$userId = $_SESSION['user_id'];
$db = Database::getInstance()->getConnection();

// RÉCUPÉRATION DES DONNÉES RÉELLES
function getUserRealStats($userId)
{
    global $db;

    // Récupérer les soldes depuis wallets
    $sql = "SELECT 
                solde_investissement,
                solde_publicite,
                solde_parrainage,
                total_retrait_invest,
                total_retrait_pub,
                total_retrait_parrain,
                total_depots
            FROM wallets 
            WHERE user_id = :user_id";

    $stmt = $db->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    $wallet = $stmt->fetch();

    if (!$wallet) {
        // Créer le wallet s'il n'existe pas
        $sqlInsert = "INSERT INTO wallets (user_id) VALUES (:user_id)";
        $stmtInsert = $db->prepare($sqlInsert);
        $stmtInsert->execute([':user_id' => $userId]);

        $wallet = [
            'solde_investissement' => 0,
            'solde_publicite' => 0,
            'solde_parrainage' => 0,
            'total_depots' => 0
        ];
    }

    // Calcul du montant total réellement investi (somme des plans)
    $sqlTotalInvesti = "SELECT COALESCE(SUM(montant_investi), 0) as total_investi 
                       FROM user_plans 
                       WHERE user_id = :user_id"; // On compte tout l'historique d'investissement

    $stmtTotalInvesti = $db->prepare($sqlTotalInvesti);
    $stmtTotalInvesti->execute([':user_id' => $userId]);
    $totalInvestiResult = $stmtTotalInvesti->fetch();
    $total_investi = $totalInvestiResult['total_investi'] ?? 0;

    // Nombre d'investissements actifs
    $sqlActive = "SELECT COUNT(*) as count 
                 FROM user_plans 
                 WHERE user_id = :user_id 
                 AND statut = 'active'
                 AND CURDATE() BETWEEN DATE(date_debut) AND DATE(date_fin)";

    $stmtActive = $db->prepare($sqlActive);
    $stmtActive->execute([':user_id' => $userId]);
    $activeResult = $stmtActive->fetch();
    $investissements_actifs = $activeResult['count'] ?? 0;

    // Gain journalier (ROI des investissements actifs + Gain Vidéos Potentiel)
    $sqlGainJour = "SELECT COALESCE(SUM(p.roi_journalier + (p.videos_par_jour * p.gain_par_video)), 0) as gain_journalier
                   FROM user_plans up
                   JOIN plans p ON up.plan_id = p.id
                   WHERE up.user_id = :user_id 
                   AND up.statut = 'active'
                   AND CURDATE() BETWEEN DATE(up.date_debut) AND DATE(up.date_fin)";

    $stmtGainJour = $db->prepare($sqlGainJour);
    $stmtGainJour->execute([':user_id' => $userId]);
    $gainJourResult = $stmtGainJour->fetch();
    $gain_journalier = $gainJourResult['gain_journalier'] ?? 0;

    // Vidéos restantes aujourd'hui
    $sqlVideos = "SELECT 
                    COALESCE(SUM(p.videos_par_jour), 0) as total_videos,
                    COUNT(av.id) as videos_vues
                  FROM user_plans up
                  JOIN plans p ON up.plan_id = p.id
                  LEFT JOIN ads_views av ON av.user_id = up.user_id 
                    AND DATE(av.date_view) = CURDATE()
                  WHERE up.user_id = :user_id 
                  AND up.statut = 'active'
                  AND CURDATE() BETWEEN DATE(up.date_debut) AND DATE(up.date_fin)";

    $stmtVideos = $db->prepare($sqlVideos);
    $stmtVideos->execute([':user_id' => $userId]);
    $videosResult = $stmtVideos->fetch();
    $videos_restantes = max(0, ($videosResult['total_videos'] ?? 0) - ($videosResult['videos_vues'] ?? 0));

    // Nombre de filleuls
    $sqlFilleuls = "SELECT COUNT(*) as count 
                   FROM referrals 
                   WHERE parrain_id = :user_id";

    $stmtFilleuls = $db->prepare($sqlFilleuls);
    $stmtFilleuls->execute([':user_id' => $userId]);
    $filleulsResult = $stmtFilleuls->fetch();
    $parrains_total = $filleulsResult['count'] ?? 0;

    // Calcul du solde total
    $solde_total = $wallet['solde_investissement'] + $wallet['solde_publicite'] + $wallet['solde_parrainage'];

    return [
        'solde_total' => $solde_total,
        'solde_investissement' => $wallet['solde_investissement'],
        'solde_publicite' => $wallet['solde_publicite'],
        'solde_parrainage' => $wallet['solde_parrainage'],
        'investissements_actifs' => $investissements_actifs,
        'gain_journalier' => $gain_journalier,
        'videos_restantes' => $videos_restantes,
        'parrains_total' => $parrains_total,
        'total_depots' => $wallet['total_depots'],
        'total_investi' => $total_investi // Ajout du vrai total investi
    ];
}

// Récupérer les transactions récentes
function getRecentTransactions($userId)
{
    global $db;

    $sql = "SELECT 
                t.type,
                t.source,
                t.montant,
                t.statut,
                t.created_at,
                t.note as description,
                CASE 
                    WHEN t.type = 'depot' THEN 'Dépôt'
                    WHEN t.type = 'retrait' THEN 'Retrait'
                    ELSE t.type
                END as type_display,
                CASE 
                    WHEN t.source = 'investissement' THEN 'ROI investissement'
                    WHEN t.source = 'publicite' THEN 'Gain vidéo'
                    WHEN t.source = 'parrainage' THEN 'Bonus parrainage'
                    ELSE t.source
                END as source_display
            FROM transactions t
            WHERE t.user_id = :user_id
            ORDER BY t.created_at DESC
            LIMIT 5";

    $stmt = $db->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    $transactions = $stmt->fetchAll();

    // Transformer pour le format attendu
    $formatted = [];
    foreach ($transactions as $tx) {
        $formatted[] = [
            'type' => $tx['source'],
            'description' => $tx['description'] ?: $tx['source_display'],
            'montant' => $tx['montant'],
            'date' => date('d/m/Y H:i', strtotime($tx['created_at'])),
            'statut' => $tx['statut'] == 'success' ? 'success' : 'pending'
        ];
    }

    return $formatted;
}

// Récupérer les plans actifs réels
function getActivePlans($userId)
{
    global $db;

    $sql = "SELECT 
                up.id,
                p.nom,
                up.montant_investi,
                up.date_debut,
                up.date_fin,
                p.roi_journalier,
                DATEDIFF(CURDATE(), up.date_debut) as jours_passes,
                DATEDIFF(up.date_fin, up.date_debut) as duree_totale
            FROM user_plans up
            JOIN plans p ON up.plan_id = p.id
            WHERE up.user_id = :user_id
            AND up.statut = 'active'
            AND CURDATE() BETWEEN DATE(up.date_debut) AND DATE(up.date_fin)
            ORDER BY up.date_debut DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll();
}

// Récupérer les données
$user_stats = getUserRealStats($userId);
$transactions = getRecentTransactions($userId);
$activePlans = getActivePlans($userId);

// Récupérer le nom de l'utilisateur
$sqlUser = "SELECT CONCAT(prenom, ' ', nom) as nom_complet, email 
           FROM users 
           WHERE id = :user_id";
$stmtUser = $db->prepare($sqlUser);
$stmtUser->execute([':user_id' => $userId]);
$userInfo = $stmtUser->fetch();
$userName = $userInfo['nom_complet'] ?? 'Utilisateur';
?>

<div class="page-transition">
    <!-- Bannière de bienvenue avec données réelles -->
    <div
        class="bg-gradient-to-r from-primary-600 via-primary-700 to-primary-800 rounded-2xl p-6 text-white shadow-lg mb-8 relative overflow-hidden">
        <!-- Effet décoratif -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-32 translate-x-16"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/5 rounded-full translate-y-24 -translate-x-12"></div>

        <div class="relative z-10">
            <div class="flex flex-col md:flex-row md:items-center justify-between">
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold mb-2">Bonjour, <?php echo htmlspecialchars($userName); ?>
                        ! 👋</h2>
                    <p class="text-primary-100 opacity-90">Bienvenue sur votre tableau de bord</p>
                    <div class="flex items-center mt-4 space-x-3">
                        <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-sm">
                            <i class="fas fa-calendar-alt mr-1"></i> <?php echo date('d/m/Y'); ?>
                        </span>

                    </div>
                </div>
                <div class="mt-4 md:mt-0 text-center md:text-right">
                    <p class="text-sm text-primary-100 mb-1">Solde total disponible</p>
                    <p class="text-3xl md:text-4xl font-bold tracking-tight">
                        <?php echo number_format($user_stats['solde_total'], 0, ',', ' '); ?>
                        <span class="text-xl">FCFA</span>
                    </p>
                    <p class="text-xs text-primary-100 mt-2">
                        <i class="fas fa-wallet mr-1"></i>
                        Investissement total : <?php echo number_format($user_stats['total_investi'], 0, ',', ' '); ?>
                        FCFA
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Cartes de statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Solde investissement -->
        <a href="investissement" class="group">
            <div
                class="bg-white rounded-2xl shadow-sm p-5 hover:shadow-lg transition-all duration-300 border border-gray-100 hover:border-secondary-200 hover:scale-[1.02]">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Solde investissement</p>
                        <p class="text-2xl font-bold text-gray-800">
                            <?php echo number_format($user_stats['solde_investissement'], 0, ',', ' '); ?>
                            <span class="text-lg">FCFA</span>
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-secondary-100 to-secondary-50 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-chart-line text-secondary-600 text-xl"></i>
                    </div>
                </div>
                <div class="flex items-center text-xs text-gray-500">
                    <div class="w-8 h-8 bg-secondary-50 rounded-lg flex items-center justify-center mr-2">
                        <i class="fas fa-bolt text-secondary-500 text-sm"></i>
                    </div>
                    <span><?php echo $user_stats['investissements_actifs']; ?> plan(s) actif(s)</span>
                </div>
            </div>
        </a>

        <!-- Solde publicité -->
        <a href="videos" class="group">
            <div
                class="bg-white rounded-2xl shadow-sm p-5 hover:shadow-lg transition-all duration-300 border border-gray-100 hover:border-blue-200 hover:scale-[1.02]">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Solde publicité</p>
                        <p class="text-2xl font-bold text-gray-800">
                            <?php echo number_format($user_stats['solde_publicite'], 0, ',', ' '); ?>
                            <span class="text-lg">FCFA</span>
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-50 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-video text-blue-600 text-xl"></i>
                    </div>
                </div>
                <div class="flex items-center text-xs text-gray-500">
                    <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center mr-2">
                        <i class="fas fa-film text-blue-500 text-sm"></i>
                    </div>
                    <span><?php echo $user_stats['videos_restantes']; ?> vidéo(s) disponible(s)</span>
                </div>
            </div>
        </a>

        <!-- Solde parrainage -->
        <a href="parainage" class="group">
            <div
                class="bg-white rounded-2xl shadow-sm p-5 hover:shadow-lg transition-all duration-300 border border-gray-100 hover:border-purple-200 hover:scale-[1.02]">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Solde parrainage</p>
                        <p class="text-2xl font-bold text-gray-800">
                            <?php echo number_format($user_stats['solde_parrainage'], 0, ',', ' '); ?>
                            <span class="text-lg">FCFA</span>
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-purple-100 to-purple-50 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-users text-purple-600 text-xl"></i>
                    </div>
                </div>
                <div class="flex items-center text-xs text-gray-500">
                    <div class="w-8 h-8 bg-purple-50 rounded-lg flex items-center justify-center mr-2">
                        <i class="fas fa-user-plus text-purple-500 text-sm"></i>
                    </div>
                    <span><?php echo $user_stats['parrains_total']; ?> filleul(s)</span>
                </div>
            </div>
        </a>

        <!-- Gain journalier -->
        <div
            class="bg-gradient-to-r from-yellow-500 via-yellow-500 to-amber-500 rounded-2xl shadow-sm p-5 text-white relative overflow-hidden group">
            <!-- Effet brillant -->
            <div
                class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000">
            </div>

            <div class="relative z-10">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-sm text-yellow-100 mb-1">Gain estimé aujourd'hui</p>
                        <p class="text-2xl font-bold">
                            +<?php echo number_format($user_stats['gain_journalier'], 0, ',', ' '); ?>
                            <span class="text-lg">FCFA</span>
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center group-hover:rotate-12 transition-transform duration-300">
                        <i class="fas fa-coins text-xl"></i>
                    </div>
                </div>
                <div class="flex items-center text-sm text-yellow-100">
                    <i class="fas fa-arrow-trend-up mr-2"></i>
                    <span>Vos investissements travaillent pour vous</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides - NOUVEAU STYLE -->
    <div class="mb-10">
        <!-- En-tête avec effet de gradient -->
        <div class="relative mb-8">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-purple-500/10 rounded-2xl blur-xl"></div>
            <div
                class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white/90 backdrop-blur-sm rounded-2xl p-6 border border-gray-200/50 shadow-sm">
                <div>
                    <h3
                        class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                        <i class="fas fa-bolt text-yellow-500 mr-3"></i>
                        Actions Rapides
                    </h3>
                    <p class="text-gray-600 mt-2 text-sm">Accédez rapidement aux fonctionnalités principales</p>
                </div>
                <div class="flex items-center">
                    <div class="relative">
                        <div
                            class="absolute -inset-1 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full blur opacity-25">
                        </div>
                        <span
                            class="relative bg-white text-gray-700 text-sm font-medium px-4 py-2 rounded-full border border-gray-200">
                            <i class="fas fa-rocket text-blue-500 mr-2"></i>
                            Navigation instantanée
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grille d'actions améliorée -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-8 gap-4">
            <!-- Investir -->
            <a href="investissement"
                class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-white to-gray-50 border border-gray-200/50 p-5 transition-all duration-300 hover:scale-[1.02] hover:shadow-xl hover:border-secondary-200">
                <div
                    class="absolute inset-0 bg-gradient-to-br from-secondary-500/5 to-secondary-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                </div>

                <!-- Effet de halo -->
                <div
                    class="absolute -top-10 -right-10 w-20 h-20 bg-gradient-to-br from-secondary-400/20 to-secondary-400/20 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500">
                </div>

                <!-- Icône -->
                <div class="relative mb-4">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-secondary-400 to-secondary-500 rounded-xl blur group-hover:blur-md transition-all duration-300">
                    </div>
                    <div
                        class="relative w-14 h-14 bg-gradient-to-br from-secondary-500 to-secondary-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-chart-line text-white text-xl"></i>
                    </div>
                </div>

                <!-- Contenu -->
                <div class="relative">
                    <h4 class="font-bold text-gray-800 mb-1">Investir</h4>
                    <p class="text-xs text-gray-600 mb-2">Augmentez vos gains</p>
                    <div class="flex items-center justify-between">
                        <span
                            class="inline-flex items-center text-xs font-medium bg-secondary-50 text-secondary-700 px-2 py-1 rounded-full">
                            <i class="fas fa-arrow-up mr-1 text-xs"></i>
                            +ROI
                        </span>
                        <i
                            class="fas fa-chevron-right text-gray-400 text-xs group-hover:text-secondary-500 transition-colors"></i>
                    </div>
                </div>
            </a>

            <!-- Retrait -->
            <a href="retrais"
                class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-white to-gray-50 border border-gray-200/50 p-5 transition-all duration-300 hover:scale-[1.02] hover:shadow-xl hover:border-blue-200">
                <div
                    class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-cyan-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                </div>

                <div
                    class="absolute -top-10 -right-10 w-20 h-20 bg-gradient-to-br from-blue-400/20 to-cyan-400/20 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500">
                </div>

                <div class="relative mb-4">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-blue-400 to-cyan-500 rounded-xl blur group-hover:blur-md transition-all duration-300">
                    </div>
                    <div
                        class="relative w-14 h-14 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-download text-white text-xl"></i>
                    </div>
                </div>

                <div class="relative">
                    <h4 class="font-bold text-gray-800 mb-1">Retrait</h4>
                    <p class="text-xs text-gray-600 mb-2">Recevez vos gains</p>
                    <div class="flex items-center justify-between">
                        <span
                            class="inline-flex items-center text-xs font-medium bg-blue-50 text-blue-700 px-2 py-1 rounded-full">
                            <i class="fas fa-bolt mr-1 text-xs"></i>
                            Rapide
                        </span>
                        <i
                            class="fas fa-chevron-right text-gray-400 text-xs group-hover:text-blue-500 transition-colors"></i>
                    </div>
                </div>
            </a>

            <!-- Vidéos -->
            <a href="videos"
                class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-white to-gray-50 border border-gray-200/50 p-5 transition-all duration-300 hover:scale-[1.02] hover:shadow-xl hover:border-purple-200">
                <div
                    class="absolute inset-0 bg-gradient-to-br from-purple-500/5 to-pink-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                </div>

                <div
                    class="absolute -top-10 -right-10 w-20 h-20 bg-gradient-to-br from-purple-400/20 to-pink-400/20 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500">
                </div>

                <div class="relative mb-4">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-purple-400 to-pink-500 rounded-xl blur group-hover:blur-md transition-all duration-300">
                    </div>
                    <div
                        class="relative w-14 h-14 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-play-circle text-white text-xl"></i>
                    </div>
                </div>

                <div class="relative">
                    <h4 class="font-bold text-gray-800 mb-1">Vidéos</h4>
                    <p class="text-xs text-gray-600 mb-2">Gagnez en regardant</p>
                    <div class="flex items-center justify-between">
                        <span
                            class="inline-flex items-center text-xs font-medium bg-purple-50 text-purple-700 px-2 py-1 rounded-full">
                            <i class="fas fa-video mr-1 text-xs"></i>
                            <?php echo $user_stats['videos_restantes']; ?> dispo
                        </span>
                        <i
                            class="fas fa-chevron-right text-gray-400 text-xs group-hover:text-purple-500 transition-colors"></i>
                    </div>
                </div>
            </a>

            <!-- Parrainage -->
            <a href="parainage"
                class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-white to-gray-50 border border-gray-200/50 p-5 transition-all duration-300 hover:scale-[1.02] hover:shadow-xl hover:border-orange-200">
                <div
                    class="absolute inset-0 bg-gradient-to-br from-orange-500/5 to-amber-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                </div>

                <div
                    class="absolute -top-10 -right-10 w-20 h-20 bg-gradient-to-br from-orange-400/20 to-amber-400/20 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500">
                </div>

                <div class="relative mb-4">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-orange-400 to-amber-500 rounded-xl blur group-hover:blur-md transition-all duration-300">
                    </div>
                    <div
                        class="relative w-14 h-14 bg-gradient-to-br from-orange-500 to-amber-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-user-plus text-white text-xl"></i>
                    </div>
                </div>

                <div class="relative">
                    <h4 class="font-bold text-gray-800 mb-1">Parrainer</h4>
                    <p class="text-xs text-gray-600 mb-2">Invitez des amis</p>
                    <div class="flex items-center justify-between">
                        <span
                            class="inline-flex items-center text-xs font-medium bg-orange-50 text-orange-700 px-2 py-1 rounded-full">
                            <i class="fas fa-gift mr-1 text-xs"></i>
                            15% Bonus
                        </span>
                        <i
                            class="fas fa-chevron-right text-gray-400 text-xs group-hover:text-orange-500 transition-colors"></i>
                    </div>
                </div>
            </a>


            <!-- Transactions -->
            <button onclick="showAllTransactions()"
                class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-white to-gray-50 border border-gray-200/50 p-5 transition-all duration-300 hover:scale-[1.02] hover:shadow-xl hover:border-red-200">
                <div
                    class="absolute inset-0 bg-gradient-to-br from-red-500/5 to-rose-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                </div>

                <div
                    class="absolute -top-10 -right-10 w-20 h-20 bg-gradient-to-br from-red-400/20 to-rose-400/20 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500">
                </div>

                <div class="relative mb-4">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-red-400 to-rose-500 rounded-xl blur group-hover:blur-md transition-all duration-300">
                    </div>
                    <div
                        class="relative w-14 h-14 bg-gradient-to-br from-red-500 to-rose-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-exchange-alt text-white text-xl"></i>
                    </div>
                </div>

                <div class="relative">
                    <h4 class="font-bold text-gray-800 mb-1">Transactions</h4>
                    <p class="text-xs text-gray-600 mb-2">Historique complet</p>
                    <div class="flex items-center justify-between">
                        <span
                            class="inline-flex items-center text-xs font-medium bg-red-50 text-red-700 px-2 py-1 rounded-full">
                            <i class="fas fa-history mr-1 text-xs"></i>
                            Voir tout
                        </span>
                        <i
                            class="fas fa-chevron-right text-gray-400 text-xs group-hover:text-red-500 transition-colors"></i>
                    </div>
                </div>
            </button>

            <!-- Support -->
            <button onclick="openSupport()"
                class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-white to-gray-50 border border-gray-200/50 p-5 transition-all duration-300 hover:scale-[1.02] hover:shadow-xl hover:border-indigo-200">
                <div
                    class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 to-violet-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                </div>

                <div
                    class="absolute -top-10 -right-10 w-20 h-20 bg-gradient-to-br from-indigo-400/20 to-violet-400/20 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500">
                </div>

                <div class="relative mb-4">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-indigo-400 to-violet-500 rounded-xl blur group-hover:blur-md transition-all duration-300">
                    </div>
                    <div
                        class="relative w-14 h-14 bg-gradient-to-br from-indigo-500 to-violet-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-headset text-white text-xl"></i>
                    </div>
                </div>

                <div class="relative">
                    <h4 class="font-bold text-gray-800 mb-1">Support</h4>
                    <p class="text-xs text-gray-600 mb-2">Assistance 24h/24</p>
                    <div class="flex items-center justify-between">
                        <span
                            class="inline-flex items-center text-xs font-medium bg-indigo-50 text-indigo-700 px-2 py-1 rounded-full">
                            <i class="fas fa-clock mr-1 text-xs"></i>
                            24/7
                        </span>
                        <i
                            class="fas fa-chevron-right text-gray-400 text-xs group-hover:text-indigo-500 transition-colors"></i>
                    </div>
                </div>
            </button>

            <!-- Profil -->
            <a href="profile"
                class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-white to-gray-50 border border-gray-200/50 p-5 transition-all duration-300 hover:scale-[1.02] hover:shadow-xl hover:border-gray-300">
                <div
                    class="absolute inset-0 bg-gradient-to-br from-gray-500/5 to-slate-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                </div>

                <div
                    class="absolute -top-10 -right-10 w-20 h-20 bg-gradient-to-br from-gray-400/20 to-slate-400/20 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500">
                </div>

                <div class="relative mb-4">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-gray-400 to-slate-500 rounded-xl blur group-hover:blur-md transition-all duration-300">
                    </div>
                    <div
                        class="relative w-14 h-14 bg-gradient-to-br from-gray-600 to-slate-700 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-user text-white text-xl"></i>
                    </div>
                </div>

                <div class="relative">
                    <h4 class="font-bold text-gray-800 mb-1">Profil</h4>
                    <p class="text-xs text-gray-600 mb-2">Gérez votre compte</p>
                    <div class="flex items-center justify-between">
                        <span
                            class="inline-flex items-center text-xs font-medium bg-gray-100 text-gray-700 px-2 py-1 rounded-full">
                            <i class="fas fa-cog mr-1 text-xs"></i>
                            Votre profil
                        </span>
                        <i
                            class="fas fa-chevron-right text-gray-400 text-xs group-hover:text-gray-600 transition-colors"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Navigation par points (pour mobile) 
    <div class="flex justify-center mt-8 lg:hidden">
        <div class="flex space-x-2">
            <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
            <span class="w-2 h-2 bg-gray-300 rounded-full"></span>
            <span class="w-2 h-2 bg-gray-300 rounded-full"></span>
            <span class="w-2 h-2 bg-gray-300 rounded-full"></span>
        </div>
    </div> -->
    </div>

    <!-- CSS additionnel pour les effets -->
    <style>
        /* Animation de pulse sur les cartes */
        @keyframes subtlePulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.01);
            }
        }

        .action-card-new:hover {
            animation: subtlePulse 0.5s ease-in-out;
        }

        /* Effet de transition pour les ombres */
        .action-card-new {
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .action-card-new:hover {
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.1);
        }

        /* Effet de lumière sur les icônes */
        .icon-circle {
            position: relative;
            overflow: hidden;
        }

        .icon-circle::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg,
                    transparent,
                    rgba(255, 255, 255, 0.3),
                    transparent);
            transform: rotate(45deg);
            transition: transform 0.6s ease;
        }

        .action-card-new:hover .icon-circle::after {
            transform: rotate(45deg) translate(50%, 50%);
        }

        /* Animation du texte */
        .action-title-new {
            position: relative;
            overflow: hidden;
        }

        .action-title-new::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: -100%;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, currentColor, transparent);
            transition: left 0.3s ease;
        }

        .action-card-new:hover .action-title-new::after {
            left: 100%;
        }
    </style>

    <!-- JavaScript pour les interactions -->
    <script>
        // Effet de parallaxe sur les cartes
        document.querySelectorAll('.action-card-new').forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                const centerX = rect.width / 2;
                const centerY = rect.height / 2;

                const rotateX = (y - centerY) / 25;
                const rotateY = (centerX - x) / 25;

                card.style.transform = `
                perspective(1000px)
                rotateX(${rotateX}deg)
                rotateY(${rotateY}deg)
                scale3d(1.02, 1.02, 1.02)
            `;
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale3d(1, 1, 1)';
            });
        });

        // Animation d'apparition en cascade
        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.action-card-new');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';

                setTimeout(() => {
                    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });

        // Effet de son au clic (optionnel)
        function playClickSound() {
            const audio = new Audio('data:audio/wav;base64,UklGRigAAABXQVZFZm10IBIAAAABAAEAQB8AAEAfAAABAAgAZGF0YQ');
            audio.volume = 0.1;
            audio.play().catch(() => { });
        }

        document.querySelectorAll('.action-card-new').forEach(card => {
            card.addEventListener('click', playClickSound);
        });
    </script>

    <!-- Dernières transactions -->
    <div class="bg-white rounded-2xl shadow-sm p-6 mb-8 border border-gray-100">
        <div class="flex justify-between items-center mb-4">
            <div class="flex items-center">
                <div
                    class="w-10 h-10 bg-gradient-to-br from-gray-100 to-gray-50 rounded-xl flex items-center justify-center mr-3">
                    <i class="fas fa-exchange-alt text-gray-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800">Dernières transactions</h3>
            </div>
            <button onclick="showAllTransactions()" class="btn-text">
                <span class="text-primary-600 hover:text-primary-700 font-medium text-sm mr-2">Voir tout</span>
                <i class="fas fa-arrow-right text-primary-500"></i>
            </button>
        </div>

        <?php if (empty($transactions)): ?>
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exchange-alt text-gray-400 text-2xl"></i>
                </div>
                <p class="text-gray-500">Aucune transaction récente</p>
                <p class="text-sm text-gray-400 mt-1">Vos transactions apparaîtront ici</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($transactions as $transaction): ?>
                    <div
                        class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-xl transition-colors duration-200 group">
                        <div class="flex items-center">
                            <div
                                class="w-12 h-12 rounded-xl flex items-center justify-center mr-3 
                        <?php echo $transaction['type'] == 'investissement' ? 'bg-gradient-to-br from-secondary-50 to-secondary-50' :
                            ($transaction['type'] == 'retrait' ? 'bg-gradient-to-br from-red-50 to-rose-50' :
                                ($transaction['type'] == 'publicite' ? 'bg-gradient-to-br from-blue-50 to-cyan-50' : 'bg-gradient-to-br from-purple-50 to-pink-50')); ?>">
                                <i
                                    class="fas 
                            <?php echo $transaction['type'] == 'investissement' ? 'fa-chart-line text-secondary-600' :
                                ($transaction['type'] == 'retrait' ? 'fa-download text-red-600' :
                                    ($transaction['type'] == 'publicite' ? 'fa-video text-blue-600' : 'fa-users text-purple-600')); ?> text-lg">
                                </i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800 group-hover:text-gray-900">
                                    <?php echo htmlspecialchars($transaction['description']); ?></p>
                                <div class="flex items-center mt-1">
                                    <span class="text-xs text-gray-500">
                                        <i class="far fa-clock mr-1"></i><?php echo $transaction['date']; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold <?php echo $transaction['montant'] > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo $transaction['montant'] > 0 ? '+' : ''; ?>
                                <?php echo number_format($transaction['montant'], 0, ',', ' '); ?> FCFA
                            </p>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                        <?php echo $transaction['statut'] == 'success' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?> mt-1">
                                <?php if ($transaction['statut'] == 'success'): ?>
                                    <i class="fas fa-check-circle mr-1 text-xs"></i>
                                <?php else: ?>
                                    <i class="fas fa-clock mr-1 text-xs"></i>
                                <?php endif; ?>
                                <?php echo $transaction['statut'] == 'success' ? 'Terminé' : 'En attente'; ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Plans actifs réels -->
    <?php if (!empty($activePlans)): ?>
        <div class="bg-white rounded-2xl shadow-sm p-6 mb-8 border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-secondary-100 to-secondary-50 rounded-xl flex items-center justify-center mr-3">
                        <i class="fas fa-chart-line text-secondary-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Plans actifs</h3>
                </div>
                <a href="investissement" class="btn-text">
                    <span class="text-secondary-600 hover:text-secondary-700 font-medium text-sm mr-2">Investir plus</span>
                    <i class="fas fa-arrow-right text-secondary-500"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <?php foreach ($activePlans as $plan): ?>
                    <?php
                    $pourcentage = ($plan['jours_passes'] / $plan['duree_totale']) * 100;
                    $gain_total = $plan['roi_journalier'] * $plan['jours_passes'];
                    $gain_restant = $plan['roi_journalier'] * ($plan['duree_totale'] - $plan['jours_passes']);
                    $jours_restants = $plan['duree_totale'] - $plan['jours_passes'];

                    // Couleur selon progression
                    if ($pourcentage < 33) {
                        $color = 'from-blue-500 to-cyan-500';
                        $bgColor = 'bg-gradient-to-r from-blue-50 to-cyan-50';
                        $borderColor = 'border-blue-200';
                    } elseif ($pourcentage < 66) {
                        $color = 'from-green-500 to-emerald-500';
                        $bgColor = 'bg-gradient-to-r from-green-50 to-emerald-50';
                        $borderColor = 'border-green-200';
                    } else {
                        $color = 'from-yellow-500 to-amber-500';
                        $bgColor = 'bg-gradient-to-r from-yellow-50 to-amber-50';
                        $borderColor = 'border-yellow-200';
                    }
                    ?>
                    <div class="<?php echo $bgColor; ?> border <?php echo $borderColor; ?> rounded-xl p-5">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h4 class="font-bold text-gray-800 text-lg"><?php echo htmlspecialchars($plan['nom']); ?></h4>
                                <p class="text-sm text-gray-600 mt-1">Investi:
                                    <?php echo number_format($plan['montant_investi'], 0, ',', ' '); ?> FCFA</p>
                            </div>
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white/80 backdrop-blur-sm text-gray-700">
                                <i class="fas fa-calendar-alt mr-1"></i>
                                <?php echo $jours_restants; ?>j restant<?php echo $jours_restants > 1 ? 's' : ''; ?>
                            </span>
                        </div>

                        <!-- Progression -->
                        <div class="mb-5">
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-gray-600">Progression</span>
                                <span class="font-medium text-gray-700">
                                    <?php echo $plan['jours_passes']; ?>/<?php echo $plan['duree_totale']; ?> jours
                                    (<?php echo round($pourcentage, 1); ?>%)
                                </span>
                            </div>
                            <div class="w-full bg-white/50 rounded-full h-3 overflow-hidden">
                                <div class="h-3 rounded-full bg-gradient-to-r <?php echo $color; ?> transition-all duration-500"
                                    style="width: <?php echo $pourcentage; ?>%"></div>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-3 gap-4">
                            <div class="text-center">
                                <div class="text-sm text-gray-500 mb-1">ROI/jour</div>
                                <div class="font-bold text-gray-800">
                                    <?php echo number_format($plan['roi_journalier'], 0, ',', ' '); ?> FCFA
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="text-sm text-gray-500 mb-1">Gain déjà perçu</div>
                                <div class="font-bold text-green-600">
                                    <?php echo number_format($gain_total, 0, ',', ' '); ?> FCFA
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="text-sm text-gray-500 mb-1">Gain restant</div>
                                <div class="font-bold text-blue-600">
                                    <?php echo number_format($gain_restant, 0, ',', ' '); ?> FCFA
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal de retrait (resté identique mais avec données réelles) -->
<div id="retraitModal" class="modal-overlay">
    <!-- ... (le modal reste identique) ... -->
</div>

<!-- Modal de dépôt -->
<div id="depotModal"
    class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md transform transition-all duration-300 modal-content">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Effectuer un dépôt</h3>
                    <p class="text-sm text-gray-500">Rechargez votre compte pour investir</p>
                </div>
                <button onclick="closeDepotModal()" class="modal-close-btn">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="space-y-6">
                <!-- Méthodes de paiement -->
                <div>
                    <label class="block text-gray-700 font-medium mb-3">Méthode de paiement</label>
                    <div class="grid grid-cols-3 gap-3">
                        <button onclick="selectPaymentMethod('orange')" class="payment-method">
                            <i class="fas fa-mobile-alt text-orange-500 text-2xl mb-2"></i>
                            <p class="text-sm font-medium">Orange Money</p>
                        </button>
                        <button onclick="selectPaymentMethod('mtn')" class="payment-method">
                            <i class="fas fa-sim-card text-yellow-500 text-2xl mb-2"></i>
                            <p class="text-sm font-medium">MTN Mobile</p>
                        </button>
                        <button onclick="selectPaymentMethod('visa')" class="payment-method">
                            <i class="fab fa-cc-visa text-blue-500 text-2xl mb-2"></i>
                            <p class="text-sm font-medium">Carte Visa</p>
                        </button>
                    </div>
                </div>

                <!-- Montant -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Montant (FCFA)</label>
                    <div class="grid grid-cols-3 gap-3 mb-3">
                        <button onclick="setDepotAmount(5000)" class="amount-btn">5,000</button>
                        <button onclick="setDepotAmount(10000)" class="amount-btn">10,000</button>
                        <button onclick="setDepotAmount(20000)" class="amount-btn">20,000</button>
                        <button onclick="setDepotAmount(50000)" class="amount-btn">50,000</button>
                        <button onclick="setDepotAmount(100000)" class="amount-btn">100,000</button>
                        <button onclick="setDepotAmount(200000)" class="amount-btn">200,000</button>
                    </div>
                    <input type="number" id="depotAmount" min="1000" max="1000000" placeholder="Montant personnalisé"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-green-500 text-lg font-medium">
                </div>

                <!-- Bouton de confirmation -->
                <button onclick="processDeposit()"
                    class="w-full bg-gradient-to-r from-green-500 to-emerald-500 text-white font-bold py-4 px-4 rounded-xl hover:from-green-600 hover:to-emerald-600 transition-all duration-300 shadow-lg hover:shadow-xl">
                    <i class="fas fa-credit-card mr-2"></i>
                    Confirmer le dépôt
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Variables globales
    let selectedPaymentMethod = null;
    let selectedDepotAmount = 0;

    function openRetraitModal() {
        // Mettre à jour les soldes réels dans le modal
        document.querySelectorAll('input[name="source"]').forEach(input => {
            const source = input.value;
            let solde = 0;

            switch (source) {
                case 'investissement':
                    solde = <?php echo $user_stats['solde_investissement']; ?>;
                    break;
                case 'publicite':
                    solde = <?php echo $user_stats['solde_publicite']; ?>;
                    break;
                case 'parrainage':
                    solde = <?php echo $user_stats['solde_parrainage']; ?>;
                    break;
            }

            // Mettre à jour l'affichage
            const parent = input.closest('label');
            const span = parent.querySelector('.text-green-600, .text-blue-600, .text-purple-600');
            if (span) {
                span.textContent = solde.toLocaleString() + ' FCFA';
            }
        });

        updateRecap();
        document.getElementById('retraitModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function openDepotModal() {
        selectedPaymentMethod = null;
        selectedDepotAmount = 0;
        document.getElementById('depotAmount').value = '';

        // Réinitialiser les boutons
        document.querySelectorAll('.payment-method').forEach(btn => {
            btn.classList.remove('selected');
        });
        document.querySelectorAll('.amount-btn').forEach(btn => {
            btn.classList.remove('selected');
        });

        document.getElementById('depotModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeDepotModal() {
        document.getElementById('depotModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function selectPaymentMethod(method) {
        selectedPaymentMethod = method;

        // Mettre à jour l'UI
        document.querySelectorAll('.payment-method').forEach(btn => {
            btn.classList.remove('selected');
            btn.style.border = '';
        });

        const btn = event.target.closest('.payment-method');
        btn.classList.add('selected');
        btn.style.border = '2px solid #10b981';
    }

    function setDepotAmount(amount) {
        selectedDepotAmount = amount;
        document.getElementById('depotAmount').value = amount;

        // Mettre à jour l'UI
        document.querySelectorAll('.amount-btn').forEach(btn => {
            btn.classList.remove('selected');
        });

        event.target.classList.add('selected');
    }

    function processDeposit() {
        const amount = document.getElementById('depotAmount').value;

        if (!selectedPaymentMethod) {
            alert('Veuillez sélectionner une méthode de paiement');
            return;
        }

        if (!amount || amount < 1000) {
            alert('Le montant minimum de dépôt est de 1,000 FCFA');
            return;
        }

        // Simuler le traitement
        alert('Dépôt de ' + parseInt(amount).toLocaleString() + ' FCFA en cours de traitement...');
        closeDepotModal();

        // Rediriger vers la page de transaction ou actualiser
        setTimeout(() => {
            window.location.href = 'transactions';
        }, 1000);
    }

    function showAllTransactions() {
        window.location.href = 'transactions';
    }

    function openSupport() {
        // Ici tu peux intégrer ton vrai système de support
        window.open('https://wa.me/237656720564?text=Bonjour%20FreeCash,%20j\'ai%20besoin%20d\'aide', '_blank');
    }

    // Les autres fonctions restent identiques...
</script>

<style>
    /* Styles pour les nouvelles actions rapides */
    .action-card-new {
        @apply bg-white rounded-xl p-3 flex flex-col items-center justify-center hover:shadow-lg transition-all duration-300 cursor-pointer border border-gray-100 hover:border-transparent hover:scale-105;
    }

    .action-icon-new {
        @apply w-14 h-14 rounded-xl flex items-center justify-center mb-3 transition-all duration-300;
    }

    .icon-circle {
        @apply w-10 h-10 rounded-full flex items-center justify-center text-white shadow-lg;
    }

    .action-title-new {
        @apply font-medium text-gray-800 text-xs text-center mb-1;
    }

    .action-badge {
        @apply text-xs font-medium px-2 py-0.5 rounded-full;
    }

    .btn-text {
        @apply flex items-center hover:scale-105 transition-transform duration-200;
    }

    .payment-method {
        @apply border-2 border-gray-200 rounded-lg p-3 text-center hover:border-green-500 cursor-pointer transition-colors duration-200 flex flex-col items-center justify-center;
    }

    .payment-method.selected {
        @apply border-green-500 bg-green-50;
    }

    .amount-btn {
        @apply border-2 border-gray-200 rounded-lg p-3 text-center hover:border-green-500 cursor-pointer transition-colors duration-200 font-medium;
    }

    .amount-btn.selected {
        @apply border-green-500 bg-green-50 text-green-700;
    }

    .modal-close-btn {
        @apply text-gray-400 hover:text-gray-600 transition-colors duration-200;
    }

    .modal-content {
        animation: slideUp 0.3s ease-out;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Animation pour les cartes */
    @keyframes float {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-5px);
        }
    }

    .action-card-new:hover {
        animation: float 0.3s ease-in-out;
    }

    /* Responsive */
    @media (max-width: 640px) {
        .grid-cols-8 {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media (max-width: 480px) {
        .grid-cols-8 {
            grid-template-columns: repeat(3, 1fr);
        }
    }
</style>