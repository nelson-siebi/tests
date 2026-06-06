<?php
// pages/home.php

// Simulation de données (à remplacer par votre BDD)
$user_stats = [
    'solde_total' => 12450,
    'solde_investissement' => 8200,
    'solde_publicite' => 3150,
    'solde_parrainage' => 1100,
    'investissements_actifs' => 2,
    'gain_journalier' => 450,
    'videos_restantes' => 7,
    'parrains_total' => 8
];

$transactions = [
    ['type' => 'roi', 'description' => 'ROI quotidien Plan Pro', 'montant' => 500, 'date' => 'Aujourd\'hui, 14:30', 'statut' => 'success'],
    ['type' => 'retrait', 'description' => 'Retrait Orange Money', 'montant' => -2000, 'date' => 'Aujourd\'hui, 10:15', 'statut' => 'pending'],
    ['type' => 'video', 'description' => 'Visionnage vidéo', 'montant' => 25, 'date' => 'Hier, 16:45', 'statut' => 'success'],
    ['type' => 'parrainage', 'description' => 'Bonus parrainage Marie K.', 'montant' => 350, 'date' => 'Hier, 09:20', 'statut' => 'success']
];
?>

<div class="page-transition">
    <!-- Bannière de bienvenue -->
    <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-2xl p-6 text-white shadow-lg mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold mb-2">Bonjour, John ! 👋</h2>
                <p class="text-green-100 opacity-90">Voici votre tableau de bord</p>
            </div>
            <div class="mt-4 md:mt-0">
                <p class="text-sm text-green-100">Solde total disponible</p>
                <p class="text-3xl md:text-4xl font-bold">
                    <?php echo number_format($user_stats['solde_total'], 0, ',', ' '); ?> 
                    <span class="text-xl">FCFA</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Cartes de statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Solde investissement -->
        <a href="?page=investissement" class="card-link">
            <div class="bg-white rounded-xl shadow-sm p-5 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-sm text-gray-500">Solde investissement</p>
                        <p class="text-2xl font-bold text-gray-800">
                            <?php echo number_format($user_stats['solde_investissement'], 0, ',', ' '); ?> 
                            <span class="text-lg">FCFA</span>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-chart-line text-green-600 text-xl"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500">
                    <i class="fas fa-bolt text-yellow-500 mr-1"></i>
                    <?php echo $user_stats['investissements_actifs']; ?> plan(s) actif(s)
                </p>
            </div>
        </a>
        
        <!-- Solde publicité -->
        <a href="?page=videos" class="card-link">
            <div class="bg-white rounded-xl shadow-sm p-5 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-sm text-gray-500">Solde publicité</p>
                        <p class="text-2xl font-bold text-gray-800">
                            <?php echo number_format($user_stats['solde_publicite'], 0, ',', ' '); ?> 
                            <span class="text-lg">FCFA</span>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-video text-blue-600 text-xl"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500">
                    <i class="fas fa-film text-blue-500 mr-1"></i>
                    <?php echo $user_stats['videos_restantes']; ?> vidéos restantes aujourd'hui
                </p>
            </div>
        </a>
        
        <!-- Solde parrainage -->
        <a href="?page=parrainage" class="card-link">
            <div class="bg-white rounded-xl shadow-sm p-5 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-sm text-gray-500">Solde parrainage</p>
                        <p class="text-2xl font-bold text-gray-800">
                            <?php echo number_format($user_stats['solde_parrainage'], 0, ',', ' '); ?> 
                            <span class="text-lg">FCFA</span>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-users text-purple-600 text-xl"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500">
                    <i class="fas fa-user-plus text-purple-500 mr-1"></i>
                    <?php echo $user_stats['parrains_total']; ?> personne(s) parrainée(s)
                </p>
            </div>
        </a>
        
        <!-- Gain journalier -->
        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-xl shadow-sm p-5 text-white">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-sm text-yellow-100">Gain aujourd'hui</p>
                    <p class="text-2xl font-bold">
                        +<?php echo number_format($user_stats['gain_journalier'], 0, ',', ' '); ?> 
                        <span class="text-lg">FCFA</span>
                    </p>
                </div>
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <i class="fas fa-coins text-xl"></i>
                </div>
            </div>
            <p class="text-sm text-yellow-100">
                <i class="fas fa-arrow-up mr-1"></i>
                +5% par rapport à hier
            </p>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="mb-8">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Actions rapides</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Investir -->
            <a href="?page=investissement" class="action-card">
                <div class="action-icon bg-green-100">
                    <i class="fas fa-plus text-green-600 text-2xl"></i>
                </div>
                <span class="action-title">Investir</span>
                <span class="action-subtitle">Augmenter ses gains</span>
            </a>
            
            <!-- Retrait -->
            <button onclick="openRetraitModal()" class="action-card">
                <div class="action-icon bg-blue-100">
                    <i class="fas fa-download text-blue-600 text-2xl"></i>
                </div>
                <span class="action-title">Retrait</span>
                <span class="action-subtitle">Disponible immédiatement</span>
            </button>
            
            <!-- Vidéos -->
            <a href="?page=videos" class="action-card">
                <div class="action-icon bg-purple-100">
                    <i class="fas fa-play-circle text-purple-600 text-2xl"></i>
                </div>
                <span class="action-title">Vidéos</span>
                <span class="action-subtitle">Gagner en regardant</span>
            </a>
            
            <!-- Parrainage -->
            <a href="?page=parrainage" class="action-card">
                <div class="action-icon bg-yellow-100">
                    <i class="fas fa-user-plus text-yellow-600 text-2xl"></i>
                </div>
                <span class="action-title">Parrainer</span>
                <span class="action-subtitle">Gagner 15%</span>
            </a>
            
            <!-- Transactions -->
            <button onclick="showAllTransactions()" class="action-card">
                <div class="action-icon bg-red-100">
                    <i class="fas fa-exchange-alt text-red-600 text-2xl"></i>
                </div>
                <span class="action-title">Transactions</span>
                <span class="action-subtitle">Voir l'historique</span>
            </button>
            
            <!-- Support -->
            <a href="#" onclick="openSupport()" class="action-card">
                <div class="action-icon bg-indigo-100">
                    <i class="fas fa-headset text-indigo-600 text-2xl"></i>
                </div>
                <span class="action-title">Support</span>
                <span class="action-subtitle">Assistance 24/7</span>
            </a>
            
            <!-- Paramètres -->
            <a href="?page=settings" class="action-card">
                <div class="action-icon bg-gray-100">
                    <i class="fas fa-cog text-gray-600 text-2xl"></i>
                </div>
                <span class="action-title">Paramètres</span>
                <span class="action-subtitle">Compte & sécurité</span>
            </a>
            
            <!-- Déconnexion -->
            <a href="logout.php" class="action-card">
                <div class="action-icon bg-gray-200">
                    <i class="fas fa-sign-out-alt text-gray-700 text-2xl"></i>
                </div>
                <span class="action-title">Déconnexion</span>
                <span class="action-subtitle">Quitter la session</span>
            </a>
        </div>
    </div>

    <!-- Dernières transactions -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-800">Dernières transactions</h3>
            <button onclick="showAllTransactions()" class="text-green-600 hover:text-green-700 font-medium text-sm">
                Voir tout <i class="fas fa-arrow-right ml-1"></i>
            </button>
        </div>
        
        <div class="space-y-4">
            <?php foreach ($transactions as $transaction): ?>
            <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3 
                        <?php echo $transaction['type'] == 'roi' ? 'bg-green-100' : 
                              ($transaction['type'] == 'retrait' ? 'bg-red-100' : 
                              ($transaction['type'] == 'video' ? 'bg-blue-100' : 'bg-purple-100')); ?>">
                        <i class="fas 
                            <?php echo $transaction['type'] == 'roi' ? 'fa-coins text-green-600' : 
                                  ($transaction['type'] == 'retrait' ? 'fa-download text-red-600' : 
                                  ($transaction['type'] == 'video' ? 'fa-video text-blue-600' : 'fa-user-plus text-purple-600')); ?>">
                        </i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-800"><?php echo $transaction['description']; ?></p>
                        <p class="text-sm text-gray-500"><?php echo $transaction['date']; ?></p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="font-bold <?php echo $transaction['montant'] > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                        <?php echo $transaction['montant'] > 0 ? '+' : ''; ?>
                        <?php echo number_format($transaction['montant'], 0, ',', ' '); ?> FCFA
                    </p>
                    <p class="text-xs <?php echo $transaction['statut'] == 'success' ? 'text-green-500' : 'text-yellow-500'; ?>">
                        <?php echo $transaction['statut'] == 'success' ? 'Terminé' : 'En attente'; ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Plans actifs -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-800">Plans d'investissement actifs</h3>
            <a href="?page=investissement" class="text-green-600 hover:text-green-700 font-medium text-sm">
                Voir tous les plans <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Plan Pro -->
            <div class="border border-green-200 rounded-xl p-4">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h4 class="font-bold text-gray-800 text-lg">Plan Pro</h4>
                        <p class="text-sm text-gray-500">Investi: 15,000 FCFA</p>
                    </div>
                    <span class="bg-green-100 text-green-800 text-xs font-medium px-3 py-1 rounded-full">
                        Actif
                    </span>
                </div>
                
                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600">Progression</span>
                        <span class="font-medium">15/30 jours</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: 50%"></div>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">ROI quotidien</p>
                        <p class="font-bold text-gray-800">750 FCFA</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Gain total</p>
                        <p class="font-bold text-green-600">11,250 FCFA</p>
                    </div>
                </div>
            </div>
            
            <!-- Plan Starter -->
            <div class="border border-blue-200 rounded-xl p-4">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h4 class="font-bold text-gray-800 text-lg">Plan Starter</h4>
                        <p class="text-sm text-gray-500">Investi: 5,000 FCFA</p>
                    </div>
                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-3 py-1 rounded-full">
                        Termine dans 5j
                    </span>
                </div>
                
                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600">Progression</span>
                        <span class="font-medium">25/30 jours</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: 83%"></div>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">ROI quotidien</p>
                        <p class="font-bold text-gray-800">200 FCFA</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Gain total</p>
                        <p class="font-bold text-green-600">5,000 FCFA</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de retrait -->
<div id="retraitModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md transform transition-all">
        <div class="p-6">
            <!-- En-tête -->
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-800">Effectuer un retrait</h3>
                <button onclick="closeRetraitModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Formulaire de retrait -->
            <form id="retraitForm">
                <!-- Source du retrait -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-medium mb-3">Source du retrait</label>
                    <div class="space-y-3">
                        <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:border-green-500">
                            <input type="radio" name="source" value="investissement" class="mr-3" checked>
                            <div class="flex-1">
                                <div class="flex justify-between">
                                    <span class="font-medium">Solde investissement</span>
                                    <span class="text-green-600 font-bold"><?php echo number_format($user_stats['solde_investissement'], 0, ',', ' '); ?> FCFA</span>
                                </div>
                                <p class="text-sm text-gray-500">Retrait des gains uniquement</p>
                            </div>
                        </label>
                        
                        <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:border-green-500">
                            <input type="radio" name="source" value="publicite" class="mr-3">
                            <div class="flex-1">
                                <div class="flex justify-between">
                                    <span class="font-medium">Solde publicité</span>
                                    <span class="text-blue-600 font-bold"><?php echo number_format($user_stats['solde_publicite'], 0, ',', ' '); ?> FCFA</span>
                                </div>
                                <p class="text-sm text-gray-500">Gains vidéos & publicités</p>
                            </div>
                        </label>
                        
                        <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:border-green-500">
                            <input type="radio" name="source" value="parrainage" class="mr-3">
                            <div class="flex-1">
                                <div class="flex justify-between">
                                    <span class="font-medium">Solde parrainage</span>
                                    <span class="text-purple-600 font-bold"><?php echo number_format($user_stats['solde_parrainage'], 0, ',', ' '); ?> FCFA</span>
                                </div>
                                <p class="text-sm text-gray-500">Bonus de parrainage</p>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Montant -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-medium mb-2">Montant à retirer (FCFA)</label>
                    <input type="number" 
                           id="montantRetrait" 
                           min="1000" 
                           max="500000"
                           placeholder="Ex: 10000"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-200 text-lg font-medium"
                           required>
                    <div class="flex justify-between mt-2">
                        <span class="text-sm text-gray-500">Minimum: 1,000 FCFA</span>
                        <button type="button" onclick="setMaxAmount()" class="text-sm text-green-600 hover:text-green-700 font-medium">
                            Retirer le maximum
                        </button>
                    </div>
                </div>
                
                <!-- Méthode de retrait -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-medium mb-3">Méthode de retrait</label>
                    <div class="grid grid-cols-3 gap-3">
                        <label class="retrait-method">
                            <input type="radio" name="methode" value="orange" class="hidden" checked>
                            <div class="border-2 border-gray-200 rounded-lg p-3 text-center hover:border-orange-500 cursor-pointer">
                                <i class="fas fa-mobile-alt text-orange-500 text-2xl mb-2"></i>
                                <p class="text-sm font-medium">Orange Money</p>
                            </div>
                        </label>
                        
                        <label class="retrait-method">
                            <input type="radio" name="methode" value="mtn" class="hidden">
                            <div class="border-2 border-gray-200 rounded-lg p-3 text-center hover:border-yellow-500 cursor-pointer">
                                <i class="fas fa-sim-card text-yellow-500 text-2xl mb-2"></i>
                                <p class="text-sm font-medium">MTN Mobile</p>
                            </div>
                        </label>
                        
                        <label class="retrait-method">
                            <input type="radio" name="methode" value="visa" class="hidden">
                            <div class="border-2 border-gray-200 rounded-lg p-3 text-center hover:border-blue-500 cursor-pointer">
                                <i class="fab fa-cc-visa text-blue-500 text-2xl mb-2"></i>
                                <p class="text-sm font-medium">Carte Visa</p>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Numéro de téléphone -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-medium mb-2">Numéro de téléphone</label>
                    <input type="tel" 
                           id="phoneNumber"
                           placeholder="Ex: +237 656 720 564"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500"
                           required>
                </div>
                
                <!-- Récapitulatif -->
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-6">
                    <h4 class="font-bold text-gray-800 mb-3">Récapitulatif</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Montant à retirer :</span>
                            <span id="recapMontant" class="font-medium">0 FCFA</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Frais de retrait :</span>
                            <span id="recapFrais" class="font-medium">0 FCFA</span>
                        </div>
                        <div class="flex justify-between border-t border-gray-300 pt-2 mt-2">
                            <span class="font-bold text-gray-800">Montant reçu :</span>
                            <span id="recapNet" class="font-bold text-green-600">0 FCFA</span>
                        </div>
                    </div>
                </div>
                
                <!-- Boutons d'action -->
                <div class="flex space-x-3">
                    <button type="button" onclick="closeRetraitModal()" 
                            class="flex-1 border-2 border-gray-300 text-gray-700 font-medium py-3 px-4 rounded-xl hover:bg-gray-50 transition">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="flex-1 bg-green-600 text-white font-bold py-3 px-4 rounded-xl hover:bg-green-700 transition">
                        <i class="fas fa-check-circle mr-2"></i>
                        Confirmer le retrait
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de confirmation -->
<div id="confirmationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md transform transition-all">
        <div class="p-8 text-center">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-check text-green-600 text-3xl"></i>
            </div>
            
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Retrait confirmé ! ✅</h3>
            <p id="confirmationMessage" class="text-gray-600 mb-6"></p>
            
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6">
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-700">Montant :</span>
                        <span id="confMontant" class="font-bold text-green-600"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-700">Méthode :</span>
                        <span id="confMethode" class="font-medium"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-700">Statut :</span>
                        <span class="font-medium text-yellow-600">En traitement</span>
                    </div>
                </div>
            </div>
            
            <p class="text-sm text-gray-500 mb-6">
                <i class="fas fa-clock mr-1"></i>
                Le retrait sera traité sous 24 heures. Vous recevrez une notification.
            </p>
            
            <button onclick="closeConfirmationModal()" 
                    class="w-full bg-green-600 text-white font-bold py-3 px-4 rounded-xl hover:bg-green-700 transition">
                <i class="fas fa-home mr-2"></i>
                Retour à l'accueil
            </button>
        </div>
    </div>
</div>

<script>
// Variables
let currentSource = 'investissement';
let currentMaxAmount = <?php echo $user_stats['solde_investissement']; ?>;

function openRetraitModal() {
    updateRecap();
    document.getElementById('retraitModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeRetraitModal() {
    document.getElementById('retraitModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('retraitForm').reset();
}

function setMaxAmount() {
    const source = document.querySelector('input[name="source"]:checked').value;
    let maxAmount = 0;
    
    switch(source) {
        case 'investissement':
            maxAmount = <?php echo $user_stats['solde_investissement']; ?>;
            break;
        case 'publicite':
            maxAmount = <?php echo $user_stats['solde_publicite']; ?>;
            break;
        case 'parrainage':
            maxAmount = <?php echo $user_stats['solde_parrainage']; ?>;
            break;
    }
    
    document.getElementById('montantRetrait').value = maxAmount;
    updateRecap();
}

function updateRecap() {
    const montant = parseFloat(document.getElementById('montantRetrait').value) || 0;
    const frais = Math.max(100, montant * 0.02); // 2% avec minimum 100 FCFA
    const net = montant - frais;
    
    document.getElementById('recapMontant').textContent = montant.toLocaleString() + ' FCFA';
    document.getElementById('recapFrais').textContent = frais.toLocaleString() + ' FCFA';
    document.getElementById('recapNet').textContent = net.toLocaleString() + ' FCFA';
}

function showAllTransactions() {
    window.location.href = '?page=transactions';
}

function openSupport() {
    // Simuler l'ouverture du support
    window.open('https://wa.me/237656720564?text=Bonjour%20Investian,%20j\'ai%20besoin%20d\'aide', '_blank');
}

// Gestion du formulaire de retrait
document.getElementById('retraitForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const montant = parseFloat(document.getElementById('montantRetrait').value);
    const source = document.querySelector('input[name="source"]:checked').value;
    const methode = document.querySelector('input[name="methode"]:checked').value;
    const phone = document.getElementById('phoneNumber').value;
    
    if (montant < 1000) {
        alert('Le montant minimum de retrait est de 1,000 FCFA');
        return;
    }
    
    // Vérifier si le solde est suffisant
    let soldeDisponible = 0;
    switch(source) {
        case 'investissement':
            soldeDisponible = <?php echo $user_stats['solde_investissement']; ?>;
            break;
        case 'publicite':
            soldeDisponible = <?php echo $user_stats['solde_publicite']; ?>;
            break;
        case 'parrainage':
            soldeDisponible = <?php echo $user_stats['solde_parrainage']; ?>;
            break;
    }
    
    if (montant > soldeDisponible) {
        alert(`Solde insuffisant. Disponible : ${soldeDisponible.toLocaleString()} FCFA`);
        return;
    }
    
    // Afficher la confirmation
    const frais = Math.max(100, montant * 0.02);
    const net = montant - frais;
    
    document.getElementById('confirmationMessage').textContent = 
        `Votre retrait de ${montant.toLocaleString()} FCFA a été pris en compte.`;
    document.getElementById('confMontant').textContent = net.toLocaleString() + ' FCFA';
    document.getElementById('confMethode').textContent = 
        methode === 'orange' ? 'Orange Money' : 
        methode === 'mtn' ? 'MTN Mobile' : 'Carte Visa';
    
    closeRetraitModal();
    document.getElementById('confirmationModal').classList.remove('hidden');
});

function closeConfirmationModal() {
    document.getElementById('confirmationModal').classList.add('hidden');
    // Actualiser la page pour mettre à jour les soldes
    setTimeout(() => {
        window.location.reload();
    }, 1000);
}

// Écouteurs d'événements
document.getElementById('montantRetrait').addEventListener('input', updateRecap);

document.querySelectorAll('input[name="source"]').forEach(input => {
    input.addEventListener('change', function() {
        const source = this.value;
        let maxAmount = 0;
        
        switch(source) {
            case 'investissement':
                maxAmount = <?php echo $user_stats['solde_investissement']; ?>;
                break;
            case 'publicite':
                maxAmount = <?php echo $user_stats['solde_publicite']; ?>;
                break;
            case 'parrainage':
                maxAmount = <?php echo $user_stats['solde_parrainage']; ?>;
                break;
        }
        
        // Mettre à jour le placeholder
        document.getElementById('montantRetrait').placeholder = `Max: ${maxAmount.toLocaleString()} FCFA`;
        document.getElementById('montantRetrait').max = maxAmount;
        updateRecap();
    });
});

// Gestion des méthodes de retrait
document.querySelectorAll('.retrait-method').forEach(method => {
    method.addEventListener('click', () => {
        document.querySelectorAll('.retrait-method').forEach(m => {
            m.querySelector('div').classList.remove('border-green-500', 'bg-green-50');
        });
        method.querySelector('div').classList.add('border-green-500', 'bg-green-50');
        method.querySelector('input').checked = true;
    });
});

// Fermer les modals en cliquant à l'extérieur
document.getElementById('retraitModal').addEventListener('click', (e) => {
    if (e.target.id === 'retraitModal') {
        closeRetraitModal();
    }
});

document.getElementById('confirmationModal').addEventListener('click', (e) => {
    if (e.target.id === 'confirmationModal') {
        closeConfirmationModal();
    }
});
</script>

<style>
.card-link:hover {
    transform: translateY(-2px);
    transition: transform 0.2s ease;
}

.action-card {
    @apply bg-white p-4 rounded-xl shadow-sm flex flex-col items-center justify-center hover:shadow-md transition cursor-pointer;
}

.action-icon {
    @apply w-12 h-12 rounded-full flex items-center justify-center mb-3;
}

.action-title {
    @apply font-medium text-gray-800 text-sm md:text-base text-center;
}

.action-subtitle {
    @apply text-xs text-gray-500 mt-1 text-center;
}

.retrait-method div {
    transition: all 0.2s ease;
}

/* Animation pour les modals */
@keyframes modalFadeIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

#retraitModal > div,
#confirmationModal > div {
    animation: modalFadeIn 0.3s ease-out;
}
</style>