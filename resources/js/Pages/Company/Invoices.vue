<script setup>
import { ref, computed, onMounted } from 'vue';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';
import { authStore } from '../../stores/auth';

// ─── État ─────────────────────────────────────────────────────────
const activeTab = ref('list');
const invoices = ref([]);
const stats = ref(null);
const loading = ref(true);
const error = ref(null);
const statusFilter = ref('');
const selectedInvoice = ref(null);
const paymentForm = ref({ date: '', amount: '', method: 'transfer', reference: '' });

// Formulaire création
const form = ref({
    type: 'invoice',
    recipient_name: '',
    recipient_address: '',
    issue_date: new Date().toISOString().split('T')[0],
    due_date: '',
    notes: '',
    items: [
        { description: '', quantity: 1, unit_price: 0, tax_rate: 0 },
    ],
});

const submitting = ref(false);
const successMsg = ref('');
const showDetailModal = ref(false);
const showPaymentModal = ref(false);

const csrfToken = computed(() => {
    return document.querySelector('meta[name=csrf-token]')?.content || '';
});

// ─── Helpers ──────────────────────────────────────────────────────
const formatCurrency = (value) => {
    if (value === null || value === undefined || isNaN(value)) return '0,00';
    return Number(value).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('fr-FR');
};

const statusBadge = (status) => {
    const map = {
        draft: 'bg-secondary',
        sent: 'bg-primary',
        paid: 'bg-success',
        cancelled: 'bg-danger',
        overdue: 'bg-warning text-dark',
    };
    return map[status] || 'bg-secondary';
};

const statusLabel = (status) => {
    const map = {
        draft: 'Brouillon',
        sent: 'Envoyée',
        paid: 'Payée',
        cancelled: 'Annulée',
        overdue: 'En retard',
    };
    return map[status] || status;
};

const typeLabel = (type) => {
    const map = { invoice: 'Facture', credit_note: 'Avoir', devis: 'Devis' };
    return map[type] || type;
};

// ─── Calculs ligne ────────────────────────────────────────────────
const lineTotalHt = (item) => {
    return (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0);
};

const lineTotalTva = (item) => {
    return lineTotalHt(item) * ((parseFloat(item.tax_rate) || 0) / 100);
};

const lineTotalTtc = (item) => {
    return lineTotalHt(item) + lineTotalTva(item);
};

const formTotalHt = computed(() => {
    return form.value.items.reduce((sum, item) => sum + lineTotalHt(item), 0);
});

const formTotalTva = computed(() => {
    return form.value.items.reduce((sum, item) => sum + lineTotalTva(item), 0);
});

const formTotalTtc = computed(() => {
    return formTotalHt.value + formTotalTva.value;
});

// ─── Gestion lignes ───────────────────────────────────────────────
const addLine = () => {
    form.value.items.push({ description: '', quantity: 1, unit_price: 0, tax_rate: 0 });
};

const removeLine = (index) => {
    if (form.value.items.length > 1) {
        form.value.items.splice(index, 1);
    }
};

// ─── API calls ────────────────────────────────────────────────────
const loadInvoices = async () => {
    loading.value = true;
    error.value = null;
    try {
        let url = '/api/company/invoices';
        if (statusFilter.value) url += `?status=${statusFilter.value}`;
        const res = await fetch(url);
        if (!res.ok) throw new Error('Erreur chargement factures');
        invoices.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const loadStats = async () => {
    try {
        const res = await fetch('/api/company/invoices/stats');
        if (res.ok) stats.value = await res.json();
    } catch (e) {
        console.error('Erreur chargement stats', e);
    }
};

const loadInvoiceDetail = async (id) => {
    try {
        const res = await fetch(`/api/company/invoices/${id}`);
        if (!res.ok) throw new Error('Erreur chargement détail');
        const data = await res.json();
        selectedInvoice.value = data.invoice;
        showDetailModal.value = true;
    } catch (e) {
        error.value = e.message;
    }
};

const submitInvoice = async () => {
    submitting.value = true;
    error.value = null;
    try {
        const res = await fetch('/api/company/invoices', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.value,
                Accept: 'application/json',
            },
            body: JSON.stringify(form.value),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur création');
        successMsg.value = data.message;
        // Reset form
        form.value = {
            type: 'invoice',
            recipient_name: '',
            recipient_address: '',
            issue_date: new Date().toISOString().split('T')[0],
            due_date: '',
            notes: '',
            items: [{ description: '', quantity: 1, unit_price: 0, tax_rate: 0 }],
        };
        activeTab.value = 'list';
        await loadInvoices();
        await loadStats();
        setTimeout(() => { successMsg.value = ''; }, 3000);
    } catch (e) {
        error.value = e.message;
    } finally {
        submitting.value = false;
    }
};

const updateInvoiceStatus = async (id, newStatus) => {
    try {
        const res = await fetch(`/api/company/invoices/${id}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.value,
                Accept: 'application/json',
            },
            body: JSON.stringify({ status: newStatus }),
        });
        if (!res.ok) throw new Error('Erreur mise à jour statut');
        await loadInvoices();
        await loadStats();
    } catch (e) {
        error.value = e.message;
    }
};

const deleteInvoice = async (id) => {
    if (!confirm('Supprimer cette facture ?')) return;
    try {
        const res = await fetch(`/api/company/invoices/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken.value,
                Accept: 'application/json',
            },
        });
        if (!res.ok) throw new Error('Erreur suppression');
        await loadInvoices();
        await loadStats();
    } catch (e) {
        error.value = e.message;
    }
};

const submitPayment = async () => {
    if (!selectedInvoice.value) return;
    try {
        const res = await fetch(`/api/company/invoices/${selectedInvoice.value.id}/payments`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.value,
                Accept: 'application/json',
            },
            body: JSON.stringify(paymentForm.value),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur paiement');
        selectedInvoice.value = data.invoice;
        showPaymentModal.value = false;
        paymentForm.value = { date: '', amount: '', method: 'transfer', reference: '' };
        await loadInvoices();
        await loadStats();
    } catch (e) {
        error.value = e.message;
    }
};

const openPaymentModal = (invoice) => {
    selectedInvoice.value = invoice;
    paymentForm.value = {
        date: new Date().toISOString().split('T')[0],
        amount: '',
        method: 'transfer',
        reference: '',
    };
    showPaymentModal.value = true;
};

// ─── Initialisation ───────────────────────────────────────────────
onMounted(() => {
    loadInvoices();
    loadStats();
});
</script>

<template>
    <CompanyLayout page-title="Facturation">
        <!-- Messages -->
        <div v-if="successMsg" class="alert alert-success alert-dismissible rounded-3 fade show">
            <i class="bi-check-circle-fill me-2"></i>{{ successMsg }}
            <button type="button" class="btn-close" @click="successMsg = ''"></button>
        </div>
        <div v-if="error" class="alert alert-danger alert-dismissible rounded-3 fade show">
            <i class="bi-exclamation-triangle-fill me-2"></i>{{ error }}
            <button type="button" class="btn-close" @click="error = null"></button>
        </div>

        <!-- Onglets -->
        <ul class="nav nav-tabs border-0 mb-4">
            <li class="nav-item" v-for="tab in [{ key: 'list', label: 'Liste', icon: 'bi-list-ul' }, { key: 'create', label: 'Nouvelle', icon: 'bi-plus-circle' }, { key: 'stats', label: 'Statistiques', icon: 'bi-bar-chart' }]" :key="tab.key">
                <button class="nav-link rounded-top-3 px-4 py-3 fw-semibold"
                        :class="activeTab === tab.key ? 'active bg-white shadow-sm border-bottom-0' : 'text-muted'"
                        @click="activeTab = tab.key; error = null">
                    <i :class="tab.icon + ' me-2'"></i>{{ tab.label }}
                </button>
            </li>
        </ul>

        <!-- ================================ ONGLET LISTE ================================ -->
        <div v-if="activeTab === 'list'">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h5 class="fw-bold mb-0 font-heading">Toutes les factures</h5>
                    <div class="d-flex gap-2 align-items-center">
                        <label class="small text-muted">Filtrer :</label>
                        <select class="form-select form-select-sm" style="width: auto;" v-model="statusFilter" @change="loadInvoices">
                            <option value="">Tous les statuts</option>
                            <option value="draft">Brouillon</option>
                            <option value="sent">Envoyée</option>
                            <option value="paid">Payée</option>
                            <option value="cancelled">Annulée</option>
                            <option value="overdue">En retard</option>
                        </select>
                        <button class="btn btn-sm btn-outline-primary" @click="activeTab = 'create'">
                            <i class="bi-plus-lg me-1"></i>Nouvelle facture
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div v-if="loading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                    </div>

                    <div v-else-if="!invoices.length" class="text-center py-5 text-muted">
                        <i class="bi-invoice" style="font-size: 3rem;"></i>
                        <p class="mt-3 fs-5">Aucune facture trouvée.</p>
                        <button class="btn btn-primary" @click="activeTab = 'create'">Créer une facture</button>
                    </div>

                    <div v-else class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">N°</th>
                                    <th>Type</th>
                                    <th>Client</th>
                                    <th>Date</th>
                                    <th>Échéance</th>
                                    <th class="text-end">Montant TTC</th>
                                    <th class="text-end">Payé</th>
                                    <th>Statut</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="inv in invoices" :key="inv.id">
                                    <td class="ps-4 fw-semibold">{{ inv.number }}</td>
                                    <td><span class="badge bg-light text-dark">{{ typeLabel(inv.type) }}</span></td>
                                    <td>{{ inv.recipient_name }}</td>
                                    <td>{{ formatDate(inv.issue_date) }}</td>
                                    <td>{{ formatDate(inv.due_date) }}</td>
                                    <td class="text-end fw-semibold">{{ formatCurrency(inv.total_ttc) }} F</td>
                                    <td class="text-end">{{ formatCurrency(inv.paid_amount) }} F</td>
                                    <td>
                                        <span class="badge rounded-pill" :class="statusBadge(inv.computed_status || inv.status)">
                                            {{ statusLabel(inv.computed_status || inv.status) }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex gap-1 justify-content-end">
                                            <button class="btn btn-sm btn-outline-info" title="Détail" @click="loadInvoiceDetail(inv.id)">
                                                <i class="bi-eye"></i>
                                            </button>
                                            <button v-if="inv.status === 'draft'" class="btn btn-sm btn-outline-success" title="Marquer envoyée" @click="updateInvoiceStatus(inv.id, 'sent')">
                                                <i class="bi-send"></i>
                                            </button>
                                            <button v-if="inv.status === 'draft'" class="btn btn-sm btn-outline-danger" title="Supprimer" @click="deleteInvoice(inv.id)">
                                                <i class="bi-trash"></i>
                                            </button>
                                            <button v-if="inv.status !== 'paid' && inv.status !== 'cancelled'" class="btn btn-sm btn-outline-success" title="Enregistrer paiement" @click="openPaymentModal(inv)">
                                                <i class="bi-cash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================================ ONGLET CRÉATION ================================ -->
        <div v-if="activeTab === 'create'">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0 font-heading">Nouvelle facture</h5>
                </div>
                <div class="card-body p-4">
                    <form @submit.prevent="submitInvoice">
                        <div class="row g-4">
                            <!-- Type -->
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Type</label>
                                <select class="form-select" v-model="form.type">
                                    <option value="invoice">Facture</option>
                                    <option value="credit_note">Avoir</option>
                                    <option value="devis">Devis</option>
                                </select>
                            </div>

                            <!-- Date d'émission -->
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Date d'émission</label>
                                <input type="date" class="form-control" v-model="form.issue_date" required>
                            </div>

                            <!-- Date d'échéance -->
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Date d'échéance</label>
                                <input type="date" class="form-control" v-model="form.due_date" required>
                            </div>

                            <!-- Destinataire -->
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Client / Destinataire</label>
                                <input type="text" class="form-control" v-model="form.recipient_name" placeholder="Nom du client" required>
                            </div>

                            <!-- Adresse -->
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Adresse du destinataire</label>
                                <textarea class="form-control" rows="2" v-model="form.recipient_address" placeholder="Adresse..."></textarea>
                            </div>

                            <!-- Notes -->
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Notes</label>
                                <textarea class="form-control" rows="2" v-model="form.notes" placeholder="Notes optionnelles..."></textarea>
                            </div>
                        </div>

                        <!-- Lignes de facture -->
                        <hr class="my-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold mb-0">Lignes de facture</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" @click="addLine">
                                <i class="bi-plus-lg me-1"></i>Ajouter ligne
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 35%;">Description</th>
                                        <th style="width: 12%;">Quantité</th>
                                        <th style="width: 15%;">Prix unitaire</th>
                                        <th style="width: 10%;">TVA %</th>
                                        <th style="width: 13%;">Total HT</th>
                                        <th style="width: 13%;">Total TTC</th>
                                        <th style="width: 2%;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in form.items" :key="index">
                                        <td>
                                            <input type="text" class="form-control form-control-sm" v-model="item.description" placeholder="Description" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0.01" class="form-control form-control-sm" v-model.number="item.quantity" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" class="form-control form-control-sm" v-model.number="item.unit_price" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" max="100" class="form-control form-control-sm" v-model.number="item.tax_rate">
                                        </td>
                                        <td class="text-end fw-semibold">{{ formatCurrency(lineTotalHt(item)) }}</td>
                                        <td class="text-end fw-semibold">{{ formatCurrency(lineTotalTtc(item)) }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-danger" @click="removeLine(index)" :disabled="form.items.length <= 1">
                                                <i class="bi-x-lg"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light fw-bold">
                                    <tr>
                                        <td colspan="4" class="text-end">Totaux :</td>
                                        <td class="text-end">{{ formatCurrency(formTotalHt) }}</td>
                                        <td class="text-end">{{ formatCurrency(formTotalTtc) }}</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end text-muted small">TVA :</td>
                                        <td colspan="2" class="text-end text-muted small">{{ formatCurrency(formTotalTva) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-light rounded-3 px-4" @click="activeTab = 'list'">Annuler</button>
                            <button type="submit" class="btn btn-primary rounded-3 px-4" :disabled="submitting">
                                <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                                <i v-else class="bi-check-lg me-1"></i>Créer la facture
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ================================ ONGLET STATISTIQUES ================================ -->
        <div v-if="activeTab === 'stats'">
            <div v-if="!stats" class="text-center py-5 text-muted">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
            </div>
            <template v-else>
                <!-- Cartes récapitulatives -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 rounded-4 shadow-sm h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stat-icon" style="background: #e3f2fd; color: #1565c0;">
                                        <i class="bi-receipt"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted small">Total factures</div>
                                        <div class="fs-3 fw-bold">{{ stats.total_invoices }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 rounded-4 shadow-sm h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stat-icon" style="background: #e8f5e9; color: #2e7d32;">
                                        <i class="bi-cash-stack"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted small">Total TTC</div>
                                        <div class="fs-3 fw-bold">{{ formatCurrency(stats.total_ttc) }} F</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 rounded-4 shadow-sm h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stat-icon" style="background: #fff3e0; color: #e65100;">
                                        <i class="bi-wallet2"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted small">Payé</div>
                                        <div class="fs-3 fw-bold">{{ formatCurrency(stats.total_paid) }} F</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 rounded-4 shadow-sm h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stat-icon" style="background: #fce4ec; color: #c62828;">
                                        <i class="bi-exclamation-triangle"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted small">Restant du</div>
                                        <div class="fs-3 fw-bold">{{ formatCurrency(stats.total_due) }} F</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Répartition par statut -->
                <div class="card border-0 rounded-4 shadow-sm">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0 font-heading">Répartition par statut</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Statut</th>
                                        <th class="text-end">Nombre</th>
                                        <th class="text-end">Montant TTC</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(data, key) in stats.by_status" :key="key">
                                        <td>
                                            <span class="badge rounded-pill" :class="statusBadge(key)">
                                                {{ statusLabel(key) }}
                                            </span>
                                        </td>
                                        <td class="text-end">{{ data.count }}</td>
                                        <td class="text-end fw-semibold">{{ formatCurrency(data.total_ttc) }} F</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- ================================ MODAL DÉTAIL ================================ -->
        <div class="modal fade" :class="{ show: showDetailModal }" tabindex="-1"
             :style="{ display: showDetailModal ? 'block' : 'none' }"
             @click.self="showDetailModal = false">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 rounded-4">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold font-heading">
                            Facture {{ selectedInvoice?.number }}
                        </h5>
                        <button type="button" class="btn-close" @click="showDetailModal = false"></button>
                    </div>
                    <div class="modal-body" v-if="selectedInvoice">
                        <!-- Infos générales -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <small class="text-muted d-block">Type</small>
                                <span class="fw-semibold">{{ typeLabel(selectedInvoice.type) }}</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Statut</small>
                                <span class="badge rounded-pill" :class="statusBadge(selectedInvoice.computed_status || selectedInvoice.status)">
                                    {{ statusLabel(selectedInvoice.computed_status || selectedInvoice.status) }}
                                </span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Créé par</small>
                                <span class="fw-semibold">{{ selectedInvoice.created_by_name }}</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Destinataire</small>
                                <span class="fw-semibold">{{ selectedInvoice.recipient_name }}</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Date d'émission</small>
                                <span>{{ formatDate(selectedInvoice.issue_date) }}</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Date d'échéance</small>
                                <span>{{ formatDate(selectedInvoice.due_date) }}</span>
                            </div>
                            <div v-if="selectedInvoice.recipient_address" class="col-12">
                                <small class="text-muted d-block">Adresse</small>
                                <span>{{ selectedInvoice.recipient_address }}</span>
                            </div>
                            <div v-if="selectedInvoice.notes" class="col-12">
                                <small class="text-muted d-block">Notes</small>
                                <span>{{ selectedInvoice.notes }}</span>
                            </div>
                        </div>

                        <!-- Lignes -->
                        <h6 class="fw-bold mb-3">Lignes</h6>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Description</th>
                                        <th class="text-end">Qté</th>
                                        <th class="text-end">P.U.</th>
                                        <th class="text-end">TVA %</th>
                                        <th class="text-end">Total HT</th>
                                        <th class="text-end">Total TTC</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in selectedInvoice.items" :key="item.id">
                                        <td>{{ item.description }}</td>
                                        <td class="text-end">{{ item.quantity }}</td>
                                        <td class="text-end">{{ formatCurrency(item.unit_price) }}</td>
                                        <td class="text-end">{{ item.tax_rate }} %</td>
                                        <td class="text-end">{{ formatCurrency(item.total_ht) }}</td>
                                        <td class="text-end">{{ formatCurrency(item.total_ttc) }}</td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light fw-bold">
                                    <tr>
                                        <td colspan="4" class="text-end">Totaux :</td>
                                        <td class="text-end">{{ formatCurrency(selectedInvoice.total_ht) }}</td>
                                        <td class="text-end">{{ formatCurrency(selectedInvoice.total_ttc) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Paiements -->
                        <h6 class="fw-bold mb-3 mt-4">Paiements reçus</h6>
                        <div v-if="!selectedInvoice.payments?.length" class="text-muted small">
                            Aucun paiement enregistré.
                        </div>
                        <div v-else class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Montant</th>
                                        <th>Méthode</th>
                                        <th>Référence</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="p in selectedInvoice.payments" :key="p.id">
                                        <td>{{ formatDate(p.date) }}</td>
                                        <td class="fw-semibold">{{ formatCurrency(p.amount) }} F</td>
                                        <td>
                                            <span class="badge bg-light text-dark text-capitalize">{{ p.method }}</span>
                                        </td>
                                        <td>{{ p.reference || '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Récapitulatif -->
                        <div class="row g-3 mt-3 p-3 bg-light rounded-3">
                            <div class="col-md-4">
                                <small class="text-muted d-block">Total HT</small>
                                <span class="fw-bold fs-5">{{ formatCurrency(selectedInvoice.total_ht) }} F</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Total TTC</small>
                                <span class="fw-bold fs-5">{{ formatCurrency(selectedInvoice.total_ttc) }} F</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Payé</small>
                                <span class="fw-bold fs-5" :class="selectedInvoice.paid_amount >= selectedInvoice.total_ttc ? 'text-success' : 'text-warning'">
                                    {{ formatCurrency(selectedInvoice.paid_amount) }} F
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-3" @click="showDetailModal = false">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="showDetailModal" class="modal-backdrop fade show"></div>

        <!-- ================================ MODAL PAIEMENT ================================ -->
        <div class="modal fade" :class="{ show: showPaymentModal }" tabindex="-1"
             :style="{ display: showPaymentModal ? 'block' : 'none' }"
             @click.self="showPaymentModal = false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold font-heading">
                            <i class="bi-cash me-2"></i>Enregistrer un paiement
                        </h5>
                        <button type="button" class="btn-close" @click="showPaymentModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="selectedInvoice" class="mb-3">
                            <small class="text-muted">Facture {{ selectedInvoice.number }}</small>
                            <div class="d-flex justify-content-between fw-semibold">
                                <span>Total TTC : {{ formatCurrency(selectedInvoice.total_ttc) }} F</span>
                                <span>Déjà payé : {{ formatCurrency(selectedInvoice.paid_amount) }} F</span>
                            </div>
                        </div>
                        <form @submit.prevent="submitPayment">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Date</label>
                                    <input type="date" class="form-control" v-model="paymentForm.date" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Montant</label>
                                    <input type="number" step="0.01" min="0.01" class="form-control" v-model.number="paymentForm.amount" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Moyen de paiement</label>
                                    <select class="form-select" v-model="paymentForm.method">
                                        <option value="cash">Espèces</option>
                                        <option value="transfer">Virement</option>
                                        <option value="momo">Mobile Money</option>
                                        <option value="cheque">Chèque</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Référence</label>
                                    <input type="text" class="form-control" v-model="paymentForm.reference" placeholder="N° de référence">
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <button type="button" class="btn btn-light rounded-3" @click="showPaymentModal = false">Annuler</button>
                                <button type="submit" class="btn btn-success rounded-3">
                                    <i class="bi-check-lg me-1"></i>Valider le paiement
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="showPaymentModal" class="modal-backdrop fade show"></div>
    </CompanyLayout>
</template>
