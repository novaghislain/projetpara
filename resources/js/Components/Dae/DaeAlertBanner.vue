<template>
    <div class="dae-alert-banner">
        <div v-if="!alerts.length" class="dae-alert-empty">
            <i class="bi bi-check-circle text-success"></i>
            <span>Aucune alerte</span>
        </div>
        <div
            v-for="(alert, i) in alerts"
            :key="i"
            class="dae-alert-item"
            :class="`dae-alert-item--${alert.type}`"
            role="alert"
        >
            <i :class="`bi ${typeIcon(alert.type)} dae-alert-icon`"></i>
            <span class="dae-alert-msg">{{ alert.message }}</span>
            <button
                type="button"
                class="dae-alert-close"
                :aria-label="`Fermer l'alerte`"
                @click="dismiss(i)"
            >
                <i class="bi bi-x"></i>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
    alerts: { type: Array, default: () => [] },
})

const localAlerts = ref([...props.alerts])

function typeIcon(type) {
    return { warning: 'bi-exclamation-triangle', danger: 'bi-x-circle', info: 'bi-info-circle', success: 'bi-check-circle' }[type] || 'bi-bell'
}

function dismiss(index) {
    localAlerts.value.splice(index, 1)
}
</script>

<style scoped>
.dae-alert-banner {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}
.dae-alert-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 1.5rem;
    color: var(--bs-secondary-color);
    font-size: 0.82rem;
}
.dae-alert-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.7rem;
    border-radius: 8px;
    font-size: 0.8rem;
    line-height: 1.4;
    position: relative;
}
.dae-alert-item--warning {
    background: #fef3c7;
    color: #92400e;
    border-left: 3px solid #f59e0b;
}
.dae-alert-item--danger {
    background: #fee2e2;
    color: #b91c1c;
    border-left: 3px solid #ef4444;
}
.dae-alert-item--info {
    background: #dbeafe;
    color: #1e40af;
    border-left: 3px solid #3b82f6;
}
.dae-alert-item--success {
    background: #d1fae5;
    color: #065f46;
    border-left: 3px solid #10b981;
}
.dae-alert-icon {
    font-size: 1rem;
    flex-shrink: 0;
}
.dae-alert-msg {
    flex: 1;
    min-width: 0;
}
.dae-alert-close {
    background: none;
    border: none;
    padding: 0.1rem 0.3rem;
    color: inherit;
    opacity: 0.6;
    cursor: pointer;
    border-radius: 4px;
    flex-shrink: 0;
    transition: opacity 0.15s ease;
}
.dae-alert-close:hover {
    opacity: 1;
    background: rgba(0,0,0,0.06);
}
</style>
