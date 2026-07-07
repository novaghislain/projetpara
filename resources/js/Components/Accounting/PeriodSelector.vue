<template>
    <div class="period-selector d-inline-flex align-items-center gap-2">
        <label v-if="label" class="form-label small mb-0 text-nowrap">{{ label }}</label>
        <select class="form-select form-select-sm" style="width: auto;" :value="periodType" @change="$emit('update:periodType', $event.target.value)">
            <option value="monthly">Mensuel</option>
            <option value="quarterly">Trimestriel</option>
            <option value="yearly">Annuel</option>
        </select>
        <select class="form-select form-select-sm" style="width: auto;" :value="year" @change="$emit('update:year', Number($event.target.value))">
            <option v-for="y in availableYears" :key="y" :value="y">{{ y }}</option>
        </select>
        <select v-if="periodType === 'monthly'"
            class="form-select form-select-sm" style="width: auto;"
            :value="month" @change="$emit('update:month', Number($event.target.value))">
            <option v-for="(label, idx) in months" :key="idx" :value="idx + 1">{{ label }}</option>
        </select>
        <select v-if="periodType === 'quarterly'"
            class="form-select form-select-sm" style="width: auto;"
            :value="quarter" @change="$emit('update:quarter', Number($event.target.value))">
            <option :value="1">T1 (Jan-Mar)</option>
            <option :value="2">T2 (Avr-Juin)</option>
            <option :value="3">T3 (Jui-Sep)</option>
            <option :value="4">T4 (Oct-Déc)</option>
        </select>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    label: { type: String, default: 'Période' },
    periodType: { type: String, default: 'monthly' },
    year: { type: Number, default: () => new Date().getFullYear() },
    month: { type: Number, default: () => new Date().getMonth() + 1 },
    quarter: { type: Number, default: 1 },
});

defineEmits(['update:periodType', 'update:year', 'update:month', 'update:quarter']);

const months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

const availableYears = computed(() => {
    const current = new Date().getFullYear();
    return Array.from({ length: 5 }, (_, i) => current - 2 + i);
});
</script>
