<template>
    <GelLayout pageTitle="Agent Cashflow & Finance">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-1 text-gray-800">
                        <i class="bi bi-cash-stack text-danger me-2"></i>Agent Cashflow & Finance
                    </h2>
                    <p class="text-muted mb-0">Prévisions de trésorerie et alertes financières intelligentes</p>
                </div>
                <div>
                    <button class="btn btn-outline-primary me-2" @click="runFraudDetection" :disabled="loading.fraud">
                        <span v-if="loading.fraud" class="spinner-border spinner-border-sm me-1"></span>
                        <i v-else class="bi bi-shield-check me-1"></i> Scanner les fraudes
                    </button>
                    <button class="btn btn-primary" @click="fetchCashflow" :disabled="loading.cashflow">
                        <span v-if="loading.cashflow" class="spinner-border spinner-border-sm me-1"></span>
                        <i v-else class="bi bi-arrow-clockwise me-1"></i> Rafraîchir les prévisions
                    </button>
                </div>
            </div>
            
            <div class="row">
                <!-- Forecast Chart Area -->
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white border-0 pt-4 pb-0">
                            <h6 class="m-0 font-weight-bold text-primary">Prévisions de Trésorerie (30 jours)</h6>
                        </div>
                        <div class="card-body">
                            <div v-if="loading.cashflow" class="text-center py-5 text-muted">
                                <div class="spinner-border text-primary mb-3" role="status"></div>
                                <p>L'IA analyse vos historiques d'encaissements et décaissements...</p>
                            </div>
                            <div v-else-if="cashflowData && cashflowData.predictions.length > 0">
                                <div class="alert alert-info border-0 d-flex align-items-center">
                                    <i class="bi bi-robot fs-4 me-3 text-info"></i>
                                    <div>
                                        <strong>Analyse IA : </strong> {{ cashflowData.resume }}
                                    </div>
                                </div>
                                <div class="table-responsive mt-4">
                                    <table class="table table-sm table-hover table-dense tabular-nums">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Entrées Prévues</th>
                                                <th>Sorties Prévues</th>
                                                <th class="text-end">Solde Estimé</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="p in cashflowData.predictions.slice(0, 15)" :key="p.date" :class="{'table-danger': p.solde_prevu < 0, 'table-warning': p.alerte === 'faible'}">
                                                <td>{{ p.jour }} {{ p.date }}</td>
                                                <td class="text-success">+{{ formatCurrency(p.entree_prevue) }}</td>
                                                <td class="text-danger">-{{ formatCurrency(p.sortie_prevue) }}</td>
                                                <td class="text-end fw-bold" :class="{'text-danger': p.solde_prevu < 0}">{{ formatCurrency(p.solde_prevu) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="text-center mt-2">
                                        <small class="text-muted">Affiche les 15 premiers jours. Le modèle se base sur l'historique des 6 derniers mois.</small>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-5 text-muted">
                                <i class="bi bi-graph-up text-gray-300 fs-1 mb-3 d-block"></i>
                                <p>Aucune donnée de trésorerie disponible pour générer des prévisions.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats & Alerts Area -->
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 mb-4 bg-primary text-white">
                        <div class="card-body">
                            <h6 class="text-uppercase text-white-50 mb-2 font-weight-bold">Solde Actuel</h6>
                            <h2 class="mb-0">{{ cashflowData ? formatCurrency(cashflowData.solde_actuel) : '---' }}</h2>
                        </div>
                    </div>
                    
                    <div class="card shadow-sm border-0 mb-4 border-left-warning">
                        <div class="card-body">
                            <h5 class="card-title text-warning"><i class="bi bi-exclamation-triangle me-2"></i>Alertes Détectées</h5>
                            <div v-if="fraudAlerts.length > 0">
                                <div v-for="(alert, i) in fraudAlerts" :key="i" class="alert alert-warning border-0 small mb-2 p-2">
                                    <strong>{{ alert.title }}</strong><br/>
                                    {{ alert.message }}
                                </div>
                            </div>
                            <div v-else class="text-muted small mt-3">
                                <p v-if="loading.fraud">Scan en cours...</p>
                                <p v-else><i class="bi bi-check-circle text-success me-1"></i> Aucune anomalie comptable ou fraude détectée sur cet exercice.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';

const cashflowData = ref(null);
const fraudAlerts = ref([]);
const loading = ref({
    cashflow: false,
    fraud: false
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF', maximumFractionDigits: 0 }).format(value || 0).replace('XOF', 'FCFA');
};

const fetchCashflow = async () => {
    loading.value.cashflow = true;
    try {
        const response = await window.axios.get('/api/ai/finance/cashflow-forecast', { params: { days: 30 } });
        if (response.data.success) {
            cashflowData.value = response.data.forecast;
        }
    } catch (error) {
        console.error("Erreur cashflow", error);
    } finally {
        loading.value.cashflow = false;
    }
};

const runFraudDetection = async () => {
    loading.value.fraud = true;
    try {
        const response = await window.axios.get('/api/ai/finance/fraud-detection');
        if (response.data.success) {
            fraudAlerts.value = response.data.alerts;
            alert(`${response.data.count} alerte(s) détectée(s) et envoyée(s) dans le Flux IA.`);
        }
    } catch (error) {
        console.error("Erreur fraude", error);
    } finally {
        loading.value.fraud = false;
    }
};

onMounted(() => {
    fetchCashflow();
});
</script>

<style scoped>
.table-dense th, .table-dense td {
    padding: 0.4rem 0.5rem;
    font-size: 0.85rem;
}
.tabular-nums {
    font-variant-numeric: tabular-nums;
}
.border-left-warning {
    border-left: 4px solid #f6c23e !important;
}
</style>
