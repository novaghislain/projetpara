<template>
    <div class="dae-events">
        <div v-if="!events.length" class="dae-events-empty">
            <i class="bi bi-calendar-check"></i>
            <span>Aucun événement aujourd'hui</span>
        </div>
        <div
            v-for="evt in events"
            :key="evt.id"
            class="dae-event-item"
            :style="{ borderLeftColor: evt.color || '#0d6efd' }"
        >
            <div class="dae-event-time">
                <span>{{ evt.time || '--:--' }}</span>
            </div>
            <div class="dae-event-body">
                <div class="dae-event-title">{{ evt.title }}</div>
                <div class="dae-event-meta">
                    <i :class="`bi ${typeIcon(evt.type)} me-1`"></i>{{ evt.type }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    events: { type: Array, default: () => [] },
})

function typeIcon(type) {
    return ({ rdv: 'bi-person', reunion: 'bi-people', appel: 'bi-telephone', echeance: 'bi-alarm', autre: 'bi-calendar' })[type] || 'bi-calendar'
}
</script>

<style scoped>
.dae-events { display: flex; flex-direction: column; gap: 0.4rem; }
.dae-events-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    color: var(--bs-secondary-color);
    gap: 0.5rem;
}
.dae-events-empty i { font-size: 2rem; opacity: 0.4; }
.dae-event-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 0.6rem;
    border-radius: 6px;
    border-left: 3px solid;
    background: #fafafa;
    transition: background 0.15s ease;
}
.dae-event-item:hover { background: #f0f4ff; }
.dae-event-time {
    min-width: 44px;
    text-align: center;
    flex-shrink: 0;
}
.dae-event-time span {
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--bs-body-color);
    background: #fff;
    padding: 0.2rem 0.4rem;
    border-radius: 4px;
    border: 1px solid var(--bs-border-color);
}
.dae-event-body { flex: 1; min-width: 0; }
.dae-event-title {
    font-size: 0.82rem;
    font-weight: 600;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.dae-event-meta {
    font-size: 0.72rem;
    color: var(--bs-secondary-color);
    margin-top: 0.1rem;
}
</style>
