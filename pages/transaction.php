<?php
// pages/transactions.php

require_once 'config/db.php';

// Vérifier connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: login');
    exit();
}

$userId = $_SESSION['user_id'];
$db = Database::getInstance()->getConnection();

// Récupérer les transactions avec filtres
function getTransactions($userId, $filters = []) {
    global $db;
    
    $whereClauses = ['t.user_id = :user_id'];
    $params = [':user_id' => $userId];
    
    // Appliquer les filtres
    if (!empty($filters['type']) && $filters['type'] !== 'all') {
        $whereClauses[] = 't.type = :type';
        $params[':type'] = $filters['type'];
    }
    
    if (!empty($filters['source']) && $filters['source'] !== 'all') {
        $whereClauses[] = 't.source = :source';
        $params[':source'] = $filters['source'];
    }
    
    if (!empty($filters['statut']) && $filters['statut'] !== 'all') {
        $whereClauses[] = 't.statut = :statut';
        $params[':statut'] = $filters['statut'];
    }
    
    if (!empty($filters['date_from'])) {
        $whereClauses[] = 'DATE(t.created_at) >= :date_from';
        $params[':date_from'] = $filters['date_from'];
    }
    
    if (!empty($filters['date_to'])) {
        $whereClauses[] = 'DATE(t.created_at) <= :date_to';
        $params[':date_to'] = $filters['date_to'];
    }
    
    $whereSQL = implode(' AND ', $whereClauses);
    
    $sql = "SELECT 
                t.id,
                t.type,
                t.source,
                t.montant,
                t.statut,
                t.methode,
                t.reference,
                t.note,
                t.created_at,
                CASE 
                    WHEN t.type = 'depot' THEN 'Dépôt'
                    WHEN t.type = 'retrait' THEN 'Retrait'
                    ELSE t.type
                END as type_display,
                CASE 
                    WHEN t.source = 'investissement' THEN 'Investissement'
                    WHEN t.source = 'publicite' THEN 'Publicité'
                    WHEN t.source = 'parrainage' THEN 'Parrainage'
                    ELSE t.source
                END as source_display,
                CASE 
                    WHEN t.methode = 'orange' THEN 'Orange Money'
                    WHEN t.methode = 'mtn' THEN 'MTN Mobile'
                    WHEN t.methode = 'visa' THEN 'Carte Visa'
                    ELSE t.methode
                END as methode_display
            FROM transactions t
            WHERE $whereSQL
            ORDER BY t.created_at DESC
            LIMIT 100";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Récupérer les statistiques
function getTransactionStats($userId) {
    global $db;
    
    $sql = "SELECT 
                COUNT(*) as total_transactions,
                SUM(CASE WHEN type = 'depot' THEN montant ELSE 0 END) as total_depots,
                SUM(CASE WHEN type = 'retrait' THEN montant ELSE 0 END) as total_retraits,
                COUNT(CASE WHEN statut = 'success' THEN 1 END) as transactions_success,
                COUNT(CASE WHEN statut = 'pending' THEN 1 END) as transactions_pending,
                COUNT(CASE WHEN statut = 'failed' THEN 1 END) as transactions_failed,
                SUM(CASE WHEN source = 'investissement' THEN montant ELSE 0 END) as total_investissement,
                SUM(CASE WHEN source = 'publicite' THEN montant ELSE 0 END) as total_publicite,
                SUM(CASE WHEN source = 'parrainage' THEN montant ELSE 0 END) as total_parrainage
            FROM transactions 
            WHERE user_id = :user_id";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetch();
}

// Appliquer les filtres depuis GET
$filters = [
    'type' => $_GET['type'] ?? 'all',
    'source' => $_GET['source'] ?? 'all',
    'statut' => $_GET['statut'] ?? 'all',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? ''
];

// Récupérer les données
$transactions = getTransactions($userId, $filters);
$stats = getTransactionStats($userId);
$stats = $stats ?: [
    'total_transactions' => 0,
    'total_depots' => 0,
    'total_retraits' => 0,
    'transactions_success' => 0,
    'transactions_pending' => 0,
    'transactions_failed' => 0,
    'total_investissement' => 0,
    'total_publicite' => 0,
    'total_parrainage' => 0
];

// Formatage des montants pour l'affichage
function formatMoney($amount) {
    return number_format($amount ?? 0, 0, ',', ' ') . ' FCFA';
}
?>

<div class="page-transition">
    <!-- En-tête de la page -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Historique des transactions</h1>
                <p class="text-gray-600">Suivez toutes vos opérations financières</p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                <button onclick="openExportModal()" 
                        class="inline-flex items-center border border-gray-300 text-gray-700 font-medium px-4 py-2 rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-download mr-2"></i>
                    Exporter
                </button>
                <button onclick="openDepotModal()" 
                        class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-plus mr-2"></i>
                    Nouvelle transaction
                </button>
            </div>
        </div>
        
        <!-- Cartes de statistiques -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total transactions -->
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-sm text-gray-500">Transactions totales</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo $stats['total_transactions']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exchange-alt text-blue-600 text-xl"></i>
                    </div>
                </div>
                <div class="flex items-center text-xs text-gray-500">
                    <i class="fas fa-check-circle text-green-500 mr-1"></i>
                    <span><?php echo $stats['transactions_success']; ?> réussies</span>
                </div>
            </div>
            
            <!-- Total dépôts -->
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-sm text-gray-500">Total dépôts</p>
                        <p class="text-2xl font-bold text-green-600"><?php echo formatMoney($stats['total_depots']); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-upload text-green-600 text-xl"></i>
                    </div>
                </div>
                <div class="flex items-center text-xs text-gray-500">
                    <i class="fas fa-arrow-up text-green-500 mr-1"></i>
                    <span>Entrées sur votre compte</span>
                </div>
            </div>
            
            <!-- Total retraits -->
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-sm text-gray-500">Total retraits</p>
                        <p class="text-2xl font-bold text-blue-600"><?php echo formatMoney($stats['total_retraits']); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-download text-blue-600 text-xl"></i>
                    </div>
                </div>
                <div class="flex items-center text-xs text-gray-500">
                    <i class="fas fa-arrow-down text-blue-500 mr-1"></i>
                    <span>Sorties de votre compte</span>
                </div>
            </div>
            
            <!-- En attente -->
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-sm text-gray-500">En attente</p>
                        <p class="text-2xl font-bold text-yellow-600"><?php echo $stats['transactions_pending']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                    </div>
                </div>
                <div class="flex items-center text-xs text-gray-500">
                    <i class="fas fa-hourglass-half text-yellow-500 mr-1"></i>
                    <span>En cours de traitement</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Section de filtres -->
    <div class="bg-white rounded-2xl shadow-sm p-6 mb-8 border border-gray-100">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-filter text-green-600 mr-2"></i>
                Filtres
            </h2>
            <button onclick="resetFilters()" class="text-sm text-gray-500 hover:text-gray-700">
                <i class="fas fa-redo mr-1"></i>
                Réinitialiser
            </button>
        </div>
        
        <form id="filterForm" method="GET" class="space-y-4">
            <input type="hidden" name="page" value="transactions">
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Type de transaction -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                    <select name="type" class="filter-select">
                        <option value="all" <?php echo $filters['type'] === 'all' ? 'selected' : ''; ?>>Tous les types</option>
                        <option value="depot" <?php echo $filters['type'] === 'depot' ? 'selected' : ''; ?>>Dépôt</option>
                        <option value="retrait" <?php echo $filters['type'] === 'retrait' ? 'selected' : ''; ?>>Retrait</option>
                    </select>
                </div>
                
                <!-- Source -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Source</label>
                    <select name="source" class="filter-select">
                        <option value="all" <?php echo $filters['source'] === 'all' ? 'selected' : ''; ?>>Toutes les sources</option>
                        <option value="investissement" <?php echo $filters['source'] === 'investissement' ? 'selected' : ''; ?>>Investissement</option>
                        <option value="publicite" <?php echo $filters['source'] === 'publicite' ? 'selected' : ''; ?>>Publicité</option>
                        <option value="parrainage" <?php echo $filters['source'] === 'parrainage' ? 'selected' : ''; ?>>Parrainage</option>
                    </select>
                </div>
                
                <!-- Statut -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                    <select name="statut" class="filter-select">
                        <option value="all" <?php echo $filters['statut'] === 'all' ? 'selected' : ''; ?>>Tous les statuts</option>
                        <option value="success" <?php echo $filters['statut'] === 'success' ? 'selected' : ''; ?>>Succès</option>
                        <option value="pending" <?php echo $filters['statut'] === 'pending' ? 'selected' : ''; ?>>En attente</option>
                        <option value="failed" <?php echo $filters['statut'] === 'failed' ? 'selected' : ''; ?>>Échoué</option>
                    </select>
                </div>
                
                <!-- Période -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Période</label>
                    <select id="periodSelect" class="filter-select" onchange="updateDateRange(this.value)">
                        <option value="">Toute période</option>
                        <option value="today">Aujourd'hui</option>
                        <option value="yesterday">Hier</option>
                        <option value="week">Cette semaine</option>
                        <option value="month">Ce mois</option>
                        <option value="last_month">Mois dernier</option>
                        <option value="custom">Personnalisée</option>
                    </select>
                </div>
            </div>
            
            <!-- Dates personnalisées (caché par défaut) -->
            <div id="customDateRange" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Du</label>
                    <input type="date" name="date_from" value="<?php echo htmlspecialchars($filters['date_from']); ?>" 
                           class="filter-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Au</label>
                    <input type="date" name="date_to" value="<?php echo htmlspecialchars($filters['date_to']); ?>" 
                           class="filter-input">
                </div>
            </div>
            
            <!-- Bouton de recherche -->
            <div class="flex justify-end pt-4 border-t border-gray-200">
                <button type="submit" 
                        class="bg-green-600 hover:bg-green-700 text-white font-medium px-6 py-2 rounded-lg transition flex items-center">
                    <i class="fas fa-search mr-2"></i>
                    Appliquer les filtres
                </button>
            </div>
        </form>
    </div>

    <!-- Liste des transactions -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-8 border border-gray-100">
        <!-- En-tête de la table -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex flex-col md:flex-row md:items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-list-alt text-gray-400 mr-2"></i>
                    <h3 class="text-lg font-semibold text-gray-800">
                        Transactions (<?php echo count($transactions); ?>)
                    </h3>
                </div>
                <div class="mt-2 md:mt-0">
                    <span class="text-sm text-gray-500">
                        <?php
                        if ($filters['type'] !== 'all' || $filters['source'] !== 'all' || $filters['statut'] !== 'all') {
                            echo 'Filtres appliqués';
                        }
                        ?>
                    </span>
                </div>
            </div>
        </div>
        
        <?php if (empty($transactions)): ?>
        <div class="text-center py-12">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-exchange-alt text-gray-400 text-3xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-700 mb-2">Aucune transaction</h3>
            <p class="text-gray-500 mb-6 max-w-md mx-auto">
                <?php 
                if (array_filter($filters)) {
                    echo 'Aucune transaction ne correspond à vos critères de recherche.';
                } else {
                    echo 'Vous n\'avez effectué aucune transaction pour le moment.';
                }
                ?>
            </p>
            <?php if (array_filter($filters)): ?>
            <button onclick="resetFilters()" class="text-green-600 hover:text-green-700 font-medium">
                <i class="fas fa-redo mr-1"></i>
                Réinitialiser les filtres
            </button>
            <?php else: ?>
            <button onclick="openDepotModal()" 
                    class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white font-medium px-6 py-3 rounded-lg transition">
                <i class="fas fa-plus mr-2"></i>
                Effectuer une transaction
            </button>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-gray-500 text-sm border-b">
                        <th class="py-3 px-4 font-medium">Date</th>
                        <th class="py-3 px-4 font-medium">Type</th>
                        <th class="py-3 px-4 font-medium">Description</th>
                        <th class="py-3 px-4 font-medium">Montant</th>
                        <th class="py-3 px-4 font-medium">Méthode</th>
                        <th class="py-3 px-4 font-medium">Statut</th>
                        <th class="py-3 px-4 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($transactions as $transaction): ?>
                    <?php
                    // Déterminer les couleurs et icônes
                    $typeColor = $transaction['type'] === 'depot' ? 'green' : 'red';
                    $typeIcon = $transaction['type'] === 'depot' ? 'fa-upload' : 'fa-download';
                    
                    $sourceColor = '';
                    $sourceIcon = '';
                    switch($transaction['source']) {
                        case 'investissement':
                            $sourceColor = 'blue';
                            $sourceIcon = 'fa-chart-line';
                            break;
                        case 'publicite':
                            $sourceColor = 'purple';
                            $sourceIcon = 'fa-video';
                            break;
                        case 'parrainage':
                            $sourceColor = 'yellow';
                            $sourceIcon = 'fa-users';
                            break;
                        default:
                            $sourceColor = 'gray';
                            $sourceIcon = 'fa-wallet';
                    }
                    
                    $statutColor = '';
                    $statutIcon = '';
                    switch($transaction['statut']) {
                        case 'success':
                            $statutColor = 'green';
                            $statutIcon = 'fa-check-circle';
                            break;
                        case 'pending':
                            $statutColor = 'yellow';
                            $statutIcon = 'fa-clock';
                            break;
                        case 'failed':
                            $statutColor = 'red';
                            $statutIcon = 'fa-times-circle';
                            break;
                        default:
                            $statutColor = 'gray';
                            $statutIcon = 'fa-question-circle';
                    }
                    
                    // Formater la date
                    $date = new DateTime($transaction['created_at']);
                    $dateDisplay = $date->format('d/m/Y');
                    $timeDisplay = $date->format('H:i');
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors transaction-row" data-id="<?php echo $transaction['id']; ?>">
                        <td class="py-4 px-4">
                            <div class="text-gray-800 font-medium"><?php echo $dateDisplay; ?></div>
                            <div class="text-sm text-gray-500"><?php echo $timeDisplay; ?></div>
                        </td>
                        
                        <td class="py-4 px-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3 bg-<?php echo $typeColor; ?>-100">
                                    <i class="fas <?php echo $typeIcon; ?> text-<?php echo $typeColor; ?>-600"></i>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-800"><?php echo $transaction['type_display']; ?></span>
                                    <div class="text-xs text-gray-500"><?php echo $transaction['source_display']; ?></div>
                                </div>
                            </div>
                        </td>
                        
                        <td class="py-4 px-4">
                            <div class="max-w-xs">
                                <p class="text-gray-800 font-medium truncate">
                                    <?php 
                                    if (!empty($transaction['note'])) {
                                        echo htmlspecialchars($transaction['note']);
                                    } else {
                                        echo $transaction['type_display'] . ' - ' . $transaction['source_display'];
                                    }
                                    ?>
                                </p>
                                <?php if (!empty($transaction['reference'])): ?>
                                <p class="text-xs text-gray-500 mt-1">
                                    Ref: <?php echo htmlspecialchars($transaction['reference']); ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </td>
                        
                        <td class="py-4 px-4">
                            <div class="text-lg font-bold <?php echo $transaction['type'] === 'depot' ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo $transaction['type'] === 'depot' ? '+' : '-'; ?>
                                <?php echo formatMoney($transaction['montant']); ?>
                            </div>
                        </td>
                        
                        <td class="py-4 px-4">
                            <div class="flex items-center">
                                <?php if ($transaction['methode'] === 'orange'): ?>
                                <i class="fas fa-mobile-alt text-orange-500 mr-2"></i>
                                <?php elseif ($transaction['methode'] === 'mtn'): ?>
                                <i class="fas fa-sim-card text-yellow-500 mr-2"></i>
                                <?php elseif ($transaction['methode'] === 'visa'): ?>
                                <i class="fab fa-cc-visa text-blue-500 mr-2"></i>
                                <?php else: ?>
                                <i class="fas fa-wallet text-gray-500 mr-2"></i>
                                <?php endif; ?>
                                <span class="text-gray-700"><?php echo $transaction['methode_display']; ?></span>
                            </div>
                        </td>
                        
                        <td class="py-4 px-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-<?php echo $statutColor; ?>-100 text-<?php echo $statutColor; ?>-800">
                                <i class="fas <?php echo $statutIcon; ?> mr-1"></i>
                                <?php 
                                $statutText = [
                                    'success' => 'Terminé',
                                    'pending' => 'En attente',
                                    'failed' => 'Échoué'
                                ];
                                echo $statutText[$transaction['statut']] ?? $transaction['statut'];
                                ?>
                            </span>
                        </td>
                        
                        <td class="py-4 px-4">
                            <div class="flex space-x-2">
                                <button onclick="viewTransactionDetails(<?php echo $transaction['id']; ?>)" 
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-blue-600 hover:bg-blue-50 transition">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="copyTransactionRef('<?php echo htmlspecialchars($transaction['reference'] ?? ''); ?>')" 
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-green-600 hover:bg-green-50 transition">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <?php if ($transaction['statut'] === 'pending'): ?>
                                <button onclick="cancelTransaction(<?php echo $transaction['id']; ?>)" 
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-red-600 hover:bg-red-50 transition">
                                    <i class="fas fa-times"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            <div class="flex flex-col md:flex-row md:items-center justify-between">
                <p class="text-sm text-gray-500">
                    Affichage de <?php echo count($transactions); ?> transactions
                </p>
                <div class="flex items-center space-x-2 mt-2 md:mt-0">
                    <button class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="px-3 py-1 bg-green-600 text-white rounded text-sm font-medium">1</button>
                    <button class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50">2</button>
                    <button class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50">3</button>
                    <button class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    
</div>

<!-- Modal détails transaction -->


<!-- Modal export -->


<script>
// Variables
let currentTransactionId = null;

// Gestion des filtres
function updateDateRange(period) {
    const customRange = document.getElementById('customDateRange');
    const dateFromInput = document.querySelector('input[name="date_from"]');
    const dateToInput = document.querySelector('input[name="date_to"]');
    
    if (period === 'custom') {
        customRange.classList.remove('hidden');
        return;
    }
    
    customRange.classList.add('hidden');
    
    const today = new Date();
    let fromDate, toDate;
    
    switch(period) {
        case 'today':
            fromDate = today;
            toDate = today;
            break;
        case 'yesterday':
            fromDate = new Date(today);
            fromDate.setDate(today.getDate() - 1);
            toDate = fromDate;
            break;
        case 'week':
            fromDate = new Date(today);
            fromDate.setDate(today.getDate() - 7);
            toDate = today;
            break;
        case 'month':
            fromDate = new Date(today.getFullYear(), today.getMonth(), 1);
            toDate = today;
            break;
        case 'last_month':
            fromDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            toDate = new Date(today.getFullYear(), today.getMonth(), 0);
            break;
        default:
            dateFromInput.value = '';
            dateToInput.value = '';
            return;
    }
    
    // Formater les dates au format YYYY-MM-DD
    dateFromInput.value = fromDate.toISOString().split('T')[0];
    dateToInput.value = toDate.toISOString().split('T')[0];
}

function resetFilters() {
    document.getElementById('filterForm').reset();
    document.getElementById('customDateRange').classList.add('hidden');
    document.getElementById('periodSelect').value = '';
    document.querySelector('input[name="date_from"]').value = '';
    document.querySelector('input[name="date_to"]').value = '';
    document.getElementById('filterForm').submit();
}

// Modals
function openExportModal() {
    document.getElementById('exportModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeExportModal() {
    document.getElementById('exportModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function viewTransactionDetails(transactionId) {
    currentTransactionId = transactionId;
    
    // Simulation de chargement des détails
    const content = document.getElementById('transactionDetailsContent');
    content.innerHTML = `
        <div class="text-center py-8">
            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-spinner fa-spin text-gray-400"></i>
            </div>
            <p class="text-gray-500">Chargement des détails...</p>
        </div>
    `;
    
    document.getElementById('transactionDetailsModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Simuler un chargement avec données fictives
    setTimeout(() => {
        const transactionData = {
            id: transactionId,
            reference: 'TRX-' + transactionId.toString().padStart(6, '0'),
            date: '02/12/2025 14:30',
            type: 'Dépôt',
            source: 'Investissement',
            montant: '15,000 FCFA',
            methode: 'Orange Money',
            statut: 'Terminé',
            note: 'Dépôt pour investissement Plan Pro',
            frais: '0 FCFA',
            net: '15,000 FCFA'
        };
        
        content.innerHTML = `
            <div class="space-y-6">
                <!-- Référence -->
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="text-gray-600">Référence</span>
                    <span class="font-mono font-bold text-gray-800">${transactionData.reference}</span>
                </div>
                
                <!-- Informations principales -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Date</p>
                        <p class="font-medium">${transactionData.date}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Type</p>
                        <p class="font-medium">${transactionData.type}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Source</p>
                        <p class="font-medium">${transactionData.source}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Méthode</p>
                        <p class="font-medium">${transactionData.methode}</p>
                    </div>
                </div>
                
                <!-- Montants -->
                <div class="border-t border-gray-200 pt-4">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Montant</span>
                            <span class="font-bold text-green-600">${transactionData.montant}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Frais</span>
                            <span class="text-gray-700">${transactionData.frais}</span>
                        </div>
                        <div class="flex justify-between border-t border-gray-200 pt-2">
                            <span class="font-bold text-gray-800">Montant net</span>
                            <span class="font-bold text-green-600">${transactionData.net}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Notes -->
                <div>
                    <p class="text-sm text-gray-500 mb-2">Notes</p>
                    <p class="text-gray-700 p-3 bg-gray-50 rounded-lg">${transactionData.note}</p>
                </div>
                
                <!-- Statut -->
                <div class="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-check text-green-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">Statut</p>
                            <p class="text-sm text-green-600">${transactionData.statut}</p>
                        </div>
                    </div>
                    <span class="text-green-600 text-sm">
                        <i class="fas fa-clock mr-1"></i>
                        Traitée en 2 minutes
                    </span>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="flex space-x-3">
                    <button onclick="copyTransactionRef('${transactionData.reference}')" 
                            class="flex-1 border border-gray-300 text-gray-700 font-medium py-2 rounded-lg hover:bg-gray-50">
                        <i class="fas fa-copy mr-2"></i>
                        Copier la référence
                    </button>
                    <button onclick="printTransaction(${transactionId})" 
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-2 rounded-lg">
                        <i class="fas fa-print mr-2"></i>
                        Imprimer
                    </button>
                </div>
            </div>
        `;
    }, 500);
}

function closeTransactionDetails() {
    document.getElementById('transactionDetailsModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Actions
function copyTransactionRef(reference) {
    if (!reference) {
        showNotification('❌ Aucune référence à copier', 'error');
        return;
    }
    
    navigator.clipboard.writeText(reference).then(() => {
        showNotification('✅ Référence copiée : ' + reference, 'success');
    });
}

function cancelTransaction(transactionId) {
    if (confirm('Êtes-vous sûr de vouloir annuler cette transaction ?')) {
        showNotification('✅ Demande d\'annulation envoyée', 'success');
        // Ici, tu ferais un appel AJAX pour annuler la transaction
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }
}

function exportTransactions() {
    const format = document.querySelector('input[name="format"]:checked').value;
    showNotification(`📥 Export ${format.toUpperCase()} en cours de préparation...`, 'info');
    closeExportModal();
    
    // Simulation de téléchargement
    setTimeout(() => {
        showNotification(`✅ Export ${format.toUpperCase()} prêt ! Téléchargement automatique.`, 'success');
    }, 2000);
}

function openDepotModal() {
    window.location.href = '?page=investissement';
}

function printTransaction(transactionId) {
    showNotification('🖨️ Impression en cours de préparation...', 'info');
    // Ici, tu ouvrirais une popup avec les détails formatés pour l'impression
}

// Gestion des formats d'export
document.querySelectorAll('.export-format').forEach(format => {
    format.addEventListener('click', () => {
        document.querySelectorAll('.export-format').forEach(f => {
            f.querySelector('div').classList.remove('border-green-500', 'bg-green-50');
        });
        format.querySelector('div').classList.add('border-green-500', 'bg-green-50');
        format.querySelector('input').checked = true;
    });
});

// Fermer les modals en cliquant à l'extérieur
document.getElementById('exportModal').addEventListener('click', (e) => {
    if (e.target.id === 'exportModal') {
        closeExportModal();
    }
});

document.getElementById('transactionDetailsModal').addEventListener('click', (e) => {
    if (e.target.id === 'transactionDetailsModal') {
        closeTransactionDetails();
    }
});

// Fonction de notification
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-medium animate-slideIn ${
        type === 'success' ? 'bg-green-600' : 
        type === 'error' ? 'bg-red-600' : 
        'bg-blue-600'
    }`;
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation' : 'info'}-circle mr-2"></i>
            ${message}
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('animate-slideOut');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Styles d'animation
const style = document.createElement('style');
style.textContent = `
    .filter-select {
        @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-200;
    }
    
    .filter-input {
        @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500;
    }
    
    .modal-close-btn {
        @apply text-gray-400 hover:text-gray-600 transition-colors duration-200;
    }
    
    .transaction-row:hover {
        @apply bg-gray-50;
    }
    
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
    
    .animate-slideIn {
        animation: slideIn 0.3s ease-out;
    }
    
    .animate-slideOut {
        animation: slideOut 0.3s ease-in;
    }
    
    /* Animation pour les modals */
    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    #exportModal > div,
    #transactionDetailsModal > div {
        animation: modalFadeIn 0.3s ease-out;
    }
`;
document.head.appendChild(style);
</script>