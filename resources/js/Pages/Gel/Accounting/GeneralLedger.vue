<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';
import { authStore } from '../../../stores/auth';

const props = defineProps({
    clientId: { type: [Number, String], default: null }
});

const accounts = ref([]);
const data = ref(null);
const selectedAccountId = ref('');
const loading = ref(true);
const error = ref(null);

const fetchData = async () => {
    loading.value = true;
    error.value = null;
    const cid = props.clientId || authStore.user?.client_id;
    if (!cid) { error.value = 'Aucun client sélectionné.'; loading.value = false; return; }
    try {
        // Fetch accounts first for filter
        const accRes = await fetch('/api/accounting/accounts/' + cid);
        if (accRes.ok) accounts.value = await accRes.json();

        const params = new URLSearchParams();
        if (selectedAccountId.value) params.append('account_id', selectedAccountId.value);
        const res = await fetch('/api/accounting/reports/grand-livre/' + cid + '?' + params.toString());
        if (!res.ok) throw new Error('Erreur de chargement');
        data.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const runningBalance = (entries) => {
    if (!entries?.length) return null;
    let balance = 0;
    return entries.map(e => {
        const d = parseFloat(e.debit) || 0;
        const c = parseFloat(e.credit) || 0;
        balance += d - c;
        return { ...e, balance };
    });
};

onMounted(fetchData);
</script>

<template>
    <GelLayout page-title="Grand Livre">
        <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
            <select v-model="selectedAccountId" class="form-select form-select-sm" style="width:auto;" @change="fetchData">
                <option value="">Tous les comptes</option>
                <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
                    {{ acc.account_number }} - {{ acc.name }}
                </option>
            </select>
            <span class="small text-muted">{{ data?.entries?.length || 0 }} écritures</span>
        </div>

        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <div v-else class="bg-white rounded-lg shadow p-6">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="bi-journal-text me-2 text-primary"></i>Grand Livre</h6>
                    <span class="small text-muted">{{ data?.account ? data.account.account_number + ' - ' + data.account.name : 'Tous comptes' }}</span>
                </div>
            </div>
            <div v-if="!data?.entries?.length" class="text-muted small py-4 text-center">Aucune écriture trouvée.</div>
            <table v-else class="table table-sm table-hover mb-0" v-for="(group, gIdx) in data.grouped || [{account: data.account, entries: data.entries, account_number: data.account?.account_number}]" :key="gIdx">
                <thead v-if="data.grouped" class="table-light">
                    <tr>
                        <th colspan="5" class="fw-bold small">
                            {{ group.account_number }} - {{ group.account_name }}
                        </th>
                    </tr>
                    <tr class="small text-muted">
                        <th>Date</th><th>Libellé</th><th class="text-end">Débit</th><th class="text-end">Crédit</th><th class="text-end">Solde</th>
                    </tr>
                </thead>
                <thead v-else class="small text-muted">
                    <tr><th>Date</th><th>Libellé</th><th class="text-end">Débit</th><th class="text-end">Crédit</th><th class="text-end">Solde</th></tr>
                </thead>
                <tbody>
                    <tr v-for="entry in (data.grouped ? group.entries : data.entries)" :key="entry.id">
                        <td class="small">{{ $formatDate(entry.date || entry.created_at) }}</td>
                        <td class="small">{{ entry.label || entry.description }}</td>
                        <td class="text-end small">{{ entry.debit ? $formatCurrency(entry.debit) : '-' }}</td>
                        <td class="text-end small">{{ entry.credit ? $formatCurrency(entry.credit) : '-' }}</td>
                        <td class="text-end small fw-medium">{{ $formatCurrency(entry.balance || entry.solde) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </GelLayout>
</template>
