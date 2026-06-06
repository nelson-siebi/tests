<?php
// Récupérer tous les utilisateurs avec pagination
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;
$limit=(int)$limit;
$offset=(int)$offset;

// Recherche et filtres
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$where = [];
$params = [];

if($search) {
    $where[] = "(nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

if($status_filter) {
    $where[] = "statut = ?";
    $params[] = $status_filter;
}

$where_clause = $where ? "WHERE " . implode(" AND ", $where) : "";

// Compter le total
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM users $where_clause");
$count_stmt->execute($params);
$total_users = $count_stmt->fetchColumn();
$total_pages = ceil($total_users / $limit);

// Récupérer les utilisateurs
// Récupérer les utilisateurs
$sql = "
    SELECT u.*, w.solde_investissement, w.solde_publicite, w.solde_parrainage,
           (SELECT COUNT(*) FROM user_plans WHERE user_id = u.id AND statut = 'active') as active_investments
    FROM users u
    LEFT JOIN wallets w ON u.id = w.user_id
    $where_clause
    ORDER BY u.created_at DESC
    LIMIT $limit OFFSET $offset
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();


// $params[] = $limit;
// $params[] = $offset;
// $stmt->execute($params);
// $users = $stmt->fetchAll();

// Traitement des actions
if(isset($_POST['action'])) {
    $user_id = (int)$_POST['user_id'];
    
    switch($_POST['action']) {
        case 'update_balance':
            $type = $_POST['balance_type'];
            $amount = (float)$_POST['amount'];
            $operation = $_POST['operation'];
            
            // Récupérer le solde actuel
            $wallet = $pdo->query("SELECT * FROM wallets WHERE user_id = $user_id")->fetch();
            
            if($operation == 'add') {
                $new_balance = $wallet[$type] + $amount;
            } else {
                $new_balance = max(0, $wallet[$type] - $amount);
            }
            
            $pdo->prepare("UPDATE wallets SET $type = ? WHERE user_id = ?")
                ->execute([$new_balance, $user_id]);
            
            // Ajouter une transaction
            $pdo->prepare("
                INSERT INTO transactions (user_id, type, source, montant, methode, statut, reference, note)
                VALUES (?, 'depot', 'autre', ?, 'autre', 'success', ?, ?)
            ")->execute([
                $user_id,
                $amount,
                'ADJUST_' . uniqid(),
                "Ajustement administratif: " . ($operation == 'add' ? '+' : '-') . " $amount FCFA"
            ]);
            
            $success = "Solde mis à jour avec succès";
            break;
            
        case 'change_status':
            $new_status = $_POST['new_status'];
            $pdo->prepare("UPDATE users SET statut = ? WHERE id = ?")
                ->execute([$new_status, $user_id]);
            $success = "Statut utilisateur mis à jour";
            break;
    }
}
?>

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold">Gestion des Utilisateurs</h3>
        <div class="flex space-x-3">
            <a href="#" onclick="exportUsers()" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                <i class="fas fa-download mr-2"></i>Exporter
            </a>
            <button onclick="openUserModal()" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                <i class="fas fa-plus mr-2"></i>Ajouter Utilisateur
            </button>
        </div>
    </div>

    <?php if(isset($success)): ?>
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            <i class="fas fa-check-circle mr-2"></i>
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <!-- Filtres et recherche -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <form method="GET" class="flex flex-wrap gap-4">
            <input type="hidden" name="page" value="users">
            
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                       placeholder="Rechercher par nom, email, téléphone..."
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <select name="status" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Tous les statuts</option>
                    <option value="active" <?= $status_filter == 'active' ? 'selected' : '' ?>>Actif</option>
                    <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>En attente</option>
                    <option value="banned" <?= $status_filter == 'banned' ? 'selected' : '' ?>>Banni</option>
                </select>
            </div>
            
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                <i class="fas fa-search mr-2"></i>Filtrer
            </button>
            
            <?php if($search || $status_filter): ?>
                <a href="?page=users" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                    <i class="fas fa-times mr-2"></i>Effacer
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Tableau des utilisateurs -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisateur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Informations</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Soldes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach($users as $user): ?>
                    <tr>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-user text-gray-500"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?>
                                    </div>
                                    <div class="text-sm text-gray-500">ID: <?= $user['id'] ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm">
                                <div class="font-medium"><?= htmlspecialchars($user['email']) ?></div>
                                <div><?= htmlspecialchars($user['phone']) ?></div>
                                <div class="text-gray-500">
                                    Inscrit le <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                                </div>
                                <div class="text-blue-500">
                                    Réf: <?= htmlspecialchars($user['referral_code']) ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="space-y-1">
                                <div class="text-sm">
                                    <span class="font-medium">Investissement:</span>
                                    <?= number_format($user['solde_investissement'] ?? 0, 2) ?> FCFA
                                </div>
                                <div class="text-sm">
                                    <span class="font-medium">Publicité:</span>
                                    <?= number_format($user['solde_publicite'] ?? 0, 2) ?> FCFA
                                </div>
                                <div class="text-sm">
                                    <span class="font-medium">Parrainage:</span>
                                    <?= number_format($user['solde_parrainage'] ?? 0, 2) ?> FCFA
                                </div>
                                <div class="text-sm text-green-600 font-medium">
                                    <i class="fas fa-chart-line mr-1"></i>
                                    <?= $user['active_investments'] ?> invest. actifs
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?= $user['statut'] == 'active' ? 'bg-green-100 text-green-800' : 
                                   ($user['statut'] == 'banned' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') ?>">
                                <?= $user['statut'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex space-x-2">
                                <!-- Voir détails -->
                                <a href="?page=user_details&id=<?= $user['id'] ?>" 
                                   class="text-blue-600 hover:text-blue-900" title="Voir détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <!-- Modifier soldes -->
                                <button onclick="openBalanceModal(<?= $user['id'] ?>, 
                                    '<?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?>',
                                    <?= $user['solde_investissement'] ?? 0 ?>, 
                                    <?= $user['solde_publicite'] ?? 0 ?>, 
                                    <?= $user['solde_parrainage'] ?? 0 ?>)" 
                                   class="text-green-600 hover:text-green-900" title="Modifier soldes">
                                    <i class="fas fa-money-bill-wave"></i>
                                </button>
                                
                                <!-- Changer statut -->
                                <button onclick="openStatusModal(<?= $user['id'] ?>, '<?= $user['statut'] ?>')"
                                   class="text-yellow-600 hover:text-yellow-900" title="Changer statut">
                                    <i class="fas fa-user-cog"></i>
                                </button>
                                
                                <!-- Supprimer -->
                                <form method="POST" class="inline" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if($total_pages > 1): ?>
        <div class="px-6 py-4 border-t">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-700">
                    Page <?= $page ?> sur <?= $total_pages ?> • 
                    <?= $total_users ?> utilisateur(s)
                </div>
                <div class="flex space-x-2">
                    <?php if($page > 1): ?>
                        <a href="?page=users&p=<?= $page-1 ?><?= $search ? '&search='.urlencode($search) : '' ?><?= $status_filter ? '&status='.urlencode($status_filter) : '' ?>"
                           class="px-3 py-1 border rounded hover:bg-gray-100">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php for($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                        <a href="?page=users&p=<?= $i ?><?= $search ? '&search='.urlencode($search) : '' ?><?= $status_filter ? '&status='.urlencode($status_filter) : '' ?>"
                           class="px-3 py-1 border rounded <?= $i == $page ? 'bg-blue-500 text-white' : 'hover:bg-gray-100' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if($page < $total_pages): ?>
                        <a href="?page=users&p=<?= $page+1 ?><?= $search ? '&search='.urlencode($search) : '' ?><?= $status_filter ? '&status='.urlencode($status_filter) : '' ?>"
                           class="px-3 py-1 border rounded hover:bg-gray-100">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal pour modifier les soldes -->
<div id="balanceModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Modifier les soldes</h3>
                <button onclick="closeBalanceModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="balanceForm" method="POST">
                <input type="hidden" name="user_id" id="balance_user_id">
                <input type="hidden" name="action" value="update_balance">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Utilisateur</label>
                    <input type="text" id="balance_user_name" class="w-full px-3 py-2 border rounded-lg bg-gray-50" readonly>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type de solde</label>
                    <select name="balance_type" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="solde_investissement">Investissement</option>
                        <option value="solde_publicite">Publicité</option>
                        <option value="solde_parrainage">Parrainage</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Opération</label>
                    <div class="flex space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="operation" value="add" checked class="mr-2">
                            <span>Ajouter</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="operation" value="subtract" class="mr-2">
                            <span>Retirer</span>
                        </label>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Montant (FCFA)</label>
                    <input type="number" name="amount" min="0" step="0.01" required
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeBalanceModal()"
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

<!-- Modal pour changer statut -->
<div id="statusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Changer le statut</h3>
                <button onclick="closeStatusModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="statusForm" method="POST">
                <input type="hidden" name="user_id" id="status_user_id">
                <input type="hidden" name="action" value="change_status">
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nouveau statut</label>
                    <select name="new_status" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="active">Actif</option>
                        <option value="pending">En attente</option>
                        <option value="banned">Banni</option>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeStatusModal()"
                            class="px-4 py-2 border rounded-lg hover:bg-gray-100">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">
                        <i class="fas fa-sync-alt mr-2"></i>Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Fonctions pour les modals
function openBalanceModal(userId, userName, invest, pub, parrain) {
    document.getElementById('balance_user_id').value = userId;
    document.getElementById('balance_user_name').value = userName;
    document.getElementById('balanceModal').classList.remove('hidden');
}

function closeBalanceModal() {
    document.getElementById('balanceModal').classList.add('hidden');
}

function openStatusModal(userId, currentStatus) {
    document.getElementById('status_user_id').value = userId;
    document.getElementById('statusForm').querySelector('select[name="new_status"]').value = currentStatus;
    document.getElementById('statusModal').classList.remove('hidden');
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
}

function exportUsers() {
    alert('Exportation des utilisateurs...');
    // Implémenter l'export CSV/Excel ici
}
</script>