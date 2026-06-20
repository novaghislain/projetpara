<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';
import { authStore } from '../../../stores/auth';

const props = defineProps({
    poleId: { type: [Number, String], required: true }
});

const pole = ref(null);
const loading = ref(true);
const error = ref(null);

const fetchPole = async () => {
    loading.value = true;
    error.value = null;
    try {
        const res = await fetch('/api/poles/' + props.poleId);
        if (!res.ok) throw new Error('Erreur de chargement');
        pole.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

onMounted(fetchPole);
</script>

<template>
    <GelLayout :page-title="pole?.name || 'Pôle'">
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <template v-else-if="pole">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow p-6 mb-4">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-2 d-flex align-items-center justify-content-center" :style="{ backgroundColor: (pole.color || '#1a237e') + '20', width: '56px', height: '56px', borderRadius: '14px' }">
                            <i class="bi-diagram-3" :style="{ color: pole.color || '#1a237e', fontSize: '28px' }"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold">{{ pole.name }}</h4>
                            <span class="small text-muted">{{ pole.slug }}</span>
                            <span class="badge ms-2" :class="pole.is_active ? 'bg-success' : 'bg-secondary'">
                                {{ pole.is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <a :href="'/poles/' + pole.id + '/edit'" class="btn btn-sm btn-outline-primary">
                            <i class="bi-pencil me-1"></i>Modifier
                        </a>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h6 class="fw-bold mb-3"><i class="bi-info-circle me-2 text-primary"></i>Description</h6>
                        <p class="small mb-0">{{ pole.description || 'Aucune description.' }}</p>
                        <hr>
                        <div class="d-flex align-items-center gap-2">
                            <span class="small text-muted">Couleur:</span>
                            <span class="badge" :style="{ backgroundColor: pole.color || '#6c757d' }">&nbsp;&nbsp;&nbsp;</span>
                            <span class="small">{{ pole.color }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="card-header bg-white px-0 pt-0">
                            <h6 class="fw-bold mb-3"><i class="bi-check2-square me-2 text-info"></i>Missions ({{ pole.missions?.length || 0 }})</h6>
                        </div>
                        <div v-if="!pole.missions?.length" class="text-muted small py-4 text-center">Aucune mission dans ce pôle.</div>
                        <table v-else class="table table-hover align-middle mb-0">
                            <thead class="small text-muted">
                                <tr><th>Titre</th><th>Client</th><th>Statut</th><th>Priorité</th><th>Progression</th><th>Échéance</th></tr>
                            </thead>
                            <tbody>
                                <tr v-for="m in pole.missions" :key="m.id">
                                    <td><a :href="'/missions/' + m.id" class="text-decoration-none fw-medium">{{ m.title }}</a></td>
                                    <td class="small">{{ m.client?.company_name || '-' }}</td>
                                    <td>
                                        <span class="badge" :class="m.status === 'terminee' ? 'bg-success' : m.status === 'en_cours' ? 'bg-primary' : m.status === 'en_attente' ? 'bg-warning' : 'bg-secondary'">
                                            {{ m.status }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge" :class="m.priority === 'haute' ? 'bg-danger' : m.priority === 'moyenne' ? 'bg-warning' : 'bg-info'">
                                            {{ m.priority }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height:6px;">
                                                <div class="progress-bar" :class="m.progress >= 100 ? 'bg-success' : 'bg-primary'" :style="{ width: (m.progress||0)+'%' }"></div>
                                            </div>
                                            <span class="small">{{ m.progress || 0 }}%</span>
                                        </div>
                                    </td>
                                    <td class="small">{{ m.end_date ? $formatDate(m.end_date) : '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </template>
    </GelLayout>
</template>
