<script setup>
import { ref, onMounted } from 'vue';
import CompanyLayout from '../../../../Layouts/CompanyLayout.vue';
import DataTable from '../../../../Components/Accounting/DataTable.vue';

const data = ref(null);
const loading = ref(false);
const asOfDate = ref(new Date().toISOString().split('T')[0]);

const columns = [
    { key: 'account_code', label: 'Compte', sortable: true },
    { key: 'account_name', label: 'Libellé' },
    { key: 'class', label: 'Cl' },
    { key: 'total_debit', label: 'Total Débit', type: 'money' },
    { key: 'total_credit', label: 'Total Crédit', type: 'money' },
    { key: 'balance', label: 'Solde', type: 'money' },
];

const fmt = (val) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(val ?? 0) + ' F';

async function loadTrialBalance() {
    loading.value = true;
    try {
        const res = await fetch(`/api/reports/financial-statements/trial-balance?as_of_date=${asOfDate.value}`, {
            headers: { Accept: 'application/json' },
        });
        const result = await res.json();
        data.value = result.data;
    } catch (e) {
        console.error('Erreur:', e);
    } finally {
        loading.value = false;
    }
}

onMounted(loadTrialBalance);
</script>

<template>
    <CompanyLayout pageTitle="Balance de Vérification">
        <div class="container-fluid pt-3 pb-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0"><i class="bi bi-check2-square me-2"></i>Balance de Vérification</h4>
                <div class="d-flex gap-2 align-items-center">
                    <input v-model="asOfDate" type="date" class="form-control form-control-sm" style="width: auto;" />
                    <button @click="loadTrialBalance" class="btn btn-primary btn-sm">
                        <i class="bi bi-arrow-repeat me-1"></i>Actualiser
                    </button>
                </div>
            </div>

            <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>

            <div v-if="data" class="row g-2 mb-3">
                <div class="col-md-3">
                    <div class="isup-card p-2 text-center">
                        <small class="text-muted">Total Débit</small>
                        <p class="fw-bold mb-0 text-primary">{{ fmt(data.totals.total_debit) }}</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="isup-card p-2 text-center">
                        <small class="text-muted">Total Crédit</small>
                        <p class="fw-bold mb-0 text-primary">{{ fmt(data.totals.total_credit) }}</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="isup-card p-2 text-center">
                        <small class="text-muted">Différence</small>
                        <p class="fw-bold mb-0" :class="data.totals.is_balanced ? 'text-success' : 'text-danger'">
                            {{ fmt(data.totals.difference) }}
                        </p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="isup-card p-2 text-center">
                        <small class="text-muted">Statut</small>
                        <p class="fw-bold mb-0">
                            <span v-if="data.totals.is_balanced" class="badge bg-success">ÉQUILIBRÉE</span>
                            <span v-else class="badge bg-danger">DÉSÉQUILIBRÉE</span>
                        </p>
                    </div>
                </div>
            </div>

            <DataTable :columns="columns" :data="data?.lines || []" />

            <div v-if="data" class="mt-2">
                <small class="text-muted">
                    {{ data.summary.total_accounts }} comptes ·
                    {{ data.summary.debit_balances }} soldes débiteurs ·
                    {{ data.summary.credit_balances }} soldes créditeurs ·
                    {{ data.summary.zero_balances }} soldes nuls
                </small>
            </div>
        </div>
    </CompanyLayout>
</template>
