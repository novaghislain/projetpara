<script setup>
import { ref, computed, onMounted } from 'vue';
import CompanyLayout from '../../../Layouts/CompanyLayout.vue';
import { authStore } from '../../../stores/auth';

const expenses = ref([]);
const loading = ref(true);
const error = ref(null);
const submitting = ref(false);
const showCreateModal = ref(false);

const form = ref({
    categorie: 'transport',
    montant: '',
    date: new Date().toISOString().split('T')[0],
    description: '',
    justificatif: null,
});

const fetchExpenses = async () => {
    loading.value = true;
    error.value = null;
    try {
        const res = await fetch('/company/rh/api/expenses');
        if (!res.ok) throw new Error('Erreur de chargement');
        expenses.value = await res.json();
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
        rembourse: 'bg-info',
    };
    return map[status] || 'bg-secondary';
};

const totalMontant = computed(() => {
    return expenses.value.reduce((s, e) => s + (parseFloat(e.montant) || 0), 0);
});

const formatCurrency = (value) => {
    if (value === null || value === undefined || isNaN(value)) return '0';
    return Number(value).toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + ' FCFA';
};

const handleFileChange = (e) => {
    form.value.justificatif = e.target.files[0] || null;
};

const submitExpense = async () => {
    submitting.value = true;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const formData = new FormData();
        formData.append('categorie', form.value.categorie);
        formData.append('montant', form.value.montant);
        formData.append('date', form.value.date);
        formData.append('description', form.value.description);
        if (form.value.justificatif) {
            formData.append('justificatif', form.value.justificatif);
        }
        const res = await fetch('/company/rh/api/expenses', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: formData,
        });
        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || 'Erreur lors de la création');
        }
        showCreateModal.value = false;
        form.value = { categorie: 'transport', montant: '', date: new Date().toISOString().split('T')[0], description: '', justificatif: null };
        await fetchExpenses();
    } catch (e) {
        alert('Erreur: ' + e.message);
    } finally {
        submitting.value = false;
    }
};

onMounted(fetchExpenses);
</script>

<template>
    <CompanyLayout page-title="Notes de Frais">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <span class="small text-muted">{{ expenses.length }} note(s) - Total: {{ formatCurrency(totalMontant) }}</span>
            <button class="btn btn-primary btn-sm" @click="showCreateModal = true">
                <i class="bi-plus-lg me-1"></i>Nouvelle note
            </button>
        </div>

        <!-- Summary -->
        <div class="row g-2 mb-3">
            <div class="col-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3 text-center">
                        <div class="text-muted small">En attente</div>
                        <div class="fw-bold fs-5 text-warning">{{ expenses.filter(e => e.statut === 'en_attente').length }}</div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3 text-center">
                        <div class="text-muted small">Approuvés</div>
                        <div class="fw-bold fs-5 text-success">{{ expenses.filter(e => e.statut === 'approuve').length }}</div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3 text-center">
                        <div class="text-muted small">Remboursés</div>
                        <div class="fw-bold fs-5 text-info">{{ expenses.filter(e => e.statut === 'rembourse').length }}</div>
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
                            <th>Catégorie</th>
                            <th class="text-end">Montant</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!expenses.length">
                            <td colspan="5" class="text-center py-4 text-muted">Aucune note de frais.</td>
                        </tr>
                        <tr v-for="e in expenses" :key="e.id">
                            <td><span class="badge bg-secondary">{{ e.categorie }}</span></td>
                            <td class="text-end fw-medium">{{ formatCurrency(e.montant) }}</td>
                            <td class="small">{{ $formatDate ? $formatDate(e.date) : e.date }}</td>
                            <td class="small text-muted text-truncate" style="max-width:200px;">{{ e.description || '-' }}</td>
                            <td><span class="badge" :class="statusBadgeClass(e.statut)">{{ e.statut }}</span></td>
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
                        <h6 class="fw-bold mb-0">Nouvelle note de frais</h6>
                        <button class="btn-close" @click="showCreateModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label small">Catégorie *</label>
                                <select v-model="form.categorie" class="form-select form-select-sm">
                                    <option value="transport">Transport</option>
                                    <option value="hebergement">Hébergement</option>
                                    <option value="restauration">Restauration</option>
                                    <option value="fourniture">Fourniture</option>
                                    <option value="carburant">Carburant</option>
                                    <option value="communication">Communication</option>
                                    <option value="autre">Autre</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Montant (FCFA) *</label>
                                <input v-model="form.montant" type="number" step="0.01" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Date *</label>
                                <input v-model="form.date" type="date" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Description</label>
                                <textarea v-model="form.description" class="form-control form-control-sm" rows="2" placeholder="Description de la dépense..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Justificatif (PDF, image)</label>
                                <input type="file" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileChange">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-secondary" @click="showCreateModal = false">Annuler</button>
                        <button class="btn btn-sm btn-primary" :disabled="submitting || !form.montant" @click="submitExpense">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                            Soumettre
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </CompanyLayout>
</template>
