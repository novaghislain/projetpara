<!--
 * AccountSelector.vue
 * Sélecteur de compte comptable avec deux modes :
 *   - Recherche avancée avec autocomplétion (quand > 10 comptes)
 *   - Select simple (quand <= 10 comptes)
 * Supporte le filtrage par classe et par type de compte.
-->
<template>
    <div class="account-selector">
        <!-- Label avec astérisque si requis -->
        <label v-if="label" class="form-label small mb-1">
            {{ label }}<span v-if="required" class="text-danger ms-1">*</span>
        </label>

        <!-- Mode recherche avancée avec dropdown (plus de 10 comptes) -->
        <div v-if="accounts.length > 10" class="position-relative">
            <!-- Champ de recherche texte -->
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
            <!-- Liste déroulante des résultats filtrés -->
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

        <!-- Mode select simple (10 comptes ou moins) -->
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

        <!-- Compte sélectionné affiché en mode recherche -->
        <div v-if="selected && accounts.length > 10" class="mt-1">
            <small class="text-success">✓ {{ selected.code }} — {{ selected.name }}</small>
        </div>
        <!-- Message d'erreur -->
        <div v-if="error" class="invalid-feedback d-block">{{ error }}</div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

/* Propriétés du composant AccountSelector */
const props = defineProps({
    modelValue: { type: [String, Number, null], default: null },  /* ID du compte sélectionné */
    label: { type: String, default: '' },
    placeholder: { type: String, default: 'Code ou nom du compte...' },
    required: { type: Boolean, default: false },
    error: { type: String, default: '' },
    classFilter: { type: String, default: '' },    /* Filtre par classe SYSCOHADA (ex: "1", "2", ...) */
    typeFilter: { type: String, default: '' },     /* Filtre par type de compte */
    accounts: { type: Array, default: () => [] },  /* Liste de tous les comptes disponibles */
});

const emit = defineEmits(['update:modelValue']);

/* État interne */
const searchTerm = ref('');          /* Texte saisi dans la recherche */
const showDropdown = ref(false);     /* Visibilité du dropdown */
const selected = ref(null);          /* Compte sélectionné (objet complet) */

/* Liste filtrée selon le terme de recherche, la classe et le type */
const filteredAccounts = computed(() => {
    let list = props.accounts;

    /* Filtre par classe SYSCOHADA (code commençant par) */
    if (props.classFilter) {
        list = list.filter(a =>
            a.code?.startsWith(props.classFilter) ||
            a.syscohada_class === props.classFilter
        );
    }
    /* Filtre par type de compte */
    if (props.typeFilter) {
        list = list.filter(a => a.type === props.typeFilter);
    }
    /* Si pas de recherche ou mode select simple, retourne la liste brute filtrée */
    if (!searchTerm.value || props.accounts.length <= 10) return list;

    /* Recherche textuelle sur le code et le nom (limité à 30 résultats) */
    const term = searchTerm.value.toLowerCase();
    return list.filter(
        a => a.code?.toLowerCase().includes(term) ||
             a.name?.toLowerCase().includes(term)
    ).slice(0, 30);
});

/* Met à jour le terme de recherche et affiche le dropdown */
function onSearch(e) {
    searchTerm.value = e.target.value;
    showDropdown.value = true;
}

/* Sélectionne un compte et émet son ID */
function selectAccount(account) {
    selected.value = account;
    searchTerm.value = `${account.code} — ${account.name}`;
    showDropdown.value = false;
    emit('update:modelValue', account.id);
}

/* Cache le dropdown avec un délai pour permettre les clics */
function hideDropdown() {
    setTimeout(() => { showDropdown.value = false; }, 200);
}
</script>

<style scoped>
.font-mono { font-family: 'Courier New', monospace; font-size: 0.85em; }
.dropdown-item:hover { background-color: #FFF3E0; }
</style>
