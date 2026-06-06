<?php
// template_ajax.php - Template pour les pages avec AJAX
$page_title = $page_title ?? 'Titre de la page';
$page_description = $page_description ?? 'Description';
?>

<div class="p-4 lg:p-6">
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center mb-6 gap-4">
        <div>
            <h3 class="text-xl font-bold"><?= htmlspecialchars($page_title) ?></h3>
            <p class="text-gray-600"><?= htmlspecialchars($page_description) ?></p>
        </div>
        <?php if(isset($action_button)): ?>
            <?= $action_button ?>
        <?php endif; ?>
    </div>

    <!-- Zone de notifications AJAX -->
    <div id="page-notifications"></div>

    <!-- Contenu principal avec AJAX -->
    <div id="ajax-content" data-reload-url="<?= htmlspecialchars($reload_url ?? '') ?>">
        <?= $content ?? '' ?>
    </div>

    <!-- Modals AJAX -->
    <div id="ajax-modals"></div>
</div>

<!-- Script spécifique à la page -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les événements AJAX
    initAjaxEvents();
    
    // Charger les données initiales si nécessaire
    if (typeof loadInitialData === 'function') {
        loadInitialData();
    }
});

function initAjaxEvents() {
    // Gérer les formulaires AJAX
    document.querySelectorAll('form.ajax-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const action = this.dataset.action || formData.get('action');
            
            if (action) {
                const result = await adminAPI.request(action, Object.fromEntries(formData));
                if (result) {
                    // Fermer les modals
                    const modal = this.closest('.modal');
                    if (modal) {
                        modal.style.display = 'none';
                    }
                    
                    // Recharger le contenu si nécessaire
                    const reloadUrl = document.getElementById('ajax-content').dataset.reloadUrl;
                    if (reloadUrl) {
                        await adminAPI.updateTable(reloadUrl, 'ajax-content');
                    }
                }
            }
        });
    });
    
    // Gérer les boutons AJAX
    document.querySelectorAll('.ajax-button').forEach(button => {
        button.addEventListener('click', async function() {
            const action = this.dataset.action;
            const data = this.dataset.data ? JSON.parse(this.dataset.data) : {};
            
            if (action) {
                await adminAPI.request(action, data);
            }
        });
    });
}
</script>