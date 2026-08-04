<!--
 * TaxSummaryCard.vue
 * Carte récapitulative pour afficher une valeur fiscale ou comptable.
 * Affiche une icône, un label, une valeur formatée et un sous-texte.
 * Utilisée dans les tableaux de bord de synthèse (TVA, taxes, etc.).
-->
<template>
    <div class="card border-0 shadow-sm h-100">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-3">
                <!-- Icône ronde colorée -->
                <div class="rounded-circle d-flex align-items-center justify-content-center" :style="{
                    width: '48px', height: '48px',
                    background: color + '20',
                    color: color,
                }">
                    <i :class="icon" style="font-size:1.4rem;"></i>
                </div>
                <!-- Texte : label, valeur et sous-texte -->
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
        icon: { type: String, default: 'bi-cash' },        /* Classe Bootstrap Icons */
        label: { type: String, required: true },            /* Texte du label */
        value: { type: [Number, String], default: 0 },      /* Valeur à afficher */
        color: { type: String, default: '#FF7900' },        /* Couleur de l'icône et de l'accent */
        subtext: { type: String, default: '' },             /* Sous-texte optionnel */
    },
    methods: {
        /* Formate la valeur en monnaie locale (fr-FR) */
        formatValue(v) {
            if (this.format === 'currency' || isNaN(v)) {
                return Number(v).toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            }
            return v;
        },
    },
};
</script>
