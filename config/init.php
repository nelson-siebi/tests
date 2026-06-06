<?php
// config/init.php

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pages qui ne nécessitent PAS d'être connecté
$public_pages = ['login', 'register', 'logout', 'politique'];

// Pages qui ne doivent PAS afficher le header/navigation
$no_layout_pages = ['login', 'register', 'logout', 'politique', 'tuto', 'admin', 'admin_dashboard', 'admin_users', 'admin_transactions'];

// Définir BASE_URL dynamiquement
$script_dir = dirname($_SERVER['SCRIPT_NAME']);
define('BASE_URL', rtrim($script_dir, '/'));
define('SITE_URL', 'https://investian.infy.uk');
define('SITE_NAME', 'FreeCash');

// Récupérer la page demandée
$page = $_GET['page'] ?? 'home';



// Vérification d'authentification
// Si l'utilisateur n'est pas connecté et essaie d'accéder à une page privée
if (!isset($_SESSION['user_id']) && !in_array($page, $public_pages)) {
    header('Location: login');
    exit();
}

// Si l'utilisateur est connecté et essaie d'accéder à login/register
if (isset($_SESSION['user_id']) && in_array($page, ['login', 'register'])) {
    header('Location: ?page=home');
    exit();
}


// Liste des pages valides
$allowed_pages = ['home', 'investissement', 'profile', 'parainage', 'videos', 'profil', 'profile', 'messages', 'settings', 'login', 'register', 'logout', 'login_admin', 'admin', 'politique', 'retrais', 'notifications', 'transactions', 'support', 'tuto'];

// Rediriger vers home si invalide (ou 404 selon votre choix)
if (!in_array($page, $allowed_pages)) {
    $page = '404';
}

// Titre de la page pour le header
$page_titles = [
    'home' => 'Tableau de bord',
    'profile' => 'Mon profil',
    'messages' => 'Messages',
    'settings' => 'Paramètres',
    'investissement' => 'investissement',
    'videos' => 'videos',
    'parainage' => 'parainage',
    'profil' => 'profil',
    'profile' => 'profile',
    'login' => 'login',
    'register' => 'register',
    'logout' => 'logout',
    'admin' => 'admin',
    'login_admin' => 'login_admin',
    'politique' => 'politique',
    'retrais' => 'retrais',
    'notifications' => 'notifications',
    'transactions' => 'transactions',
    'support' => 'support',
    'tuto' => 'tuto',
];

$page_title = $page_titles[$page] ?? 'cash plans ';

// Déterminer si on doit afficher le layout complet
$show_layout = !in_array($page, $no_layout_pages);
