<?php
// pages/parrainage.php

require_once 'config/db.php';

// Vérifier connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$db = Database::getInstance()->getConnection();

// RÉCUPÉRATION DES DONNÉES RÉELLES
function getReferralData($userId) {
    global $db;
    
    // Récupérer le code de parrainage de l'utilisateur
    $sqlUser = "SELECT referral_code, CONCAT(prenom, ' ', nom) as nom_complet 
               FROM users 
               WHERE id = :user_id";
    $stmtUser = $db->prepare($sqlUser);
    $stmtUser->execute([':user_id' => $userId]);
    $userData = $stmtUser->fetch();
    
    // Récupérer les filleuls
    $sqlFilleuls = "SELECT 
                       u.id,
                       CONCAT(u.prenom, ' ', u.nom) as nom_complet,
                       u.email,
                       u.phone,
                       u.statut,
                       u.created_at as date_inscription,
                       r.date_creation,
                       r.valide,
                       r.bonus,
                       r.date_validation,
                       (SELECT COUNT(*) FROM user_plans WHERE user_id = u.id AND statut = 'active') as plans_actifs
                    FROM referrals r
                    JOIN users u ON r.filleul_id = u.id
                    WHERE r.parrain_id = :user_id
                    ORDER BY r.date_creation DESC";
    
    $stmtFilleuls = $db->prepare($sqlFilleuls);
    $stmtFilleuls->execute([':user_id' => $userId]);
    $filleuls = $stmtFilleuls->fetchAll();
    
    // Statistiques de parrainage
    $sqlStats = "SELECT 
                    COUNT(*) as total_filleuls,
                    SUM(CASE WHEN valide = 1 THEN 1 ELSE 0 END) as filleuls_valides,
                    SUM(CASE WHEN valide = 1 THEN bonus ELSE 0 END) as bonus_total,
                    SUM(CASE WHEN DATE(date_creation) = CURDATE() THEN 1 ELSE 0 END) as filleuls_aujourdhui
                 FROM referrals 
                 WHERE parrain_id = :user_id";
    
    $stmtStats = $db->prepare($sqlStats);
    $stmtStats->execute([':user_id' => $userId]);
    $stats = $stmtStats->fetch();
    
    // Bonus disponible (solde parrainage)
    $sqlSolde = "SELECT solde_parrainage FROM wallets WHERE user_id = :user_id";
    $stmtSolde = $db->prepare($sqlSolde);
    $stmtSolde->execute([':user_id' => $userId]);
    $soldeResult = $stmtSolde->fetch();
    $solde_parrainage = $soldeResult['solde_parrainage'] ?? 0;
    
    return [
        'user' => $userData,
        'filleuls' => $filleuls,
        'stats' => [
            'total_filleuls' => $stats['total_filleuls'] ?? 0,
            'filleuls_valides' => $stats['filleuls_valides'] ?? 0,
            'bonus_total' => $stats['bonus_total'] ?? 0,
            'filleuls_aujourdhui' => $stats['filleuls_aujourdhui'] ?? 0,
            'solde_parrainage' => $solde_parrainage
        ]
    ];
}

// Récupérer les données
$data = getReferralData($userId);
$referral_code = $data['user']['referral_code'];
$nom_complet = $data['user']['nom_complet'] ?? 'Utilisateur';
?>

<div class="page-transition">
    <!-- En-tête avec statistiques -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Programme de parrainage</h1>
                <p class="text-gray-600">Invitez vos amis et gagnez ensemble</p>
            </div>
            <div class="mt-4 md:mt-0">
                <span class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-full font-medium">
                    <i class="fas fa-trophy mr-2"></i>
                    Gagnez jusqu'à 15% sur leurs investissements
                </span>
            </div>
        </div>
        
        <!-- Cartes de statistiques -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Solde parrainage -->
            <div class="bg-gradient-to-br from-green-500 to-emerald-500 rounded-2xl p-5 text-white shadow-lg">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-green-100 text-sm">Solde parrainage</p>
                        <p class="text-2xl font-bold mt-1">
                            <?php echo number_format($data['stats']['solde_parrainage'], 0, ',', ' '); ?> 
                            <span class="text-lg">FCFA</span>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
                        <i class="fas fa-wallet text-xl"></i>
                    </div>
                </div>
                <div class="flex items-center text-green-100 text-sm">
                    <i class="fas fa-download mr-2"></i>
                    <span>Disponible pour retrait</span>
                </div>
            </div>
            
            <!-- Filleuls total -->
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-gray-500 text-sm">Personnes parrainées</p>
                        <p class="text-2xl font-bold text-gray-800">
                            <?php echo $data['stats']['total_filleuls']; ?>
                            <span class="text-lg text-gray-500">/∞</span>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-users text-purple-600 text-xl"></i>
                    </div>
                </div>
                <div class="flex items-center text-xs text-gray-500">
                    <i class="fas fa-user-plus text-purple-500 mr-1"></i>
                    <span><?php echo $data['stats']['filleuls_aujourdhui']; ?> aujourd'hui</span>
                </div>
            </div>
            
            <!-- Bonus total -->
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-gray-500 text-sm">Bonus total</p>
                        <p class="text-2xl font-bold text-yellow-600">
                            <?php echo number_format($data['stats']['bonus_total'], 0, ',', ' '); ?> 
                            <span class="text-lg">FCFA</span>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-coins text-yellow-600 text-xl"></i>
                    </div>
                </div>
                <div class="flex items-center text-xs text-gray-500">
                    <i class="fas fa-chart-line text-yellow-500 mr-1"></i>
                    <span>Cumul des gains</span>
                </div>
            </div>
            
            <!-- Taux de validation -->
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-gray-500 text-sm">Taux de validation</p>
                        <p class="text-2xl font-bold text-blue-600">
                            <?php 
                            $taux = $data['stats']['total_filleuls'] > 0 
                                ? round(($data['stats']['filleuls_valides'] / $data['stats']['total_filleuls']) * 100, 1)
                                : 0;
                            echo $taux;
                            ?>%
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check-circle text-blue-600 text-xl"></i>
                    </div>
                </div>
                <div class="flex items-center text-xs text-gray-500">
                    <i class="fas fa-shield-alt text-blue-500 mr-1"></i>
                    <span>Filleuls actifs</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Section de partage -->
    <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-2xl p-6 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="lg:w-2/3">
                <h2 class="text-xl font-bold text-gray-800 mb-3">Partagez votre lien de parrainage</h2>
                <p class="text-gray-600 mb-4">
                    Invitez vos amis à rejoindre Investian avec votre lien personnel. 
                    Vous gagnerez <span class="font-bold text-green-600">15%</span> sur leurs premiers investissements !
                </p>
                
                <!-- Code de parrainage -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Votre code de parrainage</label>
                    <div class="flex">
                        <div class="flex-1 bg-white border border-green-300 rounded-l-lg px-4 py-3 font-mono text-lg font-bold text-green-600 truncate">
                            <?php echo $referral_code; ?>
                        </div>
                        <button onclick="copyReferralCode()" 
                                class="bg-green-600 hover:bg-green-700 text-white font-medium px-6 py-3 rounded-r-lg transition-colors duration-200">
                            <i class="fas fa-copy mr-2"></i>
                            Copier
                        </button>
                    </div>
                </div>
                
                <!-- Lien de parrainage -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Votre lien de parrainage</label>
                    <div class="flex">
                        <div class="flex-1 bg-white border border-gray-300 rounded-l-lg px-4 py-3 text-sm truncate text-gray-600">
                            <?php echo SITE_URL . BASE_URL . "/register?ref=" . $referral_code; ?>
                        </div>
                        <button onclick="copyReferralLink()" 
                                class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-3 rounded-r-lg transition-colors duration-200">
                            <i class="fas fa-link mr-2"></i>
                            Copier le lien
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- QR Code (simulé) -->
            <div class="lg:w-1/3 flex justify-center">
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                    <div class="w-48 h-48 bg-gray-100 rounded-lg flex items-center justify-center mb-3">
                        <div class="text-center">
                            <div class="w-40 h-40 border-4 border-green-500 border-dashed rounded-lg flex items-center justify-center mx-auto">
                                <div class="text-center">
                                    <i class="fas fa-qrcode text-green-500 text-5xl mb-2"></i>
                                    <p class="text-xs text-green-600 font-medium">QR Code</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="text-center text-sm text-gray-600">
                        Scannez pour partager
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Section avantages -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Comment ça marche -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-graduation-cap text-green-600 mr-2"></i>
                    Comment gagner avec le parrainage ?
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-6">
                        <!-- Étape 1 -->
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-500 rounded-full flex items-center justify-center text-white font-bold text-lg mr-4">
                                    1
                                </div>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 mb-2">Partagez votre lien</h3>
                                <p class="text-gray-600 text-sm">
                                    Envoyez votre lien personnel à vos amis, famille ou sur les réseaux sociaux.
                                </p>
                            </div>
                        </div>
                        
                        <!-- Étape 2 -->
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-full flex items-center justify-center text-white font-bold text-lg mr-4">
                                    2
                                </div>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 mb-2">Ils s'inscrivent</h3>
                                <p class="text-gray-600 text-sm">
                                    Vos filleuls créent un compte et effectuent leur premier dépôt minimum (4,000 FCFA).
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-6">
                        <!-- Étape 3 -->
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white font-bold text-lg mr-4">
                                    3
                                </div>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 mb-2">Ils investissent</h3>
                                <p class="text-gray-600 text-sm">
                                    Vos filleuls investissent dans n'importe quel plan d'investissement.
                                </p>
                            </div>
                        </div>
                        
                        <!-- Étape 4 -->
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-gradient-to-br from-yellow-500 to-orange-500 rounded-full flex items-center justify-center text-white font-bold text-lg mr-4">
                                    4
                                </div>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 mb-2">Vous gagnez 15%</h3>
                                <p class="text-gray-600 text-sm">
                                    Recevez instantanément 15% du montant investi par chacun de vos filleuls.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Barre de progression -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Prochain niveau</span>
                        <span class="text-sm font-bold text-green-600">+5 filleuls actifs</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-gradient-to-r from-green-500 to-emerald-500 h-3 rounded-full" 
                             style="width: <?php echo min(100, ($data['stats']['total_filleuls'] / 5) * 100); ?>%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        <i class="fas fa-gift text-green-500 mr-1"></i>
                        À 10 filleuls : Bonus spécial de 10,000 FCFA !
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Avantages -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-star text-yellow-500 mr-2"></i>
                Vos avantages
            </h2>
            
            <div class="space-y-4">
                <div class="flex items-start p-3 bg-green-50 rounded-lg border border-green-100">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-percentage text-green-600"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800">Commission de 15%</h4>
                        <p class="text-sm text-gray-600">Sur chaque investissement de vos filleuls</p>
                    </div>
                </div>
                
                <div class="flex items-start p-3 bg-blue-50 rounded-lg border border-blue-100">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-infinity text-blue-600"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800">Sans limite</h4>
                        <p class="text-sm text-gray-600">Parrainez autant de personnes que vous voulez</p>
                    </div>
                </div>
                
                <div class="flex items-start p-3 bg-purple-50 rounded-lg border border-purple-100">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-bolt text-purple-600"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800">Paiement instantané</h4>
                        <p class="text-sm text-gray-600">Bonus crédités immédiatement sur votre solde</p>
                    </div>
                </div>
                
                <div class="flex items-start p-3 bg-yellow-50 rounded-lg border border-yellow-100">
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-trophy text-yellow-600"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800">Récompenses mensuelles</h4>
                        <p class="text-sm text-gray-600">Top parrains reçoivent des bonus supplémentaires</p>
                    </div>
                </div>
            </div>
            
            <!-- Bouton retrait -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <button onclick="openRetraitParrainage()" 
                        class="w-full bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white font-bold py-3 px-4 rounded-lg transition-all duration-300 shadow hover:shadow-lg">
                    <i class="fas fa-download mr-2"></i>
                    Retirer <?php echo number_format($data['stats']['solde_parrainage'], 0, ',', ' '); ?> FCFA
                </button>
            </div>
        </div>
    </div>

    <!-- Section filleuls -->
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 mb-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-users text-purple-600 mr-2"></i>
                Vos filleuls (<?php echo count($data['filleuls']); ?>)
            </h2>
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <input type="text" 
                           placeholder="Rechercher un filleul..." 
                           class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 text-sm w-10">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
                <select class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-green-500">
                    <option>Tous les statuts</option>
                    <option>Validés</option>
                    <option>En attente</option>
                </select>
            </div>
        </div>
        
        <?php if (empty($data['filleuls'])): ?>
        <div class="text-center py-12">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-users text-gray-400 text-3xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-700 mb-2">Aucun filleul pour le moment</h3>
            <p class="text-gray-500 mb-6 max-w-md mx-auto">
                Partagez votre lien de parrainage pour inviter vos premiers filleuls et commencer à gagner des bonus !
            </p>
            <button onclick="shareReferral()" 
                    class="inline-flex items-center bg-gradient-to-r from-green-500 to-emerald-500 text-white font-medium px-6 py-3 rounded-lg hover:from-green-600 hover:to-emerald-600 transition">
                <i class="fas fa-share-alt mr-2"></i>
                Partager maintenant
            </button>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-gray-500 text-sm border-b">
                        <th class="pb-3 font-medium px-2">Filleul</th>
                        <th class="pb-3 font-medium px-2">Date d'inscription</th>
                        <th class="pb-3 font-medium px-2">Investissements</th>
                        <th class="pb-3 font-medium px-2">Bonus généré</th>
                        <th class="pb-3 font-medium px-2">Statut</th>
                        <th class="pb-3 font-medium px-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($data['filleuls'] as $filleul): ?>
                    <?php
                    $dateInscription = new DateTime($filleul['date_inscription']);
                    $now = new DateTime();
                    $interval = $dateInscription->diff($now);
                    
                    if ($interval->d == 0) {
                        $dateAffichage = "Aujourd'hui";
                    } elseif ($interval->d == 1) {
                        $dateAffichage = "Hier";
                    } else {
                        $dateAffichage = $interval->d . " jours";
                    }
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-4 px-2">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gradient-to-br from-green-100 to-emerald-100 rounded-full flex items-center justify-center mr-3">
                                    <span class="font-bold text-green-600">
                                        <?php echo strtoupper(substr($filleul['nom_complet'], 0, 2)); ?>
                                    </span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($filleul['nom_complet']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo $filleul['email']; ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-2">
                            <p class="text-gray-700"><?php echo $dateAffichage; ?></p>
                            <p class="text-xs text-gray-500"><?php echo date('d/m/Y', strtotime($filleul['date_creation'])); ?></p>
                        </td>
                        <td class="py-4 px-2">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-2">
                                    <i class="fas fa-chart-line text-blue-600 text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800"><?php echo $filleul['plans_actifs']; ?> plan(s)</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-2">
                            <p class="font-bold text-green-600">
                                <?php echo number_format($filleul['bonus'], 0, ',', ' '); ?> FCFA
                            </p>
                        </td>
                        <td class="py-4 px-2">
                            <?php if ($filleul['valide']): ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>
                                Validé
                            </span>
                            <?php else: ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-1"></i>
                                En attente
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 px-2">
                            <div class="flex space-x-2">
                                <button onclick="viewFilleul(<?php echo $filleul['id']; ?>)" 
                                        class="text-blue-600 hover:text-blue-800 p-1">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="sendMessage(<?php echo $filleul['id']; ?>)" 
                                        class="text-green-600 hover:text-green-800 p-1">
                                    <i class="fas fa-envelope"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="flex justify-between items-center mt-6 pt-6 border-t border-gray-200">
            <p class="text-sm text-gray-500">
                Affichage de <?php echo count($data['filleuls']); ?> filleuls sur <?php echo $data['stats']['total_filleuls']; ?>
            </p>
            <div class="flex space-x-2">
                <button class="w-10 h-10 border border-gray-300 rounded-lg flex items-center justify-center hover:bg-gray-50">
                    <i class="fas fa-chevron-left text-gray-600"></i>
                </button>
                <button class="w-10 h-10 bg-green-600 text-white rounded-lg font-medium">1</button>
                <button class="w-10 h-10 border border-gray-300 rounded-lg flex items-center justify-center hover:bg-gray-50">
                    2
                </button>
                <button class="w-10 h-10 border border-gray-300 rounded-lg flex items-center justify-center hover:bg-gray-50">
                    <i class="fas fa-chevron-right text-gray-600"></i>
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Section partage social -->
    <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl p-6 text-white mb-8">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold mb-2">Partagez sur les réseaux sociaux</h2>
            <p class="text-green-100 opacity-90">Augmentez vos chances d'avoir plus de filleuls</p>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <button onclick="shareOnFacebook()" 
                    class="bg-white hover:bg-gray-100 text-gray-800 font-medium py-3 px-4 rounded-lg transition flex items-center justify-center">
                <i class="fab fa-facebook-f text-blue-600 text-xl mr-2"></i>
                Facebook
            </button>
            
            <button onclick="shareOnWhatsApp()" 
                    class="bg-white hover:bg-gray-100 text-gray-800 font-medium py-3 px-4 rounded-lg transition flex items-center justify-center">
                <i class="fab fa-whatsapp text-green-500 text-xl mr-2"></i>
                WhatsApp
            </button>
            
            <button onclick="shareOnTelegram()" 
                    class="bg-white hover:bg-gray-100 text-gray-800 font-medium py-3 px-4 rounded-lg transition flex items-center justify-center">
                <i class="fab fa-telegram text-blue-400 text-xl mr-2"></i>
                Telegram
            </button>
            
            <button onclick="shareOnTwitter()" 
                    class="bg-white hover:bg-gray-100 text-gray-800 font-medium py-3 px-4 rounded-lg transition flex items-center justify-center">
                <i class="fab fa-twitter text-blue-400 text-xl mr-2"></i>
                Twitter
            </button>
        </div>
        
        <div class="text-center mt-6">
            <p class="text-green-100 text-sm">
                <i class="fas fa-lightbulb mr-1"></i>
                Conseil : Partagez votre expérience personnelle pour plus d'impact !
            </p>
        </div>
    </div>
</div>

<!-- Modal retrait parrainage -->
<div id="retraitParrainageModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md transform transition-all duration-300 modal-content">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Retrait du solde parrainage</h3>
                    <p class="text-sm text-gray-500">Transfert vers votre compte</p>
                </div>
                <button onclick="closeRetraitParrainage()" class="modal-close-btn">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="space-y-6">
                <!-- Montant disponible -->
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-600">Solde disponible</p>
                            <p class="text-2xl font-bold text-green-600">
                                <?php echo number_format($data['stats']['solde_parrainage'], 0, ',', ' '); ?> FCFA
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-coins text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Montant à retirer -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Montant à retirer (FCFA)</label>
                    <input type="number" 
                           id="retraitMontant"
                           min="1000"
                           max="<?php echo $data['stats']['solde_parrainage']; ?>"
                           value="<?php echo $data['stats']['solde_parrainage']; ?>"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-green-500 text-lg font-medium">
                    <div class="flex justify-between mt-2">
                        <span class="text-sm text-gray-500">Minimum: 1,000 FCFA</span>
                        <button type="button" onclick="setMaxRetrait()" class="text-sm text-green-600 hover:text-green-700 font-medium">
                            Retirer le maximum
                        </button>
                    </div>
                </div>
                
                <!-- Méthode de retrait -->
                <div>
                    <label class="block text-gray-700 font-medium mb-3">Méthode de retrait</label>
                    <div class="grid grid-cols-3 gap-3">
                        <button onclick="selectRetraitMethod('orange')" class="retrait-method-btn">
                            <i class="fas fa-mobile-alt text-orange-500 text-2xl mb-2"></i>
                            <p class="text-sm font-medium">Orange</p>
                        </button>
                        <button onclick="selectRetraitMethod('mtn')" class="retrait-method-btn">
                            <i class="fas fa-sim-card text-yellow-500 text-2xl mb-2"></i>
                            <p class="text-sm font-medium">MTN</p>
                        </button>
                        <button onclick="selectRetraitMethod('visa')" class="retrait-method-btn">
                            <i class="fab fa-cc-visa text-blue-500 text-2xl mb-2"></i>
                            <p class="text-sm font-medium">Visa</p>
                        </button>
                    </div>
                </div>
                
                <!-- Bouton de confirmation -->
                <button onclick="processRetraitParrainage()" 
                        class="w-full bg-gradient-to-r from-green-500 to-emerald-500 text-white font-bold py-4 px-4 rounded-xl hover:from-green-600 hover:to-emerald-600 transition-all duration-300 shadow-lg hover:shadow-xl">
                    <i class="fas fa-check-circle mr-2"></i>
                    Confirmer le retrait
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Variables globales
const referralCode = '<?php echo $referral_code; ?>';
const referralLink = '<?php echo SITE_URL . BASE_URL . "/register?ref=" . $referral_code; ?>';
let selectedRetraitMethod = null;

// Fonctions de partage
function copyReferralCode() {
    navigator.clipboard.writeText(referralCode).then(() => {
        showToast('✅ Code de parrainage copié !', 'success');
    });
}

function copyReferralLink() {
    navigator.clipboard.writeText(referralLink).then(() => {
        showToast('✅ Lien de parrainage copié !', 'success');
    });
}

function shareReferral() {
    const shareText = `🎉 Rejoins-moi sur Investian et commence à gagner de l'argent simplement. !\n\nUtilise mon code de parrainage : ${referralCode}\n\nLien d'inscription : ${referralLink}`;
    
    if (navigator.share) {
        navigator.share({
            title: 'Rejoins Investian avec moi !',
            text: shareText,
            url: referralLink
        });
    } else {
        navigator.clipboard.writeText(shareText).then(() => {
            showToast('✅ Message de parrainage copié ! Partagez-le où vous voulez.', 'success');
        });
    }
}

// Fonctions de partage social
function shareOnFacebook() {
    const url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(referralLink)}`;
    window.open(url, '_blank');
}

function shareOnWhatsApp() {
    const text = encodeURIComponent(`Rejoins-moi sur Investian ! Code : ${referralCode}\nLien : ${referralLink}`);
    const url = `https://wa.me/?text=${text}`;
    window.open(url, '_blank');
}

function shareOnTelegram() {
    const text = encodeURIComponent(`Rejoins-moi sur Investian ! Code : ${referralCode}\nLien : ${referralLink}`);
    const url = `https://t.me/share/url?url=${encodeURIComponent(referralLink)}&text=${text}`;
    window.open(url, '_blank');
}

function shareOnTwitter() {
    const text = encodeURIComponent(`Je gagne de l'argent avec Investian ! Rejoins-moi avec le code ${referralCode} 🚀`);
    const url = `https://twitter.com/intent/tweet?text=${text}&url=${encodeURIComponent(referralLink)}`;
    window.open(url, '_blank');
}

// Gestion des retraits
function openRetraitParrainage() {
    const solde = <?php echo $data['stats']['solde_parrainage']; ?>;
    
    if (solde < 1000) {
        showToast('❌ Le solde minimum pour retirer est de 1,000 FCFA', 'error');
        return;
    }
    
    selectedRetraitMethod = null;
    document.getElementById('retraitMontant').value = solde;
    
    // Réinitialiser les boutons
    document.querySelectorAll('.retrait-method-btn').forEach(btn => {
        btn.classList.remove('selected');
    });
    
    document.getElementById('retraitParrainageModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeRetraitParrainage() {
    document.getElementById('retraitParrainageModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function selectRetraitMethod(method) {
    selectedRetraitMethod = method;
    
    // Mettre à jour l'UI
    document.querySelectorAll('.retrait-method-btn').forEach(btn => {
        btn.classList.remove('selected');
    });
    
    event.target.closest('.retrait-method-btn').classList.add('selected');
}

function setMaxRetrait() {
    const solde = <?php echo $data['stats']['solde_parrainage']; ?>;
    document.getElementById('retraitMontant').value = solde;
}

function processRetraitParrainage() {
    const montant = parseFloat(document.getElementById('retraitMontant').value);
    const solde = <?php echo $data['stats']['solde_parrainage']; ?>;
    
    if (!selectedRetraitMethod) {
        showToast('❌ Veuillez sélectionner une méthode de retrait', 'error');
        return;
    }
    
    if (montant < 1000) {
        showToast('❌ Le montant minimum de retrait est de 1,000 FCFA', 'error');
        return;
    }
    
    if (montant > solde) {
        showToast(`❌ Solde insuffisant. Disponible : ${solde.toLocaleString()} FCFA`, 'error');
        return;
    }
    
    // Simuler le traitement du retrait
    showToast('✅ Demande de retrait envoyée ! Traitement sous 24h.', 'success');
    closeRetraitParrainage();
    
    // Actualiser après 2 secondes
    setTimeout(() => {
        window.location.reload();
    }, 2000);
}

// Fonctions pour les filleuls
function viewFilleul(id) {
    alert(`🔍 Affichage des détails du filleul #${id} - Cette fonctionnalité sera bientôt disponible !`);
}

function sendMessage(id) {
    alert(`📧 Envoi de message au filleul #${id} - Cette fonctionnalité sera bientôt disponible !`);
}

// Fonction utilitaire pour les notifications
function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-medium animate-slideIn ${
        type === 'success' ? 'bg-green-600' : 'bg-red-600'
    }`;
    toast.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle mr-2"></i>
            ${message}
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('animate-slideOut');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}

// Fermer le modal en cliquant à l'extérieur
document.getElementById('retraitParrainageModal').addEventListener('click', (e) => {
    if (e.target.id === 'retraitParrainageModal') {
        closeRetraitParrainage();
    }
});
</script>

<style>
/* Animation pour les toasts */
@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

.animate-slideIn {
    animation: slideIn 0.3s ease-out;
}

.animate-slideOut {
    animation: slideOut 0.3s ease-in;
}

/* Styles pour les boutons de méthode */
.retrait-method-btn {
    @apply border-2 border-gray-200 rounded-lg p-3 text-center hover:border-green-500 cursor-pointer transition-colors duration-200 flex flex-col items-center justify-center;
}

.retrait-method-btn.selected {
    @apply border-green-500 bg-green-50;
}

/* Animation pour les modals */
@keyframes modalFadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-content {
    animation: modalFadeIn 0.3s ease-out;
}

.modal-close-btn {
    @apply text-gray-400 hover:text-gray-600 transition-colors duration-200;
}

/* Effet de survol pour les lignes du tableau */
tbody tr {
    transition: background-color 0.2s ease;
}

/* Responsive */
@media (max-width: 768px) {
    .grid-cols-4 {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .flex-col-mobile {
        flex-direction: column;
    }
    
    .text-lg-mobile {
        font-size: 1.125rem;
    }
}

/* Effet de brillance sur les cartes de statistiques */
.bg-gradient-to-br {
    position: relative;
    overflow: hidden;
}

.bg-gradient-to-br::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.bg-gradient-to-br:hover::before {
    left: 100%;
}
</style>