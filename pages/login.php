<?php
// login.php - Page de connexion professionnelle

// Initialiser la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: home');
    exit();
}

// Configuration
$site_name = "FreeCash";
$site_url = "https://investian.com";
$support_email = "junior1009f@gmail.com";

// Inclure la configuration de la base de données
require_once 'config/db.php';

// Initialiser les variables
$error = '';
$success = '';
$email = '';
$remember = false;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et nettoyer les données
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    $csrf_token = $_POST['csrf_token'] ?? '';
    
$pdo = Database::getInstance()->getConnection();
    // Vérifier le token CSRF
    if (!isset($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
        $error = "Token de sécurité invalide. Veuillez rafraîchir la page.";
    } else {
        // Validation des champs
        if (empty($email) || empty($password)) {
            $error = "Veuillez remplir tous les champs.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Adresse email invalide.";
        } else {
            try {
                // Préparer la requête
                $stmt = $pdo->prepare("
                    SELECT u.*, w.solde_investissement, w.solde_publicite, w.solde_parrainage 
                    FROM users u 
                    LEFT JOIN wallets w ON u.id = w.user_id 
                    WHERE u.email = ? AND u.statut IN ('active', 'pending')
                ");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    // Vérifier le statut du compte
                    if ($user['statut'] === 'banned') {
                        $error = "Votre compte a été suspendu. Contactez le support.";
                    } elseif ($user['statut'] === 'pending') {
                        $error = "Votre compte est en attente de validation. Vérifiez vos emails.";
                    } else {
                        // Vérifier le mot de passe
                        if (password_verify($password, $user['password'])) {
                            // Connexion réussie
                            
                            // Mettre à jour la dernière connexion
                            $update_stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                            $update_stmt->execute([$user['id']]);
                            
                            // Créer la session
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['user_email'] = $user['email'];
                            $_SESSION['user_nom'] = $user['nom'];
                            $_SESSION['user_prenom'] = $user['prenom'];
                            $_SESSION['user_phone'] = $user['phone'];
                            $_SESSION['user_referral_code'] = $user['referral_code'];
                            $_SESSION['user_created_at'] = $user['created_at'];
                            $_SESSION['login_time'] = time();
                            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
                            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
                            
                            // Stocker les soldes
                            if ($user['solde_investissement'] !== null) {
                                $_SESSION['solde_investissement'] = $user['solde_investissement'];
                                $_SESSION['solde_publicite'] = $user['solde_publicite'];
                                $_SESSION['solde_parrainage'] = $user['solde_parrainage'];
                            }
                            
                            // Gestion du "Se souvenir de moi"
                            if ($remember) {
                                // Générer des tokens sécurisés
                                $selector = bin2hex(random_bytes(16));
                                $validator = bin2hex(random_bytes(32));
                                $hashed_validator = hash('sha256', $validator);
                                $expires = time() + (30 * 24 * 60 * 60); // 30 jours
                                
                                // Stocker en base de données
                                $stmt = $pdo->prepare("
                                    INSERT INTO user_sessions (user_id, selector, hashed_validator, expires_at, created_at) 
                                    VALUES (?, ?, ?, FROM_UNIXTIME(?), NOW())
                                ");
                                $stmt->execute([$user['id'], $selector, $hashed_validator, $expires]);
                                
                                // Créer le cookie sécurisé
                                $cookie_value = $selector . ':' . $validator;
                                setcookie('remember_me', $cookie_value, [
                                    'expires' => $expires,
                                    'path' => '/',
                                    'domain' => '',
                                    'secure' => true, // HTTPS en production
                                    'httponly' => true,
                                    'samesite' => 'Strict'
                                ]);
                            }
                            
                            // Réinitialiser les tentatives de connexion
                            if (isset($_SESSION['login_attempts'])) {
                                unset($_SESSION['login_attempts']);
                            }
                            
                            // Rediriger vers la page d'accueil
                            header('Location: home');
                            exit();
                            
                        } else {
                            // Mot de passe incorrect
                            $error = "Identifiants incorrects.";
                            
                            // Suivi des tentatives de connexion
                            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                            
                            // Bloquer après 5 tentatives
                            if ($_SESSION['login_attempts'] >= 5) {
                                $error = "Trop de tentatives de connexion. Veuillez réessayer dans 15 minutes.";
                                $_SESSION['lockout_time'] = time();
                            }
                        }
                    }
                } else {
                    // Utilisateur non trouvé
                    $error = "Identifiants incorrects.";
                    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                }
                
            } catch (PDOException $e) {
                // Log l'erreur (ne pas afficher aux utilisateurs)
                error_log("Login error: " . $e->getMessage());
                $error = "Une erreur est survenue. Veuillez réessayer.". $e->getMessage();
            }
        }
    }
}

// Générer un token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Vérifier si l'utilisateur est en lockout
if (isset($_SESSION['lockout_time']) && (time() - $_SESSION['lockout_time']) < 900) {
    $remaining_time = 900 - (time() - $_SESSION['lockout_time']);
    $error = "Trop de tentatives de connexion. Veuillez réessayer dans " . ceil($remaining_time / 60) . " minutes.";
    $lockout = true;
} else {
    $lockout = false;
    // Nettoyer le lockout si le temps est écoulé
    if (isset($_SESSION['lockout_time']) && (time() - $_SESSION['lockout_time']) >= 900) {
        unset($_SESSION['lockout_time']);
        unset($_SESSION['login_attempts']);
    }
}

// Vérifier le cookie "Se souvenir de moi"
if (!$lockout && !isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    $cookie_parts = explode(':', $_COOKIE['remember_me']);
    if (count($cookie_parts) === 2) {
        list($selector, $validator) = $cookie_parts;
        
        try {
            // Vérifier le token
            $stmt = $pdo->prepare("
                SELECT us.*, u.* 
                FROM user_sessions us 
                JOIN users u ON us.user_id = u.id 
                WHERE us.selector = ? AND us.expires_at > NOW() AND u.statut = 'active'
            ");
            $stmt->execute([$selector]);
            $session = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($session && hash_equals($session['hashed_validator'], hash('sha256', $validator))) {
                // Connexion automatique
                $_SESSION['user_id'] = $session['user_id'];
                $_SESSION['user_email'] = $session['email'];
                $_SESSION['user_name'] = $session['nom'];
                $_SESSION['user_prenom'] = $session['prenom'];
                $_SESSION['user_referral_code'] = $session['referral_code'];
                $_SESSION['login_time'] = time();
                
                header('Location: home');
                exit();
            } else {
                // Cookie invalide, le supprimer
                setcookie('remember_me', '', time() - 3600, '/');
            }
        } catch (Exception $e) {
            error_log("Remember me error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="Connectez-vous à votre compte <?php echo $site_name; ?> - Plateforme d'investissement sécurisée">
    <meta name="keywords" content="investissement, finance, trading, crypto, <?php echo $site_name; ?>">
    <meta name="author" content="<?php echo $site_name; ?>">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="Connexion | <?php echo $site_name; ?>">
    <meta property="og:description" content="Accédez à votre compte d'investissement <?php echo $site_name; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $site_url; ?>/login">
    <meta property="og:image" content="<?php echo $site_url; ?>/assets/og-image.jpg">
    
    <title>Connexion | <?php echo $site_name; ?> - Investissements Intelligents</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/favicon-16x16.png">
    <link rel="manifest" href="assets/site.webmanifest">
    <meta name="theme-color" content="#2e8b57">
    
    <!-- Preload CSS -->
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style" crossorigin="anonymous">
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" as="style" crossorigin="anonymous">
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Inline Critical CSS -->
    <style>
        :root {
            --primary: #0284c7;
            --primary-light: #38bdf8;
            --primary-dark: #0369a1;
            --secondary: #eab308;
            --danger: #dc2626;
            --success: #16a34a;
            --warning: #f59e0b;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html {
            font-size: 16px;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #f6f9fc 0%, #edf2f7 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            line-height: 1.5;
            color: var(--gray-700);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* Dark mode */
        @media (prefers-color-scheme: dark) {
            body {
                background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                color: #cbd5e1;
            }
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        /* Utility classes */
        .animate-fadeIn {
            animation: fadeIn 0.5s ease-out;
        }
        
        .animate-slideIn {
            animation: slideIn 0.3s ease-out;
        }
        
        .animate-pulse {
            animation: pulse 2s infinite;
        }
        
        .animate-spin {
            animation: spin 1s linear infinite;
        }
        
        .animate-shake {
            animation: shake 0.5s ease-in-out;
        }
        
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
        
        /* Focus styles */
        :focus-visible {
            outline: 3px solid var(--primary);
            outline-offset: 2px;
        }
        
        /* Mobile optimizations */
        @media (max-width: 640px) {
            html {
                font-size: 15px;
            }
            
            .mobile-hidden {
                display: none;
            }
        }
        
        /* Notch support */
        @supports (padding: max(0px)) {
            body {
                padding-left: max(1rem, env(safe-area-inset-left));
                padding-right: max(1rem, env(safe-area-inset-right));
                padding-top: max(1rem, env(safe-area-inset-top));
                padding-bottom: max(1rem, env(safe-area-inset-bottom));
            }
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Skip to content link for accessibility -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-primary text-white px-4 py-2 rounded-lg z-50">
        Aller au contenu principal
    </a>
    
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center">
            <!-- Left Column - Branding & Features -->
            <div class="mobile-hidden">
                <div class="max-w-lg mx-auto">
                    <!-- Logo -->
                    <div class="mb-10">
                        <div class="flex items-center gap-3">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center shadow-lg">
                                <i class="fas fa-chart-line text-white text-2xl"></i>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                                    <?php echo $site_name; ?>
                                </h1>
                                <p class="text-green-600 dark:text-green-400 font-medium mt-1">
                                    Investissements Intelligents
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hero Section -->
                    <div class="mb-10">
                        <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-6 leading-tight">
                            Gérez vos <span class="text-green-600">investissements</span> en toute simplicité
                        </h2>
                        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
                            Rejoignez des milliers d'investisseurs qui font croître leur capital avec notre plateforme sécurisée.
                        </p>
                        
                        <!-- Stats -->
                        <div class="grid grid-cols-3 gap-4 mb-10">
                            <div class="text-center p-4 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700">
                                <div class="text-2xl font-bold text-green-600">500+</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Investisseurs</div>
                            </div>
                            <div class="text-center p-4 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700">
                                <div class="text-2xl font-bold text-green-600">95%</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Satisfaction</div>
                            </div>
                            <div class="text-center p-4 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700">
                                <div class="text-2xl font-bold text-green-600">24h</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Support</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Features List -->
                    <div class="space-y-4">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                    <i class="fas fa-shield-alt text-green-600 dark:text-green-400"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Sécurité bancaire</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Données cryptées et protégées par les dernières technologies de sécurité.
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                    <i class="fas fa-bolt text-blue-600 dark:text-blue-400"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Retraits instantanés</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Obtenez vos fonds en moins de 24 heures via Orange Money ou MTN.
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                                    <i class="fas fa-headset text-purple-600 dark:text-purple-400"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Support premium</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Notre équipe est disponible 24h/24 et 7j/7 pour vous assister.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Testimonial -->

                </div>
            </div>
            
            <!-- Right Column - Login Form -->
            <div>
                <div class="max-w-md mx-auto">
                    <!-- Login Card -->
                    <div id="main-content" class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl overflow-hidden border border-gray-200 dark:border-slate-700 animate-fadeIn">
                        <!-- Header -->
                        <div class="bg-gradient-to-r from-primary-600 to-primary-800 p-8 text-center relative overflow-hidden">
                            <!-- Decorative elements -->
                            <div class="absolute top-0 left-0 w-32 h-32 bg-white/10 rounded-full -translate-x-16 -translate-y-16"></div>
                            <div class="absolute bottom-0 right-0 w-40 h-40 bg-white/10 rounded-full translate-x-20 translate-y-20"></div>
                            
                            <div class="relative z-10">
                                <div class="w-20 h-20 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center mx-auto mb-6 border border-white/30">
                                    <img src="assets/img/logo.png" alt="FreeCash Logo" class="w-12 h-12 object-contain">
                                </div>
                                <h2 class="text-2xl font-bold text-white mb-2">Bienvenue de retour</h2>
                                <p class="text-primary-100">Connectez-vous à votre compte</p>
                            </div>
                        </div>
                        
                        <!-- Form Content -->
                        <div class="p-8">
                            <?php if ($lockout): ?>
                            <!-- Lockout Message -->
                            <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-ban text-red-600 dark:text-red-400 text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-red-800 dark:text-red-300 mb-1">
                                            Accès temporairement bloqué
                                        </h3>
                                        <p class="text-sm text-red-700 dark:text-red-400">
                                            <?php echo $error; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!$lockout && $error): ?>
                            <!-- Error Message -->
                            <div id="error-message" class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl animate-slideIn">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 mt-0.5">
                                        <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-sm font-semibold text-red-800 dark:text-red-300 mb-1">
                                            Erreur de connexion
                                        </h3>
                                        <p class="text-sm text-red-700 dark:text-red-400">
                                            <?php echo htmlspecialchars($error); ?>
                                        </p>
                                    </div>
                                    <button type="button" onclick="this.parentElement.parentElement.remove()" 
                                            class="flex-shrink-0 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($success): ?>
                            <!-- Success Message -->
                            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-green-800 dark:text-green-300">
                                            <?php echo htmlspecialchars($success); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Attempts Warning -->
                            <?php if (!$lockout && isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] > 0): ?>
                            <div class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-exclamation-triangle text-yellow-600 dark:text-yellow-400"></i>
                                    <span class="text-sm text-yellow-800 dark:text-yellow-300">
                                        Tentative <?php echo $_SESSION['login_attempts']; ?> sur 5
                                    </span>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!$lockout): ?>
                            <!-- Login Form -->
                            <form id="loginForm" method="POST" action="" class="space-y-6" novalidate>
                                <!-- CSRF Token -->
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                
                                <!-- Email Field -->
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        <i class="fas fa-envelope mr-2 text-primary-600"></i>
                                        Adresse email
                                    </label>
                                    <div class="relative">
                                        <input type="email" 
                                               id="email" 
                                               name="email" 
                                               value="<?php echo htmlspecialchars($email); ?>"
                                               class="w-full pl-12 pr-4 py-3.5 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-xl focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-200 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 disabled:opacity-50 disabled:cursor-not-allowed"
                                               placeholder="votre@email.com"
                                               required
                                               autocomplete="email"
                                               spellcheck="false"
                                               <?php if ($lockout) echo 'disabled'; ?>>
                                        <div class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-gray-500">
                                            <i class="fas fa-envelope text-lg"></i>
                                        </div>
                                    </div>
                                    <div id="email-error" class="mt-2 text-sm text-red-600 dark:text-red-400 hidden"></div>
                                </div>
                                
                                <!-- Password Field -->
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            <i class="fas fa-lock mr-2 text-green-600"></i>
                                            Mot de passe
                                        </label>
                                        <a href="#" 
                                           class="text-sm text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300 font-medium transition-colors">
                                            <i class="fas fa-key mr-1"></i>
                                            Mot de passe oublié ?
                                        </a>
                                    </div>
                                    <div class="relative">
                                        <input type="password" 
                                               id="password" 
                                               name="password"
                                               class="w-full pl-12 pr-12 py-3.5 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-xl focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-200 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 disabled:opacity-50 disabled:cursor-not-allowed"
                                               placeholder="••••••••"
                                               required
                                               autocomplete="current-password"
                                               minlength="6"
                                               <?php if ($lockout) echo 'disabled'; ?>>
                                        <div class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-gray-500">
                                            <i class="fas fa-lock text-lg"></i>
                                        </div>
                                        <button type="button" 
                                                onclick="togglePassword('password', this)"
                                                class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors disabled:opacity-50"
                                                aria-label="Afficher/masquer le mot de passe"
                                                <?php if ($lockout) echo 'disabled'; ?>>
                                            <i class="fas fa-eye text-lg"></i>
                                        </button>
                                    </div>
                                    <div id="password-error" class="mt-2 text-sm text-red-600 dark:text-red-400 hidden"></div>
                                </div>
                                
                                <!-- Remember Me & Options -->
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <input id="remember" 
                                               name="remember" 
                                               type="checkbox"
                                               class="w-5 h-5 rounded border-gray-300 dark:border-slate-600 text-primary-600 focus:ring-primary-500 bg-white dark:bg-slate-800 disabled:opacity-50"
                                               <?php if ($remember) echo 'checked'; ?>
                                               <?php if ($lockout) echo 'disabled'; ?>>
                                        <label for="remember" class="ml-3 text-sm text-gray-700 dark:text-gray-300 cursor-pointer select-none">
                                            Se souvenir de moi
                                        </label>
                                    </div>
                                    
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-shield-alt text-green-600 text-sm"></i>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            Connexion sécurisée
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Submit Button -->
                                <button type="submit" 
                                        id="submitBtn"
                                        id="submitBtn"
                                        class="w-full bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 hover:shadow-lg active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-none group"
                                        <?php if ($lockout) echo 'disabled'; ?>>
                                    <span id="submitText">
                                        <i class="fas fa-sign-in-alt mr-2"></i>
                                        Se connecter
                                    </span>
                                    <div id="submitSpinner" class="hidden">
                                        <div class="spinner mx-auto"></div>
                                    </div>
                                </button>
                                
                                <!-- Separator -->
                                <div class="relative">
                                    <div class="absolute inset-0 flex items-center">
                                        <div class="w-full border-t border-gray-300 dark:border-slate-600"></div>
                                    </div>
                                    <div class="relative flex justify-center text-sm">
                                        <span class="px-4 bg-white dark:bg-slate-800 text-gray-500 dark:text-gray-400">
                                            Pas encore de compte ?
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Register Link -->
                                <div class="text-center">
                                    <a href="register" 
                                       class="inline-flex items-center text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium">
                                        <i class="fas fa-user-plus mr-2"></i>
                                        Créer un compte
                                    </a>
                                </div>
                            </form>
                            <?php endif; ?>
                            
                            <!-- Demo Credentials -->
                            <!-- <div class="mt-8 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                                <div class="flex items-center gap-2 mb-3">
                                    <i class="fas fa-vial text-blue-600 dark:text-blue-400"></i>
                                    <h4 class="font-medium text-gray-900 dark:text-white">Compte de démonstration</h4>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="text-sm">
                                        <span class="font-medium text-gray-600 dark:text-gray-400">Email:</span>
                                        <span class="font-mono text-gray-900 dark:text-white ml-2">demo@<?php echo strtolower($site_name); ?>.com</span>
                                    </div>
                                    <div class="text-sm">
                                        <span class="font-medium text-gray-600 dark:text-gray-400">Mot de passe:</span>
                                        <span class="font-mono text-gray-900 dark:text-white ml-2">Demo@123456</span>
                                    </div>
                                </div>
                                <button type="button" 
                                        onclick="fillDemoCredentials()"
                                        class="mt-3 w-full py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <i class="fas fa-magic mr-2"></i>
                                    Remplir automatiquement
                                </button>
                            </div> -->
                            
                            <!-- Security Info -->
                            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-slate-700">
                                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-500 dark:text-gray-400">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-shield-alt"></i>
                                        <span>Connexion sécurisée SSL</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-lock"></i>
                                        <span>Données cryptées</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-user-shield"></i>
                                        <span>Confidentialité garantie</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Back to Home -->
                    <div class="mt-6 text-center">
                        <a href="home" 
                           class="inline-flex items-center text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Retour à l'accueil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    <!-- JavaScript -->
    <script>
    // Déferrer le chargement de Tailwind CSS
    document.addEventListener('DOMContentLoaded', function() {
        const script = document.createElement('script');
        script.src = 'https://cdn.tailwindcss.com';
        script.onload = function() {
            // Configurer Tailwind après chargement
            tailwind.config = {
                darkMode: 'class',
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
                            green: {
                                50: '#f0fdf4',
                                100: '#dcfce7',
                                500: '#22c55e',
                                600: '#16a34a',
                                700: '#15803d',
                            }
                        }
                    }
                }
            };
        };
        document.head.appendChild(script);
    });

    // Fonction pour afficher/masquer le mot de passe
    function togglePassword(inputId, button) {
        const input = document.getElementById(inputId);
        const icon = button.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
            button.setAttribute('aria-label', 'Masquer le mot de passe');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
            button.setAttribute('aria-label', 'Afficher le mot de passe');
        }
    }
    
    // Remplir les identifiants de démo
    function fillDemoCredentials() {
        document.getElementById('email').value = 'demo@<?php echo strtolower($site_name); ?>.com';
        document.getElementById('password').value = 'Demo@123456';
        validateEmail();
        validatePassword();
        showToast('Identifiants de démo chargés !', 'success');
    }
    
    // Afficher une notification toast
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            info: 'bg-blue-500'
        };
        
        toast.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg font-medium z-50 animate-slideIn`;
        toast.innerHTML = `
            <div class="flex items-center gap-3">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('opacity-0', 'transition-opacity', 'duration-300');
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }
    
    // Validation des champs
    function validateEmail() {
        const email = document.getElementById('email');
        const errorDiv = document.getElementById('email-error');
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (!email.value.trim()) {
            errorDiv.textContent = "L'email est requis";
            errorDiv.classList.remove('hidden');
            email.classList.add('border-red-500');
            return false;
        } else if (!emailPattern.test(email.value)) {
            errorDiv.textContent = "Format d'email invalide";
            errorDiv.classList.remove('hidden');
            email.classList.add('border-red-500');
            return false;
        } else {
            errorDiv.classList.add('hidden');
            email.classList.remove('border-red-500');
            email.classList.add('border-green-500');
            return true;
        }
    }
    
    function validatePassword() {
        const password = document.getElementById('password');
        const errorDiv = document.getElementById('password-error');
        
        if (!password.value) {
            errorDiv.textContent = "Le mot de passe est requis";
            errorDiv.classList.remove('hidden');
            password.classList.add('border-red-500');
            return false;
        } else if (password.value.length < 6) {
            errorDiv.textContent = "Minimum 6 caractères";
            errorDiv.classList.remove('hidden');
            password.classList.add('border-red-500');
            return false;
        } else {
            errorDiv.classList.add('hidden');
            password.classList.remove('border-red-500');
            password.classList.add('border-green-500');
            return true;
        }
    }
    
    // Gestion de la soumission du formulaire
    document.getElementById('loginForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const isEmailValid = validateEmail();
        const isPasswordValid = validatePassword();
        
        if (isEmailValid && isPasswordValid) {
            // Afficher le spinner et désactiver le bouton
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitSpinner = document.getElementById('submitSpinner');
            
            submitBtn.disabled = true;
            submitText.classList.add('hidden');
            submitSpinner.classList.remove('hidden');
            
            // Soumettre le formulaire
            setTimeout(() => {
                this.submit();
            }, 500);
        } else {
            // Shake animation sur les champs invalides
            const invalidInputs = this.querySelectorAll('.border-red-500');
            invalidInputs.forEach(input => {
                input.classList.add('animate-shake');
                setTimeout(() => {
                    input.classList.remove('animate-shake');
                }, 500);
            });
            
            // Faire défiler jusqu'à la première erreur
            const firstError = this.querySelector('.border-red-500');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }
    });
    
    // Validation en temps réel
    document.getElementById('email')?.addEventListener('input', validateEmail);
    document.getElementById('email')?.addEventListener('blur', validateEmail);
    document.getElementById('password')?.addEventListener('input', validatePassword);
    document.getElementById('password')?.addEventListener('blur', validatePassword);
    
    // Sauvegarder l'email dans localStorage
    document.getElementById('email')?.addEventListener('blur', function() {
        try {
            if (this.value) {
                localStorage.setItem('last_login_email', this.value);
            }
        } catch (e) {
            // Ignorer les erreurs de localStorage
        }
    });
    
    // Remplir avec le dernier email utilisé
    document.addEventListener('DOMContentLoaded', function() {
        try {
            const lastEmail = localStorage.getItem('last_login_email');
            if (lastEmail && document.getElementById('email') && !document.getElementById('email').value) {
                document.getElementById('email').value = lastEmail;
                validateEmail();
            }
        } catch (e) {
            // Ignorer les erreurs de localStorage
        }
        
        // Auto-focus sur le champ email
        const emailInput = document.getElementById('email');
        if (emailInput && !emailInput.value) {
            setTimeout(() => {
                emailInput.focus();
            }, 300);
        }
    });
    
    // Raccourci clavier pour le mode démo
    document.addEventListener('keydown', function(e) {
        // Ctrl + Alt + D pour remplir les champs de démo
        if (e.ctrlKey && e.altKey && e.key === 'd') {
            e.preventDefault();
            fillDemoCredentials();
        }
        
        // Entrée pour soumettre le formulaire
        if (e.key === 'Enter' && !e.ctrlKey && !e.altKey) {
            // La soumission est déjà gérée par l'event listener du formulaire
        }
    });
    
    // Détecter la connexion internet
    window.addEventListener('online', function() {
        showToast('Connexion rétablie', 'success');
    });
    
    window.addEventListener('offline', function() {
        showToast('Vous êtes hors ligne', 'error');
    });
    
    // Styles d'animation supplémentaires
    const style = document.createElement('style');
    style.textContent = `
        .spinner {
            width: 1.5rem;
            height: 1.5rem;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }
        
        /* Support pour dark mode */
        @media (prefers-color-scheme: dark) {
            input:-webkit-autofill,
            input:-webkit-autofill:hover,
            input:-webkit-autofill:focus {
                -webkit-text-fill-color: white;
                -webkit-box-shadow: 0 0 0px 1000px #1e293b inset;
                transition: background-color 5000s ease-in-out 0s;
            }
        }
        
        /* Améliorations pour mobile */
        @media (max-width: 640px) {
            .shadow-2xl {
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            }
            
            .p-8 {
                padding: 1.5rem;
            }
        }
    `;
    document.head.appendChild(style);
    
    // Service Worker pour PWA (optionnel)
    if ('serviceWorker' in navigator && window.location.hostname !== 'localhost') {
        navigator.serviceWorker.register('/sw.js').catch(console.error);
    }
    </script>
</body>
</html>