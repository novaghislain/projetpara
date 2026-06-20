<script setup>
import { ref, computed, onMounted } from 'vue';
import CompanyLayout from '../../../Layouts/CompanyLayout.vue';
import { authStore } from '../../../stores/auth';

const leaves = ref([]);
const loading = ref(true);
const error = ref(null);
const submitting = ref(false);
const showCreateModal = ref(false);

const form = ref({
    type: 'conge_paye',
    date_debut: '',
    date_fin: '',
    motif: '',
});

const fetchLeaves = async () => {
    loading.value = true;
    error.value = null;
    try {
        const res = await fetch('/company/rh/api/leaves');
        if (!res.ok) throw new Error('Erreur de chargement');
        leaves.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const statusBadgeClass = (status) => {
    const map = {
        en_attente: 'bg-warning text-dark',
        approuve: 'bg-success',
        rejete: 'bg-danger',
        annule: 'bg-secondary',
    };
    return map[status] || 'bg-secondary';
};

const dureeJours = computed(() => {
    if (!form.value.date_debut || !form.value.date_fin) return 0;
    const debut = new Date(form.value.date_debut);
    const fin = new Date(form.value.date_fin);
    const diff = fin.getTime() - debut.getTime();
    return Math.max(0, Math.ceil(diff / (1000 * 3600 * 24)) + 1);
});

const submitLeaveRequest = async () => {
    submitting.value = true;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/company/rh/api/leaves', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(form.value),
        });
        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || 'Erreur lors de la création');
        }
        showCreateModal.value = false;
        form.value = { type: 'conge_paye', date_debut: '', date_fin: '', motif: '' };
        await fetchLeaves();
    } catch (e) {
        alert('Erreur: ' + e.message);
    } finally {
        submitting.value = false;
    }
};

const cancelLeave = async (id) => {
    if (!confirm('Confirmer l\'annulation de cette demande ?')) return;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        await fetch('/company/rh/api/leaves/' + id + '/cancel', {
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        await fetchLeaves();
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

onMounted(fetchLeaves);
</script>

<template>
    <CompanyLayout page-title="Mes Congés">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <span class="small text-muted">{{ leaves.length }} demande(s)</span>
            </div>
            <button class="btn btn-primary btn-sm" @click="showCreateModal = true">
                <i class="bi-plus-lg me-1"></i>Nouvelle demande
            </button>
        </div>

        <!-- Summary -->
        <div class="row g-2 mb-3">
            <div class="col-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3 text-center">
                        <div class="text-muted small">En attente</div>
                        <div class="fw-bold fs-5 text-warning">{{ leaves.filter(l => l.statut === 'en_attente').length }}</div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3 text-center">
                        <div class="text-muted small">Approuvés</div>
                        <div class="fw-bold fs-5 text-success">{{ leaves.filter(l => l.statut === 'approuve').length }}</div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3 text-center">
                        <div class="text-muted small">Rejetés</div>
                        <div class="fw-bold fs-5 text-danger">{{ leaves.filter(l => l.statut === 'rejete').length }}</div>
                    </div>
                </div>
            </div>
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
                            <th>Type</th>
                            <th>Date début</th>
                            <th>Date fin</th>
                            <th>Durée (jours)</th>
                            <th>Motif</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!leaves.length">
                            <td colspan="7" class="text-center py-4 text-muted">Aucune demande de congé.</td>
                        </tr>
                        <tr v-for="l in leaves" :key="l.id">
                            <td><span class="badge bg-info">{{ l.type }}</span></td>
                            <td class="small">{{ $formatDate ? $formatDate(l.date_debut) : l.date_debut }}</td>
                            <td class="small">{{ $formatDate ? $formatDate(l.date_fin) : l.date_fin }}</td>
                            <td class="fw-medium">{{ l.duree_jours || '-' }}</td>
                            <td class="small text-muted text-truncate" style="max-width:200px;">{{ l.motif || '-' }}</td>
                            <td><span class="badge" :class="statusBadgeClass(l.statut)">{{ l.statut }}</span></td>
                            <td class="text-end">
                                <button v-if="l.statut === 'en_attente'" class="btn btn-sm btn-outline-secondary" @click="cancelLeave(l.id)" title="Annuler"><i class="bi-x-lg"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create Modal -->
        <div v-if="showCreateModal" class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="fw-bold mb-0">Nouvelle demande de congé</h6>
                        <button class="btn-close" @click="showCreateModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label small">Type de congé *</label>
                                <select v-model="form.type" class="form-select form-select-sm">
                                    <option value="conge_paye">Congé payé</option>
                                    <option value="conge_maladie">Congé maladie</option>
                                    <option value="conge_maternite">Congé maternité</option>
                                    <option value="conge_sans_solde">Congé sans solde</option>
                                    <option value="autorisation">Autorisation d'absence</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Date début *</label>
                                <input v-model="form.date_debut" type="date" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Date fin *</label>
                                <input v-model="form.date_fin" type="date" class="form-control form-control-sm" required>
                            </div>
                            <div v-if="dureeJours > 0" class="col-12">
                                <div class="alert alert-info py-1 px-2 small mb-0">
                                    Durée : <strong>{{ dureeJours }} jour(s)</strong>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Motif</label>
                                <textarea v-model="form.motif" class="form-control form-control-sm" rows="3" placeholder="Raison de la demande..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-secondary" @click="showCreateModal = false">Annuler</button>
                        <button class="btn btn-sm btn-primary" :disabled="submitting || !form.date_debut || !form.date_fin" @click="submitLeaveRequest">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                            Envoyer la demande
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </CompanyLayout>
</template>
