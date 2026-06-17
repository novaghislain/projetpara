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
        <!-- Loading -->
        <div v-if="loading" class="d-flex align-items-center justify-content-center gap-3 py-5">
            <div class="isup-spinner"></div>
            <span style="color:#888; font-size:14px;">Chargement…</span>
        </div>

        <template v-else>
            <div class="isup-shell">
                <!-- Header -->
                <div class="isup-portal-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="isup-portal-logo">
                            <i class="bi-person" style="font-size:20px;"></i>
                        </div>
                        <div>
                            <div class="isup-portal-company">Mon Profil</div>
                            <div class="isup-portal-sub">Informations de l'entreprise</div>
                        </div>
                    </div>
                </div>

                <div class="p-3">
                    <!-- Success / Error -->
                    <div v-if="success" class="isup-alert-success mb-3">
                        <i class="bi-check-circle-fill me-2"></i>{{ success }}
                    </div>
                    <div v-if="error" class="isup-alert-error mb-3">
                        <i class="bi-exclamation-triangle-fill me-2"></i>{{ error }}
                    </div>

                    <div class="row g-3">
                        <!-- Company Info Form -->
                        <div class="col-lg-7">
                            <div class="isup-panel">
                                <div class="isup-panel-header">
                                    <i class="bi-building me-2" style="color:#FF7900;"></i>Informations de l'entreprise
                                </div>
                                <div class="isup-panel-body">
                                    <form @submit.prevent="updateProfile">
                                        <div class="mb-3">
                                            <label class="isup-label">Raison sociale</label>
                                            <input type="text" class="isup-input" v-model="form.company_name" required>
                                        </div>
                                        <div class="row g-2 mb-3">
                                            <div class="col-md-6">
                                                <label class="isup-label">Email</label>
                                                <input type="email" class="isup-input" v-model="form.email">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="isup-label">Téléphone</label>
                                                <input type="tel" class="isup-input" v-model="form.phone">
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <label class="isup-label">Adresse</label>
                                            <textarea class="isup-input" rows="2" v-model="form.address"></textarea>
                                        </div>
                                        <button type="submit" class="isup-btn-primary" :disabled="saving">
                                            <span v-if="saving" class="isup-spinner-sm me-1"></span>
                                            <i v-else class="bi-check2 me-1"></i>
                                            {{ saving ? 'Enregistrement...' : 'Enregistrer' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Side info -->
                        <div class="col-lg-5">
                            <div class="isup-panel">
                                <div class="isup-panel-header">
                                    <i class="bi-info-circle me-2" style="color:#FF7900;"></i>Détails
                                </div>
                                <div class="isup-panel-body">
                                    <div v-if="company?.legal_form" class="isup-field-row">
                                        <span class="isup-field-label">Forme juridique</span>
                                        <span class="isup-field-val">{{ company.legal_form }}</span>
                                    </div>
                                    <div v-if="company?.ifu" class="isup-field-row">
                                        <span class="isup-field-label">IFU</span>
                                        <span class="isup-field-val">{{ company.ifu }}</span>
                                    </div>
                                    <div v-if="company?.rccm" class="isup-field-row">
                                        <span class="isup-field-label">RCCM</span>
                                        <span class="isup-field-val">{{ company.rccm }}</span>
                                    </div>
                                    <div class="isup-field-row">
                                        <span class="isup-field-label">Statut</span>
                                        <span class="isup-field-val">
                                            <span class="isup-status isup-status-green">{{ company?.status || 'Actif' }}</span>
                                        </span>
                                    </div>
                                    <div v-if="company?.contract_start" class="isup-field-row">
                                        <span class="isup-field-label">Début contrat</span>
                                        <span class="isup-field-val">{{ new Date(company.contract_start).toLocaleDateString('fr-FR') }}</span>
                                    </div>
                                    <div v-if="company?.contract_end" class="isup-field-row">
                                        <span class="isup-field-label">Fin contrat</span>
                                        <span class="isup-field-val">{{ new Date(company.contract_end).toLocaleDateString('fr-FR') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </CompanyLayout>
</template>

