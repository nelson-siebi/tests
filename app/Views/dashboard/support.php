<!DOCTYPE html>
<html lang="<?= \App\Core\Language::getCurrent() ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - Investian</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
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

<body class="bg-slate-50 text-slate-800 font-sans min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav
        class="bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-50 py-4 px-6 flex items-center justify-between">
        <a href="/dashboard" class="bg-slate-100 p-2 rounded-xl text-slate-600 hover:bg-slate-200 transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h1 class="text-lg font-black text-slate-900"><?= __('help_center') ?></h1>
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
            <?php else: ?>
                <div class="w-9"></div> <!-- Spacer -->
            <?php endif; ?>
        </div>
    </nav>

    <main class="flex-1 p-6 max-w-lg mx-auto w-full space-y-8">
        <div class="text-center py-8">
            <img src="/images/logo.png" alt="Investian" class="h-16 w-auto mx-auto mb-4">
            <p class="text-slate-500 font-medium"><?= __('support_query') ?></p>
        </div>

        <div class="space-y-4">
            <!-- WhatsApp -->
            <a href="https://wa.me/<?= env('SUPPORT_WHATSAPP') ?>" target="_blank"
                class="flex items-center p-5 bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md hover:border-green-100 transition-all group">
                <div
                    class="w-12 h-12 bg-green-50 text-green-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i data-lucide="message-circle" class="w-6 h-6"></i>
                </div>
                <div class="ml-4 flex-1">
                    <h3 class="font-bold text-slate-900">WhatsApp</h3>
                    <p class="text-xs text-slate-400 font-medium"><?= __('instant_response') ?></p>
                </div>
                <i data-lucide="chevron-right" class="w-5 h-5 text-slate-300"></i>
            </a>

            <!-- Facebook -->
            <a href="<?= env('SUPPORT_FACEBOOK') ?>" target="_blank"
                class="flex items-center p-5 bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md hover:border-blue-100 transition-all group">
                <div
                    class="w-12 h-12 bg-blue-50 text-blue-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i data-lucide="facebook" class="w-6 h-6"></i>
                </div>
                <div class="ml-4 flex-1">
                    <h3 class="font-bold text-slate-900">Facebook</h3>
                    <p class="text-xs text-slate-400 font-medium"><?= __('follow_news') ?></p>
                </div>
                <i data-lucide="chevron-right" class="w-5 h-5 text-slate-300"></i>
            </a>

            <!-- Email -->
            <a href="mailto:<?= env('SUPPORT_EMAIL') ?>"
                class="flex items-center p-5 bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md hover:border-orange-100 transition-all group">
                <div
                    class="w-12 h-12 bg-orange-50 text-orange-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i data-lucide="mail" class="w-6 h-6"></i>
                </div>
                <div class="ml-4 flex-1">
                    <h3 class="font-bold text-slate-900">Email</h3>
                    <p class="text-xs text-slate-400 font-medium"><?= __('formal_queries') ?></p>
                </div>
                <i data-lucide="chevron-right" class="w-5 h-5 text-slate-300"></i>
            </a>
        </div>

        <div class="bg-blue-50 p-6 rounded-3xl border border-blue-100 text-center">
            <h4 class="font-bold text-blue-900 mb-2"><?= __('need_guide') ?></h4>
            <p class="text-xs text-blue-700 mb-4 px-4"><?= __('consult_tutorials') ?>
            </p>
            <a href="/guide"
                class="inline-block bg-white text-blue-600 font-black px-6 py-3 rounded-xl shadow-sm text-xs uppercase tracking-widest hover:bg-blue-600 hover:text-white transition-colors">
                <?= __('view_guides') ?>
            </a>
        </div>
    </main>

    <script>lucide.createIcons();</script>
</body>

</html>