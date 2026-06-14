<script setup>
import { ref, computed, onMounted } from 'vue';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';

const company   = ref(null);
const licenses  = ref([]);
const stats     = ref({ user_count: 0, active_user_count: 0 });
const loading   = ref(true);
const error     = ref(null);
const activeTab = ref('accueil');

const clientId = window.__CLIENT_ID__;

const activeLicenses  = computed(() => licenses.value.filter(l => l.valid));
const expiredLicenses = computed(() => licenses.value.filter(l => !l.valid));

const loadData = async () => {
    if (!clientId) { loading.value = false; return; }
    try {
        const res  = await fetch(`/api/company/${clientId}/info`);
        if (!res.ok) throw new Error('Erreur serveur');
        const data = await res.json();
        company.value  = data.company;
        licenses.value = data.licenses;
        stats.value    = data.stats || { user_count: 0, active_user_count: 0 };
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

onMounted(loadData);

// ── Onglets (iSupplier style) ───────────────────────────────
const tabs = [
    { key: 'accueil',       label: "Accueil",          icon: 'bi-house' },
    { key: 'commandes',     label: "Commandes",        icon: 'bi-cart-check' },
    { key: 'finance',       label: "Finance",          icon: 'bi-receipt' },
    { key: 'comptabilite',  label: "Comptabilité",     icon: 'bi-calculator' },
    { key: 'secretariat',   label: "Secrétariat",      icon: 'bi-briefcase' },
    { key: 'administration',label: "Administration",   icon: 'bi-gear' },
];

// ── Liens rapides par section (sidebar iSupplier style) ─────
const quickLinks = {
    accueil: [
        { group: 'Commandes',   links: [
            { label: 'Mes commandes',       href: '/mes-commandes',         icon: 'bi-cart' },
            { label: 'Catalogue GEL',       href: '/nos-services',          icon: 'bi-shop' },
            { label: 'Suivi des demandes',  href: '/mes-commandes',         icon: 'bi-clock-history' },
        ]},
        { group: 'Services',   links: [
            { label: 'Mes services actifs', href: '/company/services',       icon: 'bi-grid' },
            { label: 'Licences',            href: '/company/services',       icon: 'bi-key' },
        ]},
        { group: 'Documents',  links: [
            { label: 'GED — Documents',     href: '/company/ged',           icon: 'bi-folder2-open' },
            { label: 'Ajouter un document', href: '/company/ged',           icon: 'bi-upload' },
        ]},
    ],
    commandes: [
        { group: 'Commandes',  links: [
            { label: 'Toutes les commandes', href: '/mes-commandes',        icon: 'bi-list-ul' },
            { label: 'Nouvelle commande',    href: '/nos-services',         icon: 'bi-plus-circle' },
            { label: 'En attente',           href: '/mes-commandes',        icon: 'bi-clock' },
            { label: 'Livrées',              href: '/mes-commandes',        icon: 'bi-check-circle' },
            { label: 'Annulées',             href: '/mes-commandes',        icon: 'bi-x-circle' },
        ]},
        { group: 'Documents',  links: [
            { label: 'Pièces jointes',       href: '/company/ged',          icon: 'bi-paperclip' },
        ]},
    ],
    finance: [
        { group: 'Factures',   links: [
            { label: 'Mes factures',         href: '/company/invoices',     icon: 'bi-receipt' },
            { label: 'Créer une facture',    href: '/company/invoices',     icon: 'bi-plus-circle' },
            { label: 'Voir les règlements',  href: '/company/invoices',     icon: 'bi-cash' },
        ]},
        { group: 'Trésorerie', links: [
            { label: 'Caisse',               href: '/company/caisse',       icon: 'bi-cash-stack' },
            { label: 'Coordonnées bancaires',href: '/company/profile',      icon: 'bi-bank' },
        ]},
    ],
    comptabilite: [
        { group: 'Comptabilité', links: [
            { label: 'Plan comptable',       href: '/company/accounting',   icon: 'bi-list-columns' },
            { label: 'Journaux',             href: '/company/accounting',   icon: 'bi-journal' },
            { label: 'Balance',              href: '/company/accounting',   icon: 'bi-bar-chart' },
            { label: 'Bilan',                href: '/company/accounting',   icon: 'bi-file-earmark-text' },
            { label: 'Compte de résultat',   href: '/company/accounting',   icon: 'bi-graph-up' },
        ]},
        { group: 'Déclarations', links: [
            { label: 'Déclarations fiscales',href: '/company/legal',        icon: 'bi-file-earmark-check' },
            { label: 'Social & Paie',        href: '/company/hr',           icon: 'bi-people' },
        ]},
    ],
    secretariat: [
        { group: 'Secrétariat', links: [
            { label: 'Dossiers clients',     href: '/company/legal',        icon: 'bi-folder' },
            { label: 'Gestion juridique',    href: '/company/legal',        icon: 'bi-briefcase' },
            { label: 'Constitution société', href: '/company/legal',        icon: 'bi-building' },
            { label: 'Courrier entrant',     href: '/company/ged',          icon: 'bi-envelope-open' },
            { label: 'Courrier sortant',     href: '/company/ged',          icon: 'bi-envelope' },
            { label: 'Formalités adm.',      href: '/company/legal',        icon: 'bi-clipboard-check' },
        ]},
        { group: 'RH', links: [
            { label: 'Contrats de travail',  href: '/company/hr',           icon: 'bi-file-person' },
            { label: 'Paie & Déclarations',  href: '/company/hr',           icon: 'bi-cash' },
        ]},
    ],
    administration: [
        { group: 'Mon profil', links: [
            { label: 'Organisation',         href: '/company/profile',      icon: 'bi-building' },
            { label: 'Carnet d\'adresses',   href: '/company/profile',      icon: 'bi-book' },
            { label: 'Répertoire contacts',  href: '/company/profile',      icon: 'bi-people' },
            { label: 'Préférences',          href: '/company/profile',      icon: 'bi-sliders' },
        ]},
        { group: 'Paramètres', links: [
            { label: 'Utilisateurs',         href: '/company/users',        icon: 'bi-person-badge' },
            { label: 'Règlement & facturation', href: '/company/invoices',  icon: 'bi-credit-card' },
            { label: 'Coordonnées bancaires',href: '/company/profile',      icon: 'bi-bank' },
        ]},
    ],
};

// ── Contenu principal par onglet ────────────────────────────
const tabContent = {
    accueil: {
        title: 'Vue d\'ensemble',
        sections: [
            { id: 'notifications', label: 'Notifications récentes' },
            { id: 'commandes_aper', label: 'Vue d\'ensemble des commandes' },
        ]
    }
};
</script>

<template>
    <CompanyLayout page-title="Portail Client GEL">
        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border" style="color: #FF7900;" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
        </div>

        <div v-else-if="error" class="isup-alert-error">
            <i class="bi-exclamation-triangle me-2"></i>{{ error }}
        </div>

        <template v-else>

            <!-- ══ iSupplier-style Portal Shell ══════════════════════════ -->
            <div class="isup-shell">

                <!-- Company banner (blue header iSupplier) -->
                <div class="isup-portal-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="isup-portal-logo">
                            <i class="bi-building" style="font-size:18px;"></i>
                        </div>
                        <div>
                            <div class="isup-portal-company">{{ company?.company_name || 'Mon Entreprise' }}</div>
                            <div class="isup-portal-sub">Portail Client — GEL Cabinet</div>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="isup-stat-pill">
                            <span class="isup-stat-num">{{ activeLicenses.length }}</span>
                            <span class="isup-stat-lbl">Services actifs</span>
                        </div>
                        <div class="isup-stat-pill">
                            <span class="isup-stat-num">{{ licenses.length }}</span>
                            <span class="isup-stat-lbl">Licences totales</span>
                        </div>
                        <div class="isup-stat-pill">
                            <span class="isup-stat-num">{{ stats.active_user_count }}</span>
                            <span class="isup-stat-lbl">Utilisateurs</span>
                        </div>
                    </div>
                </div>

                <!-- ── Onglets horizontaux (iSupplier Tab style) ────────── -->
                <div class="isup-tabs-bar">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        class="isup-tab"
                        :class="{ 'isup-tab-active': activeTab === tab.key }"
                        @click="activeTab = tab.key"
                    >
                        <i :class="tab.icon" class="me-1"></i>
                        {{ tab.label }}
                        <span v-if="activeTab === tab.key" class="isup-tab-notch"></span>
                    </button>
                </div>

                <!-- ── Content zone ──────────────────────────────────────── -->
                <div class="isup-content d-flex gap-0">

                    <!-- Sidebar de liens rapides (iSupplier Quick Links) -->
                    <aside class="isup-quicklinks">
                        <div class="isup-ql-title">Liens rapides</div>
                        <div v-for="group in (quickLinks[activeTab] || [])" :key="group.group" class="isup-ql-group">
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

                    <!-- Main panel -->
                    <main class="isup-main flex-grow-1">

                        <!-- ── ACCUEIL ─────────────────────────────────── -->
                        <template v-if="activeTab === 'accueil'">
                            <!-- Barre de recherche rapide iSupplier -->
                            <div class="isup-search-bar mb-4">
                                <div class="isup-search-label">Recherche rapide</div>
                                <div class="d-flex gap-2">
                                    <select class="isup-select">
                                        <option>Commandes</option>
                                        <option>Factures</option>
                                        <option>Documents</option>
                                        <option>Licences</option>
                                    </select>
                                    <input type="text" class="isup-input flex-grow-1" placeholder="Numéro, référence, description...">
                                    <button class="isup-btn-primary"><i class="bi-search me-1"></i>Rechercher</button>
                                </div>
                            </div>

                            <div class="row g-3">
                                <!-- Notifications -->
                                <div class="col-lg-7">
                                    <div class="isup-panel">
                                        <div class="isup-panel-header">
                                            <i class="bi-bell me-2" style="color:#FF7900;"></i>Notifications
                                        </div>
                                        <div class="isup-panel-body p-0">
                                            <div class="isup-table-wrap">
                                                <table class="isup-table w-100">
                                                    <thead>
                                                        <tr>
                                                            <th>Type</th>
                                                            <th>Message</th>
                                                            <th>Date</th>
                                                            <th>Statut</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="4" class="text-center py-4 text-muted" style="font-size:13px;">
                                                                <i class="bi-bell-slash me-2"></i>Aucune notification récente
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Réclamations rapides -->
                                <div class="col-lg-5">
                                    <div class="isup-panel h-100">
                                        <div class="isup-panel-header">
                                            <i class="bi-exclamation-circle me-2" style="color:#FF7900;"></i>Réclamations
                                            <a href="/mes-commandes" class="isup-panel-link ms-auto">Voir tout</a>
                                        </div>
                                        <div class="isup-panel-body">
                                            <div class="text-center py-3 text-muted" style="font-size:13px;">
                                                <i class="bi-check-circle me-1" style="color:#43a047;"></i>
                                                Aucune réclamation en cours
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Vue d'ensemble commandes -->
                                <div class="col-12">
                                    <div class="isup-panel">
                                        <div class="isup-panel-header">
                                            <i class="bi-cart me-2" style="color:#FF7900;"></i>Vue d'ensemble des commandes
                                            <a href="/mes-commandes" class="isup-panel-link ms-auto">
                                                <i class="bi-arrow-right me-1"></i>Voir toutes
                                            </a>
                                        </div>
                                        <div class="isup-panel-body p-0">
                                            <div class="isup-table-wrap">
                                                <table class="isup-table w-100">
                                                    <thead>
                                                        <tr>
                                                            <th>Référence</th>
                                                            <th>Service</th>
                                                            <th>Date</th>
                                                            <th>Montant</th>
                                                            <th>Statut</th>
                                                            <th></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="6" class="text-center py-4 text-muted" style="font-size:13px;">
                                                                <i class="bi-inbox me-2"></i>Aucune commande récente —
                                                                <a href="/nos-services" style="color:#FF7900; font-weight:600;">Parcourir le catalogue</a>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Services actifs / Livraisons -->
                                <div class="col-12">
                                    <div class="isup-panel">
                                        <div class="isup-panel-header">
                                            <i class="bi-key me-2" style="color:#FF7900;"></i>Licences &amp; Services actifs
                                            <span class="isup-count-badge ms-2">{{ licenses.length }}</span>
                                        </div>
                                        <div class="isup-panel-body p-0">
                                            <div class="isup-table-wrap">
                                                <table class="isup-table w-100">
                                                    <thead>
                                                        <tr>
                                                            <th>Service</th>
                                                            <th>Clé de licence</th>
                                                            <th>Début</th>
                                                            <th>Fin</th>
                                                            <th>Durée</th>
                                                            <th class="text-center">Statut</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="lic in licenses" :key="lic.id">
                                                            <td class="fw-semibold" style="font-size:13px;">{{ lic.service_name }}</td>
                                                            <td class="font-monospace" style="font-size:12px; color:#555;">{{ lic.license_key || '—' }}</td>
                                                            <td style="font-size:12px;">{{ lic.start_date }}</td>
                                                            <td style="font-size:12px;">{{ lic.end_date }}</td>
                                                            <td style="font-size:12px;">{{ lic.duration_months }} mois</td>
                                                            <td class="text-center">
                                                                <span :class="['isup-status', lic.valid ? 'isup-status-active' : 'isup-status-expired']">
                                                                    <i :class="lic.valid ? 'bi-check-circle-fill' : 'bi-x-circle-fill'" class="me-1"></i>
                                                                    {{ lic.valid ? 'Actif' : 'Expiré' }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        <tr v-if="!licenses.length">
                                                            <td colspan="6" class="text-center py-4 text-muted" style="font-size:13px;">
                                                                <i class="bi-key me-2"></i>Aucune licence active
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- ── COMMANDES ───────────────────────────────── -->
                        <template v-else-if="activeTab === 'commandes'">
                            <div class="isup-panel">
                                <div class="isup-panel-header">
                                    <i class="bi-cart me-2" style="color:#FF7900;"></i>Mes Commandes
                                    <div class="ms-auto d-flex gap-2">
                                        <a href="/nos-services" class="isup-btn-primary">
                                            <i class="bi-plus me-1"></i>Nouvelle commande
                                        </a>
                                    </div>
                                </div>
                                <div class="isup-panel-body">
                                    <div class="d-flex gap-2 mb-3">
                                        <input type="text" class="isup-input flex-grow-1" placeholder="Numéro de commande, service...">
                                        <select class="isup-select" style="width:160px;">
                                            <option>Tous les statuts</option>
                                            <option>En cours</option>
                                            <option>Livrée</option>
                                            <option>Annulée</option>
                                        </select>
                                        <button class="isup-btn-primary"><i class="bi-search me-1"></i>Rechercher</button>
                                    </div>
                                    <div class="isup-table-wrap">
                                        <table class="isup-table w-100">
                                            <thead>
                                                <tr>
                                                    <th>Référence</th>
                                                    <th>Service</th>
                                                    <th>Date commande</th>
                                                    <th class="text-end">Montant</th>
                                                    <th>Délai</th>
                                                    <th class="text-center">Statut</th>
                                                    <th class="text-end">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="7" class="text-center py-5 text-muted" style="font-size:13px;">
                                                        <i class="bi-inbox me-2" style="font-size:20px;display:block;margin-bottom:8px;"></i>
                                                        Aucune commande — <a href="/nos-services" style="color:#FF7900; font-weight:700;">Parcourir le catalogue</a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- ── FINANCE ─────────────────────────────────── -->
                        <template v-else-if="activeTab === 'finance'">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <a href="/company/invoices" class="isup-module-card">
                                        <div class="isup-mc-icon" style="background:#fff3e0; color:#FF7900;">
                                            <i class="bi-plus-circle"></i>
                                        </div>
                                        <div class="isup-mc-label">Créer une facture</div>
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="/company/invoices" class="isup-module-card">
                                        <div class="isup-mc-icon" style="background:#e3f2fd; color:#1565c0;">
                                            <i class="bi-receipt"></i>
                                        </div>
                                        <div class="isup-mc-label">Voir mes factures</div>
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="/company/invoices" class="isup-module-card">
                                        <div class="isup-mc-icon" style="background:#e8f5e9; color:#2e7d32;">
                                            <i class="bi-cash-stack"></i>
                                        </div>
                                        <div class="isup-mc-label">Voir les règlements</div>
                                    </a>
                                </div>
                                <div class="col-12">
                                    <div class="isup-panel">
                                        <div class="isup-panel-header">
                                            <i class="bi-receipt me-2" style="color:#FF7900;"></i>Factures récentes
                                        </div>
                                        <div class="isup-panel-body p-0">
                                            <div class="isup-table-wrap">
                                                <table class="isup-table w-100">
                                                    <thead>
                                                        <tr>
                                                            <th>N° Facture</th>
                                                            <th>Date</th>
                                                            <th>N° Commande</th>
                                                            <th class="text-end">Montant</th>
                                                            <th>Devise</th>
                                                            <th class="text-center">Statut</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="6" class="text-center py-4 text-muted" style="font-size:13px;">
                                                                <i class="bi-receipt me-2"></i>Aucune facture pour le moment
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- ── COMPTABILITÉ ────────────────────────────── -->
                        <template v-else-if="activeTab === 'comptabilite'">
                            <div class="row g-3 mb-4">
                                <div class="col-md-3 col-6">
                                    <a href="/company/accounting" class="isup-module-card">
                                        <div class="isup-mc-icon" style="background:#e3f2fd; color:#1565c0;"><i class="bi-list-columns"></i></div>
                                        <div class="isup-mc-label">Plan comptable</div>
                                    </a>
                                </div>
                                <div class="col-md-3 col-6">
                                    <a href="/company/accounting" class="isup-module-card">
                                        <div class="isup-mc-icon" style="background:#f3e5f5; color:#6a1b9a;"><i class="bi-journal"></i></div>
                                        <div class="isup-mc-label">Journaux</div>
                                    </a>
                                </div>
                                <div class="col-md-3 col-6">
                                    <a href="/company/accounting" class="isup-module-card">
                                        <div class="isup-mc-icon" style="background:#e8f5e9; color:#2e7d32;"><i class="bi-bar-chart"></i></div>
                                        <div class="isup-mc-label">Balance</div>
                                    </a>
                                </div>
                                <div class="col-md-3 col-6">
                                    <a href="/company/accounting" class="isup-module-card">
                                        <div class="isup-mc-icon" style="background:#fff3e0; color:#FF7900;"><i class="bi-graph-up"></i></div>
                                        <div class="isup-mc-label">Bilan &amp; Résultat</div>
                                    </a>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <a href="/company/hr" class="isup-module-card">
                                        <div class="isup-mc-icon" style="background:#fce4ec; color:#c62828;"><i class="bi-people"></i></div>
                                        <div class="isup-mc-label">Social &amp; Paie</div>
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="/company/legal" class="isup-module-card">
                                        <div class="isup-mc-icon" style="background:#e8f5e9; color:#2e7d32;"><i class="bi-file-earmark-check"></i></div>
                                        <div class="isup-mc-label">Déclarations fiscales</div>
                                    </a>
                                </div>
                            </div>
                        </template>

                        <!-- ── SECRÉTARIAT ──────────────────────────────── -->
                        <template v-else-if="activeTab === 'secretariat'">
                            <div class="row g-3 mb-4">
                                <div class="col-md-4 col-6">
                                    <a href="/company/legal" class="isup-module-card">
                                        <div class="isup-mc-icon" style="background:#e3f2fd; color:#1565c0;"><i class="bi-folder-fill"></i></div>
                                        <div class="isup-mc-label">Dossiers clients</div>
                                    </a>
                                </div>
                                <div class="col-md-4 col-6">
                                    <a href="/company/legal" class="isup-module-card">
                                        <div class="isup-mc-icon" style="background:#fff3e0; color:#FF7900;"><i class="bi-briefcase-fill"></i></div>
                                        <div class="isup-mc-label">Gestion juridique</div>
                                    </a>
                                </div>
                                <div class="col-md-4 col-6">
                                    <a href="/company/legal" class="isup-module-card">
                                        <div class="isup-mc-icon" style="background:#f3e5f5; color:#6a1b9a;"><i class="bi-building"></i></div>
                                        <div class="isup-mc-label">Constitution de société</div>
                                    </a>
                                </div>
                                <div class="col-md-4 col-6">
                                    <a href="/company/ged" class="isup-module-card">
                                        <div class="isup-mc-icon" style="background:#e8f5e9; color:#2e7d32;"><i class="bi-envelope-open"></i></div>
                                        <div class="isup-mc-label">Courrier entrant</div>
                                    </a>
                                </div>
                                <div class="col-md-4 col-6">
                                    <a href="/company/ged" class="isup-module-card">
                                        <div class="isup-mc-icon" style="background:#fce4ec; color:#c62828;"><i class="bi-envelope"></i></div>
                                        <div class="isup-mc-label">Courrier sortant</div>
                                    </a>
                                </div>
                                <div class="col-md-4 col-6">
                                    <a href="/company/legal" class="isup-module-card">
                                        <div class="isup-mc-icon" style="background:#e0f7fa; color:#006064;"><i class="bi-clipboard-check"></i></div>
                                        <div class="isup-mc-label">Formalités administratives</div>
                                    </a>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <a href="/company/hr" class="isup-module-card">
                                        <div class="isup-mc-icon" style="background:#fff3e0; color:#FF7900;"><i class="bi-file-person"></i></div>
                                        <div class="isup-mc-label">Contrats de travail</div>
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="/company/hr" class="isup-module-card">
                                        <div class="isup-mc-icon" style="background:#e8eef5; color:#1565c0;"><i class="bi-cash"></i></div>
                                        <div class="isup-mc-label">Paie &amp; Déclarations sociales</div>
                                    </a>
                                </div>
                            </div>
                        </template>

                        <!-- ── ADMINISTRATION ───────────────────────────── -->
                        <template v-else-if="activeTab === 'administration'">
                            <div class="row g-3 mb-4">
                                <div class="col-md-3 col-6">
                                    <a href="/company/profile" class="isup-module-card">
                                        <div class="isup-mc-icon" style="background:#e3f2fd; color:#1565c0;"><i class="bi-building"></i></div>
                                        <div class="isup-mc-label">Organisation</div>
                                    </a>
                                </div>
                                <div class="col-md-3 col-6">
                                    <a href="/company/profile" class="isup-module-card">
                                        <div class="isup-mc-icon" style="background:#fff3e0; color:#FF7900;"><i class="bi-book"></i></div>
                                        <div class="isup-mc-label">Carnet d'adresses</div>
                                    </a>
                                </div>
                                <div class="col-md-3 col-6">
                                    <a href="/company/profile" class="isup-module-card">
                                        <div class="isup-mc-icon" style="background:#e8f5e9; color:#2e7d32;"><i class="bi-people"></i></div>
                                        <div class="isup-mc-label">Contacts</div>
                                    </a>
                                </div>
                                <div class="col-md-3 col-6">
                                    <a href="/company/profile" class="isup-module-card">
                                        <div class="isup-mc-icon" style="background:#f3e5f5; color:#6a1b9a;"><i class="bi-bank"></i></div>
                                        <div class="isup-mc-label">Coord. bancaires</div>
                                    </a>
                                </div>
                            </div>
                            <!-- Profil card -->
                            <div class="isup-panel">
                                <div class="isup-panel-header">
                                    <i class="bi-person-circle me-2" style="color:#FF7900;"></i>Informations de l'entreprise
                                </div>
                                <div class="isup-panel-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="isup-field-row">
                                                <div class="isup-field-label">Nom de l'entreprise</div>
                                                <div class="isup-field-val">{{ company?.company_name || '—' }}</div>
                                            </div>
                                            <div class="isup-field-row">
                                                <div class="isup-field-label">Email</div>
                                                <div class="isup-field-val">{{ company?.email || '—' }}</div>
                                            </div>
                                            <div class="isup-field-row">
                                                <div class="isup-field-label">Téléphone</div>
                                                <div class="isup-field-val">{{ company?.phone || '—' }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="isup-field-row">
                                                <div class="isup-field-label">Adresse</div>
                                                <div class="isup-field-val">{{ company?.address || '—' }}</div>
                                            </div>
                                            <div class="isup-field-row">
                                                <div class="isup-field-label">Utilisateurs actifs</div>
                                                <div class="isup-field-val">{{ stats.active_user_count }} / {{ stats.user_count }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <a href="/company/profile" class="isup-btn-primary">
                                            <i class="bi-pencil me-1"></i>Modifier le profil
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </template>

                    </main>
                </div>
            </div>

        </template>
    </CompanyLayout>
</template>

<style scoped>
/* ══════════════════════════════════════════════════════════════
   GEL Portal — iSupplier layout : Orange + Blanc + Bleu
   ══════════════════════════════════════════════════════════════ */

/* ── Portal shell ────────────────────────────────────────── */
.isup-shell {
    border: 1px solid #dce3ee;
    border-radius: 4px;
    overflow: hidden;
    background: #fff;
}

/* ── Company banner (blue header) ────────────────────────── */
.isup-portal-header {
    background: linear-gradient(135deg, #163A5E 0%, #1e4d7a 100%);
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    border-bottom: 3px solid #FF7900;
}
.isup-portal-logo {
    width: 44px; height: 44px;
    background: #FF7900; color: #fff;
    border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.isup-portal-company {
    font-family: 'Outfit', sans-serif;
    font-size: 17px; font-weight: 800; color: #fff;
}
.isup-portal-sub {
    font-size: 11px; color: rgba(255,255,255,0.6); margin-top: 2px;
}
.isup-stat-pill {
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 4px; padding: 6px 14px; text-align: center;
}
.isup-stat-num {
    display: block;
    font-size: 20px; font-weight: 800; color: #FF7900;
    font-family: 'Outfit', sans-serif;
}
.isup-stat-lbl {
    display: block;
    font-size: 10px; color: rgba(255,255,255,0.6);
}

/* ── Tabs bar (iSupplier horizontal tabs with notch) ─────── */
.isup-tabs-bar {
    display: flex;
    background: #163A5E;
    border-bottom: 1px solid #dce3ee;
    overflow-x: auto;
}
.isup-tabs-bar::-webkit-scrollbar { height: 3px; }
.isup-tabs-bar::-webkit-scrollbar-thumb { background: #FF7900; }

.isup-tab {
    position: relative;
    background: transparent;
    border: none;
    color: rgba(255,255,255,0.65);
    font-size: 13px; font-weight: 600;
    padding: 11px 18px;
    cursor: pointer;
    white-space: nowrap;
    border-right: 1px solid rgba(255,255,255,0.08);
    transition: all 0.15s;
    display: flex; align-items: center; gap: 5px;
}
.isup-tab:hover { color: #fff; background: rgba(255,255,255,0.08); }
.isup-tab.isup-tab-active {
    background: #fff;
    color: #163A5E;
    font-weight: 700;
}
/* Notch indicator under active tab */
.isup-tab-notch {
    position: absolute;
    bottom: 0; left: 50%;
    transform: translateX(-50%);
    width: 0; height: 0;
    border-left: 8px solid transparent;
    border-right: 8px solid transparent;
    border-bottom: 8px solid #FF7900;
}

/* ── Content layout ──────────────────────────────────────── */
.isup-content {
    min-height: 480px;
    background: #f5f7fb;
}

/* ── Quick links sidebar ─────────────────────────────────── */
.isup-quicklinks {
    width: 190px;
    min-width: 190px;
    background: #fff;
    border-right: 1px solid #dce3ee;
    padding: 12px 0;
    font-size: 12px;
}
.isup-ql-title {
    font-size: 10px; font-weight: 800; letter-spacing: 0.1em;
    text-transform: uppercase; color: #FF7900;
    padding: 0 14px 8px; border-bottom: 1px solid #ffe0b2; margin-bottom: 4px;
}
.isup-ql-group { padding: 8px 0 4px; }
.isup-ql-group-label {
    font-size: 10px; font-weight: 700; letter-spacing: 0.08em;
    text-transform: uppercase; color: #163A5E;
    padding: 4px 14px; margin-bottom: 2px;
}
.isup-ql-list { list-style: none; padding: 0; margin: 0; }
.isup-ql-link {
    display: flex; align-items: center; gap: 7px;
    padding: 6px 14px; color: #444; text-decoration: none;
    font-size: 12px; transition: all 0.12s;
    border-left: 2px solid transparent;
}
.isup-ql-link:hover {
    color: #FF7900; background: #FFF3E0;
    border-left-color: #FF7900;
}
.isup-ql-icon { font-size: 12px; width: 14px; text-align: center; color: #888; flex-shrink: 0; }

/* ── Main panel ──────────────────────────────────────────── */
.isup-main { padding: 16px; min-width: 0; }

/* ── Search bar ──────────────────────────────────────────── */
.isup-search-bar {
    background: #fff; border: 1px solid #dce3ee;
    border-radius: 4px; padding: 14px 16px;
}
.isup-search-label {
    font-size: 11px; font-weight: 700; color: #163A5E;
    text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 8px;
}

/* ── Panels ──────────────────────────────────────────────── */
.isup-panel {
    background: #fff; border: 1px solid #dce3ee; border-radius: 4px;
    overflow: hidden;
}
.isup-panel-header {
    background: linear-gradient(90deg, #163A5E, #1e4d7a);
    color: #fff; font-size: 13px; font-weight: 700;
    padding: 10px 14px;
    display: flex; align-items: center;
}
.isup-panel-link {
    font-size: 12px; color: rgba(255,255,255,0.7);
    text-decoration: none; font-weight: 600;
    transition: color 0.15s;
}
.isup-panel-link:hover { color: #FF7900; }
.isup-panel-body { padding: 14px; }
.isup-count-badge {
    background: #FF7900; color: #fff;
    font-size: 10px; font-weight: 700;
    padding: 1px 7px; border-radius: 10px;
}

/* ── Tables ──────────────────────────────────────────────── */
.isup-table-wrap { overflow-x: auto; }
.isup-table { border-collapse: collapse; font-size: 13px; }
.isup-table thead th {
    background: #EEF3F9; color: #163A5E;
    font-weight: 700; font-size: 11px;
    text-transform: uppercase; letter-spacing: 0.04em;
    border-bottom: 1px solid #dce3ee;
    padding: 9px 12px; white-space: nowrap;
}
.isup-table td {
    padding: 9px 12px; border-bottom: 1px solid #f0f4f8;
    vertical-align: middle; white-space: nowrap;
}
.isup-table tbody tr:hover { background: #f8fbff; }

/* ── Status badges ───────────────────────────────────────── */
.isup-status {
    display: inline-flex; align-items: center;
    font-size: 11px; font-weight: 700;
    padding: 3px 10px; border-radius: 4px;
    text-transform: uppercase; letter-spacing: 0.04em;
}
.isup-status-active { background: #e8f5e9; color: #2e7d32; }
.isup-status-expired { background: #fdecea; color: #c62828; }

/* ── Module cards ────────────────────────────────────────── */
.isup-module-card {
    display: flex; flex-direction: column; align-items: center;
    background: #fff; border: 1px solid #dce3ee; border-radius: 4px;
    padding: 20px 12px; text-decoration: none; text-align: center;
    transition: box-shadow 0.15s, transform 0.1s, border-color 0.15s;
}
.isup-module-card:hover {
    box-shadow: 0 4px 16px rgba(22,58,94,0.12);
    transform: translateY(-2px);
    border-color: #FF7900;
}
.isup-mc-icon {
    width: 52px; height: 52px; border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; margin-bottom: 10px;
}
.isup-mc-label {
    font-size: 12px; font-weight: 700; color: #163A5E; line-height: 1.3;
}

/* ── Inputs ──────────────────────────────────────────────── */
.isup-input {
    border: 1px solid #dce3ee; border-radius: 4px;
    padding: 7px 12px; font-size: 13px; outline: none;
    transition: border-color 0.15s;
}
.isup-input:focus { border-color: #FF7900; box-shadow: 0 0 0 2px rgba(255,121,0,0.12); }
.isup-select {
    border: 1px solid #dce3ee; border-radius: 4px;
    padding: 7px 12px; font-size: 13px; background: #fff;
}

/* ── Buttons ─────────────────────────────────────────────── */
.isup-btn-primary {
    background: #FF7900; color: #fff; border: none;
    border-radius: 4px; padding: 8px 16px;
    font-size: 13px; font-weight: 700;
    cursor: pointer; text-decoration: none;
    display: inline-flex; align-items: center;
    transition: background 0.15s;
}
.isup-btn-primary:hover { background: #e06700; color: #fff; }

/* ── Admin fields ────────────────────────────────────────── */
.isup-field-row {
    padding: 10px 0; border-bottom: 1px solid #f0f4f8;
}
.isup-field-label { font-size: 11px; font-weight: 700; color: #163A5E; text-transform: uppercase; margin-bottom: 3px; }
.isup-field-val { font-size: 13px; color: #333; }

/* ── Alert ───────────────────────────────────────────────── */
.isup-alert-error {
    background: #fdecea; border: 1px solid #f5c6c0; color: #c62828;
    border-radius: 4px; padding: 12px 16px; font-size: 13px;
}
</style>
