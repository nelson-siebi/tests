<?php
// ====================================================
// FICHIER: dashboard_plans.php
// AUTEUR: FreeCash
// DESCRIPTION: Affiche les plans disponibles selon les règles
// ====================================================

// 1. Connexion à la base de données
require_once 'config/db.php';

// 3. Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!-- Integration LeekPay -->
<script src="https://leekpay.fr/js/leekpay.js"></script>
<?php

$userId = $_SESSION['user_id'];

// 4. FONCTION : Récupérer les plans disponibles selon les règles
function getAvailablePlansWithRules($userId)
{
    $db = Database::getInstance()->getConnection();

    $sql = "
        SELECT p.* 
        FROM plans p
        WHERE p.actif = 1
        AND NOT (
            p.prix = 4000 
            AND EXISTS (
                SELECT 1 
                FROM (
                    -- Investissements de l'utilisateur (2+ jours)
                    SELECT user_id, date_debut
                    FROM user_plans 
                    WHERE user_id = :user_id 
                    AND statut = 'active'
                    AND DATE_ADD(date_debut, INTERVAL 2 DAY) <= NOW()
                    
                    UNION
                    
                    -- Investissements des filleuls (2+ jours)
                    SELECT up.user_id, up.date_debut
                    FROM user_plans up
                    INNER JOIN referrals r ON r.filleul_id = up.user_id
                    WHERE r.parrain_id = :parrain_id
                    AND up.statut = 'active'
                    AND DATE_ADD(up.date_debut, INTERVAL 2 DAY) <= NOW()
                ) as anciens_investissements
            )
        )
        ORDER BY p.prix ASC
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':user_id' => $userId,
        ':parrain_id' => $userId
    ]);

    return $stmt->fetchAll();
}
$db = Database::getInstance()->getConnection();

// Préparation de la requête
$query = "SELECT * FROM users WHERE  id=?";
$stmt = $db->prepare($query);

// Exécution
$stmt->execute([$userId]);

// Récupérer tous les utilisateurs
$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5. FONCTION : Calculer les données supplémentaires pour chaque plan
function enrichirPlanData($plan)
{
    // Calcul du rendement total (ROI journalier × durée)
    $rendement_total = $plan['roi_journalier'] * $plan['duree_jours'];

    // Calcul du profit net (rendement total - investissement initial)
    $profit_net = $rendement_total - $plan['prix'];

    // Calcul du taux de rendement en pourcentage
    $taux_rendement = ($profit_net / $plan['prix']) * 100;

    // Calcul des gains vidéos totaux
    $gain_videos_total = $plan['videos_par_jour'] * $plan['gain_par_video'] * $plan['duree_jours'];

    // Déterminer la catégorie selon le prix
    $prix = $plan['prix'];

    // Configuration des couleurs selon le prix
    $couleurs = [
        4000 => [
            'couleur' => 'from-orange-100 to-orange-50 border-orange-200',
            'couleur_btn' => 'bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700',
            'badge_couleur' => 'bg-gradient-to-r from-orange-500 to-orange-600',
            'icon_color' => 'text-orange-600',
            'icon_bg' => 'bg-orange-100',
            'nom_affichage' => 'Bronze Starter'
        ],
        6000 => [
            'couleur' => 'from-blue-100 to-blue-50 border-blue-200',
            'couleur_btn' => 'bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700',
            'badge_couleur' => 'bg-gradient-to-r from-blue-500 to-blue-600',
            'icon_color' => 'text-blue-600',
            'icon_bg' => 'bg-blue-100',
            'nom_affichage' => 'Silver Basic'
        ],
        10000 => [
            'couleur' => 'from-green-100 to-green-50 border-green-200',
            'couleur_btn' => 'bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700',
            'badge_couleur' => 'bg-gradient-to-r from-green-500 to-green-600',
            'icon_color' => 'text-green-600',
            'icon_bg' => 'bg-green-100',
            'nom_affichage' => 'Gold Standard'
        ],
        20000 => [
            'couleur' => 'from-purple-100 to-purple-50 border-purple-200',
            'couleur_btn' => 'bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700',
            'badge_couleur' => 'bg-gradient-to-r from-purple-500 to-purple-600',
            'icon_color' => 'text-purple-600',
            'icon_bg' => 'bg-purple-100',
            'nom_affichage' => 'Platinum Plus'
        ],
        30000 => [
            'couleur' => 'from-pink-100 to-pink-50 border-pink-200',
            'couleur_btn' => 'bg-gradient-to-r from-pink-500 to-pink-600 hover:from-pink-600 hover:to-pink-700',
            'badge_couleur' => 'bg-gradient-to-r from-pink-500 to-pink-600',
            'icon_color' => 'text-pink-600',
            'icon_bg' => 'bg-pink-100',
            'nom_affichage' => 'Diamond Elite'
        ],
        50000 => [
            'couleur' => 'from-yellow-100 to-yellow-50 border-yellow-200',
            'couleur_btn' => 'bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700',
            'badge_couleur' => 'bg-gradient-to-r from-yellow-500 to-yellow-600',
            'icon_color' => 'text-yellow-600',
            'icon_bg' => 'bg-yellow-100',
            'nom_affichage' => 'Gold VIP'
        ],
        100000 => [
            'couleur' => 'from-red-100 to-red-50 border-red-200',
            'couleur_btn' => 'bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700',
            'badge_couleur' => 'bg-gradient-to-r from-red-500 to-red-600',
            'icon_color' => 'text-red-600',
            'icon_bg' => 'bg-red-100',
            'nom_affichage' => 'Executive VIP'
        ]
    ];

    // Trouver la configuration de couleur la plus proche
    $config = $couleurs[4000];
    foreach ($couleurs as $seuil => $conf) {
        if ($prix >= $seuil) {
            $config = $conf;
        }
    }

    // Déterminer si c'est populaire (basé sur le prix moyen)
    $popular = ($prix >= 20000 && $prix <= 50000);

    // Image par défaut si non définie
    $image = !empty($plan['image']) ? $plan['image'] : 'https://images.unsplash.com/photo-1556742044-3c52d6e88c62?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80';

    // Retourner le plan enrichi
    return array_merge($plan, [
        'rendement_total' => $rendement_total,
        'profit_net' => $profit_net,
        'taux_rendement' => number_format($taux_rendement, 1) . '%',
        'gain_videos_total' => $gain_videos_total,
        'couleur' => $config['couleur'],
        'couleur_btn' => $config['couleur_btn'],
        'badge_couleur' => $config['badge_couleur'],
        'icon_color' => $config['icon_color'],
        'icon_bg' => $config['icon_bg'],
        'nom_affichage' => $config['nom_affichage'],
        'popular' => $popular,
        'image' => $image
    ]);
}

// 6. RÉCUPÉRER ET TRAITER LES PLANS
$plansBruts = getAvailablePlansWithRules($userId);
$plansEnrichis = [];

foreach ($plansBruts as $plan) {
    $plansEnrichis[] = enrichirPlanData($plan);
}

?>

<div class="page-transition">
    <!-- En-tête amélioré -->
    <div class="relative overflow-hidden rounded-2xl mb-8">
        <div class="absolute inset-0 bg-gradient-to-r from-primary-600 via-primary-700 to-primary-800"></div>

        <div class="absolute inset-0 opacity-10">
            <div class="absolute -top-20 -right-20 w-64 h-64 bg-white rounded-full"></div>
            <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-white rounded-full"></div>
        </div>

        <div class="relative p-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between">
                <div class="mb-6 md:mb-0">
                    <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">
                        <i class="fas fa-rocket mr-3"></i>
                        Plateforme d'Investissement
                    </h1>
                    <p class="text-primary-100 text-lg opacity-90 max-w-2xl">
                        Choisissez votre plan d'investissement et commencez à générer des revenus passifs
                    </p>
                </div>

                <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 shadow-lg">
                    <div class="flex items-center space-x-4">
                        <div class="w-14 h-14 bg-white/30 rounded-full flex items-center justify-center">
                            <i class="fas fa-wallet text-white text-2xl"></i>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Plans d'investissement avec images -->
    <div class="mb-12">
        <div class="text-center mb-10">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">
                <span class="bg-gradient-to-r from-primary-600 to-primary-500 bg-clip-text text-transparent">
                    Nos Plans d'Investissement
                </span>
            </h2>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                Choisissez le plan qui correspond à vos objectifs financiers.
                Tous nos plans offrent des retours sur investissement garantis.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php if (empty($plansEnrichis)): ?>
                <div class="col-span-full text-center py-12">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-chart-line text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-700 mb-3">Aucun plan disponible</h3>
                    <p class="text-gray-600">Tous les plans sont temporairement indisponibles.</p>
                </div>
            <?php else: ?>
                <?php foreach ($plansEnrichis as $plan): ?>
                    <div
                        class="group relative bg-gradient-to-b <?php echo $plan['couleur']; ?> rounded-2xl border shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 overflow-hidden animate-fade-up">
                        <!-- Badge "Populaire" -->
                        <?php if ($plan['popular']): ?>
                            <div class="absolute -top-2 right-6 z-10">
                                <div class="<?php echo $plan['badge_couleur']; ?> text-white px-6 py-2 rounded-b-lg shadow-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-crown mr-2"></i>
                                        <span class="font-bold text-sm">POPULAIRE</span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Image du plan -->
                        <div
                            class="relative h-48 bg-gradient-to-br <?php echo str_replace('from-', 'from-', $plan['badge_couleur']); ?>">
                            <!-- Nom du plan sur l'image -->
                            <div class="border">
                                <h3 class="text-2xl font-bold text-white drop-shadow-lg">
                                    <?php echo $plan['nom']; ?>
                                </h3>
                                <div class="flex items-baseline mt-1 justify-center">
                                    <span class="text-3xl font-bold text-green-300">
                                        <i
                                            class="fas fa-coins mr-2"></i><?php echo number_format($plan['prix'], 0, ',', ' '); ?>
                                    </span>
                                    <span class="text-green-100 ml-2">FCFA</span>
                                </div>
                            </div>
                        </div>

                        <!-- Contenu du plan -->
                        <div class="p-6">
                            <!-- Icône du plan -->
                            <div class="flex justify-center -mt-12 mb-4">
                                <div
                                    class="w-20 h-20 <?php echo $plan['icon_bg']; ?> rounded-full flex items-center justify-center border-4 border-white shadow-lg">
                                    <i class="fas fa-gem <?php echo $plan['icon_color']; ?> text-3xl"></i>
                                </div>
                            </div>

                            <!-- Détails du plan -->
                            <div class="space-y-4 mb-6">
                                <div class="flex items-center justify-between py-2 border-b border-gray-300/30">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar-day <?php echo $plan['icon_color']; ?> mr-3"></i>
                                        <span class="text-gray-700">Durée</span>
                                    </div>
                                    <span class="font-bold text-gray-800"><?php echo $plan['duree_jours']; ?> jours</span>
                                </div>

                                <div class="flex items-center justify-between py-2 border-b border-gray-300/30">
                                    <div class="flex items-center">
                                        <i class="fas fa-coins <?php echo $plan['icon_color']; ?> mr-3"></i>
                                        <span class="text-gray-700">ROI quotidien</span>
                                    </div>
                                    <span
                                        class="font-bold text-gray-800"><?php echo number_format($plan['roi_journalier'], 0, ',', ' '); ?>
                                        FCFA</span>
                                </div>

                                <div class="flex items-center justify-between py-2 border-b border-gray-300/30">
                                    <div class="flex items-center">
                                        <i class="fas fa-video <?php echo $plan['icon_color']; ?> mr-3"></i>
                                        <span class="text-gray-700">Vidéos/jour</span>
                                    </div>
                                    <span class="font-bold text-gray-800"><?php echo $plan['videos_par_jour']; ?></span>
                                </div>

                                <div class="flex items-center justify-between py-2">
                                    <div class="flex items-center">
                                        <i class="fas fa-chart-line <?php echo $plan['icon_color']; ?> mr-3"></i>
                                        <span class="text-gray-700">Rendement total</span>
                                    </div>
                                    <span
                                        class="font-bold text-green-600"><?php echo number_format($plan['rendement_total'], 0, ',', ' '); ?>
                                        FCFA</span>
                                </div>
                            </div>

                            <!-- Carte de profit -->
                            <div
                                class="bg-white/80 backdrop-blur-sm rounded-xl p-4 mb-6 border border-gray-200/50 shadow-inner">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-sm text-gray-600">Profit net</p>
                                        <p class="text-2xl font-bold text-green-600">
                                            +<?php echo number_format($plan['profit_net'], 0, ',', ' '); ?> FCFA</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-600">Rendement</p>
                                        <p class="text-xl font-bold <?php echo $plan['icon_color']; ?>">
                                            <?php echo $plan['taux_rendement']; ?></p>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="h-2 rounded-full <?php echo str_replace('from-', 'bg-gradient-to-r from-', $plan['badge_couleur']); ?>"
                                            style="width: <?php echo min(100, ($plan['profit_net'] / $plan['prix']) * 100 + 20); ?>%">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bouton d'investissement LeekPay -->
                            <button data-leekpay-amount="<?php echo $plan['prix']; ?>" data-leekpay-currency="XOF"
                                data-leekpay-key="pk_live_mfeiJlkBsJ5Bs1yaA44LKwJtTGfkMfj0"
                                data-leekpay-description="Investissement <?php echo htmlspecialchars($plan['nom']); ?>"
                                data-leekpay-email="<?php echo htmlspecialchars($utilisateurs[0]['email'] ?? ''); ?>"
                                data-leekpay-ref="INV-<?php echo time(); ?>-<?php echo $plan['id']; ?>"
                                class="<?php echo $plan['couleur_btn']; ?> w-full text-white font-bold py-4 px-6 rounded-xl transition-all duration-300 transform hover:scale-[1.02] hover:shadow-lg active:scale-95 group/btn animate-pulse-once">
                                <div class="flex items-center justify-center">
                                    <i class="fas fa-bolt mr-3 group-hover/btn:animate-pulse"></i>
                                    <span class="text-lg">Investir maintenant</span>
                                    <i
                                        class="fas fa-arrow-right ml-3 opacity-0 group-hover/btn:opacity-100 group-hover/btn:translate-x-2 transition-all"></i>
                                </div>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Section avantages -->
    <div class="bg-gradient-to-r from-primary-50 to-primary-50 rounded-2xl p-8 mb-8 border border-primary-100">
        <div class="text-center mb-10">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">
                <i class="fas fa-star text-yellow-500 mr-3"></i>
                Pourquoi Investir avec FreeCash ?
            </h2>
            <p class="text-gray-600 text-lg">Découvrez les avantages exclusifs de notre plateforme</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="text-center">
                <div
                    class="w-20 h-20 bg-gradient-to-br from-green-500 to-emerald-500 rounded-2xl flex items-center justify-center mx-auto mb-6 transform hover:-translate-y-2 transition-transform duration-300">
                    <i class="fas fa-shield-alt text-white text-3xl"></i>
                </div>
                <h3 class="font-bold text-gray-800 text-xl mb-3">Sécurité Maximale</h3>
                <p class="text-gray-600">Vos fonds sont protégés par des technologies bancaires de pointe.</p>
            </div>

            <div class="text-center">
                <div
                    class="w-20 h-20 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-2xl flex items-center justify-center mx-auto mb-6 transform hover:-translate-y-2 transition-transform duration-300">
                    <i class="fas fa-bolt text-white text-3xl"></i>
                </div>
                <h3 class="font-bold text-gray-800 text-xl mb-3">Retraits Rapides</h3>
                <p class="text-gray-600">Recevez vos gains en moins de 24 heures, 7 jours sur 7.</p>
            </div>

            <div class="text-center">
                <div
                    class="w-20 h-20 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center mx-auto mb-6 transform hover:-translate-y-2 transition-transform duration-300">
                    <i class="fas fa-headset text-white text-3xl"></i>
                </div>
                <h3 class="font-bold text-gray-800 text-xl mb-3">Support 24/7</h3>
                <p class="text-gray-600">Notre équipe est disponible pour vous accompagner à tout moment.</p>
            </div>

            <div class="text-center">
                <div
                    class="w-20 h-20 bg-gradient-to-br from-yellow-500 to-orange-500 rounded-2xl flex items-center justify-center mx-auto mb-6 transform hover:-translate-y-2 transition-transform duration-300">
                    <i class="fas fa-chart-line text-white text-3xl"></i>
                </div>
                <h3 class="font-bold text-gray-800 text-xl mb-3">Rendements Garantis</h3>
                <p class="text-gray-600">Des performances stables et des rendements prévisibles.</p>
            </div>
        </div>
    </div>

    <!-- FAQ améliorée -->
    <div class="bg-gradient-to-br from-gray-50 to-white rounded-2xl shadow-lg p-8">
        <div class="text-center mb-10">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">
                <i class="fas fa-question-circle text-green-600 mr-3"></i>
                Questions Fréquentes
            </h2>
            <p class="text-gray-600 text-lg">Trouvez rapidement des réponses à vos questions</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div class="faq-item">
                    <button
                        class="faq-question w-full text-left p-5 bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 flex justify-between items-center group">
                        <span class="font-bold text-gray-800 text-lg">Quand vais-je recevoir mon premier paiement
                            ?</span>
                        <div
                            class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center group-hover:bg-green-200 transition-colors">
                            <i class="fas fa-chevron-down text-green-600 transition-transform"></i>
                        </div>
                    </button>
                    <div class="faq-answer hidden mt-3 p-4 bg-green-50 rounded-lg border border-green-100">
                        <p class="text-gray-700">
                            Les paiements de ROI sont effectués tous les jours à minuit (UTC+1).
                            Votre premier paiement sera effectué 24 heures après votre investissement.
                        </p>
                    </div>
                </div>

                <div class="faq-item">
                    <button
                        class="faq-question w-full text-left p-5 bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 flex justify-between items-center group">
                        <span class="font-bold text-gray-800 text-lg">Puis-je retirer mon investissement avant les 30
                            jours ?</span>
                        <div
                            class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                            <i class="fas fa-chevron-down text-blue-600 transition-transform"></i>
                        </div>
                    </button>
                    <div class="faq-answer hidden mt-3 p-4 bg-blue-50 rounded-lg border border-blue-100">
                        <p class="text-gray-700">
                            oui, vous pouvez retirer votre argent a tout moment, les fond sont disponible tout les jours
                            et retirable pour un minimum de 1000f
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="faq-item">
                    <button
                        class="faq-question w-full text-left p-5 bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 flex justify-between items-center group">
                        <span class="font-bold text-gray-800 text-lg">Y a-t-il des frais supplémentaires ?</span>
                        <div
                            class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                            <i class="fas fa-chevron-down text-purple-600 transition-transform"></i>
                        </div>
                    </button>
                    <div class="faq-answer hidden mt-3 p-4 bg-purple-50 rounded-lg border border-purple-100">
                        <p class="text-gray-700">
                            Aucun frais caché. Le montant affiché est le montant exact que vous payez.
                            Les retraits sont gratuits jusqu'à 3 fois par mois.
                        </p>
                    </div>
                </div>

                <div class="faq-item">
                    <button
                        class="faq-question w-full text-left p-5 bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 flex justify-between items-center group">
                        <span class="font-bold text-gray-800 text-lg">Comment fonctionnent les bonus de parrainage
                            ?</span>
                        <div
                            class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center group-hover:bg-yellow-200 transition-colors">
                            <i class="fas fa-chevron-down text-yellow-600 transition-transform"></i>
                        </div>
                    </button>
                    <div class="faq-answer hidden mt-3 p-4 bg-yellow-50 rounded-lg border border-yellow-100">
                        <p class="text-gray-700">
                            Vous recevez un pourcentage sur chaque investissement de vos filleuls.
                            Le bonus est versé instantanément et ajouté à votre solde parrainage.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Animations principales */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: translateY(0);
        }

        to {
            opacity: 0;
            transform: translateY(-20px);
        }
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100%);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideOutRight {
        from {
            opacity: 1;
            transform: translateX(0);
        }

        to {
            opacity: 0;
            transform: translateX(100%);
        }
    }

    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes pulseOnce {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
        }
    }

    @keyframes pulseSlow {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.8;
        }
    }

    @keyframes shake {

        0%,
        100% {
            transform: translateX(0);
        }

        10%,
        30%,
        50%,
        70%,
        90% {
            transform: translateX(-5px);
        }

        20%,
        40%,
        60%,
        80% {
            transform: translateX(5px);
        }
    }

    /* Classes d'animation */
    .animate-fade-in {
        animation: fadeIn 0.3s ease-out;
    }

    .animate-fade-up {
        animation: fadeUp 0.6s ease-out;
    }

    .animate-slide-in-right {
        animation: slideInRight 0.3s ease-out;
    }

    .animate-slide-out-right {
        animation: slideOutRight 0.3s ease-in;
    }

    .animate-slide-up {
        animation: slideUp 0.4s ease-out;
    }

    .animate-pulse-once {
        animation: pulseOnce 1s ease;
    }

    .animate-pulse-slow {
        animation: pulseSlow 2s infinite;
    }

    .animate-shake {
        animation: shake 0.5s ease-in-out;
    }

    /* Autres classes */
    .rotate-180 {
        transform: rotate(180deg);
        transition: transform 0.3s ease;
    }

    /* Transition pour les boutons */
    .transition-all {
        transition-property: all;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 300ms;
    }

    /* Animation pour les cartes */
    .group:hover .group-hover\:scale-110 {
        transform: scale(1.1);
        transition: transform 0.3s ease;
    }
</style>

<script>
    // Animation des cartes
    function animateCards() {
        const cards = document.querySelectorAll('.animate-fade-up');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';

            setTimeout(() => {
                card.style.transition = 'all 0.6s ease-out';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    }

    // Initialisation
    document.addEventListener('DOMContentLoaded', function () {
        console.log('💰 Plateforme d\'investissement initialisée (LeekPay)');
        animateCards();

        // Gestion de la FAQ
        document.querySelectorAll('.faq-question').forEach(button => {
            button.addEventListener('click', () => {
                const answer = button.nextElementSibling;
                const icon = button.querySelector('.fa-chevron-down');

                document.querySelectorAll('.faq-answer').forEach(a => {
                    if (a !== answer) {
                        a.classList.add('hidden');
                        a.previousElementSibling.querySelector('.fa-chevron-down').classList.remove('rotate-180');
                    }
                });

                answer.classList.toggle('hidden');
                icon.classList.toggle('rotate-180');

                if (!answer.classList.contains('hidden')) {
                    answer.style.opacity = '0';
                    answer.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        answer.style.transition = 'all 0.3s ease';
                        answer.style.opacity = '1';
                        answer.style.transform = 'translateY(0)';
                    }, 10);
                }
            });
        });
    });
</script>