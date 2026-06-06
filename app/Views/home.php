<!DOCTYPE html>
<html lang="fr" class="scroll-smooth" x-data="{ darkMode: false }" :class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investian - <?= __('home_title') ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js for modern reactive interface -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

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
                        secondary: {
                            DEFAULT: '#10b981',
                            hover: '#059669',
                            glow: 'rgba(16, 185, 129, 0.15)',
                        },
                        slate: {
                            950: '#030712',
                        }
                    }
                }
            }
        }
    </script>

    <script>
        // Synchronize initial dark mode status with HTML tag
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            window.isDark = true;
        } else {
            document.documentElement.classList.remove('dark');
            window.isDark = false;
        }
    </script>

    <style>
        /* Smooth animations & custom designs */
        .theme-transition,
        .theme-transition * {
            transition: background-color 0.3s ease, border-color 0.3s ease, text-decoration-color 0.3s ease, box-shadow 0.3s ease;
        }

        /* Dotted grid pattern */
        .bg-grid {
            background-size: 30px 30px;
            background-image: radial-gradient(circle, rgba(148, 163, 184, 0.08) 1px, transparent 1.5px);
        }
        .dark .bg-grid {
            background-image: radial-gradient(circle, rgba(51, 65, 85, 0.15) 1px, transparent 1.5px);
        }

        /* Endless horizontal scrolling for partners logo ticker */
        @keyframes marquee {
            0% { transform: translateX(0%); }
            100% { transform: translateX(-50%); }
        }
        .animate-marquee {
            animation: marquee 25s linear infinite;
        }

        /* Float effects */
        @keyframes float-1 {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-12px) rotate(2deg); }
        }
        @keyframes float-2 {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(12px) rotate(-2deg); }
        }
        .animate-float-1 {
            animation: float-1 8s ease-in-out infinite;
        }
        .animate-float-2 {
            animation: float-2 10s ease-in-out infinite;
        }
    </style>
</head>

<body 
    x-data="{ 
        darkMode: window.isDark,
        mobileMenuOpen: false,
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
    class="theme-transition bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 font-sans antialiased overflow-x-hidden min-h-screen relative"
>

    <!-- Background Grid overlay -->
    <div class="absolute inset-0 bg-grid pointer-events-none -z-30"></div>

    <!-- Glowing Background Blob elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none -z-20">
        <div class="absolute top-[-5%] left-[-5%] w-[60vw] h-[60vw] max-w-[800px] bg-primary/10 dark:bg-primary/5 rounded-full blur-[130px] animate-pulse"></div>
        <div class="absolute bottom-[10%] right-[-5%] w-[50vw] h-[50vw] max-w-[700px] bg-secondary/10 dark:bg-secondary/5 rounded-full blur-[130px] animate-pulse" style="animation-delay: 2.5s"></div>
    </div>

    <!-- 1. Floating Nav Bar -->
    <nav class="fixed top-6 left-1/2 -translate-x-1/2 w-[92%] max-w-7xl z-50 bg-white/70 dark:bg-slate-900/70 backdrop-blur-lg border border-slate-200/50 dark:border-slate-800/80 rounded-2xl shadow-lg shadow-slate-100/40 dark:shadow-none py-4 px-6 flex justify-between items-center transition-all duration-300">
        <!-- Logo -->
        <div class="flex items-center space-x-3">
            <a href="/" class="hover:opacity-90 transition-all flex items-center gap-2">
                <img src="/images/logo.png" alt="Investian Logo" class="h-10 w-auto">
            </a>
        </div>

        <!-- Desktop Menu Links -->
        <div class="hidden md:flex items-center space-x-8">
            <a href="/" class="text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-primary font-semibold transition-all"><?= __('home') ?></a>
            <a href="#plans" class="text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-primary font-semibold transition-all"><?= __('investment_plans_title') ?></a>
            <a href="#calculator" class="text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-primary font-semibold transition-all">Calculateur</a>
            <a href="#features" class="text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-primary font-semibold transition-all"><?= __('explore_features') ?></a>
            <a href="#faq" class="text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-primary font-semibold transition-all">FAQ</a>
            <a href="#contact" class="text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-primary font-semibold transition-all"><?= __('contact_title') ?></a>
        </div>

        <!-- Desktop Right Menu (Theme, Language, Auth) -->
        <div class="hidden md:flex items-center space-x-4">
            <!-- Theme Toggle Button -->
            <button @click="toggleTheme()" class="text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl p-2.5 transition-all focus:outline-none" aria-label="Toggle theme">
                <i data-lucide="sun" x-show="darkMode" class="w-5 h-5"></i>
                <i data-lucide="moon" x-show="!darkMode" class="w-5 h-5"></i>
            </button>

            <!-- Language Switcher -->
            <div class="flex bg-slate-100 dark:bg-slate-800/80 p-1 rounded-xl border border-slate-200/20">
                <a href="/lang?l=en" 
                   class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-widest transition-all"
                   :class="{'bg-white dark:bg-slate-900 text-primary shadow-sm': '<?= \App\Core\Language::getCurrent() ?>' === 'en', 'text-slate-400 dark:text-slate-500 hover:text-slate-600': '<?= \App\Core\Language::getCurrent() ?>' !== 'en'}"
                >EN</a>
                <a href="/lang?l=fr" 
                   class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-widest transition-all"
                   :class="{'bg-white dark:bg-slate-900 text-primary shadow-sm': '<?= \App\Core\Language::getCurrent() ?>' === 'fr', 'text-slate-400 dark:text-slate-500 hover:text-slate-600': '<?= \App\Core\Language::getCurrent() ?>' !== 'fr'}"
                >FR</a>
            </div>

            <!-- Auth Buttons -->
            <?php if (\App\Core\Session::has('user_id')): ?>
                <a href="/dashboard" class="bg-primary hover:bg-primary-hover text-white font-bold px-6 py-2.5 rounded-xl transition-all shadow-md shadow-primary/25 text-sm">
                    <?= __('dashboard') ?>
                </a>
            <?php else: ?>
                <a href="/login" class="text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-primary font-semibold text-sm transition-all px-3 py-2"><?= __('login') ?></a>
                <a href="/register" class="bg-gradient-to-r from-primary to-blue-600 hover:opacity-95 text-white font-bold px-6 py-2.5 rounded-xl transition-all shadow-md shadow-primary/25 text-sm">
                    <?= __('get_started') ?>
                </a>
            <?php endif; ?>
        </div>

        <!-- Mobile Controls -->
        <div class="flex items-center space-x-2 md:hidden">
            <button @click="toggleTheme()" class="text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl p-2 transition-all focus:outline-none" aria-label="Toggle theme">
                <i data-lucide="sun" x-show="darkMode" class="w-5 h-5"></i>
                <i data-lucide="moon" x-show="!darkMode" class="w-5 h-5"></i>
            </button>
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl p-2 transition-all focus:outline-none" aria-label="Open menu">
                <i data-lucide="menu" x-show="!mobileMenuOpen" class="w-6 h-6"></i>
                <i data-lucide="x" x-show="mobileMenuOpen" class="w-6 h-6" x-cloak></i>
            </button>
        </div>
    </nav>

    <!-- Mobile Dropdown Menu -->
    <div 
        x-show="mobileMenuOpen" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-4"
        class="fixed top-28 left-[4%] w-[92%] z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-lg border border-slate-200/50 dark:border-slate-800/80 rounded-2xl shadow-xl p-6 md:hidden"
        x-cloak
    >
        <div class="flex flex-col space-y-4">
            <a @click="mobileMenuOpen = false" href="/" class="text-slate-600 dark:text-slate-300 hover:text-primary font-semibold text-lg py-2 transition-all"><?= __('home') ?></a>
            <a @click="mobileMenuOpen = false" href="#plans" class="text-slate-600 dark:text-slate-300 hover:text-primary font-semibold text-lg py-2 transition-all"><?= __('investment_plans_title') ?></a>
            <a @click="mobileMenuOpen = false" href="#calculator" class="text-slate-600 dark:text-slate-300 hover:text-primary font-semibold text-lg py-2 transition-all">Calculateur</a>
            <a @click="mobileMenuOpen = false" href="#features" class="text-slate-600 dark:text-slate-300 hover:text-primary font-semibold text-lg py-2 transition-all"><?= __('explore_features') ?></a>
            <a @click="mobileMenuOpen = false" href="#faq" class="text-slate-600 dark:text-slate-300 hover:text-primary font-semibold text-lg py-2 transition-all">FAQ</a>
            <a @click="mobileMenuOpen = false" href="#contact" class="text-slate-600 dark:text-slate-300 hover:text-primary font-semibold text-lg py-2 transition-all"><?= __('contact_title') ?></a>
            
            <div class="h-px bg-slate-200 dark:bg-slate-800 my-2"></div>
            
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-500 dark:text-slate-400 font-medium">Langue</span>
                <div class="flex bg-slate-100 dark:bg-slate-800 p-1 rounded-xl border border-slate-200/20">
                    <a href="/lang?l=en" 
                       class="px-4 py-1.5 rounded-lg text-xs font-bold uppercase tracking-widest transition-all"
                       :class="{'bg-white dark:bg-slate-900 text-primary shadow-sm': '<?= \App\Core\Language::getCurrent() ?>' === 'en', 'text-slate-400 dark:text-slate-500': '<?= \App\Core\Language::getCurrent() ?>' !== 'en'}"
                    >EN</a>
                    <a href="/lang?l=fr" 
                       class="px-4 py-1.5 rounded-lg text-xs font-bold uppercase tracking-widest transition-all"
                       :class="{'bg-white dark:bg-slate-900 text-primary shadow-sm': '<?= \App\Core\Language::getCurrent() ?>' === 'fr', 'text-slate-400 dark:text-slate-500': '<?= \App\Core\Language::getCurrent() ?>' !== 'fr'}"
                    >FR</a>
                </div>
            </div>
            
            <div class="h-px bg-slate-200 dark:bg-slate-800 my-2"></div>

            <?php if (\App\Core\Session::has('user_id')): ?>
                <a @click="mobileMenuOpen = false" href="/dashboard" class="w-full text-center bg-primary hover:bg-primary-hover text-white font-bold py-3.5 rounded-xl transition-all shadow-md shadow-primary/25">
                    <?= __('dashboard') ?>
                </a>
            <?php else: ?>
                <div class="grid grid-cols-2 gap-4">
                    <a @click="mobileMenuOpen = false" href="/login" class="w-full text-center border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 font-semibold py-3 rounded-xl transition-all">
                        <?= __('login') ?>
                    </a>
                    <a @click="mobileMenuOpen = false" href="/register" class="w-full text-center bg-primary hover:bg-primary-hover text-white font-bold py-3 rounded-xl transition-all shadow-md">
                        <?= __('get_started') ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>


    <!-- 2. Hero Section -->
    <header class="relative pt-44 pb-20 md:pt-56 md:pb-28 overflow-hidden">
        <!-- Floating shapes -->
        <div class="absolute top-24 left-10 w-24 h-24 bg-primary/20 dark:bg-primary/10 rounded-[2.2rem] animate-float-1 -z-10 blur-sm pointer-events-none"></div>
        <div class="absolute top-48 right-16 w-32 h-32 bg-secondary/15 dark:bg-secondary/10 rounded-full animate-float-2 -z-10 blur-sm pointer-events-none"></div>
        
        <div class="max-w-7xl mx-auto px-6 text-center">
            <!-- Animated Notification Badge -->
            <div class="inline-flex items-center space-x-2 bg-primary/10 dark:bg-primary/5 border border-primary/20 dark:border-primary/10 rounded-full px-4 py-2 mb-8 animate-bounce-slow">
                <span class="w-2 h-2 rounded-full bg-primary animate-ping"></span>
                <span class="text-xs font-extrabold text-primary tracking-wider uppercase">Plateforme Certifiée & Sécurisée</span>
            </div>

            <!-- Title with gradient and premium typography -->
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-black text-slate-900 dark:text-white tracking-tight leading-[1.15] mb-8 max-w-4xl mx-auto">
                <?= __('hero_title') ?><br>
                <span class="bg-gradient-to-r from-primary via-blue-500 to-secondary bg-clip-text text-transparent"><?= __('hero_subtitle') ?></span>
            </h1>
            
            <!-- Description -->
            <p class="max-w-2xl mx-auto text-lg md:text-xl text-slate-500 dark:text-slate-400 mb-12 font-medium leading-relaxed">
                <?= __('hero_desc') ?>
            </p>
            
            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row justify-center gap-4 mb-20">
                <a href="/register"
                    class="px-8 py-4 bg-gradient-to-r from-primary to-blue-600 hover:opacity-95 text-white text-lg font-bold rounded-xl transition-all shadow-lg shadow-primary/25 hover:scale-[1.02] active:scale-[0.98]">
                    <?= __('get_started') ?>
                </a>
                <a href="#plans"
                    class="px-8 py-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 text-lg font-bold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all hover:scale-[1.02] active:scale-[0.98]">
                    <?= __('learn_more') ?>
                </a>
            </div>
        </div>

        <!-- Dashboard Demo Container with Browser Chrome Interface -->
        <div class="max-w-5xl mx-auto px-6 mt-6 relative">
            <div class="relative z-10 rounded-3xl overflow-hidden shadow-2xl border border-slate-200/50 dark:border-slate-800/80 bg-slate-900/5 dark:bg-slate-900/20 backdrop-blur-sm p-2">
                <!-- Mock Browser Header -->
                <div class="flex items-center gap-2 px-5 py-3.5 bg-white/90 dark:bg-slate-900/90 rounded-t-2xl border-b border-slate-200/50 dark:border-slate-800/80">
                    <div class="flex gap-2">
                        <span class="w-3.5 h-3.5 rounded-full bg-red-400"></span>
                        <span class="w-3.5 h-3.5 rounded-full bg-yellow-400"></span>
                        <span class="w-3.5 h-3.5 rounded-full bg-green-400"></span>
                    </div>
                    <div class="mx-auto w-1/2 bg-slate-100 dark:bg-slate-800/80 text-xs text-slate-400 dark:text-slate-500 text-center py-1.5 rounded-lg border border-slate-200/10 truncate font-mono">
                        https://localhost:8000/dashboard
                    </div>
                </div>
                <!-- Interactive Dashboard Mockup -->
                <div class="w-full bg-slate-50 dark:bg-slate-950 p-6 md:p-8 rounded-b-2xl border-t border-slate-200/50 dark:border-slate-800/80 text-left font-sans select-none">
                    <!-- Main mockup layout -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Card 1: Balance -->
                        <div class="bg-white dark:bg-slate-900 border border-slate-200/40 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-xs text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider">Solde Principal</span>
                                <div class="w-8 h-8 rounded-full bg-blue-500/10 text-blue-500 flex items-center justify-center">
                                    <i data-lucide="wallet" class="w-4 h-4"></i>
                                </div>
                            </div>
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-white">45 000 FCFA</p>
                            <p class="text-[9px] text-slate-400 dark:text-slate-500 font-bold uppercase mt-1">Dernier dépôt : +10k FCFA</p>
                        </div>
                        <!-- Card 2: Investment -->
                        <div class="bg-white dark:bg-slate-900 border border-slate-200/40 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-xs text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider">Investi Actif</span>
                                <div class="w-8 h-8 rounded-full bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                                    <i data-lucide="trending-up" class="w-4 h-4"></i>
                                </div>
                            </div>
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-white">25 000 FCFA</p>
                            <p class="text-[9px] text-secondary font-bold uppercase mt-1">ROI : +2.5% quotidien</p>
                        </div>
                        <!-- Card 3: Watch Progress -->
                        <div class="bg-white dark:bg-slate-900 border border-slate-200/40 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-xs text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider">Tâches Vidéo</span>
                                <div class="w-8 h-8 rounded-full bg-amber-500/10 text-amber-500 flex items-center justify-center">
                                    <i data-lucide="play-circle" class="w-4 h-4"></i>
                                </div>
                            </div>
                            <div class="flex justify-between items-end mb-1">
                                <p class="text-2xl font-extrabold text-slate-900 dark:text-white">5 / 5</p>
                                <span class="text-[9px] text-amber-500 font-bold uppercase mb-1">Tâches Complétées</span>
                            </div>
                            <!-- Simple Progress Bar -->
                            <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-amber-500 h-full rounded-full w-full"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Mockup Row: Chart & Activity -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-6">
                        <!-- Left: Live Payout Chart Mock -->
                        <div class="lg:col-span-7 bg-white dark:bg-slate-900 border border-slate-200/40 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-xs text-slate-900 dark:text-white font-bold uppercase tracking-wider">Historique de Croissance</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-bold">7 derniers jours</span>
                            </div>
                            <!-- Mock Mini Line Chart using flex and borders -->
                            <div class="flex items-end justify-between h-28 pt-4 px-2">
                                <div class="w-8 bg-blue-500/10 dark:bg-blue-500/5 h-[30%] rounded-t-md relative group">
                                    <div class="absolute -top-6 left-1/2 -translate-x-1/2 bg-slate-900 dark:bg-slate-800 text-[8px] text-white font-bold px-1 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity">10k</div>
                                </div>
                                <div class="w-8 bg-blue-500/20 dark:bg-blue-500/10 h-[45%] rounded-t-md relative group">
                                    <div class="absolute -top-6 left-1/2 -translate-x-1/2 bg-slate-900 dark:bg-slate-800 text-[8px] text-white font-bold px-1 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity">15k</div>
                                </div>
                                <div class="w-8 bg-blue-500/30 dark:bg-blue-500/15 h-[40%] rounded-t-md relative group">
                                    <div class="absolute -top-6 left-1/2 -translate-x-1/2 bg-slate-900 dark:bg-slate-800 text-[8px] text-white font-bold px-1 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity">13k</div>
                                </div>
                                <div class="w-8 bg-blue-500/40 dark:bg-blue-500/20 h-[60%] rounded-t-md relative group">
                                    <div class="absolute -top-6 left-1/2 -translate-x-1/2 bg-slate-900 dark:bg-slate-800 text-[8px] text-white font-bold px-1 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity">20k</div>
                                </div>
                                <div class="w-8 bg-blue-500/50 dark:bg-blue-500/25 h-[80%] rounded-t-md relative group">
                                    <div class="absolute -top-6 left-1/2 -translate-x-1/2 bg-slate-900 dark:bg-slate-800 text-[8px] text-white font-bold px-1 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity">27k</div>
                                </div>
                                <div class="w-8 bg-gradient-to-t from-primary to-blue-400 h-full rounded-t-md relative group">
                                    <div class="absolute -top-6 left-1/2 -translate-x-1/2 bg-slate-900 dark:bg-slate-800 text-[8px] text-white font-bold px-1 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity">45k</div>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Recent Activity Mock -->
                        <div class="lg:col-span-5 bg-white dark:bg-slate-900 border border-slate-200/40 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
                            <div class="mb-4">
                                <span class="text-xs text-slate-900 dark:text-white font-bold uppercase tracking-wider">Activités Récentes</span>
                            </div>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center text-xs">
                                    <div class="flex items-center space-x-2">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        <span class="font-bold text-slate-600 dark:text-slate-400">Recharge Mobile</span>
                                    </div>
                                    <span class="font-black text-slate-900 dark:text-white">+10 000 FCFA</span>
                                </div>
                                <div class="flex justify-between items-center text-xs">
                                    <div class="flex items-center space-x-2">
                                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                        <span class="font-bold text-slate-600 dark:text-slate-400">Gain Vidéo Reçu</span>
                                    </div>
                                    <span class="font-black text-slate-900 dark:text-white">+1 250 FCFA</span>
                                </div>
                                <div class="flex justify-between items-center text-xs">
                                    <div class="flex items-center space-x-2">
                                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                        <span class="font-bold text-slate-600 dark:text-slate-400">Retrait Demandé</span>
                                    </div>
                                    <span class="font-black text-slate-900 dark:text-white">-5 000 FCFA</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="absolute -top-10 -left-10 w-48 h-48 bg-primary/20 rounded-full blur-3xl opacity-40 -z-10"></div>
            <div class="absolute -bottom-10 -right-10 w-48 h-48 bg-secondary/20 rounded-full blur-3xl opacity-40 -z-10"></div>
        </div>
    </header>


    <!-- 3. Partners Marquee Section (Scrolling Logo Ticker) -->
    <section class="py-10 bg-white/80 dark:bg-slate-900/40 border-y border-slate-200/50 dark:border-slate-800/80 overflow-hidden relative">
        <div class="w-full relative flex">
            <!-- Left & Right Shadows/Fades for visual smoothness -->
            <div class="absolute left-0 inset-y-0 w-20 bg-gradient-to-r from-slate-50 dark:from-slate-950 to-transparent z-10 pointer-events-none"></div>
            <div class="absolute right-0 inset-y-0 w-20 bg-gradient-to-l from-slate-50 dark:from-slate-950 to-transparent z-10 pointer-events-none"></div>
            
            <!-- Scrolling Logo Ticker Track -->
            <div class="flex items-center gap-16 w-max animate-marquee whitespace-nowrap">
                <!-- Group 1 -->
                <div class="flex items-center gap-16 text-slate-400 dark:text-slate-600 font-extrabold text-lg select-none">
                    <div class="flex items-center gap-2 bg-yellow-500/10 dark:bg-yellow-500/5 px-4 py-2.5 rounded-xl border border-yellow-500/20 text-yellow-600 dark:text-yellow-500">
                        <span class="font-black text-sm uppercase tracking-wider">MTN MoMo</span>
                    </div>
                    <div class="flex items-center gap-2 bg-orange-500/10 dark:bg-orange-500/5 px-4 py-2.5 rounded-xl border border-orange-500/20 text-orange-600 dark:text-orange-500">
                        <span class="font-black text-sm uppercase tracking-wider">Orange Money</span>
                    </div>
                    <div class="flex items-center gap-2 bg-blue-500/10 dark:bg-blue-500/5 px-4 py-2.5 rounded-xl border border-blue-500/20 text-blue-600 dark:text-blue-500">
                        <i data-lucide="waves" class="w-4 h-4"></i>
                        <span class="font-black text-sm uppercase tracking-wider">WAVE</span>
                    </div>
                    <div class="flex items-center gap-2 bg-yellow-500/10 dark:bg-yellow-500/5 px-4 py-2.5 rounded-xl border border-yellow-500/20 text-yellow-600 dark:text-yellow-500">
                        <i data-lucide="gem" class="w-4 h-4"></i>
                        <span class="font-black text-sm uppercase tracking-wider">Binance</span>
                    </div>
                    <div class="flex items-center gap-2 bg-emerald-500/10 dark:bg-emerald-500/5 px-4 py-2.5 rounded-xl border border-emerald-500/20 text-emerald-600 dark:text-emerald-500">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                        <span class="font-black text-sm uppercase tracking-wider">PCI Compliant</span>
                    </div>
                    <div class="flex items-center gap-2 bg-blue-600/10 dark:bg-blue-600/5 px-4 py-2.5 rounded-xl border border-blue-600/20 text-blue-600 dark:text-blue-500">
                        <span class="font-black text-sm uppercase tracking-wider">VISA</span>
                    </div>
                    <div class="flex items-center gap-2 bg-red-500/10 dark:bg-red-500/5 px-4 py-2.5 rounded-xl border border-red-500/20 text-red-500">
                        <span class="font-black text-sm uppercase tracking-wider">Mastercard</span>
                    </div>
                </div>
                
                <!-- Group 2 (Duplicate for loop) -->
                <div class="flex items-center gap-16 text-slate-400 dark:text-slate-600 font-extrabold text-lg select-none">
                    <div class="flex items-center gap-2 bg-yellow-500/10 dark:bg-yellow-500/5 px-4 py-2.5 rounded-xl border border-yellow-500/20 text-yellow-600 dark:text-yellow-500">
                        <span class="font-black text-sm uppercase tracking-wider">MTN MoMo</span>
                    </div>
                    <div class="flex items-center gap-2 bg-orange-500/10 dark:bg-orange-500/5 px-4 py-2.5 rounded-xl border border-orange-500/20 text-orange-600 dark:text-orange-500">
                        <span class="font-black text-sm uppercase tracking-wider">Orange Money</span>
                    </div>
                    <div class="flex items-center gap-2 bg-blue-500/10 dark:bg-blue-500/5 px-4 py-2.5 rounded-xl border border-blue-500/20 text-blue-600 dark:text-blue-500">
                        <i data-lucide="waves" class="w-4 h-4"></i>
                        <span class="font-black text-sm uppercase tracking-wider">WAVE</span>
                    </div>
                    <div class="flex items-center gap-2 bg-yellow-500/10 dark:bg-yellow-500/5 px-4 py-2.5 rounded-xl border border-yellow-500/20 text-yellow-600 dark:text-yellow-500">
                        <i data-lucide="gem" class="w-4 h-4"></i>
                        <span class="font-black text-sm uppercase tracking-wider">Binance</span>
                    </div>
                    <div class="flex items-center gap-2 bg-emerald-500/10 dark:bg-emerald-500/5 px-4 py-2.5 rounded-xl border border-emerald-500/20 text-emerald-600 dark:text-emerald-500">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                        <span class="font-black text-sm uppercase tracking-wider">PCI Compliant</span>
                    </div>
                    <div class="flex items-center gap-2 bg-blue-600/10 dark:bg-blue-600/5 px-4 py-2.5 rounded-xl border border-blue-600/20 text-blue-600 dark:text-blue-500">
                        <span class="font-black text-sm uppercase tracking-wider">VISA</span>
                    </div>
                    <div class="flex items-center gap-2 bg-red-500/10 dark:bg-red-500/5 px-4 py-2.5 rounded-xl border border-red-500/20 text-red-500">
                        <span class="font-black text-sm uppercase tracking-wider">Mastercard</span>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- 4. Statistiques Clés (Trust metrics) -->
    <section class="py-20 bg-slate-50/50 dark:bg-slate-950/20 relative">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Stat Card 1 -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200/40 dark:border-slate-800/80 rounded-2xl p-6 text-center shadow-sm">
                    <div class="w-12 h-12 bg-primary/10 dark:bg-primary/20 text-primary rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="users" class="w-6 h-6"></i>
                    </div>
                    <p class="text-3xl font-extrabold text-slate-900 dark:text-white mb-2">15 000+</p>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Investisseurs Actifs</p>
                </div>
                
                <!-- Stat Card 2 -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200/40 dark:border-slate-800/80 rounded-2xl p-6 text-center shadow-sm">
                    <div class="w-12 h-12 bg-secondary/10 dark:bg-secondary/20 text-secondary rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="arrow-up-circle" class="w-6 h-6"></i>
                    </div>
                    <p class="text-3xl font-extrabold text-slate-900 dark:text-white mb-2">4.5M+ FCFA</p>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Fonds Total Déposés</p>
                </div>
                
                <!-- Stat Card 3 -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200/40 dark:border-slate-800/80 rounded-2xl p-6 text-center shadow-sm">
                    <div class="w-12 h-12 bg-blue-500/10 dark:bg-blue-500/20 text-blue-500 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="arrow-down-circle" class="w-6 h-6"></i>
                    </div>
                    <p class="text-3xl font-extrabold text-slate-900 dark:text-white mb-2">2.1M+ FCFA</p>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Retraits Traités</p>
                </div>
                
                <!-- Stat Card 4 -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200/40 dark:border-slate-800/80 rounded-2xl p-6 text-center shadow-sm">
                    <div class="w-12 h-12 bg-amber-500/10 dark:bg-amber-500/20 text-amber-500 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="activity" class="w-6 h-6"></i>
                    </div>
                    <p class="text-3xl font-extrabold text-slate-900 dark:text-white mb-2">150+ Jours</p>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Jours en Ligne</p>
                </div>
            </div>
        </div>
    </section>


    <!-- 5. Interactive Investment Calculator Section -->
    <script>
        // Inject plans database dynamically into JS
        const plansList = <?= json_encode(array_map(function($p) {
            return [
                'id' => (int)$p['id'],
                'name' => $p['name'],
                'price' => (float)$p['price'],
                'daily_profit' => (float)($p['daily_profit_amount'] ?? ($p['price'] * $p['daily_profit_percent'] / 100)),
                'duration' => (int)$p['duration_days'],
                'ads' => (int)$p['ads_per_day']
            ];
        }, $plans)) ?>;
    </script>
    <section id="calculator" class="py-24 bg-white dark:bg-slate-900/40">
        <div class="max-w-4xl mx-auto px-6">
            <!-- Header -->
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tight mb-4">
                    Calculez vos Gains
                </h2>
                <p class="text-slate-500 dark:text-slate-400 text-lg font-semibold max-w-xl mx-auto">
                    Sélectionnez un plan d'investissement pour estimer vos rendements quotidiens et totaux.
                </p>
            </div>

            <!-- Calculator Box -->
            <div 
                x-data="{
                    plans: plansList,
                    selectedId: plansList[0]?.id || 0,
                    get current() {
                        return this.plans.find(p => p.id == this.selectedId) || {};
                    }
                }"
                class="bg-slate-50 dark:bg-slate-900 border border-slate-200/50 dark:border-slate-800 rounded-[2.5rem] p-8 md:p-12 shadow-xl"
            >
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
                    <!-- Left: Control -->
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest pl-2">
                                Choisir un Plan
                            </label>
                            <div class="relative">
                                <select 
                                    x-model="selectedId" 
                                    class="w-full bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl px-6 py-4 text-slate-900 dark:text-white font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 appearance-none cursor-pointer"
                                >
                                    <template x-for="p in plans" :key="p.id">
                                        <option :value="p.id" x-text="p.name"></option>
                                    </template>
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 dark:text-slate-500">
                                    <i data-lucide="chevron-down" class="w-5 h-5"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Info Pill -->
                        <div class="bg-primary/5 border border-primary/10 rounded-2xl p-4 flex items-start space-x-3">
                            <i data-lucide="info" class="w-5 h-5 text-primary shrink-0 mt-0.5"></i>
                            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-medium">
                                Les gains sont crédités quotidiennement après visionnage de <span class="font-extrabold text-primary" x-text="current.ads"></span> publicités requises.
                            </p>
                        </div>
                    </div>

                    <!-- Right: Results Display -->
                    <div class="bg-white dark:bg-slate-950 border border-slate-200/50 dark:border-slate-800/80 rounded-3xl p-8 space-y-6">
                        <!-- Cost -->
                        <div class="flex justify-between items-center text-sm border-b border-slate-100 dark:border-slate-900 pb-4">
                            <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Investissement</span>
                            <span class="text-slate-900 dark:text-white font-black text-lg" x-text="Number(current.price).toLocaleString() + ' FCFA'"></span>
                        </div>
                        
                        <!-- Daily Profit -->
                        <div class="flex justify-between items-center text-sm border-b border-slate-100 dark:border-slate-900 pb-4">
                            <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Gain Quotidien</span>
                            <span class="text-secondary font-black text-lg" x-text="'+' + Number(current.daily_profit).toLocaleString() + ' FCFA'"></span>
                        </div>

                        <!-- Duration -->
                        <div class="flex justify-between items-center text-sm border-b border-slate-100 dark:border-slate-900 pb-4">
                            <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Durée de Validité</span>
                            <span class="text-slate-900 dark:text-white font-black" x-text="current.duration + ' Jours'"></span>
                        </div>

                        <!-- Total Returns -->
                        <div class="flex justify-between items-center text-sm pt-2">
                            <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Rendement Global</span>
                            <div class="text-right">
                                <span class="text-primary font-black text-2xl" x-text="Number(current.daily_profit * current.duration).toLocaleString() + ' FCFA'"></span>
                                <p class="text-[9px] text-slate-400 dark:text-slate-500 font-bold uppercase mt-1">
                                    Bénéfice net : +<span x-text="Number((current.daily_profit * current.duration) - current.price).toLocaleString()"></span> FCFA
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- 6. Comment Ça Marche Section -->
    <section class="py-24 bg-slate-50/50 dark:bg-slate-950/20 relative">
        <div class="max-w-7xl mx-auto px-6">
            <!-- Header -->
            <div class="text-center mb-20">
                <h2 class="text-3xl md:text-5xl font-black text-slate-900 dark:text-white mb-4 tracking-tight"><?= __('how_it_works_title') ?></h2>
                <p class="text-slate-500 dark:text-slate-400 text-lg font-semibold"><?= __('how_it_works_subtitle') ?></p>
            </div>

            <!-- Steps Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <!-- Step 1 -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200/50 dark:border-slate-800 rounded-3xl p-8 relative overflow-hidden transition-all duration-300 hover:shadow-2xl hover:-translate-y-2 group">
                    <span class="text-8xl font-black text-slate-200/30 dark:text-slate-800/20 absolute -right-2 -bottom-4 select-none group-hover:scale-110 transition-all duration-300">01</span>
                    <div class="w-16 h-16 bg-primary/10 dark:bg-primary/20 text-primary rounded-2xl flex items-center justify-center mb-8 relative z-10">
                        <i data-lucide="user-plus" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-4 text-slate-900 dark:text-white relative z-10"><?= __('step_1_title') ?></h3>
                    <p class="text-slate-500 dark:text-slate-400 font-medium leading-relaxed relative z-10"><?= __('step_1_desc') ?></p>
                </div>
                
                <!-- Step 2 -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200/50 dark:border-slate-800 rounded-3xl p-8 relative overflow-hidden transition-all duration-300 hover:shadow-2xl hover:-translate-y-2 group">
                    <span class="text-8xl font-black text-slate-200/30 dark:text-slate-800/20 absolute -right-2 -bottom-4 select-none group-hover:scale-110 transition-all duration-300">02</span>
                    <div class="w-16 h-16 bg-secondary/10 dark:bg-secondary/20 text-secondary rounded-2xl flex items-center justify-center mb-8 relative z-10">
                        <i data-lucide="trending-up" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-4 text-slate-900 dark:text-white relative z-10"><?= __('step_2_title') ?></h3>
                    <p class="text-slate-500 dark:text-slate-400 font-medium leading-relaxed relative z-10"><?= __('step_2_desc') ?></p>
                </div>
                
                <!-- Step 3 -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200/50 dark:border-slate-800 rounded-3xl p-8 relative overflow-hidden transition-all duration-300 hover:shadow-2xl hover:-translate-y-2 group">
                    <span class="text-8xl font-black text-slate-200/30 dark:text-slate-800/20 absolute -right-2 -bottom-4 select-none group-hover:scale-110 transition-all duration-300">03</span>
                    <div class="w-16 h-16 bg-amber-500/10 dark:bg-amber-500/20 text-amber-500 rounded-2xl flex items-center justify-center mb-8 relative z-10">
                        <i data-lucide="wallet" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-4 text-slate-900 dark:text-white relative z-10"><?= __('step_3_title') ?></h3>
                    <p class="text-slate-500 dark:text-slate-400 font-medium leading-relaxed relative z-10"><?= __('step_3_desc') ?></p>
                </div>
            </div>
        </div>
    </section>


    <!-- 7. Investment Plans Section -->
    <section id="plans" class="py-24 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">
            <!-- Header -->
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tight mb-4">
                    <?= __('investment_plans_title') ?>
                </h2>
                <p class="text-slate-500 dark:text-slate-400 text-lg font-semibold max-w-2xl mx-auto"><?= __('choose_plan') ?></p>
            </div>

            <!-- Plans Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($plans as $plan): ?>
                    <div class="bg-white dark:bg-slate-900 border border-slate-200/50 dark:border-slate-800 rounded-[2.2rem] overflow-hidden group hover:border-primary/40 dark:hover:border-primary/40 transition-all duration-300 hover:shadow-2xl flex flex-col justify-between">
                        <!-- Plan Image -->
                        <div class="h-60 bg-slate-100 dark:bg-slate-800 relative overflow-hidden">
                            <img src="<?= $plan['image_url'] ?>" alt="<?= htmlspecialchars($plan['name']) ?>"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                            <!-- Gradient shading -->
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-900/30 to-transparent"></div>
                            
                            <!-- Yield badge -->
                            <div class="absolute bottom-6 left-6">
                                <span class="bg-gradient-to-r from-primary to-blue-600 text-white text-[11px] font-extrabold px-4.5 py-2 rounded-full uppercase tracking-wider shadow-lg shadow-primary/30">
                                    +<?= $plan['daily_profit_percent'] ?>% / Jour
                                </span>
                            </div>
                        </div>
                        
                        <!-- Details -->
                        <div class="p-8 md:p-10 flex-grow flex flex-col justify-between">
                            <div>
                                <h3 class="text-2xl font-black text-slate-900 dark:text-white mb-6"><?= htmlspecialchars($plan['name']) ?></h3>
                                
                                <div class="space-y-4 mb-10">
                                    <!-- Price Row -->
                                    <div class="flex justify-between items-center text-sm">
                                        <div class="flex items-center gap-2 text-slate-400 dark:text-slate-500">
                                            <i data-lucide="circle-dollar-sign" class="w-4 h-4"></i>
                                            <span class="font-bold uppercase tracking-wider text-[10px]"><?= __('min_amount') ?></span>
                                        </div>
                                        <span class="text-slate-900 dark:text-white font-extrabold text-lg">
                                            <?= number_format($plan['price'], 0, '.', ' ') ?> <?= __('cfa') ?>
                                        </span>
                                    </div>
                                    
                                    <!-- Duration Row -->
                                    <div class="flex justify-between items-center text-sm border-t border-slate-100 dark:border-slate-800/80 pt-4">
                                        <div class="flex items-center gap-2 text-slate-400 dark:text-slate-500">
                                            <i data-lucide="clock" class="w-4 h-4"></i>
                                            <span class="font-bold uppercase tracking-wider text-[10px]"><?= __('total_duration') ?></span>
                                        </div>
                                        <span class="text-slate-900 dark:text-white font-extrabold">
                                            <?= $plan['duration_days'] ?> <?= __('days') ?>
                                        </span>
                                    </div>
                                    
                                    <!-- Ads Row -->
                                    <div class="flex justify-between items-center text-sm border-t border-slate-100 dark:border-slate-800/80 pt-4">
                                        <div class="flex items-center gap-2 text-slate-400 dark:text-slate-500">
                                            <i data-lucide="play-circle" class="w-4 h-4"></i>
                                            <span class="font-bold uppercase tracking-wider text-[10px]"><?= __('daily_ads') ?></span>
                                        </div>
                                        <span class="text-secondary font-extrabold">
                                            <?= $plan['ads_per_day'] ?> <?= __('ads') ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Button -->
                            <a href="/login?redirect=plans"
                                class="block w-full text-center bg-slate-50 dark:bg-slate-800/80 group-hover:bg-primary text-slate-800 dark:text-slate-200 group-hover:text-white font-extrabold py-4 rounded-2xl transition-all shadow-md group-hover:shadow-primary/20 border border-slate-200 dark:border-slate-700/50 group-hover:border-primary active:scale-95">
                                <?= __('get_started') ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>


    <!-- 8. Trust, Security & Compliance Section -->
    <section class="py-24 bg-slate-50/50 dark:bg-slate-950/20 relative">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col lg:flex-row items-center gap-20">
                <!-- Text on Left -->
                <div class="flex-1 space-y-8">
                    <h2 class="text-3xl md:text-5xl font-black text-slate-900 dark:text-white leading-tight">
                        Une Sécurité de Niveau Bancaire
                    </h2>
                    <p class="text-slate-500 dark:text-slate-400 text-lg leading-relaxed font-medium">
                        La protection de vos capitaux et de vos données personnelles est notre priorité absolue. Nous utilisons des technologies de pointe pour garantir un environnement d'investissement 100% sûr.
                    </p>

                    <!-- Indicators Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4">
                        <!-- Security Card 1 -->
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-secondary/15 text-secondary rounded-xl flex items-center justify-center shrink-0">
                                <i data-lucide="lock" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <p class="font-bold text-slate-900 dark:text-white text-sm">Cryptage SSL 256 bits</p>
                                <p class="text-slate-400 text-xs mt-0.5">Transactions sécurisées</p>
                            </div>
                        </div>

                        <!-- Security Card 2 -->
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-primary/15 text-primary rounded-xl flex items-center justify-center shrink-0">
                                <i data-lucide="server" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <p class="font-bold text-slate-900 dark:text-white text-sm">Protection DDoS</p>
                                <p class="text-slate-400 text-xs mt-0.5">Serveurs en ligne 99.9%</p>
                            </div>
                        </div>

                        <!-- Security Card 3 -->
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-amber-500/15 text-amber-500 rounded-xl flex items-center justify-center shrink-0">
                                <i data-lucide="shield-check" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <p class="font-bold text-slate-900 dark:text-white text-sm">Conformité RGPD</p>
                                <p class="text-slate-400 text-xs mt-0.5">Vos données vous appartiennent</p>
                            </div>
                        </div>

                        <!-- Security Card 4 -->
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-red-500/15 text-red-500 rounded-xl flex items-center justify-center shrink-0">
                                <i data-lucide="refresh-cw" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <p class="font-bold text-slate-900 dark:text-white text-sm">Sauvegarde en Temps Réel</p>
                                <p class="text-slate-400 text-xs mt-0.5">Solde toujours protégé</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Visual Image on Right -->
                <div class="flex-1 relative">
                    <div class="relative z-10 rounded-[3rem] overflow-hidden shadow-2xl border border-slate-200/50 dark:border-slate-800/80 bg-slate-900/10 p-2">
                        <img src="/images/secure-earning.png" alt="Compliance Certificate Representation" class="w-full h-auto rounded-[2.5rem] object-cover">
                    </div>
                    <!-- Glow orb -->
                    <div class="absolute -inset-10 bg-primary/20 rounded-full blur-3xl opacity-30 -z-10"></div>
                </div>
            </div>
        </div>
    </section>


    <!-- 9. Interactive FAQ Section (Accordions) -->
    <section id="faq" class="py-24 bg-white dark:bg-slate-900/40">
        <div class="max-w-4xl mx-auto px-6">
            <!-- Header -->
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tight mb-4">
                    Questions Fréquentes
                </h2>
                <p class="text-slate-500 dark:text-slate-400 text-lg font-semibold max-w-xl mx-auto">
                    Tout ce que vous devez savoir sur notre plateforme de gains et d'investissements.
                </p>
            </div>

            <!-- Accordions Container -->
            <div x-data="{ activeFaq: 0 }" class="space-y-4">
                <!-- FAQ Item 1 -->
                <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200/50 dark:border-slate-800 rounded-2xl overflow-hidden transition-all duration-300">
                    <button 
                        @click="activeFaq = (activeFaq === 1 ? 0 : 1)" 
                        class="w-full px-6 py-5 flex items-center justify-between font-bold text-left text-slate-900 dark:text-white hover:text-primary dark:hover:text-primary transition-colors focus:outline-none"
                    >
                        <span>Comment puis-je m'inscrire et commencer à investir ?</span>
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform duration-300" :class="{'rotate-180 text-primary': activeFaq === 1}"></i>
                    </button>
                    <div 
                        x-show="activeFaq === 1" 
                        x-collapse
                        class="px-6 pb-6 text-sm text-slate-500 dark:text-slate-400 leading-relaxed font-medium"
                    >
                        L'inscription est rapide et gratuite. Cliquez sur le bouton "S'inscrire", remplissez le formulaire avec vos coordonnées en moins de 2 minutes. Une fois inscrit, rendez-vous dans la section rechargement, faites un dépôt par Mobile Money, choisissez un plan et visionnez vos publicités pour commencer à générer des profits.
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200/50 dark:border-slate-800 rounded-2xl overflow-hidden transition-all duration-300">
                    <button 
                        @click="activeFaq = (activeFaq === 2 ? 0 : 2)" 
                        class="w-full px-6 py-5 flex items-center justify-between font-bold text-left text-slate-900 dark:text-white hover:text-primary dark:hover:text-primary transition-colors focus:outline-none"
                    >
                        <span>Quels sont les opérateurs de paiement acceptés pour le rechargement ?</span>
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform duration-300" :class="{'rotate-180 text-primary': activeFaq === 2}"></i>
                    </button>
                    <div 
                        x-show="activeFaq === 2" 
                        x-collapse
                        class="px-6 pb-6 text-sm text-slate-500 dark:text-slate-400 leading-relaxed font-medium"
                    >
                        Nous acceptons les méthodes de paiements locales les plus courantes pour faciliter vos dépôts et retraits en Afrique : MTN Mobile Money (MoMo), Orange Money, et Wave. De plus, nous prenons en charge les dépôts en cryptomonnaie (Stablecoins comme l'USDT) via Binance.
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200/50 dark:border-slate-800 rounded-2xl overflow-hidden transition-all duration-300">
                    <button 
                        @click="activeFaq = (activeFaq === 3 ? 0 : 3)" 
                        class="w-full px-6 py-5 flex items-center justify-between font-bold text-left text-slate-900 dark:text-white hover:text-primary dark:hover:text-primary transition-colors focus:outline-none"
                    >
                        <span>Comment s'effectuent les retraits de gains ?</span>
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform duration-300" :class="{'rotate-180 text-primary': activeFaq === 3}"></i>
                    </button>
                    <div 
                        x-show="activeFaq === 3" 
                        x-collapse
                        class="px-6 pb-6 text-sm text-slate-500 dark:text-slate-400 leading-relaxed font-medium"
                    >
                        Les retraits s'effectuent directement depuis l'interface de votre tableau de bord. Allez dans "Retirer", saisissez le montant de retrait souhaité (minimum de 1 000 FCFA), sélectionnez votre opérateur de téléphonie mobile ou crypto, renseignez votre numéro, puis validez. Les retraits sont traités et envoyés dans un délai de 24 heures.
                    </div>
                </div>

                <!-- FAQ Item 4 -->
                <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200/50 dark:border-slate-800 rounded-2xl overflow-hidden transition-all duration-300">
                    <button 
                        @click="activeFaq = (activeFaq === 4 ? 0 : 4)" 
                        class="w-full px-6 py-5 flex items-center justify-between font-bold text-left text-slate-900 dark:text-white hover:text-primary dark:hover:text-primary transition-colors focus:outline-none"
                    >
                        <span>Est-ce que je peux cumuler plusieurs plans d'investissement ?</span>
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform duration-300" :class="{'rotate-180 text-primary': activeFaq === 4}"></i>
                    </button>
                    <div 
                        x-show="activeFaq === 4" 
                        x-collapse
                        class="px-6 pb-6 text-sm text-slate-500 dark:text-slate-400 leading-relaxed font-medium"
                    >
                        Oui, absolument. Notre plateforme vous permet de souscrire à plusieurs plans d'investissement en même temps. Les gains de chaque plan individuel s'ajouteront à votre solde global de manière indépendante, et le nombre de publicités quotidiennes à regarder correspondra au plan le plus élevé.
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- 10. Contact & Newsletter Form -->
    <section id="contact" class="py-24 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-16 items-center">
                <!-- Left: Info & Newsletter -->
                <div class="lg:col-span-5 space-y-8 text-left">
                    <div>
                        <h2 class="text-3xl md:text-5xl font-black text-slate-900 dark:text-white mb-4"><?= __('contact_title') ?></h2>
                        <p class="text-slate-500 dark:text-slate-400 font-semibold text-lg"><?= __('contact_subtitle') ?></p>
                    </div>

                    <!-- Newsletter Card -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200/50 dark:border-slate-800/80 rounded-[2rem] p-6 shadow-sm">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Abonnez-vous à la Newsletter</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed mb-4">Recevez les dernières mises à jour du marché et les opportunités d'investissements exclusifs.</p>
                        
                        <div class="flex gap-2">
                            <input type="email" placeholder="votre@email.com" class="flex-1 bg-slate-50 dark:bg-slate-950 border border-slate-200/50 dark:border-slate-800 rounded-xl px-4 py-3 text-xs focus:outline-none focus:ring-1 focus:ring-primary font-bold">
                            <button type="button" class="bg-primary hover:bg-primary-hover text-white font-extrabold text-xs px-4 py-3 rounded-xl transition-all">S'abonner</button>
                        </div>
                    </div>
                </div>

                <!-- Right: Contact Form -->
                <div class="lg:col-span-7 bg-white/80 dark:bg-slate-900/60 backdrop-blur-md p-8 md:p-12 rounded-[2.5rem] shadow-xl shadow-slate-100 dark:shadow-none border border-slate-200/50 dark:border-slate-800/80">
                    <form class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Full Name -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest pl-2">
                                    <?= __('full_name_label') ?>
                                </label>
                                <input type="text" placeholder="Eto'o Junior"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200/50 dark:border-slate-800 rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-primary/20 dark:focus:ring-primary/20 transition-all font-bold text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600">
                            </div>
                            
                            <!-- Email -->
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest pl-2">
                                    <?= __('email_label') ?>
                                </label>
                                <input type="email" placeholder="junior@gmail.cm"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200/50 dark:border-slate-800 rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-primary/20 dark:focus:ring-primary/20 transition-all font-bold text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600">
                            </div>
                        </div>
                        
                        <!-- Message -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest pl-2">
                                <?= __('message_label') ?>
                            </label>
                            <textarea rows="4" placeholder="Saisissez votre message..."
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200/50 dark:border-slate-800 rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-primary/20 dark:focus:ring-primary/20 transition-all font-bold text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600"></textarea>
                        </div>
                        
                        <!-- Submit -->
                        <button type="button"
                            class="w-full bg-gradient-to-r from-primary to-blue-600 hover:opacity-95 text-white font-extrabold py-5 rounded-2xl transition-all shadow-xl shadow-primary/25 active:scale-95">
                            <?= __('send_message') ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>


    <!-- 11. Footer -->
    <footer class="py-16 bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800/80 text-center">
        <div class="max-w-7xl mx-auto px-6 space-y-6">
            <div class="flex items-center justify-center space-x-2">
                <img src="/images/logo.png" alt="Investian Logo" class="h-8 w-auto">
            </div>
            
            <p class="text-slate-400 dark:text-slate-500 font-bold tracking-tight text-sm">
                &copy; 2026 Investian - <?= __('footer_rights') ?>
            </p>
            
            <!-- Network status pill -->
            <div class="inline-flex items-center space-x-2 bg-emerald-500/10 dark:bg-emerald-500/5 border border-emerald-500/20 px-3 py-1.5 rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-secondary animate-pulse"></span>
                <span class="text-[10px] font-black text-secondary uppercase tracking-wider">Serveur : Opérationnel & En Ligne</span>
            </div>
        </div>
    </footer>

    <!-- Include WhatsApp Floating Support Widget -->
    <?php include __DIR__ . '/partials/whatsapp.php'; ?>

    <script> 
        // Re-initialize Lucide Icons on DOM ready
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
</body>

</html>