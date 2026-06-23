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
        draft: 'isup-status-grey',
        sent: 'isup-status-blue',
        paid: 'isup-status-green',
        cancelled: 'isup-status-red',
        overdue: 'isup-status-orange',
    };
    return map[status] || 'isup-status-grey';
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

const onQrError = (e) => {
    e.target.style.display = 'none';
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

const loadInvoiceDetail = async (inv) => {
    // Si c'est une facture ERP, on a déjà toutes les données
    if (inv.source === 'erp') {
        selectedInvoice.value = {
            ...inv,
            name: inv.number,
            items: [],
            payments: [],
            created_by_name: 'Comptable GEL',
        };
        showDetailModal.value = true;
        return;
    }
    try {
        const res = await fetch(`/api/company/invoices/${inv.id}`);
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
            headers: { 'X-CSRF-TOKEN': csrfToken.value, Accept: 'application/json' },
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
        <div class="isup-shell">
            <!-- ══ HEADER ══ -->
            <div class="isup-portal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="isup-portal-logo">
                        <i class="bi-receipt" style="font-size:20px;"></i>
                    </div>
                    <div>
                        <div class="isup-portal-company">Facturation</div>
                        <div class="isup-portal-sub">Factures, devis, avoirs et paiements</div>
                    </div>
                </div>
            </div>

            <div class="p-3">
                <!-- Messages -->
                <div v-if="successMsg" class="isup-alert-success mb-3">
                    <i class="bi-check-circle-fill me-2"></i>{{ successMsg }}
                    <button class="isup-alert-close" @click="successMsg = ''">&times;</button>
                </div>
                <div v-if="error" class="isup-alert-error mb-3">
                    <i class="bi-exclamation-triangle-fill me-2"></i>{{ error }}
                    <button class="isup-alert-close" @click="error = null">&times;</button>
                </div>

                <!-- Onglets -->
                <div class="isup-tabs mb-3">
                    <button class="isup-tab" :class="{ active: activeTab === 'list' }" @click="activeTab = 'list'; error = null">
                        <i class="bi-list-ul me-1"></i>Liste
                    </button>
                    <button class="isup-tab" :class="{ active: activeTab === 'create' }" @click="activeTab = 'create'; error = null">
                        <i class="bi-plus-circle me-1"></i>Nouvelle
                    </button>
                    <button class="isup-tab" :class="{ active: activeTab === 'stats' }" @click="activeTab = 'stats'; error = null">
                        <i class="bi-bar-chart me-1"></i>Statistiques
                    </button>
                </div>

                <!-- ═══ ONGLET LISTE ═══ -->
                <div v-if="activeTab === 'list'">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex align-items-center justify-content-between">
                            <span><i class="bi-list-ul me-2" style="color:#FF7900;"></i>Toutes les factures</span>
                            <div class="d-flex gap-2 align-items-center">
                                <span class="isup-filter-label">Filtrer :</span>
                                <select class="isup-select" style="width:auto;" v-model="statusFilter" @change="loadInvoices">
                                    <option value="">Tous les statuts</option>
                                    <option value="draft">Brouillon</option>
                                    <option value="sent">Envoyée</option>
                                    <option value="paid">Payée</option>
                                    <option value="cancelled">Annulée</option>
                                    <option value="overdue">En retard</option>
                                </select>
                                <button class="isup-btn-primary" style="padding:4px 12px;font-size:11px;" @click="activeTab = 'create'">
                                    <i class="bi-plus-lg me-1"></i>Nouvelle
                                </button>
                            </div>
                        </div>
                        <div class="isup-panel-body p-0">
                            <!-- Loading -->
                            <div v-if="loading" class="text-center py-5">
                                <div class="isup-spinner"></div>
                            </div>

                            <!-- Empty -->
                            <div v-else-if="!invoices.length" class="text-center py-5">
                                <i class="bi-receipt" style="font-size:48px;color:#dce3ee;display:block;margin-bottom:12px;"></i>
                                <p style="font-size:15px;color:#888;margin-bottom:16px;">Aucune facture trouvée.</p>
                                <button class="isup-btn-primary" @click="activeTab = 'create'">
                                    <i class="bi-plus-lg me-1"></i>Créer une facture
                                </button>
                            </div>

                            <!-- Table -->
                            <div v-else class="isup-table-wrap">
                                <table class="isup-table w-100">
                                    <thead>
                                        <tr>
                                            <th class="ps-4">N°</th>
                                            <th>Type</th>
                                            <th>Client</th>
                                            <th>Date</th>
                                            <th>Échéance</th>
                                            <th class="text-end">Montant TTC</th>
                                            <th class="text-end">Payé</th>
                                            <th>Statut</th>
                                            <th>DGI</th>
                                            <th class="text-end pe-4">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="inv in invoices" :key="'inv-' + inv.id" :class="{ 'isup-row-erp': inv.source === 'erp' }">
                                            <td class="ps-4 isup-inv-number">
                                                {{ inv.number }}
                                                <span v-if="inv.source === 'erp'" class="isup-source-badge" title="Facture émise par le comptable GEL">ERP</span>
                                            </td>
                                            <td><span class="isup-badge isup-badge-light">{{ typeLabel(inv.type) }}</span></td>
                                            <td class="isup-inv-client">{{ inv.recipient_name }}</td>
                                            <td class="isup-inv-date">{{ formatDate(inv.issue_date) }}</td>
                                            <td class="isup-inv-date">{{ formatDate(inv.due_date) }}</td>
                                            <td class="text-end isup-inv-amount">{{ formatCurrency(inv.total_ttc) }} F</td>
                                            <td class="text-end isup-inv-amount">{{ formatCurrency(inv.paid_amount) }} F</td>
                                            <td>
                                                <span class="isup-status" :class="statusBadge(inv.computed_status || inv.status)">
                                                    {{ statusLabel(inv.computed_status || inv.status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span v-if="inv.source === 'erp' && inv.emecef?.statut === 'emise'" class="isup-emecef-badge" title="Certifiée DGI">
                                                    <i class="bi-shield-check"></i> DGI ✓
                                                </span>
                                                <span v-else class="isup-emecef-na">—</span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-flex gap-1 justify-content-end">
                                                    <button class="isup-icon-btn" title="Détail" @click="loadInvoiceDetail(inv)">
                                                        <i class="bi-eye" style="color:#00838f;"></i>
                                                    </button>
                                                    <button v-if="!inv.source && inv.status === 'draft'" class="isup-icon-btn" title="Marquer envoyée" @click="updateInvoiceStatus(inv.id, 'sent')">
                                                        <i class="bi-send" style="color:#1565c0;"></i>
                                                    </button>
                                                    <button v-if="!inv.source && inv.status === 'draft'" class="isup-icon-btn isup-icon-danger" title="Supprimer" @click="deleteInvoice(inv.id)">
                                                        <i class="bi-trash"></i>
                                                    </button>
                                                    <button v-if="!inv.source && inv.status !== 'paid' && inv.status !== 'cancelled'" class="isup-icon-btn" title="Enregistrer paiement" @click="openPaymentModal(inv)">
                                                        <i class="bi-cash" style="color:#2e7d32;"></i>
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

                <!-- ═══ ONGLET CRÉATION ═══ -->
                <div v-if="activeTab === 'create'">
                    <div class="isup-panel">
                        <div class="isup-panel-header">
                            <i class="bi-plus-circle me-2" style="color:#FF7900;"></i>Nouvelle facture
                        </div>
                        <div class="isup-panel-body">
                            <form @submit.prevent="submitInvoice">
                                <div class="row g-2 mb-3">
                                    <div class="col-md-3">
                                        <label class="isup-label">Type</label>
                                        <select class="isup-select" v-model="form.type">
                                            <option value="invoice">Facture</option>
                                            <option value="credit_note">Avoir</option>
                                            <option value="devis">Devis</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="isup-label">Date d'émission</label>
                                        <input type="date" class="isup-input" v-model="form.issue_date" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="isup-label">Date d'échéance</label>
                                        <input type="date" class="isup-input" v-model="form.due_date" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="isup-label">Client / Destinataire</label>
                                        <input type="text" class="isup-input" v-model="form.recipient_name" placeholder="Nom du client" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="isup-label">Adresse du destinataire</label>
                                        <textarea class="isup-input" rows="2" v-model="form.recipient_address" placeholder="Adresse..."></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="isup-label">Notes</label>
                                        <textarea class="isup-input" rows="2" v-model="form.notes" placeholder="Notes optionnelles..."></textarea>
                                    </div>
                                </div>

                                <!-- Lignes -->
                                <div style="border-top:1px solid #dce3ee;padding-top:14px;">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <span style="font-size:13px;font-weight:700;color:#163A5E;">
                                            <i class="bi-list-ul me-2" style="color:#FF7900;"></i>Lignes de facture
                                        </span>
                                        <button type="button" class="isup-btn-primary" style="padding:4px 12px;font-size:11px;" @click="addLine">
                                            <i class="bi-plus-lg me-1"></i>Ajouter ligne
                                        </button>
                                    </div>

                                    <div class="isup-table-wrap">
                                        <table class="isup-table isup-table-bordered w-100">
                                            <thead>
                                                <tr>
                                                    <th style="width:32%;">Description</th>
                                                    <th style="width:12%;">Quantité</th>
                                                    <th style="width:14%;">Prix unitaire</th>
                                                    <th style="width:10%;">TVA %</th>
                                                    <th style="width:13%;">Total HT</th>
                                                    <th style="width:13%;">Total TTC</th>
                                                    <th style="width:2%;"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(item, index) in form.items" :key="index">
                                                    <td>
                                                        <input type="text" class="isup-input" v-model="item.description" placeholder="Description" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" min="0.01" class="isup-input" v-model.number="item.quantity" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" min="0" class="isup-input" v-model.number="item.unit_price" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" min="0" max="100" class="isup-input" v-model.number="item.tax_rate">
                                                    </td>
                                                    <td class="isup-inv-amount">{{ formatCurrency(lineTotalHt(item)) }}</td>
                                                    <td class="isup-inv-amount">{{ formatCurrency(lineTotalTtc(item)) }}</td>
                                                    <td>
                                                        <button type="button" class="isup-icon-btn isup-icon-danger" @click="removeLine(index)" :disabled="form.items.length <= 1">
                                                            <i class="bi-x-lg"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tfoot class="isup-tfoot">
                                                <tr>
                                                    <td colspan="4" class="text-end">Totaux :</td>
                                                    <td class="text-end isup-inv-amount">{{ formatCurrency(formTotalHt) }}</td>
                                                    <td class="text-end isup-inv-amount">{{ formatCurrency(formTotalTtc) }}</td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" class="text-end isup-tfoot-sub">TVA :</td>
                                                    <td colspan="2" class="text-end isup-tfoot-sub">{{ formatCurrency(formTotalTva) }}</td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-3">
                                    <button type="button" class="isup-btn-grey" @click="activeTab = 'list'">Annuler</button>
                                    <button type="submit" class="isup-btn-primary" :disabled="submitting">
                                        <span v-if="submitting" class="isup-spinner-sm me-1"></span>
                                        <i v-else class="bi-check-lg me-1"></i>Créer la facture
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- ═══ ONGLET STATISTIQUES ═══ -->
                <div v-if="activeTab === 'stats'">
                    <div v-if="!stats" class="text-center py-5">
                        <div class="isup-spinner"></div>
                    </div>
                    <template v-else>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <div class="isup-stat-card">
                                    <div class="isup-stat-icon isup-stat-blue"><i class="bi-receipt"></i></div>
                                    <div>
                                        <div class="isup-stat-label">Total factures</div>
                                        <div class="isup-stat-num">{{ stats.total_invoices }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="isup-stat-card">
                                    <div class="isup-stat-icon isup-stat-green"><i class="bi-cash-stack"></i></div>
                                    <div>
                                        <div class="isup-stat-label">Total TTC</div>
                                        <div class="isup-stat-num">{{ formatCurrency(stats.total_ttc) }} F</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="isup-stat-card">
                                    <div class="isup-stat-icon isup-stat-orange"><i class="bi-wallet2"></i></div>
                                    <div>
                                        <div class="isup-stat-label">Payé</div>
                                        <div class="isup-stat-num">{{ formatCurrency(stats.total_paid) }} F</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="isup-stat-card">
                                    <div class="isup-stat-icon isup-stat-red"><i class="bi-exclamation-triangle"></i></div>
                                    <div>
                                        <div class="isup-stat-label">Restant dû</div>
                                        <div class="isup-stat-num">{{ formatCurrency(stats.total_due) }} F</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="isup-panel">
                            <div class="isup-panel-header">
                                <i class="bi-pie-chart me-2" style="color:#FF7900;"></i>Répartition par statut
                            </div>
                            <div class="isup-panel-body p-0">
                                <div class="isup-table-wrap">
                                    <table class="isup-table w-100">
                                        <thead>
                                            <tr>
                                                <th>Statut</th>
                                                <th class="text-end">Nombre</th>
                                                <th class="text-end">Montant TTC</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(data, key) in stats.by_status" :key="key">
                                                <td>
                                                    <span class="isup-status" :class="statusBadge(key)">
                                                        {{ statusLabel(key) }}
                                                    </span>
                                                </td>
                                                <td class="text-end">{{ data.count }}</td>
                                                <td class="text-end isup-inv-amount">{{ formatCurrency(data.total_ttc) }} F</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- ═══ MODAL DÉTAIL ═══ -->
            <div v-if="showDetailModal" class="isup-modal-overlay" @click.self="showDetailModal = false">
                <div class="isup-modal isup-modal-lg">
                    <div class="isup-modal-header">
                        <span>Facture {{ selectedInvoice?.number }}</span>
                        <button class="isup-modal-close" @click="showDetailModal = false">&times;</button>
                    </div>
                    <div class="isup-modal-body" v-if="selectedInvoice">
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <span class="isup-detail-label">Type</span>
                                <span class="isup-detail-val">{{ typeLabel(selectedInvoice.type) }}</span>
                            </div>
                            <div class="col-md-4">
                                <span class="isup-detail-label">Statut</span>
                                <span class="isup-status" :class="statusBadge(selectedInvoice.computed_status || selectedInvoice.status)">
                                    {{ statusLabel(selectedInvoice.computed_status || selectedInvoice.status) }}
                                </span>
                            </div>
                            <div class="col-md-4">
                                <span class="isup-detail-label">Créé par</span>
                                <span class="isup-detail-val">{{ selectedInvoice.created_by_name }}</span>
                            </div>
                            <div class="col-md-4">
                                <span class="isup-detail-label">Destinataire</span>
                                <span class="isup-detail-val">{{ selectedInvoice.recipient_name }}</span>
                            </div>
                            <div class="col-md-4">
                                <span class="isup-detail-label">Date d'émission</span>
                                <span class="isup-detail-val">{{ formatDate(selectedInvoice.issue_date) }}</span>
                            </div>
                            <div class="col-md-4">
                                <span class="isup-detail-label">Date d'échéance</span>
                                <span class="isup-detail-val">{{ formatDate(selectedInvoice.due_date) }}</span>
                            </div>
                            <div v-if="selectedInvoice.recipient_address" class="col-12">
                                <span class="isup-detail-label">Adresse</span>
                                <span class="isup-detail-val">{{ selectedInvoice.recipient_address }}</span>
                            </div>
                            <div v-if="selectedInvoice.notes" class="col-12">
                                <span class="isup-detail-label">Notes</span>
                                <span class="isup-detail-val">{{ selectedInvoice.notes }}</span>
                            </div>
                        </div>

                        <!-- Lignes -->
                        <div style="font-size:13px;font-weight:700;color:#163A5E;margin-bottom:10px;">Lignes</div>
                        <div class="isup-table-wrap">
                            <table class="isup-table w-100">
                                <thead>
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
                                <tfoot class="isup-tfoot">
                                    <tr>
                                        <td colspan="4" class="text-end">Totaux :</td>
                                        <td class="text-end">{{ formatCurrency(selectedInvoice.total_ht) }}</td>
                                        <td class="text-end">{{ formatCurrency(selectedInvoice.total_ttc) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Paiements -->
                        <div style="font-size:13px;font-weight:700;color:#163A5E;margin:16px 0 10px;">Paiements reçus</div>
                        <div v-if="!selectedInvoice.payments?.length" style="font-size:12px;color:#888;">
                            Aucun paiement enregistré.
                        </div>
                        <div v-else class="isup-table-wrap">
                            <table class="isup-table w-100">
                                <thead>
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
                                        <td class="isup-inv-amount">{{ formatCurrency(p.amount) }} F</td>
                                        <td><span class="isup-badge isup-badge-light">{{ p.method }}</span></td>
                                        <td>{{ p.reference || '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Récapitulatif -->
                        <div class="isup-inv-summary">
                            <div>
                                <span class="isup-detail-label">Total HT</span>
                                <span class="isup-inv-sum-val">{{ formatCurrency(selectedInvoice.total_ht) }} F</span>
                            </div>
                            <div>
                                <span class="isup-detail-label">Total TTC</span>
                                <span class="isup-inv-sum-val">{{ formatCurrency(selectedInvoice.total_ttc) }} F</span>
                            </div>
                            <div>
                                <span class="isup-detail-label">Payé</span>
                                <span class="isup-inv-sum-val" :class="selectedInvoice.paid_amount >= selectedInvoice.total_ttc ? 'isup-sum-paid' : 'isup-sum-due'">
                                    {{ formatCurrency(selectedInvoice.paid_amount) }} F
                                </span>
                            </div>
                        </div>

                        <!-- QR Code e-MECeF -->
                        <div v-if="selectedInvoice.emecef?.qr" class="isup-emecef-section mt-3">
                            <div style="font-size:13px;font-weight:700;color:#163A5E;margin-bottom:10px;">
                                <i class="bi-shield-check me-2" style="color:#2e7d32;"></i>Certification DGI (e-MECeF)
                            </div>
                            <div class="isup-emecef-card">
                                <div class="isup-emecef-qr-wrap">
                                    <img :src="selectedInvoice.emecef.qr" alt="QR Code DGI" class="isup-emecef-qr"
                                         @error="onQrError" />
                                </div>
                                <div class="isup-emecef-info">
                                    <div class="isup-emecef-row">
                                        <span class="isup-emecef-label">NIM</span>
                                        <span class="isup-emecef-value">{{ selectedInvoice.emecef.nim }}</span>
                                    </div>
                                    <div class="isup-emecef-row">
                                        <span class="isup-emecef-label">Compteur</span>
                                        <span class="isup-emecef-value">{{ selectedInvoice.emecef.compteur }}</span>
                                    </div>
                                    <div class="isup-emecef-row">
                                        <span class="isup-emecef-label">Émis le</span>
                                        <span class="isup-emecef-value">{{ selectedInvoice.emecef.datetime }}</span>
                                    </div>
                                    <div class="isup-emecef-row">
                                        <span class="isup-emecef-label">Statut</span>
                                        <span class="isup-emecef-value isup-emecef-valide">Certifiée ✓</span>
                                    </div>
                                    <div class="isup-emecef-row isup-emecef-full">
                                        <span class="isup-emecef-label">QR URL</span>
                                        <span class="isup-emecef-url">{{ selectedInvoice.emecef.qr }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="isup-modal-footer">
                        <button class="isup-btn-grey" @click="showDetailModal = false">Fermer</button>
                    </div>
                </div>
            </div>

            <!-- ═══ MODAL PAIEMENT ═══ -->
            <div v-if="showPaymentModal" class="isup-modal-overlay" @click.self="showPaymentModal = false">
                <div class="isup-modal">
                    <div class="isup-modal-header">
                        <span><i class="bi-cash me-2" style="color:#2e7d32;"></i>Enregistrer un paiement</span>
                        <button class="isup-modal-close" @click="showPaymentModal = false">&times;</button>
                    </div>
                    <div class="isup-modal-body">
                        <div v-if="selectedInvoice" class="isup-pay-info">
                            <span style="font-size:12px;color:#888;">Facture {{ selectedInvoice.number }}</span>
                            <div class="d-flex justify-content-between isup-inv-amount">
                                <span>Total TTC : <strong>{{ formatCurrency(selectedInvoice.total_ttc) }} F</strong></span>
                                <span>Déjà payé : <strong>{{ formatCurrency(selectedInvoice.paid_amount) }} F</strong></span>
                            </div>
                        </div>
                        <form @submit.prevent="submitPayment">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="isup-label">Date</label>
                                    <input type="date" class="isup-input" v-model="paymentForm.date" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="isup-label">Montant</label>
                                    <input type="number" step="0.01" min="0.01" class="isup-input" v-model.number="paymentForm.amount" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="isup-label">Moyen de paiement</label>
                                    <select class="isup-select" v-model="paymentForm.method">
                                        <option value="cash">Espèces</option>
                                        <option value="transfer">Virement</option>
                                        <option value="momo">Mobile Money</option>
                                        <option value="cheque">Chèque</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="isup-label">Référence</label>
                                    <input type="text" class="isup-input" v-model="paymentForm.reference" placeholder="N° de référence">
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <button type="button" class="isup-btn-grey" @click="showPaymentModal = false">Annuler</button>
                                <button type="submit" class="isup-btn-primary" style="background:#2e7d32;">
                                    <i class="bi-check-lg me-1"></i>Valider le paiement
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </CompanyLayout>
</template>

<style scoped>
/* ── Invoice-specific styles ── */
.isup-table-bordered td { border-left:1px solid #f0f4f8; }
.isup-table-bordered td:first-child { border-left:none; }
.isup-tfoot td { background:#f8fafc; font-weight:600; color:#163A5E; font-size:12px; }
.isup-tfoot-sub { color:#888; font-weight:400; font-size:11px; }
.isup-inv-number { font-weight:600; color:#163A5E; font-size:12px; }
.isup-inv-client { font-size:12px; }
.isup-inv-date { font-size:11px; color:#888; }
.isup-inv-amount { font-size:12px; font-weight:600; color:#333; }
.isup-filter-label { font-size:11px; color:#888; white-space:nowrap; }
.isup-detail-label { display:block; font-size:10px; font-weight:700; color:#163A5E; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:2px; }
.isup-detail-val { display:block; font-size:13px; color:#333; margin-bottom:8px; }
.isup-inv-summary { display:flex; gap:24px; background:#f8fafc; border:1px solid #eef2f7; border-radius:4px; padding:14px 18px; }
.isup-inv-summary > div { flex:1; }
.isup-inv-sum-val { display:block; font-size:16px; font-weight:800; font-family:'Outfit',sans-serif; color:#163A5E; }
.isup-sum-paid { color:#2e7d32; }
.isup-sum-due { color:#e65100; }
.isup-pay-info { background:#e3f2fd; border:1px solid #bbdefb; border-radius:4px; padding:12px 14px; margin-top:14px; }
.isup-alert-close { float:right; background:none; border:none; font-size:18px; line-height:1; color:#888; cursor:pointer; opacity:0.7; margin:-6px -4px 0 0; padding:0; }
.isup-alert-close:hover { opacity:1; }

/* ── ERP / e-MECeF specific styles ── */
.isup-row-erp { background:#fafbfc; }
.isup-row-erp:hover { background:#f0f4f8 !important; }
.isup-source-badge { display:inline-block; font-size:9px; font-weight:700; padding:1px 5px; border-radius:3px; background:#163A5E; color:#fff; margin-left:5px; vertical-align:middle; letter-spacing:0.02em; }
.isup-emecef-badge { display:inline-flex; align-items:center; gap:3px; font-size:11px; font-weight:600; color:#2e7d32; background:#e8f5e9; padding:2px 8px; border-radius:4px; white-space:nowrap; }
.isup-emecef-na { color:#ccc; font-size:11px; }

/* ── e-MECeF detail card ── */
.isup-emecef-section { margin-top:16px; }
.isup-emecef-card { display:flex; gap:16px; background:#f1f8e9; border:1px solid #c8e6c9; border-radius:8px; padding:14px; }
.isup-emecef-qr-wrap { flex-shrink:0; }
.isup-emecef-qr { width:120px; height:120px; border:2px solid #fff; border-radius:4px; background:#fff; }
.isup-emecef-info { flex:1; display:flex; flex-direction:column; gap:4px; }
.isup-emecef-row { display:flex; align-items:center; gap:8px; }
.isup-emecef-label { font-size:10px; font-weight:700; color:#558b2f; text-transform:uppercase; letter-spacing:0.03em; min-width:60px; }
.isup-emecef-value { font-size:12px; font-weight:600; color:#33691e; }
.isup-emecef-valide { color:#2e7d32; }
.isup-emecef-url { font-size:10px; color:#689f38; word-break:break-all; font-family:monospace; }
.isup-emecef-full { flex-direction:column; align-items:flex-start; gap:2px; }

@media (max-width:576px) {
    .isup-emecef-card { flex-direction:column; align-items:center; }
}
</style>
