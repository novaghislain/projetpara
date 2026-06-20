<script setup>
import { ref, computed, onMounted } from 'vue';
import CompanyLayout from '../../../Layouts/CompanyLayout.vue';
import { authStore } from '../../../stores/auth';

const employees = ref([]);
const loading = ref(true);
const error = ref(null);
const search = ref('');

const fetchEmployees = async () => {
    loading.value = true;
    error.value = null;
    try {
        const res = await fetch('/company/rh/api/employees');
        if (!res.ok) throw new Error('Erreur de chargement');
        employees.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const filteredEmployees = computed(() => {
    let list = employees.value;
    if (search.value) {
        const q = search.value.toLowerCase();
        list = list.filter(e =>
            (e.nom && e.nom.toLowerCase().includes(q)) ||
            (e.prenom && e.prenom.toLowerCase().includes(q)) ||
            (e.poste && e.poste.toLowerCase().includes(q))
        );
    }
    return list;
});

const statusBadgeClass = (status) => {
    const map = {
        actif: 'bg-success',
        inactif: 'bg-secondary',
        suspendu: 'bg-warning text-dark',
        conge: 'bg-info',
    };
    return map[status] || 'bg-secondary';
};

onMounted(fetchEmployees);
</script>

<template>
    <CompanyLayout page-title="Mes Employés">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div class="input-group input-group-sm" style="max-width:300px;">
                <span class="input-group-text bg-white"><i class="bi-search"></i></span>
                <input v-model="search" class="form-control form-control-sm" placeholder="Rechercher un employé...">
            </div>
            <span class="small text-muted">{{ filteredEmployees.length }} employé(s)</span>
        </div>

        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <div v-else class="card card-dashboard">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="small text-muted">
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Poste</th>
                            <th>Département</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!filteredEmployees.length">
                            <td colspan="7" class="text-center py-4 text-muted">Aucun employé trouvé.</td>
                        </tr>
                        <tr v-for="emp in filteredEmployees" :key="emp.id">
                            <td class="fw-medium">{{ emp.nom }}</td>
                            <td>{{ emp.prenom }}</td>
                            <td class="small">{{ emp.poste || '-' }}</td>
                            <td class="small">{{ emp.departement || '-' }}</td>
                            <td class="small">{{ emp.email || '-' }}</td>
                            <td class="small">{{ emp.phone || '-' }}</td>
                            <td><span class="badge" :class="statusBadgeClass(emp.status)">{{ emp.status }}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </CompanyLayout>
</template>
