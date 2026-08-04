<script setup>
/* ============================================================
 * Missions — Index
 * Liste des missions, tâches et projets du cabinet.
 * Permet de filtrer, créer, modifier, supprimer et suivre
 * la progression des missions.
 * ============================================================ */
import { ref, onMounted, nextTick } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';
import { authStore } from '../../../stores/auth';

/* --- État réactif principal --- */
const missions = ref([]);      /* Liste des missions chargées depuis l'API */
const clients = ref([]);       /* Liste des clients pour le formulaire */
const poles = ref([]);         /* Liste des pôles pour le filtre et le formulaire */
const users = ref([]);         /* Liste des utilisateurs pour l'assignation */
const loading = ref(true);     /* Indicateur de chargement en cours */
const error = ref(null);       /* Message d'erreur éventuel */

/* --- Filtres de la liste --- */
const filterStatus = ref('');     /* Filtre par statut */
const filterPriority = ref('');   /* Filtre par priorité */
const filterPole = ref('');       /* Filtre par pôle */

/* --- État de la modale de création/édition --- */
const showModal = ref(false);     /* Visibilité de la modale */
const isEditing = ref(false);     /* true = édition, false = création */
const editingId = ref(null);      /* ID de la mission en cours d'édition */
const submitting = ref(false);    /* true pendant la soumission du formulaire */
const modalEl = ref(null);        /* Référence à l'élément DOM de la modale */
const modalInstance = ref(null);  /* Instance Bootstrap de la modale */

/* --- Formulaire de création/édition --- */
const form = ref({
    title: '', description: '', type: 'mission', status: 'en_attente', priority: 'moyenne',
    client_id: '', pole_id: '', assigned_to: '', start_date: '', end_date: '', budget: '', progress: 0,
});

/* --- Options pour les listes déroulantes --- */
const statusOptions = ['en_attente', 'en_cours', 'terminee', 'annulee'];
const priorityOptions = ['basse', 'moyenne', 'haute', 'critique'];
const typeOptions = ['mission', 'tache', 'projet'];

/*
 * fetchMissions — Charge la liste des missions depuis l'API
 * en appliquant les filtres actifs (statut, priorité, pôle).
 */
const fetchMissions = async () => {
    loading.value = true;
    error.value = null;
    const params = {};
    if (filterStatus.value) params.status = filterStatus.value;
    if (filterPriority.value) params.priority = filterPriority.value;
    if (filterPole.value) params.pole_id = filterPole.value;
    try {
        const res = await window.axios.get('api/missions', { params });
        missions.value = Array.isArray(res.data) ? res.data : (res.data?.data || []);
    } catch (e) {
        error.value = e.response?.data?.message || e.message;
        missions.value = [];
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchMissions();
    fetchPoles();
    fetchClients();
    fetchUsers();
});

/*
 * fetchPoles — Charge la liste des pôles pour le filtre et le formulaire.
 */
const fetchPoles = async () => {
    try {
        const res = await window.axios.get('api/poles');
        poles.value = Array.isArray(res.data) ? res.data : (res.data?.data || []);
    } catch (e) { console.error(e) }
}

/*
 * fetchClients — Charge la liste des clients pour le formulaire.
 */
const fetchClients = async () => {
    try {
        const res = await window.axios.get('api/clients');
        clients.value = Array.isArray(res.data) ? res.data : (res.data?.data || []);
    } catch (e) { console.error(e) }
}

/*
 * fetchUsers — Charge la liste des utilisateurs pour l'assignation.
 */
const fetchUsers = async () => {
    try {
        const res = await window.axios.get('api/users');
        users.value = Array.isArray(res.data) ? res.data : (res.data?.data || []);
    } catch (e) { console.error(e) }
};

/*
 * resetForm — Réinitialise le formulaire à ses valeurs par défaut.
 */
const resetForm = () => {
    form.value = {
        title: '', description: '', type: 'mission', status: 'en_attente', priority: 'moyenne',
        client_id: '', pole_id: '', assigned_to: '', start_date: '', end_date: '', budget: '', progress: 0,
    };
};

/*
 * openCreateModal — Ouvre la modale en mode création.
 * Réinitialise le formulaire et affiche l'instance Bootstrap.
 */
const openCreateModal = () => {
    resetForm();
    isEditing.value = false;
    editingId.value = null;
    showModal.value = true;
    nextTick(() => {
        if (!modalInstance.value && modalEl.value) {
            modalInstance.value = new bootstrap.Modal(modalEl.value);
        }
        modalInstance.value?.show();
    });
};

/*
 * openEditModal — Ouvre la modale en mode édition.
 * Charge les données de la mission depuis l'API et pré-remplit le formulaire.
 */
const openEditModal = async (id) => {
    try {
        const res = await window.axios.get('api/missions/' + id);
        const data = res.data;
        form.value = {
            title: data.title || '',
            description: data.description || '',
            type: data.type || 'mission',
            status: data.status || 'en_attente',
            priority: data.priority || 'moyenne',
            client_id: data.client_id || '',
            pole_id: data.pole_id || '',
            assigned_to: data.assigned_to || '',
            start_date: data.start_date || '',
            end_date: data.end_date || '',
            budget: data.budget || '',
            progress: data.progress || 0,
        };
        isEditing.value = true;
        editingId.value = id;
        showModal.value = true;
        nextTick(() => {
            if (!modalInstance.value && modalEl.value) {
                modalInstance.value = new bootstrap.Modal(modalEl.value);
            }
            modalInstance.value?.show();
        });
    } catch (e) {
        alert('Erreur: ' + (e.response?.data?.message || e.message));
    }
};

/*
 * closeModal — Ferme la modale Bootstrap.
 */
const closeModal = () => {
    modalInstance.value?.hide();
    showModal.value = false;
};

/*
 * submitForm — Envoie les données du formulaire en création ou édition.
 * Utilise la méthode HTTP appropriée (POST ou PUT) selon le mode.
 */
const submitForm = async () => {
    submitting.value = true;
    try {
        const url = isEditing.value ? 'missions/' + editingId.value : 'missions';
        const method = isEditing.value ? 'put' : 'post';
        const payload = { ...form.value, budget: form.value.budget ? Number(form.value.budget) : null };

        await window.axios[method](url, payload);

        closeModal();
        await fetchMissions();
    } catch (e) {
        alert('Erreur: ' + (e.response?.data?.message || e.message));
    } finally {
        submitting.value = false;
    }
};

/*
 * deleteMission — Supprime une mission après confirmation utilisateur.
 */
const deleteMission = async (id) => {
    if (!confirm('Confirmer la suppression ?')) return;
    try {
        await window.axios.delete('missions/' + id);
        await fetchMissions();
    } catch (e) {
        alert('Erreur: ' + (e.response?.data?.message || e.message));
    }
};

/*
 * updateProgress — Met à jour le pourcentage de progression d'une mission.
 */
const updateProgress = async (id, progress) => {
    try {
        await window.axios.patch('missions/' + id + '/progress', { progress });
        await fetchMissions();
    } catch (e) {
        alert('Erreur lors de la mise à jour');
    }
};

/* Second appel onMounted pour recharger les données au montage (complète le premier). */
onMounted(async () => {
    await Promise.all([fetchMissions()]);
});
</script>

<template>
    <GelLayout page-title="Missions">
        <!-- Filters -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <select v-model="filterStatus" class="form-select form-select-sm" style="width:auto;">
                    <option value="">Tous statuts</option>
                    <option v-for="s in statusOptions" :key="s" :value="s">{{ s }}</option>
                </select>
                <select v-model="filterPriority" class="form-select form-select-sm" style="width:auto;">
                    <option value="">Toutes priorités</option>
                    <option v-for="p in priorityOptions" :key="p" :value="p">{{ p }}</option>
                </select>
                <select v-model="filterPole" class="form-select form-select-sm" style="width:auto;">
                    <option value="">Tous pôles</option>
                    <option v-for="pole in poles" :key="pole.id" :value="pole.id">{{ pole.name }}</option>
                </select>
            </div>
            <button class="btn btn-primary btn-sm" @click="openCreateModal">
                <i class="bi-plus-lg me-1"></i>Nouvelle mission
            </button>
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
                            <th>Client</th>
                            <th>Pôle</th>
                            <th>Statut</th>
                            <th>Priorité</th>
                            <th>Dates</th>
                            <th>Progression</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!missions.length">
                            <td colspan="8" class="text-center py-4 text-muted">Aucune mission trouvée.</td>
                        </tr>
                        <tr v-for="m in missions" :key="m.id">
                            <td class="fw-medium">{{ m.title }}</td>
                            <td class="small">{{ m.client?.company_name || '-' }}</td>
                            <td>
                                <span class="badge" :style="{ backgroundColor: m.pole?.color || '#6c757d' }">{{ m.pole?.name || '-' }}</span>
                            </td>
                            <td>
                                <span class="badge" :class="m.status === 'terminee' ? 'bg-success' : m.status === 'en_cours' ? 'bg-primary' : m.status === 'en_attente' ? 'bg-warning' : 'bg-secondary'">
                                    {{ m.status }}
                                </span>
                            </td>
                            <td>
                                <span class="badge" :class="m.priority === 'haute' || m.priority === 'critique' ? 'bg-danger' : m.priority === 'moyenne' ? 'bg-warning' : 'bg-info'">
                                    {{ m.priority }}
                                </span>
                            </td>
                            <td class="small">
                                <div v-if="m.start_date">D: {{ $formatDate(m.start_date) }}</div>
                                <div v-if="m.end_date">F: {{ $formatDate(m.end_date) }}</div>
                                <span v-if="!m.start_date && !m.end_date">-</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:6px;min-width:60px;">
                                        <div class="progress-bar" :class="m.progress >= 100 ? 'bg-success' : 'bg-primary'" :style="{ width: (m.progress||0)+'%' }"></div>
                                    </div>
                                    <span class="small text-muted" style="min-width:32px;">{{ m.progress || 0 }}%</span>
                                </div>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary me-1" title="Modifier" @click="openEditModal(m.id)"><i class="bi-pencil"></i></button>
                                <a :href="'/missions/' + m.id" class="btn btn-sm btn-outline-info me-1" title="Voir"><i class="bi-eye"></i></a>
                                <button class="btn btn-sm btn-outline-danger" title="Supprimer" @click="deleteMission(m.id)"><i class="bi-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div ref="modalEl" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">{{ isEditing ? 'Modifier la mission' : 'Nouvelle mission' }}</h5>
                        <button type="button" class="btn-close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label small">Titre *</label>
                                <input v-model="form.title" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Description</label>
                                <textarea v-model="form.description" class="form-control form-control-sm" rows="2"></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Type</label>
                                <select v-model="form.type" class="form-select form-select-sm">
                                    <option v-for="t in typeOptions" :key="t" :value="t">{{ t }}</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Statut</label>
                                <select v-model="form.status" class="form-select form-select-sm">
                                    <option v-for="s in statusOptions" :key="s" :value="s">{{ s }}</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Priorité</label>
                                <select v-model="form.priority" class="form-select form-select-sm">
                                    <option v-for="p in priorityOptions" :key="p" :value="p">{{ p }}</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Client</label>
                                <select v-model="form.client_id" class="form-select form-select-sm">
                                    <option value="">Sélectionner</option>
                                    <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company_name }}</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Pôle</label>
                                <select v-model="form.pole_id" class="form-select form-select-sm">
                                    <option value="">Sélectionner</option>
                                    <option v-for="p in poles" :key="p.id" :value="p.id">{{ p.name }}</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Assigné à</label>
                                <input v-model="form.assigned_to" class="form-control form-control-sm" placeholder="Nom du responsable">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Date début</label>
                                <input v-model="form.start_date" type="date" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Date fin</label>
                                <input v-model="form.end_date" type="date" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Budget (FCFA)</label>
                                <input v-model="form.budget" type="number" class="form-control form-control-sm" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Progression: {{ form.progress }}%</label>
                                <input v-model="form.progress" type="range" class="form-range" min="0" max="100" step="5">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-secondary" @click="closeModal">Annuler</button>
                        <button class="btn btn-sm btn-primary" :disabled="submitting" @click="submitForm">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                            {{ isEditing ? 'Mettre à jour' : 'Créer' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>
