<?php
/**
 * Page de visualisation des logs d'erreurs PHP
 * Accessible uniquement aux administrateurs
 */

// Chemin du fichier de logs
$log_file = __DIR__ . '/../php_errors.log';

// Traiter les actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'clear_logs') {
        // Vider le fichier de logs
        file_put_contents($log_file, '');
        $success_message = "Les logs ont été vidés avec succès.";
    }
}

// Lire les logs
$logs = [];
if (file_exists($log_file)) {
    $log_content = file_get_contents($log_file);
    $log_lines = explode("\n", $log_content);
    
    // Parser les logs
    foreach ($log_lines as $line) {
        if (trim($line) !== '') {
            // Extraire la date, le type et le message
            if (preg_match('/^\[(.*?)\]\s+(.*)$/', $line, $matches)) {
                $logs[] = [
                    'timestamp' => $matches[1],
                    'message' => $matches[2],
                    'type' => strpos($line, 'Fatal error') !== false ? 'fatal' :
                             (strpos($line, 'Warning') !== false ? 'warning' :
                             (strpos($line, 'Notice') !== false ? 'notice' : 'info'))
                ];
            } else {
                $logs[] = [
                    'timestamp' => '',
                    'message' => $line,
                    'type' => 'info'
                ];
            }
        }
    }
    
    // Inverser pour avoir les plus récents en premier
    $logs = array_reverse($logs);
}

// Statistiques
$total_logs = count($logs);
$fatal_count = count(array_filter($logs, fn($log) => $log['type'] === 'fatal'));
$warning_count = count(array_filter($logs, fn($log) => $log['type'] === 'warning'));
$notice_count = count(array_filter($logs, fn($log) => $log['type'] === 'notice'));
?>

<div class="space-y-6">
    <!-- En-tête avec statistiques -->
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-file-alt text-blue-500 mr-2"></i>
                    Logs d'Erreurs PHP
                </h2>
                <p class="text-gray-600 mt-1">
                    Visualisez et gérez les erreurs de l'application
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                <button onclick="location.reload()" 
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Actualiser
                </button>
                
                <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir vider tous les logs ?');" class="inline">
                    <input type="hidden" name="action" value="clear_logs">
                    <button type="submit" 
                            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                        <i class="fas fa-trash-alt mr-2"></i>
                        Vider les logs
                    </button>
                </form>
            </div>
        </div>
        
        <?php if (isset($success_message)): ?>
        <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
            <i class="fas fa-check-circle mr-2"></i>
            <?= htmlspecialchars($success_message) ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="stat-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Total</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1"><?= $total_logs ?></p>
                </div>
                <div class="stat-icon bg-blue-100 text-blue-600">
                    <i class="fas fa-list"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Erreurs Fatales</p>
                    <p class="text-3xl font-bold text-red-600 mt-1"><?= $fatal_count ?></p>
                </div>
                <div class="stat-icon bg-red-100 text-red-600">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Avertissements</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-1"><?= $warning_count ?></p>
                </div>
                <div class="stat-icon bg-yellow-100 text-yellow-600">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Notices</p>
                    <p class="text-3xl font-bold text-blue-600 mt-1"><?= $notice_count ?></p>
                </div>
                <div class="stat-icon bg-blue-100 text-blue-600">
                    <i class="fas fa-info-circle"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filtres -->
    <div class="bg-white rounded-2xl shadow-sm p-4">
        <div class="flex flex-wrap items-center gap-3">
            <label class="text-sm font-medium text-gray-700">Filtrer par type :</label>
            <button onclick="filterLogs('all')" 
                    class="filter-btn active px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                    data-filter="all">
                Tous
            </button>
            <button onclick="filterLogs('fatal')" 
                    class="filter-btn px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                    data-filter="fatal">
                Fatales
            </button>
            <button onclick="filterLogs('warning')" 
                    class="filter-btn px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                    data-filter="warning">
                Avertissements
            </button>
            <button onclick="filterLogs('notice')" 
                    class="filter-btn px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                    data-filter="notice">
                Notices
            </button>
        </div>
    </div>
    
    <!-- Liste des logs -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <?php if (empty($logs)): ?>
        <div class="p-12 text-center">
            <div class="w-20 h-20 mx-auto bg-green-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-check-circle text-green-500 text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Aucune erreur !</h3>
            <p class="text-gray-600">Le fichier de logs est vide. Tout fonctionne correctement.</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Type
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Date/Heure
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Message
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($logs as $index => $log): ?>
                    <tr class="log-row hover:bg-gray-50 transition-colors" data-type="<?= $log['type'] ?>">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $badge_colors = [
                                'fatal' => 'bg-red-100 text-red-800',
                                'warning' => 'bg-yellow-100 text-yellow-800',
                                'notice' => 'bg-blue-100 text-blue-800',
                                'info' => 'bg-gray-100 text-gray-800'
                            ];
                            $badge_icons = [
                                'fatal' => 'fa-times-circle',
                                'warning' => 'fa-exclamation-triangle',
                                'notice' => 'fa-info-circle',
                                'info' => 'fa-file-alt'
                            ];
                            ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?= $badge_colors[$log['type']] ?>">
                                <i class="fas <?= $badge_icons[$log['type']] ?> mr-1"></i>
                                <?= ucfirst($log['type']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <?= htmlspecialchars($log['timestamp']) ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-800">
                            <div class="font-mono break-all">
                                <?= htmlspecialchars($log['message']) ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.filter-btn {
    background: #f3f4f6;
    color: #6b7280;
}

.filter-btn.active {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
}

.filter-btn:hover:not(.active) {
    background: #e5e7eb;
}
</style>

<script>
function filterLogs(type) {
    // Mettre à jour les boutons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Filtrer les lignes
    document.querySelectorAll('.log-row').forEach(row => {
        if (type === 'all' || row.dataset.type === type) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
