<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#3b82f6">
    <link rel="manifest" href="/manifest.json">
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
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: { primary: '#3b82f6', success: '#10b981', warning: '#f59e0b', danger: '#ef4444' }
                }
            }
        }
    </script>
    <style>
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>

<body class="bg-gray-50 text-slate-800 font-sans min-h-screen">
    <!-- Top Navbar -->
    <nav
        class="bg-white border-b border-gray-200 sticky top-0 z-50 py-3 px-6 md:px-12 flex justify-between items-center shadow-sm">
        <div class="flex items-center space-x-3">
            <a href="/dashboard" class="hover:opacity-80 transition-opacity">
                <img src="/images/logo.png" alt="Investian" class="h-10 w-auto">
            </a>
        </div>

        <div class="hidden lg:flex items-center space-x-8">
            <a href="/dashboard" class="text-primary font-semibold transition-all"><?= __('dashboard') ?></a>
            <a href="/plans"
                class="text-slate-600 hover:text-primary font-semibold transition-all"><?= __('invest') ?></a>
            <a href="/ads" class="text-slate-600 hover:text-primary font-semibold transition-all"><?= __('ads') ?></a>
            <a href="/recharge"
                class="text-slate-600 hover:text-primary font-semibold transition-all"><?= __('wallet') ?></a>
        </div>

        <div class="flex items-center space-x-3">
            <!-- Community Icon (Visible on all devices) -->
            <a href="/community"
                class="p-2 bg-gray-100 text-slate-600 rounded-lg hover:text-primary hover:bg-blue-50 transition-all relative">
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

            <a href="/download"
                class="p-2 bg-emerald-50 text-emerald-600 rounded-lg hover:text-emerald-700 hover:bg-emerald-100 transition-all flex items-center justify-center"
                title="Télécharger l'App">
                <i data-lucide="download" class="w-5 h-5"></i>
            </a>

            <div class="flex bg-gray-100 p-1 rounded-lg">
                <a href="/lang?l=en"
                    class="px-3 py-1 rounded-md text-xs font-bold uppercase <?= \App\Core\Language::getCurrent() === 'en' ? 'bg-white text-primary shadow-sm' : 'text-slate-400' ?>">EN</a>
                <a href="/lang?l=fr"
                    class="px-3 py-1 rounded-md text-xs font-bold uppercase <?= \App\Core\Language::getCurrent() === 'fr' ? 'bg-white text-primary shadow-sm' : 'text-slate-400' ?>">FR</a>
            </div>

            <!-- Profile Dropdown -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.away="open = false"
                    class="text-primary p-2 bg-blue-50 rounded-lg border border-blue-100 transition-all hover:bg-blue-100">
                    <i data-lucide="user" class="w-5 h-5"></i>
                </button>
                <div x-show="open" x-transition
                    class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50">
                    <a href="/profile"
                        class="flex items-center space-x-2 px-4 py-2 text-sm text-slate-600 hover:bg-gray-50 hover:text-primary transition-colors">
                        <i data-lucide="user" class="w-4 h-4"></i>
                        <span class="font-bold"><?= __('profile') ?></span>
                    </a>
                    <?php if (isset($user['role']) && $user['role'] === 'admin'): ?>
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
                        <span class="font-bold"><?= __('logout') ?></span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-6 md:p-10">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-6">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight mb-1"><?= __('welcome') ?>,
                    <?= explode(' ', $user['name'])[0] ?>! 👋
                </h1>
                <p class="text-slate-500 font-medium"><?= date('l, d F Y') ?></p>
            </div>
            <div class="flex flex-wrap gap-3 w-full md:w-auto">
                <a href="/guide"
                    class="flex-1 min-w-[140px] border border-gray-300 text-slate-700 font-bold px-4 py-3 rounded-xl hover:bg-gray-50 transition-all flex items-center justify-center space-x-2">
                    <i data-lucide="book-open" class="w-5 h-5"></i>
                    <span><?= __('how_to_invest') ?></span>
                </a>
                <a href="/withdraw"
                    class="flex-1 min-w-[140px] border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold px-4 py-3 rounded-xl transition-all flex items-center justify-center space-x-2">
                    <i data-lucide="arrow-up-circle" class="w-5 h-5"></i>
                    <span><?= __('withdrawal') ?></span>
                </a>
                <a href="/recharge"
                    class="flex-1 min-w-[140px] bg-primary hover:bg-blue-600 text-white font-bold px-4 py-3 rounded-xl transition-all shadow-md shadow-primary/20 flex items-center justify-center space-x-2">
                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    <span><?= __('deposit_btn') ?></span>
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <!-- Balance Card -->
            <div class="lg:col-span-2 bg-slate-900 rounded-2xl p-8 text-white shadow-lg">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1"><?= __('balance') ?>
                        </p>
                        <div class="flex items-baseline space-x-2">
                            <span class="text-4xl font-bold"><?= number_format($user['balance'], 0, '.', ' ') ?></span>
                            <span class="text-lg font-bold text-primary"><?= __('cfa') ?></span>
                        </div>
                    </div>
                    <div class="bg-white/10 p-3 rounded-xl">
                        <i data-lucide="wallet" class="w-6 h-6 text-primary"></i>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-6 border-t border-white/10 pt-6">
                    <div>
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">
                            <?= __('total_profits') ?>
                        </p>
                        <p class="text-lg font-bold text-emerald-400"><?= number_format($totalProfits,0) ?>
                            <?= __('cfa') ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">
                            <?= __('active_investments') ?>
                        </p>
                        <p class="text-lg font-bold text-blue-400"><?= count($investments) ?></p>
                    </div>
                </div>
            </div>

            <!-- Ad Progress Card -->
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-lg font-bold text-slate-800"><?= __('watched_ads') ?></h3>
                        <i data-lucide="play-circle" class="w-5 h-5 text-amber-500"></i>
                    </div>
                    <div class="flex items-baseline space-x-2 mb-4">
                        <span class="text-3xl font-bold"><?= $watchedToday ?></span>
                        <span class="text-slate-400 font-semibold text-sm">/ <?= $requiredAds ?></span>
                    </div>
                    <div class="w-full bg-gray-100 h-2 rounded-full mb-2">
                        <div class="bg-amber-400 h-full rounded-full"
                            style="width: <?= ($requiredAds > 0) ? ($watchedToday / $requiredAds * 100) : 0 ?>%"></div>
                    </div>
                </div>
                <a href="/ads"
                    class="mt-4 text-center text-amber-600 font-bold text-sm bg-amber-50 py-3 rounded-xl hover:bg-amber-100 transition-all flex items-center justify-center space-x-1">
                    <span><?= __('watch_ads_btn') ?></span>
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
            </div>

            <!-- Referral Card -->
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between" x-data="{ 
                copyText: '<?= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/register?ref=' . ($user['referral_code'] ?? '') ?>',
                copied: false,
                copyToClipboard() {
                    if(!this.copyText) return;
                    navigator.clipboard.writeText(this.copyText);
                    this.copied = true;
                    setTimeout(() => this.copied = false, 2000);
                }
            }">
                <div>
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-lg font-bold text-slate-800"><?= __('referral_bonus') ?></h3>
                        <i data-lucide="users" class="w-5 h-5 text-primary"></i>
                    </div>
                    <p class="text-slate-500 text-sm mb-4 leading-relaxed">
                        <?= __('invite_friends') ?>
                    </p>
                </div>
                <div class="bg-indigo-50/50 border border-indigo-100/50 p-3 rounded-xl flex items-center justify-between group cursor-pointer hover:border-primary/30 transition-all"
                    @click="copyToClipboard()">
                    <div class="overflow-hidden flex-1">
                        <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-0.5">
                            <?= __('referral_link_label') ?>
                        </p>
                        <p class="text-xs font-bold text-slate-600 truncate">
                            <?= ($user['referral_code'] ?? '') ?>
                        </p>
                    </div>
                    <button
                        class="ml-3 p-2 bg-white rounded-lg shadow-sm text-slate-400 group-hover:text-primary transition-colors">
                        <i data-lucide="copy" class="w-4 h-4" x-show="!copied"></i>
                        <i data-lucide="check" class="w-4 h-4 text-emerald-500" x-show="copied" x-cloak></i>
                    </button>
                </div>
            </div>
        </div>

        <?php if (\App\Core\Session::has('error')): ?>
            <div id="error-msg" class="mb-8 bg-red-50 border border-red-200 p-6 rounded-2xl animate-fade-in">
                <div class="flex items-center space-x-3 text-red-600">
                    <i data-lucide="alert-circle" class="w-6 h-6"></i>
                    <p class="font-bold text-sm"><?= \App\Core\Session::get('error');
                    \App\Core\Session::remove('error'); ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (\App\Core\Session::has('success')): ?>
            <div id="success-msg" class="mb-8 bg-emerald-50 border border-emerald-200 p-6 rounded-2xl animate-fade-in">
                <div class="flex items-center space-x-3 text-emerald-600">
                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                    <p class="font-bold text-sm">
                        <?= \App\Core\Session::get('success');
                        \App\Core\Session::remove('success'); ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Available Plans Section -->
        <div class="mb-12">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-slate-900"><?= __('investment_plans_title') ?></h2>
                <a href="/plans"
                    class="text-primary font-bold text-xs uppercase tracking-widest hover:underline flex items-center space-x-1">
                    <span><?= __('view_more') ?></span>
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach ($availablePlans as $plan): ?>
                    <div
                        class="bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-sm group hover:border-primary/40 transition-all">
                        <div class="h-32 bg-gray-200 relative overflow-hidden">
                            <img src="<?= $plan['image_url'] ?>" alt="<?= $plan['name'] ?>"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 opacity-90">
                            <div class="absolute bottom-3 left-4">
                                <span
                                    class="bg-primary text-white text-[8px] font-bold px-2 py-1 rounded-full uppercase tracking-widest">+<?= number_format($plan['daily_profit_amount'] ?? (($plan['price'] * ($plan['daily_profit_percent'] ?? 0)) / 100), 0) ?>
                                    XAF/j</span>
                            </div>
                        </div>
                        <div class="p-4">
                            <h4 class="text-sm font-bold text-slate-900 mb-1"><?= htmlspecialchars($plan['name']) ?></h4>
                            <p class="text-[10px] font-bold text-primary mb-3"><?= number_format($plan['price'], 0) ?>
                                <?= __('cfa') ?>
                            </p>
                            <a href="/plans"
                                class="block w-full text-center bg-gray-50 hover:bg-gray-100 text-slate-800 text-[10px] font-bold py-2 rounded-lg transition-all active:scale-95">
                                <?= __('details') ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- Active Investments -->
            <div class="lg:col-span-2">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-slate-900"><?= __('active_investments_title') ?></h2>
                    <a href="/plans" class="text-primary font-bold text-sm hover:underline"><?= __('view_plans') ?></a>
                </div>

                <?php if (empty($investments)): ?>
                    <div class="bg-white p-10 rounded-2xl border border-gray-200 border-dashed text-center">
                        <i data-lucide="layers" class="w-10 h-10 text-gray-300 mx-auto mb-4"></i>
                        <h3 class="text-lg font-bold text-slate-500 mb-1"><?= __('no_investments') ?></h3>
                        <p class="text-slate-400 text-sm"><?= __('no_investments_desc') ?></p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php foreach ($investments as $inv): ?>
                            <div
                                class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm hover:border-primary/40 transition-all">
                                <div class="flex items-center space-x-4 mb-4">
                                    <div
                                        class="w-10 h-10 bg-primary/10 text-primary rounded-lg flex items-center justify-center">
                                        <i data-lucide="trending-up" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-900">
                                            <?= htmlspecialchars($inv['plan_name'] ?? 'Plan') ?>
                                        </h4>
                                        <span
                                            class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest">+<?= $inv['daily_profit_percent'] ?>%
                                            / d</span>
                                    </div>
                                </div>
                                <div class="flex justify-between items-end border-t border-gray-50 pt-4">
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">
                                            <?= __('total_profits') ?>
                                        </p>
                                        <p class="text-md font-bold text-emerald-600">
                                            <?= number_format($inv['total_profit'] ?? 0, 0) ?>         <?= __('cfa') ?>
                                        </p>
                                    </div>
                                    <span class="flex h-2 w-2 rounded-full bg-emerald-400"></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recent Activity -->
            <div>
                <h2 class="text-xl font-bold text-slate-900 mb-6"><?= __('recent_activity') ?></h2>
                <div class="space-y-4">
                    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center space-x-4">
                        <div
                            class="w-10 h-10 bg-emerald-50 text-emerald-500 rounded-xl flex items-center justify-center">
                            <i data-lucide="arrow-down-left" class="w-5 h-5"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-slate-800"><?= __('deposit_approved') ?></p>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                <?= sprintf(__('hours_ago'), 2) ?>
                            </p>
                        </div>
                    </div>
                    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center space-x-4">
                        <div class="w-10 h-10 bg-blue-50 text-blue-500 rounded-xl flex items-center justify-center">
                            <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-slate-800"><?= __('interest_paid') ?></p>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                <?= sprintf(__('today_at'), '08:00') ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Activity Feed (Simulated) -->
        <div class="mt-12 bg-slate-900 rounded-3xl p-8 overflow-hidden relative shadow-2xl border border-white/5">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-primary/20 rounded-xl flex items-center justify-center">
                        <i data-lucide="activity" class="text-primary w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-white font-bold tracking-tight"><?= __('live_activity') ?> ⚡</h3>
                        <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest">
                            <?= __('real_time_transactions') ?>
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                    <span class="text-emerald-500 text-[10px] font-bold uppercase tracking-widest">Live</span>
                </div>
            </div>

            <div id="activity-feed" class="space-y-4 h-64 overflow-hidden relative">
                <!-- Transactions will be injected here -->
                <div
                    class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-slate-900 to-transparent pointer-events-none z-10">
                </div>
            </div>
        </div>
    </main>

    <style>
        .activity-item {
            animation: slideUpFadeIn 0.5s ease-out forwards;
        }

        @keyframes slideUpFadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .activity-exit {
            animation: fadeOutDown 0.5s ease-in forwards;
        }

        @keyframes fadeOutDown {
            from {
                opacity: 1;
                transform: translateY(0);
            }

            to {
                opacity: 0;
                transform: translateY(-20px);
            }
        }
    </style>

    <script>
        const activities = [
            { type: 'withdrawal', label: '<?= __('withdrawal_approved') ?>', icon: 'arrow-up-right', color: 'text-emerald-500', bg: 'bg-emerald-500/10' },
            { type: 'deposit', label: '<?= __('deposit_confirmed') ?>', icon: 'arrow-down-left', color: 'text-blue-500', bg: 'bg-blue-500/10' },
            { type: 'investment', label: '<?= __('new_investment') ?>', icon: 'trending-up', color: 'text-amber-500', bg: 'bg-amber-500/10' }
        ];

        const operators = [
            { name: 'Orange Money', color: 'text-orange-500' },
            { name: 'MTN MoMo', color: 'text-yellow-500' }
        ];

        function getRandomPhone() {
            const prefix = ['67', '68', '69', '65'];
            const start = prefix[Math.floor(Math.random() * prefix.length)] + Math.floor(Math.random() * 999).toString().padStart(3, '0');
            return start + '...';
        }

        function getRandomAmount(type) {
            if (type === 'withdrawal') return (Math.floor(Math.random() * 50) + 5) * 1000;
            if (type === 'deposit') return (Math.floor(Math.random() * 100) + 10) * 1000;
            return (Math.floor(Math.random() * 20) + 2) * 5000;
        }

        function addActivity() {
            const feed = document.getElementById('activity-feed');

            // Majority of withdrawals (70%)
            const rand = Math.random();
            let activity;
            if (rand < 0.7) activity = activities[0];
            else if (rand < 0.85) activity = activities[1];
            else activity = activities[2];

            const operator = operators[Math.floor(Math.random() * operators.length)];
            const phone = getRandomPhone();
            const amount = getRandomAmount(activity.type);
            const ref = 'REF' + Math.random().toString(36).substring(2, 8).toUpperCase();

            const item = document.createElement('div');
            item.className = 'activity-item bg-white/5 border border-white/10 p-4 rounded-2xl flex items-center justify-between group hover:bg-white/10 transition-all';

            item.innerHTML = `
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 ${activity.bg} rounded-xl flex items-center justify-center">
                        <i data-lucide="${activity.icon}" class="${activity.color} w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <p class="text-white text-sm font-bold">${phone}</p>
                            <span class="text-[10px] ${activity.color} font-bold uppercase tracking-widest">${activity.label}</span>
                        </div>
                        <p class="text-slate-500 text-[10px] font-medium">${ref} • <span class="${operator.color}">${operator.name}</span></p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-white font-bold">${amount.toLocaleString()} FCFA</p>
                    <p class="text-slate-500 text-[9px] font-medium"><?= __('just_now') ?></p>
                </div>
            `;

            feed.prepend(item);
            lucide.createIcons();

            // Limit feed items
            if (feed.children.length > 7) {
                const last = feed.lastElementChild;
                last.classList.add('activity-exit');
                setTimeout(() => last.remove(), 500);
            }

            // Schedule next activity at random interval (3s to 60s)
            // Sometimes multiple follow quickly
            const nextInterval = Math.random() < 0.3 ? 500 + Math.random() * 2000 : 3000 + Math.random() * 27000;
            setTimeout(addActivity, nextInterval);
        }

        // Start the feed
        setTimeout(addActivity, 2000);

        // Auto-scroll to messages
        window.addEventListener('load', () => {
            const errorMsg = document.getElementById('error-msg');
            const successMsg = document.getElementById('success-msg');
            const msg = errorMsg || successMsg;
            if (msg) {
                msg.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    </script>

    <!-- Mobile Bottom Navigation -->
    <nav
        class="lg:hidden fixed bottom-4 left-4 right-4 bg-white/80 backdrop-blur-lg border border-gray-100 shadow-2xl rounded-2xl z-[100] px-4 py-3">
        <div class="flex justify-between items-center">
            <a href="/dashboard" class="flex flex-col items-center space-y-1 text-primary">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                <span class="text-[8px] font-bold uppercase tracking-[0.05em]"><?= __('dashboard') ?></span>
            </a>
            <a href="/plans"
                class="flex flex-col items-center space-y-1 text-slate-400 hover:text-primary transition-colors">
                <i data-lucide="trending-up" class="w-5 h-5"></i>
                <span class="text-[8px] font-bold uppercase tracking-[0.05em]"><?= __('invest') ?></span>
            </a>
            <a href="/ads"
                class="flex flex-col items-center space-y-1 text-slate-400 hover:text-primary transition-colors">
                <i data-lucide="play-circle" class="w-5 h-5"></i>
                <span class="text-[8px] font-bold uppercase tracking-[0.05em]"><?= __('ads') ?></span>
            </a>
            <a href="/recharge"
                class="flex flex-col items-center space-y-1 text-slate-400 hover:text-primary transition-colors">
                <i data-lucide="wallet" class="w-5 h-5"></i>
                <span class="text-[8px] font-bold uppercase tracking-[0.05em]"><?= __('wallet') ?></span>
            </a>
        </div>
    </nav>

    <script>
        lucide.createIcons();
        function copyReferral() {
            const code = document.querySelector('.font-mono').innerText;
            navigator.clipboard.writeText(code);
            alert('<?= __('link_copied') ?>');
        }
    </script>
    <?php include __DIR__ . '/../partials/whatsapp.php'; ?>
</body>

</html>