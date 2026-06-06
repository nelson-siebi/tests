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
        </div>
    </nav>

    <main class="max-w-6xl mx-auto p-6 md:p-12 space-y-12">
        <div class="mb-12 animate-fade-in">
            <h1 class="text-4xl font-black text-slate-900 dark:text-white tracking-tight mb-2"><?= __('profile_settings') ?> ⚙️</h1>
            <p class="text-slate-500 dark:text-slate-400 font-bold italic text-base"><?= __('account_info') ?></p>
        </div>

        <?php if (\App\Core\Session::has('error')): ?>
            <div class="bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 p-6 rounded-[2rem] animate-fade-in flex items-center space-x-3">
                <i data-lucide="alert-circle" class="w-6 h-6 shrink-0 text-red-500"></i>
                <p class="font-black"><?= \App\Core\Session::get('error'); \App\Core\Session::remove('error'); ?></p>
            </div>
        <?php endif; ?>

        <?php if (\App\Core\Session::has('success')): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 p-6 rounded-[2rem] animate-fade-in flex items-center space-x-3">
                <i data-lucide="check-circle" class="w-6 h-6 shrink-0 text-emerald-500"></i>
                <p class="font-black"><?= \App\Core\Session::get('success'); \App\Core\Session::remove('success'); ?></p>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            <!-- Sidebar-like User Card -->
            <div class="lg:col-span-4 space-y-6 shrink-0">
                <div class="bg-white dark:bg-slate-900 p-10 rounded-[3rem] border border-slate-200/50 dark:border-slate-800/80 shadow-xl text-center animate-fade-in"
                    style="animation-delay: 0.1s">
                    <div class="w-24 h-24 bg-primary/10 dark:bg-primary/20 text-primary dark:text-blue-400 rounded-[2rem] flex items-center justify-center mx-auto mb-6 shrink-0">
                        <i data-lucide="user" class="w-12 h-12"></i>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white mb-1 truncate"><?= htmlspecialchars($user['name']) ?></h3>
                    <p class="text-slate-400 dark:text-slate-500 font-bold text-sm mb-8 italic truncate"><?= htmlspecialchars($user['email']) ?></p>

                    <div class="bg-slate-50 dark:bg-slate-950 p-6 rounded-3xl border border-slate-100 dark:border-slate-850/80 text-left">
                        <span class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5"><?= __('balance') ?></span>
                        <span class="text-2xl font-black text-slate-900 dark:text-white">
                            <?= number_format($user['balance'], 0, '.', ' ') ?> <span class="text-sm font-bold text-slate-400 dark:text-slate-500">XAF</span>
                        </span>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-2 gap-4 animate-fade-in" style="animation-delay: 0.2s">
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200/50 dark:border-slate-800/80 shadow-lg">
                        <i data-lucide="trending-up" class="w-8 h-8 text-primary dark:text-blue-400 mb-3"></i>
                        <p class="text-[9px] font-black text-slate-450 dark:text-slate-500 uppercase tracking-widest mb-1 leading-tight">
                            <?= __('active_investments_count') ?>
                        </p>
                        <p class="text-2xl font-black text-slate-900 dark:text-white"><?= count($investments) ?></p>
                    </div>
                    <a href="/withdraw"
                        class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200/50 dark:border-slate-800/80 shadow-lg group hover:border-primary transition-all">
                        <i data-lucide="wallet" class="w-8 h-8 text-emerald-500 mb-3 group-hover:scale-105 transition-transform"></i>
                        <p class="text-[9px] font-black text-slate-450 dark:text-slate-500 uppercase tracking-widest mb-1 leading-tight">
                            <?= __('withdrawal') ?>
                        </p>
                        <p class="text-xl font-black text-slate-900 dark:text-white truncate"><?= __('min_1k') ?></p>
                    </a>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="lg:col-span-8 space-y-10">
                <!-- Active Investments section -->
                <div class="animate-fade-in" style="animation-delay: 0.3s">
                    <h2 class="text-xl font-black text-slate-900 dark:text-white mb-6 flex items-center space-x-2">
                        <i data-lucide="layout-grid" class="w-6 h-6 text-primary dark:text-blue-400"></i>
                        <span><?= __('my_active_investments') ?></span>
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php if (empty($investments)): ?>
                            <div class="col-span-full bg-white/40 dark:bg-slate-900/40 p-12 rounded-[2.5rem] border border-dashed border-slate-200 dark:border-slate-800 text-center">
                                <i data-lucide="package-open" class="w-16 h-16 text-slate-200 dark:text-slate-700 mx-auto mb-4"></i>
                                <p class="text-slate-400 dark:text-slate-500 font-bold italic"><?= __('no_investments_yet') ?></p>
                                <a href="/plans"
                                    class="inline-block mt-4 text-primary dark:text-blue-400 font-black uppercase text-[10px] tracking-widest border-b-2 border-primary/20 hover:border-primary transition-all"><?= __('discover_plans') ?></a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($investments as $inv): ?>
                                <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-200/50 dark:border-slate-800/80 shadow-xl relative overflow-hidden group">
                                    <div class="absolute top-0 right-0 w-24 h-24 bg-primary/5 rounded-bl-[4rem] -mr-8 -mt-8 group-hover:bg-primary/10 transition-colors"></div>
                                    <div class="relative space-y-4">
                                        <h4 class="text-lg font-black text-slate-900 dark:text-white truncate">
                                            <?= htmlspecialchars($inv['plan_name']) ?>
                                        </h4>
                                        <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                                            <?= __('invested') ?> : <?= number_format($inv['amount'], 0, '.', ' ') ?> <?= __('cfa') ?>
                                        </p>

                                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-100 dark:border-slate-800/80">
                                            <div>
                                                <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase leading-none mb-1">
                                                    <?= __('total_profit_label') ?>
                                                </p>
                                                <p class="text-base font-black text-emerald-500 dark:text-emerald-450">
                                                    +<?= number_format($inv['total_profit'], 0, '.', ' ') ?> <span class="text-[10px]">XAF</span>
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase leading-none mb-1">
                                                    <?= __('played_ads') ?>
                                                </p>
                                                <p class="text-base font-black text-slate-900 dark:text-white">
                                                    <?= $inv['ads_watched'] ?? 0 ?> / <?= $inv['ads_per_day'] ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Update Info Form -->
                <div class="bg-white dark:bg-slate-900 p-8 md:p-12 rounded-[3rem] border border-slate-200/50 dark:border-slate-800/80 shadow-2xl animate-fade-in"
                    style="animation-delay: 0.4s">
                    <h2 class="text-xl font-black text-slate-900 dark:text-white mb-8 flex items-center space-x-2">
                        <i data-lucide="settings" class="w-6 h-6 text-primary dark:text-blue-400"></i>
                        <span><?= __('edit_info') ?></span>
                    </h2>

                    <form action="/profile" method="POST" class="space-y-6">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest px-1">
                                Langue / Language
                            </label>
                            <div class="grid grid-cols-2 gap-4">
                                <a href="/lang?l=fr"
                                    class="block text-center py-4 rounded-2xl border-2 transition-all font-black text-xs uppercase tracking-wider <?= \App\Core\Language::getCurrent() === 'fr' ? 'border-primary bg-primary/5 text-primary dark:border-primary dark:bg-primary/10 dark:text-blue-400' : 'border-slate-100 dark:border-slate-800 text-slate-400 dark:text-slate-500 hover:border-slate-200' ?>">
                                    Français
                                </a>
                                <a href="/lang?l=en"
                                    class="block text-center py-4 rounded-2xl border-2 transition-all font-black text-xs uppercase tracking-wider <?= \App\Core\Language::getCurrent() === 'en' ? 'border-primary bg-primary/5 text-primary dark:border-primary dark:bg-primary/10 dark:text-blue-400' : 'border-slate-100 dark:border-slate-800 text-slate-400 dark:text-slate-500 hover:border-slate-200' ?>">
                                    English
                                </a>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest px-1">
                                <?= __('full_name') ?>
                            </label>
                            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl px-6 py-4 text-slate-900 dark:text-white font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm">
                        </div>

                        <div class="pt-8 border-t border-slate-100 dark:border-slate-800/80">
                            <div class="bg-blue-500/5 p-6 rounded-3xl border border-blue-100/30 dark:border-blue-900/10 mb-6">
                                <h4 class="text-sm font-black text-slate-900 dark:text-white mb-1.5"><?= __('password_label') ?></h4>
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 font-bold leading-relaxed italic">
                                    <?= __('new_password_info') ?>
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest px-1">
                                        <?= __('new_password') ?>
                                    </label>
                                    <input type="password" name="password" placeholder="••••••••"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl px-6 py-4 text-slate-900 dark:text-white font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm">
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest px-1">
                                        <?= __('confirm_password') ?>
                                    </label>
                                    <input type="password" name="confirm_password" placeholder="••••••••"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl px-6 py-4 text-slate-900 dark:text-white font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm">
                                </div>
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full bg-slate-900 dark:bg-slate-950 hover:bg-slate-800 dark:hover:bg-slate-900 border border-slate-800/80 dark:border-slate-800 text-white font-black py-4.5 rounded-2xl transition-all shadow-xl active:scale-[0.98] uppercase tracking-wider text-xs">
                            <?= __('save_changes') ?>
                        </button>
                    </form>
                </div>
            </div>
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
            <a href="/plans"
                class="flex flex-col items-center space-y-1 text-slate-400 dark:text-slate-500 hover:text-primary dark:hover:text-blue-400 transition-colors">
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
        </div>
    </nav>

    <script>lucide.createIcons();</script>
    <?php include __DIR__ . '/../partials/whatsapp.php'; ?>
</body>

</html>