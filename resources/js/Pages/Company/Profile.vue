<script setup>
import { ref, onMounted } from 'vue';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';

const company = ref(null);
const loading = ref(true);
const saving = ref(false);
const error = ref(null);
const success = ref(null);

const clientId = window.__CLIENT_ID__;

const form = ref({
    company_name: '',
    email: '',
    phone: '',
    address: '',
});

const loadData = async () => {
    if (!clientId) { loading.value = false; return; }
    try {
        const res = await fetch(`/api/company/${clientId}/info`);
        if (!res.ok) throw new Error('Erreur serveur');
        const data = await res.json();
        company.value = data.company;
        form.value = {
            company_name: data.company.company_name || '',
            email: data.company.email || '',
            phone: data.company.phone || '',
            address: data.company.address || '',
        };
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const updateProfile = async () => {
    saving.value = true;
    success.value = null;
    error.value = null;
    try {
        const res = await fetch(`/api/company/${clientId}/update`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
            },
            body: JSON.stringify(form.value),
        });
        if (!res.ok) throw new Error('Erreur lors de la mise à jour');
        const data = await res.json();
        success.value = data.message || 'Informations mises à jour.';
        company.value = data.company;
    } catch (e) {
        error.value = e.message;
    } finally {
        saving.value = false;
    }
};

onMounted(loadData);
</script>

<template>
    <CompanyLayout page-title="Mon Profil">
        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
        </div>

        <div v-else class="row g-4">
            <!-- Company Info -->
            <div class="col-lg-7">
                <div class="card border-0 rounded-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4 font-heading">
                            <i class="bi-building me-2 text-primary"></i>Informations de l'entreprise
                        </h5>

                        <div v-if="success" class="alert alert-success rounded-3">{{ success }}</div>
                        <div v-if="error" class="alert alert-danger rounded-3">{{ error }}</div>

                        <form @submit.prevent="updateProfile">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Raison sociale</label>
                                <input type="text" class="form-control" v-model="form.company_name" required>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Email</label>
                                    <input type="email" class="form-control" v-model="form.email">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Téléphone</label>
                                    <input type="tel" class="form-control" v-model="form.phone">
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold small">Adresse</label>
                                <textarea class="form-control" rows="2" v-model="form.address"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary px-4" :disabled="saving">
                                <i class="bi-check2 me-2"></i>{{ saving ? 'Enregistrement...' : 'Enregistrer' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Side info -->
            <div class="col-lg-5">
                <div class="card border-0 rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3 font-heading">
                            <i class="bi-info-circle me-2 text-primary"></i>Détails
                        </h5>
                        <div class="small">
                            <div v-if="company?.legal_form" class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Forme juridique</span>
                                <span class="fw-semibold">{{ company.legal_form }}</span>
                            </div>
                            <div v-if="company?.ifu" class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">IFU</span>
                                <span class="fw-semibold">{{ company.ifu }}</span>
                            </div>
                            <div v-if="company?.rccm" class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">RCCM</span>
                                <span class="fw-semibold">{{ company.rccm }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Statut</span>
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill">
                                    {{ company?.status || 'Actif' }}
                                </span>
                            </div>
                            <div v-if="company?.contract_start" class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Début contrat</span>
                                <span class="fw-semibold">{{ new Date(company.contract_start).toLocaleDateString('fr-FR') }}</span>
                            </div>
                            <div v-if="company?.contract_end" class="d-flex justify-content-between py-2">
                                <span class="text-muted">Fin contrat</span>
                                <span class="fw-semibold">{{ new Date(company.contract_end).toLocaleDateString('fr-FR') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </CompanyLayout>
</template>
