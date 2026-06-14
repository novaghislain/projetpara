<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';
import { authStore } from '../../../stores/auth';

const props = defineProps({
    clientId: { type: [Number, String], required: true }
});

const data = ref(null);
const loading = ref(true);
const error = ref(null);

const fetchBilan = async () => {
    loading.value = true;
    error.value = null;
    try {
        const res = await fetch('/api/accounting/reports/bilan/' + props.clientId);
        if (!res.ok) throw new Error('Erreur de chargement');
        data.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const totalActif = () => data.value?.actif?.reduce((s, i) => s + parseFloat(i.montant || 0), 0) || 0;
const totalPassif = () => data.value?.passif?.reduce((s, i) => s + parseFloat(i.montant || 0), 0) || 0;

onMounted(fetchBilan);
</script>

<template>
    <GelLayout page-title="Bilan Comptable">
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <template v-else-if="data">
            <div class="card card-dashboard mb-3">
                <div class="card-header bg-white">
                    <h6 class="fw-bold mb-0"><i class="bi-file-spreadsheet me-2 text-primary"></i>Bilan au {{ data.date ? $formatDate(data.date) : new Date().toLocaleDateString('fr-FR') }}</h6>
                </div>
            </div>

            <div class="row g-4">
                <!-- Actif -->
                <div class="col-md-6">
                    <div class="card card-dashboard h-100">
                        <div class="card-header bg-white">
                            <h6 class="fw-bold mb-0 text-primary">ACTIF</h6>
                        </div>
                        <div class="card-body p-0">
                            <div v-if="!data.actif?.length" class="text-muted small py-4 text-center">Aucun actif.</div>
                            <table v-else class="table table-sm mb-0">
                                <thead class="small text-muted">
                                    <tr><th>Rubrique</th><th class="text-end">Montant</th></tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in data.actif" :key="item.code || item.name">
                                        <td class="small">{{ item.code ? item.code + ' - ' : '' }}{{ item.name }}</td>
                                        <td class="text-end fw-medium">{{ $formatCurrency(item.montant) }}</td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr><th class="fw-bold">Total Actif</th><th class="text-end fw-bold">{{ $formatCurrency(totalActif()) }}</th></tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Passif -->
                <div class="col-md-6">
                    <div class="card card-dashboard h-100">
                        <div class="card-header bg-white">
                            <h6 class="fw-bold mb-0 text-success">PASSIF</h6>
                        </div>
                        <div class="card-body p-0">
                            <div v-if="!data.passif?.length" class="text-muted small py-4 text-center">Aucun passif.</div>
                            <table v-else class="table table-sm mb-0">
                                <thead class="small text-muted">
                                    <tr><th>Rubrique</th><th class="text-end">Montant</th></tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in data.passif" :key="item.code || item.name">
                                        <td class="small">{{ item.code ? item.code + ' - ' : '' }}{{ item.name }}</td>
                                        <td class="text-end fw-medium">{{ $formatCurrency(item.montant) }}</td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr><th class="fw-bold">Total Passif</th><th class="text-end fw-bold">{{ $formatCurrency(totalPassif()) }}</th></tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-dashboard mt-3">
                <div class="card-body text-center">
                    <span class="fw-medium">Résultat net : </span>
                    <span :class="data.resultat && data.resultat >= 0 ? 'text-success' : 'text-danger'" class="fw-bold">
                        {{ $formatCurrency(data.resultat || 0) }}
                    </span>
                </div>
            </div>
        </template>
    </GelLayout>
</template>
