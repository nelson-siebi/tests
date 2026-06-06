    <!-- JavaScript pour les interactions -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle dropdown utilisateur
            const userDropdownBtn = document.getElementById('userDropdownBtn');
            const userDropdown = document.getElementById('userDropdown');
            
            if (userDropdownBtn && userDropdown) {
                userDropdownBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userDropdown.classList.toggle('hidden');
                });
                
                // Fermer le dropdown en cliquant ailleurs
                document.addEventListener('click', function() {
                    userDropdown.classList.add('hidden');
                });
                
                // Empêcher la fermeture en cliquant dans le dropdown
                userDropdown.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
            
            // Animation de chargement des pages
            const mainContent = document.querySelector('main');
            if (mainContent) {
                mainContent.classList.add('page-transition');
            }
            
            // Highlight pour la navigation mobile
            const currentPage = '<?php echo $page; ?>';
            const navItems = document.querySelectorAll('.mobile-nav-item');
            
            navItems.forEach(item => {
                if (item.getAttribute('data-page') === currentPage) {
                    item.classList.add('active');
                }
            });
            
            // Notifications badge animation
            const notificationBadges = document.querySelectorAll('.notification-badge');
            notificationBadges.forEach(badge => {
                badge.addEventListener('click', function() {
                    this.style.transform = 'scale(0.9)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 150);
                });
            });
        });
        
        // Préchargement des pages pour une meilleure expérience
        const pages = ['home', 'profile', 'messages', 'settings'];
        pages.forEach(page => {
            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = `pages/${page}.php`;
            document.head.appendChild(link);
        });
    </script>
</body>
</html>