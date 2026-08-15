<template>
    <GelLayout pageTitle="Paramétrages & Secrétariat">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-1 text-gray-800">Paramétrages & Secrétariat Juridique</h2>
                    <p class="text-muted mb-0">Gestion de la structure, des accès, et du dossier permanent</p>
                </div>
                <div>
                    <button class="btn btn-primary">
                        <i class="bi bi-person-plus me-1"></i> Nouvel Utilisateur
                    </button>
                </div>
            </div>

            <div class="row">
                <!-- Informations de l'Entreprise -->
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 fw-bold text-gray-800"><i class="bi bi-building text-primary me-2"></i> Dossier Permanent</h6>
                            <button class="btn btn-sm btn-light border"><i class="bi bi-pencil"></i></button>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center p-3 mb-2" style="width: 80px; height: 80px;">
                                    <span class="fs-1 fw-bold text-primary">GS</span>
                                </div>
                                <h5 class="fw-bold mb-0">Global Services SARL</h5>
                                <span class="badge bg-success mt-1">Actif</span>
                            </div>
                            
                            <ul class="list-group list-group-flush small">
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Forme Juridique</span>
                                    <span class="fw-medium">SARL</span>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Capital Social</span>
                                    <span class="fw-medium tabular-nums">1 000 000 FCFA</span>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <span class="text-muted">IFU</span>
                                    <span class="fw-medium font-monospace">3201456789123</span>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <span class="text-muted">RCCM</span>
                                    <span class="fw-medium font-monospace">RB/COT/21 B 1456</span>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Régime Fiscal</span>
                                    <span class="fw-medium">Régime Réel Normal</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Utilisateurs & Accès -->
                <div class="col-xl-8 col-md-6 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-bottom p-3">
                            <h6 class="m-0 fw-bold text-gray-800"><i class="bi bi-people text-info me-2"></i> Gestion des Utilisateurs & Rôles</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-dense mb-0 align-middle">
                                    <thead class="bg-light text-muted">
                                        <tr>
                                            <th width="30%">Utilisateur</th>
                                            <th width="20%">Rôle Principal</th>
                                            <th width="25%">Département</th>
                                            <th width="15%" class="text-center">Statut</th>
                                            <th width="10%" class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="user in users" :key="user.id">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-secondary bg-opacity-10 rounded-circle p-2 me-2 text-secondary fw-bold" style="width: 32px; height: 32px; display:flex; align-items:center; justify-content:center;">
                                                        {{ user.initials }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-gray-800">{{ user.name }}</div>
                                                        <div class="small text-muted">{{ user.email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge" :class="getRoleBadgeClass(user.role)">{{ user.role }}</span>
                                            </td>
                                            <td>{{ user.department }}</td>
                                            <td class="text-center">
                                                <span class="badge" :class="user.active ? 'bg-success' : 'bg-danger'">
                                                    {{ user.active ? 'Actif' : 'Inactif' }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-light border me-1"><i class="bi bi-pencil"></i></button>
                                                <button class="btn btn-sm btn-light border text-danger"><i class="bi bi-trash"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Paramètres Comptables & Alertes Juridiques -->
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-bottom p-3">
                            <h6 class="m-0 fw-bold text-gray-800"><i class="bi bi-sliders text-warning me-2"></i> Préférences Comptables</h6>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">Exercice Comptable par Défaut</label>
                                    <select class="form-select">
                                        <option value="2026">2026 (En cours)</option>
                                        <option value="2025">2025 (Clôturé)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">Devise de Tenue de Compte</label>
                                    <input type="text" class="form-control" value="FCFA (Franc CFA BCEAO)" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">Numérotation Automatique (Factures)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">FAC-AA-MM-</span>
                                        <input type="text" class="form-control" value="0001">
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary">Enregistrer les préférences</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 fw-bold text-gray-800"><i class="bi bi-calendar-event text-danger me-2"></i> Agenda Juridique</h6>
                            <button class="btn btn-sm btn-light border">Voir Registres</button>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning border-0 border-start border-4 border-warning d-flex align-items-center mb-3">
                                <i class="bi bi-exclamation-circle fs-4 me-3 text-warning"></i>
                                <div>
                                    <h6 class="mb-1 fw-bold">Assemblée Générale Ordinaire</h6>
                                    <p class="mb-0 small">L'AGO d'approbation des comptes 2025 doit se tenir avant le 30 Juin 2026 (En retard).</p>
                                </div>
                            </div>
                            <div class="alert alert-info border-0 border-start border-4 border-info d-flex align-items-center mb-0">
                                <i class="bi bi-info-circle fs-4 me-3 text-info"></i>
                                <div>
                                    <h6 class="mb-1 fw-bold">Renouvellement Mandat Gérant</h6>
                                    <p class="mb-0 small">Le mandat de la Gérance expire le 31 Décembre 2026.</p>
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
import { ref } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';

const users = ref([
    { id: 1, name: 'Jean DUPONT', initials: 'JD', email: 'jean.dupont@globalservices.com', role: 'Administrateur', department: 'Direction', active: true },
    { id: 2, name: 'Marie CURIE', initials: 'MC', email: 'marie.curie@globalservices.com', role: 'Chef Comptable', department: 'Comptabilité', active: true },
    { id: 3, name: 'Paul KOFFI', initials: 'PK', email: 'paul.koffi@globalservices.com', role: 'Assistant RH', department: 'Ressources Humaines', active: true },
    { id: 4, name: 'Sophie LEROUX', initials: 'SL', email: 'sophie.leroux@globalservices.com', role: 'Commercial', department: 'Ventes', active: false },
]);

const getRoleBadgeClass = (role) => {
    switch(role) {
        case 'Administrateur': return 'bg-danger';
        case 'Chef Comptable': return 'bg-primary';
        case 'Assistant RH': return 'bg-info';
        case 'Commercial': return 'bg-success';
        default: return 'bg-secondary';
    }
};
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
