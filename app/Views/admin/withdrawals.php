<?php
$pageTitle = $title ?? 'Admin';
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

<body class="bg-slate-50 text-slate-800 font-sans min-h-screen flex flex-col md:flex-row">
    <?php include __DIR__ . '/partials/mobile_nav.php'; ?>

    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <main class="flex-1 md:ml-72 p-4 md:p-10 pb-24 md:pb-10">
        <div class="flex justify-between items-center mb-10">
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Gestion des Retraits</h1>
            <div class="flex items-center space-x-4">
                <!-- Bouton pour ouvrir le modal de création manuelle -->
                <button onclick="openManualWithdrawalModal()" 
                        class="bg-primary hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-bold transition-colors flex items-center space-x-2">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                    <span>Ajouter un retrait</span>
                </button>
                <div class="bg-white px-6 py-3 rounded-xl border border-slate-100 shadow-sm">
                    <span class="text-xs text-slate-400 font-black uppercase tracking-widest block">En Attente</span>
                    <span class="text-2xl font-black text-warning">
                        <?= $pendingCount ?>
                    </span>
                </div>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="bg-emerald-50 border border-emerald-100 p-6 rounded-2xl mb-6">
                <div class="flex items-center space-x-3 text-emerald-600">
                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                    <span class="font-bold">
                        <?php if ($_GET['success'] === 'approved'): ?>
                            Retrait approuvé avec succès !
                        <?php elseif ($_GET['success'] === 'rejected'): ?>
                            Retrait rejeté et montant remboursé à l'utilisateur.
                        <?php elseif ($_GET['success'] === 'manual_withdrawal_created'): ?>
                            Retrait manuel créé avec succès !
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
                        <?php if ($_GET['error'] === 'already_processed'): ?>
                            Ce retrait a déjà été traité.
                        <?php elseif ($_GET['error'] === 'not_found'): ?>
                            Retrait introuvable.
                        <?php elseif ($_GET['error'] === 'invalid_amount'): ?>
                            Le montant doit être supérieur à 0.
                        <?php elseif ($_GET['error'] === 'missing_phone'): ?>
                            Le numéro de téléphone est requis.
                        <?php elseif ($_GET['error'] === 'creation_failed'): ?>
                            Erreur lors de la création du retrait.
                        <?php else: ?>
                            Une erreur s'est produite.
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>

        <div class="bg-white border border-slate-100 rounded-[2.5rem] overflow-x-auto shadow-2xl">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-xs font-black text-slate-400 uppercase tracking-widest text-[10px]">
                    <tr>
                        <th class="px-8 py-6">Utilisateur</th>
                        <th class="px-4 py-6">Montant</th>
                        <th class="px-4 py-6">Date</th>
                        <th class="px-4 py-6">Statut</th>
                        <th class="px-8 py-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($withdrawals)): ?>
                        <tr>
                            <td colspan="5" class="px-8 py-12 text-center text-slate-400">
                                <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                                <p class="font-bold">Aucune demande de retrait</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($withdrawals as $withdrawal): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-8 py-6">
                                    <div>
                                        <span class="text-slate-900 font-bold block">
                                            <?= htmlspecialchars($withdrawal['user_name']) ?>
                                        </span>
                                        <span class="text-xs text-slate-400">
                                            <?= htmlspecialchars($withdrawal['user_email']) ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-6 font-black text-slate-900">
                                    <?= number_format($withdrawal['amount'], 0, '.', ' ') ?> XAF
                                </td>
                                <td class="px-4 py-6 text-sm text-slate-500">
                                    <?= date('d/m/Y H:i', strtotime($withdrawal['created_at'])) ?>
                                </td>
                                <td class="px-4 py-6">
                                    <?php if ($withdrawal['status'] === 'pending'): ?>
                                        <span
                                            class="bg-warning/10 text-warning px-3 py-1 rounded-full text-xs font-black uppercase">En
                                            Attente</span>
                                    <?php elseif ($withdrawal['status'] === 'completed'): ?>
                                        <span
                                            class="bg-success/10 text-success px-3 py-1 rounded-full text-xs font-black uppercase">Approuvé</span>
                                    <?php else: ?>
                                        <span
                                            class="bg-danger/10 text-danger px-3 py-1 rounded-full text-xs font-black uppercase">Rejeté</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <?php if ($withdrawal['status'] === 'pending'): ?>
                                        <div class="flex justify-end space-x-2">
                                            <form action="/admin/withdrawals/approve" method="POST" class="inline">
                                                <input type="hidden" name="id" value="<?= $withdrawal['id'] ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit"
                                                    class="p-2 text-success hover:bg-success/10 rounded-lg transition-colors"
                                                    onclick="return confirm('Approuver ce retrait ?')">
                                                    <i data-lucide="check" class="w-5 h-5"></i>
                                                </button>
                                            </form>
                                            <form action="/admin/withdrawals/approve" method="POST" class="inline">
                                                <input type="hidden" name="id" value="<?= $withdrawal['id'] ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit"
                                                    class="p-2 text-danger hover:bg-danger/10 rounded-lg transition-colors"
                                                    onclick="return confirm('Rejeter ce retrait ? Le montant sera remboursé à l\'utilisateur.')">
                                                    <i data-lucide="x" class="w-5 h-5"></i>
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-slate-300 text-sm">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal pour créer un retrait manuel -->
    <div id="manualWithdrawalModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 shadow-2xl">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-black text-slate-900">Ajouter un retrait manuel</h2>
                <button onclick="closeManualWithdrawalModal()" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <form action="/admin/withdrawals/add-manual" method="POST" class="space-y-4">
                <!-- Sélection de l'utilisateur -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Utilisateur</label>
                    <select name="user_id" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="<?= \App\Core\Session::get('user_id') ?>">Moi (Admin)</option>
                        <?php foreach ($users ?? [] as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name'] ?? $u['email']) ?> (<?= $u['email'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Montant -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Montant (FCFA)</label>
                    <input type="number" name="amount" min="1" required
                           class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="Ex: 5000">
                </div>

                <!-- Source -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Source du solde</label>
                    <select name="source" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="investissement">Investissement</option>
                        <option value="publicite">Publicité</option>
                        <option value="parrainage">Parrainage</option>
                    </select>
                </div>

                <!-- Méthode de paiement -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Méthode de retrait</label>
                    <select name="method" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="orange">Orange Money</option>
                        <option value="mtn">MTN Mobile Money</option>
                    </select>
                </div>

                <!-- Numéro de téléphone -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Numéro de téléphone</label>
                    <input type="tel" name="phone_number" required
                           class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="Ex: 612345678">
                </div>

                <!-- Statut -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Statut initial</label>
                    <select name="status" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="attente">En attente</option>
                        <option value="success">Validé immédiatement</option>
                    </select>
                    <p class="text-xs text-slate-500 mt-1">"Validé immédiatement" déduira le solde du wallet</p>
                </div>

                <!-- Note -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Note (optionnel)</label>
                    <textarea name="note" rows="2"
                              class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent"
                              placeholder="Note interne..."></textarea>
                </div>

                <!-- Boutons -->
                <div class="flex space-x-3 pt-4">
                    <button type="button" onclick="closeManualWithdrawalModal()"
                            class="flex-1 px-4 py-3 border border-slate-200 text-slate-700 rounded-xl font-bold hover:bg-slate-50 transition-colors">
                        Annuler
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-3 bg-primary text-white rounded-xl font-bold hover:bg-blue-700 transition-colors">
                        Créer le retrait
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openManualWithdrawalModal() {
            document.getElementById('manualWithdrawalModal').classList.remove('hidden');
            document.getElementById('manualWithdrawalModal').classList.add('flex');
            lucide.createIcons();
        }

        function closeManualWithdrawalModal() {
            document.getElementById('manualWithdrawalModal').classList.add('hidden');
            document.getElementById('manualWithdrawalModal').classList.remove('flex');
        }

        // Fermer le modal en cliquant en dehors
        document.getElementById('manualWithdrawalModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeManualWithdrawalModal();
            }
        });

        lucide.createIcons();
    </script>
</body>

</html>