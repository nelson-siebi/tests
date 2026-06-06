<aside class="hidden md:flex w-72 bg-white border-r border-slate-200 flex-col fixed h-full z-40">
    <div class="p-8 border-b border-slate-100 flex justify-center">
        <a href="/admin" class="hover:opacity-80 transition-opacity flex justify-center">
            <img src="/images/logo.png" alt="Investian Admin" class="h-10 w-auto">
        </a>
    </div>
    <nav class="flex-1 p-6 space-y-2 overflow-y-auto">
        <?php $currentPath = $_SERVER['REQUEST_URI']; ?>

        <a href="/admin"
            class="flex items-center space-x-3 px-4 py-3.5 rounded-2xl transition-all font-bold <?= $currentPath === '/admin' ? 'bg-primary/10 text-primary shadow-sm' : 'text-slate-500 hover:bg-slate-50 hover:text-primary' ?>">
            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
            <span>Tableau de Bord</span>
        </a>

        <a href="/admin/recharges"
            class="flex items-center justify-between px-4 py-3.5 rounded-2xl transition-all font-semibold <?= strpos($currentPath, '/admin/recharges') !== false ? 'bg-primary/10 text-primary shadow-sm' : 'text-slate-500 hover:bg-slate-50 hover:text-primary' ?>">
            <div class="flex items-center space-x-3">
                <i data-lucide="wallet" class="w-5 h-5"></i>
                <span>Recharges</span>
            </div>
            <?php if (isset($pendingRecharges) && $pendingRecharges > 0): ?>
                <span class="bg-danger text-white text-[10px] px-2 py-0.5 rounded-full font-black">
                    <?= $pendingRecharges ?>
                </span>
            <?php endif; ?>
        </a>

        <a href="/admin/withdrawals"
            class="flex items-center justify-between px-4 py-3.5 rounded-2xl transition-all font-semibold <?= strpos($currentPath, '/admin/withdrawals') !== false ? 'bg-primary/10 text-primary shadow-sm' : 'text-slate-500 hover:bg-slate-50 hover:text-primary' ?>">
            <div class="flex items-center space-x-3">
                <i data-lucide="arrow-down-circle" class="w-5 h-5"></i>
                <span>Retraits</span>
            </div>
            <?php if (isset($pendingWithdrawals) && $pendingWithdrawals > 0): ?>
                <span class="bg-danger text-white text-[10px] px-2 py-0.5 rounded-full font-black">
                    <?= $pendingWithdrawals ?>
                </span>
            <?php endif; ?>
        </a>

        <a href="/admin/users"
            class="flex items-center space-x-3 px-4 py-3.5 rounded-2xl transition-all font-semibold <?= strpos($currentPath, '/admin/users') !== false ? 'bg-primary/10 text-primary shadow-sm' : 'text-slate-500 hover:bg-slate-50 hover:text-primary' ?>">
            <i data-lucide="users" class="w-5 h-5"></i>
            <span>Utilisateurs</span>
        </a>

        <a href="/admin/plans"
            class="flex items-center space-x-3 px-4 py-3.5 rounded-2xl transition-all font-semibold <?= strpos($currentPath, '/admin/plans') !== false ? 'bg-primary/10 text-primary shadow-sm' : 'text-slate-500 hover:bg-slate-50 hover:text-primary' ?>">
            <i data-lucide="plus-square" class="w-5 h-5"></i>
            <span>Gestion des Plans</span>
        </a>

        <a href="/admin/ads"
            class="flex items-center space-x-3 px-4 py-3.5 rounded-2xl transition-all font-semibold <?= strpos($currentPath, '/admin/ads') !== false ? 'bg-primary/10 text-primary shadow-sm' : 'text-slate-500 hover:bg-slate-50 hover:text-primary' ?>">
            <i data-lucide="play-circle" class="w-5 h-5"></i>
            <span>Gestion des Pubs</span>
        </a>

        <a href="/admin/guides"
            class="flex items-center space-x-3 px-4 py-3.5 rounded-2xl transition-all font-semibold <?= strpos($currentPath, '/admin/guides') !== false ? 'bg-primary/10 text-primary shadow-sm' : 'text-slate-500 hover:bg-slate-50 hover:text-primary' ?>">
            <i data-lucide="book-open" class="w-5 h-5"></i>
            <span>Guides Tutoriels</span>
        </a>

        <a href="/admin/moderation"
            class="flex items-center space-x-3 px-4 py-3.5 rounded-2xl transition-all font-semibold <?= strpos($currentPath, '/admin/moderation') !== false ? 'bg-primary/10 text-primary shadow-sm' : 'text-slate-500 hover:bg-slate-50 hover:text-primary' ?>">
            <i data-lucide="message-square" class="w-5 h-5"></i>
            <span>Modération</span>
        </a>

        <a href="/admin/app-versions"
            class="flex items-center space-x-3 px-4 py-3.5 rounded-2xl transition-all font-semibold <?= strpos($currentPath, '/admin/app-versions') !== false ? 'bg-primary/10 text-primary shadow-sm' : 'text-slate-500 hover:bg-slate-50 hover:text-primary' ?>">
            <i data-lucide="smartphone" class="w-5 h-5"></i>
            <span>Gestion APK</span>
        </a>

        <div class="pt-6 border-t border-slate-100 mt-6">
            <a href="/dashboard"
                class="flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-400 hover:text-slate-900 transition-all font-semibold">
                <i data-lucide="eye" class="w-5 h-5"></i>
                <span>Vue Utilisateur</span>
            </a>
            <a href="/logout"
                class="flex items-center space-x-3 px-4 py-3 rounded-xl text-danger hover:bg-danger/5 transition-all font-bold">
                <i data-lucide="log-out" class="w-5 h-5"></i>
                <span>Déconnexion</span>
            </a>
        </div>
    </nav>
</aside>