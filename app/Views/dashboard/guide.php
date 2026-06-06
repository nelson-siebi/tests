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

    <main class="max-w-6xl mx-auto p-6 md:p-12 space-y-12 animate-fade-in">
        <div class="text-center max-w-2xl mx-auto space-y-4">
            <h1 class="text-4xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tight">
                <?= __('guide_title') ?> 📚
            </h1>
            <p class="text-slate-500 dark:text-slate-400 font-bold italic text-lg"><?= __('guide_desc') ?></p>
        </div>

        <!-- Guides Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($guides as $index => $guide): ?>
                <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] overflow-hidden shadow-xl border border-slate-200/50 dark:border-slate-800/80 animate-fade-in group hover:border-primary/45 transition-all flex flex-col justify-between"
                    style="animation-delay: <?= $index * 0.1 ?>s">
                    
                    <div class="h-48 bg-slate-100 dark:bg-slate-950 relative overflow-hidden shrink-0">
                        <?php if ($guide['image_url']): ?>
                            <img src="<?= $guide['image_url'] ?>" alt="<?= htmlspecialchars($guide['title']) ?>"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 opacity-90 dark:opacity-80">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-slate-300 dark:text-slate-700">
                                <i data-lucide="book-open" class="w-12 h-12"></i>
                            </div>
                        <?php endif; ?>
                        <div class="absolute top-4 left-4">
                            <span class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-md px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-widest text-primary dark:text-blue-400 shadow-sm border border-slate-100 dark:border-slate-800">
                                <?= __('guide_prefix') ?> <?= $index + 1 ?>
                            </span>
                        </div>
                    </div>

                    <div class="p-8 flex-1 flex flex-col justify-between">
                        <div class="space-y-3 mb-6">
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white leading-tight"><?= htmlspecialchars($guide['title']) ?></h3>
                            <p class="text-slate-450 dark:text-slate-500 text-sm font-semibold line-clamp-3 italic">
                                <?= htmlspecialchars($guide['description']) ?>
                            </p>
                        </div>

                        <div x-data="{ open: false }">
                            <button @click="open = true"
                                class="w-full bg-slate-50 hover:bg-primary hover:text-white dark:bg-slate-950 dark:hover:bg-primary dark:text-slate-200 font-black py-4 rounded-2xl transition-all active:scale-[0.97] text-xs uppercase tracking-wider border border-slate-200/50 dark:border-slate-800/80">
                                <?= __('view_steps') ?>
                            </button>

                            <!-- Modal steps dialog -->
                            <template x-teleport="body">
                                <div x-show="open"
                                    class="fixed inset-0 z-[100] flex items-center justify-center p-4 md:p-12" x-cloak>
                                    <!-- Modal backdrop -->
                                    <div x-show="open" x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                        class="absolute inset-0 bg-slate-950/60 backdrop-blur-md" @click="open = false">
                                    </div>

                                    <!-- Modal window -->
                                    <div x-show="open" x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        class="bg-white dark:bg-slate-900 w-full max-w-3xl rounded-[3rem] shadow-2xl relative z-10 overflow-hidden max-h-[90vh] flex flex-col border border-slate-200/50 dark:border-slate-800/85">

                                        <!-- Header Image & title -->
                                        <div class="h-44 shrink-0 bg-primary relative overflow-hidden">
                                            <?php if ($guide['image_url']): ?>
                                                <img src="<?= $guide['image_url'] ?>"
                                                    class="w-full h-full object-cover opacity-60 dark:opacity-40">
                                            <?php endif; ?>
                                            <div class="absolute inset-0 bg-gradient-to-t from-primary/90 to-transparent"></div>
                                            <div class="absolute bottom-6 left-8 right-8">
                                                <h2 class="text-3xl font-black text-white leading-tight">
                                                    <?= htmlspecialchars($guide['title']) ?>
                                                </h2>
                                            </div>
                                            <button @click="open = false"
                                                class="absolute top-6 right-6 bg-white/20 backdrop-blur-md hover:bg-white/45 p-2.5 rounded-full text-white transition-colors">
                                                <i data-lucide="x" class="w-5 h-5"></i>
                                            </button>
                                        </div>

                                        <!-- Content details -->
                                        <div class="p-8 md:p-12 overflow-y-auto space-y-12">
                                            <!-- Introduction -->
                                            <div class="prose prose-slate dark:prose-invert max-w-none text-slate-600 dark:text-slate-350 font-medium text-lg italic leading-relaxed">
                                                <?= nl2br(htmlspecialchars($guide['content'])) ?>
                                            </div>

                                            <!-- Steps sequence -->
                                            <div class="space-y-12 border-l border-slate-100 dark:border-slate-800/80 ml-6 pl-8 relative">
                                                <?php foreach ($guide['steps'] as $stepIndex => $step): ?>
                                                    <div class="relative space-y-4">
                                                        <!-- Step Number Circle -->
                                                        <div class="absolute left-[-3.5rem] top-0 w-12 h-12 bg-white dark:bg-slate-950 border-2 border-primary rounded-2xl flex items-center justify-center font-black text-primary dark:text-blue-400 shadow-sm z-10">
                                                            <?= $stepIndex + 1 ?>
                                                        </div>
                                                        
                                                        <h4 class="text-xl font-black text-slate-900 dark:text-white">
                                                            <?= htmlspecialchars($step['title'] ?: __('step_prefix') . ($stepIndex + 1)) ?>
                                                        </h4>

                                                        <div class="bg-slate-50 dark:bg-slate-950 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-850/80">
                                                            <p class="text-slate-600 dark:text-slate-300 font-semibold leading-relaxed italic text-sm">
                                                                <?= nl2br(htmlspecialchars($step['content'])) ?>
                                                            </p>
                                                        </div>

                                                        <?php if ($step['media_url']): ?>
                                                            <div class="rounded-3xl overflow-hidden border border-slate-200/50 dark:border-slate-800/80 shadow-lg shrink-0">
                                                                <?php if ($step['media_type'] === 'video'): ?>
                                                                    <div class="aspect-video bg-black flex items-center justify-center">
                                                                        <?php if (strpos($step['media_url'], 'http') === 0 && (strpos($step['media_url'], 'youtube') !== false || strpos($step['media_url'], 'vimeo') !== false)): ?>
                                                                            <iframe src="<?= $step['media_url'] ?>"
                                                                                class="w-full h-full" frameborder="0"
                                                                                allowfullscreen></iframe>
                                                                        <?php else: ?>
                                                                            <video controls class="w-full h-full">
                                                                                <source src="<?= $step['media_url'] ?>"
                                                                                    type="video/<?= pathinfo($step['media_url'], PATHINFO_EXTENSION) ?>">
                                                                                Votre navigateur ne supporte pas la lecture de vidéos.
                                                                            </video>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <img src="<?= $step['media_url'] ?>"
                                                                        class="w-full object-cover max-h-[350px]">
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>

                                        <!-- Footer controls -->
                                        <div class="p-6 bg-slate-50 dark:bg-slate-950/80 border-t border-slate-150 dark:border-slate-800/80 shrink-0 text-center">
                                            <button @click="open = false"
                                                class="bg-primary hover:bg-blue-600 text-white font-black px-12 py-4 rounded-2xl shadow-lg shadow-primary/20 transition-all active:scale-[0.97] uppercase tracking-wider text-xs">
                                                <?= __('finished_reading') ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="pt-10 text-center animate-fade-in" style="animation-delay: 0.5s">
            <a href="/recharge"
                class="inline-flex items-center space-x-4 bg-primary hover:bg-blue-600 text-white font-black px-12 py-5 rounded-[2.5rem] transition-all shadow-xl shadow-primary/25 hover:shadow-primary/35 hover:-translate-y-0.5 active:scale-95 text-base uppercase tracking-wider">
                <span><?= __('get_started') ?></span>
                <i data-lucide="sparkles" class="w-5 h-5 shrink-0"></i>
            </a>
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

    <script>
        lucide.createIcons();
    </script>
    <?php include __DIR__ . '/../partials/whatsapp.php'; ?>
</body>

</html>