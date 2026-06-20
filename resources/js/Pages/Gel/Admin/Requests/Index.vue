<script setup>
import { ref, onMounted, nextTick } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const requests = ref([]);
const loading = ref(true);
const error = ref(null);
const submitting = ref(false);

const expandedId = ref(null);

// Status update modal
const showStatusModal = ref(false);
const editingRequest = ref(null);
const statusForm = ref({
    status: 'pending',
    admin_notes: '',
});
const modalEl = ref(null);
const modalInstance = ref(null);

const statusBadgeClass = (status) => {
    const map = { pending: 'bg-warning text-dark', contacted: 'bg-info', validated: 'bg-success', rejected: 'bg-danger' };
    return map[status] || 'bg-secondary';
};

const statusLabel = (status) => {
    const map = { pending: 'En attente', contacted: 'Contacté', validated: 'Validé', rejected: 'Rejeté' };
    return map[status] || status;
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const fetchRequests = async () => {
    loading.value = true;
    error.value = null;
    try {
        const res = await fetch('/api/requests');
        if (!res.ok) throw new Error('Erreur lors du chargement des demandes');
        requests.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const toggleExpand = (id) => {
    expandedId.value = expandedId.value === id ? null : id;
};

const openStatusModal = (req) => {
    editingRequest.value = req;
    statusForm.value = {
        status: req.status || 'pending',
        admin_notes: req.admin_notes || '',
    };
    showStatusModal.value = true;
    nextTick(() => {
        if (!modalInstance.value && modalEl.value) {
            modalInstance.value = new bootstrap.Modal(modalEl.value);
        }
        modalInstance.value?.show();
    });
};

const closeStatusModal = () => {
    modalInstance.value?.hide();
    showStatusModal.value = false;
    editingRequest.value = null;
};

const updateStatus = async () => {
    if (!editingRequest.value) return;
    submitting.value = true;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/api/requests/' + editingRequest.value.id + '/status', {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(statusForm.value),
        });

        if (!res.ok) {
            const errData = await res.json();
            throw new Error(errData.message || Object.values(errData.errors || {}).flat().join(', '));
        }
        closeStatusModal();
        await fetchRequests();
    } catch (e) {
        alert('Erreur: ' + e.message);
    } finally {
        submitting.value = false;
    }
};

const deleteRequest = async (id) => {
    if (!confirm('Confirmer la suppression de cette demande ?')) return;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/api/requests/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error('Erreur lors de la suppression');
        await fetchRequests();
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

onMounted(fetchRequests);
</script>

<template>
    <GelLayout page-title="Demandes Entreprises">
        <!-- Loading -->
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <!-- Empty -->
        <div v-else-if="!requests.length" class="text-center py-5 text-muted">
            <i class="bi-envelope" style="font-size:48px;"></i>
            <p class="mt-2 fs-5">Aucune demande entrante.</p>
        </div>

        <!-- Requests List -->
        <div v-else class="row g-3">
            <div v-for="req in requests" :key="req.id" class="col-12">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                <h6 class="fw-bold mb-0">{{ req.company_name }}</h6>
                                <span class="badge" :class="statusBadgeClass(req.status)">{{ statusLabel(req.status) }}</span>
                            </div>
                            <div class="d-flex flex-wrap gap-3 small text-muted">
                                <span><i class="bi-person me-1"></i>{{ req.contact_name }}</span>
                                <span><i class="bi-envelope me-1"></i>{{ req.email }}</span>
                                <span v-if="req.phone"><i class="bi-telephone me-1"></i>{{ req.phone }}</span>
                                <span><i class="bi-calendar me-1"></i>{{ formatDate(req.created_at) }}</span>
                            </div>
                        </div>
                        <div class="d-flex gap-1 ms-3">
                            <button class="btn btn-sm btn-outline-info" title="Changer le statut" @click="openStatusModal(req)">
                                <i class="bi-tag"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" title="Supprimer" @click="deleteRequest(req.id)">
                                <i class="bi-trash"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Expand toggle -->
                    <button class="btn btn-sm btn-link text-decoration-none p-0 mt-2" @click="toggleExpand(req.id)">
                        <i class="bi" :class="expandedId === req.id ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                        {{ expandedId === req.id ? 'Moins de détails' : 'Plus de détails' }}
                    </button>

                    <!-- Expanded details -->
                    <div v-if="expandedId === req.id" class="mt-3 pt-3 border-top">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <h6 class="small fw-bold text-muted text-uppercase">Message</h6>
                                <p class="small mb-0">{{ req.message || 'Aucun message.' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="small fw-bold text-muted text-uppercase">Notes administrateur</h6>
                                <p class="small mb-0" :class="req.admin_notes ? '' : 'fst-italic text-muted'">
                                    {{ req.admin_notes || 'Aucune note.' }}
                                </p>
                            </div>
                            <div v-if="req.services?.length" class="col-12">
                                <h6 class="small fw-bold text-muted text-uppercase">Services demandés</h6>
                                <div class="d-flex flex-wrap gap-1">
                                    <span v-for="svc in req.services" :key="svc.id" class="badge badge-eden">
                                        {{ svc.name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Update Modal -->
        <div ref="modalEl" class="modal fade" tabindex="-1" @hidden.self="showStatusModal = false; editingRequest = null">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Mettre à jour le statut</h5>
                        <button type="button" class="btn-close" @click="closeStatusModal"></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="editingRequest" class="mb-3">
                            <h6 class="fw-semibold">{{ editingRequest.company_name }}</h6>
                            <p class="small text-muted mb-0">{{ editingRequest.contact_name }} - {{ editingRequest.email }}</p>
                        </div>
                        <form @submit.prevent="updateStatus">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label small">Statut *</label>
                                    <select v-model="statusForm.status" class="form-select form-select-sm" required>
                                        <option value="pending">En attente</option>
                                        <option value="contacted">Contacté</option>
                                        <option value="validated">Validé</option>
                                        <option value="rejected">Rejeté</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small">Notes administrateur</label>
                                    <textarea v-model="statusForm.admin_notes" class="form-control form-control-sm" rows="3" placeholder="Ajouter des notes internes..."></textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" @click="closeStatusModal">Annuler</button>
                        <button type="button" class="btn btn-sm btn-primary" :disabled="submitting" @click="updateStatus">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                            Mettre à jour
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>
