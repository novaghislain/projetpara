<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';
import { authStore } from '../../../stores/auth';

const props = defineProps({
    clientId: { type: [Number, String], default: null }
});

const data = ref(null);
const loading = ref(true);
const error = ref(null);

const fetchBalance = async () => {
    loading.value = true;
    error.value = null;
    const cid = props.clientId || authStore.user?.client_id;
    if (!cid) { error.value = 'Aucun client sélectionné.'; loading.value = false; return; }
    try {
        const res = await fetch('/api/accounting/reports/balance/' + cid);
        if (!res.ok) throw new Error('Erreur de chargement');
        data.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const totalDebit = (items) => items?.reduce((s, i) => s + parseFloat(i.total_debit || 0), 0) || 0;
const totalCredit = (items) => items?.reduce((s, i) => s + parseFloat(i.total_credit || 0), 0) || 0;

onMounted(fetchBalance);
</script>

<template>
    <GelLayout page-title="Balance Comptable">
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <div v-else class="bg-white rounded-lg shadow p-6">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bi-table me-2 text-primary"></i>Balance des comptes</h6>
                <span class="small text-muted">Arrêtée au {{ data?.date ? $formatDate(data.date) : new Date().toLocaleDateString('fr-FR') }}</span>
            </div>
            <div v-if="!data?.accounts?.length" class="text-muted small py-4 text-center">Aucune donnée de balance disponible.</div>
            <table v-else class="table table-sm table-hover mb-0">
                <thead class="small text-muted">
                    <tr>
                        <th>Compte</th>
                        <th>Libellé</th>
                        <th class="text-end">Débit</th>
                        <th class="text-end">Crédit</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="acc in data.accounts" :key="acc.account_number || acc.id">
                        <td class="fw-medium">{{ acc.account_number }}</td>
                        <td>{{ acc.name }}</td>
                        <td class="text-end">{{ $formatCurrency(acc.total_debit) }}</td>
                        <td class="text-end">{{ $formatCurrency(acc.total_credit) }}</td>
                    </tr>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="2" class="text-end">Totaux</th>
                        <th class="text-end">{{ $formatCurrency(totalDebit(data.accounts)) }}</th>
                        <th class="text-end">{{ $formatCurrency(totalCredit(data.accounts)) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </GelLayout>
</template>
