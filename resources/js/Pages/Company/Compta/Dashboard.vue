<script setup>
import { computed } from 'vue';
import CompanyLayout from '../../../Layouts/CompanyLayout.vue';

const props = defineProps({
    stats: Object,
    dernieres_ecritures: Array,
    comptes_tresorerie: Array,
});

const tresorerieFormatted = computed(() =>
    new Intl.NumberFormat('fr-BJ', { style: 'currency', currency: 'XOF', minimumFractionDigits: 0 })
        .format(props.stats?.total_tresorerie ?? 0)
);

const formatMontant = (val) =>
    new Intl.NumberFormat('fr-BJ', { minimumFractionDigits: 0 }).format(val ?? 0);

const navItems = [
    { label: 'Plan Comptable', icon: 'bi-journal-bookmark-fill', route: 'compta.comptes.index', color: '#163A5E' },
    { label: 'Journaux', icon: 'bi-book-fill', route: 'compta.journaux.index', color: '#FF7900' },
    { label: 'Écritures', icon: 'bi-pencil-square', route: 'compta.ecritures.index', color: '#163A5E' },
    { label: 'Factures', icon: 'bi-receipt-cutoff', route: 'compta.factures.index', color: '#FF7900' },
    { label: 'Banque', icon: 'bi-bank2', route: 'compta.banque.index', color: '#163A5E' },
    { label: 'TVA', icon: 'bi-percent', route: 'compta.tva.taux', color: '#FF7900' },
    { label: 'Bilan', icon: 'bi-bar-chart-line-fill', route: 'compta.rapports.bilan', color: '#163A5E' },
    { label: 'Résultat', icon: 'bi-graph-up-arrow', route: 'compta.rapports.resultat', color: '#FF7900' },
    { label: 'Balance', icon: 'bi-list-columns-reverse', route: 'compta.rapports.balance', color: '#163A5E' },
    { label: 'Grand Livre', icon: 'bi-layers-fill', route: 'compta.rapports.grand-livre', color: '#FF7900' },
];
</script>

<template>
    <CompanyLayout page-title="Comptabilité — Tableau de Bord">
        <div class="isup-compta-dashboard">

            <!-- Header -->
            <div class="isup-compta-header mb-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="isup-icon-wrap">
                        <i class="bi bi-calculator-fill"></i>
                    </div>
                    <div>
                        <h1 class="isup-page-title mb-0">Module Comptabilité</h1>
                        <p class="isup-page-subtitle mb-0">SYSCOHADA Révisé — Gestion complète de votre comptabilité</p>
                    </div>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="isup-kpi-card" style="--accent: #FF7900">
                        <div class="isup-kpi-icon"><i class="bi bi-bank2"></i></div>
                        <div class="isup-kpi-value">{{ tresorerieFormatted }}</div>
                        <div class="isup-kpi-label">Trésorerie nette</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="isup-kpi-card" style="--accent: #163A5E">
                        <div class="isup-kpi-icon"><i class="bi bi-journal-bookmark-fill"></i></div>
                        <div class="isup-kpi-value">{{ stats?.total_comptes ?? 0 }}</div>
                        <div class="isup-kpi-label">Comptes actifs</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="isup-kpi-card" style="--accent: #FF7900">
                        <div class="isup-kpi-icon"><i class="bi bi-book-fill"></i></div>
                        <div class="isup-kpi-value">{{ stats?.total_journaux ?? 0 }}</div>
                        <div class="isup-kpi-label">Journaux ouverts</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="isup-kpi-card" style="--accent: #163A5E">
                        <div class="isup-kpi-icon"><i class="bi bi-calendar3"></i></div>
                        <div class="isup-kpi-value">{{ stats?.exercice?.libelle ?? 'N/A' }}</div>
                        <div class="isup-kpi-label">Exercice en cours</div>
                    </div>
                </div>
            </div>

            <!-- Navigation rapide -->
            <div class="isup-section-title mb-3">
                <i class="bi bi-grid-3x3-gap-fill me-2" style="color:#FF7900"></i>
                Accès rapide
            </div>
            <div class="row g-2 mb-4">
                <div v-for="item in navItems" :key="item.route" class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <a :href="`/company/comptabilite/${item.route.replace('compta.', '').replace('.index', '').replace('.', '/')}`"
                       class="isup-nav-card text-decoration-none">
                        <div class="isup-nav-icon" :style="`color: ${item.color}`">
                            <i :class="`bi ${item.icon}`"></i>
                        </div>
                        <div class="isup-nav-label">{{ item.label }}</div>
                        <div class="isup-nav-arrow"><i class="bi bi-arrow-right-short"></i></div>
                    </a>
                </div>
            </div>

            <!-- Dernières écritures -->
            <div class="isup-section-title mb-3">
                <i class="bi bi-clock-history me-2" style="color:#FF7900"></i>
                Dernières écritures comptables
            </div>
            <div class="isup-card">
                <div v-if="dernieres_ecritures?.length === 0" class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                    Aucune écriture enregistrée pour le moment.
                </div>
                <div v-else class="table-responsive">
                    <table class="isup-table">
                        <thead>
                            <tr>
                                <th>Référence</th>
                                <th>Date</th>
                                <th>Journal</th>
                                <th>Libellé</th>
                                <th class="text-end">Débit</th>
                                <th class="text-end">Crédit</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="e in dernieres_ecritures" :key="e.id">
                                <td><code class="isup-ref">{{ e.reference }}</code></td>
                                <td>{{ new Date(e.date_ecriture).toLocaleDateString('fr-FR') }}</td>
                                <td>
                                    <span class="isup-badge-journal">{{ e.journal?.code }}</span>
                                </td>
                                <td>{{ e.libelle }}</td>
                                <td class="text-end fw-semibold">{{ formatMontant(e.total_debit) }}</td>
                                <td class="text-end fw-semibold">{{ formatMontant(e.total_credit) }}</td>
                                <td>
                                    <span v-if="e.is_validee" class="isup-badge-success">Validée</span>
                                    <span v-else class="isup-badge-warning">Brouillon</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Soldes bancaires -->
            <div v-if="comptes_tresorerie?.length > 0" class="mt-4">
                <div class="isup-section-title mb-3">
                    <i class="bi bi-credit-card-2-front me-2" style="color:#FF7900"></i>
                    Soldes des comptes de trésorerie (Classe 5)
                </div>
                <div class="row g-3">
                    <div v-for="c in comptes_tresorerie" :key="c.id" class="col-md-4">
                        <div class="isup-bank-card">
                            <div class="isup-bank-numero">{{ c.numero }}</div>
                            <div class="isup-bank-name">{{ c.intitule }}</div>
                            <div class="isup-bank-solde" :class="(c.solde_debiteur - c.solde_crediteur) >= 0 ? 'positive' : 'negative'">
                                {{ formatMontant(c.solde_debiteur - c.solde_crediteur) }} XOF
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </CompanyLayout>
</template>

<style scoped>
.isup-compta-dashboard {
    font-family: 'Inter', sans-serif;
}

/* Header */
.isup-compta-header {
    background: linear-gradient(135deg, #163A5E 0%, #1a4a76 100%);
    border-radius: 16px;
    padding: 28px 32px;
    color: white;
    box-shadow: 0 4px 24px rgba(22,58,94,0.25);
}
.isup-icon-wrap {
    width: 56px;
    height: 56px;
    background: rgba(255,121,0,0.2);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    color: #FF7900;
    flex-shrink: 0;
}
.isup-page-title { font-size: 22px; font-weight: 700; }
.isup-page-subtitle { font-size: 13px; color: rgba(255,255,255,0.7); }

/* KPI Cards */
.isup-kpi-card {
    background: white;
    border-radius: 14px;
    padding: 20px;
    border: 1px solid #eef0f4;
    border-top: 4px solid var(--accent, #163A5E);
    box-shadow: 0 2px 12px rgba(0,0,0,0.05);
    transition: transform 0.2s, box-shadow 0.2s;
}
.isup-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.10);
}
.isup-kpi-icon {
    font-size: 22px;
    color: var(--accent, #163A5E);
    margin-bottom: 10px;
}
.isup-kpi-value { font-size: 20px; font-weight: 800; color: #1a2938; line-height: 1.2; }
.isup-kpi-label { font-size: 12px; color: #7a8499; margin-top: 4px; font-weight: 500; }

/* Section Title */
.isup-section-title { font-size: 15px; font-weight: 700; color: #1a2938; }

/* Nav cards */
.isup-nav-card {
    background: white;
    border-radius: 12px;
    padding: 14px 12px;
    border: 1px solid #eef0f4;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    position: relative;
    overflow: hidden;
}
.isup-nav-card:hover {
    border-color: #FF7900;
    box-shadow: 0 4px 16px rgba(255,121,0,0.15);
    transform: translateY(-2px);
}
.isup-nav-icon { font-size: 24px; margin-bottom: 6px; transition: transform 0.2s; }
.isup-nav-card:hover .isup-nav-icon { transform: scale(1.15); }
.isup-nav-label { font-size: 11px; font-weight: 600; color: #1a2938; }
.isup-nav-arrow { font-size: 16px; color: #FF7900; opacity: 0; transition: opacity 0.2s; }
.isup-nav-card:hover .isup-nav-arrow { opacity: 1; }

/* Table */
.isup-card {
    background: white;
    border-radius: 14px;
    border: 1px solid #eef0f4;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,0.05);
}
.isup-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.isup-table thead tr { background: #f8f9fc; }
.isup-table th {
    padding: 12px 16px;
    font-weight: 700;
    color: #4a5568;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #eef0f4;
}
.isup-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #f4f6fa;
    color: #2d3748;
}
.isup-table tbody tr:hover { background: #fafbff; }
.isup-ref { font-size: 11px; background: #f0f2f8; padding: 2px 6px; border-radius: 5px; color: #163A5E; }
.isup-badge-journal {
    background: #163A5E;
    color: white;
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 20px;
    font-weight: 600;
}
.isup-badge-success {
    background: #d1fae5; color: #065f46; padding: 3px 10px;
    border-radius: 20px; font-size: 11px; font-weight: 600;
}
.isup-badge-warning {
    background: #fef3c7; color: #92400e; padding: 3px 10px;
    border-radius: 20px; font-size: 11px; font-weight: 600;
}

/* Bank cards */
.isup-bank-card {
    background: linear-gradient(135deg, #1a2938 0%, #163A5E 100%);
    color: white;
    border-radius: 16px;
    padding: 22px 24px;
    box-shadow: 0 4px 18px rgba(22,58,94,0.22);
    transition: transform 0.2s;
}
.isup-bank-card:hover { transform: translateY(-3px); }
.isup-bank-numero { font-size: 12px; color: rgba(255,255,255,0.6); font-weight: 600; letter-spacing: 1px; }
.isup-bank-name { font-size: 15px; font-weight: 700; margin: 6px 0; }
.isup-bank-solde { font-size: 22px; font-weight: 800; }
.isup-bank-solde.positive { color: #34d399; }
.isup-bank-solde.negative { color: #f87171; }
</style>
