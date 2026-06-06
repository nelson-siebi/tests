    <!-- Floating Bottom Navigation (Mobile) -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 z-40 px-4 pb-4">
        <nav class="bg-gray-900/90 backdrop-blur-lg border border-white/10 rounded-2xl shadow-2xl shadow-black/20 overflow-hidden">
            <div class="flex justify-around items-center h-16">
                <!-- Accueil -->
                <a href="?page=home" 
                   class="flex flex-col items-center justify-center flex-1 transition-all duration-300 <?php echo $page == 'home' ? 'text-primary-400' : 'text-gray-400 hover:text-white'; ?>">
                    <div class="relative">
                        <i class="fas fa-home text-lg"></i>
                        <?php if ($page == 'home'): ?>
                            <span class="absolute -top-1 -right-1 w-1.5 h-1.5 bg-primary-400 rounded-full animate-pulse"></span>
                        <?php endif; ?>
                    </div>
                    <span class="text-[10px] mt-1 font-semibold uppercase tracking-wider">Accueil</span>
                </a>
                
                <!-- Parrainage -->
                <a href="parainage" 
                   class="flex flex-col items-center justify-center flex-1 transition-all duration-300 <?php echo $page == 'parainage' ? 'text-primary-400' : 'text-gray-400 hover:text-white'; ?>">
                    <div class="relative">
                        <i class="fa-solid fa-user-plus text-lg"></i>
                    </div>
                    <span class="text-[10px] mt-1 font-semibold uppercase tracking-wider">Parrain</span>
                </a>
                
                <!-- Bouton Central (Action) -->
                <a href="investissement" 
                   class="flex flex-col items-center justify-center -mt-8 px-2">
                    <div class="w-14 h-14 bg-gradient-to-tr from-primary-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg shadow-primary-500/40 transform rotate-45 group transition-transform duration-300 active:scale-95">
                        <i class="fas fa-plus text-white text-xl -rotate-45"></i>
                    </div>
                    <span class="text-[10px] mt-3 text-white font-bold uppercase tracking-wider">Invest</span>
                </a>
                
                <!-- Publicités -->
                <a href="videos" 
                   class="flex flex-col items-center justify-center flex-1 transition-all duration-300 <?php echo $page == 'videos' ? 'text-primary-400' : 'text-gray-400 hover:text-white'; ?>">
                    <div class="relative">
                        <i class="fas fa-play-circle text-lg"></i>
                        <span class="absolute -top-1.5 -right-2 px-1.5 py-0.5 bg-red-500 text-white text-[8px] font-bold rounded-full border border-gray-900">3</span>
                    </div>
                    <span class="text-[10px] mt-1 font-semibold uppercase tracking-wider">Ads</span>
                </a>
                
                <!-- Profil -->
                <a href="profile" 
                   class="flex flex-col items-center justify-center flex-1 transition-all duration-300 <?php echo $page == 'profile' ? 'text-primary-400' : 'text-gray-400 hover:text-white'; ?>">
                    <div class="relative">
                        <i class="fas fa-user text-lg"></i>
                    </div>
                    <span class="text-[10px] mt-1 font-semibold uppercase tracking-wider">Profil</span>
                </a>
            </div>
        </nav>
    </div>

    <!-- Active Indicator Effect (CSS) -->
    <style>
        .nav-active-glow {
            filter: drop-shadow(0 0 8px rgba(14, 165, 233, 0.5));
        }
    </style>