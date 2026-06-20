<script setup>
import { ref, computed, onMounted } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const alerts = ref([]);
const loading = ref(true);
const error = ref(null);
const generating = ref(false);
const search = ref('');
const typeFilter = ref('');
const statusFilter = ref('');

const fetchAlerts = async () => {
    loading.value = true;
    error.value = null;
    try {
        const res = await fetch('/rh/api/alerts');
        if (!res.ok) throw new Error('Erreur de chargement');
        alerts.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const filteredAlerts = computed(() => {
    let list = alerts.value;
    if (search.value) {
        const q = search.value.toLowerCase();
        list = list.filter(a =>
            (a.titre && a.titre.toLowerCase().includes(q)) ||
            (a.employe_nom && a.employe_nom.toLowerCase().includes(q))
        );
    }
    if (typeFilter.value) {
        list = list.filter(a => a.type === typeFilter.value);
    }
    if (statusFilter.value) {
        list = list.filter(a => a.statut === statusFilter.value);
    }
    return list;
});

const statusBadgeClass = (status) => {
    const map = {
        en_attente: 'bg-warning text-dark',
        traite: 'bg-success',
        ignore: 'bg-secondary',
    };
    return map[status] || 'bg-secondary';
};

const typeBadgeClass = (type) => {
    const map = {
        contrat: 'bg-primary',
        conge: 'bg-info',
        visite: 'bg-success',
        document: 'bg-secondary',
        paie: 'bg-warning text-dark',
        autre: 'bg-light text-dark',
    };
    return map[type] || 'bg-secondary';
};

const generateAlerts = async () => {
    if (!confirm('Générer les alertes RH automatiques ?')) return;
    generating.value = true;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/rh/api/alerts/generate', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error('Erreur de génération');
        const data = await res.json();
        await fetchAlerts();
        alert(data.message || 'Alertes générées avec succès.');
    } catch (e) {
        alert('Erreur: ' + e.message);
    } finally {
        generating.value = false;
    }
};

const updateAlertStatus = async (id, newStatus) => {
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/rh/api/alerts/' + id + '/status', {
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ statut: newStatus }),
        });
        if (!res.ok) throw new Error('Erreur de mise à jour');
        await fetchAlerts();
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

const deleteAlert = async (id) => {
    if (!confirm('Confirmer la suppression de cette alerte ?')) return;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/rh/api/alerts/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error('Erreur de suppression');
        await fetchAlerts();
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

onMounted(fetchAlerts);
</script>

<template>
    <GelLayout page-title="Alertes RH">
        <!-- Toolbar -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="input-group input-group-sm" style="max-width:220px;">
                    <span class="input-group-text bg-white"><i class="bi-search"></i></span>
                    <input v-model="search" class="form-control form-control-sm" placeholder="Rechercher...">
                </div>
                <select v-model="typeFilter" class="form-select form-select-sm" style="width:auto;">
                    <option value="">Tous types</option>
                    <option value="contrat">Contrat</option>
                    <option value="conge">Congé</option>
                    <option value="visite">Visite médicale</option>
                    <option value="document">Document</option>
                    <option value="paie">Paie</option>
                    <option value="autre">Autre</option>
                </select>
                <select v-model="statusFilter" class="form-select form-select-sm" style="width:auto;">
                    <option value="">Tous statuts</option>
                    <option value="en_attente">En attente</option>
                    <option value="traite">Traité</option>
                    <option value="ignore">Ignoré</option>
                </select>
            </div>
            <button class="btn btn-primary btn-sm" :disabled="generating" @click="generateAlerts">
                <span v-if="generating" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="bi-lightning me-1"></i>Générer alertes
            </button>
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
                        <div class="text-muted small">Total alertes</div>
                        <div class="fw-bold fs-5">{{ alerts.length }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <div class="text-muted small">En attente</div>
                        <div class="fw-bold fs-5 text-warning">{{ alerts.filter(a => a.statut === 'en_attente').length }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <div class="text-muted small">Traitées</div>
                        <div class="fw-bold fs-5 text-success">{{ alerts.filter(a => a.statut === 'traite').length }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <div class="text-muted small">Contrats</div>
                        <div class="fw-bold fs-5 text-primary">{{ alerts.filter(a => a.type === 'contrat').length }}</div>
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
                            <th>Titre</th>
                            <th>Type</th>
                            <th>Employé</th>
                            <th>Échéance</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!filteredAlerts.length">
                            <td colspan="6" class="text-center py-4 text-muted">Aucune alerte trouvée.</td>
                        </tr>
                        <tr v-for="a in filteredAlerts" :key="a.id">
                            <td class="fw-medium">{{ a.titre }}</td>
                            <td><span class="badge" :class="typeBadgeClass(a.type)">{{ a.type }}</span></td>
                            <td>{{ a.employe_nom || a.employe?.nom }} {{ a.employe_prenom || a.employe?.prenom }}</td>
                            <td class="small">{{ $formatDate ? $formatDate(a.echeance) : a.echeance }}</td>
                            <td><span class="badge" :class="statusBadgeClass(a.statut)">{{ a.statut }}</span></td>
                            <td class="text-end">
                                <button v-if="a.statut === 'en_attente'" class="btn btn-sm btn-outline-success me-1" @click="updateAlertStatus(a.id, 'traite')" title="Marquer traité"><i class="bi-check-lg"></i></button>
                                <button v-if="a.statut === 'en_attente'" class="btn btn-sm btn-outline-secondary me-1" @click="updateAlertStatus(a.id, 'ignore')" title="Ignorer"><i class="bi-eye-slash"></i></button>
                                <button class="btn btn-sm btn-outline-danger" @click="deleteAlert(a.id)" title="Supprimer"><i class="bi-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white small text-muted">
                <span>{{ filteredAlerts.length }} alerte(s) sur {{ alerts.length }}</span>
            </div>
        </div>
    </GelLayout>
</template>
