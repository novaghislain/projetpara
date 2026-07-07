<template>
    <div class="date-input">
        <label v-if="label" class="form-label small mb-1">
            {{ label }}<span v-if="required" class="text-danger ms-1">*</span>
        </label>
        <input
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            type="date"
            class="form-control form-control-sm"
            :class="{ 'is-invalid': error }"
            :disabled="disabled"
        />
        <div v-if="error" class="invalid-feedback d-block">{{ error }}</div>
    </div>
</template>

<script setup>
defineProps({
    modelValue: { type: String, default: '' },
    label: { type: String, default: '' },
    required: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    error: { type: String, default: '' },
});
defineEmits(['update:modelValue']);
</script>
