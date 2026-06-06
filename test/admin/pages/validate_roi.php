<?php
// test/admin/pages/validate_roi.php
require_once __DIR__ . '/../../../functions/manual_roi.php';

// Récupérer les plans éligibles
$eligiblePlans = getEligibleRoiPlans($pdo);
$todayStats = getTodayRoiStats($pdo);

// Calculer le montant total à distribuer
$totalToDistribute = 0;
foreach ($eligiblePlans as $plan) {
    $totalToDistribute += floatval($plan['roi_journalier']);
}
?>

<div class="fade-in">
    <!-- En-tête avec statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Plans éligibles -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm mb-1">Plans éligibles</p>
                    <p class="text-4xl font-bold"><?= count($eligiblePlans) ?></p>
                    <p class="text-blue-100 text-xs mt-2">
                        <i class="fas fa-clock mr-1"></i>
                        En attente de validation
                    </p>
                </div>
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-list-check text-3xl"></i>
                </div>
            </div>
        </div>

        <!-- Montant à distribuer -->
        <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm mb-1">Montant total</p>
                    <p class="text-4xl font-bold"><?= number_format($totalToDistribute, 0) ?></p>
                    <p class="text-green-100 text-xs mt-2">
                        <i class="fas fa-coins mr-1"></i>
                        FCFA à distribuer
                    </p>
                </div>
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-3xl"></i>
                </div>
            </div>
        </div>

        <!-- Déjà validés aujourd'hui -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm mb-1">Validés aujourd'hui</p>
                    <p class="text-4xl font-bold"><?= $todayStats['count_validated'] ?></p>
                    <p class="text-purple-100 text-xs mt-2">
                        <i class="fas fa-check-circle mr-1"></i>
                        <?= number_format($todayStats['total_amount'], 0) ?> FCFA
                    </p>
                </div>
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-double text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Bouton de validation -->
    <?php if (count($eligiblePlans) > 0): ?>
    <div class="bg-white rounded-2xl shadow-sm p-6 mb-8 border border-gray-200">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">
                    <i class="fas fa-coins text-yellow-500 mr-2"></i>
                    Validation des ROI journaliers
                </h3>
                <p class="text-gray-600 text-sm">
                    Cliquez sur le bouton pour valider tous les ROI éligibles en une seule fois.
                    Cette action créditera automatiquement les portefeuilles des utilisateurs.
                </p>
            </div>
            <button 
                onclick="validateAllRoi()" 
                class="px-8 py-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl font-bold text-lg hover:shadow-xl transition-all duration-300 hover:scale-105 whitespace-nowrap">
                <i class="fas fa-check-circle mr-2"></i>
                Valider tous les ROI
            </button>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-blue-50 border-l-4 border-blue-400 p-6 rounded-lg mb-8">
        <div class="flex items-center">
            <i class="fas fa-info-circle text-blue-500 text-2xl mr-4"></i>
            <div>
                <h3 class="text-blue-800 font-bold mb-1">Aucun ROI à valider</h3>
                <p class="text-blue-700 text-sm">
                    Tous les ROI éligibles ont déjà été crédités aujourd'hui, ou il n'y a aucun plan actif.
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Liste des plans éligibles -->
    <?php if (count($eligiblePlans) > 0): ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200 bg-gray-50">
            <h3 class="text-xl font-bold text-gray-800">
                <i class="fas fa-list mr-2 text-gray-600"></i>
                Plans éligibles (<?= count($eligiblePlans) ?>)
            </h3>
            <p class="text-sm text-gray-600 mt-1">Liste des investissements actifs en attente de validation ROI</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Utilisateur
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Plan
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Montant investi
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            ROI Journalier
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Jours actifs
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Solde actuel
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Statut
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($eligiblePlans as $plan): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold mr-3">
                                    <?= strtoupper(substr($plan['user_prenom'], 0, 1)) ?>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800">
                                        <?= htmlspecialchars($plan['user_prenom'] . ' ' . $plan['user_nom']) ?>
                                    </p>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($plan['email']) ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <i class="fas fa-chart-line mr-2"></i>
                                <?= htmlspecialchars($plan['plan_nom']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-semibold text-gray-800">
                                <?= number_format($plan['montant_investi'], 0, ',', ' ') ?> FCFA
                            </p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-bold text-green-600 text-lg">
                                +<?= number_format($plan['roi_journalier'], 0, ',', ' ') ?> FCFA
                            </p>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <i class="fas fa-calendar-day text-gray-400 mr-2"></i>
                                <span class="text-gray-700"><?= $plan['jours_actifs'] ?> jours</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-700">
                                <?= number_format($plan['solde_investissement'], 0, ',', ' ') ?> FCFA
                            </p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-1"></i>
                                En attente
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-gray-50 border-t-2 border-gray-300">
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-right font-bold text-gray-800">
                            TOTAL À DISTRIBUER :
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-bold text-green-600 text-xl">
                                +<?= number_format($totalToDistribute, 0, ',', ' ') ?> FCFA
                            </p>
                        </td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Historique des validations récentes -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mt-8 p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">
            <i class="fas fa-history mr-2 text-gray-600"></i>
            Historique des validations (Aujourd'hui)
        </h3>
        
        <?php
        $historyQuery = $pdo->prepare("
            SELECT 
                rh.montant,
                rh.date_versement,
                u.nom,
                u.prenom,
                p.nom as plan_nom
            FROM roi_history rh
            JOIN users u ON rh.user_id = u.id
            JOIN user_plans up ON rh.user_plan_id = up.id
            JOIN plans p ON up.plan_id = p.id
            WHERE DATE(rh.date_versement) = CURDATE()
            ORDER BY rh.date_versement DESC
            LIMIT 20
        ");
        $historyQuery->execute();
        $history = $historyQuery->fetchAll(PDO::FETCH_ASSOC);
        ?>
        
        <?php if (count($history) > 0): ?>
        <div class="space-y-3">
            <?php foreach ($history as $item): ?>
            <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg border border-green-200">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white mr-3">
                        <i class="fas fa-check"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-800">
                            <?= htmlspecialchars($item['prenom'] . ' ' . $item['nom']) ?>
                        </p>
                        <p class="text-sm text-gray-600">
                            <?= htmlspecialchars($item['plan_nom']) ?>
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="font-bold text-green-600">
                        +<?= number_format($item['montant'], 0, ',', ' ') ?> FCFA
                    </p>
                    <p class="text-xs text-gray-500">
                        <?= date('H:i:s', strtotime($item['date_versement'])) ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-inbox text-4xl mb-3"></i>
            <p>Aucune validation effectuée aujourd'hui</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de résultats -->
<div id="resultsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-chart-bar mr-2 text-blue-500"></i>
                Résultats de la validation
            </h3>
        </div>
        <div id="resultsContent" class="p-6">
            <!-- Le contenu sera injecté ici par JavaScript -->
        </div>
        <div class="p-6 border-t border-gray-200 flex justify-end">
            <button onclick="closeResultsModal()" class="px-6 py-3 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300 transition-colors">
                Fermer
            </button>
        </div>
    </div>
</div>

<script>
function validateAllRoi() {
    // Confirmation
    if (!confirm('Êtes-vous sûr de vouloir valider tous les ROI journaliers ?\n\nCette action créditera les portefeuilles de <?= count($eligiblePlans) ?> utilisateur(s) pour un montant total de <?= number_format($totalToDistribute, 0, ',', ' ') ?> FCFA.')) {
        return;
    }

    // Afficher un loader
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Validation en cours...';

    // Appel AJAX
    fetch('ajax_controller.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=validate_all_roi'
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;

        if (data.success) {
            showResults(data.data);
            
            // Recharger la page après 3 secondes
            setTimeout(() => {
                window.location.reload();
            }, 3000);
        } else {
            alert('Erreur lors de la validation : ' + (data.error || 'Erreur inconnue'));
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        alert('Erreur de connexion : ' + error.message);
    });
}

function showResults(summary) {
    const content = document.getElementById('resultsContent');
    
    let html = `
        <div class="space-y-6">
            <!-- Statistiques -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                    <p class="text-sm text-green-600 mb-1">Plans validés</p>
                    <p class="text-3xl font-bold text-green-700">${summary.validated}</p>
                </div>
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <p class="text-sm text-blue-600 mb-1">Montant total</p>
                    <p class="text-3xl font-bold text-blue-700">${summary.total_amount.toLocaleString('fr-FR')} FCFA</p>
                </div>
            </div>

            ${summary.skipped > 0 ? `
            <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                <p class="text-sm text-yellow-600 mb-1">Plans ignorés</p>
                <p class="text-2xl font-bold text-yellow-700">${summary.skipped}</p>
            </div>
            ` : ''}

            <!-- Message -->
            <div class="bg-green-100 border-l-4 border-green-500 p-4 rounded">
                <p class="text-green-800 font-medium">
                    <i class="fas fa-check-circle mr-2"></i>
                    ${summary.message}
                </p>
            </div>

            <!-- Erreurs -->
            ${summary.errors && summary.errors.length > 0 ? `
            <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                <h4 class="font-bold text-red-800 mb-3">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Erreurs rencontrées (${summary.errors.length})
                </h4>
                <div class="space-y-2 max-h-60 overflow-y-auto">
                    ${summary.errors.map(error => `
                        <div class="text-sm text-red-700 bg-white p-2 rounded">
                            <strong>${error.user_name || 'Utilisateur #' + error.user_id}</strong> - ${error.plan_name || ''}<br>
                            <span class="text-red-600">${error.reason}</span>
                        </div>
                    `).join('')}
                </div>
            </div>
            ` : ''}
        </div>
    `;
    
    content.innerHTML = html;
    document.getElementById('resultsModal').classList.remove('hidden');
}

function closeResultsModal() {
    document.getElementById('resultsModal').classList.add('hidden');
}

// Fermer le modal en cliquant en dehors
document.getElementById('resultsModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeResultsModal();
    }
});
</script>
