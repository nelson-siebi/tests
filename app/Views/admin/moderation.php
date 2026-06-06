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
</head>

<body class="bg-gray-50 text-slate-800 font-sans min-h-screen flex flex-col md:flex-row">
    <?php include __DIR__ . '/partials/mobile_nav.php'; ?>
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 md:ml-72 p-4 md:p-8 pb-24 md:pb-8">
        <div class="p-4 md:p-0">
            <header class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-slate-900">Modération des Avis</h1>
                <div class="flex items-center space-x-4">
                    <button onclick="openAddMessageModal()" 
                            class="bg-primary hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-bold transition-colors flex items-center space-x-2">
                        <i data-lucide="plus" class="w-5 h-5"></i>
                        <span>Ajouter un message</span>
                    </button>
                    <span
                        class="bg-gray-100 text-slate-600 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">Admin</span>
                </div>
            </header>

            <?php if (isset($_GET['success'])): ?>
                <div
                    class="bg-emerald-50 text-emerald-600 p-4 rounded-xl border border-emerald-100 mb-6 flex items-center space-x-2">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                    <span class="font-medium">
                        <?php if ($_GET['success'] == 'approved'): ?>
                            Message approuvé avec succès.
                        <?php elseif ($_GET['success'] == 'rejected'): ?>
                            Message rejeté.
                        <?php elseif ($_GET['success'] == 'message_created'): ?>
                            Message créé avec succès !
                        <?php else: ?>
                            Opération réussie.
                        <?php endif; ?>
                    </span>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div
                    class="bg-red-50 text-red-600 p-4 rounded-xl border border-red-100 mb-6 flex items-center space-x-2">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    <span class="font-medium">
                        <?php if ($_GET['error'] == 'empty_message'): ?>
                            Le message ne peut pas être vide.
                        <?php elseif ($_GET['error'] == 'creation_failed'): ?>
                            Erreur lors de la création du message.
                        <?php else: ?>
                            Une erreur s'est produite.
                        <?php endif; ?>
                    </span>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">
                                    Utilisateur</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">
                                    Message</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Date
                                </th>
                                <th
                                    class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php foreach ($messages as $msg): ?>
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-3">
                                            <div
                                                class="bg-primary/10 text-primary w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs">
                                                <?= strtoupper(substr($msg['user_name'], 0, 2)) ?>
                                            </div>
                                            <div>
                                                <span class="font-bold text-sm text-slate-700 block">
                                                    <?= htmlspecialchars($msg['user_name']) ?>
                                                </span>
                                                <?php if (!empty($msg['real_user_name']) && $msg['real_user_name'] !== $msg['user_name']): ?>
                                                    <span class="text-xs text-orange-500">
                                                        (faux nom - réel: <?= htmlspecialchars($msg['real_user_name']) ?>)
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-slate-600 max-w-lg">
                                            <?= htmlspecialchars($msg['message']) ?>
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-bold text-slate-400">
                                        <?= date('d M H:i', strtotime($msg['created_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <form action="/admin/moderation/approve" method="POST" class="inline-block">
                                            <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                                            <button type="submit"
                                                class="bg-emerald-50 text-emerald-600 hover:bg-emerald-100 p-2 rounded-lg transition-colors"
                                                title="Approuver">
                                                <i data-lucide="check" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                        <form action="/admin/moderation/reject" method="POST" class="inline-block">
                                            <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                                            <button type="submit"
                                                class="bg-red-50 text-red-600 hover:bg-red-100 p-2 rounded-lg transition-colors"
                                                title="Rejeter">
                                                <i data-lucide="x" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($messages)): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-slate-400 text-sm font-medium">
                                        Aucun message en attente de modération.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    </div>

    <!-- Modal pour ajouter un message -->
    <div id="addMessageModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-8 max-w-lg w-full mx-4 shadow-2xl">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-black text-slate-900">Ajouter un message</h2>
                <button onclick="closeAddMessageModal()" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <form action="/admin/moderation/create" method="POST" class="space-y-4">
                <!-- Sélection de l'utilisateur réel -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Utilisateur réel (qui envoie)</label>
                    <select name="user_id" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="<?= \App\Core\Session::get('user_id') ?>">Moi (Admin)</option>
                        <?php foreach ($users ?? [] as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name'] ?? $u['email']) ?> (<?= $u['email'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Faux nom (affiché aux autres) -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Nom affiché (laisser vide pour utiliser le vrai nom)
                    </label>
                    <input type="text" name="fake_name" 
                           class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="Ex: Jean Dupont">
                    <p class="text-xs text-slate-500 mt-1">Ce nom sera visible par tous les utilisateurs</p>
                </div>

                <!-- Message -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Message</label>
                    <textarea name="message" rows="4" required
                              class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent"
                              placeholder="Écrivez votre message ici..."></textarea>
                </div>

                <!-- Statut -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Statut</label>
                    <select name="status" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="approved">Approuvé immédiatement</option>
                        <option value="pending">En attente de modération</option>
                    </select>
                </div>

                <!-- Boutons -->
                <div class="flex space-x-3 pt-4">
                    <button type="button" onclick="closeAddMessageModal()"
                            class="flex-1 px-4 py-3 border border-slate-200 text-slate-700 rounded-xl font-bold hover:bg-slate-50 transition-colors">
                        Annuler
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-3 bg-primary text-white rounded-xl font-bold hover:bg-blue-700 transition-colors">
                        Publier
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddMessageModal() {
            document.getElementById('addMessageModal').classList.remove('hidden');
            document.getElementById('addMessageModal').classList.add('flex');
            lucide.createIcons();
        }

        function closeAddMessageModal() {
            document.getElementById('addMessageModal').classList.add('hidden');
            document.getElementById('addMessageModal').classList.remove('flex');
        }

        // Fermer le modal en cliquant en dehors
        document.getElementById('addMessageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddMessageModal();
            }
        });

        lucide.createIcons();
    </script>
</body>

</html>