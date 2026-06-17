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
        <div class="isup-shell">

            <!-- ══ HEADER ══ -->
            <div class="isup-portal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="isup-portal-logo">
                        <i class="bi-key" style="font-size:20px;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="isup-portal-company">Gestion des Licences</div>
                        <div class="isup-portal-sub">Licences, abonnements et accès des clients</div>
                    </div>
                    <button class="isup-btn-primary flex-shrink-0" @click="openCreateModal">
                        <i class="bi-plus-lg me-1"></i>Nouvelle licence
                    </button>
                </div>
            </div>

            <!-- ══ CONTENU ══ -->
            <div class="p-3">

                <!-- Loading -->
                <div v-if="loading" class="d-flex align-items-center justify-content-center gap-3 py-5">
                    <div class="isup-spinner"></div>
                    <span style="color:#888; font-size:14px;">Chargement…</span>
                </div>

                <!-- Error -->
                <div v-else-if="error" class="isup-alert-error">
                    <i class="bi-exclamation-triangle-fill me-2"></i>{{ error }}
                    <button @click="fetchLicenses" class="isup-btn-primary ms-3" style="padding:4px 12px; font-size:11px;">
                        <i class="bi-arrow-clockwise me-1"></i>Réessayer
                    </button>
                </div>

                <!-- Empty -->
                <div v-else-if="!licenses.length" class="text-center py-5">
                    <i class="bi-key" style="font-size:48px; color:#dce3ee; display:block; margin-bottom:12px;"></i>
                    <p style="font-size:15px; color:#888; margin-bottom:16px;">Aucune licence enregistrée.</p>
                    <button class="isup-btn-primary" @click="openCreateModal">
                        <i class="bi-plus-lg me-1"></i>Créer une licence
                    </button>
                </div>

                <!-- Table -->
                <div v-else class="isup-panel">
                    <div class="isup-panel-header">
                        <i class="bi-key me-2" style="color:#FF7900;"></i>Liste des licences
                    </div>
                    <div class="isup-panel-body p-0">
                        <div class="isup-table-wrap">
                            <table class="isup-table w-100">
                                <thead>
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
                                        <td>
                                            <code class="isup-code">{{ lic.license_key }}</code>
                                        </td>
                                        <td style="font-size:12px;">{{ lic.client?.company_name || '-' }}</td>
                                        <td style="font-size:12px;">{{ lic.service?.name || '-' }}</td>
                                        <td style="font-size:12px;">{{ durationLabel(lic.duration_months) }}</td>
                                        <td style="font-size:12px;">{{ formatDate(lic.start_date) }}</td>
                                        <td style="font-size:12px;">{{ formatDate(lic.end_date) }}</td>
                                        <td style="font-size:12px; font-weight:600;">{{ formatCurrency(lic.price) }}</td>
                                        <td>
                                            <span class="isup-status" :class="lic.status === 'active' ? 'isup-status-green' : lic.status === 'expired' ? 'isup-status-grey' : 'isup-status-red'">
                                                {{ statusLabel(lic.status) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <button class="isup-icon-btn" title="Modifier" @click="openEditModal(lic.id)">
                                                <i class="bi-pencil"></i>
                                            </button>
                                            <button class="isup-icon-btn isup-icon-danger ms-1" title="Supprimer" @click="deleteLicense(lic.id)">
                                                <i class="bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ MODAL ══ -->
        <div v-if="showModal" class="isup-modal-overlay" @click.self="closeModal">
            <div class="isup-modal">
                <div class="isup-modal-header">
                    <span>{{ isEditing ? 'Modifier la licence' : 'Nouvelle licence' }}</span>
                    <button class="isup-modal-close" @click="closeModal">&times;</button>
                </div>
                <form @submit.prevent="submitForm" class="isup-modal-body">
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="isup-label">Entreprise *</label>
                            <select v-model="form.client_id" class="isup-select" required>
                                <option value="">Sélectionner une entreprise</option>
                                <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company_name }}</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="isup-label">Service *</label>
                            <select v-model="form.service_id" class="isup-select" required>
                                <option value="">Sélectionner un service</option>
                                <option v-for="s in services" :key="s.id" :value="s.id">
                                    {{ s.name }}
                                </option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="isup-label">Durée *</label>
                            <select v-model="form.duration_months" class="isup-select" required>
                                <option value="12">12 mois</option>
                                <option value="24">24 mois</option>
                                <option value="36">36 mois</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="isup-label">Date début *</label>
                            <input v-model="form.start_date" type="date" class="isup-input" required>
                        </div>
                        <div class="col-12">
                            <label class="isup-label">Prix (FCFA) *</label>
                            <input v-model="form.price" type="number" step="0.01" min="0" class="isup-input" required placeholder="0">
                        </div>
                    </div>
                </form>
                <div class="isup-modal-footer">
                    <button type="button" class="isup-btn-grey" @click="closeModal">Annuler</button>
                    <button type="button" class="isup-btn-primary" :disabled="submitting" @click="submitForm">
                        <span v-if="submitting" class="isup-spinner-sm me-1"></span>
                        {{ isEditing ? 'Mettre à jour' : 'Créer la licence' }}
                    </button>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<style scoped>
/* ══ Licenses — unique styles ══ */

.isup-code { font-family:'SF Mono','Fira Code','Consolas',monospace; background:#f4f6f8; padding:2px 6px; border-radius:3px; font-size:13px; color:#163A5E; border:1px solid #e0e4e8; }
.isup-key { background:#eef3f9; padding:2px 6px; border-radius:3px; font-size:12px; font-family:monospace; }
</style>
