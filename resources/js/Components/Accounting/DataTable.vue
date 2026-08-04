<!--
 * DataTable.vue
 * Tableau de données générique avec tris, colonnes typées
 * (monnaie, date, statut, booléen, nombre) et pagination.
 * Supporte un slot "toolbar" pour les actions en en-tête
 * et un slot "actions" par ligne.
-->
<template>
    <div class="isup-table-wrapper bg-white rounded shadow-sm">
        <!-- Toolbar : slot pour les actions en en-tête (filtres, boutons, etc.) -->
        <div v-if="$slots.toolbar" class="p-3 border-bottom">
            <slot name="toolbar" />
        </div>

        <!-- Tableau principal -->
        <div class="table-responsive">
            <table class="table table-hover isup-table mb-0">
                <!-- En-tête avec colonnes triables -->
                <thead class="table-light">
                    <tr>
                        <th v-for="col in columns" :key="col.key"
                            class="small text-uppercase text-muted px-3 py-2"
                            :class="{ 'cursor-pointer': col.sortable !== false }"
                            :style="{ textAlign: col.type === 'money' || col.type === 'number' ? 'right' : 'left' }"
                            @click="col.sortable !== false && sort(col.key)">
                            <span class="d-inline-flex align-items-center gap-1">
                                {{ col.label }}
                                <!-- Indicateur de direction du tri -->
                                <span v-if="sortKey === col.key" class="text-primary">
                                    {{ sortDir === 'asc' ? '↑' : '↓' }}
                                </span>
                            </span>
                        </th>
                        <th v-if="$slots.actions" class="small text-uppercase text-muted px-3 py-2 text-end">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Lignes de données avec rendu typé par colonne -->
                    <tr v-for="(row, index) in sortedData" :key="row.id || index"
                        class="cursor-pointer"
                        :class="{ 'isup-row-clickable': clickable }"
                        @click="clickable && $emit('rowClick', row)">
                        <td v-for="col in columns" :key="col.key"
                            class="px-3 py-2 small"
                            :style="{ textAlign: col.type === 'money' || col.type === 'number' ? 'right' : 'left' }">
                            <!-- Format monétaire -->
                            <span v-if="col.type === 'money'" class="fw-medium font-mono">
                                {{ formatMoney(row[col.key]) }}
                            </span>
                            <!-- Format date -->
                            <span v-else-if="col.type === 'date'" class="text-muted">
                                {{ formatDate(row[col.key]) }}
                            </span>
                            <!-- Badge de statut coloré -->
                            <span v-else-if="col.type === 'status'"
                                  :class="getStatusBadge(row[col.key])">
                                {{ getStatusLabel(row[col.key]) }}
                            </span>
                            <!-- Booléen : Oui / Non -->
                            <span v-else-if="col.type === 'boolean'">
                                <span :class="row[col.key] ? 'text-success' : 'text-muted'">
                                    {{ row[col.key] ? 'Oui' : 'Non' }}
                                </span>
                            </span>
                            <!-- Nombre formaté -->
                            <span v-else-if="col.type === 'number'" class="text-end">
                                {{ formatNumber(row[col.key]) }}
                            </span>
                            <!-- Texte brut par défaut -->
                            <template v-else>
                                {{ row[col.key] }}
                            </template>
                        </td>
                        <!-- Slot d'actions par ligne -->
                        <td v-if="$slots.actions" class="px-3 py-2 text-end">
                            <slot name="actions" :row="row" />
                        </td>
                    </tr>
                    <!-- Ligne vide quand il n'y a aucune donnée -->
                    <tr v-if="data.length === 0">
                        <td :colspan="columns.length + (!!$slots.actions ? 1 : 0)"
                            class="text-center text-muted py-5 small">
                            <i class="bi bi-inbox me-2"></i>Aucune donnée
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination : navigation entre les pages -->
        <div v-if="pagination" class="d-flex justify-content-between align-items-center px-3 py-2 border-top small">
            <span class="text-muted">
                Page {{ pagination.current_page }} / {{ pagination.last_page }}
                ({{ pagination.total }} résultats)
            </span>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: !pagination.prev_page_url }">
                        <button class="page-link" @click="$emit('pageChange', pagination.current_page - 1)">←</button>
                    </li>
                    <li class="page-item" :class="{ disabled: !pagination.next_page_url }">
                        <button class="page-link" @click="$emit('pageChange', pagination.current_page + 1)">→</button>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

/* Propriétés du composant DataTable */
const props = defineProps({
    columns: { type: Array, required: true },     /* Configuration des colonnes : { key, label, type, sortable } */
    data: { type: Array, default: () => [] },      /* Données à afficher */
    pagination: { type: Object, default: null },   /* Objet de pagination { current_page, last_page, total, ... } */
    clickable: { type: Boolean, default: false },  /* Rend les lignes cliquables (émet rowClick) */
});

const emit = defineEmits(['rowClick', 'pageChange']);

/* État interne du tri */
const sortKey = ref('');   /* Colonne actuellement triée */
const sortDir = ref('asc'); /* Direction du tri */

/* Données triées selon la colonne et la direction actives */
const sortedData = computed(() => {
    if (!sortKey.value) return props.data;
    return [...props.data].sort((a, b) => {
        const va = a[sortKey.value] ?? '';
        const vb = b[sortKey.value] ?? '';
        const cmp = typeof va === 'number' ? va - vb : String(va).localeCompare(String(vb));
        return sortDir.value === 'asc' ? cmp : -cmp;
    });
});

/* Bascule le tri : change la direction si même colonne, sinon nouvelle colonne */
function sort(key) {
    if (sortKey.value === key) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortDir.value = 'asc';
    }
}

/* Formate un montant monétaire avec le symbole F */
function formatMoney(value) {
    return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(value || 0) + ' F';
}

/* Formate un nombre avec le séparateur de milliers français */
function formatNumber(value) {
    return new Intl.NumberFormat('fr-FR').format(value || 0);
}

/* Formate une date au format local français */
function formatDate(value) {
    if (!value) return '';
    const d = new Date(value);
    return d.toLocaleDateString('fr-FR');
}

/* Retourne les classes CSS pour le badge de statut */
function getStatusBadge(status) {
    const map = {
        posted: 'badge bg-success bg-opacity-10 text-success',
        draft: 'badge bg-warning bg-opacity-10 text-warning',
        paid: 'badge bg-info bg-opacity-10 text-info',
        cancelled: 'badge bg-danger bg-opacity-10 text-danger',
        pending: 'badge bg-warning bg-opacity-10 text-warning',
        completed: 'badge bg-primary bg-opacity-10 text-primary',
        validated: 'badge bg-success bg-opacity-10 text-success',
        submitted: 'badge bg-primary bg-opacity-10 text-primary',
        computed: 'badge bg-info bg-opacity-10 text-info',
    };
    return map[status] || 'badge bg-secondary bg-opacity-10 text-secondary';
}

/* Retourne le libellé français d'un statut */
function getStatusLabel(status) {
    const map = {
        posted: 'Validée',
        draft: 'Brouillon',
        paid: 'Payée',
        cancelled: 'Annulée',
        pending: 'En attente',
        completed: 'Terminée',
        validated: 'Validée',
        submitted: 'Soumise',
        computed: 'Calculée',
    };
    return map[status] || status;
}
</script>

<style scoped>
.isup-table th { font-size: 0.7rem; letter-spacing: 0.5px; border-bottom-width: 1px; }
.isup-table td { vertical-align: middle; }
.isup-row-clickable:hover { background-color: #FFF8F0; cursor: pointer; }
.font-mono { font-family: 'Courier New', monospace; }
.cursor-pointer { cursor: pointer; }
</style>
