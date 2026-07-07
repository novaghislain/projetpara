<template>
    <div
        :class="['dae-msg-bubble', `dae-msg-bubble--${type}`, { 'dae-msg-bubble--urgent': urgent }]"
        @click="onClick"
    >
        <div class="dae-bubble-icon">
            <i :class="iconClass"></i>
        </div>
        <div class="dae-bubble-content">
            <div class="dae-bubble-head">
                <span class="dae-bubble-title">{{ title }}</span>
                <span v-if="urgent" class="dae-bubble-urgent">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </span>
            </div>
            <p v-if="subtitle" class="dae-bubble-subtitle">{{ subtitle }}</p>
            <div class="dae-bubble-footer">
                <span class="dae-bubble-time">{{ time }}</span>
                <span v-if="count !== undefined && count > 0" class="dae-bubble-count">{{ count }}</span>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    type: { type: String, default: 'info' },  // info, warning, danger, success
    iconClass: { type: String, default: 'bi-info-circle' },
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
    time: { type: String, default: '' },
    count: { type: Number, default: undefined },
    urgent: { type: Boolean, default: false },
})

const emit = defineEmits(['click'])
function onClick() { emit('click') }
</script>

<style scoped>
.dae-msg-bubble {
    display: flex; align-items: flex-start; gap: 0.6rem;
    padding: 0.65rem 0.75rem; border-radius: 8px;
    background: #fff; border: 1px solid var(--bs-border-color, #e9ecef);
    cursor: pointer; transition: all 0.15s ease;
}
.dae-msg-bubble:hover { border-color: #ff7900; box-shadow: 0 1px 4px rgba(255,121,0,0.08); }
.dae-msg-bubble--urgent { border-left: 3px solid #ef4444; }
.dae-msg-bubble--info { border-left: 3px solid #3b82f6; }
.dae-msg-bubble--warning { border-left: 3px solid #f59e0b; }
.dae-msg-bubble--danger { border-left: 3px solid #ef4444; }
.dae-msg-bubble--success { border-left: 3px solid #10b981; }

.dae-bubble-icon { font-size: 1.1rem; color: var(--bs-secondary-color); flex-shrink: 0; margin-top: 0.1rem; }
.dae-bubble-content { flex: 1; min-width: 0; }
.dae-bubble-head { display: flex; align-items: center; gap: 0.35rem; }
.dae-bubble-title { font-size: 0.8rem; font-weight: 600; color: var(--bs-body-color); }
.dae-bubble-urgent { color: #ef4444; font-size: 0.7rem; }
.dae-bubble-subtitle { font-size: 0.72rem; color: var(--bs-secondary-color); margin: 0.1rem 0 0; }
.dae-bubble-footer { display: flex; align-items: center; gap: 0.5rem; margin-top: 0.15rem; }
.dae-bubble-time { font-size: 0.65rem; color: var(--bs-secondary-color); }
.dae-bubble-count {
    font-size: 0.6rem; font-weight: 700; background: #ef4444;
    color: #fff; padding: 0.1rem 0.4rem; border-radius: 10px;
    min-width: 18px; text-align: center;
}
</style>
