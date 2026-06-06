<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
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
</head>

<body class="bg-gray-100 font-sans antialiased min-h-screen flex flex-col md:flex-row">
    <?php include __DIR__ . '/../partials/mobile_nav.php'; ?>

    <!-- Sidebar -->
    <aside class="hidden md:flex w-72 bg-white border-r border-slate-200 flex-col fixed h-full z-40">
        <div class="p-8 border-b border-slate-100 flex justify-center">
            <a href="/admin" class="hover:opacity-80 transition-opacity flex justify-center">
                <img src="/images/logo.png" alt="Investian Admin" class="h-10 w-auto">
            </a>
        </div>
        <nav class="flex-1 p-6 space-y-2 overflow-y-auto">
            <a href="/admin"
                class="flex items-center space-x-3 px-4 py-3.5 rounded-2xl text-slate-500 hover:bg-slate-50 hover:text-primary transition-all font-semibold">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i><span>Tableau de Bord</span>
            </a>
            <a href="/admin/recharges"
                class="flex items-center space-x-3 px-4 py-3.5 rounded-2xl text-slate-500 hover:bg-slate-50 hover:text-primary transition-all font-semibold">
                <i data-lucide="wallet" class="w-5 h-5"></i><span>Recharges</span>
            </a>
            <a href="/admin/withdrawals"
                class="flex items-center space-x-3 px-4 py-3.5 rounded-2xl text-slate-500 hover:bg-slate-50 hover:text-primary transition-all font-semibold">
                <i data-lucide="arrow-down-circle" class="w-5 h-5"></i><span>Retraits</span>
            </a>
            <a href="/admin/plans"
                class="flex items-center space-x-3 px-4 py-3.5 rounded-2xl text-slate-500 hover:bg-slate-50 hover:text-primary transition-all font-semibold">
                <i data-lucide="plus-square" class="w-5 h-5"></i><span>Gestion des Plans</span>
            </a>
            <a href="/admin/ads"
                class="flex items-center space-x-3 px-4 py-3.5 rounded-2xl text-slate-500 hover:bg-slate-50 hover:text-primary transition-all font-semibold">
                <i data-lucide="play-circle" class="w-5 h-5"></i><span>Gestion des Pubs</span>
            </a>
            <a href="/admin/guides"
                class="flex items-center space-x-3 px-4 py-3.5 rounded-2xl bg-primary/10 text-primary font-bold shadow-sm">
                <i data-lucide="book-open" class="w-5 h-5"></i><span>Guides Tutoriels</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 md:ml-72 p-4 md:p-10 pb-24 md:pb-10">
        <div class="max-w-4xl mx-auto">
            <a href="/admin/guides"
                class="inline-flex items-center text-slate-500 hover:text-blue-600 mb-6 transition-colors font-bold">
                <i data-lucide="chevron-left" class="w-5 h-5 mr-1"></i> Retour à la liste
            </a>
            <h1 class="text-3xl font-black text-slate-900 mb-8"><?= $title ?></h1>

            <form action="/admin/guides/create" method="POST" enctype="multipart/form-data" class="space-y-10">
                <!-- Guide Main Info -->
                <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100 space-y-6">
                    <h2 class="text-xl font-black text-slate-900 border-b border-slate-100 pb-4">Infos Générales</h2>

                    <div class="space-y-2">
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest px-1">Titre
                            du Guide</label>
                        <input type="text" name="title" required placeholder="Ex: Comment Investir"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>

                    <div class="space-y-2">
                        <label
                            class="block text-xs font-black text-slate-400 uppercase tracking-widest px-1">Description
                            Courte</label>
                        <input type="text" name="description" placeholder="Une brève explication sur le contenu..."
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest px-1">Contenu
                            d'introduction</label>
                        <textarea name="content" required rows="4" placeholder="Écrivez une introduction ici..."
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-900 font-semibold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label
                                class="block text-xs font-black text-slate-400 uppercase tracking-widest px-1 text-primary">Image
                                de Couverture</label>
                            <input type="file" name="image" accept="image/*"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest px-1">Ordre
                                d'affichage</label>
                            <input type="number" name="order_index" value="0"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label
                            class="block text-xs font-black text-slate-400 uppercase tracking-widest px-1">Statut</label>
                        <select name="status"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            <option value="active">Actif</option>
                            <option value="inactive">Inactif</option>
                        </select>
                    </div>
                </div>

                <!-- Guide Steps -->
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-black text-slate-900">Étapes du Guide</h2>
                        <button type="button" onclick="addStep()"
                            class="bg-slate-900 text-white text-xs font-bold px-4 py-2 rounded-lg hover:bg-slate-800 transition-all flex items-center space-x-2">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>Ajouter une Étape</span>
                        </button>
                    </div>

                    <div id="steps-container" class="space-y-6">
                        <!-- Steps will be injected here -->
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-primary hover:bg-blue-600 text-white font-black py-5 rounded-2xl transition-all shadow-xl shadow-primary/20 active:scale-[0.98] text-lg">
                    Enregistrer le Guide
                </button>
            </form>
        </div>
    </main>

    <!-- Template for adding steps -->
    <template id="step-template">
        <div
            class="step-card bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100 space-y-6 relative animate-fade-in">
            <button type="button" onclick="this.closest('.step-card').remove()"
                class="absolute top-6 right-6 text-danger hover:bg-red-50 p-2 rounded-lg transition-all">
                <i data-lucide="trash-2" class="w-5 h-5"></i>
            </button>
            <div class="flex items-center space-x-3 mb-2">
                <div
                    class="step-number w-8 h-8 bg-primary text-white rounded-lg flex items-center justify-center font-bold">
                    1</div>
                <h3 class="text-lg font-bold text-slate-900">Nouvelle Étape</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Titre de
                        l'étape (Optionnel)</label>
                    <input type="text" name="steps[INDEX][title]" placeholder="Ex: Étape 1 : Le Dépôt"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Type de
                        média</label>
                    <select name="steps[INDEX][media_type]"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        <option value="none">Aucun</option>
                        <option value="image">Image</option>
                        <option value="video">Vidéo</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Lien du
                        média (URL)</label>
                    <input type="text" name="steps[INDEX][media_url]" placeholder="https://..."
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>

                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-primary uppercase tracking-widest px-1">OU
                        Télécharger le média</label>
                    <input type="file" name="steps[INDEX][media_file]" accept="image/*,video/*"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Instructions /
                    Détails</label>
                <textarea name="steps[INDEX][content]" required rows="4"
                    placeholder="Décrivez les instructions pour cette étape..."
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-900 font-semibold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"></textarea>
            </div>
        </div>
    </template>

    <script>
        let stepCount = 0;
        const container = document.getElementById('steps-container');
        const template = document.getElementById('step-template');

        function addStep() {
            const clone = template.content.cloneNode(true);
            const html = clone.querySelector('.step-card').outerHTML.replace(/INDEX/g, stepCount);
            const div = document.createElement('div');
            div.innerHTML = html;
            container.appendChild(div.firstChild);

            stepCount++;
            updateStepNumbers();
            lucide.createIcons();
        }

        function updateStepNumbers() {
            const labels = container.querySelectorAll('.step-number');
            labels.forEach((label, index) => {
                label.textContent = index + 1;
            });
        }

        // Add first step by default
        window.onload = () => {
            addStep();
            lucide.createIcons();
        };

        lucide.createIcons();
    </script>
    <style>
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
            animation: fadeIn 0.3s ease-out forwards;
        }
    </style>
</body>

</html>