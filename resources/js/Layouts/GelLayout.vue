<script setup>
import { ref, computed, onMounted } from 'vue';
import { authStore } from '../stores/auth';
import Omnisearch from '../Components/Omnisearch.vue';

const props = defineProps({
    pageTitle: { type: String, default: 'GEL Cabinet' }
});

const sidebarOpen = ref(true);
const isMobile = ref(false);

// Sections repliables — stocke les clés des sections réduites
const collapsedSections = ref(loadCollapsed());

function loadCollapsed() {
    try {
        return JSON.parse(localStorage.getItem('gel_collapsed_sections') || '[]');
    } catch { return []; }
}

function saveCollapsed() {
    localStorage.setItem('gel_collapsed_sections', JSON.stringify(collapsedSections.value));
}

function toggleSection(groupKey) {
    const idx = collapsedSections.value.indexOf(groupKey);
    if (idx > -1) {
        collapsedSections.value.splice(idx, 1);
    } else {
        collapsedSections.value.push(groupKey);
    }
    saveCollapsed();
}

function isSectionCollapsed(groupKey) {
    return collapsedSections.value.includes(groupKey);
}

// Mapping section → route de création ("+Nouveau")
const sectionNewRoutes = {
    administration:  '/personnel/create',
    consultation:    '/clients/create',
    fiscal:          '/accounting/entries/create',
    it:              '/it/tickets/create',
    social_paie:     '/rh/employees/create',
    juridique:       '/juridique/dossiers/create',
    logiciel_compta: '/admin/catalogue/orders/create',
    ia_automation:   '/ai/feed',
};

function getNewUrl(groupKey) {
    const route = sectionNewRoutes[groupKey];
    return route ? buildUrl(route) : '#';
}

// Detect mobile on mount
if (typeof window !== 'undefined') {
    isMobile.value = window.innerWidth < 992;
    window.addEventListener('resize', () => {
        isMobile.value = window.innerWidth < 992;
        if (!isMobile.value) sidebarOpen.value = true;
    });
}

const clients = ref([]);
const selectedClientId = ref(null);

const getCurrentClientId = () => {
    if (typeof window === 'undefined') return null;
    const parts = window.location.pathname.split('/');
    for (let i = parts.length - 1; i >= 0; i--) {
        if (/^\d+$/.test(parts[i])) {
            return parseInt(parts[i]);
        }
    }
    return null;
};

const isSubLinkActive = (linkHref) => {
    if (typeof window === 'undefined') return false;
    const currentPath = window.location.pathname;
    const targetUrl = buildContextUrl(linkHref);
    let targetPath = '';
    try {
        targetPath = new URL(targetUrl, window.location.origin).pathname;
    } catch(e) {
        targetPath = targetUrl;
    }
    const cleanCurrent = currentPath.endsWith('/') ? currentPath.slice(0, -1) : currentPath;
    const cleanTarget = targetPath.endsWith('/') ? targetPath.slice(0, -1) : targetPath;

    return cleanCurrent === cleanTarget || cleanCurrent.startsWith(cleanTarget + '/');
};

const handleClientChange = (event) => {
    const newId = event.target.value;
    if (!newId) return;

    const currentPath = window.location.pathname;
    const parts = currentPath.split('/');

    let idIndex = -1;
    for (let i = parts.length - 1; i >= 0; i--) {
        if (/^\d+$/.test(parts[i])) {
            idIndex = i;
            break;
        }
    }

    let newPath = '';
    if (idIndex !== -1) {
        parts[idIndex] = newId;
        newPath = parts.join('/');
    } else {
        newPath = currentPath.endsWith('/') ? currentPath + newId : currentPath + '/' + newId;
    }

    window.location.href = newPath;
};

onMounted(async () => {
    selectedClientId.value = getCurrentClientId();

    setTimeout(() => {
        const activeItem = document.querySelector('.dp-nav-item--active');
        if (activeItem) {
            activeItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        const activeSubLink = document.querySelector('.dp-ps-link--active');
        if (activeSubLink) {
            activeSubLink.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        }
    }, 150);

    const checkKeys = ['gel-accounting', 'gel-dossiers', 'gel-documents'];
    if (checkKeys.includes(pageKey.value)) {
        try {
            const res = await window.axios.get('/api/clients');
            clients.value = res.data || [];
        } catch(e) {
            console.error('Error fetching clients for layout:', e);
        }
    }
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

const buildContextUrl = (path) => {
    if (!path) return '#';
    if (path.startsWith('http')) return path;

    let finalPath = path;
    if (typeof window !== 'undefined') {
        const parts = window.location.pathname.split('/');
        let entityId = null;
        for (let i = parts.length - 1; i >= 0; i--) {
            if (/^\d+$/.test(parts[i])) {
                entityId = parts[i];
                break;
            }
        }
        // If we found an ID and the target path doesn't already have one
        if (entityId && !/\d+$/.test(path)) {
            finalPath = path.endsWith('/') ? path + entityId : path + '/' + entityId;
        }
    }

    const base = getBaseUrl();
    return base + (finalPath.startsWith('/') ? finalPath : '/' + finalPath);
};

// ── Définition des sections de navigation ───────────
const navSections = {
    administration:  { label: 'Administration',        icon: 'bi-shield-lock' },
    consultation:    { label: 'Consultation',          icon: 'bi-chat-dots' },
    fiscal:          { label: 'Fiscal',                icon: 'bi-file-earmark-text' },
    it:              { label: 'IT',                    icon: 'bi-laptop' },
    social_paie:     { label: 'Social & Paie',         icon: 'bi-people' },
    juridique:       { label: 'Juridique',             icon: 'bi-briefcase' },
    logiciel_compta: { label: 'Logiciel Comptabilité', icon: 'bi-calculator' },
    ia_automation:   { label: 'IA & Automatisation',   icon: 'bi-robot' },
};

// ── Navigation principale ────────────────────────
const navItems = [
    // Accueil (toujours en haut, sans groupe)
    { name: 'Accueil', icon: 'bi-house', route: '/dashboard', key: 'gel-dashboard' },

    // ── Administration ──
    { name: 'Personnel',      icon: 'bi-people-fill',        route: '/personnel',                 key: 'gel-personnel',      group: 'administration' },
    { name: 'Administration', icon: 'bi-gear',                route: '/licenses',                  key: 'gel-licenses',       group: 'administration', module: 'superadmin_only' },
    { name: 'Admins',         icon: 'bi-person-badge',        route: '/company-admins',            key: 'gel-company-admins', group: 'administration', module: 'superadmin_only' },
    { name: 'Sécurité',       icon: 'bi-shield-lock',         route: '/securite',                  key: 'gel-security',       group: 'administration', module: 'superadmin_only' },
    { name: 'Audit',          icon: 'bi-journal-text',        route: '/administration/audit',      key: 'gel-audit',          group: 'administration', module: 'superadmin_only' },
    { name: 'Articles',       icon: 'bi-pencil-square',       route: '/administration/articles',   key: 'gel-articles',       group: 'administration', module: 'facturation' },

    // ── Consultation ──
    { name: 'Clients',        icon: 'bi-building',            route: '/clients',                   key: 'gel-clients',        group: 'consultation', module: 'crm' },
    { name: 'Missions',       icon: 'bi-check2-square',       route: '/missions',                  key: 'gel-missions',       group: 'consultation', module: 'projets' },
    { name: 'Secrétariat',    icon: 'bi-file-text',           route: '/dae',                       key: 'dae-dashboard',      group: 'consultation', module: 'dae' },
    { name: 'Signatures',     icon: 'bi-pen',                 route: '/signatures',                key: 'gel-signatures',     group: 'consultation', module: 'document' },
    { name: 'OCR',            icon: 'bi-scanner',             route: '/ocr',                       key: 'gel-ocr',            group: 'consultation', module: 'document' },

    // ── Fiscal ──
    { name: 'Comptabilité',   icon: 'bi-calculator',          route: '/accounting',                key: 'gel-accounting',     group: 'fiscal', module: 'comptabilite' },
    { name: 'Télédécl.',      icon: 'bi-file-earmark-text',   route: '/tele-declarations',         key: 'gel-tele-declarations', group: 'fiscal', module: 'comptabilite' },
    { name: 'Centres coûts',  icon: 'bi-diagram-2',           route: '/cost-centers',              key: 'gel-cost-centers',   group: 'fiscal', module: 'comptabilite' },

    // ── IT ──
    { name: 'IT Support',     icon: 'bi-laptop',              route: '/it/tickets',                key: 'gel-it-tickets',     group: 'it', module: 'it_helpdesk' },
    { name: 'Commerce',       icon: 'bi-shop',                route: '/commerce',                  key: 'commerce-dashboard', group: 'it', module: 'commerce' },
    { name: 'e-MECeF',        icon: 'bi-receipt-cutoff',      route: '/erp/invoices',              key: 'gel-emecef',         group: 'it', module: 'facturation' },

    // ── Social & Paie ──
    { name: 'RH',             icon: 'bi-people',              route: '/rh',                        key: 'rh-dashboard',       group: 'social_paie', module: 'rh' },
    { name: 'Paie',           icon: 'bi-calculator',          route: '/paie/calculateur',          key: 'gel-paie',           group: 'social_paie', module: 'rh' },
    { name: 'Relances',       icon: 'bi-bell',                route: '/relance-rules',             key: 'gel-relances',       group: 'social_paie', module: 'facturation' },

    // ── Juridique ──
    { name: 'Juridique',      icon: 'bi-briefcase',           route: '/juridique',                 key: 'legal-dashboard',    group: 'juridique', module: 'juridique' },

    // ── Logiciel Comptabilité ──
    { name: 'Commandes',      icon: 'bi-cart-check',          route: '/admin/catalogue/orders',    key: 'erp-orders',         group: 'logiciel_compta', module: 'commerce' },
    { name: 'Catalogue',      icon: 'bi-tags',                route: '/admin/catalogue/services',  key: 'erp-catalogue',      group: 'logiciel_compta', module: 'commerce' },
    { name: 'Finance ERP',    icon: 'bi-receipt',             route: '/erp/invoices',              key: 'erp-invoice',        group: 'logiciel_compta', module: 'facturation' },
    { name: 'Caisse',         icon: 'bi-cash-stack',          route: '/erp/treasury',              key: 'erp-treasury',       group: 'logiciel_compta', module: 'caisse' },
    { name: 'Tontines',       icon: 'bi-piggy-bank',          route: '/tontines',                  key: 'gel-tontines',       group: 'logiciel_compta', module: 'caisse' },
    { name: 'Validations',    icon: 'bi-check-all',           route: '/approval-workflows',        key: 'gel-approval-workflows', group: 'logiciel_compta', module: 'document' },

    // ── IA & Automatisation ──
    { name: 'Agents IA',          icon: 'bi-robot',            route: '/ai/agents',                 key: 'ai-agents',          group: 'ia_automation' },
    { name: 'Fil IA',             icon: 'bi-robot',            route: '/ai/feed',                   key: 'gel-ai-feed',        group: 'ia_automation' },
    { name: 'Rapprochement',      icon: 'bi-arrow-left-right', route: '/ai/reconciliation',         key: 'gel-ai-reconciliation', group: 'ia_automation' },
    { name: 'Relances IA',        icon: 'bi-send-check',       route: '/ai/relances',               key: 'gel-ai-relances',    group: 'ia_automation' },
    { name: 'OCR Factures',       icon: 'bi-upc-scan',         route: '/ai/ocr',                    key: 'gel-ai-ocr',         group: 'ia_automation' },
    { name: 'Prévisions',         icon: 'bi-graph-up-arrow',   route: '/ai/cashflow',               key: 'gel-ai-cashflow',    group: 'ia_automation' },
];

const groupedNavItems = computed(() => {
    const sections = {};

    const filtered = navItems.filter(t => {
        if (authStore.user?.role_secretaire) return t.key === 'dae-dashboard';
        return !t.module || authStore.hasModule(t.module);
    });

    filtered.forEach(item => {
        const g = item.group || '_ungrouped';
        if (!sections[g]) sections[g] = [];
        sections[g].push(item);
    });

    return sections;
});

// ── Sidebar contextuelle ─────────────────────────
const sidebarBySection = {
    'gel-dashboard': [
        { group: '', items: [
            { label: 'Nouveau client',    href: '/clients/create',            icon: 'bi-person-plus' },
            { label: 'Nouvelle mission',  href: '/missions/create',           icon: 'bi-check2-square' },
            { label: 'Voir commandes',    href: '/admin/catalogue/orders',   icon: 'bi-cart' },
            { label: 'Clients récents',   href: '/clients',                   icon: 'bi-building' },
            { label: 'Missions en cours', href: '/missions',                  icon: 'bi-check2-square' },
        ]},
    ],
    'gel-clients': [
        { group: '', items: [
            { label: 'Tous les clients',  href: '/clients',         icon: 'bi-list-ul' },
            { label: 'Nouveau client',    href: '/clients/create',  icon: 'bi-person-plus' },
        ]},
    ],
    'gel-missions': [
        { group: '', items: [
            { label: 'Toutes les missions', href: '/missions',          icon: 'bi-list-ul' },
            { label: 'Nouvelle mission',    href: '/missions/create',   icon: 'bi-plus-circle' },
        ]},
    ],
    'erp-orders': [
        { group: '', items: [
            { label: 'En cours',          href: '/admin/catalogue/orders',           icon: 'bi-cart-check' },
            { label: 'Archives',          href: '/admin/catalogue/orders/archives',  icon: 'bi-archive' },
        ]},
    ],
    'erp-invoice': [
        { group: '', items: [
            { label: 'Factures',          href: '/erp/invoices',    icon: 'bi-receipt' },
            { label: 'Trésorerie',        href: '/erp/treasury',    icon: 'bi-cash-stack' },
        ]},
    ],
    'gel-licenses': [
        { group: '', items: [
            { label: 'Licences',          href: '/licenses',        icon: 'bi-key' },
            { label: 'Admins entreprise', href: '/company-admins',  icon: 'bi-person-badge' },
            { label: 'Demandes',          href: '/admin/requests',  icon: 'bi-envelope' },
            { label: 'Paramètres',        href: '/settings',        icon: 'bi-gear' },
        ]},
    ],
    'gel-poles': [
        { group: '', items: [
            { label: 'Tous les pôles',    href: '/poles',           icon: 'bi-diagram-3' },
            { label: 'Services cabinet',  href: '/services',        icon: 'bi-grid-3x3-gap' },
        ]},
    ],
    'gel-services': [
        { group: '', items: [
            { label: 'Services cabinet',  href: '/services',        icon: 'bi-grid-3x3-gap' },
        ]},
    ],
    'gel-dossiers': [
        { group: '', items: [
            { label: 'Tous les dossiers', href: '/dossiers',        icon: 'bi-folder2-open' },
            { label: 'Documents',         href: '/documents',       icon: 'bi-file-text' },
        ]},
    ],
    'gel-documents': [
        { group: '', items: [
            { label: 'Tous les documents',href: '/documents',       icon: 'bi-file-text' },
            { label: 'Dossiers',          href: '/dossiers',        icon: 'bi-folder2-open' },
        ]},
    ],
    'gel-company-admins': [
        { group: '', items: [
            { label: 'Admins entreprise', href: '/company-admins',  icon: 'bi-person-badge' },
        ]},
    ],
    'gel-requests': [
        { group: '', items: [
            { label: 'Demandes entrantes',href: '/admin/requests',  icon: 'bi-envelope' },
            { label: 'Commandes en cours',href: '/admin/catalogue/orders', icon: 'bi-cart-check' },
        ]},
    ],
    'erp-archives': [
        { group: '', items: [
            { label: 'En cours',          href: '/admin/catalogue/orders',           icon: 'bi-cart-check' },
            { label: 'Archives',          href: '/admin/catalogue/orders/archives',  icon: 'bi-archive' },
        ]},
    ],
    'erp-catalogue': [
        { group: '', items: [
            { label: 'Services',          href: '/admin/catalogue/services',         icon: 'bi-tags' },
            { label: 'Commandes en cours',href: '/admin/catalogue/orders',           icon: 'bi-cart-check' },
        ]},
    ],
    'erp-stock': [
        { group: '', items: [
            { label: 'Stocks',            href: '/erp/stocks',       icon: 'bi-boxes' },
        ]},
    ],
    'erp-treasury': [
        { group: '', items: [
            { label: 'Trésorerie',        href: '/erp/treasury',     icon: 'bi-cash-stack' },
        ]},
    ],
    'gel-it-tickets': [
        { group: '', items: [
            { label: 'Tickets',           href: '/it/tickets',                  icon: 'bi-ticket' },
            { label: 'Nouveau ticket',    href: '/it/tickets/create',           icon: 'bi-plus-circle' },
            { label: 'Assets IT',         href: '/it/assets',                   icon: 'bi-laptop' },
            { label: 'Contrats maintien', href: '/it/maintenance-contracts',    icon: 'bi-file-text' },
            { label: 'Politiques SLA',    href: '/it/sla-policies',             icon: 'bi-shield-check' },
            { label: 'Base de connais.',  href: '/it/knowledge-base',           icon: 'bi-book' },
        ]},
    ],
    'gel-tontines': [
        { group: '', items: [
            { label: 'Tontines',          href: '/tontines',          icon: 'bi-list-ul' },
            { label: 'Nouvelle tontine',  href: '/tontines/create',   icon: 'bi-plus-circle' },
        ]},
    ],
    'gel-tele-declarations': [
        { group: '', items: [
            { label: 'Déclarations',      href: '/tele-declarations',          icon: 'bi-list-ul' },
            { label: 'Nouvelle déclar.',  href: '/tele-declarations/create',   icon: 'bi-plus-circle' },
        ]},
    ],
    'gel-signatures': [
        { group: '', items: [
            { label: 'Signatures',        href: '/signatures',          icon: 'bi-list-ul' },
            { label: 'Nouvelle signature',href: '/signatures/create',   icon: 'bi-plus-circle' },
        ]},
    ],
    'gel-approval-workflows': [
        { group: '', items: [
            { label: 'Workflows',         href: '/approval-workflows',           icon: 'bi-diagram-3' },
            { label: 'Nouveau workflow',  href: '/approval-workflows/create',    icon: 'bi-plus-circle' },
        ]},
    ],
    'gel-relances': [
        { group: '', items: [
            { label: 'Règles relances',   href: '/relance-rules',        icon: 'bi-list-ul' },
            { label: 'Nouvelle règle',    href: '/relance-rules/create', icon: 'bi-plus-circle' },
        ]},
    ],
    'gel-cost-centers': [
        { group: '', items: [
            { label: 'Centres de coûts',  href: '/cost-centers',         icon: 'bi-list-ul' },
            { label: 'Nouveau centre',    href: '/cost-centers/create',  icon: 'bi-plus-circle' },
        ]},
    ],
    'gel-emecef': [
        { group: '', items: [
            { label: 'Factures ERP',      href: '/erp/invoices',           icon: 'bi-receipt' },
            { label: 'Vérifier facture',  href: '/emecef/verify/1',        icon: 'bi-search' },
        ]},
    ],
    'gel-ocr': [
        { group: '', items: [
            { label: 'Analyses',          href: '/ocr',                  icon: 'bi-list-ul' },
            { label: 'Nouvelle analyse',  href: '/ocr/create',           icon: 'bi-plus-circle' },
        ]},
    ],
    'rh-dashboard': [
        { group: '', items: [
            { label: 'Tableau de bord',   href: '/rh',               icon: 'bi-speedometer2' },
            { label: 'Employés',          href: '/rh/employees',     icon: 'bi-people' },
            { label: 'Contrats',          href: '/rh/contracts',     icon: 'bi-file-text' },
            { label: 'Congés',            href: '/rh/leaves',        icon: 'bi-calendar-check' },
            { label: 'Notes de frais',    href: '/rh/expenses',      icon: 'bi-cash-stack' },
            { label: 'Paie',              href: '/rh/payrolls',      icon: 'bi-calculator' },
            { label: 'Pointage',          href: '/rh/attendance',    icon: 'bi-clock' },
            { label: 'Formations',        href: '/rh/trainings',     icon: 'bi-book' },
            { label: 'Alertes',           href: '/rh',               icon: 'bi-bell' },
        ]},
    ],
    'gel-accounting': [
        { group: '', items: [
            { label: 'Tableau de bord',   href: '/accounting',                   icon: 'bi-calculator' },
            { label: 'Budgets',           href: '/accounting/budgets',           icon: 'bi-pie-chart' },
            { label: 'Déclarations',      href: '/accounting/tax-declarations',  icon: 'bi-file-earmark-text' },
            { label: 'Clôture',           href: '/accounting/closing',           icon: 'bi-lock' },
        ]},
    ],
    'gel-settings': [
        { group: '', items: [
            { label: 'Mon profil',        href: '/settings',         icon: 'bi-person-circle' },
            { label: 'Paramètres',        href: '/settings',         icon: 'bi-gear' },
        ]},
    ],
    'dae-dashboard': [
        { group: '', items: [
            { label: 'Tableau de bord',   href: '/dae',               icon: 'bi-speedometer2' },
            { label: 'Courriers',         href: '/dae/courriers',     icon: 'bi-envelope' },
            { label: 'Emails',            href: '/dae/emails',        icon: 'bi-chat-dots' },
            { label: 'Agenda',            href: '/dae/agenda',        icon: 'bi-calendar-event' },
            { label: 'Modèles',           href: '/dae/modeles',       icon: 'bi-file-earmark-text' },
            { label: 'Contrats',          href: '/dae/contrats',      icon: 'bi-file-text' },
            { label: 'Documents',         href: '/dae/documents',     icon: 'bi-folder' },
            { label: 'Personnel RH',      href: '/dae/personnel',     icon: 'bi-people' },
            { label: 'Conformité',        href: '/dae/conformite',    icon: 'bi-shield-check' },
            { label: 'Rapports',          href: '/dae/rapports',      icon: 'bi-graph-up' },
            { label: 'Tâches',            href: '/dae/taches',        icon: 'bi-list-task' },
        ]},
    ],
    'legal-dashboard': [
        { group: '', items: [
            { label: 'Société',           href: '/juridique/societe',         icon: 'bi-building' },
            { label: 'Assemblées',        href: '/juridique/assemblees',     icon: 'bi-people' },
            { label: 'Registres',         href: '/juridique/registres/registre_assemblee/2025', icon: 'bi-journal' },
            { label: 'Contrats',          href: '/juridique/contrats',       icon: 'bi-file-text' },
            { label: 'Contentieux',       href: '/juridique/contentieux',    icon: 'bi-shield-exclamation' },
            { label: 'Conformité',        href: '/juridique/conformite',     icon: 'bi-check-circle' },
            { label: 'Bibliothèque',      href: '/juridique/bibliotheque',   icon: 'bi-book' },
            { label: 'Veille juridique',  href: '/juridique',                icon: 'bi-newspaper' },
            { label: 'Dossiers',          href: '/juridique/dossiers',       icon: 'bi-folder2-open' },
        ]},
    ],
    'gel-paie': [
        { group: '', items: [
            { label: 'Calculateur',       href: '/paie/calculateur',        icon: 'bi-calculator' },
        ]},
    ],
    'gel-security': [
        { group: '', items: [
            { label: 'Mon 2FA',           href: '/securite',                icon: 'bi-shield-check' },
            { label: 'Sessions',          href: '/securite',                icon: 'bi-laptop' },
        ]},
    ],
    'gel-audit': [
        { group: '', items: [
            { label: "Journal d'audit",  href: '/administration/audit',    icon: 'bi-journal-text' },
        ]},
    ],
    'gel-articles': [
        { group: '', items: [
            { label: 'Tous les articles', href: '/administration/articles',        icon: 'bi-list-ul' },
            { label: 'Nouvel article',    href: '/administration/articles/create', icon: 'bi-plus-circle' },
        ]},
    ],
    'commerce-dashboard': [
        { group: '', items: [
            { label: 'Tableau de bord',   href: '/commerce',               icon: 'bi-speedometer2' },
            { label: 'Produits',          href: '/commerce/products',      icon: 'bi-box-seam' },
            { label: 'Catégories',        href: '/commerce/categories',    icon: 'bi-tags' },
            { label: 'Fournisseurs',      href: '/commerce/suppliers',     icon: 'bi-truck' },
        ]},
        { group: 'POINT DE VENTE', items: [
            { label: 'Caisse POS',        href: '/commerce/pos',           icon: 'bi-cash-register' },
            { label: 'Utilisateurs',      href: '/commerce/users',         icon: 'bi-people' },
        ]},
        { group: 'STOCKS', items: [
            { label: 'Inventaire',        href: '/commerce/inventory',     icon: 'bi-clipboard-data' },
        ]},
    ],
    'gel-ai-feed': [
        { group: '', items: [
            { label: 'Fil d\'activité',    href: '/ai/feed',             icon: 'bi-robot' },
            { label: 'Rapprochement',      href: '/ai/reconciliation',   icon: 'bi-arrow-left-right' },
            { label: 'Relances IA',        href: '/ai/relances',         icon: 'bi-send-check' },
            { label: 'OCR Factures',       href: '/ai/ocr',              icon: 'bi-upc-scan' },
            { label: 'Prévisions',         href: '/ai/cashflow',         icon: 'bi-graph-up-arrow' },
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
    'erp-treasury': 'Trésorerie',
    'gel-licenses': 'Administration',
    'gel-company-admins': 'Admins',
    'gel-requests': 'Demandes',
    'gel-accounting': 'Comptabilité',
    'gel-settings': 'Paramètres',
    'dae-dashboard': 'Secrétariat DAE',
    'legal-dashboard': 'Juridique',
    'rh-dashboard': 'Ressources Humaines',
    'gel-it-tickets': 'IT Support',
    'gel-tontines': 'Tontines',
    'gel-tele-declarations': 'Télédéclarations',
    'gel-signatures': 'Signatures',
    'gel-approval-workflows': 'Validations',
    'gel-relances': 'Relances',
    'gel-cost-centers': 'Centres de Coûts',
    'gel-emecef': 'e-MECeF',
    'gel-ocr': 'OCR',
    'gel-paie': 'Calculateur Paie',
    'gel-security': 'Sécurité',
    'gel-audit': "Journal d'Audit",
    'gel-articles': 'Articles / Blog',
    'gel-personnel': 'Personnel GEL',
    'commerce-dashboard': 'Commerce / POS',
    'gel-ai-feed': 'GEL Intelligence',
    'gel-ai-reconciliation': 'Rapprochement IA',
    'gel-ai-relances': 'Relances Intelligentes',
    'gel-ai-ocr': 'OCR Factures',
    'gel-ai-cashflow': 'Prévisions Trésorerie',
};

const pageKey = computed(() => {
    const path = window.location.pathname;
    if (path.startsWith('/clients'))        return 'gel-clients';
    if (path.startsWith('/personnel'))      return 'gel-personnel';
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
    if (path.startsWith('/admin/catalogue/services'))        return 'erp-catalogue';
    if (path.startsWith('/erp/stocks'))     return 'erp-stock';
    if (path.startsWith('/erp/invoices'))   return 'erp-invoice';
    if (path.startsWith('/erp/treasury'))   return 'erp-treasury';
    if (path.startsWith('/settings'))       return 'gel-settings';
    if (path.startsWith('/dae'))            return 'dae-dashboard';
    if (path.startsWith('/juridique'))      return 'legal-dashboard';
    if (path.startsWith('/rh'))             return 'rh-dashboard';
    if (path.startsWith('/it/tickets'))     return 'gel-it-tickets';
    if (path.startsWith('/it'))             return 'gel-it-tickets';
    if (path.startsWith('/tontines'))       return 'gel-tontines';
    if (path.startsWith('/tele-declarations')) return 'gel-tele-declarations';
    if (path.startsWith('/signatures'))     return 'gel-signatures';
    if (path.startsWith('/approval-workflows')) return 'gel-approval-workflows';
    if (path.startsWith('/relance-rules'))  return 'gel-relances';
    if (path.startsWith('/cost-centers'))   return 'gel-cost-centers';
    if (path.startsWith('/emecef'))         return 'gel-emecef';
    if (path.startsWith('/ocr'))            return 'gel-ocr';
    if (path.startsWith('/paie'))           return 'gel-paie';
    if (path.startsWith('/securite'))       return 'gel-security';
    if (path.startsWith('/administration/audit')) return 'gel-audit';
    if (path.startsWith('/administration/articles')) return 'gel-articles';
    if (path.startsWith('/commerce'))       return 'commerce-dashboard';
    if (path.startsWith('/ai/feed'))        return 'gel-ai-feed';
    if (path.startsWith('/ai/reconciliation')) return 'gel-ai-reconciliation';
    if (path.startsWith('/ai/relances'))    return 'gel-ai-relances';
    if (path.startsWith('/ai/ocr'))         return 'gel-ai-ocr';
    if (path.startsWith('/ai/cashflow'))    return 'gel-ai-cashflow';
    if (path.startsWith('/ai'))             return 'gel-ai-feed';
    return 'gel-dashboard';
});

const userRoleLabel = computed(() => {
    const labels = {
        super_admin: 'Super Admin',
        director: 'Directeur',
        pole_responsible: 'Responsable Pôle',
        collaborator: 'Collaborateur',
        secretaire: 'Secrétaire',
        company_admin: 'Admin Entreprise',
        client: 'Client',
        comptable: 'Comptable',
        caissier: 'Caissier',
    };
    return labels[authStore.user?.role] || (authStore.user?.role_secretaire ? 'Secrétaire' : 'Collaborateur');
});

const userInitial = computed(() => authStore.user?.name?.charAt(0).toUpperCase() || 'U');

const logout = async () => {
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const form = document.getElementById('logout-form');
        const input = document.getElementById('logout-csrf-token');
        if (input && csrfToken) input.value = csrfToken;
        if (form) {
            form.action = buildUrl('/logout');
            form.submit();
        } else {
            await window.axios.post('/logout');
            window.location.href = buildUrl('/login');
        }
    } catch (e) {
        console.error('Logout failed:', e);
        window.location.href = buildUrl('/login');
    }
};
</script>

<template>
    <div class="dp-shell">
        <!-- ═══ TOPBAR — Glass effect ═══ -->
        <header class="dp-topbar">
            <div class="dp-topbar-left">
                <button class="dp-hamburger" @click="sidebarOpen = !sidebarOpen" :title="sidebarOpen ? 'Masquer le menu' : 'Afficher le menu'">
                    <i :class="sidebarOpen ? 'bi-x' : 'bi-list'"></i>
                </button>
                <div class="dp-topbar-divider"></div>
                <h1 class="dp-page-title">{{ props.pageTitle }}</h1>
            </div>
            <div class="dp-topbar-right">
                <Omnisearch @navigate="route => window.location.href = buildUrl(route)" />
                <span class="dp-role-badge">{{ userRoleLabel }}</span>
                <div class="dropdown">
                    <button class="dp-user-btn" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="dp-avatar">{{ userInitial }}</div>
                        <i class="bi-chevron-down dp-chevron"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dp-dd">
                        <li>
                            <div class="dp-dd-user px-3 py-2">
                                <div class="dp-dd-name">{{ authStore.user?.name }}</div>
                                <div class="dp-dd-email">{{ authStore.user?.email }}</div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dp-dd-item dropdown-item" :href="buildUrl('/settings')"><i class="bi-gear me-2"></i>Paramètres</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dp-dd-item dropdown-item dp-dd-danger" href="#" @click.prevent="logout">
                                <i class="bi-box-arrow-right me-2"></i>Déconnexion
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- ═══ BODY: Sidebar + Main ═══ -->
        <div class="dp-body">
            <!-- Sidebar -->
            <aside class="dp-sidebar" :class="{ 'dp-sidebar--open': sidebarOpen }">
                <!-- Logo section -->
                <div class="dp-logo">
                    <div class="dp-logo-icon">
                        <i class="bi-gem"></i>
                    </div>
                    <div class="dp-logo-text">
                        <span class="dp-logo-title">GEL Cabinet</span>
                        <span class="dp-logo-sub">Gestion de Cabinet</span>
                    </div>
                </div>

                <!-- Navigation principale — regroupée par sections -->
                <nav class="dp-nav">
                    <div class="dp-nav-label">Navigation</div>

                    <template v-for="(items, groupKey) in groupedNavItems" :key="groupKey">
                        <!-- Section header (sauf Accueil qui est seul) — cliquable pour replier/déplier -->
                        <div v-if="groupKey !== '_ungrouped'"
                             class="dp-section-header"
                             :class="{ 'dp-section-header--collapsed': isSectionCollapsed(groupKey) }"
                             @click="toggleSection(groupKey)"
                             role="button"
                             tabindex="0"
                             @keydown.enter.prevent="toggleSection(groupKey)">
                            <div class="dp-section-header-left">
                                <i :class="navSections[groupKey]?.icon" class="dp-section-icon"></i>
                                <span>{{ navSections[groupKey]?.label || groupKey }}</span>
                            </div>
                            <div class="dp-section-actions">
                                <!-- Bouton +Nouveau (visible au hover de la section) -->
                                <a v-if="!isSectionCollapsed(groupKey)"
                                   :href="getNewUrl(groupKey)"
                                   class="dp-section-add"
                                   title="Ajouter"
                                   @click.stop>
                                    <i class="bi-plus-lg"></i>
                                </a>
                                <i class="bi-chevron-down dp-section-chevron"
                                   :class="{ 'dp-section-chevron--rotated': isSectionCollapsed(groupKey) }"></i>
                            </div>
                        </div>

                        <template v-if="!isSectionCollapsed(groupKey)">
                            <a v-for="item in items" :key="item.key"
                               :href="buildUrl(item.route)"
                               class="dp-nav-item"
                               :class="{ 'dp-nav-item--active': pageKey === item.key }">
                                <i :class="item.icon" class="dp-nav-icon"></i>
                                <span class="dp-nav-name">{{ item.name }}</span>
                            </a>
                        </template>
                    </template>
                </nav>

                <!-- IA Mini Dashboard — Always visible widget -->
                <div class="dp-ai-widget">
                    <a :href="buildUrl('/ai/feed')" class="dp-ai-widget-header">
                        <div class="dp-ai-widget-left">
                            <div class="dp-ai-icon-wrap">
                                <i class="bi-robot"></i>
                                <span class="dp-ai-pulse"></span>
                            </div>
                            <div class="dp-ai-widget-text">
                                <span class="dp-ai-widget-title">GEL Intelligence</span>
                                <span class="dp-ai-widget-sub">Agents IA actifs</span>
                            </div>
                        </div>
                        <i class="bi-chevron-right dp-ai-chevron"></i>
                    </a>
                    <div class="dp-ai-agents-grid">
                        <a :href="buildUrl('/ai/reconciliation')" class="dp-ai-agent-chip" title="Rapprochement Bancaire">
                            <i class="bi-arrow-left-right" style="color: #06B6D4"></i>
                            <span>Réconc.</span>
                        </a>
                        <a :href="buildUrl('/ai/relances')" class="dp-ai-agent-chip" title="Relances Intelligentes">
                            <i class="bi-send-check" style="color: #F59E0B"></i>
                            <span>Relances</span>
                        </a>
                        <a :href="buildUrl('/ai/ocr')" class="dp-ai-agent-chip" title="OCR Factures">
                            <i class="bi-upc-scan" style="color: #10B981"></i>
                            <span>OCR</span>
                        </a>
                        <a :href="buildUrl('/ai/cashflow')" class="dp-ai-agent-chip" title="Prévisions Trésorerie">
                            <i class="bi-graph-up-arrow" style="color: #EF4444"></i>
                            <span>Cashflow</span>
                        </a>
                    </div>
                </div>

                <!-- User bottom section -->
                <div class="dp-sidebar-footer">
                    <div class="dp-sf-user">
                        <div class="dp-sf-avatar">{{ userInitial }}</div>
                        <div class="dp-sf-info">
                            <div class="dp-sf-name">{{ authStore.user?.name || 'Utilisateur' }}</div>
                            <div class="dp-sf-role">{{ userRoleLabel }}</div>
                        </div>
                    </div>
                    <a :href="buildUrl('/settings')" class="dp-sf-settings" title="Paramètres">
                        <i class="bi-gear"></i>
                    </a>
                </div>
            </aside>

            <!-- Overlay mobile -->
            <div v-if="sidebarOpen && isMobile" class="dp-overlay" @click="sidebarOpen = false"></div>

            <!-- Contenu principal -->
            <main class="dp-main">
                <!-- Liens contextuels (Sub-header) -->
                <div v-if="sidebarLinks.length && sidebarLinks[0]?.items" class="dp-page-subheader">
                    <div class="dp-ps-inner">
                        <template v-for="group in sidebarLinks" :key="group.group">
                            <span v-if="group.group" class="dp-ps-group">{{ group.group }}</span>
                            <a v-for="link in group.items" :key="link.label"
                               :href="buildContextUrl(link.href)"
                               class="dp-ps-link"
                               :class="{ 'dp-ps-link--active': isSubLinkActive(link.href) }">
                                <i :class="link.icon"></i>
                                <span>{{ link.label }}</span>
                            </a>
                        </template>

                        <!-- Client Dropdown Selector -->
                        <div v-if="['gel-accounting', 'gel-dossiers', 'gel-documents'].includes(pageKey)" class="dp-client-selector-wrap mt-2 mt-md-0">
                            <label class="dp-client-select-label"><i class="bi-building me-1"></i>Client :</label>
                            <select :value="selectedClientId" @change="handleClientChange" class="form-select form-select-sm dp-client-select">
                                <option :value="null">-- Choisir un client --</option>
                                <option v-for="c in clients" :key="c.id" :value="c.id">
                                    {{ c.company_name || c.raison_sociale || c.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="dp-content">
                    <slot />
                </div>
            </main>
        </div>

        <!-- Logout form -->
        <form id="logout-form" method="POST" action="/logout" style="display:none;">
            <input type="hidden" name="_token" id="logout-csrf-token">
        </form>
    </div>
</template>

<style scoped>
/* ═══════════════════════════════════════════════════════════
   Dark Premium Layout — Design rafraîchi
   ═══════════════════════════════════════════════════════════ */

/* ── Reset / global ──────────────────────────── */
.dp-shell {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background: #f5f7fa;
}
.dp-body {
    display: flex;
    flex-grow: 1;
    min-height: 0;
    position: relative;
}

/* ═══════════════════════════════════════════════
   TOPBAR — Glass / Translucent
   ═══════════════════════════════════════════════ */
.dp-topbar {
    height: 56px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    background: rgba(255,255,255,0.85);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(0,0,0,0.06);
    position: sticky;
    top: 0;
    z-index: 1025;
}
.dp-topbar-left {
    display: flex;
    align-items: center;
    gap: 12px;
}
.dp-topbar-right {
    display: flex;
    align-items: center;
    gap: 10px;
}
.dp-hamburger {
    width: 32px;
    height: 32px;
    border: none;
    background: rgba(0,0,0,0.04);
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4a5568;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.15s;
}
.dp-hamburger:hover {
    background: rgba(0,0,0,0.08);
    color: #FF7900;
}
.dp-topbar-divider {
    width: 1px;
    height: 24px;
    background: rgba(0,0,0,0.08);
}
.dp-page-title {
    font-family: 'Outfit', sans-serif;
    font-size: 16px;
    font-weight: 700;
    color: #1a202c;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ── Topbar right ──────────────────────────── */
.dp-role-badge {
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    background: rgba(255,121,0,0.1);
    color: #FF7900;
    padding: 3px 9px;
    border-radius: 4px;
    border: 1px solid rgba(255,121,0,0.15);
}
.dp-user-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    background: rgba(0,0,0,0.03);
    border: 1px solid rgba(0,0,0,0.06);
    border-radius: 8px;
    padding: 4px 10px 4px 4px;
    cursor: pointer;
    transition: all 0.15s;
}
.dp-user-btn:hover {
    background: rgba(0,0,0,0.06);
    border-color: rgba(255,121,0,0.2);
}
.dp-avatar {
    width: 28px;
    height: 28px;
    background: linear-gradient(135deg, #FF7900, #FF9A3E);
    color: #fff;
    font-weight: 700;
    font-size: 12px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.dp-chevron {
    font-size: 8px;
    color: #a0aec0;
}

/* ── Dropdown ───────────────────────────────── */
.dp-dd {
    border-radius: 10px !important;
    border: 1px solid rgba(0,0,0,0.06) !important;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12) !important;
    min-width: 220px;
    margin-top: 8px;
    padding: 6px !important;
}
.dp-dd-user { background: #f8f9fc; border-radius: 8px; }
.dp-dd-name { font-size: 13px; font-weight: 700; color: #1a202c; }
.dp-dd-email { font-size: 11px; color: #8896a6; }
.dp-dd-item { font-size: 13px; padding: 8px 12px; border-radius: 6px; color: #4a5568; }
.dp-dd-item:hover { background: rgba(255,121,0,0.08) !important; color: #FF7900 !important; }
.dp-dd-danger { color: #e74c3c !important; }
.dp-dd-danger:hover { background: #fdecea !important; color: #c0392b !important; }

/* ═══════════════════════════════════════════════
   SIDEBAR — Dark Premium
   ═══════════════════════════════════════════════ */
.dp-sidebar {
    width: 260px;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    background: linear-gradient(180deg, #0B1120 0%, #0F1A2E 100%);
    border-right: 1px solid rgba(255,255,255,0.04);
    overflow-y: auto;
    overflow-x: hidden;
    transition: margin-left 0.25s ease, opacity 0.2s ease;
    position: sticky;
    top: 56px;
    height: calc(100vh - 56px);
    z-index: 1020;
}

/* Sidebar closed state */
@media (min-width: 992px) {
    .dp-sidebar:not(.dp-sidebar--open) {
        margin-left: -260px;
        opacity: 0;
        pointer-events: none;
    }
}

/* ── Logo ────────────────────────────────────── */
.dp-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px 18px 14px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    flex-shrink: 0;
}
.dp-logo-icon {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #FF7900, #FF9A3E);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 16px;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(255,121,0,0.25);
}
.dp-logo-text {
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.dp-logo-title {
    font-family: 'Outfit', sans-serif;
    font-size: 15px;
    font-weight: 800;
    color: #fff;
    line-height: 1.2;
}
.dp-logo-sub {
    font-size: 9px;
    color: rgba(255,255,255,0.35);
    font-weight: 500;
    letter-spacing: 0.02em;
}

/* ── Navigation ──────────────────────────────── */
.dp-nav {
    padding: 12px 10px 8px;
    flex: 1;
}
.dp-nav-label {
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: rgba(255,255,255,0.25);
    padding: 8px 10px 6px;
}

/* ── Section headers (Services / Modules / Admin) ── */
.dp-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 10px 6px;
    margin: 8px 0 2px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: rgba(255,255,255,0.55);
    background: rgba(255,255,255,0.03);
    border-radius: 6px;
    cursor: pointer;
    user-select: none;
    transition: all 0.2s;
}
.dp-section-header:hover {
    color: rgba(255,255,255,0.8);
    background: rgba(255,255,255,0.06);
}
.dp-section-header:first-of-type {
    margin-top: 0;
}
.dp-section-header-left {
    display: flex;
    align-items: center;
    gap: 8px;
}
.dp-section-actions {
    display: flex;
    align-items: center;
    gap: 4px;
}
.dp-section-add {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border-radius: 4px;
    color: rgba(255,255,255,0.3);
    font-size: 11px;
    text-decoration: none;
    transition: all 0.2s;
    opacity: 0;
}
.dp-section-header:hover .dp-section-add {
    opacity: 1;
}
.dp-section-add:hover {
    background: rgba(255,121,0,0.2);
    color: #FF7900;
}
.dp-section-chevron {
    font-size: 10px;
    color: rgba(255,255,255,0.25);
    transition: transform 0.25s ease;
}
.dp-section-chevron--rotated {
    transform: rotate(-90deg);
}
.dp-section-header--collapsed {
    color: rgba(255,255,255,0.2);
}
.dp-section-header--collapsed .dp-section-icon {
    opacity: 0.4;
}
.dp-section-icon {
    font-size: 12px;
    opacity: 0.6;
}

.dp-nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 12px;
    margin: 1px 0;
    font-size: 13px;
    font-weight: 500;
    color: rgba(255,255,255,0.55);
    text-decoration: none;
    border-radius: 7px;
    border-left: 3px solid transparent;
    transition: all 0.15s ease;
    white-space: nowrap;
}
.dp-nav-item:hover {
    color: rgba(255,255,255,0.85);
    background: rgba(255,255,255,0.04);
}
.dp-nav-item--active {
    color: #fff !important;
    background: rgba(255,121,0,0.1);
    border-left-color: #FF7900;
}
.dp-nav-icon {
    font-size: 16px;
    width: 20px;
    text-align: center;
    flex-shrink: 0;
    opacity: 0.5;
    transition: opacity 0.15s;
}
.dp-nav-item:hover .dp-nav-icon {
    opacity: 0.8;
}
.dp-nav-item--active .dp-nav-icon {
    opacity: 1;
    color: #FF7900;
}
.dp-nav-name {
    line-height: 1.2;
}

/* ── Subheader links ──────────────────────────── */
.dp-page-subheader {
    background: linear-gradient(90deg, #0B1120 0%, #0F1A2E 100%);
    border-bottom: 1px solid rgba(255,255,255,0.06);
    padding: 10px 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    position: sticky;
    top: 56px;
    z-index: 1010;
    overflow: visible;
}
.dp-page-subheader::-webkit-scrollbar {
    height: 4px;
}
.dp-page-subheader::-webkit-scrollbar-track {
    background: transparent;
}
.dp-page-subheader::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.1);
    border-radius: 4px;
}
.dp-ps-inner {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.dp-ps-group {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: rgba(255,255,255,0.3);
    letter-spacing: 0.05em;
    margin-right: -4px;
}
.dp-ps-link {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    color: rgba(255,255,255,0.6);
    text-decoration: none;
    padding: 6px 14px;
    border-radius: 6px;
    transition: all 0.2s;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.05);
    flex-shrink: 0;
    white-space: nowrap;
}
.dp-ps-link:hover {
    color: #fff;
    background: rgba(255,121,0,0.15);
    border-color: rgba(255,121,0,0.3);
}
.dp-ps-link--active {
    color: #fff !important;
    background: rgba(255,121,0,0.2) !important;
    border-color: #FF7900 !important;
}
.dp-ps-link i {
    font-size: 14px;
    color: rgba(255,121,0,0.8);
}
.dp-ps-link:hover i, .dp-ps-link--active i {
    color: #FF7900;
}
.dp-client-selector-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-left: auto;
}
.dp-client-select-label {
    font-size: 12px;
    font-weight: 600;
    color: rgba(255,255,255,0.5);
    white-space: nowrap;
    margin: 0;
}
.dp-client-select {
    background: rgba(255,255,255,0.06) !important;
    border: 1px solid rgba(255,255,255,0.1) !important;
    color: #fff !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    border-radius: 6px !important;
    padding: 4px 10px !important;
    width: 200px;
    cursor: pointer;
    transition: all 0.2s;
}
.dp-client-select:focus {
    border-color: #FF7900 !important;
    box-shadow: 0 0 0 3px rgba(255,121,0,0.15) !important;
}
.dp-client-select option {
    background: #0F1A2E !important;
    color: #fff !important;
}

/* ── Sidebar — IA Widget (Always visible) ──── */
.dp-ai-widget {
    margin: 8px 10px;
    border-radius: 10px;
    background: linear-gradient(135deg, rgba(255,121,0,0.08) 0%, rgba(139,92,246,0.08) 100%);
    border: 1px solid rgba(255,121,0,0.12);
    overflow: hidden;
    margin-top: auto;
    flex-shrink: 0;
}
.dp-ai-widget-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 12px;
    text-decoration: none;
    transition: all 0.2s;
    border-bottom: 1px solid rgba(255,255,255,0.04);
}
.dp-ai-widget-header:hover {
    background: rgba(255,121,0,0.06);
}
.dp-ai-widget-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.dp-ai-icon-wrap {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #FF7900, #8B5CF6);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 15px;
    flex-shrink: 0;
    position: relative;
    box-shadow: 0 2px 8px rgba(255,121,0,0.3);
}
.dp-ai-pulse {
    position: absolute;
    top: -2px;
    right: -2px;
    width: 8px;
    height: 8px;
    background: #10B981;
    border-radius: 50%;
    border: 2px solid #0B1120;
    animation: ai-pulse 2s ease-in-out infinite;
}
@keyframes ai-pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.6; transform: scale(1.2); }
}
.dp-ai-widget-text {
    display: flex;
    flex-direction: column;
}
.dp-ai-widget-title {
    font-size: 12px;
    font-weight: 700;
    color: rgba(255,255,255,0.85);
    line-height: 1.2;
}
.dp-ai-widget-sub {
    font-size: 9px;
    color: rgba(255,255,255,0.4);
    font-weight: 500;
}
.dp-ai-chevron {
    font-size: 10px;
    color: rgba(255,255,255,0.25);
    transition: transform 0.2s;
}
.dp-ai-widget-header:hover .dp-ai-chevron {
    color: #FF7900;
    transform: translateX(2px);
}
.dp-ai-agents-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4px;
    padding: 6px;
}
.dp-ai-agent-chip {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 8px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 600;
    color: rgba(255,255,255,0.55);
    text-decoration: none;
    transition: all 0.15s;
    background: rgba(255,255,255,0.03);
}
.dp-ai-agent-chip:hover {
    background: rgba(255,255,255,0.08);
    color: rgba(255,255,255,0.85);
}
.dp-ai-agent-chip i {
    font-size: 12px;
    flex-shrink: 0;
}

/* ── Sidebar footer — User section ──────────── */
.dp-sidebar-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 14px;
    border-top: 1px solid rgba(255,255,255,0.05);
    flex-shrink: 0;
}
.dp-sf-user {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
}
.dp-sf-avatar {
    width: 30px;
    height: 30px;
    background: linear-gradient(135deg, #FF7900, #FF9A3E);
    color: #fff;
    font-weight: 700;
    font-size: 11px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.dp-sf-info {
    min-width: 0;
}
.dp-sf-name {
    font-size: 12px;
    font-weight: 600;
    color: rgba(255,255,255,0.75);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.dp-sf-role {
    font-size: 9px;
    color: rgba(255,255,255,0.3);
    font-weight: 500;
}
.dp-sf-settings {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255,255,255,0.3);
    text-decoration: none;
    transition: all 0.12s;
    flex-shrink: 0;
}
.dp-sf-settings:hover {
    color: #FF7900;
    background: rgba(255,121,0,0.1);
}

/* ── Scrollbar sidebar ───────────────────── */
.dp-sidebar::-webkit-scrollbar { width: 3px; }
.dp-sidebar::-webkit-scrollbar-track { background: transparent; }
.dp-sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 10px; }
.dp-sidebar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.15); }

/* ═══════════════════════════════════════════════
   OVERLAY (mobile)
   ═══════════════════════════════════════════════ */
.dp-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1015;
}

/* ═══════════════════════════════════════════════
   MAIN CONTENT
   ═══════════════════════════════════════════════ */
.dp-main {
    flex-grow: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    background: #f5f7fa;
}
.dp-content {
    flex-grow: 1;
    padding: 24px;
    min-height: 0;
}

/* ═══════════════════════════════════════════════
   RESPONSIVE
   ═══════════════════════════════════════════════ */

/* Tablet / mobile */
@media (max-width: 991.98px) {
    .dp-topbar { padding: 0 12px; }
    .dp-content { padding: 14px; }
    .dp-sidebar {
        position: fixed;
        left: 0;
        top: 0;
        bottom: 0;
        z-index: 1050;
        margin-left: 0 !important;
        opacity: 1 !important;
        pointer-events: auto !important;
        transform: translateX(-100%);
        box-shadow: 4px 0 24px rgba(0,0,0,0.3);
        transition: transform 0.25s ease;
    }
    .dp-sidebar.dp-sidebar--open {
        transform: translateX(0);
    }
    .dp-role-badge { display: none; }
}
@media (max-width: 575.98px) {
    .dp-topbar { height: 50px; }
    .dp-content { padding: 10px; }
    .dp-page-title { font-size: 14px; }
}

/* ═══════════════════════════════════════════════
   Deep — Styles globaux appliqués au contenu
   ═══════════════════════════════════════════════ */
:deep(.card) {
    border-radius: 12px !important;
    border: none !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.04) !important;
    background: #fff !important;
}
:deep(.card-header) {
    background: transparent !important;
    border-bottom: 1px solid rgba(0,0,0,0.06) !important;
    padding: 16px 20px !important;
    font-weight: 700 !important;
    font-size: 14px !important;
    color: #1a202c !important;
    border-radius: 12px 12px 0 0 !important;
}
:deep(.card-body) {
    padding: 20px !important;
}
:deep(.table) { font-size: 13px; }
:deep(.table thead th) {
    background: #f8f9fc !important;
    color: #4a5568 !important;
    font-weight: 600 !important;
    font-size: 11px !important;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    border-bottom: 1px solid rgba(0,0,0,0.06) !important;
    padding: 10px 14px !important;
}
:deep(.table td) {
    padding: 10px 14px !important;
    border-bottom: 1px solid rgba(0,0,0,0.04) !important;
    color: #2d3748;
}
:deep(.table tbody tr:hover) { background: #fafbfe !important; }

:deep(.btn) { border-radius: 8px !important; font-size: 13px; font-weight: 600 !important; }
:deep(.btn-primary) {
    background: #FF7900 !important;
    border-color: #FF7900 !important;
    color: #fff !important;
}
:deep(.btn-primary:hover) { background: #e06700 !important; border-color: #e06700 !important; }

:deep(.badge) { border-radius: 6px !important; font-size: 11px; font-weight: 600 !important; }
:deep(.form-control), :deep(.form-select) {
    border-radius: 8px !important;
    font-size: 13px;
    border: 1px solid rgba(0,0,0,0.1);
}
:deep(.form-control:focus), :deep(.form-select:focus) {
    border-color: #FF7900;
    box-shadow: 0 0 0 3px rgba(255,121,0,0.1);
}
:deep(.text-primary) { color: #FF7900 !important; }
:deep(.bg-primary) { background: #FF7900 !important; }
</style>
