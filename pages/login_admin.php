<?php


// Si déjà connecté, rediriger
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_plans.php');
    exit();
}

// Vérifier les identifiants (à adapter avec ta table admin_users)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // REMPLACEZ CES VÉRIFICATIONS PAR TA VRAIE LOGIQUE
    $admin_username = 'admin'; // À changer
    $admin_password = 'admin123'; // À changer
    
    if ($username === $admin_username && $password === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin_plans.php');
        exit();
    } else {
        $error = "Identifiants incorrects!";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - izyboost</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-2xl shadow-2xl w-96">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-2">
            <i class="fas fa-chart-line"></i> izyboost
        </h1>
        <p class="text-center text-gray-600 mb-8">Espace Administrateur</p>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 text-red-800 p-3 rounded-lg mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Nom d'utilisateur</label>
                <input type="text" name="username" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 mb-2">Mot de passe</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-medium">
                <i class="fas fa-sign-in-alt mr-2"></i> Se connecter
            </button>
        </form>
        
        <p class="text-center text-sm text-gray-500 mt-6">
            © <?php echo date('Y'); ?> izyboost - Admin Panel
        </p>
    </div>
</body>
</html>