<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $title ?> - Admin
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
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

<body class="bg-gray-100 font-sans antialiased min-h-screen flex flex-col md:flex-row">
    <?php include __DIR__ . '/../partials/mobile_nav.php'; ?>

    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 md:ml-72 p-4 md:p-10 pb-24 md:pb-10">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-slate-900">
                <?= $title ?>
            </h1>
            <a href="/admin/guides/create"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-lg shadow-blue-200">
                Ajouter un Guide
            </a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 p-4 rounded-xl mb-6 font-medium">
                Opération réussie !
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-slate-500 uppercase text-[10px] font-bold tracking-widest">
                        <th class="px-6 py-4">Ordre</th>
                        <th class="px-6 py-4">Image</th>
                        <th class="px-6 py-4">Titre</th>
                        <th class="px-6 py-4">Statut</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($guides)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-400 font-medium">
                                Aucun guide trouvé.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($guides as $guide): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 font-bold text-slate-400">#<?= $guide['order_index'] ?></td>
                                <td class="px-6 py-4">
                                    <?php if (!empty($guide['image_url'])): ?>
                                        <img src="<?= htmlspecialchars($guide['image_url']) ?>" alt=""
                                            class="w-16 h-10 object-cover rounded-lg">
                                    <?php else: ?>
                                        <div
                                            class="w-16 h-10 bg-slate-100 rounded-lg flex items-center justify-center text-slate-400">
                                            <i data-lucide="image" class="w-5 h-5"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 font-bold text-slate-900"><?= htmlspecialchars($guide['title']) ?></td>
                                <td class="px-6 py-4">
                                    <?php if ($guide['status'] === 'active'): ?>
                                        <span
                                            class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold uppercase">Actif</span>
                                    <?php else: ?>
                                        <span
                                            class="bg-slate-100 text-slate-500 px-3 py-1 rounded-full text-xs font-bold uppercase">Inactif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end space-x-2">
                                        <a href="/admin/guides/edit?id=<?= $guide['id'] ?>"
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                            <i data-lucide="edit" class="w-5 h-5"></i>
                                        </a>
                                        <form action="/admin/guides/delete" method="POST" class="inline"
                                            onsubmit="return confirm('Supprimer ce guide ?');">
                                            <input type="hidden" name="id" value="<?= $guide['id'] ?>">
                                            <button type="submit"
                                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
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
    <script> lucide.createIcons(); </script>
</body>

</html>