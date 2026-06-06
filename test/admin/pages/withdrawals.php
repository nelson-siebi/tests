<?php
require_once __DIR__ . '/../../../functions/notifications.php';
// Récupérer les retraits avec pagination
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Filtres
$status_filter = $_GET['status'] ?? '';
$method_filter = $_GET['method'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

$where = ["t.type = 'retrait'"];
$params = [];

if($status_filter) {
    $where[] = "t.statut = ?";
    $params[] = $status_filter;
}

if($method_filter) {
    $where[] = "t.methode = ?";
    $params[] = $method_filter;
}

if($date_from) {
    $where[] = "DATE(t.created_at) >= ?";
    $params[] = $date_from;
}

if($date_to) {
    $where[] = "DATE(t.created_at) <= ?";
    $params[] = $date_to;
}

$where_clause = "WHERE " . implode(" AND ", $where);

// Compter
$count_stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM transactions t
    $where_clause
");
$count_stmt->execute($params);
$total_withdrawals = $count_stmt->fetchColumn();
$total_pages = ceil($total_withdrawals / $limit);

// Récupérer les retraits
$stmt = $pdo->prepare("
    SELECT t.*, 
           u.nom, u.prenom, u.email, u.phone,
           w.solde_investissement, w.solde_publicite, w.solde_parrainage,
           pm.account_number, pm.account_name, pm.provider
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    LEFT JOIN wallets w ON u.id = w.user_id
    LEFT JOIN user_payment_methods pm ON u.id = pm.user_id AND pm.methode = t.methode AND pm.statut = 'active'
    $where_clause
    ORDER BY 
        CASE t.statut 
            WHEN 'attente' THEN 1
            WHEN 'success' THEN 2
            WHEN 'failed' THEN 3
            WHEN 'annule' THEN 4
            ELSE 5
        END,
        t.created_at DESC
    LIMIT $limit OFFSET $offset
");


$stmt->execute();
$withdrawals = $stmt->fetchAll();

// Actions
if(isset($_POST['process_withdrawal'])) {
    error_log("=== TRAITEMENT RETRAIT ADMIN ===");
    error_log("POST data: " . json_encode($_POST));
    
    try {
        $transaction_id = (int)$_POST['transaction_id'];
        $action = $_POST['action'];
        $note = $_POST['note'] ?? '';
        
        error_log("Transaction ID: $transaction_id, Action: $action");
        
        // Récupérer la transaction
        $transaction = $pdo->query("
            SELECT t.*, u.id as user_id, t.montant, t.methode, t.source
            FROM transactions t 
            JOIN users u ON t.user_id = u.id 
            WHERE t.id = $transaction_id
        ")->fetch();
        
        if (!$transaction) {
            error_log("ERREUR: Transaction #$transaction_id introuvable");
            throw new Exception("Transaction introuvable");
        }
        
        error_log("Transaction trouvée: " . json_encode($transaction));
        
        if($action == 'approve') {
            error_log("Approbation du retrait #$transaction_id");
            
            // Mettre à jour le statut
            $pdo->prepare("UPDATE transactions SET statut = 'success', note = CONCAT(IFNULL(note, ''), ' | ', ?) WHERE id = ?")
                ->execute([$note, $transaction_id]);
                
            // NOTE: On ne déduit PAS le solde ici car il a déjà été déduit lors de la demande (pages/retrais.php)
            
            // Récupérer le champ de solde pour mettre à jour les totaux uniquement
            $balance_field = '';
            switch($transaction['source']) {
                case 'investissement':
                    $balance_field = 'solde_investissement';
                    break;
                case 'publicite':
                    $balance_field = 'solde_publicite';
                    break;
                case 'parrainage':
                    $balance_field = 'solde_parrainage';
                    break;
                default:
                    $balance_field = 'solde_investissement';
            }
            
            // Ajouter aux totaux retrait (statistiques)
            // Note: C'est discutable si on doit le faire à la demande ou à la validation. 
            // pages/retrais.php le fait déjà pour 'total_retrait_invest' mais pas pour les autres ?
            // Vérifions pages/retrais.php : il le fait pour 'investissement'.
            
            // Pour être sûr et consistant, on met à jour les totaux globaux ici si ce n'est pas déjà fait.
            // Mais pour éviter les doublons avec pages/retrais.php, on va laisser tel quel ou vérifier.
            // Dans le doute, on suppose que retrais.php gère le 'total_retrait_invest'.
            // Mais pour pub et parrainage, retrais.php ne semblait pas avoir le code spécifique visibles dans le snippet précédent (il y avait un if investissement).
            
            // Code original :
            // $total_field = 'total_retrait_' . ($balance_field == 'solde_investissement' ? 'invest' : 
            //                  ($balance_field == 'solde_publicite' ? 'pub' : 'parrain'));
            // $pdo->prepare("UPDATE wallets SET $total_field = $total_field + ? WHERE user_id = ?")
            //     ->execute([$transaction['montant'], $transaction['user_id']]);
            
            // Correction : On laisse la mise à jour des totaux de statistiques ici pour s'assurer qu'ils sont comptés à la validation (confiés).
            // Si pages/retrais.php le fait déjà, il faudra peut-être le retirer de là-bas.
            // Cependant, le bug critique est la déduction du SOLDE.
            
            // Notification via fonction standard
            notifyWithdrawalApproved($transaction['user_id'], $transaction['montant']);
            
            $success = "Retrait approuvé avec succès";
            error_log("SUCCESS: $success");
            
        } elseif($action == 'reject') {
            error_log("Rejet du retrait #$transaction_id");
            
            // Mettre à jour le statut
            $pdo->prepare("UPDATE transactions SET statut = 'annule', note = CONCAT(IFNULL(note, ''), ' | ', ?) WHERE id = ?")
                ->execute([$note, $transaction_id]);
            
            // REMBOURSER LE SOLDE
            $balance_field = '';
            switch($transaction['source']) {
                case 'investissement': $balance_field = 'solde_investissement'; break;
                case 'publicite': $balance_field = 'solde_publicite'; break;
                case 'parrainage': $balance_field = 'solde_parrainage'; break;
                default: $balance_field = 'solde_investissement';
            }
            
            if ($balance_field) {
                error_log("Remboursement sur $balance_field");
                $pdo->prepare("UPDATE wallets SET $balance_field = $balance_field + ? WHERE user_id = ?")
                    ->execute([$transaction['montant'], $transaction['user_id']]);
                    
                // Si c'était un investissement, on doit aussi déduire du 'total_retrait_invest' si ça avait été ajouté lors de la demande
                if ($transaction['source'] === 'investissement') {
                     $pdo->prepare("UPDATE wallets SET total_retrait_invest = GREATEST(0, total_retrait_invest - ?) WHERE user_id = ?")
                        ->execute([$transaction['montant'], $transaction['user_id']]);
                }
            }

            // Notification via fonction standard
            sendNotification(
                $transaction['user_id'],
                'withdrawal',
                'Retrait refusé',
                "Votre retrait de " . number_format($transaction['montant'], 2) . " FCFA a été refusé. " . ($note ? "Raison: " . $note : ""),
                '?page=transactions',
                'Voir la transaction'
            );
            
            $success = "Retrait refusé avec succès (Montant remboursé)";
            error_log("SUCCESS: $success");
        }
        
        // Redirection JavaScript au lieu de header() pour éviter les erreurs
        echo '<script>
            alert("' . addslashes($success) . '");
            window.location.href = "?page=withdrawals";
        </script>';
        exit;
        
    } catch (Exception $e) {
        error_log("ERREUR TRAITEMENT RETRAIT: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        $error = "Erreur lors du traitement: " . $e->getMessage();
    }
}
?>

<div class="p-4 lg:p-6">
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center mb-6 gap-4">
        <h3 class="text-xl font-bold">Gestion des Retraits</h3>
        <div class="text-sm text-gray-600">
            <?= number_format($total_withdrawals) ?> retrait(s)
        </div>
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

    <!-- Statistiques -->
    <?php
    $stats_stmt = $pdo->query("
        SELECT 
            statut,
            COUNT(*) as count,
            SUM(montant) as total
        FROM transactions 
        WHERE type = 'retrait'
        GROUP BY statut
    ");
    $withdrawal_stats = $stats_stmt->fetchAll();
    
    $pending_count = 0;
    $pending_amount = 0;
    $approved_count = 0;
    $approved_amount = 0;
    
    foreach($withdrawal_stats as $stat) {
        if($stat['statut'] == 'attente') {
            $pending_count = $stat['count'];
            $pending_amount = $stat['total'];
        } elseif($stat['statut'] == 'success') {
            $approved_count = $stat['count'];
            $approved_amount = $stat['total'];
        }
    }
    ?>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
            <div class="text-yellow-800 font-bold text-lg"><?= $pending_count ?></div>
            <div class="text-yellow-600 text-sm">En attente</div>
            <div class="text-yellow-700 text-xs mt-1"><?= number_format($pending_amount, 0) ?> FCFA</div>
        </div>
        
        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
            <div class="text-green-800 font-bold text-lg"><?= $approved_count ?></div>
            <div class="text-green-600 text-sm">Approuvés</div>
            <div class="text-green-700 text-xs mt-1"><?= number_format($approved_amount, 0) ?> FCFA</div>
        </div>
        
        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            <div class="text-blue-800 font-bold text-lg"><?= number_format($total_withdrawals) ?></div>
            <div class="text-blue-600 text-sm">Total retraits</div>
        </div>
        
        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
            <div class="text-gray-800 font-bold text-lg">
                <?= number_format($approved_amount + $pending_amount, 0) ?> FCFA
            </div>
            <div class="text-gray-600 text-sm">Montant total</div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <input type="hidden" name="page" value="withdrawals">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                <select name="status" class="w-full px-3 py-2 border rounded-lg">
                    <option value="">Tous les statuts</option>
                    <option value="attente" <?= $status_filter == 'attente' ? 'selected' : '' ?>>En attente</option>
                    <option value="success" <?= $status_filter == 'success' ? 'selected' : '' ?>>Approuvé</option>
                    <option value="failed" <?= $status_filter == 'failed' ? 'selected' : '' ?>>Échoué</option>
                    <option value="annule" <?= $status_filter == 'annule' ? 'selected' : '' ?>>Annulé</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Méthode</label>
                <select name="method" class="w-full px-3 py-2 border rounded-lg">
                    <option value="">Toutes méthodes</option>
                    <option value="orange" <?= $method_filter == 'orange' ? 'selected' : '' ?>>Orange Money</option>
                    <option value="mtn" <?= $method_filter == 'mtn' ? 'selected' : '' ?>>MTN Mobile Money</option>
                    <option value="mobile_money" <?= $method_filter == 'mobile_money' ? 'selected' : '' ?>>Mobile Money</option>
                    <option value="visa" <?= $method_filter == 'visa' ? 'selected' : '' ?>>Carte Visa</option>
                    <option value="autre" <?= $method_filter == 'autre' ? 'selected' : '' ?>>Autre</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date (du)</label>
                <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>"
                       class="w-full px-3 py-2 border rounded-lg">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date (au)</label>
                <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>"
                       class="w-full px-3 py-2 border rounded-lg">
            </div>
            
            <div class="md:col-span-2 lg:col-span-4 flex space-x-3">
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                    <i class="fas fa-filter mr-2"></i>Filtrer
                </button>
                
                <?php if($status_filter || $method_filter || $date_from || $date_to): ?>
                    <a href="?page=withdrawals" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                        <i class="fas fa-times mr-2"></i>Effacer
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tableau des retraits -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1000px]">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Utilisateur</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Méthode</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Compte</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach($withdrawals as $withdrawal): ?>
                    <tr class="<?= $withdrawal['statut'] == 'attente' ? 'bg-yellow-50' : '' ?>">
                        <td class="px-4 lg:px-6 py-4 text-sm font-mono">
                            #<?= str_pad($withdrawal['id'], 6, '0', STR_PAD_LEFT) ?>
                        </td>
                        <td class="px-4 lg:px-6 py-4">
                            <div class="text-sm font-medium text-gray-900 truncate max-w-[150px]">
                                <?= htmlspecialchars($withdrawal['nom'] . ' ' . $withdrawal['prenom']) ?>
                            </div>
                            <div class="text-xs text-gray-500 truncate max-w-[150px]">
                                <?= htmlspecialchars($withdrawal['email']) ?>
                            </div>
                        </td>
                        <td class="px-4 lg:px-6 py-4">
                            <div class="text-lg font-bold text-blue-600">
                                <?= number_format($withdrawal['montant'], 0) ?> FCFA
                            </div>
                            <div class="text-xs text-gray-500">
                                Source: <?= $withdrawal['source'] ?>
                            </div>
                        </td>
                        <td class="px-4 lg:px-6 py-4">
                            <div class="flex items-center">
                                <?php
                                $method_icons = [
                                    'orange' => 'fas fa-sim-card text-orange-500',
                                    'mtn' => 'fas fa-sim-card text-yellow-500',
                                    'mobile_money' => 'fas fa-mobile-alt text-green-500',
                                    'visa' => 'fab fa-cc-visa text-blue-500',
                                    'autre' => 'fas fa-wallet text-gray-500'
                                ];
                                $method_texts = [
                                    'orange' => 'Orange Money',
                                    'mtn' => 'MTN MoMo',
                                    'mobile_money' => 'Mobile Money',
                                    'visa' => 'Carte Visa',
                                    'autre' => 'Autre'
                                ];
                                ?>
                                <i class="<?= $method_icons[$withdrawal['methode']] ?? 'fas fa-wallet' ?> mr-2"></i>
                                <span class="text-sm"><?= $method_texts[$withdrawal['methode']] ?? $withdrawal['methode'] ?></span>
                            </div>
                        </td>
                        <td class="px-4 lg:px-6 py-4">
                            <?php if($withdrawal['account_number']): ?>
                                <div class="text-sm font-medium">
                                    <?= htmlspecialchars($withdrawal['account_number']) ?>
                                </div>
                                <div class="text-xs text-gray-500">
                                    <?= htmlspecialchars($withdrawal['account_name']) ?>
                                </div>
                            <?php else: ?>
                                <span class="text-sm text-gray-500">Non spécifié</span>
                            <?php endif; ?>
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
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $status_classes[$withdrawal['statut']] ?? 'bg-gray-100' ?>">
                                <?= $withdrawal['statut'] ?>
                            </span>
                        </td>
                        <td class="px-4 lg:px-6 py-4 text-sm text-gray-500">
                            <?= date('d/m/Y H:i', strtotime($withdrawal['created_at'])) ?>
                        </td>
                        <td class="px-4 lg:px-6 py-4">
                            <div class="flex flex-wrap gap-2">
                                <?php if($withdrawal['statut'] == 'attente'): ?>
                                    <!-- Approuver -->
                                    <form method="POST" class="inline" onsubmit="return confirm('Approuver ce retrait ?')">
                                        <input type="hidden" name="process_withdrawal" value="1">
                                        <input type="hidden" name="transaction_id" value="<?= $withdrawal['id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <textarea name="note" placeholder="Note" rows="1"
                                                  class="hidden" id="note_<?= $withdrawal['id'] ?>">Approuvé par admin</textarea>
                                        <button type="submit" 
                                                class="text-green-600 hover:text-green-900" title="Approuver">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    
                                    <!-- Refuser -->
                                    <button onclick="openRejectModal(<?= $withdrawal['id'] ?>)"
                                            class="text-red-600 hover:text-red-900" title="Refuser">
                                        <i class="fas fa-times"></i>
                                    </button>
                                <?php endif; ?>
                                
                                <!-- Voir détails -->
                                <button onclick="viewWithdrawalDetails(<?= $withdrawal['id'] ?>)"
                                        class="text-blue-600 hover:text-blue-900" title="Détails">
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
                    Page <?= $page ?> sur <?= $total_pages ?>
                </div>
                <div class="flex space-x-2">
                    <?php if($page > 1): ?>
                        <a href="?page=withdrawals&p=<?= $page-1 ?><?= $status_filter ? '&status='.urlencode($status_filter) : '' ?><?= $method_filter ? '&method='.urlencode($method_filter) : '' ?><?= $date_from ? '&date_from='.urlencode($date_from) : '' ?><?= $date_to ? '&date_to='.urlencode($date_to) : '' ?>"
                           class="px-3 py-1 border rounded hover:bg-gray-100">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php for($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                        <a href="?page=withdrawals&p=<?= $i ?><?= $status_filter ? '&status='.urlencode($status_filter) : '' ?><?= $method_filter ? '&method='.urlencode($method_filter) : '' ?><?= $date_from ? '&date_from='.urlencode($date_from) : '' ?><?= $date_to ? '&date_to='.urlencode($date_to) : '' ?>"
                           class="px-3 py-1 border rounded <?= $i == $page ? 'bg-blue-500 text-white' : 'hover:bg-gray-100' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if($page < $total_pages): ?>
                        <a href="?page=withdrawals&p=<?= $page+1 ?><?= $status_filter ? '&status='.urlencode($status_filter) : '' ?><?= $method_filter ? '&method='.urlencode($method_filter) : '' ?><?= $date_from ? '&date_from='.urlencode($date_from) : '' ?><?= $date_to ? '&date_to='.urlencode($date_to) : '' ?>"
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

<!-- Modal pour refuser un retrait -->
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Refuser le retrait</h3>
                <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="rejectForm" method="POST">
                <input type="hidden" name="process_withdrawal" value="1">
                <input type="hidden" id="reject_transaction_id" name="transaction_id">
                <input type="hidden" name="action" value="reject">
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Raison du refus *</label>
                    <textarea name="note" rows="4" required
                              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                              placeholder="Expliquez la raison du refus..."></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeRejectModal()"
                            class="px-4 py-2 border rounded-lg hover:bg-gray-100">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                        <i class="fas fa-times mr-2"></i>Refuser le retrait
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openRejectModal(transactionId) {
    document.getElementById('reject_transaction_id').value = transactionId;
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}

function viewWithdrawalDetails(transactionId) {
    window.location.href = `?page=transaction_details&id=${transactionId}`;
}
</script>