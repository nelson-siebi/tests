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
                    colors: { primary: '#3b82f6', success: '#10b981', warning: '#f59e0b', danger: '#ef4444' }
                }
            }
        }
    </script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-blue-50 via-white to-slate-50 text-slate-800 font-sans min-h-screen">
    <!-- Header -->
    <nav class="bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-50 py-4 px-6 md:px-12">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <a href="<?= $user ? '/dashboard' : '/' ?>"
                    class="bg-primary p-2 rounded-xl text-white shadow-lg shadow-primary/20 hover:scale-105 transition-transform">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <img src="/images/logo.png" alt="Investian" class="h-10 w-auto">
            </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto p-6 md:p-12 py-16">
        <!-- Update Warning Banner -->
        <?php if ($activeVersion && $daysLeft > 0 && $daysLeft <= 14): ?>
            <div
                class="bg-gradient-to-r from-amber-500 to-orange-500 text-white p-8 rounded-3xl shadow-2xl mb-12 <?= $daysLeft <= 3 ? 'animate-pulse' : '' ?>">
                <div class="flex items-start space-x-4">
                    <div class="bg-white/20 p-3 rounded-2xl">
                        <i data-lucide="alert-triangle" class="w-8 h-8"></i>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-2xl font-black mb-2">⚠️ Mise à Jour Importante</h2>
                        <p class="text-white/90 font-bold text-lg mb-3">
                            Une nouvelle version de l'application sera disponible dans <span
                                class="text-yellow-200 font-black"><?= $daysLeft ?>
                                jour<?= $daysLeft > 1 ? 's' : '' ?></span>.
                        </p>
                        <p class="text-white/80 font-medium">
                            L'ancienne version ne fonctionnera plus après cette date. Pensez à télécharger la nouvelle
                            version dès maintenant !
                        </p>
                    </div>
                </div>
            </div>
        <?php elseif (!$activeVersion): ?>
            <div class="bg-red-50 border-2 border-red-200 p-8 rounded-3xl shadow-xl mb-12">
                <div class="flex items-start space-x-4">
                    <div class="bg-red-100 p-3 rounded-2xl">
                        <i data-lucide="x-circle" class="w-8 h-8 text-red-600"></i>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-2xl font-black text-red-900 mb-2">Application Non Disponible</h2>
                        <p class="text-red-700 font-bold">
                            Aucune version de l'application n'est actuellement disponible au téléchargement. Veuillez
                            réessayer plus tard.
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Main Content -->
        <div class="bg-white rounded-[3rem] shadow-2xl p-8 md:p-12 border border-slate-100">
            <div class="text-center mb-12">
                <div
                    class="w-24 h-24 bg-gradient-to-br from-primary to-blue-600 rounded-3xl mx-auto mb-6 flex items-center justify-center shadow-xl shadow-primary/30">
                    <i data-lucide="smartphone" class="w-12 h-12 text-white"></i>
                </div>
                <h1 class="text-4xl md:text-5xl font-black text-slate-900 mb-4">
                    Téléchargez l'Application 📱
                </h1>
                <p class="text-slate-500 text-lg font-medium max-w-2xl mx-auto">
                    Installez Investian sur votre téléphone pour une expérience optimale et un accès rapide à vos
                    investissements.
                </p>
            </div>

            <!-- APK Download Button (Android Only) -->
            <?php if ($activeVersion): ?>
                <div class="mb-12 text-center">
                    <a href="/download/apk"
                        class="inline-flex items-center space-x-3 bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white font-black px-12 py-6 rounded-3xl shadow-2xl shadow-emerald-500/30 hover:scale-105 transition-all text-xl">
                        <i data-lucide="download" class="w-7 h-7"></i>
                        <span>Télécharger l'APK (Android)</span>
                    </a>
                    <p class="text-slate-500 font-medium mt-4">
                        Version: <span
                            class="font-bold text-primary"><?= htmlspecialchars($activeVersion['version_name']) ?></span> •
                        Taille: <span class="font-bold"><?= round($activeVersion['file_size'] / 1024 / 1024, 2) ?> MB</span>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Installation Steps -->
            <div class="space-y-8">
                <div class="bg-gradient-to-br from-blue-50 to-slate-50 p-8 rounded-3xl border border-blue-100">
                    <div class="flex items-start space-x-4">
                        <div
                            class="w-12 h-12 bg-primary text-white rounded-2xl flex items-center justify-center font-black text-xl flex-shrink-0">
                            1
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-black text-slate-900 mb-3">Ouvrir le Menu du Navigateur</h3>
                            <p class="text-slate-600 font-medium mb-4">
                                Sur <span class="font-bold text-primary">Google Chrome</span> ou <span
                                    class="font-bold text-primary">Edge</span>, appuyez sur les <span
                                    class="font-black">trois points</span> en haut à droite de votre navigateur.
                            </p>
                            <div class="bg-white p-4 rounded-2xl border border-slate-200">
                                <p class="text-sm font-bold text-slate-500 flex items-center space-x-2">
                                    <i data-lucide="info" class="w-4 h-4"></i>
                                    <span>Assurez-vous d'être sur le site investian.infy.uk</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-emerald-50 to-slate-50 p-8 rounded-3xl border border-emerald-100">
                    <div class="flex items-start space-x-4">
                        <div
                            class="w-12 h-12 bg-success text-white rounded-2xl flex items-center justify-center font-black text-xl flex-shrink-0">
                            2
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-black text-slate-900 mb-3">Ajouter à l'Écran d'Accueil</h3>
                            <p class="text-slate-600 font-medium mb-4">
                                Dans le menu, sélectionnez <span class="font-black text-success">"Ajouter à l'écran
                                    d'accueil"</span> ou <span class="font-black text-success">"Installer
                                    l'application"</span>.
                            </p>
                            <div class="bg-white p-4 rounded-2xl border border-slate-200 space-y-2">
                                <p class="text-sm font-bold text-slate-700">📱 L'icône apparaîtra sur votre écran
                                    d'accueil</p>
                                <p class="text-sm font-bold text-slate-700">⚡ Accès instantané sans navigateur</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-red-50 to-slate-50 p-8 rounded-3xl border border-red-100">
                    <div class="flex items-start space-x-4">
                        <div
                            class="w-12 h-12 bg-danger text-white rounded-2xl flex items-center justify-center font-black text-xl flex-shrink-0">
                            3
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-black text-slate-900 mb-3">⚠️ Important : Google Play Protect</h3>
                            <p class="text-slate-600 font-medium mb-4">
                                Lors de l'installation, Google Play Protect peut afficher un avertissement. <span
                                    class="font-black text-danger">C'est normal !</span>
                            </p>
                            <div class="bg-white p-6 rounded-2xl border-2 border-danger space-y-4">
                                <div class="space-y-3">
                                    <div class="flex items-start space-x-3">
                                        <div
                                            class="w-6 h-6 bg-danger/10 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                            <i data-lucide="x" class="w-4 h-4 text-danger"></i>
                                        </div>
                                        <p class="text-sm font-bold text-slate-700">
                                            Si Google demande <span class="text-danger">"Analyser l'application"</span>
                                            → Cliquez sur <span class="font-black">"Ne pas envoyer"</span>
                                        </p>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <div
                                            class="w-6 h-6 bg-danger/10 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                            <i data-lucide="x" class="w-4 h-4 text-danger"></i>
                                        </div>
                                        <p class="text-sm font-bold text-slate-700">
                                            Si un message <span class="text-danger">"Application dangereuse"</span>
                                            apparaît → Cliquez sur <span class="font-black">"Installer quand
                                                même"</span>
                                        </p>
                                    </div>
                                    <div class="flex items-start space-x-3">
                                        <div
                                            class="w-6 h-6 bg-success/10 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                            <i data-lucide="check" class="w-4 h-4 text-success"></i>
                                        </div>
                                        <p class="text-sm font-bold text-slate-700">
                                            Confirmez l'installation et <span class="text-success font-black">c'est
                                                terminé !</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="bg-amber-50 p-4 rounded-xl border border-amber-200">
                                    <p class="text-xs font-bold text-amber-800 flex items-start space-x-2">
                                        <i data-lucide="shield-check" class="w-4 h-4 flex-shrink-0 mt-0.5"></i>
                                        <span>Ces avertissements sont normaux pour les applications web (PWA). Investian
                                            est 100% sécurisé et ne contient aucun virus.</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-50 to-slate-50 p-8 rounded-3xl border border-purple-100">
                    <div class="flex items-start space-x-4">
                        <div
                            class="w-12 h-12 bg-purple-500 text-white rounded-2xl flex items-center justify-center font-black text-xl flex-shrink-0">
                            4
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-black text-slate-900 mb-3">✅ Profitez de l'Application !</h3>
                            <p class="text-slate-600 font-medium mb-4">
                                L'application est maintenant installée sur votre téléphone. Ouvrez-la depuis votre écran
                                d'accueil !
                            </p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-white p-4 rounded-2xl border border-slate-200">
                                    <p class="text-sm font-bold text-slate-700 flex items-center space-x-2">
                                        <i data-lucide="zap" class="w-4 h-4 text-amber-500"></i>
                                        <span>Démarrage ultra-rapide</span>
                                    </p>
                                </div>
                                <div class="bg-white p-4 rounded-2xl border border-slate-200">
                                    <p class="text-sm font-bold text-slate-700 flex items-center space-x-2">
                                        <i data-lucide="wifi-off" class="w-4 h-4 text-blue-500"></i>
                                        <span>Fonctionne hors ligne</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA Button -->
            <div class="mt-12 text-center">
                <a href="<?= $user ? '/dashboard' : '/' ?>"
                    class="inline-flex items-center space-x-3 bg-gradient-to-r from-primary to-blue-600 hover:from-blue-600 hover:to-primary text-white font-black px-10 py-5 rounded-2xl shadow-2xl shadow-primary/30 hover:scale-105 transition-all">
                    <i data-lucide="home" class="w-6 h-6"></i>
                    <span class="text-lg">Retour à l'Accueil</span>
                </a>
            </div>
        </div>

        <!-- Help Section -->
        <div class="mt-12 bg-slate-100 p-8 rounded-3xl text-center">
            <h3 class="text-xl font-black text-slate-900 mb-3">Besoin d'aide ?</h3>
            <p class="text-slate-600 font-medium mb-6">
                Si vous rencontrez des difficultés lors de l'installation, contactez notre support.
            </p>
            <a href="/support" class="inline-flex items-center space-x-2 text-primary font-bold hover:underline">
                <i data-lucide="message-circle" class="w-5 h-5"></i>
                <span>Contacter le Support</span>
            </a>
        </div>
    </main>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>