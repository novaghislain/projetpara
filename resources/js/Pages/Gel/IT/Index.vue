<template>
    <GelLayout pageTitle="Support Informatique (IT)">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-1 text-gray-800">IT Helpdesk & Tickets</h2>
                    <p class="text-muted mb-0">Signalez un incident ou demandez du matériel</p>
                </div>
                <div>
                    <button class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Nouveau Ticket</button>
                </div>
            </div>

            <!-- KPI IT -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 border-start border-4 border-danger h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs fw-bold text-danger text-uppercase mb-1">Tickets Ouverts</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">4</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 border-start border-4 border-warning h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">En cours de traitement</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">2</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 border-start border-4 border-success h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">Résolus (Ce mois)</div>
                            <div class="h5 mb-0 fw-bold text-gray-800 tabular-nums">18</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 border-start border-4 border-info h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">Parc Informatique</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">12 Postes</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Tableau des Tickets -->
                <div class="col-md-8 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 fw-bold text-gray-800">Mes Tickets</h6>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-secondary active">Tous</button>
                                <button class="btn btn-sm btn-outline-secondary">Ouverts</button>
                                <button class="btn btn-sm btn-outline-secondary">Résolus</button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-dense mb-0 align-middle">
                                    <thead class="bg-light text-muted">
                                        <tr>
                                            <th>ID</th>
                                            <th width="40%">Sujet</th>
                                            <th>Priorité</th>
                                            <th>Statut</th>
                                            <th>Créé le</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="tabular-nums fw-bold text-muted">#T-0042</td>
                                            <td>
                                                <div class="fw-bold text-gray-800">Imprimante 3ème étage bloquée</div>
                                                <div class="small text-muted">Erreur bourrage papier récurrente</div>
                                            </td>
                                            <td><span class="badge bg-danger">Haute</span></td>
                                            <td><span class="badge bg-warning text-dark">En cours</span></td>
                                            <td class="tabular-nums">Aujourd'hui, 09:14</td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-light border"><i class="bi bi-eye"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="tabular-nums fw-bold text-muted">#T-0041</td>
                                            <td>
                                                <div class="fw-bold text-gray-800">Demande souris sans fil</div>
                                                <div class="small text-muted">Pour nouvel arrivant (Compta)</div>
                                            </td>
                                            <td><span class="badge bg-info text-dark">Basse</span></td>
                                            <td><span class="badge bg-primary">Nouveau</span></td>
                                            <td class="tabular-nums">Hier, 16:30</td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-light border"><i class="bi bi-eye"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="tabular-nums fw-bold text-muted">#T-0038</td>
                                            <td>
                                                <div class="fw-bold text-gray-800">Problème accès VPN</div>
                                                <div class="small text-muted">Connexion impossible depuis domicile</div>
                                            </td>
                                            <td><span class="badge bg-warning text-dark">Moyenne</span></td>
                                            <td><span class="badge bg-success">Résolu</span></td>
                                            <td class="tabular-nums">12 Oct 2026</td>
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

                <!-- Outils et Base de connaissances -->
                <div class="col-md-4 mb-4">
                    <!-- Base de connaissances -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white border-bottom p-3">
                            <h6 class="m-0 fw-bold text-gray-800"><i class="bi bi-journal-text text-primary me-2"></i> Base de Connaissances</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <a href="#" class="list-group-item list-group-item-action d-flex align-items-center">
                                    <i class="bi bi-wifi text-muted fs-5 me-3"></i>
                                    <div>
                                        <div class="fw-bold text-sm">Se connecter au Wi-Fi Visiteur</div>
                                        <div class="text-xs text-muted">Guide pas-à-pas</div>
                                    </div>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action d-flex align-items-center">
                                    <i class="bi bi-shield-lock text-muted fs-5 me-3"></i>
                                    <div>
                                        <div class="fw-bold text-sm">Changer son mot de passe Windows</div>
                                        <div class="text-xs text-muted">Sécurité</div>
                                    </div>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action d-flex align-items-center">
                                    <i class="bi bi-printer text-muted fs-5 me-3"></i>
                                    <div>
                                        <div class="fw-bold text-sm">Configurer l'imprimante réseau</div>
                                        <div class="text-xs text-muted">Bureautique</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Matériel assigné -->
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-bottom p-3">
                            <h6 class="m-0 fw-bold text-gray-800"><i class="bi bi-pc-display text-primary me-2"></i> Mon Matériel Assigné</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center p-2 border rounded mb-2">
                                <i class="bi bi-laptop fs-3 text-secondary me-3"></i>
                                <div>
                                    <div class="fw-bold text-sm">Dell Latitude 5520</div>
                                    <div class="text-xs text-muted">S/N: 8J9K2L1</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center p-2 border rounded">
                                <i class="bi bi-telephone fs-3 text-secondary me-3"></i>
                                <div>
                                    <div class="fw-bold text-sm">Cisco IP Phone 8841</div>
                                    <div class="text-xs text-muted">EXT: 402</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import GelLayout from '../../../Layouts/GelLayout.vue';
</script>

<style scoped>
.table-dense th, .table-dense td {
    padding: 0.6rem;
    font-size: 0.85rem;
}
.tabular-nums {
    font-variant-numeric: tabular-nums;
}
</style>
