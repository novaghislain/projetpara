<template>
    <GelLayout pageTitle="GED & Validations">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-1 text-gray-800">Gestion Documentaire & Validations</h2>
                    <p class="text-muted mb-0">Centralisez vos documents et gérez vos circuits d'approbation</p>
                </div>
                <div>
                    <button class="btn btn-light border me-2"><i class="bi bi-upload me-1"></i> Uploader</button>
                    <button class="btn btn-primary"><i class="bi bi-diagram-3 me-1"></i> Nouveau Workflow</button>
                </div>
            </div>

            <!-- KPI GED & Workflows -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 border-start border-4 border-primary h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">Documents Stockés</div>
                            <div class="h5 mb-0 fw-bold text-gray-800 tabular-nums">4,815</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 border-start border-4 border-warning h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">En attente de validation</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">12 Documents</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 border-start border-4 border-success h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">Validés (Mois)</div>
                            <div class="h5 mb-0 fw-bold text-gray-800 tabular-nums">145</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 border-start border-4 border-danger h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs fw-bold text-danger text-uppercase mb-1">Rejetés / À corriger</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">3</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Colonne de gauche: Arborescence GED -->
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-bottom p-3">
                            <h6 class="m-0 fw-bold text-gray-800"><i class="bi bi-folder2-open text-primary me-2"></i> Dossiers GED</h6>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center bg-light cursor-pointer border-start border-4 border-primary">
                                    <span><i class="bi bi-folder-fill text-warning me-2"></i> 01_Comptabilité</span>
                                    <span class="badge bg-secondary rounded-pill">145</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center cursor-pointer ps-4">
                                    <span><i class="bi bi-folder text-warning me-2"></i> Factures Fournisseurs</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center cursor-pointer ps-4">
                                    <span><i class="bi bi-folder text-warning me-2"></i> Relevés Bancaires</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center cursor-pointer">
                                    <span><i class="bi bi-folder-fill text-warning me-2"></i> 02_Ressources Humaines</span>
                                    <span class="badge bg-secondary rounded-pill">42</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center cursor-pointer">
                                    <span><i class="bi bi-folder-fill text-warning me-2"></i> 03_Juridique</span>
                                    <span class="badge bg-secondary rounded-pill">18</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center cursor-pointer text-muted">
                                    <span><i class="bi bi-archive text-muted me-2"></i> Archives 2025</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Colonne de droite: Circuits de Validation -->
                <div class="col-md-8 mb-4">
                    <!-- Onglets -->
                    <ul class="nav nav-tabs mb-3">
                        <li class="nav-item">
                            <a class="nav-link" :class="{ active: currentTab === 'documents' }" @click="currentTab = 'documents'" href="#">Documents Récents</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" :class="{ active: currentTab === 'workflows' }" @click="currentTab = 'workflows'" href="#">À Valider <span class="badge bg-danger ms-1">12</span></a>
                        </li>
                    </ul>

                    <!-- Liste des Documents -->
                    <div v-if="currentTab === 'documents'" class="card shadow-sm border-0">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-dense mb-0 align-middle">
                                    <thead class="bg-light text-muted">
                                        <tr>
                                            <th width="40%">Nom du Fichier</th>
                                            <th>Type</th>
                                            <th>Taille</th>
                                            <th>Ajouté le</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="i in 5" :key="i">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-file-earmark-pdf fs-4 text-danger me-2"></i>
                                                    <div>
                                                        <div class="fw-bold text-gray-800">Facture_Fournisseur_00{{i}}.pdf</div>
                                                        <div class="small text-muted">01_Comptabilité / Factures Fournisseurs</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary">PDF</span></td>
                                            <td class="tabular-nums">1.{{i}} MB</td>
                                            <td class="tabular-nums">1{{i}} Oct 2026</td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-light border me-1"><i class="bi bi-eye"></i></button>
                                                <button class="btn btn-sm btn-light border"><i class="bi bi-download"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Liste des Workflows (À valider) -->
                    <div v-if="currentTab === 'workflows'" class="card shadow-sm border-0">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-dense mb-0 align-middle">
                                    <thead class="bg-light text-muted">
                                        <tr>
                                            <th width="35%">Document / Demande</th>
                                            <th>Soumis par</th>
                                            <th>Étape actuelle</th>
                                            <th class="text-center">Statut</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-gray-800">Bon à Payer - Facture BuroTech</div>
                                                <div class="small text-muted">Montant : 1 250 000 FCFA</div>
                                            </td>
                                            <td>M. Zinsou</td>
                                            <td><span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> Validation DG</span></td>
                                            <td class="text-center"><span class="badge bg-warning bg-opacity-10 text-warning">En attente</span></td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-success me-1" title="Approuver"><i class="bi bi-check-lg"></i></button>
                                                <button class="btn btn-sm btn-danger" title="Rejeter"><i class="bi bi-x-lg"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-gray-800">Demande de Congés Payés</div>
                                                <div class="small text-muted">Du 01/11/2026 au 15/11/2026</div>
                                            </td>
                                            <td>J. Dupont</td>
                                            <td><span class="badge bg-info text-dark"><i class="bi bi-person-check me-1"></i> Validation RH</span></td>
                                            <td class="text-center"><span class="badge bg-warning bg-opacity-10 text-warning">En attente</span></td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-success me-1" title="Approuver"><i class="bi bi-check-lg"></i></button>
                                                <button class="btn btn-sm btn-danger" title="Rejeter"><i class="bi bi-x-lg"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-gray-800">Contrat Prestataire IT.pdf</div>
                                                <div class="small text-muted">Signature électronique requise</div>
                                            </td>
                                            <td>Direction</td>
                                            <td><span class="badge bg-primary"><i class="bi bi-pen me-1"></i> Signature Client</span></td>
                                            <td class="text-center"><span class="badge bg-primary bg-opacity-10 text-primary">En cours de signature</span></td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-light border"><i class="bi bi-eye"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
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
import GelLayout from '../../../../Layouts/GelLayout.vue';

const currentTab = ref('workflows');
</script>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}
.list-group-item:hover {
    background-color: #f8f9fa;
}
.table-dense th, .table-dense td {
    padding: 0.6rem;
    font-size: 0.85rem;
}
.tabular-nums {
    font-variant-numeric: tabular-nums;
}
</style>
