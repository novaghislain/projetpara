<script setup>
import { ref, onMounted } from 'vue';
import CompanyLayout from '../../../../Layouts/CompanyLayout.vue';
import DateInput from '../../../../Components/Accounting/DateInput.vue';

const data = ref(null);
const loading = ref(false);
const filters = ref({
    start_date: new Date().getFullYear() + '-01-01',
    end_date: new Date().toISOString().split('T')[0],
});

const fmt = (val) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(val ?? 0) + ' F';

async function loadCashFlow() {
    loading.value = true;
    try {
        const params = new URLSearchParams(filters.value);
        const res = await fetch(`/api/reports/financial-statements/cash-flow?${params}`, {
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

onMounted(loadCashFlow);
</script>

<template>
    <CompanyLayout pageTitle="Tableau de Flux de Trésorerie">
        <div class="container-fluid pt-3 pb-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0"><i class="bi bi-cash-stack me-2"></i>Flux de Trésorerie</h4>
                <div class="d-flex gap-2 align-items-center">
                    <DateInput v-model="filters.start_date" label="Du" />
                    <DateInput v-model="filters.end_date" label="Au" />
                    <button @click="loadCashFlow" class="btn btn-primary btn-sm">
                        <i class="bi bi-arrow-repeat me-1"></i>Actualiser
                    </button>
                </div>
            </div>

            <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>

            <div v-if="data" class="row g-3">
                <!-- Flux d'exploitation -->
                <div class="col-md-4">
                    <div class="isup-card p-3 h-100">
                        <h6 class="fw-bold mb-3"><i class="bi bi-gear me-2 text-primary"></i>Exploitation</h6>
                        <table class="table table-sm">
                            <tr><td class="small">Résultat net</td><td class="text-end small">{{ fmt(data.resultat_net) }}</td></tr>
                            <tr><td class="small">Dotations amortissements</td><td class="text-end small text-success">+{{ fmt(data.ajustements.dotations_amortissements) }}</td></tr>
                            <tr><td class="small">Reprises provisions</td><td class="text-end small text-danger">-{{ fmt(data.ajustements.reprises_provisions) }}</td></tr>
                            <tr><td class="small">Variation BFR</td><td class="text-end small" :class="data.variation_bfr.variation >= 0 ? 'text-danger' : 'text-success'">{{ fmt(-data.variation_bfr.variation) }}</td></tr>
                            <tr class="table-primary"><th class="small">FLUX NET</th><th class="text-end small">{{ fmt(data.flux_tresorerie.exploitation) }}</th></tr>
                        </table>
                    </div>
                </div>

                <!-- Flux d'investissement -->
                <div class="col-md-4">
                    <div class="isup-card p-3 h-100">
                        <h6 class="fw-bold mb-3"><i class="bi bi-building me-2 text-warning"></i>Investissement</h6>
                        <table class="table table-sm">
                            <tr><td class="small">Acquisitions</td><td class="text-end small text-danger">-{{ fmt(0) }}</td></tr>
                            <tr><td class="small">Cessions</td><td class="text-end small text-success">+{{ fmt(0) }}</td></tr>
                            <tr class="table-warning"><th class="small">FLUX NET</th><th class="text-end small">{{ fmt(data.flux_tresorerie.investissement) }}</th></tr>
                        </table>
                    </div>
                </div>

                <!-- Flux de financement -->
                <div class="col-md-4">
                    <div class="isup-card p-3 h-100">
                        <h6 class="fw-bold mb-3"><i class="bi bi-bank me-2 text-success"></i>Financement</h6>
                        <table class="table table-sm">
                            <tr><td class="small">Variation emprunts</td><td class="text-end small">{{ fmt(data.flux_tresorerie.financement) }}</td></tr>
                            <tr class="table-success"><th class="small">FLUX NET</th><th class="text-end small">{{ fmt(data.flux_tresorerie.financement) }}</th></tr>
                        </table>
                    </div>
                </div>

                <!-- Synthèse -->
                <div class="col-12">
                    <div class="isup-card p-3">
                        <h6 class="fw-bold mb-3">Synthèse</h6>
                        <div class="row g-2">
                            <div class="col-md-3">
                                <div class="p-2 bg-light rounded">
                                    <small class="text-muted">Flux exploitation</small>
                                    <p class="fw-bold mb-0" :class="data.flux_tresorerie.exploitation >= 0 ? 'text-success' : 'text-danger'">
                                        {{ fmt(data.flux_tresorerie.exploitation) }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-2 bg-light rounded">
                                    <small class="text-muted">Flux investissement</small>
                                    <p class="fw-bold mb-0 text-warning">{{ fmt(data.flux_tresorerie.investissement) }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-2 bg-light rounded">
                                    <small class="text-muted">Flux financement</small>
                                    <p class="fw-bold mb-0 text-info">{{ fmt(data.flux_tresorerie.financement) }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-2 rounded" style="background: var(--primary-light);">
                                    <small class="text-muted">Variation nette</small>
                                    <p class="fw-bold mb-0 fs-5" :class="data.flux_tresorerie.variation_totale >= 0 ? 'text-success' : 'text-danger'">
                                        {{ fmt(data.flux_tresorerie.variation_totale) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trésorerie -->
                <div class="col-12">
                    <div class="isup-card p-3">
                        <h6 class="fw-bold mb-3">Trésorerie</h6>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <div class="p-2 bg-light rounded">
                                    <small class="text-muted">Ouverture</small>
                                    <p class="fw-bold mb-0">{{ fmt(data.tresorerie.ouverture) }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-2 bg-light rounded">
                                    <small class="text-muted">Variation</small>
                                    <p class="fw-bold mb-0" :class="data.tresorerie.variation >= 0 ? 'text-success' : 'text-danger'">
                                        {{ fmt(data.tresorerie.variation) }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-2 rounded" style="background: var(--primary-light);">
                                    <small class="text-muted">Clôture</small>
                                    <p class="fw-bold mb-0 fs-5">{{ fmt(data.tresorerie.cloture) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </CompanyLayout>
</template>
