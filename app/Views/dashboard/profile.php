<!DOCTYPE html>
<html lang="<?= \App\Core\Language::getCurrent() ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Investian</title>
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
                    colors: { primary: '#3b82f6' }
                }
            }
        }
    </script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.4s ease-out forwards;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 font-sans min-h-screen pb-24 lg:pb-0">
    <!-- Top Navbar -->
    <nav
        class="bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-50 py-4 px-6 md:px-12 flex justify-between items-center shadow-sm">
        <div class="flex items-center space-x-3">
            <a href="/dashboard"
                class="bg-primary p-2 rounded-xl text-white shadow-lg shadow-primary/20 hover:scale-105 transition-transform flex items-center justify-center">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <img src="/images/logo.png" alt="Investian" class="h-10 w-auto">
        </div>

        <div class="flex items-center space-x-3">
            <!-- Community Icon -->
            <a href="/community"
                class="p-2 bg-slate-100 text-slate-600 rounded-lg hover:text-primary hover:bg-blue-50 transition-all relative">
                <i data-lucide="message-circle" class="w-5 h-5"></i>
                <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full border border-white"></span>
            </a>

            <?php if (\App\Core\Session::get('user_role') === 'admin'): ?>
                <a href="/admin"
                    class="p-2 bg-amber-50 text-amber-600 rounded-lg hover:text-amber-700 hover:bg-amber-100 transition-all flex items-center justify-center"
                    title="<?= __('admin_panel') ?>">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </a>
            <?php endif; ?>

            <div class="flex bg-slate-100 p-1 rounded-xl">
                <a href="/lang?l=en"
                    class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest <?= \App\Core\Language::getCurrent() === 'en' ? 'bg-white text-primary shadow-sm' : 'text-slate-400' ?>">EN</a>
                <a href="/lang?l=fr"
                    class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest <?= \App\Core\Language::getCurrent() === 'fr' ? 'bg-white text-primary shadow-sm' : 'text-slate-400' ?>">FR</a>
            </div>
        </div>

        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" @click.away="open = false"
                class="text-primary p-2 bg-blue-50 rounded-lg border border-blue-100 transition-all hover:bg-blue-100">
                <i data-lucide="user" class="w-5 h-5"></i>
            </button>
            <div x-show="open" x-transition x-cloak
                class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50">
                <a href="/profile"
                    class="flex items-center space-x-2 px-4 py-2 text-sm text-slate-600 hover:bg-gray-50 hover:text-primary transition-colors">
                    <i data-lucide="user" class="w-4 h-4"></i>
                    <span class="font-bold"><?= __('profile') ?></span>
                </a>
                <?php if (\App\Core\Session::get('user_role') === 'admin'): ?>
                    <a href="/admin"
                        class="flex items-center space-x-2 px-4 py-2 text-sm text-slate-600 hover:bg-gray-50 hover:text-primary transition-colors">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                        <span class="font-bold"><?= __('admin_panel') ?></span>
                    </a>
                <?php endif; ?>
                <hr class="my-1 border-gray-50">
                <a href="/logout"
                    class="flex items-center space-x-2 px-4 py-2 text-sm text-red-500 hover:bg-red-50 transition-colors">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span class="font-bold"><?= __('logout') ?></span>
                </a>
            </div>
        </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto p-6 md:p-12">
        <div class="mb-12 animate-fade-in">
            <h1 class="text-4xl font-black text-slate-900 tracking-tight mb-2"><?= __('profile_settings') ?> ⚙️</h1>
            <p class="text-slate-500 font-bold italic"><?= __('account_info') ?></p>
        </div>

        <?php if (\App\Core\Session::has('error')): ?>
            <div
                class="mb-8 bg-red-50 border border-red-100 p-6 rounded-[2rem] animate-fade-in flex items-center space-x-3">
                <i data-lucide="alert-circle" class="w-6 h-6 text-red-500"></i>
                <p class="font-black text-red-600">
                    <?= \App\Core\Session::get('error');
                    \App\Core\Session::remove('error'); ?>
                </p>
            </div>
        <?php endif; ?>

        <?php if (\App\Core\Session::has('success')): ?>
            <div
                class="mb-8 bg-emerald-50 border border-emerald-100 p-6 rounded-[2rem] animate-fade-in flex items-center space-x-3">
                <i data-lucide="check-circle" class="w-6 h-6 text-emerald-500"></i>
                <p class="font-black text-emerald-600">
                    <?= \App\Core\Session::get('success');
                    \App\Core\Session::remove('success'); ?>
                </p>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            <!-- Sidebar-like User Card -->
            <div class="lg:col-span-4 space-y-8">
                <div class="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-xl shadow-slate-200/50 text-center animate-fade-in"
                    style="animation-delay: 0.1s">
                    <div
                        class="w-24 h-24 bg-primary/10 text-primary rounded-[2rem] flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="user" class="w-12 h-12"></i>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 mb-1"><?= htmlspecialchars($user['name']) ?></h3>
                    <p class="text-slate-400 font-bold text-sm mb-8 italic"><?= htmlspecialchars($user['email']) ?></p>

                    <div class="bg-slate-50 p-6 rounded-3xl border border-slate-100 text-left">
                        <span
                            class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1"><?= __('balance') ?></span>
                        <span
                            class="text-2xl font-black text-slate-900"><?= number_format($user['balance'], 0, '.', ' ') ?>
                            <span class="text-sm">XAF</span></span>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-2 gap-4 animate-fade-in" style="animation-delay: 0.2s">
                    <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-lg shadow-slate-200/40">
                        <i data-lucide="trending-up" class="w-8 h-8 text-primary mb-3"></i>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">
                            <?= __('active_investments_count') ?>
                        </p>
                        <p class="text-xl font-black text-slate-900"><?= count($investments) ?></p>
                    </div>
                    <a href="/withdraw"
                        class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-lg shadow-slate-200/40 group hover:border-primary transition-all">
                        <i data-lucide="wallet"
                            class="w-8 h-8 text-emerald-500 mb-3 group-hover:scale-110 transition-transform"></i>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">
                            <?= __('withdrawal') ?>
                        </p>
                        <p class="text-xl font-black text-slate-900"><?= __('min_1k') ?></p>
                    </a>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="lg:col-span-8 space-y-10">
                <!-- Active Investments section -->
                <div class="animate-fade-in" style="animation-delay: 0.3s">
                    <h2 class="text-xl font-black text-slate-900 mb-6 flex items-center space-x-2">
                        <i data-lucide="layout-grid" class="w-6 h-6 text-primary"></i>
                        <span><?= __('my_active_investments') ?></span>
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php if (empty($investments)): ?>
                            <div
                                class="col-span-full bg-white p-12 rounded-[2.5rem] border border-dashed border-slate-200 text-center">
                                <i data-lucide="package-open" class="w-16 h-16 text-slate-200 mx-auto mb-4"></i>
                                <p class="text-slate-400 font-bold italic"><?= __('no_investments_yet') ?>
                                </p>
                                <a href="/plans"
                                    class="inline-block mt-4 text-primary font-black uppercase text-[10px] tracking-widest border-b-2 border-primary/20 hover:border-primary transition-all"><?= __('discover_plans') ?></a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($investments as $inv): ?>
                                <div
                                    class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 relative overflow-hidden group">
                                    <div
                                        class="absolute top-0 right-0 w-24 h-24 bg-primary/5 rounded-bl-[4rem] -mr-8 -mt-8 group-hover:bg-primary/10 transition-colors">
                                    </div>
                                    <div class="relative">
                                        <h4 class="text-lg font-black text-slate-900 mb-1">
                                            <?= htmlspecialchars($inv['plan_name']) ?>
                                        </h4>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6">
                                            <?= __('invested') ?>
                                            : <?= number_format($inv['amount'], 0, '.', ' ') ?>         <?= __('cfa') ?>
                                        </p>

                                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-50">
                                            <div>
                                                <p class="text-[10px] font-black text-slate-400 uppercase">
                                                    <?= __('total_profit_label') ?>
                                                </p>
                                                <p class="text-lg font-black text-emerald-500">
                                                    +<?= number_format($inv['total_profit'], 0, '.', ' ') ?> <span
                                                        class="text-[10px]">XAF</span></p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-[10px] font-black text-slate-400 uppercase">
                                                    <?= __('played_ads') ?>
                                                </p>
                                                <p class="text-lg font-black text-slate-900"><?= $inv['ads_watched'] ?? 0 ?> /
                                                    <?= $inv['ads_per_day'] ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Update Form -->
                <div class="bg-white p-8 md:p-12 rounded-[3rem] border border-slate-100 shadow-2xl shadow-slate-200/50 animate-fade-in"
                    style="animation-delay: 0.4s">
                    <h2 class="text-xl font-black text-slate-900 mb-8 flex items-center space-x-2">
                        <i data-lucide="settings" class="w-6 h-6 text-primary"></i>
                        <span><?= __('edit_info') ?></span>
                    </h2>

                    <form action="/profile" method="POST" class="space-y-8">
                        <div class="space-y-2">
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Langue
                                / Language</label>
                            <div class="grid grid-cols-2 gap-4">
                                <a href="/lang?l=fr"
                                    class="block text-center py-4 rounded-2xl border-2 transition-all font-black <?= \App\Core\Language::getCurrent() === 'fr' ? 'border-primary bg-primary/5 text-primary' : 'border-slate-100 text-slate-400 hover:border-slate-200' ?>">
                                    Français
                                </a>
                                <a href="/lang?l=en"
                                    class="block text-center py-4 rounded-2xl border-2 transition-all font-black <?= \App\Core\Language::getCurrent() === 'en' ? 'border-primary bg-primary/5 text-primary' : 'border-slate-100 text-slate-400 hover:border-slate-200' ?>">
                                    English
                                </a>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1"><?= __('full_name') ?></label>
                            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required
                                class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-4 text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        </div>

                        <div class="pt-8 border-t border-slate-50">
                            <div class="bg-blue-50/50 p-6 rounded-3xl border border-blue-100 mb-8">
                                <h4 class="text-sm font-black text-slate-900 mb-2"><?= __('password_label') ?></h4>
                                <p class="text-[10px] text-slate-500 font-bold leading-relaxed italic">
                                    <?= __('new_password_info') ?>
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label
                                        class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1"><?= __('new_password') ?></label>
                                    <input type="password" name="password" placeholder="••••••••"
                                        class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-4 text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                                </div>
                                <div class="space-y-2">
                                    <label
                                        class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1"><?= __('confirm_password') ?></label>
                                    <input type="password" name="confirm_password" placeholder="••••••••"
                                        class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-4 text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                                </div>
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full bg-slate-900 hover:bg-slate-800 text-white font-black py-5 rounded-2xl transition-all shadow-xl shadow-slate-200 active:scale-[0.98]">
                            <?= __('save_changes') ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Mobile Bottom Navigation -->
    <nav
        class="lg:hidden fixed bottom-4 left-4 right-4 bg-white/80 backdrop-blur-lg border border-gray-100 shadow-2xl rounded-2xl z-[100] px-4 py-3">
        <div class="flex justify-between items-center">
            <a href="/dashboard"
                class="flex flex-col items-center space-y-1 text-slate-400 hover:text-primary transition-colors">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                <span class="text-[8px] font-bold uppercase tracking-[0.05em]"><?= __('dashboard') ?></span>
            </a>
            <a href="/plans"
                class="flex flex-col items-center space-y-1 text-slate-400 hover:text-primary transition-colors">
                <i data-lucide="trending-up" class="w-5 h-5"></i>
                <span class="text-[8px] font-bold uppercase tracking-[0.05em]"><?= __('invest') ?></span>
            </a>
            <a href="/ads"
                class="flex flex-col items-center space-y-1 text-slate-400 hover:text-primary transition-colors">
                <i data-lucide="play-circle" class="w-5 h-5"></i>
                <span class="text-[8px] font-bold uppercase tracking-[0.05em]"><?= __('ads') ?></span>
            </a>
            <a href="/recharge"
                class="flex flex-col items-center space-y-1 text-slate-400 hover:text-primary transition-colors">
                <i data-lucide="wallet" class="w-5 h-5"></i>
                <span class="text-[8px] font-bold uppercase tracking-[0.05em]"><?= __('wallet') ?></span>
            </a>
        </div>
    </nav>

    <script>lucide.createIcons();</script>
    <?php include __DIR__ . '/../partials/whatsapp.php'; ?>
</body>

</html>