<script setup>
import { ref, computed } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const props = defineProps({
    orders: { type: Array, required: true },
});

const searchQuery = ref('');
const statusFilter = ref('All');

const statusConfig = {
    'Livrée': { cls: 'status-livree', label: 'Livrée', icon: 'bi-check-circle-fill' },
    'Annulée': { cls: 'status-annulee', label: 'Annulée', icon: 'bi-x-circle-fill' },
};

const filteredOrders = computed(() => {
    return props.orders.filter(order => {
        const matchesSearch =
            (order.reference && order.reference.toLowerCase().includes(searchQuery.value.toLowerCase())) ||
            (order.service?.nom && order.service.nom.toLowerCase().includes(searchQuery.value.toLowerCase())) ||
            (order.client?.name && order.client.name.toLowerCase().includes(searchQuery.value.toLowerCase()));

        const matchesStatus = statusFilter.value === 'All' || order.statut === statusFilter.value;

        return matchesSearch && matchesStatus;
    });
});

const totalLivrees = computed(() => props.orders.filter(o => o.statut === 'Livrée').length);
const totalAnnulees = computed(() => props.orders.filter(o => o.statut === 'Annulée').length);
</script>

<template>
    <GelLayout page-title="Archives des Commandes">
        <div>
            <!-- Page header iSupplier style -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h5 fw-bold mb-1" style="font-family: 'Outfit', sans-serif; color: #1a1a1a;">
                        <i class="bi-archive me-2" style="color: #FF7900;"></i>Commandes Archivées
                    </h1>
                    <p class="small text-muted mb-0">Historique des commandes livrées et annulées</p>
                </div>
                <a href="/admin/catalogue/orders" class="btn btn-outline-secondary btn-sm">
                    <i class="bi-arrow-left me-1"></i>Retour au Kanban
                </a>
            </div>

            <!-- Stat cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #e8f5e9; color: #198754;">
                            <i class="bi-archive-fill"></i>
                        </div>
                        <div>
                            <div class="stat-value">{{ orders.length }}</div>
                            <div class="stat-label">Total archivées</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #e8f5e9; color: #198754;">
                            <i class="bi-check-circle-fill"></i>
                        </div>
                        <div>
                            <div class="stat-value">{{ totalLivrees }}</div>
                            <div class="stat-label">Livrées</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #fdecea; color: #cd3c14;">
                            <i class="bi-x-circle-fill"></i>
                        </div>
                        <div>
                            <div class="stat-value">{{ totalAnnulees }}</div>
                            <div class="stat-label">Annulées</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtres -->
            <div class="isup-card mb-4">
                <div class="isup-card-header">
                    <i class="bi-funnel me-2"></i>Filtres de recherche
                </div>
                <div class="isup-card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text isup-input-addon">
                                    <i class="bi-search text-muted" style="font-size: 13px;"></i>
                                </span>
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    class="form-control isup-input"
                                    placeholder="Rechercher par référence, client ou service..."
                                />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select v-model="statusFilter" class="form-select isup-input">
                                <option value="All">Tous les statuts</option>
                                <option value="Livrée">Livrée</option>
                                <option value="Annulée">Annulée</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="isup-card">
                <div class="isup-card-header d-flex align-items-center justify-content-between">
                    <span><i class="bi-table me-2"></i>Résultats ({{ filteredOrders.length }})</span>
                </div>
                <div class="table-responsive">
                    <table class="table isup-table mb-0">
                        <thead>
                            <tr>
                                <th>Référence</th>
                                <th>Service</th>
                                <th>Client</th>
                                <th>Responsable</th>
                                <th class="text-end">Montant</th>
                                <th>Date</th>
                                <th class="text-center">Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in filteredOrders" :key="order.id">
                                <td>
                                    <span class="isup-ref">{{ order.reference }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold" style="font-size: 13px; color: #1a1a1a;">{{ order.service?.nom || 'N/A' }}</div>
                                    <div class="text-muted" style="font-size: 11px;">{{ order.category?.nom || '' }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="client-avatar">
                                            {{ order.client?.name?.charAt(0).toUpperCase() || 'C' }}
                                        </div>
                                        <div>
                                            <div style="font-size: 13px; font-weight: 500; color: #1a1a1a;">{{ order.client?.name || 'Client inconnu' }}</div>
                                            <div class="text-muted" style="font-size: 11px;">{{ order.client?.email || '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span v-if="order.responsable" class="resp-badge">
                                        <i class="bi-person-badge me-1"></i>
                                        {{ order.responsable.name }}
                                    </span>
                                    <span v-else class="text-muted" style="font-size: 12px;">Non assigné</span>
                                </td>
                                <td class="text-end fw-bold" style="font-size: 13px;">
                                    <span v-if="order.montant_estime_fcfa">
                                        {{ Number(order.montant_estime_fcfa).toLocaleString('fr-FR') }} FCFA
                                    </span>
                                    <span v-else class="text-muted">—</span>
                                </td>
                                <td class="text-muted" style="font-size: 12px;">
                                    {{ new Date(order.created_at).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' }) }}
                                </td>
                                <td class="text-center">
                                    <span :class="['isup-status-badge', statusConfig[order.statut]?.cls || 'status-default']">
                                        <i :class="statusConfig[order.statut]?.icon || 'bi-circle'" class="me-1"></i>
                                        {{ order.statut }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a :href="'/admin/catalogue/orders/' + order.id" class="btn btn-sm btn-outline-primary">
                                        Détails <i class="bi-chevron-right ms-1"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr v-if="filteredOrders.length === 0">
                                <td colspan="8" class="text-center py-5">
                                    <i class="bi-inbox" style="font-size: 2.5rem; color: #ccc;"></i>
                                    <p class="mb-0 fw-semibold mt-2" style="font-size: 14px;">Aucune commande archivée trouvée</p>
                                    <p class="small text-muted">Essayez de modifier vos critères de recherche ou filtres.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<style scoped>
/* ── Stat cards ─────────────────────────────────────── */
.stat-card {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 2px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 16px;
}
.stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.stat-value {
    font-size: 22px;
    font-weight: 700;
    color: #1a1a1a;
    line-height: 1;
}
.stat-label {
    font-size: 12px;
    color: #888;
    margin-top: 3px;
}

/* ── iSupplier card ─────────────────────────────────── */
.isup-card {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 2px;
    overflow: hidden;
}
.isup-card-header {
    background: #f8f8f8;
    border-bottom: 1px solid #e5e5e5;
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 600;
    color: #333;
}
.isup-card-body {
    padding: 16px;
}

/* ── Inputs ─────────────────────────────────────────── */
.isup-input {
    border-radius: 0 !important;
    font-size: 13px;
    border: 1px solid #d8d8d8;
}
.isup-input:focus {
    border-color: #FF7900;
    box-shadow: 0 0 0 2px rgba(255, 121, 0, 0.12);
}
.isup-input-addon {
    border-radius: 0 !important;
    background: #f5f5f5;
    border-color: #d8d8d8;
    border-right: none;
}

/* ── Table ──────────────────────────────────────────── */
.isup-table { font-size: 13px; }
.isup-table thead th {
    background: #efefef;
    color: #444;
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    border-color: #d8d8d8;
    padding: 10px 14px;
}
.isup-table td {
    padding: 10px 14px;
    vertical-align: middle;
    border-color: #ebebeb;
}
.isup-table tbody tr:hover { background: #fdf5ec; }

/* ── Ref ────────────────────────────────────────────── */
.isup-ref {
    font-family: monospace;
    font-size: 12px;
    font-weight: 700;
    color: #FF7900;
    background: #fff3e0;
    padding: 2px 8px;
    border-radius: 2px;
}

/* ── Client avatar ──────────────────────────────────── */
.client-avatar {
    width: 28px;
    height: 28px;
    background: #163A5E;
    color: #fff;
    border-radius: 2px;
    font-size: 11px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* ── Resp badge ─────────────────────────────────────── */
.resp-badge {
    background: #f0f0f0;
    color: #444;
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 2px;
    display: inline-flex;
    align-items: center;
}

/* ── Status badges ──────────────────────────────────── */
.isup-status-badge {
    display: inline-flex;
    align-items: center;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 2px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.status-livree {
    background: #e8f5e9;
    color: #198754;
    border: 1px solid #c8e6c9;
}
.status-annulee {
    background: #fdecea;
    color: #cd3c14;
    border: 1px solid #f5c6c0;
}
.status-default {
    background: #f0f0f0;
    color: #555;
    border: 1px solid #ddd;
}
</style>
