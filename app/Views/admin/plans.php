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

    <main class="flex-1 md:ml-72 p-4 md:p-10 pb-24 md:pb-10">
        <div class="flex justify-between items-center mb-12">
            <div>
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mb-2">Plans d'Investissement</h1>
                <p class="text-slate-500 font-bold italic">Gérez vos offres de gains avec montant fixe et durée de 3
                    jours.</p>
            </div>
            <a href="/admin/plans/create"
                class="bg-primary hover:bg-blue-600 text-white font-black px-8 py-5 rounded-3xl transition-all shadow-2xl shadow-primary/25 hover:-translate-y-1 flex items-center space-x-2">
                <i data-lucide="plus" class="w-6 h-6"></i>
                <span>Nouveau Plan</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($plans as $plan): ?>
                <div
                    class="bg-white border border-slate-100 rounded-[3rem] shadow-xl overflow-hidden hover:shadow-2xl transition-all group relative">
                    <div class="h-48 relative overflow-hidden">
                        <img src="<?= htmlspecialchars($plan['image_url']) ?>"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-white via-white/20 to-transparent"></div>
                        <div class="absolute top-4 left-4">
                            <span
                                class="bg-white/90 backdrop-blur-md px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-widest text-primary shadow-sm border border-white">
                                Plan Actif
                            </span>
                        </div>
                    </div>
                    <div class="p-8">
                        <h3 class="text-2xl font-black text-slate-900 mb-2"><?= htmlspecialchars($plan['name']) ?></h3>
                        <p class="text-slate-400 text-xs font-bold mb-8 italic line-clamp-2">
                            <?= htmlspecialchars($plan['description']) ?>
                        </p>

                        <div class="bg-slate-50 p-6 rounded-[2rem] border border-slate-100 space-y-4 mb-8">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-400 font-black uppercase tracking-widest text-[10px]">Prix
                                    Fixe</span>
                                <span
                                    class="text-slate-900 font-black text-lg"><?= number_format($plan['price'], 0, '.', ' ') ?>
                                    XAF</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-400 font-black uppercase tracking-widest text-[10px]">Profit /
                                    Jour</span>
                                <span
                                    class="text-emerald-500 font-black text-lg">+<?= number_format($plan['daily_profit_amount'], 0) ?>
                                    XAF</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-400 font-black uppercase tracking-widest text-[10px]">Durée</span>
                                <span class="text-blue-600 font-black text-lg"><?= $plan['duration_days'] ?> Jours</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-400 font-black uppercase tracking-widest text-[10px]">Pubs /
                                    Jour</span>
                                <span class="text-amber-500 font-black text-lg"><?= $plan['ads_per_day'] ?></span>
                            </div>
                        </div>

                        <div class="flex space-x-3">
                            <a href="/admin/plans/edit?id=<?= $plan['id'] ?>"
                                class="flex-1 bg-slate-100 text-slate-900 font-black py-4 rounded-2xl text-xs uppercase hover:bg-primary hover:text-white transition-all text-center flex items-center justify-center">
                                Éditer
                            </a>
                            <form action="/admin/plans/delete" method="POST" class="inline"
                                onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce plan ?')">
                                <input type="hidden" name="id" value="<?= $plan['id'] ?>">
                                <button type="submit"
                                    class="bg-red-50 text-red-500 p-4 rounded-2xl hover:bg-red-500 hover:text-white transition-all">
                                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script> lucide.createIcons(); </script>
</body>

</html>