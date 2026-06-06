<?php
// pages/settings.php

require_once 'config/db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

try {
    $userId = $_SESSION['user_id'];
$pdo = Database::getInstance()->getConnection();

    
    // Récupérer les informations de l'utilisateur
    $stmt = $pdo->prepare("
        SELECT 
            u.*,
            w.*,
            k.statut as kyc_status,
            k.type_document as kyc_type,
            k.date_validation as kyc_date
        FROM users u
        LEFT JOIN wallets w ON u.id = w.user_id
        LEFT JOIN kyc k ON u.id = k.user_id AND k.statut = 'approved'
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception("Utilisateur non trouvé");
    }
    
    // Récupérer les paramètres généraux
    $stmt = $pdo->query("SELECT id, value FROM settings");
    $settings = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['id']] = $row['value'];
    }
    
    // Récupérer les méthodes de paiement disponibles
    $payment_methods = [
        ['code' => 'orange', 'name' => 'Orange Money', 'icon' => 'fa-mobile-alt', 'country' => 'CI'],
        ['code' => 'mtn', 'name' => 'MTN Mobile Money', 'icon' => 'fa-sim-card', 'country' => 'CI'],
        ['code' => 'wave', 'name' => 'Wave', 'icon' => 'fa-wave-square', 'country' => 'CI'],
        ['code' => 'moov', 'name' => 'Moov Money', 'icon' => 'fa-sim-card', 'country' => 'BF'],
        ['code' => 'visa', 'name' => 'Carte Visa', 'icon' => 'fa-credit-card', 'country' => 'ALL'],
        ['code' => 'mastercard', 'name' => 'Mastercard', 'icon' => 'fa-credit-card', 'country' => 'ALL'],
        ['code' => 'paypal', 'name' => 'PayPal', 'icon' => 'fa-paypal', 'country' => 'ALL']
    ];
    
    // Récupérer les méthodes enregistrées par l'utilisateur
   $stmt = $pdo->prepare("
    SELECT methode, reference, MAX(statut) as statut
    FROM transactions 
    WHERE user_id = ? AND type = 'depot' 
    GROUP BY methode, reference
    ORDER BY MAX(created_at) DESC
");

    $stmt->execute([$user_id]);
    $user_payment_methods = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupérer l'historique des sessions
    $stmt = $pdo->prepare("
        SELECT 
            id,
            user_id,
            ip_address,
            user_agent,
            last_activity,
            created_at
        FROM user_sessions 
        WHERE user_id = ?
        ORDER BY last_activity DESC
        LIMIT 10
    ");
    $stmt->execute([$user_id]);
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupérer les notifications
    $stmt = $pdo->prepare("
        SELECT 
            id,
            type,
            title,
            message,
            is_read,
            created_at
        FROM notifications 
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 20
    ");
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Traitement des formulaires
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'update_profile':
                $nom = trim($_POST['nom'] ?? '');
                $prenom = trim($_POST['prenom'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $country = trim($_POST['country'] ?? '');
                $city = trim($_POST['city'] ?? '');
                
                if (!empty($nom) && !empty($prenom)) {
                    $stmt = $pdo->prepare("
                        UPDATE users 
                        SET nom = ?, prenom = ?, phone = ?, pays = ?, ville = ?, updated_at = NOW() 
                        WHERE id = ?
                    ");
                    $stmt->execute([$nom, $prenom, $phone, $country, $city, $user_id]);
                    $success = "Profil mis à jour avec succès !";
                } else {
                    $error = "Le nom et le prénom sont obligatoires";
                }
                break;
                
            case 'update_email':
                $email = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    // Vérifier si l'email est déjà utilisé
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                    $stmt->execute([$email, $user_id]);
                    if ($stmt->fetch()) {
                        $error = "Cet email est déjà utilisé par un autre compte";
                    } else {
                        // Vérifier le mot de passe
                        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                        $stmt->execute([$user_id]);
                        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if (password_verify($password, $user_data['password'])) {
                            $stmt = $pdo->prepare("UPDATE users SET email = ?, updated_at = NOW() WHERE id = ?");
                            $stmt->execute([$email, $user_id]);
                            $success = "Email mis à jour avec succès !";
                        } else {
                            $error = "Mot de passe incorrect";
                        }
                    }
                } else {
                    $error = "Email invalide";
                }
                break;
                
            case 'change_password':
                $current_password = $_POST['current_password'] ?? '';
                $new_password = $_POST['new_password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                
                // Vérifier l'ancien mot de passe
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($current_password, $user_data['password'])) {
                    if ($new_password === $confirm_password) {
                        if (strlen($new_password) >= 6) {
                            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
                            $stmt->execute([$hashed_password, $user_id]);
                            $success = "Mot de passe modifié avec succès !";
                        } else {
                            $error = "Le mot de passe doit contenir au moins 6 caractères";
                        }
                    } else {
                        $error = "Les nouveaux mots de passe ne correspondent pas";
                    }
                } else {
                    $error = "Mot de passe actuel incorrect";
                }
                break;
                
            case 'update_notifications':
                $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
                $sms_notifications = isset($_POST['sms_notifications']) ? 1 : 0;
                $push_notifications = isset($_POST['push_notifications']) ? 1 : 0;
                $marketing_emails = isset($_POST['marketing_emails']) ? 1 : 0;
                
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET 
                        email_notifications = ?,
                        sms_notifications = ?,
                        push_notifications = ?,
                        marketing_emails = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$email_notifications, $sms_notifications, $push_notifications, $marketing_emails, $user_id]);
                $success = "Préférences de notifications mises à jour !";
                break;
                
            case 'update_security':
                $two_factor = isset($_POST['two_factor']) ? 1 : 0;
                $login_alerts = isset($_POST['login_alerts']) ? 1 : 0;
                $session_timeout = intval($_POST['session_timeout'] ?? 60);
                
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET 
                        two_factor_auth = ?,
                        login_alerts = ?,
                        session_timeout = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$two_factor, $login_alerts, $session_timeout, $user_id]);
                $success = "Paramètres de sécurité mis à jour !";
                break;
                
            case 'add_payment_method':
                $method = $_POST['method'] ?? '';
                $account_name = trim($_POST['account_name'] ?? '');
                $account_number = trim($_POST['account_number'] ?? '');
                $provider = trim($_POST['provider'] ?? '');
                
                if (!empty($method) && !empty($account_number)) {
                    // Vérifier si la méthode existe déjà
                    $stmt = $pdo->prepare("
                        SELECT id FROM user_payment_methods 
                        WHERE user_id = ? AND methode = ? AND account_number = ?
                    ");
                    $stmt->execute([$user_id, $method, $account_number]);
                    
                    if (!$stmt->fetch()) {
                        $stmt = $pdo->prepare("
                            INSERT INTO user_payment_methods 
                            (user_id, methode, account_name, account_number, provider, statut, created_at) 
                            VALUES (?, ?, ?, ?, ?, 'active', NOW())
                        ");
                        $stmt->execute([$user_id, $method, $account_name, $account_number, $provider]);
                        $success = "Méthode de paiement ajoutée avec succès !";
                    } else {
                        $error = "Cette méthode de paiement existe déjà";
                    }
                } else {
                    $error = "Veuillez remplir tous les champs obligatoires";
                }
                break;
                
            case 'delete_payment_method':
                $method_id = intval($_POST['method_id'] ?? 0);
                
                if ($method_id > 0) {
                    $stmt = $pdo->prepare("DELETE FROM user_payment_methods WHERE id = ? AND user_id = ?");
                    $stmt->execute([$method_id, $user_id]);
                    $success = "Méthode de paiement supprimée !";
                }
                break;
                
            case 'upload_kyc':
                if (isset($_FILES['kyc_document']) && $_FILES['kyc_document']['error'] == 0) {
                    $document_type = $_POST['document_type'] ?? '';
                    $document_number = trim($_POST['document_number'] ?? '');
                    
                    if (!empty($document_type)) {
                        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
                        $max_size = 10 * 1024 * 1024; // 10MB
                        
                        if (in_array($_FILES['kyc_document']['type'], $allowed_types)) {
                            if ($_FILES['kyc_document']['size'] <= $max_size) {
                                $upload_dir = '../uploads/kyc/';
                                if (!is_dir($upload_dir)) {
                                    mkdir($upload_dir, 0755, true);
                                }
                                
                                $extension = pathinfo($_FILES['kyc_document']['name'], PATHINFO_EXTENSION);
                                $filename = 'kyc_' . $user_id . '_' . time() . '.' . $extension;
                                $filepath = $upload_dir . $filename;
                                
                                if (move_uploaded_file($_FILES['kyc_document']['tmp_name'], $filepath)) {
                                    // Supprimer l'ancienne demande KYC
                                    $stmt = $pdo->prepare("DELETE FROM kyc WHERE user_id = ? AND statut != 'approved'");
                                    $stmt->execute([$user_id]);
                                    
                                    // Insérer la nouvelle demande
                                    $stmt = $pdo->prepare("
                                        INSERT INTO kyc 
                                        (user_id, type_document, numero_document, fichier_path, statut, date_soumission) 
                                        VALUES (?, ?, ?, ?, 'pending', NOW())
                                    ");
                                    $stmt->execute([$user_id, $document_type, $document_number, 'uploads/kyc/' . $filename]);
                                    $success = "Document KYC soumis avec succès ! La vérification prendra 24-48h.";
                                } else {
                                    $error = "Erreur lors de l'upload du document";
                                }
                            } else {
                                $error = "Le document est trop volumineux (max 10MB)";
                            }
                        } else {
                            $error = "Format de fichier non supporté. Formats acceptés: JPG, PNG, GIF, PDF";
                        }
                    } else {
                        $error = "Veuillez sélectionner un type de document";
                    }
                } else {
                    $error = "Aucun document sélectionné ou erreur d'upload";
                }
                break;
                
            case 'delete_account':
                $confirm_text = $_POST['confirm_text'] ?? '';
                $password = $_POST['password'] ?? '';
                
                if ($confirm_text === 'SUPPRIMER MON COMPTE') {
                    // Vérifier le mot de passe
                    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (password_verify($password, $user_data['password'])) {
                        // Désactiver le compte
                        $stmt = $pdo->prepare("UPDATE users SET statut = 'banned', updated_at = NOW() WHERE id = ?");
                        $stmt->execute([$user_id]);
                        
                        // Déconnecter l'utilisateur
                        session_destroy();
                        header('Location: ../logout.php');
                        exit();
                    } else {
                        $error = "Mot de passe incorrect";
                    }
                } else {
                    $error = "Veuillez taper exactement 'SUPPRIMER MON COMPTE'";
                }
                break;
                
            case 'export_data':
                // Générer l'export des données
                $export_data = [
                    'user' => $user,
                    'transactions' => [],
                    'investments' => []
                ];
                
                // Récupérer les transactions
                $stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC");
                $stmt->execute([$user_id]);
                $export_data['transactions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Récupérer les investissements
                $stmt = $pdo->prepare("
                    SELECT up.*, p.nom as plan_name 
                    FROM user_plans up 
                    JOIN plans p ON up.plan_id = p.id 
                    WHERE up.user_id = ? 
                    ORDER BY up.created_at DESC
                ");
                $stmt->execute([$user_id]);
                $export_data['investments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Convertir en JSON
                $json_data = json_encode($export_data, JSON_PRETTY_PRINT);
                
                // Générer le fichier
                $filename = 'Investian_export_' . $user_id . '_' . date('Y-m-d') . '.json';
                header('Content-Type: application/json');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                echo $json_data;
                exit();
                break;
        }
        
        // Recharger les données après mise à jour
        if (!in_array($action, ['export_data', 'delete_account'])) {
            $stmt = $pdo->prepare("
                SELECT 
                    u.*,
                    w.*,
                    k.statut as kyc_status,
                    k.type_document as kyc_type,
                    k.date_validation as kyc_date
                FROM users u
                LEFT JOIN wallets w ON u.id = w.user_id
                LEFT JOIN kyc k ON u.id = k.user_id AND k.statut = 'approved'
                WHERE u.id = ?
            ");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
    
} catch (Exception $e) {
    $error = "Erreur: " . $e->getMessage();
    $user = [];
    $settings = [];
    $payment_methods = [];
    $user_payment_methods = [];
    $sessions = [];
    $notifications = [];
}

// Liste des pays africains
$african_countries = [
    'CI' => 'Côte d\'Ivoire',
    'SN' => 'Sénégal',
    'ML' => 'Mali',
    'BF' => 'Burkina Faso',
    'GN' => 'Guinée',
    'TG' => 'Togo',
    'BJ' => 'Bénin',
    'NE' => 'Niger',
    'CM' => 'Cameroun',
    'CD' => 'RDC',
    'GA' => 'Gabon',
    'CG' => 'Congo',
    'FR' => 'France',
    'BE' => 'Belgique',
    'CH' => 'Suisse',
    'CA' => 'Canada'
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres - cash</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .settings-container { animation: fadeIn 0.5s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }
        .animate-slideIn { animation: slideIn 0.3s ease-out; }
        .animate-slideOut { animation: slideOut 0.3s ease-in; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Navigation -->
        
        
        <!-- Messages d'alerte -->
        <?php if ($error): ?>
        <div class="fixed top-4 right-4 z-50 px-6 py-3 bg-red-600 text-white rounded-lg shadow-lg font-medium animate-slideIn">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="fixed top-4 right-4 z-50 px-6 py-3 bg-green-600 text-white rounded-lg shadow-lg font-medium animate-slideIn">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="container mx-auto px-4 py-8">
            <div class="settings-container">
                <!-- En-tête -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Paramètres du compte</h1>
                    <p class="text-gray-600">Gérez vos informations personnelles, sécurité et préférences</p>
                </div>

                <!-- Menu latéral et contenu -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                    <!-- Menu latéral -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-2xl shadow-sm p-4">
                            <nav class="space-y-2">
                                <a href="#profil" onclick="switchSection('profil')" 
                                   class="section-btn active flex items-center px-4 py-3 rounded-lg bg-green-50 text-green-600 font-medium">
                                    <i class="fas fa-user-circle mr-3"></i>
                                    Profil
                                </a>
                                <a href="#securite" onclick="switchSection('securite')" 
                                   class="section-btn flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">
                                    <i class="fas fa-shield-alt mr-3"></i>
                                    Sécurité
                                </a>
                                <a href="#notifications" onclick="switchSection('notifications')" 
                                   class="section-btn flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">
                                    <i class="fas fa-bell mr-3"></i>
                                    Notifications
                                </a>
                                <a href="#paiements" onclick="switchSection('paiements')" 
                                   class="section-btn flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">
                                    <i class="fas fa-credit-card mr-3"></i>
                                    Paiements
                                </a>
                                <a href="#kyc" onclick="switchSection('kyc')" 
                                   class="section-btn flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">
                                    <i class="fas fa-id-card mr-3"></i>
                                    Vérification KYC
                                </a>
                                <a href="#sessions" onclick="switchSection('sessions')" 
                                   class="section-btn flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">
                                    <i class="fas fa-laptop mr-3"></i>
                                    Sessions actives
                                </a>
                                <a href="#privacy" onclick="switchSection('privacy')" 
                                   class="section-btn flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">
                                    <i class="fas fa-lock mr-3"></i>
                                    Confidentialité
                                </a>
                                <a href="#danger" onclick="switchSection('danger')" 
                                   class="section-btn flex items-center px-4 py-3 rounded-lg text-red-600 hover:bg-red-50 font-medium">
                                    <i class="fas fa-exclamation-triangle mr-3"></i>
                                    Zone de danger
                                </a>
                            </nav>
                            
                            <!-- Statut du compte -->
                            <div class="mt-8 pt-6 border-t border-gray-200">
                                <h3 class="text-sm font-medium text-gray-500 mb-3">Statut du compte</h3>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-700">Vérification KYC</span>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                            <?php echo ($user['kyc_status'] ?? '') == 'approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                            <?php echo ($user['kyc_status'] ?? '') == 'approved' ? 'Vérifié' : 'En attente'; ?>
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-700">2FA activée</span>
                                        <span class="text-sm font-medium <?php echo ($user['two_factor_auth'] ?? 0) ? 'text-green-600' : 'text-gray-500'; ?>">
                                            <?php echo ($user['two_factor_auth'] ?? 0) ? 'Oui' : 'Non'; ?>
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-700">Dernière connexion</span>
                                        <span class="text-sm text-gray-500">Aujourd'hui</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contenu principal -->
                    <div class="lg:col-span-3">
                        <!-- Section Profil -->
                        <div id="section-profil" class="section-content active">
                            <div class="bg-white rounded-2xl shadow-sm p-6">
                                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                                    <i class="fas fa-user-circle text-green-600 mr-2"></i>
                                    Informations du profil
                                </h2>
                                
                                <form method="POST" class="space-y-6">
                                    <input type="hidden" name="action" value="update_profile">
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Prénom *</label>
                                            <input type="text" name="prenom" value="<?php echo htmlspecialchars($user['prenom'] ?? ''); ?>" 
                                                   required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
                                            <input type="text" name="nom" value="<?php echo htmlspecialchars($user['nom'] ?? ''); ?>" 
                                                   required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                        <div class="flex items-center">
                                            <input type="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" 
                                                   disabled class="flex-1 px-4 py-3 border border-gray-300 rounded-lg bg-gray-50">
                                            <button type="button" onclick="openEmailModal()" 
                                                    class="ml-3 px-4 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                                                Modifier
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
                                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Pays</label>
                                            <select name="country" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                                                <option value="">Sélectionner un pays</option>
                                                <?php foreach ($african_countries as $code => $name): ?>
                                                <option value="<?php echo $code; ?>" <?php echo ($user['pays'] ?? '') == $code ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($name); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Ville</label>
                                        <input type="text" name="city" value="<?php echo htmlspecialchars($user['ville'] ?? ''); ?>" 
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                                    </div>
                                    
                                    <div class="flex justify-end pt-4 border-t border-gray-200">
                                        <button type="submit" 
                                                class="px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                                            <i class="fas fa-save mr-2"></i>
                                            Enregistrer les modifications
                                        </button>
                                    </div>
                                </form>
                                
                                <!-- Changement de mot de passe -->
                                <div class="mt-8 pt-6 border-t border-gray-200">
                                    <h3 class="text-lg font-bold text-gray-800 mb-4">Changement de mot de passe</h3>
                                    <button onclick="openPasswordModal()" 
                                            class="px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                                        <i class="fas fa-key mr-2"></i>
                                        Changer le mot de passe
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Section Sécurité -->
                        <div id="section-securite" class="section-content hidden">
                            <div class="bg-white rounded-2xl shadow-sm p-6">
                                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                                    <i class="fas fa-shield-alt text-green-600 mr-2"></i>
                                    Sécurité et connexion
                                </h2>
                                
                                <form method="POST" class="space-y-6">
                                    <input type="hidden" name="action" value="update_security">
                                    
                                    <!-- Authentification à deux facteurs -->
                                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-4">
                                                <i class="fas fa-mobile-alt text-green-600"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-gray-800">Authentification à deux facteurs (2FA)</h4>
                                                <p class="text-sm text-gray-500">Ajoutez une couche de sécurité supplémentaire à votre compte</p>
                                            </div>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="two_factor" value="1" 
                                                   class="sr-only peer" <?php echo ($user['two_factor_auth'] ?? 0) ? 'checked' : ''; ?>>
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                        </label>
                                    </div>
                                    
                                    <!-- Alertes de connexion -->
                                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                                <i class="fas fa-bell text-blue-600"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-gray-800">Alertes de connexion</h4>
                                                <p class="text-sm text-gray-500">Recevez des notifications lors de nouvelles connexions</p>
                                            </div>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="login_alerts" value="1" 
                                                   class="sr-only peer" <?php echo ($user['login_alerts'] ?? 1) ? 'checked' : ''; ?>>
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                        </label>
                                    </div>
                                    
                                    <!-- Délai de session -->
                                    <div class="p-4 border border-gray-200 rounded-lg">
                                        <div class="flex items-center mb-4">
                                            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                                                <i class="fas fa-clock text-purple-600"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-gray-800">Délai d'expiration de session</h4>
                                                <p class="text-sm text-gray-500">Temps d'inactivité avant déconnexion automatique</p>
                                            </div>
                                        </div>
                                        <select name="session_timeout" class="w-full md:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                                            <option value="15" <?php echo ($user['session_timeout'] ?? 60) == 15 ? 'selected' : ''; ?>>15 minutes</option>
                                            <option value="30" <?php echo ($user['session_timeout'] ?? 60) == 30 ? 'selected' : ''; ?>>30 minutes</option>
                                            <option value="60" <?php echo ($user['session_timeout'] ?? 60) == 60 ? 'selected' : ''; ?>>1 heure</option>
                                            <option value="120" <?php echo ($user['session_timeout'] ?? 60) == 120 ? 'selected' : ''; ?>>2 heures</option>
                                            <option value="240" <?php echo ($user['session_timeout'] ?? 60) == 240 ? 'selected' : ''; ?>>4 heures</option>
                                        </select>
                                    </div>
                                    
                                    <div class="flex justify-end pt-4 border-t border-gray-200">
                                        <button type="submit" 
                                                class="px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                                            <i class="fas fa-save mr-2"></i>
                                            Enregistrer les paramètres
                                        </button>
                                    </div>
                                </form>
                                
                                <!-- Historique des sessions -->
                                <div class="mt-8 pt-6 border-t border-gray-200">
                                    <h3 class="text-lg font-bold text-gray-800 mb-4">Sessions actives</h3>
                                    <div class="space-y-3">
                                        <?php if (!empty($sessions)): ?>
                                            <?php foreach ($sessions as $session): ?>
                                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                                <div class="flex items-center">
                                                    <i class="fas fa-laptop text-gray-400 text-xl mr-3"></i>
                                                    <div>
                                                        <p class="font-medium text-gray-800">
                                                            <?php echo htmlspecialchars($session['user_agent'] ?? 'Appareil inconnu'); ?>
                                                        </p>
                                                        <p class="text-sm text-gray-500">
                                                            IP: <?php echo htmlspecialchars($session['ip_address'] ?? ''); ?> • 
                                                            Dernière activité: <?php echo date('d/m H:i', strtotime($session['last_activity'])); ?>
                                                        </p>
                                                    </div>
                                                </div>
                                                <?php if ($session['id'] == session_id()): ?>
                                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded">
                                                    Actuelle
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p class="text-gray-500 text-center py-4">Aucune session active</p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mt-4">
                                        <button onclick="terminateOtherSessions()" 
                                                class="text-sm text-red-600 hover:text-red-700 font-medium">
                                            <i class="fas fa-sign-out-alt mr-1"></i>
                                            Déconnecter toutes les autres sessions
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section Notifications -->
                        <div id="section-notifications" class="section-content hidden">
                            <div class="bg-white rounded-2xl shadow-sm p-6">
                                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                                    <i class="fas fa-bell text-green-600 mr-2"></i>
                                    Préférences de notifications
                                </h2>
                                
                                <form method="POST" class="space-y-6">
                                    <input type="hidden" name="action" value="update_notifications">
                                    
                                    <!-- Notifications par email -->
                                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                                <i class="fas fa-envelope text-blue-600"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-gray-800">Notifications par email</h4>
                                                <p class="text-sm text-gray-500">Recevez des notifications importantes par email</p>
                                            </div>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="email_notifications" value="1" 
                                                   class="sr-only peer" <?php echo ($user['email_notifications'] ?? 1) ? 'checked' : ''; ?>>
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                        </label>
                                    </div>
                                    
                                    <!-- Notifications SMS -->
                                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-4">
                                                <i class="fas fa-sms text-green-600"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-gray-800">Notifications SMS</h4>
                                                <p class="text-sm text-gray-500">Recevez des notifications importantes par SMS</p>
                                            </div>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="sms_notifications" value="1" 
                                                   class="sr-only peer" <?php echo ($user['sms_notifications'] ?? 0) ? 'checked' : ''; ?>>
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                        </label>
                                    </div>
                                    
                                    <!-- Notifications push -->
                                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                                                <i class="fas fa-bell text-purple-600"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-gray-800">Notifications push</h4>
                                                <p class="text-sm text-gray-500">Recevez des notifications dans votre navigateur</p>
                                            </div>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="push_notifications" value="1" 
                                                   class="sr-only peer" <?php echo ($user['push_notifications'] ?? 1) ? 'checked' : ''; ?>>
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translateX-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                        </label>
                                    </div>
                                    
                                    <!-- Emails marketing -->
                                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center mr-4">
                                                <i class="fas fa-bullhorn text-yellow-600"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-gray-800">Emails marketing</h4>
                                                <p class="text-sm text-gray-500">Recevez des offres promotionnelles et des nouveautés</p>
                                            </div>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="marketing_emails" value="1" 
                                                   class="sr-only peer" <?php echo ($user['marketing_emails'] ?? 1) ? 'checked' : ''; ?>>
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translateX-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                        </label>
                                    </div>
                                    
                                    <div class="flex justify-end pt-4 border-t border-gray-200">
                                        <button type="submit" 
                                                class="px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                                            <i class="fas fa-save mr-2"></i>
                                            Enregistrer les préférences
                                        </button>
                                    </div>
                                </form>
                                
                                <!-- Historique des notifications -->
                                <div class="mt-8 pt-6 border-t border-gray-200">
                                    <h3 class="text-lg font-bold text-gray-800 mb-4">Dernières notifications</h3>
                                    <div class="space-y-3 max-h-64 overflow-y-auto">
                                        <?php if (!empty($notifications)): ?>
                                            <?php foreach ($notifications as $notification): ?>
                                            <div class="p-3 border border-gray-200 rounded-lg <?php echo !$notification['is_read'] ? 'bg-blue-50' : ''; ?>">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <p class="font-medium text-gray-800"><?php echo htmlspecialchars($notification['title']); ?></p>
                                                        <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($notification['message']); ?></p>
                                                        <p class="text-xs text-gray-500 mt-2">
                                                            <?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?>
                                                        </p>
                                                    </div>
                                                    <?php if (!$notification['is_read']): ?>
                                                    <span class="w-2 h-2 bg-blue-600 rounded-full"></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p class="text-gray-500 text-center py-4">Aucune notification</p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mt-4">
                                        <button onclick="markAllAsRead()" class="text-sm text-green-600 hover:text-green-700 font-medium">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Tout marquer comme lu
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section Paiements -->
                        <div id="section-paiements" class="section-content hidden">
                            <div class="bg-white rounded-2xl shadow-sm p-6">
                                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                                    <i class="fas fa-credit-card text-green-600 mr-2"></i>
                                    Méthodes de paiement
                                </h2>
                                
                                <!-- Méthodes enregistrées -->
                                <div class="mb-8">
                                    <h3 class="text-lg font-bold text-gray-800 mb-4">Vos méthodes de paiement</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <?php if (!empty($user_payment_methods)): ?>
                                            <?php foreach ($user_payment_methods as $method): ?>
                                            <div class="border border-gray-200 rounded-lg p-4">
                                                <div class="flex justify-between items-start mb-3">
                                                    <div class="flex items-center">
                                                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                                            <i class="fas fa-<?php echo $method['methode'] == 'orange' ? 'mobile-alt' : 
                                                                             ($method['methode'] == 'mtn' ? 'sim-card' : 
                                                                             ($method['methode'] == 'visa' ? 'credit-card' : 'money-bill')); ?> 
                                                                text-green-600"></i>
                                                        </div>
                                                        <div>
                                                            <h4 class="font-bold text-gray-800">
                                                                <?php echo $method['methode'] == 'orange' ? 'Orange Money' : 
                                                                       ($method['methode'] == 'mtn' ? 'MTN Mobile Money' : 
                                                                       ($method['methode'] == 'visa' ? 'Carte Visa' : ucfirst($method['methode']))); ?>
                                                            </h4>
                                                            <p class="text-sm text-gray-600">
                                                                <?php echo htmlspecialchars($method['reference'] ?? ''); ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded">
                                                        <?php echo $method['statut'] == 'success' ? 'Activé' : 'En attente'; ?>
                                                    </span>
                                                </div>
                                                <div class="flex justify-end space-x-2">
                                                    <button onclick="editPaymentMethod(<?php echo $method['id'] ?? 0; ?>)" 
                                                            class="text-sm text-blue-600 hover:text-blue-700">
                                                        <i class="fas fa-edit mr-1"></i> Modifier
                                                    </button>
                                                    <form method="POST" class="inline">
                                                        <input type="hidden" name="action" value="delete_payment_method">
                                                        <input type="hidden" name="method_id" value="<?php echo $method['id'] ?? 0; ?>">
                                                        <button type="submit" onclick="return confirm('Supprimer cette méthode de paiement ?')" 
                                                                class="text-sm text-red-600 hover:text-red-700">
                                                            <i class="fas fa-trash mr-1"></i> Supprimer
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="col-span-2 text-center py-8">
                                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                                    <i class="fas fa-credit-card text-gray-400 text-2xl"></i>
                                                </div>
                                                <p class="text-gray-500 mb-2">Aucune méthode de paiement enregistrée</p>
                                                <p class="text-sm text-gray-400">Ajoutez une méthode pour faciliter vos transactions</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Ajouter une nouvelle méthode -->
                                <div class="pt-6 border-t border-gray-200">
                                    <h3 class="text-lg font-bold text-gray-800 mb-4">Ajouter une méthode de paiement</h3>
                                    <form method="POST" class="space-y-4">
                                        <input type="hidden" name="action" value="add_payment_method">
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Type de méthode</label>
                                                <select name="method" required 
                                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                                                    <option value="">Sélectionner une méthode</option>
                                                    <?php foreach ($payment_methods as $method): ?>
                                                    <option value="<?php echo $method['code']; ?>">
                                                        <?php echo htmlspecialchars($method['name']); ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Nom sur le compte</label>
                                                <input type="text" name="account_name" 
                                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Numéro de compte *</label>
                                                <input type="text" name="account_number" required 
                                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500"
                                                       placeholder="Ex: 07 XX XX XX XX">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Opérateur</label>
                                                <input type="text" name="provider" 
                                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500"
                                                       placeholder="Ex: Orange CI, MTN BF, etc.">
                                            </div>
                                        </div>
                                        
                                        <div class="flex justify-end">
                                            <button type="submit" 
                                                    class="px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                                                <i class="fas fa-plus mr-2"></i>
                                                Ajouter la méthode
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Section KYC -->
                        <div id="section-kyc" class="section-content hidden">
                            <div class="bg-white rounded-2xl shadow-sm p-6">
                                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                                    <i class="fas fa-id-card text-green-600 mr-2"></i>
                                    Vérification d'identité (KYC)
                                </h2>
                                
                                <!-- Statut KYC -->
                                <div class="mb-8">
                                    <div class="flex items-center justify-between p-6 bg-gray-50 rounded-xl">
                                        <div class="flex items-center">
                                            <div class="w-12 h-12 <?php echo ($user['kyc_status'] ?? '') == 'approved' ? 'bg-green-100' : 'bg-yellow-100'; ?> 
                                                                 rounded-full flex items-center justify-center mr-4">
                                                <i class="fas fa-<?php echo ($user['kyc_status'] ?? '') == 'approved' ? 'check' : 'clock'; ?> 
                                                               text-<?php echo ($user['kyc_status'] ?? '') == 'approved' ? 'green' : 'yellow'; ?>-600 text-xl"></i>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-bold text-gray-800">
                                                    Statut: 
                                                    <span class="<?php echo ($user['kyc_status'] ?? '') == 'approved' ? 'text-green-600' : 'text-yellow-600'; ?>">
                                                        <?php echo ($user['kyc_status'] ?? '') == 'approved' ? 'Vérifié' : 
                                                               (($user['kyc_status'] ?? '') == 'pending' ? 'En attente de vérification' : 'Non vérifié'); ?>
                                                    </span>
                                                </h3>
                                                <p class="text-gray-600">
                                                    <?php if (($user['kyc_status'] ?? '') == 'approved'): ?>
                                                    Votre identité a été vérifiée le <?php echo date('d/m/Y', strtotime($user['kyc_date'])); ?>
                                                    <?php elseif (($user['kyc_status'] ?? '') == 'pending'): ?>
                                                    Votre document est en cours de vérification (24-48h)
                                                    <?php else: ?>
                                                    Complétez la vérification KYC pour augmenter vos limites
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <?php if (($user['kyc_status'] ?? '') == 'approved'): ?>
                                        <span class="px-4 py-2 bg-green-100 text-green-800 font-medium rounded-lg">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            Vérifié
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Avantages KYC -->
                                <div class="mb-8">
                                    <h3 class="text-lg font-bold text-gray-800 mb-4">Avantages de la vérification KYC</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="border border-gray-200 rounded-lg p-4 text-center">
                                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                                <i class="fas fa-money-bill-wave text-blue-600"></i>
                                            </div>
                                            <h4 class="font-bold text-gray-800 mb-1">Limites augmentées</h4>
                                            <p class="text-sm text-gray-600">Retrait jusqu'à 500,000 FCFA/jour</p>
                                        </div>
                                        <div class="border border-gray-200 rounded-lg p-4 text-center">
                                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                                <i class="fas fa-shield-alt text-green-600"></i>
                                            </div>
                                            <h4 class="font-bold text-gray-800 mb-1">Sécurité renforcée</h4>
                                            <p class="text-sm text-gray-600">Protection accrue de votre compte</p>
                                        </div>
                                        <div class="border border-gray-200 rounded-lg p-4 text-center">
                                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                                <i class="fas fa-bolt text-purple-600"></i>
                                            </div>
                                            <h4 class="font-bold text-gray-800 mb-1">Traitement rapide</h4>
                                            <p class="text-sm text-gray-600">Retraits traités en 2h maximum</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Formulaire KYC -->
                                <?php if (($user['kyc_status'] ?? '') != 'approved'): ?>
                                <div class="pt-6 border-t border-gray-200">
                                    <h3 class="text-lg font-bold text-gray-800 mb-4">Soumettre un document</h3>
                                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                                        <input type="hidden" name="action" value="upload_kyc">
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Type de document *</label>
                                                <select name="document_type" required 
                                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                                                    <option value="">Sélectionner un document</option>
                                                    <option value="id_card">Carte d'identité nationale</option>
                                                    <option value="passport">Passeport</option>
                                                    <option value="driving_license">Permis de conduire</option>
                                                    <option value="voter_card">Carte d'électeur</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Numéro du document</label>
                                                <input type="text" name="document_number" 
                                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500"
                                                       placeholder="Ex: AB123456">
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Document d'identité *</label>
                                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-green-500 transition-colors">
                                                <div class="mb-4">
                                                    <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl"></i>
                                                </div>
                                                <p class="text-gray-600 mb-2">Glissez-déposez votre fichier ou</p>
                                                <label class="cursor-pointer inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                                    <i class="fas fa-upload mr-2"></i>
                                                    Parcourir les fichiers
                                                    <input type="file" name="kyc_document" required class="hidden" 
                                                           accept=".jpg,.jpeg,.png,.gif,.pdf">
                                                </label>
                                                <p class="text-xs text-gray-500 mt-4">
                                                    Formats acceptés: JPG, PNG, GIF, PDF (max 10MB)
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                            <h4 class="font-bold text-gray-800 mb-2 flex items-center">
                                                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                                Instructions importantes
                                            </h4>
                                            <ul class="text-sm text-gray-700 space-y-1">
                                                <li>• Le document doit être clairement lisible</li>
                                                <li>• Toutes les informations doivent être visibles</li>
                                                <li>• Le document ne doit pas être expiré</li>
                                                <li>• La vérification prend généralement 24-48h</li>
                                            </ul>
                                        </div>
                                        
                                        <div class="flex justify-end">
                                            <button type="submit" 
                                                    class="px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                                                <i class="fas fa-paper-plane mr-2"></i>
                                                Soumettre pour vérification
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Section Sessions -->
                        <div id="section-sessions" class="section-content hidden">
                            <div class="bg-white rounded-2xl shadow-sm p-6">
                                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                                    <i class="fas fa-laptop text-green-600 mr-2"></i>
                                    Sessions actives
                                </h2>
                                
                                <div class="space-y-4">
                                    <?php if (!empty($sessions)): ?>
                                        <?php foreach ($sessions as $session): ?>
                                        <div class="border border-gray-200 rounded-lg p-4">
                                            <div class="flex flex-col md:flex-row md:items-center justify-between">
                                                <div class="flex items-center mb-4 md:mb-0">
                                                    <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center mr-4">
                                                        <i class="fas fa-<?php echo strpos($session['user_agent'] ?? '', 'Mobile') !== false ? 'mobile-alt' : 'laptop'; ?> text-gray-600"></i>
                                                    </div>
                                                    <div>
                                                        <h4 class="font-bold text-gray-800">
                                                            <?php 
                                                            $device = 'Appareil inconnu';
                                                            if (strpos($session['user_agent'] ?? '', 'Windows') !== false) $device = 'Windows PC';
                                                            elseif (strpos($session['user_agent'] ?? '', 'Mac') !== false) $device = 'Mac';
                                                            elseif (strpos($session['user_agent'] ?? '', 'Linux') !== false) $device = 'Linux PC';
                                                            elseif (strpos($session['user_agent'] ?? '', 'iPhone') !== false) $device = 'iPhone';
                                                            elseif (strpos($session['user_agent'] ?? '', 'Android') !== false) $device = 'Android';
                                                            echo htmlspecialchars($device);
                                                            ?>
                                                        </h4>
                                                        <div class="text-sm text-gray-600">
                                                            <p class="mt-1">
                                                                <i class="fas fa-globe mr-1"></i>
                                                                IP: <?php echo htmlspecialchars($session['ip_address'] ?? ''); ?>
                                                            </p>
                                                            <p class="mt-1">
                                                                <i class="fas fa-calendar-alt mr-1"></i>
                                                                Créée: <?php echo date('d/m/Y H:i', strtotime($session['created_at'])); ?>
                                                            </p>
                                                            <p class="mt-1">
                                                                <i class="fas fa-clock mr-1"></i>
                                                                Dernière activité: <?php echo date('d/m/Y H:i', strtotime($session['last_activity'])); ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="flex space-x-2">
                                                    <?php if ($session['id'] != session_id()): ?>
                                                    <button onclick="terminateSession('<?php echo htmlspecialchars($session['id']); ?>')" 
                                                            class="px-3 py-1 border border-red-300 text-red-600 text-sm rounded hover:bg-red-50 transition">
                                                        <i class="fas fa-sign-out-alt mr-1"></i>
                                                        Déconnecter
                                                    </button>
                                                    <?php else: ?>
                                                    <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded">
                                                        <i class="fas fa-check-circle mr-1"></i>
                                                        Session actuelle
                                                    </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center py-8">
                                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                                <i class="fas fa-laptop text-gray-400 text-2xl"></i>
                                            </div>
                                            <p class="text-gray-500">Aucune session active</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <div class="flex flex-col md:flex-row md:items-center justify-between">
                                        <div>
                                            <p class="text-sm text-gray-600">
                                                Nombre de sessions actives: <span class="font-medium"><?php echo count($sessions); ?></span>
                                            </p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                Vous pouvez déconnecter les appareils que vous ne reconnaissez pas
                                            </p>
                                        </div>
                                        <button onclick="terminateAllSessions()" 
                                                class="mt-4 md:mt-0 px-4 py-2 border border-red-300 text-red-600 font-medium rounded-lg hover:bg-red-50 transition">
                                            <i class="fas fa-sign-out-alt mr-2"></i>
                                            Déconnecter toutes les autres sessions
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section Confidentialité -->
                        <div id="section-privacy" class="section-content hidden">
                            <div class="bg-white rounded-2xl shadow-sm p-6">
                                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                                    <i class="fas fa-lock text-green-600 mr-2"></i>
                                    Confidentialité et données
                                </h2>
                                
                                <!-- Politique de confidentialité -->
                                <div class="mb-8">
                                    <h3 class="text-lg font-bold text-gray-800 mb-4">Politique de confidentialité</h3>
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <p class="text-gray-700 mb-3">
                                            Nous prenons votre confidentialité au sérieux. Vos données personnelles sont protégées conformément 
                                            au Règlement Général sur la Protection des Données (RGPD) et aux lois locales.
                                        </p>
                                        <a href="#" class="text-green-600 hover:text-green-700 font-medium">
                                            <i class="fas fa-file-pdf mr-2"></i>
                                            Lire la politique de confidentialité complète
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Export des données -->
                                <div class="mb-8">
                                    <h3 class="text-lg font-bold text-gray-800 mb-4">Export de vos données</h3>
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex flex-col md:flex-row md:items-center justify-between">
                                            <div class="mb-4 md:mb-0">
                                                <h4 class="font-bold text-gray-800">Télécharger vos données</h4>
                                                <p class="text-sm text-gray-600 mt-1">
                                                    Téléchargez toutes vos données personnelles au format JSON
                                                </p>
                                            </div>
                                            <form method="POST">
                                                <input type="hidden" name="action" value="export_data">
                                                <button type="submit" 
                                                        class="px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                                                    <i class="fas fa-download mr-2"></i>
                                                    Exporter mes données
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Partage de données -->
                                <div class="mb-8">
                                    <h3 class="text-lg font-bold text-gray-800 mb-4">Partage de données avec des tiers</h3>
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                            <div>
                                                <h4 class="font-bold text-gray-800">Données analytiques</h4>
                                                <p class="text-sm text-gray-600 mt-1">
                                                    Partage anonyme pour améliorer nos services
                                                </p>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" class="sr-only peer" checked>
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                            </label>
                                        </div>
                                        
                                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                            <div>
                                                <h4 class="font-bold text-gray-800">Partenaires marketing</h4>
                                                <p class="text-sm text-gray-600 mt-1">
                                                    Offres personnalisées de nos partenaires
                                                </p>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" class="sr-only peer" checked>
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Suppression de données -->
                                <div class="pt-6 border-t border-gray-200">
                                    <h3 class="text-lg font-bold text-gray-800 mb-4">Suppression de données</h3>
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                        <h4 class="font-bold text-red-800 mb-2 flex items-center">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                            Supprimer certaines données
                                        </h4>
                                        <p class="text-sm text-red-700 mb-4">
                                            Vous pouvez demander la suppression de certaines données personnelles. 
                                            Cette action est irréversible et peut affecter l'utilisation de certains services.
                                        </p>
                                        <button onclick="openDataDeletionModal()" 
                                                class="px-4 py-2 border border-red-300 text-red-600 font-medium rounded-lg hover:bg-red-100 transition">
                                            <i class="fas fa-trash mr-2"></i>
                                            Demander la suppression de données
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Zone de danger -->
                        <div id="section-danger" class="section-content hidden">
                            <div class="bg-white rounded-2xl shadow-sm p-6">
                                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                                    <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                                    Zone de danger
                                </h2>
                                
                                <div class="space-y-6">
                                    <!-- Désactivation temporaire -->
                                    <div class="border border-yellow-200 rounded-lg p-6 bg-yellow-50">
                                        <div class="flex items-start">
                                            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center mr-4 mt-1">
                                                <i class="fas fa-pause text-yellow-600"></i>
                                            </div>
                                            <div class="flex-1">
                                                <h3 class="text-lg font-bold text-gray-800 mb-2">Désactiver temporairement le compte</h3>
                                                <p class="text-gray-700 mb-4">
                                                    Votre compte sera inaccessible mais vos données seront conservées. 
                                                    Vous pourrez réactiver votre compte à tout moment en vous reconnectant.
                                                </p>
                                                <button onclick="openDeactivateModal()" 
                                                        class="px-4 py-2 border border-yellow-300 text-yellow-700 font-medium rounded-lg hover:bg-yellow-100 transition">
                                                    <i class="fas fa-pause-circle mr-2"></i>
                                                    Désactiver mon compte
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Suppression définitive -->
                                    <div class="border border-red-200 rounded-lg p-6 bg-red-50">
                                        <div class="flex items-start">
                                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-4 mt-1">
                                                <i class="fas fa-trash-alt text-red-600"></i>
                                            </div>
                                            <div class="flex-1">
                                                <h3 class="text-lg font-bold text-gray-800 mb-2">Supprimer définitivement le compte</h3>
                                                <p class="text-red-700 mb-2">
                                                    <strong class="font-bold">ATTENTION : Cette action est irréversible !</strong>
                                                </p>
                                                <ul class="text-sm text-red-700 mb-4 space-y-1">
                                                    <li>• Toutes vos données seront supprimées définitivement</li>
                                                    <li>• Vos investissements actifs seront annulés</li>
                                                    <li>• Votre solde sera perdu</li>
                                                    <li>• Vous ne pourrez pas récupérer votre compte</li>
                                                </ul>
                                                <button onclick="openDeleteModal()" 
                                                        class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition">
                                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                                    Supprimer mon compte
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Avertissement -->
                                    <div class="bg-gray-100 border border-gray-300 rounded-lg p-4">
                                        <div class="flex items-center">
                                            <i class="fas fa-info-circle text-gray-600 text-xl mr-3"></i>
                                            <div>
                                                <h4 class="font-bold text-gray-800 mb-1">Avant de prendre une décision</h4>
                                                <p class="text-sm text-gray-700">
                                                    Si vous rencontrez des problèmes, contactez d'abord notre support technique. 
                                                    Nous sommes là pour vous aider à résoudre tout problème que vous pourriez rencontrer.
                                                </p>
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <a href="mailto:junior1009f@gmail.com" 
                                               class="inline-flex items-center text-green-600 hover:text-green-700 font-medium">
                                                <i class="fas fa-envelope mr-2"></i>
                                                Contacter le support
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal changement d'email -->
    <div id="emailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md transform transition-all">
            <form method="POST">
                <input type="hidden" name="action" value="update_email">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-gray-800">Changer l'adresse email</h3>
                        <button type="button" onclick="closeModal('email')" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nouvel email</label>
                            <input type="email" name="email" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mot de passe actuel</label>
                            <input type="password" name="password" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <p class="text-sm text-blue-700">
                                <i class="fas fa-info-circle mr-2"></i>
                                Un email de confirmation sera envoyé à la nouvelle adresse
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex space-x-3 mt-8">
                        <button type="button" onclick="closeModal('email')" 
                                class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                            Annuler
                        </button>
                        <button type="submit" 
                                class="flex-1 px-4 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                            Changer l'email
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal changement de mot de passe -->
    <div id="passwordModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md transform transition-all">
            <form method="POST">
                <input type="hidden" name="action" value="change_password">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-gray-800">Changer le mot de passe</h3>
                        <button type="button" onclick="closeModal('password')" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mot de passe actuel</label>
                            <input type="password" name="current_password" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nouveau mot de passe</label>
                            <input type="password" name="new_password" required minlength="6"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Confirmer le mot de passe</label>
                            <input type="password" name="confirm_password" required minlength="6"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                        </div>
                        
                        <div class="bg-green-50 p-4 rounded-lg">
                            <p class="text-sm text-green-700">
                                <i class="fas fa-lightbulb mr-2"></i>
                                Votre mot de passe doit contenir au moins 6 caractères
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex space-x-3 mt-8">
                        <button type="button" onclick="closeModal('password')" 
                                class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                            Annuler
                        </button>
                        <button type="submit" 
                                class="flex-1 px-4 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                            Changer le mot de passe
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal désactivation du compte -->
    <div id="deactivateModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md transform transition-all">
            <div class="p-6">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-pause-circle text-yellow-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Désactiver votre compte</h3>
                    <p class="text-gray-600">
                        Votre compte sera inaccessible temporairement. Vous pourrez le réactiver à tout moment.
                    </p>
                </div>
                
                <div class="space-y-4">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <h4 class="font-bold text-yellow-800 mb-2">Ce qui se passera :</h4>
                        <ul class="text-sm text-yellow-700 space-y-1">
                            <li>• Vous ne pourrez plus vous connecter</li>
                            <li>• Vos investissements continueront à générer des gains</li>
                            <li>• Vos données seront conservées</li>
                            <li>• Vous pourrez réactiver votre compte en vous reconnectant</li>
                        </ul>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button onclick="closeModal('deactivate')" 
                                class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                            Annuler
                        </button>
                        <button onclick="confirmDeactivate()" 
                                class="flex-1 px-4 py-3 bg-yellow-600 text-white font-medium rounded-lg hover:bg-yellow-700 transition">
                            <i class="fas fa-pause-circle mr-2"></i>
                            Désactiver
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal suppression du compte -->
    <div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md transform transition-all">
            <form method="POST">
                <input type="hidden" name="action" value="delete_account">
                <div class="p-6">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Supprimer votre compte</h3>
                        <p class="text-gray-600 mb-4">
                            Cette action est irréversible. Toutes vos données seront supprimées définitivement.
                        </p>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <h4 class="font-bold text-red-800 mb-2">Attention :</h4>
                            <ul class="text-sm text-red-700 space-y-1">
                                <li>• Tous vos investissements seront annulés</li>
                                <li>• Votre solde sera perdu</li>
                                <li>• Votre historique sera supprimé</li>
                                <li>• Cette action ne peut pas être annulée</li>
                            </ul>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tapez <span class="font-bold text-red-600">SUPPRIMER MON COMPTE</span> pour confirmer
                            </label>
                            <input type="text" name="confirm_text" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500"
                                   placeholder="SUPPRIMER MON COMPTE">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mot de passe actuel</label>
                            <input type="password" name="password" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500">
                        </div>
                        
                        <div class="flex space-x-3">
                            <button type="button" onclick="closeModal('delete')" 
                                    class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                                Annuler
                            </button>
                            <button type="submit" onclick="return confirm('Êtes-vous ABSOLUMENT SÛR de vouloir supprimer votre compte ?')" 
                                    class="flex-1 px-4 py-3 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition">
                                <i class="fas fa-trash-alt mr-2"></i>
                                Supprimer définitivement
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal suppression de données -->
    <div id="dataDeletionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md transform transition-all">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-800">Demander la suppression de données</h3>
                    <button onclick="closeModal('dataDeletion')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <p class="text-gray-700">
                        Sélectionnez les données que vous souhaitez supprimer :
                    </p>
                    
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" class="mr-3 h-4 w-4 text-green-600">
                            <span class="text-gray-700">Historique des transactions</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="mr-3 h-4 w-4 text-green-600">
                            <span class="text-gray-700">Informations personnelles</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="mr-3 h-4 w-4 text-green-600">
                            <span class="text-gray-700">Données de profil</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="mr-3 h-4 w-4 text-green-600">
                            <span class="text-gray-700">Historique de navigation</span>
                        </label>
                    </div>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-sm text-blue-700">
                            <i class="fas fa-info-circle mr-2"></i>
                            Votre demande sera traitée dans les 30 jours conformément au RGPD
                        </p>
                    </div>
                    
                    <div class="flex space-x-3 mt-6">
                        <button onclick="closeModal('dataDeletion')" 
                                class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                            Annuler
                        </button>
                        <button onclick="submitDataDeletion()" 
                                class="flex-1 px-4 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Envoyer la demande
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Variables
    const userId = <?php echo $user_id; ?>;

    // Navigation entre sections
    function switchSection(sectionId) {
        event.preventDefault();
        
        // Mettre à jour le menu
        document.querySelectorAll('.section-btn').forEach(btn => {
            btn.classList.remove('active', 'bg-green-50', 'text-green-600');
            btn.classList.add('text-gray-700', 'hover:bg-gray-50');
        });
        
        // Activer le bouton cliqué
        const activeBtn = event.currentTarget;
        activeBtn.classList.add('active', 'bg-green-50', 'text-green-600');
        activeBtn.classList.remove('text-gray-700', 'hover:bg-gray-50');
        
        // Masquer toutes les sections
        document.querySelectorAll('.section-content').forEach(section => {
            section.classList.remove('active');
            section.classList.add('hidden');
        });
        
        // Afficher la section correspondante
        document.getElementById('section-' + sectionId).classList.remove('hidden');
        document.getElementById('section-' + sectionId).classList.add('active');
        
        // Mettre à jour l'URL
        history.pushState(null, null, '#' + sectionId);
    }

    // Fonctions pour les modals
    function openModal(modalName) {
        document.getElementById(modalName + 'Modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalName) {
        document.getElementById(modalName + 'Modal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function openEmailModal() {
        openModal('email');
    }

    function openPasswordModal() {
        openModal('password');
    }

    function openDeactivateModal() {
        openModal('deactivate');
    }

    function openDeleteModal() {
        openModal('delete');
    }

    function openDataDeletionModal() {
        openModal('dataDeletion');
    }

    // Gestion des sessions
    function terminateSession(sessionId) {
        if (confirm('Déconnecter cette session ?')) {
            fetch('../api/terminate_session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    session_id: sessionId,
                    user_id: userId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('✅ Session déconnectée avec succès !', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Erreur lors de la déconnexion', 'error');
                }
            });
        }
    }

    function terminateAllSessions() {
        if (confirm('Déconnecter toutes les autres sessions ?')) {
            fetch('../api/terminate_all_sessions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: userId,
                    current_session: '<?php echo session_id(); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('✅ Toutes les autres sessions ont été déconnectées !', 'success');
                    setTimeout(() => location.reload(), 1000);
                }
            });
        }
    }

    function terminateOtherSessions() {
        terminateAllSessions();
    }

    // Gestion des notifications
    function markAllAsRead() {
        fetch('../api/mark_notifications_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('✅ Toutes les notifications marquées comme lues !', 'success');
                setTimeout(() => location.reload(), 1000);
            }
        });
    }

    // Désactivation du compte
    function confirmDeactivate() {
        fetch('../api/deactivate_account.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('✅ Compte désactivé avec succès !', 'success');
                setTimeout(() => {
                    window.location.href = '../logout.php';
                }, 2000);
            }
        });
    }

    // Suppression de données
    function submitDataDeletion() {
        const checkboxes = document.querySelectorAll('#dataDeletionModal input[type="checkbox"]:checked');
        const dataTypes = Array.from(checkboxes).map(cb => cb.nextElementSibling.textContent);
        
        if (dataTypes.length === 0) {
            showToast('Veuillez sélectionner au moins un type de données', 'error');
            return;
        }
        
        fetch('../api/request_data_deletion.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userId,
                data_types: dataTypes
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('✅ Demande de suppression envoyée !', 'success');
                closeModal('dataDeletion');
            }
        });
    }

    // Fonction utilitaire pour les notifications
    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-medium animate-slideIn ${
            type === 'success' ? 'bg-green-600' : 'bg-red-600'
        }`;
        toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle mr-2"></i>
                ${message}
            </div>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('animate-slideOut');
            setTimeout(() => {
                if (toast.parentNode) {
                    document.body.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }

    // Initialisation
    document.addEventListener('DOMContentLoaded', function() {
        // Vérifier l'URL hash pour la section
        const hash = window.location.hash.substring(1);
        if (hash) {
            const sectionBtn = document.querySelector(`a[href="#${hash}"]`);
            if (sectionBtn) {
                sectionBtn.click();
            }
        }
        
        // Auto-hide les messages d'erreur/succès
        setTimeout(() => {
            const alerts = document.querySelectorAll('.fixed.top-4.right-4');
            alerts.forEach(alert => {
                alert.classList.add('animate-slideOut');
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.parentNode.removeChild(alert);
                    }
                }, 300);
            });
        }, 5000);
        
        // Validation des formulaires
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const action = this.querySelector('[name="action"]')?.value;
                
                // Validation spécifique pour la suppression de compte
                if (action === 'delete_account') {
                    const confirmText = this.querySelector('[name="confirm_text"]').value;
                    const password = this.querySelector('[name="password"]').value;
                    
                    if (confirmText !== 'SUPPRIMER MON COMPTE') {
                        e.preventDefault();
                        showToast('Veuillez taper exactement "SUPPRIMER MON COMPTE"', 'error');
                        return;
                    }
                    
                    if (!password) {
                        e.preventDefault();
                        showToast('Veuillez entrer votre mot de passe', 'error');
                        return;
                    }
                }
                
                // Validation pour l'ajout de méthode de paiement
                if (action === 'add_payment_method') {
                    const method = this.querySelector('[name="method"]').value;
                    const accountNumber = this.querySelector('[name="account_number"]').value;
                    
                    if (!method) {
                        e.preventDefault();
                        showToast('Veuillez sélectionner un type de méthode', 'error');
                        return;
                    }
                    
                    if (!accountNumber) {
                        e.preventDefault();
                        showToast('Veuillez entrer un numéro de compte', 'error');
                        return;
                    }
                }
                
                // Validation pour le KYC
                if (action === 'upload_kyc') {
                    const fileInput = this.querySelector('[name="kyc_document"]');
                    if (fileInput.files.length === 0) {
                        e.preventDefault();
                        showToast('Veuillez sélectionner un document', 'error');
                        return;
                    }
                }
            });
        });
    });

    // Animation CSS
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .animate-slideIn {
            animation: slideIn 0.3s ease-out;
        }
        
        .animate-slideOut {
            animation: slideOut 0.3s ease-in;
        }
        
        .section-content {
            display: none;
            animation: fadeIn 0.3s ease-out;
        }
        
        .section-content.active {
            display: block;
        }
        
        #emailModal > div,
        #passwordModal > div,
        #deactivateModal > div,
        #deleteModal > div,
        #dataDeletionModal > div {
            animation: modalFadeIn 0.3s ease-out;
        }
        
        /* Scrollbar personnalisée */
        .overflow-y-auto::-webkit-scrollbar {
            width: 6px;
        }
        
        .overflow-y-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        
        .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }
    `;
    document.head.appendChild(style);

    // Fermer les modals en cliquant à l'extérieur
    document.querySelectorAll('.fixed.inset-0').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                const modalId = modal.id.replace('Modal', '');
                closeModal(modalId);
            }
        });
    });
    </script>
</body>
</html>