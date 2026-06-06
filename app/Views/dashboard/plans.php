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
                    colors: { primary: '#3b82f6', success: '#10b981', warning: '#f59e0b', danger: '#ef4444' }
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
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.4s ease-out forwards;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 font-sans min-h-screen">
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

            <!-- Profile Dropdown -->
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

    <main class="max-w-7xl mx-auto p-6 md:p-12">
        <div class="text-center max-w-2xl mx-auto mb-16 animate-fade-in">
            <h1 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tight mb-4">
                <?= __('investment_plans_title') ?> 🚀
            </h1>
            <p class="text-slate-500 font-bold italic text-lg"><?= __('choose_plan') ?></p>
            <a href="/guide"
                class="inline-flex items-center space-x-2 text-primary font-bold text-xs mt-6 hover:underline">
                <i data-lucide="help-circle" class="w-4 h-4"></i>
                <span><?= __('need_guide') ?></span>
            </a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div
                class="max-w-xl mx-auto bg-emerald-50 border border-emerald-100 text-emerald-600 p-6 rounded-[2rem] font-bold mb-12 flex items-center space-x-4 animate-fade-in shadow-xl shadow-emerald-100">
                <i data-lucide="check-circle" class="w-8 h-8 text-emerald-500"></i>
                <div>
                    <p class="text-lg"><?= __('success_invest') ?></p>
                    <p class="text-xs font-medium opacity-80"><?= __('success_invest_desc') ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            <?php foreach ($plans as $plan): ?>
                <div
                    class="bg-white rounded-[3rem] shadow-xl border border-slate-100 overflow-hidden group hover:border-primary/40 transition-all flex flex-col h-full animate-fade-in">
                    <!-- Plan Header Image -->
                    <div class="h-56 bg-slate-50 relative overflow-hidden">
                        <img src="<?= $plan['image_url'] ?>" alt="<?= htmlspecialchars($plan['name']) ?>"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-white/40 to-transparent"></div>
                        <div class="absolute top-6 left-6">
                            <span
                                class="bg-white/90 backdrop-blur-md px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-widest text-primary shadow-sm border border-white">
                                <?= __('duration') ?> : <?= $plan['duration_days'] ?>     <?= __('days') ?>
                            </span>
                        </div>
                    </div>

                    <div class="p-10 flex flex-col flex-1">
                        <div class="mb-8">
                            <h3 class="text-2xl font-black text-slate-900 mb-2"><?= htmlspecialchars($plan['name']) ?></h3>
                            <p class="text-slate-400 text-sm font-bold italic line-clamp-2">
                                <?= htmlspecialchars($plan['description']) ?>
                            </p>
                        </div>

                        <div class="space-y-4 mb-10 flex-1">
                            <div
                                class="flex justify-between items-center bg-slate-50 p-5 rounded-2xl border border-slate-100">
                                <span
                                    class="text-slate-400 text-[10px] font-black uppercase tracking-widest"><?= __('fixed_cost') ?></span>
                                <span
                                    class="text-slate-900 text-xl font-black"><?= number_format($plan['price'], 0, '.', ' ') ?>
                                    <span class="text-sm">XAF</span></span>
                            </div>
                            <div
                                class="flex justify-between items-center bg-slate-50 p-5 rounded-2xl border border-slate-100">
                                <span
                                    class="text-slate-400 text-[10px] font-black uppercase tracking-widest"><?= __('daily_profit') ?></span>
                                <span
                                    class="text-emerald-500 text-xl font-black">+<?= number_format($plan['daily_profit_amount'] ?? (($plan['price'] * ($plan['daily_profit_percent'] ?? 0)) / 100), 0, '.', ' ') ?>
                                    XAF</span>
                            </div>
                            <div
                                class="flex justify-between items-center bg-slate-50 p-5 rounded-2xl border border-slate-100">
                                <span
                                    class="text-slate-400 text-[10px] font-black uppercase tracking-widest"><?= __('ads_per_day_label') ?></span>
                                <span class="text-amber-600 text-xl font-black"><?= $plan['ads_per_day'] ?>
                                    <?= __('ads') ?></span>
                            </div>
                        </div>

                        <form action="/invest" method="POST" class="space-y-6">
                            <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">

                            <?php
                            $sessionError = \App\Core\Session::get('plan_error');
                            $targetPlanId = \App\Core\Session::get('target_plan_id');
                            ?>
                            <?php if ($sessionError && $targetPlanId == $plan['id']): ?>
                                <div id="error-msg-<?= $plan['id'] ?>"
                                    class="sticky top-0 z-50 bg-gradient-to-r from-amber-50 to-orange-50 border-2 border-amber-200 p-6 rounded-2xl mb-6 shadow-2xl animate-pulse">
                                    <div class="flex items-start space-x-4">
                                        <div class="flex-shrink-0 bg-amber-100 p-3 rounded-full">
                                            <i data-lucide="alert-triangle" class="w-8 h-8 text-amber-600"></i>
                                        </div>
                                        <div class="flex-1">
                                            <?php if ($sessionError === 'insufficient_balance'): ?>
                                                <p class="text-base font-black text-amber-900 mb-2">💰
                                                    <?= __('insufficient_balance_title') ?>
                                                </p>
                                                <p class="text-sm font-bold text-amber-700 mb-3">
                                                    <?= __('missing_balance') ?> <span
                                                        class="text-lg text-amber-900"><?= number_format(\App\Core\Session::get('missing_amount'), 0, '.', ' ') ?>
                                                        <?= __('cfa') ?></span>
                                                </p>
                                                <a href="/recharge?amount=<?= \App\Core\Session::get('missing_amount') ?>"
                                                    class="inline-flex items-center space-x-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-black px-6 py-3 rounded-xl transition-all shadow-lg active:scale-95">
                                                    <i data-lucide="wallet" class="w-4 h-4"></i>
                                                    <span><?= __('recharge_now') ?></span>
                                                </a>
                                            <?php else: ?>
                                                <p class="text-base font-black text-amber-900 mb-2">⚠️ Erreur</p>
                                                <p class="text-sm font-bold text-amber-700"><?= htmlspecialchars($sessionError) ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <script>
                                    // Auto-scroll to error message on mobile
                                    document.addEventListener('DOMContentLoaded', function () {
                                        const errorMsg = document.getElementById('error-msg-<?= $plan['id'] ?>');
                                        if (errorMsg) {
                                            errorMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                            // Remove pulse animation after 3 seconds
                                            setTimeout(() => {
                                                errorMsg.classList.remove('animate-pulse');
                                            }, 3000);
                                        }
                                    });
                                </script>
                                <?php
                                // Clear error after displaying it for the correct plan
                                // Ideally clear it once at the top of the page, but since we are in a loop 
                                // and we want to show it, we can just leave it in session until the next request 
                                // OR clear it at the very end of the page.
                                // For now, let's NOT clear it inside the loop to avoid the bug.
                                // We can add a cleanup script at the bottom of the page or just rely on flash data behavior 
                                // if the framework supports it. 
                                // Assuming Session::get doesn't clear.
                                // We should verify if we need to manually clear it. 
                                // The previous code did Session::remove.
                                // If we don't remove it, it persists. 
                                // Let's add a cleanup block at the end of the page.
                                ?>
                            <?php endif; ?>

                            <button type="submit"
                                class="w-full bg-slate-900 hover:bg-slate-800 text-white font-black py-5 rounded-2xl transition-all shadow-xl shadow-slate-200 active:scale-[0.98] text-sm uppercase tracking-widest">
                                <?= __('activate_plan') ?>
                                <?= number_format($plan['price'], 0, '.', ' ') ?>     <?= __('cfa') ?>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
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
            <a href="/plans" class="flex flex-col items-center space-y-1 text-primary">
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
            <a href="/profile"
                class="flex flex-col items-center space-y-1 text-slate-400 hover:text-primary transition-colors">
                <i data-lucide="user" class="w-5 h-5"></i>
                <span class="text-[8px] font-bold uppercase tracking-[0.05em]"><?= __('profile') ?></span>
            </a>
        </div>
    </nav>

    <script>
        lucide.createIcons();
        window.addEventListener('load', () => {
            const errorMsg = document.getElementById('error-msg');
            if (errorMsg) errorMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    </script>
    <?php
    \App\Core\Session::remove('plan_error');
    \App\Core\Session::remove('target_plan_id');
    \App\Core\Session::remove('missing_amount');
    ?>
    <?php include __DIR__ . '/../partials/whatsapp.php'; ?>
</body>

</html>