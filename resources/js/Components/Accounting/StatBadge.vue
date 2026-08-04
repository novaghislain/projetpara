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
/*
 * StatBadge.vue -- Composant d'affichage de statistique sous forme de badge coloré
 *
 * Affiche une valeur numérique ou textuelle avec une icône, une couleur de fond
 * et une tendance (hausse/baisse) optionnelle.
 */
export default {
    name: 'StatBadge',

    /* Propriétés du composant */
    props: {
        icon: { type: String, default: 'bi-box' },               /* Icône Bootstrap affichée à gauche */
        label: { type: String, required: true },                 /* Libellé descriptif */
        value: { type: [Number, String], default: 0 },           /* Valeur à afficher */
        color: { type: String, default: '#FF7900' },             /* Couleur de thème (hex) */
        trend: { type: Number, default: null },                  /* Tendance en pourcentage (négatif = baisse) */
        format: { type: String, default: 'number' },             /* Format d'affichage : 'number' ou 'currency' */
    },

    /* Propriétés calculées */
    computed: {
        /* Style dynamique du badge : fond translucide + bordure latérale */
        badgeStyle() {
            return {
                background: this.color + '18',
                borderLeft: `4px solid ${this.color}`,
                color: this.color,
            };
        },
    },

    /* Méthodes du composant */
    methods: {
        /* Formate la valeur selon le format demandé (nombre ou devise) */
        formatValue(v) {
            if (this.format === 'currency') {
                return Number(v).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
            return v;
        },
    },
};
</script>
