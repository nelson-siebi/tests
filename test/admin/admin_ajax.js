// admin_ajax.js - Gestion centralisée des requêtes AJAX

class AdminAPI {
    constructor() {
        this.baseUrl = 'ajax_controller.php';
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        this.setupGlobalErrorHandling();
    }

    // Configuration globale des erreurs
    setupGlobalErrorHandling() {
        // Intercepter les erreurs JavaScript
        window.addEventListener('error', (event) => {
            console.error('JavaScript Error:', event.error);
            this.showError('Une erreur JavaScript est survenue: ' + event.error.message);
        });

        // Intercepter les promesses non gérées
        window.addEventListener('unhandledrejection', (event) => {
            console.error('Unhandled Promise Rejection:', event.reason);
            this.showError('Une erreur asynchrone est survenue');
        });
    }

    // Fonction générique pour les requêtes AJAX
    async request(action, data = {}, method = 'POST') {
        try {
            // Ajouter l'action aux données
            const formData = new FormData();
            formData.append('action', action);
            
            // Ajouter les autres données
            for (const [key, value] of Object.entries(data)) {
                formData.append(key, value);
            }

            // Options de la requête
            const options = {
                method: method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            };

            // Ajouter CSRF token si disponible
            if (this.csrfToken) {
                options.headers['X-CSRF-Token'] = this.csrfToken;
            }

            // Afficher l'indicateur de chargement
            this.showLoading(true);

            // Exécuter la requête
            const response = await fetch(this.baseUrl, options);
            
            // Cacher l'indicateur de chargement
            this.showLoading(false);

            // Vérifier la réponse HTTP
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Parser la réponse JSON
            const result = await response.json();

            // Vérifier le succès de l'API
            if (!result.success) {
                this.showError(result.message || 'Une erreur est survenue');
                return null;
            }

            // Afficher le message de succès
            if (result.message) {
                this.showSuccess(result.message);
            }

            return result.data;

        } catch (error) {
            this.showLoading(false);
            this.showError(`Erreur de communication: ${error.message}`);
            console.error('API Error:', error);
            return null;
        }
    }

    // Fonctions spécifiques pour chaque action

    // Administrateurs
    async getAdmins() {
        return await this.request('get_admins', {}, 'GET');
    }

    async addAdmin(data) {
        return await this.request('add_admin', data);
    }

    async updateAdmin(data) {
        return await this.request('update_admin', data);
    }

    async deleteAdmin(adminId) {
        return await this.request('delete_admin', { admin_id: adminId });
    }

    // Utilisateurs
    async getUser(userId) {
        return await this.request('get_user', { id: userId }, 'GET');
    }

    async updateUserStatus(userId, status) {
        return await this.request('update_user_status', {
            user_id: userId,
            status: status
        });
    }

    // Investissements
    async addRoi(userPlanId, userId, amount, note = '') {
        return await this.request('add_roi', {
            user_plan_id: userPlanId,
            user_id: userId,
            amount: amount,
            note: note
        });
    }

    async terminateInvestment(investmentId) {
        return await this.request('terminate_investment', {
            investment_id: investmentId
        });
    }

    // Retraits
    async processWithdrawal(transactionId, actionType, note = '') {
        return await this.request('process_withdrawal', {
            transaction_id: transactionId,
            action_type: actionType,
            note: note
        });
    }

    // Validation KYC
    async validateInvestment(transactionId) {
        return await this.request('validate_investment', {
            transaction_id: transactionId
        });
    }

    // Statistiques
    async getStats() {
        return await this.request('get_stats', {}, 'GET');
    }

    // Fonctions d'interface utilisateur

    showLoading(show = true) {
        let loadingOverlay = document.getElementById('ajax-loading-overlay');
        
        if (!loadingOverlay && show) {
            loadingOverlay = document.createElement('div');
            loadingOverlay.id = 'ajax-loading-overlay';
            loadingOverlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
            `;
            
            const spinner = document.createElement('div');
            spinner.style.cssText = `
                width: 50px;
                height: 50px;
                border: 5px solid #f3f3f3;
                border-top: 5px solid #3498db;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            `;
            
            loadingOverlay.appendChild(spinner);
            document.body.appendChild(loadingOverlay);
            
            // Ajouter l'animation CSS
            const style = document.createElement('style');
            style.textContent = `
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
        }
        
        if (loadingOverlay) {
            loadingOverlay.style.display = show ? 'flex' : 'none';
        }
    }

    showSuccess(message) {
        this.showNotification(message, 'success');
    }

    showError(message) {
        this.showNotification(message, 'error');
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `ajax-notification ajax-notification-${type}`;
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} mr-2"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close">&times;</button>
        `;
        
        // Styles
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            background: ${type === 'success' ? '#d1fae5' : type === 'error' ? '#fee2e2' : '#dbeafe'};
            color: ${type === 'success' ? '#065f46' : type === 'error' ? '#7f1d1d' : '#1e40af'};
            border: 1px solid ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            z-index: 10000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-width: 300px;
            max-width: 400px;
            animation: slideIn 0.3s ease-out;
        `;
        
        // Animation CSS
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
        
        // Bouton de fermeture
        notification.querySelector('.notification-close').onclick = () => {
            notification.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => notification.remove(), 300);
        };
        
        // Auto-remove après 5 secondes
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
        
        document.body.appendChild(notification);
    }

    // Confirmation dialog
    confirmDialog(message, title = 'Confirmation') {
        return new Promise((resolve) => {
            const dialog = document.createElement('div');
            dialog.className = 'confirm-dialog-overlay';
            dialog.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 10000;
            `;
            
            const content = document.createElement('div');
            content.className = 'confirm-dialog';
            content.style.cssText = `
                background: white;
                padding: 30px;
                border-radius: 12px;
                max-width: 400px;
                width: 90%;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            `;
            
            content.innerHTML = `
                <h3 class="text-lg font-bold mb-4">${title}</h3>
                <p class="text-gray-600 mb-6">${message}</p>
                <div class="flex justify-end space-x-3">
                    <button class="px-4 py-2 border rounded-lg hover:bg-gray-100" id="cancelBtn">Annuler</button>
                    <button class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600" id="confirmBtn">Confirmer</button>
                </div>
            `;
            
            dialog.appendChild(content);
            document.body.appendChild(dialog);
            
            document.getElementById('cancelBtn').onclick = () => {
                dialog.remove();
                resolve(false);
            };
            
            document.getElementById('confirmBtn').onclick = () => {
                dialog.remove();
                resolve(true);
            };
        });
    }

    // Mise à jour des tables avec pagination
    async updateTable(url, tableContainerId, params = {}) {
        try {
            // Ajouter les paramètres à l'URL
            const queryParams = new URLSearchParams(params);
            const fullUrl = `${url}?${queryParams.toString()}`;
            
            const response = await fetch(fullUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) throw new Error('Erreur de chargement');
            
            const html = await response.text();
            document.getElementById(tableContainerId).innerHTML = html;
            
            // Réattacher les événements
            this.attachTableEvents();
            
        } catch (error) {
            this.showError('Erreur lors du chargement des données');
            console.error(error);
        }
    }

    // Attacher les événements aux tables
    attachTableEvents() {
        // Gérer les formulaires dans les tables
        document.querySelectorAll('table form').forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const formData = new FormData(form);
                const action = formData.get('action');
                
                if (await this.confirmDialog('Confirmer cette action ?')) {
                    await this.request(action, Object.fromEntries(formData));
                    
                    // Recharger la table si nécessaire
                    if (form.closest('.ajax-table-container')) {
                        const container = form.closest('.ajax-table-container');
                        const url = container.dataset.reloadUrl;
                        if (url) {
                            await this.updateTable(url, container.id);
                        }
                    }
                }
            });
        });
    }
}

// Initialiser l'API globale
window.adminAPI = new AdminAPI();

// Exposer les fonctions globales
window.showLoading = (show) => adminAPI.showLoading(show);
window.showError = (msg) => adminAPI.showError(msg);
window.showSuccess = (msg) => adminAPI.showSuccess(msg);
window.confirmAction = (msg) => adminAPI.confirmDialog(msg);