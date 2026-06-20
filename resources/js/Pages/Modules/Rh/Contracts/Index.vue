<script setup>
import { ref, computed, onMounted } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const contracts = ref([]);
const loading = ref(true);
const error = ref(null);
const search = ref('');
const statusFilter = ref('');

const fetchContracts = async () => {
    loading.value = true;
    error.value = null;
    try {
        const res = await fetch('/rh/contracts');
        if (!res.ok) throw new Error('Erreur de chargement');
        contracts.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const filteredContracts = computed(() => {
    let list = contracts.value;
    if (search.value) {
        const q = search.value.toLowerCase();
        list = list.filter(c =>
            (c.reference && c.reference.toLowerCase().includes(q)) ||
            (c.employe_nom && c.employe_nom.toLowerCase().includes(q)) ||
            (c.type_contrat && c.type_contrat.toLowerCase().includes(q))
        );
    }
    if (statusFilter.value) {
        list = list.filter(c => c.statut === statusFilter.value);
    }
    return list;
});

const statusBadgeClass = (status) => {
    const map = {
        actif: 'bg-success',
        termine: 'bg-secondary',
        resilie: 'bg-danger',
        suspendu: 'bg-warning text-dark',
    };
    return map[status] || 'bg-secondary';
};

const deleteContract = async (id) => {
    if (!confirm('Confirmer la suppression de ce contrat ?')) return;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/rh/contracts/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error('Erreur de suppression');
        await fetchContracts();
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

const downloadContract = async (id) => {
    try {
        const res = await fetch('/rh/contracts/' + id + '/download');
        if (!res.ok) throw new Error('Erreur de téléchargement');
        const blob = await res.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'contrat-' + id + '.pdf';
        a.click();
        window.URL.revokeObjectURL(url);
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

onMounted(fetchContracts);
</script>

<template>
    <GelLayout page-title="Contrats">
        <!-- Toolbar -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="input-group input-group-sm" style="max-width:260px;">
                    <span class="input-group-text bg-white"><i class="bi-search"></i></span>
                    <input v-model="search" class="form-control form-control-sm" placeholder="Rechercher...">
                </div>
                <select v-model="statusFilter" class="form-select form-select-sm" style="width:auto;">
                    <option value="">Tous statuts</option>
                    <option value="actif">Actif</option>
                    <option value="termine">Terminé</option>
                    <option value="resilie">Résilié</option>
                    <option value="suspendu">Suspendu</option>
                </select>
            </div>
            <a href="/rh/contracts/create" class="btn btn-primary btn-sm">
                <i class="bi-plus-lg me-1"></i>Nouveau contrat
            </a>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <!-- Table -->
        <div v-else class="card card-dashboard">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="small text-muted">
                        <tr>
                            <th>Référence</th>
                            <th>Employé</th>
                            <th>Type</th>
                            <th>Date début</th>
                            <th>Date fin</th>
                            <th class="text-end">Salaire</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!filteredContracts.length">
                            <td colspan="8" class="text-center py-4 text-muted">Aucun contrat trouvé.</td>
                        </tr>
                        <tr v-for="c in filteredContracts" :key="c.id">
                            <td class="small fw-medium">{{ c.reference || '-' }}</td>
                            <td>{{ c.employe_nom || c.employe?.nom }} {{ c.employe_prenom || c.employe?.prenom }}</td>
                            <td><span class="badge bg-secondary">{{ c.type_contrat }}</span></td>
                            <td class="small">{{ $formatDate ? $formatDate(c.date_debut) : c.date_debut }}</td>
                            <td class="small">{{ c.date_fin ? ($formatDate ? $formatDate(c.date_fin) : c.date_fin) : '-' }}</td>
                            <td class="text-end">{{ $formatCurrency ? $formatCurrency(c.salaire) : c.salaire }}</td>
                            <td><span class="badge" :class="statusBadgeClass(c.statut)">{{ c.statut }}</span></td>
                            <td class="text-end">
                                <button v-if="c.fichier" class="btn btn-sm btn-outline-info me-1" @click="downloadContract(c.id)" title="Télécharger"><i class="bi-download"></i></button>
                                <a :href="'/rh/contracts/' + c.id + '/edit'" class="btn btn-sm btn-outline-primary me-1" title="Modifier"><i class="bi-pencil"></i></a>
                                <button class="btn btn-sm btn-outline-danger" @click="deleteContract(c.id)" title="Supprimer"><i class="bi-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white small text-muted">
                <span>{{ filteredContracts.length }} contrat(s) sur {{ contracts.length }}</span>
            </div>
        </div>
    </GelLayout>
</template>
