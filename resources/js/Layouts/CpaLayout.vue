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

    // Assign active status
    return links.map(link => ({
        ...link,
        active: isLinkActive(link.href)
    }));
});

const logout = async () => {
    const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
    const form = document.getElementById('logout-form');
    const input = document.getElementById('logout-csrf-token');
    if (input && csrfToken) input.value = csrfToken;
    if (form) form.submit();
};

function handleResize() {
    isMobile.value = window.innerWidth < 768;
    if (!isMobile.value) sidebarOpen.value = true;
}

onMounted(() => window.addEventListener('resize', handleResize));
onUnmounted(() => window.removeEventListener('resize', handleResize));
</script>

<template>
    <div class="cpa-shell">
        <!-- ═══ TOP BAR ═══ -->
        <header class="cpa-topbar">
            <div class="cpa-topbar-left">
                <button class="cpa-menu-btn" @click="sidebarOpen = !sidebarOpen">
                    <i class="bi-list"></i>
                </button>
                <div class="cpa-logo">
                    <div class="cpa-logo-icon"><i class="bi-gem"></i></div>
                    <span class="cpa-logo-text">GEL Cabinet</span>
                </div>
            </div>
            <div class="cpa-topbar-right">
                <span class="cpa-role-badge">{{ userRoleLabel }}</span>
                <div class="dropdown">
                    <button class="cpa-user-btn" data-bs-toggle="dropdown">
                        <div class="cpa-avatar">{{ userInitial }}</div>
                        <i class="bi-chevron-down cpa-chevron"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end cpa-dropdown">
                        <li>
                            <div class="cpa-dd-user">
                                <div class="cpa-dd-name">{{ authStore.user?.name }}</div>
                                <div class="cpa-dd-email">{{ authStore.user?.email }}</div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item cpa-dd-item" href="#" @click.prevent="logout">
                            <i class="bi-box-arrow-right me-2"></i>Déconnexion</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- ═══ BODY ═══ -->
        <div class="cpa-body">
            <!-- Sidebar -->
            <aside class="cpa-sidebar" :class="{ 'cpa-sidebar-open': sidebarOpen }">
                <nav class="cpa-nav">
                    <a v-for="link in sidebarLinks" :key="link.label"
                       :href="link.href"
                       class="cpa-nav-link"
                       :class="{ 'cpa-nav-active': link.active }">
                        <i :class="link.icon"></i>
                        <span>{{ link.label }}</span>
                    </a>
                </nav>
                <div class="cpa-sidebar-footer">
                    <a href="/company/dashboard" class="cpa-nav-link cpa-nav-back">
                        <i class="bi-arrow-left"></i>
                        <span>Portail Entreprise</span>
                    </a>
                </div>
            </aside>

            <!-- Overlay mobile -->
            <div v-if="sidebarOpen && isMobile" class="cpa-overlay" @click="sidebarOpen = false"></div>

            <!-- Main -->
            <main class="cpa-main">
                <slot />
            </main>
        </div>

        <!-- Mobile Bottom Bar -->
        <nav class="cpa-bottombar d-md-none">
            <a v-for="link in sidebarLinks" :key="link.label"
               :href="link.href"
               class="cpa-bottombar-link"
               :class="{ 'cpa-bottombar-active': link.active }">
                <i :class="link.icon"></i>
                <span>{{ link.label }}</span>
            </a>
        </nav>

        <!-- Logout form caché -->
        <form id="logout-form" method="POST" action="/logout" style="display:none;">
            <input type="hidden" name="_token" id="logout-csrf-token">
        </form>
    </div>
</template>

<style>
/* ═══════════════════════════════════════════════════════
   CPA LAYOUT — Design Crescendo CPA
   Fond blanc dominant, orange/bleu en accents
   ═══════════════════════════════════════════════════════ */

/* ── Variables ── */
:root {
    --cpa-primary: #FF7900;
    --cpa-primary-hover: #e06700;
    --cpa-dark: #163A5E;
    --cpa-dark-hover: #1e4d7a;
    --cpa-border: #eef0f4;
    --cpa-bg: #ffffff;
    --cpa-bg-alt: #f8f9fc;
    --cpa-text: #1a1a2e;
    --cpa-text-muted: #6b7280;
    --cpa-radius: 10px;
    --cpa-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    --cpa-shadow-lg: 0 10px 25px -5px rgba(0,0,0,0.08), 0 4px 10px -6px rgba(0,0,0,0.04);
    --cpa-topbar-height: 56px;
    --cpa-sidebar-width: 240px;
}

/* ── Shell ── */
.cpa-shell {
    min-height: 100vh;
    background: var(--cpa-bg-alt);
    display: flex;
    flex-direction: column;
}

/* ── Topbar glow accent ── */
.cpa-topbar::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, var(--cpa-primary) 0%, #163A5E 50%, var(--cpa-primary) 100%);
    z-index: 1;
}

/* ── Topbar ── */
.cpa-topbar {
    height: var(--cpa-topbar-height);
    background: var(--cpa-bg);
    border-bottom: 1px solid var(--cpa-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    position: sticky;
    top: 0;
    z-index: 1020;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}
.cpa-topbar-left,
.cpa-topbar-right {
    display: flex;
    align-items: center;
    gap: 12px;
}
.cpa-menu-btn {
    background: none;
    border: 1px solid var(--cpa-border);
    border-radius: 6px;
    width: 34px;
    height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--cpa-text-muted);
    cursor: pointer;
    font-size: 18px;
    transition: all 0.15s;
}
.cpa-menu-btn:hover {
    background: var(--cpa-bg-alt);
    color: var(--cpa-dark);
}
.cpa-logo {
    display: flex;
    align-items: center;
    gap: 8px;
}
.cpa-logo-icon {
    width: 30px;
    height: 30px;
    background: var(--cpa-primary);
    color: #fff;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}
.cpa-logo-text {
    font-family: 'Outfit', sans-serif;
    font-weight: 800;
    font-size: 15px;
    color: var(--cpa-dark);
}
.cpa-role-badge {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--cpa-primary);
    background: rgba(255,121,0,0.08);
    padding: 4px 10px;
    border-radius: 6px;
}
.cpa-user-btn {
    background: none;
    border: 1px solid var(--cpa-border);
    border-radius: 6px;
    padding: 4px 10px;
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.15s;
}
.cpa-user-btn:hover {
    background: var(--cpa-bg-alt);
}
.cpa-avatar {
    width: 28px;
    height: 28px;
    background: var(--cpa-dark);
    color: #fff;
    font-weight: 700;
    font-size: 11px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.cpa-chevron {
    font-size: 9px;
    color: var(--cpa-text-muted);
}
.cpa-dropdown {
    border-radius: 8px !important;
    border: 1px solid var(--cpa-border) !important;
    box-shadow: var(--cpa-shadow-lg) !important;
    margin-top: 8px;
    min-width: 200px;
}
.cpa-dd-user {
    padding: 10px 14px;
}
.cpa-dd-name {
    font-weight: 700;
    font-size: 13px;
    color: var(--cpa-dark);
}
.cpa-dd-email {
    font-size: 11px;
    color: var(--cpa-text-muted);
}
.cpa-dd-item {
    font-size: 13px;
    padding: 8px 14px;
    color: var(--cpa-text);
}
.cpa-dd-item:hover {
    background: rgba(255,121,0,0.06) !important;
    color: var(--cpa-primary) !important;
}

/* ── Body layout ── */
.cpa-body {
    display: flex;
    flex-grow: 1;
    min-height: 0;
    position: relative;
}

/* ── Sidebar ── */
.cpa-sidebar {
    width: var(--cpa-sidebar-width);
    min-width: var(--cpa-sidebar-width);
    background: var(--cpa-bg);
    border-right: 1px solid var(--cpa-border);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.25s ease, width 0.25s ease;
    overflow-y: auto;
}
.cpa-sidebar-footer {
    border-top: 1px solid var(--cpa-border);
    padding: 8px 0;
}
.cpa-nav {
    padding: 12px 0;
}
.cpa-nav-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 500;
    color: var(--cpa-text-muted);
    text-decoration: none;
    border-left: 3px solid transparent;
    transition: all 0.15s;
}
.cpa-nav-link:hover {
    color: var(--cpa-dark);
    background: var(--cpa-bg-alt);
}
.cpa-nav-link i {
    font-size: 16px;
    width: 20px;
    text-align: center;
    flex-shrink: 0;
}
.cpa-nav-active {
    color: var(--cpa-primary);
    background: rgba(255,121,0,0.06);
    border-left-color: var(--cpa-primary);
    font-weight: 600;
}
.cpa-nav-active i {
    color: var(--cpa-primary);
}
.cpa-nav-back {
    color: var(--cpa-text-muted);
    font-size: 12px;
}
.cpa-nav-back:hover {
    color: var(--cpa-dark);
}

/* ── Main ── */
.cpa-main {
    flex-grow: 1;
    padding: 24px;
    min-width: 0;
    overflow-y: auto;
    background: var(--cpa-bg-alt);
    background-image: radial-gradient(circle, rgba(0,0,0,0.025) 1px, transparent 1px);
    background-size: 24px 24px;
}

/* ── Overlay mobile ── */
.cpa-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.3);
    z-index: 1040;
}

/* ── Réutilisables ── */
.cpa-card {
    background: var(--cpa-bg);
    border: 1px solid var(--cpa-border);
    border-radius: var(--cpa-radius);
    box-shadow: var(--cpa-shadow);
    transition: box-shadow 0.2s;
}
.cpa-card:hover {
    box-shadow: var(--cpa-shadow-lg);
}
.cpa-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--cpa-border);
    font-weight: 700;
    font-size: 14px;
    color: var(--cpa-dark);
}
.cpa-card-body {
    padding: 20px;
}
.cpa-stat-value {
    font-family: 'Outfit', sans-serif;
    font-size: 28px;
    font-weight: 800;
    color: var(--cpa-dark);
    line-height: 1.2;
}
.cpa-stat-label {
    font-size: 12px;
    font-weight: 500;
    color: var(--cpa-text-muted);
    margin-top: 4px;
}
.cpa-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}
.cpa-badge-success {
    background: #e8f5e9;
    color: #2e7d32;
}
.cpa-badge-warning {
    background: #fff3e0;
    color: #e65100;
}
.cpa-badge-danger {
    background: #fdecea;
    color: #c62828;
}
.cpa-badge-info {
    background: #e3f2fd;
    color: #1565c0;
}
.cpa-badge-neutral {
    background: #f3f4f6;
    color: #6b7280;
}
.cpa-panel {
    background: var(--cpa-bg);
    border: 1px solid var(--cpa-border);
    border-radius: var(--cpa-radius);
    box-shadow: var(--cpa-shadow);
}
.cpa-panel-title {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--cpa-text-muted);
    margin-bottom: 16px;
}

/* ── Animations ── */
@keyframes cpa-fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.cpa-animate {
    animation: cpa-fadeInUp 0.4s ease forwards;
}
.cpa-animate-d1 { animation-delay: 0.05s; }
.cpa-animate-d2 { animation-delay: 0.1s; }
.cpa-animate-d3 { animation-delay: 0.15s; }
.cpa-animate-d4 { animation-delay: 0.2s; }
.cpa-animate-d5 { animation-delay: 0.25s; }

/* ── Progress bar ── */
.cpa-progress {
    height: 6px;
    background: #f0f0f0;
    border-radius: 3px;
    overflow: hidden;
}
.cpa-progress-bar {
    height: 100%;
    border-radius: 3px;
    transition: width 0.6s ease;
    background: var(--cpa-primary);
}
.cpa-progress-bar-green { background: #2e7d32; }
.cpa-progress-bar-blue { background: #1565c0; }

/* ── Table stylée ── */
.cpa-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.cpa-table th {
    text-align: left;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--cpa-text-muted);
    padding: 10px 14px;
    border-bottom: 1px solid var(--cpa-border);
}
.cpa-table td {
    padding: 10px 14px;
    border-bottom: 1px solid var(--cpa-border);
    color: var(--cpa-text);
}
.cpa-table tr:hover td {
    background: var(--cpa-bg-alt);
}

/* ══ RESPONSIVE & MOBILE BOTTOM BAR ══ */
.cpa-bottombar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 60px;
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-top: 1px solid var(--cpa-border);
    display: flex;
    align-items: center;
    justify-content: space-around;
    z-index: 1030;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
    padding-bottom: env(safe-area-inset-bottom);
}
.cpa-bottombar-link {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: var(--cpa-text-muted);
    text-decoration: none;
    font-size: 10px;
    font-weight: 500;
    flex: 1;
    height: 100%;
    gap: 2px;
    transition: color 0.15s ease;
}
.cpa-bottombar-link:hover {
    color: var(--cpa-primary);
}
.cpa-bottombar-link i {
    font-size: 18px;
}
.cpa-bottombar-link.cpa-bottombar-active {
    color: var(--cpa-primary);
    font-weight: 600;
}

@media (max-width: 767.98px) {
    .cpa-topbar { padding: 0 12px; }
    .cpa-logo-text { display: none; }
    .cpa-main { 
        padding: 12px;
        padding-bottom: 80px; /* Leave space for mobile bottom bar */
    }
    .cpa-sidebar {
        display: none; /* Hide sidebar completely on mobile since we have bottom bar */
    }
    .cpa-menu-btn {
        display: none; /* Hide toggle menu button on mobile since we use bottom bar */
    }
    .cpa-stat-value { font-size: 22px; }
}
@media (min-width: 768px) and (max-width: 1024px) {
    .cpa-sidebar { width: 80px; min-width: 80px; }
    .cpa-sidebar span { display: none; } /* Hide labels on tablet */
    .cpa-sidebar .cpa-nav-link { justify-content: center; padding: 12px 0; }
    .cpa-sidebar .cpa-nav-link i { font-size: 20px; margin: 0; }
    .cpa-main { padding: 16px; }
}

</style>
