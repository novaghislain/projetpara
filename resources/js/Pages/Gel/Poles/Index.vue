<script setup>
import { ref, onMounted, nextTick } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';
import { authStore } from '../../../stores/auth';

const poles = ref([]);
const loading = ref(true);
const error = ref(null);
const submitting = ref(false);

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const modalEl = ref(null);
const modalInstance = ref(null);

const form = ref({ name: '', slug: '', description: '', color: '#1a237e', is_active: true });

const fetchPoles = async () => {
    loading.value = true;
    error.value = null;
    try {
        const res = await fetch('/api/poles');
        if (!res.ok) throw new Error('Erreur de chargement');
        poles.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const resetForm = () => {
    form.value = { name: '', slug: '', description: '', color: '#1a237e', is_active: true };
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

const openEditModal = (pole) => {
    form.value = {
        name: pole.name || '',
        slug: pole.slug || '',
        description: pole.description || '',
        color: pole.color || '#1a237e',
        is_active: pole.is_active ?? true,
    };
    isEditing.value = true;
    editingId.value = pole.id;
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
        const url = isEditing.value ? '/api/poles/' + editingId.value : '/api/poles';
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
        await fetchPoles();
    } catch (e) {
        alert('Erreur: ' + e.message);
    } finally {
        submitting.value = false;
    }
};

const deletePole = async (id) => {
    if (!confirm('Confirmer la suppression de ce pôle ?')) return;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/api/poles/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error('Erreur lors de la suppression');
        await fetchPoles();
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

onMounted(fetchPoles);
</script>

<template>
    <GelLayout page-title="Pôles">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div></div>
            <button class="btn btn-primary btn-sm" @click="openCreateModal">
                <i class="bi-plus-lg me-1"></i>Nouveau pôle
            </button>
        </div>

        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <div v-else-if="!poles.length" class="text-center py-5 text-muted">
            <i class="bi-diagram-3" style="font-size:48px;"></i>
            <p class="mt-2">Aucun pôle créé.</p>
        </div>

        <div v-else class="row g-3">
            <div v-for="pole in poles" :key="pole.id" class="col-md-6 col-lg-4">
                <div class="card card-dashboard h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-2 d-flex align-items-center justify-content-center" :style="{ backgroundColor: (pole.color || '#1a237e') + '20', width: '48px', height: '48px', borderRadius: '12px' }">
                                <i class="bi-diagram-3" :style="{ color: pole.color || '#1a237e', fontSize: '24px' }"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0 fw-bold">{{ pole.name }}</h6>
                                <span class="small text-muted">{{ pole.slug }}</span>
                            </div>
                            <span class="badge" :class="pole.is_active ? 'bg-success' : 'bg-secondary'">
                                {{ pole.is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>
                        <p v-if="pole.description" class="small text-muted mb-3">{{ pole.description }}</p>
                        <p v-else class="small text-muted mb-3 fst-italic">Aucune description</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <a :href="'/poles/' + pole.id" class="btn btn-sm btn-outline-primary"><i class="bi-eye me-1"></i>Voir</a>
                            <div>
                                <button class="btn btn-sm btn-outline-secondary me-1" @click="openEditModal(pole)"><i class="bi-pencil"></i></button>
                                <button class="btn btn-sm btn-outline-danger" @click="deletePole(pole.id)"><i class="bi-trash"></i></button>
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
                        <h5 class="modal-title fw-bold">{{ isEditing ? 'Modifier le pôle' : 'Nouveau pôle' }}</h5>
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
                                <input v-model="form.slug" class="form-control form-control-sm" placeholder="nom-du-pole">
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Description</label>
                                <textarea v-model="form.description" class="form-control form-control-sm" rows="2"></textarea>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Couleur</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input v-model="form.color" type="color" class="form-control form-control-color" style="width:50px;">
                                    <span class="small text-muted">{{ form.color }}</span>
                                </div>
                            </div>
                            <div class="col-6 d-flex align-items-end pb-1">
                                <div class="form-check">
                                    <input v-model="form.is_active" type="checkbox" id="isActive" class="form-check-input">
                                    <label for="isActive" class="form-check-label small">Pôle actif</label>
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
