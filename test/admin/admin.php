<?php
// Démarrer l'output buffering pour éviter les erreurs "headers already sent"
ob_start();

session_start();
require_once 'config/database.php';

// Vérifier l'authentification administrateur
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Récupérer les statistiques pour la sidebar
$stats = [
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'total_investments' => $pdo->query("SELECT COUNT(*) FROM user_plans WHERE statut = 'active'")->fetchColumn(),
    'total_deposits' => $pdo->query("SELECT SUM(montant) FROM transactions WHERE type = 'depot' AND statut = 'success'")->fetchColumn() ?? 0,
    'total_withdrawals' => $pdo->query("SELECT SUM(montant) FROM transactions WHERE type = 'retrait' AND statut = 'success'")->fetchColumn() ?? 0,
    'pending_kyc' => $pdo->query("SELECT COUNT(*) FROM kyc WHERE statut = 'pending'")->fetchColumn(),
    'pending_withdrawals' => $pdo->query("SELECT COUNT(*) FROM transactions WHERE type = 'retrait' AND statut = 'attente'")->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Invest Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            min-height: 100vh;
        }
        
        .sidebar {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-logo {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-item {
            position: relative;
            border-radius: 12px;
            margin: 4px 12px;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
        
        .nav-item.active {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
        
        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: #60a5fa;
            border-radius: 0 4px 4px 0;
        }
        
        .badge {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        .mobile-menu-btn {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
        
        .mobile-menu-overlay {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
        }
        
        /* Scrollbar personnalisée */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        /* Animations */
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Gradient text */
        .gradient-text {
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="text-gray-800">
    <!-- Mobile Menu Button -->
    <div class="lg:hidden fixed top-4 left-4 z-50">
        <button id="mobileMenuBtn" class="mobile-menu-btn w-12 h-12 rounded-full flex items-center justify-center text-white shadow-lg">
            <i class="fas fa-bars text-lg"></i>
        </button>
    </div>

    <!-- Mobile Overlay -->
    <div id="mobileOverlay" class="mobile-menu-overlay fixed inset-0 z-40 hidden lg:hidden"></div>

    <!-- Sidebar -->
    <div id="sidebar" class="sidebar fixed inset-y-0 left-0 z-40 w-72 text-white lg:translate-x-0">
        <!-- Logo et en-tête -->
        <div class="p-6 border-b border-gray-800">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-lg">
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold sidebar-logo">Invest Admin</h1>
                    <p class="text-xs text-gray-400 mt-1">Super Admin Panel</p>
                </div>
            </div>
            
            <!-- Admin info -->
            <div class="mt-6 p-3 bg-gray-800/50 rounded-xl">
                <div class="flex items-center space-x-3">
                    <div class="relative">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-cyan-400 flex items-center justify-center">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-green-500 rounded-full border-2 border-gray-900"></div>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold"><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></p>
                        <p class="text-xs text-gray-400">Super Admin</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="p-4 flex-1 overflow-y-auto" style="max-height: calc(100vh - 200px);">
            <div class="space-y-1">
                <a href="admin.php?page=dashboard" 
                   class="nav-item flex items-center px-4 py-3 <?= (!isset($_GET['page']) || $_GET['page'] == 'dashboard') ? 'active' : '' ?>">
                    <div class="w-8 text-center">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <span class="ml-3 font-medium">Tableau de Bord</span>
                </a>
                
                <a href="admin.php?page=users" 
                   class="nav-item flex items-center px-4 py-3 <?= (isset($_GET['page']) && $_GET['page'] == 'users') ? 'active' : '' ?>">
                    <div class="w-8 text-center">
                        <i class="fas fa-users"></i>
                    </div>
                    <span class="ml-3 font-medium">Utilisateurs</span>
                    <span class="ml-auto bg-blue-500 text-white text-xs px-2 py-1 rounded-full">
                        <?= number_format($stats['total_users']) ?>
                    </span>
                </a>
                
                <a href="admin.php?page=plans" 
                   class="nav-item flex items-center px-4 py-3 <?= (isset($_GET['page']) && $_GET['page'] == 'plans') ? 'active' : '' ?>">
                    <div class="w-8 text-center">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <span class="ml-3 font-medium">Plans</span>
                </a>
                
                <a href="admin.php?page=investments" 
                   class="nav-item flex items-center px-4 py-3 <?= (isset($_GET['page']) && $_GET['page'] == 'investments') ? 'active' : '' ?>">
                    <div class="w-8 text-center">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <span class="ml-3 font-medium">Investissements</span>
                    <span class="ml-auto bg-green-500 text-white text-xs px-2 py-1 rounded-full">
                        <?= number_format($stats['total_investments']) ?>
                    </span>
                </a>
                
                <a href="admin.php?page=transactions" 
                   class="nav-item flex items-center px-4 py-3 <?= (isset($_GET['page']) && $_GET['page'] == 'transactions') ? 'active' : '' ?>">
                    <div class="w-8 text-center">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <span class="ml-3 font-medium">Transactions</span>
                </a>
                
                <a href="admin.php?page=withdrawals" 
                   class="nav-item flex items-center px-4 py-3 <?= (isset($_GET['page']) && $_GET['page'] == 'withdrawals') ? 'active' : '' ?>">
                    <div class="w-8 text-center">
                        <i class="fas fa-money-check-alt"></i>
                    </div>
                    <span class="ml-3 font-medium">Retraits</span>
                    <?php if($stats['pending_withdrawals'] > 0): ?>
                    <span class="ml-auto badge bg-red-500 text-white text-xs px-2 py-1 rounded-full">
                        <?= $stats['pending_withdrawals'] ?>
                    </span>
                    <?php endif; ?>
                </a>
                
                <a href="admin.php?page=kyc" 
                   class="nav-item flex items-center px-4 py-3 <?= (isset($_GET['page']) && $_GET['page'] == 'kyc') ? 'active' : '' ?>">
                    <div class="w-8 text-center">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <span class="ml-3 font-medium">valider invest </span>
                    <?php if($stats['pending_kyc'] > 0): ?>
                    <span class="ml-auto badge bg-yellow-500 text-white text-xs px-2 py-1 rounded-full">
                        <?= $stats['pending_kyc'] ?>
                    </span>
                    <?php endif; ?>
                </a>
                
                <a href="admin.php?page=validate_roi" 
                   class="nav-item flex items-center px-4 py-3 <?= (isset($_GET['page']) && $_GET['page'] == 'validate_roi') ? 'active' : '' ?>">
                    <div class="w-8 text-center">
                        <i class="fas fa-coins"></i>
                    </div>
                    <span class="ml-3 font-medium">Valider ROI</span>
                    <span class="ml-auto badge bg-yellow-500 text-white text-xs px-2 py-1 rounded-full">
                        Quotidien
                    </span>
                </a>
                
                <a href="admin.php?page=videos" 
                   class="nav-item flex items-center px-4 py-3 <?= (isset($_GET['page']) && $_GET['page'] == 'videos') ? 'active' : '' ?>">
                    <div class="w-8 text-center">
                        <i class="fas fa-video"></i>
                    </div>
                    <span class="ml-3 font-medium">Vidéos</span>
                </a>
                
                <a href="admin.php?page=error_logs" 
                   class="nav-item flex items-center px-4 py-3 <?= (isset($_GET['page']) && $_GET['page'] == 'error_logs') ? 'active' : '' ?>">
                    <div class="w-8 text-center">
                        <i class="fas fa-bug"></i>
                    </div>
                    <span class="ml-3 font-medium">Logs d'Erreurs</span>
                </a>
                
                <a href="admin.php?page=settings" 
                   class="nav-item flex items-center px-4 py-3 <?= (isset($_GET['page']) && $_GET['page'] == 'settings') ? 'active' : '' ?>">
                    <div class="w-8 text-center">
                        <i class="fas fa-cog"></i>
                    </div>
                    <span class="ml-3 font-medium">Paramètres</span>
                </a>
                
                <a href="admin.php?page=admins" 
                   class="nav-item flex items-center px-4 py-3 <?= (isset($_GET['page']) && $_GET['page'] == 'admins') ? 'active' : '' ?>">
                    <div class="w-8 text-center">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <span class="ml-3 font-medium">Administrateurs</span>
                </a>
            </div>
            
            <!-- Statistiques rapides -->
            <div class="mt-8 px-4">
                <h3 class="text-xs uppercase text-gray-400 font-semibold mb-3">Aperçu rapide</h3>
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-300">Dépôts totaux</span>
                        <span class="font-semibold text-green-400"><?= number_format($stats['total_deposits'], 0) ?> FCFA</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-300">Retraits totaux</span>
                        <span class="font-semibold text-blue-400"><?= number_format($stats['total_withdrawals'], 0) ?> FCFA</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer sidebar -->
        <div class="p-4 border-t border-gray-800">
            <a href="logout.php" 
               class="flex items-center justify-center px-4 py-3 bg-gradient-to-r from-red-500 to-pink-600 rounded-xl text-white font-medium hover:shadow-lg transition-all duration-300">
                <i class="fas fa-sign-out-alt mr-2"></i>
                Déconnexion
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content lg:ml-72 min-h-screen transition-all duration-300">
        <!-- Top Bar -->
        <div class="bg-white border-b border-gray-200 px-4 lg:px-8 py-4 lg:py-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold gradient-text">
                        <?php
                        $page_titles = [
                            'dashboard' => 'Tableau de Bord',
                            'users' => 'Gestion des Utilisateurs',
                            'plans' => 'Plans d\'Investissement',
                            'transactions' => 'Transactions',
                            'investments' => 'Investissements Actifs',
                            'withdrawals' => 'Gestion des Retraits',
                            'kyc' => 'Vérification KYC',
                            'validate_roi' => 'Validation ROI Journaliers',
                            'videos' => 'Vidéos Publicitaires',
                            'settings' => 'Paramètres de la Plateforme',
                            'admins' => 'Gestion des Administrateurs',
                            'error_logs' => 'Logs d\'Erreurs PHP',
                            'diagnostic' => 'Diagnostic des Données'
                        ];
                        $current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
                        echo $page_titles[$current_page] ?? 'Tableau de Bord';
                        ?>
                    </h1>
                    <p class="text-gray-600 mt-2">
                        <i class="far fa-calendar-alt mr-2"></i>
                        <?= date('l, d F Y') ?>
                    </p>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Search -->
                    <div class="relative hidden lg:block">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" 
                               placeholder="Rechercher..." 
                               class="pl-10 pr-4 py-2 w-64 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <!-- Notifications -->
                    <div class="relative">
                        <button id="notificationsBtn" class="relative p-2 text-gray-600 hover:text-blue-600 transition-colors">
                            <i class="fas fa-bell text-xl"></i>
                            <?php if($stats['pending_withdrawals'] > 0): ?>
                                <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
                                    <?= $stats['pending_withdrawals'] ?>
                                </span>
                            <?php endif; ?>
                        </button>
                    </div>
                    
                    <!-- User Menu -->
                    <div class="relative">
                        <button id="userMenuBtn" class="flex items-center space-x-3 p-2 hover:bg-gray-100 rounded-xl transition-colors">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="hidden lg:block text-left">
                                <p class="text-sm font-semibold"><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></p>
                                <p class="text-xs text-gray-500">Super Admin</p>
                            </div>
                            <i class="fas fa-chevron-down text-gray-400 hidden lg:block"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div id="userDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-50">
                            <a href="#" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user-circle mr-3"></i>
                                Mon Profil
                            </a>
                            <a href="?page=settings" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-cog mr-3"></i>
                                Paramètres
                            </a>
                            <div class="border-t border-gray-200 my-1"></div>
                            <a href="logout.php" class="flex items-center px-4 py-2 text-red-600 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt mr-3"></i>
                                Déconnexion
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
        // Afficher les erreurs récentes si en développement
        if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
            $log_file = __DIR__ . '/php_errors.log';
            if (file_exists($log_file)) {
                $log_content = file_get_contents($log_file);
                $log_lines = array_filter(explode("\n", $log_content));
                $recent_errors = array_slice(array_reverse($log_lines), 0, 3);
                
                if (!empty($recent_errors)) {
                    echo '<div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mx-4 lg:mx-8 mt-4 rounded-lg">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-sm font-medium text-yellow-800">
                                    Erreurs PHP récentes détectées
                                </h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <ul class="list-disc pl-5 space-y-1">';
                    
                    foreach ($recent_errors as $error) {
                        $error_text = htmlspecialchars(substr($error, 0, 150));
                        echo "<li class='font-mono text-xs'>{$error_text}...</li>";
                    }
                    
                    echo '</ul>
                                </div>
                                <div class="mt-3">
                                    <a href="admin.php?page=error_logs" class="text-sm font-medium text-yellow-800 hover:text-yellow-900">
                                        Voir tous les logs <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                </div>
                            </div>
                            <button onclick="this.parentElement.parentElement.remove()" class="ml-3 flex-shrink-0">
                                <i class="fas fa-times text-yellow-400 hover:text-yellow-600"></i>
                            </button>
                        </div>
                    </div>';
                }
            }
        }
        ?>

        <!-- Content Area -->
        <div class="p-4 lg:p-6 fade-in">
            <?php
            // Inclure la page demandée
            $page = $current_page;
            $allowed_pages = ['dashboard', 'users', 'plans', 'transactions', 'investments', 'withdrawals', 'kyc', 'validate_roi', 'videos', 'settings', 'admins', 'error_logs', 'diagnostic'];
            
            if(in_array($page, $allowed_pages)) {
                $file_path = "pages/{$page}.php";
                if(file_exists($file_path)) {
                    include_once $file_path;
                } else {
                    echo '<div class="bg-white rounded-2xl shadow p-8 text-center">
                        <div class="w-20 h-20 mx-auto bg-blue-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-exclamation-triangle text-blue-500 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Page non trouvée</h3>
                        <p class="text-gray-600 mb-6">La page demandée n\'existe pas encore.</p>
                        <a href="?page=dashboard" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl font-semibold hover:shadow-lg transition-all duration-300">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Retour au Tableau de Bord
                        </a>
                    </div>';
                }
            } else {
                include_once "pages/dashboard.php";
            }
            ?>
        </div>

        <!-- Footer -->
        <div class="mt-auto border-t border-gray-200 px-6 py-4">
            <div class="flex flex-col md:flex-row justify-between items-center text-sm text-gray-600">
                <div class="mb-2 md:mb-0">
                    <i class="fas fa-shield-alt mr-1 text-blue-500"></i>
                    <span class="font-medium">Invest Platform Admin</span>
                    <span class="mx-2">•</span>
                    <span>v2.0.0</span>
                </div>
                <div>
                    <span class="hidden lg:inline">© <?= date('Y') ?> Tous droits réservés.</span>
                    <span class="text-blue-500 font-medium">Dashboard</span>
                    <span class="mx-2">•</span>
                    <span><?= date('H:i') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications Panel (Hidden by default) -->
    <div id="notificationsPanel" class="hidden fixed top-0 right-0 w-80 lg:w-96 h-full bg-white shadow-2xl z-50 border-l border-gray-200">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold">Notifications</h3>
                <button id="closeNotifications" class="p-2 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="space-y-4">
                <!-- Notification items will be loaded here -->
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-bell-slash text-3xl mb-3"></i>
                    <p>Aucune notification</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mobile Menu Toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.getElementById('sidebar');
        const mobileOverlay = document.getElementById('mobileOverlay');
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userDropdown = document.getElementById('userDropdown');
        const notificationsBtn = document.getElementById('notificationsBtn');
        const notificationsPanel = document.getElementById('notificationsPanel');
        const closeNotifications = document.getElementById('closeNotifications');

        // Toggle mobile sidebar
        mobileMenuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            mobileOverlay.classList.toggle('hidden');
            document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : 'auto';
        });

        // Close sidebar when clicking overlay
        mobileOverlay.addEventListener('click', () => {
            sidebar.classList.remove('active');
            mobileOverlay.classList.add('hidden');
            document.body.style.overflow = 'auto';
        });

        // Close sidebar when clicking a link (mobile)
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', () => {
                if (window.innerWidth < 1024) {
                    sidebar.classList.remove('active');
                    mobileOverlay.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }
            });
        });

        // User dropdown toggle
        userMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.classList.add('hidden');
            }
        });

        // Notifications panel
        notificationsBtn.addEventListener('click', () => {
            notificationsPanel.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });

        closeNotifications.addEventListener('click', () => {
            notificationsPanel.classList.add('hidden');
            document.body.style.overflow = 'auto';
        });

        // Close notifications when clicking outside
        document.addEventListener('click', (e) => {
            if (!notificationsBtn.contains(e.target) && !notificationsPanel.contains(e.target)) {
                notificationsPanel.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        });

        // Close sidebar on window resize (if mobile)
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('active');
                mobileOverlay.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        });

        // Add active state to clicked nav items
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Confirmation for logout
        document.querySelectorAll('a[href="logout.php"]').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                    e.preventDefault();
                }
            });
        });

        // Add hover effect to cards
        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.stat-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.transform = 'translateY(-5px)';
                });
                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'translateY(0)';
                });
            });
        });

        // Auto-hide notifications after 5 seconds
        setTimeout(() => {
            notificationsPanel.classList.add('hidden');
        }, 5000);
    </script>
</body>
</html>