document.querySelectorAll('.nav-item').forEach(link => {
    link.addEventListener('click', function(e) {
        const page = new URL(this.href).searchParams.get('page');
        
        // Option 1 : recharger la page (simple, déjà fonctionnel)
        // → rien à faire, le lien HTML suffit

        // Option 2 : charger en AJAX (plus avancé) → à implémenter si besoin
    });
});