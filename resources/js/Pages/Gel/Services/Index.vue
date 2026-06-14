<script setup>
import { ref, onMounted, nextTick } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';
import { authStore } from '../../../stores/auth';

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
    name: '', slug: '', description: '', icon: 'bi-gear', color: '#1a237e', is_active: true,
});

const iconOptions = [
    'bi-gear', 'bi-calculator', 'bi-file-text', 'bi-bar-chart', 'bi-people',
    'bi-building', 'bi-cash-stack', 'bi-graph-up', 'bi-shield', 'bi-search',
    'bi-pie-chart', 'bi-book', 'bi-clipboard-data', 'bi-grid-3x3-gap',
];

const fetchServices = async () => {
    loading.value = true;
    error.value = null;
    try {
        const res = await fetch('/api/services');
        if (!res.ok) throw new Error('Erreur de chargement');
        services.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const resetForm = () => {
    form.value = { name: '', slug: '', description: '', icon: 'bi-gear', color: '#1a237e', is_active: true };
};

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

const openEditModal = (svc) => {
    form.value = {
        name: svc.name || '',
        slug: svc.slug || '',
        description: svc.description || '',
        icon: svc.icon || 'bi-gear',
        color: svc.color || '#1a237e',
        is_active: svc.is_active ?? true,
    };
    isEditing.value = true;
    editingId.value = svc.id;
    showModal.value = true;
    nextTick(() => {
        if (!modalInstance.value && modalEl.value) {
            modalInstance.value = new bootstrap.Modal(modalEl.value);
        }
        modalInstance.value?.show();
    });
};

const closeModal = () => {
    modalInstance.value?.hide();
    showModal.value = false;
};

const submitForm = async () => {
    submitting.value = true;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const url = isEditing.value ? '/api/services/' + editingId.value : '/api/services';
        const method = isEditing.value ? 'PUT' : 'POST';

        const res = await fetch(url, {
            method,
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(form.value),
        });
        if (!res.ok) {
            const errData = await res.json();
            throw new Error(errData.message || Object.values(errData.errors || {}).flat().join(', '));
        }
        closeModal();
        await fetchServices();
    } catch (e) {
        alert('Erreur: ' + e.message);
    } finally {
        submitting.value = false;
    }
};

const deleteService = async (id) => {
    if (!confirm('Confirmer la suppression ?')) return;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/api/services/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error('Erreur');
        await fetchServices();
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

onMounted(fetchServices);
</script>

<template>
    <GelLayout page-title="Services">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div></div>
            <button class="btn btn-primary btn-sm" @click="openCreateModal">
                <i class="bi-plus-lg me-1"></i>Nouveau service
            </button>
        </div>

        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <div v-else-if="!services.length" class="text-center py-5 text-muted">
            <i class="bi-grid-3x3-gap" style="font-size:48px;"></i>
            <p class="mt-2">Aucun service créé.</p>
        </div>

        <div v-else class="row g-3">
            <div v-for="svc in services" :key="svc.id" class="col-md-6 col-lg-4">
                <div class="card card-dashboard h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                 :style="{ backgroundColor: (svc.color || '#1a237e') + '20', width: '48px', height: '48px', borderRadius: '12px' }">
                                <i :class="svc.icon || 'bi-gear'" :style="{ color: svc.color || '#1a237e', fontSize: '22px' }"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="mb-0 fw-bold">{{ svc.name }}</h6>
                                    <span class="badge" :class="svc.is_active ? 'bg-success' : 'bg-secondary'">
                                        {{ svc.is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </div>
                                <span class="small text-muted">{{ svc.slug }}</span>
                            </div>
                        </div>
                        <p v-if="svc.description" class="small text-muted mb-3">{{ svc.description }}</p>
                        <p v-else class="small text-muted mb-3 fst-italic">Aucune description</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <a :href="'/services/' + svc.id" class="btn btn-sm btn-outline-primary"><i class="bi-eye me-1"></i>Détails</a>
                            <div>
                                <button class="btn btn-sm btn-outline-secondary me-1" @click="openEditModal(svc)"><i class="bi-pencil"></i></button>
                                <button class="btn btn-sm btn-outline-danger" @click="deleteService(svc.id)"><i class="bi-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div ref="modalEl" class="modal fade" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">{{ isEditing ? 'Modifier le service' : 'Nouveau service' }}</h5>
                        <button type="button" class="btn-close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small">Nom *</label>
                                <input v-model="form.name" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Slug</label>
                                <input v-model="form.slug" class="form-control form-control-sm" placeholder="nom-du-service">
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Description</label>
                                <textarea v-model="form.description" class="form-control form-control-sm" rows="2"></textarea>
                            </div>
                            <div class="col-4">
                                <label class="form-label small">Icône</label>
                                <select v-model="form.icon" class="form-select form-select-sm">
                                    <option v-for="ico in iconOptions" :key="ico" :value="ico">
                                        {{ ico.replace('bi-', '') }}
                                    </option>
                                </select>
                                <div class="mt-1">
                                    <i :class="form.icon" style="font-size:20px;"></i>
                                </div>
                            </div>
                            <div class="col-4">
                                <label class="form-label small">Couleur</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input v-model="form.color" type="color" class="form-control form-control-color" style="width:50px;">
                                    <span class="small text-muted">{{ form.color }}</span>
                                </div>
                            </div>
                            <div class="col-4 d-flex align-items-end pb-1">
                                <div class="form-check">
                                    <input v-model="form.is_active" type="checkbox" id="svcIsActive" class="form-check-input">
                                    <label for="svcIsActive" class="form-check-label small">Service actif</label>
                                </div>
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
