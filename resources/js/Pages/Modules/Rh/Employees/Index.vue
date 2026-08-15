<template>
  <div class="container-fluid p-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 text-gray-800">
                <i class="bi bi-people text-primary me-2"></i>Employés
            </h2>
            <p class="text-muted mb-0">Gérez les dossiers du personnel et les accès</p>
        </div>
        <div>
            <button class="btn btn-outline-secondary me-2"><i class="bi bi-download me-2"></i>Exporter</button>
            <button class="btn btn-primary" @click="showCreateModal = true"><i class="bi bi-person-plus me-2"></i>Ajouter un employé</button>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" placeholder="Rechercher par nom, matricule..." v-model="search">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" v-model="filterStatus">
                        <option value="">Tous les statuts</option>
                        <option value="Actif">Actif</option>
                        <option value="En congé">En congé</option>
                        <option value="Inactif">Inactif</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" v-model="filterDepartment">
                        <option value="">Tous les départements</option>
                        <option value="Direction">Direction</option>
                        <option value="IT">IT</option>
                        <option value="Commercial">Commercial</option>
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
                            <th class="border-0">Matricule</th>
                            <th class="border-0">Département</th>
                            <th class="border-0">Poste</th>
                            <th class="border-0">Statut</th>
                            <th class="border-0 text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!employees || !employees.data || employees.data.length === 0">
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-4 d-block mb-3 opacity-25"></i>
                                Aucun employé trouvé.
                            </td>
                        </tr>
                        <tr v-for="employee in employees?.data" :key="employee.id">
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-primary-subtle text-primary me-3 d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; border-radius: 50%;">
                                        {{ getInitials(employee.prenom, employee.nom) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ employee.prenom }} {{ employee.nom }}</div>
                                        <div class="text-muted small">{{ employee.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ employee.matricule }}</span></td>
                            <td>{{ employee.departement || '-' }}</td>
                            <td>{{ employee.poste || '-' }}</td>
                            <td>
                                <span class="badge" :class="getStatusClass(employee.status)">
                                    {{ employee.status || 'Actif' }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light text-primary me-1" title="Voir le profil">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-light text-warning" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Pagination logic goes here -->
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import FilterChips from '../../../../Components/FilterChips.vue';

const props = defineProps({
    employees: {
        type: Object,
        default: () => ({ data: [] })
    }
});

const search = ref('');
const filterStatus = ref('');
const filterDepartment = ref('');
const showCreateModal = ref(false);

const activeFilters = computed(() => {
    const filters = [];
    if (search.value) filters.push({ key: 'search', label: 'Recherche', value: search.value });
    if (filterStatus.value) filters.push({ key: 'status', label: 'Statut', value: filterStatus.value });
    if (filterDepartment.value) filters.push({ key: 'department', label: 'Département', value: filterDepartment.value });
    return filters;
});

const removeFilter = (key) => {
    if (key === 'search') search.value = '';
    if (key === 'status') filterStatus.value = '';
    if (key === 'department') filterDepartment.value = '';
};

const clearAllFilters = () => {
    search.value = '';
    filterStatus.value = '';
    filterDepartment.value = '';
};

const getInitials = (prenom, nom) => {
    let initials = '';
    if (prenom) initials += prenom.charAt(0);
    if (nom) initials += nom.charAt(0);
    return initials.toUpperCase() || '?';
};

const getStatusClass = (status) => {
    switch(status?.toLowerCase()) {
        case 'actif': return 'bg-success-subtle text-success border border-success-subtle';
        case 'en congé': return 'bg-warning-subtle text-warning border border-warning-subtle';
        case 'inactif': return 'bg-danger-subtle text-danger border border-danger-subtle';
        default: return 'bg-success-subtle text-success border border-success-subtle';
    }
};
</script>

<style scoped>
.avatar-circle {
    font-size: 1.1rem;
}
.shadow-sm {
    box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important;
}
.bg-primary-subtle {
    background-color: #e0e8ff !important;
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
