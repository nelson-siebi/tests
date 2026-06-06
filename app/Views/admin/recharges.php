<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Admin Investian</title>
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
    <?php include __DIR__ . '/partials/mobile_nav.php'; ?>

    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 md:ml-72 p-4 md:p-10 pb-24 md:pb-10">
        <h1 class="text-3xl font-black text-slate-900 mb-8 tracking-tight">Demandes de Recharge</h1>

        <?php if (isset($_GET['success'])): ?>
            <div
                class="bg-emerald-50 border border-emerald-100 text-emerald-600 p-6 rounded-[2rem] font-bold mb-10 flex items-center space-x-4 shadow-lg shadow-emerald-500/5">
                <div class="bg-emerald-500 p-2 rounded-full text-white"><i data-lucide="check" class="w-5 h-5"></i></div>
                <span>La recharge a été approuvée avec succès !</span>
            </div>
        <?php endif; ?>

        <div class="bg-white border border-slate-100 rounded-[2.5rem] overflow-x-auto shadow-2xl shadow-slate-200/50">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-xs font-black text-slate-400 uppercase tracking-[0.2em]">
                    <tr>
                        <th class="px-8 py-6">Investisseur</th>
                        <th class="px-4 py-6">Montant</th>
                        <th class="px-4 py-6">Référence TX</th>
                        <th class="px-8 py-6 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <?php foreach ($pending as $tx): ?>
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="px-8 py-8">
                                <span
                                    class="text-slate-900 font-extrabold block text-lg"><?= htmlspecialchars($tx['user_name']) ?></span>
                                <span
                                    class="text-xs text-slate-400 font-bold tracking-widest uppercase italic italic italic">USR-<?= $tx['user_id'] ?></span>
                            </td>
                            <td class="px-4 py-8">
                                <span
                                    class="px-4 py-2 bg-primary/5 text-primary text-xl font-black rounded-2xl italic italic italic">
                                    <?= number_format($tx['amount'], 0, '.', ' ') ?> FCFA
                                </span>
                            </td>
                            <td class="px-4 py-8 text-slate-500 font-mono font-bold">
                                <?= htmlspecialchars($tx['reference']) ?>
                            </td>
                            <td class="px-8 py-8 text-right">
                                <form action="/admin/recharges/approve" method="POST">
                                    <input type="hidden" name="id" value="<?= $tx['id'] ?>">
                                    <button type="submit"
                                        class="bg-emerald-500 hover:bg-emerald-600 text-white font-black px-6 py-3.5 rounded-2xl transition-all shadow-xl shadow-emerald-500/20 hover:-translate-y-1">
                                        Approuver
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($pending)): ?>
                        <tr>
                            <td colspan="4" class="px-8 py-24 text-center">
                                <div
                                    class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i data-lucide="check-circle-2" class="w-8 h-8"></i>
                                </div>
                                <p class="text-slate-400 font-bold italic italic italic italic">Tout est à jour. Aucune
                                    demande en attente.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>