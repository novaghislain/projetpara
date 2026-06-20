<script setup>
import { ref, computed, onMounted } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const attendances = ref([]);
const loading = ref(true);
const error = ref(null);
const search = ref('');
const dateFilter = ref('');
const presenceFilter = ref('');

const fetchAttendance = async () => {
    loading.value = true;
    error.value = null;
    try {
        const params = new URLSearchParams();
        if (dateFilter.value) params.append('date', dateFilter.value);
        if (presenceFilter.value) params.append('presence', presenceFilter.value);
        const url = '/rh/attendance' + (params.toString() ? '?' + params.toString() : '');
        const res = await fetch(url);
        if (!res.ok) throw new Error('Erreur de chargement');
        attendances.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const filteredAttendance = computed(() => {
    let list = attendances.value;
    if (search.value) {
        const q = search.value.toLowerCase();
        list = list.filter(a =>
            (a.employe_nom && a.employe_nom.toLowerCase().includes(q)) ||
            (a.employe_prenom && a.employe_prenom.toLowerCase().includes(q))
        );
    }
    return list;
});

const presenceBadgeClass = (presence) => {
    const map = {
        present: 'bg-success',
        absent: 'bg-danger',
        retard: 'bg-warning text-dark',
        conge: 'bg-info',
        mission: 'bg-primary',
    };
    return map[presence] || 'bg-secondary';
};

const formatHeures = (heures) => {
    if (heures === null || heures === undefined) return '-';
    return Number(heures).toFixed(2) + 'h';
};

const deleteAttendance = async (id) => {
    if (!confirm('Confirmer la suppression de cette entrée ?')) return;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/rh/attendance/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error('Erreur de suppression');
        await fetchAttendance();
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

onMounted(fetchAttendance);
</script>

<template>
    <GelLayout page-title="Pointage">
        <!-- Toolbar -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="input-group input-group-sm" style="max-width:260px;">
                    <span class="input-group-text bg-white"><i class="bi-search"></i></span>
                    <input v-model="search" class="form-control form-control-sm" placeholder="Rechercher employé...">
                </div>
                <input v-model="dateFilter" type="date" class="form-control form-control-sm" style="width:160px;" @change="fetchAttendance">
                <select v-model="presenceFilter" class="form-select form-select-sm" style="width:auto;" @change="fetchAttendance">
                    <option value="">Tous</option>
                    <option value="present">Présent</option>
                    <option value="absent">Absent</option>
                    <option value="retard">Retard</option>
                    <option value="conge">Congé</option>
                    <option value="mission">Mission</option>
                </select>
            </div>
            <a href="/rh/attendance/create" class="btn btn-primary btn-sm">
                <i class="bi-plus-lg me-1"></i>Nouveau pointage
            </a>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <!-- Summary -->
        <div v-else class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <div class="text-muted small">Présents</div>
                        <div class="fw-bold fs-5 text-success">{{ attendances.filter(a => a.presence === 'present').length }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <div class="text-muted small">Absents</div>
                        <div class="fw-bold fs-5 text-danger">{{ attendances.filter(a => a.presence === 'absent').length }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <div class="text-muted small">Retards</div>
                        <div class="fw-bold fs-5 text-warning">{{ attendances.filter(a => a.presence === 'retard').length }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <div class="text-muted small">Total</div>
                        <div class="fw-bold fs-5">{{ attendances.length }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div v-if="!loading && !error" class="card card-dashboard">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="small text-muted">
                        <tr>
                            <th>Employé</th>
                            <th>Date</th>
                            <th>Arrivée</th>
                            <th>Départ</th>
                            <th>Heures</th>
                            <th>Présence</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!filteredAttendance.length">
                            <td colspan="7" class="text-center py-4 text-muted">Aucun pointage trouvé.</td>
                        </tr>
                        <tr v-for="a in filteredAttendance" :key="a.id">
                            <td class="fw-medium">{{ a.employe_nom || a.employe?.nom }} {{ a.employe_prenom || a.employe?.prenom }}</td>
                            <td class="small">{{ $formatDate ? $formatDate(a.date) : a.date }}</td>
                            <td class="small">{{ a.arrivee || '-' }}</td>
                            <td class="small">{{ a.depart || '-' }}</td>
                            <td class="fw-medium">{{ formatHeures(a.heures || a.nb_heures) }}</td>
                            <td><span class="badge" :class="presenceBadgeClass(a.presence)">{{ a.presence }}</span></td>
                            <td class="text-end">
                                <a :href="'/rh/attendance/' + a.id + '/edit'" class="btn btn-sm btn-outline-primary me-1" title="Modifier"><i class="bi-pencil"></i></a>
                                <button class="btn btn-sm btn-outline-danger" @click="deleteAttendance(a.id)" title="Supprimer"><i class="bi-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white small text-muted">
                <span>{{ filteredAttendance.length }} pointage(s) sur {{ attendances.length }}</span>
            </div>
        </div>
    </GelLayout>
</template>
