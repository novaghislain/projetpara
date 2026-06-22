<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { authStore, startPermissionPolling } from '../stores/auth';

const props = defineProps({
    pageTitle: { type: String, default: 'Mon Espace' }
});

const company = ref(null);
const licenses = ref([]);
const loading = ref(true);

const userInitial = computed(() => {
    return authStore.user?.name?.charAt(0).toUpperCase() || 'U';
});

const userRoleLabel = computed(() => {
    return company.value?.name || 'Mon Espace';
});

const sidebarOpen = ref(true);
let stopPermissionPolling = null;

// ── Navigation principale ──
const navItems = [
    { name: 'Accueil',        icon: 'bi-house',          route: '/company/dashboard',    key: 'company-dashboard' },
    { name: 'Commandes',      icon: 'bi-cart-check',     route: '/mes-commandes',        key: 'mes-commandes' },
    { name: 'Finance',        icon: 'bi-cash-stack',     route: '/company/invoices',     key: 'company-invoices',  module: 'facturation' },
    { name: 'Comptabilité',   icon: 'bi-calculator',     route: '/company/accounting',   key: 'company-accounting', module: 'comptabilite' },
    { name: 'GED',            icon: 'bi-folder',          route: '/company/ged',          key: 'company-ged',       module: 'document' },
    { name: 'Secrétariat',    icon: 'bi-file-text',      route: '/company/dae',          key: 'company-dae',       module: 'dae' },
    { name: 'RH',             icon: 'bi-people',         route: '/company/rh',           key: 'company-rh',        module: 'rh' },
    { name: 'Administration', icon: 'bi-gear',           route: '/company/profile',      key: 'company-profile' },
];

const filteredNavItems = computed(() =>
    navItems.filter(t => !t.module || authStore.hasModule(t.module))
);

/* ── Module requirements for sidebar items ── */
const sidebarModuleMap = {
    '/company/ged':        'document',
    '/company/caisse':     'caisse',
    '/company/invoices':   'facturation',
    '/company/rh':         'rh',
    '/company/accounting': 'comptabilite',
    '/company/legal':      'juridique',
    '/company/projects':   'projets',
    '/company/dae':        'dae',
};

function hasSidebarAccess(href) {
    const mod = sidebarModuleMap[href];
    return !mod || authStore.hasModule(mod);
}

// ── Sous-fonctionnalités contextuelles ──
const sidebarBySection = {
    'company-dashboard': [
        { group: '', items: [
            { label: 'Mes commandes',     href: '/mes-commandes',       icon: 'bi-cart-check' },
            { label: 'Catalogue GEL',     href: '/nos-services',       icon: 'bi-shop' },
            { label: 'Notifications',     href: '/company/notifications', icon: 'bi-bell' },
            { label: 'Services actifs',   href: '/company/services',   icon: 'bi-grid-3x3-gap' },
            { label: 'Documents (GED)',   href: '/company/ged',        icon: 'bi-folder2-open' },
        ]},
    ],
    'mes-commandes': [
        { group: '', items: [
            { label: 'Mes commandes',     href: '/mes-commandes',       icon: 'bi-cart-check' },
            { label: 'Catalogue GEL',     href: '/nos-services',       icon: 'bi-shop' },
            { label: 'Suivi des demandes',href: '/company/notifications', icon: 'bi-envelope' },
        ]},
    ],
    'company-invoices': [
        { group: '', items: [
            { label: 'Facturation',       href: '/company/invoices',   icon: 'bi-receipt' },
            { label: 'Caisse',            href: '/company/caisse',     icon: 'bi-cash-stack' },
        ]},
    ],
    'company-accounting': [
        { group: '', items: [
            { label: 'Comptabilité',      href: '/company/accounting', icon: 'bi-calculator' },
            { label: 'Facturation',       href: '/company/invoices',   icon: 'bi-receipt' },
        ]},
    ],
    'company-ged': [
        { group: '', items: [
            { label: 'Documents (GED)',   href: '/company/ged',        icon: 'bi-folder2-open' },
        ]},
    ],
    'company-profile': [
        { group: '', items: [
            { label: 'Mon Profil',        href: '/company/profile',        icon: 'bi-person-circle' },
            { label: 'Notifications',     href: '/company/notifications',  icon: 'bi-bell' },
            { label: 'Utilisateurs',      href: '/company/users',          icon: 'bi-people-fill' },
        ]},
    ],
    'company-dae': [
        { group: '', items: [
            { label: 'Tableau de bord',   href: '/company/dae',            icon: 'bi-speedometer2' },
            { label: 'Courriers',         href: '/company/dae/courriers',  icon: 'bi-envelope' },
            { label: 'Documents',         href: '/company/dae/documents',  icon: 'bi-folder' },
            { label: 'Contrats',          href: '/company/dae/contrats',   icon: 'bi-file-text' },
            { label: 'Tâches',            href: '/company/dae/taches',     icon: 'bi-list-task' },
        ]},
    ],
    'company-rh': [
        { group: '', items: [
            { label: 'Tableau de bord',   href: '/company/rh',              icon: 'bi-speedometer2' },
            { label: 'Employés',          href: '/company/rh/employees',    icon: 'bi-people' },
            { label: 'Congés',            href: '/company/rh/leaves',       icon: 'bi-calendar-check' },
            { label: 'Notes de frais',    href: '/company/rh/expenses',     icon: 'bi-cash-stack' },
            { label: 'Paie',              href: '/company/rh/payrolls',     icon: 'bi-calculator' },
            { label: 'Formations',        href: '/company/rh/trainings',    icon: 'bi-book' },
        ]},
    ],
};

const sidebarLinks = computed(() => {
    const raw = sidebarBySection[pageKey.value] || sidebarBySection['company-dashboard'];
    return raw.map(group => ({
        ...group,
        items: group.items.filter(item => hasSidebarAccess(item.href))
    })).filter(group => group.items.length > 0);
});

const flatLinks = computed(() =>
    sidebarLinks.value.flatMap(g => g.items)
);

const sectionTitles = {
    'company-dashboard': 'Accueil',
    'mes-commandes': 'Commandes',
    'company-invoices': 'Finance',
    'company-accounting': 'Comptabilité',
    'company-ged': 'GED',
    'company-dae': 'Secrétariat DAE',
    'company-rh': 'Ressources Humaines',
    'company-profile': 'Administration',
};

const pageKey = computed(() => {
    const path = window.location.pathname;
    if (path.startsWith('/company/dashboard') || path === '/company') return 'company-dashboard';
    if (path.startsWith('/company/services')) return 'company-dashboard';
    if (path.startsWith('/company/users')) return 'company-profile';
    if (path.startsWith('/company/profile')) return 'company-profile';
    if (path.startsWith('/company/ged')) return 'company-ged';
    if (path.startsWith('/company/caisse')) return 'company-dashboard';
    if (path.startsWith('/company/accounting')) return 'company-accounting';
    if (path.startsWith('/company/invoices')) return 'company-invoices';
    if (path.startsWith('/company/rh')) return 'company-rh';
    if (path.startsWith('/company/legal')) return 'company-ged';
    if (path.startsWith('/company/projects')) return 'company-ged';
    if (path.startsWith('/company/crm')) return 'company-ged';
    if (path.startsWith('/company/ai')) return 'company-ged';
    if (path.startsWith('/company/notifications')) return 'company-profile';
    if (path.startsWith('/company/dae')) return 'company-dae';
    if (path.startsWith('/mes-commandes')) return 'mes-commandes';
    if (path.startsWith('/nos-services')) return 'mes-commandes';
    return 'company-dashboard';
});

const getBaseUrl = () => {
    if (typeof document === 'undefined') return '';
    const meta = document.querySelector('meta[name="base-url"]');
    let url = meta ? meta.content : '';
    return url.endsWith('/') ? url.slice(0, -1) : url;
};

const buildUrl = (path) => {
    if (!path) return '#';
    if (path.startsWith('http')) return path;
    const base = getBaseUrl();
    return base + (path.startsWith('/') ? path : '/' + path);
};

const logout = async () => {
    try {
        await window.axios.post('/logout');
        window.location.href = '/login';
    } catch (e) {
        console.error('Logout failed with Axios, falling back to form submit:', e);
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
        const form = document.getElementById('logout-form');
        const input = document.getElementById('logout-csrf-token');
        if (input && csrfToken) input.value = csrfToken;
        if (form) {
            form.submit();
        }
    }
};

// Carte page → module requis
const pageModuleMap = {
    'company-caisse': 'caisse',
    'company-ged': 'document',
    'company-accounting': 'comptabilite',
    'company-invoices': 'facturation',
    'company-rh': 'rh',
    'company-legal': 'juridique',
    'company-projects': 'projets',
};

onMounted(async () => {
    const clientId = window.__CLIENT_ID__;
    if (!clientId) { loading.value = false; return; }

    if (!authStore.modules.length && authStore.isAuthenticated) {
        await authStore.refreshPermissions();
    }

    const requiredModule = pageModuleMap[pageKey.value];
    if (requiredModule && !authStore.hasModule(requiredModule)) {
        window.location.href = '/company/dashboard';
        return;
    }

    try {
        const res = await fetch(`/api/company/${clientId}/info`);
        const data = await res.json();
        company.value = data.company;
        licenses.value = data.licenses;
    } catch (e) {
        console.error('Erreur chargement entreprise', e);
    } finally {
        loading.value = false;
    }

    stopPermissionPolling = startPermissionPolling();
});

// ─── Notifications ─────────────────────────────
const notifications = ref([]);
const unreadCount = ref(0);
let pollInterval = null;

function notifIcon(type) {
    const icons = { info: 'bi-info-circle text-primary', success: 'bi-check-circle text-success', warning: 'bi-exclamation-triangle text-warning', error: 'bi-x-circle text-danger' };
    return 'bi ' + (icons[type] || 'bi-bell text-secondary');
}

function relativeDate(dateStr) {
    if (!dateStr) return '';
    const diff = Date.now() - new Date(dateStr).getTime();
    const mins = Math.floor(diff / 60000);
    if (mins < 1) return "à l'instant";
    if (mins < 60) return 'il y a ' + mins + ' min';
    const hours = Math.floor(mins / 60);
    if (hours < 24) return 'il y a ' + hours + ' h';
    const days = Math.floor(hours / 24);
    return 'il y a ' + days + ' j';
}

async function fetchNotifications() {
    const token = document.querySelector('meta[name=csrf-token]')?.content;
    try {
        const res = await fetch('/api/company/notifications?limit=5', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token }
        });
        const data = await res.json();
        notifications.value = data.notifications || [];
        unreadCount.value = data.unread_count || 0;
    } catch(e) { console.error('Notif error', e); }
}

async function markRead(n) {
    if (n.read_at) return;
    const token = document.querySelector('meta[name=csrf-token]')?.content;
    try {
        await fetch('/api/company/notifications/' + n.id + '/read', {
            method: 'PATCH', headers: { 'X-CSRF-TOKEN': token }
        });
        n.read_at = new Date().toISOString();
        unreadCount.value = Math.max(0, unreadCount.value - 1);
    } catch(e) { console.error('Mark read error', e); }
}

async function markAllRead() {
    const token = document.querySelector('meta[name=csrf-token]')?.content;
    try {
        await fetch('/api/company/notifications/read-all', {
            method: 'PATCH', headers: { 'X-CSRF-TOKEN': token }
        });
        notifications.value.forEach(n => n.read_at = n.read_at || new Date().toISOString());
        unreadCount.value = 0;
    } catch(e) { console.error('Mark all read error', e); }
}

onMounted(() => {
    fetchNotifications();
    pollInterval = setInterval(fetchNotifications, 30000);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
    if (stopPermissionPolling) stopPermissionPolling();
});
</script>

<template>
    <div class="g-shell d-flex flex-column" style="min-height:100vh; background:#f0f4f8;">

        <!-- ═══ TOP BAR — Logo + User ═══ -->
        <header class="g-topbar d-flex align-items-center justify-content-between px-3">
            <div class="d-flex align-items-center gap-2">
                <div class="g-logo-icon"><i class="bi-gem"></i></div>
                <span class="g-logo-text">Portail Client — GEL</span>
            </div>

            <div class="d-flex align-items-center gap-2">
                <div class="dropdown" v-if="authStore.user">
                    <button class="g-icon-btn position-relative" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi-bell" style="font-size:16px;"></i>
                        <span v-if="unreadCount > 0"
                              class="position-absolute top-0 start-100 translate-middle badge rounded-pill"
                              style="background:#FF7900; color:#fff; font-size:9px; padding:2px 5px;">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end g-dropdown" style="width:360px;">
                        <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold" style="font-size:13px;">Notifications</h6>
                            <button v-if="unreadCount > 0" @click="markAllRead"
                                    class="btn btn-sm btn-link p-0 text-decoration-none" style="font-size:12px;color:#FF7900;">
                                Tout marquer lu
                            </button>
                        </div>
                        <div style="max-height:320px; overflow-y:auto;">
                            <div v-if="notifications.length === 0" class="text-center py-4 text-muted small">Aucune notification</div>
                            <a v-for="n in notifications" :key="n.id" href="#"
                               @click.prevent="markRead(n)" class="dropdown-item px-3 py-2"
                               :style="{ background: !n.read_at ? '#fff8f0' : '' }">
                                <div class="d-flex gap-2">
                                    <i :class="notifIcon(n.type)" class="mt-1" style="font-size:15px;"></i>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold" style="font-size:12px;">{{ n.title }}</div>
                                        <div class="text-muted text-truncate" style="font-size:12px;">{{ n.message }}</div>
                                        <div class="text-muted" style="font-size:10px;">{{ relativeDate(n.created_at) }}</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="border-top px-3 py-2">
                            <a href="/company/notifications" class="g-notif-all">Voir toutes les notifications</a>
                        </div>
                    </div>
                </div>

                <span class="g-role-badge d-none d-sm-inline">{{ userRoleLabel }}</span>
                <button class="g-toggle-btn" @click="sidebarOpen = !sidebarOpen" title="Afficher/Masquer le panneau latéral">
                    <i :class="sidebarOpen ? 'bi-x-lg' : 'bi-list'"></i>
                </button>
                <div class="dropdown">
                    <button class="g-user-btn d-flex align-items-center gap-2"
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="g-avatar">{{ userInitial }}</div>
                        <i class="bi-chevron-down" style="font-size:9px; color:rgba(255,255,255,0.6);"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end g-dropdown">
                        <li>
                            <div class="g-dd-user px-3 py-2 border-bottom">
                                <div class="fw-bold" style="font-size:13px; color:#163A5E;">{{ company?.name }} — {{ authStore.user?.name }}</div>
                                <div style="font-size:11px; color:#888;">{{ authStore.user?.email }}</div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- ═══ MAIN LAYOUT: Sidebar + Contenu ═══ -->
        <div class="g-body d-flex flex-grow-1" style="min-height:0;">

            <!-- Sidebar : Navigation principale -->
            <aside class="g-sidebar d-flex flex-column flex-shrink-0"
                   :class="{ 'g-sidebar-closed': !sidebarOpen }">
                <div class="gs-nav">
                    <a v-for="item in filteredNavItems" :key="item.key"
                       :href="item.route"
                       class="gs-nav-item"
                       :class="{ 'gs-nav-active': pageKey === item.key }">
                        <i :class="item.icon" class="gs-nav-icon"></i>
                        <span class="gs-nav-label">{{ item.name }}</span>
                    </a>
                </div>

                <!-- ══ BAS DE SIDEBAR — Compte ══ -->
                <div class="gs-sidebar-divider"></div>
                <div class="gs-sidebar-bottom">
                    <a href="/company/profile" class="gs-nav-item">
                        <i class="bi-gear gs-nav-icon"></i>
                        <span class="gs-nav-label">Paramètres</span>
                    </a>
                    <a href="#" class="gs-nav-item gs-nav-logout" @click.prevent="logout">
                        <i class="bi-box-arrow-right gs-nav-icon"></i>
                        <span class="gs-nav-label">Déconnexion</span>
                    </a>
                </div>
            </aside>

            <!-- Overlay mobile -->
            <div v-if="sidebarOpen" class="g-overlay d-lg-none" @click="sidebarOpen = false"></div>

            <!-- Contenu principal -->
            <main class="g-main flex-grow-1 d-flex flex-column" style="min-width:0;">
                <!-- Barre des sous-fonctionnalités -->
                <div class="gs-subnav" v-if="flatLinks.length">
                    <div class="gs-subnav-title">{{ sectionTitles[pageKey] || '' }}</div>
                    <div class="gs-subnav-links">
                        <a v-for="link in flatLinks" :key="link.label"
                           :href="link.href"
                           class="gs-subnav-link">
                            <i :class="link.icon"></i>
                            {{ link.label }}
                        </a>
                    </div>
                </div>
                <!-- Contenu de la page -->
                <div class="flex-grow-1 p-4" style="min-height:0;">
                    <slot />
                </div>
            </main>
        </div>
    </div>

    <form id="logout-form" method="POST" action="/logout" style="display:none;">
        <input type="hidden" name="_token" id="logout-csrf-token">
    </form>
</template>

<style scoped>
/* ══ PORTAL CLIENT LAYOUT — Sidebar verticale + Subnav ══ */

/* ── Top bar simplifiée ─────────────────────── */
.g-topbar {
    background: #163A5E;
    height: 52px;
    flex-shrink: 0;
    border-bottom: 2px solid #FF7900;
    box-shadow: 0 2px 6px rgba(22,58,94,0.12);
    position: sticky;
    top: 0;
    z-index: 1030;
}
.g-logo-icon {
    width: 30px; height: 30px;
    background: #FF7900; color: #fff;
    border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; flex-shrink: 0;
}
.g-logo-text {
    font-family: 'Outfit', sans-serif;
    font-weight: 800; font-size: 15px; color: #fff;
}
.g-toggle-btn {
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.15);
    color: rgba(255,255,255,0.7);
    border-radius: 4px;
    width: 30px; height: 30px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 14px;
    transition: all 0.15s;
}
.g-toggle-btn:hover {
    background: rgba(255,255,255,0.15);
    color: #fff;
}

/* ── User / Notifs ───────────────────────── */
.g-icon-btn {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 4px; width: 32px; height: 32px;
    display: flex; align-items: center; justify-content: center;
    color: rgba(255,255,255,0.8); cursor: pointer;
    transition: background 0.15s;
}
.g-icon-btn:hover { background: rgba(255,255,255,0.2); color: #fff; }
.g-role-badge {
    font-size: 9px; font-weight: 800; letter-spacing: 0.06em;
    text-transform: uppercase;
    background: rgba(255,121,0,0.2);
    color: #FF7900;
    padding: 3px 8px; border-radius: 3px;
}
.g-user-btn {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 4px; padding: 3px 8px; cursor: pointer;
    transition: background 0.15s;
}
.g-user-btn:hover { background: rgba(255,255,255,0.18); }
.g-avatar {
    width: 28px; height: 28px;
    background: #fff; color: #163A5E;
    font-weight: 800; font-size: 11px;
    border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
}

/* ── Dropdowns ───────────────────────────── */
.g-dropdown { border-radius: 4px !important; min-width: 210px; margin-top: 6px; border: 1px solid #dce3ee !important; box-shadow: 0 4px 16px rgba(0,0,0,0.12) !important; }
.g-dd-user { background: #f8fbff; }
.g-dd-item { font-size: 13px; padding: 9px 16px; color: #333; }
.g-dd-item:hover { background: #FFF3E0 !important; color: #FF7900 !important; }
.g-dd-danger { color: #e53935 !important; }
.g-dd-danger:hover { background: #fdecea !important; color: #e53935 !important; }
.g-notif-all {
    display: block; text-align: center; padding: 7px;
    background: #FFF3E0; color: #FF7900; border-radius: 4px;
    font-size: 12px; font-weight: 600; text-decoration: none;
}
.g-notif-all:hover { background: #FFE0B2; color: #e06700; }

/* ── Sidebar ──────────────────────────────────── */
.g-body { position: relative; }
.g-sidebar {
    width: 240px;
    background: #1a2938;
    border-right: 1px solid #1e3244;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    transition: width 0.2s ease, opacity 0.2s ease;
    flex-shrink: 0;
}
.g-sidebar-closed {
    width: 0;
    overflow: hidden;
    opacity: 0;
    border-right: none;
    padding: 0;
}

/* ── Navigation principale ────────────────────── */
.gs-nav {
    padding: 8px 0;
    flex: 1;
}
.gs-nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    margin: 1px 8px;
    font-size: 13px;
    font-weight: 600;
    color: rgba(255,255,255,0.75);
    text-decoration: none;
    border-radius: 6px;
    border-left: 3px solid transparent;
    transition: all 0.12s;
    white-space: nowrap;
}
.gs-nav-item:hover {
    color: #fff;
    background: rgba(255,255,255,0.08);
}
.gs-nav-active {
    color: #fff !important;
    background: rgba(255,121,0,0.15);
    border-left-color: #FF7900;
}
.gs-nav-icon {
    font-size: 16px;
    width: 20px;
    text-align: center;
    flex-shrink: 0;
}
.gs-nav-active .gs-nav-icon { color: #FF7900; }
.gs-nav-label { line-height: 1; }

/* ── Bas de sidebar — Compte ─────────────── */
.gs-sidebar-divider {
    height: 1px;
    background: rgba(255,255,255,0.08);
    margin: 4px 16px 2px;
}
.gs-sidebar-bottom {
    padding: 2px 0 8px;
}
.gs-nav-logout {
    color: rgba(231, 76, 60, 0.7) !important;
}
.gs-nav-logout:hover {
    color: #e74c3c !important;
    background: rgba(231, 76, 60, 0.12) !important;
}

/* ── Subnav (sous-fonctionnalités en haut du contenu) ── */
.gs-subnav {
    background: #fff;
    border-bottom: 1px solid #dce3ee;
    padding: 10px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    flex-shrink: 0;
}
.gs-subnav-title {
    font-family: 'Outfit', sans-serif;
    font-size: 16px;
    font-weight: 700;
    color: #163A5E;
    white-space: nowrap;
    margin-right: 8px;
}
.gs-subnav-links {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
}
.gs-subnav-group {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #aaa;
    margin: 0 4px 0 8px;
    white-space: nowrap;
}
.gs-subnav-link {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    font-size: 12px;
    font-weight: 600;
    color: #555;
    text-decoration: none;
    border-radius: 5px;
    border: 1px solid transparent;
    transition: all 0.12s;
    white-space: nowrap;
}
.gs-subnav-link:hover {
    color: #FF7900;
    background: #FFF3E0;
    border-color: #FFE0B2;
}
.gs-subnav-link i {
    font-size: 12px;
    color: #999;
}
.gs-subnav-link:hover i { color: #FF7900; }

/* ── Main ────────────────────────────────── */
.g-main { background: #f0f4f8; min-width: 0; }

/* ── Mobile overlay ──────────────────────── */
.g-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    z-index: 1040;
}

/* ── Deep overrides ──────────────────── */
:deep(.btn) { border-radius: 4px !important; font-size: 13px; }
:deep(.btn-primary) {
    background: #FF7900 !important; border-color: #FF7900 !important;
    color: #fff !important; font-weight: 700 !important;
}
:deep(.btn-primary:hover) { background: #e06700 !important; border-color: #e06700 !important; }
:deep(.btn-outline-primary) { color: #FF7900 !important; border-color: #FF7900 !important; }
:deep(.btn-outline-primary:hover) { background: #FF7900 !important; color: #fff !important; }
:deep(.btn-outline-secondary) { color: #888 !important; border-color: #ddd !important; }
:deep(.btn-outline-secondary:hover) { background: #f5f5f5 !important; color: #555 !important; }
:deep(.card) { border-radius: 6px !important; border: 1px solid #dce3ee; box-shadow: 0 1px 4px rgba(22,58,94,0.06); }
:deep(.card-header) {
    background: linear-gradient(90deg, #163A5E, #1e4d7a);
    border-bottom: none; font-size: 13px; font-weight: 700;
    padding: 10px 16px; border-radius: 6px 6px 0 0 !important; color: #fff;
}
:deep(.table) { font-size: 13px; }
:deep(.table thead th) {
    background: #EEF3F9; color: #163A5E; font-weight: 700;
    font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em;
    border-color: #dce3ee; padding: 10px 12px;
}
:deep(.table td) { padding: 10px 12px; border-color: #f0f4f8; }
:deep(.table tbody tr:hover) { background: #f8fbff; }
:deep(.badge) { border-radius: 4px !important; font-size: 11px; font-weight: 700; }
:deep(.form-control), :deep(.form-select) {
    border-radius: 4px !important; font-size: 13px; border: 1px solid #dce3ee;
}
:deep(.form-control:focus), :deep(.form-select:focus) {
    border-color: #FF7900; box-shadow: 0 0 0 2px rgba(255,121,0,0.15);
}
:deep(.text-primary) { color: #FF7900 !important; }
:deep(.bg-primary) { background: #FF7900 !important; }
:deep(.border-primary) { border-color: #FF7900 !important; }
:deep(.progress-bar) { background: #FF7900; }

/* ══ RESPONSIVE ══ */
@media (max-width: 991.98px) {
    .g-topbar { padding-left: 10px !important; padding-right: 10px !important; }
    .g-main > .p-4 { padding: 12px !important; }
    .g-sidebar {
        position: fixed;
        left: 0;
        top: 52px;
        bottom: 0;
        z-index: 1050;
        box-shadow: 4px 0 20px rgba(0,0,0,0.3);
        width: 260px;
    }
    .g-sidebar-closed {
        width: 0 !important;
        transform: translateX(-100%);
        opacity: 1;
    }
}
@media (max-width: 575.98px) {
    .g-topbar { height: 48px; }
    .g-main > .p-4 { padding: 8px !important; }
    .g-role-badge { display: none; }
    .gs-subnav { padding: 8px 12px; gap: 8px; }
    .gs-subnav-title { font-size: 14px; }
}
</style>
