<script setup>
import { ref, onMounted } from 'vue';
import CompanyLayout from '../../../../Layouts/CompanyLayout.vue';

const props = defineProps({
    type: { type: String, default: 'customer' },
});

const data = ref(null);
const loading = ref(false);
const type = ref(props.type);
const asOfDate = ref(new Date().toISOString().split('T')[0]);

const fmt = (val) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(val ?? 0) + ' F';

async function loadAging() {
    loading.value = true;
    try {
        const params = new URLSearchParams({ type: type.value, as_of_date: asOfDate.value });
        const res = await fetch(`/api/reports/financial-statements/aging?${params}`, {
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

function agingTotal(account) {
    return Object.values(account.aging || {}).reduce((s, v) => s + v, 0);
}

onMounted(loadAging);
</script>

<template>
    <CompanyLayout pageTitle="Balance Âgée">
        <div class="container-fluid pt-3 pb-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>Balance Âgée</h4>
                <div class="d-flex gap-2 align-items-center">
                    <select v-model="type" class="form-select form-select-sm" style="width: auto;">
                        <option value="customer">Clients</option>
                        <option value="supplier">Fournisseurs</option>
                    </select>
                    <input v-model="asOfDate" type="date" class="form-control form-control-sm" style="width: auto;" />
                    <button @click="loadAging" class="btn btn-primary btn-sm">
                        <i class="bi bi-arrow-repeat me-1"></i>Actualiser
                    </button>
                </div>
            </div>

            <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>

            <div v-if="data" class="isup-card p-3">
                <table class="table table-sm">
                    <thead class="table-light">
                        <tr>
                            <th class="small">Compte</th>
                            <th class="small">Libellé</th>
                            <th class="small text-end">Solde</th>
                            <th class="small text-end">0-30 jours</th>
                            <th class="small text-end">31-60 jours</th>
                            <th class="small text-end">61-90 jours</th>
                            <th class="small text-end">91+ jours</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="acc in data.accounts" :key="acc.account_code">
                            <td class="small font-mono">{{ acc.account_code }}</td>
                            <td class="small">{{ acc.account_name }}</td>
                            <td class="small text-end fw-bold">{{ fmt(acc.balance) }}</td>
                            <td class="small text-end" :class="acc.aging['0_30'] > 0 ? 'text-success' : ''">{{ fmt(acc.aging['0_30']) }}</td>
                            <td class="small text-end" :class="acc.aging['31_60'] > 0 ? 'text-warning' : ''">{{ fmt(acc.aging['31_60']) }}</td>
                            <td class="small text-end" :class="acc.aging['61_90'] > 0 ? 'text-warning' : ''">{{ fmt(acc.aging['61_90']) }}</td>
                            <td class="small text-end fw-bold" :class="acc.aging['91_plus'] > 0 ? 'text-danger' : ''">{{ fmt(acc.aging['91_plus']) }}</td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="data.accounts.length === 0" class="text-center text-muted py-4">
                    Aucun compte {{ type === 'customer' ? 'client' : 'fournisseur' }} trouvé
                </div>

                <small class="text-muted">{{ data.total }} comptes · Arrêté au {{ asOfDate }}</small>
            </div>
        </div>
    </CompanyLayout>
</template>

<style scoped>
.font-mono { font-family: 'Courier New', monospace; }
</style>
