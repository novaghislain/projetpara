<script setup>
import { ref, onMounted, nextTick } from 'vue';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';

const users = ref([]);
const roles = ref([]);
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
    role_id: '',
    fonction: '',
});

const statusLabel = (active) => active ? 'Actif' : 'Inactif';
const statusClass = (active) => active ? 'bg-success' : 'bg-secondary';

const roleLabel = (slug) => {
    const map = {
        comptable: 'bg-primary',
        caissier: 'bg-info',
        juriste: 'bg-warning text-dark',
        rh: 'bg-success',
        gestionnaire_projet: 'bg-secondary',
    };
    return map[slug] || 'bg-secondary';
};

const fetchUsers = async () => {
    loading.value = true;
    error.value = null;
    try {
        const res = await fetch('/api/company/users');
        if (!res.ok) throw new Error('Erreur lors du chargement');
        const data = await res.json();
        users.value = data.users || [];
        roles.value = data.roles || [];
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const resetForm = () => {
    form.value = { name: '', email: '', password: '', role_id: '', fonction: '' };
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

const openEditModal = async (id) => {
    try {
        const res = await fetch('/api/company/users/' + id);
        if (!res.ok) throw new Error('Erreur de chargement');
        const data = await res.json();
        form.value = {
            name: data.name || '',
            email: data.email || '',
            password: '',
            role_id: data.role_id || '',
            fonction: data.fonction || '',
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
        const url = isEditing.value ? '/api/company/users/' + editingId.value : '/api/company/users';
        const method = isEditing.value ? 'PUT' : 'POST';

        const payload = { ...form.value };
        if (isEditing.value && !payload.password) {
            delete payload.password;
        }

        const res = await fetch(url, {
            method,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        });

        if (!res.ok) {
            const errData = await res.json();
            throw new Error(errData.message || Object.values(errData.errors || {}).flat().join(', '));
        }
        closeModal();
        await fetchUsers();
    } catch (e) {
        alert('Erreur: ' + e.message);
    } finally {
        submitting.value = false;
    }
};

const toggleStatus = async (id, currentStatus) => {
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/api/company/users/' + id, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ is_active: !currentStatus }),
        });
        if (!res.ok) throw new Error('Erreur de mise à jour');
        await fetchUsers();
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

const deleteUser = async (id) => {
    if (!confirm('Confirmer la suppression de cet utilisateur ?')) return;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/api/company/users/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error('Erreur lors de la suppression');
        await fetchUsers();
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

const formatDate = (d) => d || '';

onMounted(fetchUsers);
</script>

<template>
    <CompanyLayout page-title="Gestion des Utilisateurs">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <p class="text-muted mb-0 small">
                <i class="bi-people me-1"></i>
                Gérez les utilisateurs de votre entreprise et leurs accès aux modules.
            </p>
            <button class="btn btn-primary btn-sm" @click="openCreateModal">
                <i class="bi-plus-lg me-1"></i>Nouvel utilisateur
            </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <!-- Empty -->
        <div v-else-if="!users.length" class="text-center py-5 text-muted">
            <i class="bi-people" style="font-size:48px;"></i>
            <p class="mt-2 fs-5">Aucun utilisateur dans votre entreprise.</p>
            <button class="btn btn-primary btn-sm" @click="openCreateModal">
                <i class="bi-plus-lg me-1"></i>Créer un utilisateur
            </button>
        </div>

        <!-- Users Table -->
        <div v-else class="card card-dashboard">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="small text-muted">
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Fonction</th>
                            <th>Rôle</th>
                            <th>Statut</th>
                            <th>Créé le</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="u in users" :key="u.id">
                            <td class="fw-semibold small">{{ u.name }}</td>
                            <td class="small">{{ u.email }}</td>
                            <td class="small text-muted">{{ u.fonction || '-' }}</td>
                            <td>
                                <span class="badge" :class="roleLabel(u.role_slug)">
                                    {{ u.role_name }}
                                </span>
                            </td>
                            <td>
                                <span class="badge" :class="statusClass(u.is_active)">
                                    {{ statusLabel(u.is_active) }}
                                </span>
                            </td>
                            <td class="small text-muted">{{ formatDate(u.created_at) }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary me-1"
                                        :title="u.is_active ? 'Désactiver' : 'Activer'"
                                        @click="toggleStatus(u.id, u.is_active)">
                                    <i :class="u.is_active ? 'bi-pause-circle' : 'bi-play-circle'"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-info me-1" title="Modifier"
                                        @click="openEditModal(u.id)">
                                    <i class="bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" title="Supprimer"
                                        @click="deleteUser(u.id)">
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
                        <h5 class="modal-title fw-bold">
                            {{ isEditing ? "Modifier l'utilisateur" : 'Nouvel utilisateur' }}
                        </h5>
                        <button type="button" class="btn-close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submitForm">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label small">Nom complet *</label>
                                    <input v-model="form.name" type="text" class="form-control form-control-sm"
                                           required placeholder="Jean Martin">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small">Email *</label>
                                    <input v-model="form.email" type="email" class="form-control form-control-sm"
                                           required placeholder="jean@entreprise.com">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small">
                                        {{ isEditing ? 'Mot de passe (laisser vide pour conserver)' : 'Mot de passe *' }}
                                    </label>
                                    <input v-model="form.password" type="password"
                                           class="form-control form-control-sm"
                                           :required="!isEditing" minlength="8"
                                           placeholder="Minimum 8 caractères">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small">Rôle *</label>
                                    <select v-model="form.role_id" class="form-select form-select-sm" required>
                                        <option value="">Sélectionner un rôle</option>
                                        <option v-for="r in roles" :key="r.id" :value="r.id">
                                            {{ r.name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small">Fonction</label>
                                    <input v-model="form.fonction" type="text" class="form-control form-control-sm"
                                           placeholder="Ex: Comptable senior">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" @click="closeModal">Annuler</button>
                        <button type="button" class="btn btn-sm btn-primary" :disabled="submitting" @click="submitForm">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                            {{ isEditing ? 'Mettre à jour' : "Créer l'utilisateur" }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </CompanyLayout>
</template>
