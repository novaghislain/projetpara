<template>
    <GelLayout pageTitle="Immobilisations & Amortissements">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-1 text-gray-800">Immobilisations & Amortissements</h2>
                    <p class="text-muted mb-0">Registre des actifs immobilisés et calcul des dotations</p>
                </div>
                <div>
                    <button class="btn btn-outline-primary me-2">
                        <i class="bi bi-file-earmark-excel me-1"></i> Importer
                    </button>
                    <button class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Nouvelle Immobilisation
                    </button>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-start-primary shadow-sm h-100 py-2 border-0">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Valeur Brute (Total)</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800 tabular-nums">120 500 000 FCFA</div>
                                </div>
                                <div class="col-auto"><i class="bi bi-building-gear fs-2 text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-start-success shadow-sm h-100 py-2 border-0">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Amortissements Cumulés</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800 tabular-nums">45 200 000 FCFA</div>
                                </div>
                                <div class="col-auto"><i class="bi bi-graph-down fs-2 text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-start-info shadow-sm h-100 py-2 border-0">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Valeur Nette Comptable (VNC)</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800 tabular-nums">75 300 000 FCFA</div>
                                </div>
                                <div class="col-auto"><i class="bi bi-pie-chart fs-2 text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-start-warning shadow-sm h-100 py-2 border-0">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Dotations (Exercice en cours)</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800 tabular-nums">12 400 000 FCFA</div>
                                </div>
                                <div class="col-auto"><i class="bi bi-calculator fs-2 text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des Immobilisations -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <div class="input-group input-group-sm" style="width: 300px;">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control bg-light border-start-0" placeholder="Rechercher (Code, Libellé)..." v-model="search">
                    </div>
                    <div class="d-flex">
                        <select class="form-select form-select-sm w-auto me-2">
                            <option value="">Toutes les catégories</option>
                            <option value="21">Immobilisations Incorporelles (21)</option>
                            <option value="22">Terrains (22)</option>
                            <option value="23">Bâtiments (23)</option>
                            <option value="24">Matériel & Outillage (24)</option>
                            <option value="244">Matériel Informatique (244)</option>
                        </select>
                        <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-gear"></i> Générer Dotations</button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-dense mb-0 align-middle">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th width="10%">Code</th>
                                    <th width="20%">Libellé</th>
                                    <th width="10%">Date d'Acq.</th>
                                    <th width="10%" class="text-end">Valeur Brute</th>
                                    <th width="8%" class="text-center">Taux</th>
                                    <th width="8%" class="text-center">Durée</th>
                                    <th width="12%" class="text-end">Amort. Cumulés</th>
                                    <th width="12%" class="text-end">VNC</th>
                                    <th width="10%" class="text-center">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="asset in filteredAssets" :key="asset.id">
                                    <td class="font-monospace fw-bold">{{ asset.code }}</td>
                                    <td>
                                        <div>{{ asset.name }}</div>
                                        <small class="text-muted font-monospace">Compte: {{ asset.account }}</small>
                                    </td>
                                    <td>{{ asset.acquisition_date }}</td>
                                    <td class="text-end tabular-nums fw-medium">{{ formatCurrency(asset.brut) }}</td>
                                    <td class="text-center tabular-nums">{{ asset.rate }}%</td>
                                    <td class="text-center tabular-nums">{{ asset.duration }} ans</td>
                                    <td class="text-end tabular-nums text-danger">{{ formatCurrency(asset.amortized) }}</td>
                                    <td class="text-end tabular-nums fw-bold text-success">{{ formatCurrency(asset.vnc) }}</td>
                                    <td class="text-center">
                                        <span class="badge" :class="getAssetBadgeClass(asset.status)">
                                            {{ asset.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const search = ref('');

const assets = ref([
    { id: 1, code: 'IMM-0001', name: 'Serveur Dell PowerEdge', account: '2441', acquisition_date: '15/01/2023', brut: 3500000, rate: 33.33, duration: 3, amortized: 1166550, vnc: 2333450, status: 'En cours' },
    { id: 2, code: 'IMM-0002', name: 'Véhicule Toyota Hilux', account: '2451', acquisition_date: '10/05/2021', brut: 18500000, rate: 20, duration: 5, amortized: 11100000, vnc: 7400000, status: 'En cours' },
    { id: 3, code: 'IMM-0003', name: 'Mobilier Bureau Direction', account: '2444', acquisition_date: '02/11/2020', brut: 2500000, rate: 20, duration: 5, amortized: 2500000, vnc: 0, status: 'Totalement Amorti' },
    { id: 4, code: 'IMM-0004', name: 'Licence Logiciel ERP', account: '213', acquisition_date: '20/08/2025', brut: 5000000, rate: 33.33, duration: 3, amortized: 0, vnc: 5000000, status: 'Nouveau' },
    { id: 5, code: 'IMM-0005', name: 'Bâtiment Siège Social', account: '2311', acquisition_date: '01/01/2010', brut: 85000000, rate: 5, duration: 20, amortized: 59500000, vnc: 25500000, status: 'En cours' },
]);

const filteredAssets = computed(() => {
    if (!search.value) return assets.value;
    const s = search.value.toLowerCase();
    return assets.value.filter(a => a.code.toLowerCase().includes(s) || a.name.toLowerCase().includes(s));
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF', maximumFractionDigits: 0 }).format(value).replace('XOF', 'FCFA');
};

const getAssetBadgeClass = (status) => {
    switch(status) {
        case 'Nouveau': return 'bg-primary';
        case 'En cours': return 'bg-success';
        case 'Totalement Amorti': return 'bg-secondary';
        case 'Cédé': return 'bg-danger';
        default: return 'bg-dark';
    }
};
</script>

<style scoped>
.table-dense th, .table-dense td {
    padding: 0.5rem;
    font-size: 0.85rem;
}
.border-start-primary { border-left: 4px solid #4e73df !important; }
.border-start-success { border-left: 4px solid #1cc88a !important; }
.border-start-info { border-left: 4px solid #36b9cc !important; }
.border-start-warning { border-left: 4px solid #f6c23e !important; }
</style>
