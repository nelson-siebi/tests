<?php
// pages/videos.php

require_once 'config/db.php';

// Vérifier connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$db = Database::getInstance()->getConnection();

// 1. CALCULER QUOTA RÉEL
function getRealVideoQuota($userId) {
    global $db;
    
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
    $sql = "SELECT COUNT(*) as vues, SUM(gain) as gains 
            FROM ads_views 
            WHERE user_id = :user_id 
            AND DATE(date_view) = CURDATE()";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    $result = $stmt->fetch();
    $vues = $result['vues'] ?? 0;
    $gains = $result['gains'] ?? 0;
    
    return [
        'total' => $total,
        'restant' => max(0, $total - $vues),
        'vues' => $vues,
        'gagne_aujourdhui' => $gains,
        'progress' => ($total > 0) ? ($vues / $total * 100) : 0
    ];
}

// 2. RÉCUPÉRER VIDÉOS RÉELLES (MODIFIÉ POUR YOUTUBE)
function getRealVideos($userId) {
    global $db;
    
    // D'abord, récupérer les vidéos actives avec extraction d'ID YouTube
    $sql = "SELECT *, 
            CASE 
                WHEN youtube_url LIKE '%youtu.be%' 
                    THEN SUBSTRING_INDEX(SUBSTRING_INDEX(youtube_url, '/', -1), '?', 1)
                WHEN youtube_url LIKE '%youtube.com/watch%' 
                    THEN SUBSTRING_INDEX(SUBSTRING_INDEX(youtube_url, 'v=', -1), '&', 1)
                WHEN youtube_url LIKE '%youtube.com/embed%' 
                    THEN SUBSTRING_INDEX(SUBSTRING_INDEX(youtube_url, '/embed/', -1), '?', 1)
                ELSE youtube_video_id 
            END as extracted_youtube_id
            FROM ads_videos 
            WHERE actif = 1 
            ORDER BY id DESC";
    
    $stmt = $db->query($sql);
    $videosBD = $stmt->fetchAll();
    
    // Récupérer les vidéos déjà vues aujourd'hui par cet utilisateur
    $sqlVues = "SELECT video_id FROM ads_views 
                WHERE user_id = :user_id 
                AND DATE(date_view) = CURDATE()";
    $stmtVues = $db->prepare($sqlVues);
    $stmtVues->execute([':user_id' => $userId]);
    $videosVues = $stmtVues->fetchAll(PDO::FETCH_COLUMN);
    
    // Récupérer le gain par vidéo du plan actif
    $sqlGain = "SELECT p.gain_par_video 
                FROM user_plans up 
                JOIN plans p ON up.plan_id = p.id 
                WHERE up.user_id = :user_id 
                AND up.statut = 'active'
                LIMIT 1";
    $stmtGain = $db->prepare($sqlGain);
    $stmtGain->execute([':user_id' => $userId]);
    $gainResult = $stmtGain->fetch();
    $gainParVideo = $gainResult['gain_par_video'] ?? 10; // 10 FCFA par défaut
    
    // Transformer les données
    $videos = [];
    foreach ($videosBD as $index => $video) {
        // Vérifier si déjà vue aujourd'hui
        $dejaVue = in_array($video['id'], $videosVues);
        
        // Utiliser l'ID YouTube extrait
        $youtubeId = $video['extracted_youtube_id'] ?? 
                    $video['youtube_video_id'] ?? 
                    'dQw4w9WgXcQ'; // ID par défaut
        
        // Si pas d'ID YouTube valide, chercher dans l'URL avec regex
        if (empty($youtubeId) || strlen($youtubeId) !== 11) {
            $url = $video['youtube_url'] ?? '';
            if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
                $youtubeId = $matches[1];
            }
        }
        
        // Dernière date de vue si applicable
        $vueIlYa = null;
        if ($dejaVue) {
            $sqlDate = "SELECT date_view FROM ads_views 
                       WHERE user_id = :user_id AND video_id = :video_id 
                       ORDER BY date_view DESC LIMIT 1";
            $stmtDate = $db->prepare($sqlDate);
            $stmtDate->execute([
                ':user_id' => $userId,
                ':video_id' => $video['id']
            ]);
            $dateResult = $stmtDate->fetch();
            
            if ($dateResult) {
                $dateVue = new DateTime($dateResult['date_view']);
                $now = new DateTime();
                $interval = $dateVue->diff($now);
                
                if ($interval->d > 0) {
                    $vueIlYa = $interval->d . ' jour' . ($interval->d > 1 ? 's' : '');
                } elseif ($interval->h > 0) {
                    $vueIlYa = $interval->h . ' heure' . ($interval->h > 1 ? 's' : '');
                } elseif ($interval->i > 0) {
                    $vueIlYa = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '');
                } else {
                    $vueIlYa = 'À l\'instant';
                }
            }
        }
        
        // Déterminer catégorie
        $categorie = 'Divers';
        $titre = strtolower($video['titre']);
        if (strpos($titre, 'blockchain') !== false || strpos($titre, 'crypto') !== false) {
            $categorie = 'Technologie';
        } elseif (strpos($titre, 'investissement') !== false || strpos($titre, 'trading') !== false) {
            $categorie = 'Finance';
        } elseif (strpos($titre, 'tutoriel') !== false || strpos($titre, 'apprendre') !== false) {
            $categorie = 'Éducation';
        }
        
        // Durée estimée
        $durees = ['2:15', '3:45', '4:30', '5:20', '3:10', '4:50'];
        $duree = $durees[$index % count($durees)];
        
        $videos[] = [
            'id' => $video['id'],
            'titre' => $video['titre'],
            'description' => $video['titre'] . ' - Regardez cette vidéo pour gagner de l\'argent.',
            'duree' => $duree,
            'gain' => $gainParVideo,
            'vue' => $dejaVue,
            'categorie' => $categorie,
            'vue_il_y_a' => $vueIlYa,
            'youtube_id' => $youtubeId,
            'created_at' => $video['created_at']
        ];
    }
    
    return $videos;
}

// 3. TRAITEMENT AJAX DÉPLACÉ VERS api/video_watch.php

// 4. RÉCUPÉRER LES DONNÉES POUR L'AFFICHAGE
$quota_journalier = getRealVideoQuota($userId);
$videos = getRealVideos($userId);

// 5. CALCULER LES GAINS TOTAUX DU MOIS
$sqlTotal = "SELECT SUM(gain) as total_mois 
            FROM ads_views 
            WHERE user_id = :user_id 
            AND MONTH(date_view) = MONTH(CURDATE()) 
            AND YEAR(date_view) = YEAR(CURDATE())";
$stmtTotal = $db->prepare($sqlTotal);
$stmtTotal->execute([':user_id' => $userId]);
$totalResult = $stmtTotal->fetch();
$totalMois = $totalResult['total_mois'] ?? 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vidéos Publicitaires - Investian</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        #youtubePlayerContainer {
            position: relative;
        }
        #playerOverlay {
            pointer-events: none;
        }
        #progressBar {
            transition: width 0.5s ease-in-out;
        }
        button:disabled {
            opacity: 0.7;
            cursor: not-allowed !important;
        }
        #youtubePlayer iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        .video-thumbnail {
            background-image: linear-gradient(to right, #1f2937, #111827);
        }
    </style>
</head>
<body class="bg-gray-50">
<div class="max-w-7xl mx-auto px-4 py-8">
    
    <!-- En-tête -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Visionnage de vidéos</h1>
        <p class="text-gray-600">Gagnez de l'argent en regardant des vidéos publicitaires</p>
    </div>
    
    <!-- Cartes statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Quota -->
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Quota journalier</p>
                    <p class="text-2xl font-bold mt-1">
                        <span class="text-blue-600"><?php echo $quota_journalier['restant']; ?></span>
                        <span class="text-gray-400">/</span>
                        <span class="text-gray-700"><?php echo $quota_journalier['total']; ?></span>
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-film text-blue-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full" style="width: <?php echo $quota_journalier['progress']; ?>%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-2">
                    <?php echo $quota_journalier['vues']; ?> vidéos regardées aujourd'hui
                </p>
            </div>
        </div>
        
        <!-- Gains du jour -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl shadow p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm">Gains aujourd'hui</p>
                    <p class="text-2xl font-bold mt-1">
                        <?php echo number_format($quota_journalier['gagne_aujourdhui'], 0, ',', ' '); ?> FCFA
                    </p>
                </div>
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <i class="fas fa-coins text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-green-100 mt-4">
                Votre solde publicité sera crédité automatiquement
            </p>
        </div>
        
        <!-- Gains du mois -->
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Gains ce mois</p>
                    <p class="text-2xl font-bold mt-1 text-gray-800">
                        <?php echo number_format($totalMois, 0, ',', ' '); ?> FCFA
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-4">
                <i class="fas fa-calendar-alt mr-1"></i>
                <?php echo date('F Y'); ?>
            </p>
        </div>
    </div>
    
    <!-- Instructions -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-8">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-blue-600 text-xl mt-1 mr-3"></i>
            <div>
                <h3 class="font-bold text-gray-800 mb-2">Comment gagner de l'argent ?</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                    <div class="flex items-start">
                        <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-3 mt-1">
                            <span class="text-blue-600 font-bold">1</span>
                        </div>
                        <p>Cliquez sur "Regarder" pour une vidéo disponible</p>
                    </div>
                    <div class="flex items-start">
                        <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-3 mt-1">
                            <span class="text-blue-600 font-bold">2</span>
                        </div>
                        <p>La vidéo YouTube s'ouvre dans une fenêtre modale</p>
                    </div>
                    <div class="flex items-start">
                        <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-3 mt-1">
                            <span class="text-blue-600 font-bold">3</span>
                        </div>
                        <p>Regardez la vidéo jusqu'à la fin</p>
                    </div>
                    <div class="flex items-start">
                        <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-3 mt-1">
                            <span class="text-blue-600 font-bold">4</span>
                        </div>
                        <p>Cliquez sur "Valider" pour recevoir votre gain</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Liste des vidéos -->
    <h2 class="text-xl font-bold text-gray-800 mb-4">Vidéos disponibles (<?php echo count($videos); ?>)</h2>
    
    <?php if (empty($videos)): ?>
    <div class="bg-white rounded-xl shadow p-8 text-center">
        <i class="fas fa-video-slash text-gray-400 text-5xl mb-4"></i>
        <h3 class="text-xl font-bold text-gray-700 mb-2">Aucune vidéo disponible</h3>
        <p class="text-gray-500">Revenez plus tard pour de nouvelles vidéos publicitaires</p>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <?php foreach ($videos as $video): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
            <!-- Badge -->
            <div class="absolute top-4 right-4 z-10">
                <?php if ($video['vue']): ?>
                <span class="bg-gray-100 text-gray-700 text-xs font-medium px-3 py-1 rounded-full">
                    <i class="fas fa-check-circle mr-1"></i> Vue
                </span>
                <?php else: ?>
                <span class="bg-green-100 text-green-700 text-xs font-medium px-3 py-1 rounded-full">
                    <i class="fas fa-play-circle mr-1"></i> Nouvelle
                </span>
                <?php endif; ?>
            </div>
            
            <!-- Miniature YouTube réelle -->
            <div class="h-40 relative bg-black">
                <?php if (!empty($video['youtube_id']) && strlen($video['youtube_id']) === 11): ?>
                <img 
                    src="https://img.youtube.com/vi/<?php echo $video['youtube_id']; ?>/hqdefault.jpg" 
                    alt="<?php echo htmlspecialchars($video['titre']); ?>"
                    class="w-full h-full object-cover"
                    onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"100%\" height=\"100%\" viewBox=\"0 0 400 225\"><rect width=\"100%\" height=\"100%\" fill=\"%23333\"/><text x=\"50%\" y=\"50%\" font-family=\"Arial\" font-size=\"24\" fill=\"white\" text-anchor=\"middle\" dy=\".3em\">Miniature YouTube</text></svg>'"
                >
                <!-- Play button overlay -->
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-12 h-12 bg-red-600 rounded-full flex items-center justify-center opacity-90 hover:opacity-100 transition-opacity cursor-pointer">
                        <i class="fas fa-play text-white text-lg ml-1"></i>
                    </div>
                </div>
                <?php else: ?>
                <!-- Fallback si pas d'ID YouTube -->
                <div class="h-full video-thumbnail flex items-center justify-center">
                    <div class="text-center">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-play text-white"></i>
                        </div>
                        <span class="text-white text-sm"><?php echo $video['duree']; ?></span>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="absolute bottom-3 left-3">
                    <span class="bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded">
                        <?php echo $video['categorie']; ?>
                    </span>
                </div>
                <div class="absolute bottom-3 right-3">
                    <span class="bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded">
                        <?php echo $video['duree']; ?>
                    </span>
                </div>
            </div>
            
            <!-- Contenu -->
            <div class="p-5">
                <h3 class="font-bold text-gray-800 mb-2 truncate"><?php echo htmlspecialchars($video['titre']); ?></h3>
                <p class="text-gray-600 text-sm mb-4 line-clamp-2"><?php echo htmlspecialchars($video['description']); ?></p>
                
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-coins text-green-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Gain</p>
                            <p class="font-bold text-green-600">+<?php echo $video['gain']; ?> FCFA</p>
                        </div>
                    </div>
                    
                    <?php if ($video['vue']): ?>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Visionnée</p>
                        <p class="text-sm text-gray-700"><?php echo $video['vue_il_y_a'] ?? 'Aujourd\'hui'; ?></p>
                    </div>
                    <?php else: ?>
                    <button 
                        onclick="watchVideo(<?php echo $video['id']; ?>, <?php echo $video['gain']; ?>, '<?php echo addslashes($video['titre']); ?>', '<?php echo $video['youtube_id'] ?? ''; ?>')"
                        class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                        <i class="fas fa-play mr-2"></i>
                        Regarder
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <!-- Historique -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Historique récent</h2>
        <?php
        $sqlHistory = "SELECT av.*, adv.titre as video_titre 
                      FROM ads_views av 
                      JOIN ads_videos adv ON av.video_id = adv.id 
                      WHERE av.user_id = :user_id 
                      ORDER BY av.date_view DESC 
                      LIMIT 5";
        $stmtHistory = $db->prepare($sqlHistory);
        $stmtHistory->execute([':user_id' => $userId]);
        $history = $stmtHistory->fetchAll();
        ?>
        
        <?php if (empty($history)): ?>
        <p class="text-gray-500 text-center py-4">Aucun visionnage enregistré</p>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-gray-500 text-sm border-b">
                        <th class="pb-3 font-medium">Vidéo</th>
                        <th class="pb-3 font-medium">Date</th>
                        <th class="pb-3 font-medium">Gain</th>
                        <th class="pb-3 font-medium">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $item): ?>
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="py-3">
                            <p class="font-medium text-gray-800"><?php echo htmlspecialchars($item['video_titre']); ?></p>
                        </td>
                        <td class="py-3 text-gray-700">
                            <?php echo date('d/m/Y H:i', strtotime($item['date_view'])); ?>
                        </td>
                        <td class="py-3">
                            <span class="font-bold text-green-600">+<?php echo $item['gain']; ?> FCFA</span>
                        </td>
                        <td class="py-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i> Payé
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de visionnage AVEC IFRAME YOUTUBE -->
<div id="watchModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl w-full max-w-4xl">
        <!-- En-tête -->
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <h3 id="modalVideoTitle" class="text-xl font-bold text-gray-800 mb-1"></h3>
                    <p id="modalVideoGain" class="text-green-600 font-bold"></p>
                </div>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <!-- Contenu -->
        <div class="p-6">
            <!-- Iframe YouTube -->
            <div id="youtubePlayerContainer" class="mb-6 rounded-lg overflow-hidden bg-black">
                <div id="youtubePlayer" class="w-full aspect-video">
                    <!-- L'iframe sera injectée ici -->
                </div>
            </div>
            
            <!-- Barre de progression -->
            <div class="mb-6">
                <div class="flex justify-between mb-2">
                    <span class="text-sm text-gray-600">Progression du visionnage</span>
                    <span id="timerDisplay" class="text-sm font-medium">0%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div id="progressBar" class="bg-green-500 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>
            
            <!-- Instructions -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-gray-700 flex items-start">
                    <i class="fas fa-info-circle text-blue-500 mr-2 mt-0.5"></i>
                    <span>
                        <strong>Instructions :</strong> Regardez la vidéo jusqu'à la fin. La barre de progression suivra votre visionnage. 
                        Une fois à 100%, cliquez sur "Valider et recevoir votre gain".
                    </span>
                </p>
            </div>
            
            <!-- Boutons -->
            <div class="flex space-x-3">
                <button onclick="closeModal()" 
                        class="flex-1 border border-gray-300 text-gray-700 font-medium py-3 rounded-lg hover:bg-gray-50 transition">
                    Annuler
                </button>
                <button id="validateBtn" 
                        onclick="validateWatching()"
                        disabled
                        class="flex-1 bg-gray-300 text-gray-500 font-bold py-3 rounded-lg cursor-not-allowed">
                    <span id="btnText">Visionnage en cours... 0%</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal succès -->
<div id="successModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl w-full max-w-md transform transition-all">
        <div class="p-6 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check text-green-600 text-2xl"></i>
            </div>
            
            <h3 class="text-xl font-bold text-gray-800 mb-2">Félicitations !</h3>
            <p id="successMessage" class="text-gray-600 mb-4"></p>
            
            <button onclick="closeSuccessModal()" 
                    class="w-full bg-green-600 text-white font-bold py-3 rounded-lg hover:bg-green-700">
                Continuer
            </button>
        </div>
    </div>
</div>

<script>
// Variables globales
let currentVideoId = null;
let currentYoutubeId = null;
let currentVideoGain = 0;
let currentVideoTitle = '';
let watchInterval = null;
let progress = 0;
let quotaRestant = <?php echo $quota_journalier['restant']; ?>;
let youtubePlayer = null;
let playerReady = false;

// Charger l'API YouTube
function loadYouTubeAPI() {
    if (!document.getElementById('youtubeAPI')) {
        const tag = document.createElement('script');
        tag.id = 'youtubeAPI';
        tag.src = 'https://www.youtube.com/iframe_api';
        const firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
    }
}

// Appelé par l'API YouTube quand elle est prête
function onYouTubeIframeAPIReady() {
    console.log('API YouTube chargée');
}

function watchVideo(videoId, gain, title, youtubeId = null) {
    if (quotaRestant <= 0) {
        alert('❌ Vous avez atteint votre quota journalier ! Revenez demain.');
        return;
    }
    
    currentVideoId = videoId;
    currentVideoGain = gain;
    currentVideoTitle = title;
    currentYoutubeId = youtubeId;
    
    // Mettre à jour le modal
    document.getElementById('modalVideoTitle').textContent = title;
    document.getElementById('modalVideoGain').textContent = '+ ' + gain + ' FCFA';
    
    // Réinitialiser la progression
    progress = 0;
    document.getElementById('progressBar').style.width = '0%';
    document.getElementById('timerDisplay').textContent = '0%';
    document.getElementById('btnText').textContent = 'Visionnage en cours... 0%';
    
    // Désactiver le bouton
    const btn = document.getElementById('validateBtn');
    btn.disabled = true;
    btn.className = 'flex-1 bg-gray-300 text-gray-500 font-bold py-3 rounded-lg cursor-not-allowed';
    
    // Charger l'API YouTube
    loadYouTubeAPI();
    
    // Afficher le modal
    document.getElementById('watchModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Attendre un peu que le modal soit visible, puis charger le lecteur
    setTimeout(() => {
        loadYouTubePlayer(youtubeId);
    }, 300);
}

function loadYouTubePlayer(youtubeId) {
    const container = document.getElementById('youtubePlayer');
    
    if (!youtubeId || youtubeId.length !== 11) {
        container.innerHTML = `
            <div class="w-full h-full bg-gray-800 flex items-center justify-center">
                <div class="text-center text-white p-6">
                    <i class="fas fa-exclamation-triangle text-4xl mb-3 text-yellow-400"></i>
                    <p class="font-medium">ID YouTube invalide</p>
                    <p class="text-sm opacity-75 mt-1">La vidéo ne peut pas être chargée</p>
                    <button onclick="startSimulationMode()" class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        Simuler le visionnage
                    </button>
                </div>
            </div>
        `;
        return;
    }
    
    container.innerHTML = `
        <div id="ytPlayer" class="w-full h-full"></div>
        <div id="playerOverlay" class="absolute inset-0 bg-black bg-opacity-0 transition-opacity duration-300"></div>
    `;
    
    // Attendre que l'API soit disponible
    const checkYT = setInterval(() => {
        if (typeof YT !== 'undefined' && YT.Player) {
            clearInterval(checkYT);
            
            // Créer le player YouTube
            youtubePlayer = new YT.Player('ytPlayer', {
                height: '100%',
                width: '100%',
                videoId: youtubeId,
                playerVars: {
                    'autoplay': 1,
                    'controls': 1,
                    'rel': 0,
                    'modestbranding': 1,
                    'showinfo': 0
                },
                events: {
                    'onReady': onPlayerReady,
                    'onStateChange': onPlayerStateChange,
                    'onError': onPlayerError
                }
            });
        }
    }, 100);
}

function onPlayerReady(event) {
    playerReady = true;
    console.log('Player YouTube prêt');
    startVideoMonitoring();
}

function onPlayerStateChange(event) {
    // États YouTube: -1 (non démarré), 0 (terminé), 1 (lecture), 2 (pause), 3 (buffering), 5 (cue)
    
    if (event.data === YT.PlayerState.ENDED) {
        // Vidéo terminée
        progress = 100;
        document.getElementById('progressBar').style.width = '100%';
        document.getElementById('timerDisplay').textContent = '100%';
        
        // Activer le bouton de validation
        const btn = document.getElementById('validateBtn');
        btn.disabled = false;
        btn.className = 'flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg transition';
        document.getElementById('btnText').textContent = `Valider + ${currentVideoGain} FCFA`;
    }
}

function onPlayerError(event) {
    console.error('Erreur YouTube:', event.data);
    
    // Mode simulation en cas d'erreur
    document.getElementById('youtubePlayer').innerHTML = `
        <div class="w-full h-full bg-gray-800 flex items-center justify-center">
            <div class="text-center text-white p-6">
                <i class="fas fa-exclamation-triangle text-4xl mb-3 text-yellow-400"></i>
                <p class="font-medium">Vidéo non disponible</p>
                <p class="text-sm opacity-75 mt-1">Mode simulation activé</p>
                <button onclick="startSimulationMode()" class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    Simuler le visionnage
                </button>
            </div>
        </div>
    `;
}

function startSimulationMode() {
    // Mode simulation pour les vidéos sans ID YouTube valide
    clearInterval(watchInterval);
    progress = 0;
    
    watchInterval = setInterval(() => {
        progress += 1; // 1% toutes les secondes
        if (progress >= 100) {
            progress = 100;
            clearInterval(watchInterval);
            
            // Activer le bouton
            const btn = document.getElementById('validateBtn');
            btn.disabled = false;
            btn.className = 'flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg transition';
            document.getElementById('btnText').textContent = `Valider + ${currentVideoGain} FCFA`;
        }
        
        document.getElementById('progressBar').style.width = progress + '%';
        document.getElementById('timerDisplay').textContent = progress + '%';
        document.getElementById('btnText').textContent = `Visionnage... ${progress}%`;
    }, 1000);
}

function startVideoMonitoring() {
    // Surveiller la progression de la vidéo
    clearInterval(watchInterval);
    
    watchInterval = setInterval(() => {
        if (youtubePlayer && playerReady) {
            try {
                const currentTime = youtubePlayer.getCurrentTime();
                const duration = youtubePlayer.getDuration();
                
                if (duration > 0) {
                    progress = Math.min(100, Math.round((currentTime / duration) * 100));
                    
                    document.getElementById('progressBar').style.width = progress + '%';
                    document.getElementById('timerDisplay').textContent = progress + '%';
                    document.getElementById('btnText').textContent = `Visionnage... ${progress}%`;
                    
                    // Si la vidéo est à plus de 90%, considérer comme terminée
                    if (progress >= 90) {
                        const btn = document.getElementById('validateBtn');
                        if (btn.disabled) {
                            btn.disabled = false;
                            btn.className = 'flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg transition';
                            document.getElementById('btnText').textContent = `Valider + ${currentVideoGain} FCFA`;
                        }
                    }
                }
            } catch (e) {
                console.log('Erreur lecture progression:', e);
            }
        }
    }, 1000);
}

function validateWatching() {
    // Envoyer la requête AJAX
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'api/video_watch.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                
                if (response.success) {
                    // Décrémenter le quota local
                    quotaRestant--;
                    
                    // Mettre à jour l'affichage de la vidéo
                    const videoElement = document.querySelector(`[onclick*="watchVideo(${currentVideoId}"]`);
                    if (videoElement) {
                        const parentCard = videoElement.closest('.bg-white');
                        if (parentCard) {
                            // Remplacer le bouton
                            const buttonContainer = videoElement.parentElement;
                            buttonContainer.innerHTML = `
                                <div class="text-right">
                                    <p class="text-sm text-gray-500">Visionnée</p>
                                    <p class="text-sm text-gray-700">À l'instant</p>
                                </div>
                            `;
                            
                            // Mettre à jour le badge
                            const badge = parentCard.querySelector('.absolute.top-4');
                            if (badge) {
                                badge.innerHTML = `
                                    <span class="bg-gray-100 text-gray-700 text-xs font-medium px-3 py-1 rounded-full">
                                        <i class="fas fa-check-circle mr-1"></i> Vue
                                    </span>
                                `;
                            }
                        }
                    }
                    
                    // Afficher le succès
                    document.getElementById('successMessage').textContent = response.message;
                    closeModal();
                    document.getElementById('successModal').classList.remove('hidden');
                    
                } else {
                    alert('Erreur: ' + response.message);
                }
            } catch (e) {
                console.error('Erreur parsing JSON:', e);
                console.log('Réponse reçue:', xhr.responseText);
                alert('Erreur technique (JSON): ' + xhr.responseText.substring(0, 100) + '...');
            }
        } else {
            console.error('Erreur HTTP:', xhr.status);
            alert('Erreur communication serveur: ' + xhr.status);
        }
    };
    
    xhr.onerror = function() {
        alert('Erreur réseau. Vérifiez votre connexion internet.');
    };
    
    xhr.send('action=watch_video&video_id=' + currentVideoId);
}

function closeModal() {
    // Arrêter le monitoring
    clearInterval(watchInterval);
    
    // Détruire le player YouTube
    if (youtubePlayer) {
        try {
            youtubePlayer.stopVideo();
            youtubePlayer.destroy();
        } catch (e) {
            console.log('Erreur destruction player:', e);
        }
        youtubePlayer = null;
    }
    
    playerReady = false;
    
    // Cacher le modal
    document.getElementById('watchModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    
    // Vider le conteneur
    document.getElementById('youtubePlayer').innerHTML = '';
}

function closeSuccessModal() {
    document.getElementById('successModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    
    // Rafraîchir la page pour mettre à jour les statistiques
    setTimeout(() => {
        location.reload();
    }, 1000);
}

// Fermer les modals en cliquant à l'extérieur
document.getElementById('watchModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

document.getElementById('successModal').addEventListener('click', function(e) {
    if (e.target === this) closeSuccessModal();
});

// Fermer avec la touche Échap
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!document.getElementById('watchModal').classList.contains('hidden')) {
            closeModal();
        }
        if (!document.getElementById('successModal').classList.contains('hidden')) {
            closeSuccessModal();
        }
    }
});
</script>
</body>
</html>