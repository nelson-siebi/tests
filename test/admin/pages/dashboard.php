<?php

require_once 'config/database.php';

// Vérifier l'authentification administrateur
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// 1. STATISTIQUES PRINCIPALES
$stats = [
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'total_investments' => $pdo->query("SELECT COUNT(*) FROM user_plans WHERE statut = 'active'")->fetchColumn(),
    'total_deposits' => $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM transactions WHERE type = 'depot' AND statut = 'success'")->fetchColumn(),
    'total_withdrawals' => $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM transactions WHERE type = 'retrait' AND statut = 'success'")->fetchColumn(),
    'pending_kyc' => $pdo->query("SELECT COUNT(*) FROM kyc WHERE statut = 'pending'")->fetchColumn(),
    'pending_withdrawals' => $pdo->query("SELECT COUNT(*) FROM transactions WHERE type = 'retrait' AND statut = 'attente'")->fetchColumn(),
];

// 2. DONNÉES POUR LES GRAPHIQUES
$today = date('Y-m-d');
$week_ago = date('Y-m-d', strtotime('-7 days'));
$month_ago = date('Y-m-d', strtotime('-30 days'));

// Données pour le graphique d'activité (7 derniers jours)
$activity_data = [];
$activity_dates = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-{$i} days"));
    $activity_dates[] = date('d/m', strtotime($date));
    
    // Utilisateurs inscrits ce jour
    $users_today = $pdo->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = '$date'")->fetchColumn();
    
    // Transactions ce jour
    $transactions_today = $pdo->query("SELECT COUNT(*) FROM transactions WHERE DATE(created_at) = '$date'")->fetchColumn();
    
    $activity_data['users'][] = $users_today;
    $activity_data['transactions'][] = $transactions_today;
}

// 3. DONNÉES POUR LE GRAPHIQUE DE TRANSACTIONS
$transaction_stats = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN type = 'depot' AND statut = 'success' THEN 1 ELSE 0 END) as deposits_success,
        SUM(CASE WHEN type = 'retrait' AND statut = 'success' THEN 1 ELSE 0 END) as withdrawals_success,
        SUM(CASE WHEN statut = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN statut = 'failed' THEN 1 ELSE 0 END) as failed
    FROM transactions
")->fetch(PDO::FETCH_ASSOC);

$transaction_chart_data = [
    $transaction_stats['deposits_success'] ?? 0,
    $transaction_stats['withdrawals_success'] ?? 0,
    $transaction_stats['pending'] ?? 0,
    $transaction_stats['failed'] ?? 0
];

// 4. DERNIÈRES TRANSACTIONS (10 plus récentes)
$recent_transactions = $pdo->query("
    SELECT t.*, u.nom, u.prenom, u.email 
    FROM transactions t 
    JOIN users u ON t.user_id = u.id 
    ORDER BY t.created_at DESC 
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// 5. DERNIERS UTILISATEURS INSCRITS (10 plus récents)
$recent_users = $pdo->query("
    SELECT id, nom, prenom, email, phone, created_at, statut 
    FROM users 
    ORDER BY created_at DESC 
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// 6. STATISTIQUES SUPPLÉMENTAIRES POUR LE TABLEAU DE BORD
$additional_stats = [];

// Statistiques du jour
$today_stats = $pdo->query("
    SELECT 
        (SELECT COUNT(*) FROM users WHERE DATE(created_at) = '$today') as new_users_today,
        (SELECT COUNT(*) FROM transactions WHERE DATE(created_at) = '$today' AND statut = 'success') as transactions_today,
        (SELECT COALESCE(SUM(montant), 0) FROM transactions WHERE DATE(created_at) = '$today' AND type = 'depot' AND statut = 'success') as deposits_today,
        (SELECT COALESCE(SUM(montant), 0) FROM transactions WHERE DATE(created_at) = '$today' AND type = 'retrait' AND statut = 'success') as withdrawals_today
")->fetch(PDO::FETCH_ASSOC);

// Statistiques des 7 derniers jours
$week_stats = $pdo->query("
    SELECT 
        (SELECT COUNT(*) FROM users WHERE created_at >= '$week_ago') as new_users_week,
        (SELECT COUNT(*) FROM transactions WHERE created_at >= '$week_ago') as transactions_week,
        (SELECT COALESCE(SUM(montant), 0) FROM transactions WHERE created_at >= '$week_ago' AND type = 'depot' AND statut = 'success') as deposits_week,
        (SELECT COALESCE(SUM(montant), 0) FROM transactions WHERE created_at >= '$week_ago' AND type = 'retrait' AND statut = 'success') as withdrawals_week
")->fetch(PDO::FETCH_ASSOC);

// Statistiques par source
$source_stats = $pdo->query("
    SELECT 
        source,
        COUNT(*) as count,
        COALESCE(SUM(montant), 0) as total_amount
    FROM transactions 
    WHERE statut = 'success'
    GROUP BY source
    ORDER BY total_amount DESC
")->fetchAll(PDO::FETCH_ASSOC);

// 7. PLANS LES PLUS POPULAIRES
$popular_plans = $pdo->query("
    SELECT 
        p.nom,
        p.prix,
        p.roi_journalier,
        COUNT(up.id) as total_subscriptions,
        SUM(up.montant_investi) as total_invested
    FROM plans p
    LEFT JOIN user_plans up ON p.id = up.plan_id
    WHERE p.actif = 1
    GROUP BY p.id
    ORDER BY total_subscriptions DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// 8. DONNÉES POUR LES INDICATEURS DE PERFORMANCE
$performance_indicators = [];

// Taux de conversion utilisateurs -> investisseurs
$total_investors = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM user_plans")->fetchColumn();
$conversion_rate = $stats['total_users'] > 0 ? ($total_investors / $stats['total_users']) * 100 : 0;

// Taux de succès des transactions
$total_transactions = $pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn();
$successful_transactions = $pdo->query("SELECT COUNT(*) FROM transactions WHERE statut = 'success'")->fetchColumn();
$success_rate = $total_transactions > 0 ? ($successful_transactions / $total_transactions) * 100 : 0;

// Revenu moyen par utilisateur
$avg_revenue_per_user = $stats['total_users'] > 0 ? ($stats['total_deposits'] / $stats['total_users']) : 0;

$performance_indicators = [
    'conversion_rate' => round($conversion_rate, 1),
    'success_rate' => round($success_rate, 1),
    'avg_revenue_per_user' => round($avg_revenue_per_user, 0),
    'total_investors' => $total_investors
];

// 9. ACTIVITÉS RÉCENTES POUR LES NOTIFICATIONS
$recent_activities = $pdo->query("
    SELECT 
        'depot' as type,
        CONCAT('Nouveau dépôt de ', FORMAT(montant, 0), ' FCFA') as description,
        created_at,
        user_id
    FROM transactions 
    WHERE type = 'depot' AND statut = 'success'
    UNION ALL
    SELECT 
        'retrait' as type,
        CONCAT('Nouveau retrait de ', FORMAT(montant, 0), ' FCFA') as description,
        created_at,
        user_id
    FROM transactions 
    WHERE type = 'retrait' AND statut = 'success'
    UNION ALL
    SELECT 
        'user' as type,
        CONCAT('Nouvel utilisateur inscrit') as description,
        created_at,
        id as user_id
    FROM users
    ORDER BY created_at DESC
    LIMIT 15
")->fetchAll(PDO::FETCH_ASSOC);

// 10. DONNÉES POUR LES ALERTES
$alerts = [];

// Alertes KYC en attente
if ($stats['pending_kyc'] > 0) {
    $alerts[] = [
        'type' => 'warning',
        'message' => "{$stats['pending_kyc']} demande(s) KYC en attente de validation",
        'icon' => 'fas fa-id-card'
    ];
}

// Alertes retraits en attente
if ($stats['pending_withdrawals'] > 0) {
    $alerts[] = [
        'type' => 'warning',
        'message' => "{$stats['pending_withdrawals']} retrait(s) en attente de traitement",
        'icon' => 'fas fa-money-check-alt'
    ];
}

// Alertes transactions échouées récentes
$failed_transactions_today = $pdo->query("
    SELECT COUNT(*) 
    FROM transactions 
    WHERE DATE(created_at) = '$today' AND statut = 'failed'
")->fetchColumn();

if ($failed_transactions_today > 0) {
    $alerts[] = [
        'type' => 'danger',
        'message' => "{$failed_transactions_today} transaction(s) ont échoué aujourd'hui",
        'icon' => 'fas fa-exclamation-triangle'
    ];
}

// Vérifier si des plans sont inactifs
$inactive_plans = $pdo->query("SELECT COUNT(*) FROM plans WHERE actif = 0")->fetchColumn();
if ($inactive_plans > 0) {
    $alerts[] = [
        'type' => 'info',
        'message' => "{$inactive_plans} plan(s) d'investissement sont inactifs",
        'icon' => 'fas fa-box'
    ];
}

// 11. DONNÉES POUR LES TENDANCES
$trends = [];

// Tendance des inscriptions (mois courant vs mois précédent)
$current_month_users = $pdo->query("
    SELECT COUNT(*) 
    FROM users 
    WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
    AND YEAR(created_at) = YEAR(CURRENT_DATE())
")->fetchColumn();

$previous_month_users = $pdo->query("
    SELECT COUNT(*) 
    FROM users 
    WHERE MONTH(created_at) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)) 
    AND YEAR(created_at) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))
")->fetchColumn();

$user_growth = $previous_month_users > 0 
    ? (($current_month_users - $previous_month_users) / $previous_month_users) * 100 
    : ($current_month_users > 0 ? 100 : 0);

// Tendance des dépôts
$current_month_deposits = $pdo->query("
    SELECT COALESCE(SUM(montant), 0) 
    FROM transactions 
    WHERE type = 'depot' 
    AND statut = 'success'
    AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
    AND YEAR(created_at) = YEAR(CURRENT_DATE())
")->fetchColumn();

$previous_month_deposits = $pdo->query("
    SELECT COALESCE(SUM(montant), 0) 
    FROM transactions 
    WHERE type = 'depot' 
    AND statut = 'success'
    AND MONTH(created_at) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)) 
    AND YEAR(created_at) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))
")->fetchColumn();

$deposit_growth = $previous_month_deposits > 0 
    ? (($current_month_deposits - $previous_month_deposits) / $previous_month_deposits) * 100 
    : ($current_month_deposits > 0 ? 100 : 0);

$trends = [
    'user_growth' => round($user_growth, 1),
    'deposit_growth' => round($deposit_growth, 1),
    'current_month_users' => $current_month_users,
    'current_month_deposits' => $current_month_deposits
];

// 12. FORMATAGE DES DONNÉES POUR L'AFFICHAGE
function format_number($number, $decimals = 0) {
    return number_format($number, $decimals, ',', ' ');
}

function format_money($amount, $currency = 'FCFA') {
    return format_number($amount, 0) . ' ' . $currency;
}

function get_status_badge($status) {
    $badges = [
        'success' => 'bg-green-100 text-green-800',
        'pending' => 'bg-yellow-100 text-yellow-800',
        'failed' => 'bg-red-100 text-red-800',
        'active' => 'bg-green-100 text-green-800',
        'banned' => 'bg-red-100 text-red-800',
        'attente' => 'bg-yellow-100 text-yellow-800',
        'annule' => 'bg-gray-100 text-gray-800'
    ];
    
    return $badges[$status] ?? 'bg-gray-100 text-gray-800';
}

function get_type_badge($type) {
    if ($type == 'depot') {
        return 'bg-green-100 text-green-800';
    } elseif ($type == 'retrait') {
        return 'bg-blue-100 text-blue-800';
    }
    return 'bg-gray-100 text-gray-800';
}

// 13. CALCUL DES POURCENTAGES POUR LES INDICATEURS
$stats_with_percentages = [];

// Pourcentage d'utilisateurs actifs
$active_users = $pdo->query("SELECT COUNT(*) FROM users WHERE statut = 'active'")->fetchColumn();
$stats_with_percentages['active_users_percentage'] = $stats['total_users'] > 0 
    ? round(($active_users / $stats['total_users']) * 100, 1) 
    : 0;

// Pourcentage de retraits vs dépôts
$stats_with_percentages['withdrawal_vs_deposit_percentage'] = $stats['total_deposits'] > 0 
    ? round(($stats['total_withdrawals'] / $stats['total_deposits']) * 100, 1) 
    : 0;

// Pourcentage d'investissements actifs
$total_user_plans = $pdo->query("SELECT COUNT(*) FROM user_plans")->fetchColumn();
$stats_with_percentages['active_investments_percentage'] = $total_user_plans > 0 
    ? round(($stats['total_investments'] / $total_user_plans) * 100, 1) 
    : 0;

// 14. PRÉPARATION DES DONNÉES POUR JAVASCRIPT
$js_data = [
    'activity_labels' => $activity_dates,
    'activity_users' => $activity_data['users'],
    'activity_transactions' => $activity_data['transactions'],
    'transaction_chart_data' => $transaction_chart_data,
    'today_stats' => $today_stats,
    'week_stats' => $week_stats,
    'trends' => $trends,
    'performance' => $performance_indicators
];

// Convertir en JSON pour utilisation dans JavaScript
$js_data_json = json_encode($js_data);
?>

<div class="p-4 lg:p-6">
    <!-- Statistiques -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-6 lg:mb-8">
        <div class="stat-card bg-white p-4 lg:p-6 rounded-lg shadow border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="p-2 lg:p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-users text-lg lg:text-xl"></i>
                </div>
                <div class="ml-3 lg:ml-4">
                    <p class="text-xs lg:text-sm text-gray-500">Utilisateurs</p>
                    <p class="text-xl lg:text-2xl font-bold"><span class="stat-value" data-value="<?= $stats['total_users'] ?>"><?= number_format($stats['total_users']) ?></span></p>
                </div>
            </div>

            <div class="mt-4 text-sm text-gray-600">
    <i class="fas fa-clock mr-2"></i>
    <span id="liveTime"><?php echo date('H:i:s'); ?></span> • 
    <span id="liveDate"><?php echo date('l, d F Y'); ?></span>
</div>
        </div>
        
        <div class="stat-card bg-white p-4 lg:p-6 rounded-lg shadow border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="p-2 lg:p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-hand-holding-usd text-lg lg:text-xl"></i>
                </div>
                <div class="ml-3 lg:ml-4">
                    <p class="text-xs lg:text-sm text-gray-500">Investissements</p>
                    <p class="text-xl lg:text-2xl font-bold"><span class="stat-value" data-value="<?= $stats['total_investments'] ?>"><?= number_format($stats['total_investments']) ?></span></p>
                </div>
            </div>
        </div>
        
        <div class="stat-card bg-white p-4 lg:p-6 rounded-lg shadow border-l-4 border-purple-500">
            <div class="flex items-center">
                <div class="p-2 lg:p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-money-bill-wave text-lg lg:text-xl"></i>
                </div>
                <div class="ml-3 lg:ml-4">
                    <p class="text-xs lg:text-sm text-gray-500">Total Dépôts</p>
                    <p class="text-xl lg:text-2xl font-bold"><span class="stat-value" data-value="<?= $stats['total_deposits'] ?? 0 ?>"><?= number_format($stats['total_deposits'] ?? 0, 0) ?></span> FCFA</p>
                </div>
            </div>
        </div>
        
        <div class="stat-card bg-white p-4 lg:p-6 rounded-lg shadow border-l-4 border-red-500">
            <div class="flex items-center">
                <div class="p-2 lg:p-3 rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-credit-card text-lg lg:text-xl"></i>
                </div>
                <div class="ml-3 lg:ml-4">
                    <p class="text-xs lg:text-sm text-gray-500">Total Retraits</p>
                    <p class="text-xl lg:text-2xl font-bold"><span class="stat-value" data-value="<?= $stats['total_withdrawals'] ?? 0 ?>"><?= number_format($stats['total_withdrawals'] ?? 0, 0) ?></span> FCFA</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique et autres sections avec améliorations responsive -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6 mb-6 lg:mb-8">
        <div class="bg-white p-4 lg:p-6 rounded-lg shadow">
            <h3 class="text-base lg:text-lg font-semibold mb-3 lg:mb-4">Activité Récente</h3>
            <div class="h-64 lg:h-72">
                <canvas id="activityChart"></canvas>
            </div>
        </div>
        
        <div class="bg-white p-4 lg:p-6 rounded-lg shadow">
            <h3 class="text-base lg:text-lg font-semibold mb-3 lg:mb-4">Statut des Transactions</h3>
            <div class="h-64 lg:h-72">
                <canvas id="transactionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tableaux avec overflow horizontal sur mobile -->
    <div class="space-y-6">
        <!-- Dernières transactions -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 lg:p-6 border-b">
                <h3 class="text-base lg:text-lg font-semibold">Dernières Transactions</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[600px]">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Utilisateur</th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach($recent_transactions as $transaction): ?>
                        <tr>
                            <td class="px-4 lg:px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 truncate max-w-[150px]">
                                    <?= htmlspecialchars($transaction['nom'] . ' ' . $transaction['prenom']) ?>
                                </div>
                                <div class="text-xs text-gray-500 truncate max-w-[150px]"><?= htmlspecialchars($transaction['email']) ?></div>
                            </td>
                            <td class="px-4 lg:px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full <?= $transaction['type'] == 'depot' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' ?>">
                                    <?= $transaction['type'] == 'depot' ? 'Dépôt' : 'Retrait' ?>
                                </span>
                            </td>
                            <td class="px-4 lg:px-6 py-4 text-sm font-medium">
                                <?= number_format($transaction['montant'], 0) ?> FCFA
                            </td>
                            <td class="px-4 lg:px-6 py-4">
                                <?php
                                $status_classes = [
                                    'attente' => 'bg-yellow-100 text-yellow-800',
                                    'success' => 'bg-green-100 text-green-800',
                                    'failed' => 'bg-red-100 text-red-800',
                                    'annule' => 'bg-gray-100 text-gray-800'
                                ];
                                ?>
                                <span class="px-2 py-1 text-xs rounded-full <?= $status_classes[$transaction['statut']] ?? 'bg-gray-100' ?>">
                                    <?= $transaction['statut'] ?>
                                </span>
                            </td>
                            <td class="px-4 lg:px-6 py-4 text-xs lg:text-sm text-gray-500">
                                <?= date('d/m H:i', strtotime($transaction['created_at'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Derniers utilisateurs -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 lg:p-6 border-b">
                <h3 class="text-base lg:text-lg font-semibold">Derniers Utilisateurs</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[600px]">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Téléphone</th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Inscription</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach($recent_users as $user): ?>
                        <tr>
                            <td class="px-4 lg:px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 lg:h-10 lg:w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                        <i class="fas fa-user text-gray-500 text-sm lg:text-base"></i>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900 truncate max-w-[120px]">
                                            <?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 lg:px-6 py-4 text-sm text-gray-900 truncate max-w-[150px]"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="px-4 lg:px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($user['phone']) ?></td>
                            <td class="px-4 lg:px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full <?= $user['statut'] == 'active' ? 'bg-green-100 text-green-800' : ($user['statut'] == 'banned' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') ?>">
                                    <?= $user['statut'] ?>
                                </span>
                            </td>
                            <td class="px-4 lg:px-6 py-4 text-xs lg:text-sm text-gray-500">
                                <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
// Récupérer les données PHP dans JavaScript
const dashboardData = <?php echo $js_data_json; ?>;

// Graphique d'activité responsive avec données réelles
const activityCtx = document.getElementById('activityChart').getContext('2d');
const activityChart = new Chart(activityCtx, {
    type: 'line',
    data: {
        labels: dashboardData.activity_labels,
        datasets: [{
            label: 'Utilisateurs',
            data: dashboardData.activity_users,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            fill: true,
            tension: 0.4
        }, {
            label: 'Transactions',
            data: dashboardData.activity_transactions,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    boxWidth: 12,
                    font: {
                        size: 11
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return `${context.dataset.label}: ${context.parsed.y}`;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    font: {
                        size: 10
                    },
                    callback: function(value) {
                        return value;
                    }
                }
            },
            x: {
                ticks: {
                    font: {
                        size: 10
                    }
                }
            }
        }
    }
});

// Graphique des transactions responsive avec données réelles
const transactionCtx = document.getElementById('transactionChart').getContext('2d');
const transactionChart = new Chart(transactionCtx, {
    type: 'doughnut',
    data: {
        labels: ['Dépôts réussis', 'Retraits réussis', 'En attente', 'Échoués'],
        datasets: [{
            data: dashboardData.transaction_chart_data,
            backgroundColor: [
                '#10b981',
                '#3b82f6',
                '#f59e0b',
                '#ef4444'
            ],
            borderWidth: 1,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 12,
                    font: {
                        size: 11
                    },
                    padding: 15
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = Math.round((context.parsed / total) * 100);
                        return `${context.label}: ${context.parsed} (${percentage}%)`;
                    }
                }
            }
        },
        cutout: '60%'
    }
});

// Mettre à jour les statistiques avec animation
document.addEventListener('DOMContentLoaded', function() {
    // Animer les chiffres des statistiques
    // Animer les chiffres des statistiques
    const statElements = document.querySelectorAll('.stat-value');
    
    statElements.forEach(element => {
        const finalValue = parseFloat(element.dataset.value);
        let currentValue = 0;
        const duration = 1500;
        const steps = 60;
        const increment = finalValue / steps;
        const intervalTime = duration / steps;
        
        // Si la valeur est 0 ou invalide, ne pas animer
        if (!finalValue || isNaN(finalValue)) {
            return;
        }

        const timer = setInterval(() => {
            currentValue += increment;
            if (currentValue >= finalValue) {
                element.textContent = Math.round(finalValue).toLocaleString().replace(/\s/g, ' '); // Format avec espaces
                clearInterval(timer);
            } else {
                element.textContent = Math.round(currentValue).toLocaleString().replace(/\s/g, ' ');
            }
        }, intervalTime);
    });
    
    // Afficher les indicateurs de performance
    if (dashboardData.performance) {
        const performanceHTML = `
            <div class="mt-4 grid grid-cols-2 gap-4">
                <div class="text-center">
                    <div class="text-sm text-gray-500">Taux de conversion</div>
                    <div class="text-lg font-bold ${dashboardData.performance.conversion_rate >= 10 ? 'text-green-600' : 'text-yellow-600'}">
                        ${dashboardData.performance.conversion_rate}%
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-sm text-gray-500">Taux de succès</div>
                    <div class="text-lg font-bold ${dashboardData.performance.success_rate >= 95 ? 'text-green-600' : 'text-yellow-600'}">
                        ${dashboardData.performance.success_rate}%
                    </div>
                </div>
            </div>
        `;
        
        // Ajouter cette section après les statistiques principales
        const statsContainer = document.querySelector('.grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-4');
        if (statsContainer) {
            statsContainer.insertAdjacentHTML('afterend', performanceHTML);
        }
    }
    
    // Afficher les tendances
    if (dashboardData.trends) {
        const trendsHTML = `
            <div class="mt-6 bg-blue-50 rounded-lg p-4">
                <h4 class="font-semibold text-blue-800 mb-2">📈 Tendances</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-sm text-blue-600">Croissance utilisateurs</div>
                        <div class="text-lg font-bold ${dashboardData.trends.user_growth >= 0 ? 'text-green-600' : 'text-red-600'}">
                            ${dashboardData.trends.user_growth >= 0 ? '+' : ''}${dashboardData.trends.user_growth}%
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-blue-600">Croissance dépôts</div>
                        <div class="text-lg font-bold ${dashboardData.trends.deposit_growth >= 0 ? 'text-green-600' : 'text-red-600'}">
                            ${dashboardData.trends.deposit_growth >= 0 ? '+' : ''}${dashboardData.trends.deposit_growth}%
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Ajouter cette section après les graphiques
        const chartsContainer = document.querySelector('.grid.grid-cols-1.lg\\:grid-cols-2');
        if (chartsContainer) {
            chartsContainer.insertAdjacentHTML('afterend', trendsHTML);
        }
    }
});

// Redimensionner les graphiques lors du redimensionnement de la fenêtre
window.addEventListener('resize', function() {
    activityChart.resize();
    transactionChart.resize();
});

// Fonction pour actualiser les données du dashboard
function refreshDashboard() {
    // Afficher un indicateur de chargement
    const refreshBtn = document.querySelector('#refreshBtn');
    if (refreshBtn) {
        refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        refreshBtn.disabled = true;
    }
    
    // Rafraîchir la page après 2 secondes
    setTimeout(() => {
        window.location.reload();
    }, 2000);
}

// Ajouter un bouton de rafraîchissement
const refreshButtonHTML = `
    <button id="refreshBtn" onclick="refreshDashboard()" 
            class="fixed bottom-4 right-4 bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-full shadow-lg z-50">
        <i class="fas fa-sync-alt"></i>
    </button>
`;

document.body.insertAdjacentHTML('beforeend', refreshButtonHTML);

// Mettre à jour l'heure en temps réel
function updateLiveTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('fr-FR', { 
        hour: '2-digit', 
        minute: '2-digit',
        second: '2-digit'
    });
    
    const dateString = now.toLocaleDateString('fr-FR', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    const timeElement = document.querySelector('#liveTime');
    const dateElement = document.querySelector('#liveDate');
    
    if (timeElement) timeElement.textContent = timeString;
    if (dateElement) dateElement.textContent = dateString;
}

// Démarrer l'horloge en temps réel
setInterval(updateLiveTime, 1000);
updateLiveTime(); // Initial call
</script>