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

const fetchResultat = async () => {
    loading.value = true;
    error.value = null;
    try {
        const res = await fetch('/api/accounting/reports/resultat/' + props.clientId);
        if (!res.ok) throw new Error('Erreur de chargement');
        data.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const totalProduits = () => data.value?.produits?.reduce((s, i) => s + parseFloat(i.montant || 0), 0) || 0;
const totalCharges = () => data.value?.charges?.reduce((s, i) => s + parseFloat(i.montant || 0), 0) || 0;
const resultatNet = () => totalProduits() - totalCharges();

onMounted(fetchResultat);
</script>

<template>
    <GelLayout page-title="Compte de Résultat">
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <template v-else-if="data">
            <div class="card card-dashboard mb-3">
                <div class="card-header bg-white">
                    <h6 class="fw-bold mb-0"><i class="bi-bar-chart-line me-2 text-primary"></i>Compte de Résultat au {{ data.date ? $formatDate(data.date) : new Date().toLocaleDateString('fr-FR') }}</h6>
                </div>
            </div>

            <div class="row g-4">
                <!-- Produits -->
                <div class="col-md-6">
                    <div class="card card-dashboard h-100">
                        <div class="card-header bg-white">
                            <h6 class="fw-bold mb-0 text-success">PRODUITS</h6>
                        </div>
                        <div class="card-body p-0">
                            <div v-if="!data.produits?.length" class="text-muted small py-4 text-center">Aucun produit.</div>
                            <table v-else class="table table-sm mb-0">
                                <thead class="small text-muted">
                                    <tr><th>Rubrique</th><th class="text-end">Montant</th></tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in data.produits" :key="item.code || item.name">
                                        <td class="small">{{ item.code ? item.code + ' - ' : '' }}{{ item.name }}</td>
                                        <td class="text-end fw-medium">{{ $formatCurrency(item.montant) }}</td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr><th class="fw-bold">Total Produits</th><th class="text-end fw-bold">{{ $formatCurrency(totalProduits()) }}</th></tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Charges -->
                <div class="col-md-6">
                    <div class="card card-dashboard h-100">
                        <div class="card-header bg-white">
                            <h6 class="fw-bold mb-0 text-danger">CHARGES</h6>
                        </div>
                        <div class="card-body p-0">
                            <div v-if="!data.charges?.length" class="text-muted small py-4 text-center">Aucune charge.</div>
                            <table v-else class="table table-sm mb-0">
                                <thead class="small text-muted">
                                    <tr><th>Rubrique</th><th class="text-end">Montant</th></tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in data.charges" :key="item.code || item.name">
                                        <td class="small">{{ item.code ? item.code + ' - ' : '' }}{{ item.name }}</td>
                                        <td class="text-end fw-medium">{{ $formatCurrency(item.montant) }}</td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr><th class="fw-bold">Total Charges</th><th class="text-end fw-bold">{{ $formatCurrency(totalCharges()) }}</th></tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-dashboard mt-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-4 text-end">
                            <span class="fw-medium">Total Produits</span><br>
                            <span class="fw-bold text-success">{{ $formatCurrency(totalProduits()) }}</span>
                        </div>
                        <div class="col-4 text-center">
                            <span class="fw-medium">Résultat Net</span><br>
                            <span :class="resultatNet() >= 0 ? 'text-success' : 'text-danger'" class="fw-bold fs-5">
                                {{ $formatCurrency(resultatNet()) }}
                            </span>
                        </div>
                        <div class="col-4 text-start">
                            <span class="fw-medium">Total Charges</span><br>
                            <span class="fw-bold text-danger">{{ $formatCurrency(totalCharges()) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </GelLayout>
</template>
