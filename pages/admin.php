<?php
// ====================================================
// FICHIER: admin_plans.php
// DESCRIPTION: Interface d'administration des plans d'investissement
// ====================================================

// 1. Sécurité - Vérifier si l'utilisateur est admin

// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header('Location: admin_login.php');
//     exit();
// }

// 2. Connexion à la base de données
require_once 'config/db.php';
$db = Database::getInstance()->getConnection();

// 3. GESTION DES ACTIONS
$message = '';
$message_type = ''; // success, error, warning

// A. AJOUTER UN NOUVEAU PLAN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    try {
        $sql = "INSERT INTO plans (nom, prix, roi_journalier, duree_jours, videos_par_jour, gain_par_video, description, image) 
                VALUES (:nom, :prix, :roi_journalier, :duree_jours, :videos_par_jour, :gain_par_video, :description, :image)";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':nom' => $_POST['nom'],
            ':prix' => $_POST['prix'],
            ':roi_journalier' => $_POST['roi_journalier'],
            ':duree_jours' => $_POST['duree_jours'],
            ':videos_par_jour' => $_POST['videos_par_jour'],
            ':gain_par_video' => $_POST['gain_par_video'],
            ':description' => $_POST['description'],
            ':image' => $_POST['image'] ?? null
        ]);

        $message = "✅ Plan ajouté avec succès!";
        $message_type = "success";

    } catch (Exception $e) {
        $message = "❌ Erreur: " . $e->getMessage();
        $message_type = "error";
    }
}

// B. MODIFIER UN PLAN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    try {
        $sql = "UPDATE plans SET 
                nom = :nom,
                prix = :prix,
                roi_journalier = :roi_journalier,
                duree_jours = :duree_jours,
                videos_par_jour = :videos_par_jour,
                gain_par_video = :gain_par_video,
                actif = :actif,
                description = :description,
                image = :image
                WHERE id = :id";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':id' => $_POST['plan_id'],
            ':nom' => $_POST['nom'],
            ':prix' => $_POST['prix'],
            ':roi_journalier' => $_POST['roi_journalier'],
            ':duree_jours' => $_POST['duree_jours'],
            ':videos_par_jour' => $_POST['videos_par_jour'],
            ':gain_par_video' => $_POST['gain_par_video'],
            ':actif' => isset($_POST['actif']) ? 1 : 0,
            ':description' => $_POST['description'],
            ':image' => $_POST['image'] ?? null
        ]);

        $message = "✅ Plan modifié avec succès!";
        $message_type = "success";

    } catch (Exception $e) {
        $message = "❌ Erreur: " . $e->getMessage();
        $message_type = "error";
    }
}

// C. SUPPRIMER UN PLAN
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        // Vérifier d'abord si des utilisateurs ont ce plan
        $checkSql = "SELECT COUNT(*) as count FROM user_plans WHERE plan_id = :id";
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->execute([':id' => $_GET['id']]);
        $result = $checkStmt->fetch();

        if ($result['count'] > 0) {
            // Désactiver au lieu de supprimer
            $sql = "UPDATE plans SET actif = 0 WHERE id = :id";
            $message = "⚠️ Plan désactivé (des utilisateurs y sont inscrits)";
        } else {
            // Supprimer complètement
            $sql = "DELETE FROM plans WHERE id = :id";
            $message = "✅ Plan supprimé avec succès!";
        }

        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $_GET['id']]);
        $message_type = "warning";

    } catch (Exception $e) {
        $message = "❌ Erreur: " . $e->getMessage();
        $message_type = "error";
    }
}

// D. RÉCUPÉRER TOUS LES PLANS
$sql = "SELECT * FROM plans ORDER BY prix ASC";
$plans = $db->query($sql)->fetchAll();

// E. RÉCUPÉRER UN PLAN POUR ÉDITION
$planToEdit = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $sql = "SELECT * FROM plans WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $_GET['id']]);
    $planToEdit = $stmt->fetch();
}

// 4. HTML DE LA PAGE
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestion des Plans</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .form-input {
            transition: all 0.3s;
        }

        .form-input:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .badge-active {
            background-color: #10b981;
        }

        .badge-inactive {
            background-color: #ef4444;
        }
    </style>
</head>

<body class="bg-gray-100">

    <div class="flex">
        <!-- Sidebar -->
        <div class="sidebar bg-gray-800 text-white w-64">
            <div class="p-6">
                <h1 class="text-2xl font-bold">
                    <i class="fas fa-chart-line mr-2"></i>FreeCash Admin
                </h1>
                <p class="text-gray-400 text-sm mt-2">Gestion des plans</p>
            </div>

            <nav class="mt-6">
                <a href="?page=admin_dashboard" class="nav-link block py-3 px-6 hover:bg-gray-700">
                    <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                </a>
                <a href="?page=admin" class="nav-link block py-3 px-6 bg-blue-600">
                    <i class="fas fa-coins mr-3"></i>Plans d'investissement
                </a>
                <a href="?page=admin_users" class="nav-link block py-3 px-6 hover:bg-gray-700">
                    <i class="fas fa-users mr-3"></i>Utilisateurs
                </a>
                <a href="?page=admin_transactions" class="nav-link block py-3 px-6 hover:bg-gray-700">
                    <i class="fas fa-exchange-alt mr-3"></i>Transactions
                </a>
                <a href="?page=logout" class="nav-link block py-3 px-6 hover:bg-gray-700 mt-10">
                    <i class="fas fa-sign-out-alt mr-3"></i>Déconnexion
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800">
                    <i class="fas fa-coins mr-3"></i>Gestion des Plans
                </h1>
                <button onclick="document.getElementById('addPlanModal').classList.remove('hidden')"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium">
                    <i class="fas fa-plus mr-2"></i>Ajouter un Plan
                </button>
            </div>

            <!-- Messages -->
            <?php if ($message): ?>
                <div
                    class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800' : ($message_type === 'error' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Liste des Plans -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-xl font-semibold text-gray-800">Liste des Plans (<?php echo count($plans); ?>)</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ID</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nom</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Prix (FCFA)</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ROI/jour</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Durée</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Statut</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($plans as $plan): ?>
                                <?php
                                $rendement_total = $plan['roi_journalier'] * $plan['duree_jours'];
                                $profit_net = $rendement_total - $plan['prix'];
                                $taux_rendement = ($profit_net / $plan['prix']) * 100;
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        #<?php echo $plan['id']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($plan['nom']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo substr($plan['description'] ?? 'Sans description', 0, 50); ?>...
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-lg font-bold text-green-600">
                                            <?php echo number_format($plan['prix'], 0, ',', ' '); ?> FCFA
                                        </div>
                                        <div class="text-xs text-gray-500">Rendement:
                                            <?php echo number_format($taux_rendement, 1); ?>%
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div><?php echo number_format($plan['roi_journalier'], 0, ',', ' '); ?> FCFA/jour
                                        </div>
                                        <div class="text-xs text-gray-500">Total:
                                            <?php echo number_format($rendement_total, 0, ',', ' '); ?> F
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div><?php echo $plan['duree_jours']; ?> jours</div>
                                        <div class="text-xs text-gray-500"><?php echo $plan['videos_par_jour']; ?>
                                            vidéos/jour</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $plan['actif'] ? 'badge-active text-white' : 'badge-inactive text-white'; ?>">
                                            <?php echo $plan['actif'] ? 'Actif' : 'Inactif'; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="?page=admin&action=edit&id=<?php echo $plan['id']; ?>"
                                            class="text-blue-600 hover:text-blue-900 mr-4">
                                            <i class="fas fa-edit"></i> Modifier
                                        </a>
                                        <a href="?page=admin&action=delete&id=<?php echo $plan['id']; ?>"
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce plan?')"
                                            class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($plans)): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                        <i class="fas fa-coins text-4xl mb-4"></i>
                                        <p class="text-lg">Aucun plan disponible</p>
                                        <p class="text-sm mt-2">Commencez par ajouter un plan d'investissement</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-8">
                <div class="bg-white p-6 rounded-xl shadow">
                    <div class="flex items-center">
                        <div class="bg-blue-100 p-3 rounded-lg">
                            <i class="fas fa-coins text-blue-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500">Plans actifs</p>
                            <p class="text-2xl font-bold">
                                <?php echo count(array_filter($plans, fn($p) => $p['actif'])); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow">
                    <div class="flex items-center">
                        <div class="bg-green-100 p-3 rounded-lg">
                            <i class="fas fa-money-bill-wave text-green-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500">Plancher prix</p>
                            <p class="text-2xl font-bold">
                                <?php echo count($plans) > 0 ? number_format(min(array_column($plans, 'prix')), 0, ',', ' ') : '0'; ?>
                                F
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow">
                    <div class="flex items-center">
                        <div class="bg-purple-100 p-3 rounded-lg">
                            <i class="fas fa-chart-line text-purple-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500">Plafond prix</p>
                            <p class="text-2xl font-bold">
                                <?php echo count($plans) > 0 ? number_format(max(array_column($plans, 'prix')), 0, ',', ' ') : '0'; ?>
                                F
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow">
                    <div class="flex items-center">
                        <div class="bg-yellow-100 p-3 rounded-lg">
                            <i class="fas fa-video text-yellow-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500">Vidéos moyennes</p>
                            <p class="text-2xl font-bold">
                                <?php echo count($plans) > 0 ? round(array_sum(array_column($plans, 'videos_par_jour')) / count($plans), 1) : '0'; ?>/jour
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL AJOUTER/MODIFIER PLAN -->
    <div id="addPlanModal"
        class="<?php echo $planToEdit ? '' : 'hidden'; ?> fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="px-8 py-6 border-b">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-<?php echo $planToEdit ? 'edit' : 'plus'; ?> mr-2"></i>
                        <?php echo $planToEdit ? 'Modifier le Plan' : 'Ajouter un Nouveau Plan'; ?>
                    </h2>
                    <button onclick="document.getElementById('addPlanModal').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600 text-2xl">
                        &times;
                    </button>
                </div>
            </div>

            <form method="POST" action="?page=admin" class="p-8">
                <input type="hidden" name="action" value="<?php echo $planToEdit ? 'update' : 'add'; ?>">
                <?php if ($planToEdit): ?>
                    <input type="hidden" name="plan_id" value="<?php echo $planToEdit['id']; ?>">
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nom du Plan -->
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nom du Plan *
                        </label>
                        <input type="text" name="nom" required value="<?php echo $planToEdit['nom'] ?? ''; ?>"
                            placeholder="Ex: Plan Bronze, Plan VIP, etc."
                            class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Prix -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Prix d'investissement (FCFA) *
                        </label>
                        <input type="number" name="prix" required min="0" step="100"
                            value="<?php echo $planToEdit['prix'] ?? '4000'; ?>" placeholder="4000"
                            class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg">
                    </div>

                    <!-- ROI Journalier -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            ROI Journalier (FCFA) *
                        </label>
                        <input type="number" name="roi_journalier" required min="0" step="10"
                            value="<?php echo $planToEdit['roi_journalier'] ?? '200'; ?>" placeholder="200"
                            class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg">
                    </div>

                    <!-- Durée en Jours -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Durée (jours) *
                        </label>
                        <input type="number" name="duree_jours" required min="1" max="365"
                            value="<?php echo $planToEdit['duree_jours'] ?? '30'; ?>" placeholder="30"
                            class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg">
                    </div>

                    <!-- Vidéos par Jour -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Vidéos par jour
                        </label>
                        <input type="number" name="videos_par_jour" min="0"
                            value="<?php echo $planToEdit['videos_par_jour'] ?? '0'; ?>" placeholder="0"
                            class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg">
                    </div>

                    <!-- Gain par Vidéo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Gain par vidéo (FCFA)
                        </label>
                        <input type="number" name="gain_par_video" min="0" step="10"
                            value="<?php echo $planToEdit['gain_par_video'] ?? '0'; ?>" placeholder="0"
                            class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg">
                    </div>

                    <!-- URL Image -->
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            URL de l'image (optionnel)
                        </label>
                        <input type="url" name="image" value="<?php echo $planToEdit['image'] ?? ''; ?>"
                            placeholder="https://example.com/image.jpg"
                            class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg">
                        <p class="text-xs text-gray-500 mt-1">Laissez vide pour utiliser l'image par défaut</p>
                    </div>

                    <!-- Description -->
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Description
                        </label>
                        <textarea name="description" rows="3" placeholder="Description détaillée du plan..."
                            class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg"><?php echo $planToEdit['description'] ?? ''; ?></textarea>
                    </div>

                    <!-- Actif (uniquement pour modification) -->
                    <?php if ($planToEdit): ?>
                        <div class="col-span-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="actif" <?php echo $planToEdit['actif'] ? 'checked' : ''; ?>
                                    class="h-5 w-5 text-blue-600 rounded">
                                <span class="ml-2 text-gray-700">Plan actif (visible pour les utilisateurs)</span>
                            </label>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Boutons -->
                <div class="mt-8 flex justify-end space-x-4">
                    <button type="button" onclick="document.getElementById('addPlanModal').classList.add('hidden')"
                        class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="submit"
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">
                        <i class="fas fa-<?php echo $planToEdit ? 'save' : 'plus'; ?> mr-2"></i>
                        <?php echo $planToEdit ? 'Enregistrer' : 'Ajouter le Plan'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Afficher le modal si on a un plan à éditer
        <?php if ($planToEdit): ?>
            document.addEventListener('DOMContentLoaded', function () {
                document.getElementById('addPlanModal').classList.remove('hidden');
            });
        <?php endif; ?>

        // Calcul automatique du rendement total
        document.querySelectorAll('input[name="prix"], input[name="roi_journalier"], input[name="duree_jours"]').forEach(input => {
            input.addEventListener('input', function () {
                const prix = parseFloat(document.querySelector('input[name="prix"]').value) || 0;
                const roi = parseFloat(document.querySelector('input[name="roi_journalier"]').value) || 0;
                const duree = parseFloat(document.querySelector('input[name="duree_jours"]').value) || 0;

                if (prix > 0 && duree > 0) {
                    const rendementTotal = roi * duree;
                    const profitNet = rendementTotal - prix;
                    const taux = prix > 0 ? (profitNet / prix) * 100 : 0;

                    // Afficher un aperçu (optionnel)
                    const preview = document.getElementById('preview');
                    if (!preview) {
                        const div = document.createElement('div');
                        div.id = 'preview';
                        div.className = 'mt-4 p-4 bg-gray-50 rounded-lg text-sm';
                        div.innerHTML = `
                            <strong>Aperçu:</strong><br>
                            Investissement: ${prix.toLocaleString()} FCFA<br>
                            Rendement total: ${rendementTotal.toLocaleString()} FCFA<br>
                            Profit net: ${profitNet.toLocaleString()} FCFA (${taux.toFixed(1)}%)
                        `;
                        document.querySelector('form').insertBefore(div, document.querySelector('form button[type="submit"]'));
                    } else {
                        preview.innerHTML = `
                            <strong>Aperçu:</strong><br>
                            Investissement: ${prix.toLocaleString()} FCFA<br>
                            Rendement total: ${rendementTotal.toLocaleString()} FCFA<br>
                            Profit net: ${profitNet.toLocaleString()} FCFA (${taux.toFixed(1)}%)
                        `;
                    }
                }
            });
        });
    </script>
</body>

</html>