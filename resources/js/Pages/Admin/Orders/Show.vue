<script setup>
import { ArrowLeft, Package, User, MapPin, CreditCard, Clock, CheckCircle2, ShoppingCart, Truck } from 'lucide-vue-next';

const props = defineProps({
    order: { type: Object, required: true }
});

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
        <div class="mb-4">
            <a href="/admin/orders" class="text-muted text-decoration-none d-flex align-items-center gap-2 mb-3 small hover-text-primary transition-all">
                <ArrowLeft :size="16" /> Retour aux commandes
            </a>
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h2 class="fw-bold mb-1">Commande #{{ order.id }}</h2>
                    <p class="text-muted mb-0 small">Passée le {{ new Date(order.created_at).toLocaleString() }}</p>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge rounded-pill px-4 py-2 fs-6" :class="getStatusBadge(order.status)">
                        {{ getStatusLabel(order.status) }}
                    </span>
                    <div class="dropdown">
                        <button class="btn btn-outline-primary rounded-pill px-4 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            Changer le statut
                        </button>
                        <form :action="`/admin/orders/${order.id}/status`" method="POST" class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-2">
                            <input type="hidden" name="_token" :value="document.querySelector('meta[name=csrf-token]')?.content">
                            <button type="submit" name="status" value="pending" class="dropdown-item rounded-2 mb-1">En attente</button>
                            <button type="submit" name="status" value="shipped" class="dropdown-item rounded-2 mb-1">Expédié</button>
                            <button type="submit" name="status" value="completed" class="dropdown-item rounded-2 mb-1 text-success">Livré</button>
                            <button type="submit" name="status" value="cancelled" class="dropdown-item rounded-2 text-danger">Annuler</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left: Order Items -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <h5 class="fw-bold mb-4 d-flex align-items-center gap-2">
                        <Package :size="20" class="text-primary" /> Articles
                    </h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="bg-light">
                                <tr class="text-muted small uppercase">
                                    <th class="border-0 p-3">Produit</th>
                                    <th class="border-0 p-3 text-center">Quantité</th>
                                    <th class="border-0 p-3 text-end">Prix</th>
                                    <th class="border-0 p-3 text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in (typeof order.items === 'string' ? JSON.parse(order.items) : order.items)" :key="item.id">
                                    <td class="p-3">
                                        <div class="fw-bold">{{ item.name }}</div>
                                        <small class="text-muted">{{ item.category }}</small>
                                    </td>
                                    <td class="p-3 text-center">{{ item.quantity }}</td>
                                    <td class="p-3 text-end">{{ item.price.toLocaleString() }} FCFA</td>
                                    <td class="p-3 text-end fw-bold">{{ (item.price * item.quantity).toLocaleString() }} FCFA</td>
                                </tr>
                            </tbody>
                            <tfoot class="border-top">
                                <tr>
                                    <td colspan="3" class="p-3 text-end fw-bold">Sous-total</td>
                                    <td class="p-3 text-end fw-bold">{{ order.total.toLocaleString() }} FCFA</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="p-3 text-end fw-bold">Livraison</td>
                                    <td class="p-3 text-end text-success fw-bold">Gratuit</td>
                                </tr>
                                <tr class="fs-5">
                                    <td colspan="3" class="p-3 text-end fw-bold text-primary">Total</td>
                                    <td class="p-3 text-end fw-bold text-primary">{{ order.total.toLocaleString() }} FCFA</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Timeline / History (Placeholder) -->
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h5 class="fw-bold mb-4 d-flex align-items-center gap-2">
                        <Clock :size="20" class="text-primary" /> Historique
                    </h5>
                    <div class="position-relative ps-4 border-start ms-2">
                        <div class="mb-4 position-relative">
                            <span class="position-absolute translate-middle start-0 bg-success rounded-circle p-1" style="left: -17px !important; top: 10px"><CheckCircle2 :size="12" class="text-white" /></span>
                            <div class="fw-bold small">Commande payée</div>
                            <p class="text-muted x-small mb-0">La transaction a été validée avec succès.</p>
                        </div>
                        <div class="position-relative">
                            <span class="position-absolute translate-middle start-0 bg-primary rounded-circle p-1" style="left: -17px !important; top: 10px"><CheckCircle2 :size="12" class="text-white" /></span>
                            <div class="fw-bold small">Commande créée</div>
                            <p class="text-muted x-small mb-0">Enregistré dans le système.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Customer Info -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <h5 class="fw-bold mb-4 d-flex align-items-center gap-2">
                        <User :size="20" class="text-primary" /> Client
                    </h5>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-light p-3 rounded-circle text-primary">
                            <User :size="24" />
                        </div>
                        <div>
                            <div class="fw-bold">{{ order.customer_firstName }} {{ order.customer_lastName }}</div>
                            <div class="text-muted small">{{ order.customer_phone }}</div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <h5 class="fw-bold mb-4 d-flex align-items-center gap-2">
                        <MapPin :size="20" class="text-primary" /> Livraison
                    </h5>
                    <div class="mb-3">
                        <div class="small fw-bold text-uppercase text-muted mb-1">Ville</div>
                        <div class="fw-bold">{{ order.customer_city }}</div>
                    </div>
                    <div>
                        <div class="small fw-bold text-uppercase text-muted mb-1">Adresse</div>
                        <p class="mb-0 small">{{ order.customer_address }}</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h5 class="fw-bold mb-4 d-flex align-items-center gap-2">
                        <CreditCard :size="20" class="text-primary" /> Paiement
                    </h5>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small">Méthode</span>
                        <span class="fw-bold small">{{ order.paymentMethod }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted small">Statut</span>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 small">Payé</span>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.x-small { font-size: 0.75rem; }
.hover-text-primary:hover { color: var(--primary) !important; }
.transition-all { transition: all 0.2s ease-in-out; }
</style>
