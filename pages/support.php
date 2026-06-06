<?php
// support.php - Page de support client

// Initialiser la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuration
$site_name = "Investian";
$site_url = "investian.infy.uk";
$support_email = "junior1009f@gmail.com";
$support_phone = "+237656720564";
$whatsapp_number = "+237656720564";
$telegram_username = "@investian2371";
$facebook_username = "investian.officiel";

// Contacts de support
$support_contacts = [
    'email' => [
        'icon' => 'fas fa-envelope',
        'title' => 'Email Support',
        'value' => $support_email,
        'link' => 'mailto:' . $support_email,
        'description' => 'Réponse sous 24h',
        'color' => 'from-red-500 to-pink-500'
    ],
    'phone' => [
        'icon' => 'fas fa-phone',
        'title' => 'Support Téléphonique',
        'value' => $support_phone,
        'link' => 'tel:' . preg_replace('/[^0-9+]/', '', $support_phone),
        'description' => 'Lun-Ven: 8h-18h',
        'color' => 'from-green-500 to-emerald-500'
    ],
    'whatsapp' => [
        'icon' => 'fab fa-whatsapp',
        'title' => 'WhatsApp',
        'value' => $whatsapp_number,
        'link' => 'https://wa.me/' . preg_replace('/[^0-9]/', '', $whatsapp_number),
        'description' => 'Réponse rapide',
        'color' => 'from-green-400 to-teal-500'
    ]
];

// Réseaux sociaux
$social_networks = [
    'telegram' => [
        'icon' => 'fab fa-telegram',
        'name' => 'Telegram',
        'username' => $telegram_username,
        'link' => 'https://t.me/investian237',
        'description' => 'Communauté active',
        'color' => 'from-blue-500 to-cyan-500',
        'members' => 'K+ membres'
    ],
    'facebook' => [
        'icon' => 'fab fa-facebook',
        'name' => 'Facebook',
        'username' => $facebook_username,
        'link' => 'https://www.facebook.com/profile.php?id=61585031597114',
        'description' => 'Page officielle',
        'color' => 'from-blue-600 to-blue-800',
        'followers' => 'k+ followers'
    ],
        'whatsapp' => [
        'icon' => 'fab fa-whatsapp',
        'name' => 'communaute',
        'username' => "",
        'link' => 'https://whatsapp.com/channel/0029VbBJD06Jf05c69AGmY1T',
        'description' => 'Page officielle',
        'color' => 'from-blue-600 to-blue-800',
        'followers' => 'k+ followers'
    ]
];

// FAQ
$faq_categories = [
    'general' => [
        'title' => 'Questions Générales',
        'icon' => 'fas fa-question-circle',
        'questions' => [
            [
                'question' => 'Qu\'est-ce que ' . $site_name . ' ?',
                'answer' => $site_name . ' est une plateforme d\'investissement sécurisée qui vous permet de générer des revenus passifs via différents plans d\'investissement, le visionnage de vidéos rémunérées et le parrainage.'
            ],
            [
                'question' => 'Comment créer un compte ?',
                'answer' => 'Cliquez sur "S\'inscrire" en haut de la page, remplissez le formulaire avec vos informations personnelles et validez votre email. Votre compte sera activé immédiatement.'
            ],
            [
                'question' => 'Est-ce sécurisé ?',
                'answer' => 'Oui, nous utilisons le cryptage SSL, la double authentification et des protocoles de sécurité bancaire pour protéger vos données et vos transactions.'
            ]
        ]
    ],
    'account' => [
        'title' => 'Compte & Sécurité',
        'icon' => 'fas fa-user-shield',
        'questions' => [
            [
                'question' => 'Comment réinitialiser mon mot de passe ?',
                'answer' => 'Allez sur la page de connexion, cliquez sur "Mot de passe oublié", entrez votre email et suivez les instructions que vous recevrez.'
            ],
            [
                'question' => 'Comment activer la 2FA ?',
                'answer' => 'Dans les paramètres de sécurité de votre compte, vous pouvez activer l\'authentification à deux facteurs via Google Authenticator.'
            ],
            [
                'question' => 'Pourquoi mon compte est-il en attente ?',
                'answer' => 'Les nouveaux comptes nécessitent une vérification. Vérifiez votre email pour le lien de confirmation ou contactez le support.'
            ]
        ]
    ],
    'financial' => [
        'title' => 'Finances & Paiements',
        'icon' => 'fas fa-money-bill-wave',
        'questions' => [
            [
                'question' => 'Comment déposer des fonds ?',
                'answer' => 'Connectez-vous à votre compte, allez dans "Dépôt", choisissez votre méthode de paiement (Orange Money, MTN Mobile Money) et suivez les instructions.'
            ],
            [
                'question' => 'Quand sont versés les gains ?',
                'answer' => 'Les gains sur investissement sont versés quotidiennement à minuit. Les retraits sont traités sous 24-48 heures.'
            ],
            [
                'question' => 'Quel est le minimum de retrait ?',
                'answer' => 'Le minimum de retrait est de 1 000 FCFA pour toutes les méthodes de paiement.'
            ]
        ]
    ]
];

// Traitement du formulaire de contact
$contact_errors = [];
$contact_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_form'])) {
    // Récupération des données
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Vérification CSRF
    if (!isset($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
        $contact_errors[] = "Token de sécurité invalide";
    } else {
        // Validation
        if (empty($name)) $contact_errors[] = "Le nom est requis";
        if (empty($email)) {
            $contact_errors[] = "L'email est requis";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $contact_errors[] = "Email invalide";
        }
        if (empty($subject)) $contact_errors[] = "Le sujet est requis";
        if (empty($category)) $contact_errors[] = "La catégorie est requise";
        if (empty($message)) $contact_errors[] = "Le message est requis";
        if (strlen($message) < 10) $contact_errors[] = "Le message est trop court";
        
        if (empty($contact_errors)) {
            // Ici, vous enverriez l'email ou sauvegarderiez en base de données
            // Exemple d'envoi d'email :
            /*
            $to = $support_email;
            $headers = "From: $email\r\n";
            $headers .= "Reply-To: $email\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            
            $email_body = "
                <h2>Nouveau message de support</h2>
                <p><strong>Nom:</strong> $name</p>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Sujet:</strong> $subject</p>
                <p><strong>Catégorie:</strong> $category</p>
                <p><strong>Message:</strong></p>
                <p>" . nl2br(htmlspecialchars($message)) . "</p>
            ";
            
            if (mail($to, "[Support] $subject", $email_body, $headers)) {
                $contact_success = true;
            } else {
                $contact_errors[] = "Erreur lors de l'envoi du message";
            }
            */
            
            // Pour la démo, on simule le succès
            $contact_success = true;
            
            // Réinitialiser le formulaire
            if ($contact_success) {
                $name = $email = $subject = $category = $message = '';
            }
        }
    }
}

// Générer un token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Inline Critical CSS -->
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #3b82f6;
            --primary-dark: #1e40af;
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
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        .animate-fadeIn {
            animation: fadeIn 0.6s ease-out;
        }
        
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        
        .animate-pulse {
            animation: pulse 2s infinite;
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


    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-br from-green-50 to-emerald-100 dark:from-green-900/30 dark:to-emerald-900/20 py-16">
        <!-- Background elements -->
        <div class="absolute top-0 left-0 w-64 h-64 bg-green-200 dark:bg-green-800/30 rounded-full -translate-x-32 -translate-y-32"></div>
        <div class="absolute bottom-0 right-0 w-80 h-80 bg-emerald-200 dark:bg-emerald-800/30 rounded-full translate-x-40 translate-y-40"></div>
        
        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white dark:bg-slate-800 shadow-lg mb-6">
                    <i class="fas fa-headset text-green-600 dark:text-green-400 text-3xl"></i>
                </div>
                
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">
                    Support Client <span class="text-green-600">24/7</span>
                </h1>
                
                <p class="text-xl text-gray-600 dark:text-gray-400 mb-8 max-w-2xl mx-auto">
                    Notre équipe est là pour vous aider. Contactez-nous via votre canal préféré et obtenez une réponse rapide.
                </p>
                
                <!-- Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-2xl mx-auto">
                    <div class="bg-white dark:bg-slate-800/50 backdrop-blur-sm rounded-xl p-4 border border-green-200 dark:border-green-800">
                        <div class="text-2xl font-bold text-green-600">24h</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Temps de réponse</div>
                    </div>
                    <div class="bg-white dark:bg-slate-800/50 backdrop-blur-sm rounded-xl p-4 border border-green-200 dark:border-green-800">
                        <div class="text-2xl font-bold text-green-600">95%</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Satisfaction</div>
                    </div>
                    <div class="bg-white dark:bg-slate-800/50 backdrop-blur-sm rounded-xl p-4 border border-green-200 dark:border-green-800">
                        <div class="text-2xl font-bold text-green-600">4.8/5</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Note moyenne</div>
                    </div>
                    <div class="bg-white dark:bg-slate-800/50 backdrop-blur-sm rounded-xl p-4 border border-green-200 dark:border-green-800">
                        <div class="text-2xl font-bold text-green-600">5min</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">WhatsApp réponse</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <main class="container mx-auto px-4 py-12">
        <!-- Contact Methods -->
        <section class="mb-16 animate-fadeIn">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                    <i class="fas fa-comments mr-3 text-green-600"></i>
                    Contactez-nous
                </h2>
                <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                    Choisissez votre méthode de contact préférée. Notre équipe est disponible pour vous aider.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <?php foreach ($support_contacts as $key => $contact): ?>
                <a href="<?php echo $contact['link']; ?>" 
                   target="_blank" 
                   class="group bg-white dark:bg-slate-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 p-6 text-center border border-gray-200 dark:border-slate-700">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-xl bg-gradient-to-br <?php echo $contact['color']; ?> text-white text-2xl mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="<?php echo $contact['icon']; ?>"></i>
                    </div>
                    
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                        <?php echo $contact['title']; ?>
                    </h3>
                    
                    <div class="text-green-600 dark:text-green-400 font-medium text-lg mb-2">
                        <?php echo $contact['value']; ?>
                    </div>
                    
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">
                        <?php echo $contact['description']; ?>
                    </p>
                    
                    <div class="inline-flex items-center text-green-600 dark:text-green-400 font-medium">
                        <span>Contacter</span>
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-2 transition-transform"></i>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            
            <!-- Note -->
            <div class="text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-50 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 rounded-lg">
                    <i class="fas fa-clock"></i>
                    <span class="text-sm">Heures de support: Lundi - Vendredi (8h - 18h) | Samedi (9h - 13h)</span>
                </div>
            </div>
        </section>
        
        <!-- Social Networks -->
        <section class="mb-16 animate-fadeIn">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                    <i class="fas fa-users mr-3 text-green-600"></i>
                    Rejoignez notre communauté
                </h2>
                <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                    Connectez-vous avec d'autres investisseurs, obtenez des conseils et restez informé des dernières nouvelles.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($social_networks as $key => $social): ?>
                <a href="<?php echo $social['link']; ?>" 
                   target="_blank" 
                   class="group bg-white dark:bg-slate-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 p-6 text-center border border-gray-200 dark:border-slate-700">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-xl bg-gradient-to-br <?php echo $social['color']; ?> text-white text-2xl mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="<?php echo $social['icon']; ?>"></i>
                    </div>
                    
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                        <?php echo $social['name']; ?>
                    </h3>
                    
                    <div class="text-green-600 dark:text-green-400 font-medium text-lg mb-2">
                        <?php echo $social['username']; ?>
                    </div>
                    
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">
                        <?php echo $social['description']; ?>
                    </p>
                    
                    <?php if (isset($social['members']) || isset($social['followers'])): ?>
                    <div class="inline-flex items-center gap-1 px-3 py-1 bg-gray-100 dark:bg-slate-700 rounded-full text-xs text-gray-700 dark:text-gray-300 mb-4">
                        <i class="fas fa-user-friends"></i>
                        <span><?php echo $social['members'] ?? $social['followers']; ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="inline-flex items-center text-green-600 dark:text-green-400 font-medium">
                        <span>Rejoindre</span>
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-2 transition-transform"></i>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </section>
        
        <!-- FAQ -->
        <section class="mb-16 animate-fadeIn">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                    <i class="fas fa-question-circle mr-3 text-green-600"></i>
                    Questions Fréquentes
                </h2>
                <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                    Trouvez rapidement des réponses aux questions les plus courantes.
                </p>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <?php foreach ($faq_categories as $category_key => $category): ?>
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-slate-700">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <i class="<?php echo $category['icon']; ?> text-green-600 dark:text-green-400 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                            <?php echo $category['title']; ?>
                        </h3>
                    </div>
                    
                    <div class="space-y-4">
                        <?php foreach ($category['questions'] as $index => $faq): ?>
                        <div class="faq-item border-b border-gray-100 dark:border-slate-700 pb-4 last:border-0 last:pb-0">
                            <button class="faq-question w-full text-left flex items-center justify-between gap-4 group"
                                    onclick="toggleFaq(this)">
                                <span class="font-medium text-gray-900 dark:text-white group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors">
                                    <?php echo $faq['question']; ?>
                                </span>
                                <i class="fas fa-chevron-down text-gray-400 group-hover:text-green-600 transition-all duration-300"></i>
                            </button>
                            <div class="faq-answer mt-3 text-gray-600 dark:text-gray-400 hidden">
                                <?php echo $faq['answer']; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- More Help -->
            <div class="mt-12 text-center">
                <div class="inline-flex flex-col sm:flex-row items-center gap-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 p-6 rounded-2xl border border-green-200 dark:border-green-800">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <i class="fas fa-search text-green-600 dark:text-green-400 text-xl"></i>
                        </div>
                        <div class="text-left">
                            <h4 class="font-bold text-gray-900 dark:text-white">Vous ne trouvez pas votre réponse ?</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Parcourez notre base de connaissances complète</p>
                        </div>
                    </div>
                    <a href="index.php?page=tuto" 
                       class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors whitespace-nowrap">
                        <i class="fas fa-book-open mr-2"></i>
                        Base de connaissances
                    </a>
                </div>
            </div>
        </section>
        
        <!-- Contact Form -->
        <section class="animate-fadeIn">
            <div class="max-w-4xl mx-auto">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg overflow-hidden border border-gray-200 dark:border-slate-700">
                    <div class="md:flex">
                        <!-- Left Column -->
                        <div class="md:w-2/5 bg-gradient-to-br from-green-600 to-emerald-600 p-8 text-white">
                            <div class="h-full flex flex-col justify-center">
                                <div class="mb-8">
                                    <div class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center mb-6 border border-white/30">
                                        <i class="fas fa-paper-plane text-2xl"></i>
                                    </div>
                                    <h3 class="text-2xl font-bold mb-4">Envoyez-nous un message</h3>
                                    <p class="text-green-100">
                                        Remplissez le formulaire et notre équipe vous répondra dans les plus brefs délais.
                                    </p>
                                </div>
                                
                                <div class="space-y-4 mt-auto">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium">Réponse rapide</div>
                                            <div class="text-sm text-green-100">Sous 24 heures</div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                                            <i class="fas fa-user-headset"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium">Support expert</div>
                                            <div class="text-sm text-green-100">Équipe dédiée</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Column - Form -->
                        <div class="md:w-3/5 p-8">
                            <h4 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                                Formulaire de contact
                            </h4>
                            
                            <?php if ($contact_success): ?>
                            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-green-800 dark:text-green-300 font-medium">
                                            Message envoyé avec succès !
                                        </p>
                                        <p class="text-sm text-green-700 dark:text-green-400 mt-1">
                                            Nous vous répondrons dans les plus brefs délais.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($contact_errors)): ?>
                            <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 mt-0.5">
                                        <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400"></i>
                                    </div>
                                    <div>
                                        <h5 class="text-sm font-semibold text-red-800 dark:text-red-300 mb-2">
                                            Veuillez corriger les erreurs suivantes :
                                        </h5>
                                        <ul class="space-y-1">
                                            <?php foreach ($contact_errors as $error): ?>
                                            <li class="text-sm text-red-700 dark:text-red-400 flex items-center gap-2">
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                                <?php echo $error; ?>
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="" id="contactForm" class="space-y-6">
                                <input type="hidden" name="contact_form" value="1">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Name -->
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="fas fa-user mr-2 text-green-600"></i>
                                            Nom complet
                                        </label>
                                        <input type="text" 
                                               id="name" 
                                               name="name" 
                                               value="<?php echo htmlspecialchars($name ?? ''); ?>"
                                               class="w-full px-4 py-3 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-lg focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all duration-200 text-gray-900 dark:text-white"
                                               placeholder="Votre nom"
                                               required>
                                    </div>
                                    
                                    <!-- Email -->
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="fas fa-envelope mr-2 text-green-600"></i>
                                            Adresse email
                                        </label>
                                        <input type="email" 
                                               id="email" 
                                               name="email" 
                                               value="<?php echo htmlspecialchars($email ?? ''); ?>"
                                               class="w-full px-4 py-3 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-lg focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all duration-200 text-gray-900 dark:text-white"
                                               placeholder="votre@email.com"
                                               required>
                                    </div>
                                </div>
                                
                                <!-- Subject -->
                                <div>
                                    <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        <i class="fas fa-tag mr-2 text-green-600"></i>
                                        Sujet
                                    </label>
                                    <input type="text" 
                                           id="subject" 
                                           name="subject" 
                                           value="<?php echo htmlspecialchars($subject ?? ''); ?>"
                                           class="w-full px-4 py-3 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-lg focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all duration-200 text-gray-900 dark:text-white"
                                           placeholder="Sujet de votre message"
                                           required>
                                </div>
                                
                                <!-- Category -->
                                <div>
                                    <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        <i class="fas fa-filter mr-2 text-green-600"></i>
                                        Catégorie
                                    </label>
                                    <select id="category" 
                                            name="category"
                                            class="w-full px-4 py-3 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-lg focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all duration-200 text-gray-900 dark:text-white"
                                            required>
                                        <option value="">Sélectionnez une catégorie</option>
                                        <option value="general" <?php echo (($category ?? '') === 'general') ? 'selected' : ''; ?>>Question générale</option>
                                        <option value="technical" <?php echo (($category ?? '') === 'technical') ? 'selected' : ''; ?>>Problème technique</option>
                                        <option value="financial" <?php echo (($category ?? '') === 'financial') ? 'selected' : ''; ?>>Question financière</option>
                                        <option value="account" <?php echo (($category ?? '') === 'account') ? 'selected' : ''; ?>>Problème de compte</option>
                                        <option value="other" <?php echo (($category ?? '') === 'other') ? 'selected' : ''; ?>>Autre</option>
                                    </select>
                                </div>
                                
                                <!-- Message -->
                                <div>
                                    <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        <i class="fas fa-comment-dots mr-2 text-green-600"></i>
                                        Message
                                    </label>
                                    <textarea id="message" 
                                              name="message"
                                              rows="5"
                                              class="w-full px-4 py-3 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-lg focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all duration-200 text-gray-900 dark:text-white resize-none"
                                              placeholder="Décrivez votre problème ou votre question en détail..."
                                              required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                                    <div class="text-right mt-2">
                                        <span id="charCount" class="text-sm text-gray-500 dark:text-gray-400">0/1000 caractères</span>
                                    </div>
                                </div>
                                
                                <!-- Submit Button -->
                                <div class="flex items-center justify-between pt-4">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Réponse garantie sous 24h
                                    </div>
                                    <button type="submit" 
                                            id="submitContactBtn"
                                            class="px-8 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold rounded-lg transition-all duration-300 transform hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800 group">
                                        <span id="submitText">
                                            <i class="fas fa-paper-plane mr-2"></i>
                                            Envoyer le message
                                        </span>
                                        <div id="submitSpinner" class="hidden">
                                            <div class="spinner mx-auto"></div>
                                        </div>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Emergency Contact -->
        <section class="mt-16 text-center">
            <div class="max-w-2xl mx-auto p-6 bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 rounded-2xl border border-red-200 dark:border-red-800">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400 text-xl"></i>
                        </div>
                        <div class="text-left">
                            <h4 class="font-bold text-gray-900 dark:text-white">Urgence ?</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Problème urgent nécessitant une attention immédiate</p>
                        </div>
                    </div>
                    <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $support_phone); ?>" 
                       class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors whitespace-nowrap">
                        <i class="fas fa-phone-alt mr-2"></i>
                        Appeler le support
                    </a>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white mt-16">
        <div class="container mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div>
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center">
                            <i class="fas fa-chart-line text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold"><?php echo $site_name; ?></h3>
                            <p class="text-green-400 text-sm">Investissements Intelligents</p>
                        </div>
                    </div>
                    <p class="text-gray-400 text-sm">
                        Plateforme d'investissement sécurisée et fiable pour générer des revenus passifs.
                    </p>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h4 class="text-lg font-bold mb-6">Liens rapides</h4>
                    <ul class="space-y-3">
                        <li><a href="index.php?page=home" class="text-gray-400 hover:text-green-400 transition-colors"><i class="fas fa-chevron-right mr-2 text-xs"></i> Accueil</a></li>
                        <li><a href="index.php?page=support" class="text-gray-400 hover:text-green-400 transition-colors"><i class="fas fa-chevron-right mr-2 text-xs"></i> À propos</a></li>
                        <li><a href="index.php?page=investissement" class="text-gray-400 hover:text-green-400 transition-colors"><i class="fas fa-chevron-right mr-2 text-xs"></i> Investir</a></li>
                        <li><a href="index.php?page=investissement" class="text-gray-400 hover:text-green-400 transition-colors"><i class="fas fa-chevron-right mr-2 text-xs"></i> Plans</a></li>
                    </ul>
                </div>
                
                <!-- Support Links -->
                <div>
                    <h4 class="text-lg font-bold mb-6">Support</h4>
                    <ul class="space-y-3">
                        <li><a href="index.php?page=support" class="text-gray-400 hover:text-green-400 transition-colors"><i class="fas fa-chevron-right mr-2 text-xs"></i> Contact</a></li>
                        <li><a href="index.php?page=support" class="text-gray-400 hover:text-green-400 transition-colors"><i class="fas fa-chevron-right mr-2 text-xs"></i> FAQ</a></li>
                        <li><a href="index.php?page=politique" class="text-gray-400 hover:text-green-400 transition-colors"><i class="fas fa-chevron-right mr-2 text-xs"></i> Conditions</a></li>
                        <li><a href="index.php?page=politique" class="text-gray-400 hover:text-green-400 transition-colors"><i class="fas fa-chevron-right mr-2 text-xs"></i> Confidentialité</a></li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div>
                    <h4 class="text-lg font-bold mb-6">Contact</h4>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3 text-gray-400">
                            <i class="fas fa-envelope text-green-400"></i>
                            <span><?php echo $support_email; ?></span>
                        </li>
                        <li class="flex items-center gap-3 text-gray-400">
                            <i class="fas fa-phone text-green-400"></i>
                            <span><?php echo $support_phone; ?></span>
                        </li>
                        <li class="flex items-center gap-3 text-gray-400">
                            <i class="fab fa-whatsapp text-green-400"></i>
                            <span><?php echo $whatsapp_number; ?></span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Social Links -->
            <div class="border-t border-gray-800 mt-8 pt-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="text-gray-400 text-sm">
                        &copy; <?php echo date('Y'); ?> <?php echo $site_name; ?>. Tous droits réservés.
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <?php foreach ($social_networks as $key => $social): ?>
                        <a href="<?php echo $social['link']; ?>" 
                           target="_blank"
                           class="w-10 h-10 rounded-lg bg-gray-800 hover:bg-green-600 text-white flex items-center justify-center transition-colors"
                           aria-label="<?php echo $social['name']; ?>">
                            <i class="<?php echo $social['icon']; ?>"></i>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
    // Toggle FAQ answers
    function toggleFaq(button) {
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
    
    // Character counter for message
    const messageTextarea = document.getElementById('message');
    const charCount = document.getElementById('charCount');
    
    if (messageTextarea && charCount) {
        messageTextarea.addEventListener('input', function() {
            const length = this.value.length;
            charCount.textContent = `${length}/1000 caractères`;
            
            if (length > 1000) {
                charCount.classList.add('text-red-600');
                charCount.classList.remove('text-gray-500');
            } else {
                charCount.classList.remove('text-red-600');
                charCount.classList.add('text-gray-500');
            }
        });
        
        // Initialize count
        messageTextarea.dispatchEvent(new Event('input'));
    }
    
    // Form validation
    function validateContactForm() {
        let isValid = true;
        
        // Reset errors
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        document.querySelectorAll('input, textarea, select').forEach(el => {
            el.classList.remove('border-red-500');
        });
        
        // Check required fields
        const requiredFields = ['name', 'email', 'subject', 'category', 'message'];
        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field && !field.value.trim()) {
                isValid = false;
                field.classList.add('border-red-500');
                showError(field, 'Ce champ est requis');
            }
        });
        
        // Validate email
        const email = document.getElementById('email');
        if (email && email.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
            isValid = false;
            email.classList.add('border-red-500');
            showError(email, 'Email invalide');
        }
        
        // Validate message length
        const message = document.getElementById('message');
        if (message && message.value.length < 10) {
            isValid = false;
            message.classList.add('border-red-500');
            showError(message, 'Le message doit contenir au moins 10 caractères');
        }
        
        return isValid;
    }
    
    function showError(field, message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message text-sm text-red-600 dark:text-red-400 mt-2';
        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle mr-1"></i> ${message}`;
        
        // Insert after the field
        field.parentNode.insertBefore(errorDiv, field.nextSibling);
    }
    
    // Handle form submission
    document.getElementById('contactForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (validateContactForm()) {
            const submitBtn = document.getElementById('submitContactBtn');
            const submitText = document.getElementById('submitText');
            const submitSpinner = document.getElementById('submitSpinner');
            
            if (submitBtn && submitText && submitSpinner) {
                submitBtn.disabled = true;
                submitText.classList.add('hidden');
                submitSpinner.classList.remove('hidden');
            }
            
            // Submit form
            setTimeout(() => {
                this.submit();
            }, 500);
        } else {
            // Shake invalid fields
            const invalidFields = this.querySelectorAll('.border-red-500');
            invalidFields.forEach(field => {
                field.classList.add('animate-shake');
                setTimeout(() => {
                    field.classList.remove('animate-shake');
                }, 500);
            });
            
            // Scroll to first error
            const firstError = this.querySelector('.border-red-500');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }
    });
    
    // Mobile menu
    document.getElementById('mobileMenuButton')?.addEventListener('click', function() {
        // Implement mobile menu functionality
        alert('Menu mobile - À implémenter');
    });
    
    // Copy contact info on click
    document.querySelectorAll('[data-copy]').forEach(element => {
        element.addEventListener('click', function() {
            const text = this.dataset.copy;
            navigator.clipboard.writeText(text).then(() => {
                showToast('Copié dans le presse-papier !', 'success');
            });
        });
    });
    
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
    
    // Initialize animations
    document.addEventListener('DOMContentLoaded', function() {
        // Add animation classes
        const elements = document.querySelectorAll('.animate-on-scroll');
        elements.forEach((el, index) => {
            el.style.animationDelay = `${index * 0.1}s`;
        });
        
        // Auto-open first FAQ if on mobile
        if (window.innerWidth < 768) {
            const firstFaq = document.querySelector('.faq-question');
            if (firstFaq) {
                firstFaq.click();
            }
        }
    });
    
    // Add styles for animations
    const style = document.createElement('style');
    style.textContent = `
        .spinner {
            width: 1.5rem;
            height: 1.5rem;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        
        .animate-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        @media (prefers-color-scheme: dark) {
            input:-webkit-autofill,
            input:-webkit-autofill:hover,
            input:-webkit-autofill:focus {
                -webkit-text-fill-color: white;
                -webkit-box-shadow: 0 0 0px 1000px #1e293b inset;
                transition: background-color 5000s ease-in-out 0s;
            }
        }
    `;
    document.head.appendChild(style);
    
    // Intersection Observer for scroll animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.1 });
    
    document.querySelectorAll('.animate-on-scroll').forEach(el => observer.observe(el));
    </script>
</body>
</html>