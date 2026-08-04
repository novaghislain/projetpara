<script setup>
/*
 * Composant : Resultat.vue
 * Description : Affiche le compte de resultat (produits / charges / resultat net) d'un client.
 *              Les donnees sont chargees depuis l'API /api/accounting/reports/resultat/:clientId.
 *              Utilise le layout GelLayout et le store d'authentification.
 */
import { ref, onMounted } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';
import { authStore } from '../../../stores/auth';

// Proprietes du composant : identifiant du client (optionnel)
const props = defineProps({
    clientId: { type: [Number, String], default: null }
});

// Etat reactif : donnees du compte de resultat, chargement, erreur
const data = ref(null);
const loading = ref(true);
const error = ref(null);

// Chargement asynchrone du compte de resultat depuis l'API
const fetchResultat = async () => {
    loading.value = true;
    error.value = null;
    const cid = props.clientId || authStore.user?.client_id;
    if (!cid) { error.value = 'Aucun client sélectionné.'; loading.value = false; return; }
    try {
        const res = await fetch('/api/accounting/reports/resultat/' + cid);
        if (!res.ok) throw new Error('Erreur de chargement');
        data.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

// Somme de tous les montants de produits
const totalProduits = () => data.value?.produits?.reduce((s, i) => s + parseFloat(i.montant || 0), 0) || 0;
// Somme de tous les montants de charges
const totalCharges = () => data.value?.charges?.reduce((s, i) => s + parseFloat(i.montant || 0), 0) || 0;
// Calcul du resultat net (produits moins charges)
const resultatNet = () => totalProduits() - totalCharges();

// Chargement automatique au montage du composant
onMounted(fetchResultat);
</script>

<template>
    <GelLayout page-title="Compte de Résultat">
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <template v-else-if="data">
            <div class="bg-white rounded-lg shadow p-6 mb-3">
                <div class="card-header bg-white">
                    <h6 class="fw-bold mb-0"><i class="bi-bar-chart-line me-2 text-primary"></i>Compte de Résultat au {{ data.date ? $formatDate(data.date) : new Date().toLocaleDateString('fr-FR') }}</h6>
                </div>
            </div>

            <div class="row g-4">
                <!-- Produits -->
                <div class="col-md-6">
                    <div class="bg-white rounded-lg shadow p-6 h-100">
                        <div class="card-header bg-white">
                            <h6 class="fw-bold mb-0 text-success">PRODUITS</h6>
                        </div>
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

                <!-- Charges -->
                <div class="col-md-6">
                    <div class="bg-white rounded-lg shadow p-6 h-100">
                        <div class="card-header bg-white">
                            <h6 class="fw-bold mb-0 text-danger">CHARGES</h6>
                        </div>
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

            <div class="bg-white rounded-lg shadow p-6 mt-3">
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
        </template>
    </GelLayout>
</template>
