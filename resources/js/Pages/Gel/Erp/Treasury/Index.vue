<template>
    <GelLayout pageTitle="Caisse & Trésorerie">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-1 text-gray-800">Caisse & Trésorerie</h2>
                    <p class="text-muted mb-0">Gestion des comptes bancaires, caisses et suivi des flux</p>
                </div>
                <div>
                    <button class="btn btn-outline-success me-2">
                        <i class="bi bi-arrow-down-circle me-1"></i> Encaissement
                    </button>
                    <button class="btn btn-outline-danger me-2">
                        <i class="bi bi-arrow-up-circle me-1"></i> Décaissement
                    </button>
                    <button class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Nouveau Compte
                    </button>
                </div>
            </div>

            <!-- Comptes de Trésorerie -->
            <div class="row mb-4">
                <div class="col-md-4" v-for="account in accounts" :key="account.id">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded-circle p-2 me-3 text-primary">
                                        <i class="bi fs-4" :class="account.type === 'Banque' ? 'bi-bank' : 'bi-cash-coin'"></i>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-0 fw-bold">{{ account.name }}</h5>
                                        <small class="text-muted font-monospace">{{ account.number }}</small>
                                    </div>
                                </div>
                            </div>
                            <h3 class="fw-bold tabular-nums text-end mb-2" :class="account.balance < 0 ? 'text-danger' : 'text-success'">
                                {{ formatCurrency(account.balance) }}
                            </h3>
                            <div class="d-flex justify-content-between text-muted small mt-3 border-top pt-2">
                                <span><i class="bi bi-arrow-down text-success"></i> Entrées: {{ formatCurrency(account.incomes_month) }}</span>
                                <span><i class="bi bi-arrow-up text-danger"></i> Sorties: {{ formatCurrency(account.expenses_month) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dernières Transactions -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-gray-800">Derniers Mouvements</h6>
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control bg-light border-start-0" placeholder="Rechercher..." v-model="search">
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-dense mb-0 align-middle">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th width="12%">Date</th>
                                    <th width="15%">Compte</th>
                                    <th width="35%">Libellé / Référence</th>
                                    <th width="12%">Type</th>
                                    <th width="15%" class="text-end">Montant</th>
                                    <th width="11%" class="text-end">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="trx in filteredTransactions" :key="trx.id">
                                    <td>{{ trx.date }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ trx.account }}</span></td>
                                    <td>{{ trx.description }}</td>
                                    <td>
                                        <span class="badge rounded-pill" :class="trx.type === 'Encaissement' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'">
                                            <i class="bi me-1" :class="trx.type === 'Encaissement' ? 'bi-arrow-down-left' : 'bi-arrow-up-right'"></i>
                                            {{ trx.type }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold tabular-nums" :class="trx.type === 'Encaissement' ? 'text-success' : 'text-danger'">
                                        {{ trx.type === 'Encaissement' ? '+' : '-' }} {{ formatCurrency(trx.amount) }}
                                    </td>
                                    <td class="text-end">
                                        <span class="badge" :class="trx.status === 'Rapproché' ? 'bg-success' : 'bg-warning text-dark'">
                                            {{ trx.status }}
                                        </span>
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

const accounts = ref([
    { id: 1, name: 'BGFIBank Principal', type: 'Banque', number: 'BJ063 01001 00123456789 12', balance: 15450000, incomes_month: 2500000, expenses_month: 1200000 },
    { id: 2, name: 'Ecobank Devises', type: 'Banque', number: 'BJ010 01001 98765432100 45', balance: 8200000, incomes_month: 0, expenses_month: 500000 },
    { id: 3, name: 'Caisse Principale', type: 'Caisse', number: '531100', balance: 250000, incomes_month: 150000, expenses_month: 200000 },
]);

const transactions = ref([
    { id: 1, date: '15/08/2026', account: 'BGFIBank Principal', description: 'Règlement Facture FA-2026-0001 (TECH SOLUCE)', type: 'Encaissement', amount: 1500000, status: 'Non rapproché' },
    { id: 2, date: '14/08/2026', account: 'BGFIBank Principal', description: 'Paiement Fournisseur OLA Energy', type: 'Décaissement', amount: 450000, status: 'Rapproché' },
    { id: 3, date: '12/08/2026', account: 'Caisse Principale', description: 'Achat petites fournitures', type: 'Décaissement', amount: 15000, status: 'Rapproché' },
    { id: 4, date: '10/08/2026', account: 'BGFIBank Principal', description: 'Virement Salaires Août', type: 'Décaissement', amount: 3200000, status: 'Non rapproché' },
    { id: 5, date: '05/08/2026', account: 'Ecobank Devises', description: 'Paiement Abonnement AWS', type: 'Décaissement', amount: 125000, status: 'Rapproché' },
]);

const filteredTransactions = computed(() => {
    if (!search.value) return transactions.value;
    const s = search.value.toLowerCase();
    return transactions.value.filter(t => t.description.toLowerCase().includes(s) || t.account.toLowerCase().includes(s));
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF', maximumFractionDigits: 0 }).format(value).replace('XOF', 'FCFA');
};
</script>

<style scoped>
.table-dense th, .table-dense td {
    padding: 0.5rem;
    font-size: 0.85rem;
}
.bg-success-subtle { background-color: #d1e7dd !important; }
.bg-danger-subtle { background-color: #f8d7da !important; }
</style>
