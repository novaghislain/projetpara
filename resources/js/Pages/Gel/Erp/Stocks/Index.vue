<template>
    <GelLayout pageTitle="Achats & Gestion des Stocks">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-1 text-gray-800">Achats & Gestion des Stocks</h2>
                    <p class="text-muted mb-0">Centralisez vos articles, commandes fournisseurs et inventaires</p>
                </div>
                <div>
                    <button class="btn btn-light border me-2"><i class="bi bi-file-earmark-excel me-1"></i> Exporter</button>
                    <button class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Nouvelle Commande</button>
                </div>
            </div>

            <!-- KPI Achats & Stocks -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 border-start border-4 border-primary h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">Articles en Stock</div>
                            <div class="h5 mb-0 fw-bold text-gray-800 tabular-nums">1,245 Unités</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 border-start border-4 border-danger h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs fw-bold text-danger text-uppercase mb-1">Ruptures Imminentes</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">12 Articles</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 border-start border-4 border-warning h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">Commandes en cours</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">4 <span class="text-muted small fw-normal">(En livraison)</span></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 border-start border-4 border-success h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">Valeur du Stock</div>
                            <div class="h5 mb-0 fw-bold text-gray-800 tabular-nums">18 500 000 FCFA</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Onglets -->
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <a class="nav-link" :class="{ active: currentTab === 'articles' }" @click="currentTab = 'articles'" href="#">Catalogue & Stocks</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" :class="{ active: currentTab === 'commandes' }" @click="currentTab = 'commandes'" href="#">Commandes Fournisseurs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" :class="{ active: currentTab === 'fournisseurs' }" @click="currentTab = 'fournisseurs'" href="#">Annuaire Fournisseurs</a>
                </li>
            </ul>

            <!-- Table Articles / Stocks -->
            <div v-if="currentTab === 'articles'" class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <div class="input-group" style="max-width: 300px;">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0 bg-light" placeholder="Rechercher un article...">
                    </div>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" style="width: auto;">
                            <option value="">Toutes les catégories</option>
                            <option value="it">Matériel Informatique</option>
                            <option value="office">Fournitures de Bureau</option>
                        </select>
                        <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-filter"></i></button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-dense mb-0 align-middle">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th>Réf.</th>
                                    <th>Désignation Article</th>
                                    <th>Catégorie</th>
                                    <th class="text-end">Prix Unitaire (HT)</th>
                                    <th class="text-center">Quantité</th>
                                    <th class="text-center">Seuil d'alerte</th>
                                    <th class="text-center">Statut</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="i in 5" :key="i">
                                    <td class="font-monospace text-muted">ART-{{ 1000 + i }}</td>
                                    <td class="fw-bold text-gray-800">Ordinateur Portable Dell Latitude {{ i }}000</td>
                                    <td>Matériel Informatique</td>
                                    <td class="text-end tabular-nums">450 000 F</td>
                                    <td class="text-center tabular-nums fw-bold" :class="{ 'text-danger': i === 2 }">
                                        {{ i === 2 ? 3 : 15 + i }}
                                    </td>
                                    <td class="text-center tabular-nums text-muted">5</td>
                                    <td class="text-center">
                                        <span class="badge" :class="i === 2 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-success bg-opacity-10 text-success'">
                                            {{ i === 2 ? 'Rupture Proche' : 'En Stock' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-light border me-1" title="Ajustement de stock"><i class="bi bi-arrow-left-right"></i></button>
                                        <button class="btn btn-sm btn-light border"><i class="bi bi-pencil"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Table Commandes Fournisseurs -->
            <div v-if="currentTab === 'commandes'" class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-dense mb-0 align-middle">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th>N° Commande</th>
                                    <th>Date</th>
                                    <th>Fournisseur</th>
                                    <th class="text-end">Montant Total</th>
                                    <th class="text-center">Statut Livraison</th>
                                    <th class="text-center">Statut Paiement</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="i in 4" :key="i">
                                    <td class="font-monospace fw-bold">BC-2026-0{{ i }}</td>
                                    <td>1{{ i }} Oct 2026</td>
                                    <td class="fw-bold">Global IT Distribution SA</td>
                                    <td class="text-end tabular-nums fw-bold">1 250 000 F</td>
                                    <td class="text-center">
                                        <span class="badge" :class="i === 1 ? 'bg-warning text-dark' : 'bg-success'">
                                            {{ i === 1 ? 'En cours d\'acheminement' : 'Réceptionnée' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge" :class="i === 2 ? 'bg-danger' : 'bg-success'">
                                            {{ i === 2 ? 'Impayée' : 'Payée' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-light border" title="Bon de Réception"><i class="bi bi-box-seam"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Table Fournisseurs -->
            <div v-if="currentTab === 'fournisseurs'" class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-dense mb-0 align-middle">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th>Nom du Fournisseur</th>
                                    <th>Contact Principal</th>
                                    <th>Email / Téléphone</th>
                                    <th>Catégorie de Produits</th>
                                    <th class="text-center">Évaluation</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="i in 3" :key="i">
                                    <td>
                                        <div class="fw-bold text-gray-800">BuroTech Bénin</div>
                                        <div class="small text-muted">IFU: 3210987654321</div>
                                    </td>
                                    <td>Marc Zinsou</td>
                                    <td>
                                        <div class="small">contact@burotech.bj</div>
                                        <div class="small text-muted tabular-nums">+229 95 00 00 00</div>
                                    </td>
                                    <td>Fournitures & Mobilier</td>
                                    <td class="text-center text-warning">
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-half"></i>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-light border"><i class="bi bi-envelope"></i></button>
                                        <button class="btn btn-sm btn-light border ms-1"><i class="bi bi-eye"></i></button>
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
import { ref } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const currentTab = ref('articles');
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
