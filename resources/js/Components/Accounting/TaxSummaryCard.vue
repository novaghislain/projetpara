<template>
    <div class="card border-0 shadow-sm h-100">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" :style="{
                    width: '48px', height: '48px',
                    background: color + '20',
                    color: color,
                }">
                    <i :class="icon" style="font-size:1.4rem;"></i>
                </div>
                <div>
                    <div class="small text-muted text-uppercase">{{ label }}</div>
                    <div class="fw-bold fs-5">{{ formatValue(value) }}</div>
                    <div v-if="subtext" class="small text-muted">{{ subtext }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'TaxSummaryCard',
    props: {
        icon: { type: String, default: 'bi-cash' },
        label: { type: String, required: true },
        value: { type: [Number, String], default: 0 },
        color: { type: String, default: '#FF7900' },
        subtext: { type: String, default: '' },
    },
    methods: {
        formatValue(v) {
            if (this.format === 'currency' || isNaN(v)) {
                return Number(v).toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            }
            return v;
        },
    },
};
</script>
