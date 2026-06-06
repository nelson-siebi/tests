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
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-6">
            <div>
                <h1 class="text-3xl font-black text-slate-900">Statistiques Globales</h1>
                <p class="text-slate-500 mt-1 font-medium italic">Résumé en temps réel de l'activité financière.</p>
            </div>
            <a href="/admin/payout" onclick="return confirm('Lancer le calcul des intérêts ?')"
                class="bg-emerald-500 hover:bg-emerald-600 text-white font-black px-8 py-4 rounded-2xl transition-all shadow-xl shadow-emerald-500/25 hover:-translate-y-1 flex items-center space-x-3">
                <i data-lucide="zap" class="w-5 h-5"></i>
                <span>Lancer les Payouts</span>
            </a>
        </div>

        <?php if (isset($_GET['payouts'])): ?>
            <div
                class="bg-emerald-50 border border-emerald-100 text-emerald-600 p-6 rounded-[2rem] font-bold mb-10 flex items-center space-x-4 shadow-lg shadow-emerald-500/5">
                <div class="bg-emerald-500 p-2 rounded-full text-white"><i data-lucide="check" class="w-5 h-5"></i></div>
                <span>Succès : <?= $_GET['payouts'] ?> paiements d'intérêts effectués aujourd'hui.</span>
            </div>
        <?php endif; ?>

        <?php if (Session::has('success')): ?>
            <div class="bg-emerald-50 border border-emerald-100 text-emerald-600 p-6 rounded-[2rem] font-bold mb-10 flex items-center space-x-4 shadow-lg shadow-emerald-500/5">
                <div class="bg-emerald-500 p-2 rounded-full text-white"><i data-lucide="check" class="w-5 h-5"></i></div>
                <span><?= Session::get('success') ?></span>
            </div>
            <?php Session::remove('success'); ?>
        <?php endif; ?>

        <?php if (Session::has('error')): ?>
            <div class="bg-red-50 border border-red-100 text-red-600 p-6 rounded-[2rem] font-bold mb-10 flex items-center space-x-4 shadow-lg shadow-red-500/5">
                <div class="bg-red-500 p-2 rounded-full text-white"><i data-lucide="alert-circle" class="w-5 h-5"></i></div>
                <span><?= Session::get('error') ?></span>
            </div>
            <?php Session::remove('error'); ?>
        <?php endif; ?>

        <?php if ($pendingMigrations > 0): ?>
            <div class="bg-amber-50 border border-amber-200 rounded-[2rem] p-6 mb-10 shadow-lg shadow-amber-500/10">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="bg-amber-500 p-3 rounded-full text-white">
                            <i data-lucide="database" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-amber-800 text-lg">Mises à jour de la base de données</h3>
                            <p class="text-amber-700"><?= $pendingMigrations ?> migration(s) en attente d'exécution</p>
                        </div>
                    </div>
                    <form action="/admin/migrations/run" method="POST">
                        <button type="submit" 
                                onclick="return confirm('Exécuter les migrations de la base de données ?')"
                                class="bg-amber-500 hover:bg-amber-600 text-white font-bold px-6 py-3 rounded-xl transition-all shadow-lg hover:-translate-y-1 flex items-center space-x-2">
                            <i data-lucide="play" class="w-5 h-5"></i>
                            <span>Exécuter les mises à jour</span>
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div
                class="bg-white border border-slate-100 p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 hover:border-primary/50 transition-all">
                <div class="w-12 h-12 bg-blue-50 text-primary rounded-2xl flex items-center justify-center mb-6">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <p class="text-slate-400 text-xs font-black uppercase tracking-widest mb-1">Membres Actifs</p>
                <h3 class="text-4xl font-black text-slate-900"><?= $userCount ?></h3>
            </div>

            <div
                class="bg-white border border-slate-100 p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 hover:border-emerald-500/50 transition-all">
                <div class="w-12 h-12 bg-emerald-50 text-success rounded-2xl flex items-center justify-center mb-6">
                    <i data-lucide="trending-up" class="w-6 h-6"></i>
                </div>
                <p class="text-slate-400 text-xs font-black uppercase tracking-widest mb-1">Total Dépôts</p>
                <h3 class="text-4xl font-black text-slate-900 italic italic">
                    <?= number_format($totalDeposits, 0, '.', ' ') ?> FCFA
                </h3>
            </div>

            <div
                class="bg-white border border-slate-100 p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 hover:border-danger/50 transition-all">
                <div class="w-12 h-12 bg-red-50 text-danger rounded-2xl flex items-center justify-center mb-6">
                    <i data-lucide="clock" class="w-6 h-6"></i>
                </div>
                <p class="text-slate-400 text-xs font-black uppercase tracking-widest mb-1">Attente Validation</p>
                <h3 class="text-4xl font-black text-danger"><?= $pendingRecharges ?></h3>
            </div>

            <?php if ($pendingMigrations == 0): ?>
                <div class="bg-white border border-slate-100 p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 hover:border-emerald-500/50 transition-all">
                    <div class="w-12 h-12 bg-emerald-50 text-emerald-500 rounded-2xl flex items-center justify-center mb-6">
                        <i data-lucide="database-check" class="w-6 h-6"></i>
                    </div>
                    <p class="text-slate-400 text-xs font-black uppercase tracking-widest mb-1">Base de données</p>
                    <h3 class="text-xl font-black text-emerald-600">À jour ✓</h3>
                    <p class="text-slate-400 text-xs mt-2">Toutes les migrations sont exécutées</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>