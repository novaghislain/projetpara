<script setup>
/*
 * AccountingClientSelector -- Sélecteur de dossier client pour les comptables.
 * Affiche une carte permettant de choisir l'entreprise cliente sur laquelle
 * travailler. Visible uniquement pour les rôles comptable, super_admin
 * et director.
 */
import { ref, onMounted, computed } from 'vue';
import { authStore } from '../../stores/auth';

const emit = defineEmits(['select']);

const clients = ref([]);     /* Liste des clients disponibles */
const selectedId = ref(null); /* ID du client sélectionné */
const loading = ref(true);    /* Indicateur de chargement */

/* Charge la liste des clients depuis l'API */
const fetchClients = async () => {
    loading.value = true;
    try {
        const res = await fetch('/api/clients');
        if (res.ok) clients.value = await res.json();
    } catch (e) { /* silencieux */ }
    finally { loading.value = false; }
};

/* Émet l'événement de sélection avec l'ID choisi */
const onSelect = () => {
    if (selectedId.value) emit('select', Number(selectedId.value));
};

/* Vérifie si l'utilisateur actuel a le droit d'accéder au sélecteur */
const isComptable = computed(() =>
    ['comptable', 'super_admin', 'director'].includes(authStore.user?.role)
);

onMounted(fetchClients);
</script>

<template>
    <div v-if="isComptable && !$attrs.clientId" class="card border-warning mb-4">
        <div class="card-body d-flex align-items-center gap-3 flex-wrap">
            <div class="text-warning">
                <i class="bi bi-building-check fs-4"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-semibold mb-1">Sélectionner un dossier client</div>
                <div class="text-muted small">Choisissez l'entreprise pour laquelle vous souhaitez travailler.</div>
            </div>
            <div v-if="loading" class="spinner-border spinner-border-sm text-warning"></div>
            <div v-else class="d-flex gap-2 align-items-center">
                <select v-model="selectedId" class="form-select form-select-sm" style="min-width:220px;">
                    <option value="">— Choisir un client —</option>
                    <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company_name || c.email }}</option>
                </select>
                <button
                    class="btn btn-warning btn-sm"
                    :disabled="!selectedId"
                    @click="onSelect"
                >
                    <i class="bi bi-arrow-right-circle me-1"></i> Accéder
                </button>
            </div>
        </div>
    </div>
</template>
