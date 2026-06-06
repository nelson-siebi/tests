<!DOCTYPE html>
<html lang="<?= \App\Core\Language::getCurrent() ?>">

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
                    colors: { primary: '#3b82f6', success: '#10b981', danger: '#ef4444' }
                }
            }
        }
    </script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        .glass {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 font-sans min-h-screen">
    <!-- Navbar -->
    <nav
        class="glass sticky top-0 z-50 border-b border-slate-200 py-4 px-6 md:px-12 flex justify-between items-center shadow-sm">
        <div class="flex items-center space-x-3">
            <a href="/dashboard"
                class="bg-primary p-2 rounded-xl text-white shadow-lg shadow-primary/20 hover:scale-105 transition-transform flex items-center justify-center">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <img src="/images/logo.png" alt="Investian" class="h-10 w-auto">
        </div>

        <div class="flex items-center space-x-3">
            <!-- Community Icon -->
            <a href="/community"
                class="p-2 bg-slate-100 text-slate-600 rounded-lg hover:text-primary hover:bg-blue-50 transition-all relative">
                <i data-lucide="message-circle" class="w-5 h-5"></i>
                <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full border border-white"></span>
            </a>

            <?php if (\App\Core\Session::get('user_role') === 'admin'): ?>
                <a href="/admin"
                    class="p-2 bg-amber-50 text-amber-600 rounded-lg hover:text-amber-700 hover:bg-amber-100 transition-all flex items-center justify-center"
                    title="<?= __('admin_panel') ?>">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </a>
            <?php endif; ?>

            <div class="flex bg-slate-100 p-1 rounded-xl">
                <a href="/lang?l=en"
                    class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest <?= \App\Core\Language::getCurrent() === 'en' ? 'bg-white text-primary shadow-sm' : 'text-slate-400' ?>">EN</a>
                <a href="/lang?l=fr"
                    class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest <?= \App\Core\Language::getCurrent() === 'fr' ? 'bg-white text-primary shadow-sm' : 'text-slate-400' ?>">FR</a>
            </div>

            <!-- Profile Dropdown -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.away="open = false"
                    class="text-primary p-2 bg-blue-50 rounded-lg border border-blue-100 transition-all hover:bg-blue-100">
                    <i data-lucide="user" class="w-5 h-5"></i>
                </button>
                <div x-show="open" x-transition x-cloak
                    class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50">
                    <a href="/profile"
                        class="flex items-center space-x-2 px-4 py-2 text-sm text-slate-600 hover:bg-gray-50 hover:text-primary transition-colors">
                        <i data-lucide="user" class="w-4 h-4"></i>
                        <span class="font-bold">
                            <?= __('profile') ?>
                        </span>
                    </a>
                    <?php if (\App\Core\Session::get('user_role') === 'admin'): ?>
                        <a href="/admin"
                            class="flex items-center space-x-2 px-4 py-2 text-sm text-slate-600 hover:bg-gray-50 hover:text-primary transition-colors">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                            <span class="font-bold"><?= __('admin_panel') ?></span>
                        </a>
                    <?php endif; ?>
                    <hr class="my-1 border-gray-50">
                    <a href="/logout"
                        class="flex items-center space-x-2 px-4 py-2 text-sm text-red-500 hover:bg-red-50 transition-colors">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        <span class="font-bold">
                            <?= __('logout') ?>
                        </span>
                    </a>
                </div>
            </div>
    </nav>

    <main class="max-w-4xl mx-auto p-6 md:p-12">
        <div class="mb-10">
            <h1 class="text-3xl font-black text-slate-900 mb-2">
                <?= $title ?>
            </h1>
            <p class="text-slate-500 font-medium italic"><?= __('withdraw_desc') ?></p>
            <a href="/guide"
                class="inline-flex items-center space-x-2 text-primary font-bold text-xs mt-3 hover:underline">
                <i data-lucide="help-circle" class="w-4 h-4"></i>
                <span><?= __('need_guide') ?></span>
            </a>
        </div>

        <?php if (\App\Core\Session::has('success')): ?>
            <div
                class="bg-emerald-50 border border-emerald-100 text-emerald-600 p-6 rounded-3xl font-bold mb-8 animate-fade-in flex items-center space-x-3">
                <i data-lucide="check-circle" class="w-6 h-6 text-emerald-500"></i>
                <span>
                    <?= \App\Core\Session::get('success');
                    \App\Core\Session::remove('success'); ?>
                </span>
            </div>
        <?php endif; ?>

        <?php if (\App\Core\Session::has('error')): ?>
            <div
                class="bg-red-50 border border-red-100 text-red-600 p-6 rounded-3xl font-bold mb-8 animate-fade-in flex items-center space-x-3">
                <i data-lucide="alert-circle" class="w-6 h-6 text-red-500"></i>
                <span>
                    <?= \App\Core\Session::get('error');
                    \App\Core\Session::remove('error'); ?>
                </span>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            <!-- Form -->
            <div
                class="bg-white p-8 md:p-10 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 h-fit">
                <div class="bg-slate-50 p-6 rounded-3xl mb-8 border border-slate-100">
                    <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">
                        <?= __('available_balance') ?>
                    </p>
                    <p class="text-3xl font-black text-slate-900">
                        <?= number_format($user['balance'], 0, '.', ' ') ?> <span
                            class="text-sm"><?= __('cfa') ?></span>
                    </p>
                </div>

                <form action="/withdraw" method="POST" class="space-y-6">
                    <div class="space-y-2">
                        <label
                            class="block text-xs font-bold text-slate-400 uppercase tracking-widest px-1"><?= __('amount_to_withdraw') ?></label>
                        <input type="number" name="amount" min="1000" max="<?= $user['balance'] ?>" required
                            placeholder="0"
                            class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-4 text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>

                    <div class="space-y-2">
                        <label
                            class="block text-xs font-bold text-slate-400 uppercase tracking-widest px-1"><?= __('operator') ?></label>
                        <select name="operator" required
                            class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-4 text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            <option value="Orange Money">Orange Money</option>
                            <option value="MTN Mobile Money">MTN Mobile Money</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label
                            class="block text-xs font-bold text-slate-400 uppercase tracking-widest px-1"><?= __('phone_number') ?></label>
                        <input type="text" name="phone_number" required placeholder="691234567"
                            class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-4 text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>

                    <button type="submit"
                        class="w-full bg-primary hover:bg-blue-600 text-white font-black py-5 rounded-2xl transition-all shadow-xl shadow-primary/20 active:scale-[0.98] mt-4">
                        <?= __('confirm_withdrawal') ?>
                    </button>
                </form>
            </div>

            <!-- History -->
            <div class="space-y-6">
                <h2 class="text-xl font-black text-slate-900 flex items-center space-x-2">
                    <i data-lucide="history" class="w-6 h-6 text-primary"></i>
                    <span><?= __('withdrawal_history') ?></span>
                </h2>

                <div class="space-y-4">
                    <?php if (empty($withdrawals)): ?>
                        <div class="bg-white p-10 rounded-[2rem] border border-slate-100 text-center">
                            <i data-lucide="inbox" class="w-12 h-12 text-slate-200 mx-auto mb-4"></i>
                            <p class="text-slate-400 font-bold italic"><?= __('no_withdrawals') ?></p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($withdrawals as $tx): ?>
                            <div
                                class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex justify-between items-center transition-all hover:border-primary/20">
                                <div>
                                    <p class="text-sm font-black text-slate-900">
                                        <?= number_format($tx['amount'], 0, '.', ' ') ?>         <?= __('cfa') ?>
                                    </p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase">
                                        <?= date('d M Y, H:i', strtotime($tx['created_at'])) ?>
                                    </p>
                                    <p class="text-xs text-slate-500 font-medium mt-1">
                                        <?= htmlspecialchars($tx['description']) ?>
                                    </p>
                                </div>
                                <div>
                                    <span
                                        class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest
                                        <?= $tx['status'] === 'completed' ? 'bg-emerald-100 text-emerald-600' :
                                            ($tx['status'] === 'pending' ? 'bg-amber-100 text-amber-600' : 'bg-red-100 text-red-600') ?>">
                                        <?php
                                        switch ($tx['status']) {
                                            case 'completed':
                                                echo __('status_completed');
                                                break;
                                            case 'pending':
                                                echo __('status_pending');
                                                break;
                                            case 'rejected':
                                                echo __('status_rejected');
                                                break;
                                            default:
                                                echo htmlspecialchars($tx['status']);
                                                break;
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Navigation Mobile -->
    <nav
        class="lg:hidden fixed bottom-4 left-4 right-4 bg-white/80 backdrop-blur-lg border border-gray-100 shadow-2xl rounded-2xl z-[100] px-4 py-3">
        <div class="flex justify-between items-center">
            <a href="/dashboard"
                class="flex flex-col items-center space-y-1 text-slate-400 hover:text-primary transition-colors">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                <span class="text-[8px] font-bold uppercase tracking-[0.05em]">
                    <?= __('dashboard') ?>
                </span>
            </a>
            <a href="/plans"
                class="flex flex-col items-center space-y-1 text-slate-400 hover:text-primary transition-colors">
                <i data-lucide="trending-up" class="w-5 h-5"></i>
                <span class="text-[8px] font-bold uppercase tracking-[0.05em]">
                    <?= __('invest') ?>
                </span>
            </a>
            <a href="/ads"
                class="flex flex-col items-center space-y-1 text-slate-400 hover:text-primary transition-colors">
                <i data-lucide="play-circle" class="w-5 h-5"></i>
                <span class="text-[8px] font-bold uppercase tracking-[0.05em]">
                    <?= __('ads') ?>
                </span>
            </a>
            <a href="/recharge"
                class="flex flex-col items-center space-y-1 text-slate-400 hover:text-primary transition-colors">
                <i data-lucide="wallet" class="w-5 h-5"></i>
                <span class="text-[8px] font-bold uppercase tracking-[0.05em]">
                    <?= __('wallet') ?>
                </span>
            </a>
        </div>
    </nav>

    <script>lucide.createIcons();</script>
    <?php include __DIR__ . '/../partials/whatsapp.php'; ?>
</body>

</html>