<!DOCTYPE html>
<html lang="<?= \App\Core\Language::getCurrent() ?>" class="scroll-smooth" x-data="{ darkMode: false }" :class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Investian</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            DEFAULT: '#2563eb',
                            hover: '#1d4ed8',
                            glow: 'rgba(37, 99, 235, 0.15)',
                        },
                        success: '#10b981',
                        warning: '#f59e0b',
                        danger: '#ef4444',
                        slate: {
                            950: '#030712',
                        }
                    }
                }
            }
        }
    </script>
    
    <script>
        // Check theme status in local storage on page load
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            window.isDark = true;
        } else {
            document.documentElement.classList.remove('dark');
            window.isDark = false;
        }
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        .theme-transition,
        .theme-transition * {
            transition: background-color 0.3s ease, border-color 0.3s ease, text-decoration-color 0.3s ease, box-shadow 0.3s ease;
        }

        .bg-grid {
            background-size: 30px 30px;
            background-image: radial-gradient(circle, rgba(148, 163, 184, 0.08) 1px, transparent 1.5px);
        }
        .dark .bg-grid {
            background-image: radial-gradient(circle, rgba(51, 65, 85, 0.15) 1px, transparent 1.5px);
        }

        .glass {
            background: rgba(255, 255, 255, 0.45);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }
        .dark class-glass, .dark .glass {
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.97);
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

<body 
    x-data="{ 
        darkMode: window.isDark,
        toggleTheme() {
            this.darkMode = !this.darkMode;
            if (this.darkMode) {
                document.documentElement.classList.add('dark');
                localStorage.setItem('color-theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('color-theme', 'light');
            }
        }
    }"
    class="theme-transition bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 font-sans antialiased min-h-screen relative overflow-x-hidden pb-24 lg:pb-6"
>
    <!-- Background overlay patterns -->
    <div class="absolute inset-0 bg-grid pointer-events-none -z-30"></div>
    <div class="absolute inset-0 overflow-hidden pointer-events-none -z-20">
        <div class="absolute top-[-10%] right-[-10%] w-[50vw] h-[50vw] max-w-[600px] bg-primary/10 dark:bg-primary/5 rounded-full blur-[120px] animate-pulse"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[50vw] h-[50vw] max-w-[600px] bg-emerald-500/10 dark:bg-emerald-500/5 rounded-full blur-[120px] animate-pulse" style="animation-delay: 3s"></div>
    </div>

    <!-- Top Navbar -->
    <nav class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200/50 dark:border-slate-800/80 sticky top-0 z-50 py-3.5 px-6 md:px-12 flex justify-between items-center shadow-sm">
        <div class="flex items-center space-x-3">
            <a href="/dashboard"
                class="bg-primary hover:bg-blue-600 text-white p-2 rounded-xl transition-all shadow-md flex items-center justify-center">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <img src="/images/logo.png" alt="Investian" class="h-10 w-auto">
        </div>

        <div class="flex items-center space-x-3">
            <!-- Community Icon -->
            <a href="/community"
                class="p-2.5 bg-slate-100 hover:bg-slate-200/80 dark:bg-slate-800 dark:hover:bg-slate-700/80 text-slate-600 dark:text-slate-300 rounded-xl hover:text-primary dark:hover:text-blue-400 transition-all relative">
                <i data-lucide="message-circle" class="w-5 h-5"></i>
                <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white dark:border-slate-900"></span>
            </a>

            <?php if (\App\Core\Session::get('user_role') === 'admin'): ?>
                <a href="/admin"
                    class="p-2.5 bg-amber-50 hover:bg-amber-100 dark:bg-amber-500/10 dark:hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 rounded-xl transition-all flex items-center justify-center"
                    title="<?= __('admin_panel') ?>">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </a>
            <?php endif; ?>

            <!-- Theme Toggle Button -->
            <button @click="toggleTheme()" class="text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl p-2.5 transition-all focus:outline-none" aria-label="Toggle theme">
                <i data-lucide="sun" x-show="darkMode" class="w-5 h-5"></i>
                <i data-lucide="moon" x-show="!darkMode" class="w-5 h-5"></i>
            </button>

            <!-- Language Switcher -->
            <div class="flex bg-slate-100 dark:bg-slate-800 p-1 rounded-xl">
                <a href="/lang?l=en"
                    class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider transition-all <?= \App\Core\Language::getCurrent() === 'en' ? 'bg-white dark:bg-slate-900 text-primary dark:text-blue-400 shadow-sm' : 'text-slate-400 dark:text-slate-500' ?>">EN</a>
                <a href="/lang?l=fr"
                    class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider transition-all <?= \App\Core\Language::getCurrent() === 'fr' ? 'bg-white dark:bg-slate-900 text-primary dark:text-blue-400 shadow-sm' : 'text-slate-400' ?>">FR</a>
            </div>

            <!-- Profile Dropdown -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.away="open = false"
                    class="text-primary dark:text-blue-400 p-2.5 bg-blue-50 dark:bg-blue-950/50 hover:bg-blue-100 dark:hover:bg-blue-950/80 rounded-xl border border-blue-100/50 dark:border-blue-900/30 transition-all">
                    <i data-lucide="user" class="w-5 h-5"></i>
                </button>
                <div x-show="open" x-transition x-cloak
                    class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-100 dark:border-slate-800 py-2 z-50">
                    <a href="/profile"
                        class="flex items-center space-x-2 px-4 py-2.5 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 hover:text-primary dark:hover:text-blue-400 transition-colors">
                        <i data-lucide="user" class="w-4 h-4"></i>
                        <span class="font-bold"><?= __('profile') ?></span>
                    </a>
                    <?php if (\App\Core\Session::get('user_role') === 'admin'): ?>
                        <a href="/admin"
                            class="flex items-center space-x-2 px-4 py-2.5 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 hover:text-primary dark:hover:text-blue-400 transition-colors">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                            <span class="font-bold"><?= __('admin_panel') ?></span>
                        </a>
                    <?php endif; ?>
                    <hr class="my-1.5 border-slate-100 dark:border-slate-800">
                    <a href="/logout"
                        class="flex items-center space-x-2 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        <span class="font-bold"><?= __('logout') ?></span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-6 md:p-12 space-y-12">
        <div class="text-center max-w-2xl mx-auto space-y-4 animate-fade-in">
            <h1 class="text-4xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tight">
                <?= __('investment_plans_title') ?> 🚀
            </h1>
            <p class="text-slate-500 dark:text-slate-400 font-bold italic text-lg"><?= __('choose_plan') ?></p>
            <a href="/guide"
                class="inline-flex items-center space-x-2 text-primary dark:text-blue-400 font-black text-xs uppercase tracking-wider hover:underline">
                <i data-lucide="help-circle" class="w-4.5 h-4.5"></i>
                <span><?= __('need_guide') ?></span>
            </a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div
                class="max-w-xl mx-auto bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 p-6 rounded-[2rem] font-bold mb-12 flex items-center space-x-4 animate-fade-in shadow-lg shadow-emerald-500/5">
                <i data-lucide="check-circle" class="w-8 h-8 shrink-0"></i>
                <div>
                    <p class="text-lg"><?= __('success_invest') ?></p>
                    <p class="text-xs font-semibold opacity-80"><?= __('success_invest_desc') ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            <?php foreach ($plans as $plan): ?>
                <div class="bg-white dark:bg-slate-900 rounded-[3rem] shadow-xl border border-slate-200/50 dark:border-slate-800/80 overflow-hidden group hover:border-primary/40 transition-all flex flex-col h-full animate-fade-in">
                    <!-- Plan Header Image -->
                    <div class="h-56 bg-slate-50 dark:bg-slate-950 relative overflow-hidden shrink-0">
                        <img src="<?= $plan['image_url'] ?>" alt="<?= htmlspecialchars($plan['name']) ?>"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 opacity-95 dark:opacity-80">
                        <div class="absolute inset-0 bg-gradient-to-t from-white/20 dark:from-slate-900/50 to-transparent"></div>
                        <div class="absolute top-6 left-6">
                            <span
                                class="bg-white/90 dark:bg-slate-900/95 backdrop-blur-md px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-widest text-primary dark:text-blue-400 shadow-sm border border-white dark:border-slate-800">
                                <?= __('duration') ?> : <?= $plan['duration_days'] ?> <?= __('days') ?>
                            </span>
                        </div>
                    </div>

                    <div class="p-8 md:p-10 flex flex-col flex-grow justify-between text-slate-850 dark:text-slate-100">
                        <div>
                            <div class="mb-8">
                                <h3 class="text-2xl font-black text-slate-900 dark:text-white mb-2"><?= htmlspecialchars($plan['name']) ?></h3>
                                <p class="text-slate-400 dark:text-slate-500 text-sm font-semibold italic line-clamp-2">
                                    <?= htmlspecialchars($plan['description']) ?>
                                </p>
                            </div>

                            <div class="space-y-4 mb-8">
                                <div class="flex justify-between items-center bg-slate-50 dark:bg-slate-950/50 p-4 rounded-2xl border border-slate-100 dark:border-slate-800/50">
                                    <span class="text-slate-400 dark:text-slate-500 text-[10px] font-black uppercase tracking-widest"><?= __('fixed_cost') ?></span>
                                    <span class="text-slate-900 dark:text-white text-xl font-black">
                                        <?= number_format($plan['price'], 0, '.', ' ') ?> <span class="text-sm">XAF</span>
                                    </span>
                                </div>
                                <div class="flex justify-between items-center bg-slate-50 dark:bg-slate-950/50 p-4 rounded-2xl border border-slate-100 dark:border-slate-800/50">
                                    <span class="text-slate-400 dark:text-slate-500 text-[10px] font-black uppercase tracking-widest"><?= __('daily_profit') ?></span>
                                    <span class="text-emerald-500 dark:text-emerald-400 text-xl font-black">
                                        +<?= number_format($plan['daily_profit_amount'] ?? (($plan['price'] * ($plan['daily_profit_percent'] ?? 0)) / 100), 0, '.', ' ') ?> XAF
                                    </span>
                                </div>
                                <div class="flex justify-between items-center bg-slate-50 dark:bg-slate-950/50 p-4 rounded-2xl border border-slate-100 dark:border-slate-800/50">
                                    <span class="text-slate-400 dark:text-slate-500 text-[10px] font-black uppercase tracking-widest"><?= __('ads_per_day_label') ?></span>
                                    <span class="text-amber-500 dark:text-amber-400 text-xl font-black">
                                        <?= $plan['ads_per_day'] ?> <?= __('ads') ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <form action="/invest" method="POST" class="space-y-4">
                            <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">

                            <?php
                            $sessionError = \App\Core\Session::get('plan_error');
                            $targetPlanId = \App\Core\Session::get('target_plan_id');
                            ?>
                            <?php if ($sessionError && $targetPlanId == $plan['id']): ?>
                                <div id="error-msg-<?= $plan['id'] ?>"
                                    class="bg-amber-500/10 border border-amber-500/20 p-5 rounded-2xl mb-4 text-slate-800 dark:text-slate-200">
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-shrink-0 bg-amber-500/20 p-2 rounded-xl text-amber-600 dark:text-amber-400">
                                            <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                                        </div>
                                        <div class="flex-grow">
                                            <?php if ($sessionError === 'insufficient_balance'): ?>
                                                <p class="text-sm font-black text-amber-700 dark:text-amber-400 mb-1">
                                                    💰 <?= __('insufficient_balance_title') ?>
                                                </p>
                                                <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-3">
                                                    <?= __('missing_balance') ?> <span class="text-base font-black text-amber-600 dark:text-amber-400"><?= number_format(\App\Core\Session::get('missing_amount'), 0, '.', ' ') ?> <?= __('cfa') ?></span>
                                                </p>
                                                <a href="/recharge?amount=<?= \App\Core\Session::get('missing_amount') ?>"
                                                    class="inline-flex items-center space-x-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-black px-4 py-2.5 rounded-xl transition-all shadow-md active:scale-95">
                                                    <i data-lucide="wallet" class="w-4 h-4"></i>
                                                    <span><?= __('recharge_now') ?></span>
                                                </a>
                                            <?php else: ?>
                                                <p class="text-sm font-black text-amber-700 dark:text-amber-400 mb-1">⚠️ Erreur</p>
                                                <p class="text-xs font-bold text-slate-600 dark:text-slate-400"><?= htmlspecialchars($sessionError) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <script>
                                    document.addEventListener('DOMContentLoaded', function () {
                                        const errorMsg = document.getElementById('error-msg-<?= $plan['id'] ?>');
                                        if (errorMsg) {
                                            errorMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                        }
                                    });
                                </script>
                            <?php endif; ?>

                            <button type="submit"
                                class="w-full bg-slate-900 dark:bg-slate-950 hover:bg-slate-800 dark:hover:bg-slate-900 border border-slate-800/80 dark:border-slate-800 text-white font-black py-4.5 rounded-2xl transition-all active:scale-[0.98] text-xs uppercase tracking-wider">
                                <?= __('activate_plan') ?> <?= number_format($plan['price'], 0, '.', ' ') ?> <?= __('cfa') ?>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Mobile Bottom Navigation -->
    <nav class="lg:hidden fixed bottom-4 left-4 right-4 bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg border border-slate-200/50 dark:border-slate-800/65 shadow-xl rounded-2xl z-50 px-4 py-3">
        <div class="flex justify-between items-center">
            <a href="/dashboard"
                class="flex flex-col items-center space-y-1 text-slate-400 dark:text-slate-500 hover:text-primary dark:hover:text-blue-400 transition-colors">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                <span class="text-[8px] font-black uppercase tracking-wider"><?= __('dashboard') ?></span>
            </a>
            <a href="/plans" class="flex flex-col items-center space-y-1 text-primary dark:text-blue-400">
                <i data-lucide="trending-up" class="w-5 h-5"></i>
                <span class="text-[8px] font-black uppercase tracking-wider"><?= __('invest') ?></span>
            </a>
            <a href="/ads"
                class="flex flex-col items-center space-y-1 text-slate-400 dark:text-slate-500 hover:text-primary dark:hover:text-blue-400 transition-colors">
                <i data-lucide="play-circle" class="w-5 h-5"></i>
                <span class="text-[8px] font-black uppercase tracking-wider"><?= __('ads') ?></span>
            </a>
            <a href="/recharge"
                class="flex flex-col items-center space-y-1 text-slate-400 dark:text-slate-500 hover:text-primary dark:hover:text-blue-400 transition-colors">
                <i data-lucide="wallet" class="w-5 h-5"></i>
                <span class="text-[8px] font-black uppercase tracking-wider"><?= __('wallet') ?></span>
            </a>
            <a href="/profile"
                class="flex flex-col items-center space-y-1 text-slate-400 dark:text-slate-500 hover:text-primary dark:hover:text-blue-400 transition-colors">
                <i data-lucide="user" class="w-5 h-5"></i>
                <span class="text-[8px] font-black uppercase tracking-wider"><?= __('profile') ?></span>
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