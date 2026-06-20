<script setup>
import { ref, computed, onMounted } from 'vue';
import CompanyLayout from '../../../Layouts/CompanyLayout.vue';
import { authStore } from '../../../stores/auth';

const trainings = ref([]);
const loading = ref(true);
const error = ref(null);

const fetchTrainings = async () => {
    loading.value = true;
    error.value = null;
    try {
        const res = await fetch('/company/rh/api/trainings');
        if (!res.ok) throw new Error('Erreur de chargement');
        const data = await res.json();
        trainings.value = data.data || data;
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const statusBadgeClass = (status) => {
    const map = {
        planifie: 'bg-info',
        en_cours: 'bg-primary',
        termine: 'bg-success',
        annule: 'bg-danger',
    };
    return map[status] || 'bg-secondary';
};

onMounted(fetchTrainings);
</script>

<template>
    <CompanyLayout page-title="Mes Formations">
        <div class="mb-3">
            <span class="small text-muted">{{ trainings.length }} formation(s)</span>
        </div>

        <!-- Summary -->
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3 text-center">
                        <div class="text-muted small">Planifiées</div>
                        <div class="fw-bold fs-5 text-info">{{ trainings.filter(t => t.statut === 'planifie').length }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3 text-center">
                        <div class="text-muted small">En cours</div>
                        <div class="fw-bold fs-5 text-primary">{{ trainings.filter(t => t.statut === 'en_cours').length }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3 text-center">
                        <div class="text-muted small">Terminées</div>
                        <div class="fw-bold fs-5 text-success">{{ trainings.filter(t => t.statut === 'termine').length }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3 text-center">
                        <div class="text-muted small">Total</div>
                        <div class="fw-bold fs-5">{{ trainings.length }}</div>
                    </div>
                </div>
            </div>
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
                            <th>Titre</th>
                            <th>Organisme</th>
                            <th>Date début</th>
                            <th>Date fin</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!trainings.length">
                            <td colspan="5" class="text-center py-4 text-muted">Aucune formation trouvée.</td>
                        </tr>
                        <tr v-for="t in trainings" :key="t.id">
                            <td class="fw-medium">{{ t.titre }}</td>
                            <td class="small">{{ t.organisme || '-' }}</td>
                            <td class="small">{{ $formatDate ? $formatDate(t.date_debut) : t.date_debut }}</td>
                            <td class="small">{{ t.date_fin ? ($formatDate ? $formatDate(t.date_fin) : t.date_fin) : '-' }}</td>
                            <td><span class="badge" :class="statusBadgeClass(t.statut)">{{ t.statut }}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </CompanyLayout>
</template>
