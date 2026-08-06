<script setup>
import { ref, onMounted } from 'vue';
import CompanyLayout from '../../../../Layouts/CompanyLayout.vue';

const data = ref(null);
const loading = ref(false);
const asOfDate = ref(new Date().toISOString().split('T')[0]);

const fmt = (val) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(val ?? 0) + ' F';

async function loadBilan() {
    loading.value = true;
    try {
        const res = await fetch(`/api/reports/financial-statements/balance-sheet?as_of_date=${asOfDate.value}`, {
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

onMounted(loadBilan);
</script>

<template>
    <CompanyLayout pageTitle="Bilan Comptable SYSCOHADA">
        <div class="container-fluid pt-3 pb-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0"><i class="bi bi-bar-chart-line me-2"></i>Bilan (Actif / Passif)</h4>
                <div class="d-flex gap-2 align-items-center">
                    <input v-model="asOfDate" type="date" class="form-control form-control-sm" style="width: auto;" />
                    <button @click="loadBilan" class="btn btn-primary btn-sm">
                        <i class="bi bi-arrow-repeat me-1"></i>Actualiser
                    </button>
                </div>
            </div>

            <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>

            <div v-if="data" class="row g-4">
                <!-- ACTIF -->
                <div class="col-md-6">
                    <div class="isup-card p-3">
                        <h5 class="fw-bold mb-3" style="color: #163A5E;">ACTIF</h5>
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr><th class="small">Rubrique</th><th class="small text-end">Montant</th></tr>
                            </thead>
                            <tbody>
                                <template v-for="heading in data.actif.headings" :key="heading.code">
                                    <tr v-if="heading.accounts.length > 0">
                                        <td class="fw-medium small">{{ heading.label }}</td>
                                        <td class="text-end small fw-bold">{{ fmt(heading.accounts.reduce((s, a) => s + (a.value || 0), 0)) }}</td>
                                    </tr>
                                    <tr v-for="acc in heading.accounts" :key="acc.account_code">
                                        <td class="ps-4 small text-muted">{{ acc.account_code }} — {{ acc.account_name }}</td>
                                        <td class="text-end small">{{ fmt(acc.value) }}</td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th class="small">TOTAL ACTIF</th>
                                    <th class="text-end small fs-6">{{ fmt(data.actif.total) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- PASSIF -->
                <div class="col-md-6">
                    <div class="isup-card p-3">
                        <h5 class="fw-bold mb-3" style="color: #FF7900;">PASSIF</h5>
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr><th class="small">Rubrique</th><th class="small text-end">Montant</th></tr>
                            </thead>
                            <tbody>
                                <template v-for="heading in data.passif.headings" :key="heading.code">
                                    <tr v-if="heading.code === '13'">
                                        <td class="fw-medium small">{{ heading.label }}</td>
                                        <td class="text-end small fw-bold">{{ fmt(data.resultat_net) }}</td>
                                    </tr>
                                    <tr v-if="heading.accounts.length > 0 && heading.code !== '13'">
                                        <td class="fw-medium small">{{ heading.label }}</td>
                                        <td class="text-end small fw-bold">{{ fmt(heading.accounts.reduce((s, a) => s + (a.value || 0), 0)) }}</td>
                                    </tr>
                                    <tr v-for="acc in (heading.accounts || [])" :key="acc.account_code">
                                        <td class="ps-4 small text-muted">{{ acc.account_code }} — {{ acc.account_name }}</td>
                                        <td class="text-end small">{{ fmt(acc.value) }}</td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th class="small">TOTAL PASSIF</th>
                                    <th class="text-end small fs-6">{{ fmt(data.passif.total) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div v-if="data && data.verification" class="mt-3">
                <div class="alert" :class="data.verification.is_balanced ? 'alert-success' : 'alert-warning'">
                    <i :class="data.verification.is_balanced ? 'bi bi-check-circle' : 'bi bi-exclamation-triangle'" class="me-2"></i>
                    <strong>Vérification :</strong> Actif = {{ fmt(data.verification.total_actif) }} |
                    Passif = {{ fmt(data.verification.total_passif) }} |
                    Différence = {{ fmt(data.verification.difference) }}
                    <span v-if="data.verification.is_balanced" class="ms-2 badge bg-success">ÉQUILIBRÉ</span>
                    <span v-else class="ms-2 badge bg-warning">Écart à corriger</span>
                </div>
            </div>
        </div>
    </CompanyLayout>
</template>
