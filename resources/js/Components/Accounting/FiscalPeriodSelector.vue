<!--
 * FiscalPeriodSelector.vue
 * Sélecteur de période fiscale (exercice + mois optionnel).
 * Utilisé pour filtrer les écritures comptables par exercice
 * et éventuellement par mois au sein de l'exercice.
-->
<template>
    <div class="fiscal-period-selector d-flex gap-2 align-items-center">
        <!-- Label optionnel -->
        <label v-if="label" class="small mb-0 text-nowrap">{{ label }}</label>
        <!-- Sélection de l'exercice fiscal -->
        <select class="form-select form-select-sm" v-model="year" @change="emitChange">
            <option v-for="y in years" :key="y.id" :value="y.id">{{ y.label }}</option>
        </select>
        <!-- Sélection du mois (optionnel, affiché via showMonth) -->
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
        fiscalYears: { type: Array, default: () => [] }, /* Liste des exercices fiscaux */
        label: { type: String, default: 'Période' },
        showMonth: { type: Boolean, default: false },    /* Affiche le sélecteur de mois */
    },
    emits: ['change'],
    data() {
        return {
            year: this.fiscalYears?.length ? this.fiscalYears[0].id : null,  /* Exercice sélectionné par défaut */
            month: '',                                                         /* Mois sélectionné (vide = tous) */
        };
    },
    computed: {
        /* Transforme les exercices en options { id, label } pour le <select> */
        years() {
            return (this.fiscalYears || []).map(fy => ({
                id: fy.id,
                label: `${fy.year} (${fy.status})`,
            }));
        },
        /* Liste des mois en français */
        months() {
            return ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
        },
    },
    methods: {
        /* Émet l'événement avec l'exercice et le mois sélectionnés */
        emitChange() {
            this.$emit('change', { fiscal_year_id: this.year, month: this.month });
        },
    },
};
</script>
