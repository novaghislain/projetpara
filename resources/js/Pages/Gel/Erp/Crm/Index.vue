<template>
    <GelLayout pageTitle="CRM & Pipeline Commercial">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-1 text-gray-800">Pipeline Commercial & Clients</h2>
                    <p class="text-muted mb-0">Gérez vos opportunités, devis et suivi client</p>
                </div>
                <div>
                    <button class="btn btn-light border me-2"><i class="bi bi-download me-1"></i> Exporter</button>
                    <button class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Nouvelle Opportunité</button>
                </div>
            </div>

            <!-- KPI CRM -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 border-start border-4 border-primary h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">Nouveaux Prospects (Mois)</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">45</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 border-start border-4 border-warning h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">Devis en cours</div>
                            <div class="h5 mb-0 fw-bold text-gray-800 tabular-nums">12 450 000 FCFA</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 border-start border-4 border-success h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">Opportunités Gagnées</div>
                            <div class="h5 mb-0 fw-bold text-gray-800 tabular-nums">24 100 000 FCFA</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 border-start border-4 border-info h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">Taux de conversion</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">28.4%</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Onglets -->
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <a class="nav-link" :class="{ active: currentTab === 'pipeline' }" @click="currentTab = 'pipeline'" href="#">Pipeline (Kanban)</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" :class="{ active: currentTab === 'clients' }" @click="currentTab = 'clients'" href="#">Liste des Clients</a>
                </li>
            </ul>

            <!-- Kanban Pipeline -->
            <div v-if="currentTab === 'pipeline'" class="kanban-board d-flex gap-4 pb-3" style="overflow-x: auto;">
                
                <!-- Colonne Prospection -->
                <div class="kanban-column bg-light rounded px-3 py-3" style="min-width: 300px; flex: 1;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0 text-muted">Prospection <span class="badge bg-secondary ms-2">3</span></h6>
                    </div>
                    <div class="card shadow-sm border-0 mb-3 kanban-card cursor-pointer">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-light text-dark border">Logiciel</span>
                                <span class="text-muted small">05 Oct</span>
                            </div>
                            <h6 class="fw-bold mb-1">Refonte site web E-commerce</h6>
                            <p class="small text-muted mb-2">Tech Solutions SARL</p>
                            <div class="fw-bold tabular-nums text-primary mb-2">2 500 000 FCFA</div>
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-secondary" role="progressbar" style="width: 20%"></div>
                            </div>
                        </div>
                    </div>
                    <!-- Autre carte... -->
                    <div class="card shadow-sm border-0 mb-3 kanban-card cursor-pointer">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-1">Audit Sécurité</h6>
                            <p class="small text-muted mb-2">Banque Atlantic</p>
                            <div class="fw-bold tabular-nums text-primary mb-2">À définir</div>
                        </div>
                    </div>
                </div>

                <!-- Colonne Qualification -->
                <div class="kanban-column bg-light rounded px-3 py-3" style="min-width: 300px; flex: 1;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0 text-primary">Qualification <span class="badge bg-primary ms-2">2</span></h6>
                    </div>
                    <div class="card shadow-sm border-0 border-start border-3 border-primary mb-3 kanban-card cursor-pointer">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-1">Déploiement ERP</h6>
                            <p class="small text-muted mb-2">Industries Réunies</p>
                            <div class="fw-bold tabular-nums text-primary mb-2">15 000 000 FCFA</div>
                            <div class="small text-muted"><i class="bi bi-calendar-event me-1"></i> R.D.V. le 12 Oct</div>
                        </div>
                    </div>
                </div>

                <!-- Colonne Proposition / Devis -->
                <div class="kanban-column bg-light rounded px-3 py-3" style="min-width: 300px; flex: 1;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0 text-warning">Proposition / Devis <span class="badge bg-warning text-dark ms-2">1</span></h6>
                    </div>
                    <div class="card shadow-sm border-0 border-start border-3 border-warning mb-3 kanban-card cursor-pointer">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-warning text-dark">Devis Envoyé</span>
                            </div>
                            <h6 class="fw-bold mb-1">Mission de Commissariat</h6>
                            <p class="small text-muted mb-2">Groupe CFAO</p>
                            <div class="fw-bold tabular-nums text-primary mb-2">5 000 000 FCFA</div>
                        </div>
                    </div>
                </div>

                <!-- Colonne Gagné -->
                <div class="kanban-column bg-light rounded px-3 py-3" style="min-width: 300px; flex: 1;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0 text-success">Gagné <span class="badge bg-success ms-2">1</span></h6>
                    </div>
                    <div class="card shadow-sm border-0 border-start border-3 border-success mb-3 kanban-card cursor-pointer">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-1">Fourniture Matériel IT</h6>
                            <p class="small text-muted mb-2">Ministère de l'Économie</p>
                            <div class="fw-bold tabular-nums text-success mb-2">8 500 000 FCFA</div>
                            <div class="small text-success"><i class="bi bi-check-circle-fill me-1"></i> Facturé (FAC-2026-0045)</div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Table Clients -->
            <div v-if="currentTab === 'clients'" class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <div class="input-group" style="max-width: 300px;">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0 bg-light" placeholder="Rechercher un client...">
                    </div>
                    <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-filter me-1"></i> Filtres</button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-dense mb-0 align-middle">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th>Client / Entreprise</th>
                                    <th>Contact Principal</th>
                                    <th>Téléphone</th>
                                    <th>Secteur</th>
                                    <th class="text-end">CA Généré</th>
                                    <th class="text-center">Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="i in 5" :key="i">
                                    <td>
                                        <div class="fw-bold text-gray-800">Tech Solutions SARL</div>
                                        <div class="small text-muted">IFU: 3201548{{i}}14</div>
                                    </td>
                                    <td>Jean Dupont</td>
                                    <td class="tabular-nums">+229 97 00 00 0{{i}}</td>
                                    <td>Informatique</td>
                                    <td class="text-end tabular-nums fw-bold">12 400 000 F</td>
                                    <td class="text-center">
                                        <span class="badge bg-success bg-opacity-10 text-success">Client Actif</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-light border"><i class="bi bi-eye"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white p-3 d-flex justify-content-between align-items-center">
                    <span class="small text-muted">Affichage de 1 à 5 sur 42 clients</span>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled"><a class="page-link" href="#">Précédent</a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">Suivant</a></li>
                    </ul>
                </div>
            </div>

        </div>
    </GelLayout>
</template>

<script setup>
import { ref } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const currentTab = ref('pipeline');
</script>

<style scoped>
.kanban-board {
    min-height: 60vh;
}
.kanban-card {
    transition: transform 0.2s;
}
.kanban-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}
.cursor-pointer {
    cursor: pointer;
}
.table-dense th, .table-dense td {
    padding: 0.6rem;
    font-size: 0.85rem;
}
.tabular-nums {
    font-variant-numeric: tabular-nums;
}
</style>
