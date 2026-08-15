<template>
    <GelLayout pageTitle="Facturation & e-MECeF">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-1 text-gray-800">Facturation & e-MECeF</h2>
                    <p class="text-muted mb-0">Gestion centralisée des factures de vente et normalisation</p>
                </div>
                <button class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Créer une Facture
                </button>
            </div>

            <!-- KPI Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-start-primary shadow-sm h-100 py-2 border-0">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Chiffre d'Affaires (Mois)</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800 tabular-nums">4 500 000 FCFA</div>
                                </div>
                                <div class="col-auto"><i class="bi bi-wallet2 fs-2 text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-start-success shadow-sm h-100 py-2 border-0">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Factures Payées</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800 tabular-nums">85%</div>
                                </div>
                                <div class="col-auto"><i class="bi bi-check-circle fs-2 text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-start-warning shadow-sm h-100 py-2 border-0">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">En attente normalisation</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800 tabular-nums">3 factures</div>
                                </div>
                                <div class="col-auto"><i class="bi bi-hourglass-split fs-2 text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-start-danger shadow-sm h-100 py-2 border-0">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Impayés</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800 tabular-nums">1 200 000 FCFA</div>
                                </div>
                                <div class="col-auto"><i class="bi bi-exclamation-triangle fs-2 text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des factures -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <div class="input-group input-group-sm" style="width: 300px;">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control bg-light border-start-0" placeholder="Rechercher (N°, Client)..." v-model="search">
                    </div>
                    <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-filter"></i> Filtres</button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-dense mb-0 align-middle">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th width="12%">N° Facture</th>
                                    <th width="12%">Date</th>
                                    <th width="25%">Client</th>
                                    <th width="15%" class="text-end">Montant TTC</th>
                                    <th width="12%" class="text-center">Statut e-MECeF</th>
                                    <th width="12%" class="text-center">Paiement</th>
                                    <th width="12%" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="invoice in filteredInvoices" :key="invoice.id">
                                    <td class="font-monospace fw-bold">{{ invoice.reference }}</td>
                                    <td>{{ invoice.date }}</td>
                                    <td>{{ invoice.client }}</td>
                                    <td class="text-end tabular-nums fw-medium">{{ formatCurrency(invoice.total_ttc) }}</td>
                                    <td class="text-center">
                                        <span class="badge" :class="invoice.emccef_status === 'normalisee' ? 'bg-success' : 'bg-warning text-dark'">
                                            {{ invoice.emccef_status === 'normalisee' ? 'Normalisée' : 'En attente' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge" :class="getPaymentBadgeClass(invoice.payment_status)">
                                            {{ invoice.payment_status }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-light text-primary me-1" title="Voir"><i class="bi bi-eye"></i></button>
                                        <button class="btn btn-sm btn-light text-success" title="Normaliser" v-if="invoice.emccef_status !== 'normalisee'"><i class="bi bi-upc-scan"></i></button>
                                        <button class="btn btn-sm btn-light text-danger" title="Télécharger PDF" v-else><i class="bi bi-file-earmark-pdf"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const search = ref('');

const invoices = ref([
    { id: 1, reference: 'FA-2026-0001', date: '01/08/2026', client: 'TECH SOLUCE SARL', total_ttc: 1500000, emccef_status: 'normalisee', payment_status: 'Payé' },
    { id: 2, reference: 'FA-2026-0002', date: '05/08/2026', client: 'MINISTÈRE DU PLAN', total_ttc: 3250000, emccef_status: 'normalisee', payment_status: 'En attente' },
    { id: 3, reference: 'FA-2026-0003', date: '10/08/2026', client: 'AGENCE NATIONALE', total_ttc: 750000, emccef_status: 'attente', payment_status: 'En attente' },
    { id: 4, reference: 'FA-2026-0004', date: '12/08/2026', client: 'BOUTIQUE LA GRACE', total_ttc: 120000, emccef_status: 'attente', payment_status: 'Impayé' },
]);

const filteredInvoices = computed(() => {
    if (!search.value) return invoices.value;
    const s = search.value.toLowerCase();
    return invoices.value.filter(i => i.reference.toLowerCase().includes(s) || i.client.toLowerCase().includes(s));
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF', maximumFractionDigits: 0 }).format(value).replace('XOF', 'FCFA');
};

const getPaymentBadgeClass = (status) => {
    switch(status) {
        case 'Payé': return 'bg-success';
        case 'En attente': return 'bg-secondary';
        case 'Impayé': return 'bg-danger';
        default: return 'bg-dark';
    }
};
</script>

<style scoped>
.table-dense th, .table-dense td {
    padding: 0.5rem;
    font-size: 0.85rem;
}
.border-start-primary { border-left: 4px solid #4e73df !important; }
.border-start-success { border-left: 4px solid #1cc88a !important; }
.border-start-info { border-left: 4px solid #36b9cc !important; }
.border-start-warning { border-left: 4px solid #f6c23e !important; }
.border-start-danger { border-left: 4px solid #e74a3b !important; }
</style>
