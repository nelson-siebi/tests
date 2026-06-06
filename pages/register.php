<?php
// pages/register.php

// Initialiser la session si pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: home');
    exit();
}

// Inclure la connexion à la base de données
require_once 'config/db.php';
$db = Database::getInstance()->getConnection();

// Traitement du formulaire d'inscription
$errors = [];
$success = false;
$form_data = [
    'nom' => '',
    'prenom' => '',
    'email' => '',
    'telephone' => '',
    'referral_code' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et sécurisation des données
    $form_data['nom'] = filter_var(trim($_POST['nom'] ?? ''), FILTER_SANITIZE_STRING);
    $form_data['prenom'] = filter_var(trim($_POST['prenom'] ?? ''), FILTER_SANITIZE_STRING);
    $form_data['email'] = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $form_data['telephone'] = filter_var(trim($_POST['telephone'] ?? ''), FILTER_SANITIZE_STRING);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $form_data['referral_code'] = strtoupper(trim($_POST['referral_code'] ?? ''));
    $terms = isset($_POST['terms']);
    $newsletter = isset($_POST['newsletter']);

    // Validation
    if (empty($form_data['nom'])) {
        $errors['nom'] = "Le nom est requis";
    } elseif (strlen($form_data['nom']) < 2) {
        $errors['nom'] = "Le nom doit contenir au moins 2 caractères";
    }

    if (empty($form_data['prenom'])) {
        $errors['prenom'] = "Le prénom est requis";
    } elseif (strlen($form_data['prenom']) < 2) {
        $errors['prenom'] = "Le prénom doit contenir au moins 2 caractères";
    }

    if (empty($form_data['email'])) {
        $errors['email'] = "L'email est requis";
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Format d'email invalide";
    }

    if (empty($form_data['telephone'])) {
        $errors['telephone'] = "Le téléphone est requis";
    } elseif (!preg_match('/^[\+]?[0-9\s\-\(\)]{8,20}$/', $form_data['telephone'])) {
        $errors['telephone'] = "Format de téléphone invalide";
    }

    if (empty($password)) {
        $errors['password'] = "Le mot de passe est requis";
    } elseif (strlen($password) < 8) {
        $errors['password'] = "Le mot de passe doit contenir au moins 8 caractères";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors['password'] = "Le mot de passe doit contenir au moins une majuscule";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors['password'] = "Le mot de passe doit contenir au moins un chiffre";
    }

    if ($password !== $confirm_password) {
        $errors['confirm_password'] = "Les mots de passe ne correspondent pas";
    }

    if (!$terms) {
        $errors['terms'] = "Vous devez accepter les conditions d'utilisation";
    }

    // Vérifier si l'email existe déjà
    if (empty($errors['email'])) {
        try {
            $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute([':email' => $form_data['email']]);
            if ($stmt->fetch()) {
                $errors['email'] = "Cet email est déjà utilisé";
            }
        } catch (PDOException $e) {
            error_log("Email check error: " . $e->getMessage());
            $errors['email'] = "Erreur de vérification de l'email";
        }
    }

    // Vérifier le code de parrainage si fourni
    $referred_by = null;
    if (empty($errors['referral_code']) && !empty($form_data['referral_code'])) {
        try {
            $stmt = $db->prepare("SELECT id FROM users WHERE referral_code = :referral_code AND statut = 'active'");
            $stmt->execute([':referral_code' => $form_data['referral_code']]);
            $referrer = $stmt->fetch();

            if ($referrer) {
                $referred_by = $referrer['id'];
            } else {
                $errors['referral_code'] = "Code de parrainage invalide";
            }
        } catch (PDOException $e) {
            error_log("Referral code check error: " . $e->getMessage());
            $errors['referral_code'] = "Erreur de vérification du code de parrainage";
        }
    }

    // Vérifier les tentatives d'inscription
    if (!isset($_SESSION['register_attempts'])) {
        $_SESSION['register_attempts'] = 0;
    }

    if ($_SESSION['register_attempts'] >= 10) {
        $errors[] = "Trop de tentatives d'inscription. Veuillez réessayer plus tard.";
    }

    // Si pas d'erreurs, procéder à l'inscription
    if (empty($errors)) {
        try {
            // Démarrer une transaction
            $db->beginTransaction();

            // Générer un code de parrainage unique
            $max_attempts = 10;
            $attempts = 0;
            do {
                $generated_referral_code = generateReferralCode($form_data['prenom'], $form_data['nom']);
                $stmt = $db->prepare("SELECT id FROM users WHERE referral_code = :referral_code");
                $stmt->execute([':referral_code' => $generated_referral_code]);
                $attempts++;
            } while ($stmt->fetch() && $attempts < $max_attempts);

            if ($attempts >= $max_attempts) {
                throw new Exception("Impossible de générer un code de parrainage unique");
            }

            // Hacher le mot de passe
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insérer l'utilisateur
            $stmt = $db->prepare("
                INSERT INTO users (nom, prenom, email, phone, password, referral_code, referred_by, statut, created_at, updated_at)
                VALUES (:nom, :prenom, :email, :phone, :password, :referral_code, :referred_by, 'active', NOW(), NOW())
            ");

            $stmt->execute([
                ':nom' => $form_data['nom'],
                ':prenom' => $form_data['prenom'],
                ':email' => $form_data['email'],
                ':phone' => $form_data['telephone'],
                ':password' => $hashed_password,
                ':referral_code' => $generated_referral_code,
                ':referred_by' => $referred_by
            ]);

            $user_id = $db->lastInsertId();

            // Créer le wallet pour l'utilisateur
            $stmt = $db->prepare("
                INSERT INTO wallets (user_id, solde_investissement, solde_publicite, solde_parrainage, total_depots, created_at, updated_at)
                VALUES (:user_id, 0.00, 0.00, 0.00, 0.00, NOW(), NOW())
            ");
            $stmt->execute([':user_id' => $user_id]);

            // Enregistrer le bonus de bienvenue (parrainage)
            if ($referred_by) {
                // Récupérer le bonus de parrainage depuis les paramètres
                $bonus_amount = 500; // 500 FCFA par défaut

                // Enregistrer la relation de parrainage
                $stmt = $db->prepare("
                    INSERT INTO referrals (parrain_id, filleul_id, bonus, valide, date_creation)
                    VALUES (:parrain_id, :filleul_id, :bonus, 0, NOW())
                ");
                $stmt->execute([
                    ':parrain_id' => $referred_by,
                    ':filleul_id' => $user_id,
                    ':bonus' => $bonus_amount
                ]);
            }

            // Envoyer un email de bienvenue (à implémenter)
            // sendWelcomeEmail($form_data['email'], $form_data['prenom']);

            // Valider la transaction
            $db->commit();

            // Réinitialiser les tentatives
            $_SESSION['register_attempts'] = 0;

            // Connexion automatique
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_nom'] = $form_data['nom'];
            $_SESSION['user_prenom'] = $form_data['prenom'];
            $_SESSION['user_email'] = $form_data['email'];
            $_SESSION['user_phone'] = $form_data['telephone'];
            $_SESSION['user_referral_code'] = $generated_referral_code;
            $_SESSION['login_time'] = time();
            $_SESSION['new_user'] = true;

            $success = true;

        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $db->rollBack();
            error_log("Registration error: " . $e->getMessage());
            $errors[] = "Erreur lors de l'inscription. Veuillez réessayer.";
            $_SESSION['register_attempts']++;
        }
    } else {
        $_SESSION['register_attempts']++;
    }
}

// Fonction pour générer un code de parrainage
function generateReferralCode($prenom, $nom)
{
    $initials = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
    $random = strtoupper(bin2hex(random_bytes(3)));
    return 'INV' . $initials . $random;
}
?>

<!DOCTYPE html>
<html lang="fr" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="Inscrivez-vous sur FreeCash - Plateforme d'investissement sécurisée">
    <meta name="theme-color" content="#0284c7">
    <title>Inscription | FreeCash - Investissements Intelligents</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico">
    <link rel="apple-touch-icon" href="assets/apple-touch-icon.png">

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
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Critical CSS Inline -->
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
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #f6f9fc 0%, #edf2f7 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            line-height: 1.5;
            color: #334155;
        }

        /* Support pour dark mode */
        @media (prefers-color-scheme: dark) {
            body {
                background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                color: #cbd5e1;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInRight {
            from {
                transform: translateX(30px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        @keyframes checkmark {
            0% {
                transform: scale(0);
                opacity: 0;
            }

            50% {
                transform: scale(1.2);
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.5s ease-out;
        }

        .animate-slideInRight {
            animation: slideInRight 0.5s ease-out;
        }

        .animate-pulse {
            animation: pulse 2s infinite;
        }

        .animate-checkmark {
            animation: checkmark 0.5s ease-out;
        }

        /* Accessibilité */
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

        /* Focus styles pour accessibilité */
        :focus-visible {
            outline: 3px solid var(--primary);
            outline-offset: 2px;
        }

        /* Support mobile */
        @media (max-width: 640px) {
            .mobile-hidden {
                display: none;
            }
        }
    </style>

    <!-- Styles différés -->
    <style>
        .register-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 3rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        @media (min-width: 1024px) {
            .register-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .brand-section {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .register-card {
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            max-width: 520px;
            width: 100%;
            margin: 0 auto;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        @media (prefers-color-scheme: dark) {
            .register-card {
                background: #1e293b;
                border-color: #334155;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            }
        }

        .gradient-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 2.5rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }

        .step {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .step.active {
            background: white;
            width: 24px;
            border-radius: 4px;
        }

        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 0.5rem;
            transition: all 0.3s ease;
        }

        .password-strength.weak {
            background: #ef4444;
            width: 25%;
        }

        .password-strength.fair {
            background: #f59e0b;
            width: 50%;
        }

        .password-strength.good {
            background: #3b82f6;
            width: 75%;
        }

        .password-strength.strong {
            background: #10b981;
            width: 100%;
        }

        .requirement {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.25rem;
            font-size: 0.75rem;
        }

        .requirement.valid {
            color: #10b981;
        }

        .requirement.invalid {
            color: #94a3b8;
        }
    </style>
</head>

<body class="min-h-screen">
    <!-- Accessibilité : Skip to content -->
    <a href="#main-content"
        class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-primary text-white px-4 py-2 rounded-lg z-50">
        Aller au contenu principal
    </a>

    <div class="register-grid w-full">
        <!-- Section de présentation -->
        <div class="brand-section mobile-hidden">
            <div class="max-w-lg mx-auto lg:mx-0">
                <!-- Logo et titre -->
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div
                            class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center">
                            <img src="assets/img/logo.png" alt="FreeCash Logo" class="w-8 h-8 object-contain">
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                                Inves<span class="text-primary-600">tian</span>
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">
                                Votre succès commence ici
                            </p>
                        </div>
                    </div>

                    <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-4">
                        Commencez à <span class="text-primary-600">investir</span> en toute confiance
                    </h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
                        Rejoignez notre communauté d'investisseurs et découvrez des opportunités de croissance.
                    </p>
                </div>

                <!-- Avantages -->
                <div class="space-y-4 mb-10">


                    <div
                        class="flex items-start gap-4 p-4 bg-white dark:bg-slate-800/50 rounded-xl border border-gray-200 dark:border-slate-700">
                        <div class="flex-shrink-0">
                            <div
                                class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                <i class="fas fa-chart-line text-blue-600 dark:text-blue-400"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Retours sur investissement
                                garantis</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Jusqu'à 30% de rendement</p>
                        </div>
                    </div>

                    <div
                        class="flex items-start gap-4 p-4 bg-white dark:bg-slate-800/50 rounded-xl border border-gray-200 dark:border-slate-700">
                        <div class="flex-shrink-0">
                            <div
                                class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                                <i class="fas fa-users text-purple-600 dark:text-purple-400"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Programme de parrainage</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Gagnez jusqu'à 15% sur les
                                investissements de vos filleuls</p>
                        </div>
                    </div>
                </div>



            </div>
        </div>

        <!-- Carte d'inscription -->
        <div class="flex items-center justify-center">
            <?php if ($success): ?>
                <!-- Message de succès -->
                <div class="register-card animate-fadeIn">
                    <div class="gradient-header">
                        <div class="relative z-10">
                            <div
                                class="w-20 h-20 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center mx-auto mb-4 border border-white/30">
                                <i class="fas fa-check text-white text-3xl animate-checkmark"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-white mb-2">Inscription réussie !</h2>
                            <p class="text-primary-100">Bienvenue dans la communauté FreeCash</p>
                        </div>
                    </div>

                    <div class="p-6 md:p-8 text-center">
                        <!-- Avatar de bienvenue -->
                        <div
                            class="w-24 h-24 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center mx-auto mb-6 text-white text-4xl font-bold">
                            <?php echo strtoupper(substr($form_data['prenom'], 0, 1)); ?>
                        </div>

                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                            Bienvenue, <?php echo htmlspecialchars($form_data['prenom']); ?> ! 👋
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-8">
                            Votre compte a été créé avec succès. Vous êtes maintenant connecté.
                        </p>

                        <!-- Code de parrainage -->
                        <div
                            class="bg-gradient-to-r from-primary-50 to-primary-50 dark:from-primary-900/20 dark:to-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-2xl p-6 mb-8">
                            <div class="flex items-center justify-center gap-2 mb-3">
                                <i class="fas fa-user-friends text-primary-600 dark:text-primary-400"></i>
                                <h4 class="font-semibold text-gray-900 dark:text-white">Votre code de parrainage</h4>
                            </div>
                            <div class="mb-4">
                                <div
                                    class="text-2xl font-bold text-primary-600 dark:text-primary-400 tracking-wider font-mono bg-white dark:bg-slate-800 py-3 px-6 rounded-lg border-2 border-dashed border-primary-300 dark:border-primary-700">
                                    <?php echo $_SESSION['user_referral_code']; ?>
                                </div>
                            </div>
                            <button onclick="copyReferralCode()"
                                class="w-full bg-primary-600 hover:bg-primary-700 dark:bg-primary-700 dark:hover:bg-primary-600 text-white font-medium py-3 px-4 rounded-lg transition-colors flex items-center justify-center gap-2">
                                <i class="fas fa-copy"></i>
                                Copier le code
                            </button>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">
                                Partagez ce code avec vos amis et gagnez des commissions
                            </p>
                        </div>

                        <!-- Prochaines étapes -->
                        <div class="space-y-4">
                            <a href="home"
                                class="block w-full bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 hover:shadow-lg">
                                <i class="fas fa-tachometer-alt mr-2"></i>
                                Accéder à mon tableau de bord
                            </a>

                            <a href="depot"
                                class="block w-full border-2 border-primary-600 text-primary-600 hover:bg-primary-50 dark:text-primary-400 dark:border-primary-500 dark:hover:bg-primary-900/20 font-medium py-3 px-6 rounded-xl transition-colors">
                                <i class="fas fa-arrow-circle-down mr-2"></i>
                                Faire mon premier dépôt
                            </a>
                        </div>

                        <!-- Redirection automatique -->
                        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-slate-700">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <i class="fas fa-clock mr-2"></i>
                                Redirection vers votre tableau de bord dans <span id="countdown">5</span> secondes...
                            </p>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- Formulaire d'inscription -->
                <div id="main-content" class="register-card animate-fadeIn">
                    <!-- En-tête gradient -->
                    <div class="gradient-header">
                        <div class="relative z-10">
                            <div
                                class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center mx-auto mb-4 border border-white/30">
                                <i class="fas fa-user-plus text-white text-2xl"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-white mb-2">Créer un compte</h2>
                            <p class="text-primary-100">Rejoignez notre communauté d'investisseurs</p>
                        </div>
                    </div>

                    <!-- Contenu principal -->
                    <div class="p-6 md:p-8">
                        <!-- Indicateur de progression -->
                        <div class="step-indicator">
                            <div class="step active"></div>
                            <div class="step"></div>
                            <div class="step"></div>
                        </div>

                        <!-- Messages d'erreur généraux -->
                        <?php if (!empty($errors) && is_array($errors) && count(array_filter($errors, 'is_string')) > 0): ?>
                            <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl"
                                role="alert">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 mt-0.5">
                                        <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-sm font-semibold text-red-800 dark:text-red-300 mb-2">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Veuillez corriger les erreurs suivantes
                                        </h3>
                                        <ul class="space-y-1">
                                            <?php foreach ($errors as $error):
                                                if (is_string($error)): ?>
                                                    <li class="text-sm text-red-700 dark:text-red-400 flex items-center gap-2">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                                        <?php echo htmlspecialchars($error); ?>
                                                    </li>
                                                <?php endif; endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Formulaire -->
                        <form method="POST" action="" id="registerForm" class="space-y-6" novalidate>
                            <!-- Nom et Prénom -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="nom"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        <i class="fas fa-user-tag mr-2 text-green-600"></i>
                                        Nom
                                    </label>
                                    <input type="text" id="nom" name="nom"
                                        value="<?php echo htmlspecialchars($form_data['nom']); ?>"
                                        class="w-full pl-12 pr-4 py-3.5 bg-white dark:bg-slate-800 border <?php echo isset($errors['nom']) ? 'border-red-500' : 'border-gray-300 dark:border-slate-600'; ?> rounded-xl focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all duration-200 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
                                        placeholder="Votre nom" required autocomplete="family-name" minlength="2">
                                    <div
                                        class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-gray-500">
                                        <i class="fas fa-user-tag text-lg"></i>
                                    </div>
                                    <?php if (isset($errors['nom'])): ?>
                                        <div class="mt-2 text-sm text-red-600 dark:text-red-400">
                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                            <?php echo $errors['nom']; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <label for="prenom"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        <i class="fas fa-user mr-2 text-green-600"></i>
                                        Prénom
                                    </label>
                                    <div class="relative">
                                        <input type="text" id="prenom" name="prenom"
                                            value="<?php echo htmlspecialchars($form_data['prenom']); ?>"
                                            class="w-full pl-12 pr-4 py-3.5 bg-white dark:bg-slate-800 border <?php echo isset($errors['prenom']) ? 'border-red-500' : 'border-gray-300 dark:border-slate-600'; ?> rounded-xl focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all duration-200 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
                                            placeholder="Votre prénom" required autocomplete="given-name" minlength="2">
                                        <div
                                            class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-gray-500">
                                            <i class="fas fa-user text-lg"></i>
                                        </div>
                                    </div>
                                    <?php if (isset($errors['prenom'])): ?>
                                        <div class="mt-2 text-sm text-red-600 dark:text-red-400">
                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                            <?php echo $errors['prenom']; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <i class="fas fa-envelope mr-2 text-green-600"></i>
                                    Adresse email
                                </label>
                                <div class="relative">
                                    <input type="email" id="email" name="email"
                                        value="<?php echo htmlspecialchars($form_data['email']); ?>"
                                        class="w-full pl-12 pr-4 py-3.5 bg-white dark:bg-slate-800 border <?php echo isset($errors['email']) ? 'border-red-500' : 'border-gray-300 dark:border-slate-600'; ?> rounded-xl focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all duration-200 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
                                        placeholder="exemple@email.com" required autocomplete="email" spellcheck="false">
                                    <div
                                        class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-gray-500">
                                        <i class="fas fa-envelope text-lg"></i>
                                    </div>
                                </div>
                                <?php if (isset($errors['email'])): ?>
                                    <div class="mt-2 text-sm text-red-600 dark:text-red-400">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        <?php echo $errors['email']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Téléphone -->
                            <div>
                                <label for="telephone"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <i class="fas fa-phone mr-2 text-green-600"></i>
                                    Numéro de téléphone
                                </label>
                                <div class="relative">
                                    <input type="tel" id="telephone" name="telephone"
                                        value="<?php echo htmlspecialchars($form_data['telephone']); ?>"
                                        class="w-full pl-12 pr-4 py-3.5 bg-white dark:bg-slate-800 border <?php echo isset($errors['telephone']) ? 'border-red-500' : 'border-gray-300 dark:border-slate-600'; ?> rounded-xl focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all duration-200 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
                                        placeholder="+237 6XX XX XX XX" required autocomplete="tel">
                                    <div
                                        class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-gray-500">
                                        <i class="fas fa-phone text-lg"></i>
                                    </div>
                                </div>
                                <?php if (isset($errors['telephone'])): ?>
                                    <div class="mt-2 text-sm text-red-600 dark:text-red-400">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        <?php echo $errors['telephone']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Mot de passe -->
                            <div>
                                <label for="password"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <i class="fas fa-lock mr-2 text-green-600"></i>
                                    Mot de passe
                                </label>
                                <div class="relative">
                                    <input type="password" id="password" name="password"
                                        class="w-full pl-12 pr-12 py-3.5 bg-white dark:bg-slate-800 border <?php echo isset($errors['password']) ? 'border-red-500' : 'border-gray-300 dark:border-slate-600'; ?> rounded-xl focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all duration-200 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
                                        placeholder="••••••••" required autocomplete="new-password" minlength="8">
                                    <div
                                        class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-gray-500">
                                        <i class="fas fa-lock text-lg"></i>
                                    </div>
                                    <button type="button" onclick="togglePassword('password', this)"
                                        class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors"
                                        aria-label="Afficher/masquer le mot de passe">
                                        <i class="fas fa-eye text-lg"></i>
                                    </button>
                                </div>
                                <div id="passwordStrength" class="password-strength weak"></div>
                                <?php if (isset($errors['password'])): ?>
                                    <div class="mt-2 text-sm text-red-600 dark:text-red-400">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        <?php echo $errors['password']; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Exigences de mot de passe -->
                                <div class="mt-3 space-y-1">
                                    <div id="reqLength" class="requirement invalid">
                                        <i class="fas fa-circle text-xs"></i>
                                        <span>Au moins 8 caractères</span>
                                    </div>
                                    <div id="reqUppercase" class="requirement invalid">
                                        <i class="fas fa-circle text-xs"></i>
                                        <span>Une lettre majuscule</span>
                                    </div>
                                    <div id="reqNumber" class="requirement invalid">
                                        <i class="fas fa-circle text-xs"></i>
                                        <span>Un chiffre</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Confirmation mot de passe -->
                            <div>
                                <label for="confirm_password"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <i class="fas fa-lock mr-2 text-green-600"></i>
                                    Confirmer le mot de passe
                                </label>
                                <div class="relative">
                                    <input type="password" id="confirm_password" name="confirm_password"
                                        class="w-full pl-12 pr-12 py-3.5 bg-white dark:bg-slate-800 border <?php echo isset($errors['confirm_password']) ? 'border-red-500' : 'border-gray-300 dark:border-slate-600'; ?> rounded-xl focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all duration-200 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
                                        placeholder="••••••••" required autocomplete="new-password">
                                    <div
                                        class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-gray-500">
                                        <i class="fas fa-lock text-lg"></i>
                                    </div>
                                    <button type="button" onclick="togglePassword('confirm_password', this)"
                                        class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors"
                                        aria-label="Afficher/masquer le mot de passe">
                                        <i class="fas fa-eye text-lg"></i>
                                    </button>
                                </div>
                                <?php if (isset($errors['confirm_password'])): ?>
                                    <div class="mt-2 text-sm text-red-600 dark:text-red-400">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        <?php echo $errors['confirm_password']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Code de parrainage -->
                            <div>
                                <label for="referral_code"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <i class="fas fa-user-plus mr-2 text-green-600"></i>
                                    Code de parrainage (optionnel)
                                </label>
                                <div class="relative">
                                    <input type="text" id="referral_code" name="referral_code"
                                        value="<?php echo htmlspecialchars($_GET['ref'] ?? ''); ?>"
                                        class="w-full pl-12 pr-4 py-3.5 bg-white dark:bg-slate-800 border <?php echo isset($errors['referral_code']) ? 'border-red-500' : 'border-gray-300 dark:border-slate-600'; ?> rounded-xl focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all duration-200 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
                                        placeholder="Ex: IZYAB1234" autocomplete="off" spellcheck="false">
                                    <div
                                        class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-gray-500">
                                        <i class="fas fa-user-plus text-lg"></i>
                                    </div>
                                </div>
                                <?php if (isset($errors['referral_code'])): ?>
                                    <div class="mt-2 text-sm text-red-600 dark:text-red-400">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        <?php echo $errors['referral_code']; ?>
                                    </div>
                                <?php endif; ?>
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Si un membre vous a invité, entrez son code pour bénéficier d'un bonus
                                </p>
                            </div>

                            <!-- Newsletter et conditions -->
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="newsletter" name="newsletter" type="checkbox"
                                            class="w-5 h-5 rounded border-gray-300 dark:border-slate-600 text-green-600 focus:ring-green-500 bg-white dark:bg-slate-800"
                                            checked>
                                    </div>
                                    <label for="newsletter"
                                        class="ml-3 text-sm text-gray-700 dark:text-gray-300 cursor-pointer select-none">
                                        Je souhaite recevoir les actualités et offres spéciales par email
                                    </label>
                                </div>

                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="terms" name="terms" type="checkbox"
                                            class="w-5 h-5 rounded border-gray-300 dark:border-slate-600 text-green-600 focus:ring-green-500 bg-white dark:bg-slate-800"
                                            required>
                                    </div>
                                    <label for="terms"
                                        class="ml-3 text-sm text-gray-700 dark:text-gray-300 cursor-pointer select-none">
                                        J'accepte les
                                        <a href="terms"
                                            class="text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300 font-medium"
                                            target="_blank">
                                            conditions d'utilisation
                                        </a>
                                        et la
                                        <a href="privacy"
                                            class="text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300 font-medium"
                                            target="_blank">
                                            politique de confidentialité
                                        </a>
                                    </label>
                                </div>
                                <?php if (isset($errors['terms'])): ?>
                                    <div class="text-sm text-red-600 dark:text-red-400">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        <?php echo $errors['terms']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Bouton d'inscription -->
                            <button type="submit" id="submitBtn"
                                class="w-full bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 hover:shadow-lg active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-none group">
                                <span id="submitText">
                                    <i class="fas fa-user-plus mr-2"></i>
                                    Créer mon compte
                                </span>
                                <div id="submitSpinner" class="hidden">
                                    <div class="spinner mx-auto"></div>
                                </div>
                            </button>

                            <!-- Séparateur -->
                            <div class="relative">
                                <div class="absolute inset-0 flex items-center">
                                    <div class="w-full border-t border-gray-300 dark:border-slate-600"></div>
                                </div>
                                <div class="relative flex justify-center text-sm">
                                    <span class="px-4 bg-white dark:bg-slate-800 text-gray-500 dark:text-gray-400">
                                        Déjà membre ?
                                    </span>
                                </div>
                            </div>

                            <!-- Lien vers connexion -->
                            <div class="text-center">
                                <a href="login"
                                    class="inline-flex items-center text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium">
                                    <i class="fas fa-sign-in-alt mr-2"></i>
                                    Connectez-vous ici
                                </a>
                            </div>
                        </form>

                        <!-- Sécurité et confidentialité -->
                        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-slate-700">
                            <div
                                class="flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-500 dark:text-gray-400">
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
            <?php endif; ?>
        </div>
    </div>

    <!-- Scripts JavaScript -->
    <script>
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

        // Copier le code de parrainage
        function copyReferralCode() {
            const code = '<?php echo $_SESSION['user_referral_code'] ?? ''; ?>';
            navigator.clipboard.writeText(code).then(() => {
                showToast('Code copié dans le presse-papier !', 'success');
            }).catch(err => {
                showToast('Erreur lors de la copie', 'error');
            });
        }

        // Compte à rebours pour la redirection
        <?php if ($success): ?>
            let countdown = 5;
            const countdownElement = document.getElementById('countdown');
            const countdownInterval = setInterval(() => {
                countdown--;
                countdownElement.textContent = countdown;

                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    window.location.href = 'home';
                }
            }, 1000);

            // Permettre à l'utilisateur d'annuler la redirection
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    clearInterval(countdownInterval);
                    countdownElement.textContent = 'redirection annulée';
                    countdownElement.parentElement.innerHTML = '<i class="fas fa-times mr-2"></i> Redirection annulée';
                }
            });
        <?php endif; ?>

        // Afficher une notification toast
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                info: 'bg-blue-500'
            };

            toast.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg font-medium z-50 animate-slideInRight`;
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

        // Vérification de la force du mot de passe
        function checkPasswordStrength(password) {
            let strength = 0;
            const requirements = {
                length: false,
                uppercase: false,
                number: false
            };

            // Longueur minimale
            if (password.length >= 8) {
                strength += 1;
                requirements.length = true;
            }

            // Majuscule
            if (/[A-Z]/.test(password)) {
                strength += 1;
                requirements.uppercase = true;
            }

            // Chiffre
            if (/[0-9]/.test(password)) {
                strength += 1;
                requirements.number = true;
            }

            // Mettre à jour les indicateurs visuels
            updateRequirementsUI(requirements);

            // Mettre à jour la barre de force
            const strengthBar = document.getElementById('passwordStrength');
            strengthBar.classList.remove('weak', 'fair', 'good', 'strong');

            switch (strength) {
                case 1:
                    strengthBar.classList.add('weak');
                    break;
                case 2:
                    strengthBar.classList.add('fair');
                    break;
                case 3:
                    strengthBar.classList.add('good');
                    break;
            }

            return strength;
        }

        // Mettre à jour l'UI des exigences
        function updateRequirementsUI(requirements) {
            const reqLength = document.getElementById('reqLength');
            const reqUppercase = document.getElementById('reqUppercase');
            const reqNumber = document.getElementById('reqNumber');

            if (requirements.length) {
                reqLength.classList.remove('invalid');
                reqLength.classList.add('valid');
                reqLength.innerHTML = '<i class="fas fa-check-circle text-green-500"></i><span>Au moins 8 caractères</span>';
            } else {
                reqLength.classList.remove('valid');
                reqLength.classList.add('invalid');
                reqLength.innerHTML = '<i class="fas fa-circle text-xs"></i><span>Au moins 8 caractères</span>';
            }

            if (requirements.uppercase) {
                reqUppercase.classList.remove('invalid');
                reqUppercase.classList.add('valid');
                reqUppercase.innerHTML = '<i class="fas fa-check-circle text-green-500"></i><span>Une lettre majuscule</span>';
            } else {
                reqUppercase.classList.remove('valid');
                reqUppercase.classList.add('invalid');
                reqUppercase.innerHTML = '<i class="fas fa-circle text-xs"></i><span>Une lettre majuscule</span>';
            }

            if (requirements.number) {
                reqNumber.classList.remove('invalid');
                reqNumber.classList.add('valid');
                reqNumber.innerHTML = '<i class="fas fa-check-circle text-green-500"></i><span>Un chiffre</span>';
            } else {
                reqNumber.classList.remove('valid');
                reqNumber.classList.add('invalid');
                reqNumber.innerHTML = '<i class="fas fa-circle text-xs"></i><span>Un chiffre</span>';
            }
        }

        // Validation du formulaire
        function validateForm() {
            let isValid = true;

            // Réinitialiser les erreurs
            document.querySelectorAll('[id$="Error"]').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('input').forEach(input => {
                input.classList.remove('border-red-500');
            });

            // Valider chaque champ
            const fields = ['nom', 'prenom', 'email', 'telephone', 'password', 'confirm_password'];
            fields.forEach(field => {
                const input = document.getElementById(field);
                if (input && !input.value.trim() && input.required) {
                    showError(field, 'Ce champ est requis');
                    isValid = false;
                }
            });

            // Valider l'email
            const email = document.getElementById('email');
            if (email && email.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
                showError('email', 'Format d\'email invalide');
                isValid = false;
            }

            // Valider le téléphone
            const telephone = document.getElementById('telephone');
            if (telephone && telephone.value && !/^[\+]?[0-9\s\-\(\)]{8,20}$/.test(telephone.value)) {
                showError('telephone', 'Format de téléphone invalide');
                isValid = false;
            }

            // Valider le mot de passe
            const password = document.getElementById('password');
            if (password && password.value) {
                const strength = checkPasswordStrength(password.value);
                if (strength < 3) {
                    showError('password', 'Le mot de passe ne respecte pas toutes les exigences de sécurité');
                    isValid = false;
                }
            }

            // Valider la confirmation du mot de passe
            const confirmPassword = document.getElementById('confirm_password');
            if (password && confirmPassword && password.value !== confirmPassword.value) {
                showError('confirm_password', 'Les mots de passe ne correspondent pas');
                isValid = false;
            }

            // Valider les conditions
            const terms = document.getElementById('terms');
            if (terms && !terms.checked) {
                showError('terms', 'Vous devez accepter les conditions');
                isValid = false;
            }

            return isValid;
        }

        // Afficher une erreur
        function showError(field, message) {
            const input = document.getElementById(field);
            if (input) {
                input.classList.add('border-red-500');
                const errorDiv = document.getElementById(field + 'Error');
                if (errorDiv) {
                    errorDiv.textContent = message;
                    errorDiv.classList.remove('hidden');
                }
            }
        }

        // Gestion de la soumission du formulaire
        document.getElementById('registerForm')?.addEventListener('submit', function (e) {
            e.preventDefault();

            if (validateForm()) {
                // Afficher le spinner et désactiver le bouton
                const submitBtn = document.getElementById('submitBtn');
                const submitText = document.getElementById('submitText');
                const submitSpinner = document.getElementById('submitSpinner');

                if (submitBtn && submitText && submitSpinner) {
                    submitBtn.disabled = true;
                    submitText.classList.add('hidden');
                    submitSpinner.classList.remove('hidden');
                }

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
        document.getElementById('password')?.addEventListener('input', function () {
            checkPasswordStrength(this.value);
        });

        document.getElementById('confirm_password')?.addEventListener('input', function () {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;

            if (confirmPassword && password !== confirmPassword) {
                this.classList.add('border-red-500');
                const errorDiv = document.getElementById('confirm_passwordError');
                if (errorDiv) {
                    errorDiv.textContent = 'Les mots de passe ne correspondent pas';
                    errorDiv.classList.remove('hidden');
                }
            } else {
                this.classList.remove('border-red-500');
                const errorDiv = document.getElementById('confirm_passwordError');
                if (errorDiv) {
                    errorDiv.classList.add('hidden');
                }
            }
        });

        // Auto-formater le numéro de téléphone
        document.getElementById('telephone')?.addEventListener('input', function (e) {
            let value = this.value.replace(/\D/g, '');

            if (value.startsWith('237')) {
                value = '+' + value;
            } else if (value.startsWith('0')) {
                value = '+237' + value.substring(1);
            } else if (value.length > 0 && !value.startsWith('+')) {
                value = '+237' + value;
            }

            // Formater l'affichage
            if (value.length > 3) {
                value = value.substring(0, 4) + ' ' + value.substring(4);
            }
            if (value.length > 7) {
                value = value.substring(0, 8) + ' ' + value.substring(8);
            }
            if (value.length > 10) {
                value = value.substring(0, 11) + ' ' + value.substring(11);
            }
            if (value.length > 13) {
                value = value.substring(0, 14) + ' ' + value.substring(14);
            }

            this.value = value.trim();
        });

        // Auto-majuscules pour le code de parrainage
        document.getElementById('referral_code')?.addEventListener('input', function () {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        });

        // Styles d'animation supplémentaires
        const style = document.createElement('style');
        style.textContent = `
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .animate-shake {
            animation: shake 0.5s ease-in-out;
        }
        
        .spinner {
            width: 1.5rem;
            height: 1.5rem;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
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
            .register-card {
                border-radius: 1rem;
            }
            
            .gradient-header {
                padding: 2rem 1.5rem;
            }
            
            .p-6 {
                padding: 1.5rem;
            }
        }
        
        /* Support pour les appareils avec notches */
        @supports (padding: max(0px)) {
            body {
                padding-left: max(1rem, env(safe-area-inset-left));
                padding-right: max(1rem, env(safe-area-inset-right));
                padding-top: max(1rem, env(safe-area-inset-top));
                padding-bottom: max(1rem, env(safe-area-inset-bottom));
            }
        }
    `;
        document.head.appendChild(style);

        // Auto-focus sur le premier champ
        document.addEventListener('DOMContentLoaded', function () {
            <?php if (!$success): ?>
                const firstInput = document.querySelector('input:not([type="hidden"])');
                if (firstInput && !firstInput.value) {
                    setTimeout(() => {
                        firstInput.focus();
                    }, 300);
                }

                // Remplir automatiquement depuis le cache du navigateur
                try {
                    const savedData = JSON.parse(localStorage.getItem('register_form_data') || '{}');
                    Object.keys(savedData).forEach(key => {
                        const input = document.getElementById(key);
                        if (input && !input.value) {
                            input.value = savedData[key];

                            // Déclencher les événements de validation
                            if (input.id === 'password') {
                                checkPasswordStrength(input.value);
                            }
                            if (input.id === 'confirm_password') {
                                input.dispatchEvent(new Event('input'));
                            }
                        }
                    });
                } catch (e) {
                    // Ignorer les erreurs de localStorage
                }
            <?php endif; ?>
        });

        // Sauvegarder les données du formulaire dans localStorage
        document.querySelectorAll('#registerForm input').forEach(input => {
            input.addEventListener('input', function () {
                try {
                    const formData = JSON.parse(localStorage.getItem('register_form_data') || '{}');
                    formData[this.id] = this.value;
                    localStorage.setItem('register_form_data', JSON.stringify(formData));
                } catch (e) {
                    // Ignorer les erreurs de localStorage
                }
            });
        });

        // Effacer les données sauvegardées après soumission réussie
        <?php if ($success): ?>
            try {
                localStorage.removeItem('register_form_data');
            } catch (e) {
                // Ignorer les erreurs de localStorage
            }
        <?php endif; ?>

        // Détecter la connexion internet
        window.addEventListener('online', function () {
            showToast('Connexion rétablie', 'success');
        });

        window.addEventListener('offline', function () {
            showToast('Vous êtes hors ligne', 'error');
        });
    </script>
</body>

</html>