<template>
  <div class="container-fluid p-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 text-gray-800">
                <i class="bi bi-airplane text-warning me-2"></i>Gestion des Congés
            </h2>
            <p class="text-muted mb-0">Demandes de congés et absences</p>
        </div>
        <div>
            <button class="btn btn-outline-secondary me-2"><i class="bi bi-calendar-range me-2"></i>Planning</button>
            <button class="btn btn-warning text-white"><i class="bi bi-plus-lg me-2"></i>Nouvelle Demande</button>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" placeholder="Rechercher un employé..." v-model="search">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" v-model="filterStatus">
                        <option value="">Tous les statuts</option>
                        <option value="En attente">En attente</option>
                        <option value="Approuvé">Approuvé</option>
                        <option value="Refusé">Refusé</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" v-model="filterPeriod">
                        <option value="">Ce mois-ci</option>
                        <option value="Mois dernier">Mois dernier</option>
                        <option value="Cette année">Cette année</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- FilterChips (Data Density P0 V2.2) -->
    <FilterChips :filters="activeFilters" @remove="removeFilter" @clearAll="clearAllFilters" />

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dense align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 ps-4">Employé</th>
                            <th class="border-0">Type de congé</th>
                            <th class="border-0">Période</th>
                            <th class="border-0">Durée</th>
                            <th class="border-0">Statut</th>
                            <th class="border-0 text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!leaves || !leaves.data || leaves.data.length === 0">
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x display-4 d-block mb-3 opacity-25"></i>
                                Aucune demande de congé trouvée.
                            </td>
                        </tr>
                        <tr v-for="leave in leaves?.data" :key="leave.id">
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-secondary text-white me-3 d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; border-radius: 50%;">
                                        {{ getInitials(leave.employee?.prenom, leave.employee?.nom) }}
                                    </div>
                                    <div class="fw-bold">{{ leave.employee?.prenom }} {{ leave.employee?.nom }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ leave.type || 'Congé payé' }}</span>
                            </td>
                            <td>
                                <div class="text-sm">Du {{ formatDate(leave.start_date) }}</div>
                                <div class="text-sm">Au {{ formatDate(leave.end_date) }}</div>
                            </td>
                            <td>{{ leave.days || '?' }} jours</td>
                            <td>
                                <span class="badge" :class="getStatusClass(leave.status)">
                                    <i class="bi me-1" :class="getStatusIcon(leave.status)"></i>
                                    {{ leave.status || 'En attente' }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button v-if="leave.status === 'En attente'" class="btn btn-sm btn-success text-white me-1" title="Approuver">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                <button v-if="leave.status === 'En attente'" class="btn btn-sm btn-danger text-white me-1" title="Refuser">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                                <button class="btn btn-sm btn-light text-primary" title="Détails">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import FilterChips from '../../../../Components/FilterChips.vue';

const props = defineProps({
    leaves: {
        type: Object,
        default: () => ({ data: [] })
    }
});

const search = ref('');
const filterStatus = ref('');
const filterPeriod = ref('');

const activeFilters = computed(() => {
    const filters = [];
    if (search.value) filters.push({ key: 'search', label: 'Employé', value: search.value });
    if (filterStatus.value) filters.push({ key: 'status', label: 'Statut', value: filterStatus.value });
    if (filterPeriod.value) filters.push({ key: 'period', label: 'Période', value: filterPeriod.value });
    return filters;
});

const removeFilter = (key) => {
    if (key === 'search') search.value = '';
    if (key === 'status') filterStatus.value = '';
    if (key === 'period') filterPeriod.value = '';
};

const clearAllFilters = () => {
    search.value = '';
    filterStatus.value = '';
    filterPeriod.value = '';
};

const getInitials = (prenom, nom) => {
    let initials = '';
    if (prenom) initials += prenom.charAt(0);
    if (nom) initials += nom.charAt(0);
    return initials.toUpperCase() || 'EMP';
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR');
};

const getStatusClass = (status) => {
    switch(status?.toLowerCase()) {
        case 'approuvé': return 'bg-success-subtle text-success border border-success-subtle';
        case 'refusé': return 'bg-danger-subtle text-danger border border-danger-subtle';
        case 'en attente':
        default: return 'bg-warning-subtle text-warning border border-warning-subtle';
    }
};

const getStatusIcon = (status) => {
    switch(status?.toLowerCase()) {
        case 'approuvé': return 'bi-check-circle-fill';
        case 'refusé': return 'bi-x-circle-fill';
        case 'en attente':
        default: return 'bi-clock-fill';
    }
};
</script>

<style scoped>
.avatar-circle {
    font-size: 1rem;
}
.shadow-sm {
    box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important;
}
.bg-success-subtle {
    background-color: #d1e7dd !important;
}
.bg-warning-subtle {
    background-color: #fff3cd !important;
}
.bg-danger-subtle {
    background-color: #f8d7da !important;
}
</style>