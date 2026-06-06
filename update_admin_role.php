<?php
require 'vendor/autoload.php';

use App\Core\Model;

$db = Model::connect();

echo "=== Mise à jour du rôle utilisateur ===\n\n";

// Mise à jour du rôle
$stmt = $db->prepare('UPDATE users SET role = ? WHERE email = ?');
$result = $stmt->execute(['admin', 'nelsonsiebi237@gmail.com']);

if ($result) {
    echo "✓ Rôle mis à jour avec succès!\n\n";

    // Vérification
    $stmt = $db->prepare('SELECT id, name, email, role FROM users WHERE email = ?');
    $stmt->execute(['nelsonsiebi237@gmail.com']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "Vérification:\n";
    echo "- ID: {$user['id']}\n";
    echo "- Nom: {$user['name']}\n";
    echo "- Email: {$user['email']}\n";
    echo "- Rôle: {$user['role']}\n\n";

    echo "✓ L'utilisateur peut maintenant accéder au panneau d'administration!\n";
} else {
    echo "✗ Erreur lors de la mise à jour.\n";
}

echo "\n=== Fin de la mise à jour ===\n";
