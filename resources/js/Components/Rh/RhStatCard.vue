<template>
    <div
        class="rh-stat-card"
        role="status"
        :aria-label="`${label} : ${displayValue}`"
    >
        <div class="rh-stat-icon" :class="`rh-stat-icon--${color}`">
            <i :class="`bi ${icon}`" aria-hidden="true"></i>
        </div>
        <div class="rh-stat-body">
            <span class="rh-stat-label">{{ label }}</span>
            <span class="rh-stat-value">{{ displayValue }}</span>
            <span
                v-if="trend !== null"
                class="rh-stat-trend"
                :class="trend >= 0 ? 'rh-stat-trend--up' : 'rh-stat-trend--down'"
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
/* ═══════════════════════════════════════════════════════════════
   RhStatCard — Carte de statistique RH
   ═══════════════════════════════════════════════════════════════ */

.rh-stat-card {
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

.rh-stat-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.07);
    transform: translateY(-1px);
}

/* ─── Icône ──────────────────────────────────────────────────── */
.rh-stat-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
}

/* Variantes de couleur */
.rh-stat-icon--primary {
    background: rgba(255, 121, 0, 0.12);
    color: #ff7900;
}

.rh-stat-icon--success {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
}

.rh-stat-icon--info {
    background: rgba(59, 130, 246, 0.12);
    color: #3b82f6;
}

.rh-stat-icon--warning {
    background: rgba(245, 158, 11, 0.12);
    color: #f59e0b;
}

.rh-stat-icon--danger {
    background: rgba(239, 68, 68, 0.12);
    color: #ef4444;
}

/* ─── Corps ──────────────────────────────────────────────────── */
.rh-stat-body {
    display: flex;
    flex-direction: column;
    min-width: 0;
    line-height: 1.2;
}

.rh-stat-label {
    font-size: 0.72rem;
    color: var(--bs-secondary-color, #6c757d);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    font-weight: 600;
}

.rh-stat-value {
    font-size: 1.15rem;
    font-weight: 700;
    line-height: 1.3;
    color: var(--bs-body-color, #212529);
}

/* ─── Trend ───────────────────────────────────────────────────── */
.rh-stat-trend {
    font-size: 0.7rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.1rem;
}

.rh-stat-trend--up  { color: #10b981; }
.rh-stat-trend--down { color: #ef4444; }
</style>
