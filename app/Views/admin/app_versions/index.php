<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $title ?> - Admin Investian
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
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

<body class="bg-slate-50 text-slate-800 font-sans min-h-screen flex flex-col md:flex-row">
    <?php include __DIR__ . '/../partials/mobile_nav.php'; ?>
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <main class="flex-1 md:ml-72 p-4 md:p-10 pb-24 md:pb-10">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-6">
            <div>
                <h1 class="text-3xl font-black text-slate-900">Gestion des Versions APK</h1>
                <p class="text-slate-500 mt-1 font-medium">Uploadez et gérez les versions de l'application Android.</p>
            </div>
        </div>

        <?php if (\App\Core\Session::has('success')): ?>
            <div
                class="bg-emerald-50 border border-emerald-100 text-emerald-600 p-6 rounded-2xl font-bold mb-6 flex items-center space-x-4">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                <span>
                    <?= \App\Core\Session::get('success') ?>
                </span>
            </div>
            <?php \App\Core\Session::delete('success'); ?>
        <?php endif; ?>

        <?php if (\App\Core\Session::has('error')): ?>
            <div
                class="bg-red-50 border border-red-100 text-red-600 p-6 rounded-2xl font-bold mb-6 flex items-center space-x-4">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
                <span>
                    <?= \App\Core\Session::get('error') ?>
                </span>
            </div>
            <?php \App\Core\Session::delete('error'); ?>
        <?php endif; ?>

        <!-- Upload Form -->
        <div class="bg-white border border-slate-100 p-8 rounded-3xl shadow-xl mb-10">
            <h2 class="text-2xl font-black text-slate-900 mb-6 flex items-center space-x-3">
                <i data-lucide="upload-cloud" class="w-6 h-6 text-primary"></i>
                <span>Uploader une Nouvelle Version</span>
            </h2>

            <form action="/admin/app-versions/upload" method="POST" enctype="multipart/form-data" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nom de la Version *</label>
                        <input type="text" name="version_name" required placeholder="Ex: Version 1.0.0"
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary font-medium">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Code de Version *</label>
                        <input type="number" name="version_code" required placeholder="Ex: 1"
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary font-medium">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Fichier APK *</label>
                    <input type="file" name="apk_file" accept=".apk" required
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary font-medium file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-primary file:text-white hover:file:bg-blue-600">
                    <p class="text-xs text-slate-500 mt-2">Taille maximale: 100MB. Format: .apk uniquement</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Notes (Optionnel)</label>
                    <textarea name="notes" rows="3" placeholder="Décrivez les changements de cette version..."
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary font-medium"></textarea>
                </div>

                <div class="bg-amber-50 border border-amber-200 p-4 rounded-xl">
                    <p class="text-sm font-bold text-amber-800 flex items-start space-x-2">
                        <i data-lucide="info" class="w-4 h-4 flex-shrink-0 mt-0.5"></i>
                        <span>L'ancienne version sera automatiquement désactivée. La nouvelle version expirera dans 14
                            jours.</span>
                    </p>
                </div>

                <button type="submit"
                    class="bg-primary hover:bg-blue-600 text-white font-black px-8 py-4 rounded-2xl transition-all shadow-lg shadow-primary/25 flex items-center space-x-3">
                    <i data-lucide="upload" class="w-5 h-5"></i>
                    <span>Uploader la Version</span>
                </button>
            </form>
        </div>

        <!-- Active Version -->
        <?php if ($activeVersion): ?>
            <?php
            $daysLeft = \App\Models\AppVersion::getDaysUntilExpiry($activeVersion['upload_date'], $activeVersion['expiry_date']);
            ?>
            <div
                class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 p-8 rounded-3xl shadow-xl mb-10">
                <h2 class="text-2xl font-black text-emerald-900 mb-4 flex items-center space-x-3">
                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                    <span>Version Active</span>
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <p class="text-sm font-bold text-emerald-600 mb-1">Version</p>
                        <p class="text-xl font-black text-emerald-900">
                            <?= htmlspecialchars($activeVersion['version_name']) ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-emerald-600 mb-1">Téléchargements</p>
                        <p class="text-xl font-black text-emerald-900">
                            <?= $activeVersion['download_count'] ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-emerald-600 mb-1">Expire dans</p>
                        <p class="text-xl font-black text-emerald-900">
                            <?= $daysLeft ?> jour
                            <?= $daysLeft > 1 ? 's' : '' ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-amber-50 border border-amber-200 p-8 rounded-3xl mb-10">
                <p class="text-amber-800 font-bold flex items-center space-x-3">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    <span>Aucune version active. Uploadez une version APK pour permettre aux utilisateurs de télécharger
                        l'application.</span>
                </p>
            </div>
        <?php endif; ?>

        <!-- Version History -->
        <div class="bg-white border border-slate-100 p-8 rounded-3xl shadow-xl">
            <h2 class="text-2xl font-black text-slate-900 mb-6">Historique des Versions</h2>

            <?php if (empty($versions)): ?>
                <p class="text-slate-500 font-medium text-center py-8">Aucune version uploadée pour le moment.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($versions as $version): ?>
                        <?php
                        $daysLeft = \App\Models\AppVersion::getDaysUntilExpiry($version['upload_date'], $version['expiry_date']);
                        $isExpired = $daysLeft <= 0;
                        $isActive = $version['is_active'] && !$isExpired;
                        ?>
                        <div
                            class="border border-slate-200 p-6 rounded-2xl <?= $isActive ? 'bg-emerald-50 border-emerald-200' : '' ?>">
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <h3 class="text-lg font-black text-slate-900">
                                            <?= htmlspecialchars($version['version_name']) ?>
                                        </h3>
                                        <?php if ($isActive): ?>
                                            <span
                                                class="bg-emerald-500 text-white text-xs px-3 py-1 rounded-full font-black">ACTIVE</span>
                                        <?php elseif ($isExpired): ?>
                                            <span
                                                class="bg-red-500 text-white text-xs px-3 py-1 rounded-full font-black">EXPIRÉE</span>
                                        <?php else: ?>
                                            <span
                                                class="bg-slate-400 text-white text-xs px-3 py-1 rounded-full font-black">INACTIVE</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                        <div>
                                            <span class="text-slate-500 font-medium">Code:</span>
                                            <span class="text-slate-900 font-bold ml-1">
                                                <?= $version['version_code'] ?>
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-slate-500 font-medium">Taille:</span>
                                            <span class="text-slate-900 font-bold ml-1">
                                                <?= round($version['file_size'] / 1024 / 1024, 2) ?> MB
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-slate-500 font-medium">Téléchargements:</span>
                                            <span class="text-slate-900 font-bold ml-1">
                                                <?= $version['download_count'] ?>
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-slate-500 font-medium">Uploadée le:</span>
                                            <span class="text-slate-900 font-bold ml-1">
                                                <?= date('d/m/Y', strtotime($version['upload_date'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <?php if ($version['notes']): ?>
                                        <p class="text-sm text-slate-600 mt-2 italic">
                                            <?= htmlspecialchars($version['notes']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <form action="/admin/app-versions/delete" method="POST"
                                    onsubmit="return confirm('Supprimer cette version ?')">
                                    <input type="hidden" name="version_id" value="<?= $version['id'] ?>">
                                    <button type="submit"
                                        class="bg-red-50 hover:bg-red-100 text-red-600 font-bold px-4 py-2 rounded-xl transition-all flex items-center space-x-2">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        <span>Supprimer</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>