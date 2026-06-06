<?php
$pageTitle = $title ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Admin Investian</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: { primary: '#3b82f6', success: '#10b981', warning: '#f59e0b', danger: '#ef4444' }
                }
            }
        }
    </script>
</head>

<body class="bg-slate-50 text-slate-800 font-sans min-h-screen flex flex-col md:flex-row"
    x-data="{ showModal: false, editMode: false, currentAd: {} }">
    <?php include __DIR__ . '/partials/mobile_nav.php'; ?>

    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <main class="flex-1 md:ml-72 p-4 md:p-10 pb-24 md:pb-10">
        <div class="flex justify-between items-center mb-10">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Gestion des Publicités</h1>
                <p class="text-slate-500 mt-2">Gérez les publicités PTC (Paid-To-Click) de la plateforme</p>
            </div>
            <button @click="showModal = true; editMode = false; currentAd = {}"
                class="bg-primary hover:bg-blue-600 text-white font-black px-6 py-4 rounded-2xl transition-all shadow-xl shadow-primary/25 hover:-translate-y-1 flex items-center space-x-2">
                <i data-lucide="plus" class="w-5 h-5"></i>
                <span>Ajouter une Pub</span>
            </button>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="bg-emerald-50 border border-emerald-100 p-6 rounded-2xl mb-6">
                <div class="flex items-center space-x-3 text-emerald-600">
                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                    <span class="font-bold">
                        <?php if ($_GET['success'] === 'created'): ?>
                            Publicité créée avec succès !
                        <?php elseif ($_GET['success'] === 'updated'): ?>
                            Publicité mise à jour !
                        <?php elseif ($_GET['success'] === 'deleted'): ?>
                            Publicité supprimée !
                        <?php elseif ($_GET['success'] === 'toggled'): ?>
                            Statut modifié !
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>

        <div class="bg-white border border-slate-100 rounded-[2.5rem] overflow-hidden shadow-2xl">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-xs font-black text-slate-400 uppercase tracking-widest text-[10px]">
                    <tr>
                        <th class="px-8 py-6">Titre</th>
                        <th class="px-4 py-6">Lien</th>
                        <th class="px-4 py-6">Durée</th>
                        <th class="px-4 py-6">Récompense</th>
                        <th class="px-4 py-6">Vues</th>
                        <th class="px-4 py-6">Statut</th>
                        <th class="px-8 py-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($ads)): ?>
                        <tr>
                            <td colspan="7" class="px-8 py-12 text-center text-slate-400">
                                <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                                <p class="font-bold">Aucune publicité</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ads as $ad): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-8 py-6 font-bold text-slate-900"><?= htmlspecialchars($ad['title']) ?></td>
                                <td class="px-4 py-6 text-sm text-slate-500 max-w-xs truncate">
                                    <a href="<?= htmlspecialchars($ad['link']) ?>" target="_blank" class="hover:text-primary">
                                        <?= htmlspecialchars($ad['link']) ?>
                                    </a>
                                </td>
                                <td class="px-4 py-6 text-sm text-slate-500"><?= $ad['duration'] ?>s</td>
                                <td class="px-4 py-6 font-black text-success"><?= number_format($ad['reward'], 0) ?> XAF</td>
                                <td class="px-4 py-6 text-sm text-slate-500"><?= $ad['view_count'] ?? 0 ?></td>
                                <td class="px-4 py-6">
                                    <form action="/admin/ads/toggle" method="POST" class="inline">
                                        <input type="hidden" name="id" value="<?= $ad['id'] ?>">
                                        <button type="submit" class="cursor-pointer">
                                            <?php if ($ad['status'] === 'active'): ?>
                                                <span
                                                    class="bg-success/10 text-success px-3 py-1 rounded-full text-xs font-black uppercase">Active</span>
                                            <?php else: ?>
                                                <span
                                                    class="bg-slate-100 text-slate-400 px-3 py-1 rounded-full text-xs font-black uppercase">Inactive</span>
                                            <?php endif; ?>
                                        </button>
                                    </form>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end space-x-2">
                                        <button
                                            @click="editMode = true; currentAd = <?= htmlspecialchars(json_encode($ad)) ?>; showModal = true"
                                            class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors">
                                            <i data-lucide="edit" class="w-5 h-5"></i>
                                        </button>
                                        <form action="/admin/ads/delete" method="POST" class="inline">
                                            <input type="hidden" name="id" value="<?= $ad['id'] ?>">
                                            <button type="submit"
                                                class="p-2 text-danger hover:bg-danger/10 rounded-lg transition-colors"
                                                onclick="return confirm('Supprimer cette publicité ?')">
                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div @click="showModal = false" class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-3xl shadow-2xl max-w-2xl w-full p-8">
                <h2 class="text-2xl font-black text-slate-900 mb-6"
                    x-text="editMode ? 'Modifier la Publicité' : 'Nouvelle Publicité'"></h2>

                <form :action="editMode ? '/admin/ads/update' : '/admin/ads/create'" method="POST" class="space-y-6">
                    <input type="hidden" name="id" x-model="currentAd.id">

                    <div>
                        <label class="block text-sm font-black text-slate-700 mb-2">Titre de la Publicité</label>
                        <input type="text" name="title" x-model="currentAd.title" required
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-black text-slate-700 mb-2">Lien URL</label>
                        <input type="url" name="link" x-model="currentAd.link" required
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent"
                            placeholder="https://example.com">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-black text-slate-700 mb-2">Durée (secondes)</label>
                            <input type="number" name="duration" x-model="currentAd.duration" value="30" min="5"
                                max="300"
                                class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-black text-slate-700 mb-2">Récompense (XAF)</label>
                            <input type="number" name="reward" x-model="currentAd.reward" value="50" min="10" step="10"
                                class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-black text-slate-700 mb-2">Statut</label>
                        <select name="status" x-model="currentAd.status"
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="flex space-x-4 pt-4">
                        <button type="submit"
                            class="flex-1 bg-primary hover:bg-blue-600 text-white font-black py-4 rounded-xl transition-all shadow-lg">
                            <span x-text="editMode ? 'Mettre à jour' : 'Créer'"></span>
                        </button>
                        <button type="button" @click="showModal = false"
                            class="px-6 bg-slate-100 hover:bg-slate-200 text-slate-700 font-black py-4 rounded-xl transition-all">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script> lucide.createIcons(); </script>
</body>

</html>