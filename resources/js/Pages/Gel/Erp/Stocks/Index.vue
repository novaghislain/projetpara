<template>
    <GelLayout pageTitle="Gestion des Stocks">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-1 text-gray-800">Gestion des Stocks</h2>
                    <p class="text-muted mb-0">Suivi des articles, alertes de rupture et mouvements</p>
                </div>
                <div>
                    <button class="btn btn-outline-primary me-2">
                        <i class="bi bi-arrow-left-right me-1"></i> Nouveau Mouvement
                    </button>
                    <button class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Nouvel Article
                    </button>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white shadow-sm h-100 border-0">
                        <div class="card-body py-3">
                            <h6 class="fw-normal mb-1">Valeur Totale du Stock</h6>
                            <h3 class="fw-bold mb-0 tabular-nums">24 500 000 FCFA</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white shadow-sm h-100 border-0">
                        <div class="card-body py-3">
                            <h6 class="fw-normal mb-1">Articles Actifs</h6>
                            <h3 class="fw-bold mb-0 tabular-nums">1,204</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark shadow-sm h-100 border-0">
                        <div class="card-body py-3">
                            <h6 class="fw-normal mb-1">Alertes de Réapprovisionnement</h6>
                            <h3 class="fw-bold mb-0 tabular-nums">15</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white shadow-sm h-100 border-0">
                        <div class="card-body py-3">
                            <h6 class="fw-normal mb-1">Ruptures de stock</h6>
                            <h3 class="fw-bold mb-0 tabular-nums">3</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table des Stocks -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <div class="input-group input-group-sm" style="width: 300px;">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control bg-light border-start-0" placeholder="Rechercher un article (SKU, nom)..." v-model="search">
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-dense mb-0 align-middle">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th width="15%">Code (SKU)</th>
                                    <th width="35%">Article</th>
                                    <th width="15%">Catégorie</th>
                                    <th width="10%" class="text-end">En Stock</th>
                                    <th width="10%" class="text-end">Prix U. HT</th>
                                    <th width="15%" class="text-end">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in filteredItems" :key="item.id">
                                    <td class="font-monospace fw-bold text-primary">{{ item.sku }}</td>
                                    <td>{{ item.name }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ item.category }}</span></td>
                                    <td class="text-end fw-bold tabular-nums" :class="getStockClass(item.stock, item.min_stock)">
                                        {{ item.stock }} {{ item.unit }}
                                    </td>
                                    <td class="text-end tabular-nums">{{ formatCurrency(item.price) }}</td>
                                    <td class="text-end">
                                        <span class="badge" :class="getStatusBadgeClass(item.stock, item.min_stock)">
                                            {{ getStatusText(item.stock, item.min_stock) }}
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

const items = ref([
    { id: 1, sku: 'ART-001', name: 'Ordinateur Portable HP ProBook', category: 'Matériel Informatique', stock: 12, min_stock: 5, unit: 'pce', price: 450000 },
    { id: 2, sku: 'ART-002', name: 'Imprimante Laser Canon', category: 'Matériel Informatique', stock: 4, min_stock: 5, unit: 'pce', price: 120000 },
    { id: 3, sku: 'ART-003', name: 'Rame de papier A4 (Carton)', category: 'Fournitures de bureau', stock: 45, min_stock: 10, unit: 'carton', price: 15000 },
    { id: 4, sku: 'ART-004', name: 'Clé USB 64Go', category: 'Accessoires', stock: 0, min_stock: 15, unit: 'pce', price: 8000 },
    { id: 5, sku: 'ART-005', name: 'Fauteuil de Direction Ergonomique', category: 'Mobilier', stock: 3, min_stock: 2, unit: 'pce', price: 185000 },
]);

const filteredItems = computed(() => {
    if (!search.value) return items.value;
    const s = search.value.toLowerCase();
    return items.value.filter(i => i.sku.toLowerCase().includes(s) || i.name.toLowerCase().includes(s));
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF', maximumFractionDigits: 0 }).format(value).replace('XOF', 'FCFA');
};

const getStockClass = (stock, min) => {
    if (stock === 0) return 'text-danger';
    if (stock <= min) return 'text-warning';
    return 'text-success';
};

const getStatusBadgeClass = (stock, min) => {
    if (stock === 0) return 'bg-danger';
    if (stock <= min) return 'bg-warning text-dark';
    return 'bg-success';
};

const getStatusText = (stock, min) => {
    if (stock === 0) return 'En Rupture';
    if (stock <= min) return 'À Réapprovisionner';
    return 'En Stock';
};
</script>

<style scoped>
.table-dense th, .table-dense td {
    padding: 0.5rem;
    font-size: 0.85rem;
}
</style>
