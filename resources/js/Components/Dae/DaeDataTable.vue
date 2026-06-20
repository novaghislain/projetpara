<template>
    <div class="dae-data-table">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th v-for="col in columns" :key="col.key" class="small text-uppercase text-muted"
                            :class="col.class" :style="col.width ? { width: col.width } : {}">
                            {{ col.label }}
                        </th>
                        <th v-if="actions" class="small text-uppercase text-muted" style="width:80px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="!rows.length">
                        <td :colspan="columns.length + (actions ? 1 : 0)" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            Aucune donnée
                        </td>
                    </tr>
                    <tr v-for="(row, i) in rows" :key="row.id || i"
                        :class="{ 'cursor-pointer': rowClickable }" @click="rowClickable && $emit('row-click', row)">
                        <td v-for="col in columns" :key="col.key" class="small">
                            <slot :name="`cell-${col.key}`" :row="row" :value="resolve(row, col.key)">
                                {{ resolve(row, col.key) }}
                            </slot>
                        </td>
                        <td v-if="actions" @click.stop>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li v-for="action in actions" :key="action.key">
                                        <button class="dropdown-item small" :class="action.danger ? 'text-danger' : ''"
                                                @click="$emit('action', { action: action.key, row })">
                                            <i :class="`bi ${action.icon} me-2`"></i>{{ action.label }}
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-if="totalPages > 1" class="d-flex justify-content-between align-items-center mt-3">
            <small class="text-muted">{{ rows.length }} résultat(s)</small>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: currentPage <= 1 }">
                        <button class="page-link" @click="$emit('page-change', currentPage - 1)">‹</button>
                    </li>
                    <li v-for="p in totalPages" :key="p" class="page-item" :class="{ active: p === currentPage }">
                        <button class="page-link" @click="$emit('page-change', p)">{{ p }}</button>
                    </li>
                    <li class="page-item" :class="{ disabled: currentPage >= totalPages }">
                        <button class="page-link" @click="$emit('page-change', currentPage + 1)">›</button>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        columns:     { type: Array, required: true },
        rows:        { type: Array, default: () => [] },
        actions:     { type: Array, default: null },
        currentPage: { type: Number, default: 1 },
        totalPages:  { type: Number, default: 1 },
        rowClickable:{ type: Boolean, default: false },
    },
    emits: ['row-click', 'action', 'page-change'],
    methods: {
        resolve(obj, path) {
            return path.split('.').reduce((acc, part) => acc?.[part] ?? '', obj);
        },
    },
};
</script>

<style scoped>
.cursor-pointer { cursor: pointer; }
.cursor-pointer:hover { background-color: rgba(13,110,253,0.03); }
</style>
