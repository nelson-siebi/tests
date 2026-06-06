<?php
// partials/users_table.php
// Cette partie est chargée via AJAX
?>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left">ID</th>
                    <th class="px-4 py-3 text-left">Utilisateur</th>
                    <th class="px-4 py-3 text-left">Solde</th>
                    <th class="px-4 py-3 text-left">Statut</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $user): ?>
                <tr class="border-t" id="user-row-<?= $user['id'] ?>">
                    <td class="px-4 py-3">#<?= $user['id'] ?></td>
                    <td class="px-4 py-3">
                        <div><?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?></div>
                        <div class="text-sm text-gray-500"><?= htmlspecialchars($user['email']) ?></div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-medium"><?= number_format($user['solde_investissement'] + $user['solde_publicite'] + $user['solde_parrainage'], 0) ?> FCFA</div>
                    </td>
                    <td class="px-4 py-3">
                        <select class="status-select border rounded px-2 py-1 text-sm"
                                data-user-id="<?= $user['id'] ?>"
                                onchange="changeUserStatus(<?= $user['id'] ?>, this.value)">
                            <option value="active" <?= $user['statut'] == 'active' ? 'selected' : '' ?>>Actif</option>
                            <option value="banned" <?= $user['statut'] == 'banned' ? 'selected' : '' ?>>Banni</option>
                            <option value="pending" <?= $user['statut'] == 'pending' ? 'selected' : '' ?>>En attente</option>
                        </select>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex space-x-2">
                            <button onclick="editUser(<?= $user['id'] ?>)" 
                                    class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="viewUserDetails(<?= $user['id'] ?>)" 
                                    class="text-green-600 hover:text-green-900">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="resetPassword(<?= $user['id'] ?>)" 
                                    class="text-purple-600 hover:text-purple-900">
                                <i class="fas fa-key"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination AJAX -->
    <?php if($total_pages > 1): ?>
    <div class="px-4 py-3 border-t">
        <div class="flex justify-between items-center">
            <div>Page <?= $page ?> sur <?= $total_pages ?></div>
            <div class="flex space-x-2">
                <?php if($page > 1): ?>
                    <button onclick="changePage(<?= $page - 1 ?>)" 
                            class="px-3 py-1 border rounded hover:bg-gray-100">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                <?php endif; ?>
                
                <?php for($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <button onclick="changePage(<?= $i ?>)" 
                            class="px-3 py-1 border rounded <?= $i == $page ? 'bg-blue-500 text-white' : 'hover:bg-gray-100' ?>">
                        <?= $i ?>
                    </button>
                <?php endfor; ?>
                
                <?php if($page < $total_pages): ?>
                    <button onclick="changePage(<?= $page + 1 ?>)" 
                            class="px-3 py-1 border rounded hover:bg-gray-100">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>