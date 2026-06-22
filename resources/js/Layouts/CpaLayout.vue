<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { authStore } from '../stores/auth';

const props = defineProps({
    pageTitle: { type: String, default: 'Portail Client' }
});

const sidebarOpen = ref(true);
const isMobile = ref(window.innerWidth < 768);

const userInitial = computed(() =>
    authStore.user?.name?.charAt(0).toUpperCase() || 'U'
);

const userRoleLabel = computed(() => {
    const labels = {
        super_admin: 'Administrateur',
        company_admin: 'Admin Entreprise',
        client: 'Client',
        comptable: 'Comptable',
        collaborator: 'Collaborateur',
    };
    return labels[authStore.user?.role] || 'Utilisateur';
});

const isLinkActive = (linkHref) => {
    const currentPath = window.location.pathname;
    const currentSearch = window.location.search;

    if (linkHref.includes('?section=')) {
        const linkParams = new URLSearchParams(linkHref.split('?')[1]);
        const urlParams = new URLSearchParams(currentSearch);
        return currentPath === linkHref.split('?')[0] && urlParams.get('section') === linkParams.get('section');
    }

    if (linkHref === '/cpa-dashboard') {
        const urlParams = new URLSearchParams(currentSearch);
        return currentPath === '/cpa-dashboard' && !urlParams.has('section');
    }

    return currentPath === linkHref;
};

const sidebarLinks = computed(() => {
    const role = authStore.user?.role;
    const links = [
        { label: 'Tableau de bord', icon: 'bi-speedometer2', href: '/cpa-dashboard' },
    ];

    if (role === 'super_admin') {
        links.push(
            { label: 'Clients', icon: 'bi-people', href: '/cpa-dashboard?section=clients' },
            { label: 'Dossiers', icon: 'bi-folder', href: '/clients' },
            { label: 'Statistiques', icon: 'bi-graph-up', href: '/cpa-dashboard?section=stats' },
        );
    }
    if (role === 'client') {
        links.push(
            { label: 'Mes déclarations', icon: 'bi-file-earmark-text', href: '/cpa-dashboard?section=declarations' },
            { label: 'Mes documents', icon: 'bi-folder2-open', href: '/cpa-dashboard?section=documents' },
            { label: 'Messagerie', icon: 'bi-envelope', href: '/cpa-dashboard?section=messages' },
        );
    }
    if (role === 'company_admin') {
        links.push(
            { label: 'Finance', icon: 'bi-receipt', href: '/company/invoices' },
            { label: 'RH & Paie', icon: 'bi-people', href: '/company/hr' },
            { label: 'Documents', icon: 'bi-folder2-open', href: '/company/ged' },
            { label: 'Échéances', icon: 'bi-calendar-event', href: '/cpa-dashboard?section=echeances' },
        );
    }
    if (role === 'comptable') {
        links.push(
            { label: 'Dossiers clients', icon: 'bi-briefcase', href: '/cpa-dashboard?section=dossiers' },
            { label: 'Tâches', icon: 'bi-check2-square', href: '/cpa-dashboard?section=taches' },
            { label: 'Calendrier fiscal', icon: 'bi-calendar3', href: '/cpa-dashboard?section=calendrier' },
            { label: 'Messagerie', icon: 'bi-envelope', href: '/cpa-dashboard?section=messages' },
        );
    }

    return links.map(link => ({
        ...link,
        active: isLinkActive(link.href)
    }));
});

// Current section label for subnav
const activeSectionLabel = computed(() => {
    const active = sidebarLinks.value.find(l => l.active);
    return active?.label || 'Tableau de bord';
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
        window.location.href = '/cpa-login';
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

function handleResize() {
    isMobile.value = window.innerWidth < 768;
    if (!isMobile.value) sidebarOpen.value = true;
}

onMounted(() => window.addEventListener('resize', handleResize));
onUnmounted(() => window.removeEventListener('resize', handleResize));
</script>

<template>
    <div class="g-shell d-flex flex-column" style="min-height:100vh; background:#f0f4f8;">

        <!-- ═══ TOP BAR ═══ -->
        <header class="g-topbar d-flex align-items-center justify-content-between px-3">
            <div class="d-flex align-items-center gap-2">
                <div class="g-logo-icon"><i class="bi-gem"></i></div>
                <span class="g-logo-text">GEL Cabinet CPA</span>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="g-role-badge d-none d-sm-inline">{{ userRoleLabel }}</span>
                <button class="g-toggle-btn" @click="sidebarOpen = !sidebarOpen" title="Afficher/Masquer la sidebar">
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
                                <div class="fw-bold" style="font-size:13px; color:#163A5E;">{{ authStore.user?.name }}</div>
                                <div style="font-size:11px; color:#888;">{{ authStore.user?.email }}</div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- ═══ MAIN LAYOUT: Sidebar + Contenu ═══ -->
        <div class="g-body d-flex flex-grow-1" style="min-height:0;">

            <!-- Sidebar -->
            <aside class="g-sidebar d-flex flex-column flex-shrink-0 justify-content-between"
                   :class="{ 'g-sidebar-closed': !sidebarOpen }">
                <div class="gs-nav">
                    <a v-for="link in sidebarLinks" :key="link.label"
                       :href="link.href"
                       class="gs-nav-item"
                       :class="{ 'gs-nav-active': link.active }">
                        <i :class="link.icon" class="gs-nav-icon"></i>
                        <span class="gs-nav-label">{{ link.label }}</span>
                    </a>
                </div>

                <!-- ══ BAS DE SIDEBAR — Compte ══ -->
                <div class="gs-sidebar-divider"></div>
                <div class="gs-sidebar-bottom">
                    <a href="/settings" class="gs-nav-item">
                        <i class="bi-gear gs-nav-icon"></i>
                        <span class="gs-nav-label">Paramètres</span>
                    </a>
                    <a href="#" class="gs-nav-item gs-nav-logout" @click.prevent="logout">
                        <i class="bi-box-arrow-right gs-nav-icon"></i>
                        <span class="gs-nav-label">Déconnexion</span>
                    </a>
                </div>

                <div class="gs-nav" style="border-top:1px solid rgba(255,255,255,0.06); padding:8px 0;">
                    <a href="/company/dashboard"
                       class="gs-nav-item"
                       title="Retour au portail entreprise">
                        <i class="bi-arrow-left gs-nav-icon"></i>
                        <span class="gs-nav-label">Portail Entreprise</span>
                    </a>
                </div>
            </aside>

            <!-- Overlay mobile -->
            <div v-if="sidebarOpen && isMobile" class="g-overlay" @click="sidebarOpen = false"></div>

            <!-- Contenu principal -->
            <main class="g-main flex-grow-1 d-flex flex-column" style="min-width:0;">
                <!-- Subnav bar -->
                <div class="gs-subnav">
                    <div class="gs-subnav-title">{{ activeSectionLabel }}</div>
                    <div class="gs-subnav-links">
                        <a v-for="link in sidebarLinks" :key="link.href"
                           :href="link.href"
                           class="gs-subnav-link"
                           :class="{ 'gs-subnav-active': link.active }">
                            <i :class="link.icon"></i>
                            {{ link.label }}
                        </a>
                    </div>
                </div>
                <!-- Page content -->
                <div class="flex-grow-1 p-4" style="min-height:0;">
                    <slot />
                </div>
            </main>
        </div>

        <!-- Mobile Bottom Bar -->
        <nav class="g-bottombar d-md-none">
            <a v-for="link in sidebarLinks" :key="link.href"
               :href="link.href"
               class="g-bottombar-link"
               :class="{ 'g-bottombar-active': link.active }">
                <i :class="link.icon"></i>
                <span>{{ link.label }}</span>
            </a>
        </nav>

        <!-- Logout form -->
        <form id="logout-form" method="POST" action="/logout" style="display:none;">
            <input type="hidden" name="_token" id="logout-csrf-token">
        </form>
    </div>
</template>

<style scoped>
/* ══ CPA LAYOUT — Sidebar verticale + Subnav ══ */

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

/* ── User ───────────────────────────────── */
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
.g-role-badge {
    font-size: 9px; font-weight: 800; letter-spacing: 0.06em;
    text-transform: uppercase;
    background: rgba(255,121,0,0.2);
    color: #FF7900;
    padding: 3px 8px; border-radius: 3px;
}
.g-dropdown { border-radius: 4px !important; min-width: 210px; margin-top: 6px; border: 1px solid #dce3ee !important; box-shadow: 0 4px 16px rgba(0,0,0,0.12) !important; }
.g-dd-user { background: #f8fbff; }
.g-dd-item { font-size: 13px; padding: 9px 16px; color: #333; }
.g-dd-item:hover { background: #FFF3E0 !important; color: #FF7900 !important; }
.g-dd-danger { color: #e53935 !important; }
.g-dd-danger:hover { background: #fdecea !important; color: #e53935 !important; }

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

/* ── Navigation ────────────────────────────── */
.gs-nav {
    padding: 4px 0;
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

/* ── Subnav ──────────────────────────────────── */
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
.gs-subnav-active {
    color: #FF7900 !important;
    background: #FFF3E0;
    border-color: #FFE0B2;
}
.gs-subnav-link i {
    font-size: 12px;
    color: #999;
}
.gs-subnav-link:hover i,
.gs-subnav-active i { color: #FF7900; }

/* ── Main ────────────────────────────────── */
.g-main { background: #f0f4f8; min-width: 0; }

/* ── Mobile overlay ──────────────────────── */
.g-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    z-index: 1040;
}

/* ── Mobile Bottom Bar ──────────────────────── */
.g-bottombar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 60px;
    background: rgba(255,255,255,0.98);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-top: 1px solid #dce3ee;
    display: flex;
    align-items: center;
    justify-content: space-around;
    z-index: 1030;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
    padding-bottom: env(safe-area-inset-bottom);
}
.g-bottombar-link {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    text-decoration: none;
    font-size: 10px;
    font-weight: 500;
    flex: 1;
    height: 100%;
    gap: 2px;
    transition: color 0.15s;
}
.g-bottombar-link:hover { color: #FF7900; }
.g-bottombar-link i { font-size: 18px; }
.g-bottombar-active {
    color: #FF7900;
    font-weight: 600;
}

/* ── Deep overrides ──────────────────────── */
:deep(.btn) { border-radius: 4px !important; font-size: 13px; }
:deep(.btn-primary) {
    background: #FF7900 !important; border-color: #FF7900 !important;
    color: #fff !important; font-weight: 700 !important;
}
:deep(.btn-primary:hover) { background: #e06700 !important; border-color: #e06700 !important; }
:deep(.btn-outline-primary) { color: #FF7900 !important; border-color: #FF7900 !important; }
:deep(.btn-outline-primary:hover) { background: #FF7900 !important; color: #fff !important; }
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
@media (max-width: 767.98px) {
    .g-topbar { height: 48px; }
    .g-main > .p-4 { padding: 8px !important; padding-bottom: 76px; }
    .g-role-badge { display: none; }
    .g-logo-text { font-size: 13px; }
    .gs-subnav { padding: 8px 12px; gap: 8px; }
    .gs-subnav-title { font-size: 14px; }
    .gs-subnav-links { display: none; }
    .g-bottombar { display: flex; }
}
@media (min-width: 768px) {
    .g-bottombar { display: none !important; }
}
</style>
