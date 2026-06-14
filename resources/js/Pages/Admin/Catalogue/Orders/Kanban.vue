<script setup>
import { ref, computed } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const props = defineProps({
    kanban:  { type: Object, required: true },
    statuts: { type: Array,  required: true },
    team:    { type: Array,  required: true },
});

const localKanban = ref(JSON.parse(JSON.stringify(props.kanban)));
const dragging    = ref(null);
const overColumn  = ref(null);
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const statusConfig = {
    'Nouvelle Demande': { color: '#163A5E', bg: '#eef5fb', badge: '#163A5E', icon: 'bi-inbox' },
    'En cours':         { color: '#FF7900', bg: '#fff6ee', badge: '#FF7900', icon: 'bi-arrow-repeat' },
    'En attente client':{ color: '#8c6d00', bg: '#fffce8', badge: '#c49900', icon: 'bi-clock' },
    'Livrée':           { color: '#198754', bg: '#f0faf4', badge: '#198754', icon: 'bi-check-circle' },
    'Annulée':          { color: '#cd3c14', bg: '#fdf2f0', badge: '#cd3c14', icon: 'bi-x-circle' },
};

const totalOrders = computed(() => {
    return Object.values(localKanban.value).reduce((sum, col) => sum + col.length, 0);
});

// ── Drag & Drop ───────────────────────────────────────────────────────────

function onDragStart(order, fromStatut) {
    dragging.value = { order, fromStatut };
}

function onDragOver(statut) {
    overColumn.value = statut;
}

async function onDrop(toStatut) {
    if (!dragging.value || dragging.value.fromStatut === toStatut) {
        dragging.value = null;
        overColumn.value = null;
        return;
    }

    const { order, fromStatut } = dragging.value;

    // Mise à jour locale immédiate (optimistic UI)
    const fromCol = localKanban.value[fromStatut];
    const idx = fromCol.findIndex(o => o.id === order.id);
    if (idx !== -1) {
        const [moved] = fromCol.splice(idx, 1);
        moved.statut = toStatut;
        localKanban.value[toStatut].unshift(moved);
    }

    // Appel API avec fetch
    try {
        await fetch(`/admin/catalogue/orders/${order.id}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ statut: toStatut }),
        });
    } catch (e) {
        console.error('Erreur mise à jour statut:', e);
    }

    dragging.value  = null;
    overColumn.value = null;
}

function onDragEnd() {
    dragging.value  = null;
    overColumn.value = null;
}
</script>

<template>
    <GelLayout page-title="Gestion des Commandes">
        <div style="min-height: calc(100vh - 88px);">
            <!-- Barre d'actions -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <h1 class="h5 fw-bold mb-0" style="font-family: 'Outfit', sans-serif; color: #1a1a1a;">
                            <i class="bi-kanban me-2" style="color: #FF7900;"></i>Tableau Kanban
                        </h1>
                        <p class="small text-muted mb-0">{{ totalOrders }} commande(s) en cours de traitement</p>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="/admin/catalogue/orders/archives" class="btn btn-outline-secondary btn-sm">
                        <i class="bi-archive me-1"></i>Archives
                    </a>
                    <a href="/admin/catalogue/services" class="btn btn-primary btn-sm">
                        <i class="bi-grid me-1"></i>Catalogue
                    </a>
                </div>
            </div>

            <!-- Kanban Board -->
            <div class="d-flex gap-3 overflow-auto pb-4 kanban-board" style="min-height: 560px; align-items: flex-start;">
                <div
                    v-for="statut in statuts"
                    :key="statut"
                    class="kanban-column flex-shrink-0"
                    :class="{ 'is-drag-over': overColumn === statut && dragging }"
                    @dragover.prevent="onDragOver(statut)"
                    @drop="onDrop(statut)"
                >
                    <!-- En-tête colonne -->
                    <div class="kanban-col-header d-flex align-items-center justify-content-between"
                         :style="{ borderTopColor: statusConfig[statut]?.color || '#666' }">
                        <div class="d-flex align-items-center gap-2">
                            <i :class="statusConfig[statut]?.icon || 'bi-circle'"
                               :style="{ color: statusConfig[statut]?.color || '#666', fontSize: '15px' }"></i>
                            <span class="fw-bold" style="font-size: 13px; color: #1a1a1a;">{{ statut }}</span>
                        </div>
                        <span class="kanban-count-badge"
                              :style="{ background: statusConfig[statut]?.color || '#666' }">
                            {{ localKanban[statut]?.length || 0 }}
                        </span>
                    </div>

                    <!-- Cartes -->
                    <div class="kanban-col-body d-flex flex-column gap-2">
                        <div
                            v-for="order in localKanban[statut]"
                            :key="order.id"
                            draggable="true"
                            @dragstart="onDragStart(order, statut)"
                            @dragend="onDragEnd"
                            class="kanban-card"
                            :class="{ 'is-dragging': dragging?.order?.id === order.id }"
                        >
                            <!-- Référence + Date -->
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="kanban-ref" :style="{ color: statusConfig[statut]?.color || '#FF7900' }">
                                    {{ order.reference }}
                                </span>
                                <span class="kanban-date">
                                    {{ new Date(order.created_at).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' }) }}
                                </span>
                            </div>

                            <!-- Nom service -->
                            <p class="kanban-service-name mb-2">{{ order.service?.nom }}</p>

                            <!-- Client -->
                            <div v-if="order.client" class="d-flex align-items-center gap-2 mb-2">
                                <div class="kanban-avatar">
                                    {{ order.client.name?.charAt(0).toUpperCase() || 'C' }}
                                </div>
                                <span class="kanban-client-name">{{ order.client.name }}</span>
                            </div>

                            <!-- Montant -->
                            <div v-if="order.montant_estime_fcfa" class="kanban-amount">
                                {{ Number(order.montant_estime_fcfa).toLocaleString('fr-FR') }} FCFA
                            </div>

                            <!-- Responsable -->
                            <div v-if="order.responsable" class="kanban-responsable">
                                <i class="bi-person-badge"></i>
                                {{ order.responsable.name }}
                            </div>

                            <!-- Action -->
                            <div class="kanban-card-footer">
                                <a :href="'/admin/catalogue/orders/' + order.id" class="kanban-link">
                                    Voir détails <i class="bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Zone vide -->
                        <div v-if="!localKanban[statut]?.length"
                             class="kanban-empty-zone">
                            <i class="bi-inbox" style="font-size: 20px; color: #ccc;"></i>
                            <div style="font-size: 12px; color: #bbb; margin-top: 6px;">Glisser une commande ici</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<style scoped>
/* ── Kanban Board ───────────────────────────────────── */
.kanban-board {
    user-select: none;
}

.kanban-column {
    width: 272px;
    min-width: 272px;
    display: flex;
    flex-direction: column;
    background: #f4f5f7;
    border-radius: 2px;
    border: 1px solid #e0e0e0;
    overflow: hidden;
    transition: box-shadow 0.15s;
}
.kanban-column.is-drag-over {
    box-shadow: 0 0 0 2px #FF7900;
    background: #fff9f4;
}

/* ── Column header ──────────────────────────────────── */
.kanban-col-header {
    background: #ffffff;
    border-top: 3px solid #ccc;
    padding: 10px 12px;
    border-bottom: 1px solid #e8e8e8;
}

.kanban-count-badge {
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 10px;
    min-width: 24px;
    text-align: center;
}

/* ── Column body ────────────────────────────────────── */
.kanban-col-body {
    padding: 10px;
    flex-grow: 1;
    min-height: 200px;
}

/* ── Card ───────────────────────────────────────────── */
.kanban-card {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 2px;
    padding: 12px;
    cursor: grab;
    transition: box-shadow 0.15s, transform 0.1s;
}
.kanban-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transform: translateY(-1px);
}
.kanban-card.is-dragging {
    opacity: 0.45;
    transform: scale(0.98);
}

.kanban-ref {
    font-size: 12px;
    font-family: monospace;
    font-weight: 700;
}
.kanban-date {
    font-size: 11px;
    color: #999;
}
.kanban-service-name {
    font-size: 13px;
    font-weight: 600;
    color: #1a1a1a;
    line-height: 1.3;
    margin: 0;
}
.kanban-avatar {
    width: 24px;
    height: 24px;
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
.kanban-client-name {
    font-size: 12px;
    color: #555;
}
.kanban-amount {
    font-size: 12px;
    font-weight: 700;
    color: #198754;
    padding: 3px 0;
}
.kanban-responsable {
    font-size: 11px;
    color: #888;
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 2px 0;
}
.kanban-card-footer {
    border-top: 1px solid #f0f0f0;
    margin-top: 8px;
    padding-top: 8px;
}
.kanban-link {
    font-size: 12px;
    color: #FF7900;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.15s;
}
.kanban-link:hover {
    color: #e06700;
}

/* ── Empty zone ─────────────────────────────────────── */
.kanban-empty-zone {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 100px;
    border: 1px dashed #d8d8d8;
    border-radius: 2px;
    background: #fafafa;
}
</style>
