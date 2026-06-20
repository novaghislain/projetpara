<script setup>
import { ref, computed, onMounted } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const leaves = ref([]);
const loading = ref(true);
const error = ref(null);
const search = ref('');
const statusFilter = ref('');

const fetchLeaves = async () => {
    loading.value = true;
    error.value = null;
    try {
        const res = await fetch('/rh/leaves');
        if (!res.ok) throw new Error('Erreur de chargement');
        leaves.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const filteredLeaves = computed(() => {
    let list = leaves.value;
    if (search.value) {
        const q = search.value.toLowerCase();
        list = list.filter(l =>
            (l.employe_nom && l.employe_nom.toLowerCase().includes(q)) ||
            (l.type && l.type.toLowerCase().includes(q))
        );
    }
    if (statusFilter.value) {
        list = list.filter(l => l.statut === statusFilter.value);
    }
    return list;
});

const statusBadgeClass = (status) => {
    const map = {
        en_attente: 'bg-warning text-dark',
        approuve: 'bg-success',
        rejete: 'bg-danger',
        annule: 'bg-secondary',
    };
    return map[status] || 'bg-secondary';
};

const updateStatus = async (id, newStatus) => {
    const actionLabel = newStatus === 'approuve' ? 'approuver' : 'rejeter';
    if (!confirm('Confirmer la ' + actionLabel + ' de cette demande ?')) return;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/rh/leaves/' + id + '/status', {
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ statut: newStatus }),
        });
        if (!res.ok) throw new Error('Erreur de mise à jour');
        await fetchLeaves();
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

const deleteLeave = async (id) => {
    if (!confirm('Confirmer la suppression de cette demande ?')) return;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/rh/leaves/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error('Erreur de suppression');
        await fetchLeaves();
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

onMounted(fetchLeaves);
</script>

<template>
    <GelLayout page-title="Congés">
        <!-- Toolbar -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="input-group input-group-sm" style="max-width:260px;">
                    <span class="input-group-text bg-white"><i class="bi-search"></i></span>
                    <input v-model="search" class="form-control form-control-sm" placeholder="Rechercher employé...">
                </div>
                <select v-model="statusFilter" class="form-select form-select-sm" style="width:auto;">
                    <option value="">Tous statuts</option>
                    <option value="en_attente">En attente</option>
                    <option value="approuve">Approuvé</option>
                    <option value="rejete">Rejeté</option>
                    <option value="annule">Annulé</option>
                </select>
            </div>
            <a href="/rh/leaves/create" class="btn btn-primary btn-sm">
                <i class="bi-plus-lg me-1"></i>Nouvelle demande
            </a>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <!-- Summary cards -->
        <div v-else class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <div class="text-muted small">En attente</div>
                        <div class="fw-bold fs-5 text-warning">{{ leaves.filter(l => l.statut === 'en_attente').length }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <div class="text-muted small">Approuvés</div>
                        <div class="fw-bold fs-5 text-success">{{ leaves.filter(l => l.statut === 'approuve').length }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <div class="text-muted small">Rejetés</div>
                        <div class="fw-bold fs-5 text-danger">{{ leaves.filter(l => l.statut === 'rejete').length }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <div class="text-muted small">Total</div>
                        <div class="fw-bold fs-5">{{ leaves.length }}</div>
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
                            <th>Type</th>
                            <th>Date début</th>
                            <th>Date fin</th>
                            <th>Durée (jours)</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!filteredLeaves.length">
                            <td colspan="7" class="text-center py-4 text-muted">Aucune demande de congé trouvée.</td>
                        </tr>
                        <tr v-for="l in filteredLeaves" :key="l.id">
                            <td class="fw-medium">{{ l.employe_nom || l.employe?.nom }} {{ l.employe_prenom || l.employe?.prenom }}</td>
                            <td><span class="badge bg-info">{{ l.type }}</span></td>
                            <td class="small">{{ $formatDate ? $formatDate(l.date_debut) : l.date_debut }}</td>
                            <td class="small">{{ $formatDate ? $formatDate(l.date_fin) : l.date_fin }}</td>
                            <td class="fw-medium">{{ l.duree_jours || '-' }}</td>
                            <td><span class="badge" :class="statusBadgeClass(l.statut)">{{ l.statut }}</span></td>
                            <td class="text-end">
                                <button v-if="l.statut === 'en_attente'" class="btn btn-sm btn-outline-success me-1" @click="updateStatus(l.id, 'approuve')" title="Approuver"><i class="bi-check-lg"></i></button>
                                <button v-if="l.statut === 'en_attente'" class="btn btn-sm btn-outline-danger me-1" @click="updateStatus(l.id, 'rejete')" title="Rejeter"><i class="bi-x-lg"></i></button>
                                <a :href="'/rh/leaves/' + l.id + '/edit'" class="btn btn-sm btn-outline-primary me-1" title="Modifier"><i class="bi-pencil"></i></a>
                                <button class="btn btn-sm btn-outline-danger" @click="deleteLeave(l.id)" title="Supprimer"><i class="bi-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white small text-muted">
                <span>{{ filteredLeaves.length }} demande(s) sur {{ leaves.length }}</span>
            </div>
        </div>
    </GelLayout>
</template>
