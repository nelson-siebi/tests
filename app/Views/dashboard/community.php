<!DOCTYPE html>
<html lang="fr" class="scroll-smooth" x-data="{ darkMode: false }" :class="{ 'dark': darkMode }">

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
            animation: fadeIn 0.3s ease-out forwards;
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
    class="theme-transition bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 font-sans antialiased min-h-screen relative overflow-x-hidden flex flex-col"
>
    <!-- Background overlay patterns -->
    <div class="absolute inset-0 bg-grid pointer-events-none -z-30"></div>
    <div class="absolute inset-0 overflow-hidden pointer-events-none -z-20">
        <div class="absolute top-[-10%] right-[-10%] w-[50vw] h-[50vw] max-w-[600px] bg-primary/10 dark:bg-primary/5 rounded-full blur-[120px] animate-pulse"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[50vw] h-[50vw] max-w-[600px] bg-emerald-500/10 dark:bg-emerald-500/5 rounded-full blur-[120px] animate-pulse" style="animation-delay: 3s"></div>
    </div>

    <!-- Top Navbar -->
    <nav class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200/50 dark:border-slate-800/80 sticky top-0 z-50 py-3.5 px-6 flex justify-between items-center shadow-sm shrink-0 animate-fade-in">
        <div class="flex items-center space-x-3">
            <a href="/dashboard"
                class="bg-primary hover:bg-blue-600 text-white p-2 rounded-xl transition-all shadow-md flex items-center justify-center">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <h1 class="text-lg font-black text-slate-900 dark:text-white"><?= __('community_title') ?></h1>
            <a href="/guide" class="text-primary dark:text-blue-400 hover:underline flex items-center space-x-1 text-[10px] font-black uppercase tracking-wider pl-2">
                <i data-lucide="help-circle" class="w-3.5 h-3.5"></i>
                <span>Aide</span>
            </a>
        </div>

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
                    class="p-2.5 bg-amber-50 hover:bg-amber-100 dark:bg-amber-500/10 dark:hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 rounded-xl transition-all flex items-center justify-center animate-fade-in"
                    title="<?= __('admin_panel') ?>">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Main Message Feed -->
    <main class="flex-1 p-4 md:p-6 max-w-2xl mx-auto w-full flex flex-col pb-28">
        <?php if (!$canPost): ?>
            <div class="bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 p-4 rounded-2xl text-xs font-black uppercase tracking-wider mb-6 flex items-center space-x-2 shrink-0 animate-fade-in">
                <i data-lucide="lock" class="w-4 h-4 shrink-0"></i>
                <span><?= __('only_investors_post') ?></span>
            </div>
        <?php endif; ?>

        <!-- Messages List -->
        <div id="messages-container" class="space-y-4 flex-grow">
            <!-- Loading state -->
            <div class="text-center py-10" id="loading">
                <i data-lucide="loader-2" class="w-8 h-8 animate-spin mx-auto text-primary"></i>
            </div>
        </div>
    </main>

    <!-- Post Form (Pinned Bottom) -->
    <?php if ($canPost): ?>
        <div class="fixed bottom-0 left-0 right-0 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-t border-slate-200/50 dark:border-slate-800/80 p-4 pb-8 z-40">
            <form id="postForm" class="max-w-2xl mx-auto flex gap-3">
                <input type="text" name="message" id="messageInput" required
                    class="flex-1 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3.5 text-sm font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all placeholder-slate-400 dark:placeholder-slate-700"
                    placeholder="<?= __('share_opinion') ?>">
                <button type="submit"
                    class="bg-primary hover:bg-blue-600 text-white p-4.5 rounded-2xl transition-all active:scale-[0.96] shadow-lg shadow-primary/20 flex items-center justify-center">
                    <i data-lucide="send" class="w-5 h-5"></i>
                </button>
            </form>
        </div>
    <?php endif; ?>

    <script>
        const userId = <?= json_encode($user['id'] ?? null) ?>;
        const container = document.getElementById('messages-container');
        const loading = document.getElementById('loading');
        let lastFetch = 0;

        function renderMessage(msg) {
            const isMe = msg.user_id == userId;
            const isPending = msg.status === 'pending';

            return `
                <div class="flex ${isMe ? 'justify-end' : 'justify-start'} animate-fade-in">
                    <div class="max-w-[80%] ${isMe ? 'bg-primary text-white rounded-br-none shadow-primary/15' : 'bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 border border-slate-200/50 dark:border-slate-800/80 rounded-bl-none'} p-4.5 rounded-3xl shadow-md relative">
                        <div class="flex justify-between items-center mb-1 gap-4">
                            <span class="text-[9px] font-black uppercase tracking-wider ${isMe ? 'text-blue-150' : 'text-slate-400 dark:text-slate-500'}">
                                ${msg.user_name}
                            </span>
                            <div class="flex items-center space-x-0.5">
                                ${isPending ? `
                                    <i data-lucide="check" class="w-3 h-3 ${isMe ? 'text-blue-200' : 'text-slate-300 dark:text-slate-700'}"></i>
                                ` : `
                                    <i data-lucide="check-check" class="w-3 h-3 ${isMe ? 'text-blue-100' : 'text-emerald-500'}"></i>
                                `}
                            </div>
                        </div>
                        <p class="text-sm font-semibold leading-relaxed">${escapeHtml(msg.message)}</p>
                        <span class="text-[9px] ${isMe ? 'text-blue-200' : 'text-slate-400 dark:text-slate-500'} block text-right mt-1.5 font-bold uppercase">
                            ${new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                        </span>
                    </div>
                </div>
            `;
        }

        function escapeHtml(text) {
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        async function fetchMessages() {
            try {
                const response = await fetch('/community/fetch');
                const messages = await response.json();

                if (loading) loading.style.display = 'none';

                container.innerHTML = messages.map(renderMessage).join('');
                lucide.createIcons();

            } catch (e) {
                console.error("Polling error", e);
            }
        }

        const postForm = document.getElementById('postForm');
        if (postForm) {
            postForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const input = document.getElementById('messageInput');
                const message = input.value;
                if (!message.trim()) return;

                const formData = new FormData();
                formData.append('message', message);

                try {
                    const res = await fetch('/community/post', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await res.json();

                    if (res.ok && data.success) {
                        input.value = '';
                        fetchMessages(); 
                    } else {
                        throw new Error(data.error || <?= json_encode(__('error_sending')) ?>);
                    }
                } catch (e) {
                    alert(e.message);
                }
            });
        }

        fetchMessages();
        setInterval(fetchMessages, 3000);
        lucide.createIcons();
    </script>
</body>

</html>