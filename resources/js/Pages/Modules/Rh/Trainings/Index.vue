<script setup>
import { ref, computed, onMounted } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const trainings = ref([]);
const loading = ref(true);
const error = ref(null);
const search = ref('');
const statusFilter = ref('');

const fetchTrainings = async () => {
    loading.value = true;
    error.value = null;
    try {
        const res = await fetch('/rh/trainings');
        if (!res.ok) throw new Error('Erreur de chargement');
        trainings.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const filteredTrainings = computed(() => {
    let list = trainings.value;
    if (search.value) {
        const q = search.value.toLowerCase();
        list = list.filter(t =>
            (t.titre && t.titre.toLowerCase().includes(q)) ||
            (t.organisme && t.organisme.toLowerCase().includes(q)) ||
            (t.employe_nom && t.employe_nom.toLowerCase().includes(q))
        );
    }
    if (statusFilter.value) {
        list = list.filter(t => t.statut === statusFilter.value);
    }
    return list;
});

const statusBadgeClass = (status) => {
    const map = {
        planifie: 'bg-info',
        en_cours: 'bg-primary',
        termine: 'bg-success',
        annule: 'bg-danger',
    };
    return map[status] || 'bg-secondary';
};

const deleteTraining = async (id) => {
    if (!confirm('Confirmer la suppression de cette formation ?')) return;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/rh/trainings/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error('Erreur de suppression');
        await fetchTrainings();
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

onMounted(fetchTrainings);
</script>

<template>
    <GelLayout page-title="Formations">
        <!-- Toolbar -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="input-group input-group-sm" style="max-width:260px;">
                    <span class="input-group-text bg-white"><i class="bi-search"></i></span>
                    <input v-model="search" class="form-control form-control-sm" placeholder="Rechercher...">
                </div>
                <select v-model="statusFilter" class="form-select form-select-sm" style="width:auto;">
                    <option value="">Tous statuts</option>
                    <option value="planifie">Planifié</option>
                    <option value="en_cours">En cours</option>
                    <option value="termine">Terminé</option>
                    <option value="annule">Annulé</option>
                </select>
            </div>
            <a href="/rh/trainings/create" class="btn btn-primary btn-sm">
                <i class="bi-plus-lg me-1"></i>Nouvelle formation
            </a>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <!-- Stats -->
        <div v-else class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <div class="text-muted small">Total formations</div>
                        <div class="fw-bold fs-5">{{ trainings.length }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <div class="text-muted small">Planifiées</div>
                        <div class="fw-bold fs-5 text-info">{{ trainings.filter(t => t.statut === 'planifie').length }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <div class="text-muted small">En cours</div>
                        <div class="fw-bold fs-5 text-primary">{{ trainings.filter(t => t.statut === 'en_cours').length }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <div class="text-muted small">Terminées</div>
                        <div class="fw-bold fs-5 text-success">{{ trainings.filter(t => t.statut === 'termine').length }}</div>
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
                            <th>Titre</th>
                            <th>Organisme</th>
                            <th>Date début</th>
                            <th>Date fin</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!filteredTrainings.length">
                            <td colspan="7" class="text-center py-4 text-muted">Aucune formation trouvée.</td>
                        </tr>
                        <tr v-for="t in filteredTrainings" :key="t.id">
                            <td class="fw-medium">{{ t.employe_nom || t.employe?.nom }} {{ t.employe_prenom || t.employe?.prenom }}</td>
                            <td>{{ t.titre }}</td>
                            <td class="small">{{ t.organisme || '-' }}</td>
                            <td class="small">{{ $formatDate ? $formatDate(t.date_debut) : t.date_debut }}</td>
                            <td class="small">{{ t.date_fin ? ($formatDate ? $formatDate(t.date_fin) : t.date_fin) : '-' }}</td>
                            <td><span class="badge" :class="statusBadgeClass(t.statut)">{{ t.statut }}</span></td>
                            <td class="text-end">
                                <a :href="'/rh/trainings/' + t.id + '/edit'" class="btn btn-sm btn-outline-primary me-1" title="Modifier"><i class="bi-pencil"></i></a>
                                <button class="btn btn-sm btn-outline-danger" @click="deleteTraining(t.id)" title="Supprimer"><i class="bi-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white small text-muted">
                <span>{{ filteredTrainings.length }} formation(s) sur {{ trainings.length }}</span>
            </div>
        </div>
    </GelLayout>
</template>
