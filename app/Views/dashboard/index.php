<!DOCTYPE html>
<html lang="fr" class="scroll-smooth" x-data="{ darkMode: false }" :class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2563eb">
    <link rel="manifest" href="/manifest.json">
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
            <a href="/dashboard" class="hover:opacity-95 transition-opacity">
                <img src="/images/logo.png" alt="Investian" class="h-10 w-auto">
            </a>
        </div>

        <div class="hidden lg:flex items-center space-x-8">
            <a href="/dashboard" class="text-primary dark:text-blue-400 font-bold transition-all"><?= __('dashboard') ?></a>
            <a href="/plans" class="text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-blue-400 font-bold transition-all"><?= __('invest') ?></a>
            <a href="/ads" class="text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-blue-400 font-bold transition-all"><?= __('ads') ?></a>
            <a href="/recharge" class="text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-blue-400 font-bold transition-all"><?= __('wallet') ?></a>
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

            <a href="/download"
                class="p-2.5 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-500/10 dark:hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 rounded-xl transition-all flex items-center justify-center"
                title="Télécharger l'App">
                <i data-lucide="download" class="w-5 h-5"></i>
            </a>

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
                <div x-show="open" x-transition
                    class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-100 dark:border-slate-800 py-2 z-50">
                    <a href="/profile"
                        class="flex items-center space-x-2 px-4 py-2.5 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 hover:text-primary dark:hover:text-blue-400 transition-colors">
                        <i data-lucide="user" class="w-4 h-4"></i>
                        <span class="font-bold"><?= __('profile') ?></span>
                    </a>
                    <?php if (isset($user['role']) && $user['role'] === 'admin'): ?>
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

    <main class="max-w-7xl mx-auto p-6 md:p-10 space-y-10">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight mb-1">
                    <?= __('welcome') ?>, <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?>! 👋
                </h1>
                <p class="text-slate-500 dark:text-slate-400 font-semibold text-sm"><?= date('l, d F Y') ?></p>
            </div>
            <div class="flex flex-wrap gap-3 w-full md:w-auto">
                <a href="/guide"
                    class="flex-1 min-w-[140px] bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-800 font-bold px-4 py-3.5 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all flex items-center justify-center space-x-2">
                    <i data-lucide="book-open" class="w-5 h-5 text-primary"></i>
                    <span><?= __('how_to_invest') ?></span>
                </a>
                <a href="/withdraw"
                    class="flex-1 min-w-[140px] bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-800 font-bold px-4 py-3.5 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all flex items-center justify-center space-x-2">
                    <i data-lucide="arrow-up-circle" class="w-5 h-5 text-emerald-500"></i>
                    <span><?= __('withdrawal') ?></span>
                </a>
                <a href="/recharge"
                    class="flex-1 min-w-[140px] bg-primary hover:bg-blue-600 text-white font-bold px-4 py-3.5 rounded-2xl transition-all shadow-lg shadow-primary/20 hover:shadow-primary/30 flex items-center justify-center space-x-2">
                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    <span><?= __('deposit_btn') ?></span>
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Balance Card (Premium Navy Metallic Style) -->
            <div class="lg:col-span-2 bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-950 rounded-3xl p-8 text-white shadow-xl relative overflow-hidden border border-slate-800/65 group">
                <div class="absolute right-0 top-0 w-32 h-32 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>
                <!-- Chip & Logo -->
                <div class="flex justify-between items-start mb-8 relative z-10">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5"><?= __('balance') ?></p>
                        <div class="flex items-baseline space-x-2">
                            <span class="text-4xl font-extrabold tracking-tight"><?= number_format($user['balance'], 0, '.', ' ') ?></span>
                            <span class="text-lg font-bold text-blue-400 uppercase tracking-wider"><?= __('cfa') ?></span>
                        </div>
                    </div>
                    <div class="bg-white/10 p-3 rounded-2xl border border-white/5 flex items-center justify-center">
                        <i data-lucide="wallet" class="w-6 h-6 text-blue-400"></i>
                    </div>
                </div>

                <!-- Card Bottom Section -->
                <div class="grid grid-cols-2 gap-6 border-t border-white/10 pt-6 relative z-10">
                    <div>
                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1.5"><?= __('total_profits') ?></p>
                        <p class="text-xl font-bold text-emerald-400"><?= number_format($totalProfits, 0, '.', ' ') ?> <?= __('cfa') ?></p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1.5"><?= __('active_investments') ?></p>
                        <p class="text-xl font-bold text-blue-400"><?= count($investments) ?></p>
                    </div>
                </div>
            </div>

            <!-- Ad Progress Card -->
            <div class="glass p-8 rounded-3xl shadow-lg flex flex-col justify-between group">
                <div>
                    <div class="flex justify-between items-start mb-6">
                        <h3 class="text-lg font-black text-slate-800 dark:text-white"><?= __('watched_ads') ?></h3>
                        <div class="bg-amber-500/10 p-2 rounded-xl text-amber-500">
                            <i data-lucide="play-circle" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline space-x-2 mb-4">
                        <span class="text-3xl font-bold dark:text-white"><?= $watchedToday ?></span>
                        <span class="text-slate-400 dark:text-slate-500 font-bold text-sm">/ <?= $requiredAds ?></span>
                    </div>
                    <div class="w-full bg-slate-200/50 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden mb-2">
                        <div class="bg-amber-400 h-full rounded-full transition-all duration-500"
                            style="width: <?= ($requiredAds > 0) ? ($watchedToday / $requiredAds * 100) : 0 ?>%"></div>
                    </div>
                </div>
                <a href="/ads"
                    class="mt-6 text-center text-amber-600 dark:text-amber-400 font-black text-xs uppercase tracking-widest bg-amber-500/10 py-3.5 rounded-2xl hover:bg-amber-500/20 transition-all flex items-center justify-center space-x-1">
                    <span><?= __('watch_ads_btn') ?></span>
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
            </div>

            <!-- Referral Card -->
            <div class="glass p-8 rounded-3xl shadow-lg flex flex-col justify-between group" x-data="{ 
                copyText: '<?= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/register?ref=' . ($user['referral_code'] ?? '') ?>',
                copied: false,
                copyToClipboard() {
                    if(!this.copyText) return;
                    navigator.clipboard.writeText(this.copyText);
                    this.copied = true;
                    setTimeout(() => this.copied = false, 2000);
                }
            }">
                <div>
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-lg font-black text-slate-800 dark:text-white"><?= __('referral_bonus') ?></h3>
                        <div class="bg-primary/10 p-2 rounded-xl text-primary dark:text-blue-400">
                            <i data-lucide="users" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 text-xs font-semibold leading-relaxed mb-6">
                        <?= __('invite_friends') ?>
                    </p>
                </div>
                <div class="bg-indigo-50/50 dark:bg-slate-950/40 border border-indigo-100/30 dark:border-slate-800/80 p-3 rounded-2xl flex items-center justify-between group/code cursor-pointer hover:border-primary/30 transition-all"
                    @click="copyToClipboard()">
                    <div class="overflow-hidden flex-1">
                        <p class="text-[9px] font-black text-indigo-400 dark:text-blue-400 uppercase tracking-widest mb-0.5">
                            <?= __('referral_link_label') ?>
                        </p>
                        <p class="text-xs font-bold text-slate-600 dark:text-slate-300 truncate pr-2">
                            <?= htmlspecialchars($user['referral_code'] ?? '') ?>
                        </p>
                    </div>
                    <button
                        class="p-2 bg-white dark:bg-slate-900 rounded-xl shadow-sm text-slate-400 dark:text-slate-500 group-hover/code:text-primary dark:group-hover/code:text-blue-400 transition-colors">
                        <i data-lucide="copy" class="w-4 h-4" x-show="!copied"></i>
                        <i data-lucide="check" class="w-4 h-4 text-emerald-500" x-show="copied" x-cloak></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Session Message Alerts -->
        <?php if (\App\Core\Session::has('error')): ?>
            <div id="error-msg" class="bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 p-6 rounded-3xl">
                <div class="flex items-center space-x-3">
                    <i data-lucide="alert-circle" class="w-6 h-6"></i>
                    <p class="font-bold text-sm"><?= \App\Core\Session::get('error'); \App\Core\Session::remove('error'); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (\App\Core\Session::has('success')): ?>
            <div id="success-msg" class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 p-6 rounded-3xl">
                <div class="flex items-center space-x-3">
                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                    <p class="font-bold text-sm"><?= \App\Core\Session::get('success'); \App\Core\Session::remove('success'); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Available Plans Section -->
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-black text-slate-900 dark:text-white"><?= __('investment_plans_title') ?></h2>
                <a href="/plans"
                    class="text-primary dark:text-blue-400 font-bold text-xs uppercase tracking-widest hover:underline flex items-center space-x-1">
                    <span><?= __('view_more') ?></span>
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach ($availablePlans as $plan): ?>
                    <div class="bg-white dark:bg-slate-900 rounded-3xl overflow-hidden border border-slate-200/50 dark:border-slate-800/80 shadow-md group hover:border-primary/30 transition-all flex flex-col h-full">
                        <div class="h-36 bg-slate-100 dark:bg-slate-950 relative overflow-hidden shrink-0">
                            <img src="<?= $plan['image_url'] ?>" alt="<?= htmlspecialchars($plan['name']) ?>"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 opacity-90 dark:opacity-80">
                            <div class="absolute bottom-3 left-4">
                                <span class="bg-primary/90 backdrop-blur-md text-white text-[9px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider shadow-sm">
                                    +<?= number_format($plan['daily_profit_amount'] ?? (($plan['price'] * ($plan['daily_profit_percent'] ?? 0)) / 100), 0) ?> XAF/j
                                </span>
                            </div>
                        </div>
                        <div class="p-6 flex flex-col justify-between flex-1">
                            <div>
                                <h4 class="text-base font-black text-slate-900 dark:text-white mb-1"><?= htmlspecialchars($plan['name']) ?></h4>
                                <p class="text-xs font-black text-primary dark:text-blue-400 mb-4"><?= number_format($plan['price'], 0) ?> <?= __('cfa') ?></p>
                            </div>
                            <a href="/plans"
                                class="block w-full text-center bg-slate-100 dark:bg-slate-800 hover:bg-primary hover:text-white dark:hover:bg-primary text-slate-800 dark:text-slate-200 text-xs font-black py-3 rounded-xl transition-all active:scale-[0.98] uppercase tracking-wider">
                                <?= __('details') ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- Active Investments -->
            <div class="lg:col-span-2 space-y-6">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-black text-slate-900 dark:text-white"><?= __('active_investments_title') ?></h2>
                    <a href="/plans" class="text-primary dark:text-blue-400 font-bold text-sm hover:underline"><?= __('view_plans') ?></a>
                </div>

                <?php if (empty($investments)): ?>
                    <div class="bg-white/40 dark:bg-slate-900/40 p-12 rounded-3xl border border-slate-200/50 dark:border-slate-800/50 border-dashed text-center">
                        <i data-lucide="layers" class="w-12 h-12 text-slate-300 dark:text-slate-700 mx-auto mb-4"></i>
                        <h3 class="text-lg font-bold text-slate-500 dark:text-slate-400 mb-1"><?= __('no_investments') ?></h3>
                        <p class="text-slate-400 dark:text-slate-500 text-sm"><?= __('no_investments_desc') ?></p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php foreach ($investments as $inv): ?>
                            <div class="glass p-6 rounded-3xl shadow-sm hover:border-primary/30 transition-all flex flex-col justify-between">
                                <div class="flex items-center space-x-4 mb-4">
                                    <div class="w-10 h-10 bg-primary/10 text-primary dark:text-blue-400 rounded-xl flex items-center justify-center shrink-0">
                                        <i data-lucide="trending-up" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-900 dark:text-white truncate max-w-[180px]">
                                            <?= htmlspecialchars($inv['plan_name'] ?? 'Plan') ?>
                                        </h4>
                                        <span class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">+<?= $inv['daily_profit_percent'] ?>% / j</span>
                                    </div>
                                </div>
                                <div class="flex justify-between items-end border-t border-slate-100 dark:border-slate-800/80 pt-4 mt-2">
                                    <div>
                                        <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1"><?= __('total_profits') ?></p>
                                        <p class="text-base font-bold text-emerald-600 dark:text-emerald-400">
                                            <?= number_format($inv['total_profit'] ?? 0, 0) ?> <?= __('cfa') ?>
                                        </p>
                                    </div>
                                    <span class="flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recent Activity -->
            <div class="space-y-6">
                <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-6"><?= __('recent_activity') ?></h2>
                <div class="space-y-4">
                    <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-100 dark:border-slate-800/80 shadow-sm flex items-center space-x-4">
                        <div class="w-10 h-10 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-500 rounded-xl flex items-center justify-center shrink-0">
                            <i data-lucide="arrow-down-left" class="w-5 h-5"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-slate-800 dark:text-white truncate"><?= __('deposit_approved') ?></p>
                            <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                                <?= sprintf(__('hours_ago'), 2) ?>
                            </p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-100 dark:border-slate-800/80 shadow-sm flex items-center space-x-4">
                        <div class="w-10 h-10 bg-blue-50 dark:bg-blue-500/10 text-blue-500 rounded-xl flex items-center justify-center shrink-0">
                            <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-slate-800 dark:text-white truncate"><?= __('interest_paid') ?></p>
                            <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                                <?= sprintf(__('today_at'), '08:00') ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Activity Feed -->
        <div class="bg-slate-900 dark:bg-slate-900/60 rounded-3xl p-8 overflow-hidden relative shadow-xl border border-slate-800/80">
            <div class="absolute top-0 right-0 w-48 h-48 bg-primary/5 rounded-full blur-3xl pointer-events-none"></div>
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-primary/20 rounded-xl flex items-center justify-center">
                        <i data-lucide="activity" class="text-primary dark:text-blue-400 w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-white font-black tracking-tight"><?= __('live_activity') ?> ⚡</h3>
                        <p class="text-slate-500 text-[9px] font-black uppercase tracking-wider">
                            <?= __('real_time_transactions') ?>
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-2 bg-emerald-500/10 border border-emerald-500/20 px-3 py-1 rounded-full">
                    <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full animate-pulse"></span>
                    <span class="text-emerald-400 text-[10px] font-black uppercase tracking-wider">Live</span>
                </div>
            </div>

            <div id="activity-feed" class="space-y-4 h-64 overflow-hidden relative">
                <!-- Transactions injected dynamically by JS -->
                <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-slate-900 to-transparent pointer-events-none z-10"></div>
            </div>
        </div>
    </main>

    <!-- Mobile Bottom Navigation -->
    <nav class="lg:hidden fixed bottom-4 left-4 right-4 bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg border border-slate-200/50 dark:border-slate-800/65 shadow-xl rounded-2xl z-50 px-4 py-3">
        <div class="flex justify-between items-center">
            <a href="/dashboard" class="flex flex-col items-center space-y-1 text-primary dark:text-blue-400">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                <span class="text-[8px] font-black uppercase tracking-wider"><?= __('dashboard') ?></span>
            </a>
            <a href="/plans" class="flex flex-col items-center space-y-1 text-slate-400 dark:text-slate-500 hover:text-primary dark:hover:text-blue-400 transition-colors">
                <i data-lucide="trending-up" class="w-5 h-5"></i>
                <span class="text-[8px] font-black uppercase tracking-wider"><?= __('invest') ?></span>
            </a>
            <a href="/ads" class="flex flex-col items-center space-y-1 text-slate-400 dark:text-slate-500 hover:text-primary dark:hover:text-blue-400 transition-colors">
                <i data-lucide="play-circle" class="w-5 h-5"></i>
                <span class="text-[8px] font-black uppercase tracking-wider"><?= __('ads') ?></span>
            </a>
            <a href="/recharge" class="flex flex-col items-center space-y-1 text-slate-400 dark:text-slate-500 hover:text-primary dark:hover:text-blue-400 transition-colors">
                <i data-lucide="wallet" class="w-5 h-5"></i>
                <span class="text-[8px] font-black uppercase tracking-wider"><?= __('wallet') ?></span>
            </a>
            <a href="/profile" class="flex flex-col items-center space-y-1 text-slate-400 dark:text-slate-500 hover:text-primary dark:hover:text-blue-400 transition-colors">
                <i data-lucide="user" class="w-5 h-5"></i>
                <span class="text-[8px] font-black uppercase tracking-wider"><?= __('profile') ?></span>
            </a>
        </div>
    </nav>

    <!-- Style for animations of activity items -->
    <style>
        .activity-item {
            animation: slideUpFadeIn 0.5s ease-out forwards;
        }

        @keyframes slideUpFadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .activity-exit {
            animation: fadeOutDown 0.5s ease-in forwards;
        }

        @keyframes fadeOutDown {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(-20px);
            }
        }
    </style>

    <script>
        const activities = [
            { type: 'withdrawal', label: '<?= __('withdrawal_approved') ?>', icon: 'arrow-up-right', color: 'text-emerald-500', bg: 'bg-emerald-500/10' },
            { type: 'deposit', label: '<?= __('deposit_confirmed') ?>', icon: 'arrow-down-left', color: 'text-blue-500', bg: 'bg-blue-500/10' },
            { type: 'investment', label: '<?= __('new_investment') ?>', icon: 'trending-up', color: 'text-amber-500', bg: 'bg-amber-500/10' }
        ];

        const operators = [
            { name: 'Orange Money', color: 'text-orange-500' },
            { name: 'MTN MoMo', color: 'text-yellow-500' }
        ];

        function getRandomPhone() {
            const prefix = ['67', '68', '69', '65'];
            const start = prefix[Math.floor(Math.random() * prefix.length)] + Math.floor(Math.random() * 999).toString().padStart(3, '0');
            return start + '...';
        }

        function getRandomAmount(type) {
            if (type === 'withdrawal') return (Math.floor(Math.random() * 50) + 5) * 1000;
            if (type === 'deposit') return (Math.floor(Math.random() * 100) + 10) * 1000;
            return (Math.floor(Math.random() * 20) + 2) * 5000;
        }

        function addActivity() {
            const feed = document.getElementById('activity-feed');
            if(!feed) return;

            const rand = Math.random();
            let activity;
            if (rand < 0.7) activity = activities[0];
            else if (rand < 0.85) activity = activities[1];
            else activity = activities[2];

            const operator = operators[Math.floor(Math.random() * operators.length)];
            const phone = getRandomPhone();
            const amount = getRandomAmount(activity.type);
            const ref = 'REF' + Math.random().toString(36).substring(2, 8).toUpperCase();

            const item = document.createElement('div');
            item.className = 'activity-item bg-white/5 border border-white/10 p-4 rounded-2xl flex items-center justify-between hover:bg-white/10 transition-all';

            item.innerHTML = `
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 ${activity.bg} rounded-xl flex items-center justify-center shrink-0">
                        <i data-lucide="${activity.icon}" class="${activity.color} w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center space-x-2 flex-wrap">
                            <p class="text-white text-sm font-bold">${phone}</p>
                            <span class="text-[9px] ${activity.color} font-black uppercase tracking-wider">${activity.label}</span>
                        </div>
                        <p class="text-slate-500 text-[9px] font-bold uppercase tracking-wider">${ref} • <span class="${operator.color}">${operator.name}</span></p>
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-white font-extrabold text-sm">${amount.toLocaleString()} FCFA</p>
                    <p class="text-slate-500 text-[9px] font-bold uppercase tracking-wider"><?= __('just_now') ?></p>
                </div>
            `;

            feed.prepend(item);
            lucide.createIcons();

            if (feed.children.length > 7) {
                const last = feed.lastElementChild;
                if (last) {
                    last.classList.add('activity-exit');
                    setTimeout(() => last.remove(), 500);
                }
            }

            const nextInterval = Math.random() < 0.3 ? 800 + Math.random() * 2000 : 3000 + Math.random() * 25000;
            setTimeout(addActivity, nextInterval);
        }

        // Start the activity feed
        setTimeout(addActivity, 1500);

        // Auto-scroll logic for notification targets
        window.addEventListener('load', () => {
            const errorMsg = document.getElementById('error-msg');
            const successMsg = document.getElementById('success-msg');
            const msg = errorMsg || successMsg;
            if (msg) {
                msg.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    </script>
    <script>
        lucide.createIcons();
    </script>
    <?php include __DIR__ . '/../partials/whatsapp.php'; ?>
</body>

</html>