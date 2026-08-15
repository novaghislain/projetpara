<template>
    <GelLayout pageTitle="Agent OCR & GED Intelligente">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-1 text-gray-800">
                        <i class="bi bi-scanner text-success me-2"></i>Agent OCR & GED Intelligente
                    </h2>
                    <p class="text-muted mb-0">Zéro saisie manuelle : Extraction automatique des données comptables</p>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white border-0 pt-4 pb-0">
                            <h6 class="m-0 font-weight-bold text-primary">Soumettre une facture</h6>
                        </div>
                        <div class="card-body">
                            <form @submit.prevent="submitOcr">
                                <div class="mb-3">
                                    <label class="form-label">Client Associé</label>
                                    <select class="form-select" v-model="form.client_id" required>
                                        <option value="" disabled>Sélectionnez un client</option>
                                        <option value="1">Entreprise Demo SARL</option>
                                        <option value="2">Client Test Inc</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Document (PDF, Image)</label>
                                    <div class="border border-dashed p-5 text-center bg-light rounded" @click="$refs.fileInput.click()" style="cursor: pointer;">
                                        <i class="bi bi-cloud-arrow-up fs-1 text-muted mb-2 d-block"></i>
                                        <span v-if="!form.file">Cliquez ou glissez le document ici</span>
                                        <span v-else class="text-success fw-bold"><i class="bi bi-file-earmark-check"></i> {{ form.file.name }}</span>
                                        <input type="file" ref="fileInput" class="d-none" @change="handleFileChange" accept=".pdf,.png,.jpg,.jpeg">
                                    </div>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-success" :disabled="loading || !form.file || !form.client_id">
                                        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                        <i v-else class="bi bi-magic me-2"></i> Lancer l'analyse intelligente
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white border-0 pt-4 pb-0">
                            <h6 class="m-0 font-weight-bold text-primary">Dernières extractions</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item p-3" v-for="i in 3" :key="i">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold text-dark">Facture_Fournisseur_00{{i}}.pdf</span>
                                        <span class="badge bg-success">Terminé</span>
                                    </div>
                                    <div class="small text-muted mb-2">Traité le 15/08/2026 à 10:{{15 + i}}</div>
                                    <div class="bg-light p-2 rounded small font-monospace">
                                        Fournisseur: OLA Energy<br/>
                                        Montant TTC: {{ 15000 * i }} FCFA<br/>
                                        TVA: {{ 2700 * i }} FCFA
                                    </div>
                                </div>
                            </div>
                            <div class="p-3 text-center">
                                <a href="/ocr" class="btn btn-sm btn-outline-secondary">Voir tout l'historique</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import { ref } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';

const form = ref({
    client_id: '1',
    file: null
});

const fileInput = ref(null);
const loading = ref(false);

const handleFileChange = (e) => {
    if (e.target.files.length > 0) {
        form.value.file = e.target.files[0];
    }
};

const submitOcr = async () => {
    loading.value = true;
    
    // Simulation du temps de traitement OCR et envoi au flux
    setTimeout(() => {
        loading.value = false;
        alert("L'analyse OCR a été effectuée avec succès.\nLes données extraites ont été poussées vers le Flux IA pour approbation.");
        form.value.file = null;
    }, 2000);
};
</script>

<style scoped>
.border-dashed {
    border-style: dashed !important;
    border-width: 2px !important;
}
</style>
