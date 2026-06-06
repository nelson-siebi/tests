<!DOCTYPE html>
<html lang="<?= \App\Core\Language::getCurrent() ?>" class="scroll-smooth" x-data="{ darkMode: false }" :class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - Investian</title>
    
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
    <nav class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200/50 dark:border-slate-800/80 sticky top-0 z-50 py-3.5 px-6 flex items-center justify-between shadow-sm shrink-0 animate-fade-in">
        <a href="/dashboard" class="bg-slate-100 hover:bg-slate-200/80 dark:bg-slate-800 dark:hover:bg-slate-700/80 text-slate-650 dark:text-slate-300 p-2 rounded-xl transition-all flex items-center justify-center">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h1 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-wider"><?= __('help_center') ?></h1>
        <div class="flex items-center space-x-3">
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

            <?php if (\App\Core\Session::get('user_role') === 'admin'): ?>
                <a href="/admin"
                    class="p-2.5 bg-amber-50 hover:bg-amber-100 dark:bg-amber-500/10 dark:hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 rounded-xl transition-all flex items-center justify-center"
                    title="<?= __('admin_panel') ?>">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </a>
            <?php else: ?>
                <div class="w-9"></div> <!-- Spacer -->
            <?php endif; ?>
        </div>
    </nav>

    <main class="flex-grow p-6 max-w-lg mx-auto w-full space-y-8 animate-fade-in">
        <div class="text-center py-8">
            <img src="/images/logo.png" alt="Investian" class="h-16 w-auto mx-auto mb-4">
            <p class="text-slate-500 dark:text-slate-400 font-bold italic text-base"><?= __('support_query') ?></p>
        </div>

        <div class="space-y-4">
            <!-- WhatsApp -->
            <a href="https://wa.me/<?= env('SUPPORT_WHATSAPP') ?>" target="_blank"
                class="flex items-center p-5 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800/80 shadow-md hover:shadow-lg hover:border-green-500/40 transition-all group shrink-0">
                <div class="w-12 h-12 bg-green-500/10 text-green-500 rounded-xl flex items-center justify-center group-hover:scale-105 transition-transform shrink-0">
                    <i data-lucide="message-circle" class="w-6 h-6"></i>
                </div>
                <div class="ml-4 flex-grow min-w-0 pr-2">
                    <h3 class="font-extrabold text-slate-900 dark:text-white">WhatsApp</h3>
                    <p class="text-xs text-slate-400 dark:text-slate-500 font-bold italic truncate"><?= __('instant_response') ?></p>
                </div>
                <i data-lucide="chevron-right" class="w-5 h-5 text-slate-300 dark:text-slate-650 group-hover:translate-x-0.5 transition-transform shrink-0"></i>
            </a>

            <!-- Facebook -->
            <a href="<?= env('SUPPORT_FACEBOOK') ?>" target="_blank"
                class="flex items-center p-5 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800/80 shadow-md hover:shadow-lg hover:border-blue-500/40 transition-all group shrink-0">
                <div class="w-12 h-12 bg-blue-500/10 text-blue-500 rounded-xl flex items-center justify-center group-hover:scale-105 transition-transform shrink-0">
                    <i data-lucide="facebook" class="w-6 h-6"></i>
                </div>
                <div class="ml-4 flex-grow min-w-0 pr-2">
                    <h3 class="font-extrabold text-slate-900 dark:text-white">Facebook</h3>
                    <p class="text-xs text-slate-400 dark:text-slate-500 font-bold italic truncate"><?= __('follow_news') ?></p>
                </div>
                <i data-lucide="chevron-right" class="w-5 h-5 text-slate-300 dark:text-slate-650 group-hover:translate-x-0.5 transition-transform shrink-0"></i>
            </a>

            <!-- Email -->
            <a href="mailto:<?= env('SUPPORT_EMAIL') ?>"
                class="flex items-center p-5 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800/80 shadow-md hover:shadow-lg hover:border-orange-500/40 transition-all group shrink-0">
                <div class="w-12 h-12 bg-orange-500/10 text-orange-550 rounded-xl flex items-center justify-center group-hover:scale-105 transition-transform shrink-0">
                    <i data-lucide="mail" class="w-6 h-6"></i>
                </div>
                <div class="ml-4 flex-grow min-w-0 pr-2">
                    <h3 class="font-extrabold text-slate-900 dark:text-white">Email</h3>
                    <p class="text-xs text-slate-400 dark:text-slate-500 font-bold italic truncate"><?= __('formal_queries') ?></p>
                </div>
                <i data-lucide="chevron-right" class="w-5 h-5 text-slate-300 dark:text-slate-650 group-hover:translate-x-0.5 transition-transform shrink-0"></i>
            </a>
        </div>

        <div class="bg-blue-500/10 border border-blue-500/20 p-6 rounded-[2rem] text-center space-y-4 shadow-sm shadow-blue-500/5">
            <h4 class="font-black text-blue-800 dark:text-blue-400 leading-tight"><?= __('need_guide') ?></h4>
            <p class="text-xs text-slate-550 dark:text-slate-400 font-semibold px-4 leading-relaxed"><?= __('consult_tutorials') ?></p>
            <a href="/guide"
                class="inline-block bg-white dark:bg-slate-900 hover:bg-primary dark:hover:bg-primary hover:text-white text-primary dark:text-blue-400 font-black px-6 py-3.5 rounded-xl shadow-md text-xs uppercase tracking-wider transition-colors border border-slate-200/40 dark:border-slate-800/80">
                <?= __('view_guides') ?>
            </a>
        </div>
    </main>

    <script>lucide.createIcons();</script>
</body>

</html>