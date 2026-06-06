<?php
// Vérifier si l'utilisateur est superadmin

if($_SESSION['admin_role'] !== 'superadmin') {
    echo '<div class="p-6">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Accès refusé. Seuls les super administrateurs peuvent gérer les administrateurs.
        </div>
    </div>';
    exit;
}

// Récupérer tous les administrateurs
$admins = $pdo->query("SELECT * FROM admin_users ORDER BY role, created_at DESC")->fetchAll();

// Actions
if(isset($_POST['action'])) {
    if($_POST['action'] == 'add_admin') {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];
        
        // Vérifier si le nom d'utilisateur existe déjà
        $check = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE username = ?");
        $check->execute([$username]);
        
        if($check->fetchColumn() > 0) {
            $error = "Ce nom d'utilisateur existe déjà";
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO admin_users (username, password, role, email, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([$username, $password, $role, $email]);
            $success = "Administrateur ajouté avec succès";
        }
        
    } elseif($_POST['action'] == 'edit_admin') {
        $admin_id = (int)$_POST['admin_id'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        
        // Si un nouveau mot de passe est fourni
        if(!empty($_POST['new_password'])) {
            $password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                UPDATE admin_users 
                SET email = ?, role = ?, password = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$email, $role, $password, $admin_id]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE admin_users 
                SET email = ?, role = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$email, $role, $admin_id]);
        }
        
        $success = "Administrateur modifié avec succès";
        
    } elseif($_POST['action'] == 'delete_admin') {
        $admin_id = (int)$_POST['admin_id'];
        
        // Ne pas permettre de supprimer son propre compte
        if($admin_id == $_SESSION['admin_id']) {
            $error = "Vous ne pouvez pas supprimer votre propre compte";
        } else {
            $pdo->prepare("DELETE FROM admin_users WHERE id = ?")->execute([$admin_id]);
            $success = "Administrateur supprimé avec succès";
        }
    }
    
    header("Refresh:0");
    exit;
}
?>

<div class="p-4 lg:p-6">
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center mb-6 gap-4">
        <div>
            <h3 class="text-xl font-bold">Gestion des Administrateurs</h3>
            <p class="text-gray-600">Gérez les accès au panneau d'administration</p>
        </div>
        <button onclick="openAddAdminModal()" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
            <i class="fas fa-user-plus mr-2"></i>Ajouter un Admin
        </button>
    </div>

    <?php if(isset($success)): ?>
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            <i class="fas fa-check-circle mr-2"></i>
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if(isset($error)): ?>
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Tableau des administrateurs -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Utilisateur</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rôle</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dernière connexion</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Inscription</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach($admins as $admin): 
                        $role_colors = [
                            'superadmin' => 'bg-purple-100 text-purple-800',
                            'support' => 'bg-blue-100 text-blue-800',
                            'finance' => 'bg-green-100 text-green-800',
                            'moderator' => 'bg-yellow-100 text-yellow-800'
                        ];
                    ?>
                    <tr>
                        <td class="px-4 lg:px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-user-shield text-gray-500"></i>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($admin['username']) ?>
                                        <?php if($admin['id'] == $_SESSION['admin_id']): ?>
                                            <span class="ml-2 text-xs text-blue-600">(Vous)</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 lg:px-6 py-4 text-sm text-gray-900">
                            <?= htmlspecialchars($admin['email'] ?? 'Non spécifié') ?>
                        </td>
                        <td class="px-4 lg:px-6 py-4">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $role_colors[$admin['role']] ?? 'bg-gray-100' ?>">
                                <?= $admin['role'] ?>
                            </span>
                        </td>
                        <td class="px-4 lg:px-6 py-4 text-sm text-gray-500">
                            <?= $admin['last_login'] ? date('d/m/Y H:i', strtotime($admin['last_login'])) : 'Jamais' ?>
                        </td>
                        <td class="px-4 lg:px-6 py-4 text-sm text-gray-500">
                            <?= date('d/m/Y', strtotime($admin['created_at'])) ?>
                        </td>
                        <td class="px-4 lg:px-6 py-4">
                            <div class="flex space-x-2">
                                <!-- Modifier -->
                                <button onclick="openEditAdminModal(<?= $admin['id'] ?>, '<?= htmlspecialchars(addslashes($admin['username'])) ?>', '<?= htmlspecialchars(addslashes($admin['email'] ?? '')) ?>', '<?= $admin['role'] ?>')"
                                        class="text-blue-600 hover:text-blue-900" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <!-- Supprimer -->
                                <?php if($admin['id'] != $_SESSION['admin_id']): ?>
                                    <form method="POST" class="inline" onsubmit="return confirm('Supprimer cet administrateur ?')">
                                        <input type="hidden" name="admin_id" value="<?= $admin['id'] ?>">
                                        <input type="hidden" name="action" value="delete_admin">
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal pour ajouter/modifier un administrateur -->
<div id="adminModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 id="adminModalTitle" class="text-lg font-semibold">Ajouter un Administrateur</h3>
                <button onclick="closeAdminModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="adminForm" method="POST">
                <input type="hidden" id="admin_id" name="admin_id">
                <input type="hidden" id="form_action" name="action" value="add_admin">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nom d'utilisateur *</label>
                    <input type="text" id="admin_username" name="username" required
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                           <?= isset($_POST['admin_id']) ? 'readonly' : '' ?>>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="email"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rôle *</label>
                    <select name="role" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="support">Support</option>
                        <option value="finance">Finance</option>
                        <option value="moderator">Modérateur</option>
                        <option value="superadmin">Super Admin</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <span id="password_label">Mot de passe *</span>
                        <span id="optional_note" class="text-xs text-gray-500 hidden"> (Laissez vide pour ne pas modifier)</span>
                    </label>
                    <input type="password" id="admin_password" name="password" required
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div id="confirm_password_field" class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirmer le mot de passe *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeAdminModal()"
                            class="px-4 py-2 border rounded-lg hover:bg-gray-100">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                        <i class="fas fa-save mr-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddAdminModal() {
    document.getElementById('adminModalTitle').textContent = 'Ajouter un Administrateur';
    document.getElementById('adminForm').reset();
    document.getElementById('form_action').value = 'add_admin';
    document.getElementById('admin_id').value = '';
    document.getElementById('admin_username').readOnly = false;
    document.getElementById('password_label').textContent = 'Mot de passe *';
    document.getElementById('optional_note').classList.add('hidden');
    document.getElementById('admin_password').required = true;
    document.getElementById('confirm_password_field').style.display = 'block';
    document.getElementById('admin_password').name = 'password';
    document.getElementById('adminModal').classList.remove('hidden');
}

function openEditAdminModal(adminId, username, email, role) {
    document.getElementById('adminModalTitle').textContent = 'Modifier l\'Administrateur';
    document.getElementById('admin_id').value = adminId;
    document.getElementById('admin_username').value = username;
    document.querySelector('input[name="email"]').value = email;
    document.querySelector('select[name="role"]').value = role;
    document.getElementById('form_action').value = 'edit_admin';
    document.getElementById('admin_username').readOnly = true;
    document.getElementById('password_label').textContent = 'Nouveau mot de passe';
    document.getElementById('optional_note').classList.remove('hidden');
    document.getElementById('admin_password').required = false;
    document.getElementById('confirm_password_field').style.display = 'none';
    document.getElementById('admin_password').name = 'new_password';
    document.getElementById('adminModal').classList.remove('hidden');
}

function closeAdminModal() {
    document.getElementById('adminModal').classList.add('hidden');
}

// Validation du formulaire
document.getElementById('adminForm').addEventListener('submit', function(e) {
    const password = document.getElementById('admin_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const formAction = document.getElementById('form_action').value;
    
    if(formAction === 'add_admin') {
        if(password !== confirmPassword) {
            e.preventDefault();
            alert('Les mots de passe ne correspondent pas');
            return false;
        }
        
        if(password.length < 6) {
            e.preventDefault();
            alert('Le mot de passe doit contenir au moins 6 caractères');
            return false;
        }
    }
    
    if(formAction === 'edit_admin' && password !== '') {
        if(password.length < 6) {
            e.preventDefault();
            alert('Le mot de passe doit contenir au moins 6 caractères');
            return false;
        }
    }
    
    return true;
});
</script>