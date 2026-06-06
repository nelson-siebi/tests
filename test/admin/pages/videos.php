<?php
// Récupérer toutes les vidéos
$videos = $pdo->query("
    SELECT v.*, 
           COUNT(vw.id) as total_views,
           SUM(vw.gain) as total_gains
    FROM ads_videos v
    LEFT JOIN ads_views vw ON v.id = vw.video_id AND vw.valide = 1
    GROUP BY v.id
    ORDER BY v.created_at DESC
")->fetchAll();

// Actions
if(isset($_POST['action'])) {
    if($_POST['action'] == 'add_video') {
        $titre = $_POST['titre'];
        $youtube_url = $_POST['youtube_url'];
        $actif = isset($_POST['actif']) ? 1 : 0;
        
        // Extraire l'ID de la vidéo YouTube
        $video_id = '';
        if(preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $youtube_url, $matches)) {
            $video_id = $matches[1];
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO ads_videos (titre, youtube_url, youtube_video_id, actif, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([$titre, $youtube_url, $video_id, $actif]);
        $success = "Vidéo ajoutée avec succès";
        
    } elseif($_POST['action'] == 'edit_video') {
        $video_id = (int)$_POST['video_id'];
        $titre = $_POST['titre'];
        $youtube_url = $_POST['youtube_url'];
        $actif = isset($_POST['actif']) ? 1 : 0;
        
        // Extraire l'ID de la vidéo YouTube
        $youtube_video_id = '';
        if(preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $youtube_url, $matches)) {
            $youtube_video_id = $matches[1];
        }
        
        $stmt = $pdo->prepare("
            UPDATE ads_videos 
            SET titre = ?, youtube_url = ?, youtube_video_id = ?, actif = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->execute([$titre, $youtube_url, $youtube_video_id, $actif, $video_id]);
        $success = "Vidéo modifiée avec succès";
        
    } elseif($_POST['action'] == 'delete_video') {
        $video_id = (int)$_POST['video_id'];
        $pdo->prepare("DELETE FROM ads_videos WHERE id = ?")->execute([$video_id]);
        $success = "Vidéo supprimée avec succès";
    }
    
    // header("Refresh:0");
    exit;
}

// Statistiques
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total_videos,
        SUM(CASE WHEN actif = 1 THEN 1 ELSE 0 END) as active_videos,
        (SELECT COUNT(*) FROM ads_views WHERE DATE(date_view) = CURDATE()) as today_views,
        (SELECT COALESCE(SUM(gain), 0) FROM ads_views WHERE DATE(date_view) = CURDATE()) as today_gains
    FROM ads_videos
")->fetch();
?>

<div class="p-4 lg:p-6">
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center mb-6 gap-4">
        <h3 class="text-xl font-bold">Gestion des Vidéos Publicitaires</h3>
        <button onclick="openAddVideoModal()" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
            <i class="fas fa-plus mr-2"></i>Ajouter une Vidéo
        </button>
    </div>

    <?php if(isset($success)): ?>
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            <i class="fas fa-check-circle mr-2"></i>
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            <div class="flex items-center">
                <div class="p-2 rounded-full bg-blue-100 text-blue-600 mr-3">
                    <i class="fas fa-video"></i>
                </div>
                <div>
                    <div class="text-blue-800 font-bold text-lg"><?= $stats['total_videos'] ?></div>
                    <div class="text-blue-600 text-sm">Total vidéos</div>
                </div>
            </div>
        </div>
        
        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
            <div class="flex items-center">
                <div class="p-2 rounded-full bg-green-100 text-green-600 mr-3">
                    <i class="fas fa-play-circle"></i>
                </div>
                <div>
                    <div class="text-green-800 font-bold text-lg"><?= $stats['active_videos'] ?></div>
                    <div class="text-green-600 text-sm">Vidéos actives</div>
                </div>
            </div>
        </div>
        
        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
            <div class="flex items-center">
                <div class="p-2 rounded-full bg-yellow-100 text-yellow-600 mr-3">
                    <i class="fas fa-eye"></i>
                </div>
                <div>
                    <div class="text-yellow-800 font-bold text-lg"><?= $stats['today_views'] ?></div>
                    <div class="text-yellow-600 text-sm">Vues aujourd'hui</div>
                </div>
            </div>
        </div>
        
        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
            <div class="flex items-center">
                <div class="p-2 rounded-full bg-purple-100 text-purple-600 mr-3">
                    <i class="fas fa-coins"></i>
                </div>
                <div>
                    <div class="text-purple-800 font-bold text-lg"><?= number_format($stats['today_gains'], 0) ?> FCFA</div>
                    <div class="text-purple-600 text-sm">Gains aujourd'hui</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des vidéos -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach($videos as $video): ?>
            <div class="bg-white rounded-lg shadow overflow-hidden border <?= $video['actif'] ? 'border-green-200' : 'border-red-200' ?>">
                <div class="relative">
                    <?php if($video['youtube_video_id']): ?>
                        <div class="aspect-w-16 aspect-h-9">
                            <img src="https://img.youtube.com/vi/<?= $video['youtube_video_id'] ?>/hqdefault.jpg" 
                                 alt="<?= htmlspecialchars($video['titre']) ?>" 
                                 class="w-full h-48 object-cover">
                        </div>
                        <div class="absolute top-2 right-2 bg-black bg-opacity-70 text-white text-xs px-2 py-1 rounded">
                            <i class="fab fa-youtube mr-1"></i>YouTube
                        </div>
                    <?php else: ?>
                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-video text-gray-400 text-4xl"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="absolute top-2 left-2">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $video['actif'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                            <?= $video['actif'] ? 'Actif' : 'Inactif' ?>
                        </span>
                    </div>
                </div>
                
                <div class="p-4">
                    <h4 class="font-bold text-gray-900 mb-2 truncate"><?= htmlspecialchars($video['titre']) ?></h4>
                    
                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Vues totales:</span>
                            <span class="font-medium"><?= number_format($video['total_views']) ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Gains totaux:</span>
                            <span class="font-medium text-green-600"><?= number_format($video['total_gains'], 0) ?> FCFA</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Ajoutée le:</span>
                            <span class="font-medium"><?= date('d/m/Y', strtotime($video['created_at'])) ?></span>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <a href="<?= htmlspecialchars($video['youtube_url']) ?>" target="_blank" 
                           class="text-blue-600 hover:text-blue-800 text-sm">
                            <i class="fas fa-external-link-alt mr-1"></i>Voir la vidéo
                        </a>
                        
                        <div class="flex space-x-2">
                            <button onclick="openEditVideoModal(<?= $video['id'] ?>, '<?= htmlspecialchars(addslashes($video['titre'])) ?>', '<?= htmlspecialchars(addslashes($video['youtube_url'])) ?>', <?= $video['actif'] ?>)"
                                    class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-edit"></i>
                            </button>
                            
                            <form method="POST" class="inline" onsubmit="return confirm('Supprimer cette vidéo ?')">
                                <input type="hidden" name="video_id" value="<?= $video['id'] ?>">
                                <input type="hidden" name="action" value="delete_video">
                                <button type="submit" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal pour ajouter/modifier une vidéo -->
<div id="videoModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 id="videoModalTitle" class="text-lg font-semibold">Ajouter une Vidéo</h3>
                <button onclick="closeVideoModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="videoForm" method="POST">
                <input type="hidden" id="video_id" name="video_id">
                <input type="hidden" id="form_action" name="action" value="add_video">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Titre de la vidéo *</label>
                    <input type="text" name="titre" required
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="Ex: Publicité investissement">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">URL YouTube *</label>
                    <input type="url" name="youtube_url" required
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="https://www.youtube.com/watch?v=...">
                    <p class="text-xs text-gray-500 mt-1">Lien YouTube de la vidéo publicitaire</p>
                </div>
                
                <div class="mb-6">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="actif" checked class="mr-2">
                        <span>Vidéo active</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1">Les vidéos inactives ne seront pas affichées aux utilisateurs</p>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeVideoModal()"
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
function openAddVideoModal() {
    document.getElementById('videoModalTitle').textContent = 'Ajouter une Vidéo';
    document.getElementById('videoForm').reset();
    document.getElementById('form_action').value = 'add_video';
    document.getElementById('video_id').value = '';
    document.getElementById('videoModal').classList.remove('hidden');
}

function openEditVideoModal(videoId, titre, youtubeUrl, actif) {
    document.getElementById('videoModalTitle').textContent = 'Modifier la Vidéo';
    document.getElementById('video_id').value = videoId;
    document.querySelector('input[name="titre"]').value = titre;
    document.querySelector('input[name="youtube_url"]').value = youtubeUrl;
    document.querySelector('input[name="actif"]').checked = actif == 1;
    document.getElementById('form_action').value = 'edit_video';
    document.getElementById('videoModal').classList.remove('hidden');
}

function closeVideoModal() {
    document.getElementById('videoModal').classList.add('hidden');
}
</script>