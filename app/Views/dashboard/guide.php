<!DOCTYPE html>
<html lang="<?= \App\Core\Language::getCurrent() ?>">

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
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: { primary: '#3b82f6', success: '#10b981' }
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
            animation: fadeIn 0.4s ease-out forwards;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 font-sans min-h-screen">
    <!-- Top Navbar -->
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
                    class="px-3 py-1 rounded-lg text-[10px) font-black uppercase tracking-widest <?= \App\Core\Language::getCurrent() === 'fr' ? 'bg-white text-primary shadow-sm' : 'text-slate-400' ?>">FR</a>
            </div>
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
    </nav>

    <main class="max-w-6xl mx-auto p-6 md:p-12">
        <div class="text-center mb-16 animate-fade-in">
            <h1 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tight mb-4">
                <?= __('guide_title') ?> 📚
            </h1>
            <p class="text-slate-500 font-bold italic text-lg">
                <?= __('guide_desc') ?>
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($guides as $index => $guide): ?>
                <div class="bg-white rounded-[2.5rem] overflow-hidden shadow-xl shadow-slate-200/40 border border-slate-100 animate-fade-in group hover:border-primary/40 transition-all"
                    style="animation-delay: <?= $index * 0.1 ?>s">
                    <div class="h-48 bg-slate-100 relative overflow-hidden">
                        <?php if ($guide['image_url']): ?>
                            <img src="<?= $guide['image_url'] ?>" alt="<?= htmlspecialchars($guide['title']) ?>"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center">
                                <i data-lucide="book-open" class="w-12 h-12 text-slate-300"></i>
                            </div>
                        <?php endif; ?>
                        <div class="absolute top-4 left-4">
                            <span
                                class="bg-white/90 backdrop-blur-md px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-widest text-primary shadow-sm">
                                <?= __('guide_prefix') ?>     <?= $index + 1 ?>
                            </span>
                        </div>
                    </div>
                    <div class="p-8 text-center md:text-left">
                        <h3 class="text-xl font-bold text-slate-900 mb-2"><?= htmlspecialchars($guide['title']) ?></h3>
                        <p class="text-slate-500 text-sm font-medium mb-8 line-clamp-2 italic">
                            <?= htmlspecialchars($guide['description']) ?>
                        </p>

                        <div x-data="{ open: false }">
                            <button @click="open = true"
                                class="w-full bg-slate-50 hover:bg-primary hover:text-white text-slate-900 font-black py-4 rounded-2xl transition-all active:scale-95 text-xs uppercase tracking-widest">
                                <?= __('view_steps') ?>
                            </button>

                            <!-- Modal -->
                            <template x-teleport="body">
                                <div x-show="open"
                                    class="fixed inset-0 z-[100] flex items-center justify-center p-6 md:p-12" x-cloak>
                                    <div x-show="open" x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                        class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" @click="open = false">
                                    </div>

                                    <div x-show="open" x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        class="bg-white w-full max-w-3xl rounded-[3rem] shadow-2xl relative z-10 overflow-hidden max-h-[90vh] flex flex-col">

                                        <!-- Header Image & Close -->
                                        <div class="h-40 shrink-0 bg-primary relative overflow-hidden">
                                            <?php if ($guide['image_url']): ?>
                                                <img src="<?= $guide['image_url'] ?>"
                                                    class="w-full h-full object-cover opacity-60">
                                            <?php endif; ?>
                                            <div class="absolute inset-0 bg-gradient-to-t from-primary/80 to-transparent">
                                            </div>
                                            <div class="absolute bottom-6 left-8 right-8">
                                                <h2 class="text-3xl font-black text-white">
                                                    <?= htmlspecialchars($guide['title']) ?>
                                                </h2>
                                            </div>
                                            <button @click="open = false"
                                                class="absolute top-6 right-6 bg-white/20 backdrop-blur-md hover:bg-white/40 p-2 rounded-full text-white transition-colors">
                                                <i data-lucide="x" class="w-6 h-6"></i>
                                            </button>
                                        </div>

                                        <!-- Content -->
                                        <div class="p-8 md:p-12 overflow-y-auto space-y-12">
                                            <!-- Introduction -->
                                            <div
                                                class="prose prose-slate max-w-none text-slate-600 font-medium text-lg italic leading-relaxed">
                                                <?= nl2br(htmlspecialchars($guide['content'])) ?>
                                            </div>

                                            <!-- Steps -->
                                            <div class="space-y-12">
                                                <?php foreach ($guide['steps'] as $stepIndex => $step): ?>
                                                    <div class="relative pl-12">
                                                        <!-- Line -->
                                                        <?php if ($stepIndex < count($guide['steps']) - 1): ?>
                                                            <div class="absolute left-6 top-10 bottom-[-3rem] w-px bg-slate-100">
                                                            </div>
                                                        <?php endif; ?>

                                                        <!-- Step Number Circle -->
                                                        <div
                                                            class="absolute left-0 top-0 w-12 h-12 bg-white border-2 border-primary rounded-2xl flex items-center justify-center font-black text-primary shadow-sm z-10">
                                                            <?= $stepIndex + 1 ?>
                                                        </div>
                                                        <div class="space-y-6">
                                                            <div
                                                                class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                                                <h4 class="text-xl font-black text-slate-900">
                                                                    <?= htmlspecialchars($step['title'] ?: __('step_prefix') . ($stepIndex + 1)) ?>
                                                                </h4>
                                                            </div>

                                                            <div
                                                                class="bg-slate-50 p-6 rounded-[2rem] border border-slate-100 shadow-inner">
                                                                <p class="text-slate-600 font-medium leading-relaxed italic">
                                                                    <?= nl2br(htmlspecialchars($step['content'])) ?>
                                                                </p>
                                                            </div>

                                                            <?php if ($step['media_url']): ?>
                                                                <div
                                                                    class="rounded-3xl overflow-hidden border border-slate-100 shadow-lg group">
                                                                    <?php if ($step['media_type'] === 'video'): ?>
                                                                        <div
                                                                            class="aspect-video bg-black flex items-center justify-center">
                                                                            <?php if (strpos($step['media_url'], 'http') === 0 && (strpos($step['media_url'], 'youtube') !== false || strpos($step['media_url'], 'vimeo') !== false)): ?>
                                                                                <iframe src="<?= $step['media_url'] ?>"
                                                                                    class="w-full h-full" frameborder="0"
                                                                                    allowfullscreen></iframe>
                                                                            <?php else: ?>
                                                                                <video controls class="w-full h-full">
                                                                                    <source src="<?= $step['media_url'] ?>"
                                                                                        type="video/<?= pathinfo($step['media_url'], PATHINFO_EXTENSION) ?>">
                                                                                    Votre navigateur ne supporte pas la lecture de vidéos.
                                                                                </video>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    <?php else: ?>
                                                                        <img src="<?= $step['media_url'] ?>"
                                                                            class="w-full object-cover group-hover:scale-105 transition-transform duration-700">
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>

                                        <!-- Footer -->
                                        <div class="p-8 bg-slate-50 border-t border-slate-100 shrink-0 text-center">
                                            <button @click="open = false"
                                                class="bg-primary hover:bg-blue-600 text-white font-black px-12 py-4 rounded-[1.5rem] shadow-xl shadow-primary/20 transition-all active:scale-95">
                                                <?= __('finished_reading') ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-20 text-center animate-fade-in" style="animation-delay: 0.5s">
            <a href="/recharge"
                class="inline-flex items-center space-x-4 bg-primary hover:bg-blue-600 text-white font-black px-12 py-6 rounded-[2.5rem] transition-all shadow-2xl shadow-primary/30 hover:shadow-primary/50 hover:-translate-y-1 active:scale-95 text-lg">
                <span><?= __('get_started') ?></span>
                <i data-lucide="sparkles" class="w-6 h-6"></i>
            </a>
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
    </script>
    <?php include __DIR__ . '/../partials/whatsapp.php'; ?>
</body>

</html>