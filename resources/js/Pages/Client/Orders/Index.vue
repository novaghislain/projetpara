<script setup>
import CompanyLayout from '../../../Layouts/CompanyLayout.vue';

const props = defineProps({
    orders: {
        type: Array,
        required: true,
    }
});

const getStatusColor = (status) => {
    const colors = {
        'Nouvelle Demande': 'bg-blue-100 text-blue-800',
        'En cours': 'bg-yellow-100 text-yellow-800',
        'En attente client': 'bg-orange-100 text-orange-800',
        'Livrée': 'bg-green-100 text-green-800',
        'Annulée': 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <CompanyLayout page-title="Mes Commandes">
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-100">
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h4 mb-0 fw-bold text-dark font-heading">Mes Commandes de Services</h2>
                        <a href="/nos-services" class="btn btn-primary btn-sm">
                            <i class="bi-plus-circle me-1"></i> Nouveau Service
                        </a>
                    </div>

                    <div v-if="orders.length > 0" class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Référence</th>
                                    <th>Service</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="order in orders" :key="order.id">
                                    <td class="fw-medium text-dark">
                                        {{ order.reference }}
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ order.service?.nom }}</div>
                                        <div class="small text-muted">{{ order.category?.nom }}</div>
                                    </td>
                                    <td class="text-muted">
                                        {{ new Date(order.date_commande).toLocaleDateString('fr-FR') }}
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill" :class="getStatusColor(order.statut)">
                                            {{ order.statut }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a :href="'/mes-commandes/' + order.id" class="btn btn-outline-primary btn-sm">
                                            <i class="bi-eye"></i> Suivre
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="text-center py-5 text-muted">
                        <i class="bi-inbox" style="font-size: 3rem; opacity: 0.5;"></i>
                        <h5 class="mt-3">Aucune commande</h5>
                        <p>Vous n'avez pas encore commandé de services.</p>
                        <a href="/nos-services" class="btn btn-primary mt-2">
                            Parcourir le catalogue
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </CompanyLayout>
</template>
