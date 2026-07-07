<template>
    <div class="account-selector">
        <label v-if="label" class="form-label small mb-1">
            {{ label }}<span v-if="required" class="text-danger ms-1">*</span>
        </label>

        <!-- Mode recherche avancé (quand on a des comptes) -->
        <div v-if="accounts.length > 10" class="position-relative">
            <input
                :value="searchTerm"
                @input="onSearch"
                @focus="showDropdown = true"
                @blur="hideDropdown"
                type="text"
                class="form-control form-control-sm"
                :class="{ 'is-invalid': error }"
                :placeholder="placeholder"
            />
            <ul v-if="showDropdown && filteredAccounts.length > 0"
                class="dropdown-menu show w-100 mt-1 p-0 shadow-sm"
                style="max-height: 280px; overflow-y: auto;">
                <li v-for="account in filteredAccounts" :key="account.id"
                    @mousedown.prevent="selectAccount(account)"
                    class="dropdown-item py-2 px-3 cursor-pointer"
                    style="cursor: pointer; border-bottom: 1px solid #f0f0f0;">
                    <small class="text-muted font-mono">{{ account.code }}</small>
                    <span class="ms-2">{{ account.name }}</span>
                </li>
            </ul>
        </div>

        <!-- Mode select simple (quand peu de comptes ou via propriétaire) -->
        <select v-else
            class="form-select form-select-sm"
            :class="{ 'is-invalid': error }"
            :value="modelValue"
            @change="$emit('update:modelValue', $event.target.value)">
            <option value="">— Sélectionner un compte —</option>
            <option v-for="acc in filteredAccounts" :key="acc.id" :value="acc.id">
                {{ acc.code }} — {{ acc.name }}
            </option>
        </select>

        <div v-if="selected && accounts.length > 10" class="mt-1">
            <small class="text-success">✓ {{ selected.code }} — {{ selected.name }}</small>
        </div>
        <div v-if="error" class="invalid-feedback d-block">{{ error }}</div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    modelValue: { type: [String, Number, null], default: null },
    label: { type: String, default: '' },
    placeholder: { type: String, default: 'Code ou nom du compte...' },
    required: { type: Boolean, default: false },
    error: { type: String, default: '' },
    classFilter: { type: String, default: '' },
    typeFilter: { type: String, default: '' },
    accounts: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue']);

const searchTerm = ref('');
const showDropdown = ref(false);
const selected = ref(null);

const filteredAccounts = computed(() => {
    let list = props.accounts;

    if (props.classFilter) {
        list = list.filter(a =>
            a.code?.startsWith(props.classFilter) ||
            a.syscohada_class === props.classFilter
        );
    }
    if (props.typeFilter) {
        list = list.filter(a => a.type === props.typeFilter);
    }
    if (!searchTerm.value || props.accounts.length <= 10) return list;

    const term = searchTerm.value.toLowerCase();
    return list.filter(
        a => a.code?.toLowerCase().includes(term) ||
             a.name?.toLowerCase().includes(term)
    ).slice(0, 30);
});

function onSearch(e) {
    searchTerm.value = e.target.value;
    showDropdown.value = true;
}

function selectAccount(account) {
    selected.value = account;
    searchTerm.value = `${account.code} — ${account.name}`;
    showDropdown.value = false;
    emit('update:modelValue', account.id);
}

function hideDropdown() {
    setTimeout(() => { showDropdown.value = false; }, 200);
}
</script>

<style scoped>
.font-mono { font-family: 'Courier New', monospace; font-size: 0.85em; }
.dropdown-item:hover { background-color: #FFF3E0; }
</style>
