<script setup>
import { ref, computed } from 'vue';
import { authStore } from '../stores/auth';

const props = defineProps({
    pageTitle: { type: String, default: 'GEL Cabinet' }
});

const sidebarOpen = ref(true);

const tabs = [
    { name: 'Accueil',        icon: 'bi-house',        route: '/dashboard',               key: 'gel-dashboard' },
    { name: 'Clients',        icon: 'bi-building',     route: '/clients',                  key: 'gel-clients' },
    { name: 'Missions',       icon: 'bi-check2-square',route: '/missions',                 key: 'gel-missions' },
    { name: 'Commandes',      icon: 'bi-cart-check',   route: '/admin/catalogue/orders',   key: 'erp-orders' },
    { name: 'Finance ERP',    icon: 'bi-receipt',      route: '/erp/invoices',             key: 'erp-invoice' },
    { name: 'Administration', icon: 'bi-gear',         route: '/licenses',                 key: 'gel-licenses' },
];

const sidebarBySection = {
    'gel-dashboard': [
        { group: 'Actions rapides', items: [
            { label: 'Nouveau client',  href: '/clients/create',    icon: 'bi-person-plus' },
            { label: 'Nouvelle mission',href: '/missions/create',   icon: 'bi-check2-square' },
            { label: 'Voir commandes',  href: '/admin/catalogue/orders', icon: 'bi-cart' },
        ]},
        { group: 'Consultation', items: [
            { label: 'Clients récents', href: '/clients',           icon: 'bi-building' },
            { label: 'Missions en cours',href: '/missions',         icon: 'bi-check2-square' },
        ]},
    ],
    'gel-clients': [
        { group: 'Clients', items: [
            { label: 'Tous les clients',  href: '/clients',         icon: 'bi-list-ul' },
            { label: 'Nouveau client',    href: '/clients/create',  icon: 'bi-person-plus' },
        ]},
        { group: 'Organisation', items: [
            { label: 'Pôles',             href: '/poles',           icon: 'bi-diagram-3' },
            { label: 'Services cabinet',  href: '/services',        icon: 'bi-grid-3x3-gap' },
        ]},
        { group: 'Gestion', items: [
            { label: 'Licences',          href: '/licenses',        icon: 'bi-key' },
            { label: 'Admins entreprise', href: '/company-admins',  icon: 'bi-person-badge' },
        ]},
    ],
    'gel-missions': [
        { group: 'Missions', items: [
            { label: 'Toutes les missions', href: '/missions',          icon: 'bi-list-ul' },
            { label: 'Nouvelle mission',    href: '/missions/create',   icon: 'bi-plus-circle' },
        ]},
        { group: 'Suivi', items: [
            { label: 'Dossiers clients',    href: '/dossiers',          icon: 'bi-folder2-open' },
            { label: 'Documents',           href: '/documents',         icon: 'bi-file-text' },
        ]},
    ],
    'erp-orders': [
        { group: 'Commandes', items: [
            { label: 'En cours',          href: '/admin/catalogue/orders',           icon: 'bi-cart-check' },
            { label: 'Archives',          href: '/admin/catalogue/orders/archives',  icon: 'bi-archive' },
        ]},
        { group: 'Catalogue', items: [
            { label: 'Services',          href: '/admin/catalogue/services',         icon: 'bi-tags' },
            { label: 'Demandes clients',  href: '/admin/requests',                   icon: 'bi-envelope' },
        ]},
    ],
    'erp-invoice': [
        { group: 'Finance ERP', items: [
            { label: 'Facturation',       href: '/erp/invoices',    icon: 'bi-receipt' },
            { label: 'Trésorerie',        href: '/erp/treasury',    icon: 'bi-cash-stack' },
        ]},
        { group: 'Gestion', items: [
            { label: 'Stocks',            href: '/erp/stocks',      icon: 'bi-boxes' },
            { label: 'RH & Paie',         href: '/erp/hr',          icon: 'bi-people' },
        ]},
    ],
    'gel-licenses': [
        { group: 'Clients', items: [
            { label: 'Tous les clients',  href: '/clients',         icon: 'bi-building' },
            { label: 'Nouveau client',    href: '/clients/create',  icon: 'bi-person-plus' },
        ]},
        { group: 'Administration', items: [
            { label: 'Pôles',             href: '/poles',           icon: 'bi-diagram-3' },
            { label: 'Services cabinet',  href: '/services',        icon: 'bi-grid-3x3-gap' },
            { label: 'Licences',          href: '/licenses',        icon: 'bi-key' },
        ]},
        { group: 'Utilisateurs', items: [
            { label: 'Admins entreprise', href: '/company-admins',  icon: 'bi-person-badge' },
            { label: 'Demandes entrantes',href: '/admin/requests',  icon: 'bi-envelope' },
            { label: 'Paramètres',        href: '/settings',        icon: 'bi-gear' },
        ]},
    ],
    'gel-poles': [
        { group: 'Organisation', items: [
            { label: 'Tous les pôles',    href: '/poles',           icon: 'bi-diagram-3' },
            { label: 'Services cabinet',  href: '/services',        icon: 'bi-grid-3x3-gap' },
        ]},
        { group: 'Clients', items: [
            { label: 'Tous les clients',  href: '/clients',         icon: 'bi-building' },
            { label: 'Nouveau client',    href: '/clients/create',  icon: 'bi-person-plus' },
        ]},
    ],
    'gel-services': [
        { group: 'Services', items: [
            { label: 'Services cabinet',  href: '/services',        icon: 'bi-grid-3x3-gap' },
        ]},
    ],
    'gel-dossiers': [
        { group: 'Dossiers', items: [
            { label: 'Tous les dossiers', href: '/dossiers',        icon: 'bi-folder2-open' },
            { label: 'Documents',         href: '/documents',       icon: 'bi-file-text' },
        ]},
    ],
    'gel-documents': [
        { group: 'Documents', items: [
            { label: 'Tous les documents',href: '/documents',       icon: 'bi-file-text' },
            { label: 'Dossiers',          href: '/dossiers',        icon: 'bi-folder2-open' },
        ]},
    ],
    'gel-company-admins': [
        { group: 'Clients', items: [
            { label: 'Tous les clients',  href: '/clients',         icon: 'bi-building' },
            { label: 'Nouveau client',    href: '/clients/create',  icon: 'bi-person-plus' },
        ]},
        { group: 'Administration', items: [
            { label: 'Admins entreprise', href: '/company-admins',  icon: 'bi-person-badge' },
            { label: 'Licences',          href: '/licenses',        icon: 'bi-key' },
            { label: 'Paramètres',        href: '/settings',        icon: 'bi-gear' },
        ]},
    ],
    'gel-requests': [
        { group: 'Demandes', items: [
            { label: 'Demandes entrantes',href: '/admin/requests',  icon: 'bi-envelope' },
            { label: 'Commandes en cours',href: '/admin/catalogue/orders', icon: 'bi-cart-check' },
        ]},
    ],
    'erp-archives': [
        { group: 'Commandes', items: [
            { label: 'En cours',          href: '/admin/catalogue/orders',           icon: 'bi-cart-check' },
            { label: 'Archives',          href: '/admin/catalogue/orders/archives',  icon: 'bi-archive' },
        ]},
        { group: 'Catalogue', items: [
            { label: 'Services',          href: '/admin/catalogue/services',         icon: 'bi-tags' },
            { label: 'Demandes clients',  href: '/admin/requests',                   icon: 'bi-envelope' },
        ]},
    ],
    'erp-catalogue': [
        { group: 'Catalogue', items: [
            { label: 'Services',          href: '/admin/catalogue/services',         icon: 'bi-tags' },
            { label: 'Commandes en cours',href: '/admin/catalogue/orders',           icon: 'bi-cart-check' },
        ]},
    ],
    'erp-stock': [
        { group: 'ERP', items: [
            { label: 'Stocks',            href: '/erp/stocks',       icon: 'bi-boxes' },
            { label: 'Facturation',       href: '/erp/invoices',     icon: 'bi-receipt' },
        ]},
    ],
    'erp-hr': [
        { group: 'RH', items: [
            { label: 'RH & Paie',         href: '/erp/hr',           icon: 'bi-people' },
        ]},
    ],
    'erp-treasury': [
        { group: 'Finance', items: [
            { label: 'Trésorerie',        href: '/erp/treasury',     icon: 'bi-cash-stack' },
            { label: 'Facturation',       href: '/erp/invoices',     icon: 'bi-receipt' },
        ]},
    ],
    'gel-accounting': [
        { group: 'Comptabilité', items: [
            { label: 'Comptabilité',      href: '/accounting',       icon: 'bi-calculator' },
            { label: 'Facturation',       href: '/erp/invoices',     icon: 'bi-receipt' },
        ]},
    ],
    'gel-settings': [
        { group: 'Clients', items: [
            { label: 'Tous les clients',  href: '/clients',         icon: 'bi-building' },
            { label: 'Nouveau client',    href: '/clients/create',  icon: 'bi-person-plus' },
        ]},
        { group: 'Compte', items: [
            { label: 'Mon profil',        href: '/settings',         icon: 'bi-person-circle' },
            { label: 'Paramètres',        href: '/settings',         icon: 'bi-gear' },
        ]},
    ],
};

const sidebarLinks = computed(() => sidebarBySection[pageKey.value] || sidebarBySection['gel-dashboard']);

const sectionTitles = {
    'gel-dashboard': 'Accueil',
    'gel-clients': 'Clients',
    'gel-missions': 'Missions',
    'gel-poles': 'Pôles',
    'gel-services': 'Services',
    'erp-orders': 'Commandes',
    'erp-archives': 'Archives',
    'erp-catalogue': 'Catalogue',
    'erp-invoice': 'Finance ERP',
    'erp-stock': 'Stocks',
    'erp-hr': 'RH',
    'erp-treasury': 'Trésorerie',
    'gel-licenses': 'Administration',
    'gel-company-admins': 'Admins',
    'gel-requests': 'Demandes',
    'gel-accounting': 'Comptabilité',
    'gel-settings': 'Paramètres',
};

const sectionTitle = computed(() => {
    const key = pageKey.value;
    // Extract base key for sub-pages
    if (key.startsWith('gel-')) return sectionTitles[key] || 'Accueil';
    if (key.startsWith('erp-')) return sectionTitles[key] || 'ERP';
    return sectionTitles[key] || 'Accueil';
});

const pageKey = computed(() => {
    const path = window.location.pathname;
    if (path.startsWith('/clients'))        return 'gel-clients';
    if (path.startsWith('/poles'))          return 'gel-poles';
    if (path.startsWith('/missions'))       return 'gel-missions';
    if (path.startsWith('/services'))       return 'gel-services';
    if (path.startsWith('/dossiers'))       return 'gel-dossiers';
    if (path.startsWith('/documents'))      return 'gel-documents';
    if (path.startsWith('/accounting'))     return 'gel-accounting';
    if (path.startsWith('/licenses'))       return 'gel-licenses';
    if (path.startsWith('/company-admins')) return 'gel-company-admins';
    if (path.startsWith('/admin/requests')) return 'gel-requests';
    if (path.startsWith('/admin/catalogue/orders/archives')) return 'erp-archives';
    if (path.startsWith('/admin/catalogue/orders'))          return 'erp-orders';
    if (path.startsWith('/admin/catalogue/services'))        return 'erp-orders';
    if (path.startsWith('/erp/stocks'))     return 'erp-stock';
    if (path.startsWith('/erp/invoices'))   return 'erp-invoice';
    if (path.startsWith('/erp/hr'))         return 'erp-hr';
    if (path.startsWith('/erp/treasury'))   return 'erp-treasury';
    if (path.startsWith('/settings'))       return 'gel-settings';
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
    <div class="g-shell d-flex flex-column" style="min-height:100vh; background:#f0f4f8;">

        <!-- ═══ TOP BAR — Logo + Tabs + User ═══ -->
        <header class="g-topbar d-flex align-items-center justify-content-between px-3">
            <div class="d-flex align-items-center gap-1">
                <div class="g-logo d-flex align-items-center gap-2 me-3">
                    <button class="g-toggler d-lg-none me-1" @click="sidebarOpen = !sidebarOpen">
                        <i class="bi-list"></i>
                    </button>
                    <div class="g-logo-icon"><i class="bi-gem"></i></div>
                    <span class="g-logo-text d-none d-sm-inline">GEL Cabinet</span>
                </div>

                <nav class="g-tabs d-flex">
                    <a v-for="tab in tabs" :key="tab.key"
                       :href="tab.route"
                       class="g-tab"
                       :class="{ 'g-tab-active': pageKey === tab.key }">
                        <i :class="tab.icon"></i>
                        <span class="d-none d-md-inline">{{ tab.name }}</span>
                    </a>
                </nav>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button class="g-toggle-btn d-none d-lg-flex me-1" @click="sidebarOpen = !sidebarOpen" title="Afficher/Masquer le panneau latéral">
                    <i :class="sidebarOpen ? 'bi-layout-sidebar-inset' : 'bi-layout-sidebar'"></i>
                </button>
                <span class="g-role-badge d-none d-sm-inline">{{ userRoleLabel }}</span>
                <div class="dropdown">
                    <button class="g-user-btn d-flex align-items-center gap-2"
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="g-avatar">{{ userInitial }}</div>
                        <i class="bi-chevron-down" style="font-size:9px; color:rgba(255,255,255,0.6);"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end g-dropdown">
                        <li>
                            <div class="g-dd-user px-3 py-2 border-bottom">
                                <div class="fw-bold" style="font-size:13px; color:#163A5E;">{{ authStore.user?.name }}</div>
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

        <!-- ═══ MAIN LAYOUT: Sidebar gauche + Contenu ═══ -->
        <div class="g-body d-flex flex-grow-1" style="min-height:0;">

            <!-- Sidebar gauche (fonctionnalités additionnelles) -->
            <aside class="g-sidebar d-flex flex-column flex-shrink-0"
                   :class="{ 'g-sidebar-closed': !sidebarOpen }">
                <div class="g-sb-header">
                    <i class="bi-layout-sidebar me-1"></i>
                    <span>{{ sectionTitle }}</span>
                </div>
                <div class="g-sb-content">
                    <div v-for="group in sidebarLinks" :key="group.group" class="g-sb-group">
                        <div class="g-sb-group-title">{{ group.group }}</div>
                        <a v-for="link in group.items" :key="link.label"
                           :href="link.href"
                           class="g-sb-link">
                            <i :class="link.icon"></i>
                            {{ link.label }}
                        </a>
                    </div>
                </div>
            </aside>

            <!-- Overlay mobile -->
            <div v-if="sidebarOpen" class="g-overlay d-lg-none" @click="sidebarOpen = false"></div>

            <!-- Contenu principal -->
            <main class="g-main flex-grow-1 p-4">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
/* ══ GEL Layout — Top tabs + Left sidebar ══ */

/* ── Top bar ─────────────────────────────── */
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
.g-toggler {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    color: #fff;
    border-radius: 4px;
    width: 30px; height: 30px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 15px;
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

/* ── Tabs ────────────────────────────────── */
.g-tabs { gap: 2px; }
.g-tab {
    display: flex; align-items: center; gap: 5px;
    padding: 0 14px;
    height: 50px;
    font-size: 12px; font-weight: 600;
    color: rgba(255,255,255,0.7);
    text-decoration: none;
    border-bottom: 3px solid transparent;
    transition: all 0.15s;
    white-space: nowrap;
}
.g-tab:hover { color: #fff; background: rgba(255,255,255,0.06); border-bottom-color: rgba(255,121,0,0.4); }
.g-tab.g-tab-active { color: #fff; border-bottom-color: #FF7900; background: rgba(255,121,0,0.1); }
.g-tab i { font-size: 14px; }

/* ── User ────────────────────────────────── */
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

/* ── Dropdown ────────────────────────────── */
.g-dropdown { border-radius: 4px !important; min-width: 210px; margin-top: 6px; border: 1px solid #dce3ee !important; box-shadow: 0 4px 16px rgba(0,0,0,0.12) !important; }
.g-dd-user { background: #f8fbff; }
.g-dd-item { font-size: 13px; padding: 9px 16px; color: #333; }
.g-dd-item:hover { background: #FFF3E0 !important; color: #FF7900 !important; }
.g-dd-danger { color: #e53935 !important; }
.g-dd-danger:hover { background: #fdecea !important; color: #e53935 !important; }

/* ── Sidebar gauche ───────────────────────── */
.g-body { position: relative; }
.g-sidebar {
    width: 210px;
    background: #fff;
    border-right: 1px solid #dce3ee;
    overflow-y: auto;
    transition: width 0.2s ease, padding 0.2s ease;
}
.g-sidebar-closed {
    width: 0;
    overflow: hidden;
    border-right: none;
    padding: 0;
}
.g-sb-header {
    padding: 12px 14px;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #FF7900;
    border-bottom: 1px solid #f0f4f8;
    white-space: nowrap;
}
.g-sb-content { padding: 8px 0; }
.g-sb-group { padding: 4px 0; }
.g-sb-group-title {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #163A5E;
    padding: 6px 14px 4px;
    white-space: nowrap;
}
.g-sb-link {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 7px 14px;
    font-size: 12px;
    font-weight: 500;
    color: #444;
    text-decoration: none;
    border-left: 2px solid transparent;
    transition: all 0.12s;
    white-space: nowrap;
}
.g-sb-link:hover {
    color: #FF7900;
    background: #FFF3E0;
    border-left-color: #FF7900;
}
.g-sb-link i {
    font-size: 13px;
    width: 16px;
    text-align: center;
    color: #888;
    flex-shrink: 0;
}
.g-sb-link:hover i { color: #FF7900; }

/* ── Main ────────────────────────────────── */
.g-main { background: #f0f4f8; min-width: 0; }

/* ── Mobile overlay ──────────────────────── */
.g-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    z-index: 1040;
}

/* ── Global deep overrides ───────────────── */
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
    .g-tab { padding: 0 10px; font-size: 11px; height: 46px; }
    .g-tab i { font-size: 13px; }
    .g-main { padding: 12px !important; }
    .g-sidebar {
        position: fixed;
        left: 0;
        top: 52px;
        bottom: 0;
        z-index: 1050;
        box-shadow: 4px 0 20px rgba(0,0,0,0.15);
        width: 230px;
    }
    .g-sidebar-closed {
        width: 0 !important;
        transform: translateX(-100%);
    }
}
@media (max-width: 575.98px) {
    .g-topbar { height: 48px; }
    .g-tab { padding: 0 8px; font-size: 10px; height: 42px; gap: 3px; }
    .g-tab i { font-size: 12px; }
    .g-main { padding: 8px !important; }
    .g-role-badge { display: none; }
}
</style>
