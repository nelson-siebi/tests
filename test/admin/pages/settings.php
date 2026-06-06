<?php
// Récupérer les paramètres
$settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);

// Sauvegarder les paramètres
if(isset($_POST['save_settings'])) {
    foreach($_POST['settings'] as $key => $value) {
        $stmt = $pdo->prepare("
            INSERT INTO settings (id, value, updated_at) 
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE value = ?, updated_at = NOW()
        ");
        $stmt->execute([$key, $value, $value]);
    }
    
    // Traitement spécial pour les fichiers
    if(isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $upload_dir = '../uploads/';
        if(!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_name = 'logo_' . time() . '.' . pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $file_path = $upload_dir . $file_name;
        
        if(move_uploaded_file($_FILES['logo']['tmp_name'], $file_path)) {
            $pdo->prepare("
                INSERT INTO settings (id, value, updated_at) 
                VALUES ('logo_url', ?, NOW())
                ON DUPLICATE KEY UPDATE value = ?, updated_at = NOW()
            ")->execute([$file_name, $file_name]);
        }
    }
    
    $success = "Paramètres enregistrés avec succès";
    header("Refresh:0");
    exit;
}
?>

<div class="p-4 lg:p-6">
    <div class="mb-6">
        <h3 class="text-xl font-bold">Paramètres de la Plateforme</h3>
        <p class="text-gray-600">Configurez les paramètres généraux de votre plateforme</p>
    </div>

    <?php if(isset($success)): ?>
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            <i class="fas fa-check-circle mr-2"></i>
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-8">
        <!-- Général -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 lg:p-6 border-b">
                <h4 class="text-lg font-semibold">
                    <i class="fas fa-cog mr-2"></i>Paramètres Généraux
                </h4>
            </div>
            <div class="p-4 lg:p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nom de la plateforme</label>
                        <input type="text" name="settings[platform_name]" 
                               value="<?= htmlspecialchars($settings['platform_name'] ?? 'Invest Platform') ?>"
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Logo de la plateforme</label>
                        <input type="file" name="logo" accept="image/*"
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <?php if(isset($settings['logo_url'])): ?>
                            <div class="mt-2">
                                <img src="../uploads/<?= htmlspecialchars($settings['logo_url']) ?>" 
                                     alt="Logo" class="h-12 w-auto">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email de contact</label>
                    <input type="email" name="settings[contact_email]" 
                           value="<?= htmlspecialchars($settings['contact_email'] ?? 'contact@invest.com') ?>"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone de support</label>
                    <input type="text" name="settings[support_phone]" 
                           value="<?= htmlspecialchars($settings['support_phone'] ?? '+237 XXX XXX XXX') ?>"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Adresse</label>
                    <textarea name="settings[address]" rows="2"
                              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($settings['address'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Financier -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 lg:p-6 border-b">
                <h4 class="text-lg font-semibold">
                    <i class="fas fa-money-bill-wave mr-2"></i>Paramètres Financiers
                </h4>
            </div>
            <div class="p-4 lg:p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Devise</label>
                        <select name="settings[currency]" class="w-full px-3 py-2 border rounded-lg">
                            <option value="FCFA" <?= ($settings['currency'] ?? 'FCFA') == 'FCFA' ? 'selected' : '' ?>>FCFA</option>
                            <option value="USD" <?= ($settings['currency'] ?? '') == 'USD' ? 'selected' : '' ?>>USD</option>
                            <option value="EUR" <?= ($settings['currency'] ?? '') == 'EUR' ? 'selected' : '' ?>>EUR</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Symbole de la devise</label>
                        <input type="text" name="settings[currency_symbol]" 
                               value="<?= htmlspecialchars($settings['currency_symbol'] ?? 'FCFA') ?>"
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Dépôt minimum (FCFA)</label>
                        <input type="number" name="settings[min_deposit]" 
                               value="<?= htmlspecialchars($settings['min_deposit'] ?? '1000') ?>"
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Retrait minimum (FCFA)</label>
                        <input type="number" name="settings[min_withdrawal]" 
                               value="<?= htmlspecialchars($settings['min_withdrawal'] ?? '5000') ?>"
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Commission de retrait (%)</label>
                        <input type="number" step="0.01" name="settings[withdrawal_fee]" 
                               value="<?= htmlspecialchars($settings['withdrawal_fee'] ?? '1') ?>"
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Commission parrainage (%)</label>
                        <input type="number" step="0.01" name="settings[referral_commission]" 
                               value="<?= htmlspecialchars($settings['referral_commission'] ?? '10') ?>"
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
        </div>

        <!-- Vidéos publicitaires -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 lg:p-6 border-b">
                <h4 class="text-lg font-semibold">
                    <i class="fas fa-video mr-2"></i>Paramètres Vidéos
                </h4>
            </div>
            <div class="p-4 lg:p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Vidéos par jour maximum</label>
                        <input type="number" name="settings[max_videos_per_day]" 
                               value="<?= htmlspecialchars($settings['max_videos_per_day'] ?? '10') ?>"
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Durée minimale de visionnage (secondes)</label>
                        <input type="number" name="settings[min_video_watch_time]" 
                               value="<?= htmlspecialchars($settings['min_video_watch_time'] ?? '30') ?>"
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Gain minimum par vidéo (FCFA)</label>
                        <input type="number" step="0.01" name="settings[min_video_earn]" 
                               value="<?= htmlspecialchars($settings['min_video_earn'] ?? '10') ?>"
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Gain maximum par vidéo (FCFA)</label>
                        <input type="number" step="0.01" name="settings[max_video_earn]" 
                               value="<?= htmlspecialchars($settings['max_video_earn'] ?? '100') ?>"
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sécurité -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 lg:p-6 border-b">
                <h4 class="text-lg font-semibold">
                    <i class="fas fa-shield-alt mr-2"></i>Paramètres de Sécurité
                </h4>
            </div>
            <div class="p-4 lg:p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tentatives de connexion max</label>
                        <input type="number" name="settings[max_login_attempts]" 
                               value="<?= htmlspecialchars($settings['max_login_attempts'] ?? '5') ?>"
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Délai de blocage (minutes)</label>
                        <input type="number" name="settings[lockout_time]" 
                               value="<?= htmlspecialchars($settings['lockout_time'] ?? '15') ?>"
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Session timeout (minutes)</label>
                    <input type="number" name="settings[session_timeout]" 
                           value="<?= htmlspecialchars($settings['session_timeout'] ?? '60') ?>"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mots-clés interdits (séparés par des virgules)</label>
                    <textarea name="settings[banned_keywords]" rows="3"
                              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($settings['banned_keywords'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Maintenance -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 lg:p-6 border-b">
                <h4 class="text-lg font-semibold">
                    <i class="fas fa-tools mr-2"></i>Mode Maintenance
                </h4>
            </div>
            <div class="p-4 lg:p-6">
                <div class="flex items-center mb-4">
                    <input type="checkbox" id="maintenance_mode" name="settings[maintenance_mode]" 
                           value="1" <?= ($settings['maintenance_mode'] ?? '0') == '1' ? 'checked' : '' ?>
                           class="mr-2">
                    <label for="maintenance_mode" class="text-sm font-medium text-gray-700">
                        Activer le mode maintenance
                    </label>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Message de maintenance</label>
                    <textarea name="settings[maintenance_message]" rows="3"
                              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($settings['maintenance_message'] ?? 'La plateforme est actuellement en maintenance. Merci de revenir plus tard.') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="flex justify-end space-x-4">
            <button type="reset" 
                    class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                <i class="fas fa-undo mr-2"></i>Réinitialiser
            </button>
            
            <button type="submit" name="save_settings"
                    class="px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                <i class="fas fa-save mr-2"></i>Enregistrer les paramètres
            </button>
        </div>
    </form>
</div>