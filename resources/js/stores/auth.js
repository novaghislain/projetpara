import { reactive } from 'vue';

/**
 * Récupère l'URL de base pour les appels API (utilisé par fetch()).
 * Lis la meta <meta name="base-url"> définie dans app.blade.php.
 * Si la meta n'existe pas, utilise l'origine de la page.
 */
function apiBaseUrl(path) {
    const meta = document.querySelector('meta[name=base-url]');
    const base = meta ? meta.content : window.location.origin;
    return base + path;
}

export const authStore = reactive({
    user: null,
    isAuthenticated: false,
    permissions: [],       // format: ["caisse:encaissement", "comptabilite:lire", ...]
    modules: [],           // format: ["caisse", "comptabilite", ...]
    permissionIds: [],     // IDs des permissions directes
    companies: [],         // Liste des entreprises disponibles [{id, company_name, rccm, ifu}]
    activeCompany: null,   // Entreprise actuellement sélectionnée
    fieldRestrictions: {}, // Restrictions de champs par module { module: ['field1', 'field2'] }

    // ─── Getters (via fonctions) ────────────────────────────────────

    get hasMultipleCompanies() {
        return this.companies?.length > 1;
    },

    get activeCompanyName() {
        return this.activeCompany?.company_name || '';
    },

    get isCompanyUser() {
        if (!this.isAuthenticated) return false;
        if (this.user?.is_super_admin) return false;
        if (this.user?.is_comptable) return false;
        return this.companies?.length > 0 || !!this.user?.active_client_id;
    },

    get isCompanyAdmin() {
        if (!this.isAuthenticated) return false;
        return !!(this.user?.is_company_admin || this.user?.role === 'company_admin');
    },

    get isSuperAdmin() {
        if (!this.isAuthenticated) return false;
        return !!(this.user?.is_super_admin || this.user?.role === 'super_admin');
    },

    get isComptable() {
        if (!this.isAuthenticated) return false;
        return !!(this.user?.is_comptable);
    },

    get isClient() {
        if (!this.isAuthenticated) return false;
        return !!(this.user?.is_client || this.user?.role === 'client');
    },

    get mustChangePassword() {
        return !!(this.user?.must_change_password);
    },

    // ─── Initialisation ─────────────────────────────────────────────

    init(data) {
        this.user = data.user || null;
        this.isAuthenticated = !!data.user;
        this.permissions = data.permissions || [];
        this.modules = data.modules || [];
        this.permissionIds = data.permission_ids || [];
        this.companies = data.companies || [];
        this.activeCompany = data.active_company || null;
    },

    /**
     * Initialisation complète depuis /api/me/profile.
     */
    async initFromApi() {
        try {
            const csrf = document.querySelector('meta[name=csrf-token]')?.content;
            const res = await fetch(apiBaseUrl('/api/me/profile'), {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
            });
            if (res.ok) {
                const data = await res.json();
                this.user = data.user || null;
                this.isAuthenticated = !!data.user;
                this.permissions = data.permissions || [];
                this.modules = data.modules || [];
                this.companies = data.companies || [];
                this.activeCompany = data.active_company || null;
            }
        } catch (e) {
            console.warn('Erreur chargement profil:', e);
        }
    },

    // ─── Vérifications d'accès ──────────────────────────────────────

    /**
     * Vérifie si l'utilisateur a accès à un module spécifique.
     */
    hasModule(module) {
        if (!this.isAuthenticated) return false;
        if (this.isSuperAdmin) return true;
        if (this.isCompanyAdmin) return true;
        return this.modules.includes(module);
    },

    /**
     * Vérifie si l'utilisateur a une action spécifique dans un module.
     */
    can(module, action) {
        if (!this.isAuthenticated) return false;
        if (this.isSuperAdmin) return true;
        if (this.isCompanyAdmin) return true;
        return this.permissions.includes(`${module}:${action}`);
    },

    // ─── Restrictions de champs ─────────────────────────────────────

    /**
     * Vérifie si un champ est caché pour l'utilisateur dans un module.
     */
    isFieldHidden(module, field) {
        const hidden = this.fieldRestrictions[module];
        if (!hidden || !Array.isArray(hidden)) return false;
        return hidden.includes(field);
    },

    /**
     * Recharge les restrictions de champs pour un module.
     */
    async loadFieldRestrictions(module) {
        try {
            const csrf = document.querySelector('meta[name=csrf-token]')?.content;
            const res = await fetch(apiBaseUrl(`/api/me/field-restrictions/${module}`), {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
            });
            if (res.ok) {
                const data = await res.json();
                this.fieldRestrictions[module] = data.hidden_fields || [];
            }
        } catch (e) {
            console.warn(`Erreur chargement restrictions ${module}:`, e);
        }
    },

    // ─── Contexte entreprise ────────────────────────────────────────

    /**
     * Basculer vers une autre entreprise.
     */
    async switchToCompany(clientId) {
        try {
            const csrf = document.querySelector('meta[name=csrf-token]')?.content;
            const res = await fetch(apiBaseUrl('/api/me/switch-context'), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ client_id: clientId })
            });
            if (res.ok) {
                const data = await res.json();
                this.user.active_client_id = data.active_client_id;
                this.activeCompany = this.companies.find(c => c.id === data.active_client_id) || null;
                // Recharger les permissions pour le nouveau contexte
                await this.refreshPermissions();
                window.location.reload();
                return true;
            }
        } catch (e) {
            console.warn('Erreur changement contexte:', e);
        }
        return false;
    },

    // ─── Permissions ────────────────────────────────────────────────

    /**
     * Recharge les permissions depuis l'API.
     */
    async refreshPermissions() {
        try {
            const csrf = document.querySelector('meta[name=csrf-token]')?.content;
            const res = await fetch(apiBaseUrl('/api/me/permissions'), {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
            });
            if (res.ok) {
                const data = await res.json();
                this.permissions = data.permissions || [];
                this.modules = data.modules || [];
                return true;
            }
        } catch (e) {
            console.warn('Erreur chargement permissions:', e);
        }
        return false;
    },

    /**
     * Vérifie si les permissions ont été modifiées (polling).
     */
    async checkForUpdates() {
        try {
            const csrf = document.querySelector('meta[name=csrf-token]')?.content;
            const res = await fetch(apiBaseUrl('/api/company/events/check'), {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
            });
            if (res.ok) {
                const data = await res.json();
                if (data.updated) {
                    this.permissions = data.permissions || [];
                    this.modules = data.modules || [];
                    this.permissionIds = data.permission_ids || [];
                    return true;
                }
            }
        } catch (e) {
            // Silencieux
        }
        return false;
    }
});

// ─── Variables de polling (privées) ─────────────────────────────
let _pollingInterval = null;

/**
 * Démarre le polling de vérification des permissions (toutes les 15s).
 */
export function startPermissionPolling() {
    if (_pollingInterval) return; // Déjà démarré

    async function poll() {
        const updated = await authStore.checkForUpdates();
        if (updated) {
            window.location.reload();
        }
    }

    _pollingInterval = setInterval(poll, 15000);
}

/**
 * Arrête le polling des permissions.
 */
export function stopPermissionPolling() {
    if (_pollingInterval) {
        clearInterval(_pollingInterval);
        _pollingInterval = null;
    }
}

/**
 * Initialise le store à partir du tag <script id="auth-data">.
 */
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
                // Tentative 2 : décoder les entités HTML
                const txt = document.createElement('textarea');
                txt.innerHTML = raw;
                data = JSON.parse(txt.value);
            }

            authStore.user = data.user;
            authStore.isAuthenticated = !!data.user;

            // Charger les permissions et le profil complet après l'init
            if (authStore.isAuthenticated) {
                authStore.initFromApi();
            }
        } catch (e) {
            console.warn('Failed to parse auth data:', e);
        }
    }
}
