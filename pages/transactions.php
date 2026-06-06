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

<style>
    /* Styles modernes pour la page transactions */
    .page-transition {
        animation: fadeIn 0.5s ease-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .stat-card-modern {
        background: linear-gradient(145deg, #ffffff, #f8fafc);
        border-radius: 20px;
        padding: 24px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 
            0 10px 25px -5px rgba(0, 0, 0, 0.05),
            0 8px 10px -6px rgba(0, 0, 0, 0.01);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    .stat-card-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--gradient-color), transparent);
        border-radius: 20px 20px 0 0;
    }
    
    .stat-card-modern:hover {
        transform: translateY(-5px);
        box-shadow: 
            0 20px 40px -10px rgba(0, 0, 0, 0.1),
            0 10px 15px -3px rgba(0, 0, 0, 0.05);
    }
    
    .stat-card-modern.blue { --gradient-color: #3b82f6; }
    .stat-card-modern.green { --gradient-color: #10b981; }
    .stat-card-modern.purple { --gradient-color: #8b5cf6; }
    .stat-card-modern.amber { --gradient-color: #f59e0b; }
    
    .stat-icon-modern {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        background: linear-gradient(145deg, var(--icon-light), var(--icon-dark));
        color: white;
        box-shadow: 0 4px 15px var(--icon-shadow);
    }
    
    .stat-icon-modern.blue {
        --icon-light: #60a5fa;
        --icon-dark: #3b82f6;
        --icon-shadow: rgba(59, 130, 246, 0.3);
    }
    
    .stat-icon-modern.green {
        --icon-light: #34d399;
        --icon-dark: #10b981;
        --icon-shadow: rgba(16, 185, 129, 0.3);
    }
    
    .stat-icon-modern.purple {
        --icon-light: #a78bfa;
        --icon-dark: #8b5cf6;
        --icon-shadow: rgba(139, 92, 246, 0.3);
    }
    
    .stat-icon-modern.amber {
        --icon-light: #fbbf24;
        --icon-dark: #f59e0b;
        --icon-shadow: rgba(245, 158, 11, 0.3);
    }
    
    .filter-card-modern {
        background: linear-gradient(145deg, #ffffff, #f8fafc);
        border-radius: 20px;
        padding: 32px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 
            0 10px 25px -5px rgba(0, 0, 0, 0.05),
            0 8px 10px -6px rgba(0, 0, 0, 0.01);
        backdrop-filter: blur(10px);
    }
    
    .table-modern {
        background: linear-gradient(145deg, #ffffff, #f8fafc);
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
    }
    
    .table-header-modern {
        background: linear-gradient(90deg, #f8fafc, #f1f5f9);
        padding: 24px;
        border-bottom: 1px solid rgba(203, 213, 225, 0.3);
    }
    
    .transaction-row-modern {
        border-bottom: 1px solid rgba(203, 213, 225, 0.2);
        transition: all 0.2s ease;
        background: transparent;
    }
    
    .transaction-row-modern:hover {
        background: linear-gradient(90deg, rgba(59, 130, 246, 0.05), rgba(139, 92, 246, 0.05));
        transform: scale(1.002);
    }
    
    .transaction-row-modern:last-child {
        border-bottom: none;
    }
    
    .badge-modern {
        padding: 6px 14px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.3px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s ease;
    }
    
    .badge-modern.success {
        background: linear-gradient(135deg, #10b981, #34d399);
        color: white;
        box-shadow: 0 2px 10px rgba(16, 185, 129, 0.3);
    }
    
    .badge-modern.pending {
        background: linear-gradient(135deg, #f59e0b, #fbbf24);
        color: white;
        box-shadow: 0 2px 10px rgba(245, 158, 11, 0.3);
    }
    
    .badge-modern.failed {
        background: linear-gradient(135deg, #ef4444, #f87171);
        color: white;
        box-shadow: 0 2px 10px rgba(239, 68, 68, 0.3);
    }
    
    .filter-select-modern {
        width: 100%;
        padding: 14px 18px;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        background: white;
        color: #334155;
        font-size: 15px;
        font-weight: 500;
        transition: all 0.3s ease;
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 18px center;
        background-size: 20px;
    }
    
    .filter-select-modern:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }
    
    .filter-select-modern:hover {
        border-color: #cbd5e1;
    }
    
    .btn-modern {
        padding: 14px 28px;
        border-radius: 14px;
        font-weight: 600;
        font-size: 15px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        border: none;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    
    .btn-modern::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 5px;
        height: 5px;
        background: rgba(255, 255, 255, 0.5);
        opacity: 0;
        border-radius: 100%;
        transform: scale(1, 1) translate(-50%);
        transform-origin: 50% 50%;
    }
    
    .btn-modern:focus:not(:active)::after {
        animation: ripple 1s ease-out;
    }
    
    @keyframes ripple {
        0% {
            transform: scale(0, 0);
            opacity: 0.5;
        }
        20% {
            transform: scale(25, 25);
            opacity: 0.3;
        }
        100% {
            opacity: 0;
            transform: scale(40, 40);
        }
    }
    
    .btn-modern.primary {
        background: linear-gradient(135deg, #3b82f6, #8b5cf6);
        color: white;
        box-shadow: 0 4px 20px rgba(59, 130, 246, 0.3);
    }
    
    .btn-modern.primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(59, 130, 246, 0.4);
    }
    
    .btn-modern.secondary {
        background: white;
        color: #475569;
        border: 2px solid #e2e8f0;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .btn-modern.secondary:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        transform: translateY(-1px);
    }
    
    .transaction-type-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
        color: white;
        box-shadow: 0 4px 12px var(--shadow-color);
    }
    
    .deposit-icon {
        --gradient-start: #10b981;
        --gradient-end: #34d399;
        --shadow-color: rgba(16, 185, 129, 0.3);
    }
    
    .withdrawal-icon {
        --gradient-start: #3b82f6;
        --gradient-end: #60a5fa;
        --shadow-color: rgba(59, 130, 246, 0.3);
    }
    
    .chart-modern {
        background: linear-gradient(145deg, #ffffff, #f8fafc);
        border-radius: 20px;
        padding: 32px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
    }
    
    .empty-state-modern {
        padding: 80px 20px;
        text-align: center;
        background: linear-gradient(145deg, #f8fafc, #f1f5f9);
        border-radius: 20px;
        border: 2px dashed #cbd5e1;
    }
    
    .empty-state-icon {
        width: 100px;
        height: 100px;
        margin: 0 auto 24px;
        background: linear-gradient(135deg, #3b82f6, #8b5cf6);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        color: white;
        box-shadow: 0 10px 30px rgba(59, 130, 246, 0.2);
    }
    
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(10px);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        animation: fadeIn 0.3s ease-out;
    }
    
    .modal-content {
        background: linear-gradient(145deg, #ffffff, #f8fafc);
        border-radius: 24px;
        width: 100%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
        animation: slideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
    
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    
    .floating-action-btn {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6, #8b5cf6);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
        cursor: pointer;
        z-index: 100;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
    }
    
    .floating-action-btn:hover {
        transform: translateY(-5px) scale(1.1);
        box-shadow: 0 15px 40px rgba(59, 130, 246, 0.5);
    }
    
    /* Animation pour les lignes du tableau */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .transaction-row-modern {
        animation: slideIn 0.3s ease-out;
        animation-fill-mode: both;
    }
    
    .transaction-row-modern:nth-child(1) { animation-delay: 0.1s; }
    .transaction-row-modern:nth-child(2) { animation-delay: 0.2s; }
    .transaction-row-modern:nth-child(3) { animation-delay: 0.3s; }
    .transaction-row-modern:nth-child(4) { animation-delay: 0.4s; }
    .transaction-row-modern:nth-child(5) { animation-delay: 0.5s; }
    .transaction-row-modern:nth-child(6) { animation-delay: 0.6s; }
</style>

<div class="page-transition">
    <!-- En-tête de la page -->
    <div class="mb-10">
        <div class="relative">
            <!-- Background avec effet gradient -->
            <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 via-purple-500/10 to-pink-500/10 rounded-3xl blur-3xl"></div>
            
            <div class="relative">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-8 gap-6">
                    <div>
                        <div class="inline-flex items-center px-4 py-2 rounded-full bg-gradient-to-r from-blue-500/10 to-purple-500/10 border border-blue-200/50 mb-4">
                            <i class="fas fa-exchange-alt text-blue-500 mr-2"></i>
                            <span class="text-sm font-medium text-blue-700">Transactions</span>
                        </div>
                        <h1 class="text-4xl font-bold text-gray-900 mb-3">
                            Historique des <span class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">Transactions</span>
                        </h1>
                        <p class="text-gray-600 text-lg max-w-2xl">
                            Suivez toutes vos opérations financières en temps réel avec notre interface intuitive
                        </p>
                    </div>
                    
                    <div class="flex flex-wrap gap-3">
                        <button onclick="openExportModal()" 
                                class="btn-modern secondary">
                            <i class="fas fa-download"></i>
                            <span>Exporter</span>
                        </button>
                        <button onclick="openDepotModal()" 
                                class="btn-modern primary">
                            <i class="fas fa-plus-circle"></i>
                            <span>Nouvelle Transaction</span>
                        </button>
                    </div>
                </div>
                
                <!-- Cartes de statistiques modernes -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total transactions -->
                    <div class="stat-card-modern blue">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 mb-2">Transactions Totales</p>
                                <p class="text-3xl font-bold text-gray-900 mb-1"><?php echo $stats['total_transactions']; ?></p>
                                <div class="flex items-center text-sm">
                                    <i class="fas fa-check-circle text-green-500 mr-1"></i>
                                    <span class="text-gray-600"><?php echo $stats['transactions_success']; ?> réussies</span>
                                </div>
                            </div>
                            <div class="stat-icon-modern blue">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-100/50">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500">Activité totale</span>
                                <div class="flex items-center">
                                    <i class="fas fa-arrow-up text-green-500 text-xs mr-1"></i>
                                    <span class="text-xs font-medium text-green-600">+12%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total dépôts -->
                    <div class="stat-card-modern green">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 mb-2">Total Dépôts</p>
                                <p class="text-3xl font-bold text-gray-900 mb-1"><?php echo formatMoney($stats['total_depots']); ?></p>
                                <div class="flex items-center text-sm">
                                    <i class="fas fa-arrow-up text-green-500 mr-1"></i>
                                    <span class="text-gray-600">Entrées sur compte</span>
                                </div>
                            </div>
                            <div class="stat-icon-modern green">
                                <i class="fas fa-upload"></i>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-100/50">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500">Mouvements positifs</span>
                                <div class="flex items-center">
                                    <i class="fas fa-arrow-up text-green-500 text-xs mr-1"></i>
                                    <span class="text-xs font-medium text-green-600">+<?php echo $stats['total_depots'] > 0 ? round($stats['total_depots'] / 1000) : 0; ?>K</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total retraits -->
                    <div class="stat-card-modern purple">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 mb-2">Total Retraits</p>
                                <p class="text-3xl font-bold text-gray-900 mb-1"><?php echo formatMoney($stats['total_retraits']); ?></p>
                                <div class="flex items-center text-sm">
                                    <i class="fas fa-arrow-down text-blue-500 mr-1"></i>
                                    <span class="text-gray-600">Sorties de compte</span>
                                </div>
                            </div>
                            <div class="stat-icon-modern purple">
                                <i class="fas fa-download"></i>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-100/50">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500">Retraits traités</span>
                                <div class="flex items-center">
                                    <i class="fas fa-check-circle text-green-500 text-xs mr-1"></i>
                                    <span class="text-xs font-medium text-green-600">100%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- En attente -->
                    <div class="stat-card-modern amber">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 mb-2">Transactions en Attente</p>
                                <p class="text-3xl font-bold text-gray-900 mb-1"><?php echo $stats['transactions_pending']; ?></p>
                                <div class="flex items-center text-sm">
                                    <i class="fas fa-clock text-yellow-500 mr-1"></i>
                                    <span class="text-gray-600">En cours de traitement</span>
                                </div>
                            </div>
                            <div class="stat-icon-modern amber">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-100/50">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500">Temps moyen</span>
                                <div class="flex items-center">
                                    <i class="fas fa-bolt text-yellow-500 text-xs mr-1"></i>
                                    <span class="text-xs font-medium text-yellow-600">24h</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section de filtres modernes -->
    <div class="filter-card-modern mb-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center mr-3">
                        <i class="fas fa-sliders-h text-white"></i>
                    </div>
                    <span>Filtres Avancés</span>
                </h2>
                <p class="text-gray-500 mt-2">Affinez votre recherche selon vos besoins</p>
            </div>
            <button onclick="resetFilters()" 
                    class="group flex items-center text-gray-600 hover:text-blue-600 transition-colors">
                <i class="fas fa-redo mr-2 group-hover:rotate-180 transition-transform"></i>
                <span>Réinitialiser</span>
            </button>
        </div>
        
        <form id="filterForm" method="GET" class="space-y-6">
            <input type="hidden" name="page" value="transactions">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Type de transaction -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-filter text-blue-500 mr-2"></i>
                        Type de Transaction
                    </label>
                    <select name="type" class="filter-select-modern">
                        <option value="all" <?php echo $filters['type'] === 'all' ? 'selected' : ''; ?>>Tous les types</option>
                        <option value="depot" <?php echo $filters['type'] === 'depot' ? 'selected' : ''; ?>>📈 Dépôt</option>
                        <option value="retrait" <?php echo $filters['type'] === 'retrait' ? 'selected' : ''; ?>>📉 Retrait</option>
                    </select>
                </div>
                
                <!-- Source -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-compass text-purple-500 mr-2"></i>
                        Source
                    </label>
                    <select name="source" class="filter-select-modern">
                        <option value="all" <?php echo $filters['source'] === 'all' ? 'selected' : ''; ?>>Toutes les sources</option>
                        <option value="investissement" <?php echo $filters['source'] === 'investissement' ? 'selected' : ''; ?>>💼 Investissement</option>
                        <option value="publicite" <?php echo $filters['source'] === 'publicite' ? 'selected' : ''; ?>>📺 Publicité</option>
                        <option value="parrainage" <?php echo $filters['source'] === 'parrainage' ? 'selected' : ''; ?>>👥 Parrainage</option>
                    </select>
                </div>
                
                <!-- Statut -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-flag text-green-500 mr-2"></i>
                        Statut
                    </label>
                    <select name="statut" class="filter-select-modern">
                        <option value="all" <?php echo $filters['statut'] === 'all' ? 'selected' : ''; ?>>Tous les statuts</option>
                        <option value="success" <?php echo $filters['statut'] === 'success' ? 'selected' : ''; ?>>✅ Succès</option>
                        <option value="pending" <?php echo $filters['statut'] === 'pending' ? 'selected' : ''; ?>>⏳ En attente</option>
                        <option value="failed" <?php echo $filters['statut'] === 'failed' ? 'selected' : ''; ?>>❌ Échoué</option>
                    </select>
                </div>
                
                <!-- Période -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-calendar text-amber-500 mr-2"></i>
                        Période
                    </label>
                    <select id="periodSelect" class="filter-select-modern" onchange="updateDateRange(this.value)">
                        <option value="">⌛ Toute période</option>
                        <option value="today">☀️ Aujourd'hui</option>
                        <option value="yesterday">📅 Hier</option>
                        <option value="week">📆 Cette semaine</option>
                        <option value="month">📊 Ce mois</option>
                        <option value="last_month">🗓️ Mois dernier</option>
                        <option value="custom">🎛️ Personnalisée</option>
                    </select>
                </div>
            </div>
            
            <!-- Dates personnalisées -->
            <div id="customDateRange" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        <i class="fas fa-calendar-plus text-blue-500 mr-2"></i>
                        Date de début
                    </label>
                    <input type="date" name="date_from" value="<?php echo htmlspecialchars($filters['date_from']); ?>" 
                           class="filter-select-modern">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        <i class="fas fa-calendar-minus text-purple-500 mr-2"></i>
                        Date de fin
                    </label>
                    <input type="date" name="date_to" value="<?php echo htmlspecialchars($filters['date_to']); ?>" 
                           class="filter-select-modern">
                </div>
            </div>
            
            <!-- Bouton de recherche -->
            <div class="flex justify-end pt-6 border-t border-gray-200/50">
                <button type="submit" 
                        class="group relative overflow-hidden btn-modern primary px-8">
                    <span class="relative z-10 flex items-center">
                        <i class="fas fa-search mr-2 group-hover:rotate-12 transition-transform"></i>
                        Appliquer les Filtres
                    </span>
                    <div class="absolute inset-0 bg-gradient-to-r from-purple-600 to-pink-600 transform -skew-x-12 -translate-x-full group-hover:translate-x-0 transition-transform duration-500"></div>
                </button>
            </div>
        </form>
    </div>

    <!-- Liste des transactions modernes -->
    <div class="table-modern mb-8">
        <!-- En-tête de la table -->
        <div class="table-header-modern">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center mr-4">
                        <i class="fas fa-list-ul text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">
                            Liste des Transactions
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            <?php echo count($transactions); ?> transaction(s) trouvée(s)
                            <?php if ($filters['type'] !== 'all' || $filters['source'] !== 'all' || $filters['statut'] !== 'all'): ?>
                            <span class="inline-flex items-center ml-2 px-2 py-1 rounded-full bg-blue-100 text-blue-700 text-xs">
                                <i class="fas fa-filter mr-1"></i>
                                Filtres actifs
                            </span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="relative group">
                        <button class="w-10 h-10 rounded-xl border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-50">
                            <i class="fas fa-sort-amount-down"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-10">
                            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">Plus récentes</a>
                            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">Plus anciennes</a>
                            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">Montant croissant</a>
                            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">Montant décroissant</a>
                        </div>
                    </div>
                    
                    <button onclick="toggleTableView()" 
                            class="w-10 h-10 rounded-xl border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-50">
                        <i class="fas fa-grip-horizontal"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <?php if (empty($transactions)): ?>
        <div class="empty-state-modern">
            <div class="empty-state-icon">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-3">
                <?php echo array_filter($filters) ? 'Aucun résultat' : 'Aucune transaction'; ?>
            </h3>
            <p class="text-gray-500 mb-8 max-w-md mx-auto">
                <?php 
                if (array_filter($filters)) {
                    echo 'Aucune transaction ne correspond à vos critères de recherche. Essayez de modifier vos filtres.';
                } else {
                    echo 'Commencez votre aventure financière en effectuant votre première transaction.';
                }
                ?>
            </p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <?php if (array_filter($filters)): ?>
                <button onclick="resetFilters()" 
                        class="btn-modern secondary">
                    <i class="fas fa-redo mr-2"></i>
                    Réinitialiser les filtres
                </button>
                <?php endif; ?>
                <button onclick="openDepotModal()" 
                        class="btn-modern primary">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Effectuer une transaction
                </button>
            </div>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-sm text-gray-500 border-b border-gray-200/50">
                        <th class="py-5 px-8 font-semibold">Date & Heure</th>
                        <th class="py-5 px-8 font-semibold">Type & Source</th>
                        <th class="py-5 px-8 font-semibold">Description</th>
                        <th class="py-5 px-8 font-semibold">Montant</th>
                        <th class="py-5 px-8 font-semibold">Méthode</th>
                        <th class="py-5 px-8 font-semibold">Statut</th>
                        <th class="py-5 px-8 font-semibold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                    <?php
                    // Déterminer les couleurs et icônes
                    $typeColor = $transaction['type'] === 'depot' ? 'green' : 'blue';
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
                            $statutColor = 'success';
                            $statutIcon = 'fa-check-circle';
                            break;
                        case 'pending':
                            $statutColor = 'pending';
                            $statutIcon = 'fa-clock';
                            break;
                        case 'failed':
                            $statutColor = 'failed';
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
                    <tr class="transaction-row-modern">
                        <td class="py-6 px-8">
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-900 text-lg"><?php echo $dateDisplay; ?></span>
                                <span class="text-sm text-gray-500"><?php echo $timeDisplay; ?></span>
                            </div>
                        </td>
                        
                        <td class="py-6 px-8">
                            <div class="flex items-center gap-4">
                                <div class="transaction-type-icon <?php echo $transaction['type'] === 'depot' ? 'deposit-icon' : 'withdrawal-icon'; ?>">
                                    <i class="fas <?php echo $typeIcon; ?>"></i>
                                </div>
                                <div>
                                    <span class="font-bold text-gray-900 block"><?php echo $transaction['type_display']; ?></span>
                                    <div class="flex items-center text-sm text-gray-500 mt-1">
                                        <i class="fas <?php echo $sourceIcon; ?> text-<?php echo $sourceColor; ?>-500 mr-2"></i>
                                        <?php echo $transaction['source_display']; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        
                        <td class="py-6 px-8">
                            <div class="max-w-xs">
                                <p class="text-gray-900 font-semibold truncate">
                                    <?php 
                                    if (!empty($transaction['note'])) {
                                        echo htmlspecialchars($transaction['note']);
                                    } else {
                                        echo $transaction['type_display'] . ' - ' . $transaction['source_display'];
                                    }
                                    ?>
                                </p>
                                <?php if (!empty($transaction['reference'])): ?>
                                <div class="flex items-center mt-2">
                                    <span class="text-xs text-gray-500 bg-gray-100 px-3 py-1 rounded-full font-mono">
                                        <i class="fas fa-hashtag mr-1"></i>
                                        <?php echo substr(htmlspecialchars($transaction['reference']), 0, 12) . '...'; ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        
                        <td class="py-6 px-8">
                            <div class="text-xl font-bold <?php echo $transaction['type'] === 'depot' ? 'text-green-600' : 'text-blue-600'; ?>">
                                <?php echo $transaction['type'] === 'depot' ? '↗ +' : '↘ -'; ?>
                                <?php echo formatMoney($transaction['montant']); ?>
                            </div>
                        </td>
                        
                        <td class="py-6 px-8">
                            <div class="flex items-center">
                                <?php if ($transaction['methode'] === 'orange'): ?>
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-500 to-red-500 flex items-center justify-center mr-3">
                                    <i class="fas fa-mobile-alt text-white"></i>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-900 block">Orange Money</span>
                                    <span class="text-xs text-gray-500">Mobile Money</span>
                                </div>
                                <?php elseif ($transaction['methode'] === 'mtn'): ?>
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-yellow-500 to-amber-500 flex items-center justify-center mr-3">
                                    <i class="fas fa-sim-card text-white"></i>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-900 block">MTN Mobile</span>
                                    <span class="text-xs text-gray-500">Mobile Money</span>
                                </div>
                                <?php elseif ($transaction['methode'] === 'visa'): ?>
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center mr-3">
                                    <i class="fab fa-cc-visa text-white"></i>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-900 block">Carte Visa</span>
                                    <span class="text-xs text-gray-500">Carte bancaire</span>
                                </div>
                                <?php else: ?>
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-gray-600 to-slate-600 flex items-center justify-center mr-3">
                                    <i class="fas fa-wallet text-white"></i>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-900 block"><?php echo $transaction['methode_display']; ?></span>
                                    <span class="text-xs text-gray-500">Autre méthode</span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        
                        <td class="py-6 px-8">
                            <span class="badge-modern <?php echo $statutColor; ?>">
                                <i class="fas <?php echo $statutIcon; ?>"></i>
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
                        
                        <td class="py-6 px-8">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="viewTransactionDetails(<?php echo $transaction['id']; ?>)" 
                                        class="action-btn-view">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="copyTransactionRef('<?php echo htmlspecialchars($transaction['reference'] ?? ''); ?>')" 
                                        class="action-btn-copy">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <?php if ($transaction['statut'] === 'pending'): ?>
                                <button onclick="cancelTransaction(<?php echo $transaction['id']; ?>)" 
                                        class="action-btn-cancel">
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
        
        <!-- Pagination moderne -->
        <div class="px-8 py-6 border-t border-gray-200/50 bg-gray-50/30">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <p class="text-sm text-gray-500">
                    Affichage de <span class="font-semibold text-gray-900"><?php echo count($transactions); ?></span> transaction(s)
                </p>
                <div class="flex items-center gap-2">
                    <button class="p-3 rounded-xl border border-gray-200 text-gray-600 hover:bg-white hover:border-gray-300 hover:shadow-sm transition-all">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-purple-500 text-white font-bold shadow-md">
                        1
                    </button>
                    <button class="w-12 h-12 rounded-xl border border-gray-200 text-gray-700 font-medium hover:bg-gray-50">
                        2
                    </button>
                    <button class="w-12 h-12 rounded-xl border border-gray-200 text-gray-700 font-medium hover:bg-gray-50">
                        3
                    </button>
                    <span class="px-2 text-gray-400">...</span>
                    <button class="w-12 h-12 rounded-xl border border-gray-200 text-gray-700 font-medium hover:bg-gray-50">
                        8
                    </button>
                    <button class="p-3 rounded-xl border border-gray-200 text-gray-600 hover:bg-white hover:border-gray-300 hover:shadow-sm transition-all">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Graphiques modernes -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Répartition par type -->
        <div class="chart-modern">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center mr-3">
                            <i class="fas fa-chart-pie text-white"></i>
                        </div>
                        Répartition par Type
                    </h3>
                    <p class="text-gray-500 mt-2">Distribution de vos transactions</p>
                </div>
                <div class="relative group">
                    <button class="w-10 h-10 rounded-xl border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-50">
                        <i class="fas fa-ellipsis-h"></i>
                    </button>
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                        <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">Exporter</a>
                        <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">Voir détails</a>
                        <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">Actualiser</a>
                    </div>
                </div>
            </div>
            
            <div class="relative h-64">
                <!-- Graphique circulaire simulé amélioré -->
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="relative w-48 h-48">
                        <!-- Arrière-plan du graphique -->
                        <div class="absolute inset-0 rounded-full border-12 border-gray-100"></div>
                        
                        <!-- Segment Dépôts (60%) -->
                        <div class="absolute inset-0 rounded-full border-12 border-green-500" 
                             style="clip-path: polygon(50% 50%, 50% 0%, 100% 0%, 100% 100%, 50% 100%);"></div>
                        
                        <!-- Segment Retraits (25%) -->
                        <div class="absolute inset-0 rounded-full border-12 border-blue-500" 
                             style="clip-path: polygon(50% 50%, 100% 0%, 100% 100%, 0% 100%, 0% 25%);"></div>
                        
                        <!-- Segment Autres (15%) -->
                        <div class="absolute inset-0 rounded-full border-12 border-purple-500" 
                             style="clip-path: polygon(50% 50%, 0% 25%, 0% 0%, 50% 0%);"></div>
                        
                        <!-- Centre du graphique -->
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="w-24 h-24 rounded-full bg-white shadow-lg flex items-center justify-center">
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-gray-900">100%</p>
                                    <p class="text-xs text-gray-500">Total</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-3 gap-4 mt-8">
                <div class="text-center p-4 rounded-xl bg-green-50/50 hover:bg-green-50 transition-colors">
                    <div class="flex items-center justify-center mb-2">
                        <div class="w-4 h-4 rounded-full bg-green-500 mr-2"></div>
                        <span class="font-bold text-gray-900">Dépôts</span>
                    </div>
                    <p class="text-2xl font-bold text-green-600">60%</p>
                    <p class="text-xs text-gray-500"><?php echo formatMoney($stats['total_depots']); ?></p>
                </div>
                <div class="text-center p-4 rounded-xl bg-blue-50/50 hover:bg-blue-50 transition-colors">
                    <div class="flex items-center justify-center mb-2">
                        <div class="w-4 h-4 rounded-full bg-blue-500 mr-2"></div>
                        <span class="font-bold text-gray-900">Retraits</span>
                    </div>
                    <p class="text-2xl font-bold text-blue-600">25%</p>
                    <p class="text-xs text-gray-500"><?php echo formatMoney($stats['total_retraits']); ?></p>
                </div>
                <div class="text-center p-4 rounded-xl bg-purple-50/50 hover:bg-purple-50 transition-colors">
                    <div class="flex items-center justify-center mb-2">
                        <div class="w-4 h-4 rounded-full bg-purple-500 mr-2"></div>
                        <span class="font-bold text-gray-900">Autres</span>
                    </div>
                    <p class="text-2xl font-bold text-purple-600">15%</p>
                    <p class="text-xs text-gray-500">Transactions diverses</p>
                </div>
            </div>
        </div>
        
        <!-- Évolution mensuelle -->
        <div class="chart-modern">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center mr-3">
                            <i class="fas fa-chart-line text-white"></i>
                        </div>
                        Évolution Mensuelle
                    </h3>
                    <p class="text-gray-500 mt-2">Performance ce mois</p>
                </div>
                <div class="flex items-center gap-2">
                    <button class="px-4 py-2 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">
                        Mois
                    </button>
                    <button class="px-4 py-2 rounded-lg border border-blue-200 bg-blue-50 text-sm text-blue-600">
                        Trimestre
                    </button>
                    <button class="px-4 py-2 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">
                        Année
                    </button>
                </div>
            </div>
            
            <!-- Graphique à barres simulé -->
            <div class="h-64 flex items-end justify-between gap-2 px-4">
                <?php for($i = 1; $i <= 7; $i++): ?>
                <?php 
                $height = rand(40, 100);
                $isToday = $i === 7;
                ?>
                <div class="flex flex-col items-center flex-1">
                    <div class="relative w-full">
                        <!-- Barre du graphique -->
                        <div class="w-full rounded-t-lg transition-all duration-500 hover:opacity-80 cursor-pointer"
                             style="height: <?php echo $height; ?>px; 
                                    background: linear-gradient(to top, 
                                        <?php echo $isToday ? '#3b82f6' : '#60a5fa'; ?>, 
                                        <?php echo $isToday ? '#1d4ed8' : '#3b82f6'; ?>);">
                            <!-- Valeur sur la barre -->
                            <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 text-xs font-bold text-gray-700">
                                <?php echo rand(500, 5000); ?>K
                            </div>
                        </div>
                        
                        <!-- Point d'interaction -->
                        <div class="absolute -top-2 left-1/2 transform -translate-x-1/2 w-3 h-3 rounded-full 
                                    <?php echo $isToday ? 'bg-blue-500' : 'bg-gray-300'; ?> 
                                    border-2 border-white shadow-md">
                        </div>
                    </div>
                    
                    <!-- Jour -->
                    <div class="mt-4 text-sm font-medium text-gray-700">
                        <?php echo ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'][$i-1]; ?>
                    </div>
                    <div class="text-xs text-gray-500"><?php echo $i; ?> Déc</div>
                </div>
                <?php endfor; ?>
            </div>
            
            <div class="mt-8 pt-6 border-t border-gray-200/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full bg-blue-500 mr-2"></div>
                        <span class="text-sm text-gray-600">Volume quotidien</span>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-gray-900">
                            <?php echo formatMoney(rand(50000, 200000)); ?>
                        </p>
                        <p class="text-xs text-green-600 flex items-center justify-end">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <?php echo rand(5, 25); ?>% vs mois dernier
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Action Button -->
<button onclick="openDepotModal()" class="floating-action-btn">
    <i class="fas fa-plus"></i>
</button>

<!-- Modals restent les mêmes mais avec les nouvelles classes CSS -->

<script>
// Styles pour les boutons d'action
const actionBtnStyle = `
    .action-btn-view,
    .action-btn-copy,
    .action-btn-cancel {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    
    .action-btn-view {
        background: linear-gradient(135deg, #3b82f6, #60a5fa);
        color: white;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
    }
    
    .action-btn-view:hover {
        transform: translateY(-2px) scale(1.1);
        box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
    }
    
    .action-btn-copy {
        background: linear-gradient(135deg, #10b981, #34d399);
        color: white;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }
    
    .action-btn-copy:hover {
        transform: translateY(-2px) scale(1.1);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
    }
    
    .action-btn-cancel {
        background: linear-gradient(135deg, #ef4444, #f87171);
        color: white;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    }
    
    .action-btn-cancel:hover {
        transform: translateY(-2px) scale(1.1);
        box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
    }
    
    /* Effet de vague au clic */
    .action-btn-view::after,
    .action-btn-copy::after,
    .action-btn-cancel::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 5px;
        height: 5px;
        background: rgba(255, 255, 255, 0.5);
        opacity: 0;
        border-radius: 100%;
        transform: scale(1, 1) translate(-50%);
        transform-origin: 50% 50%;
    }
    
    .action-btn-view:active::after,
    .action-btn-copy:active::after,
    .action-btn-cancel:active::after {
        animation: ripple 0.6s ease-out;
    }
`;

// Ajouter les styles au document
const styleElement = document.createElement('style');
styleElement.textContent = actionBtnStyle;
document.head.appendChild(styleElement);

// Animation pour les cartes au chargement
document.addEventListener('DOMContentLoaded', () => {
    // Animer les cartes de statistiques
    const statCards = document.querySelectorAll('.stat-card-modern');
    statCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 150);
    });
    
    // Effet parallaxe sur les cartes au survol
    statCards.forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const rotateX = (y - centerY) / 20;
            const rotateY = (centerX - x) / 20;
            
            card.style.transform = `
                perspective(1000px)
                rotateX(${rotateX}deg)
                rotateY(${rotateY}deg)
                translateY(-5px)
            `;
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateY(0)';
        });
    });
    
    // Animation pour les lignes du tableau
    const tableRows = document.querySelectorAll('.transaction-row-modern');
    tableRows.forEach((row, index) => {
        row.style.animationDelay = `${index * 0.05}s`;
    });
    
    // Effet de survol amélioré pour les lignes du tableau
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', () => {
            row.style.transform = 'scale(1.002)';
            row.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.05)';
        });
        
        row.addEventListener('mouseleave', () => {
            row.style.transform = 'scale(1)';
            row.style.boxShadow = 'none';
        });
    });
});

// Fonction pour basculer entre vue tableau et vue cartes
function toggleTableView() {
    const table = document.querySelector('table');
    const tableBody = table?.querySelector('tbody');
    
    if (!tableBody) return;
    
    // Ici, tu pourrais implémenter la logique pour basculer entre différentes vues
    // Pour l'instant, nous allons juste montrer une notification
    showNotification('🔄 Changement de vue disponible dans la prochaine version', 'info');
}

// Fonction de notification améliorée
function showNotification(message, type = 'info') {
    // Supprimer les notifications existantes
    const existingNotifications = document.querySelectorAll('.modern-notification');
    existingNotifications.forEach(notif => notif.remove());
    
    // Créer la notification
    const notification = document.createElement('div');
    notification.className = `modern-notification fixed top-6 right-6 z-50`;
    
    const colors = {
        success: { bg: 'bg-gradient-to-r from-green-500 to-emerald-500', icon: 'fa-check-circle' },
        error: { bg: 'bg-gradient-to-r from-red-500 to-pink-500', icon: 'fa-exclamation-circle' },
        info: { bg: 'bg-gradient-to-r from-blue-500 to-cyan-500', icon: 'fa-info-circle' },
        warning: { bg: 'bg-gradient-to-r from-yellow-500 to-amber-500', icon: 'fa-exclamation-triangle' }
    };
    
    const config = colors[type] || colors.info;
    
    notification.innerHTML = `
        <div class="flex items-center ${config.bg} text-white px-6 py-4 rounded-2xl shadow-2xl animate-slideInRight">
            <i class="fas ${config.icon} text-xl mr-3"></i>
            <span class="font-medium">${message}</span>
            <button onclick="this.closest('.modern-notification').remove()" 
                    class="ml-4 text-white/80 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Supprimer automatiquement après 5 secondes
    setTimeout(() => {
        if (notification.parentNode) {
            notification.querySelector('div').classList.add('animate-slideOutRight');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        }
    }, 5000);
}

// Animation CSS pour les notifications
const notificationStyle = document.createElement('style');
notificationStyle.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .animate-slideInRight {
        animation: slideInRight 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .animate-slideOutRight {
        animation: slideOutRight 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
`;
document.head.appendChild(notificationStyle);

// Les autres fonctions JavaScript restent les mêmes mais avec les nouvelles classes
function updateDateRange(period) {
    const customRange = document.getElementById('customDateRange');
    const dateFromInput = document.querySelector('input[name="date_from"]');
    const dateToInput = document.querySelector('input[name="date_to"]');
    
    if (period === 'custom') {
        customRange.classList.remove('hidden');
        customRange.style.animation = 'fadeIn 0.3s ease-out';
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
    
    // Ajouter une animation de confirmation
    showNotification('🎛️ Filtres réinitialisés', 'success');
    
    // Soumettre le formulaire après un délai
    setTimeout(() => {
        document.getElementById('filterForm').submit();
    }, 800);
}

// Fonctions des modals restent les mêmes
function openExportModal() {
    // Implémenter l'ouverture du modal d'export
    showNotification('📥 Fonction d\'export disponible bientôt', 'info');
}

function viewTransactionDetails(transactionId) {
    // Implémenter la vue des détails
    showNotification('🔍 Affichage des détails de la transaction', 'info');
}

function copyTransactionRef(reference) {
    if (!reference) {
        showNotification('❌ Aucune référence à copier', 'error');
        return;
    }
    
    navigator.clipboard.writeText(reference).then(() => {
        showNotification('✅ Référence copiée dans le presse-papier', 'success');
    }).catch(() => {
        showNotification('❌ Impossible de copier la référence', 'error');
    });
}

function cancelTransaction(transactionId) {
    if (confirm('Êtes-vous sûr de vouloir annuler cette transaction ? Cette action est irréversible.')) {
        // Simulation d'annulation
        showNotification('⏳ Annulation de la transaction en cours...', 'info');
        
        // Simuler un délai de traitement
        setTimeout(() => {
            showNotification('✅ Transaction annulée avec succès', 'success');
            // Rafraîchir la page après annulation
            setTimeout(() => window.location.reload(), 1000);
        }, 1500);
    }
}

function openDepotModal() {
    window.location.href = '?page=investissement';
}
</script>