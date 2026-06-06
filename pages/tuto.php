<?php
// tutorials.php - Page de tutoriels vidéos et textuels

// Initialiser la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuration
$site_name = "Investian";
$site_url = "https://investian.com";

// Catégories de tutoriels
$tutorial_categories = [
    'getting-started' => [
        'title' => 'Débuter sur Investian',
        'icon' => 'fas fa-rocket',
        'color' => 'from-blue-500 to-cyan-500',
        'description' => 'Apprenez les bases pour commencer à investir',
        'tutorials' => [
            [
                'id' => 'tuto-1',
                'title' => 'Créer son compte',
                'type' => 'video',
                'duration' => '3:15',
                'level' => 'beginner',
                'youtube_id' => 'dQw4w9WgXcQ',
                'text_content' => '
                    <h3>Étapes pour créer votre compte</h3>
                    <ol class="space-y-3">
                        <li class="flex items-start">
                            <span class="flex-shrink-0 w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mr-3">
                                <span class="text-blue-600 dark:text-blue-400 font-bold">1</span>
                            </span>
                            <div>
                                <strong>Cliquez sur "S\'inscrire"</strong>
                                <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">Depuis la page d\'accueil ou de connexion</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <span class="flex-shrink-0 w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mr-3">
                                <span class="text-blue-600 dark:text-blue-400 font-bold">2</span>
                            </span>
                            <div>
                                <strong>Remplissez le formulaire</strong>
                                <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">Nom, prénom, email, téléphone et mot de passe sécurisé</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <span class="flex-shrink-0 w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mr-3">
                                <span class="text-blue-600 dark:text-blue-400 font-bold">3</span>
                            </span>
                            <div>
                                <strong>Validez votre email</strong>
                                <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">Cliquez sur le lien dans l\'email de confirmation</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <span class="flex-shrink-0 w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mr-3">
                                <span class="text-blue-600 dark:text-blue-400 font-bold">4</span>
                            </span>
                            <div>
                                <strong>Complétez votre profil</strong>
                                <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">Ajoutez vos informations personnelles pour la sécurité</p>
                            </div>
                        </li>
                    </ol>
                ',
                'tips' => [
                    'Utilisez une adresse email valide que vous consultez régulièrement',
                    'Choisissez un mot de passe fort avec chiffres, majuscules et caractères spéciaux',
                    'Notez votre code de parrainage unique pour inviter des amis'
                ]
            ],
            [
                'id' => 'tuto-2',
                'title' => 'Première connexion',
                'type' => 'text',
                'duration' => '2 min',
                'level' => 'beginner',
                'text_content' => '
                    <h3>Se connecter pour la première fois</h3>
                    <div class="space-y-4">
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <strong class="flex items-center text-green-600 dark:text-green-400">
                                <i class="fas fa-check-circle mr-2"></i>
                                Étape 1: Accédez à la page de connexion
                            </strong>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mt-2 ml-6">
                                Cliquez sur "Se connecter" depuis n\'importe quelle page du site
                            </p>
                        </div>
                        
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <strong class="flex items-center text-green-600 dark:text-green-400">
                                <i class="fas fa-check-circle mr-2"></i>
                                Étape 2: Entrez vos identifiants
                            </strong>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mt-2 ml-6">
                                Utilisez l\'email et le mot de passe que vous avez créés
                            </p>
                        </div>
                        
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <strong class="flex items-center text-green-600 dark:text-green-400">
                                <i class="fas fa-check-circle mr-2"></i>
                                Étape 3: Explorez votre tableau de bord
                            </strong>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mt-2 ml-6">
                                Découvrez les différentes sections: Portefeuille, Investissements, Vidéos
                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <h4 class="font-bold text-blue-800 dark:text-blue-300 mb-2">
                            <i class="fas fa-lightbulb mr-2"></i>
                            Astuce de sécurité
                        </h4>
                        <p class="text-blue-700 dark:text-blue-400 text-sm">
                            Activez l\'authentification à deux facteurs (2FA) dans vos paramètres de sécurité pour protéger votre compte.
                        </p>
                    </div>
                ',
                'tips' => [
                    'Cochez "Se souvenir de moi" sur vos appareils personnels',
                    'Déconnectez-vous toujours sur les appareils publics',
                    'Vérifiez régulièrement votre activité de connexion'
                ]
            ],
            [
                'id' => 'tuto-3',
                'title' => 'Comprendre l\'interface',
                'type' => 'video',
                'duration' => '5:42',
                'level' => 'beginner',
                'youtube_id' => 'dQw4w9WgXcQ',
                'text_content' => '
                    <h3>Navigation dans l\'interface Investian</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div class="flex items-center mb-2">
                                <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center mr-3">
                                    <i class="fas fa-tachometer-alt text-green-600 dark:text-green-400"></i>
                                </div>
                                <strong>Tableau de bord</strong>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">
                                Vue d\'ensemble de vos soldes, investissements actifs et gains récents.
                            </p>
                        </div>
                        
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div class="flex items-center mb-2">
                                <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mr-3">
                                    <i class="fas fa-wallet text-blue-600 dark:text-blue-400"></i>
                                </div>
                                <strong>Portefeuille</strong>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">
                                Gérez vos soldes: investissement, publicité, parrainage et retraits.
                            </p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div class="flex items-center mb-2">
                                <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center mr-3">
                                    <i class="fas fa-chart-line text-purple-600 dark:text-purple-400"></i>
                                </div>
                                <strong>Investir</strong>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">
                                Choisissez un plan d\'investissement et suivez sa progression.
                            </p>
                        </div>
                        
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div class="flex items-center mb-2">
                                <div class="w-10 h-10 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center mr-3">
                                    <i class="fas fa-video text-orange-600 dark:text-orange-400"></i>
                                </div>
                                <strong>Vidéos</strong>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">
                                Regardez des vidéos rémunérées et gagnez de l\'argent quotidiennement.
                            </p>
                        </div>
                    </div>
                ',
                'tips' => [
                    'Utilisez le menu latéral pour une navigation rapide',
                    'Les notifications vous alertent des événements importants',
                    'Le bouton d\'aide (?) explique chaque fonctionnalité'
                ]
            ]
        ]
    ],
    'investing' => [
        'title' => 'Investir & Gagner',
        'icon' => 'fas fa-chart-line',
        'color' => 'from-green-500 to-emerald-500',
        'description' => 'Maximisez vos rendements d\'investissement',
        'tutorials' => [
            [
                'id' => 'tuto-4',
                'title' => 'Choisir un plan d\'investissement',
                'type' => 'video',
                'duration' => '4:30',
                'level' => 'intermediate',
                'youtube_id' => 'dQw4w9WgXcQ',
                'text_content' => '
                    <h3>Comment choisir le meilleur plan pour vous</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-800">
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Plan</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Investissement</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">ROI Journalier</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Durée</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Pour qui ?</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900 dark:text-white">Starter</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Parfait pour débuter</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium">5 000 FCFA</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-green-600 dark:text-green-400">2.5%</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium">30 jours</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-sm text-gray-600 dark:text-gray-400">Nouveaux investisseurs</div>
                                    </td>
                                </tr>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900 dark:text-white">Pro</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Équilibre rendement/risque</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium">25 000 FCFA</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-green-600 dark:text-green-400">3.5%</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium">60 jours</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-sm text-gray-600 dark:text-gray-400">Investisseurs réguliers</div>
                                    </td>
                                </tr>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900 dark:text-white">VIP</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Rendements maximaux</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium">100 000 FCFA</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-green-600 dark:text-green-400">5%</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium">90 jours</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-sm text-gray-600 dark:text-gray-400">Investisseurs expérimentés</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <h4 class="font-bold text-blue-800 dark:text-blue-300 mb-2">
                                <i class="fas fa-coins mr-2"></i>
                                Budget
                            </h4>
                            <p class="text-blue-700 dark:text-blue-400 text-sm">
                                Commencez avec un montant confortable. Vous pouvez toujours augmenter plus tard.
                            </p>
                        </div>
                        
                        <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <h4 class="font-bold text-green-800 dark:text-green-300 mb-2">
                                <i class="fas fa-calendar-alt mr-2"></i>
                                Durée
                            </h4>
                            <p class="text-green-700 dark:text-green-400 text-sm">
                                Choisissez une durée qui correspond à vos objectifs financiers.
                            </p>
                        </div>
                        
                        <div class="p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                            <h4 class="font-bold text-purple-800 dark:text-purple-300 mb-2">
                                <i class="fas fa-bullseye mr-2"></i>
                                Objectifs
                            </h4>
                            <p class="text-purple-700 dark:text-purple-400 text-sm">
                                Définissez clairement ce que vous voulez accomplir avec votre investissement.
                            </p>
                        </div>
                    </div>
                ',
                'tips' => [
                    'Commencez petit pour comprendre le système',
                    'Réinvestissez vos gains pour accélérer la croissance',
                    'Diversifiez avec plusieurs plans si votre budget le permet'
                ]
            ],
            [
                'id' => 'tuto-5',
                'title' => 'Faire un dépôt',
                'type' => 'text',
                'duration' => '3 min',
                'level' => 'beginner',
                'text_content' => '
                    <h3>Déposer des fonds sur votre compte</h3>
                    
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mr-4">
                                <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                    <span class="text-green-600 dark:text-green-400 font-bold text-xl">1</span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-900 dark:text-white mb-2">Accédez à la page de dépôt</h4>
                                <p class="text-gray-600 dark:text-gray-400">
                                    Cliquez sur "Dépôt" dans votre tableau de bord ou dans le menu principal.
                                </p>
                                <div class="mt-2 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                    <code class="text-sm text-gray-700 dark:text-gray-300">
                                        Tableau de bord → Portefeuille → Faire un dépôt
                                    </code>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mr-4">
                                <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                    <span class="text-green-600 dark:text-green-400 font-bold text-xl">2</span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-900 dark:text-white mb-2">Choisissez votre méthode</h4>
                                <p class="text-gray-600 dark:text-gray-400">
                                    Sélectionnez Orange Money ou MTN Mobile Money selon votre préférence.
                                </p>
                                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div class="p-3 border border-orange-200 dark:border-orange-800 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center mr-3">
                                                <i class="fas fa-mobile-alt text-orange-600 dark:text-orange-400"></i>
                                            </div>
                                            <strong class="text-orange-700 dark:text-orange-300">Orange Money</strong>
                                        </div>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">
                                            Code USSD: #144#
                                        </p>
                                    </div>
                                    <div class="p-3 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 rounded-lg bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center mr-3">
                                                <i class="fas fa-sim-card text-yellow-600 dark:text-yellow-400"></i>
                                            </div>
                                            <strong class="text-yellow-700 dark:text-yellow-300">MTN Mobile Money</strong>
                                        </div>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">
                                            Code USSD: *126#
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mr-4">
                                <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                    <span class="text-green-600 dark:text-green-400 font-bold text-xl">3</span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-900 dark:text-white mb-2">Suivez les instructions</h4>
                                <p class="text-gray-600 dark:text-gray-400">
                                    Entrez le montant, confirmez et effectuez le paiement depuis votre mobile.
                                </p>
                                <div class="mt-3 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                    <p class="text-sm text-blue-700 dark:text-blue-400">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Le système génère automatiquement un numéro de transaction. Gardez-le pour référence.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                ',
                'tips' => [
                    'Le minimum de dépôt est de 1 000 FCFA',
                    'Les fonds sont crédités instantanément',
                    'Conservez toujours le code de transaction',
                    'Vérifiez que le numéro affiché correspond à votre compte'
                ]
            ],
            [
                'id' => 'tuto-6',
                'title' => 'Suivre ses investissements',
                'type' => 'video',
                'duration' => '6:15',
                'level' => 'intermediate',
                'youtube_id' => 'dQw4w9WgXcQ',
                'text_content' => '
                    <h3>Surveiller la performance de vos investissements</h3>
                    
                    <div class="mb-6">
                        <h4 class="font-bold text-gray-900 dark:text-white mb-4">Indicateurs clés à surveiller</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-gray-600 dark:text-gray-400 text-sm">Montant investi</span>
                                    <i class="fas fa-coins text-yellow-500"></i>
                                </div>
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">25 000 FCFA</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Capital initial</div>
                            </div>
                            
                            <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-gray-600 dark:text-gray-400 text-sm">Gains actuels</span>
                                    <i class="fas fa-chart-line text-green-500"></i>
                                </div>
                                <div class="text-2xl font-bold text-green-600 dark:text-green-400">8 750 FCFA</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">+35% depuis le début</div>
                            </div>
                            
                            <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-gray-600 dark:text-gray-400 text-sm">Jours restants</span>
                                    <i class="fas fa-calendar-alt text-blue-500"></i>
                                </div>
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">42</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Sur 90 jours au total</div>
                            </div>
                            
                            <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-gray-600 dark:text-gray-400 text-sm">ROI quotidien</span>
                                    <i class="fas fa-bolt text-purple-500"></i>
                                </div>
                                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">625 FCFA</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">2.5% par jour</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <h4 class="font-bold text-gray-900 dark:text-white mb-4">Graphique de progression</h4>
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Progression de l\'investissement</div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">46.7%</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Fin prévue</div>
                                    <div class="font-bold text-gray-900 dark:text-white">15/03/2024</div>
                                </div>
                            </div>
                            
                            <!-- Barre de progression -->
                            <div class="relative pt-1">
                                <div class="flex mb-2 items-center justify-between">
                                    <div>
                                        <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-green-600 bg-green-200">
                                            En cours
                                        </span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-semibold inline-block text-green-600">
                                            46.7%
                                        </span>
                                    </div>
                                </div>
                                <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-green-100 dark:bg-green-900/30">
                                    <div style="width:46.7%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-green-500"></div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <div class="text-gray-600 dark:text-gray-400">Date de début</div>
                                    <div class="font-medium">15/12/2023</div>
                                </div>
                                <div>
                                    <div class="text-gray-600 dark:text-gray-400">Gain total estimé</div>
                                    <div class="font-medium text-green-600 dark:text-green-400">56 250 FCFA</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                        <h4 class="font-bold text-yellow-800 dark:text-yellow-300 mb-2">
                            <i class="fas fa-bell mr-2"></i>
                            Notifications importantes
                        </h4>
                        <p class="text-yellow-700 dark:text-yellow-400 text-sm">
                            Vous recevrez une notification lorsque votre investissement arrive à échéance. Pensez à réinvestir pour continuer à générer des revenus.
                        </p>
                    </div>
                ',
                'tips' => [
                    'Vérifiez vos gains quotidiens à la même heure',
                    'Notez les dates d\'échéance dans votre calendrier',
                    'Comparez les performances de différents plans',
                    'Utilisez les données pour optimiser vos futurs investissements'
                ]
            ]
        ]
    ],
    'earning' => [
        'title' => 'Gains & Retraits',
        'icon' => 'fas fa-money-bill-wave',
        'color' => 'from-purple-500 to-pink-500',
        'description' => 'Retirez vos gains facilement',
        'tutorials' => [
            [
                'id' => 'tuto-7',
                'title' => 'Regarder des vidéos rémunérées',
                'type' => 'video',
                'duration' => '4:10',
                'level' => 'beginner',
                'youtube_id' => 'dQw4w9WgXcQ',
                'text_content' => '
                    <h3>Gagnez de l\'argent en regardant des vidéos</h3>
                    
                    <div class="mb-6">
                        <h4 class="font-bold text-gray-900 dark:text-white mb-4">Comment ça marche ?</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="text-center p-4">
                                <div class="w-16 h-16 rounded-2xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-play text-blue-600 dark:text-blue-400 text-2xl"></i>
                                </div>
                                <h5 class="font-bold text-gray-900 dark:text-white mb-2">1. Sélectionnez</h5>
                                <p class="text-gray-600 dark:text-gray-400 text-sm">
                                    Choisissez une vidéo dans la liste des vidéos disponibles
                                </p>
                            </div>
                            
                            <div class="text-center p-4">
                                <div class="w-16 h-16 rounded-2xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-eye text-green-600 dark:text-green-400 text-2xl"></i>
                                </div>
                                <h5 class="font-bold text-gray-900 dark:text-white mb-2">2. Regardez</h5>
                                <p class="text-gray-600 dark:text-gray-400 text-sm">
                                    Regardez la vidéo en entier sans la quitter
                                </p>
                            </div>
                            
                            <div class="text-center p-4">
                                <div class="w-16 h-16 rounded-2xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-coins text-purple-600 dark:text-purple-400 text-2xl"></i>
                                </div>
                                <h5 class="font-bold text-gray-900 dark:text-white mb-2">3. Gagnez</h5>
                                <p class="text-gray-600 dark:text-gray-400 text-sm">
                                    Recevez instantanément votre récompense
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <h4 class="font-bold text-gray-900 dark:text-white mb-4">Limites quotidiennes par plan</h4>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-800">
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Plan d\'investissement</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Vidéos/jour</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Gain/vidéo</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Gain max/jour</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-gray-900 dark:text-white">Starter</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium">5 vidéos</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-green-600 dark:text-green-400">50 FCFA</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium">250 FCFA</div>
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-gray-900 dark:text-white">Pro</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium">10 vidéos</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-green-600 dark:text-green-400">75 FCFA</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium">750 FCFA</div>
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-gray-900 dark:text-white">VIP</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium">20 vidéos</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-green-600 dark:text-green-400">100 FCFA</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium">2 000 FCFA</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <div class="flex items-start">
                            <i class="fas fa-lightbulb text-blue-600 dark:text-blue-400 text-xl mt-1 mr-3"></i>
                            <div>
                                <h4 class="font-bold text-blue-800 dark:text-blue-300 mb-2">Conseils pour maximiser vos gains</h4>
                                <ul class="space-y-2 text-blue-700 dark:text-blue-400 text-sm">
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle mt-1 mr-2 text-sm"></i>
                                        <span>Regardez toutes vos vidéos quotidiennes pour atteindre le maximum</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle mt-1 mr-2 text-sm"></i>
                                        <span>Les vidéos se renouvellent chaque jour à minuit</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle mt-1 mr-2 text-sm"></i>
                                        <span>Ne quittez pas la page pendant la lecture pour valider le gain</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                ',
                'tips' => [
                    'Regardez vos vidéos à la même heure chaque jour pour créer une routine',
                    'Assurez-vous d\'avoir une connexion internet stable',
                    'Augmentez votre plan pour avoir plus de vidéos quotidiennes',
                    'Les gains des vidéos vont directement sur votre solde "Publicité"'
                ]
            ],
            [
                'id' => 'tuto-8',
                'title' => 'Demander un retrait',
                'type' => 'text',
                'duration' => '5 min',
                'level' => 'intermediate',
                'text_content' => '
                    <h3>Retirer vos gains en toute simplicité</h3>
                    
                    <div class="mb-6">
                        <h4 class="font-bold text-gray-900 dark:text-white mb-4">Conditions de retrait</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center mb-3">
                                    <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center mr-3">
                                        <i class="fas fa-money-bill-wave text-green-600 dark:text-green-400"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900 dark:text-white">Montant minimum</div>
                                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">1 000 FCFA</div>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Pour toutes les méthodes de paiement
                                </p>
                            </div>
                            
                            <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center mb-3">
                                    <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mr-3">
                                        <i class="fas fa-clock text-blue-600 dark:text-blue-400"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900 dark:text-white">Délai de traitement</div>
                                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">24-48h</div>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Du lundi au vendredi
                                </p>
                            </div>
                            
                            <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center mb-3">
                                    <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center mr-3">
                                        <i class="fas fa-percentage text-purple-600 dark:text-purple-400"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900 dark:text-white">Frais</div>
                                        <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">0%</div>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Aucun frais sur les retraits
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-6">
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <h4 class="font-bold text-gray-900 dark:text-white mb-3 flex items-center">
                                <span class="w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center mr-3">
                                    <span class="text-green-600 dark:text-green-400 font-bold">1</span>
                                </span>
                                Accédez à la page de retrait
                            </h4>
                            <p class="text-gray-600 dark:text-gray-400 ml-11">
                                Allez dans "Portefeuille" → "Retirer" ou cliquez sur le bouton "Retirer" dans votre tableau de bord.
                            </p>
                        </div>
                        
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <h4 class="font-bold text-gray-900 dark:text-white mb-3 flex items-center">
                                <span class="w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center mr-3">
                                    <span class="text-green-600 dark:text-green-400 font-bold">2</span>
                                </span>
                                Sélectionnez le compte source
                            </h4>
                            <p class="text-gray-600 dark:text-gray-400 ml-11">
                                Choisissez d\'où retirer l\'argent:
                            </p>
                            <div class="mt-3 ml-11 grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div class="p-3 border border-blue-200 dark:border-blue-800 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mr-3">
                                            <i class="fas fa-chart-line text-blue-600 dark:text-blue-400"></i>
                                        </div>
                                        <strong>Investissement</strong>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">
                                        Gains sur vos placements
                                    </p>
                                </div>
                                <div class="p-3 border border-green-200 dark:border-green-800 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center mr-3">
                                            <i class="fas fa-video text-green-600 dark:text-green-400"></i>
                                        </div>
                                        <strong>Publicité</strong>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">
                                        Gains des vidéos
                                    </p>
                                </div>
                                <div class="p-3 border border-purple-200 dark:border-purple-800 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center mr-3">
                                            <i class="fas fa-users text-purple-600 dark:text-purple-400"></i>
                                        </div>
                                        <strong>Parrainage</strong>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">
                                        Bonus de parrainage
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <h4 class="font-bold text-gray-900 dark:text-white mb-3 flex items-center">
                                <span class="w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center mr-3">
                                    <span class="text-green-600 dark:text-green-400 font-bold">3</span>
                                </span>
                                Choisissez la méthode et confirmez
                            </h4>
                            <div class="ml-11 space-y-4">
                                <div>
                                    <p class="text-gray-600 dark:text-gray-400 mb-2">
                                        Sélectionnez votre méthode de retrait:
                                    </p>
                                    <div class="flex flex-wrap gap-3">
                                        <div class="flex items-center px-4 py-2 bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300 rounded-lg">
                                            <i class="fas fa-mobile-alt mr-2"></i>
                                            <span>Orange Money</span>
                                        </div>
                                        <div class="flex items-center px-4 py-2 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 rounded-lg">
                                            <i class="fas fa-sim-card mr-2"></i>
                                            <span>MTN Mobile Money</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <p class="text-gray-600 dark:text-gray-400 mb-2">
                                        Entrez le montant et votre numéro de téléphone:
                                    </p>
                                    <div class="p-3 bg-white dark:bg-gray-900 rounded-lg border border-gray-300 dark:border-gray-700">
                                        <div class="text-sm text-gray-600 dark:text-gray-400">Montant minimum: <strong>1 000 FCFA</strong></div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Format du numéro: <strong>+237 6XX XX XX XX</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                        <h4 class="font-bold text-yellow-800 dark:text-yellow-300 mb-2">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Important: Premier retrait sur investissement
                        </h4>
                        <p class="text-yellow-700 dark:text-yellow-400 text-sm">
                            Pour des raisons de sécurité, lors de votre premier retrait sur le solde d\'investissement, un des 3 derniers chiffres de votre numéro sera automatiquement modifié. C\'est normal et cela n\'affecte pas la réception des fonds.
                        </p>
                    </div>
                ',
                'tips' => [
                    'Vérifiez votre solde avant de faire une demande',
                    'Assurez-vous que le numéro entré est correct',
                    'Les retraits sont traités du lundi au vendredi',
                    'Conservez le numéro de transaction pour suivi',
                    'Vérifiez vos notifications pour le statut du retrait'
                ]
            ]
        ]
    ],
    'referral' => [
        'title' => 'Parrainage & Bonus',
        'icon' => 'fas fa-users',
        'color' => 'from-orange-500 to-red-500',
        'description' => 'Gagnez en parrainant vos amis',
        'tutorials' => [
            [
                'id' => 'tuto-9',
                'title' => 'Comment parrainer',
                'type' => 'video',
                'duration' => '3:45',
                'level' => 'beginner',
                'youtube_id' => 'dQw4w9WgXcQ',
                'text_content' => '
                    <h3>Gagnez des commissions en parrainant</h3>
                    
                    <div class="mb-6">
                        <h4 class="font-bold text-gray-900 dark:text-white mb-4">Votre code de parrainage unique</h4>
                        
                        <div class="p-6 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl border border-green-200 dark:border-green-800">
                            <div class="text-center mb-4">
                                <p class="text-gray-600 dark:text-gray-400 mb-2">Votre code personnel</p>
                                <div class="text-3xl font-bold text-green-600 dark:text-green-400 tracking-wider font-mono">
                                    <?php echo isset($_SESSION[\'user_referral_code\']) ? $_SESSION[\'user_referral_code\'] : \'IZYAB1234\'; ?>
                                </div>
                            </div>
                            <div class="flex flex-col sm:flex-row gap-3">
                                <button onclick="copyReferralCode()" 
                                        class="flex-1 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                                    <i class="fas fa-copy"></i>
                                    Copier le code
                                </button>
                                <button onclick="shareReferral()" 
                                        class="flex-1 py-3 border-2 border-green-600 text-green-600 hover:bg-green-50 dark:text-green-400 dark:border-green-500 dark:hover:bg-green-900/20 font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                                    <i class="fas fa-share-alt"></i>
                                    Partager
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <h4 class="font-bold text-gray-900 dark:text-white mb-4">Comment inviter des amis</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="text-center p-4">
                                <div class="w-16 h-16 rounded-2xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-link text-blue-600 dark:text-blue-400 text-2xl"></i>
                                </div>
                                <h5 class="font-bold text-gray-900 dark:text-white mb-2">Lien de parrainage</h5>
                                <p class="text-gray-600 dark:text-gray-400 text-sm">
                                    Partagez votre lien personnel avec vos amis
                                </p>
                                <div class="mt-3 p-2 bg-gray-100 dark:bg-gray-800 rounded text-xs font-mono truncate">
                                    <?php echo $site_url; ?>/?ref=<?php echo isset($_SESSION[\'user_referral_code\']) ? $_SESSION[\'user_referral_code\'] : \'IZYAB1234\'; ?>
                                </div>
                            </div>
                            
                            <div class="text-center p-4">
                                <div class="w-16 h-16 rounded-2xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-qrcode text-green-600 dark:text-green-400 text-2xl"></i>
                                </div>
                                <h5 class="font-bold text-gray-900 dark:text-white mb-2">QR Code</h5>
                                <p class="text-gray-600 dark:text-gray-400 text-sm">
                                    Scannez le code pour s\'inscrire rapidement
                                </p>
                                <div class="mt-3 flex justify-center">
                                    <div class="w-20 h-20 bg-gray-300 dark:bg-gray-700 rounded flex items-center justify-center">
                                        <i class="fas fa-qrcode text-2xl text-gray-500"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-center p-4">
                                <div class="w-16 h-16 rounded-2xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-share-nodes text-purple-600 dark:text-purple-400 text-2xl"></i>
                                </div>
                                <h5 class="font-bold text-gray-900 dark:text-white mb-2">Réseaux sociaux</h5>
                                <p class="text-gray-600 dark:text-gray-400 text-sm">
                                    Partagez directement sur vos réseaux
                                </p>
                                <div class="mt-3 flex justify-center gap-2">
                                    <button class="w-10 h-10 rounded-lg bg-blue-600 text-white flex items-center justify-center">
                                        <i class="fab fa-facebook-f"></i>
                                    </button>
                                    <button class="w-10 h-10 rounded-lg bg-blue-400 text-white flex items-center justify-center">
                                        <i class="fab fa-twitter"></i>
                                    </button>
                                    <button class="w-10 h-10 rounded-lg bg-green-500 text-white flex items-center justify-center">
                                        <i class="fab fa-whatsapp"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <h4 class="font-bold text-blue-800 dark:text-blue-300 mb-2">
                            <i class="fas fa-gift mr-2"></i>
                            Bonus de parrainage
                        </h4>
                        <p class="text-blue-700 dark:text-blue-400 text-sm">
                            Vous recevez 10% sur le premier dépôt de chaque filleul, et 5% sur leurs investissements pendant 6 mois. Plus vous parrainez, plus vous gagnez !
                        </p>
                    </div>
                ',
                'tips' => [
                    'Partagez votre code avec vos amis et famille en qui vous avez confiance',
                    'Expliquez les avantages d\'Investian pour les motiver',
                    'Suivez le nombre de filleuls dans votre tableau de bord',
                    'Les bonus sont crédités automatiquement sur votre solde parrainage'
                ]
            ]
        ]
    ]
];

// Statistiques
$tutorial_stats = [
    'total_tutorials' => 0,
    'total_videos' => 0,
    'total_text' => 0,
    'total_duration' => '0h 0min'
];

foreach ($tutorial_categories as $category) {
    $tutorial_stats['total_tutorials'] += count($category['tutorials']);
    foreach ($category['tutorials'] as $tutorial) {
        if ($tutorial['type'] === 'video') {
            $tutorial_stats['total_videos']++;
        } else {
            $tutorial_stats['total_text']++;
        }
    }
}

$tutorial_stats['total_duration'] = floor($tutorial_stats['total_tutorials'] * 4) . 'min';
?>

<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="Tutoriels vidéos et textuels pour maîtriser <?php echo $site_name; ?> - Apprenez à investir, gagner et retirer">
    <meta name="keywords" content="tutoriels, guide, aide, formation, <?php echo $site_name; ?>, investissement">
    <meta name="author" content="<?php echo $site_name; ?>">
    
    <title>Tutoriels | <?php echo $site_name; ?> - Apprendre à utiliser la plateforme</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- YouTube Iframe API -->
    <script src="https://www.youtube.com/iframe_api"></script>
    
    <!-- Inline Critical CSS -->
    <style>
        :root {
            --primary: #2e8b57;
            --primary-light: #3cb371;
            --primary-dark: #1f6b42;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            min-height: 100vh;
            color: #334155;
            line-height: 1.5;
        }
        
        @media (prefers-color-scheme: dark) {
            body {
                background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                color: #cbd5e1;
            }
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideIn {
            from { transform: translateX(-20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        .animate-fadeIn {
            animation: fadeIn 0.6s ease-out;
        }
        
        .animate-slideIn {
            animation: slideIn 0.4s ease-out;
        }
        
        .animate-pulse {
            animation: pulse 2s infinite;
        }
        
        /* YouTube Player */
        .video-container {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 */
            height: 0;
            overflow: hidden;
            border-radius: 12px;
        }
        
        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }
        
        /* Accessibility */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
        
        /* Mobile optimization */
        @media (max-width: 640px) {
            .mobile-stack {
                flex-direction: column;
            }
        }
        
        /* Support for notches */
        @supports (padding: max(0px)) {
            .safe-area {
                padding-left: max(1rem, env(safe-area-inset-left));
                padding-right: max(1rem, env(safe-area-inset-right));
                padding-top: max(1rem, env(safe-area-inset-top));
                padding-bottom: max(1rem, env(safe-area-inset-bottom));
            }
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Header -->
    <header class="bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <a href="index.php?page=home" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center">
                        <i class="fas fa-graduation-cap text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Tutoriels</h1>
                        <p class="text-xs text-green-600 dark:text-green-400"><?php echo $site_name; ?></p>
                    </div>
                </a>
                
                <!-- Navigation -->
                <nav class="hidden md:flex items-center gap-6">
                    <a href="index.php?page=home" class="text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">
                        <i class="fas fa-home mr-2"></i>Accueil
                    </a>
                    <a href="index.php?page=home" class="text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">
                        <i class="fas fa-tachometer-alt mr-2"></i>Tableau de bord
                    </a>
                    <a href="index.php?page=support" class="text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">
                        <i class="fas fa-headset mr-2"></i>Support
                    </a>
                    <a href="index.php?page=tuto" class="text-green-600 dark:text-green-400 font-medium">
                        <i class="fas fa-graduation-cap mr-2"></i>Tutoriels
                    </a>
                </nav>
                
                <!-- Mobile menu button -->
                <button id="mobileMenuButton" class="md:hidden text-gray-600 dark:text-gray-400">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-green-50 to-emerald-100 dark:from-green-900/30 dark:to-emerald-900/20 py-12">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white dark:bg-slate-800 shadow-lg mb-6">
                        <i class="fas fa-graduation-cap text-green-600 dark:text-green-400 text-3xl"></i>
                    </div>
                    
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">
                        Centre de <span class="text-green-600">Formation</span>
                    </h1>
                    
                    <p class="text-xl text-gray-600 dark:text-gray-400 mb-8 max-w-3xl mx-auto">
                        Apprenez à maîtriser toutes les fonctionnalités d'<?php echo $site_name; ?> avec nos tutoriels vidéos et guides textuels détaillés.
                    </p>
                </div>
                
                <!-- Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-3xl mx-auto mb-8">
                    <div class="bg-white dark:bg-slate-800/50 backdrop-blur-sm rounded-xl p-4 border border-green-200 dark:border-green-800 text-center">
                        <div class="text-2xl font-bold text-green-600"><?php echo $tutorial_stats['total_tutorials']; ?></div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Tutoriels</div>
                    </div>
                    <div class="bg-white dark:bg-slate-800/50 backdrop-blur-sm rounded-xl p-4 border border-green-200 dark:border-green-800 text-center">
                        <div class="text-2xl font-bold text-green-600"><?php echo $tutorial_stats['total_videos']; ?></div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Vidéos</div>
                    </div>
                    <div class="bg-white dark:bg-slate-800/50 backdrop-blur-sm rounded-xl p-4 border border-green-200 dark:border-green-800 text-center">
                        <div class="text-2xl font-bold text-green-600"><?php echo $tutorial_stats['total_text']; ?></div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Guides écrits</div>
                    </div>
                    <div class="bg-white dark:bg-slate-800/50 backdrop-blur-sm rounded-xl p-4 border border-green-200 dark:border-green-800 text-center">
                        <div class="text-2xl font-bold text-green-600"><?php echo $tutorial_stats['total_duration']; ?></div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Durée totale</div>
                    </div>
                </div>
                
                <!-- Search -->
                <div class="max-w-2xl mx-auto">
                    <div class="relative">
                        <input type="text" 
                               id="searchTutorials"
                               placeholder="Rechercher un tutoriel, une fonctionnalité..."
                               class="w-full pl-12 pr-4 py-4 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-2xl focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all duration-200 text-gray-900 dark:text-white">
                        <div class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-gray-500">
                            <i class="fas fa-search text-lg"></i>
                        </div>
                        <button id="clearSearch" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 hidden">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <main class="container mx-auto px-4 py-12">
        <!-- Categories Navigation -->
        <section class="mb-12">
            <div class="flex flex-wrap gap-3 justify-center mb-8">
                <button onclick="filterTutorials('all')" 
                        class="px-6 py-3 bg-green-600 text-white font-medium rounded-lg transition-colors active-category">
                    <i class="fas fa-th-large mr-2"></i>Tous
                </button>
                
                <?php foreach ($tutorial_categories as $key => $category): ?>
                <button onclick="filterTutorials('<?php echo $key; ?>')" 
                        class="px-6 py-3 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 font-medium rounded-lg border border-gray-300 dark:border-slate-600 hover:border-green-500 hover:text-green-600 dark:hover:text-green-400 transition-colors category-filter"
                        data-category="<?php echo $key; ?>">
                    <i class="<?php echo $category['icon']; ?> mr-2"></i>
                    <?php echo $category['title']; ?>
                </button>
                <?php endforeach; ?>
            </div>
        </section>
        
        <!-- Tutorials Grid -->
        <section id="tutorialsContainer">
            <?php foreach ($tutorial_categories as $category_key => $category): ?>
            <div class="mb-16 tutorial-category" data-category="<?php echo $category_key; ?>">
                <!-- Category Header -->
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br <?php echo $category['color']; ?> flex items-center justify-center text-white text-2xl">
                            <i class="<?php echo $category['icon']; ?>"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                                <?php echo $category['title']; ?>
                            </h2>
                            <p class="text-gray-600 dark:text-gray-400">
                                <?php echo $category['description']; ?>
                            </p>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        <?php echo count($category['tutorials']); ?> tutoriels
                    </div>
                </div>
                
                <!-- Tutorials List -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <?php foreach ($category['tutorials'] as $tutorial): ?>
                    <div class="tutorial-card bg-white dark:bg-slate-800 rounded-2xl shadow-lg overflow-hidden border border-gray-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300 animate-fadeIn"
                         data-tags="<?php echo $tutorial['title'] . ' ' . $category['title'] . ' ' . $tutorial['level']; ?>">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <?php if ($tutorial['type'] === 'video'): ?>
                                        <span class="px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 text-xs font-medium rounded-full flex items-center gap-1">
                                            <i class="fas fa-play"></i> Vidéo
                                        </span>
                                        <?php else: ?>
                                        <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-xs font-medium rounded-full flex items-center gap-1">
                                            <i class="fas fa-file-alt"></i> Texte
                                        </span>
                                        <?php endif; ?>
                                        
                                        <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-medium rounded-full flex items-center gap-1">
                                            <i class="fas fa-clock"></i> <?php echo $tutorial['duration']; ?>
                                        </span>
                                        
                                        <?php if ($tutorial['level'] === 'beginner'): ?>
                                        <span class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs font-medium rounded-full">
                                            Débutant
                                        </span>
                                        <?php else: ?>
                                        <span class="px-3 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 text-xs font-medium rounded-full">
                                            Intermédiaire
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                                        <?php echo $tutorial['title']; ?>
                                    </h3>
                                    
                                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">
                                        Apprenez étape par étape comment <?php echo strtolower($tutorial['title']); ?> sur <?php echo $site_name; ?>.
                                    </p>
                                </div>
                                
                                <button onclick="toggleTutorial('<?php echo $tutorial['id']; ?>')"
                                        class="flex-shrink-0 w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 hover:bg-green-200 dark:hover:bg-green-800/50 flex items-center justify-center transition-colors ml-4">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>
                            
                            <!-- Tutorial Content (Hidden by default) -->
                            <div id="content-<?php echo $tutorial['id']; ?>" class="tutorial-content hidden mt-6 border-t border-gray-200 dark:border-slate-700 pt-6">
                                <!-- Video Player or Text Content -->
                                <?php if ($tutorial['type'] === 'video' && isset($tutorial['youtube_id'])): ?>
                                <div class="mb-6">
                                    <div class="video-container">
                                        <div id="player-<?php echo $tutorial['id']; ?>"></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <!-- Text Content -->
                                <div class="prose dark:prose-invert max-w-none">
                                    <?php echo $tutorial['text_content']; ?>
                                </div>
                                
                                <!-- Tips -->
                                <?php if (!empty($tutorial['tips'])): ?>
                                <div class="mt-8">
                                    <h4 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                                        <i class="fas fa-lightbulb text-yellow-500 mr-3"></i>
                                        Conseils pratiques
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <?php foreach ($tutorial['tips'] as $index => $tip): ?>
                                        <div class="flex items-start p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center mr-3 mt-1">
                                                <span class="text-green-600 dark:text-green-400 text-xs font-bold"><?php echo $index + 1; ?></span>
                                            </span>
                                            <span class="text-gray-700 dark:text-gray-300 text-sm"><?php echo $tip; ?></span>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <!-- Actions -->
                                <div class="mt-8 pt-6 border-t border-gray-200 dark:border-slate-700 flex flex-wrap gap-3">
                                    <?php if ($tutorial['type'] === 'video'): ?>
                                    <button onclick="playVideo('<?php echo $tutorial['id']; ?>')"
                                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors flex items-center gap-2">
                                        <i class="fas fa-play"></i>
                                        Lire la vidéo
                                    </button>
                                    <?php endif; ?>
                                    
                                    <button onclick="markAsCompleted('<?php echo $tutorial['id']; ?>')"
                                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors flex items-center gap-2">
                                        <i class="fas fa-check"></i>
                                        Marquer comme terminé
                                    </button>
                                    
                                    <button onclick="shareTutorial('<?php echo $tutorial['id']; ?>', '<?php echo $tutorial['title']; ?>')"
                                            class="px-4 py-2 border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 font-medium rounded-lg transition-colors flex items-center gap-2">
                                        <i class="fas fa-share"></i>
                                        Partager
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </section>
        
        <!-- No Results -->
        <div id="noResults" class="hidden text-center py-16">
            <div class="max-w-md mx-auto">
                <div class="w-24 h-24 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-search text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">
                    Aucun résultat trouvé
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-8">
                    Essayez avec d'autres mots-clés ou parcourez toutes les catégories.
                </p>
                <button onclick="clearSearch()"
                        class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                    Voir tous les tutoriels
                </button>
            </div>
        </div>
        
        <!-- Progress -->
        <section class="mt-16">
            <div class="max-w-4xl mx-auto">
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl p-8 border border-green-200 dark:border-green-800">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">
                                Votre progression d'apprentissage
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                Suivez votre avancement dans la maîtrise de <?php echo $site_name; ?>
                            </p>
                            
                            <!-- Progress Bar -->
                            <div class="mt-6">
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="text-gray-700 dark:text-gray-300">Progression globale</span>
                                    <span class="font-bold text-green-600 dark:text-green-400" id="progressPercent">0%</span>
                                </div>
                                <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div id="progressBar" class="h-full bg-green-500 rounded-full transition-all duration-500" style="width: 0%"></div>
                                </div>
                                <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    <span id="completedCount">0</span> sur <?php echo $tutorial_stats['total_tutorials']; ?> tutoriels complétés
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex-shrink-0">
                            <div class="relative w-40 h-40">
                                <!-- Circular Progress -->
                                <svg class="w-full h-full" viewBox="0 0 100 100">
                                    <!-- Background circle -->
                                    <circle cx="50" cy="50" r="45" fill="none" stroke="#e5e7eb" stroke-width="8" stroke-linecap="round"/>
                                    <!-- Progress circle -->
                                    <circle id="progressCircle" cx="50" cy="50" r="45" fill="none" stroke="#10b981" stroke-width="8" stroke-linecap="round"
                                            stroke-dasharray="283" stroke-dashoffset="283" transform="rotate(-90 50 50)"/>
                                </svg>
                                <!-- Center text -->
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="text-center">
                                        <div id="circlePercent" class="text-3xl font-bold text-green-600 dark:text-green-400">0%</div>
                                        <div class="text-xs text-gray-600 dark:text-gray-400">Terminé</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Certificate -->
                    <div class="mt-8 pt-8 border-t border-green-200 dark:border-green-800">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-yellow-500 to-orange-500 flex items-center justify-center">
                                    <i class="fas fa-award text-white text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-white">Certificat de maîtrise</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Obtenez votre certificat après avoir complété tous les tutoriels
                                    </p>
                                </div>
                            </div>
                            <button onclick="downloadCertificate()"
                                    class="px-6 py-3 bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white font-medium rounded-lg transition-colors flex items-center gap-2">
                                <i class="fas fa-download"></i>
                                Télécharger le certificat
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- FAQ -->
        <section class="mt-16">
            <div class="max-w-4xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                        <i class="fas fa-question-circle mr-3 text-green-600"></i>
                        Questions sur les tutoriels
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400">
                        Réponses aux questions fréquentes sur l'utilisation de nos tutoriels
                    </p>
                </div>
                
                <div class="space-y-4">
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
                        <button onclick="toggleFAQ(this)"
                                class="w-full flex items-center justify-between gap-4 group">
                            <span class="text-left font-medium text-gray-900 dark:text-white group-hover:text-green-600 dark:group-hover:text-green-400">
                                Les tutoriels sont-ils gratuits ?
                            </span>
                            <i class="fas fa-chevron-down text-gray-400 group-hover:text-green-600 transition-transform duration-300"></i>
                        </button>
                        <div class="mt-4 text-gray-600 dark:text-gray-400 hidden">
                            Oui, tous nos tutoriels sont entièrement gratuits. Nous croyons que chaque investisseur devrait avoir accès à une formation complète pour maximiser ses chances de succès.
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
                        <button onclick="toggleFAQ(this)"
                                class="w-full flex items-center justify-between gap-4 group">
                            <span class="text-left font-medium text-gray-900 dark:text-white group-hover:text-green-600 dark:group-hover:text-green-400">
                                Puis-je télécharger les vidéos ?
                            </span>
                            <i class="fas fa-chevron-down text-gray-400 group-hover:text-green-600 transition-transform duration-300"></i>
                        </button>
                        <div class="mt-4 text-gray-600 dark:text-gray-400 hidden">
                            Pour des raisons techniques et de droits d'auteur, les vidéos ne sont pas téléchargeables. Cependant, vous pouvez les regarder autant de fois que vous le souhaitez en ligne.
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-gray-200 dark:border-slate-700">
                        <button onclick="toggleFAQ(this)"
                                class="w-full flex items-center justify-between gap-4 group">
                            <span class="text-left font-medium text-gray-900 dark:text-white group-hover:text-green-600 dark:group-hover:text-green-400">
                                Comment suivre ma progression ?
                            </span>
                            <i class="fas fa-chevron-down text-gray-400 group-hover:text-green-600 transition-transform duration-300"></i>
                        </button>
                        <div class="mt-4 text-gray-600 dark:text-gray-400 hidden">
                            Votre progression est automatiquement enregistrée lorsque vous marquez un tutoriel comme "terminé". Vous pouvez voir votre avancement dans la section "Progression" en bas de la page.
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white mt-16">
        <div class="container mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Quick Links -->
                <div>
                    <h4 class="text-lg font-bold mb-6">Navigation</h4>
                    <ul class="space-y-3">
                        <li><a href="index.php?page=home" class="text-gray-400 hover:text-green-400 transition-colors"><i class="fas fa-chevron-right mr-2 text-xs"></i> Accueil</a></li>
                        <li><a href="index.php?page=tuto" class="text-gray-400 hover:text-green-400 transition-colors"><i class="fas fa-chevron-right mr-2 text-xs"></i> Tutoriels</a></li>
                        <li><a href="index.php?page=support" class="text-gray-400 hover:text-green-400 transition-colors"><i class="fas fa-chevron-right mr-2 text-xs"></i> Support</a></li>
                        <li><a href="index.php?page=support" class="text-gray-400 hover:text-green-400 transition-colors"><i class="fas fa-chevron-right mr-2 text-xs"></i> FAQ</a></li>
                    </ul>
                </div>
                
                <!-- Tutorial Categories -->
                <div>
                    <h4 class="text-lg font-bold mb-6">Catégories</h4>
                    <ul class="space-y-3">
                        <?php foreach ($tutorial_categories as $key => $category): ?>
                        <li>
                            <a href="tutorials.php#<?php echo $key; ?>" class="text-gray-400 hover:text-green-400 transition-colors flex items-center">
                                <i class="<?php echo $category['icon']; ?> mr-3 text-green-500"></i>
                                <?php echo $category['title']; ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- Contact -->
                <div>
                    <h4 class="text-lg font-bold mb-6">Aide & Support</h4>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3 text-gray-400">
                            <i class="fas fa-headset text-green-400"></i>
                            <span>Support 24/7</span>
                        </li>
                        <li class="flex items-center gap-3 text-gray-400">
                            <i class="fas fa-envelope text-green-400"></i>
                            <span>junior1009f@gmail.com</span>
                        </li>
                        <li class="flex items-center gap-3 text-gray-400">
                            <i class="fab fa-whatsapp text-green-400"></i>
                            <span>+237 6XX XX XX XX</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Newsletter -->
                <div>
                    <h4 class="text-lg font-bold mb-6">Nouveaux tutoriels</h4>
                    <p class="text-gray-400 text-sm mb-4">
                        Soyez notifié lorsque de nouveaux tutoriels sont ajoutés.
                    </p>
                    <div class="flex">
                        <input type="email" 
                               placeholder="Votre email"
                               class="flex-1 px-4 py-2 bg-gray-800 border border-gray-700 rounded-l-lg focus:outline-none focus:border-green-500">
                        <button class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-r-lg transition-colors">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400 text-sm">
                <p>&copy; <?php echo date('Y'); ?> <?php echo $site_name; ?>. Tous droits réservés.</p>
                <p class="mt-2">Les tutoriels sont régulièrement mis à jour pour refléter les dernières fonctionnalités.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
    // YouTube Players
    let youtubePlayers = {};
    
    // Completed tutorials
    let completedTutorials = JSON.parse(localStorage.getItem('completedTutorials') || '[]');
    const totalTutorials = <?php echo $tutorial_stats['total_tutorials']; ?>;
    
    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize YouTube players
        <?php foreach ($tutorial_categories as $category): ?>
            <?php foreach ($category['tutorials'] as $tutorial): ?>
                <?php if ($tutorial['type'] === 'video' && isset($tutorial['youtube_id'])): ?>
                    initYouTubePlayer('<?php echo $tutorial['id']; ?>', '<?php echo $tutorial['youtube_id']; ?>');
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
        
        // Load progress
        updateProgress();
        
        // Mark already completed tutorials
        completedTutorials.forEach(id => {
            const btn = document.querySelector(`[onclick*="${id}"]`);
            if (btn) {
                btn.innerHTML = '<i class="fas fa-check-double"></i> Terminé';
                btn.classList.remove('bg-green-600', 'hover:bg-green-700');
                btn.classList.add('bg-gray-600', 'hover:bg-gray-700', 'cursor-default');
                btn.setAttribute('onclick', '');
            }
        });
        
        // Initialize search
        initSearch();
    });
    
    // YouTube Player initialization
    function initYouTubePlayer(playerId, videoId) {
        // This function will be called by YouTube API
        window.onYouTubeIframeAPIReady = function() {
            youtubePlayers[playerId] = new YT.Player(`player-${playerId}`, {
                height: '360',
                width: '640',
                videoId: videoId,
                playerVars: {
                    'playsinline': 1,
                    'rel': 0,
                    'modestbranding': 1
                },
                events: {
                    'onReady': onPlayerReady,
                    'onStateChange': onPlayerStateChange
                }
            });
        };
    }
    
    function onPlayerReady(event) {
        // Player is ready
    }
    
    function onPlayerStateChange(event) {
        // Track when video ends
        if (event.data === YT.PlayerState.ENDED) {
            const playerId = Object.keys(youtubePlayers).find(key => 
                youtubePlayers[key] === event.target
            );
            if (playerId) {
                markAsCompleted(playerId);
            }
        }
    }
    
    // Toggle tutorial content
    function toggleTutorial(tutorialId) {
        const content = document.getElementById(`content-${tutorialId}`);
        const button = document.querySelector(`[onclick*="${tutorialId}"]`);
        
        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            button.innerHTML = '<i class="fas fa-chevron-up"></i>';
            content.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            content.classList.add('hidden');
            button.innerHTML = '<i class="fas fa-chevron-down"></i>';
        }
    }
    
    // Play video
    function playVideo(tutorialId) {
        if (youtubePlayers[tutorialId]) {
            youtubePlayers[tutorialId].playVideo();
            // Scroll to video
            document.getElementById(`content-${tutorialId}`).scrollIntoView({ behavior: 'smooth' });
        }
    }
    
    // Mark as completed
    function markAsCompleted(tutorialId) {
        if (!completedTutorials.includes(tutorialId)) {
            completedTutorials.push(tutorialId);
            localStorage.setItem('completedTutorials', JSON.stringify(completedTutorials));
            
            // Update button
            const btn = document.querySelector(`[onclick*="markAsCompleted('${tutorialId}')"]`);
            if (btn) {
                btn.innerHTML = '<i class="fas fa-check-double"></i> Terminé';
                btn.classList.remove('bg-green-600', 'hover:bg-green-700');
                btn.classList.add('bg-gray-600', 'hover:bg-gray-700', 'cursor-default');
                btn.setAttribute('onclick', '');
                
                // Show success message
                showToast('Tutoriel marqué comme terminé !', 'success');
            }
            
            // Update progress
            updateProgress();
        }
    }
    
    // Update progress display
    function updateProgress() {
        const completedCount = completedTutorials.length;
        const progressPercent = totalTutorials > 0 ? Math.round((completedCount / totalTutorials) * 100) : 0;
        
        // Update progress bar
        document.getElementById('progressBar').style.width = `${progressPercent}%`;
        document.getElementById('progressPercent').textContent = `${progressPercent}%`;
        document.getElementById('completedCount').textContent = completedCount;
        
        // Update circle progress
        const circle = document.getElementById('progressCircle');
        const circumference = 2 * Math.PI * 45;
        const offset = circumference - (progressPercent / 100) * circumference;
        circle.style.strokeDashoffset = offset;
        document.getElementById('circlePercent').textContent = `${progressPercent}%`;
    }
    
    // Filter tutorials by category
    function filterTutorials(category) {
        const allCategories = document.querySelectorAll('.tutorial-category');
        const filterButtons = document.querySelectorAll('.category-filter, .active-category');
        
        // Update active button
        filterButtons.forEach(btn => {
            btn.classList.remove('active-category');
            btn.classList.add('category-filter');
            if (category === 'all' && btn.textContent.includes('Tous')) {
                btn.classList.add('active-category');
                btn.classList.remove('category-filter');
            } else if (btn.dataset.category === category) {
                btn.classList.add('active-category');
                btn.classList.remove('category-filter');
            }
        });
        
        // Show/hide categories
        allCategories.forEach(cat => {
            if (category === 'all' || cat.dataset.category === category) {
                cat.classList.remove('hidden');
                cat.style.animation = 'none';
                setTimeout(() => {
                    cat.style.animation = 'fadeIn 0.6s ease-out';
                }, 10);
            } else {
                cat.classList.add('hidden');
            }
        });
        
        // Hide no results message
        document.getElementById('noResults').classList.add('hidden');
    }
    
    // Search functionality
    function initSearch() {
        const searchInput = document.getElementById('searchTutorials');
        const clearButton = document.getElementById('clearSearch');
        const tutorialCards = document.querySelectorAll('.tutorial-card');
        
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            let hasResults = false;
            
            if (searchTerm.length > 0) {
                clearButton.classList.remove('hidden');
                
                // Search in tutorial cards
                tutorialCards.forEach(card => {
                    const tags = card.dataset.tags.toLowerCase();
                    const title = card.querySelector('h3').textContent.toLowerCase();
                    const content = card.querySelector('p').textContent.toLowerCase();
                    
                    if (tags.includes(searchTerm) || title.includes(searchTerm) || content.includes(searchTerm)) {
                        card.classList.remove('hidden');
                        hasResults = true;
                    } else {
                        card.classList.add('hidden');
                    }
                });
                
                // Show/hide no results message
                if (hasResults) {
                    document.getElementById('noResults').classList.add('hidden');
                } else {
                    document.getElementById('noResults').classList.remove('hidden');
                }
            } else {
                clearButton.classList.add('hidden');
                tutorialCards.forEach(card => card.classList.remove('hidden'));
                document.getElementById('noResults').classList.add('hidden');
            }
        });
        
        clearButton.addEventListener('click', function() {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
            searchInput.focus();
        });
    }
    
    // Clear search
    function clearSearch() {
        document.getElementById('searchTutorials').value = '';
        document.getElementById('searchTutorials').dispatchEvent(new Event('input'));
        filterTutorials('all');
    }
    
    // Share tutorial
    function shareTutorial(tutorialId, tutorialTitle) {
        const url = window.location.href.split('#')[0] + `#${tutorialId}`;
        const text = `Découvrez ce tutoriel sur ${tutorialTitle} - ${url}`;
        
        if (navigator.share) {
            navigator.share({
                title: tutorialTitle,
                text: `Apprenez à ${tutorialTitle.toLowerCase()} sur <?php echo $site_name; ?>`,
                url: url
            });
        } else {
            navigator.clipboard.writeText(text).then(() => {
                showToast('Lien copié dans le presse-papier !', 'success');
            });
        }
    }
    
    // Copy referral code
    function copyReferralCode() {
        const code = '<?php echo isset($_SESSION['user_referral_code']) ? $_SESSION['user_referral_code'] : 'IZYAB1234'; ?>';
        navigator.clipboard.writeText(code).then(() => {
            showToast('Code copié !', 'success');
        });
    }
    
    // Share referral
    function shareReferral() {
        const url = '<?php echo $site_url; ?>/?ref=<?php echo isset($_SESSION['user_referral_code']) ? $_SESSION['user_referral_code'] : 'IZYAB1234'; ?>';
        const text = `Rejoins-moi sur <?php echo $site_name; ?> et commence à investir ! Utilise mon code: <?php echo isset($_SESSION['user_referral_code']) ? $_SESSION['user_referral_code'] : 'IZYAB1234'; ?> - ${url}`;
        
        if (navigator.share) {
            navigator.share({
                title: 'Rejoins <?php echo $site_name; ?>',
                text: text,
                url: url
            });
        } else {
            navigator.clipboard.writeText(text).then(() => {
                showToast('Message copié ! Partagez-le avec vos amis.', 'success');
            });
        }
    }
    
    // Download certificate
    function downloadCertificate() {
        const completedCount = completedTutorials.length;
        if (completedCount === totalTutorials) {
            // Generate and download certificate
            showToast('Téléchargement du certificat...', 'info');
            // In a real implementation, this would generate a PDF certificate
        } else {
            showToast(`Complétez tous les tutoriels (${completedCount}/${totalTutorials}) pour obtenir votre certificat.`, 'info');
        }
    }
    
    // Toggle FAQ
    function toggleFAQ(button) {
        const answer = button.nextElementSibling;
        const icon = button.querySelector('i');
        
        if (answer.classList.contains('hidden')) {
            answer.classList.remove('hidden');
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
        } else {
            answer.classList.add('hidden');
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
        }
    }
    
    // Show toast notification
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            info: 'bg-blue-500'
        };
        
        toast.className = `fixed bottom-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg font-medium z-50 animate-fadeIn`;
        toast.innerHTML = `
            <div class="flex items-center gap-3">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('opacity-0', 'transition-opacity', 'duration-300');
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }
    
    // Mobile menu
    document.getElementById('mobileMenuButton')?.addEventListener('click', function() {
        // Implement mobile menu
        alert('Menu mobile - À implémenter');
    });
    
    // Load YouTube API if not already loaded
    if (!window.YT) {
        const tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        const firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
    }
    </script>
</body>
</html>