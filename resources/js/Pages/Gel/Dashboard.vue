<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../Layouts/GelLayout.vue';
import { authStore } from '../../stores/auth';

const stats   = ref(null);
const loading = ref(true);
const error   = ref(null);
const activeTab = ref('accueil');

const fetchStats = async () => {
    loading.value = true; error.value = null;
    try {
        const res = await fetch('/api/stats');
        if (!res.ok) throw new Error('Erreur lors du chargement des statistiques');
        stats.value = await res.json();
    } catch (e) { error.value = e.message; }
    finally { loading.value = false; }
};

onMounted(fetchStats);

// ── Onglets style iSupplier ───────────────────────────────────────
const tabs = [
    { key: 'accueil',        label: 'Accueil',        icon: 'bi-house' },
    { key: 'clients',        label: 'Clients',         icon: 'bi-building' },
    { key: 'missions',       label: 'Missions',        icon: 'bi-check2-square' },
    { key: 'commandes',      label: 'Commandes',       icon: 'bi-cart-check' },
    { key: 'finance',        label: 'Finance ERP',     icon: 'bi-receipt' },
    { key: 'administration', label: 'Administration',  icon: 'bi-gear' },
];

// ── Liens rapides par section ─────────────────────────────────────
const quickLinks = {
    accueil: [
        { group: 'Clients', links: [
            { label: 'Liste des clients',   href: '/clients',           icon: 'bi-building' },
            { label: 'Nouveau client',      href: '/clients/create',    icon: 'bi-plus-circle' },
        ]},
        { group: 'Missions', links: [
            { label: 'Toutes les missions', href: '/missions',          icon: 'bi-check2-square' },
            { label: 'Nouvelle mission',    href: '/missions/create',   icon: 'bi-plus-circle' },
        ]},
        { group: 'Accès rapide', links: [
            { label: 'Catalogue GEL',       href: '/nos-services',      icon: 'bi-shop' },
            { label: 'Commandes en cours',  href: '/admin/catalogue/orders', icon: 'bi-cart' },
        ]},
    ],
    clients: [
        { group: 'Clients', links: [
            { label: 'Liste des clients',   href: '/clients',           icon: 'bi-building' },
            { label: 'Nouveau client',      href: '/clients/create',    icon: 'bi-plus-circle' },
            { label: 'Pôles',               href: '/poles',             icon: 'bi-diagram-3' },
            { label: 'Services',            href: '/services',          icon: 'bi-grid-3x3-gap' },
        ]},
        { group: 'Licences', links: [
            { label: 'Licences actives',    href: '/licenses',          icon: 'bi-key' },
            { label: 'Admins Entreprise',   href: '/company-admins',    icon: 'bi-person-badge' },
        ]},
    ],
    missions: [
        { group: 'Missions', links: [
            { label: 'Toutes les missions', href: '/missions',          icon: 'bi-check2-square' },
            { label: 'Nouvelle mission',    href: '/missions/create',   icon: 'bi-plus-circle' },
        ]},
        { group: 'Documents', links: [
            { label: 'Dossiers clients',    href: '/clients',           icon: 'bi-folder2-open' },
        ]},
    ],
    commandes: [
        { group: 'Commandes', links: [
            { label: 'Commandes en cours',  href: '/admin/catalogue/orders',          icon: 'bi-cart-check' },
            { label: 'Archives',            href: '/admin/catalogue/orders/archives', icon: 'bi-archive' },
            { label: 'Catalogue services',  href: '/admin/catalogue/services',        icon: 'bi-tags' },
        ]},
        { group: 'Catalogue', links: [
            { label: 'Nos services publics',href: '/nos-services',      icon: 'bi-shop' },
            { label: 'Demandes clients',    href: '/admin/requests',    icon: 'bi-envelope' },
        ]},
    ],
    finance: [
        { group: 'Finance ERP', links: [
            { label: 'Facturation',         href: '/erp/invoices',      icon: 'bi-receipt' },
            { label: 'Trésorerie',          href: '/erp/treasury',      icon: 'bi-cash-stack' },
            { label: 'Stocks',              href: '/erp/stocks',        icon: 'bi-boxes' },
        ]},
        { group: 'RH', links: [
            { label: 'RH & Paie',           href: '/erp/hr',            icon: 'bi-people' },
        ]},
    ],
    administration: [
        { group: 'Organisation', links: [
            { label: 'Pôles',               href: '/poles',             icon: 'bi-diagram-3' },
            { label: 'Services',            href: '/services',          icon: 'bi-grid-3x3-gap' },
            { label: 'Licences',            href: '/licenses',          icon: 'bi-key' },
        ]},
        { group: 'Utilisateurs', links: [
            { label: 'Admins Entreprise',   href: '/company-admins',    icon: 'bi-person-badge' },
            { label: 'Demandes entrantes',  href: '/admin/requests',    icon: 'bi-envelope' },
            { label: 'Paramètres',          href: '/settings',          icon: 'bi-gear' },
        ]},
    ],
};

const currentLinks = () => quickLinks[activeTab.value] || [];

// ── Helpers ───────────────────────────────────────────────────────
const fmtNum = (n) => Number(n || 0).toLocaleString('fr-FR');
const statusLabel = (s) => ({ en_attente:'En attente', en_cours:'En cours', terminee:'Terminée', annulee:'Annulée' }[s] || s);
const statusBadge = (s) => ({
    en_attente: 'isup-status isup-status-warn',
    en_cours:   'isup-status isup-status-blue',
    terminee:   'isup-status isup-status-green',
    annulee:    'isup-status isup-status-red',
}[s] || 'isup-status isup-status-grey');

const barHeight = (val, allVals) => {
    const max = Math.max(...allVals.map(r => r.total || 0), 1);
    return Math.max(4, Math.round((val / max) * 130)) + 'px';
};
</script>

<template>
    <GelLayout page-title="Tableau de bord">

        <!-- ── Loading ── -->
        <div v-if="loading" class="d-flex align-items-center justify-content-center py-5 gap-3">
            <div class="isup-spinner"></div>
            <span style="color:#888; font-size:14px;">Chargement en cours…</span>
        </div>

        <!-- ── Error ── -->
        <div v-else-if="error" class="isup-alert-error mb-3">
            <i class="bi-exclamation-triangle-fill me-2"></i>{{ error }}
            <button @click="fetchStats" class="isup-btn-primary ms-3" style="padding:4px 12px; font-size:12px;">
                <i class="bi-arrow-clockwise me-1"></i>Réessayer
            </button>
        </div>

        <!-- ── iSupplier Shell ── -->
        <template v-else-if="stats">
        <div class="isup-shell">

            <!-- ══ HEADER BLEU (style Oracle iSupplier) ══ -->
            <div class="isup-portal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="isup-portal-logo">
                        <i class="bi-gem" style="font-size:20px;"></i>
                    </div>
                    <div>
                        <div class="isup-portal-company">GEL Cabinet — Super Administration</div>
                        <div class="isup-portal-sub">
                            Connecté en tant que : {{ authStore.user?.name }} &nbsp;|&nbsp;
                            {{ new Date().toLocaleDateString('fr-FR', { weekday:'long', day:'numeric', month:'long', year:'numeric' }) }}
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <div class="isup-stat-pill">
                        <span class="isup-stat-num">{{ fmtNum(stats.total_clients) }}</span>
                        <span class="isup-stat-lbl">Clients</span>
                    </div>
                    <div class="isup-stat-pill">
                        <span class="isup-stat-num">{{ fmtNum(stats.total_missions) }}</span>
                        <span class="isup-stat-lbl">Missions</span>
                    </div>
                    <div class="isup-stat-pill">
                        <span class="isup-stat-num">{{ fmtNum(stats.pending_missions) }}</span>
                        <span class="isup-stat-lbl">En attente</span>
                    </div>
                    <div class="isup-stat-pill">
                        <span class="isup-stat-num">{{ fmtNum(stats.total_poles) }}</span>
                        <span class="isup-stat-lbl">Pôles</span>
                    </div>
                </div>
            </div>

            <!-- ══ ONGLETS HORIZONTAUX avec NOTCH (iSupplier style) ══ -->
            <div class="isup-tabs-bar">
                <button v-for="tab in tabs" :key="tab.key"
                        class="isup-tab"
                        :class="{ 'isup-tab-active': activeTab === tab.key }"
                        @click="activeTab = tab.key">
                    <i :class="tab.icon"></i>
                    {{ tab.label }}
                    <span v-if="activeTab === tab.key" class="isup-tab-notch"></span>
                </button>
            </div>

            <!-- ══ CONTENU ══ -->
            <div class="isup-content d-flex">

                <!-- ── Sidebar Liens rapides ── -->
                <aside class="isup-quicklinks">
                    <div class="isup-ql-title">Liens rapides</div>
                    <div v-for="group in currentLinks()" :key="group.group" class="isup-ql-group">
                        <div class="isup-ql-group-label">{{ group.group }}</div>
                        <ul class="isup-ql-list">
                            <li v-for="link in group.links" :key="link.label">
                                <a :href="link.href" class="isup-ql-link">
                                    <i :class="link.icon" class="isup-ql-icon"></i>
                                    {{ link.label }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </aside>

                <!-- ── Main ── -->
                <main class="isup-main flex-grow-1">

                    <!-- ══ ACCUEIL ══ -->
                    <template v-if="activeTab === 'accueil'">

                        <!-- Barre de recherche / bienvenue -->
                        <div class="isup-search-bar mb-3">
                            <div class="isup-search-label">
                                <i class="bi-speedometer2 me-2" style="color:#FF7900;"></i>
                                Vue d'ensemble — Tableau de bord administrateur
                            </div>
                            <div class="d-flex gap-2 flex-wrap mt-2">
                                <a href="/clients/create" class="isup-btn-primary">
                                    <i class="bi-plus-circle me-1"></i>Nouveau client
                                </a>
                                <a href="/missions/create" class="isup-btn-outline-navy">
                                    <i class="bi-check2-square me-1"></i>Nouvelle mission
                                </a>
                                <a href="/admin/catalogue/orders" class="isup-btn-outline-navy">
                                    <i class="bi-cart me-1"></i>Voir commandes
                                </a>
                            </div>
                        </div>

                        <!-- KPI row -->
                        <div class="row g-3 mb-3">
                            <div class="col-6 col-md-3">
                                <div class="isup-kpi-card">
                                    <div class="isup-kpi-icon" style="background:#fff3e0; color:#FF7900;"><i class="bi-building"></i></div>
                                    <div class="isup-kpi-val">{{ fmtNum(stats.total_clients) }}</div>
                                    <div class="isup-kpi-lbl">Total Clients</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="isup-kpi-card">
                                    <div class="isup-kpi-icon" style="background:#e3f2fd; color:#1565c0;"><i class="bi-person-check"></i></div>
                                    <div class="isup-kpi-val">{{ fmtNum(stats.active_clients) }}</div>
                                    <div class="isup-kpi-lbl">Clients Actifs</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="isup-kpi-card">
                                    <div class="isup-kpi-icon" style="background:#e8f5e9; color:#2e7d32;"><i class="bi-check-circle"></i></div>
                                    <div class="isup-kpi-val">{{ fmtNum(stats.completed_missions) }}</div>
                                    <div class="isup-kpi-lbl">Missions terminées</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="isup-kpi-card">
                                    <div class="isup-kpi-icon" style="background:#fff8e1; color:#f57f17;"><i class="bi-hourglass-split"></i></div>
                                    <div class="isup-kpi-val">{{ fmtNum(stats.pending_missions) }}</div>
                                    <div class="isup-kpi-lbl">En attente</div>
                                </div>
                            </div>
                        </div>

                        <!-- Tableaux -->
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="isup-panel">
                                    <div class="isup-panel-header">
                                        <i class="bi-building me-2" style="color:#FF7900;"></i>Clients récents
                                        <a href="/clients" class="isup-panel-link ms-auto">Voir tout <i class="bi-arrow-right"></i></a>
                                    </div>
                                    <div class="isup-panel-body p-0">
                                        <div v-if="!stats.recent_clients?.length" class="text-center py-4 text-muted" style="font-size:13px;">
                                            <i class="bi-inbox" style="font-size:22px; display:block; margin-bottom:6px;"></i>Aucun client récent
                                        </div>
                                        <div v-else class="isup-table-wrap">
                                            <table class="isup-table w-100">
                                                <thead><tr>
                                                    <th>Société</th><th>Email</th><th class="text-center">Statut</th>
                                                </tr></thead>
                                                <tbody>
                                                    <tr v-for="c in stats.recent_clients" :key="c.id">
                                                        <td><a :href="'/clients/' + c.id" class="isup-link fw-semibold">{{ c.company_name }}</a></td>
                                                        <td class="text-muted" style="font-size:12px;">{{ c.email }}</td>
                                                        <td class="text-center">
                                                            <span :class="c.status === 'actif' ? 'isup-status isup-status-green' : 'isup-status isup-status-grey'">{{ c.status }}</span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="isup-panel">
                                    <div class="isup-panel-header">
                                        <i class="bi-check2-square me-2" style="color:#FF7900;"></i>Missions récentes
                                        <a href="/missions" class="isup-panel-link ms-auto">Voir tout <i class="bi-arrow-right"></i></a>
                                    </div>
                                    <div class="isup-panel-body p-0">
                                        <div v-if="!stats.recent_missions?.length" class="text-center py-4 text-muted" style="font-size:13px;">
                                            <i class="bi-inbox" style="font-size:22px; display:block; margin-bottom:6px;"></i>Aucune mission récente
                                        </div>
                                        <div v-else class="isup-table-wrap">
                                            <table class="isup-table w-100">
                                                <thead><tr>
                                                    <th>Titre</th><th>Statut</th><th>Avancement</th>
                                                </tr></thead>
                                                <tbody>
                                                    <tr v-for="m in stats.recent_missions" :key="m.id">
                                                        <td>
                                                            <div class="fw-semibold" style="font-size:13px;">{{ m.title }}</div>
                                                            <div class="text-muted" style="font-size:11px;">{{ m.client?.company_name || '—' }}</div>
                                                        </td>
                                                        <td><span :class="statusBadge(m.status)">{{ statusLabel(m.status) }}</span></td>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <div class="isup-progress-bar">
                                                                    <div class="isup-progress-fill" :style="{ width: (m.progress||0)+'%', background: m.progress>=100?'#2e7d32':'#FF7900' }"></div>
                                                                </div>
                                                                <span style="font-size:11px; color:#888;">{{ m.progress||0 }}%</span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Répartition pôles + Revenus -->
                        <div class="row g-3 mt-1">
                            <div class="col-lg-5">
                                <div class="isup-panel">
                                    <div class="isup-panel-header">
                                        <i class="bi-pie-chart me-2" style="color:#FF7900;"></i>Répartition par Pôle
                                    </div>
                                    <div class="isup-panel-body">
                                        <div v-if="!stats.pole_distribution?.length" class="text-center py-3 text-muted" style="font-size:13px;">Aucune donnée</div>
                                        <div v-else class="d-flex flex-column gap-3">
                                            <div v-for="p in stats.pole_distribution" :key="p.name" class="d-flex align-items-center gap-2">
                                                <span style="font-size:12px; font-weight:600; color:#163A5E; min-width:90px;">{{ p.name }}</span>
                                                <div style="flex-grow:1; height:8px; background:#eef3f9; border-radius:4px; overflow:hidden;">
                                                    <div :style="{ width: p.pourcentage+'%', height:'100%', background: p.color || '#FF7900', borderRadius:'4px' }"></div>
                                                </div>
                                                <span style="font-size:12px; font-weight:700; color:#163A5E; min-width:20px;">{{ p.count }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="isup-panel">
                                    <div class="isup-panel-header">
                                        <i class="bi-bar-chart me-2" style="color:#FF7900;"></i>Revenus Mensuels
                                    </div>
                                    <div class="isup-panel-body">
                                        <div v-if="!stats.monthly_revenue?.length" class="text-center py-3 text-muted" style="font-size:13px;">Aucune donnée</div>
                                        <div v-else class="isup-bar-chart">
                                            <div v-for="(item,i) in stats.monthly_revenue" :key="i" class="isup-bar-col">
                                                <div class="isup-bar-val">{{ fmtNum(item.total) }}</div>
                                                <div class="isup-bar" :style="{ height: barHeight(item.total, stats.monthly_revenue) }"></div>
                                                <div class="isup-bar-label">{{ (item.month||'').substring(5,7) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- ══ CLIENTS ══ -->
                    <template v-else-if="activeTab === 'clients'">
                        <div class="isup-search-bar mb-3">
                            <div class="isup-search-label"><i class="bi-building me-2" style="color:#FF7900;"></i>Gestion des Clients</div>
                            <div class="d-flex gap-2 mt-2">
                                <a href="/clients/create" class="isup-btn-primary"><i class="bi-plus-circle me-1"></i>Nouveau client</a>
                                <a href="/clients" class="isup-btn-outline-navy"><i class="bi-list me-1"></i>Voir tous</a>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-3 col-6">
                                <a href="/clients" class="isup-module-card">
                                    <div class="isup-mc-icon" style="background:#fff3e0; color:#FF7900;"><i class="bi-building"></i></div>
                                    <div class="isup-mc-label">Tous les clients</div>
                                </a>
                            </div>
                            <div class="col-md-3 col-6">
                                <a href="/clients/create" class="isup-module-card">
                                    <div class="isup-mc-icon" style="background:#e8f5e9; color:#2e7d32;"><i class="bi-plus-circle"></i></div>
                                    <div class="isup-mc-label">Nouveau client</div>
                                </a>
                            </div>
                            <div class="col-md-3 col-6">
                                <a href="/poles" class="isup-module-card">
                                    <div class="isup-mc-icon" style="background:#e3f2fd; color:#1565c0;"><i class="bi-diagram-3"></i></div>
                                    <div class="isup-mc-label">Pôles</div>
                                </a>
                            </div>
                            <div class="col-md-3 col-6">
                                <a href="/licenses" class="isup-module-card">
                                    <div class="isup-mc-icon" style="background:#f3e5f5; color:#6a1b9a;"><i class="bi-key"></i></div>
                                    <div class="isup-mc-label">Licences</div>
                                </a>
                            </div>
                            <div class="col-md-3 col-6">
                                <a href="/company-admins" class="isup-module-card">
                                    <div class="isup-mc-icon" style="background:#fce4ec; color:#c62828;"><i class="bi-person-badge"></i></div>
                                    <div class="isup-mc-label">Admins Entreprise</div>
                                </a>
                            </div>
                            <div class="col-md-3 col-6">
                                <a href="/services" class="isup-module-card">
                                    <div class="isup-mc-icon" style="background:#e0f7fa; color:#006064;"><i class="bi-grid-3x3-gap"></i></div>
                                    <div class="isup-mc-label">Services cabinet</div>
                                </a>
                            </div>
                        </div>
                        <div class="isup-panel">
                            <div class="isup-panel-header">
                                <i class="bi-building me-2" style="color:#FF7900;"></i>Clients récents
                                <a href="/clients" class="isup-panel-link ms-auto">Voir tout <i class="bi-arrow-right"></i></a>
                            </div>
                            <div class="isup-panel-body p-0">
                                <div class="isup-table-wrap">
                                    <table class="isup-table w-100">
                                        <thead><tr>
                                            <th>Société</th><th>Email</th><th>Téléphone</th><th class="text-center">Statut</th><th class="text-end">Action</th>
                                        </tr></thead>
                                        <tbody>
                                            <tr v-if="!stats.recent_clients?.length">
                                                <td colspan="5" class="text-center py-4 text-muted" style="font-size:13px;">
                                                    Aucun client. <a href="/clients/create" style="color:#FF7900; font-weight:700;">Créer le premier</a>
                                                </td>
                                            </tr>
                                            <tr v-for="c in stats.recent_clients" :key="c.id">
                                                <td><a :href="'/clients/' + c.id" class="isup-link fw-semibold">{{ c.company_name }}</a></td>
                                                <td class="text-muted" style="font-size:12px;">{{ c.email }}</td>
                                                <td class="text-muted" style="font-size:12px;">{{ c.phone || '—' }}</td>
                                                <td class="text-center">
                                                    <span :class="c.status === 'actif' ? 'isup-status isup-status-green' : 'isup-status isup-status-grey'">{{ c.status }}</span>
                                                </td>
                                                <td class="text-end">
                                                    <a :href="'/clients/' + c.id" class="isup-btn-primary" style="padding:4px 10px; font-size:11px;">
                                                        <i class="bi-eye me-1"></i>Voir
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- ══ MISSIONS ══ -->
                    <template v-else-if="activeTab === 'missions'">
                        <div class="isup-search-bar mb-3">
                            <div class="isup-search-label"><i class="bi-check2-square me-2" style="color:#FF7900;"></i>Gestion des Missions</div>
                            <div class="d-flex gap-2 mt-2">
                                <a href="/missions/create" class="isup-btn-primary"><i class="bi-plus-circle me-1"></i>Nouvelle mission</a>
                                <a href="/missions" class="isup-btn-outline-navy"><i class="bi-list me-1"></i>Toutes les missions</a>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-3 col-6">
                                <div class="isup-kpi-card">
                                    <div class="isup-kpi-icon" style="background:#fff3e0; color:#FF7900;"><i class="bi-collection"></i></div>
                                    <div class="isup-kpi-val">{{ fmtNum(stats.total_missions) }}</div>
                                    <div class="isup-kpi-lbl">Total missions</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="isup-kpi-card">
                                    <div class="isup-kpi-icon" style="background:#e3f2fd; color:#1565c0;"><i class="bi-arrow-right-circle"></i></div>
                                    <div class="isup-kpi-val">{{ fmtNum(stats.total_missions - stats.pending_missions - stats.completed_missions) }}</div>
                                    <div class="isup-kpi-lbl">En cours</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="isup-kpi-card">
                                    <div class="isup-kpi-icon" style="background:#fff8e1; color:#f57f17;"><i class="bi-hourglass-split"></i></div>
                                    <div class="isup-kpi-val">{{ fmtNum(stats.pending_missions) }}</div>
                                    <div class="isup-kpi-lbl">En attente</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="isup-kpi-card">
                                    <div class="isup-kpi-icon" style="background:#e8f5e9; color:#2e7d32;"><i class="bi-check-circle"></i></div>
                                    <div class="isup-kpi-val">{{ fmtNum(stats.completed_missions) }}</div>
                                    <div class="isup-kpi-lbl">Terminées</div>
                                </div>
                            </div>
                        </div>
                        <div class="isup-panel">
                            <div class="isup-panel-header">
                                <i class="bi-check2-square me-2" style="color:#FF7900;"></i>Missions récentes
                                <a href="/missions" class="isup-panel-link ms-auto">Voir tout <i class="bi-arrow-right"></i></a>
                            </div>
                            <div class="isup-panel-body p-0">
                                <div class="isup-table-wrap">
                                    <table class="isup-table w-100">
                                        <thead><tr>
                                            <th>Titre</th><th>Client</th><th class="text-center">Statut</th><th>Avancement</th><th class="text-end">Action</th>
                                        </tr></thead>
                                        <tbody>
                                            <tr v-if="!stats.recent_missions?.length">
                                                <td colspan="5" class="text-center py-4 text-muted" style="font-size:13px;">
                                                    Aucune mission. <a href="/missions/create" style="color:#FF7900; font-weight:700;">Créer la première</a>
                                                </td>
                                            </tr>
                                            <tr v-for="m in stats.recent_missions" :key="m.id">
                                                <td class="fw-semibold" style="font-size:13px;">{{ m.title }}</td>
                                                <td class="text-muted" style="font-size:12px;">{{ m.client?.company_name || '—' }}</td>
                                                <td class="text-center"><span :class="statusBadge(m.status)">{{ statusLabel(m.status) }}</span></td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="isup-progress-bar">
                                                            <div class="isup-progress-fill" :style="{ width:(m.progress||0)+'%', background: m.progress>=100?'#2e7d32':'#FF7900' }"></div>
                                                        </div>
                                                        <span style="font-size:11px; color:#888;">{{ m.progress||0 }}%</span>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <a :href="'/missions/' + m.id" class="isup-btn-primary" style="padding:4px 10px; font-size:11px;">
                                                        <i class="bi-eye me-1"></i>Voir
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- ══ COMMANDES ══ -->
                    <template v-else-if="activeTab === 'commandes'">
                        <div class="isup-search-bar mb-3">
                            <div class="isup-search-label"><i class="bi-cart-check me-2" style="color:#FF7900;"></i>Gestion des Commandes &amp; Catalogue</div>
                            <div class="d-flex gap-2 mt-2">
                                <a href="/admin/catalogue/orders" class="isup-btn-primary"><i class="bi-cart me-1"></i>Commandes en cours</a>
                                <a href="/admin/catalogue/services" class="isup-btn-outline-navy"><i class="bi-tags me-1"></i>Gérer le catalogue</a>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4 col-6">
                                <a href="/admin/catalogue/orders" class="isup-module-card">
                                    <div class="isup-mc-icon" style="background:#fff3e0; color:#FF7900;"><i class="bi-cart-check"></i></div>
                                    <div class="isup-mc-label">Commandes en cours</div>
                                </a>
                            </div>
                            <div class="col-md-4 col-6">
                                <a href="/admin/catalogue/orders/archives" class="isup-module-card">
                                    <div class="isup-mc-icon" style="background:#e3f2fd; color:#1565c0;"><i class="bi-archive"></i></div>
                                    <div class="isup-mc-label">Archives commandes</div>
                                </a>
                            </div>
                            <div class="col-md-4 col-6">
                                <a href="/admin/catalogue/services" class="isup-module-card">
                                    <div class="isup-mc-icon" style="background:#e8f5e9; color:#2e7d32;"><i class="bi-tags"></i></div>
                                    <div class="isup-mc-label">Catalogue services</div>
                                </a>
                            </div>
                            <div class="col-md-4 col-6">
                                <a href="/nos-services" class="isup-module-card">
                                    <div class="isup-mc-icon" style="background:#f3e5f5; color:#6a1b9a;"><i class="bi-shop"></i></div>
                                    <div class="isup-mc-label">Vitrine publique</div>
                                </a>
                            </div>
                            <div class="col-md-4 col-6">
                                <a href="/admin/requests" class="isup-module-card">
                                    <div class="isup-mc-icon" style="background:#fce4ec; color:#c62828;"><i class="bi-envelope"></i></div>
                                    <div class="isup-mc-label">Demandes clients</div>
                                </a>
                            </div>
                            <div class="col-md-4 col-6">
                                <a href="/mes-commandes" class="isup-module-card">
                                    <div class="isup-mc-icon" style="background:#e0f7fa; color:#006064;"><i class="bi-clock-history"></i></div>
                                    <div class="isup-mc-label">Suivi commandes</div>
                                </a>
                            </div>
                        </div>
                    </template>

                    <!-- ══ FINANCE ERP ══ -->
                    <template v-else-if="activeTab === 'finance'">
                        <div class="isup-search-bar mb-3">
                            <div class="isup-search-label"><i class="bi-receipt me-2" style="color:#FF7900;"></i>Modules Finance &amp; ERP</div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-3 col-6">
                                <a href="/erp/invoices" class="isup-module-card">
                                    <div class="isup-mc-icon" style="background:#fff3e0; color:#FF7900;"><i class="bi-receipt"></i></div>
                                    <div class="isup-mc-label">Facturation</div>
                                </a>
                            </div>
                            <div class="col-md-3 col-6">
                                <a href="/erp/treasury" class="isup-module-card">
                                    <div class="isup-mc-icon" style="background:#e3f2fd; color:#1565c0;"><i class="bi-cash-stack"></i></div>
                                    <div class="isup-mc-label">Trésorerie</div>
                                </a>
                            </div>
                            <div class="col-md-3 col-6">
                                <a href="/erp/stocks" class="isup-module-card">
                                    <div class="isup-mc-icon" style="background:#e8f5e9; color:#2e7d32;"><i class="bi-boxes"></i></div>
                                    <div class="isup-mc-label">Stocks</div>
                                </a>
                            </div>
                            <div class="col-md-3 col-6">
                                <a href="/erp/hr" class="isup-module-card">
                                    <div class="isup-mc-icon" style="background:#f3e5f5; color:#6a1b9a;"><i class="bi-people"></i></div>
                                    <div class="isup-mc-label">RH &amp; Paie</div>
                                </a>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="isup-panel">
                                    <div class="isup-panel-header"><i class="bi-bar-chart me-2" style="color:#FF7900;"></i>Revenus Mensuels</div>
                                    <div class="isup-panel-body">
                                        <div v-if="!stats.monthly_revenue?.length" class="text-center py-3 text-muted" style="font-size:13px;">Aucune donnée</div>
                                        <div v-else class="isup-bar-chart">
                                            <div v-for="(item,i) in stats.monthly_revenue" :key="i" class="isup-bar-col">
                                                <div class="isup-bar-val">{{ fmtNum(item.total) }}</div>
                                                <div class="isup-bar" :style="{ height: barHeight(item.total, stats.monthly_revenue) }"></div>
                                                <div class="isup-bar-label">{{ (item.month||'').substring(5,7) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="isup-panel">
                                    <div class="isup-panel-header"><i class="bi-pie-chart me-2" style="color:#FF7900;"></i>Répartition par Pôle</div>
                                    <div class="isup-panel-body">
                                        <div v-if="!stats.pole_distribution?.length" class="text-center py-3 text-muted" style="font-size:13px;">Aucune donnée</div>
                                        <div v-else class="d-flex flex-column gap-3">
                                            <div v-for="p in stats.pole_distribution" :key="p.name" class="d-flex align-items-center gap-2">
                                                <span style="font-size:12px; font-weight:600; color:#163A5E; min-width:80px;">{{ p.name }}</span>
                                                <div style="flex-grow:1; height:8px; background:#eef3f9; border-radius:4px; overflow:hidden;">
                                                    <div :style="{ width: p.pourcentage+'%', height:'100%', background: p.color || '#FF7900', borderRadius:'4px' }"></div>
                                                </div>
                                                <span style="font-size:12px; font-weight:700; color:#163A5E; min-width:20px;">{{ p.count }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- ══ ADMINISTRATION ══ -->
                    <template v-else-if="activeTab === 'administration'">
                        <div class="isup-search-bar mb-3">
                            <div class="isup-search-label"><i class="bi-gear me-2" style="color:#FF7900;"></i>Administration du Cabinet</div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-3 col-6">
                                <a href="/poles" class="isup-module-card">
                                    <div class="isup-mc-icon" style="background:#fff3e0; color:#FF7900;"><i class="bi-diagram-3"></i></div>
                                    <div class="isup-mc-label">Pôles</div>
                                </a>
                            </div>
                            <div class="col-md-3 col-6">
                                <a href="/services" class="isup-module-card">
                                    <div class="isup-mc-icon" style="background:#e3f2fd; color:#1565c0;"><i class="bi-grid-3x3-gap"></i></div>
                                    <div class="isup-mc-label">Services cabinet</div>
                                </a>
                            </div>
                            <div class="col-md-3 col-6">
                                <a href="/licenses" class="isup-module-card">
                                    <div class="isup-mc-icon" style="background:#e8f5e9; color:#2e7d32;"><i class="bi-key"></i></div>
                                    <div class="isup-mc-label">Licences</div>
                                </a>
                            </div>
                            <div class="col-md-3 col-6">
                                <a href="/company-admins" class="isup-module-card">
                                    <div class="isup-mc-icon" style="background:#f3e5f5; color:#6a1b9a;"><i class="bi-person-badge"></i></div>
                                    <div class="isup-mc-label">Admins Entreprise</div>
                                </a>
                            </div>
                            <div class="col-md-3 col-6">
                                <a href="/admin/requests" class="isup-module-card">
                                    <div class="isup-mc-icon" style="background:#fce4ec; color:#c62828;"><i class="bi-envelope"></i></div>
                                    <div class="isup-mc-label">Demandes entrantes</div>
                                </a>
                            </div>
                            <div class="col-md-3 col-6">
                                <a href="/settings" class="isup-module-card">
                                    <div class="isup-mc-icon" style="background:#e0f7fa; color:#006064;"><i class="bi-gear"></i></div>
                                    <div class="isup-mc-label">Paramètres</div>
                                </a>
                            </div>
                        </div>
                        <!-- Infos admin -->
                        <div class="isup-panel">
                            <div class="isup-panel-header"><i class="bi-person-circle me-2" style="color:#FF7900;"></i>Profil Administrateur</div>
                            <div class="isup-panel-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="isup-field-row">
                                            <div class="isup-field-label">Nom complet</div>
                                            <div class="isup-field-val">{{ authStore.user?.name || '—' }}</div>
                                        </div>
                                        <div class="isup-field-row">
                                            <div class="isup-field-label">Email</div>
                                            <div class="isup-field-val">{{ authStore.user?.email || '—' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="isup-field-row">
                                            <div class="isup-field-label">Rôle</div>
                                            <div class="isup-field-val">
                                                <span class="isup-status isup-status-blue">Super Admin</span>
                                            </div>
                                        </div>
                                        <div class="isup-field-row">
                                            <div class="isup-field-label">Clients gérés</div>
                                            <div class="isup-field-val fw-bold" style="color:#FF7900;">{{ fmtNum(stats.total_clients) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                </main>
            </div>
        </div>
        </template>
    </GelLayout>
</template>

<style scoped>
/* ══ GEL Super Admin — style iSupplier Oracle (Orange + Blanc + Bleu navy) ══ */

/* ── Shell ────────────────────────────────────────────────── */
.isup-shell { border:1px solid #dce3ee; border-radius:6px; overflow:hidden; background:#fff; }

/* ── Header bleu navy ─────────────────────────────────────── */
.isup-portal-header {
    background: linear-gradient(135deg, #163A5E 0%, #1e4d7a 100%);
    padding: 18px 22px;
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 14px;
    border-bottom: 3px solid #FF7900;
}
.isup-portal-logo {
    width: 46px; height: 46px; background: #FF7900; color: #fff;
    border-radius: 4px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.isup-portal-company { font-family:'Outfit',sans-serif; font-size:17px; font-weight:800; color:#fff; }
.isup-portal-sub { font-size:11px; color:rgba(255,255,255,.6); margin-top:3px; }
.isup-stat-pill { background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.2); border-radius:4px; padding:7px 16px; text-align:center; }
.isup-stat-num { display:block; font-size:20px; font-weight:800; color:#FF7900; font-family:'Outfit',sans-serif; }
.isup-stat-lbl { display:block; font-size:10px; color:rgba(255,255,255,.6); }

/* ── Onglets avec NOTCH ───────────────────────────────────── */
.isup-tabs-bar { display:flex; background:#163A5E; border-bottom:1px solid #dce3ee; overflow-x:auto; }
.isup-tabs-bar::-webkit-scrollbar { height:3px; }
.isup-tabs-bar::-webkit-scrollbar-thumb { background:#FF7900; }
.isup-tab {
    position:relative; background:transparent; border:none;
    color:rgba(255,255,255,.65); font-size:13px; font-weight:600;
    padding:12px 20px; cursor:pointer; white-space:nowrap;
    border-right:1px solid rgba(255,255,255,.08);
    transition:all 0.15s; display:flex; align-items:center; gap:6px;
}
.isup-tab:hover { color:#fff; background:rgba(255,255,255,.08); }
.isup-tab.isup-tab-active { background:#fff; color:#163A5E; font-weight:700; }
.isup-tab-notch {
    position:absolute; bottom:0; left:50%; transform:translateX(-50%);
    width:0; height:0;
    border-left:8px solid transparent; border-right:8px solid transparent; border-bottom:8px solid #FF7900;
}

/* ── Content layout ───────────────────────────────────────── */
.isup-content { min-height:500px; background:#f5f7fb; }

/* ── Quick links sidebar ──────────────────────────────────── */
.isup-quicklinks { width:190px; min-width:190px; background:#fff; border-right:1px solid #dce3ee; padding:12px 0; font-size:12px; }
.isup-ql-title { font-size:10px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; color:#FF7900; padding:0 14px 8px; border-bottom:1px solid #ffe0b2; margin-bottom:4px; }
.isup-ql-group { padding:8px 0 4px; }
.isup-ql-group-label { font-size:10px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:#163A5E; padding:4px 14px; margin-bottom:2px; }
.isup-ql-list { list-style:none; padding:0; margin:0; }
.isup-ql-link { display:flex; align-items:center; gap:7px; padding:6px 14px; color:#444; text-decoration:none; font-size:12px; transition:all 0.12s; border-left:2px solid transparent; }
.isup-ql-link:hover { color:#FF7900; background:#fff3e0; border-left-color:#FF7900; }
.isup-ql-icon { font-size:12px; width:14px; text-align:center; color:#888; flex-shrink:0; }

/* ── Main panel ───────────────────────────────────────────── */
.isup-main { padding:16px; min-width:0; }

/* ── Search / action bar ──────────────────────────────────── */
.isup-search-bar { background:#fff; border:1px solid #dce3ee; border-radius:4px; padding:14px 16px; }
.isup-search-label { font-size:13px; font-weight:700; color:#163A5E; }

/* ── KPI Cards ────────────────────────────────────────────── */
.isup-kpi-card { background:#fff; border:1px solid #dce3ee; border-radius:4px; padding:16px 14px; transition:box-shadow 0.15s, border-color 0.15s; }
.isup-kpi-card:hover { box-shadow:0 4px 14px rgba(22,58,94,.10); border-color:#FF7900; }
.isup-kpi-icon { width:40px; height:40px; border-radius:4px; display:flex; align-items:center; justify-content:center; font-size:18px; margin-bottom:10px; }
.isup-kpi-val { font-family:'Outfit',sans-serif; font-size:26px; font-weight:800; color:#163A5E; line-height:1; margin-bottom:4px; }
.isup-kpi-lbl { font-size:12px; color:#888; font-weight:600; }

/* ── Module cards ─────────────────────────────────────────── */
.isup-module-card { display:flex; flex-direction:column; align-items:center; background:#fff; border:1px solid #dce3ee; border-radius:4px; padding:18px 10px; text-decoration:none; text-align:center; transition:box-shadow 0.15s, transform 0.1s, border-color 0.15s; }
.isup-module-card:hover { box-shadow:0 4px 16px rgba(22,58,94,.12); transform:translateY(-2px); border-color:#FF7900; }
.isup-mc-icon { width:50px; height:50px; border-radius:4px; display:flex; align-items:center; justify-content:center; font-size:22px; margin-bottom:10px; }
.isup-mc-label { font-size:12px; font-weight:700; color:#163A5E; line-height:1.3; }

/* ── Panels ───────────────────────────────────────────────── */
.isup-panel { background:#fff; border:1px solid #dce3ee; border-radius:4px; overflow:hidden; }
.isup-panel-header { background:linear-gradient(90deg,#163A5E,#1e4d7a); color:#fff; font-size:13px; font-weight:700; padding:10px 14px; display:flex; align-items:center; }
.isup-panel-link { font-size:12px; color:rgba(255,255,255,.7); text-decoration:none; font-weight:600; transition:color 0.15s; }
.isup-panel-link:hover { color:#FF7900; }
.isup-panel-body { padding:14px; }

/* ── Tables ───────────────────────────────────────────────── */
.isup-table-wrap { overflow-x:auto; }
.isup-table { border-collapse:collapse; font-size:13px; }
.isup-table thead th { background:#EEF3F9; color:#163A5E; font-weight:700; font-size:11px; text-transform:uppercase; letter-spacing:.04em; border-bottom:1px solid #dce3ee; padding:9px 12px; white-space:nowrap; }
.isup-table td { padding:9px 12px; border-bottom:1px solid #f0f4f8; vertical-align:middle; }
.isup-table tbody tr:hover { background:#f8fbff; }
.isup-link { color:#163A5E; text-decoration:none; transition:color 0.12s; }
.isup-link:hover { color:#FF7900; }

/* ── Status badges ────────────────────────────────────────── */
.isup-status { display:inline-flex; align-items:center; font-size:11px; font-weight:700; padding:3px 10px; border-radius:4px; text-transform:uppercase; letter-spacing:.04em; }
.isup-status-green  { background:#e8f5e9; color:#2e7d32; }
.isup-status-blue   { background:#e3f2fd; color:#1565c0; }
.isup-status-warn   { background:#fff8e1; color:#f57f17; }
.isup-status-red    { background:#fdecea; color:#c62828; }
.isup-status-grey   { background:#f3f4f6; color:#666; }

/* ── Progress ─────────────────────────────────────────────── */
.isup-progress-bar { flex-grow:1; height:6px; background:#eee; border-radius:3px; overflow:hidden; }
.isup-progress-fill { height:100%; border-radius:3px; }

/* ── Bar chart ────────────────────────────────────────────── */
.isup-bar-chart { display:flex; align-items:flex-end; gap:6px; height:150px; padding-bottom:22px; }
.isup-bar-col { display:flex; flex-direction:column; align-items:center; flex:1; justify-content:flex-end; position:relative; }
.isup-bar-val { font-size:9px; color:#888; margin-bottom:3px; white-space:nowrap; overflow:hidden; max-width:100%; }
.isup-bar { width:100%; background:linear-gradient(180deg,#FF7900 0%,#e06700 100%); border-radius:3px 3px 0 0; min-height:4px; }
.isup-bar-label { position:absolute; bottom:-18px; font-size:11px; color:#888; font-weight:600; }

/* ── Buttons ──────────────────────────────────────────────── */
.isup-btn-primary { background:#FF7900; color:#fff; border:none; border-radius:4px; padding:8px 16px; font-size:13px; font-weight:700; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; transition:background 0.15s; }
.isup-btn-primary:hover { background:#e06700; color:#fff; }
.isup-btn-outline-navy { background:transparent; color:#163A5E; border:1px solid #163A5E; border-radius:4px; padding:7px 15px; font-size:13px; font-weight:600; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; transition:all 0.15s; }
.isup-btn-outline-navy:hover { background:#163A5E; color:#fff; }

/* ── Fields ───────────────────────────────────────────────── */
.isup-field-row { padding:10px 0; border-bottom:1px solid #f0f4f8; }
.isup-field-label { font-size:11px; font-weight:700; color:#163A5E; text-transform:uppercase; margin-bottom:3px; }
.isup-field-val { font-size:13px; color:#333; }

/* ── Alert ────────────────────────────────────────────────── */
.isup-alert-error { background:#fdecea; border:1px solid #f5c6c0; color:#c62828; border-radius:4px; padding:12px 16px; font-size:13px; display:flex; align-items:center; }
.isup-spinner { width:26px; height:26px; border:3px solid #ffe0b2; border-top-color:#FF7900; border-radius:50%; animation:spin .7s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }
</style>
