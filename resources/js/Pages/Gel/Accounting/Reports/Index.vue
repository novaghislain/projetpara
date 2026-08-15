<template>
    <GelLayout pageTitle="États Financiers & Rapports">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-1 text-gray-800">États Financiers</h2>
                    <p class="text-muted mb-0">Balance, Grand Livre, Bilan et Compte de Résultat</p>
                </div>
            </div>

            <div class="row g-4">
                <!-- Balance -->
                <div class="col-md-6 col-lg-3">
                    <div class="card shadow-sm border-0 h-100 text-center hover-card">
                        <div class="card-body p-4">
                            <div class="icon-circle bg-primary text-white mb-3 mx-auto">
                                <i class="bi bi-bar-chart-steps fs-3"></i>
                            </div>
                            <h5 class="fw-bold">Balance Générale</h5>
                            <p class="text-muted small">Aperçu global de l'équilibre des comptes (Débit/Crédit) sur une période donnée.</p>
                            <button class="btn btn-outline-primary btn-sm w-100 mt-2">Générer</button>
                        </div>
                    </div>
                </div>

                <!-- Grand Livre -->
                <div class="col-md-6 col-lg-3">
                    <div class="card shadow-sm border-0 h-100 text-center hover-card">
                        <div class="card-body p-4">
                            <div class="icon-circle bg-success text-white mb-3 mx-auto">
                                <i class="bi bi-journal-text fs-3"></i>
                            </div>
                            <h5 class="fw-bold">Grand Livre</h5>
                            <p class="text-muted small">Détail chronologique des écritures compte par compte avec solde progressif.</p>
                            <button class="btn btn-outline-success btn-sm w-100 mt-2">Générer</button>
                        </div>
                    </div>
                </div>

                <!-- Compte de résultat -->
                <div class="col-md-6 col-lg-3">
                    <div class="card shadow-sm border-0 h-100 text-center hover-card">
                        <div class="card-body p-4">
                            <div class="icon-circle bg-warning text-dark mb-3 mx-auto">
                                <i class="bi bi-graph-up-arrow fs-3"></i>
                            </div>
                            <h5 class="fw-bold">Compte de Résultat</h5>
                            <p class="text-muted small">Analyse de la performance de l'entreprise (Produits - Charges).</p>
                            <button class="btn btn-outline-warning btn-sm w-100 mt-2">Générer</button>
                        </div>
                    </div>
                </div>

                <!-- Bilan -->
                <div class="col-md-6 col-lg-3">
                    <div class="card shadow-sm border-0 h-100 text-center hover-card">
                        <div class="card-body p-4">
                            <div class="icon-circle bg-info text-white mb-3 mx-auto">
                                <i class="bi bi-bank fs-3"></i>
                            </div>
                            <h5 class="fw-bold">Bilan Comptable</h5>
                            <p class="text-muted small">Photographie du patrimoine de l'entreprise (Actif / Passif) à une date précise.</p>
                            <button class="btn btn-outline-info btn-sm w-100 mt-2">Générer</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Zone des critères de filtrage (simulée) -->
            <div class="card shadow-sm border-0 mt-5">
                <div class="card-header bg-white border-bottom p-3">
                    <h6 class="m-0 fw-bold">Critères d'édition</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small">Période du</label>
                            <input type="date" class="form-control form-control-sm" value="2026-01-01">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Au</label>
                            <input type="date" class="form-control form-control-sm" value="2026-12-31">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Comptes</label>
                            <select class="form-select form-select-sm">
                                <option>Tous les comptes</option>
                                <option>Clients (41)</option>
                                <option>Fournisseurs (40)</option>
                                <option>Trésorerie (5)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Options</label>
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" id="inclureBrouillard" checked>
                                <label class="form-check-label small" for="inclureBrouillard">Inclure écritures non lettrées</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import GelLayout from '../../../../Layouts/GelLayout.vue';
</script>

<style scoped>
.hover-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
}
.icon-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
