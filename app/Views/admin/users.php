<?php
$pageTitle = $title ?? 'Gestion Utilisateurs';
?>
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
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
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

<body class="bg-slate-50 text-slate-800 font-sans min-h-screen flex flex-col md:flex-row"
    x-data="{ showEditModal: false, showPlanModal: false, currentUser: {}, currentUserId: null }">
    <?php include __DIR__ . '/partials/mobile_nav.php'; ?>

    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <main class="flex-1 md:ml-72 p-4 md:p-10 pb-24 md:pb-10">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-10 gap-4">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Utilisateurs</h1>
                <p class="text-slate-500 mt-2">Gérez les comptes utilisateurs, soldes et plans</p>
            </div>

            <form action="/admin/users" method="GET" class="relative w-full md:w-96">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                    placeholder="Rechercher par nom ou email..."
                    class="w-full bg-white border border-slate-200 rounded-2xl pl-12 pr-4 py-3 text-slate-700 font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm">
                <i data-lucide="search"
                    class="w-5 h-5 text-slate-400 absolute left-4 top-1/2 transform -translate-y-1/2"></i>
            </form>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="bg-emerald-50 border border-emerald-100 p-6 rounded-2xl mb-6">
                <div class="flex items-center space-x-3 text-emerald-600">
                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                    <span class="font-bold">
                        <?php if ($_GET['success'] === 'updated'): ?>
                            Utilisateur mis à jour avec succès !
                        <?php elseif ($_GET['success'] === 'plan_added'): ?>
                            Plan ajouté à l'utilisateur !
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-50 border border-red-100 p-6 rounded-2xl mb-6">
                <div class="flex items-center space-x-3 text-red-600">
                    <i data-lucide="alert-circle" class="w-6 h-6"></i>
                    <span class="font-bold">
                        Une erreur est survenue.
                    </span>
                </div>
            </div>
        <?php endif; ?>

        <div class="bg-white border border-slate-100 rounded-[2.5rem] overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-xs font-black text-slate-400 uppercase tracking-widest text-[10px]">
                        <tr>
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Utilisateur</th>
                            <th class="px-6 py-4">Email</th>
                            <th class="px-6 py-4">Solde</th>
                            <th class="px-6 py-4">Rôle</th>
                            <th class="px-6 py-4">Statut</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" class="px-8 py-12 text-center text-slate-400">
                                    <p class="font-bold">Aucun utilisateur trouvé</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 font-bold text-slate-400">#
                                        <?= $user['id'] ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-3">
                                            <div
                                                class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold text-xs">
                                                <?= strtoupper(substr($user['name'], 0, 2)) ?>
                                            </div>
                                            <span class="font-bold text-slate-900">
                                                <?= htmlspecialchars($user['name']) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-500">
                                        <?= htmlspecialchars($user['email']) ?>
                                    </td>
                                    <td class="px-6 py-4 font-black text-slate-800">
                                        <?= number_format($user['balance'], 0) ?> XAF
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="px-2 py-1 rounded text-xs font-bold uppercase <?= $user['role'] === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-500' ?>">
                                            <?= $user['role'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if (($user['status'] ?? 'active') === 'active'): ?>
                                            <span
                                                class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold uppercase">Actif</span>
                                        <?php else: ?>
                                            <span
                                                class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold uppercase">Inactif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end space-x-2">
                                            <button @click="showPlanModal = true; currentUserId = <?= $user['id'] ?>"
                                                class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                                title="Ajouter un Plan">
                                                <i data-lucide="plus-square" class="w-5 h-5"></i>
                                            </button>
                                            <button
                                                @click="showEditModal = true; currentUser = <?= htmlspecialchars(json_encode($user)) ?>"
                                                class="p-2 text-primary hover:bg-blue-50 rounded-lg transition-colors"
                                                title="Modifier">
                                                <i data-lucide="edit" class="w-5 h-5"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Edit User Modal -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div @click="showEditModal = false" class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-3xl shadow-2xl max-w-lg w-full p-8 animate-fade-in">
                <h2 class="text-2xl font-black text-slate-900 mb-6">Modifier Utilisateur</h2>

                <form action="/admin/users/update" method="POST" class="space-y-6">
                    <input type="hidden" name="id" x-model="currentUser.id">

                    <div>
                        <label class="block text-sm font-black text-slate-700 mb-2">Solde (XAF)</label>
                        <input type="number" name="balance" x-model="currentUser.balance" required min="0" step="any"
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-black text-slate-700 mb-2">Rôle</label>
                        <select name="role" x-model="currentUser.role"
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="user">Utilisateur</option>
                            <option value="admin">Administrateur</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-black text-slate-700 mb-2">Statut</label>
                        <select name="status" x-model="currentUser.status"
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="active">Actif</option>
                            <option value="suspended">Suspendu</option>
                        </select>
                    </div>

                    <div class="flex space-x-4 pt-4">
                        <button type="submit"
                            class="flex-1 bg-primary hover:bg-blue-600 text-white font-black py-4 rounded-xl transition-all shadow-lg">
                            Enregistrer
                        </button>
                        <button type="button" @click="showEditModal = false"
                            class="px-6 bg-slate-100 hover:bg-slate-200 text-slate-700 font-black py-4 rounded-xl transition-all">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Plan Modal -->
    <div x-show="showPlanModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div @click="showPlanModal = false" class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-3xl shadow-2xl max-w-lg w-full p-8 animate-fade-in">
                <h2 class="text-2xl font-black text-slate-900 mb-6">Ajouter un Plan Manuellement</h2>
                <p class="text-slate-500 mb-6 text-sm">Ceci ajoutera un plan d'investissement actif à l'utilisateur sans
                    débiter son solde (Offert).</p>

                <form action="/admin/users/plan" method="POST" class="space-y-6">
                    <input type="hidden" name="user_id" x-model="currentUserId">

                    <div>
                        <label class="block text-sm font-black text-slate-700 mb-2">Sélectionner un Plan</label>
                        <select name="plan_id" required
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">-- Choisir un plan --</option>
                            <?php foreach ($plans as $plan): ?>
                                <option value="<?= $plan['id'] ?>">
                                    <?= htmlspecialchars($plan['name']) ?> -
                                    <?= number_format($plan['price'], 0) ?> XAF (
                                    <?= number_format($plan['daily_profit_amount'], 0) ?> XAF/jour)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="flex space-x-4 pt-4">
                        <button type="submit"
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-black py-4 rounded-xl transition-all shadow-lg">
                            Activer le Plan
                        </button>
                        <button type="button" @click="showPlanModal = false"
                            class="px-6 bg-slate-100 hover:bg-slate-200 text-slate-700 font-black py-4 rounded-xl transition-all">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script> lucide.createIcons(); </script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.2s ease-out forwards;
        }
    </style>
</body>

</html>