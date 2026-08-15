<template>
    <GelLayout pageTitle="Plan Comptable">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-1 text-gray-800">Plan Comptable</h2>
                    <p class="text-muted mb-0">Gestion des comptes SYSCOHADA de l'entreprise</p>
                </div>
                <button class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Nouveau Compte
                </button>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <div class="input-group input-group-sm" style="width: 300px;">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control bg-light border-start-0" placeholder="Rechercher (compte, libellé)..." v-model="search">
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-dense mb-0 align-middle">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th width="15%">Numéro</th>
                                    <th width="40%">Intitulé du Compte</th>
                                    <th width="15%">Type</th>
                                    <th width="15%">Solde Actuel</th>
                                    <th width="15%" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="compte in filteredAccounts" :key="compte.id">
                                    <td class="font-monospace fw-bold">{{ compte.numero }}</td>
                                    <td>{{ compte.intitule }}</td>
                                    <td>
                                        <span class="badge bg-secondary rounded-pill fw-normal">{{ compte.type }}</span>
                                    </td>
                                    <td class="tabular-nums" :class="compte.solde < 0 ? 'text-danger' : (compte.solde > 0 ? 'text-success' : 'text-muted')">
                                        {{ formatCurrency(compte.solde) }}
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-light text-primary me-1" title="Modifier"><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-sm btn-light text-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                                <tr v-if="filteredAccounts.length === 0">
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        Aucun compte trouvé.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

// Mock data pour l'instant (sera remplacé par un appel API)
const accounts = ref([
    { id: 1, numero: '1011', intitule: 'Capital social', type: 'Capitaux', solde: -10000000 },
    { id: 2, numero: '4011', intitule: 'Fournisseurs', type: 'Tiers', solde: -2500000 },
    { id: 3, numero: '4111', intitule: 'Clients', type: 'Tiers', solde: 5800000 },
    { id: 4, numero: '5211', intitule: 'Banque - BGFIBank', type: 'Trésorerie', solde: 15450000 },
    { id: 5, numero: '6011', intitule: 'Achats de marchandises', type: 'Charges', solde: 4500000 },
    { id: 6, numero: '7011', intitule: 'Ventes de marchandises', type: 'Produits', solde: -12000000 },
]);

const search = ref('');

const filteredAccounts = computed(() => {
    if (!search.value) return accounts.value;
    const s = search.value.toLowerCase();
    return accounts.value.filter(a => a.numero.includes(s) || a.intitule.toLowerCase().includes(s));
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF', maximumFractionDigits: 0 }).format(value || 0).replace('XOF', 'FCFA');
};
</script>

<style scoped>
.table-dense th, .table-dense td {
    padding: 0.5rem;
    font-size: 0.85rem;
}
</style>
