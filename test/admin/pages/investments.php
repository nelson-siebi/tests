<?php
// Récupérer tous les investissements avec pagination
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Filtres
$status_filter = $_GET['status'] ?? '';
$plan_filter = $_GET['plan'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

$where = [];
$params = [];

if($status_filter) {
    $where[] = "up.statut = ?";
    $params[] = $status_filter;
}

if($plan_filter) {
    $where[] = "up.plan_id = ?";
    $params[] = $plan_filter;
}

if($date_from) {
    $where[] = "DATE(up.date_debut) >= ?";
    $params[] = $date_from;
}

if($date_to) {
    $where[] = "DATE(up.date_debut) <= ?";
    $params[] = $date_to;
}

$where_clause = $where ? "WHERE " . implode(" AND ", $where) : "";

// Compter
$count_stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM user_plans up
    $where_clause
");
$count_stmt->execute($params);
$total_investments = $count_stmt->fetchColumn();
$total_pages = ceil($total_investments / $limit);

// Récupérer les investissements
$stmt = $pdo->prepare("
    SELECT up.*, 
           u.nom, u.prenom, u.email, u.phone,
           p.nom as plan_nom, p.prix, p.roi_journalier, p.duree_jours,
           (SELECT COALESCE(SUM(montant), 0) FROM roi_history WHERE user_plan_id = up.id) as total_roi_gagne
    FROM user_plans up
    JOIN users u ON up.user_id = u.id
    JOIN plans p ON up.plan_id = p.id
    $where_clause
    ORDER BY up.created_at DESC
    LIMIT  $limit OFFSET $offset
");


$stmt->execute();
$investments = $stmt->fetchAll();

// Récupérer la liste des plans pour le filtre
$plans = $pdo->query("SELECT id, nom FROM plans WHERE actif = 1")->fetchAll();

// Actions
if(isset($_POST['action'])) {
    $investment_id = (int)$_POST['investment_id'];
    
    switch($_POST['action']) {
        case 'terminate':
            $pdo->prepare("UPDATE user_plans SET statut = 'termine', date_fin = NOW() WHERE id = ?")
                ->execute([$investment_id]);
            $success = "Investissement terminé avec succès";
            break;
            
        case 'cancel':
            $pdo->prepare("UPDATE user_plans SET statut = 'annule', date_fin = NOW() WHERE id = ?")
                ->execute([$investment_id]);
            $success = "Investissement annulé avec succès";
            break;
            
        case 'add_roi':
            $amount = (float)$_POST['roi_amount'];
            $note = $_POST['note'] ?? '';
            
            // Ajouter le ROI
            $pdo->prepare("
                INSERT INTO roi_history (user_id, user_plan_id, montant, date_versement, note)
                VALUES (?, ?, ?, NOW(), ?)
            ")->execute([
                $_POST['user_id'],
                $investment_id,
                $amount,
                $note
            ]);
            
            // Mettre à jour le solde investissement
            $pdo->prepare("
                UPDATE wallets 
                SET solde_investissement = solde_investissement + ? 
                WHERE user_id = ?
            ")->execute([$amount, $_POST['user_id']]);
            
            $success = "ROI ajouté avec succès";
            break;
    }
    
    // Redirection JavaScript au lieu de header() pour éviter les erreurs
    echo '<script>window.location.href = "?page=investments";</script>';
    exit;
}
?>

<div class="p-4 lg:p-6">
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center mb-6 gap-4">
        <h3 class="text-xl font-bold">Gestion des Investissements</h3>
        <div class="text-sm text-gray-600">
            <?= number_format($total_investments) ?> investissement(s)
        </div>
    </div>

    <?php if(isset($success)): ?>
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            <i class="fas fa-check-circle mr-2"></i>
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <!-- Filtres -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <input type="hidden" name="page" value="investments">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                <select name="status" class="w-full px-3 py-2 border rounded-lg">
                    <option value="">Tous les statuts</option>
                    <option value="active" <?= $status_filter == 'active' ? 'selected' : '' ?>>Actif</option>
                    <option value="termine" <?= $status_filter == 'termine' ? 'selected' : '' ?>>Terminé</option>
                    <option value="expire" <?= $status_filter == 'expire' ? 'selected' : '' ?>>Expiré</option>
                    <option value="annule" <?= $status_filter == 'annule' ? 'selected' : '' ?>>Annulé</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Plan</label>
                <select name="plan" class="w-full px-3 py-2 border rounded-lg">
                    <option value="">Tous les plans</option>
                    <?php foreach($plans as $plan): ?>
                        <option value="<?= $plan['id'] ?>" <?= $plan_filter == $plan['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($plan['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date début (du)</label>
                <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>"
                       class="w-full px-3 py-2 border rounded-lg">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date début (au)</label>
                <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>"
                       class="w-full px-3 py-2 border rounded-lg">
            </div>
            
            <div class="md:col-span-2 lg:col-span-4 flex space-x-3">
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                    <i class="fas fa-filter mr-2"></i>Filtrer
                </button>
                
                <?php if($status_filter || $plan_filter || $date_from || $date_to): ?>
                    <a href="?page=investments" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                        <i class="fas fa-times mr-2"></i>Effacer
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tableau des investissements -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1000px]">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Utilisateur</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Période</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ROI Gagné</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach($investments as $investment): 
                        $days_passed = (strtotime(date('Y-m-d')) - strtotime($investment['date_debut'])) / (60 * 60 * 24);
                        $days_remaining = max(0, $investment['duree_jours'] - $days_passed);
                        $progress = min(100, ($days_passed / $investment['duree_jours']) * 100);
                    ?>
                    <tr>
                        <td class="px-4 lg:px-6 py-4 text-sm font-mono">
                            #<?= str_pad($investment['id'], 6, '0', STR_PAD_LEFT) ?>
                        </td>
                        <td class="px-4 lg:px-6 py-4">
                            <div class="text-sm font-medium text-gray-900 truncate max-w-[150px]">
                                <?= htmlspecialchars($investment['nom'] . ' ' . $investment['prenom']) ?>
                            </div>
                            <div class="text-xs text-gray-500"><?= htmlspecialchars($investment['email']) ?></div>
                        </td>
                        <td class="px-4 lg:px-6 py-4">
                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($investment['plan_nom']) ?></div>
                            <div class="text-xs text-gray-500">
                                ROI journalier: <?= number_format($investment['roi_journalier'], 0) ?> FCFA
                            </div>
                        </td>
                        <td class="px-4 lg:px-6 py-4">
                            <div class="text-sm font-bold text-blue-600">
                                <?= number_format($investment['montant_investi'], 0) ?> FCFA
                            </div>
                        </td>
                        <td class="px-4 lg:px-6 py-4">
                            <div class="space-y-1">
                                <div class="text-sm">
                                    <span class="text-gray-600">Début:</span>
                                    <span class="font-medium"><?= date('d/m/Y', strtotime($investment['date_debut'])) ?></span>
                                </div>
                                <div class="text-sm">
                                    <span class="text-gray-600">Fin:</span>
                                    <span class="font-medium"><?= date('d/m/Y', strtotime($investment['date_fin'])) ?></span>
                                </div>
                                <div class="text-xs">
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                                            <div class="bg-blue-600 h-1.5 rounded-full" 
                                                 style="width: <?= $progress ?>%"></div>
                                        </div>
                                        <span class="ml-2 text-gray-600"><?= round($progress) ?>%</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 lg:px-6 py-4">
                            <div class="text-sm font-bold text-green-600">
                                <?= number_format($investment['total_roi_gagne'], 0) ?> FCFA
                            </div>
                            <div class="text-xs text-gray-500">
                                <?= number_format(($investment['total_roi_gagne'] / $investment['montant_investi']) * 100, 1) ?>%
                            </div>
                        </td>
                        <td class="px-4 lg:px-6 py-4">
                            <?php
                            $status_classes = [
                                'active' => 'bg-green-100 text-green-800',
                                'termine' => 'bg-blue-100 text-blue-800',
                                'expire' => 'bg-yellow-100 text-yellow-800',
                                'annule' => 'bg-red-100 text-red-800'
                            ];
                            ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $status_classes[$investment['statut']] ?? 'bg-gray-100' ?>">
                                <?= $investment['statut'] ?>
                            </span>
                        </td>
                        <td class="px-4 lg:px-6 py-4">
                            <div class="flex flex-wrap gap-2">
                                <!-- Ajouter ROI -->
                                <?php if($investment['statut'] == 'active'): ?>
                                    <button onclick="openAddRoiModal(<?= $investment['id'] ?>, <?= $investment['user_id'] ?>, '<?= htmlspecialchars($investment['nom'] . ' ' . $investment['prenom']) ?>', <?= $investment['roi_journalier'] ?>)"
                                            class="text-green-600 hover:text-green-900" title="Ajouter ROI">
                                        <i class="fas fa-plus-circle"></i>
                                    </button>
                                <?php endif; ?>
                                
                                <!-- Terminer -->
                                <?php if($investment['statut'] == 'active'): ?>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="investment_id" value="<?= $investment['id'] ?>">
                                        <input type="hidden" name="action" value="terminate">
                                        <button type="submit" onclick="return confirm('Terminer cet investissement ?')"
                                                class="text-blue-600 hover:text-blue-900" title="Terminer">
                                            <i class="fas fa-flag-checkered"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <!-- Annuler -->
                                <?php if($investment['statut'] == 'active'): ?>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="investment_id" value="<?= $investment['id'] ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <button type="submit" onclick="return confirm('Annuler cet investissement ?')"
                                                class="text-red-600 hover:text-red-900" title="Annuler">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <!-- Détails -->
                                <button onclick="viewInvestmentDetails(<?= $investment['id'] ?>)"
                                        class="text-gray-600 hover:text-gray-900" title="Détails">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if($total_pages > 1): ?>
        <div class="px-4 lg:px-6 py-4 border-t">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div class="text-sm text-gray-700">
                    Page <?= $page ?> sur <?= $total_pages ?> • 
                    <?= $total_investments ?> investissement(s)
                </div>
                <div class="flex space-x-2">
                    <?php if($page > 1): ?>
                        <a href="?page=investments&p=<?= $page-1 ?><?= $status_filter ? '&status='.urlencode($status_filter) : '' ?><?= $plan_filter ? '&plan='.urlencode($plan_filter) : '' ?><?= $date_from ? '&date_from='.urlencode($date_from) : '' ?><?= $date_to ? '&date_to='.urlencode($date_to) : '' ?>"
                           class="px-3 py-1 border rounded hover:bg-gray-100">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php for($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                        <a href="?page=investments&p=<?= $i ?><?= $status_filter ? '&status='.urlencode($status_filter) : '' ?><?= $plan_filter ? '&plan='.urlencode($plan_filter) : '' ?><?= $date_from ? '&date_from='.urlencode($date_from) : '' ?><?= $date_to ? '&date_to='.urlencode($date_to) : '' ?>"
                           class="px-3 py-1 border rounded <?= $i == $page ? 'bg-blue-500 text-white' : 'hover:bg-gray-100' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if($page < $total_pages): ?>
                        <a href="?page=investments&p=<?= $page+1 ?><?= $status_filter ? '&status='.urlencode($status_filter) : '' ?><?= $plan_filter ? '&plan='.urlencode($plan_filter) : '' ?><?= $date_from ? '&date_from='.urlencode($date_from) : '' ?><?= $date_to ? '&date_to='.urlencode($date_to) : '' ?>"
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

<!-- Modal pour ajouter ROI -->
<div id="roiModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Ajouter un ROI</h3>
                <button onclick="closeRoiModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="roiForm" method="POST">
                <input type="hidden" id="roi_investment_id" name="investment_id">
                <input type="hidden" id="roi_user_id" name="user_id">
                <input type="hidden" name="action" value="add_roi">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Utilisateur</label>
                    <input type="text" id="roi_user_name" class="w-full px-3 py-2 border rounded-lg bg-gray-50" readonly>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">ROI journalier suggéré</label>
                    <input type="text" id="roi_suggested" class="w-full px-3 py-2 border rounded-lg bg-gray-50" readonly>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Montant (FCFA) *</label>
                    <input type="number" name="roi_amount" min="0" step="0.01" required
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Note (optionnelle)</label>
                    <textarea name="note" rows="3"
                              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeRoiModal()"
                            class="px-4 py-2 border rounded-lg hover:bg-gray-100">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                        <i class="fas fa-plus-circle mr-2"></i>Ajouter ROI
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddRoiModal(investmentId, userId, userName, dailyRoi) {
    document.getElementById('roi_investment_id').value = investmentId;
    document.getElementById('roi_user_id').value = userId;
    document.getElementById('roi_user_name').value = userName;
    document.getElementById('roi_suggested').value = dailyRoi.toLocaleString() + ' FCFA';
    document.querySelector('input[name="roi_amount"]').value = dailyRoi;
    document.getElementById('roiModal').classList.remove('hidden');
}

function closeRoiModal() {
    document.getElementById('roiModal').classList.add('hidden');
}

function viewInvestmentDetails(investmentId) {
    window.location.href = `?page=investment_details&id=${investmentId}`;
}
</script>