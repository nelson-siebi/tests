<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $title ?> - Admin Investian
    </title>
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

<body class="bg-slate-50 text-slate-800 font-sans min-h-screen flex">
    <main class="flex-1 p-12 max-w-4xl mx-auto">
        <a href="/admin/plans"
            class="inline-flex items-center space-x-2 text-slate-400 hover:text-primary font-bold mb-8 transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
            <span>Retour aux plans</span>
        </a>

        <div class="bg-white p-10 md:p-12 rounded-[2.5rem] shadow-2xl shadow-slate-200/50 border border-slate-100">
            <h1 class="text-4xl font-black text-slate-900 mb-2 tracking-tight">Modifier le Plan</h1>
            <p class="text-slate-500 font-bold mb-10 italic">Modification de
                <?= htmlspecialchars($plan['name']) ?>
            </p>

            <form action="/admin/plans/edit" method="POST" enctype="multipart/form-data" class="space-y-8">
                <input type="hidden" name="id" value="<?= $plan['id'] ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Nom du
                            Plan</label>
                        <input type="text" name="name" required value="<?= htmlspecialchars($plan['name']) ?>"
                            class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-6 py-4 text-slate-900 font-black focus:outline-none focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all"
                            placeholder="Ex: Plan Étoile, Plan Pro...">
                    </div>

                    <div class="md:col-span-2">
                        <label
                            class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Description</label>
                        <textarea name="description" rows="3"
                            class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-6 py-4 text-slate-900 font-medium focus:outline-none focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all"
                            placeholder="Avantages du plan..."><?= htmlspecialchars($plan['description']) ?></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Image du
                            Plan (Laisser vide pour conserver)</label>
                        <div class="flex items-center space-x-4 mb-2">
                            <img src="<?= htmlspecialchars($plan['image_url']) ?>"
                                class="w-16 h-16 object-cover rounded-xl border border-slate-200">
                            <span class="text-xs text-slate-400 font-medium italic">Image actuelle</span>
                        </div>
                        <input type="file" name="image" accept="image/*"
                            class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-6 py-4 text-slate-900 font-medium focus:outline-none focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Prix Fixe
                            (XAF)</label>
                        <input type="number" name="price" required min="1000" value="<?= $plan['price'] ?>"
                            class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-6 py-4 text-slate-900 font-black focus:outline-none focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all"
                            placeholder="5000">
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Profit
                            Quotidien (XAF)</label>
                        <input type="number" name="daily_profit_amount" required
                            value="<?= (int) $plan['daily_profit_amount'] ?>"
                            class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-4 text-emerald-500 font-black focus:outline-none focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all"
                            placeholder="500">
                    </div>

                    <div>
                        <label class="block text-xs font-black text-amber-500 uppercase tracking-widest mb-3">Nombre de
                            Pubs / Jour</label>
                        <input type="number" name="ads_per_day" value="<?= $plan['ads_per_day'] ?>" required
                            class="w-full bg-amber-50 border border-amber-100 rounded-2xl px-6 py-4 text-amber-600 font-black focus:outline-none focus:ring-4 focus:ring-amber-500/5 focus:border-amber-500 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-black text-blue-500 uppercase tracking-widest mb-3">Durée de
                            l'Investissement</label>
                        <div
                            class="w-full bg-blue-50 border border-blue-100 rounded-2xl px-6 py-4 text-blue-600 font-black">
                            30 Jours (Fixe)
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2 pt-6">
                    <button type="submit"
                        class="w-full bg-slate-900 hover:bg-slate-800 text-white font-black py-6 rounded-3xl transition-all shadow-2xl shadow-slate-200 active:scale-[0.98]">
                        Enregistrer les Modifications
                    </button>
                </div>
            </form>
        </div>
    </main>
    <script> lucide.createIcons(); </script>
</body>

</html>