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

        @keyframes pulse-ring {
            0% {
                transform: scale(0.33);
                opacity: 0;
            }

            80%,
            100% {
                transform: scale(1);
                opacity: 0;
            }
        }

        .pulse-ring::before {
            content: "";
            position: absolute;
            width: 100%;
            height: 100%;
            border: 4px solid #f59e0b;
            border-radius: 9999px;
            animation: pulse-ring 1.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) infinite;
        }
    </style>
</head>

<body class="bg-gray-50 text-slate-800 font-sans min-h-screen">
    <!-- Top Navbar (Same as Dashboard) -->
    <nav
        class="bg-white border-b border-gray-200 sticky top-0 z-50 py-3 px-6 md:px-12 flex justify-between items-center shadow-sm">
        <div class="flex items-center space-x-3">
            <div class="bg-primary p-2 rounded-lg text-white">
                <i data-lucide="trending-up" class="w-5 h-5"></i>
            </div>
            <a href="/dashboard" class="hover:opacity-80 transition-opacity">
                <img src="/images/logo.png" alt="Investian" class="h-10 w-auto">
            </a>
        </div>

        <div class="hidden lg:flex items-center space-x-8">
            <a href="/dashboard"
                class="text-slate-600 hover:text-primary font-semibold transition-all"><?= __('dashboard') ?></a>
            <a href="/plans"
                class="text-slate-600 hover:text-primary font-semibold transition-all"><?= __('invest') ?></a>
            <a href="/ads" class="text-primary font-semibold transition-all"><?= __('ads') ?></a>
            <a href="/recharge"
                class="text-slate-600 hover:text-primary font-semibold transition-all"><?= __('wallet') ?></a>
        </div>

        <!-- Community Icon -->
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
                    <span class="font-bold"><?= __('logout') ?></span>
                </a>
            </div>
        </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-6 md:p-10">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h1 class="text-3xl font-bold text-slate-900 tracking-tight mb-3"><?= __('ads_zone') ?> 📺</h1>
            <p class="text-slate-500 font-medium"><?= __('view_ads_daily') ?></p>
            <a href="/guide"
                class="inline-flex items-center space-x-2 text-primary font-bold text-xs mt-4 hover:underline">
                <i data-lucide="help-circle" class="w-4 h-4"></i>
                <span><?= __('need_guide') ?></span>
            </a>
        </div>

        <!-- Progress Tracker -->
        <div class="max-w-xl mx-auto mb-12 bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-slate-800"><?= __('watched_ads') ?></h3>
                <span class="text-xl font-bold text-primary"><?= $watchedToday ?> / <?= $requiredAds ?></span>
            </div>
            <div class="w-full bg-gray-100 h-2.5 rounded-full overflow-hidden">
                <div class="bg-primary h-full rounded-full transition-all duration-1000"
                    style="width: <?= ($requiredAds > 0) ? min(100, ($watchedToday / $requiredAds) * 100) : 0 ?>%">
                </div>
            </div>
        </div>

        <?php if (!$hasActiveInvestment): ?>
            <div
                class="max-w-xl mx-auto mb-8 bg-amber-50 border border-amber-200 p-6 rounded-2xl flex items-center space-x-4">
                <div
                    class="w-12 h-12 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center flex-shrink-0">
                    <i data-lucide="alert-circle" class="w-6 h-6"></i>
                </div>
                <div>
                    <h4 class="font-bold text-amber-900"><?= __('ads_restricted') ?? 'Accès restreint' ?></h4>
                    <p class="text-sm text-amber-700"><?= __('must_invest_ads') ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($ads as $ad): ?>
                <div
                    class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 group hover:border-primary/40 transition-all text-center">
                    <div
                        class="w-14 h-14 bg-blue-50 text-primary rounded-xl flex items-center justify-center mx-auto mb-5 group-hover:scale-105 transition-transform">
                        <i data-lucide="play" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-md font-bold text-slate-900 mb-1"><?= htmlspecialchars($ad['title']) ?></h3>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6"><?= $ad['duration'] ?>s
                        •
                        <?= number_format($ad['reward'], 0) ?>     <?= __('cfa') ?>
                    </p>

                    <?php if ($hasActiveInvestment): ?>
                        <form action="/ads/watch" method="POST">
                            <input type="hidden" name="ad_id" value="<?= $ad['id'] ?>">
                            <button type="submit"
                                class="w-full bg-gray-50 text-slate-500 hover:bg-primary hover:text-white font-bold py-3 rounded-xl transition-all text-sm">
                                <?= __('watch_btn') ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <button onclick="alert('<?= __('must_invest_ads') ?>')"
                            class="w-full bg-gray-50 text-slate-300 cursor-not-allowed font-bold py-3 rounded-xl transition-all text-sm">
                            <?= __('watch_btn') ?>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Mobile Bottom Navigation -->
    <nav
        class="lg:hidden fixed bottom-4 left-4 right-4 bg-white/80 backdrop-blur-lg border border-gray-100 shadow-2xl rounded-2xl z-[100] px-4 py-3">
        <div class="flex justify-between items-center">
            <a href="/dashboard"
                class="flex flex-col items-center space-y-1 text-slate-400 hover:text-primary transition-colors">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                <span class="text-[8px] font-bold uppercase tracking-[0.05em]"><?= __('dashboard') ?></span>
            </a>
            <a href="/plans"
                class="flex flex-col items-center space-y-1 text-slate-400 hover:text-primary transition-colors">
                <i data-lucide="trending-up" class="w-5 h-5"></i>
                <span class="text-[8px] font-bold uppercase tracking-[0.05em]"><?= __('invest') ?></span>
            </a>
            <a href="/ads" class="flex flex-col items-center space-y-1 text-primary">
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
        window.addEventListener('load', () => {
            const errorMsg = document.getElementById('error-msg');
            const successMsg = document.getElementById('success-msg');
            const msg = errorMsg || successMsg;
            if (msg) {
                msg.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    </script>
    <?php include __DIR__ . '/../partials/whatsapp.php'; ?>
</body>

</html>