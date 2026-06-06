<?php
// pages/notifications.php


// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$alert_type = '';
$notifications = [];
$unread_count = 0;
$stats = [];

// Types de notifications avec icônes
$notification_types = [
    'all' => ['name' => 'Toutes', 'icon' => 'fas fa-bell', 'color' => 'gray'],
    'unread' => ['name' => 'Non lues', 'icon' => 'fas fa-envelope', 'color' => 'red'],
    'investment' => ['name' => 'Investissements', 'icon' => 'fas fa-chart-line', 'color' => 'green'],
    'withdrawal' => ['name' => 'Retraits', 'icon' => 'fas fa-download', 'color' => 'blue'],
    'referral' => ['name' => 'Parrainage', 'icon' => 'fas fa-users', 'color' => 'yellow'],
    'video' => ['name' => 'Vidéos', 'icon' => 'fas fa-video', 'color' => 'purple'],
    'system' => ['name' => 'Système', 'icon' => 'fas fa-cog', 'color' => 'gray'],
    'promotion' => ['name' => 'Promotions', 'icon' => 'fas fa-gift', 'color' => 'pink']
];

// Fonction pour formater la date
function timeAgo($datetime) {
    if (!$datetime) return 'Date inconnue';
    
    $time = strtotime($datetime);
    if ($time === false) return 'Date invalide';
    
    $time_difference = time() - $time;
    
    if ($time_difference < 60) {
        return 'À l\'instant';
    } elseif ($time_difference < 3600) {
        $minutes = round($time_difference / 60);
        return "Il y a $minutes minute" . ($minutes > 1 ? 's' : '');
    } elseif ($time_difference < 86400) {
        $hours = round($time_difference / 3600);
        return "Il y a $hours heure" . ($hours > 1 ? 's' : '');
    } elseif ($time_difference < 604800) {
        $days = round($time_difference / 86400);
        return "Il y a $days jour" . ($days > 1 ? 's' : '');
    } else {
        return date('d/m/Y à H:i', $time);
    }
}

try {
    // Connexion à la base de données
    require_once 'config/db.php';
    $pdo = Database::getInstance()->getConnection();
    
    // Créer la table notifications si elle n'existe pas
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS notifications (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            type ENUM('system', 'investment', 'withdrawal', 'referral', 'video', 'promotion', 'security', 'update') NOT NULL DEFAULT 'system',
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            icon VARCHAR(50) DEFAULT 'fas fa-bell',
            icon_color VARCHAR(20) DEFAULT 'text-green-600',
            bg_color VARCHAR(20) DEFAULT 'bg-green-100',
            is_read BOOLEAN DEFAULT FALSE,
            action_url VARCHAR(500),
            action_text VARCHAR(100),
            expires_at DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_read (is_read),
            INDEX idx_created (created_at),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    
    // Traitement des actions
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'mark_all_read':
                $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $message = "Toutes les notifications marquées comme lues";
                $alert_type = 'success';
                break;
                
            case 'mark_read':
                $notification_id = intval($_POST['notification_id'] ?? 0);
                if ($notification_id > 0) {
                    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
                    $stmt->execute([$notification_id, $user_id]);
                    $message = "Notification marquée comme lue";
                    $alert_type = 'success';
                }
                break;
                
            case 'delete_notification':
                $notification_id = intval($_POST['notification_id'] ?? 0);
                if ($notification_id > 0) {
                    $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
                    $stmt->execute([$notification_id, $user_id]);
                    $message = "Notification supprimée";
                    $alert_type = 'success';
                }
                break;
                
            case 'delete_all_read':
                $stmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ? AND is_read = 1");
                $stmt->execute([$user_id]);
                $message = "Toutes les notifications lues ont été supprimées";
                $alert_type = 'success';
                break;
        }
    }
    
    // Récupérer les notifications
    $filter = $_GET['filter'] ?? 'all';
    $search = $_GET['search'] ?? '';
    
    $query = "SELECT * FROM notifications WHERE user_id = ?";
    $params = [$user_id];
    
    // Appliquer les filtres
    if ($filter === 'unread') {
        $query .= " AND is_read = 0";
    } elseif ($filter === 'read') {
        $query .= " AND is_read = 1";
    } elseif (in_array($filter, ['system', 'investment', 'withdrawal', 'referral', 'video', 'promotion', 'security', 'update'])) {
        $query .= " AND type = ?";
        $params[] = $filter;
    }
    
    // Appliquer la recherche
    if (!empty($search)) {
        $query .= " AND (title LIKE ? OR message LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    // Trier et limiter
    $query .= " ORDER BY created_at DESC LIMIT 50";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Compter les non-lues
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $unread_count = $result ? (int)$result['count'] : 0;
    
    // Statistiques - requête corrigée
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
            SUM(CASE WHEN type = 'investment' THEN 1 ELSE 0 END) as investment,
            SUM(CASE WHEN type = 'withdrawal' THEN 1 ELSE 0 END) as withdrawal,
            SUM(CASE WHEN type = 'referral' THEN 1 ELSE 0 END) as referral,
            SUM(CASE WHEN type = 'video' THEN 1 ELSE 0 END) as video,
            SUM(CASE WHEN type = 'system' THEN 1 ELSE 0 END) as system_notifications,
            SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as last_7_days
        FROM notifications 
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // S'assurer que toutes les clés existent
    $stats = array_merge([
        'total' => 0,
        'unread' => 0,
        'investment' => 0,
        'withdrawal' => 0,
        'referral' => 0,
        'video' => 0,
        'system_notifications' => 0,
        'last_7_days' => 0
    ], $stats ?: []);
    
    // Générer des notifications de démo si aucune
    if (empty($notifications)) {
        // generateDemoNotifications($pdo, $user_id);
        // Recharger les notifications
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
        $stmt->execute([$user_id]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (Exception $e) {
    $message = "Erreur: " . $e->getMessage();
    $alert_type = 'error';
    $notifications = [];
    $unread_count = 0;
    $stats = [
        'total' => 0,
        'unread' => 0,
        'investment' => 0,
        'withdrawal' => 0,
        'referral' => 0,
        'video' => 0,
        'system_notifications' => 0,
        'last_7_days' => 0
    ];
}

// Fonction pour générer des notifications de démo
// function generateDemoNotifications($pdo, $user_id) {
//     $demo_notifications = [
//         [
//             'type' => 'investment',
//             'title' => 'Nouveau ROI disponible',
//             'message' => 'Votre investissement "Plan Starter" a généré un ROI de 750 FCFA',
//             'icon' => 'fas fa-coins',
//             'icon_color' => 'text-yellow-600',
//             'bg_color' => 'bg-yellow-100',
//             'action_url' => '?page=wallet',
//             'action_text' => 'Voir mon portefeuille'
//         ],
//         [
//             'type' => 'withdrawal',
//             'title' => 'Retrait approuvé',
//             'message' => 'Votre retrait de 25,000 FCFA a été traité et sera crédité sous 24h',
//             'icon' => 'fas fa-check-circle',
//             'icon_color' => 'text-green-600',
//             'bg_color' => 'bg-green-100',
//             'action_url' => '?page=transactions',
//             'action_text' => 'Voir la transaction'
//         ],
//         [
//             'type' => 'referral',
//             'title' => 'Nouveau filleul',
//             'message' => 'Marie Konaté s\'est inscrit avec votre code de parrainage',
//             'icon' => 'fas fa-user-plus',
//             'icon_color' => 'text-blue-600',
//             'bg_color' => 'bg-blue-100',
//             'action_url' => '?page=parrainage',
//             'action_text' => 'Voir mes filleuls'
//         ],
//         [
//             'type' => 'video',
//             'title' => 'Vidéos disponibles',
//             'message' => '5 nouvelles vidéos rémunérées sont disponibles aujourd\'hui',
//             'icon' => 'fas fa-video',
//             'icon_color' => 'text-purple-600',
//             'bg_color' => 'bg-purple-100',
//             'action_url' => '?page=videos',
//             'action_text' => 'Regarder maintenant'
//         ],
//         [
//             'type' => 'promotion',
//             'title' => 'Offre spéciale',
//             'message' => 'Investissez 50,000 FCFA et obtenez 10% de bonus supplémentaire',
//             'icon' => 'fas fa-gift',
//             'icon_color' => 'text-red-600',
//             'bg_color' => 'bg-red-100',
//             'action_url' => '?page=investissement',
//             'action_text' => 'Profiter de l\'offre'
//         ],
//         [
//             'type' => 'security',
//             'title' => 'Connexion détectée',
//             'message' => 'Une nouvelle connexion a été détectée depuis un appareil Windows',
//             'icon' => 'fas fa-shield-alt',
//             'icon_color' => 'text-orange-600',
//             'bg_color' => 'bg-orange-100',
//             'action_url' => '?page=settings&section=securite',
//             'action_text' => 'Vérifier la sécurité'
//         ],
//         [
//             'type' => 'update',
//             'title' => 'Mise à jour disponible',
//             'message' => 'Une nouvelle version de l\'application est disponible',
//             'icon' => 'fas fa-sync-alt',
//             'icon_color' => 'text-indigo-600',
//             'bg_color' => 'bg-indigo-100',
//             'action_url' => '#',
//             'action_text' => 'Mettre à jour'
//         ]
//     ];
    
//     $stmt = $pdo->prepare("
//         INSERT INTO notifications 
//         (user_id, type, title, message, icon, icon_color, bg_color, action_url, action_text, created_at) 
//         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
//     ");
    
//     foreach ($demo_notifications as $index => $notification) {
//         $date = date('Y-m-d H:i:s', strtotime("-{$index} hours"));
//         $stmt->execute([
//             $user_id,
//             $notification['type'],
//             $notification['title'],
//             $notification['message'],
//             $notification['icon'],
//             $notification['icon_color'],
//             $notification['bg_color'],
//             $notification['action_url'],
//             $notification['action_text'],
//             $date
//         ]);
//     }
// }
?>


    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            overflow-x: hidden;
        }
        .notifications-container { animation: fadeIn 0.5s ease-out; }
        @keyframes fadeIn { 
            from { opacity: 0; transform: translateY(10px); } 
            to { opacity: 1; transform: translateY(0); } 
        }
        @keyframes slideIn { 
            from { transform: translateX(-20px); opacity: 0; } 
            to { transform: translateX(0); opacity: 1; } 
        }
        @keyframes pulse { 
            0%, 100% { opacity: 1; } 
            50% { opacity: 0.5; } 
        }
        .animate-slideIn { animation: slideIn 0.3s ease-out; }
        .animate-pulse { animation: pulse 2s infinite; }
        
        /* Correction pour le débordement sur mobile */
        .notification-grid-container {
            display: flex;
            flex-direction: column;
        }
        
        @media (min-width: 1024px) {
            .notification-grid-container {
                display: grid;
                grid-template-columns: 1fr 3fr;
                gap: 2rem;
            }
        }
        
        /* Correction des largeurs sur mobile */
        .container {
            width: 100%;
            max-width: 100%;
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        @media (min-width: 640px) {
            .container {
                max-width: 640px;
            }
        }
        
        @media (min-width: 768px) {
            .container {
                max-width: 768px;
            }
        }
        
        @media (min-width: 1024px) {
            .container {
                max-width: 1024px;
            }
        }
        
        /* Amélioration du responsive des cartes */
        .notification-card {
            width: 100%;
            margin-bottom: 1rem;
        }
        
        /* Correction des icônes et couleurs */
        .icon-gray-100 { background-color: #f3f4f6 !important; }
        .text-gray-600 { color: #4b5563 !important; }
        
        /* Empêcher le débordement horizontal */
        * {
            max-width: 100%;
        }
        
        /* Amélioration du scroll sur mobile */
        .notifications-list {
            -webkit-overflow-scrolling: touch;
        }
    </style>

    <div class="min-h-screen ">

        
        <!-- Message d'alerte -->
        <?php if ($message): ?>
        <div class="fixed top-4 right-4 z-50 px-6 py-3 <?php echo $alert_type == 'success' ? 'bg-green-600' : 'bg-red-600'; ?> 
                    text-white rounded-lg shadow-lg font-medium animate-slideIn">
            <div class="flex items-center">
                <i class="fas fa-<?php echo $alert_type == 'success' ? 'check' : 'exclamation'; ?>-circle mr-2"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="container mx-auto px-4 py-4 md:py-8">
            <div class="notifications-container">
                <!-- En-tête avec statistiques -->
                <div class="mb-6 md:mb-8">
                    <div class="flex flex-col md:flex-row md:items-center justify-between mb-4 md:mb-6">
                        <div class="mb-4 md:mb-0">
                            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">Notifications</h1>
                            <p class="text-gray-600 text-sm md:text-base">Restez informé de toutes vos activités</p>
                        </div>
                        
                        <!-- Boutons d'action -->
                        <div class="flex flex-wrap gap-2 md:space-x-3">
                            <button onclick="markAllAsRead()" 
                                    class="flex-1 md:flex-none px-3 py-2 md:px-4 md:py-2 bg-green-600 text-white text-sm md:text-base font-medium rounded-lg hover:bg-green-700 transition">
                                <i class="fas fa-check-double mr-2"></i>
                                <span class="hidden md:inline">Tout marquer</span>
                                <span class="md:hidden">Tout lu</span>
                            </button>
                            <button onclick="deleteAllRead()" 
                                    class="flex-1 md:flex-none px-3 py-2 md:px-4 md:py-2 border border-red-300 text-red-600 text-sm md:text-base font-medium rounded-lg hover:bg-red-50 transition">
                                <i class="fas fa-trash-alt mr-2"></i>
                                <span class="hidden md:inline">Supprimer lues</span>
                                <span class="md:hidden">Supprimer</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Statistiques -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 md:gap-4 mb-4 md:mb-6">
                        <div class="bg-white rounded-xl shadow-sm p-3 md:p-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 md:w-12 md:h-12 bg-red-100 rounded-full flex items-center justify-center mr-2 md:mr-3">
                                    <i class="fas fa-envelope text-red-600 text-sm md:text-base"></i>
                                </div>
                                <div>
                                    <p class="text-xs md:text-sm text-gray-500">Non lues</p>
                                    <p class="text-lg md:text-2xl font-bold text-gray-800"><?php echo $unread_count; ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-xl shadow-sm p-3 md:p-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 md:w-12 md:h-12 bg-blue-100 rounded-full flex items-center justify-center mr-2 md:mr-3">
                                    <i class="fas fa-bell text-blue-600 text-sm md:text-base"></i>
                                </div>
                                <div>
                                    <p class="text-xs md:text-sm text-gray-500">Total</p>
                                    <p class="text-lg md:text-2xl font-bold text-gray-800"><?php echo $stats['total']; ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-xl shadow-sm p-3 md:p-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 md:w-12 md:h-12 bg-green-100 rounded-full flex items-center justify-center mr-2 md:mr-3">
                                    <i class="fas fa-calendar-alt text-green-600 text-sm md:text-base"></i>
                                </div>
                                <div>
                                    <p class="text-xs md:text-sm text-gray-500">7 derniers jours</p>
                                    <p class="text-lg md:text-2xl font-bold text-gray-800"><?php echo $stats['last_7_days']; ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-xl shadow-sm p-3 md:p-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 md:w-12 md:h-12 bg-purple-100 rounded-full flex items-center justify-center mr-2 md:mr-3">
                                    <i class="fas fa-chart-pie text-purple-600 text-sm md:text-base"></i>
                                </div>
                                <div>
                                    <p class="text-xs md:text-sm text-gray-500">Types</p>
                                    <p class="text-lg md:text-2xl font-bold text-gray-800"><?php echo count($notification_types) - 2; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="notification-grid-container">
                    <!-- Filtres latéraux - masqué sur mobile, accessible via bouton -->
                    <div class="hidden lg:block lg:col-span-1">
                        <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6 mb-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-filter text-green-600 mr-2"></i>
                                Filtres
                            </h3>
                            
                            <div class="space-y-2">
                                <?php foreach ($notification_types as $type_key => $type_info): ?>
                                <a href="?page=notifications&filter=<?php echo $type_key; ?>" 
                                   class="flex items-center px-3 py-2 rounded-lg transition-colors
                                          <?php echo ($filter == $type_key) ? 'bg-green-50 text-green-600' : 'text-gray-700 hover:bg-gray-50'; ?>">
                                    <div class="w-8 h-8 <?php echo $type_info['color']; ?>-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="<?php echo $type_info['icon']; ?> <?php echo $type_info['color']; ?>-600 text-sm"></i>
                                    </div>
                                    <span class="font-medium text-sm md:text-base"><?php echo $type_info['name']; ?></span>
                                    <?php if ($type_key === 'unread' && $unread_count > 0): ?>
                                    <span class="ml-auto px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full">
                                        <?php echo $unread_count; ?>
                                    </span>
                                    <?php endif; ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Préférences -->
                        <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-cog text-blue-600 mr-2"></i>
                                Préférences
                            </h3>
                            
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-800 text-sm md:text-base">Notifications push</p>
                                        <p class="text-xs md:text-sm text-gray-500">Sur votre navigateur</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" id="pushToggle" class="sr-only peer" 
                                               onchange="togglePushNotifications(this.checked)">
                                        <div class="w-10 h-6 md:w-11 md:h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 
                                                    peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full 
                                                    peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] 
                                                    after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full 
                                                    after:h-4 after:w-4 md:after:h-5 md:after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                    </label>
                                </div>
                                
                                <div class="pt-4 border-t border-gray-200">
                                    <button onclick="openSettings()" 
                                            class="w-full px-4 py-2 border border-gray-300 text-gray-700 font-medium 
                                                   rounded-lg hover:bg-gray-50 transition text-sm md:text-base">
                                        <i class="fas fa-sliders-h mr-2"></i>
                                        Paramètres avancés
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Liste des notifications -->
                    <div class="lg:col-span-3">
                        <!-- Barre de recherche et filtres mobiles -->
                        <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6 mb-4 md:mb-6">
                            <div class="flex flex-col space-y-4 md:space-y-0 md:flex-row md:items-center justify-between">
                                <div class="mb-2 md:mb-0">
                                    <h3 class="text-lg font-bold text-gray-800">
                                        <?php 
                                        if ($filter === 'all') {
                                            echo 'Toutes les notifications';
                                        } elseif ($filter === 'unread') {
                                            echo 'Notifications non lues';
                                        } else {
                                            echo $notification_types[$filter]['name'] ?? ucfirst($filter);
                                        }
                                        ?>
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        <?php echo count($notifications); ?> notification<?php echo count($notifications) > 1 ? 's' : ''; ?>
                                    </p>
                                </div>
                                
                                <div class="flex flex-col md:flex-row md:items-center space-y-2 md:space-y-0 md:space-x-3">
                                    <!-- Filtres mobiles -->
                                    <div class="lg:hidden">
                                        <select onchange="window.location.href='?page=notifications&filter='+this.value" 
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 text-sm">
                                            <?php foreach ($notification_types as $type_key => $type_info): ?>
                                            <option value="<?php echo $type_key; ?>" <?php echo $filter == $type_key ? 'selected' : ''; ?>>
                                                <?php echo $type_info['name']; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <!-- Recherche -->
                                    <form method="GET" class="flex">
                                        <input type="hidden" name="page" value="notifications">
                                        <input type="hidden" name="filter" value="<?php echo $filter; ?>">
                                        <div class="relative flex-grow">
                                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                                                   placeholder="Rechercher..." 
                                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-l-lg focus:outline-none 
                                                          focus:border-green-500 text-sm">
                                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                                        </div>
                                        <button type="submit" 
                                                class="px-4 py-2 bg-green-600 text-white font-medium rounded-r-lg hover:bg-green-700 transition">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Liste -->
                        <?php if (!empty($notifications)): ?>
                        <div class="notifications-list space-y-3 md:space-y-4">
                            <?php foreach ($notifications as $notification): 
                                // S'assurer que les clés existent
                                $notification = array_merge([
                                    'id' => 0,
                                    'is_read' => 0,
                                    'bg_color' => 'bg-green-100',
                                    'icon' => 'fas fa-bell',
                                    'icon_color' => 'text-green-600',
                                    'title' => 'Sans titre',
                                    'message' => '',
                                    'type' => 'system',
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'expires_at' => null,
                                    'action_url' => null,
                                    'action_text' => null
                                ], $notification);
                            ?>
                            <div class="notification-card bg-white rounded-xl md:rounded-2xl shadow-sm border 
                                        <?php echo !$notification['is_read'] ? 'border-green-200' : 'border-gray-200'; ?> 
                                        hover:shadow-md transition-all duration-300"
                                 data-id="<?php echo $notification['id']; ?>"
                                 data-read="<?php echo $notification['is_read'] ? 'true' : 'false'; ?>">
                                <div class="p-3 md:p-5">
                                    <div class="flex items-start">
                                        <!-- Icône -->
                                        <div class="flex-shrink-0 mr-3 md:mr-4">
                                            <div class="w-10 h-10 md:w-12 md:h-12 <?php echo $notification['bg_color']; ?> rounded-full 
                                                                 flex items-center justify-center">
                                                <i class="<?php echo $notification['icon']; ?> <?php echo $notification['icon_color']; ?> text-base md:text-lg"></i>
                                            </div>
                                        </div>
                                        
                                        <!-- Contenu -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex flex-col md:flex-row md:justify-between md:items-start mb-2">
                                                <div class="flex-1 mb-2 md:mb-0">
                                                    <div class="flex items-center mb-1">
                                                        <?php if (!$notification['is_read']): ?>
                                                        <span class="inline-block w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                                                        <?php endif; ?>
                                                        <h4 class="font-bold text-gray-800 text-base md:text-lg truncate">
                                                            <?php echo htmlspecialchars($notification['title']); ?>
                                                        </h4>
                                                    </div>
                                                    <p class="text-gray-600 text-sm md:text-base line-clamp-2">
                                                        <?php echo htmlspecialchars($notification['message']); ?>
                                                    </p>
                                                </div>
                                                
                                                <div class="flex space-x-2">
                                                    <?php if (!$notification['is_read']): ?>
                                                    <form method="POST" class="inline">
                                                        <input type="hidden" name="action" value="mark_read">
                                                        <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                                        <button type="submit" 
                                                                class="text-green-600 hover:text-green-700 p-1"
                                                                title="Marquer comme lu">
                                                            <i class="fas fa-check text-sm md:text-base"></i>
                                                        </button>
                                                    </form>
                                                    <?php endif; ?>
                                                    
                                                    <div class="relative group">
                                                        <button class="text-gray-400 hover:text-gray-600 p-1">
                                                            <i class="fas fa-ellipsis-v text-sm md:text-base"></i>
                                                        </button>
                                                        <div class="hidden group-hover:block absolute right-0 mt-1 w-40 bg-white 
                                                                    shadow-lg rounded-lg border border-gray-200 z-10">
                                                            <form method="POST" class="p-1">
                                                                <input type="hidden" name="action" value="mark_read">
                                                                <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                                                <button type="submit" 
                                                                        class="w-full text-left px-3 py-2 text-sm text-gray-700 
                                                                               hover:bg-gray-100 rounded">
                                                                    <i class="fas fa-check mr-2"></i>
                                                                    Marquer comme lu
                                                                </button>
                                                            </form>
                                                            <form method="POST" class="p-1">
                                                                <input type="hidden" name="action" value="delete_notification">
                                                                <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                                                <button type="submit" 
                                                                        onclick="return confirm('Supprimer cette notification ?')"
                                                                        class="w-full text-left px-3 py-2 text-sm text-red-600 
                                                                               hover:bg-red-50 rounded">
                                                                    <i class="fas fa-trash-alt mr-2"></i>
                                                                    Supprimer
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Métadonnées et actions -->
                                            <div class="flex flex-col md:flex-row md:items-center justify-between mt-3 md:mt-4 pt-3 border-t border-gray-100">
                                                <div class="flex items-center flex-wrap gap-2 md:space-x-4 text-xs md:text-sm text-gray-500 mb-2 md:mb-0">
                                                    <span>
                                                        <i class="far fa-clock mr-1"></i>
                                                        <?php echo timeAgo($notification['created_at']); ?>
                                                    </span>
                                                    <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs">
                                                        <?php 
                                                        $type_names = [
                                                            'system' => 'Système',
                                                            'investment' => 'Investissement',
                                                            'withdrawal' => 'Retrait',
                                                            'referral' => 'Parrainage',
                                                            'video' => 'Vidéo',
                                                            'promotion' => 'Promotion',
                                                            'security' => 'Sécurité',
                                                            'update' => 'Mise à jour'
                                                        ];
                                                        echo $type_names[$notification['type']] ?? ucfirst($notification['type']);
                                                        ?>
                                                    </span>
                                                </div>
                                                
                                                <?php if ($notification['action_url'] && $notification['action_text']): ?>
                                                <a href="<?php echo htmlspecialchars($notification['action_url']); ?>" 
                                                   class="inline-flex items-center px-3 py-1 md:px-4 md:py-2 bg-green-600 text-white text-sm md:text-base font-medium 
                                                          rounded-lg hover:bg-green-700 transition mt-2 md:mt-0">
                                                    <span class="hidden md:inline"><?php echo htmlspecialchars($notification['action_text']); ?></span>
                                                    <span class="md:hidden">Voir</span>
                                                    <i class="fas fa-arrow-right ml-2 text-xs md:text-sm"></i>
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-6 md:mt-8 flex justify-center">
                            <nav class="flex items-center space-x-1 md:space-x-2">
                                <button class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center border border-gray-300 
                                              rounded-lg hover:bg-gray-50 transition">
                                    <i class="fas fa-chevron-left text-xs md:text-sm"></i>
                                </button>
                                <button class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center bg-green-600 text-white rounded-lg text-sm md:text-base">1</button>
                                <button class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center border border-gray-300 
                                              rounded-lg hover:bg-gray-50 transition text-sm md:text-base">2</button>
                                <button class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center border border-gray-300 
                                              rounded-lg hover:bg-gray-50 transition">
                                    <i class="fas fa-chevron-right text-xs md:text-sm"></i>
                                </button>
                            </nav>
                        </div>
                        <?php else: ?>
                        <div class="bg-white rounded-2xl shadow-sm p-8 md:p-12 text-center">
                            <div class="w-16 h-16 md:w-24 md:h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 md:mb-6">
                                <i class="fas fa-bell-slash text-gray-400 text-2xl md:text-3xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Aucune notification</h3>
                            <p class="text-gray-600 mb-4 md:mb-6 text-sm md:text-base">
                                <?php if ($filter === 'unread'): ?>
                                Vous n'avez aucune notification non lue
                                <?php elseif ($filter !== 'all'): ?>
                                Aucune notification de type "<?php echo $notification_types[$filter]['name'] ?? $filter; ?>"
                                <?php else: ?>
                                Vous n'avez pas encore de notifications
                                <?php endif; ?>
                            </p>
                            <?php if ($filter !== 'all'): ?>
                            <a href="?page=notifications&filter=all" 
                               class="inline-flex items-center px-4 py-2 bg-green-600 text-white font-medium 
                                      rounded-lg hover:bg-green-700 transition text-sm md:text-base">
                                <i class="fas fa-bell mr-2"></i>
                                Voir toutes les notifications
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Statistiques détaillées -->
                        <?php if ($stats['total'] > 0): ?>
                        <div class="mt-6 md:mt-8 bg-white rounded-2xl shadow-sm p-4 md:p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-chart-bar text-green-600 mr-2"></i>
                                Statistiques des notifications
                            </h3>
                            
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 md:gap-4 mb-4 md:mb-6">
                                <div class="text-center p-3 md:p-4 border border-gray-200 rounded-lg">
                                    <div class="text-xl md:text-2xl font-bold text-green-600 mb-1"><?php echo $stats['investment']; ?></div>
                                    <p class="text-xs md:text-sm text-gray-600">Investissements</p>
                                </div>
                                <div class="text-center p-3 md:p-4 border border-gray-200 rounded-lg">
                                    <div class="text-xl md:text-2xl font-bold text-blue-600 mb-1"><?php echo $stats['withdrawal']; ?></div>
                                    <p class="text-xs md:text-sm text-gray-600">Retraits</p>
                                </div>
                                <div class="text-center p-3 md:p-4 border border-gray-200 rounded-lg">
                                    <div class="text-xl md:text-2xl font-bold text-yellow-600 mb-1"><?php echo $stats['referral']; ?></div>
                                    <p class="text-xs md:text-sm text-gray-600">Parrainage</p>
                                </div>
                                <div class="text-center p-3 md:p-4 border border-gray-200 rounded-lg">
                                    <div class="text-xl md:text-2xl font-bold text-purple-600 mb-1"><?php echo $stats['video']; ?></div>
                                    <p class="text-xs md:text-sm text-gray-600">Vidéos</p>
                                </div>
                            </div>
                            
                            <!-- Graphique simple -->
                            <div class="mt-4 md:mt-6 pt-4 border-t border-gray-200">
                                <div class="flex items-center justify-between mb-3 md:mb-4">
                                    <h4 class="font-medium text-gray-800 text-sm md:text-base">Répartition par type</h4>
                                    <span class="text-xs md:text-sm text-gray-500">100%</span>
                                </div>
                                <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <?php 
                                    $total = max(1, $stats['total']);
                                    $investment_percent = round(($stats['investment']) / $total * 100);
                                    $withdrawal_percent = round(($stats['withdrawal']) / $total * 100);
                                    $referral_percent = round(($stats['referral']) / $total * 100);
                                    $video_percent = round(($stats['video']) / $total * 100);
                                    $system_percent = round(($stats['system_notifications']) / $total * 100);
                                    ?>
                                    <div class="h-full flex">
                                        <div class="bg-green-500" style="width: <?php echo $investment_percent; ?>%"></div>
                                        <div class="bg-blue-500" style="width: <?php echo $withdrawal_percent; ?>%"></div>
                                        <div class="bg-yellow-500" style="width: <?php echo $referral_percent; ?>%"></div>
                                        <div class="bg-purple-500" style="width: <?php echo $video_percent; ?>%"></div>
                                        <div class="bg-gray-500" style="width: <?php echo $system_percent; ?>%"></div>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-3 md:gap-4 mt-3 text-xs md:text-sm">
                                    <div class="flex items-center">
                                        <div class="w-2 h-2 md:w-3 md:h-3 bg-green-500 rounded mr-1 md:mr-2"></div>
                                        <span class="text-gray-600">Investissements (<?php echo $investment_percent; ?>%)</span>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-2 h-2 md:w-3 md:h-3 bg-blue-500 rounded mr-1 md:mr-2"></div>
                                        <span class="text-gray-600">Retraits (<?php echo $withdrawal_percent; ?>%)</span>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-2 h-2 md:w-3 md:h-3 bg-yellow-500 rounded mr-1 md:mr-2"></div>
                                        <span class="text-gray-600">Parrainage (<?php echo $referral_percent; ?>%)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal paramètres avancés -->
    <div id="settingsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md transform transition-all max-h-[90vh] overflow-y-auto">
            <div class="p-4 md:p-6">
                <div class="flex justify-between items-center mb-4 md:mb-6">
                    <h3 class="text-lg md:text-xl font-bold text-gray-800">Paramètres des notifications</h3>
                    <button onclick="closeSettings()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-lg md:text-xl"></i>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-800 text-sm md:text-base">Notifications push</p>
                            <p class="text-xs md:text-sm text-gray-500">Recevoir des notifications push</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" checked>
                            <div class="w-10 h-6 md:w-11 md:h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 
                                        peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full 
                                        peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] 
                                        after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full 
                                        after:h-4 after:w-4 md:after:h-5 md:after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                        </label>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-800 text-sm md:text-base">Notifications email</p>
                            <p class="text-xs md:text-sm text-gray-500">Recevoir des emails de notification</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" checked>
                            <div class="w-10 h-6 md:w-11 md:h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 
                                        peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full 
                                        peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] 
                                        after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full 
                                        after:h-4 after:w-4 md:after:h-5 md:after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                        </label>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-800 text-sm md:text-base">Notifications SMS</p>
                            <p class="text-xs md:text-sm text-gray-500">Recevoir des SMS (coût applicable)</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer">
                            <div class="w-10 h-6 md:w-11 md:h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 
                                        peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full 
                                        peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] 
                                        after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full 
                                        after:h-4 after:w-4 md:after:h-5 md:after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                        </label>
                    </div>
                    
                    <div class="pt-4 border-t border-gray-200">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fréquence des résumés</label>
                        <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 text-sm md:text-base">
                            <option value="daily">Quotidien</option>
                            <option value="weekly" selected>Hebdomadaire</option>
                            <option value="monthly">Mensuel</option>
                            <option value="none">Jamais</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex space-x-3 mt-6 md:mt-8">
                    <button onclick="closeSettings()" 
                            class="flex-1 px-4 py-2 md:py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition text-sm md:text-base">
                        Annuler
                    </button>
                    <button onclick="saveNotificationSettings()" 
                            class="flex-1 px-4 py-2 md:py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition text-sm md:text-base">
                        Enregistrer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Fonctions pour les actions
    function markAllAsRead() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="mark_all_read">
        `;
        document.body.appendChild(form);
        form.submit();
    }

    function deleteAllRead() {
        if (confirm('Supprimer toutes les notifications lues ?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete_all_read">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function openSettings() {
        document.getElementById('settingsModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeSettings() {
        document.getElementById('settingsModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function saveNotificationSettings() {
        showToast('Paramètres enregistrés avec succès', 'success');
        closeSettings();
    }

    // Notifications push
    function togglePushNotifications(enabled) {
        if (enabled) {
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        showToast('Notifications push activées', 'success');
                    } else {
                        document.getElementById('pushToggle').checked = false;
                        showToast('Permission refusée pour les notifications', 'error');
                    }
                });
            } else if (Notification.permission === 'granted') {
                showToast('Notifications push activées', 'success');
            } else {
                document.getElementById('pushToggle').checked = false;
                showToast('Veuillez autoriser les notifications dans les paramètres du navigateur', 'error');
            }
        } else {
            showToast('Notifications push désactivées', 'info');
        }
    }

    // Fonction utilitaire pour les notifications
    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 px-4 py-2 md:px-6 md:py-3 rounded-lg shadow-lg text-white font-medium animate-slideIn ${
            type === 'success' ? 'bg-green-600' : 
            type === 'error' ? 'bg-red-600' : 
            'bg-blue-600'
        }`;
        toast.innerHTML = `
            <div class="flex items-center text-sm md:text-base">
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

    // Interactivité des notifications
    document.addEventListener('DOMContentLoaded', function() {
        // Marquer comme lu au clic
        document.querySelectorAll('.notification-card').forEach(item => {
            item.addEventListener('click', function(e) {
                if (!e.target.closest('button') && !e.target.closest('a') && !e.target.closest('form')) {
                    const id = this.dataset.id;
                    const isRead = this.dataset.read === 'true';
                    
                    if (!isRead) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.innerHTML = `
                            <input type="hidden" name="action" value="mark_read">
                            <input type="hidden" name="notification_id" value="${id}">
                        `;
                        document.body.appendChild(form);
                        form.submit();
                    }
                }
            });
        });

        // Auto-hide les messages
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

        // Recherche en temps réel
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.form.submit();
                }, 500);
            });
        }

        // Correction pour les écrans mobiles
        function adjustForMobile() {
            const isMobile = window.innerWidth < 768;
            
            if (isMobile) {
                // Ajuster les largeurs sur mobile
                document.querySelectorAll('.container').forEach(container => {
                    container.style.paddingLeft = '0.5rem';
                    container.style.paddingRight = '0.5rem';
                });
                
                // Simplifier les cartes de notification
                document.querySelectorAll('.notification-card').forEach(card => {
                    card.style.marginLeft = '-0.5rem';
                    card.style.marginRight = '-0.5rem';
                    card.style.borderRadius = '0';
                });
            }
        }
        
        // Exécuter au chargement et au redimensionnement
        adjustForMobile();
        window.addEventListener('resize', adjustForMobile);
    });

    // Animation CSS
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(-20px);
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
                transform: translateX(20px);
                opacity: 0;
            }
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.7;
                transform: scale(1.05);
            }
        }
        
        .animate-slideIn {
            animation: slideIn 0.3s ease-out;
        }
        
        .animate-slideOut {
            animation: slideOut 0.3s ease-in;
        }
        
        .animate-pulse {
            animation: pulse 1s infinite;
        }
        
        .notification-card {
            transition: all 0.3s ease;
        }
        
        .notification-card:hover {
            transform: translateY(-2px);
        }
        
        .notification-card.read {
            opacity: 0.8;
        }
        
        .notification-card.unread {
            border-left-width: 4px;
            border-left-color: #10b981;
        }
        
        /* Limiter le texte à 2 lignes */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        /* Cacher le texte long */
        .truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        /* Amélioration du dropdown */
        .group:hover .group-hover\\:block {
            display: block;
        }
        
        /* Correction pour les très petits écrans */
        @media (max-width: 360px) {
            .container {
                padding-left: 0.25rem;
                padding-right: 0.25rem;
            }
            
            .notification-card {
                margin-left: -0.25rem;
                margin-right: -0.25rem;
            }
        }
        
        /* Empêcher le zoom sur les inputs sur iOS */
        input, select, textarea {
            font-size: 16px;
        }
        
        /* Amélioration du touch sur mobile */
        button, a {
            -webkit-tap-highlight-color: transparent;
        }
    `;
    document.head.appendChild(style);

    // Fermer le modal en cliquant à l'extérieur
    document.getElementById('settingsModal').addEventListener('click', (e) => {
        if (e.target.id === 'settingsModal') {
            closeSettings();
        }
    });
    </script>
