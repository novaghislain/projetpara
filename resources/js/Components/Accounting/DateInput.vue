<!--
 * DateInput.vue
 * Champ de saisie de date standard (input type="date").
 * Simple wrapper avec label, gestion d'erreur et v-model.
-->
<template>
    <div class="date-input">
        <!-- Label avec astérisque si requis -->
        <label v-if="label" class="form-label small mb-1">
            {{ label }}<span v-if="required" class="text-danger ms-1">*</span>
        </label>
        <!-- Champ date HTML natif -->
        <input
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            type="date"
            class="form-control form-control-sm"
            :class="{ 'is-invalid': error }"
            :disabled="disabled"
        />
        <!-- Message d'erreur -->
        <div v-if="error" class="invalid-feedback d-block">{{ error }}</div>
    </div>
</template>

<script setup>
/* Propriétés du composant DateInput */
defineProps({
    modelValue: { type: String, default: '' },  /* Date au format YYYY-MM-DD (v-model) */
    label: { type: String, default: '' },
    required: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    error: { type: String, default: '' },
});
defineEmits(['update:modelValue']);
</script>
