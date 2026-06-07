<script setup>
import { 
    Users, 
    ShoppingBag, 
    TrendingUp, 
    Clock,
    ArrowUpRight,
    ArrowDownRight,
    CheckCircle2,
    Eye
} from 'lucide-vue-next';

const stats = [
    { name: 'Ventes Totales', value: '450.000 FCFA', icon: TrendingUp, change: '+12%', type: 'increase' },
    { name: 'Commandes', value: '24', icon: ShoppingBag, change: '+5', type: 'increase' },
    { name: 'Clients', value: '18', icon: Users, change: '+3', type: 'increase' },
    { name: 'En attente', value: '7', icon: Clock, change: '-2', type: 'decrease' },
];

const recentOrders = [
    { id: '#1234', customer: 'Jean Dupont', total: '25.000 FCFA', status: 'pending', date: 'Il y a 2h' },
    { id: '#1235', customer: 'Marie Sossa', total: '15.000 FCFA', status: 'completed', date: 'Il y a 5h' },
    { id: '#1236', customer: 'Koffi Paul', total: '45.000 FCFA', status: 'shipped', date: 'Hier' },
];

const getStatusBadge = (status) => {
    switch (status) {
        case 'pending': return 'bg-warning text-dark';
        case 'completed': return 'bg-success text-white';
        case 'shipped': return 'bg-info text-white';
        default: return 'bg-secondary text-white';
    }
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'pending': return 'En attente';
        case 'completed': return 'Livré';
        case 'shipped': return 'Expédié';
        default: return status;
    }
};
</script>

<template>
    <AdminLayout>
        <div class="mb-4">
            <h2 class="fw-bold">Tableau de Bord</h2>
            <p class="text-muted">Bienvenue, voici un aperçu de votre activité aujourd'hui.</p>
        </div>

        <!-- Stats Grid -->
        <div class="row g-4 mb-5">
            <div v-for="stat in stats" :key="stat.name" class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-4">
                            <component :is="stat.icon" :size="24" />
                        </div>
                        <div 
                            class="badge rounded-pill px-2 py-1 small"
                            :class="stat.type === 'increase' ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger'"
                        >
                            {{ stat.change }}
                            <component :is="stat.type === 'increase' ? ArrowUpRight : ArrowDownRight" :size="14" />
                        </div>
                    </div>
                    <h6 class="text-muted fw-bold small text-uppercase mb-1">{{ stat.name }}</h6>
                    <h3 class="fw-bold mb-0">{{ stat.value }}</h3>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Recent Orders -->
            <div class="col-xl-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h5 class="fw-bold mb-0">Commandes Récentes</h5>
                        <a href="/admin/orders" class="btn btn-primary btn-sm rounded-pill px-3">Voir tout</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="text-muted small uppercase">
                                    <th class="border-0 p-3">ID</th>
                                    <th class="border-0 p-3">Client</th>
                                    <th class="border-0 p-3">Total</th>
                                    <th class="border-0 p-3">Statut</th>
                                    <th class="border-0 p-3">Date</th>
                                    <th class="border-0 p-3 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="order in recentOrders" :key="order.id">
                                    <td class="p-3 fw-bold">{{ order.id }}</td>
                                    <td class="p-3">{{ order.customer }}</td>
                                    <td class="p-3 fw-bold text-primary">{{ order.total }}</td>
                                    <td class="p-3">
                                        <span class="badge rounded-pill px-3 py-2" :class="getStatusBadge(order.status)">
                                            {{ getStatusLabel(order.status) }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-muted small">{{ order.date }}</td>
                                    <td class="p-3 text-end">
                                        <button class="btn btn-light btn-sm rounded-circle shadow-sm" title="Voir">
                                            <Eye :size="16" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                    <h5 class="fw-bold mb-4">Actions Rapides</h5>
                    <div class="d-grid gap-3">
                        <button class="btn btn-white border-2 border-dashed rounded-4 p-4 text-start hover-border-primary transition-all">
                            <h6 class="fw-bold mb-1">Ajouter un produit</h6>
                            <p class="text-muted small mb-0">Mettre en ligne une nouvelle référence.</p>
                        </button>
                        <button class="btn btn-white border-2 border-dashed rounded-4 p-4 text-start hover-border-primary transition-all">
                            <h6 class="fw-bold mb-1">Voir les reports</h6>
                            <p class="text-muted small mb-0">Exporter les statistiques de vente.</p>
                        </button>
                        <button class="btn btn-white border-2 border-dashed rounded-4 p-4 text-start hover-border-primary transition-all">
                            <h6 class="fw-bold mb-1">Support Client</h6>
                            <p class="text-muted small mb-0">Répondre aux messages d'assistance.</p>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.hover-border-primary:hover { border-color: var(--primary) !important; color: var(--primary); }
.border-dashed { border-style: dashed !important; }
.transition-all { transition: all 0.2s ease-in-out; }
</style>
