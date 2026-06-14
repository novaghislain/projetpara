<script setup>
import { ref, computed } from 'vue';
import { authStore } from '../stores/auth';

const props = defineProps({
    pageTitle: { type: String, default: 'GEL Cabinet' }
});

const isSidebarCollapsed = ref(false);

const sidebarLinks = [
    { name: 'Tableau de bord', icon: 'bi-speedometer2', route: '/dashboard', key: 'gel-dashboard' },
    { name: 'Clients',         icon: 'bi-building',     route: '/clients',   key: 'gel-clients' },
    { name: 'Pôles',           icon: 'bi-diagram-3',    route: '/poles',     key: 'gel-poles' },
    { name: 'Missions',        icon: 'bi-check2-square',route: '/missions',  key: 'gel-missions' },
    { name: 'Services',        icon: 'bi-grid-3x3-gap', route: '/services',  key: 'gel-services' },
    { name: 'Licences',        icon: 'bi-key',          route: '/licenses',  key: 'gel-licenses' },
    { name: 'Admins Entreprise',icon:'bi-person-badge', route: '/company-admins', key: 'gel-company-admins' },
    { name: 'Demandes',        icon: 'bi-envelope',     route: '/admin/requests', key: 'gel-requests' },
];

const erpLinks = [
    { name: 'Cmds Services', icon: 'bi-cart-check',  route: '/admin/catalogue/orders',          key: 'erp-orders' },
    { name: 'Archives Cmds', icon: 'bi-archive',     route: '/admin/catalogue/orders/archives', key: 'erp-archives' },
    { name: 'Catalogue',     icon: 'bi-tags',        route: '/admin/catalogue/services',        key: 'erp-catalogue' },
    { name: 'Stocks',        icon: 'bi-boxes',       route: '/erp/stocks',                      key: 'erp-stock' },
    { name: 'Facturation',   icon: 'bi-receipt',     route: '/erp/invoices',                    key: 'erp-invoice' },
    { name: 'RH & Paie',    icon: 'bi-people',      route: '/erp/hr',                          key: 'erp-hr' },
    { name: 'Trésorerie',    icon: 'bi-cash-stack',  route: '/erp/treasury',                    key: 'erp-treasury' },
];

const pageKey = computed(() => {
    const path = window.location.pathname;
    if (path.startsWith('/clients'))        return 'gel-clients';
    if (path.startsWith('/poles'))          return 'gel-poles';
    if (path.startsWith('/missions'))       return 'gel-missions';
    if (path.startsWith('/services'))       return 'gel-services';
    if (path.startsWith('/dossiers'))       return 'gel-dossiers';
    if (path.startsWith('/documents'))      return 'gel-documents';
    if (path.startsWith('/accounting'))     return 'gel-clients';
    if (path.startsWith('/admin/catalogue/orders/archives')) return 'erp-archives';
    if (path.startsWith('/admin/catalogue/orders'))          return 'erp-orders';
    if (path.startsWith('/admin/catalogue/services'))        return 'erp-catalogue';
    if (path.startsWith('/erp/stocks'))     return 'erp-stock';
    if (path.startsWith('/erp/invoices'))   return 'erp-invoice';
    if (path.startsWith('/erp/hr'))         return 'erp-hr';
    if (path.startsWith('/erp/treasury'))   return 'erp-treasury';
    if (path.startsWith('/licenses'))       return 'gel-licenses';
    if (path.startsWith('/company-admins')) return 'gel-company-admins';
    if (path.startsWith('/admin/requests')) return 'gel-requests';
    return 'gel-dashboard';
});

const userRoleLabel = computed(() => {
    const labels = {
        super_admin: 'Super Admin',
        director: 'Directeur',
        pole_responsible: 'Responsable Pôle',
        collaborator: 'Collaborateur'
    };
    return labels[authStore.user?.role] || 'Collaborateur';
});

const userInitial = computed(() => authStore.user?.name?.charAt(0).toUpperCase() || 'U');
const toggleSidebar = () => { isSidebarCollapsed.value = !isSidebarCollapsed.value; };

const logout = async () => {
    const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
    try {
        const res = await fetch('/logout', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        });
        if (res.redirected || res.ok) window.location.href = '/login';
    } catch {
        const form = document.getElementById('logout-form');
        const input = document.getElementById('logout-csrf-token');
        if (input && csrfToken) input.value = csrfToken;
        if (form) form.submit();
    }
};
</script>

<template>
    <div class="g-shell d-flex" style="min-height:100vh; background:#f0f4f8;">

        <!-- ═══ SIDEBAR — Bleu Navy ═══ -->
        <nav class="g-sidebar d-flex flex-column flex-shrink-0"
             :style="{ width: isSidebarCollapsed ? '62px' : '240px', transition: 'width 0.25s ease' }">

            <!-- Logo -->
            <div class="g-sidebar-logo d-flex align-items-center gap-2"
                 :class="{ 'justify-content-center': isSidebarCollapsed }">
                <div class="g-logo-box">
                    <i class="bi-gem" style="font-size:16px;"></i>
                </div>
                <div v-if="!isSidebarCollapsed" class="text-truncate">
                    <div class="g-logo-text">GEL Cabinet</div>
                    <div class="g-logo-sub">Administration</div>
                </div>
            </div>

            <!-- Section Admin -->
            <div v-if="!isSidebarCollapsed" class="g-section-lbl px-3 pt-3 pb-1">Administration</div>
            <div v-else class="g-sep mx-2 my-2"></div>

            <!-- Nav links -->
            <ul class="nav flex-column px-1 mt-1">
                <li v-for="link in sidebarLinks" :key="link.key">
                    <a :href="link.route"
                       class="g-nav-link d-flex align-items-center gap-2"
                       :class="{ 'g-nav-active': pageKey === link.key }"
                       :title="isSidebarCollapsed ? link.name : ''">
                        <i :class="link.icon" class="g-nav-icon flex-shrink-0"></i>
                        <span v-if="!isSidebarCollapsed" class="g-nav-label">{{ link.name }}</span>
                    </a>
                </li>
            </ul>

            <!-- ERP section -->
            <div v-if="!isSidebarCollapsed" class="g-section-lbl px-3 pt-3 pb-1">
                <i class="bi-grid me-1" style="font-size:9px;"></i>Modules ERP
            </div>
            <div v-else class="g-sep mx-2 my-2"></div>

            <ul class="nav flex-column px-1 flex-grow-1">
                <li v-for="link in erpLinks" :key="link.key">
                    <a :href="link.route"
                       class="g-nav-link d-flex align-items-center gap-2"
                       :class="{ 'g-nav-active': pageKey === link.key }"
                       :title="isSidebarCollapsed ? link.name : ''">
                        <i :class="link.icon" class="g-nav-icon flex-shrink-0"></i>
                        <span v-if="!isSidebarCollapsed" class="g-nav-label">{{ link.name }}</span>
                    </a>
                </li>
            </ul>

            <!-- Collapse toggle -->
            <div class="g-sidebar-foot px-2 py-3">
                <button @click="toggleSidebar" class="g-toggle-btn w-100"
                        :class="{ 'justify-content-center': isSidebarCollapsed }"
                        :title="isSidebarCollapsed ? 'Étendre' : 'Réduire'">
                    <i :class="isSidebarCollapsed ? 'bi-chevron-double-right' : 'bi-chevron-double-left'"></i>
                    <span v-if="!isSidebarCollapsed" class="ms-2">Réduire</span>
                </button>
            </div>
        </nav>

        <!-- ═══ MAIN ═══ -->
        <div class="d-flex flex-column flex-grow-1" style="min-width:0;">

            <!-- Top Header — Orange #FF7900 -->
            <header class="g-header d-flex align-items-center justify-content-between px-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="g-header-logo">
                        <span>GEL</span>
                    </div>
                    <div>
                        <div class="g-header-title">{{ pageTitle }}</div>
                        <div class="g-header-sub">GEL Cabinet · Super Administration</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">

                    <!-- Quick links -->
                    <a href="/nos-services" class="g-header-icon-btn" title="Catalogue public">
                        <i class="bi-shop"></i>
                    </a>
                    <a href="/admin/catalogue/orders" class="g-header-icon-btn" title="Commandes">
                        <i class="bi-cart-check"></i>
                    </a>

                    <!-- User dropdown -->
                    <div class="dropdown">
                        <button class="g-user-btn d-flex align-items-center gap-2"
                                data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="g-avatar">{{ userInitial }}</div>
                            <div class="text-start d-none d-md-block">
                                <div class="g-user-name">{{ authStore.user?.name || 'Utilisateur' }}</div>
                                <div class="g-user-role">{{ userRoleLabel }}</div>
                            </div>
                            <i class="bi-chevron-down" style="font-size:10px; opacity:.7;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end g-dropdown">
                            <li>
                                <div class="g-dd-user-info px-3 py-2 border-bottom">
                                    <div style="font-size:13px; font-weight:700; color:#163A5E;">{{ authStore.user?.name }}</div>
                                    <div style="font-size:11px; color:#888;">{{ authStore.user?.email }}</div>
                                </div>
                            </li>
                            <li><a class="g-dd-item dropdown-item" href="/settings">
                                <i class="bi-gear me-2" style="color:#FF7900;"></i>Paramètres</a></li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a href="#" class="g-dd-item g-dd-danger dropdown-item" @click.prevent="logout">
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

            <!-- Breadcrumb strip — blanc sous header orange -->
            <div class="g-breadcrumb-bar px-4 d-flex align-items-center justify-content-between">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size:12px;">
                        <li class="breadcrumb-item">
                            <a href="/dashboard" class="text-decoration-none" style="color:#FF7900;">
                                <i class="bi-house me-1"></i>Accueil
                            </a>
                        </li>
                        <li class="breadcrumb-item active" style="color:#555;" aria-current="page">{{ pageTitle }}</li>
                    </ol>
                </nav>
                <div class="d-none d-md-flex align-items-center gap-2" style="font-size:11px; color:#888;">
                    <span class="g-env-badge">SUPER ADMIN</span>
                    <span>GEL Cabinet</span>
                </div>
            </div>

            <!-- Page content -->
            <main class="g-main flex-grow-1 p-4" style="overflow-y:auto;">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
/* ══ GEL Layout — Super Admin — Navy + Orange + Blanc ══ */

/* ── Sidebar Navy foncé ───────────────────────────────── */
.g-sidebar {
    background: #163A5E;
    border-right: none;
    overflow: hidden;
    box-shadow: 2px 0 8px rgba(0,0,0,0.15);
}
.g-sidebar-logo {
    padding: 14px 12px;
    border-bottom: 2px solid #FF7900;
    min-height: 62px;
    background: #0f2a45;
}
.g-logo-box {
    width: 36px; height: 36px;
    background: #FF7900; color: #fff;
    border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.g-logo-text {
    font-family: 'Outfit', sans-serif;
    font-weight: 800; font-size: 15px; color: #fff;
    white-space: nowrap;
}
.g-logo-sub {
    font-size: 9px; font-weight: 700; color: rgba(255,255,255,0.45);
    letter-spacing: 0.1em; text-transform: uppercase;
}
.g-section-lbl {
    font-size: 10px; font-weight: 700;
    letter-spacing: 0.1em; text-transform: uppercase;
    color: rgba(255,255,255,0.35); margin-top: 4px;
}
.g-sep { height: 1px; background: rgba(255,255,255,0.1); }

/* Nav links — sidebar bleu navy */
.g-nav-link {
    color: rgba(255,255,255,0.65);
    padding: 9px 10px; margin: 1px 0;
    font-size: 13px; text-decoration: none;
    border-radius: 4px;
    border-left: 3px solid transparent;
    transition: all 0.15s;
    white-space: nowrap; overflow: hidden;
}
.g-nav-link:hover {
    color: #fff;
    background: rgba(255,255,255,0.1);
    border-left-color: rgba(255,121,0,0.5);
}
.g-nav-link.g-nav-active {
    color: #fff;
    background: rgba(255,121,0,0.2);
    border-left-color: #FF7900;
    font-weight: 700;
}
.g-nav-icon { font-size: 15px; width: 18px; text-align: center; }
.g-nav-label { font-size: 13px; overflow: hidden; text-overflow: ellipsis; }

/* Sidebar footer */
.g-sidebar-foot { border-top: 1px solid rgba(255,255,255,0.1); }
.g-toggle-btn {
    display: flex; align-items: center;
    background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15);
    color: rgba(255,255,255,0.6); font-size: 12px; font-weight: 600;
    padding: 7px 10px; border-radius: 4px; cursor: pointer;
    transition: all 0.15s;
}
.g-toggle-btn:hover { background: rgba(255,255,255,0.15); color: #fff; }

/* ── Header Orange ─────────────────────────────────────── */
.g-header {
    background: #FF7900;
    height: 62px;
    border-bottom: 1px solid #e06700;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(255,121,0,0.25);
}
.g-header-logo {
    width: 36px; height: 36px;
    background: rgba(255,255,255,0.2);
    border: 1px solid rgba(255,255,255,0.4);
    border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
}
.g-header-logo span { font-size: 12px; font-weight: 900; color: #fff; letter-spacing: -0.5px; }
.g-header-title {
    font-family: 'Outfit', sans-serif;
    font-size: 16px; font-weight: 800; color: #fff; line-height: 1.2;
}
.g-header-sub { font-size: 10px; color: rgba(255,255,255,0.65); }

/* Icon buttons in header */
.g-header-icon-btn {
    width: 36px; height: 36px;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.3);
    border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 16px; text-decoration: none;
    transition: background 0.15s;
}
.g-header-icon-btn:hover { background: rgba(255,255,255,0.25); color: #fff; }

/* ── Breadcrumb strip ──────────────────────────────────── */
.g-breadcrumb-bar {
    background: #fff;
    height: 36px;
    border-bottom: 1px solid #dce3ee;
    flex-shrink: 0;
}
.g-env-badge {
    background: #163A5E; color: #fff;
    font-size: 9px; font-weight: 800; letter-spacing: 0.08em;
    padding: 2px 7px; border-radius: 3px;
}

/* ── User button ───────────────────────────────────────── */
.g-user-btn {
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.35);
    border-radius: 4px; padding: 6px 12px; cursor: pointer;
    transition: background 0.15s;
}
.g-user-btn:hover { background: rgba(255,255,255,0.25); }
.g-avatar {
    width: 32px; height: 32px;
    background: #fff; color: #FF7900;
    font-weight: 800; font-size: 14px;
    border-radius: 4px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.g-user-name { font-size: 13px; font-weight: 700; color: #fff; line-height: 1.2; }
.g-user-role { font-size: 11px; color: rgba(255,255,255,0.7); line-height: 1.2; }

/* ── Dropdown ──────────────────────────────────────────── */
.g-dropdown { border-radius: 4px !important; min-width: 210px; margin-top: 6px; border: 1px solid #dce3ee !important; box-shadow: 0 4px 16px rgba(0,0,0,0.12) !important; }
.g-dd-user-info { background: #f8fbff; }
.g-dd-item { font-size: 13px; padding: 9px 16px; color: #333; }
.g-dd-item:hover { background: #FFF3E0 !important; color: #FF7900 !important; }
.g-dd-danger { color: #e53935 !important; }
.g-dd-danger:hover { background: #fdecea !important; color: #e53935 !important; }

/* ── Main content ──────────────────────────────────────── */
.g-main { background: #f0f4f8; }

/* ── Global deep overrides ─────────────────────────────── */
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

:deep(.card) {
    border-radius: 6px !important;
    border: 1px solid #dce3ee;
    box-shadow: 0 1px 4px rgba(22,58,94,0.06);
}
:deep(.card-header) {
    background: linear-gradient(90deg, #163A5E, #1e4d7a);
    border-bottom: none;
    font-size: 13px; font-weight: 700;
    padding: 10px 16px; border-radius: 6px 6px 0 0 !important;
    color: #fff;
}
:deep(.table) { font-size: 13px; }
:deep(.table thead th) {
    background: #EEF3F9; color: #163A5E; font-weight: 700;
    font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em;
    border-color: #dce3ee; padding: 10px 12px;
}
:deep(.table td) { padding: 10px 12px; vertical-align: middle; border-color: #f0f4f8; }
:deep(.table tbody tr:hover) { background: #f8fbff; }
:deep(.badge) { border-radius: 4px !important; font-size: 11px; font-weight: 700; }
:deep(.form-control), :deep(.form-select) {
    border-radius: 4px !important; font-size: 13px;
    border: 1px solid #dce3ee;
}
:deep(.form-control:focus), :deep(.form-select:focus) {
    border-color: #FF7900; box-shadow: 0 0 0 2px rgba(255,121,0,0.15);
}
:deep(.text-primary) { color: #FF7900 !important; }
:deep(.bg-primary) { background: #FF7900 !important; }
:deep(.border-primary) { border-color: #FF7900 !important; }
:deep(.progress-bar) { background: #FF7900; }
</style>
