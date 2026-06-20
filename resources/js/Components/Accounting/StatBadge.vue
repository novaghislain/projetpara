<template>
    <span class="stat-badge d-inline-flex align-items-center gap-2 p-3 rounded-3" :style="badgeStyle">
        <i :class="icon" style="font-size:1.5rem;"></i>
        <div>
            <div class="small text-uppercase" style="opacity:0.85;">{{ label }}</div>
            <div class="fw-bold fs-5">{{ formatValue(value) }}</div>
            <div v-if="trend !== null && trend !== undefined" class="small" :class="trend >= 0 ? 'text-success' : 'text-danger'">
                <i :class="trend >= 0 ? 'bi-arrow-up' : 'bi-arrow-down'"></i>
                {{ Math.abs(trend) }}%
            </div>
        </div>
    </span>
</template>

<script>
export default {
    name: 'StatBadge',
    props: {
        icon: { type: String, default: 'bi-box' },
        label: { type: String, required: true },
        value: { type: [Number, String], default: 0 },
        color: { type: String, default: '#FF7900' },
        trend: { type: Number, default: null },
        format: { type: String, default: 'number' },
    },
    computed: {
        badgeStyle() {
            return {
                background: this.color + '18',
                borderLeft: `4px solid ${this.color}`,
                color: this.color,
            };
        },
    },
    methods: {
        formatValue(v) {
            if (this.format === 'currency') {
                return Number(v).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
            return v;
        },
    },
};
</script>
