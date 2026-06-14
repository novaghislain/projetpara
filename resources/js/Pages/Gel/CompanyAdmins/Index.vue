<script setup>
import { ref, onMounted, nextTick } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';

const admins = ref([]);
const clients = ref([]);
const loading = ref(true);
const error = ref(null);
const submitting = ref(false);

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const modalEl = ref(null);
const modalInstance = ref(null);

const form = ref({
    name: '',
    email: '',
    password: '',
    client_id: '',
});

const statusBadgeClass = (status) => {
    const map = { active: 'bg-success', inactive: 'bg-secondary' };
    return map[status] || 'bg-secondary';
};

const statusLabel = (status) => {
    const map = { active: 'Actif', inactive: 'Inactif' };
    return map[status] || status;
};

const fetchAdmins = async () => {
    loading.value = true;
    error.value = null;
    try {
        const res = await fetch('/api/company-admins');
        if (!res.ok) throw new Error('Erreur lors du chargement des administrateurs');
        admins.value = await res.json();
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

const resetForm = () => {
    form.value = {
        name: '',
        email: '',
        password: '',
        client_id: '',
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
        const res = await fetch('/api/company-admins/' + id);
        if (!res.ok) throw new Error('Erreur de chargement');
        const data = await res.json();
        form.value = {
            name: data.name || '',
            email: data.email || '',
            password: '',
            client_id: data.client_id || '',
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
        const url = isEditing.value ? '/api/company-admins/' + editingId.value : '/api/company-admins';
        const method = isEditing.value ? 'PUT' : 'POST';

        const body = { ...form.value };
        if (isEditing.value && !body.password) {
            delete body.password;
        }

        const res = await fetch(url, {
            method,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(body),
        });

        if (!res.ok) {
            const errData = await res.json();
            throw new Error(errData.message || Object.values(errData.errors || {}).flat().join(', '));
        }
        closeModal();
        await fetchAdmins();
    } catch (e) {
        alert('Erreur: ' + e.message);
    } finally {
        submitting.value = false;
    }
};

const deleteAdmin = async (id) => {
    if (!confirm('Confirmer la suppression de cet administrateur ?')) return;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/api/company-admins/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error('Erreur lors de la suppression');
        await fetchAdmins();
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

onMounted(async () => {
    await Promise.all([fetchAdmins(), fetchClients()]);
});
</script>

<template>
    <GelLayout page-title="Administrateurs Entreprise">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div></div>
            <button class="btn btn-primary btn-sm" @click="openCreateModal">
                <i class="bi-plus-lg me-1"></i>Nouvel administrateur
            </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <!-- Empty -->
        <div v-else-if="!admins.length" class="text-center py-5 text-muted">
            <i class="bi-person-badge" style="font-size:48px;"></i>
            <p class="mt-2 fs-5">Aucun administrateur entreprise.</p>
            <button class="btn btn-primary btn-sm" @click="openCreateModal">
                <i class="bi-plus-lg me-1"></i>Ajouter un administrateur
            </button>
        </div>

        <!-- Table -->
        <div v-else class="card card-dashboard">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="small text-muted">
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Entreprise</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!admins.length">
                            <td colspan="5" class="text-center py-4 text-muted">Aucun administrateur trouvé.</td>
                        </tr>
                        <tr v-for="admin in admins" :key="admin.id">
                            <td class="fw-medium">{{ admin.name }}</td>
                            <td class="small">{{ admin.email }}</td>
                            <td class="small">{{ admin.client?.company_name || '-' }}</td>
                            <td>
                                <span class="badge" :class="statusBadgeClass(admin.status)">
                                    {{ statusLabel(admin.status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary me-1" title="Modifier" @click="openEditModal(admin.id)">
                                    <i class="bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" title="Supprimer" @click="deleteAdmin(admin.id)">
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
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">{{ isEditing ? "Modifier l'administrateur" : "Nouvel administrateur" }}</h5>
                        <button type="button" class="btn-close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submitForm">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label small">Nom *</label>
                                    <input v-model="form.name" type="text" class="form-control form-control-sm" required placeholder="Nom complet">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small">Email *</label>
                                    <input v-model="form.email" type="email" class="form-control form-control-sm" required placeholder="email@entreprise.com">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small">
                                        Mot de passe {{ isEditing ? '(laisser vide pour conserver)' : '*' }}
                                    </label>
                                    <input v-model="form.password" type="password" class="form-control form-control-sm" :required="!isEditing" placeholder="Mot de passe">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small">Entreprise *</label>
                                    <select v-model="form.client_id" class="form-select form-select-sm" required>
                                        <option value="">Sélectionner une entreprise</option>
                                        <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company_name }}</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" @click="closeModal">Annuler</button>
                        <button type="button" class="btn btn-sm btn-primary" :disabled="submitting" @click="submitForm">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                            {{ isEditing ? 'Mettre à jour' : "Créer l'administrateur" }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>
