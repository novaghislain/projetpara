<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';
import { authStore } from '../../../stores/auth';

const invoices = ref([]);
const clients = ref([]);
const loading = ref(true);
const error = ref(null);
const submitting = ref(false);
const statusFilter = ref('');

const showModal = ref(false);
const form = ref({
    client_id: '', reference: '', date: new Date().toISOString().substring(0, 10),
    due_date: '', total_ht: '', total_tva: '', total_ttc: '', notes: '',
});

const fetchData = async () => {
    loading.value = true;
    error.value = null;
    try {
        const [invRes, cliRes] = await Promise.all([
            fetch('/api/erp/invoices'),
            fetch('/api/clients'),
        ]);
        if (invRes.ok) invoices.value = await invRes.json();
        if (cliRes.ok) clients.value = await cliRes.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const filteredInvoices = () => {
    if (!statusFilter.value) return invoices.value;
    return invoices.value.filter(inv => inv.status === statusFilter.value);
};

const submitForm = async () => {
    submitting.value = true;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/erp/invoices', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(form.value),
        });
        if (!res.ok) throw new Error('Erreur');
        showModal.value = false;
        form.value = {
            client_id: '', reference: '', date: new Date().toISOString().substring(0, 10),
            due_date: '', total_ht: '', total_tva: '', total_ttc: '', notes: '',
        };
        await fetchData();
    } catch (e) {
        alert('Erreur: ' + e.message);
    } finally {
        submitting.value = false;
    }
};

const statusBadgeClass = (status) => {
    const map = { brouillon: 'bg-secondary', emise: 'bg-primary', envoyee: 'bg-info', payee: 'bg-success', impayee: 'bg-danger', annulee: 'bg-dark' };
    return map[status] || 'bg-secondary';
};

onMounted(fetchData);
</script>

<template>
    <GelLayout page-title="Facturation">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <select v-model="statusFilter" class="form-select form-select-sm" style="width:auto;">
                <option value="">Tous statuts</option>
                <option value="brouillon">Brouillon</option>
                <option value="emise">Émise</option>
                <option value="envoyee">Envoyée</option>
                <option value="payee">Payée</option>
                <option value="impayee">Impayée</option>
                <option value="annulee">Annulée</option>
            </select>
            <button class="btn btn-primary btn-sm" @click="showModal = true"><i class="bi-plus-lg me-1"></i>Nouvelle facture</button>
        </div>

        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <div v-else class="card card-dashboard">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="small text-muted">
                        <tr><th>Réf.</th><th>Client</th><th>Date</th><th>Échéance</th><th class="text-end">Montant TTC</th><th>Statut</th></tr>
                    </thead>
                    <tbody>
                        <tr v-if="!filteredInvoices().length"><td colspan="6" class="text-center py-4 text-muted">Aucune facture.</td></tr>
                        <tr v-for="inv in filteredInvoices()" :key="inv.id">
                            <td class="fw-medium">{{ inv.reference }}</td>
                            <td class="small">{{ inv.client?.company_name || '-' }}</td>
                            <td class="small">{{ inv.date ? $formatDate(inv.date) : '-' }}</td>
                            <td class="small">{{ inv.due_date ? $formatDate(inv.due_date) : '-' }}</td>
                            <td class="text-end fw-medium">{{ $formatCurrency(inv.total_ttc) }}</td>
                            <td><span class="badge" :class="statusBadgeClass(inv.status)">{{ inv.status }}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header"><h6 class="fw-bold">Nouvelle facture</h6><button class="btn-close" @click="showModal = false"></button></div>
                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small">Client</label>
                                <select v-model="form.client_id" class="form-select form-select-sm">
                                    <option value="">Sélectionner</option>
                                    <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company_name }}</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Référence</label>
                                <input v-model="form.reference" class="form-control form-control-sm" placeholder="FAC-2024-001">
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Date</label>
                                <input v-model="form.date" type="date" class="form-control form-control-sm">
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Échéance</label>
                                <input v-model="form.due_date" type="date" class="form-control form-control-sm">
                            </div>
                            <div class="col-4">
                                <label class="form-label small">Total HT</label>
                                <input v-model="form.total_ht" type="number" step="0.01" class="form-control form-control-sm">
                            </div>
                            <div class="col-4">
                                <label class="form-label small">TVA</label>
                                <input v-model="form.total_tva" type="number" step="0.01" class="form-control form-control-sm">
                            </div>
                            <div class="col-4">
                                <label class="form-label small">Total TTC</label>
                                <input v-model="form.total_ttc" type="number" step="0.01" class="form-control form-control-sm">
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Notes</label>
                                <textarea v-model="form.notes" class="form-control form-control-sm" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-secondary" @click="showModal = false">Annuler</button>
                        <button class="btn btn-sm btn-primary" :disabled="submitting" @click="submitForm">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>Créer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>
