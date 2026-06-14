<script setup>
import { ref, onMounted, nextTick } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';
import { authStore } from '../../../stores/auth';

const props = defineProps({
    clientId: { type: [Number, String], required: true }
});

const accounts = ref([]);
const loading = ref(true);
const error = ref(null);
const submitting = ref(false);

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = ref({
    account_number: '', name: '', type: 'actif', category: '', description: '', balance: 0,
});

const accountTypes = ['actif', 'passif', 'charge', 'produit', 'tresorerie'];

const fetchAccounts = async () => {
    loading.value = true;
    error.value = null;
    try {
        const res = await fetch('/api/accounting/accounts/' + props.clientId);
        if (!res.ok) throw new Error('Erreur de chargement');
        accounts.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const resetForm = () => {
    form.value = { account_number: '', name: '', type: 'actif', category: '', description: '', balance: 0 };
};

const openCreateModal = () => {
    resetForm();
    isEditing.value = false;
    editingId.value = null;
    showModal.value = true;
};

const openEditModal = (acc) => {
    form.value = {
        account_number: acc.account_number || '',
        name: acc.name || '',
        type: acc.type || 'actif',
        category: acc.category || '',
        description: acc.description || '',
        balance: acc.balance || 0,
    };
    isEditing.value = true;
    editingId.value = acc.id;
    showModal.value = true;
};

const closeModal = () => { showModal.value = false; };

const submitForm = async () => {
    submitting.value = true;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const url = isEditing.value ? '/api/accounting/accounts/' + editingId.value : '/api/accounting/accounts';
        const method = isEditing.value ? 'PUT' : 'POST';

        const res = await fetch(url, {
            method,
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ ...form.value, client_id: props.clientId }),
        });
        if (!res.ok) {
            const errData = await res.json();
            throw new Error(errData.message || Object.values(errData.errors || {}).flat().join(', '));
        }
        closeModal();
        await fetchAccounts();
    } catch (e) {
        alert('Erreur: ' + e.message);
    } finally {
        submitting.value = false;
    }
};

const deleteAccount = async (id) => {
    if (!confirm('Confirmer la suppression ?')) return;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/api/accounting/accounts/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error('Erreur');
        await fetchAccounts();
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

const typeBadgeClass = (type) => {
    const map = { actif: 'bg-primary', passif: 'bg-info', charge: 'bg-warning', produit: 'bg-success', tresorerie: 'bg-secondary' };
    return map[type] || 'bg-secondary';
};

onMounted(fetchAccounts);
</script>

<template>
    <GelLayout page-title="Plan Comptable">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <span class="text-muted small">{{ accounts.length }} compte(s)</span>
            </div>
            <button class="btn btn-primary btn-sm" @click="openCreateModal">
                <i class="bi-plus-lg me-1"></i>Nouveau compte
            </button>
        </div>

        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <div v-else class="card card-dashboard">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="small text-muted">
                        <tr>
                            <th>Numéro</th>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Catégorie</th>
                            <th class="text-end">Solde</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!accounts.length">
                            <td colspan="6" class="text-center py-4 text-muted">Aucun compte. Créez votre premier compte.</td>
                        </tr>
                        <tr v-for="acc in accounts" :key="acc.id">
                            <td class="fw-medium">{{ acc.account_number }}</td>
                            <td>{{ acc.name }}</td>
                            <td><span class="badge" :class="typeBadgeClass(acc.type)">{{ acc.type }}</span></td>
                            <td class="small">{{ acc.category || '-' }}</td>
                            <td class="text-end fw-medium">{{ $formatCurrency(acc.balance) }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary me-1" @click="openEditModal(acc)"><i class="bi-pencil"></i></button>
                                <button class="btn btn-sm btn-outline-danger" @click="deleteAccount(acc.id)"><i class="bi-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold">{{ isEditing ? 'Modifier' : 'Nouveau' }} compte</h6>
                        <button type="button" class="btn-close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small">Numéro de compte *</label>
                                <input v-model="form.account_number" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Type</label>
                                <select v-model="form.type" class="form-select form-select-sm">
                                    <option v-for="t in accountTypes" :key="t" :value="t">{{ t }}</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Nom *</label>
                                <input v-model="form.name" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Catégorie</label>
                                <input v-model="form.category" class="form-control form-control-sm" placeholder="Ex: Immobilisations, Fournisseurs...">
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Description</label>
                                <textarea v-model="form.description" class="form-control form-control-sm" rows="2"></textarea>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Solde initial</label>
                                <input v-model="form.balance" type="number" step="0.01" class="form-control form-control-sm">
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
