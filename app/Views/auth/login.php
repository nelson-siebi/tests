<!DOCTYPE html>
<html lang="fr">

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
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        primary: '#3b82f6',
                    },
                }
            }
        }
    </script>
    <style>
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
            animation: fadeIn 0.5s ease-out forwards;
        }
    </style>
</head>

<body class="bg-gray-50 text-slate-900 font-sans min-h-screen flex items-center justify-center p-6 relative">
    <!-- Language Switcher -->
    <div class="absolute top-6 right-6 flex bg-white p-1 rounded-lg shadow-sm border border-gray-100">
        <a href="/lang?l=en"
            class="px-3 py-1.5 rounded-md text-[10px] font-bold uppercase tracking-widest <?= \App\Core\Language::getCurrent() === 'en' ? 'bg-primary text-white' : 'text-slate-400' ?>">EN</a>
        <a href="/lang?l=fr"
            class="px-3 py-1.5 rounded-md text-[10px] font-bold uppercase tracking-widest <?= \App\Core\Language::getCurrent() === 'fr' ? 'bg-primary text-white' : 'text-slate-400' ?>">FR</a>
    </div>

    <div class="max-w-md w-full animate-fade-in">
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center">
                <img src="/images/logo.png" alt="Investian" class="h-12 w-auto">
            </a>
            <p class="text-slate-500 mt-3 font-medium text-sm"><?= __('login_title') ?></p>
        </div>

        <div class="bg-white p-8 md:p-10 rounded-2xl shadow-sm border border-gray-100">
            <h1 class="text-2xl font-bold mb-8 text-slate-900"><?= __('welcome_back') ?></h1>

            <?php if (isset($error)): ?>
                <div id="error-msg"
                    class="bg-red-50 border border-red-100 text-red-600 p-4 rounded-xl text-xs mb-8 flex items-center space-x-3">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    <span class="font-bold"><?= $error ?></span>
                </div>
            <?php endif; ?>

            <form action="/login" method="POST" class="space-y-5" x-data="{ loading: false }" @submit="loading = true">
                <div class="space-y-2">
                    <label
                        class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest px-1"><?= __('email_label') ?></label>
                    <div class="relative group">
                        <div
                            class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-primary transition-colors">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <input type="text" name="email" required
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-11 pr-4 py-3 text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm"
                            placeholder="<?= __('login_field_placeholder') ?>">
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between px-1">
                        <label
                            class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest"><?= __('password_label') ?></label>
                        <a href="#"
                            class="text-[10px] font-bold text-primary hover:underline uppercase tracking-widest"><?= __('forgot_password') ?></a>
                    </div>
                    <div class="relative group">
                        <div
                            class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-primary transition-colors">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                        <input type="password" name="password" required
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-11 pr-4 py-3 text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm"
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center px-1">
                    <input type="checkbox" id="remember"
                        class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                    <label for="remember"
                        class="ml-2.5 text-xs text-slate-500 font-medium"><?= __('remember_me') ?></label>
                </div>

                <button type="submit" :disabled="loading"
                    class="w-full bg-primary hover:bg-blue-600 text-white font-bold py-4 rounded-xl transition-all shadow-md shadow-primary/20 active:scale-95 text-sm flex items-center justify-center space-x-2">
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

            <div class="mt-8 text-center border-t border-gray-50 pt-8">
                <p class="text-slate-400 font-medium text-xs"><?= __('no_account') ?> <a href="/register"
                        class="text-primary font-bold hover:underline ml-1"><?= __('create_account') ?></a></p>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
        window.addEventListener('load', () => {
            const errorMsg = document.getElementById('error-msg');
            const successMsg = document.getElementById('success-msg');
            const msg = errorMsg || successMsg;
            if (msg) {
                msg.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    </script>
</body>

</html>