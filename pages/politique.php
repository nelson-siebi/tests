<?php
// pages/politique-confidentialite.php

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Politique de Confidentialité - Investian</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Style personnalisé */
        .policy-nav {
            position: sticky;
            top: 20px;
        }
        
        .policy-section {
            scroll-margin-top: 100px;
        }
        
        .highlight {
            background: linear-gradient(120deg, rgba(34, 197, 94, 0.1) 0%, rgba(34, 197, 94, 0.05) 100%);
            border-left: 4px solid #10b981;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .nav-link.active {
            background-color: #f0fdf4;
            border-left: 4px solid #10b981;
            color: #065f46;
            font-weight: 600;
        }
        
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="index.php" class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-chart-line text-white"></i>
                    </div>
                    <span class="text-xl font-bold text-gray-800">Investian</span>
                </a>
                <div class="flex items-center space-x-4">
                    <a href="home" class="text-gray-600 hover:text-green-600 font-medium">
                        <i class="fas fa-home mr-1"></i> Accueil
                    </a>
                    <a href="?page=login" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium">
                        <i class="fas fa-sign-in-alt mr-1"></i> Connexion
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        <!-- En-tête de la politique -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-800 mb-4">Politique de Confidentialité</h1>
            <p class="text-gray-600 text-lg max-w-3xl mx-auto">
                Dernière mise à jour : <span class="font-bold"><?php echo date('d/m/Y'); ?></span>
            </p>
            <div class="flex items-center justify-center mt-4 space-x-4">
                <span class="text-sm text-gray-500">
                    <i class="far fa-clock mr-1"></i> Temps de lecture : 10 min
                </span>
                <span class="text-sm text-gray-500">
                    <i class="fas fa-shield-alt mr-1"></i> Conforme RGPD
                </span>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Navigation latérale -->
            <div class="lg:w-1/4">
                <div class="bg-white rounded-xl shadow-sm p-6 policy-nav">
                    <h3 class="font-bold text-gray-800 mb-4">Sommaire</h3>
                    <nav class="space-y-2">
                        <a href="#introduction" class="block py-2 px-4 text-gray-600 hover:text-green-600 hover:bg-green-50 rounded-lg transition nav-link">
                            <i class="fas fa-book-open mr-2"></i> Introduction
                        </a>
                        <a href="#collecte" class="block py-2 px-4 text-gray-600 hover:text-green-600 hover:bg-green-50 rounded-lg transition nav-link">
                            <i class="fas fa-database mr-2"></i> Données collectées
                        </a>
                        <a href="#utilisation" class="block py-2 px-4 text-gray-600 hover:text-green-600 hover:bg-green-50 rounded-lg transition nav-link">
                            <i class="fas fa-cogs mr-2"></i> Utilisation des données
                        </a>
                        <a href="#protection" class="block py-2 px-4 text-gray-600 hover:text-green-600 hover:bg-green-50 rounded-lg transition nav-link">
                            <i class="fas fa-shield-alt mr-2"></i> Protection
                        </a>
                        <a href="#droits" class="block py-2 px-4 text-gray-600 hover:text-green-600 hover:bg-green-50 rounded-lg transition nav-link">
                            <i class="fas fa-user-check mr-2"></i> Vos droits
                        </a>
                        <a href="#cookies" class="block py-2 px-4 text-gray-600 hover:text-green-600 hover:bg-green-50 rounded-lg transition nav-link">
                            <i class="fas fa-cookie-bite mr-2"></i> Cookies
                        </a>
                        <a href="#contact" class="block py-2 px-4 text-gray-600 hover:text-green-600 hover:bg-green-50 rounded-lg transition nav-link">
                            <i class="fas fa-envelope mr-2"></i> Contact
                        </a>
                    </nav>
                    
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <i class="fas fa-info-circle text-green-600 text-lg mb-2"></i>
                            <p class="text-sm text-gray-700">
                                Cette politique est conforme au <strong>Règlement Général sur la Protection des Données (RGPD)</strong> et aux lois camerounaises sur la protection des données.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenu principal -->
            <div class="lg:w-3/4">
                <div class="bg-white rounded-xl shadow-sm p-8">
                    <!-- Introduction -->
                    <section id="introduction" class="policy-section mb-12">
                        <div class="flex items-start mb-6">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-book-open text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800 mb-2">1. Introduction</h2>
                                <p class="text-gray-600">
                                    Chez <strong>Investian</strong>, la protection de vos données personnelles est notre priorité. Cette politique de confidentialité explique comment nous collectons, utilisons, protégeons et partageons vos informations lorsque vous utilisez notre plateforme d'investissement.
                                </p>
                            </div>
                        </div>
                        
                        <div class="highlight">
                            <h3 class="font-bold text-gray-800 mb-2">
                                <i class="fas fa-exclamation-circle text-green-600 mr-2"></i>
                                Important
                            </h3>
                            <p class="text-gray-700">
                                En utilisant Investian, vous acceptez les termes de cette politique de confidentialité. Si vous n'êtes pas d'accord avec ces termes, veuillez ne pas utiliser nos services.
                            </p>
                        </div>
                        
                        <div class="mt-6">
                            <h3 class="font-bold text-gray-800 mb-3">Responsable du traitement</h3>
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <p class="text-gray-700">
                                    <strong>Nom :</strong> Investian<br>
                                    <strong>Adresse :</strong> Yaoundé, Cameroun<br>
                                    <strong>Email :</strong> junior1009f@gmail.com<br>
                                    <strong>Téléphone :</strong> +237 656 720 564
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Données collectées -->
                    <section id="collecte" class="policy-section mb-12">
                        <div class="flex items-start mb-6">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-database text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800 mb-2">2. Données que nous collectons</h2>
                                <p class="text-gray-600">
                                    Nous collectons différents types de données pour fournir et améliorer nos services :
                                </p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center mb-3">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-user text-green-600"></i>
                                    </div>
                                    <h3 class="font-bold text-gray-800">Données personnelles</h3>
                                </div>
                                <ul class="space-y-2 text-sm text-gray-600">
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                                        <span>Nom, prénom, email, téléphone</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                                        <span>Date de naissance, genre</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                                        <span>Adresse, ville, pays</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                                        <span>Coordonnées bancaires (pour retraits)</span>
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center mb-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-chart-line text-blue-600"></i>
                                    </div>
                                    <h3 class="font-bold text-gray-800">Données financières</h3>
                                </div>
                                <ul class="space-y-2 text-sm text-gray-600">
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-blue-500 mt-1 mr-2"></i>
                                        <span>Historique des investissements</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-blue-500 mt-1 mr-2"></i>
                                        <span>Transactions (dépôts, retraits)</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-blue-500 mt-1 mr-2"></i>
                                        <span>Soldes des comptes</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-blue-500 mt-1 mr-2"></i>
                                        <span>Gains et commissions</span>
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center mb-3">
                                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-laptop text-purple-600"></i>
                                    </div>
                                    <h3 class="font-bold text-gray-800">Données techniques</h3>
                                </div>
                                <ul class="space-y-2 text-sm text-gray-600">
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-purple-500 mt-1 mr-2"></i>
                                        <span>Adresse IP, type de navigateur</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-purple-500 mt-1 mr-2"></i>
                                        <span>Appareil utilisé, système d'exploitation</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-purple-500 mt-1 mr-2"></i>
                                        <span>Pages visitées, temps de connexion</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-purple-500 mt-1 mr-2"></i>
                                        <span>Localisation (pays, ville)</span>
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center mb-3">
                                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-camera text-yellow-600"></i>
                                    </div>
                                    <h3 class="font-bold text-gray-800">Documents KYC</h3>
                                </div>
                                <ul class="space-y-2 text-sm text-gray-600">
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-yellow-500 mt-1 mr-2"></i>
                                        <span>Copie de carte d'identité/passeport</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-yellow-500 mt-1 mr-2"></i>
                                        <span>Justificatif de domicile</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-yellow-500 mt-1 mr-2"></i>
                                        <span>Selfie avec pièce d'identité</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <h3 class="font-bold text-yellow-800 mb-2">
                                <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                                Données sensibles
                            </h3>
                            <p class="text-yellow-700">
                                Nous ne collectons <strong>jamais</strong> de données sensibles telles que l'origine raciale, les opinions politiques, les croyances religieuses, les données de santé ou l'orientation sexuelle.
                            </p>
                        </div>
                    </section>

                    <!-- Utilisation des données -->
                    <section id="utilisation" class="policy-section mb-12">
                        <div class="flex items-start mb-6">
                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-cogs text-purple-600 text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800 mb-2">3. Utilisation de vos données</h2>
                                <p class="text-gray-600">
                                    Nous utilisons vos données uniquement dans les buts légitimes suivants :
                                </p>
                            </div>
                        </div>
                        
                        <div class="space-y-6">
                            <div class="border-l-4 border-green-500 pl-4">
                                <h3 class="font-bold text-gray-800 mb-2">Fourniture des services</h3>
                                <ul class="space-y-2 text-gray-600">
                                    <li>Gestion de votre compte utilisateur</li>
                                    <li>Exécution des transactions financières</li>
                                    <li>Suivi de vos investissements et gains</li>
                                    <li>Validation KYC et lutte contre la fraude</li>
                                </ul>
                            </div>
                            
                            <div class="border-l-4 border-blue-500 pl-4">
                                <h3 class="font-bold text-gray-800 mb-2">Amélioration du service</h3>
                                <ul class="space-y-2 text-gray-600">
                                    <li>Analyse des performances de la plateforme</li>
                                    <li>Développement de nouvelles fonctionnalités</li>
                                    <li>Tests et débogage techniques</li>
                                    <li>Personnalisation de l'expérience utilisateur</li>
                                </ul>
                            </div>
                            
                            <div class="border-l-4 border-purple-500 pl-4">
                                <h3 class="font-bold text-gray-800 mb-2">Communication</h3>
                                <ul class="space-y-2 text-gray-600">
                                    <li>Envoi de confirmations de transaction</li>
                                    <li>Notifications sur vos investissements</li>
                                    <li>Informations sur les mises à jour</li>
                                    <li>Support client et assistance</li>
                                </ul>
                            </div>
                            
                            <div class="border-l-4 border-red-500 pl-4">
                                <h3 class="font-bold text-gray-800 mb-2">Sécurité et conformité</h3>
                                <ul class="space-y-2 text-gray-600">
                                    <li>Prévention de la fraude et du blanchiment</li>
                                    <li>Respect des obligations légales</li>
                                    <li>Archivage des transactions financières</li>
                                    <li>Audits et contrôles de sécurité</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="highlight mt-6">
                            <h3 class="font-bold text-gray-800 mb-2">
                                <i class="fas fa-handshake text-green-600 mr-2"></i>
                                Base légale du traitement
                            </h3>
                            <p class="text-gray-700">
                                Le traitement de vos données repose sur : votre <strong>consentement</strong>, l'<strong>exécution d'un contrat</strong> (nos conditions d'utilisation), nos <strong>intérêts légitimes</strong> et nos <strong>obligations légales</strong> (lois anti-blanchiment).
                            </p>
                        </div>
                    </section>

                    <!-- Protection des données -->
                    <section id="protection" class="policy-section mb-12">
                        <div class="flex items-start mb-6">
                            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-shield-alt text-red-600 text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800 mb-2">4. Protection de vos données</h2>
                                <p class="text-gray-600">
                                    Nous mettons en œuvre des mesures de sécurité robustes pour protéger vos données :
                                </p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div class="text-center p-4">
                                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-lock text-green-600 text-2xl"></i>
                                </div>
                                <h3 class="font-bold text-gray-800 mb-2">Chiffrement</h3>
                                <p class="text-sm text-gray-600">
                                    TLS/SSL 256-bit pour toutes les transmissions de données
                                </p>
                            </div>
                            
                            <div class="text-center p-4">
                                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-server text-blue-600 text-2xl"></i>
                                </div>
                                <h3 class="font-bold text-gray-800 mb-2">Infrastructure sécurisée</h3>
                                <p class="text-sm text-gray-600">
                                    Serveurs hébergés chez des fournisseurs certifiés ISO 27001
                                </p>
                            </div>
                            
                            <div class="text-center p-4">
                                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-user-shield text-purple-600 text-2xl"></i>
                                </div>
                                <h3 class="font-bold text-gray-800 mb-2">Accès restreint</h3>
                                <p class="text-sm text-gray-600">
                                    Accès aux données limité au personnel autorisé
                                </p>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                            <h3 class="font-bold text-gray-800 mb-4">Mesures techniques et organisationnelles</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex items-start">
                                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3 mt-1">
                                        <i class="fas fa-check text-green-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-800">Authentification à deux facteurs</h4>
                                        <p class="text-sm text-gray-600">Pour toutes les opérations sensibles</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3 mt-1">
                                        <i class="fas fa-check text-green-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-800">Surveillance 24/7</h4>
                                        <p class="text-sm text-gray-600">Détection des activités suspectes</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3 mt-1">
                                        <i class="fas fa-check text-green-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-800">Sauvegardes régulières</h4>
                                        <p class="text-sm text-gray-600">Données sauvegardées quotidiennement</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3 mt-1">
                                        <i class="fas fa-check text-green-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-800">Formation du personnel</h4>
                                        <p class="text-sm text-gray-600">Sensibilisation à la protection des données</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <h3 class="font-bold text-gray-800 mb-3">Conservation des données</h3>
                            <div class="overflow-x-auto">
                                <table class="w-full border border-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="border border-gray-300 px-4 py-2 text-left">Type de données</th>
                                            <th class="border border-gray-300 px-4 py-2 text-left">Durée de conservation</th>
                                            <th class="border border-gray-300 px-4 py-2 text-left">Raison</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="border border-gray-300 px-4 py-2">Données du compte</td>
                                            <td class="border border-gray-300 px-4 py-2">5 ans après fermeture</td>
                                            <td class="border border-gray-300 px-4 py-2">Obligations légales</td>
                                        </tr>
                                        <tr class="bg-gray-50">
                                            <td class="border border-gray-300 px-4 py-2">Transactions financières</td>
                                            <td class="border border-gray-300 px-4 py-2">10 ans</td>
                                            <td class="border border-gray-300 px-4 py-2">Droit fiscal</td>
                                        </tr>
                                        <tr>
                                            <td class="border border-gray-300 px-4 py-2">Documents KYC</td>
                                            <td class="border border-gray-300 px-4 py-2">5 ans après fermeture</td>
                                            <td class="border border-gray-300 px-4 py-2">Lutte anti-blanchiment</td>
                                        </tr>
                                        <tr class="bg-gray-50">
                                            <td class="border border-gray-300 px-4 py-2">Logs de connexion</td>
                                            <td class="border border-gray-300 px-4 py-2">1 an</td>
                                            <td class="border border-gray-300 px-4 py-2">Sécurité</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <!-- Vos droits -->
                    <section id="droits" class="policy-section mb-12">
                        <div class="flex items-start mb-6">
                            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-user-check text-orange-600 text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800 mb-2">5. Vos droits</h2>
                                <p class="text-gray-600">
                                    Conformément au RGPD et aux lois applicables, vous disposez des droits suivants :
                                </p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="border border-green-200 rounded-lg p-4 bg-green-50">
                                <div class="flex items-center mb-2">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-eye text-green-600"></i>
                                    </div>
                                    <h3 class="font-bold text-gray-800">Droit d'accès</h3>
                                </div>
                                <p class="text-sm text-gray-600">
                                    Vous pouvez demander une copie de toutes les données que nous détenons sur vous.
                                </p>
                            </div>
                            
                            <div class="border border-blue-200 rounded-lg p-4 bg-blue-50">
                                <div class="flex items-center mb-2">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-edit text-blue-600"></i>
                                    </div>
                                    <h3 class="font-bold text-gray-800">Droit de rectification</h3>
                                </div>
                                <p class="text-sm text-gray-600">
                                    Vous pouvez corriger vos données si elles sont inexactes ou incomplètes.
                                </p>
                            </div>
                            
                            <div class="border border-red-200 rounded-lg p-4 bg-red-50">
                                <div class="flex items-center mb-2">
                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-trash-alt text-red-600"></i>
                                    </div>
                                    <h3 class="font-bold text-gray-800">Droit à l'effacement</h3>
                                </div>
                                <p class="text-sm text-gray-600">
                                    Vous pouvez demander la suppression de vos données, sous réserve d'obligations légales.
                                </p>
                            </div>
                            
                            <div class="border border-purple-200 rounded-lg p-4 bg-purple-50">
                                <div class="flex items-center mb-2">
                                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-ban text-purple-600"></i>
                                    </div>
                                    <h3 class="font-bold text-gray-800">Droit d'opposition</h3>
                                </div>
                                <p class="text-sm text-gray-600">
                                    Vous pouvez vous opposer au traitement de vos données pour le marketing direct.
                                </p>
                            </div>
                            
                            <div class="border border-yellow-200 rounded-lg p-4 bg-yellow-50">
                                <div class="flex items-center mb-2">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-file-export text-yellow-600"></i>
                                    </div>
                                    <h3 class="font-bold text-gray-800">Droit à la portabilité</h3>
                                </div>
                                <p class="text-sm text-gray-600">
                                    Vous pouvez recevoir vos données dans un format structuré et lisible par machine.
                                </p>
                            </div>
                            
                            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                <div class="flex items-center mb-2">
                                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-pause text-gray-600"></i>
                                    </div>
                                    <h3 class="font-bold text-gray-800">Droit à la limitation</h3>
                                </div>
                                <p class="text-sm text-gray-600">
                                    Vous pouvez demander la limitation du traitement dans certaines circonstances.
                                </p>
                            </div>
                        </div>
                        
                        <div class="highlight">
                            <h3 class="font-bold text-gray-800 mb-2">
                                <i class="fas fa-exclamation-circle text-green-600 mr-2"></i>
                                Comment exercer vos droits ?
                            </h3>
                            <p class="text-gray-700">
                                Pour exercer vos droits, contactez notre délégué à la protection des données (DPO) à l'adresse <strong>junior1009f@gmail.com</strong>. Nous répondrons à votre demande dans un délai maximum de <strong>30 jours</strong>.
                            </p>
                        </div>
                    </section>

                    <!-- Cookies -->
                    <section id="cookies" class="policy-section mb-12">
                        <div class="flex items-start mb-6">
                            <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-cookie-bite text-amber-600 text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800 mb-2">6. Cookies et technologies similaires</h2>
                                <p class="text-gray-600">
                                    Nous utilisons des cookies pour améliorer votre expérience sur notre plateforme :
                                </p>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto mb-6">
                            <table class="w-full border border-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="border border-gray-300 px-4 py-2 text-left">Type de cookie</th>
                                        <th class="border border-gray-300 px-4 py-2 text-left">But</th>
                                        <th class="border border-gray-300 px-4 py-2 text-left">Durée</th>
                                        <th class="border border-gray-300 px-4 py-2 text-left">Gestion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="border border-gray-300 px-4 py-2">
                                            <span class="font-medium">Cookies essentiels</span>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2">
                                            Connexion, sécurité, fonctionnalités de base
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2">Session</td>
                                        <td class="border border-gray-300 px-4 py-2">
                                            <span class="text-red-600 font-medium">Obligatoires</span>
                                        </td>
                                    </tr>
                                    <tr class="bg-gray-50">
                                        <td class="border border-gray-300 px-4 py-2">
                                            <span class="font-medium">Cookies de performance</span>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2">
                                            Analyse d'audience, amélioration du site
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2">13 mois</td>
                                        <td class="border border-gray-300 px-4 py-2">
                                            <span class="text-green-600 font-medium">Gérable</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border border-gray-300 px-4 py-2">
                                            <span class="font-medium">Cookies fonctionnels</span>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2">
                                            Mémorisation des préférences, personnalisation
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2">13 mois</td>
                                        <td class="border border-gray-300 px-4 py-2">
                                            <span class="text-green-600 font-medium">Gérable</span>
                                        </td>
                                    </tr>
                                    <tr class="bg-gray-50">
                                        <td class="border border-gray-300 px-4 py-2">
                                            <span class="font-medium">Cookies publicitaires</span>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2">
                                            Publicité ciblée, retargeting
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2">13 mois</td>
                                        <td class="border border-gray-300 px-4 py-2">
                                            <span class="text-green-600 font-medium">Gérable</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                            <h3 class="font-bold text-amber-800 mb-2">
                                <i class="fas fa-cog text-amber-600 mr-2"></i>
                                Gérer vos préférences cookies
                            </h3>
                            <p class="text-amber-700 mb-3">
                                Vous pouvez gérer vos préférences cookies à tout moment :
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <button onclick="acceptAllCookies()" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm">
                                    Accepter tous les cookies
                                </button>
                                <button onclick="rejectNonEssentialCookies()" class="px-4 py-2 bg-gray-600 text-white rounded-lg text-sm">
                                    Refuser les cookies non essentiels
                                </button>
                                <button onclick="openCookieSettings()" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">
                                    Personnaliser les préférences
                                </button>
                            </div>
                        </div>
                    </section>

                    <!-- Contact -->
                    <section id="contact" class="policy-section">
                        <div class="flex items-start mb-6">
                            <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-envelope text-indigo-600 text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800 mb-2">7. Contact et réclamations</h2>
                                <p class="text-gray-600">
                                    Pour toute question concernant cette politique ou pour exercer vos droits :
                                </p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                                <div class="flex items-center mb-4">
                                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                                        <i class="fas fa-user-tie text-green-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-800">Délégué à la Protection des Données (DPO)</h3>
                                        <p class="text-sm text-gray-600">Contact principal</p>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex items-start">
                                        <i class="fas fa-envelope text-gray-400 mt-1 mr-3"></i>
                                        <div>
                                            <p class="text-sm text-gray-500">Email</p>
                                            <p class="font-medium">junior1009f@gmail.com</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <i class="fas fa-phone text-gray-400 mt-1 mr-3"></i>
                                        <div>
                                            <p class="text-sm text-gray-500">Téléphone</p>
                                            <p class="font-medium">+237 XXX XXX XXX</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <i class="fas fa-clock text-gray-400 mt-1 mr-3"></i>
                                        <div>
                                            <p class="text-sm text-gray-500">Délai de réponse</p>
                                            <p class="font-medium">Sous 72 heures</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                                <div class="flex items-center mb-4">
                                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                        <i class="fas fa-headset text-blue-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-800">Support Client</h3>
                                        <p class="text-sm text-gray-600">Questions générales</p>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex items-start">
                                        <i class="fas fa-envelope text-gray-400 mt-1 mr-3"></i>
                                        <div>
                                            <p class="text-sm text-gray-500">Email</p>
                                            <p class="font-medium">junior1009f@gmail.com</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <i class="fas fa-comments text-gray-400 mt-1 mr-3"></i>
                                        <div>
                                            <p class="text-sm text-gray-500">Chat en direct</p>
                                            <p class="font-medium">Disponible 24/7</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <i class="fab fa-whatsapp text-gray-400 mt-1 mr-3"></i>
                                        <div>
                                            <p class="text-sm text-gray-500">WhatsApp</p>
                                            <p class="font-medium">+237 XXX XXX XXX</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="highlight">
                            <h3 class="font-bold text-gray-800 mb-2">
                                <i class="fas fa-balance-scale text-green-600 mr-2"></i>
                                Réclamation auprès de l'autorité
                            </h3>
                            <p class="text-gray-700">
                                Si vous estimez que vos droits n'ont pas été respectés, vous avez le droit d'introduire une réclamation auprès de l'autorité de protection des données compétente. Au Cameroun, il s'agit de l'<strong>Agence de Régulation des Télécommunications (ART)</strong>.
                            </p>
                        </div>
                    </section>

                    <!-- Pied de section -->
                    <div class="mt-12 pt-8 border-t border-gray-200">
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="font-bold text-gray-800 mb-3">Mise à jour de la politique</h3>
                            <p class="text-gray-600 mb-4">
                                Nous pouvons mettre à jour cette politique de confidentialité occasionnellement. Nous vous informerons de tout changement significatif en publiant la nouvelle politique sur notre site et, le cas échéant, en vous envoyant une notification.
                            </p>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fas fa-history mr-2"></i>
                                <span>Dernière mise à jour : <?php echo date('d/m/Y'); ?></span>
                                <span class="mx-2">•</span>
                                <span>Version : 2.0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <div class="flex items-center mb-2">
                        <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-chart-line text-white text-sm"></i>
                        </div>
                        <span class="text-xl font-bold">Investian</span>
                    </div>
                    <p class="text-gray-400 text-sm">Plateforme d'investissement sécurisée</p>
                </div>
                
                <div class="flex space-x-6">
                    <a href="?page=cgu" class="text-gray-400 hover:text-white text-sm">Conditions d'utilisation</a>
                    <a href="?page=confidentialite" class="text-green-400 font-medium text-sm">Politique de confidentialité</a>
                    <a href="?page=cookies" class="text-gray-400 hover:text-white text-sm">Politique des cookies</a>
                    <a href="?page=contact" class="text-gray-400 hover:text-white text-sm">Contact</a>
                </div>
            </div>
            
            <div class="mt-8 pt-8 border-t border-gray-700 text-center">
                <p class="text-gray-400 text-sm">
                    © <?php echo date('Y'); ?> Investian. Tous droits réservés. 
                    <span class="mx-2">•</span>
                    Conforme RGPD et lois camerounaises
                </p>
            </div>
        </div>
    </footer>

    <!-- Bouton retour en haut -->
    <button onclick="scrollToTop()" class="back-to-top hidden bg-green-600 hover:bg-green-700 text-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center transition-all">
        <i class="fas fa-chevron-up"></i>
    </button>

    <script>
    // Navigation active
    const sections = document.querySelectorAll('.policy-section');
    const navLinks = document.querySelectorAll('.nav-link');
    
    window.addEventListener('scroll', () => {
        let current = '';
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop - 150;
            if (scrollY >= sectionTop) {
                current = section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + current) {
                link.classList.add('active');
            }
        });
        
        // Bouton retour en haut
        const backToTop = document.querySelector('.back-to-top');
        if (window.scrollY > 300) {
            backToTop.classList.remove('hidden');
        } else {
            backToTop.classList.add('hidden');
        }
    });
    
    // Fonctions de navigation
    function scrollToTop() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    
    // Fonctions cookies (simulation)
    function acceptAllCookies() {
        showNotification('✅ Tous les cookies ont été acceptés', 'success');
    }
    
    function rejectNonEssentialCookies() {
        showNotification('⚙️ Cookies non essentiels refusés', 'info');
    }
    
    function openCookieSettings() {
        showNotification('🔧 Paramètres cookies ouverts', 'info');
    }
    
    // Fonction de notification
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `fixed bottom-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-medium animate-slideIn ${
            type === 'success' ? 'bg-green-600' : 'bg-blue-600'
        }`;
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check' : 'info'}-circle mr-2"></i>
                ${message}
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('animate-slideOut');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
    
    // Ajouter les styles d'animation
    const style = document.createElement('style');
    style.textContent = `
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
    `;
    document.head.appendChild(style);
    </script>
</body>
</html>