<?php
// add_admin.php

// Configuration de la base de données
$host = 'localhost';
$dbname = 'invest';
$user = 'root';
$pass = ''; // Mets ton mot de passe MySQL ici

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
   
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

$message = '';

// Vérifier si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Vérifier les champs obligatoires
    if (empty($username) || empty($password) || empty($role)) {
        $message = "Erreur : Les champs username, password et role sont obligatoires.";
    } else {
        // Hachage du mot de passe
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Préparer et exécuter l'insertion
        try {
            $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, role, email) VALUES (:username, :password, :role, :email)");
            $stmt->execute([
                ':username' => $username,
                ':password' => $passwordHash,
                ':role'     => $role,
                ':email'    => $email
            ]);
            $message = "Administrateur '<strong>$username</strong>' ajouté avec succès !";
        } catch (PDOException $e) {
            $message = "Erreur lors de l'ajout : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Administrateur</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Facultatif : Styles pour mieux centrer le contenu pour la démo */
        body {
            background-color: #f7fafc; /* bg-gray-50 */
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-xl shadow-2xl">
        <h2 class="text-3xl font-bold text-center text-gray-900">Ajouter un Administrateur 🧑‍💻</h2>

        <?php if ($message): ?>
            <div class="p-4 rounded-md <?php echo strpos($message, 'succès') !== false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                       placeholder="Ex: john.doe">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                       placeholder="Ex: john.doe@example.com">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
                <input type="password" id="password" name="password" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                       placeholder="********">
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-gray-700">Rôle</label>
                <select id="role" name="role" required
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm">
                    <option value="superadmin">Superadmin</option>
                    <option value="support">Support</option>
                    <option value="finance">Finance</option>
                    <option value="moderator">Moderator</option>
                </select>
            </div>

            <div>
                <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                    Ajouter l'administrateur ➕
                </button>
            </div>
        </form>
    </div>
</body>
</html>