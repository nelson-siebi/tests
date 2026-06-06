<!DOCTYPE html>
<html lang="fr" class="scroll-smooth" x-data="{ darkMode: false }" :class="{ 'dark': darkMode }">

<head>
    <?php $title = $title ?? __('recharge_title'); ?>
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

        window.addEventListener('load', () => {
            const errorMsg = document.getElementById('error-msg');
            const successMsg = document.getElementById('success-msg');
            const msg = errorMsg || successMsg;
            if (msg) {
                msg.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
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

        <div class="hidden lg:flex items-center space-x-8">
            <a href="/dashboard" class="text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-blue-400 font-bold transition-all"><?= __('dashboard') ?></a>
            <a href="/plans" class="text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-blue-400 font-bold transition-all"><?= __('invest') ?></a>
            <a href="/ads" class="text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-blue-400 font-bold transition-all"><?= __('ads') ?></a>
            <a href="/recharge" class="text-primary dark:text-blue-400 font-bold transition-all"><?= __('wallet') ?></a>
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

    <main class="max-w-7xl mx-auto p-6 md:p-10 space-y-12">
        <div class="text-center max-w-2xl mx-auto space-y-4 animate-fade-in">
            <h1 class="text-4xl font-black text-slate-900 dark:text-white tracking-tight"><?= __('recharge_title') ?> 💳</h1>
            <p class="text-slate-500 dark:text-slate-400 font-bold italic text-base"><?= __('recharge_desc') ?></p>
            <a href="/guide"
                class="inline-flex items-center space-x-2 text-primary dark:text-blue-400 font-black text-xs uppercase tracking-wider hover:underline">
                <i data-lucide="help-circle" class="w-4.5 h-4.5"></i>
                <span><?= __('need_guide') ?></span>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- Recharge Form Container -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-slate-900 p-8 md:p-12 rounded-[2.5rem] shadow-xl border border-slate-200/50 dark:border-slate-800/80 space-y-10 animate-fade-in">
                    <?php if (isset($_GET['success'])): ?>
                        <div id="success-msg"
                            class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 p-6 rounded-2xl font-bold flex items-center space-x-3">
                            <i data-lucide="check-circle" class="w-6 h-6 shrink-0"></i>
                            <span><?= __('payment_confirmed') ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Step 1: Amount -->
                    <div class="space-y-4">
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest px-1">
                            <?= __('step_1') ?> : <?= __('amount_to_recharge') ?>
                        </label>
                        <input type="hidden" id="rechargeAmount" name="amount" required>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <?php
                            $amounts = [4000, 6000, 10000, 20000, 50000, 100000, 200000, 500000];
                            foreach ($amounts as $amt):
                            ?>
                                <button type="button" onclick="selectAmount(<?= $amt ?>, this)"
                                    class="amount-btn bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800/80 text-slate-700 dark:text-slate-300 font-extrabold py-3.5 rounded-2xl hover:border-primary dark:hover:border-primary transition-all active:scale-[0.98] text-sm">
                                    <?= number_format($amt, 0, '.', ' ') ?> <span class="text-[9px] font-black uppercase text-slate-400">XAF</span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Step 2: Operator -->
                    <div class="space-y-4">
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest px-1">
                            <?= __('step_2') ?> : <?= __('select_operator') ?>
                        </label>
                        <div class="grid grid-cols-2 gap-4">
                            <button onclick="selectOperator('orange')" id="btn-orange"
                                class="operator-btn flex flex-col items-center p-6 bg-slate-50 dark:bg-slate-950 border border-slate-200/85 dark:border-slate-800/80 rounded-3xl hover:border-orange-500 transition-all active:scale-[0.98]">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/c/c8/Orange_logo.svg"
                                    class="h-10 w-10 mb-3" alt="Orange">
                                <span class="text-xs font-black text-slate-700 dark:text-slate-300"><?= __('orange_money') ?></span>
                            </button>
                            <button onclick="selectOperator('mtn')" id="btn-mtn"
                                class="operator-btn flex flex-col items-center p-6 bg-slate-50 dark:bg-slate-950 border border-slate-200/85 dark:border-slate-800/80 rounded-3xl hover:border-yellow-500 transition-all active:scale-[0.98]">
                                <div class="h-10 w-10 mb-3 bg-yellow-400 text-slate-950 rounded-xl flex items-center justify-center font-black text-xs shadow-md">
                                    MTN
                                </div>
                                <span class="text-xs font-black text-slate-700 dark:text-slate-300"><?= __('mtn_money') ?></span>
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: USSD Instructions (Hidden initially) -->
                    <div id="ussdSection" class="hidden space-y-6 pt-4 border-t border-slate-100 dark:border-slate-800/60">
                        <div class="bg-slate-950 text-white p-8 rounded-[2rem] shadow-xl space-y-6 border border-slate-800/80">
                            <div class="flex justify-between items-center flex-wrap gap-2">
                                <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">
                                    <?= __('step_by_step') ?>
                                </h3>
                                <span class="bg-primary/20 text-primary dark:text-blue-400 text-[10px] font-black uppercase tracking-wider px-3 py-1 rounded-full">
                                    <?= __('payment_instructions') ?>
                                </span>
                            </div>

                            <div class="bg-white/5 border border-white/10 p-5 rounded-2xl text-center space-y-4">
                                <p class="text-xs text-slate-400 font-bold"><?= __('copy_code') ?></p>
                                <p id="ussdDisplay" class="text-2xl font-mono font-black tracking-tight text-white select-all py-1 break-all"></p>
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <button type="button" onclick="copyUSSD(event)"
                                        class="flex-1 bg-white/10 hover:bg-white/20 text-white py-4 rounded-xl text-xs font-black uppercase tracking-wider transition-all flex items-center justify-center space-x-2">
                                        <i data-lucide="copy" class="w-4.5 h-4.5"></i>
                                        <span><?= __('copy') ?></span>
                                    </button>
                                    <a id="ussdCall" href="#"
                                        class="flex-1 bg-primary hover:bg-blue-600 text-white py-4 rounded-xl text-xs font-black uppercase tracking-wider transition-all flex items-center justify-center space-x-2 shadow-lg shadow-primary/20">
                                        <i data-lucide="phone" class="w-4.5 h-4.5"></i>
                                        <span><?= __('launch_ussd') ?></span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <button type="button" id="showReferenceBtn" onclick="showReferenceStep()"
                            class="w-full bg-slate-100 hover:bg-slate-200 dark:bg-slate-950 hover:dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-350 font-black py-5.5 rounded-2xl transition-all uppercase tracking-wider text-sm shadow-md">
                            <?= __('confirm_payment_step') ?>
                        </button>

                        <div id="referenceStep" class="hidden space-y-6 pt-6 border-t border-slate-100 dark:border-slate-800/60">
                            <form action="/recharge" method="POST" class="space-y-6">
                                <input type="hidden" name="amount" id="formAmount">
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest px-1">
                                        <?= __('transaction_reference') ?>
                                    </label>
                                    <input type="text" name="reference" required
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl px-6 py-4.5 text-slate-900 dark:text-white font-extrabold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm"
                                        placeholder="Ex: TX-987654321">
                                </div>
                                <button type="submit"
                                    class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-black py-5 rounded-2xl transition-all shadow-lg shadow-emerald-500/20 active:scale-95 flex items-center justify-center space-x-3 uppercase tracking-wider text-xs">
                                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                                    <span><?= __('payment_done_button') ?></span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recharge History Sidebar -->
            <div class="space-y-6 animate-fade-in">
                <div class="flex items-center space-x-3 px-2">
                    <div class="w-9 h-9 bg-slate-150 dark:bg-slate-900 rounded-xl flex items-center justify-center text-slate-400 dark:text-slate-500 shrink-0">
                        <i data-lucide="history" class="w-5 h-5"></i>
                    </div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white"><?= __('recent_history') ?></h2>
                </div>

                <div class="space-y-4">
                    <?php $transactions = $transactions ?? []; ?>
                    <?php foreach ($transactions as $tx): ?>
                        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/50 dark:border-slate-800/80 shadow-md flex justify-between items-center group hover:border-primary/20 transition-all">
                            <div>
                                <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">
                                    <?= date('d M, H:i', strtotime($tx['created_at'])) ?>
                                </p>
                                <h4 class="text-lg font-black text-slate-800 dark:text-white">
                                    <?= number_format($tx['amount'], 0, '.', ' ') ?> <span class="text-xs text-slate-400"><?= __('cfa') ?></span>
                                </h4>
                            </div>
                            <?php if ($tx['status'] === 'completed'): ?>
                                <div class="bg-emerald-500/10 text-emerald-500 p-2.5 rounded-xl border border-emerald-500/10 shrink-0">
                                    <i data-lucide="check" class="w-4.5 h-4.5"></i>
                                </div>
                            <?php else: ?>
                                <div class="bg-amber-500/10 text-amber-500 p-2.5 rounded-xl border border-amber-500/10 shrink-0 animate-pulse">
                                    <i data-lucide="clock" class="w-4.5 h-4.5"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($transactions)): ?>
                        <div class="text-center py-16 bg-white/40 dark:bg-slate-900/40 rounded-3xl border border-dashed border-slate-200 dark:border-slate-800">
                            <i data-lucide="inbox" class="w-10 h-10 text-slate-300 dark:text-slate-700 mx-auto mb-3"></i>
                            <p class="text-slate-400 dark:text-slate-500 text-sm font-semibold italic"><?= __('no_deposits') ?></p>
                        </div>
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
            <a href="/recharge" class="flex flex-col items-center space-y-1 text-primary dark:text-blue-400">
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
        let selectedOperator = null;
        const ussdConfigs = {
            orange: {
                base: '#150*1*1*656720564*',
                suffix: '#',
                call: 'tel:%23150*1*1*656720564*AMOUNT%23'
            },
            mtn: {
                base: '*126*9*682004136*',
                suffix: '#',
                call: 'tel:*126*9*682004136*AMOUNT%23'
            }
        };

        function selectAmount(amount, btn) {
            document.getElementById('rechargeAmount').value = amount;
            document.getElementById('formAmount').value = amount;
            document.getElementById('ussdSection').classList.add('hidden');
            document.getElementById('referenceStep').classList.add('hidden');

            document.querySelectorAll('.amount-btn').forEach(b => {
                b.classList.remove('border-primary', 'bg-primary/5', 'text-primary', 'dark:border-primary', 'dark:bg-primary/10', 'dark:text-blue-400');
                b.classList.add('border-slate-200/80', 'bg-slate-50', 'text-slate-700', 'dark:border-slate-800/80', 'dark:bg-slate-950', 'dark:text-slate-300');
            });

            btn.classList.remove('border-slate-200/80', 'bg-slate-50', 'text-slate-700', 'dark:border-slate-800/80', 'dark:bg-slate-950', 'dark:text-slate-300');
            btn.classList.add('border-primary', 'bg-primary/5', 'text-primary', 'dark:border-primary', 'dark:bg-primary/10', 'dark:text-blue-400');
        }

        function selectOperator(op) {
            const amount = document.getElementById('rechargeAmount').value;
            if (!amount) {
                alert('<?= __('select_amount_alert') ?>');
                return;
            }

            selectedOperator = op;
            document.querySelectorAll('.operator-btn').forEach(b => b.classList.remove('border-primary', 'dark:border-primary', 'bg-primary/5', 'dark:bg-primary/10', 'shadow-md'));
            document.getElementById(`btn-${op}`).classList.add('border-primary', 'dark:border-primary', 'bg-primary/5', 'dark:bg-primary/10', 'shadow-md');
            document.getElementById('referenceStep').classList.add('hidden');

            updateUSSDFlow(op, amount);
        }

        function updateUSSDFlow(op, amount) {
            const config = ussdConfigs[op];
            const fullCode = config.base + amount + config.suffix;
            const callUrl = config.call.replace('AMOUNT', amount);

            document.getElementById('ussdDisplay').innerText = fullCode;
            document.getElementById('ussdCall').href = callUrl;
            document.getElementById('formAmount').value = amount;

            const section = document.getElementById('ussdSection');
            section.classList.remove('hidden');
            section.scrollIntoView({ behavior: 'smooth' });
        }

        function showReferenceStep() {
            const referenceSection = document.getElementById('referenceStep');
            referenceSection.classList.remove('hidden');
            referenceSection.scrollIntoView({ behavior: 'smooth' });
        }

        function copyUSSD(event) {
            const code = document.getElementById('ussdDisplay').innerText;
            navigator.clipboard.writeText(code).then(() => {
                const btn = event.currentTarget.querySelector('span');
                const oldText = btn.innerText;
                btn.innerText = '<?= __('copied_success') ?>';
                setTimeout(() => btn.innerText = oldText, 2000);
            });
        }

        lucide.createIcons();
    </script>
    <?php include __DIR__ . '/../partials/whatsapp.php'; ?>
</body>

</html>