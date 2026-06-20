<script setup>
import { ref, onMounted, nextTick } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';

const licenses = ref([]);
const clients = ref([]);
const services = ref([]);
const loading = ref(true);
const error = ref(null);
const submitting = ref(false);

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const modalEl = ref(null);
const modalInstance = ref(null);

const form = ref({
    client_id: '',
    service_id: '',
    duration_months: 12,
    start_date: '',
    price: '',
});

const statusLabel = (status) => {
    const map = { active: 'Actif', expired: 'Expiré', revoked: 'Révoqué' };
    return map[status] || status;
};

const durationLabel = (months) => {
    const map = { 12: '12 mois', 24: '24 mois', 36: '36 mois' };
    return map[months] || months + ' mois';
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' });
};

const formatCurrency = (value) => {
    if (value === null || value === undefined || isNaN(value)) return '0,00';
    return Number(value).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' FCFA';
};

const fetchLicenses = async () => {
    loading.value = true;
    error.value = null;
    try {
        const res = await fetch('/api/licenses');
        if (!res.ok) throw new Error('Erreur lors du chargement des licences');
        licenses.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const fetchClients = async () => {
    try {
        const res = await fetch('/api/clients');
        if (res.ok) clients.value = await res.json();
    } catch (e) { /* non-critique */ }
};

const fetchServices = async () => {
    try {
        const res = await fetch('/api/services');
        if (res.ok) services.value = await res.json();
    } catch (e) { /* non-critique */ }
};

const resetForm = () => {
    form.value = {
        client_id: '',
        service_id: '',
        duration_months: 12,
        start_date: '',
        price: '',
    };
};

const openCreateModal = async () => {
    await nextTick();
    resetForm();
    isEditing.value = false;
    editingId.value = null;
    showModal.value = true;
    if (!modalInstance.value && modalEl.value) {
        modalInstance.value = new bootstrap.Modal(modalEl.value);
    }
    modalInstance.value?.show();
};

const openEditModal = async (id) => {
    try {
        const res = await fetch('/api/licenses/' + id);
        if (!res.ok) throw new Error('Erreur de chargement');
        const data = await res.json();
        form.value = {
            client_id: data.client_id || '',
            service_id: data.service_id || '',
            duration_months: data.duration_months || 12,
            start_date: data.start_date ? data.start_date.slice(0, 10) : '',
            price: data.price || '',
        };
        isEditing.value = true;
        editingId.value = id;
        showModal.value = true;
        await nextTick();
        if (!modalInstance.value && modalEl.value) {
            modalInstance.value = new bootstrap.Modal(modalEl.value);
        }
        modalInstance.value?.show();
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

const closeModal = () => {
    modalInstance.value?.hide();
    showModal.value = false;
};

const submitForm = async () => {
    submitting.value = true;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const url = isEditing.value ? '/api/licenses/' + editingId.value : '/api/licenses';
        const method = isEditing.value ? 'PUT' : 'POST';

        const res = await fetch(url, {
            method,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(form.value),
        });

        if (!res.ok) {
            const errData = await res.json();
            throw new Error(errData.message || Object.values(errData.errors || {}).flat().join(', '));
        }
        closeModal();
        await fetchLicenses();
    } catch (e) {
        alert('Erreur: ' + e.message);
    } finally {
        submitting.value = false;
    }
};

const deleteLicense = async (id) => {
    if (!confirm('Confirmer la suppression de cette licence ?')) return;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/api/licenses/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error('Erreur lors de la suppression');
        await fetchLicenses();
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

onMounted(async () => {
    await Promise.all([fetchLicenses(), fetchClients(), fetchServices()]);
});
</script>

<template>
    <GelLayout page-title="Gestion des Licences">
        <!-- Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h5 class="fw-bold mb-1">Gestion des Licences</h5>
                <p class="text-muted small mb-0">Licences, abonnements et accès des clients</p>
            </div>
            <button class="btn btn-primary btn-sm" @click="openCreateModal">
                <i class="bi-plus-lg me-1"></i>Nouvelle licence
            </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement…</span></div>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="alert alert-danger">
            <i class="bi-exclamation-triangle-fill me-2"></i>{{ error }}
            <button @click="fetchLicenses" class="btn btn-primary btn-sm ms-3">
                <i class="bi-arrow-clockwise me-1"></i>Réessayer
            </button>
        </div>

        <!-- Empty -->
        <div v-else-if="!licenses.length" class="text-center py-5 text-muted">
            <i class="bi-key" style="font-size:48px; color:#dce3ee; display:block; margin-bottom:12px;"></i>
            <p class="mb-3">Aucune licence enregistrée.</p>
            <button class="btn btn-primary btn-sm" @click="openCreateModal">
                <i class="bi-plus-lg me-1"></i>Créer une licence
            </button>
        </div>

        <!-- Table -->
        <div v-else class="bg-white rounded-lg shadow p-6">
            <h6 class="fw-bold mb-3"><i class="bi-key me-2" style="color:#FF7900;"></i>Liste des licences</h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="small text-muted">
                        <tr>
                            <th>Clé de licence</th>
                            <th>Entreprise</th>
                            <th>Service</th>
                            <th>Durée</th>
                            <th>Date début</th>
                            <th>Date fin</th>
                            <th>Prix</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="lic in licenses" :key="lic.id">
                            <td><code class="small" style="background:#f4f6f8; padding:2px 6px; border-radius:3px;">{{ lic.license_key }}</code></td>
                            <td class="small">{{ lic.client?.company_name || '-' }}</td>
                            <td class="small">{{ lic.service?.name || '-' }}</td>
                            <td class="small">{{ durationLabel(lic.duration_months) }}</td>
                            <td class="small">{{ formatDate(lic.start_date) }}</td>
                            <td class="small">{{ formatDate(lic.end_date) }}</td>
                            <td class="small fw-medium">{{ formatCurrency(lic.price) }}</td>
                            <td>
                                <span class="badge" :class="lic.status === 'active' ? 'bg-success' : lic.status === 'expired' ? 'bg-secondary' : 'bg-danger'">
                                    {{ statusLabel(lic.status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary me-1" title="Modifier" @click="openEditModal(lic.id)">
                                    <i class="bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" title="Supprimer" @click="deleteLicense(lic.id)">
                                    <i class="bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal -->
        <div ref="modalEl" class="modal fade" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">{{ isEditing ? 'Modifier la licence' : 'Nouvelle licence' }}</h5>
                        <button type="button" class="btn-close" @click="closeModal"></button>
                    </div>
                    <form @submit.prevent="submitForm">
                        <div class="modal-body">
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label small">Entreprise *</label>
                                    <select v-model="form.client_id" class="form-select form-select-sm" required>
                                        <option value="">Sélectionner une entreprise</option>
                                        <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company_name }}</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small">Service *</label>
                                    <select v-model="form.service_id" class="form-select form-select-sm" required>
                                        <option value="">Sélectionner un service</option>
                                        <option v-for="s in services" :key="s.id" :value="s.id">{{ s.name }}</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small">Durée *</label>
                                    <select v-model="form.duration_months" class="form-select form-select-sm" required>
                                        <option value="12">12 mois</option>
                                        <option value="24">24 mois</option>
                                        <option value="36">36 mois</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small">Date début *</label>
                                    <input v-model="form.start_date" type="date" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small">Prix (FCFA) *</label>
                                    <input v-model="form.price" type="number" step="0.01" min="0" class="form-control form-control-sm" required placeholder="0">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" @click="closeModal">Annuler</button>
                            <button type="submit" class="btn btn-sm btn-primary" :disabled="submitting">
                                <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                                {{ isEditing ? 'Mettre à jour' : 'Créer la licence' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </GelLayout>
</template>
