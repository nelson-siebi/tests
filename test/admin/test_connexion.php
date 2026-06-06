<?php
// test_connexion.php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=invest;charset=utf8mb4', 'root', '');
    echo "Connexion OK";
} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>