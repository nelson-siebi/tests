<!DOCTYPE html>
<html lang="fr" class="scroll-smooth" x-data="{ darkMode: false }" :class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Investian</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Google Fonts -->
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
                        },
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
    class="theme-transition bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 font-sans antialiased min-h-screen flex items-center justify-center p-6 relative overflow-x-hidden overflow-y-auto"
>

    <!-- Grid overlay -->
    <div class="absolute inset-0 bg-grid pointer-events-none -z-30"></div>

    <!-- Glowing Background Blobs -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none -z-20">
        <div class="absolute top-[-10%] right-[-10%] w-[50vw] h-[50vw] max-w-[600px] bg-primary/10 dark:bg-primary/5 rounded-full blur-[120px] animate-pulse"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[50vw] h-[50vw] max-w-[600px] bg-emerald-500/10 dark:bg-emerald-500/5 rounded-full blur-[120px] animate-pulse" style="animation-delay: 3s"></div>
    </div>

    <!-- Language & Theme Switcher (Top Right) -->
    <div class="absolute top-6 right-6 flex items-center space-x-4">
        <!-- Theme Toggle -->
        <button @click="toggleTheme()" class="text-slate-500 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-900 rounded-xl p-2.5 transition-all focus:outline-none" aria-label="Toggle theme">
            <i data-lucide="sun" x-show="darkMode" class="w-5 h-5"></i>
            <i data-lucide="moon" x-show="!darkMode" class="w-5 h-5"></i>
        </button>

        <!-- Language Switcher -->
        <div class="flex bg-white dark:bg-slate-900 p-1 rounded-xl shadow-sm border border-slate-200/50 dark:border-slate-800">
            <a href="/lang?l=en"
                class="px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-widest transition-all <?= \App\Core\Language::getCurrent() === 'en' ? 'bg-primary text-white' : 'text-slate-400 dark:text-slate-500' ?>">EN</a>
            <a href="/lang?l=fr"
                class="px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-widest transition-all <?= \App\Core\Language::getCurrent() === 'fr' ? 'bg-primary text-white' : 'text-slate-400 dark:text-slate-500' ?>">FR</a>
        </div>
    </div>

    <!-- Main Container -->
    <div class="max-w-md w-full relative z-10 py-12">
        <!-- Branding Header -->
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center">
                <img src="/images/logo.png" alt="Investian Logo" class="h-12 w-auto">
            </a>
            <p class="text-slate-500 dark:text-slate-400 mt-3 font-semibold text-sm"><?= __('login_title') ?></p>
        </div>

        <!-- Login Card -->
        <div class="bg-white/80 dark:bg-slate-900/60 backdrop-blur-md p-8 md:p-10 rounded-3xl shadow-xl border border-slate-200/50 dark:border-slate-800/80">
            <h1 class="text-2xl font-black mb-8 text-slate-900 dark:text-white"><?= __('welcome_back') ?></h1>

            <!-- Error message dynamic alert -->
            <?php if (isset($error)): ?>
                <div id="error-msg"
                    class="bg-red-500/10 dark:bg-red-500/5 border border-red-500/20 text-red-600 dark:text-red-400 p-4 rounded-xl text-xs mb-8 flex items-center space-x-3">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <span class="font-extrabold"><?= $error ?></span>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form action="/login" method="POST" class="space-y-5" x-data="{ loading: false, showPass: false }" @submit="loading = true">
                <!-- Identifier Field -->
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest px-1">
                        <?= __('email_label') ?>
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 dark:text-slate-500 group-focus-within:text-primary transition-colors">
                            <i data-lucide="mail" class="w-4.5 h-4.5"></i>
                        </div>
                        <input type="text" name="email" required
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200/50 dark:border-slate-800 rounded-2xl pl-11 pr-4 py-3.5 text-slate-900 dark:text-white font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 dark:focus:ring-primary/20 focus:border-primary dark:focus:border-primary transition-all text-sm placeholder-slate-400 dark:placeholder-slate-600"
                            placeholder="<?= __('login_field_placeholder') ?>">
                    </div>
                </div>

                <!-- Password Field -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between px-1">
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                            <?= __('password_label') ?>
                        </label>
                        <a href="#" class="text-[10px] font-black text-primary hover:text-primary-hover uppercase tracking-widest">
                            <?= __('forgot_password') ?>
                        </a>
                    </div>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 dark:text-slate-500 group-focus-within:text-primary transition-colors">
                            <i data-lucide="lock" class="w-4.5 h-4.5"></i>
                        </div>
                        <input :type="showPass ? 'text' : 'password'" name="password" required
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200/50 dark:border-slate-800 rounded-2xl pl-11 pr-11 py-3.5 text-slate-900 dark:text-white font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 dark:focus:ring-primary/20 focus:border-primary dark:focus:border-primary transition-all text-sm placeholder-slate-400 dark:placeholder-slate-600"
                            placeholder="••••••••">
                        <!-- Eye Toggle Button -->
                        <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 dark:text-slate-500 hover:text-primary transition-colors">
                            <i data-lucide="eye" x-show="!showPass" class="w-4.5 h-4.5"></i>
                            <i data-lucide="eye-off" x-show="showPass" class="w-4.5 h-4.5" x-cloak></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center px-1">
                    <input type="checkbox" id="remember"
                        class="w-4 h-4 text-primary border-slate-200 dark:border-slate-800 rounded focus:ring-primary/20 bg-slate-50 dark:bg-slate-950">
                    <label for="remember" class="ml-2.5 text-xs text-slate-500 dark:text-slate-400 font-bold cursor-pointer select-none">
                        <?= __('remember_me') ?>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" :disabled="loading"
                    class="w-full bg-gradient-to-r from-primary to-blue-600 hover:opacity-95 text-white font-extrabold py-4 rounded-2xl transition-all shadow-md shadow-primary/25 active:scale-95 text-sm flex items-center justify-center space-x-2 disabled:opacity-50">
                    <template x-if="loading">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </template>
                    <span x-text="loading ? 'Connexion en cours...' : '<?= __('login_btn') ?>'"></span>
                </button>
            </form>

            <!-- Bottom Redirect -->
            <div class="mt-8 text-center border-t border-slate-100 dark:border-slate-800 pt-8">
                <p class="text-slate-400 dark:text-slate-500 font-bold text-xs">
                    <?= __('no_account') ?> 
                    <a href="/register" class="text-primary hover:text-primary-hover font-black ml-1">
                        <?= __('create_account') ?>
                    </a>
                </p>
            </div>
        </div>

        <!-- Back to Home -->
        <div class="mt-6 text-center">
            <a href="/" class="inline-flex items-center text-xs font-bold text-slate-400 dark:text-slate-500 hover:text-primary transition-colors">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5 mr-2"></i>
                Retour à l'accueil
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            
            const errorMsg = document.getElementById('error-msg');
            if (errorMsg) {
                errorMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    </script>
</body>

</html>