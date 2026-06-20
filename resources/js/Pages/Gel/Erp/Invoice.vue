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
    client_id: '', client_name: '', invoice_number: '', invoice_date: new Date().toISOString().substring(0, 10),
    due_date: '', total_ht: '', tax_amount: '', total_ttc: '', notes: '',
    type: 'invoice', items: [{ designation: '', quantity: 1, unit_price: 0, total_price: 0 }],
});

const fetchData = async () => {
    loading.value = true;
    error.value = null;
    try {
        const [invRes, cliRes] = await Promise.all([
            fetch('/api/erp/invoices'),
            fetch('/api/clients'),
        ]);
        const invData = invRes.ok ? await invRes.json() : [];
        invoices.value = Array.isArray(invData) ? invData : (invData.data ?? []);
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
        if (!res.ok) {
            const errData = await res.json();
            throw new Error(errData.errors ? Object.values(errData.errors).flat().join(', ') : 'Erreur');
        }
        showModal.value = false;
        form.value = {
            client_id: '', invoice_number: '', invoice_date: new Date().toISOString().substring(0, 10),
            due_date: '', total_ht: '', tax_amount: '', total_ttc: '', notes: '',
            type: 'invoice', items: [{ designation: '', quantity: 1, unit_price: 0, total_price: 0 }],
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

const emitEmecef = async (invoiceId) => {
    if (!confirm('Émettre cette facture auprès de la DGI (e-MECeF) ?')) return;
    try {
        const res = await fetch(`/emecef/emit/${invoiceId}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (data.success) {
            alert('Facture émise à la DGI avec succès.');
            await fetchData();
        } else {
            alert('Erreur e-MECeF: ' + (data.error || 'Inconnue'));
        }
    } catch (e) {
        alert('Erreur réseau: ' + e.message);
    }
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

        <div v-else class="bg-white rounded-lg shadow p-6">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="small text-muted">
                        <tr><th>Réf.</th><th>Client</th><th>Date</th><th>Échéance</th><th class="text-end">Montant TTC</th><th>Statut</th><th>DGI</th></tr>
                    </thead>
                    <tbody>
                        <tr v-if="!filteredInvoices().length"><td colspan="7" class="text-center py-4 text-muted">Aucune facture.</td></tr>
                        <tr v-for="inv in filteredInvoices()" :key="inv.id">
                            <td class="fw-medium">{{ inv.reference }}</td>
                            <td class="small">{{ inv.client?.company_name || '-' }}</td>
                            <td class="small">{{ inv.date ? $formatDate(inv.date) : '-' }}</td>
                            <td class="small">{{ inv.due_date ? $formatDate(inv.due_date) : '-' }}</td>
                            <td class="text-end fw-medium">{{ $formatCurrency(inv.total_ttc) }}</td>
                            <td><span class="badge" :class="statusBadgeClass(inv.status)">{{ inv.status }}</span></td>
                            <td class="text-nowrap">
                                <button v-if="inv.status === 'emise' || inv.status === 'envoyee'" class="btn btn-outline-warning btn-sm" title="Émettre e-MECeF" @click="emitEmecef(inv.id)">
                                    <i class="bi-shield-check"></i>
                                </button>
                                <span v-if="inv.emecef_statut === 'emise'" class="badge bg-success bg-opacity-10 text-success ms-1 small">DGI</span>
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
                                <label class="form-label small">N° Facture</label>
                                <input v-model="form.invoice_number" class="form-control form-control-sm" placeholder="FAC-2026-001">
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Date</label>
                                <input v-model="form.invoice_date" type="date" class="form-control form-control-sm">
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
                                <input v-model="form.tax_amount" type="number" step="0.01" class="form-control form-control-sm">
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
