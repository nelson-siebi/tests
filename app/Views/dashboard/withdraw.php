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
        <div class="max-w-2xl mx-auto text-center space-y-4 animate-fade-in">
            <h1 class="text-4xl font-black text-slate-900 dark:text-white tracking-tight"><?= $title ?></h1>
            <p class="text-slate-500 dark:text-slate-400 font-bold italic text-base"><?= __('withdraw_desc') ?></p>
            <a href="/guide"
                class="inline-flex items-center space-x-2 text-primary dark:text-blue-400 font-black text-xs uppercase tracking-wider hover:underline">
                <i data-lucide="help-circle" class="w-4.5 h-4.5"></i>
                <span><?= __('need_guide') ?></span>
            </a>
        </div>

        <?php if (\App\Core\Session::has('success')): ?>
            <div class="max-w-xl mx-auto bg-emerald-55 dark:bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 p-6 rounded-3xl font-bold mb-8 animate-fade-in flex items-center space-x-3">
                <i data-lucide="check-circle" class="w-6 h-6 shrink-0 text-emerald-500"></i>
                <span><?= \App\Core\Session::get('success'); \App\Core\Session::remove('success'); ?></span>
            </div>
        <?php endif; ?>

        <?php if (\App\Core\Session::has('error')): ?>
            <div class="max-w-xl mx-auto bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 p-6 rounded-3xl font-bold mb-8 animate-fade-in flex items-center space-x-3">
                <i data-lucide="alert-circle" class="w-6 h-6 shrink-0 text-red-500"></i>
                <span><?= \App\Core\Session::get('error'); \App\Core\Session::remove('error'); ?></span>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            <!-- Withdrawal Form -->
            <div class="bg-white dark:bg-slate-900 p-8 md:p-10 rounded-[2.5rem] shadow-xl border border-slate-200/50 dark:border-slate-800/80 h-fit space-y-8 animate-fade-in">
                <div class="bg-slate-50 dark:bg-slate-950 p-6 rounded-3xl border border-slate-100 dark:border-slate-850/80">
                    <p class="text-slate-400 dark:text-slate-500 text-[10px] font-black uppercase tracking-widest mb-1.5">
                        <?= __('available_balance') ?>
                    </p>
                    <p class="text-3xl font-black text-slate-900 dark:text-white">
                        <?= number_format($user['balance'], 0, '.', ' ') ?> <span class="text-sm font-bold text-slate-400 dark:text-slate-500"><?= __('cfa') ?></span>
                    </p>
                </div>

                <form action="/withdraw" method="POST" class="space-y-6">
                    <div class="space-y-2">
                        <label class="block text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest px-1">
                            <?= __('amount_to_withdraw') ?>
                        </label>
                        <input type="number" name="amount" min="1000" max="<?= $user['balance'] ?>" required
                            placeholder="0"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl px-6 py-4 text-slate-900 dark:text-white font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm placeholder-slate-400 dark:placeholder-slate-700">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest px-1">
                            <?= __('operator') ?>
                        </label>
                        <select name="operator" required
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl px-6 py-4 text-slate-900 dark:text-white font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm">
                            <option value="Orange Money">Orange Money</option>
                            <option value="MTN Mobile Money">MTN Mobile Money</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest px-1">
                            <?= __('phone_number') ?>
                        </label>
                        <input type="text" name="phone_number" required placeholder="691234567"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl px-6 py-4 text-slate-900 dark:text-white font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm placeholder-slate-400 dark:placeholder-slate-700">
                    </div>

                    <button type="submit"
                        class="w-full bg-primary hover:bg-blue-600 text-white font-black py-4.5 rounded-2xl transition-all shadow-lg shadow-primary/20 hover:shadow-primary/30 active:scale-[0.98] mt-4 uppercase tracking-wider text-xs">
                        <?= __('confirm_withdrawal') ?>
                    </button>
                </form>
            </div>

            <!-- Withdrawal History List -->
            <div class="space-y-6 animate-fade-in">
                <h2 class="text-xl font-black text-slate-900 dark:text-white flex items-center space-x-2 px-2">
                    <i data-lucide="history" class="w-6 h-6 text-primary dark:text-blue-400"></i>
                    <span><?= __('withdrawal_history') ?></span>
                </h2>

                <div class="space-y-4">
                    <?php if (empty($withdrawals)): ?>
                        <div class="bg-white/40 dark:bg-slate-900/40 p-16 rounded-[2.5rem] border border-dashed border-slate-200 dark:border-slate-800 text-center">
                            <i data-lucide="inbox" class="w-12 h-12 text-slate-300 dark:text-slate-700 mx-auto mb-4"></i>
                            <p class="text-slate-400 dark:text-slate-500 font-bold italic"><?= __('no_withdrawals') ?></p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($withdrawals as $tx): ?>
                            <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/50 dark:border-slate-800/80 shadow-md flex justify-between items-center group hover:border-primary/20 transition-all">
                                <div class="min-w-0 pr-4">
                                    <p class="text-base font-black text-slate-900 dark:text-white">
                                        <?= number_format($tx['amount'], 0, '.', ' ') ?> <span class="text-xs text-slate-450 dark:text-slate-500"><?= __('cfa') ?></span>
                                    </p>
                                    <p class="text-[9px] text-slate-400 dark:text-slate-500 font-black uppercase tracking-wider mt-0.5">
                                        <?= date('d M Y, H:i', strtotime($tx['created_at'])) ?>
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 font-bold italic truncate max-w-[200px] mt-1.5">
                                        <?= htmlspecialchars($tx['description']) ?>
                                    </p>
                                </div>
                                <div class="shrink-0">
                                    <span class="px-3.5 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest
                                        <?= $tx['status'] === 'completed' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/10' :
                                            ($tx['status'] === 'pending' ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/10 animate-pulse' : 'bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/10') ?>">
                                        <?php
                                        switch ($tx['status']) {
                                            case 'completed':
                                                echo __('status_completed');
                                                break;
                                            case 'pending':
                                                echo __('status_pending');
                                                break;
                                            case 'rejected':
                                                echo __('status_rejected');
                                                break;
                                            default:
                                                echo htmlspecialchars($tx['status']);
                                                break;
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
            <a href="/profile"
                class="flex flex-col items-center space-y-1 text-slate-400 dark:text-slate-500 hover:text-primary dark:hover:text-blue-400 transition-colors">
                <i data-lucide="user" class="w-5 h-5"></i>
                <span class="text-[8px] font-black uppercase tracking-wider"><?= __('profile') ?></span>
            </a>
        </div>
    </nav>

    <script>lucide.createIcons();</script>
    <?php include __DIR__ . '/../partials/whatsapp.php'; ?>
</body>

</html>