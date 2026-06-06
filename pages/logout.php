<?php
// signout.php ou logout.php

// Démarrer la session si elle n'est pas déjà active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vider toutes les variables de session
$_SESSION = [];

// Détruire le cookie de session si utilisé
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Détruire complètement la session
session_destroy();

// Supprimer le cookie "remember me" s'il existe
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Supprimer d'autres cookies spécifiques si nécessaire
if (isset($_COOKIE['user_preferences'])) {
    setcookie('user_preferences', '', time() - 3600, '/');
}

// Redirection vers la page de connexion avec un message
header('Location: index.php?page=login&message=deconnected');
exit();

