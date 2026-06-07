<script setup>
import { ref } from 'vue';
import { 
    Search, 
    Filter, 
    ChevronRight,
    Eye,
    MoreVertical,
    Download
} from 'lucide-vue-next';

const props = defineProps({
    orders: { type: Array, default: () => [] }
});

const searchQuery = ref('');
const statusFilter = ref('all');

// Mock data if no props provided (for initial dev)
const mockOrders = [
    { id: '#1234', customer: 'Jean Dupont', city: 'Cotonou', total: 25000, status: 'pending', date: '2024-03-15 10:30' },
    { id: '#1235', customer: 'Marie Sossa', city: 'Porto-Novo', total: 15000, status: 'completed', date: '2024-03-15 09:15' },
    { id: '#1236', customer: 'Koffi Paul', city: 'Ouidah', total: 45000, status: 'shipped', date: '2024-03-14 16:45' },
    { id: '#1237', customer: 'Alice Zinsou', city: 'Abomey-Calavi', total: 32500, status: 'cancelled', date: '2024-03-14 14:20' },
];

const currentOrders = props.orders.length > 0 ? props.orders : mockOrders;

const getStatusBadge = (status) => {
    switch (status) {
        case 'pending': return 'bg-warning text-dark';
        case 'completed': return 'bg-success text-white';
        case 'shipped': return 'bg-info text-white';
        case 'cancelled': return 'bg-danger text-white';
        default: return 'bg-secondary text-white';
    }
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'pending': return 'En attente';
        case 'completed': return 'Livré';
        case 'shipped': return 'Expédié';
        case 'cancelled': return 'Annulé';
        default: return status;
    }
};
</script>

<template>
    <AdminLayout>
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fw-bold mb-1">Gestion des Commandes</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb small mb-0">
                        <li class="breadcrumb-item"><a href="/admin" class="text-muted">Admin</a></li>
                        <li class="breadcrumb-item active text-primary">Commandes</li>
                    </ol>
                </nav>
            </div>
            <button class="btn btn-outline-primary rounded-pill px-4 d-flex align-items-center gap-2">
                <Download :size="18" /> Exporter
            </button>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><Search :size="18" class="text-muted" /></span>
                        <input type="text" v-model="searchQuery" class="form-control bg-light border-0" placeholder="Rechercher une commande, un client..." />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><Filter :size="18" class="text-muted" /></span>
                        <select v-model="statusFilter" class="form-select bg-light border-0">
                            <option value="all">Tous les statuts</option>
                            <option value="pending">En attente</option>
                            <option value="shipped">Expédié</option>
                            <option value="completed">Livré</option>
                            <option value="cancelled">Annulé</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2 text-end">
                    <span class="text-muted small fw-bold">{{ currentOrders.length }} commandes</span>
                </div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted small text-uppercase ls-1">
                            <th class="p-4 border-0">ID</th>
                            <th class="p-4 border-0">Client</th>
                            <th class="p-4 border-0">Ville</th>
                            <th class="p-4 border-0">Date</th>
                            <th class="p-4 border-0">Total</th>
                            <th class="p-4 border-0">Statut</th>
                            <th class="p-4 border-0 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="order in currentOrders" :key="order.id" class="border-bottom border-light">
                            <td class="p-4 fw-bold text-dark">{{ order.id }}</td>
                            <td class="p-4">
                                <div class="fw-bold">{{ order.customer }}</div>
                            </td>
                            <td class="p-4 text-muted">{{ order.city }}</td>
                            <td class="p-4 text-muted small">{{ order.date }}</td>
                            <td class="p-4 fw-bold text-primary">{{ order.total.toLocaleString() }} FCFA</td>
                            <td class="p-4">
                                <span class="badge rounded-pill px-3 py-2" :class="getStatusBadge(order.status)">
                                    {{ getStatusLabel(order.status) }}
                                </span>
                            </td>
                            <td class="p-4 text-end">
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm rounded-circle p-2" type="button" data-bs-toggle="dropdown">
                                        <Eye :size="18" />
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3">
                                        <li><a class="dropdown-item py-2" :href="`/admin/orders/${order.id.replace('#', '')}`">Détails</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item py-2 text-success" href="#">Marquer comme livré</a></li>
                                        <li><a class="dropdown-item py-2 text-info" href="#">Marquer comme expédié</a></li>
                                        <li><a class="dropdown-item py-2 text-danger" href="#">Annuler la commande</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="p-4 border-top border-light d-flex align-items-center justify-content-between">
                <div class="text-muted small">Affichage de 1 à 4 sur 24 résultats</div>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled"><a class="page-link border-0 rounded-start-pill px-3" href="#">Précédent</a></li>
                        <li class="page-item active"><a class="page-link border-0 px-3" href="#">1</a></li>
                        <li class="page-item"><a class="page-link border-0 px-3" href="#">2</a></li>
                        <li class="page-item"><a class="page-link border-0 rounded-end-pill px-3" href="#">Suivant</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.ls-1 { letter-spacing: 0.05em; }
.dropdown-item:active { background-color: var(--primary); }
</style>
