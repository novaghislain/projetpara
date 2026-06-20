<template>
    <div class="account-selector">
        <label v-if="label" class="form-label small">{{ label }}</label>
        <select class="form-select form-select-sm" :value="modelValue" @change="$emit('update:modelValue', $event.target.value)">
            <option value="">— Sélectionner un compte —</option>
            <option v-for="acc in filteredAccounts" :key="acc.id" :value="acc.id">
                {{ acc.code }} — {{ acc.name }}
            </option>
        </select>
    </div>
</template>

<script>
export default {
    name: 'AccountSelector',
    props: {
        modelValue: { type: [String, Number], default: '' },
        accounts: { type: Array, default: () => [] },
        label: { type: String, default: '' },
        typeFilter: { type: String, default: '' },
        classFilter: { type: String, default: '' },
    },
    emits: ['update:modelValue'],
    computed: {
        filteredAccounts() {
            let list = this.accounts;
            if (this.typeFilter) {
                list = list.filter(a => a.type === this.typeFilter);
            }
            if (this.classFilter) {
                list = list.filter(a => a.syscohada_class === this.classFilter);
            }
            return list;
        },
    },
};
</script>
