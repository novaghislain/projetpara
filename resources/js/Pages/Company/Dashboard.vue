<script setup>
import { ref, computed, onMounted } from 'vue';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';

const company = ref(null);
const licenses = ref([]);
const stats = ref({ user_count: 0, active_user_count: 0 });
const loading = ref(true);
const error = ref(null);

const clientId = window.__CLIENT_ID__;

const activeLicenses = computed(() => licenses.value.filter(l => l.valid));
const expiredLicenses = computed(() => licenses.value.filter(l => !l.valid));

const loadData = async () => {
    if (!clientId) { loading.value = false; return; }
    try {
        const res = await fetch(`/api/company/${clientId}/info`);
        if (!res.ok) throw new Error('Erreur serveur');
        const data = await res.json();
        company.value = data.company;
        licenses.value = data.licenses;
        stats.value = data.stats || { user_count: 0, active_user_count: 0 };
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

onMounted(loadData);
</script>

<template>
    <CompanyLayout page-title="Tableau de bord">
        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
        </div>

        <div v-else-if="error" class="alert alert-danger rounded-3">
            <i class="bi-exclamation-triangle me-2"></i>{{ error }}
        </div>

        <template v-else>
            <!-- Welcome Banner -->
            <div class="card border-0 rounded-4 mb-4 overflow-hidden">
                <div class="card-body p-4 p-md-5"
                     style="background: linear-gradient(135deg, #0d1b2a 0%, #1a237e 100%); color: white;">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="fw-bold font-heading mb-2">
                                Bienvenue, {{ company?.company_name || 'Espace Client' }}
                            </h2>
                            <p class="mb-0 opacity-75">
                                Gérez vos services, consultez vos licences et suivez vos prestations.
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <i class="bi-building" style="font-size: 4rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card card-dashboard h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon" style="background: #e8f5e9; color: #2e7d32;">
                                    <i class="bi-check-circle"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0 fw-bold">{{ activeLicenses.length }}</h3>
                                    <span class="text-muted small">Services actifs</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-dashboard h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon" style="background: #fff3e0; color: #e65100;">
                                    <i class="bi-key"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0 fw-bold">{{ licenses.length }}</h3>
                                    <span class="text-muted small">Licences totales</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-dashboard h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon" style="background: #fce4ec; color: #c62828;">
                                    <i class="bi-people"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0 fw-bold">{{ stats.active_user_count }} <small class="fs-6 text-muted">/ {{ stats.user_count }}</small></h3>
                                    <span class="text-muted small">Utilisateurs (actifs/total)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-dashboard h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon" style="background: #e3f2fd; color: #1565c0;">
                                    <i class="bi-calendar-check"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0 fw-bold">{{ company?.contract_end ? new Date(company.contract_end).toLocaleDateString('fr-FR') : 'N/A' }}</h3>
                                    <span class="text-muted small">Fin de contrat</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Licensed Services Detail -->
            <div class="card border-0 rounded-4">
                <div class="card-header bg-white border-0 rounded-4 pt-4 px-4">
                    <h5 class="fw-bold mb-0 font-heading">
                        <i class="bi-grid-3x3-gap me-2 text-primary"></i>
                        Mes Services
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div v-if="!licenses.length" class="text-center py-4 text-muted">
                        <i class="bi-inbox" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">Aucun service sous licence pour le moment.</p>
                    </div>
                    <div v-else class="row g-3">
                        <div v-for="lic in licenses" :key="lic.id" class="col-md-6">
                            <div class="d-flex align-items-start gap-3 p-3 rounded-3 border"
                                 :class="lic.valid ? 'border-success border-opacity-25' : 'border-warning border-opacity-25'">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                     :class="lic.valid ? 'bg-success bg-opacity-10' : 'bg-warning bg-opacity-10'"
                                     style="width: 48px; height: 48px;">
                                    <i :class="lic.valid ? 'bi-check-circle text-success' : 'bi-exclamation-triangle text-warning'"
                                       style="font-size: 24px;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="fw-bold mb-1">{{ lic.service_name }}</h6>
                                        <span class="badge rounded-pill"
                                              :class="lic.valid ? 'bg-success bg-opacity-10 text-success' : 'bg-warning bg-opacity-10 text-warning'">
                                            {{ lic.valid ? 'Actif' : 'Expiré' }}
                                        </span>
                                    </div>
                                    <div class="small text-muted">
                                        <span v-if="lic.license_key" class="d-block">
                                            <i class="bi-key me-1"></i>Licence : <code class="text-dark">{{ lic.license_key }}</code>
                                        </span>
                                        <span class="d-block">
                                            <i class="bi-calendar me-1"></i>
                                            Du {{ lic.start_date }} au {{ lic.end_date }} ({{ lic.duration_months }} mois)
                                        </span>
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
