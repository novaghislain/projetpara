import { reactive } from 'vue';

export const authStore = reactive({
    user: null,
    isAuthenticated: false,
    permissions: [],       // format: ["caisse:encaissement", "comptabilite:lire", ...]
    modules: [],           // format: ["caisse", "comptabilite", ...]
    permissionIds: [],     // IDs des permissions directes

    init(data) {
        this.user = data.user || null;
        this.isAuthenticated = !!data.user;
        this.permissions = data.permissions || [];
        this.modules = data.modules || [];
        this.permissionIds = data.permission_ids || [];
    },

    /**
     * Vérifie si l'utilisateur a accès à un module spécifique.
     */
    hasModule(module) {
        if (!this.isAuthenticated) return false;

        // Super Admin et Company Admin voient tout
        if (this.user?.is_super_admin) return true;
        if (this.user?.is_company_admin) return true;

        // Fallback robuste : role 'company_admin' en string
        if (this.user?.role === 'company_admin') return true;

        return this.modules.includes(module);
    },

    /**
     * Vérifie si l'utilisateur a une action spécifique dans un module.
     */
    can(module, action) {
        if (!this.isAuthenticated) return false;

        // Super Admin et Company Admin voient tout
        if (this.user?.is_super_admin) return true;
        if (this.user?.is_company_admin) return true;

        // Fallback robuste : role 'company_admin' en string
        if (this.user?.role === 'company_admin') return true;

        return this.permissions.includes(`${module}:${action}`);
    },

    /**
     * Recharge les permissions depuis l'API.
     */
    async refreshPermissions() {
        try {
            const csrf = document.querySelector('meta[name=csrf-token]')?.content;
            const res = await fetch('/api/me/permissions', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
            });
            if (res.ok) {
                const data = await res.json();
                this.init(data);
            }
        } catch (e) {
            console.warn('Erreur chargement permissions:', e);
        }
    },

    /**
     * Vérifie si les permissions ont été modifiées (polling SSE-like).
     */
    async checkForUpdates() {
        try {
            const csrf = document.querySelector('meta[name=csrf-token]')?.content;
            const res = await fetch('/api/company/events/check', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
            });
            if (res.ok) {
                const data = await res.json();
                if (data.updated) {
                    // Mettre à jour modules/permissions sans écraser l'utilisateur
                    this.permissions = data.permissions || [];
                    this.modules = data.modules || [];
                    this.permissionIds = data.permission_ids || [];
                    return true;
                }
            }
        } catch (e) {
            // Silencieux — le polling ne doit pas déranger
        }
        return false;
    }
});

export function initAuth() {
    const el = document.getElementById('auth-data');
    if (el) {
        try {
            let raw = el.textContent.trim();

            // Tentative 1 : parse direct
            let data;
            try {
                data = JSON.parse(raw);
            } catch (_) {
                // Tentative 2 : décoder les entités HTML ({{ }} dans Blade échappe " → &quot;)
                const txt = document.createElement('textarea');
                txt.innerHTML = raw;
                data = JSON.parse(txt.value);
            }

            authStore.user = data.user;
            authStore.isAuthenticated = !!data.user;

            // Charger les permissions immédiatement après l'init
            if (authStore.isAuthenticated) {
                authStore.refreshPermissions();
            }
        } catch (e) {
            console.warn('Failed to parse auth data:', e);
        }
    }
}

/**
 * Démarre le polling de vérification des permissions (toutes les 15s).
 * Appelé depuis CompanyLayout.vue
 */
export function startPermissionPolling() {
    let interval = null;
    function poll() {
        authStore.checkForUpdates().then(updated => {
            if (updated) {
                // Les permissions ont changé — recharger la page
                window.location.reload();
            }
        });
    }
    interval = setInterval(poll, 15000);
    return () => clearInterval(interval);
}
