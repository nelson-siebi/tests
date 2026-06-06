<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investian - <?= __('home_title') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                        secondary: '#10b981',
                        dark: '#020617',
                        card: '#0f172a',
                    },
                }
            }
        }
    </script>
</head>

<body class="bg-gray-50 text-slate-900 font-sans selection:bg-primary selection:text-white">
    <!-- Navigation -->
    <nav
        class="fixed w-full z-50 bg-white border-b border-gray-100 shadow-sm px-6 py-4 flex justify-between items-center h-20">
        <div class="flex items-center space-x-3">
            <a href="/" class="hover:opacity-80 transition-opacity">
                <img src="/images/logo.png" alt="Investian" class="h-10 w-auto">
            </a>
        </div>

        <div class="flex items-center space-x-4 md:hidden">
            <div class="flex bg-gray-100 p-1 rounded-lg">
                <a href="/lang?l=en"
                    class="px-2 py-1 rounded-md text-[8px] font-bold uppercase tracking-widest <?= \App\Core\Language::getCurrent() === 'en' ? 'bg-white text-primary shadow-sm' : 'text-slate-400' ?>">EN</a>
                <a href="/lang?l=fr"
                    class="px-2 py-1 rounded-md text-[8px] font-bold uppercase tracking-widest <?= \App\Core\Language::getCurrent() === 'fr' ? 'bg-white text-primary shadow-sm' : 'text-slate-400' ?>">FR</a>
            </div>
            <a href="/login" class="text-primary font-bold text-sm"><?= __('login') ?></a>
        </div>

        <div class="hidden md:flex items-center space-x-8">
            <a href="/" class="text-slate-600 hover:text-primary font-semibold transition-all"><?= __('home') ?></a>
            <a href="/login"
                class="text-slate-600 hover:text-primary font-semibold transition-all"><?= __('login') ?></a>

            <div class="flex bg-gray-100 p-1 rounded-lg">
                <a href="/lang?l=en"
                    class="px-3 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest <?= \App\Core\Language::getCurrent() === 'en' ? 'bg-white text-primary shadow-sm' : 'text-slate-400' ?>">EN</a>
                <a href="/lang?l=fr"
                    class="px-3 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest <?= \App\Core\Language::getCurrent() === 'fr' ? 'bg-white text-primary shadow-sm' : 'text-slate-400' ?>">FR</a>
            </div>

            <a href="/register"
                class="bg-primary hover:bg-blue-600 text-white font-bold px-6 py-3 rounded-xl transition-all shadow-md shadow-primary/20">
                <?= __('get_started') ?>
            </a>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="relative pt-40 pb-20 md:pt-56 md:pb-32 overflow-hidden bg-white">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold text-slate-900 tracking-tight mb-8">
                <?= __('hero_title') ?><br>
                <span class="text-primary"><?= __('hero_subtitle') ?></span>
            </h1>
            <p class="max-w-2xl mx-auto text-lg md:text-xl text-slate-500 mb-12 font-medium">
                <?= __('hero_desc') ?>
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="/register"
                    class="px-8 py-4 bg-primary text-white text-lg font-bold rounded-xl hover:bg-blue-600 transition-all shadow-lg shadow-primary/20">
                    <?= __('get_started') ?>
                </a>
                <a href="#features"
                    class="px-8 py-4 bg-gray-50 text-slate-700 text-lg font-bold rounded-xl hover:bg-gray-100 transition-all border border-gray-200">
                    <?= __('learn_more') ?>
                </a>
            </div>
        </div>

        <!-- Dashboard Image Section -->
        <div class="max-w-6xl mx-auto px-6 mt-20 relative">
            <div
                class="relative z-10 rounded-[2.5rem] overflow-hidden shadow-2xl shadow-primary/20 border-4 border-white">
                <img src="/images/dashboard-demo.png" alt="Dashboard Preview" class="w-full h-auto">
            </div>
            <!-- Decorative Elements -->
            <div class="absolute -top-10 -left-10 w-40 h-40 bg-blue-100 rounded-full blur-3xl opacity-50"></div>
            <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-emerald-100 rounded-full blur-3xl opacity-50"></div>
        </div>
    </header>

    <!-- How It Works Section -->
    <section class="py-24 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-20">
                <h2 class="text-3xl md:text-5xl font-bold text-slate-900 mb-4"><?= __('how_it_works_title') ?></h2>
                <p class="text-slate-500 text-lg font-medium"><?= __('how_it_works_subtitle') ?></p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 relative">
                <!-- Step 1 -->
                <div class="text-center group">
                    <div
                        class="w-20 h-20 bg-blue-50 text-primary rounded-[2rem] flex items-center justify-center text-3xl font-black mb-8 mx-auto group-hover:bg-primary group-hover:text-white transition-all shadow-xl shadow-blue-500/10">
                        1</div>
                    <h3 class="text-xl font-bold mb-4 text-slate-900"><?= __('step_1_title') ?></h3>
                    <p class="text-slate-500 font-medium leading-relaxed px-4"><?= __('step_1_desc') ?></p>
                </div>
                <!-- Step 2 -->
                <div class="text-center group">
                    <div
                        class="w-20 h-20 bg-emerald-50 text-emerald-500 rounded-[2rem] flex items-center justify-center text-3xl font-black mb-8 mx-auto group-hover:bg-emerald-500 group-hover:text-white transition-all shadow-xl shadow-emerald-500/10">
                        2</div>
                    <h3 class="text-xl font-bold mb-4 text-slate-900"><?= __('step_2_title') ?></h3>
                    <p class="text-slate-500 font-medium leading-relaxed px-4"><?= __('step_2_desc') ?></p>
                </div>
                <!-- Step 3 -->
                <div class="text-center group">
                    <div
                        class="w-20 h-20 bg-amber-50 text-amber-500 rounded-[2rem] flex items-center justify-center text-3xl font-black mb-8 mx-auto group-hover:bg-amber-500 group-hover:text-white transition-all shadow-xl shadow-amber-500/10">
                        3</div>
                    <h3 class="text-xl font-bold mb-4 text-slate-900"><?= __('step_3_title') ?></h3>
                    <p class="text-slate-500 font-medium leading-relaxed px-4"><?= __('step_3_desc') ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Investment Plans Section -->
    <section id="plans" class="py-24 bg-gray-50/50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-5xl font-bold text-slate-900 tracking-tight mb-4">
                    <?= __('investment_plans_title') ?>
                </h2>
                <p class="text-slate-500 text-lg font-medium"><?= __('choose_plan') ?></p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($plans as $plan): ?>
                    <div
                        class="bg-white rounded-[2rem] overflow-hidden border border-gray-100 group hover:border-primary/40 transition-all hover:shadow-2xl hover:shadow-primary/10">
                        <div class="h-56 bg-gray-100 relative overflow-hidden">
                            <img src="<?= $plan['image_url'] ?>" alt="<?= $plan['name'] ?>"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent">
                            </div>
                            <div class="absolute bottom-6 left-8">
                                <span
                                    class="bg-primary text-white text-[10px] font-black px-4 py-2 rounded-full uppercase tracking-widest shadow-xl shadow-primary/30">+<?= $plan['daily_profit_percent'] ?>%
                                    / d</span>
                            </div>
                        </div>
                        <div class="p-8 md:p-10">
                            <h3 class="text-2xl font-black text-slate-900 mb-6"><?= htmlspecialchars($plan['name']) ?></h3>
                            <div class="space-y-4 mb-10">
                                <div class="flex justify-between items-center text-sm">
                                    <span
                                        class="text-slate-400 font-bold uppercase tracking-wider text-[10px]"><?= __('min_amount') ?></span>
                                    <span
                                        class="text-slate-900 font-black italic"><?= number_format($plan['price'], 0, '.', ' ') ?>
                                        <?= __('cfa') ?></span>
                                </div>
                                <div class="flex justify-between items-center text-sm border-t border-slate-50 pt-4">
                                    <span
                                        class="text-slate-400 font-bold uppercase tracking-wider text-[10px]"><?= __('total_duration') ?></span>
                                    <span class="text-slate-900 font-black italic"><?= $plan['duration_days'] ?>
                                        <?= __('days') ?></span>
                                </div>
                                <div class="flex justify-between items-center text-sm border-t border-slate-50 pt-4">
                                    <span
                                        class="text-slate-400 font-bold uppercase tracking-wider text-[10px]"><?= __('daily_ads') ?></span>
                                    <span class="text-emerald-500 font-black italic"><?= $plan['ads_per_day'] ?>
                                        <?= __('ads') ?></span>
                                </div>
                            </div>
                            <a href="/login?redirect=plans"
                                class="block w-full text-center bg-primary hover:bg-blue-600 text-white font-black py-5 rounded-2xl transition-all shadow-xl shadow-primary/20 active:scale-95">
                                <?= __('get_started') ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-24 bg-white overflow-hidden relative">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col lg:flex-row items-center gap-20">
                <div class="flex-1 space-y-10">
                    <h2 class="text-3xl md:text-5xl font-black text-slate-900 leading-tight">
                        <?= __('explore_features') ?>
                    </h2>
                    <div class="space-y-8">
                        <div class="flex items-start space-x-6">
                            <div
                                class="w-14 h-14 bg-blue-50 text-primary rounded-2xl flex items-center justify-center text-2xl shrink-0">
                                ⚡</div>
                            <div>
                                <h3 class="text-xl font-bold mb-2 text-slate-900"><?= __('feature_1_title') ?></h3>
                                <p class="text-slate-500 font-medium"><?= __('feature_1_desc') ?></p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-6">
                            <div
                                class="w-14 h-14 bg-emerald-50 text-emerald-500 rounded-2xl flex items-center justify-center text-2xl shrink-0">
                                🛡️</div>
                            <div>
                                <h3 class="text-xl font-bold mb-2 text-slate-900"><?= __('feature_2_title') ?></h3>
                                <p class="text-slate-500 font-medium"><?= __('feature_2_desc') ?></p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-6">
                            <div
                                class="w-14 h-14 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center text-2xl shrink-0">
                                📊</div>
                            <div>
                                <h3 class="text-xl font-bold mb-2 text-slate-900"><?= __('feature_3_title') ?></h3>
                                <p class="text-slate-500 font-medium"><?= __('feature_3_desc') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex-1 relative">
                    <div class="relative z-10 rounded-[3rem] overflow-hidden shadow-2xl">
                        <img src="/images/secure-earning.png" alt="Secure Earning" class="w-full">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-24 bg-gray-50">
        <div class="max-w-4xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-5xl font-black text-slate-900 mb-4"><?= __('contact_title') ?></h2>
                <p class="text-slate-500 text-lg font-medium"><?= __('contact_subtitle') ?></p>
            </div>

            <div class="bg-white p-8 md:p-12 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-white">
                <form class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-2"><?= __('full_name_label') ?></label>
                            <input type="text" placeholder="Eto'o Junior"
                                class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all font-bold">
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-2"><?= __('email_label') ?></label>
                            <input type="email" placeholder="junior@gmail.cm"
                                class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all font-bold">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label
                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-2"><?= __('message_label') ?></label>
                        <textarea rows="4"
                            class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-4 focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all font-bold"></textarea>
                    </div>
                    <button type="button"
                        class="w-full bg-primary hover:bg-blue-600 text-white font-black py-5 rounded-2xl transition-all shadow-xl shadow-primary/20 active:scale-95">
                        <?= __('send_message') ?>
                    </button>
                </form>
            </div>
        </div>
    </section>

    <footer class="py-16 bg-white border-t border-gray-100 text-center">
        <p class="text-slate-400 font-bold tracking-tight">&copy; 2026 Investian - <?= __('footer_rights') ?></p>
    </footer>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script> lucide.createIcons(); </script>
</body>

</html>