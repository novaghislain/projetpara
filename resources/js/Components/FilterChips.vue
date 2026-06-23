<script setup>
defineProps({
    filters: { type: Array, default: () => [] }
});

const emit = defineEmits(['remove', 'clearAll']);
</script>

<template>
    <div v-if="filters.length" class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <span class="text-muted" style="font-size:12px;font-weight:500;text-transform:uppercase;letter-spacing:0.03em;">
            <i class="bi-funnel me-1"></i> Filtres :
        </span>
        <span v-for="filter in filters" :key="filter.key"
              class="dp-chip"
              :title="`${filter.label}: ${filter.value}`">
            <span class="dp-chip-label">{{ filter.label }}:</span>
            <span class="dp-chip-value">{{ filter.value }}</span>
            <button class="dp-chip-remove" @click="emit('remove', filter.key)" title="Retirer ce filtre">
                &times;
            </button>
        </span>
        <button v-if="filters.length > 1" class="dp-chip-clear btn btn-sm btn-link text-muted p-0"
                @click="emit('clearAll')" style="font-size:12px;text-decoration:none;">
            <i class="bi-x-circle me-1"></i>Tout effacer
        </button>
    </div>
</template>

<style scoped>
.dp-chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 3px 8px 3px 10px;
    font-size: 12px;
    transition: all 0.2s ease;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}
.dp-chip:hover {
    border-color: #FF7900;
    background: rgba(255,121,0,0.03);
}
.dp-chip-label {
    color: #64748b;
    font-weight: 500;
}
.dp-chip-value {
    color: #1e293b;
    font-weight: 600;
}
.dp-chip-remove {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 16px;
    height: 16px;
    border: none;
    background: #f1f5f9;
    border-radius: 50%;
    color: #94a3b8;
    font-size: 12px;
    line-height: 1;
    cursor: pointer;
    padding: 0;
    transition: all 0.2s ease;
    margin-left: 2px;
}
.dp-chip-remove:hover {
    background: #fee2e2;
    color: #ef4444;
}
.dp-chip-clear:hover {
    color: #ef4444 !important;
}
</style>
