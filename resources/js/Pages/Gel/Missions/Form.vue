<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';
import { authStore } from '../../../stores/auth';

const props = defineProps({
    missionId: { type: [Number, String], default: null }
});

const mission = ref(null);
const clients = ref([]);
const poles = ref([]);
const loading = ref(true);
const submitting = ref(false);
const error = ref(null);
const saved = ref(false);

const form = ref({
    title: '', description: '', type: 'mission', status: 'en_attente', priority: 'moyenne',
    client_id: '', pole_id: '', assigned_to: '', start_date: '', end_date: '', budget: '', progress: 0,
});

const statusOptions = ['en_attente', 'en_cours', 'terminee', 'annulee'];
const priorityOptions = ['basse', 'moyenne', 'haute', 'critique'];
const typeOptions = ['mission', 'tache', 'projet'];

const fetchData = async () => {
    loading.value = true;
    error.value = null;
    try {
        const [clientsRes, polesRes] = await Promise.all([
            fetch('/api/clients'), fetch('/api/poles')
        ]);
        if (clientsRes.ok) clients.value = await clientsRes.json();
        if (polesRes.ok) poles.value = await polesRes.json();

        if (props.missionId) {
            const res = await fetch('/api/missions/' + props.missionId);
            if (!res.ok) throw new Error('Erreur de chargement de la mission');
            const data = await res.json();
            mission.value = data;
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
        }
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const submitForm = async () => {
    submitting.value = true;
    saved.value = false;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const isEdit = !!props.missionId;
        const url = isEdit ? '/missions/' + props.missionId : '/missions';
        const method = isEdit ? 'PUT' : 'POST';
        const payload = { ...form.value, budget: form.value.budget ? Number(form.value.budget) : null };

        const res = await fetch(url, {
            method,
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload),
        });
        if (!res.ok) {
            const errData = await res.json();
            throw new Error(errData.message || Object.values(errData.errors || {}).flat().join(', '));
        }
        saved.value = true;
        if (!isEdit) {
            const data = await res.json();
            if (data.id) window.location.href = '/missions/' + data.id;
        }
    } catch (e) {
        alert('Erreur: ' + e.message);
    } finally {
        submitting.value = false;
    }
};

const goBack = () => {
    window.history.back();
};

onMounted(fetchData);
</script>

<template>
    <GelLayout :page-title="missionId ? (mission?.title || 'Modifier la mission') : 'Nouvelle mission'">
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <div v-else class="card card-dashboard p-4">
            <div v-if="saved" class="alert alert-success d-flex align-items-center gap-2">
                <i class="bi-check-circle-fill"></i> Mission enregistrée avec succès.
            </div>

            <form @submit.prevent="submitForm">
                <div class="row g-3">
                    <div class="col-12">
                        <h6 class="fw-bold text-primary border-bottom pb-2">Informations générales</h6>
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Titre *</label>
                        <input v-model="form.title" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Description</label>
                        <textarea v-model="form.description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Type</label>
                        <select v-model="form.type" class="form-select">
                            <option v-for="t in typeOptions" :key="t" :value="t">{{ t }}</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Statut</label>
                        <select v-model="form.status" class="form-select">
                            <option v-for="s in statusOptions" :key="s" :value="s">{{ s }}</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Priorité</label>
                        <select v-model="form.priority" class="form-select">
                            <option v-for="p in priorityOptions" :key="p" :value="p">{{ p }}</option>
                        </select>
                    </div>

                    <div class="col-12 mt-3">
                        <h6 class="fw-bold text-primary border-bottom pb-2">Affectation</h6>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Client</label>
                        <select v-model="form.client_id" class="form-select">
                            <option value="">Sélectionner un client</option>
                            <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company_name }}</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Pôle</label>
                        <select v-model="form.pole_id" class="form-select">
                            <option value="">Sélectionner un pôle</option>
                            <option v-for="p in poles" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Assigné à</label>
                        <input v-model="form.assigned_to" class="form-control" placeholder="Nom du responsable">
                    </div>

                    <div class="col-12 mt-3">
                        <h6 class="fw-bold text-primary border-bottom pb-2">Planning & Budget</h6>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Date début</label>
                        <input v-model="form.start_date" type="date" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Date fin</label>
                        <input v-model="form.end_date" type="date" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Budget (FCFA)</label>
                        <input v-model="form.budget" type="number" class="form-control" min="0" step="0.01">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Progression: {{ form.progress }}%</label>
                        <input v-model="form.progress" type="range" class="form-range" min="0" max="100" step="5">
                    </div>

                    <div class="col-12 mt-4 d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-secondary" @click="goBack">
                            <i class="bi-arrow-left me-1"></i>Retour
                        </button>
                        <button type="submit" class="btn btn-sm btn-primary" :disabled="submitting">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                            {{ missionId ? 'Mettre à jour' : 'Créer la mission' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </GelLayout>
</template>
