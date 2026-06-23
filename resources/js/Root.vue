<template>
    <component :is="pageComponent" v-bind="pageProps" />
</template>

<script setup>
// =============================================================================
// FICHIER : Root.vue
// RÔLE    : Routeur dynamique — Point d'entrée du SPA Vue 3
// ÉQUIPE  : GEL Cabinet — Équipe Dev Frontend
// =============================================================================
// Ce composant est le point d'entrée unique de l'application Vue.
// Il reçoit depuis Laravel (via @inertia ou injection manuelle) :
//   - page       : le nom de la page à afficher (ex: 'gel-dashboard')
//   - pageProps  : les propriétés injectées par le contrôleur Laravel
//
// Le mapping page → composant est défini dans pageComponent.
// Si une page n'est pas trouvée → composant fallback 404.
//
// ⚠️  Pour ajouter une nouvelle page :
//     1. Importe le composant Vue ici
//     2. Ajoute une entrée dans le map
//     3. Ajoute l'entrée dans la vue Blade (company.blade.php, etc.)
// =============================================================================

import { inject, computed } from 'vue';

const page = inject('page', '');
const pageProps = inject('pageProps', {});

const pageComponent = computed(() => {
    const map = {
        'gel-dashboard': 'gel-dashboard',
        'gel-clients': 'gel-clients',
        'gel-clients-show': 'gel-client-show',
        'gel-poles': 'gel-poles',
        'gel-pole-show': 'gel-pole-show',
        'gel-missions': 'gel-missions',
        'gel-missions-create': 'gel-mission-form',
        'gel-missions-edit': 'gel-mission-form',
        'gel-missions-show': 'gel-mission-form',
        'gel-dossiers': 'gel-dossiers',
        'gel-documents': 'gel-dossiers',
        'gel-services': 'gel-services',
        'gel-services-show': 'gel-services-show',
        'gel-accounting': 'gel-accounting',
        'gel-accounting-accounts': 'gel-accounting-accounts',
        'gel-accounting-journals': 'gel-accounting-journals',
        'gel-accounting-journal-form': 'gel-accounting-journal-form',
        'gel-accounting-balance': 'gel-accounting-balance',
        'gel-accounting-ledger': 'gel-accounting-ledger',
        'gel-accounting-bilan': 'gel-accounting-bilan',
        'gel-accounting-resultat': 'gel-accounting-resultat',
        'gel-accounting-budgets': 'gel-accounting-budgets',
        'gel-accounting-tax-declarations': 'gel-accounting-tax-declarations',
        'gel-accounting-closing': 'gel-accounting-closing',
        'erp-catalogue': 'erp-catalogue',
        'erp-stock': 'erp-stock',
        'erp-invoice': 'erp-invoice',
        'erp-treasury': 'erp-treasury',
        'gel-licenses': 'gel-licenses',
        'gel-personnel': 'gel-personnel',
        'gel-company-admins': 'gel-company-admins',
        'gel-requests': 'gel-requests',
        'settings': 'gel-settings',
        'public-catalogue-index': 'public-catalogue-index',
        'public-catalogue-show': 'public-catalogue-show',
        'public-order-wizard': 'public-order-wizard',
        'client-orders-index': 'client-orders-index',
        'client-orders-show': 'client-orders-show',
        'admin-orders-kanban': 'admin-orders-kanban',
        'admin-orders-archives': 'admin-orders-archives',
        'admin-orders-show': 'admin-orders-show',
        'admin-services-index': 'admin-services-index',
        'cpa-login': 'cpa-login',
        'cpa-register': 'cpa-register',
        'cpa-test': 'cpa-test',
        'cpa-dashboard': 'cpa-dashboard',
        'company-dashboard': 'company-dashboard',
        'company-services': 'company-services',
        'company-profile': 'company-profile',
        'company-users': 'company-users',
        'company-ged': 'company-ged',
        'company-invoices': 'company-invoices',
        'company-human-resources': 'company-human-resources',
        'company-notifications': 'company-notifications',
        'company-legal': 'company-legal',
        'company-projects': 'company-projects',
        'company-crm': 'company-crm',
        'company-ai-assistant': 'company-ai-assistant',
        'company-accounting': 'company-accounting',
        'company-caisse': 'company-caisse',
        'company-emecef': 'company-emecef',
        'company-dae-dashboard': 'company-dae-dashboard',
        'company-dae-courriers': 'company-dae-courriers',
        'company-dae-contrats': 'company-dae-contrats',
        'company-dae-documents': 'company-dae-documents',
        'company-dae-taches': 'company-dae-taches',
        'dae-dashboard': 'dae-dashboard',
        'dae-courriers': 'dae-courriers-index',
        'dae-courriers-create': 'dae-courriers-form',
        'dae-courriers-edit': 'dae-courriers-form',
        'dae-courriers-show': 'dae-courriers-show',
        'dae-emails': 'dae-emails-index',
        'dae-emails-create': 'dae-emails-index',
        'dae-emails-show': 'dae-emails-index',
        'dae-agenda': 'dae-agenda-index',
        'dae-contrats': 'dae-contrats-index',
        'dae-contrats-create': 'dae-contrats-form',
        'dae-contrats-edit': 'dae-contrats-form',
        'dae-contrats-show': 'dae-contrats-show',
        'dae-documents': 'dae-documents-index',
        'dae-documents-show': 'dae-documents-show',
        'dae-personnel': 'dae-personnel-index',
        'dae-personnel-show': 'dae-personnel-show',
        'dae-conformite': 'dae-conformite-index',
        'dae-conformite-show': 'dae-conformite-show',
        'dae-rapports': 'dae-rapports-index',
        'dae-rapports-show': 'dae-rapports-show',
        'dae-taches': 'dae-taches-index',
        'dae-modeles': 'dae-modeles-index',
        'legal-dashboard': 'legal-dashboard',
        'legal-societe': 'legal-societe',
        'legal-assemblees': 'legal-assemblees-index',
        'legal-assemblees-create': 'legal-assemblees-form',
        'legal-assemblees-edit': 'legal-assemblees-form',
        'legal-assemblees-show': 'legal-assemblees-show',
        'legal-contrats': 'legal-contrats-index',
        'legal-contrats-create': 'legal-contrats-form',
        'legal-contrats-edit': 'legal-contrats-form',
        'legal-contrats-show': 'legal-contrats-show',
        'legal-contentieux': 'legal-contentieux-index',
        'legal-contentieux-create': 'legal-contentieux-form',
        'legal-contentieux-show': 'legal-contentieux-show',
        'legal-conformite': 'legal-conformite-index',
        'legal-conformite-create': 'legal-conformite-form',
        'legal-conformite-calendrier': 'legal-conformite-calendrier',
        'legal-bibliotheque': 'legal-bibliotheque-index',
        'legal-bibliotheque-create': 'legal-bibliotheque-form',
        'legal-bibliotheque-edit': 'legal-bibliotheque-form',
        'legal-bibliotheque-generer': 'legal-bibliotheque-generer',
        'legal-registres-show': 'legal-registres-show',
        'legal-dossiers': 'legal-dossiers-index',
        'legal-dossiers-create': 'legal-dossiers-form',
        'legal-dossiers-show': 'legal-dossiers-show',
        'rh-dashboard': 'rh-dashboard',
        'rh-employees': 'rh-employees-index',
        'rh-employees-create': 'rh-employees-form',
        'rh-employees-edit': 'rh-employees-form',
        'rh-employees-show': 'rh-employees-show',
        'rh-contracts': 'rh-contracts-index',
        'rh-leaves': 'rh-leaves-index',
        'rh-expenses': 'rh-expenses-index',
        'rh-payrolls': 'rh-payrolls-index',
        'rh-payrolls-show': 'rh-payrolls-show',
        'rh-attendance': 'rh-attendance-index',
        'rh-trainings': 'rh-trainings-index',
        'rh-alerts': 'rh-alerts-index',
        'company-rh-dashboard': 'company-rh-dashboard',
        'company-rh-employees': 'company-rh-employees',
        'company-rh-leaves': 'company-rh-leaves',
        'company-rh-expenses': 'company-rh-expenses',
        'company-rh-payrolls': 'company-rh-payrolls',
        'company-rh-trainings': 'company-rh-trainings',

        // ─── IT — Helpdesk & Ticketing ─────────────────────────
        'gel-it-tickets': 'gel-it-tickets',
        'gel-it-tickets-form': 'gel-it-tickets-form',
        'gel-it-tickets-show': 'gel-it-tickets-show',
        'gel-it-assets': 'gel-it-assets',
        'gel-it-assets-form': 'gel-it-assets-form',
        'gel-it-assets-show': 'gel-it-assets-show',
        'gel-it-sla-policies': 'gel-it-sla-policies',
        'gel-it-sla-policies-form': 'gel-it-sla-policies-form',
        'gel-it-maintenance-contracts': 'gel-it-maintenance-contracts',
        'gel-it-maintenance-contracts-form': 'gel-it-maintenance-contracts-form',
        'gel-it-maintenance-contracts-show': 'gel-it-maintenance-contracts-show',
        'gel-it-knowledge-base': 'gel-it-knowledge-base',
        'gel-it-knowledge-base-form': 'gel-it-knowledge-base-form',
        'gel-it-knowledge-base-show': 'gel-it-knowledge-base-show',

        // ─── Tontines ────────────────────────────────────────
        'gel-tontines': 'gel-tontines',
        'gel-tontines-form': 'gel-tontines-form',
        'gel-tontines-show': 'gel-tontines-show',

        // ─── Télédéclaration ─────────────────────────────────
        'gel-tele-declarations': 'gel-tele-declarations',
        'gel-tele-declarations-form': 'gel-tele-declarations-form',
        'gel-tele-declarations-show': 'gel-tele-declarations-show',

        // ─── Signatures électroniques ────────────────────────
        'gel-document-signatures': 'gel-document-signatures',
        'gel-document-signatures-form': 'gel-document-signatures-form',
        'gel-document-signatures-show': 'gel-document-signatures-show',
        'gel-document-sign': 'gel-document-sign',

        // ─── Workflows d'approbation ─────────────────────────
        'gel-approval-workflows': 'gel-approval-workflows',
        'gel-approval-workflows-form': 'gel-approval-workflows-form',
        'gel-approval-workflows-show': 'gel-approval-workflows-show',

        // ─── Articles (Blog) ─────────────────────────────────
        'gel-articles': 'gel-articles',
        'gel-articles-form': 'gel-articles-form',
        'gel-articles-show': 'gel-articles-show',

        // ─── Règles de relance ───────────────────────────────
        'gel-relance-rules': 'gel-relance-rules',
        'gel-relance-rules-form': 'gel-relance-rules-form',
        'gel-relance-rules-show': 'gel-relance-rules-show',

        // ─── Centres de coût ─────────────────────────────────
        'gel-cost-centers': 'gel-cost-centers',
        'gel-cost-centers-form': 'gel-cost-centers-form',
        'gel-cost-centers-show': 'gel-cost-centers-show',

        // ─── SLA Policies ──────────────────────────────────
        'gel-it-sla-policies-show': 'gel-it-sla-policies-show',

        // ─── OCR ──────────────────────────────────────────
        'gel-ocr': 'gel-ocr',
        'gel-ocr-form': 'gel-ocr-form',
        'gel-ocr-show': 'gel-ocr-show',

        // ─── Paie — Calculateur IRPP/CNSS ───────────────
        'gel-paie': 'gel-paie',

        // ─── Sécurité — 2FA & Sessions ──────────────────
        'gel-security': 'gel-security',

        // ─── Audit — Journal d'Audit ────────────────────
        'gel-audit': 'gel-audit',

        // ─── IA & Automatisation ─────────────────────────
        'ai-agents': 'ai-agents',
        'gel-ai-feed': 'gel-ai-feed',
        'gel-ai-reconciliation': 'gel-ai-reconciliation',
        'gel-ai-relances': 'gel-ai-relances',
        'gel-ai-ocr': 'gel-ai-ocr',
        'gel-ai-cashflow': 'gel-ai-cashflow',

        // ─── Multi-Tenant / Permissions ─────────────────
        'select-context': 'select-context',
        'gel-client-modules': 'gel-client-modules',
        'company-user-permissions': 'company-user-permissions',

        // ─── Commerce / POS ────────────────────────────
        'commerce-dashboard': 'commerce-dashboard',
        'commerce-products': 'commerce-products',
        'commerce-categories': 'commerce-categories',
        'commerce-suppliers': 'commerce-suppliers',
        'commerce-pos': 'commerce-pos',
        'commerce-inventory': 'commerce-inventory',
        'commerce-business-users': 'commerce-business-users',

        // ─── Onboarding / Inscription entreprise ──────────────
        'register-step1': 'onboarding-step1',
        'register-step2': 'onboarding-step2',
        'register-step3': 'onboarding-step3',
        'register-step4': 'onboarding-step4',
        'register-step5': 'onboarding-step5',
    }

    return map[page] || null
})
</script>
