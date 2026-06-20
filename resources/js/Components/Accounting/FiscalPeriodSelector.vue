<template>
    <div class="fiscal-period-selector d-flex gap-2 align-items-center">
        <label v-if="label" class="small mb-0 text-nowrap">{{ label }}</label>
        <select class="form-select form-select-sm" v-model="year" @change="emitChange">
            <option v-for="y in years" :key="y.id" :value="y.id">{{ y.label }}</option>
        </select>
        <select v-if="showMonth" class="form-select form-select-sm" v-model="month" @change="emitChange">
            <option value="">Tous</option>
            <option v-for="(m, i) in months" :key="i" :value="i + 1">{{ m }}</option>
        </select>
    </div>
</template>

<script>
export default {
    name: 'FiscalPeriodSelector',
    props: {
        fiscalYears: { type: Array, default: () => [] },
        label: { type: String, default: 'Période' },
        showMonth: { type: Boolean, default: false },
    },
    emits: ['change'],
    data() {
        return {
            year: this.fiscalYears?.length ? this.fiscalYears[0].id : null,
            month: '',
        };
    },
    computed: {
        years() {
            return (this.fiscalYears || []).map(fy => ({
                id: fy.id,
                label: `${fy.year} (${fy.status})`,
            }));
        },
        months() {
            return ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
        },
    },
    methods: {
        emitChange() {
            this.$emit('change', { fiscal_year_id: this.year, month: this.month });
        },
    },
};
</script>
