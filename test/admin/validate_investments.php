<?php
// Débogage avancé
function debug_log($message, $data = null) {
    $log = date('Y-m-d H:i:s') . " - " . $message;
    if ($data !== null) {
        $log .= " - " . print_r($data, true);
    }
    file_put_contents('debug.log', $log . PHP_EOL, FILE_APPEND);
    error_log($log);
}

// Enregistrer toutes les erreurs
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    debug_log("ERREUR [$errno] $errstr dans $errfile ligne $errline");
    return false;
});

// Enregistrer les exceptions
set_exception_handler(function($exception) {
    debug_log("EXCEPTION: " . $exception->getMessage() . " dans " . $exception->getFile() . ":" . $exception->getLine());
});

debug_log("=== Début de l'exécution ===");
debug_log("SESSION", $_SESSION);
debug_log("GET", $_GET);
debug_log("POST", $_POST);