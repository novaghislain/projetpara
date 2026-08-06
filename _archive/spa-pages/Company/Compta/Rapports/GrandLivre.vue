<script setup>
import { ref, onMounted } from 'vue';
import CompanyLayout from '../../../../Layouts/CompanyLayout.vue';
import AccountSelector from '../../../../Components/Accounting/AccountSelector.vue';
import DateInput from '../../../../Components/Accounting/DateInput.vue';
import DataTable from '../../../../Components/Accounting/DataTable.vue';

const data = ref(null);
const accounts = ref([]);
const loading = ref(false);
const accountId = ref('');
const pagination = ref(null);
const filters = ref({
    start_date: new Date().getFullYear() + '-01-01',
    end_date: new Date().toISOString().split('T')[0],
});

const columns = [
    { key: 'entry_date', label: 'Date', type: 'date' },
    { key: 'reference', label: 'Référence' },
    { key: 'journal_code', label: 'Journal' },
    { key: 'entry_description', label: 'Libellé' },
    { key: 'debit', label: 'Débit', type: 'money' },
    { key: 'credit', label: 'Crédit', type: 'money' },
    { key: 'running_balance', label: 'Solde', type: 'money' },
];

async function loadLedger(page = 1) {
    if (!accountId.value) return;
    loading.value = true;
    try {
        const params = new URLSearchParams({ ...filters.value, page, per_page: 50 });
        const res = await fetch(`/api/reports/ledger/${accountId.value}?${params}`, { headers: { Accept: 'application/json' } });
        const result = await res.json();
        data.value = result.data;
        pagination.value = result.data?.pagination;
    } catch (e) {
        console.error('Erreur:', e);
    } finally {
        loading.value = false;
    }
}

async function loadAccounts() {
    try {
        const res = await fetch('/api/chart-accounts?per_page=500', {
            headers: { Accept: 'application/json' },
            credentials: 'include',
        });
        const result = await res.json();
        accounts.value = result.data?.data || result.data || [];
    } catch (e) { console.error('Erreur:', e); }
}

onMounted(loadAccounts);
</script>

<template>
    <CompanyLayout pageTitle="Grand Livre">
        <div class="container-fluid pt-3 pb-5">
            <div class="isup-card p-3 mb-3">
                <h4 class="fw-bold mb-0"><i class="bi bi-list-columns me-2"></i>Grand Livre</h4>
            </div>

            <div class="isup-card p-3 mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <AccountSelector v-model="accountId" :accounts="accounts" label="Compte" required />
                    </div>
                    <div class="col-md-2">
                        <DateInput v-model="filters.start_date" label="Du" />
                    </div>
                    <div class="col-md-2">
                        <DateInput v-model="filters.end_date" label="Au" />
                    </div>
                    <div class="col-md-2">
                        <button @click="loadLedger()" class="btn btn-primary btn-sm w-100" :disabled="loading || !accountId">
                            <i v-if="loading" class="bi bi-arrow-repeat me-1 spin"></i>
                            <i v-else class="bi bi-search me-1"></i>
                            Consulter
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="data" class="row g-2 mb-3">
                <div class="col-md-3">
                    <div class="isup-stat-card p-3 bg-light rounded">
                        <small class="text-muted">Solde ouverture</small>
                        <p class="fw-bold mb-0 fs-5">{{ new Intl.NumberFormat('fr-FR').format(data.opening_balance) }} F</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="isup-stat-card p-3 bg-light rounded">
                        <small class="text-muted">Total Débit</small>
                        <p class="fw-bold mb-0 fs-5">{{ new Intl.NumberFormat('fr-FR').format(data.total_debit) }} F</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="isup-stat-card p-3 bg-light rounded">
                        <small class="text-muted">Total Crédit</small>
                        <p class="fw-bold mb-0 fs-5">{{ new Intl.NumberFormat('fr-FR').format(data.total_credit) }} F</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="isup-stat-card p-3 bg-light rounded">
                        <small class="text-muted">Solde clôture</small>
                        <p class="fw-bold mb-0 fs-5" :class="data.closing_balance >= 0 ? 'text-success' : 'text-danger'">
                            {{ new Intl.NumberFormat('fr-FR').format(data.closing_balance) }} F
                        </p>
                    </div>
                </div>
            </div>

            <DataTable
                :columns="columns"
                :data="data?.lines || []"
                :pagination="pagination"
                @page-change="loadLedger"
            />

            <div v-if="!data && !loading" class="text-center text-muted py-5">
                <i class="bi bi-book fs-1 d-block mb-3"></i>
                <p>Sélectionnez un compte et cliquez sur "Consulter"</p>
            </div>
        </div>
    </CompanyLayout>
</template>

<style scoped>
.spin { animation: spin 1s linear infinite; }
@keyframes spin { 100% { transform: rotate(360deg); } }
.isup-stat-card { border: 1px solid var(--border); }
</style>
