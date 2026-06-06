<?php
// pages/404.php

// Configuration
$site_name = "Investian";
$site_url = "https://investian.com";
$support_email = "changerlemonde@gmail.com";
?>


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
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            min-height: 100vh;
            color: #334155;
        }
        
        @media (prefers-color-scheme: dark) {
            body {
                background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                color: #cbd5e1;
            }
        }
        
        /* Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        
        .animate-pulse {
            animation: pulse 2s ease-in-out infinite;
        }
        
        .animate-blink {
            animation: blink 1.5s ease-in-out infinite;
        }
        
        .animate-spin-slow {
            animation: spin 20s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>

<div class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white dark:bg-slate-800 shadow-sm">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <a href="index.php?page=home" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center">
                        <i class="fas fa-chart-line text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white"><?php echo $site_name; ?></h1>
                        <p class="text-xs text-green-600 dark:text-green-400">Investissements Intelligents</p>
                    </div>
                </a>
                
                <!-- Navigation -->
                <nav class="hidden md:flex items-center gap-6">
                    <a href="index.php?page=home" class="text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">
                        Accueil
                    </a>
                    <a href="index.php?page=home" class="text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">
                        Tableau de bord
                    </a>
                    <a href="index.php?page=investissement" class="text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">
                        Investir
                    </a>
                    <a href="index.php?page=support" class="text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">
                        Support
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex items-center justify-center py-12 px-4">
        <div class="container mx-auto max-w-6xl">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Left Column - Illustration & Message -->
                <div class="text-center lg:text-left">
                    <!-- Decorative Elements -->
                    <div class="relative mb-8">
                        <!-- Floating circles -->
                        <div class="absolute -top-4 -left-4 w-24 h-24 rounded-full bg-gradient-to-br from-green-200 to-emerald-300 dark:from-green-900/30 dark:to-emerald-900/30 animate-float opacity-50"></div>
                        <div class="absolute -bottom-4 -right-4 w-32 h-32 rounded-full bg-gradient-to-br from-blue-200 to-cyan-300 dark:from-blue-900/30 dark:to-cyan-900/30 animate-float opacity-50" style="animation-delay: -3s;"></div>
                        
                        <!-- Error Number -->
                        <div class="relative z-10">
                            <div class="inline-flex items-center justify-center mb-6">
                                <div class="relative">
                                    <!-- Animated background circles -->
                                    <div class="absolute inset-0 w-48 h-48 rounded-full bg-gradient-to-br from-red-100 to-pink-100 dark:from-red-900/20 dark:to-pink-900/20 animate-pulse"></div>
                                    <div class="absolute inset-6 w-36 h-36 rounded-full bg-gradient-to-br from-orange-100 to-red-100 dark:from-orange-900/20 dark:to-red-900/20 animate-pulse" style="animation-delay: -1s;"></div>
                                    
                                    <!-- Main number -->
                                    <div class="relative z-10">
                                        <h1 class="text-9xl md:text-[12rem] font-bold text-gray-900 dark:text-white leading-none">
                                            4<span class="text-green-500 animate-blink">0</span>4
                                        </h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Error Message -->
                    <div class="mb-8">
                        <h2 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">
                            Oups ! Page introuvable
                        </h2>
                        <p class="text-xl text-gray-600 dark:text-gray-400 mb-6">
                            La page que vous recherchez semble s'être égarée dans l'espace numérique.
                        </p>
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-300 rounded-lg">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="text-sm">Code d'erreur: 404 - Page Not Found</span>
                        </div>
                    </div>
                    
                    <!-- Possible Reasons -->
                    <div class="mb-8 p-6 bg-white dark:bg-slate-800 rounded-2xl shadow-lg border border-gray-200 dark:border-slate-700">
                        <h3 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <i class="fas fa-search mr-3 text-green-600"></i>
                            Pourquoi cette erreur ?
                        </h3>
                        <ul class="space-y-3 text-gray-600 dark:text-gray-400">
                            <li class="flex items-start">
                                <i class="fas fa-times-circle text-red-500 mt-1 mr-3"></i>
                                <span>L'URL a été mal saisie ou contient une faute de frappe</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-external-link-alt text-blue-500 mt-1 mr-3"></i>
                                <span>Le lien que vous avez suivi est obsolète ou cassé</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-archive text-purple-500 mt-1 mr-3"></i>
                                <span>La page a été déplacée, renommée ou supprimée</span>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Primary Actions -->
                    <div class="space-y-4">
                        <a href="home" 
                           class="inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold rounded-xl transition-all duration-300 transform hover:-translate-y-1 hover:shadow-xl active:translate-y-0 w-full lg:w-auto">
                            <i class="fas fa-home mr-3"></i>
                            Retour à l'accueil
                        </a>
                        
                        <button onclick="window.history.back()"
                                class="inline-flex items-center justify-center px-8 py-4 border-2 border-green-600 text-green-600 hover:bg-green-50 dark:text-green-400 dark:border-green-500 dark:hover:bg-green-900/20 font-medium rounded-xl transition-colors w-full lg:w-auto">
                            <i class="fas fa-arrow-left mr-3"></i>
                            Retour à la page précédente
                        </button>
                    </div>
                </div>
                
                <!-- Right Column - Illustration & Quick Links -->
                <div class="relative">
                    <!-- Space Illustration -->
                    <div class="relative">
                        <!-- Stars -->
                        <div class="absolute inset-0">
                            <?php for ($i = 0; $i < 50; $i++): ?>
                            <div class="absolute w-1 h-1 bg-white rounded-full animate-pulse"
                                 style="top: <?php echo rand(0, 100); ?>%; left: <?php echo rand(0, 100); ?>%; animation-delay: -<?php echo rand(0, 2000); ?>ms;"></div>
                            <?php endfor; ?>
                        </div>
                        
                        <!-- Planets -->
                        <div class="relative z-10">
                            <!-- Lost Planet -->
                            <div class="w-64 h-64 mx-auto mb-8 relative">
                                <!-- Planet -->
                                <div class="absolute inset-0 rounded-full bg-gradient-to-br from-gray-300 to-gray-400 dark:from-gray-600 dark:to-gray-800 shadow-2xl animate-spin-slow"></div>
                                
                                <!-- Planet Details -->
                                <div class="absolute inset-4 rounded-full bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-900">
                                    <!-- Craters -->
                                    <div class="absolute top-8 left-8 w-12 h-12 rounded-full bg-gradient-to-br from-gray-400 to-gray-500 dark:from-gray-800 dark:to-gray-900"></div>
                                    <div class="absolute bottom-12 right-12 w-8 h-8 rounded-full bg-gradient-to-br from-gray-400 to-gray-500 dark:from-gray-800 dark:to-gray-900"></div>
                                    <div class="absolute top-20 right-16 w-6 h-6 rounded-full bg-gradient-to-br from-gray-400 to-gray-500 dark:from-gray-800 dark:to-gray-900"></div>
                                </div>
                                
                                <!-- Lost Astronaut -->
                                <div class="absolute -top-4 -right-4 animate-float">
                                    <div class="relative">
                                        <div class="w-20 h-20">
                                            <!-- Astronaut -->
                                            <div class="absolute w-12 h-16 bg-white dark:bg-gray-300 rounded-t-3xl rounded-b-lg"></div>
                                            <!-- Helmet -->
                                            <div class="absolute -top-1 left-3 w-10 h-10 bg-blue-300 dark:bg-blue-500 rounded-full border-4 border-white dark:border-gray-300"></div>
                                            <!-- Flag -->
                                            <div class="absolute top-4 -right-4">
                                                <div class="w-8 h-6 bg-red-500 rounded-r-lg"></div>
                                                <div class="w-1 h-8 bg-gray-400 ml-1"></div>
                                            </div>
                                        </div>
                                        <!-- Thought bubble -->
                                        <div class="absolute -top-12 -left-8">
                                            <div class="w-16 h-16 bg-white dark:bg-slate-800 rounded-full border-2 border-gray-300 dark:border-slate-700 flex items-center justify-center">
                                                <span class="text-2xl">?</span>
                                            </div>
                                            <div class="absolute bottom-2 right-2 w-6 h-6 bg-white dark:bg-slate-800 border-2 border-gray-300 dark:border-slate-700 rounded-full"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Satellite -->
                                <div class="absolute -bottom-8 -left-8 animate-float" style="animation-delay: -2s;">
                                    <div class="w-16 h-16">
                                        <div class="absolute inset-0 bg-gray-400 dark:bg-gray-600 rounded-lg transform rotate-45"></div>
                                        <div class="absolute inset-2 bg-gray-300 dark:bg-gray-500 rounded transform rotate-45"></div>
                                        <div class="absolute top-1/2 left-1/2 w-8 h-8 bg-gray-200 dark:bg-gray-400 rounded-full transform -translate-x-1/2 -translate-y-1/2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Links -->
                    <div class="mt-12">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 text-center">
                            <i class="fas fa-compass mr-3 text-green-600"></i>
                            Naviguez vers ces pages populaires
                        </h3>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <a href="home"
                               class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:border-green-500 hover:shadow-lg transition-all duration-300 text-center group">
                                <div class="w-12 h-12 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                                    <i class="fas fa-tachometer-alt text-green-600 dark:text-green-400"></i>
                                </div>
                                <div class="font-medium text-gray-900 dark:text-white">Tableau de bord</div>
                            </a>
                            
                            <a href="investissement"
                               class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:border-green-500 hover:shadow-lg transition-all duration-300 text-center group">
                                <div class="w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                                    <i class="fas fa-chart-line text-blue-600 dark:text-blue-400"></i>
                                </div>
                                <div class="font-medium text-gray-900 dark:text-white">Investir</div>
                            </a>
                            
                            <a href="support"
                               class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:border-green-500 hover:shadow-lg transition-all duration-300 text-center group">
                                <div class="w-12 h-12 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                                    <i class="fas fa-headset text-purple-600 dark:text-purple-400"></i>
                                </div>
                                <div class="font-medium text-gray-900 dark:text-white">Support</div>
                            </a>
                            
                            <a href="tuto"
                               class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:border-green-500 hover:shadow-lg transition-all duration-300 text-center group">
                                <div class="w-12 h-12 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                                    <i class="fas fa-graduation-cap text-orange-600 dark:text-orange-400"></i>
                                </div>
                                <div class="font-medium text-gray-900 dark:text-white">Tutoriels</div>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Search Form -->
                    <div class="mt-8">
                        <div class="relative">
                            <input type="text" 
                                   id="search404"
                                   placeholder="Que recherchez-vous ?"
                                   class="w-full pl-12 pr-4 py-3 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-xl focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all duration-200 text-gray-900 dark:text-white">
                            <div class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-gray-500">
                                <i class="fas fa-search"></i>
                            </div>
                            <button onclick="searchFrom404()"
                                    class="absolute right-2 top-1/2 transform -translate-y-1/2 px-4 py-1.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                                Rechercher
                            </button>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 text-center">
                            Essayez de rechercher directement ce que vous cherchez
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Report Error -->
            <div class="mt-16 pt-8 border-t border-gray-200 dark:border-slate-700">
                <div class="max-w-3xl mx-auto text-center">
                    <div class="inline-flex flex-col sm:flex-row items-center gap-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 p-6 rounded-2xl border border-green-200 dark:border-green-800">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                <i class="fas fa-bug text-green-600 dark:text-green-400 text-xl"></i>
                            </div>
                            <div class="text-left">
                                <h4 class="font-bold text-gray-900 dark:text-white">Vous pensez que c'est une erreur ?</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Signalez-nous ce lien cassé</p>
                            </div>
                        </div>
                        <a href="mailto:<?php echo $support_email; ?>?subject=Lien%20cassé%20sur%20<?php echo $site_name; ?>&body=URL%20problématique:%20<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                           class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors whitespace-nowrap">
                            <i class="fas fa-flag mr-2"></i>
                            Signaler un problème
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white">
        <div class="container mx-auto px-4 py-8">
            <div class="text-center">
                <p class="text-gray-400">
                    &copy; <?php echo date('Y'); ?> <?php echo $site_name; ?>. Tous droits réservés.
                </p>
                <div class="mt-4 flex justify-center space-x-6 text-sm text-gray-400">
                    <a href="politique" class="hover:text-green-400 transition-colors">Conditions</a>
                    <a href="politique" class="hover:text-green-400 transition-colors">Confidentialité</a>
                    <a href="support" class="hover:text-green-400 transition-colors">Support</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
    // Search functionality
    function searchFrom404() {
        const searchTerm = document.getElementById('search404').value.trim();
        if (searchTerm) {
            window.location.href = `politique?q=${encodeURIComponent(searchTerm)}`;
        } else {
            document.getElementById('search404').focus();
        }
    }
    
    // Enter key support for search
    document.getElementById('search404').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchFrom404();
        }
    });
    
    // Auto-focus search on page load
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            document.getElementById('search404').focus();
        }, 1000);
    });
    
    // Create more stars dynamically
    function createStars() {
        const starsContainer = document.querySelector('.absolute.inset-0');
        if (starsContainer) {
            for (let i = 0; i < 30; i++) {
                const star = document.createElement('div');
                star.className = 'absolute w-1 h-1 bg-white rounded-full animate-pulse';
                star.style.top = `${Math.random() * 100}%`;
                star.style.left = `${Math.random() * 100}%`;
                star.style.animationDelay = `-${Math.random() * 2000}ms`;
                starsContainer.appendChild(star);
            }
        }
    }
    
    // Initialize stars
    createStars();
    
    // Add click animation to 404 number
    document.querySelector('h1').addEventListener('click', function() {
        this.style.transform = 'scale(1.1)';
        this.style.transition = 'transform 0.3s ease';
        setTimeout(() => {
            this.style.transform = 'scale(1)';
        }, 300);
        
        // Create explosion effect
        for (let i = 0; i < 20; i++) {
            const particle = document.createElement('div');
            particle.className = 'fixed w-2 h-2 bg-yellow-400 rounded-full z-50';
            particle.style.left = '50%';
            particle.style.top = '50%';
            particle.style.transform = 'translate(-50%, -50%)';
            
            const angle = Math.random() * Math.PI * 2;
            const velocity = 2 + Math.random() * 3;
            const vx = Math.cos(angle) * velocity;
            const vy = Math.sin(angle) * velocity;
            
            document.body.appendChild(particle);
            
            let x = 50;
            let y = 50;
            const animation = setInterval(() => {
                x += vx;
                y += vy;
                particle.style.left = `${x}%`;
                particle.style.top = `${y}%`;
                particle.style.opacity = parseFloat(particle.style.opacity || 1) - 0.05;
                
                if (parseFloat(particle.style.opacity) <= 0) {
                    clearInterval(animation);
                    particle.remove();
                }
            }, 16);
        }
    });
    
    // Easter egg: Konami code
    const konamiCode = ['ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', 'b', 'a'];
    let konamiIndex = 0;
    
    document.addEventListener('keydown', function(e) {
        if (e.key === konamiCode[konamiIndex]) {
            konamiIndex++;
            if (konamiIndex === konamiCode.length) {
                // Konami code activated!
                document.body.style.background = 'linear-gradient(135deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4, #feca57, #ff9ff3)';
                document.body.style.backgroundSize = '1200% 1200%';
                document.body.style.animation = 'rainbow 10s ease infinite';
                
                const style = document.createElement('style');
                style.textContent = `
                    @keyframes rainbow {
                        0% { background-position: 0% 50%; }
                        50% { background-position: 100% 50%; }
                        100% { background-position: 0% 50%; }
                    }
                `;
                document.head.appendChild(style);
                
                // Show secret message
                const secretMsg = document.createElement('div');
                secretMsg.className = 'fixed top-4 left-1/2 transform -translate-x-1/2 bg-black/80 text-white px-6 py-3 rounded-lg z-50 animate-fadeIn';
                secretMsg.innerHTML = '<i class="fas fa-star mr-2"></i> Code Konami activé ! Bien joué !';
                document.body.appendChild(secretMsg);
                
                setTimeout(() => {
                    secretMsg.remove();
                }, 3000);
                
                konamiIndex = 0;
            }
        } else {
            konamiIndex = 0;
        }
    });
    
    // Track 404 errors (optional analytics)
    if (typeof gtag !== 'undefined') {
        gtag('event', 'page_view', {
            page_title: '404 - Page Not Found',
            page_location: window.location.href,
            page_path: window.location.pathname
        });
    }
    </script>
