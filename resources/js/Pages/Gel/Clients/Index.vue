<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';
import { authStore } from '../../../stores/auth';

const props = defineProps({
    clientId: { type: [Number, String], default: null }
});

// ── Data ──
const clients = ref([]);
const poles = ref([]);
const services = ref([]);
const loading = ref(true);
const error = ref(null);
const submitting = ref(false);

// Filters
const search = ref('');
const filterStatus = ref('');
const filterPole = ref('');
const debounceTimer = ref(null);

// Modal state
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const modalInstance = ref(null);
const modalEl = ref(null);

// ── Form ──
const form = ref({
    company_name: '', legal_form: '', rccm: '', ifu: '',
    address: '', city: '', country: '', phone: '', email: '', website: '',
    status: 'prospect',
    contract_type: '', contract_start: '', contract_end: '', notes: '',
    pole_ids: [],
    service_ids: [],
});

const statuses = ['actif', 'inactif', 'prospect'];
const contractTypes = ['mensuel', 'trimestriel', 'semestriel', 'annuel', 'ponctuel'];
const legalForms = ['SARL', 'SA', 'SAS', 'SASU', 'EURL', 'SNC', 'SCI', 'Association', 'Auto-entrepreneur', 'Autre'];

// ── Fetch data ──
const fetchClients = async () => {
    const params = {};
    if (search.value) params.search = search.value;
    if (filterStatus.value) params.status = filterStatus.value;
    if (filterPole.value) params.pole_id = filterPole.value;
    try {
        const res = await window.axios.get('api/clients', { params });
        clients.value = Array.isArray(res.data) ? res.data : (res.data?.data || []);
    } catch (e) {
        error.value = e.response?.data?.message || e.message;
    } finally {
        loading.value = false;
    }
};

const fetchPoles = async () => {
    try {
        const res = await window.axios.get('api/poles');
        poles.value = Array.isArray(res.data) ? res.data : (res.data?.data || []);
    } catch (e) {
        console.error('fetchPoles error:', e);
    }
};

const fetchServices = async () => {
    try {
        const res = await window.axios.get('api/services');
        services.value = Array.isArray(res.data) ? res.data : (res.data?.data || []);
    } catch (e) {
        console.error('fetchServices error:', e);
    }
};

// ── Search with debounce ──
watch(search, () => {
    clearTimeout(debounceTimer.value);
    debounceTimer.value = setTimeout(() => fetchClients(), 300);
});
watch([filterStatus, filterPole], () => fetchClients());

// ── Modal ──
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
        const res = await window.axios.get('api/clients/' + id);
        const data = res.data;
        form.value = {
            company_name: data.company_name || '',
            legal_form: data.legal_form || '',
            rccm: data.rccm || '',
            ifu: data.ifu || '',
            address: data.address || '',
            city: data.city || '',
            country: data.country || '',
            phone: data.phone || '',
            email: data.email || '',
            website: data.website || '',
            status: data.status || 'prospect',
            contract_type: data.contract_type || '',
            contract_start: data.contract_start || '',
            contract_end: data.contract_end || '',
            notes: data.notes || '',
            pole_ids: data.poles?.map(p => p.id) || [],
            service_ids: data.services?.map(s => s.id) || [],
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
        alert('Erreur lors du chargement du client: ' + (e.response?.data?.message || e.message));
    }
};

const closeModal = () => {
    modalInstance.value?.hide();
    showModal.value = false;
};

const resetForm = () => {
    form.value = {
        company_name: '', legal_form: '', rccm: '', ifu: '',
        address: '', city: '', country: '', phone: '', email: '', website: '',
        status: 'prospect',
        contract_type: '', contract_start: '', contract_end: '', notes: '',
        pole_ids: [],
        service_ids: [],
    };
};

const togglePole = (poleId) => {
    const idx = form.value.pole_ids.indexOf(poleId);
    if (idx === -1) form.value.pole_ids.push(poleId);
    else form.value.pole_ids.splice(idx, 1);
};

const toggleService = (serviceId) => {
    const idx = form.value.service_ids.indexOf(serviceId);
    if (idx === -1) form.value.service_ids.push(serviceId);
    else form.value.service_ids.splice(idx, 1);
};

const submitForm = async () => {
    submitting.value = true;
    try {
        if (isEditing.value) {
            await window.axios.put('clients/' + editingId.value, form.value);
        } else {
            await window.axios.post('clients', form.value);
        }
        closeModal();
        await fetchClients();
    } catch (e) {
        const errData = e.response?.data;
        const msg = errData?.message || Object.values(errData?.errors || {}).flat().join(', ') || e.message;
        alert('Erreur: ' + msg);
    } finally {
        submitting.value = false;
    }
};

const deleteClient = async (id) => {
    if (!confirm('Confirmer la suppression de ce client ?')) return;
    try {
        await window.axios.delete('clients/' + id);
        await fetchClients();
    } catch (e) {
        alert('Erreur: ' + (e.response?.data?.message || e.message));
    }
};

// ── Lifecycle ──
onMounted(async () => {
    await Promise.all([fetchClients(), fetchPoles(), fetchServices()]);
});
</script>

<template>
    <GelLayout page-title="Clients">
        <!-- Filters -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="input-group input-group-sm" style="max-width:300px;">
                    <span class="input-group-text bg-white"><i class="bi-search"></i></span>
                    <input v-model="search" type="text" class="form-control" placeholder="Nom, email, téléphone, RCCM, IFU...">
                </div>
                <select v-model="filterStatus" class="form-select form-select-sm" style="width:auto;">
                    <option value="">Tous statuts</option>
                    <option value="actif">Actif</option>
                    <option value="inactif">Inactif</option>
                    <option value="prospect">Prospect</option>
                </select>
                <select v-model="filterPole" class="form-select form-select-sm" style="width:auto;">
                    <option value="">Tous pôles</option>
                    <option v-for="pole in poles" :key="pole.id" :value="pole.id">{{ pole.name }}</option>
                </select>
            </div>
            <button class="btn btn-primary btn-sm" @click="openCreateModal">
                <i class="bi-plus-lg me-1"></i>Nouveau Client
            </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <!-- Table -->
        <div v-else class="card card-dashboard">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="small text-muted">
                        <tr>
                            <th>Société</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Statut</th>
                            <th>Pôles</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!clients.length">
                            <td colspan="6" class="text-center py-4 text-muted">Aucun client trouvé.</td>
                        </tr>
                        <tr v-for="client in clients" :key="client.id">
                            <td>
                                <a :href="'/clients/' + client.id" class="text-decoration-none fw-medium">{{ client.company_name }}</a>
                                <div class="small text-muted">{{ client.rccm ? 'RCCM: ' + client.rccm : '' }}{{ client.ifu ? ' | IFU: ' + client.ifu : '' }}</div>
                            </td>
                            <td class="small">{{ client.email }}</td>
                            <td class="small">{{ client.phone }}</td>
                            <td>
                                <span class="badge" :class="client.status === 'actif' ? 'bg-success' : client.status === 'inactif' ? 'bg-secondary' : 'bg-warning'">
                                    {{ client.status }}
                                </span>
                            </td>
                            <td>
                                <span v-for="pole in client.poles" :key="pole.id" class="badge badge-eden me-1" :style="{ backgroundColor: (pole.color || '#e8eaf6') + '30', color: pole.color || '#1a237e' }">
                                    {{ pole.name }}
                                </span>
                                <span v-if="!client.poles?.length" class="text-muted small">-</span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary me-1" title="Modifier" @click="openEditModal(client.id)">
                                    <i class="bi-pencil"></i>
                                </button>
                                <a :href="'/clients/' + client.id" class="btn btn-sm btn-outline-info me-1" title="Voir">
                                    <i class="bi-eye"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger" title="Supprimer" @click="deleteClient(client.id)">
                                    <i class="bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div ref="modalEl" class="modal fade" tabindex="-1" @hidden.self="showModal = false">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">{{ isEditing ? 'Modifier le client' : 'Nouveau client' }}</h5>
                        <button type="button" class="btn-close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submitForm">
                            <div class="row g-3">
                                <div class="col-12">
                                    <h6 class="fw-bold text-primary border-bottom pb-2">Informations générales</h6>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Raison sociale *</label>
                                    <input v-model="form.company_name" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Forme juridique</label>
                                    <select v-model="form.legal_form" class="form-select form-select-sm">
                                        <option value="">Sélectionner</option>
                                        <option v-for="f in legalForms" :key="f" :value="f">{{ f }}</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Statut</label>
                                    <select v-model="form.status" class="form-select form-select-sm">
                                        <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">RCCM</label>
                                    <input v-model="form.rccm" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">IFU</label>
                                    <input v-model="form.ifu" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Site web</label>
                                    <input v-model="form.website" class="form-control form-control-sm" placeholder="https://">
                                </div>

                                <div class="col-12 mt-3">
                                    <h6 class="fw-bold text-primary border-bottom pb-2">Coordonnées</h6>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Email</label>
                                    <input v-model="form.email" type="email" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Téléphone</label>
                                    <input v-model="form.phone" type="tel" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Ville</label>
                                    <input v-model="form.city" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Adresse</label>
                                    <input v-model="form.address" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Pays</label>
                                    <input v-model="form.country" class="form-control form-control-sm">
                                </div>

                                <div class="col-12 mt-3">
                                    <h6 class="fw-bold text-primary border-bottom pb-2">Contrat</h6>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Type de contrat</label>
                                    <select v-model="form.contract_type" class="form-select form-select-sm">
                                        <option value="">Sélectionner</option>
                                        <option v-for="ct in contractTypes" :key="ct" :value="ct">{{ ct }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Date début</label>
                                    <input v-model="form.contract_start" type="date" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Date fin</label>
                                    <input v-model="form.contract_end" type="date" class="form-control form-control-sm">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small">Notes</label>
                                    <textarea v-model="form.notes" class="form-control form-control-sm" rows="2"></textarea>
                                </div>

                                <div class="col-12 mt-3">
                                    <h6 class="fw-bold text-primary border-bottom pb-2">Pôles</h6>
                                    <div v-if="poles.length" class="d-flex flex-wrap gap-2">
                                        <div v-for="pole in poles" :key="pole.id" class="form-check">
                                            <input type="checkbox" :id="'pole-'+pole.id" class="form-check-input"
                                                   :checked="form.pole_ids.includes(pole.id)"
                                                   @change="togglePole(pole.id)">
                                            <label :for="'pole-'+pole.id" class="form-check-label small">
                                                <span class="badge" :style="{ backgroundColor: pole.color || '#6c757d' }">{{ pole.name }}</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div v-else class="text-muted small">Aucun pôle disponible.</div>
                                </div>

                                <div class="col-12 mt-3">
                                    <h6 class="fw-bold text-primary border-bottom pb-2">Services</h6>
                                    <div v-if="services.length" class="d-flex flex-wrap gap-2">
                                        <div v-for="service in services" :key="service.id" class="form-check">
                                            <input type="checkbox" :id="'svc-'+service.id" class="form-check-input"
                                                   :checked="form.service_ids.includes(service.id)"
                                                   @change="toggleService(service.id)">
                                            <label :for="'svc-'+service.id" class="form-check-label small">
                                                <i :class="service.icon || 'bi-gear'"></i> {{ service.name }}
                                            </label>
                                        </div>
                                    </div>
                                    <div v-else class="text-muted small">Aucun service disponible.</div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" @click="closeModal">Annuler</button>
                        <button type="button" class="btn btn-sm btn-primary" :disabled="submitting" @click="submitForm">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                            {{ isEditing ? 'Mettre à jour' : 'Créer le client' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>
