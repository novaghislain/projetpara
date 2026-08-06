<script setup>
import { ref, onMounted } from 'vue';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';

const company = ref(null);
const stats = ref({ user_count: 0, active_user_count: 0 });
const loading = ref(true);
const clientId = window.__CLIENT_ID__;

onMounted(async () => {
    if (!clientId) { loading.value = false; return; }
    try {
        const res = await fetch(`/api/company/${clientId}/info`);
        const data = await res.json();
        company.value = data.company;
        stats.value = data.stats || { user_count: 0, active_user_count: 0 };
    } catch (e) {
        console.error('Erreur chargement entreprise', e);
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <CompanyLayout page-title="Vue d'ensemble">
        <div v-if="loading" class="d-flex justify-content-center align-items-center" style="height: 300px;">
            <div class="spinner-border text-primary" role="status"></div>
        </div>

        <div v-else>
            <!-- En-tête -->
            <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-light">
                <div class="bg-primary text-white rounded d-flex align-items-center justify-content-center me-3" style="width:50px; height:50px; font-size:24px;">
                    <i class="bi bi-building"></i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold text-dark">{{ company?.company_name || 'Mon Entreprise' }}</h3>
                    <div class="text-muted small">Espace Administrateur d'Entreprise</div>
                </div>
            </div>

            <!-- Stats -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card h-100 border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <h6 class="text-muted fw-bold mb-2 text-uppercase" style="font-size:11px;">Équipe & Utilisateurs</h6>
                            <h2 class="fw-bold text-dark mb-0">{{ stats.active_user_count }} <span class="fs-6 text-muted fw-normal">/ {{ stats.user_count }} actifs</span></h2>
                            <a href="/company/users" class="text-primary text-decoration-none small mt-2 d-inline-block fw-semibold">Gérer l'équipe <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100 border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <h6 class="text-muted fw-bold mb-2 text-uppercase" style="font-size:11px;">Demandes (B2C)</h6>
                            <h2 class="fw-bold text-dark mb-0">0 <span class="fs-6 text-muted fw-normal">en attente</span></h2>
                            <a href="/company/client-requests" class="text-primary text-decoration-none small mt-2 d-inline-block fw-semibold">Voir les demandes <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100 border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <h6 class="text-muted fw-bold mb-2 text-uppercase" style="font-size:11px;">Abonnement en cours</h6>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-success me-2">Actif</span>
                                <span class="text-dark fw-bold">Plan Standard</span>
                            </div>
                            <a href="/company/subscription" class="text-primary text-decoration-none small mt-2 d-inline-block fw-semibold">Gérer l'abonnement <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100 border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <h6 class="text-muted fw-bold mb-2 text-uppercase" style="font-size:11px;">Sécurité</h6>
                            <h2 class="fw-bold text-dark mb-0"><i class="bi bi-shield-check text-success"></i></h2>
                            <a href="/company/security" class="text-primary text-decoration-none small mt-2 d-inline-block fw-semibold">Paramètres de sécurité <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accès Rapides -->
            <h5 class="fw-bold text-dark mb-3">Accès Rapides</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <a href="/company/profile" class="text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm rounded-3 hover-lift">
                            <div class="card-body d-flex align-items-center">
                                <div class="bg-light rounded p-2 me-3 text-primary"><i class="bi bi-building fs-4"></i></div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Identité et Profil</h6>
                                    <div class="text-muted small">Modifier les informations de l'entreprise</div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="/company/roles" class="text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm rounded-3 hover-lift">
                            <div class="card-body d-flex align-items-center">
                                <div class="bg-light rounded p-2 me-3 text-primary"><i class="bi bi-shield-lock fs-4"></i></div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Rôles & Accès</h6>
                                    <div class="text-muted small">Gérer les permissions des collaborateurs</div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="/company/audit" class="text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm rounded-3 hover-lift">
                            <div class="card-body d-flex align-items-center">
                                <div class="bg-light rounded p-2 me-3 text-primary"><i class="bi bi-clock-history fs-4"></i></div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Historique d'Audit</h6>
                                    <div class="text-muted small">Consulter l'historique des actions</div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </CompanyLayout>
</template>

<style scoped>
.hover-lift {
    transition: transform 0.2s, box-shadow 0.2s;
}
.hover-lift:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.1) !important;
}
</style>
