<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $title ?> - Investian
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: { primary: '#3b82f6' }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-50 text-slate-800 font-sans min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50 py-3 px-6 flex justify-between items-center">
        <div class="flex items-center space-x-3">
            <a href="/dashboard" class="bg-gray-100 p-2 rounded-xl text-slate-600 hover:bg-gray-200 transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <h1 class="text-lg font-bold text-slate-900"><?= __('community_title') ?></h1>
            <a href="/guide" class="text-primary hover:underline flex items-center space-x-1 text-[10px] font-bold">
                <i data-lucide="help-circle" class="w-3 h-3"></i>
                <span>Aide</span>
            </a>
        </div>

        <div class="flex items-center space-x-3">
            <div class="flex bg-gray-100 p-1 rounded-lg">
                <a href="/lang?l=en"
                    class="px-3 py-1 rounded-md text-xs font-bold uppercase <?= \App\Core\Language::getCurrent() === 'en' ? 'bg-white text-primary shadow-sm' : 'text-slate-400' ?>">EN</a>
                <a href="/lang?l=fr"
                    class="px-3 py-1 rounded-md text-xs font-bold uppercase <?= \App\Core\Language::getCurrent() === 'fr' ? 'bg-white text-primary shadow-sm' : 'text-slate-400' ?>">FR</a>
            </div>

            <?php if (\App\Core\Session::get('user_role') === 'admin'): ?>
                <a href="/admin"
                    class="p-2 bg-amber-50 text-amber-600 rounded-lg hover:text-amber-700 hover:bg-amber-100 transition-all flex items-center justify-center"
                    title="<?= __('admin_panel') ?>">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <main class="flex-1 p-4 md:p-6 max-w-2xl mx-auto w-full flex flex-col pb-24">

        <?php if (!$canPost): ?>
            <div
                class="bg-amber-50 border border-amber-100 text-amber-800 p-4 rounded-xl text-xs font-bold mb-6 flex items-center space-x-2">
                <i data-lucide="lock" class="w-4 h-4"></i>
                <span><?= __('only_investors_post') ?></span>
            </div>
        <?php endif; ?>

        <!-- Messages List -->
        <div id="messages-container" class="space-y-4 flex-1">
            <!-- Loading state -->
            <div class="text-center py-10" id="loading">
                <i data-lucide="loader-2" class="w-8 h-8 animate-spin mx-auto text-primary"></i>
            </div>
        </div>

    </main>

    <!-- Post Form -->
    <?php if ($canPost): ?>
        <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 p-4 pb-8 z-40">
            <form id="postForm" class="max-w-2xl mx-auto flex gap-3">
                <input type="text" name="message" id="messageInput" required
                    class="flex-1 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold focus:outline-none focus:border-primary transition-all"
                    placeholder="<?= __('share_opinion') ?>">
                <button type="submit"
                    class="bg-primary hover:bg-blue-600 text-white p-3 rounded-xl transition-all active:scale-95 shadow-lg shadow-primary/20">
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
                    <div class="max-w-[80%] ${isMe ? 'bg-primary text-white rounded-br-none' : 'bg-white border border-gray-100 rounded-bl-none'} p-4 rounded-2xl shadow-sm relative">
                        <div class="flex justify-between items-center mb-1 gap-4">
                            <span class="text-[10px] font-black uppercase tracking-widest ${isMe ? 'text-blue-100' : 'text-slate-400'}">
                                ${msg.user_name}
                            </span>
                            <div class="flex items-center space-x-0.5">
                                ${isPending ? `
                                    <i data-lucide="check" class="w-3 h-3 ${isMe ? 'text-blue-200' : 'text-slate-300'}"></i>
                                ` : `
                                    <i data-lucide="check-check" class="w-3 h-3 ${isMe ? 'text-blue-100' : 'text-emerald-500'}"></i>
                                `}
                            </div>
                        </div>
                        <p class="text-sm font-medium leading-relaxed">${escapeHtml(msg.message)}</p>
                        <span class="text-[9px] ${isMe ? 'text-blue-100' : 'text-slate-300'} block text-right mt-1">
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

                // Simple render for now - clear and rebuild
                // In production, you'd optimize diffing
                container.innerHTML = messages.map(renderMessage).join('');
                lucide.createIcons();

            } catch (e) {
                console.error("Polling error", e);
            }
        }

        // Post Message
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
                        fetchMessages(); // Refresh immediately
                    } else {
                        throw new Error(data.error || <?= json_encode(__('error_sending')) ?>);
                    }
                } catch (e) {
                    alert(e.message);
                }
            });
        }

        // Poll every 3 seconds
        fetchMessages();
        setInterval(fetchMessages, 3000);

        lucide.createIcons();
    </script>
    <style>
        .animate-fade-in {
            animation: fadeIn 0.3s ease-out forwards;
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
    </style>
</body>

</html>