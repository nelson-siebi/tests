<nav
    class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 px-6 py-3 md:hidden z-50 flex justify-between items-center pb-safe">
    <a href="/admin"
        class="flex flex-col items-center space-y-1 text-slate-400 hover:text-primary <?= $_SERVER['REQUEST_URI'] === '/admin' ? 'text-primary' : '' ?>">
        <i data-lucide="layout-dashboard" class="w-6 h-6"></i>
        <span class="text-[10px] font-bold">Accueil</span>
    </a>
    <a href="/admin/recharges"
        class="flex flex-col items-center space-y-1 text-slate-400 hover:text-primary <?= strpos($_SERVER['REQUEST_URI'], '/admin/recharges') !== false ? 'text-primary' : '' ?>">
        <i data-lucide="wallet" class="w-6 h-6"></i>
        <span class="text-[10px] font-bold">Recharges</span>
    </a>
    <div class="relative">
        <a href="/admin/withdrawals"
            class="flex flex-col items-center space-y-1 text-slate-400 hover:text-primary <?= strpos($_SERVER['REQUEST_URI'], '/admin/withdrawals') !== false ? 'text-primary' : '' ?>">
            <div class="relative">
                <i data-lucide="arrow-down-circle" class="w-6 h-6"></i>
                <?php if (isset($pendingWithdrawals) && $pendingWithdrawals > 0): ?>
                    <span
                        class="absolute -top-1 -right-1 bg-danger text-white text-[9px] w-3.5 h-3.5 flex items-center justify-center rounded-full font-black border-2 border-white">
                        <?= $pendingWithdrawals ?>
                    </span>
                <?php endif; ?>
            </div>
            <span class="text-[10px] font-bold">Retraits</span>
        </a>
    </div>
    <a href="/admin/plans"
        class="flex flex-col items-center space-y-1 text-slate-400 hover:text-primary <?= strpos($_SERVER['REQUEST_URI'], '/admin/plans') !== false ? 'text-primary' : '' ?>">
        <i data-lucide="plus-square" class="w-6 h-6"></i>
        <span class="text-[10px] font-bold">Plans</span>
    </a>

    <div x-data="{ open: false }" class="relative">
        <button @click="open = !open" class="flex flex-col items-center space-y-1 text-slate-400 hover:text-primary">
            <i data-lucide="menu" class="w-6 h-6"></i>
            <span class="text-[10px] font-bold">Menu</span>
        </button>

        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-10" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-10"
            class="absolute bottom-full right-0 mb-4 w-48 bg-white rounded-2xl shadow-2xl border border-slate-100 p-2 space-y-1"
            style="display: none;">

            <a href="/admin/ads"
                class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-slate-50 text-slate-600 font-bold text-sm">
                <i data-lucide="play-circle" class="w-4 h-4"></i>
                <span>Publicités</span>
            </a>
            <a href="/admin/guides"
                class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-slate-50 text-slate-600 font-bold text-sm">
                <i data-lucide="book-open" class="w-4 h-4"></i>
                <span>Guides</span>
            </a>
            <a href="/admin/users"
                class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-slate-50 text-slate-600 font-bold text-sm">
                <i data-lucide="users" class="w-4 h-4"></i>
                <span>Utilisateurs</span>
            </a>
            <a href="/admin/moderation"
                class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-slate-50 text-slate-600 font-bold text-sm">
                <i data-lucide="message-square" class="w-4 h-4"></i>
                <span>Modération</span>
            </a>
            <a href="/admin/app-versions"
                class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-slate-50 text-slate-600 font-bold text-sm">
                <i data-lucide="smartphone" class="w-4 h-4"></i>
                <span>Gestion APK</span>
            </a>
            <div class="h-px bg-slate-100 my-1"></div>
            <a href="/dashboard"
                class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-blue-50 text-primary font-bold text-sm">
                <i data-lucide="layout-grid" class="w-4 h-4"></i>
                <span>Dashboard Utilisateur</span>
            </a>
            <a href="/logout"
                class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-red-50 text-red-500 font-bold text-sm">
                <i data-lucide="log-out" class="w-4 h-4"></i>
                <span>Déconnexion</span>
            </a>
        </div>
    </div>
</nav>

<!-- Mobile Header -->
<header
    class="md:hidden bg-white border-b border-slate-100 px-6 py-4 flex justify-between items-center sticky top-0 z-40">
    <div class="flex items-center space-x-3">
        <img src="/images/logo.png" alt="Logo" class="h-8 w-auto">
        <span class="font-black text-slate-900 tracking-tight text-lg">Admin</span>
    </div>
    <div class="flex items-center space-x-3">
        <div
            class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold text-xs">
            A
        </div>
    </div>
</header>