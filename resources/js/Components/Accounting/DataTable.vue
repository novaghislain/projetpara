<template>
    <div class="isup-table-wrapper bg-white rounded shadow-sm">
        <!-- Toolbar -->
        <div v-if="$slots.toolbar" class="p-3 border-bottom">
            <slot name="toolbar" />
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-hover isup-table mb-0">
                <thead class="table-light">
                    <tr>
                        <th v-for="col in columns" :key="col.key"
                            class="small text-uppercase text-muted px-3 py-2"
                            :class="{ 'cursor-pointer': col.sortable !== false }"
                            :style="{ textAlign: col.type === 'money' || col.type === 'number' ? 'right' : 'left' }"
                            @click="col.sortable !== false && sort(col.key)">
                            <span class="d-inline-flex align-items-center gap-1">
                                {{ col.label }}
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
                    <tr v-for="(row, index) in sortedData" :key="row.id || index"
                        class="cursor-pointer"
                        :class="{ 'isup-row-clickable': clickable }"
                        @click="clickable && $emit('rowClick', row)">
                        <td v-for="col in columns" :key="col.key"
                            class="px-3 py-2 small"
                            :style="{ textAlign: col.type === 'money' || col.type === 'number' ? 'right' : 'left' }">
                            <span v-if="col.type === 'money'" class="fw-medium font-mono">
                                {{ formatMoney(row[col.key]) }}
                            </span>
                            <span v-else-if="col.type === 'date'" class="text-muted">
                                {{ formatDate(row[col.key]) }}
                            </span>
                            <span v-else-if="col.type === 'status'"
                                  :class="getStatusBadge(row[col.key])">
                                {{ getStatusLabel(row[col.key]) }}
                            </span>
                            <span v-else-if="col.type === 'boolean'">
                                <span :class="row[col.key] ? 'text-success' : 'text-muted'">
                                    {{ row[col.key] ? 'Oui' : 'Non' }}
                                </span>
                            </span>
                            <span v-else-if="col.type === 'number'" class="text-end">
                                {{ formatNumber(row[col.key]) }}
                            </span>
                            <template v-else>
                                {{ row[col.key] }}
                            </template>
                        </td>
                        <td v-if="$slots.actions" class="px-3 py-2 text-end">
                            <slot name="actions" :row="row" />
                        </td>
                    </tr>
                    <tr v-if="data.length === 0">
                        <td :colspan="columns.length + (!!$slots.actions ? 1 : 0)"
                            class="text-center text-muted py-5 small">
                            <i class="bi bi-inbox me-2"></i>Aucune donnée
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
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

const props = defineProps({
    columns: { type: Array, required: true },
    data: { type: Array, default: () => [] },
    pagination: { type: Object, default: null },
    clickable: { type: Boolean, default: false },
});

const emit = defineEmits(['rowClick', 'pageChange']);

const sortKey = ref('');
const sortDir = ref('asc');

const sortedData = computed(() => {
    if (!sortKey.value) return props.data;
    return [...props.data].sort((a, b) => {
        const va = a[sortKey.value] ?? '';
        const vb = b[sortKey.value] ?? '';
        const cmp = typeof va === 'number' ? va - vb : String(va).localeCompare(String(vb));
        return sortDir.value === 'asc' ? cmp : -cmp;
    });
});

function sort(key) {
    if (sortKey.value === key) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortDir.value = 'asc';
    }
}

function formatMoney(value) {
    return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(value || 0) + ' F';
}

function formatNumber(value) {
    return new Intl.NumberFormat('fr-FR').format(value || 0);
}

function formatDate(value) {
    if (!value) return '';
    const d = new Date(value);
    return d.toLocaleDateString('fr-FR');
}

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
