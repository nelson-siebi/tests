<?php
require 'vendor/autoload.php';

use App\Core\Model;

$db = Model::connect();

echo "=== Vérification de l'utilisateur admin ===\n\n";

// Recherche avec gmail.com
echo "1. Recherche avec nelsonsiebi237@gmail.com:\n";
$stmt = $db->prepare('SELECT id, name, email, role FROM users WHERE email = ?');
$stmt->execute(['nelsonsiebi237@gmail.com']);
$userGmail = $stmt->fetch(PDO::FETCH_ASSOC);

if ($userGmail) {
    echo "   ✓ Utilisateur trouvé!\n";
    echo "   - ID: {$userGmail['id']}\n";
    echo "   - Nom: {$userGmail['name']}\n";
    echo "   - Email: {$userGmail['email']}\n";
    echo "   - Rôle: {$userGmail['role']}\n\n";
} else {
    echo "   ✗ Aucun utilisateur trouvé.\n\n";
}

// Recherche avec gail.com (faute de frappe dans schema.sql)
echo "2. Recherche avec nelsonsiebi237@gail.com:\n";
$stmt = $db->prepare('SELECT id, name, email, role FROM users WHERE email = ?');
$stmt->execute(['nelsonsiebi237@gail.com']);
$userGail = $stmt->fetch(PDO::FETCH_ASSOC);

if ($userGail) {
    echo "   ✓ Utilisateur trouvé!\n";
    echo "   - ID: {$userGail['id']}\n";
    echo "   - Nom: {$userGail['name']}\n";
    echo "   - Email: {$userGail['email']}\n";
    echo "   - Rôle: {$userGail['role']}\n\n";
} else {
    echo "   ✗ Aucun utilisateur trouvé.\n\n";
}

// Proposition de correction
if ($userGail && !$userGmail) {
    echo "=== Correction suggérée ===\n";
    echo "L'utilisateur admin existe avec l'email 'gail.com' (faute de frappe).\n";
    echo "Voulez-vous corriger l'email en 'gmail.com'? (y/n): ";

    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    if (trim($line) === 'y') {
        $stmt = $db->prepare('UPDATE users SET email = ? WHERE id = ?');
        $stmt->execute(['nelsonsiebi237@gmail.com', $userGail['id']]);
        echo "✓ Email corrigé avec succès!\n";
    } else {
        echo "✗ Correction annulée.\n";
    }
    fclose($handle);
} elseif (!$userGail && !$userGmail) {
    echo "=== Aucun utilisateur admin trouvé ===\n";
    echo "Vous devez créer un utilisateur admin manuellement.\n";
}

echo "\n=== Fin de la vérification ===\n";
