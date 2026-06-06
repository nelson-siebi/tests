<?php
require_once 'config/db.php';



$db = Database::getInstance()->getConnection();

// ID de l'utilisateur (ex: via session, token, ou GET)
$user_id = $_SESSION['user_id'];

$sql = "SELECT id, nom, prenom, email, phone, referral_code, referred_by, statut, created_at 
        FROM users 
        WHERE id = ? 
        LIMIT 1";

$stmt = $db->prepare($sql);
$stmt->execute([$user_id]);

$user = $stmt->fetch(); // retourne un tableau ou false


?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FreeCash - <?php echo $page_title; ?></title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                        secondary: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            200: '#fde68a',
                            300: '#fcd34d',
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309',
                            800: '#92400e',
                            900: '#78350f',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --primary-color: #0284c7;
            --primary-light: #38bdf8;
            --primary-dark: #0369a1;
            --secondary-color: #f59e0b;
        }

        body {
            font-family: 'Poppins', sans-serif;
            padding-bottom: 80px;
            /* Espace pour la bottom nav mobile */
        }

        @media (min-width: 768px) {
            body {
                padding-bottom: 0;
                /* Pas de padding sur desktop */
            }
        }

        /* Animation pour les transitions de page */
        .page-transition {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>



    <!-- includes/header.php -->
    <header
        class="fixed w-full top-0 left-0 z-50 transition-all duration-300 bg-white/80 backdrop-blur-md border-b border-white/20 shadow-sm">
        <!-- Version Mobile (visible sur mobile/tablette) -->
        <div class="md:hidden">
            <div class="flex items-center justify-between px-4 py-3">
                <!-- Logo avec icône -->
                <a href="home" class="flex items-center space-x-2 group">
                    <div
                        class="h-10 w-10 flex items-center justify-center bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl shadow-lg shadow-primary-500/20 group-hover:scale-105 transition-transform duration-300">
                        <img src="assets/img/logo.png" alt="FreeCash Logo"
                            class="h-8 object-contain brightness-0 invert">
                    </div>
                    <div class="flex flex-col">
                        <span
                            class="text-lg font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent leading-none">
                            FreeCash
                        </span>
                        <span class="text-[10px] text-primary-600 font-medium uppercase tracking-wider mt-0.5">Premium
                            Finance</span>
                    </div>
                </a>

                <!-- Actions rapides et menu -->
                <div class="flex items-center space-x-3">
                    <!-- Notification badge -->
                    <a href="notifications"
                        class="relative w-10 h-10 flex items-center justify-center rounded-xl bg-gray-50 text-gray-600 hover:text-primary-600 hover:bg-primary-50 transition-all duration-300">
                        <i class="fas fa-bell text-lg"></i>
                        <span
                            class="absolute top-2 right-2 w-4 h-4 bg-red-500 text-white text-[10px] rounded-full flex items-center justify-center border-2 border-white animate-pulse">
                            <?php echo $_SESSION['notification_count'] ?? 0; ?>
                        </span>
                    </a>

                    <!-- Menu burger amélioré -->
                    <button id="mobileMenuBtn" class="w-10 h-10 flex flex-col items-center justify-center rounded-xl 
                                               bg-gray-900 text-white hover:bg-gray-800 transition-all duration-300 
                                               focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2
                                               group relative overflow-hidden">
                        <span
                            class="block w-5 h-0.5 bg-current transition-all duration-300 group-active:rotate-45 group-active:translate-y-1.5 mb-1"></span>
                        <span
                            class="block w-3 h-0.5 bg-current transition-all duration-300 group-active:opacity-0 mb-1 ml-auto"></span>
                        <span
                            class="block w-5 h-0.5 bg-current transition-all duration-300 group-active:-rotate-45 group-active:-translate-y-1.5"></span>
                    </button>
                </div>
            </div>

            <!-- Menu mobile amélioré (Contenu existant) -->
            <nav id="mobileMenu" class="hidden bg-white shadow-xl pb-4 border-t border-gray-200 animate-slideDown">
                <!-- En-tête du menu -->
                <div class="px-4 py-3 border-b border-gray-100">
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-12 h-12 bg-gradient-to-r from-green-500 to-green-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-gray-800">
                                <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur'); ?></p>
                            <p class="text-sm text-gray-500">Solde:
                                <?php echo number_format($_SESSION['balance'] ?? 0, 0, ',', ' '); ?> FCFA</p>
                        </div>
                    </div>
                </div>

                <!-- Navigation principale -->
                <div class="py-2">
                    <a href="home" class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors group">
                        <div
                            class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-blue-200 transition-colors">
                            <i class="fas fa-home text-blue-600"></i>
                        </div>
                        <span class="font-medium text-gray-700">Accueil</span>
                        <i class="fas fa-chevron-right text-gray-400 ml-auto text-sm"></i>
                    </a>

                    <a href="investissement"
                        class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors group">
                        <div
                            class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-green-200 transition-colors">
                            <i class="fas fa-chart-line text-green-600"></i>
                        </div>
                        <span class="font-medium text-gray-700">Investissements</span>
                        <span class="ml-auto px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                            <?php echo $_SESSION['active_investments'] ?? 0; ?> actifs
                        </span>
                    </a>

                    <a href="videos" class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors group">
                        <div
                            class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-purple-200 transition-colors">
                            <i class="fas fa-video text-purple-600"></i>
                        </div>
                        <span class="font-medium text-gray-700">Vidéos rémunérées</span>
                        <span class="ml-auto px-2 py-1 bg-purple-100 text-purple-700 text-xs font-medium rounded-full">
                            +<?php echo $_SESSION['daily_videos'] ?? 0; ?> vidéos
                        </span>
                    </a>

                    <a href="parainage" class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors group">
                        <div
                            class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-yellow-200 transition-colors">
                            <i class="fas fa-users text-yellow-600"></i>
                        </div>
                        <span class="font-medium text-gray-700">Parrainage</span>
                        <span class="ml-auto px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-medium rounded-full">
                            <?php echo $_SESSION['referrals_count'] ?? 0; ?> filleuls
                        </span>
                    </a>
                </div>

                <!-- Navigation secondaire -->
                <div class="py-2 border-t border-gray-100">
                    <a href="profile" class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors group">
                        <div
                            class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-indigo-200 transition-colors">
                            <i class="fas fa-user-circle text-indigo-600"></i>
                        </div>
                        <span class="font-medium text-gray-700">Mon profil</span>
                    </a>

                    <a href="home" class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors group">
                        <div
                            class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-green-200 transition-colors">
                            <i class="fas fa-wallet text-green-600"></i>
                        </div>
                        <span class="font-medium text-gray-700">Portefeuille</span>
                        <i class="fas fa-chevron-right text-gray-400 ml-auto text-sm"></i>
                    </a>

                    <a href="transactions" class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors group">
                        <div
                            class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-blue-200 transition-colors">
                            <i class="fas fa-exchange-alt text-blue-600"></i>
                        </div>
                        <span class="font-medium text-gray-700">Transactions</span>
                    </a>

                    <a href="settings" class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors group">
                        <div
                            class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-gray-200 transition-colors">
                            <i class="fas fa-cog text-gray-600"></i>
                        </div>
                        <span class="font-medium text-gray-700">Paramètres</span>
                    </a>

                    <a href="support" class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors group">
                        <div
                            class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-red-200 transition-colors">
                            <i class="fas fa-headset text-red-600"></i>
                        </div>
                        <span class="font-medium text-gray-700">Support</span>
                    </a>
                </div>

                <!-- Actions rapides -->
                <div class="py-9 border-t border-gray-100">
                    <div class="grid grid-cols-3 gap-2 px-2">
                        <a href="investissement"
                            class="flex flex-col items-center p-3 rounded-lg bg-gradient-to-br from-green-500 to-green-600 text-white hover:from-green-600 hover:to-green-700 transition-all transform hover:-translate-y-0.5">
                            <i class="fas fa-plus text-lg mb-1"></i>
                            <span class="text-xs font-medium">Investir</span>
                        </a>

                        <a href="retrais"
                            class="flex flex-col items-center p-3 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 text-white hover:from-blue-600 hover:to-blue-700 transition-all transform hover:-translate-y-0.5">
                            <i class="fas fa-download text-lg mb-1"></i>
                            <span class="text-xs font-medium">Retrait</span>
                        </a>

                        <a href="videos"
                            class="flex flex-col items-center p-3 rounded-lg bg-gradient-to-br from-purple-500 to-purple-600 text-white hover:from-purple-600 hover:to-purple-700 transition-all transform hover:-translate-y-0.5">
                            <i class="fas fa-play-circle text-lg mb-1"></i>
                            <span class="text-xs font-medium">Vidéos</span>
                        </a>
                    </div>
                </div>

                <!-- Déconnexion -->
                <div class="pt-2 py-4 border-t border-gray-100">
                    <a href="logout"
                        class="flex items-center justify-center px-4 py-3 bg-red-50 hover:bg-red-100 transition-colors group">
                        <div
                            class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-red-200 transition-colors">
                            <i class="fas fa-sign-out-alt text-red-600"></i>
                        </div>
                        <span class="font-medium text-red-600">Déconnexion</span>
                    </a>
                </div>
            </nav>
        </div>

        <!-- Version Desktop (visible sur desktop) -->
        <div class="hidden md:block">
            <div class="container mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <!-- Logo et navigation principale -->
                    <div class="flex items-center space-x-8">
                        <!-- Logo -->
                        <a href="index" class="flex items-center space-x-2 group">
                            <div
                                class="h-12 w-auto flex items-center justify-center group-hover:scale-105 transition-transform">
                                <img src="assets/img/logo.png" alt="FreeCash Logo" class="h-12 object-contain">
                            </div>
                            <span
                                class="text-2xl font-bold bg-gradient-to-r from-primary-600 to-primary-400 bg-clip-text text-transparent">
                                FreeCash
                            </span>
                        </a>

                        <!-- Navigation Principale -->
                        <nav class="hidden lg:flex items-center space-x-1">
                            <a href="home"
                                class="flex items-center px-4 py-2 rounded-lg transition-all duration-200 <?php echo $page == 'home' || $page == 'index' ? 'bg-green-50 text-green-600 font-semibold' : 'text-gray-600 hover:text-green-600 hover:bg-gray-50'; ?>">
                                <i class="fas fa-home mr-2 text-lg"></i>
                                <span>Accueil</span>
                            </a>

                            <!-- Menu déroulant Investissements -->
                            <div class="relative group">
                                <button
                                    class="flex items-center px-4 py-2 rounded-lg transition-all duration-200 text-gray-600 hover:text-green-600 hover:bg-gray-50">
                                    <i class="fas fa-chart-line mr-2 text-lg"></i>
                                    <span>Investissements</span>
                                    <i class="fas fa-chevron-down ml-2 text-xs"></i>
                                </button>
                                <div
                                    class="absolute left-0 mt-2 w-56 bg-white rounded-lg shadow-xl py-2 hidden group-hover:block border border-gray-100 z-50">
                                    <a href="investissement"
                                        class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors">
                                        <div
                                            class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-chart-bar text-green-600 text-sm"></i>
                                        </div>
                                        <div class="flex-1">
                                            <span class="font-medium text-gray-700">Mes Investissements</span>
                                            <span
                                                class="block text-xs text-gray-500"><?php echo $_SESSION['active_investments'] ?? 0; ?>
                                                actifs</span>
                                        </div>
                                    </a>
                                    <a href="investissement"
                                        class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors">
                                        <div
                                            class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-plus-circle text-blue-600 text-sm"></i>
                                        </div>
                                        <span class="font-medium text-gray-700">Nouvel investissement</span>
                                    </a>
                                    <a href="investissement"
                                        class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors">
                                        <div
                                            class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-history text-purple-600 text-sm"></i>
                                        </div>
                                        <span class="font-medium text-gray-700">Historique</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Menu déroulant Parrainage -->
                            <div class="relative group">
                                <button
                                    class="flex items-center px-4 py-2 rounded-lg transition-all duration-200 text-gray-600 hover:text-green-600 hover:bg-gray-50">
                                    <i class="fas fa-users mr-2 text-lg"></i>
                                    <span>Parrainage</span>
                                    <i class="fas fa-chevron-down ml-2 text-xs"></i>
                                </button>
                                <div
                                    class="absolute left-0 mt-2 w-56 bg-white rounded-lg shadow-xl py-2 hidden group-hover:block border border-gray-100 z-50">
                                    <a href="parainage"
                                        class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors">
                                        <div
                                            class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-user-plus text-yellow-600 text-sm"></i>
                                        </div>
                                        <div class="flex-1">
                                            <span class="font-medium text-gray-700">Mon équipe</span>
                                            <span
                                                class="block text-xs text-gray-500"><?php echo $_SESSION['referrals_count'] ?? 0; ?>
                                                filleuls</span>
                                        </div>
                                    </a>
                                    <a href="parainage"
                                        class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors">
                                        <div
                                            class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-coins text-green-600 text-sm"></i>
                                        </div>
                                        <span class="font-medium text-gray-700">Gains parrainage</span>
                                    </a>
                                    <a href="parainage"
                                        class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors">
                                        <div
                                            class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-share-alt text-blue-600 text-sm"></i>
                                        </div>
                                        <span class="font-medium text-gray-700">Partager mon lien</span>
                                    </a>
                                </div>
                            </div>

                            <a href="videos"
                                class="flex items-center px-4 py-2 rounded-lg transition-all duration-200 <?php echo $page == 'videos' ? 'bg-green-50 text-green-600 font-semibold' : 'text-gray-600 hover:text-green-600 hover:bg-gray-50'; ?>">
                                <i class="fas fa-video mr-2 text-lg"></i>
                                <span>Vidéos</span>
                                <span
                                    class="ml-2 px-2 py-0.5 bg-purple-100 text-purple-700 text-xs font-medium rounded-full">
                                    +<?php echo $_SESSION['daily_videos'] ?? 0; ?>
                                </span>
                            </a>
                        </nav>
                    </div>

                    <!-- Côté droit avec infos utilisateur et actions -->
                    <div class="flex items-center space-x-6">
                        <!-- Actions rapides desktop -->
                        <div class="hidden lg:flex items-center space-x-3">
                            <a href="investissement"
                                class="px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:from-green-600 hover:to-green-700 transition-all flex items-center shadow-md hover:shadow-lg">
                                <i class="fas fa-plus mr-2"></i>
                                <span class="font-medium">Investir</span>
                            </a>
                            <a href="retrais"
                                class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all flex items-center shadow-md hover:shadow-lg">
                                <i class="fas fa-download mr-2"></i>
                                <span class="font-medium">Retrait</span>
                            </a>
                        </div>

                        <!-- Notifications -->
                        <div class="relative">
                            <a href="notifications"
                                class="relative flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 transition-colors">
                                <i class="fas fa-bell text-gray-600 text-lg"></i>
                                <span
                                    class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center animate-pulse">
                                    <?php echo $_SESSION['notification_count'] ?? 0; ?>
                                </span>
                            </a>
                        </div>

                        <!-- Portefeuille -->
                        <!-- <div class="hidden lg:block px-4 py-2 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200">
                        <div class="flex items-center">
                            <i class="fas fa-wallet text-green-600 mr-2"></i>
                            <div>
                                <p class="text-xs text-gray-500">Solde</p>
                                <p class="font-bold text-gray-800"><?php echo number_format($_SESSION['balance'] ?? 0, 0, ',', ' '); ?> FCFA</p>
                            </div>
                        </div>
                    </div> -->

                        <!-- Menu utilisateur -->
                        <div class="relative group">
                            <button class="flex items-center space-x-3 focus:outline-none">
                                <div
                                    class="w-10 h-10 bg-gradient-to-r from-green-500 to-green-600 rounded-full flex items-center justify-center text-white font-semibold shadow">
                                    <?php
                                    $initials = isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'U';
                                    echo $initials;
                                    ?>
                                </div>
                                <div class="hidden lg:block text-left">
                                    <p class="font-medium text-gray-800 text-sm">
                                        <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur'); ?></p>
                                    <p class="text-xs text-gray-500">Membre
                                        <?php echo $_SESSION['user_level'] ?? 'Standard'; ?></p>
                                </div>
                                <i class="fas fa-chevron-down text-gray-500 text-sm hidden lg:block"></i>
                            </button>

                            <!-- Menu déroulant utilisateur -->
                            <div
                                class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl py-2 hidden group-hover:block border border-gray-100 z-50">
                                <!-- En-tête profil -->
                                <div class="px-4 py-3 border-b border-gray-100">
                                    <div class="flex items-center space-x-3">
                                        <div
                                            class="w-10 h-10 bg-gradient-to-r from-green-500 to-green-600 rounded-full flex items-center justify-center text-white font-semibold">
                                            <?php echo $initials; ?>
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-800 text-sm">
                                                <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur'); ?>
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                <?php echo $_SESSION['user_email'] ?? ''; ?></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Liens profil -->
                                <a href="profile"
                                    class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors group">
                                    <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-user-circle text-indigo-600 text-sm"></i>
                                    </div>
                                    <span class="font-medium text-gray-700">Mon profil</span>
                                </a>

                                <a href="home"
                                    class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors group">
                                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-wallet text-green-600 text-sm"></i>
                                    </div>
                                    <span class="font-medium text-gray-700">Portefeuille</span>
                                </a>

                                <a href="transactions"
                                    class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors group">
                                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-exchange-alt text-blue-600 text-sm"></i>
                                    </div>
                                    <span class="font-medium text-gray-700">Transactions</span>
                                </a>

                                <!-- Séparateur -->
                                <div class="border-t my-2"></div>

                                <!-- Paramètres -->
                                <a href="settings"
                                    class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors group">
                                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-cog text-gray-600 text-sm"></i>
                                    </div>
                                    <span class="font-medium text-gray-700">Paramètres</span>
                                </a>

                                <a href="support"
                                    class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors group">
                                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-headset text-red-600 text-sm"></i>
                                    </div>
                                    <span class="font-medium text-gray-700">Support</span>
                                </a>

                                <!-- Déconnexion -->
                                <div class="border-t mt-2">
                                    <a href="logout"
                                        class="flex items-center px-4 py-3 hover:bg-red-50 transition-colors group">
                                        <div
                                            class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-sign-out-alt text-red-600 text-sm"></i>
                                        </div>
                                        <span class="font-medium text-red-600">Déconnexion</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Overlay pour le menu mobile -->
    <div id="mobileMenuOverlay"
        class="md:hidden hidden fixed inset-0 bg-black bg-opacity-50 z-40 backdrop-blur-sm animate-fadeIn"></div>

    <!-- Ajouter un padding top pour que le contenu ne soit pas caché sous le header -->
    <div class="h-16 md:h-20"></div>

    <!-- Styles et Scripts (conservés de la version mobile) -->
    <style>
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .animate-slideDown {
            animation: slideDown 0.3s ease-out;
        }

        .animate-fadeIn {
            animation: fadeIn 0.3s ease-out;
        }

        /* Amélioration du scroll dans le menu */
        #mobileMenu {
            max-height: calc(100vh - 64px);
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #d1d5db #f3f4f6;
        }

        #mobileMenu::-webkit-scrollbar {
            width: 4px;
        }

        #mobileMenu::-webkit-scrollbar-track {
            background: #f3f4f6;
            border-radius: 2px;
        }

        #mobileMenu::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 2px;
        }

        #mobileMenu::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        /* Styles pour les menus déroulants desktop */
        .relative.group .absolute {
            display: none;
        }

        .relative.group:hover .absolute {
            display: block;
        }

        /* Transition smooth pour les dropdowns */
        .relative.group .absolute {
            animation: slideDown 0.2s ease-out;
        }
    </style>

    <script>
        // Scripts pour le menu mobile (conservés de la version originale)
        const mobileBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileOverlay = document.getElementById('mobileMenuOverlay');
        const body = document.body;

        // Fonction pour ouvrir/fermer le menu mobile
        function toggleMobileMenu() {
            const isOpen = mobileMenu.classList.contains('hidden');

            if (isOpen) {
                mobileMenu.classList.remove('hidden');
                mobileOverlay.classList.remove('hidden');
                body.style.overflow = 'hidden';
                mobileBtn.classList.add('active');

                const firstLink = mobileMenu.querySelector('a');
                if (firstLink) firstLink.focus();
            } else {
                mobileMenu.classList.add('hidden');
                mobileOverlay.classList.add('hidden');
                body.style.overflow = 'auto';
                mobileBtn.classList.remove('active');
            }
        }

        // Gestionnaires d'événements pour mobile
        if (mobileBtn) {
            mobileBtn.addEventListener('click', toggleMobileMenu);
        }
        if (mobileOverlay) {
            mobileOverlay.addEventListener('click', toggleMobileMenu);
        }

        if (mobileMenu) {
            mobileMenu.addEventListener('click', (e) => {
                if (e.target.tagName === 'A' && !e.target.closest('[href*="logout"]')) {
                    setTimeout(() => {
                        toggleMobileMenu();
                    }, 200);
                }
            });
        }

        // Fermer le menu avec la touche Échap
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && mobileMenu && !mobileMenu.classList.contains('hidden')) {
                toggleMobileMenu();
            }
        });

        // Gestion du scroll pour cacher/montrer le header
        let lastScrollTop = 0;
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset || document.documentElement.scrollTop;
            const header = document.querySelector('header');

            if (currentScroll > lastScrollTop && currentScroll > 100) {
                header.style.transform = 'translateY(-100%)';
                header.style.transition = 'transform 0.3s ease-out';
            } else {
                header.style.transform = 'translateY(0)';
            }

            lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
        });

        // Fonction pour mettre à jour les données
        function updateHeaderData() {
            // Simulation de mise à jour des notifications
            const notificationBadges = document.querySelectorAll('.relative a .absolute, .relative .absolute');
            notificationBadges.forEach(badge => {
                if (badge && Math.random() > 0.7) {
                    const newCount = Math.floor(Math.random() * 5) + 1;
                    badge.textContent = newCount;
                    badge.classList.add('animate-pulse');

                    setTimeout(() => {
                        badge.classList.remove('animate-pulse');
                    }, 1000);
                }
            });
        }

        // Mettre à jour toutes les 30 secondes
        setInterval(updateHeaderData, 30000);
    </script>