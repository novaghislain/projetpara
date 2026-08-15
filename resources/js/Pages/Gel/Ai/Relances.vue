<template>
    <GelLayout pageTitle="Agent Relance & CRM">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-1 text-gray-800">
                        <i class="bi bi-bell text-warning me-2"></i>Agent Relance & CRM
                    </h2>
                    <p class="text-muted mb-0">Analyse comportementale, prédiction de churn et relances intelligentes</p>
                </div>
                <div>
                    <button class="btn btn-outline-primary me-2" @click="fetchSegmentation" :disabled="loading.segmentation">
                        <span v-if="loading.segmentation" class="spinner-border spinner-border-sm me-1"></span>
                        <i v-else class="bi bi-pie-chart me-1"></i> Segmentation
                    </button>
                    <button class="btn btn-primary" @click="fetchRelances" :disabled="loading.relances">
                        <span v-if="loading.relances" class="spinner-border spinner-border-sm me-1"></span>
                        <i v-else class="bi bi-arrow-clockwise me-1"></i> Générer Relances
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white border-0 pt-4 pb-0">
                            <h6 class="m-0 font-weight-bold text-primary">Recommandations de Relance</h6>
                        </div>
                        <div class="card-body">
                            <div v-if="loading.relances" class="text-center py-5 text-muted">
                                <div class="spinner-border text-primary mb-3" role="status"></div>
                                <p>L'IA analyse le comportement de paiement de vos clients...</p>
                            </div>
                            <div v-else-if="recommendations.length > 0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-dense">
                                        <thead>
                                            <tr>
                                                <th>Client</th>
                                                <th>Priorité</th>
                                                <th>Action Suggérée</th>
                                                <th>Canal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(rec, idx) in recommendations.slice(0, 20)" :key="idx">
                                                <td class="fw-bold">{{ rec.company_name }}</td>
                                                <td>
                                                    <span class="badge" :class="{
                                                        'bg-danger': rec.action.priority === 'haute',
                                                        'bg-warning text-dark': rec.action.priority === 'moyenne',
                                                        'bg-info text-dark': rec.action.priority === 'basse'
                                                    }">
                                                        {{ rec.action.priority }}
                                                    </span>
                                                </td>
                                                <td>{{ rec.action.reason }}</td>
                                                <td>
                                                    <span class="badge bg-light text-dark border">
                                                        <i class="bi bi-envelope me-1" v-if="rec.action.channel === 'email'"></i>
                                                        <i class="bi bi-telephone me-1" v-else-if="rec.action.channel === 'appel'"></i>
                                                        {{ rec.action.channel }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div v-else class="text-center py-5 text-muted">
                                <i class="bi bi-inbox text-gray-300 fs-1 mb-3 d-block"></i>
                                <p>Aucune relance urgente à effectuer. Vos clients sont à jour !</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 mb-4 border-left-danger">
                        <div class="card-body">
                            <h5 class="card-title text-danger"><i class="bi bi-heartbreak me-2"></i>Risque de Churn</h5>
                            <div v-if="loading.churn" class="text-center py-3">
                                <div class="spinner-border spinner-border-sm text-danger"></div>
                            </div>
                            <div v-else-if="churnClients.length > 0">
                                <p class="text-muted small mb-3">Ces clients montrent des signes de décrochage (baisse d'activité, retards récurrents).</p>
                                <div v-for="(client, i) in churnClients.slice(0, 5)" :key="i" class="mb-3 border-bottom pb-2">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ client.company_name }}</strong>
                                        <span class="badge bg-danger">{{ client.risk_score }}/100</span>
                                    </div>
                                    <div class="small text-muted mt-1">{{ client.recommendation }}</div>
                                </div>
                            </div>
                            <div v-else class="text-muted small mt-3">
                                <p>Aucun risque de churn critique détecté.</p>
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

const recommendations = ref([]);
const churnClients = ref([]);
const loading = ref({
    relances: false,
    churn: false,
    segmentation: false
});

const fetchRelances = async () => {
    loading.value.relances = true;
    try {
        const response = await window.axios.get('/api/ai/crm/relances');
        if (response.data.success) {
            recommendations.value = response.data.recommendations;
        }
    } catch (error) {
        console.error("Erreur relances", error);
    } finally {
        loading.value.relances = false;
    }
};

const fetchChurn = async () => {
    loading.value.churn = true;
    try {
        const response = await window.axios.get('/api/ai/crm/churn');
        if (response.data.success) {
            churnClients.value = response.data.at_risk_clients;
        }
    } catch (error) {
        console.error("Erreur churn", error);
    } finally {
        loading.value.churn = false;
    }
};

const fetchSegmentation = async () => {
    loading.value.segmentation = true;
    try {
        const response = await window.axios.get('/api/ai/crm/segmentation');
        if (response.data.success) {
            console.log(response.data.segments);
            alert("Segmentation effectuée. Données disponibles dans la console.");
        }
    } catch (error) {
        console.error("Erreur segmentation", error);
    } finally {
        loading.value.segmentation = false;
    }
};

onMounted(() => {
    fetchRelances();
    fetchChurn();
});
</script>

<style scoped>
.table-dense th, .table-dense td {
    padding: 0.5rem;
    font-size: 0.85rem;
}
.border-left-danger {
    border-left: 4px solid #e74a3b !important;
}
</style>
