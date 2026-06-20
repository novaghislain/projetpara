<template>
    <div class="dae-activity">
        <div v-if="!activities.length" class="dae-activity-empty">
            <i class="bi bi-inbox"></i>
            <span>Aucune activité récente</span>
        </div>
        <div
            v-for="a in activities"
            :key="a.id"
            class="dae-activity-item"
        >
            <span class="dae-activity-dot" :class="`dae-activity-dot--${actionColor(a.action)}`"></span>
            <div class="dae-activity-body">
                <div class="dae-activity-line">
                    <strong>{{ a.user }}</strong>
                    <span class="text-muted mx-1">•</span>
                    <span class="text-capitalize">{{ a.action }}</span>
                    <span class="text-muted ms-1">{{ a.entity }}</span>
                </div>
                <div class="dae-activity-time">
                    <i class="bi bi-clock me-1"></i>{{ a.date }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    activities: { type: Array, default: () => [] },
})

function actionColor(action) {
    return ({ create: 'green', update: 'blue', delete: 'red', traiter: 'orange', archiver: 'gray' })[action] || 'primary'
}
</script>

<style scoped>
.dae-activity { display: flex; flex-direction: column; gap: 0.5rem; }
.dae-activity-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    color: var(--bs-secondary-color);
    gap: 0.5rem;
}
.dae-activity-empty i { font-size: 2rem; opacity: 0.4; }
.dae-activity-item {
    display: flex;
    gap: 0.6rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--bs-border-color, #f0f0f0);
}
.dae-activity-item:last-child { border-bottom: none; }
.dae-activity-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
    margin-top: 0.35rem;
}
.dae-activity-dot--green  { background: #10b981; }
.dae-activity-dot--blue   { background: #3b82f6; }
.dae-activity-dot--red    { background: #ef4444; }
.dae-activity-dot--orange { background: #f59e0b; }
.dae-activity-dot--gray   { background: #6b7280; }
.dae-activity-dot--primary{ background: #ff7900; }
.dae-activity-body { flex: 1; min-width: 0; }
.dae-activity-line {
    font-size: 0.82rem;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.dae-activity-time {
    font-size: 0.72rem;
    color: var(--bs-secondary-color);
    margin-top: 0.1rem;
}
</style>
