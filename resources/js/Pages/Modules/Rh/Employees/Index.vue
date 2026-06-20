<script setup>
import { ref, computed, onMounted } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';
import RhStatCard from '../../../../Components/Rh/RhStatCard.vue';
import { authStore } from '../../../../stores/auth';

const employees = ref([]);
const loading = ref(true);
const error = ref(null);
const search = ref('');
const statusFilter = ref('');
const selectedIds = ref([]);

const fetchEmployees = async () => {
    loading.value = true;
    error.value = null;
    try {
        const res = await fetch('/rh/employees');
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
            (e.matricule && e.matricule.toLowerCase().includes(q)) ||
            (e.nom && e.nom.toLowerCase().includes(q)) ||
            (e.prenom && e.prenom.toLowerCase().includes(q)) ||
            (e.poste && e.poste.toLowerCase().includes(q)) ||
            (e.departement && e.departement.toLowerCase().includes(q))
        );
    }
    if (statusFilter.value) {
        list = list.filter(e => e.status === statusFilter.value);
    }
    return list;
});

const stats = computed(() => ({
    total: employees.value.length,
    actifs: employees.value.filter(e => e.status === 'actif').length,
    cdi: employees.value.filter(e => e.type_contrat === 'CDI').length,
    stagiaires: employees.value.filter(e => e.type_contrat === 'STAGE').length,
}));

const statusBadgeClass = (status) => {
    const map = {
        actif: 'bg-success',
        inactif: 'bg-secondary',
        suspendu: 'bg-warning text-dark',
        conge: 'bg-info',
    };
    return map[status] || 'bg-secondary';
};

const deleteEmployee = async (id) => {
    if (!confirm('Confirmer la suppression de cet employé ?')) return;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/rh/employees/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error('Erreur de suppression');
        await fetchEmployees();
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

onMounted(fetchEmployees);
</script>

<template>
    <GelLayout page-title="Gestion des Employés">
        <!-- Stats Cards -->
        <div class="row g-2 mb-4">
            <div class="col-6 col-md-3">
                <RhStatCard icon="bi-people" label="Total" :value="stats.total" color="primary" />
            </div>
            <div class="col-6 col-md-3">
                <RhStatCard icon="bi-person-check" label="Actifs" :value="stats.actifs" color="success" />
            </div>
            <div class="col-6 col-md-3">
                <RhStatCard icon="bi-file-earmark-text" label="CDI" :value="stats.cdi" color="info" />
            </div>
            <div class="col-6 col-md-3">
                <RhStatCard icon="bi-mortarboard" label="Stagiaires" :value="stats.stagiaires" color="warning" />
            </div>
        </div>

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
                    <option value="inactif">Inactif</option>
                    <option value="suspendu">Suspendu</option>
                    <option value="conge">Congé</option>
                </select>
            </div>
            <a href="/rh/employees/create" class="btn btn-primary btn-sm">
                <i class="bi-plus-lg me-1"></i>Nouvel employé
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
                            <th style="width:30px;">
                                <input type="checkbox" class="form-check-input" @change="e => selectedIds = e.target.checked ? employees.map(m => m.id) : []" :checked="selectedIds.length === employees.length && employees.length > 0">
                            </th>
                            <th>Matricule</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Poste</th>
                            <th>Département</th>
                            <th>Type contrat</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!filteredEmployees.length">
                            <td colspan="9" class="text-center py-4 text-muted">Aucun employé trouvé.</td>
                        </tr>
                        <tr v-for="emp in filteredEmployees" :key="emp.id">
                            <td><input type="checkbox" class="form-check-input" :checked="selectedIds.includes(emp.id)" @change="e => e.target.checked ? selectedIds.push(emp.id) : selectedIds = selectedIds.filter(id => id !== emp.id)"></td>
                            <td class="small fw-medium">{{ emp.matricule || '-' }}</td>
                            <td class="fw-medium">{{ emp.nom }}</td>
                            <td>{{ emp.prenom }}</td>
                            <td class="small">{{ emp.poste || '-' }}</td>
                            <td class="small">{{ emp.departement || '-' }}</td>
                            <td><span class="badge bg-secondary">{{ emp.type_contrat || '-' }}</span></td>
                            <td><span class="badge" :class="statusBadgeClass(emp.status)">{{ emp.status }}</span></td>
                            <td class="text-end">
                                <a :href="'/rh/employees/' + emp.id + '/edit'" class="btn btn-sm btn-outline-primary me-1" title="Modifier"><i class="bi-pencil"></i></a>
                                <button class="btn btn-sm btn-outline-danger" @click="deleteEmployee(emp.id)" title="Supprimer"><i class="bi-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white small text-muted d-flex justify-content-between align-items-center">
                <span>{{ filteredEmployees.length }} employé(s) sur {{ employees.length }}</span>
            </div>
        </div>
    </GelLayout>
</template>
