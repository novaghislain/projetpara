<!--
 * MoneyInput.vue
 * Champ de saisie monétaire avec séparateur de milliers.
 * Affiche un symbole monétaire à gauche et le code de devise à droite.
 * Nettoie la saisie en ne conservant que les chiffres pour la valeur
 * interne, tout en affichant un format lisible (espacement des milliers).
-->
<template>
    <div class="money-input">
        <!-- Label avec astérisque si requis -->
        <label v-if="label" class="form-label small mb-1">
            {{ label }}<span v-if="required" class="text-danger ms-1">*</span>
        </label>
        <!-- Groupe d'entrée avec symbole, champ et devise -->
        <div class="input-group input-group-sm">
            <span class="input-group-text">{{ currencySymbol }}</span>
            <input
                :value="displayValue"
                @input="onInput"
                type="text"
                class="form-control text-end font-mono"
                :class="{ 'is-invalid': error }"
                :placeholder="placeholder"
                :disabled="disabled"
            />
            <span class="input-group-text">{{ locale }}</span>
        </div>
        <!-- Message d'erreur -->
        <div v-if="error" class="invalid-feedback d-block">{{ error }}</div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

/* Propriétés du composant MoneyInput */
const props = defineProps({
    modelValue: { type: [Number, String], default: 0 },  /* Valeur liée (v-model) */
    label: { type: String, default: '' },
    placeholder: { type: String, default: '0' },
    required: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    error: { type: String, default: '' },
    currencySymbol: { type: String, default: 'F' },       /* Symbole affiché à gauche */
    locale: { type: String, default: 'XOF' },             /* Code devise affiché à droite */
});

const emit = defineEmits(['update:modelValue']);

/* Valeur formatée avec séparateur de milliers pour l'affichage */
const displayValue = computed(() => {
    const num = Number(props.modelValue) || 0;
    return num.toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
});

/* Nettoie la saisie : ne garde que les chiffres, émet la valeur numérique */
function onInput(event) {
    const raw = event.target.value.replace(/[^\d]/g, '');
    emit('update:modelValue', raw === '' ? 0 : Number(raw));
}
</script>

<style scoped>
.font-mono { font-family: 'Courier New', monospace; }
.input-group-sm .input-group-text { font-size: 0.8rem; }
</style>
