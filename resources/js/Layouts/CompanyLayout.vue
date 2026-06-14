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
    return authStore.user?.name?.charAt(0).toUpperCase() || 'E';
});

let stopPermissionPolling = null;

const sidebarLinks = computed(() => {
    const links = [
        { name: 'Tableau de bord', icon: 'bi-speedometer2', route: '/company/dashboard', key: 'company-dashboard', module: null },
        { name: 'Catalogue GEL', icon: 'bi-shop', route: '/nos-services', key: 'catalogue', module: null },
        { name: 'Mes Commandes', icon: 'bi-cart-check', route: '/mes-commandes', key: 'mes-commandes', module: null },
        { name: 'Mes Services', icon: 'bi-grid-3x3-gap', route: '/company/services', key: 'company-services', module: null },
        { name: 'Caisse', icon: 'bi-cash-stack', route: '/company/caisse', key: 'company-caisse', module: 'caisse' },
        { name: 'Documents (GED)', icon: 'bi-folder2-open', route: '/company/ged', key: 'company-ged', module: 'document' },
        { name: 'Comptabilité', icon: 'bi-calculator', route: '/company/accounting', key: 'company-accounting', module: 'comptabilite' },
        { name: 'Facturation', icon: 'bi-receipt', route: '/company/invoices', key: 'company-invoices', module: 'facturation' },
        { name: 'RH', icon: 'bi-people', route: '/company/hr', key: 'company-hr', module: 'rh' },
        { name: 'Juridique', icon: 'bi-file-earmark-text', route: '/company/legal', key: 'company-legal', module: 'juridique' },
        { name: 'Projets', icon: 'bi-kanban', route: '/company/projects', key: 'company-projects', module: 'projets' },
        { name: 'CRM', icon: 'bi-person-lines-fill', route: '/company/crm', key: 'company-crm', module: null },
        { name: 'Assistant IA', icon: 'bi-robot', route: '/company/ai', key: 'company-ai', module: null },
        { name: 'Notifications', icon: 'bi-bell', route: '/company/notifications', key: 'company-notifications', module: null },
        { name: 'Mon Profil', icon: 'bi-person', route: '/company/profile', key: 'company-profile', module: null },
    ];

    // Only company admins can manage users
    if (authStore.user?.is_company_admin) {
        const usersLink = { name: 'Utilisateurs', icon: 'bi-people-fill', route: '/company/users', key: 'company-users', module: null };
        // Insert after Dashboard
        links.splice(1, 0, usersLink);
    }

    // Filtrer : ce qui n'est pas activé n'existe pas
    return links.filter(link => {
        // Un simple client (sans client_id) ne voit que le catalogue, ses commandes et son profil
        if (authStore.user?.role === 'client' && !authStore.user?.client_id) {
            return ['catalogue', 'mes-commandes', 'company-profile'].includes(link.key);
        }

        if (!link.module) return true; // toujours visible (dashboard, profil, etc.)
        return authStore.hasModule(link.module);
    });
});

const pageKey = computed(() => {
    const path = window.location.pathname;
    if (path.startsWith('/company/dashboard') || path === '/company') return 'company-dashboard';
    if (path.startsWith('/company/services')) return 'company-services';
    if (path.startsWith('/company/users')) return 'company-users';
    if (path.startsWith('/company/profile')) return 'company-profile';
    if (path.startsWith('/company/ged')) return 'company-ged';
    if (path.startsWith('/company/caisse')) return 'company-caisse';
    if (path.startsWith('/company/accounting')) return 'company-accounting';
    if (path.startsWith('/company/invoices')) return 'company-invoices';
    if (path.startsWith('/company/hr')) return 'company-hr';
    if (path.startsWith('/company/legal')) return 'company-legal';
    if (path.startsWith('/company/projects')) return 'company-projects';
    if (path.startsWith('/company/crm')) return 'company-crm';
    if (path.startsWith('/company/ai')) return 'company-ai';
    if (path.startsWith('/company/notifications')) return 'company-notifications';
    return 'company-dashboard';
});

const isSidebarCollapsed = ref(false);

const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
};

const logout = async () => {
    const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
    try {
        const res = await fetch('/logout', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        });
        if (res.redirected || res.ok) {
            window.location.href = '/login';
        }
    } catch {
        const form = document.getElementById('logout-form');
        const input = document.getElementById('logout-csrf-token');
        if (input && csrfToken) input.value = csrfToken;
        if (form) form.submit();
    }
};

    // Carte page → module requis
    const pageModuleMap = {
        'company-caisse': 'caisse',
        'company-ged': 'document',
        'company-accounting': 'comptabilite',
        'company-invoices': 'facturation',
        'company-hr': 'rh',
        'company-legal': 'juridique',
        'company-projects': 'projets',
    };

onMounted(async () => {
    const clientId = window.__CLIENT_ID__;
    if (!clientId) { loading.value = false; return; }

    // Attendre que les permissions soient chargées
    if (!authStore.modules.length && authStore.isAuthenticated) {
        await authStore.refreshPermissions();
    }

    // Vérifier l'accès au module de la page courante
    const requiredModule = pageModuleMap[pageKey.value];
    if (requiredModule && !authStore.hasModule(requiredModule)) {
        // Règle "ce qui n'est pas activé n'existe pas" : rediriger
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
        // Démarrer le polling des permissions (temps réel)
        stopPermissionPolling = startPermissionPolling();
    } finally {
        loading.value = false;
    }
});

// ─── Notifications ─────────────────────────────────────────────
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
    <div class="d-flex" style="min-height: 100vh;">
        <!-- Sidebar Orange+Blanc -->
        <nav class="co-sidebar d-flex flex-column flex-shrink-0"
             :style="{ width: isSidebarCollapsed ? '60px' : '240px', transition: 'width 0.25s ease' }">

            <!-- Logo -->
            <div class="co-sidebar-logo d-flex align-items-center gap-2"
                 :class="{ 'justify-content-center': isSidebarCollapsed }">
                <div class="co-logo-box">
                    <i class="bi-building" style="font-size:16px;"></i>
                </div>
                <div v-if="!isSidebarCollapsed" class="text-truncate">
                    <div class="co-logo-text">{{ company?.company_name || 'Mon Entreprise' }}</div>
                    <div class="co-logo-sub">ESPACE CLIENT</div>
                </div>
            </div>

            <!-- Section label -->
            <div v-if="!isSidebarCollapsed" class="co-section-lbl px-3 pt-3 pb-1">Navigation</div>

            <!-- Nav -->
            <ul class="nav flex-column flex-grow-1 px-1 mt-1">
                <li v-for="link in sidebarLinks" :key="link.key">
                    <a :href="link.route"
                       class="co-nav-link d-flex align-items-center gap-2"
                       :class="{ 'co-nav-active': pageKey === link.key }"
                       :title="isSidebarCollapsed ? link.name : ''">
                        <i :class="link.icon" class="co-nav-icon flex-shrink-0"></i>
                        <span v-if="!isSidebarCollapsed" class="co-nav-label">{{ link.name }}</span>
                    </a>
                </li>
            </ul>

            <!-- Licences actives -->
            <div v-if="!isSidebarCollapsed && licenses.length" class="co-section-lbl px-3 pt-2 pb-1">
                <i class="bi-key me-1"></i>Services actifs
            </div>
            <ul v-if="!isSidebarCollapsed" class="nav flex-column mb-1 px-1">
                <li v-for="lic in licenses.slice(0,4)" :key="lic.id">
                    <span class="co-lic-item d-flex align-items-center gap-2">
                        <i class="bi-check-circle-fill flex-shrink-0"
                           :style="{ color: lic.valid ? '#FF7900' : '#e0e0e0', fontSize: '11px' }"></i>
                        <span class="text-truncate">{{ lic.service_name }}</span>
                        <span v-if="!lic.valid" class="co-lic-badge ms-auto">Exp.</span>
                    </span>
                </li>
            </ul>

            <!-- Toggle -->
            <div class="co-sidebar-foot px-2 py-3">
                <button @click="toggleSidebar"
                        class="co-toggle-btn w-100"
                        :class="{ 'justify-content-center': isSidebarCollapsed }"
                        :title="isSidebarCollapsed ? 'Étendre' : 'Réduire'">
                    <i :class="isSidebarCollapsed ? 'bi-chevron-double-right' : 'bi-chevron-double-left'"></i>
                    <span v-if="!isSidebarCollapsed" class="ms-2">Réduire</span>
                </button>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="d-flex flex-column flex-grow-1" style="min-width: 0;">

            <!-- Header Orange -->
            <header class="co-header d-flex align-items-center justify-content-between px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="co-header-logo">
                        <span>GEL</span>
                    </div>
                    <h5 class="mb-0 co-header-title">{{ pageTitle }}</h5>
                </div>
                <div class="d-flex align-items-center gap-2">

                    <!-- Notifications -->
                    <div class="dropdown" v-if="authStore.user">
                        <button class="co-icon-btn position-relative" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi-bell" style="font-size: 17px;"></i>
                            <span v-if="unreadCount > 0"
                                  class="position-absolute top-0 start-100 translate-middle badge rounded-pill"
                                  style="background:#fff; color:#FF7900; font-size:9px; padding:3px 5px;">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end co-notif-menu" style="width:360px;">
                            <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold" style="font-size:13px;">Notifications</h6>
                                <button v-if="unreadCount > 0" @click="markAllRead"
                                        class="btn btn-sm btn-link p-0 text-decoration-none" style="font-size:12px;color:#FF7900;">
                                    Tout marquer lu
                                </button>
                            </div>
                            <div style="max-height:320px; overflow-y:auto;">
                                <div v-if="notifications.length === 0" class="text-center py-4 text-muted small">
                                    Aucune notification
                                </div>
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
                                <a href="/company/notifications" class="co-notif-all-btn">Voir toutes les notifications</a>
                            </div>
                        </div>
                    </div>

                    <!-- User -->
                    <div class="dropdown">
                        <button class="co-user-btn d-flex align-items-center gap-2"
                                data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="co-user-avatar">{{ userInitial }}</div>
                            <div class="text-start d-none d-md-block">
                                <div class="co-user-name">{{ authStore.user?.name || 'Utilisateur' }}</div>
                                <div class="co-user-role">{{ authStore.user?.role_name || 'Utilisateur' }}</div>
                            </div>
                            <i class="bi-chevron-down" style="font-size:10px; color:rgba(255,255,255,0.6);"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end co-user-menu">
                            <li><a class="co-dd-item dropdown-item" href="/company/profile">
                                <i class="bi-person me-2"></i>Mon Profil
                            </a></li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a href="#" class="co-dd-item co-dd-danger dropdown-item" @click.prevent="logout">
                                    <i class="bi-box-arrow-right me-2"></i>Déconnexion
                                </a>
                                <form id="logout-form" method="POST" action="/logout" style="display:none;">
                                    <input type="hidden" name="_token" id="logout-csrf-token">
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Breadcrumb blanc sous header orange -->
            <div class="co-breadcrumb px-4 d-flex align-items-center justify-content-between">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size:12px;">
                        <li class="breadcrumb-item">
                            <a href="/company/dashboard" class="text-decoration-none" style="color:#FF7900;">Accueil</a>
                        </li>
                        <li class="breadcrumb-item active" style="color:#555;" aria-current="page">{{ pageTitle }}</li>
                    </ol>
                </nav>
                <div v-if="company" class="d-none d-md-block" style="font-size:11px; color:#999;">
                    <i class="bi-building me-1" style="color:#FF7900;"></i>{{ company.company_name }}
                </div>
            </div>

            <!-- Content -->
            <main class="flex-grow-1 p-4" style="overflow-y:auto; background:#fafafa;">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
/* ══ Palette Orange + Blanc ══════════════════════════════ */

/* ── Sidebar ─────────────────────────────────────────── */
.co-sidebar {
    background: #FFFFFF;
    border-right: 1px solid #FFE0B2;
    overflow: hidden;
}
.co-sidebar-logo {
    padding: 12px;
    border-bottom: 2px solid #FF7900;
    min-height: 56px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.co-logo-box {
    width: 34px; height: 34px;
    background: #FF7900; color: #fff;
    border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.co-logo-text {
    font-family: 'Outfit', sans-serif;
    font-weight: 800; font-size: 14px;
    color: #FF7900; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.co-logo-sub {
    font-size: 9px; font-weight: 700;
    color: #FFAB6E; letter-spacing: 0.1em;
}
.co-section-lbl {
    font-size: 10px; font-weight: 700;
    letter-spacing: 0.1em; text-transform: uppercase; color: #FFAB6E;
}
.co-nav-link {
    color: #555; padding: 9px 10px; margin: 1px 0;
    font-size: 13px; text-decoration: none;
    border-radius: 4px; border-left: 3px solid transparent;
    transition: all 0.15s; white-space: nowrap; overflow: hidden;
}
.co-nav-link:hover {
    color: #FF7900; background: #FFF3E0; border-left-color: #FFD0A0;
}
.co-nav-link.co-nav-active {
    color: #FF7900; background: #FFF3E0; border-left-color: #FF7900; font-weight: 700;
}
.co-nav-icon { font-size: 15px; width: 18px; text-align: center; }
.co-nav-label { font-size: 13px; overflow: hidden; text-overflow: ellipsis; }
.co-lic-item {
    padding: 5px 10px; font-size: 12px; color: #777;
}
.co-lic-badge {
    background: #FFE0B2; color: #e65100;
    font-size: 10px; padding: 1px 5px; border-radius: 2px;
}
.co-sidebar-foot { border-top: 1px solid #FFE0B2; }
.co-toggle-btn {
    display: flex; align-items: center;
    background: #FFF3E0; border: 1px solid #FFD0A0;
    color: #FF7900; font-size: 12px; font-weight: 600;
    padding: 7px 10px; border-radius: 4px; cursor: pointer; transition: all 0.15s;
}
.co-toggle-btn:hover { background: #FFE0B2; }

/* ── Header Orange ───────────────────────────────────── */
.co-header {
    background: #FF7900;
    height: 56px;
    border-bottom: 1px solid #e06700;
    flex-shrink: 0;
}
.co-header-logo {
    width: 34px; height: 34px;
    background: rgba(255,255,255,0.2);
    border: 1px solid rgba(255,255,255,0.35);
    border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.co-header-logo span {
    font-size: 12px; font-weight: 900; color: #fff; letter-spacing: -0.5px;
}
.co-header-title {
    font-family: 'Outfit', sans-serif;
    font-size: 15px; font-weight: 800; color: #fff;
}

/* ── Icon btn (notifications) ────────────────────────── */
.co-icon-btn {
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.35);
    border-radius: 4px; width: 36px; height: 36px;
    display: flex; align-items: center; justify-content: center;
    color: #fff; cursor: pointer; transition: background 0.15s;
}
.co-icon-btn:hover { background: rgba(255,255,255,0.25); }

/* ── User btn ────────────────────────────────────────── */
.co-user-btn {
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.35);
    border-radius: 4px; padding: 6px 12px; cursor: pointer; transition: background 0.15s;
}
.co-user-btn:hover { background: rgba(255,255,255,0.25); }
.co-user-avatar {
    width: 30px; height: 30px;
    background: #fff; color: #FF7900;
    font-weight: 800; font-size: 13px; border-radius: 4px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.co-user-name { font-size: 13px; font-weight: 700; color: #fff; line-height: 1.2; }
.co-user-role { font-size: 11px; color: rgba(255,255,255,0.7); line-height: 1.2; }

/* ── Breadcrumb ──────────────────────────────────────── */
.co-breadcrumb {
    background: #fff; height: 36px; border-bottom: 1px solid #FFE0B2; flex-shrink: 0;
}

/* ── Dropdowns ───────────────────────────────────────── */
.co-notif-menu { border-radius: 4px !important; border: 1px solid #FFE0B2 !important; }
.co-notif-all-btn {
    display: block; text-align: center; padding: 7px;
    background: #FFF3E0; color: #FF7900; border-radius: 4px;
    font-size: 12px; font-weight: 600; text-decoration: none;
}
.co-notif-all-btn:hover { background: #FFE0B2; color: #e06700; }
.co-user-menu { border-radius: 4px !important; border: 1px solid #FFE0B2 !important; min-width: 190px; }
.co-dd-item { font-size: 13px; padding: 9px 16px; color: #333; }
.co-dd-item:hover { background: #FFF3E0 !important; color: #FF7900 !important; }
.co-dd-danger { color: #e53935 !important; }
.co-dd-danger:hover { background: #fdecea !important; color: #e53935 !important; }

/* ── Deep overrides ──────────────────────────────────── */
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
:deep(.card) { border-radius: 4px !important; border: 1px solid #FFE0B2; box-shadow: 0 1px 4px rgba(255,121,0,0.06); }
:deep(.card-header) {
    background: #fff8f0; border-bottom: 1px solid #FFE0B2;
    font-size: 13px; font-weight: 700; padding: 10px 16px;
    border-radius: 4px 4px 0 0 !important; color: #FF7900;
}
:deep(.table) { font-size: 13px; }
:deep(.table thead th) {
    background: #FFF3E0; color: #FF7900; font-weight: 700;
    font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em;
    border-color: #FFD0A0; padding: 10px 12px;
}
:deep(.table td) { padding: 10px 12px; vertical-align: middle; border-color: #fff0e0; }
:deep(.table tbody tr:hover) { background: #FFF8F0; }
:deep(.badge) { border-radius: 4px !important; font-size: 11px; font-weight: 700; }
:deep(.form-control), :deep(.form-select) {
    border-radius: 4px !important; font-size: 13px; border: 1px solid #FFD0A0;
}
:deep(.form-control:focus), :deep(.form-select:focus) {
    border-color: #FF7900; box-shadow: 0 0 0 2px rgba(255,121,0,0.15);
}
:deep(.text-primary) { color: #FF7900 !important; }
:deep(.bg-primary) { background: #FF7900 !important; }
</style>
