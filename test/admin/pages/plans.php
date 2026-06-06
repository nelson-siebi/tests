<?php
// Récupérer tous les plans
$plans = $pdo->query("SELECT * FROM plans ORDER BY prix")->fetchAll();

// Ajouter un nouveau plan
if(isset($_POST['add_plan'])) {
    $nom = $_POST['nom'];
    $prix = (float)$_POST['prix'];
    $roi_journalier = (float)$_POST['roi_journalier'];
    $duree_jours = (int)$_POST['duree_jours'];
    $videos_par_jour = (int)$_POST['videos_par_jour'];
    $gain_par_video = (float)$_POST['gain_par_video'];
    $description = $_POST['description'];
    $actif = isset($_POST['actif']) ? 1 : 0;
    
    $stmt = $pdo->prepare("
        INSERT INTO plans (nom, prix, roi_journalier, duree_jours, videos_par_jour, gain_par_video, actif, description)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([$nom, $prix, $roi_journalier, $duree_jours, $videos_par_jour, $gain_par_video, $actif, $description]);
    
    $success = "Plan ajouté avec succès";
    header("Refresh:0");
    exit;
}

// Modifier un plan
if(isset($_POST['edit_plan'])) {
    $id = (int)$_POST['plan_id'];
    $nom = $_POST['nom'];
    $prix = (float)$_POST['prix'];
    $roi_journalier = (float)$_POST['roi_journalier'];
    $duree_jours = (int)$_POST['duree_jours'];
    $videos_par_jour = (int)$_POST['videos_par_jour'];
    $gain_par_video = (float)$_POST['gain_par_video'];
    $description = $_POST['description'];
    $actif = isset($_POST['actif']) ? 1 : 0;
    
    $stmt = $pdo->prepare("
        UPDATE plans SET 
            nom = ?, prix = ?, roi_journalier = ?, duree_jours = ?, 
            videos_par_jour = ?, gain_par_video = ?, actif = ?, description = ?, updated_at = NOW()
        WHERE id = ?
    ");
    
    $stmt->execute([$nom, $prix, $roi_journalier, $duree_jours, $videos_par_jour, $gain_par_video, $actif, $description, $id]);
    
    $success = "Plan modifié avec succès";
    header("Refresh:0");
    exit;
}

// Supprimer un plan
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Vérifier si des utilisateurs utilisent ce plan
    $check = $pdo->query("SELECT COUNT(*) FROM user_plans WHERE plan_id = $id")->fetchColumn();
    
    if($check == 0) {
        $pdo->prepare("DELETE FROM plans WHERE id = ?")->execute([$id]);
        $success = "Plan supprimé avec succès";
    } else {
        $error = "Impossible de supprimer ce plan car des utilisateurs l'utilisent";
    }
    
    header("Location: ?page=plans");
    exit;
}
?>

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold">Gestion des Plans d'Investissement</h3>
        <button onclick="openAddPlanModal()" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
            <i class="fas fa-plus mr-2"></i>Ajouter un Plan
        </button>
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

    <!-- Grille des plans -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach($plans as $plan): ?>
            <div class="bg-white rounded-lg shadow overflow-hidden border <?= $plan['actif'] ? 'border-green-200' : 'border-red-200' ?>">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h4 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($plan['nom']) ?></h4>
                            <span class="text-sm <?= $plan['actif'] ? 'text-green-600' : 'text-red-600' ?>">
                                <i class="fas fa-circle text-xs mr-1"></i>
                                <?= $plan['actif'] ? 'Actif' : 'Inactif' ?>
                            </span>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="openEditPlanModal(<?= $plan['id'] ?>)" 
                                    class="text-blue-600 hover:text-blue-900" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="?page=plans&delete=<?= $plan['id'] ?>" 
                               onclick="return confirm('Supprimer ce plan ?')"
                               class="text-red-600 hover:text-red-900" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Prix d'achat:</span>
                            <span class="text-lg font-bold text-blue-600">
                                <?= number_format($plan['prix'], 0) ?> FCFA
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">ROI journalier:</span>
                            <span class="font-semibold text-green-600">
                                <?= number_format($plan['roi_journalier'], 0) ?> FCFA
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Durée:</span>
                            <span class="font-semibold"><?= $plan['duree_jours'] ?> jours</span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Vidéos/jour:</span>
                            <span class="font-semibold"><?= $plan['videos_par_jour'] ?></span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Gain/vidéo:</span>
                            <span class="font-semibold"><?= number_format($plan['gain_par_video'], 0) ?> FCFA</span>
                        </div>
                    </div>
                    
                    <?php if($plan['description']): ?>
                    <div class="mt-4 p-3 bg-gray-50 rounded">
                        <p class="text-sm text-gray-600"><?= htmlspecialchars($plan['description']) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mt-6 pt-4 border-t">
                        <div class="text-sm text-gray-500">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            ROI total: <?= number_format($plan['roi_journalier'] * $plan['duree_jours'], 0) ?> FCFA
                            (<?= number_format(($plan['roi_journalier'] * $plan['duree_jours'] / $plan['prix']) * 100, 1) ?>%)
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal pour ajouter/modifier un plan -->
<div id="planModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 id="planModalTitle" class="text-lg font-semibold">Ajouter un Plan</h3>
                <button onclick="closePlanModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="planForm" method="POST">
                <input type="hidden" id="plan_id" name="plan_id">
                <input type="hidden" id="form_action" name="add_plan">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nom du plan *</label>
                        <input type="text" name="nom" required
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Prix (FCFA) *</label>
                        <input type="number" name="prix" min="0" step="0.01" required
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ROI journalier (FCFA) *</label>
                        <input type="number" name="roi_journalier" min="0" step="0.01" required
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Durée (jours) *</label>
                        <input type="number" name="duree_jours" min="1" required
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Vidéos par jour</label>
                        <input type="number" name="videos_par_jour" min="0"
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Gain par vidéo (FCFA)</label>
                        <input type="number" name="gain_par_video" min="0" step="0.01"
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                
                <div class="mb-6">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="actif" checked class="mr-2">
                        <span>Plan actif</span>
                    </label>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closePlanModal()"
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

<script>
function openAddPlanModal() {
    document.getElementById('planModalTitle').textContent = 'Ajouter un Plan';
    document.getElementById('planForm').reset();
    document.getElementById('form_action').name = 'add_plan';
    document.getElementById('planModal').classList.remove('hidden');
}

function openEditPlanModal(planId) {
    // Charger les données du plan via AJAX
    fetch(`ajax.php?action=get_plan&id=${planId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('planModalTitle').textContent = 'Modifier le Plan';
            document.getElementById('plan_id').value = data.id;
            document.querySelector('input[name="nom"]').value = data.nom;
            document.querySelector('input[name="prix"]').value = data.prix;
            document.querySelector('input[name="roi_journalier"]').value = data.roi_journalier;
            document.querySelector('input[name="duree_jours"]').value = data.duree_jours;
            document.querySelector('input[name="videos_par_jour"]').value = data.videos_par_jour;
            document.querySelector('input[name="gain_par_video"]').value = data.gain_par_video;
            document.querySelector('textarea[name="description"]').value = data.description;
            document.querySelector('input[name="actif"]').checked = data.actif == 1;
            
            document.getElementById('form_action').name = 'edit_plan';
            document.getElementById('planModal').classList.remove('hidden');
        });
}

function closePlanModal() {
    document.getElementById('planModal').classList.add('hidden');
}
</script>