<template>
    <div class="money-input">
        <label v-if="label" class="form-label small mb-1">
            {{ label }}<span v-if="required" class="text-danger ms-1">*</span>
        </label>
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
        <div v-if="error" class="invalid-feedback d-block">{{ error }}</div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: { type: [Number, String], default: 0 },
    label: { type: String, default: '' },
    placeholder: { type: String, default: '0' },
    required: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    error: { type: String, default: '' },
    currencySymbol: { type: String, default: 'F' },
    locale: { type: String, default: 'XOF' },
});

const emit = defineEmits(['update:modelValue']);

const displayValue = computed(() => {
    const num = Number(props.modelValue) || 0;
    return num.toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
});

function onInput(event) {
    const raw = event.target.value.replace(/[^\d]/g, '');
    emit('update:modelValue', raw === '' ? 0 : Number(raw));
}
</script>

<style scoped>
.font-mono { font-family: 'Courier New', monospace; }
.input-group-sm .input-group-text { font-size: 0.8rem; }
</style>
