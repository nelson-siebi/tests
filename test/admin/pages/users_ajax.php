<?php
// users_ajax.php
$page_title = 'Gestion des Utilisateurs';
$page_description = 'Gérez les utilisateurs de la plateforme';
$reload_url = 'partials/users_table.php';

// Récupérer les utilisateurs
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$limit = 20;

$where = [];
$params = [];

if($search) {
    $where[] = "(u.nom LIKE ? OR u.prenom LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
}

if($status) {
    $where[] = "u.statut = ?";
    $params[] = $status;
}

$where_clause = $where ? "WHERE " . implode(" AND ", $where) : "";

// Compter
$count_sql = "SELECT COUNT(*) FROM users u $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_users = $count_stmt->fetchColumn();

// Récupérer les utilisateurs
$sql = "SELECT u.*, 
               w.solde_investissement, w.solde_publicite, w.solde_parrainage,
               (SELECT COUNT(*) FROM user_plans WHERE user_id = u.id) as total_investments
        FROM users u
        LEFT JOIN wallets w ON u.id = w.user_id
        $where_clause
        ORDER BY u.created_at DESC
        LIMIT $limit OFFSET " . (($page - 1) * $limit);

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Bouton d'action
$action_button = '
    <div class="flex space-x-3">
        <div class="relative">
            <input type="text" id="user-search" placeholder="Rechercher..." 
                   class="pl-10 pr-4 py-2 border rounded-lg">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
        </div>
        <button onclick="exportUsers()" class="px-4 py-2 border rounded-lg hover:bg-gray-100">
            <i class="fas fa-download mr-2"></i>Exporter
        </button>
    </div>
';

// Inclure le template
include 'template_ajax.php';
?>

<!-- Contenu spécifique -->
<div class="mb-6">
    <!-- Filtres -->
    <div class="bg-white p-4 rounded-lg shadow mb-4">
        <form id="filter-form" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                <select name="status" class="w-full px-3 py-2 border rounded-lg" onchange="loadUsers()">
                    <option value="">Tous les statuts</option>
                    <option value="active" <?= $status == 'active' ? 'selected' : '' ?>>Actif</option>
                    <option value="banned" <?= $status == 'banned' ? 'selected' : '' ?>>Banni</option>
                    <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>En attente</option>
                </select>
            </div>
            <!-- Autres filtres... -->
        </form>
    </div>

    <!-- Table des utilisateurs -->
    <div id="users-table-container">
        <?php include 'partials/users_table.php'; ?>
    </div>
</div>

<!-- Modal d'édition d'utilisateur -->
<div id="edit-user-modal" class="modal hidden">
    <!-- Contenu du modal -->
</div>

<script>
// Charger les données initiales
function loadInitialData() {
    loadUsers();
}

// Charger les utilisateurs avec AJAX
async function loadUsers() {
    const search = document.getElementById('user-search')?.value || '';
    const status = document.querySelector('select[name="status"]')?.value || '';
    const page = new URLSearchParams(window.location.search).get('p') || 1;
    
    await adminAPI.updateTable('partials/users_table.php', 'users-table-container', {
        search: search,
        status: status,
        p: page
    });
}

// Gérer la recherche
let searchTimeout;
document.getElementById('user-search')?.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadUsers();
    }, 500);
});

// Exporter les utilisateurs
async function exportUsers() {
    const search = document.getElementById('user-search')?.value || '';
    const status = document.querySelector('select[name="status"]')?.value || '';
    
    window.location.href = `export_users.php?search=${encodeURIComponent(search)}&status=${status}`;
}

// Modifier un utilisateur
async function editUser(userId) {
    const user = await adminAPI.getUser(userId);
    if (user) {
        showEditModal(user);
    }
}

// Afficher le modal d'édition
function showEditModal(user) {
    const modal = document.getElementById('edit-user-modal');
    modal.innerHTML = `
        <div class="modal-content">
            <h3 class="text-lg font-semibold mb-4">Modifier l'utilisateur</h3>
            <form class="ajax-form" data-action="update_user">
                <input type="hidden" name="user_id" value="${user.id}">
                <!-- Champs du formulaire -->
            </form>
        </div>
    `;
    modal.style.display = 'block';
}

// Changer le statut d'un utilisateur
async function changeUserStatus(userId, newStatus) {
    if (await adminAPI.confirmDialog(`Changer le statut de cet utilisateur en "${newStatus}" ?`)) {
        await adminAPI.updateUserStatus(userId, newStatus);
        loadUsers(); // Recharger la table
    }
}
</script>