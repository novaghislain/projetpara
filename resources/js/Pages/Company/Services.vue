<script setup>
import { ref, computed, onMounted } from 'vue';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';

const company = ref(null);
const licenses = ref([]);
const loading = ref(true);
const error = ref(null);

const clientId = window.__CLIENT_ID__;

const loadData = async () => {
    if (!clientId) { loading.value = false; return; }
    try {
        const res = await fetch(`/api/company/${clientId}/info`);
        if (!res.ok) throw new Error('Erreur serveur');
        const data = await res.json();
        company.value = data.company;
        licenses.value = data.licenses;
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

onMounted(loadData);
</script>

<template>
    <CompanyLayout page-title="Mes Services">
        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
        </div>

        <div v-else-if="error" class="alert alert-danger rounded-3">
            <i class="bi-exclamation-triangle me-2"></i>{{ error }}
        </div>

        <template v-else>
            <!-- En-tête -->
            <div class="card border-0 rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon" style="background: #e8eaf6; color: #1a237e;">
                            <i class="bi-grid-3x3-gap"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-1 font-heading">Services sous licence</h4>
                            <p class="text-muted mb-0 small">
                                {{ company?.company_name }} — {{ licenses.length }} service{{ licenses.length > 1 ? 's' : '' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grille des services -->
            <div v-if="!licenses.length" class="card border-0 rounded-4">
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi-inbox" style="font-size: 3rem;"></i>
                    <p class="mt-3 fs-5">Aucun service pour le moment.</p>
                    <p class="small">Contactez votre conseiller pour souscrire à des services.</p>
                </div>
            </div>

            <div v-else class="row g-4">
                <div v-for="lic in licenses" :key="lic.id" class="col-lg-6">
                    <div class="card border-0 rounded-4 h-100" style="box-shadow: 0 2px 12px rgba(0,0,0,0.06);">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                                         style="width: 52px; height: 52px; background: #e8eaf6; color: #1a237e; font-size: 24px;">
                                        <i class="bi-gear"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-1">{{ lic.service_name }}</h5>
                                        <span class="badge rounded-pill"
                                              :class="lic.valid ? 'bg-success bg-opacity-10 text-success' : 'bg-warning bg-opacity-10 text-warning'">
                                            <i :class="lic.valid ? 'bi-check-circle' : 'bi-exclamation-circle'" class="me-1"></i>
                                            {{ lic.valid ? 'Actif' : 'Expiré' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-light rounded-3 p-3 mb-3">
                                <div class="row g-2 small">
                                    <div class="col-6">
                                        <span class="text-muted">Licence</span>
                                        <code class="d-block text-dark fw-semibold">{{ lic.license_key }}</code>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-muted">Validité</span>
                                        <span class="d-block fw-semibold">{{ lic.duration_months }} mois</span>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-muted">Début</span>
                                        <span class="d-block fw-semibold">{{ lic.start_date }}</span>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-muted">Fin</span>
                                        <span class="d-block fw-semibold">{{ lic.end_date }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Barre de progression -->
                            <div v-if="lic.valid" class="small">
                                <div class="d-flex justify-content-between text-muted mb-1">
                                    <span>Validité</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: 75%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </CompanyLayout>
</template>
