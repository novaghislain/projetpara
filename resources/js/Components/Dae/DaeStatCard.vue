<template>
    <div
        class="dae-stat-card"
        role="status"
        :aria-label="`${label} : ${displayValue}`"
    >
        <div class="dae-stat-icon" :class="`dae-stat-icon--${color}`">
            <i :class="`bi ${icon}`" aria-hidden="true"></i>
        </div>
        <div class="dae-stat-body">
            <span class="dae-stat-label">{{ label }}</span>
            <span class="dae-stat-value">{{ displayValue }}</span>
            <span
                v-if="trend !== null"
                class="dae-stat-trend"
                :class="trend >= 0 ? 'dae-stat-trend--up' : 'dae-stat-trend--down'"
            >
                <i :class="trend >= 0 ? 'bi-arrow-up-short' : 'bi-arrow-down-short'"></i>
                {{ Math.abs(trend) }}%
            </span>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    icon:  { type: String, required: true },
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
    color: { type: String, default: 'primary' },
    trend: { type: Number, default: null },
})

const displayValue = computed(() => {
    if (props.value == null || props.value === '') return '—'
    const num = Number(props.value)
    return Number.isNaN(num) ? props.value : num.toLocaleString('fr-FR')
})
</script>

<style scoped>
.dae-stat-card {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.75rem;
    background: #fff;
    border: 1px solid var(--bs-border-color, #e9ecef);
    border-radius: 10px;
    transition: box-shadow 0.2s ease, transform 0.2s ease;
    height: 100%;
}
.dae-stat-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.07);
    transform: translateY(-1px);
}
.dae-stat-icon {
    width: 42px; height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
}
.dae-stat-icon--primary  { background: rgba(255,121,0,0.12); color: #ff7900; }
.dae-stat-icon--success  { background: rgba(16,185,129,0.12); color: #10b981; }
.dae-stat-icon--info     { background: rgba(59,130,246,0.12); color: #3b82f6; }
.dae-stat-icon--warning  { background: rgba(245,158,11,0.12); color: #f59e0b; }
.dae-stat-icon--danger   { background: rgba(239,68,68,0.12); color: #ef4444; }
.dae-stat-icon--secondary{ background: rgba(107,114,128,0.12); color: #6b7280; }

.dae-stat-body {
    display: flex;
    flex-direction: column;
    min-width: 0;
    line-height: 1.2;
}
.dae-stat-label {
    font-size: 0.72rem;
    color: var(--bs-secondary-color);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    font-weight: 600;
}
.dae-stat-value {
    font-size: 1.15rem;
    font-weight: 700;
    line-height: 1.3;
    color: var(--bs-body-color, #212529);
}
.dae-stat-trend {
    font-size: 0.7rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.1rem;
}
.dae-stat-trend--up  { color: #10b981; }
.dae-stat-trend--down{ color: #ef4444; }
</style>
