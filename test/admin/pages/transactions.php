<?php
// Pagination
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Filtres
$type_filter = $_GET['type'] ?? '';
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

$where = [];
$params = [];

if($type_filter) {
    $where[] = "type = ?";
    $params[] = $type_filter;
}

if($status_filter) {
    $where[] = "statut = ?";
    $params[] = $status_filter;
}

if($date_from) {
    $where[] = "DATE(created_at) >= ?";
    $params[] = $date_from;
}

if($date_to) {
    $where[] = "DATE(created_at) <= ?";
    $params[] = $date_to;
}

$where_clause = $where ? "WHERE " . implode(" AND ", $where) : "";

// Compter
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions $where_clause");
$count_stmt->execute($params);
$total_transactions = $count_stmt->fetchColumn();
$total_pages = ceil($total_transactions / $limit);

// Récupérer
$stmt = $pdo->prepare("
    SELECT t.*, u.nom, u.prenom, u.email 
    FROM transactions t 
    JOIN users u ON t.user_id = u.id 
    $where_clause 
    ORDER BY t.created_at DESC 
    LIMIT $limit OFFSET $offset
");


$stmt->execute();
$transactions = $stmt->fetchAll();

// Statistiques
$stats_stmt = $pdo->query("
    SELECT 
        type,
        statut,
        COUNT(*) as count,
        SUM(montant) as total
    FROM transactions 
    GROUP BY type, statut
");
$stats = $stats_stmt->fetchAll();
?>

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold">Gestion des Transactions</h3>
        <div class="text-sm text-gray-600">
            <?= number_format($total_transactions) ?> transactions
        </div>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <?php
        $stats_summary = [
            'depot_success' => 0,
            'depot_pending' => 0,
            'retrait_success' => 0,
            'retrait_pending' => 0
        ];
        
        foreach($stats as $stat) {
            if($stat['type'] == 'depot' && $stat['statut'] == 'success') {
                $stats_summary['depot_success'] = $stat['total'];
            } elseif($stat['type'] == 'depot' && $stat['statut'] == 'attente') {
                $stats_summary['depot_pending'] = $stat['count'];
            } elseif($stat['type'] == 'retrait' && $stat['statut'] == 'success') {
                $stats_summary['retrait_success'] = $stat['total'];
            } elseif($stat['type'] == 'retrait' && $stat['statut'] == 'attente') {
                $stats_summary['retrait_pending'] = $stat['count'];
            }
        }
        ?>
        
        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
            <div class="text-green-800 font-bold text-lg">
                <?= number_format($stats_summary['depot_success'], 2) ?> FCFA
            </div>
            <div class="text-green-600 text-sm">Dépôts validés</div>
        </div>
        
        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
            <div class="text-yellow-800 font-bold text-lg">
                <?= $stats_summary['depot_pending'] ?>
            </div>
            <div class="text-yellow-600 text-sm">Dépôts en attente</div>
        </div>
        
        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            <div class="text-blue-800 font-bold text-lg">
                <?= number_format($stats_summary['retrait_success'], 2) ?> FCFA
            </div>
            <div class="text-blue-600 text-sm">Retraits validés</div>
        </div>
        
        <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
            <div class="text-orange-800 font-bold text-lg">
                <?= $stats_summary['retrait_pending'] ?>
            </div>
            <div class="text-orange-600 text-sm">Retraits en attente</div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="hidden" name="page" value="transactions">
            
            <div>
                <select name="type" class="w-full px-3 py-2 border rounded-lg">
                    <option value="">Tous types</option>
                    <option value="depot" <?= $type_filter == 'depot' ? 'selected' : '' ?>>Dépôts</option>
                    <option value="retrait" <?= $type_filter == 'retrait' ? 'selected' : '' ?>>Retraits</option>
                </select>
            </div>
            
            <div>
                <select name="status" class="w-full px-3 py-2 border rounded-lg">
                    <option value="">Tous statuts</option>
                    <option value="attente" <?= $status_filter == 'attente' ? 'selected' : '' ?>>En attente</option>
                    <option value="success" <?= $status_filter == 'success' ? 'selected' : '' ?>>Succès</option>
                    <option value="failed" <?= $status_filter == 'failed' ? 'selected' : '' ?>>Échoué</option>
                    <option value="annule" <?= $status_filter == 'annule' ? 'selected' : '' ?>>Annulé</option>
                </select>
            </div>
            
            <div>
                <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>"
                       class="w-full px-3 py-2 border rounded-lg">
            </div>
            
            <div>
                <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>"
                       class="w-full px-3 py-2 border rounded-lg">
            </div>
            
            <div class="md:col-span-4 flex space-x-3">
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                    <i class="fas fa-filter mr-2"></i>Filtrer
                </button>
                
                <?php if($type_filter || $status_filter || $date_from || $date_to): ?>
                    <a href="?page=transactions" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                        <i class="fas fa-times mr-2"></i>Effacer
                    </a>
                <?php endif; ?>
                
                <button type="button" onclick="exportTransactions()" 
                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                    <i class="fas fa-download mr-2"></i>Exporter
                </button>
            </div>
        </form>
    </div>

    <!-- Tableau des transactions -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisateur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Méthode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach($transactions as $transaction): ?>
                    <tr>
                        <td class="px-6 py-4 text-sm font-mono text-gray-900">
                            #<?= str_pad($transaction['id'], 6, '0', STR_PAD_LEFT) ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">
                                <?= htmlspecialchars($transaction['nom'] . ' ' . $transaction['prenom']) ?>
                            </div>
                            <div class="text-xs text-gray-500"><?= htmlspecialchars($transaction['email']) ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    <?= $transaction['type'] == 'depot' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' ?>">
                                    <?= $transaction['type'] == 'depot' ? 'Dépôt' : 'Retrait' ?>
                                </span>
                                <?php if($transaction['source']): ?>
                                    <span class="ml-2 text-xs text-gray-500">(<?= $transaction['source'] ?>)</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm font-bold">
                            <?= number_format($transaction['montant'], 2) ?> FCFA
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm">
                                <span class="font-medium"><?= $transaction['methode'] ?></span>
                                <?php if($transaction['reference']): ?>
                                    <div class="text-xs text-gray-500">Ref: <?= substr($transaction['reference'], 0, 10) ?>...</div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <?php
                            $status_classes = [
                                'attente' => 'bg-yellow-100 text-yellow-800',
                                'success' => 'bg-green-100 text-green-800',
                                'failed' => 'bg-red-100 text-red-800',
                                'annule' => 'bg-gray-100 text-gray-800'
                            ];
                            ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $status_classes[$transaction['statut']] ?? 'bg-gray-100' ?>">
                                <?= $transaction['statut'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?= date('d/m/Y H:i', strtotime($transaction['created_at'])) ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex space-x-2">
                                <?php if($transaction['statut'] == 'attente'): ?>
                                    <button onclick="updateTransactionStatus(<?= $transaction['id'] ?>, 'success')" 
                                            class="text-green-600 hover:text-green-900" title="Approuver">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button onclick="updateTransactionStatus(<?= $transaction['id'] ?>, 'failed')" 
                                            class="text-red-600 hover:text-red-900" title="Refuser">
                                        <i class="fas fa-times"></i>
                                    </button>
                                <?php endif; ?>
                                
                                <button onclick="viewTransactionDetails(<?= $transaction['id'] ?>)"
                                        class="text-blue-600 hover:text-blue-900" title="Détails">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                                
                                <?php if($transaction['note']): ?>
                                    <span class="text-gray-400" title="<?= htmlspecialchars($transaction['note']) ?>">
                                        <i class="fas fa-sticky-note"></i>
                                    </span>
                                <?php endif; ?>
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
                    Page <?= $page ?> sur <?= $total_pages ?>
                </div>
                <div class="flex space-x-2">
                    <?php if($page > 1): ?>
                        <a href="?page=transactions&p=<?= $page-1 ?><?= $type_filter ? '&type='.urlencode($type_filter) : '' ?><?= $status_filter ? '&status='.urlencode($status_filter) : '' ?><?= $date_from ? '&date_from='.urlencode($date_from) : '' ?><?= $date_to ? '&date_to='.urlencode($date_to) : '' ?>"
                           class="px-3 py-1 border rounded hover:bg-gray-100">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php for($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                        <a href="?page=transactions&p=<?= $i ?><?= $type_filter ? '&type='.urlencode($type_filter) : '' ?><?= $status_filter ? '&status='.urlencode($status_filter) : '' ?><?= $date_from ? '&date_from='.urlencode($date_from) : '' ?><?= $date_to ? '&date_to='.urlencode($date_to) : '' ?>"
                           class="px-3 py-1 border rounded <?= $i == $page ? 'bg-blue-500 text-white' : 'hover:bg-gray-100' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if($page < $total_pages): ?>
                        <a href="?page=transactions&p=<?= $page+1 ?><?= $type_filter ? '&type='.urlencode($type_filter) : '' ?><?= $status_filter ? '&status='.urlencode($status_filter) : '' ?><?= $date_from ? '&date_from='.urlencode($date_from) : '' ?><?= $date_to ? '&date_to='.urlencode($date_to) : '' ?>"
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

<script>
function updateTransactionStatus(transactionId, newStatus) {
    if(confirm('Êtes-vous sûr de vouloir modifier le statut de cette transaction ?')) {
        fetch('ajax.php?action=update_transaction_status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${transactionId}&status=${newStatus}`
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('Statut mis à jour avec succès');
                location.reload();
            }
        });
    }
}

function viewTransactionDetails(transactionId) {
    // Ouvrir un modal avec les détails
    alert('Détails de la transaction #' + transactionId);
    // Implémenter l'affichage des détails complets
}

function exportTransactions() {
    const params = new URLSearchParams(window.location.search);
    window.open('export.php?type=transactions&' + params.toString(), '_blank');
}
</script>