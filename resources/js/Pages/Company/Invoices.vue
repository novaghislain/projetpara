<script setup>
import { ref, computed, onMounted } from 'vue';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';

const clientId = window.__CLIENT_ID__;
const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;

// ─── Onglets ──────────────────────────────────────────────────────────
const activeTab = ref('list');
const tabs = [
    { key: 'list', label: 'Factures', icon: 'bi-list-ul' },
    { key: 'create', label: 'Nouvelle facture', icon: 'bi-plus-circle' },
    { key: 'stats', label: 'Statistiques', icon: 'bi-graph-up' },
];

// ─── Utilitaires ──────────────────────────────────────────────────────
const formatCurrency = (value) => {
    if (value === null || value === undefined || isNaN(value)) return '0,00';
    return Number(value).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr + (dateStr.includes('T') ? '' : 'T00:00:00'));
    return d.toLocaleDateString('fr-FR', { year: 'numeric', month: 'short', day: 'numeric' });
};

const statusLabels = {
    brouillon: 'Brouillon',
    emise: 'Emise',
    payee: 'Payee',
    annulee: 'Annulee',
    impayee: 'Impayee',
};

const statusColors = {
    brouillon: 'secondary',
    emise: 'primary',
    payee: 'success',
    annulee: 'dark',
    impayee: 'danger',
};

const typeLabels = {
    devis: 'Devis',
    facture: 'Facture',
    avoir: 'Avoir',
};

const methodLabels = {
    cash: 'Especes',
    transfer: 'Virement',
    momo: 'Mobile Money',
    cheque: 'Cheque',
};

// ─── API Helper ───────────────────────────────────────────────────────
const apiFetch = async (url, options = {}) => {
    const defaultHeaders = {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    };
    const res = await fetch(url, {
        ...options,
        headers: { ...defaultHeaders, ...options.headers },
    });
    if (res.status === 403) {
        const data = await res.json();
        throw new Error(data.message || 'Acces refuse.');
    }
    if (!res.ok) {
        const data = await res.json();
        throw new Error(data.message || 'Erreur serveur.');
    }
    return res.json();
};

// ─── ETAT: LISTE ──────────────────────────────────────────────────────
const invoices = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const listLoading = ref(false);
const listError = ref(null);
const filterStatus = ref('');
const filterType = ref('');
const searchQuery = ref('');

const loadInvoices = async (page = 1) => {
    listLoading.value = true;
    listError.value = null;
    try {
        const params = new URLSearchParams({ page, per_page: 20 });
        if (filterStatus.value) params.append('status', filterStatus.value);
        if (filterType.value) params.append('type', filterType.value);
        if (searchQuery.value) params.append('search', searchQuery.value);

        const data = await apiFetch(`/api/company/invoices?${params}`);
        invoices.value = data.data || data;
        pagination.value = {
            current_page: data.current_page || 1,
            last_page: data.last_page || 1,
            total: data.total || 0,
        };
    } catch (e) {
        listError.value = e.message;
    } finally {
        listLoading.value = false;
    }
};

// ─── ETAT: CREATION ───────────────────────────────────────────────────
const form = ref({
    type: 'facture',
    recipient_name: '',
    recipient_address: '',
    issue_date: new Date().toISOString().split('T')[0],
    due_date: '',
    notes: '',
    items: [
        { description: '', quantity: 1, unit_price: 0, tax_rate: 0 },
    ],
});

const createLoading = ref(false);
const createError = ref(null);
const createSuccess = ref(null);

const addLine = () => {
    form.value.items.push({ description: '', quantity: 1, unit_price: 0, tax_rate: 0 });
};

const removeLine = (index) => {
    if (form.value.items.length > 1) {
        form.value.items.splice(index, 1);
    }
};

const computeLineHt = (item) => (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0);
const computeLineTva = (item) => computeLineHt(item) * ((parseFloat(item.tax_rate) || 0) / 100);
const computeLineTtc = (item) => computeLineHt(item) + computeLineTva(item);

const totalHt = computed(() => form.value.items.reduce((sum, item) => sum + computeLineHt(item), 0));
const totalTva = computed(() => form.value.items.reduce((sum, item) => sum + computeLineTva(item), 0));
const totalTtc = computed(() => totalHt.value + totalTva.value);

const resetForm = () => {
    form.value = {
        type: 'facture',
        recipient_name: '',
        recipient_address: '',
        issue_date: new Date().toISOString().split('T')[0],
        due_date: '',
        notes: '',
        items: [{ description: '', quantity: 1, unit_price: 0, tax_rate: 0 }],
    };
    createSuccess.value = null;
    createError.value = null;
};

const submitInvoice = async () => {
    createLoading.value = true;
    createError.value = null;
    createSuccess.value = null;

    try {
        const payload = {
            ...form.value,
            items: form.value.items.map(item => ({
                ...item,
                quantity: parseFloat(item.quantity) || 0,
                unit_price: parseFloat(item.unit_price) || 0,
                tax_rate: parseFloat(item.tax_rate) || 0,
            })),
        };

        const data = await apiFetch('/api/company/invoices', {
            method: 'POST',
            body: JSON.stringify(payload),
        });

        createSuccess.value = `Facture creee: ${data.invoice.number}`;
        resetForm();
        activeTab.value = 'list';
        loadInvoices();
    } catch (e) {
        createError.value = e.message;
    } finally {
        createLoading.value = false;
    }
};

// ─── ETAT: DETAIL (MODAL) ────────────────────────────────────────────
const detailInvoice = ref(null);
const detailLoading = ref(false);
const showDetailModal = ref(false);

const openDetail = async (id) => {
    detailLoading.value = true;
    try {
        const data = await apiFetch(`/api/company/invoices/${id}`);
        detailInvoice.value = data;
        showDetailModal.value = true;
    } catch (e) {
        alert(e.message);
    } finally {
        detailLoading.value = false;
    }
};

const closeDetail = () => {
    showDetailModal.value = false;
    detailInvoice.value = null;
};

// ─── ETAT: PAIEMENT (dans le modal) ──────────────────────────────────
const paymentForm = ref({ date: new Date().toISOString().split('T')[0], amount: 0, method: 'transfer', reference: '' });
const paymentLoading = ref(false);
const paymentError = ref(null);

const openPaymentForm = () => {
    if (!detailInvoice.value) return;
    paymentForm.value = {
        date: new Date().toISOString().split('T')[0],
        amount: detailInvoice.value.balance || detailInvoice.value.total_ttc - detailInvoice.value.paid_amount,
        method: 'transfer',
        reference: '',
    };
    paymentError.value = null;
};

const submitPayment = async () => {
    if (!detailInvoice.value) return;
    paymentLoading.value = true;
    paymentError.value = null;
    try {
        const data = await apiFetch(`/api/company/invoices/${detailInvoice.value.id}/payments`, {
            method: 'POST',
            body: JSON.stringify(paymentForm.value),
        });
        detailInvoice.value = data.invoice;
        paymentForm.value = { date: new Date().toISOString().split('T')[0], amount: 0, method: 'transfer', reference: '' };
        loadInvoices();
    } catch (e) {
        paymentError.value = e.message;
    } finally {
        paymentLoading.value = false;
    }
};

// ─── ACTIONS: STATUT ──────────────────────────────────────────────────
const updateStatus = async (id, status) => {
    if (!confirm(`Confirmer le passage au statut "${statusLabels[status] || status}" ?`)) return;
    try {
        await apiFetch(`/api/company/invoices/${id}/status`, {
            method: 'PATCH',
            body: JSON.stringify({ status }),
        });
        if (detailInvoice.value?.id === id) {
            detailInvoice.value = await apiFetch(`/api/company/invoices/${id}`);
        }
        loadInvoices();
    } catch (e) {
        alert(e.message);
    }
};

// ─── ACTIONS: SUPPRESSION ────────────────────────────────────────────
const confirmDelete = async (id) => {
    if (!confirm('Supprimer cette facture ? Cette action est irreversible.')) return;
    try {
        await apiFetch(`/api/company/invoices/${id}`, { method: 'DELETE' });
        if (showDetailModal.value && detailInvoice.value?.id === id) {
            closeDetail();
        }
        loadInvoices();
    } catch (e) {
        alert(e.message);
    }
};

// ─── ETAT: STATISTIQUES ──────────────────────────────────────────────
const statsData = ref(null);
const statsLoading = ref(false);
const statsError = ref(null);

const loadStats = async () => {
    statsLoading.value = true;
    statsError.value = null;
    try {
        const data = await apiFetch('/api/company/invoices/stats');
        statsData.value = data;
    } catch (e) {
        statsError.value = e.message;
    } finally {
        statsLoading.value = false;
    }
};

const totalDue = computed(() => {
    if (!statsData.value) return 0;
    return statsData.value.by_status?.reduce((sum, s) => {
        if (s.status !== 'payee' && s.status !== 'annulee') {
            return sum + parseFloat(s.total || 0);
        }
        return sum;
    }, 0) || 0;
});

// ─── IMPRESSION ───────────────────────────────────────────────────────
const printInvoice = () => {
    if (!detailInvoice.value) return;
    const inv = detailInvoice.value;
    const w = window.open('', '_blank');
    w.document.write(`
        <!DOCTYPE html>
        <html><head><meta charset="utf-8">
        <title>${inv.number}</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { padding: 40px; font-family: 'Inter', sans-serif; }
            .header { border-bottom: 3px solid #1a237e; padding-bottom: 20px; margin-bottom: 30px; }
            table { width: 100%; border-collapse: collapse; }
            th { background: #f5f7fa; padding: 10px 12px; text-align: left; font-size: 13px; text-transform: uppercase; }
            td { padding: 10px 12px; border-bottom: 1px solid #eee; }
            .totals { margin-top: 30px; text-align: right; }
            .footer { margin-top: 50px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; text-align: center; }
            @media print { body { padding: 0; } .no-print { display: none !important; } }
        </style>
        </head><body>
        <div class="header d-flex justify-content-between align-items-center">
            <div>
                <h2 style="color: #1a237e; margin: 0;">${typeLabels[inv.type] || inv.type}</h2>
                <h5 style="margin: 5px 0 0; color: #666;">N° ${inv.number}</h5>
            </div>
            <div class="text-end">
                <h5 style="margin: 0;">${inv.recipient_name}</h5>
                ${inv.recipient_address ? '<p style="margin: 5px 0 0; color: #666;">' + inv.recipient_address.replace(/\n/g, '<br>') + '</p>' : ''}
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-6">
                <small class="text-muted">Date d'emission</small><br>
                <strong>${formatDate(inv.issue_date)}</strong>
            </div>
            <div class="col-6 text-end">
                ${inv.due_date ? '<small class="text-muted">Echeance</small><br><strong>' + formatDate(inv.due_date) + '</strong>' : ''}
            </div>
        </div>
        <table>
            <thead><tr>
                <th>Description</th>
                <th style="text-align:center;">Qté</th>
                <th style="text-align:right;">Prix unit.</th>
                <th style="text-align:right;">TVA</th>
                <th style="text-align:right;">Total HT</th>
                <th style="text-align:right;">Total TTC</th>
            </tr></thead>
            <tbody>
                ${(inv.items || []).map(i => '<tr>' +
                    '<td>' + i.description + '</td>' +
                    '<td style="text-align:center;">' + Number(i.quantity).toLocaleString('fr-FR') + '</td>' +
                    '<td style="text-align:right;">' + Number(i.unit_price).toLocaleString('fr-FR', {minimumFractionDigits:2}) + '</td>' +
                    '<td style="text-align:right;">' + Number(i.tax_rate).toLocaleString('fr-FR') + '%</td>' +
                    '<td style="text-align:right;">' + Number(i.total_ht).toLocaleString('fr-FR', {minimumFractionDigits:2}) + '</td>' +
                    '<td style="text-align:right;">' + Number(i.total_ttc).toLocaleString('fr-FR', {minimumFractionDigits:2}) + '</td>' +
                '</tr>').join('')}
            </tbody>
        </table>
        <div class="totals">
            <div><strong>Total HT:</strong> ${Number(inv.total_ht).toLocaleString('fr-FR', {minimumFractionDigits:2})} CFA</div>
            <div><strong>TVA:</strong> ${Number(inv.total_tva).toLocaleString('fr-FR', {minimumFractionDigits:2})} CFA</div>
            <div style="font-size:18px;"><strong>Total TTC:</strong> ${Number(inv.total_ttc).toLocaleString('fr-FR', {minimumFractionDigits:2})} CFA</div>
            ${inv.paid_amount > 0 ? '<div><strong>Deja paye:</strong> ' + Number(inv.paid_amount).toLocaleString('fr-FR', {minimumFractionDigits:2}) + ' CFA</div>' : ''}
            ${inv.balance > 0 ? '<div style="color:#dc3545;"><strong>Reste:</strong> ' + Number(inv.balance).toLocaleString('fr-FR', {minimumFractionDigits:2}) + ' CFA</div>' : ''}
        </div>
        ${inv.notes ? '<div class="mt-4 p-3 bg-light rounded"><strong>Notes:</strong><br>' + inv.notes + '</div>' : ''}
        <div class="footer">Document genere le ${new Date().toLocaleDateString('fr-FR')} via GEL Cabinet</div>
        <div class="no-print text-center mt-4"><button class="btn btn-primary" onclick="window.print()">Imprimer</button> <button class="btn btn-secondary" onclick="window.close()">Fermer</button></div>
        <script>window.print();<' + '/' + 'script>
        </body></html>
    `);
    w.document.close();
};

// ─── INIT ─────────────────────────────────────────────────────────────
onMounted(() => {
    loadInvoices();
});

const onTabChange = (tab) => {
    activeTab.value = tab;
    if (tab === 'stats') {
        loadStats();
    }
};
</script>

<template>
    <CompanyLayout page-title="Facturation">
        <!-- Onglets -->
        <ul class="nav nav-tabs border-0 mb-4">
            <li v-for="t in tabs" :key="t.key" class="nav-item">
                <button class="nav-link rounded-top-3 px-4 py-3 fw-semibold"
                        :class="activeTab === t.key ? 'active bg-white border-primary text-primary' : 'text-muted'"
                        @click="onTabChange(t.key)">
                    <i :class="t.icon" class="me-2"></i>{{ t.label }}
                </button>
            </li>
        </ul>

        <!-- ============================================================ -->
        <!-- ONGLET: LISTE                                                -->
        <!-- ============================================================ -->
        <div v-if="activeTab === 'list'">
            <!-- Filtres -->
            <div class="card border-0 rounded-4 mb-4 shadow-sm">
                <div class="card-body p-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">Recherche</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi-search text-muted"></i>
                                </span>
                                <input type="text" class="form-control border-start-0 ps-0" placeholder="N° ou client..."
                                       v-model="searchQuery" @keyup.enter="loadInvoices()">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted">Statut</label>
                            <select class="form-select" v-model="filterStatus" @change="loadInvoices()">
                                <option value="">Tous les statuts</option>
                                <option v-for="(label, key) in statusLabels" :key="key" :value="key">{{ label }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted">Type</label>
                            <select class="form-select" v-model="filterType" @change="loadInvoices()">
                                <option value="">Tous les types</option>
                                <option v-for="(label, key) in typeLabels" :key="key" :value="key">{{ label }}</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button class="btn btn-outline-primary" @click="loadInvoices()">
                                <i class="bi-search me-1"></i>Filtrer
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading -->
            <div v-if="listLoading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
            </div>

            <!-- Error -->
            <div v-else-if="listError" class="alert alert-danger rounded-3">
                <i class="bi-exclamation-triangle me-2"></i>{{ listError }}
            </div>

            <!-- Tableau -->
            <div v-else class="card border-0 rounded-4 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3">N° Facture</th>
                                    <th class="py-3">Client</th>
                                    <th class="py-3">Type</th>
                                    <th class="py-3">Date</th>
                                    <th class="py-3 text-end">Montant TTC</th>
                                    <th class="py-3">Statut</th>
                                    <th class="pe-4 py-3 text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!invoices.length">
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi-inbox" style="font-size: 2rem;"></i>
                                        <p class="mt-2 mb-0">Aucune facture trouvee.</p>
                                    </td>
                                </tr>
                                <tr v-for="inv in invoices" :key="inv.id" class="align-middle">
                                    <td class="ps-4 fw-semibold">{{ inv.number }}</td>
                                    <td>{{ inv.recipient_name }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark rounded-pill">
                                            {{ typeLabels[inv.type] || inv.type }}
                                        </span>
                                    </td>
                                    <td class="text-muted small">{{ formatDate(inv.issue_date) }}</td>
                                    <td class="text-end fw-semibold">{{ formatCurrency(inv.total_ttc) }} CFA</td>
                                    <td>
                                        <span class="badge rounded-pill"
                                              :class="'bg-' + (statusColors[inv.status] || 'secondary') + ' bg-opacity-10 text-' + (statusColors[inv.status] || 'secondary')">
                                            {{ statusLabels[inv.status] || inv.status }}
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <button class="btn btn-sm btn-outline-primary rounded-pill"
                                                @click="openDetail(inv.id)" title="Voir">
                                            <i class="bi-eye"></i>
                                        </button>
                                        <button v-if="inv.status === 'brouillon'"
                                                class="btn btn-sm btn-outline-danger rounded-pill ms-1"
                                                @click="confirmDelete(inv.id)" title="Supprimer">
                                            <i class="bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="pagination.last_page > 1" class="d-flex justify-content-between align-items-center p-3 border-top">
                        <small class="text-muted">
                            Page {{ pagination.current_page }} sur {{ pagination.last_page }} ({{ pagination.total }} resultats)
                        </small>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item" :class="{ disabled: pagination.current_page <= 1 }">
                                    <button class="page-link" @click="loadInvoices(pagination.current_page - 1)">
                                        <i class="bi-chevron-left"></i>
                                    </button>
                                </li>
                                <li v-for="p in pagination.last_page" :key="p" class="page-item"
                                    :class="{ active: p === pagination.current_page }">
                                    <button class="page-link" @click="loadInvoices(p)">{{ p }}</button>
                                </li>
                                <li class="page-item" :class="{ disabled: pagination.current_page >= pagination.last_page }">
                                    <button class="page-link" @click="loadInvoices(pagination.current_page + 1)">
                                        <i class="bi-chevron-right"></i>
                                    </button>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- ONGLET: CREATION                                             -->
        <!-- ============================================================ -->
        <div v-if="activeTab === 'create'">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 rounded-4 shadow-sm">
                        <div class="card-header bg-white border-0 rounded-4 pt-4 px-4">
                            <h5 class="fw-bold mb-0 font-heading">
                                <i class="bi-file-earmark-text me-2 text-primary"></i>
                                Nouvelle facture
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <!-- Alerts -->
                            <div v-if="createSuccess" class="alert alert-success rounded-3 d-flex align-items-center">
                                <i class="bi-check-circle me-2"></i>{{ createSuccess }}
                            </div>
                            <div v-if="createError" class="alert alert-danger rounded-3">
                                <i class="bi-exclamation-triangle me-2"></i>{{ createError }}
                            </div>

                            <form @submit.prevent="submitInvoice">
                                <!-- Infos generales -->
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Type</label>
                                        <select class="form-select" v-model="form.type">
                                            <option value="facture">Facture</option>
                                            <option value="devis">Devis</option>
                                            <option value="avoir">Avoir</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Date d'emission</label>
                                        <input type="date" class="form-control" v-model="form.issue_date" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Date d'echeance</label>
                                        <input type="date" class="form-control" v-model="form.due_date">
                                    </div>
                                </div>

                                <!-- Destinataire -->
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3 text-primary">
                                        <i class="bi-person me-2"></i>Destinataire
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Nom / Societe <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" v-model="form.recipient_name" required
                                                   placeholder="Nom du client ou de la societe">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Adresse</label>
                                            <textarea class="form-control" v-model="form.recipient_address" rows="2"
                                                      placeholder="Adresse complete"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Lignes de facture -->
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="fw-bold mb-0 text-primary">
                                            <i class="bi-list me-2"></i>Lignes de facture
                                        </h6>
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill"
                                                @click="addLine">
                                            <i class="bi-plus-lg me-1"></i>Ajouter une ligne
                                        </button>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="min-width:200px;">Description</th>
                                                    <th style="width:90px;" class="text-center">Qte</th>
                                                    <th style="width:130px;" class="text-end">Prix unit.</th>
                                                    <th style="width:80px;" class="text-center">TVA %</th>
                                                    <th style="width:130px;" class="text-end">Total HT</th>
                                                    <th style="width:130px;" class="text-end">Total TTC</th>
                                                    <th style="width:40px;"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(item, idx) in form.items" :key="idx">
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm"
                                                               v-model="item.description" placeholder="Description" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control form-control-sm text-center"
                                                               v-model="item.quantity" min="0.01" step="0.01" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control form-control-sm text-end"
                                                               v-model="item.unit_price" min="0" step="0.01" required>
                                                    </td>
                                                    <td>
                                                        <select class="form-select form-select-sm" v-model="item.tax_rate">
                                                            <option :value="0">0%</option>
                                                            <option :value="5">5%</option>
                                                            <option :value="10">10%</option>
                                                            <option :value="18">18%</option>
                                                            <option :value="19.25">19.25%</option>
                                                            <option :value="20">20%</option>
                                                        </select>
                                                    </td>
                                                    <td class="text-end fw-semibold">
                                                        {{ formatCurrency(computeLineHt(item)) }}
                                                    </td>
                                                    <td class="text-end fw-semibold">
                                                        {{ formatCurrency(computeLineTtc(item)) }}
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-outline-danger border-0"
                                                                @click="removeLine(idx)"
                                                                :disabled="form.items.length <= 1">
                                                            <i class="bi-x-lg"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tfoot class="table-light fw-bold">
                                                <tr>
                                                    <td colspan="4" class="text-end">Totaux</td>
                                                    <td class="text-end">{{ formatCurrency(totalHt) }}</td>
                                                    <td class="text-end">{{ formatCurrency(totalTtc) }}</td>
                                                    <td></td>
                                                </tr>
                                                <tr v-if="totalTva > 0">
                                                    <td colspan="4" class="text-end text-muted fw-normal">TVA</td>
                                                    <td colspan="2" class="text-end text-muted fw-normal">{{ formatCurrency(totalTva) }}</td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Notes (optionnelles)</label>
                                    <textarea class="form-control" v-model="form.notes" rows="3"
                                              placeholder="Conditions de paiement, instructions..."></textarea>
                                </div>

                                <!-- Submit -->
                                <div class="d-flex gap-2 justify-content-end">
                                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                                            @click="resetForm">
                                        <i class="bi-arrow-counterclockwise me-1"></i>Reinitialiser
                                    </button>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4"
                                            :disabled="createLoading">
                                        <span v-if="createLoading" class="spinner-border spinner-border-sm me-1"></span>
                                        <i v-else class="bi-check-lg me-1"></i>
                                        Creer la facture
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Apercu rapide -->
                <div class="col-lg-4">
                    <div class="card border-0 rounded-4 shadow-sm bg-light">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3">
                                <i class="bi-calculator me-2 text-primary"></i>Apercu
                            </h6>
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Type</span>
                                <span class="fw-semibold">{{ typeLabels[form.type] || form.type }}</span>
                            </div>
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Lignes</span>
                                <span class="fw-semibold">{{ form.items.length }}</span>
                            </div>
                            <hr>
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">Total HT</span>
                                <span class="fw-semibold">{{ formatCurrency(totalHt) }} CFA</span>
                            </div>
                            <div class="mb-2 d-flex justify-content-between">
                                <span class="text-muted">TVA</span>
                                <span class="fw-semibold">{{ formatCurrency(totalTva) }} CFA</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">Total TTC</span>
                                <span class="fw-bold text-primary fs-5">{{ formatCurrency(totalTtc) }} CFA</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- ONGLET: STATISTIQUES                                         -->
        <!-- ============================================================ -->
        <div v-if="activeTab === 'stats'">
            <div v-if="statsLoading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
            </div>

            <div v-else-if="statsError" class="alert alert-danger rounded-3">
                <i class="bi-exclamation-triangle me-2"></i>{{ statsError }}
            </div>

            <template v-else-if="statsData">
                <!-- KPI Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 rounded-4 shadow-sm h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stat-icon" style="background: #e8eaf6; color: #1a237e;">
                                        <i class="bi-receipt"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold">{{ statsData.total_count }}</h3>
                                        <span class="text-muted small">Factures</span>
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
                                        <i class="bi-check-circle"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold">{{ formatCurrency(statsData.total_paid) }}</h3>
                                        <span class="text-muted small">Paye</span>
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
                                        <i class="bi-clock-history"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold">{{ formatCurrency(totalDue) }}</h3>
                                        <span class="text-muted small">Impaye</span>
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
                                        <i class="bi-currency-dollar"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold">{{ formatCurrency(statsData.total_ttc) }}</h3>
                                        <span class="text-muted small">Chiffre d'affaires</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Par statut -->
                    <div class="col-md-6">
                        <div class="card border-0 rounded-4 shadow-sm">
                            <div class="card-header bg-white border-0 pt-4 px-4">
                                <h6 class="fw-bold mb-0"><i class="bi-pie-chart me-2 text-primary"></i>Par statut</h6>
                            </div>
                            <div class="card-body p-4">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Statut</th>
                                            <th class="text-end">Nombre</th>
                                            <th class="text-end">Montant</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="s in statsData.by_status" :key="s.status">
                                            <td>
                                                <span class="badge rounded-pill"
                                                      :class="'bg-' + (statusColors[s.status] || 'secondary') + ' bg-opacity-10 text-' + (statusColors[s.status] || 'secondary')">
                                                    {{ statusLabels[s.status] || s.status }}
                                                </span>
                                            </td>
                                            <td class="text-end">{{ s.count }}</td>
                                            <td class="text-end fw-semibold">{{ formatCurrency(s.total) }} CFA</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Par type -->
                    <div class="col-md-6">
                        <div class="card border-0 rounded-4 shadow-sm">
                            <div class="card-header bg-white border-0 pt-4 px-4">
                                <h6 class="fw-bold mb-0"><i class="bi-bar-chart me-2 text-primary"></i>Par type</h6>
                            </div>
                            <div class="card-body p-4">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th class="text-end">Nombre</th>
                                            <th class="text-end">Montant</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="s in statsData.by_type" :key="s.type">
                                            <td>
                                                <span class="badge bg-light text-dark rounded-pill">
                                                    {{ typeLabels[s.type] || s.type }}
                                                </span>
                                            </td>
                                            <td class="text-end">{{ s.count }}</td>
                                            <td class="text-end fw-semibold">{{ formatCurrency(s.total) }} CFA</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Mensuel -->
                    <div class="col-12">
                        <div class="card border-0 rounded-4 shadow-sm">
                            <div class="card-header bg-white border-0 pt-4 px-4">
                                <h6 class="fw-bold mb-0"><i class="bi-graph-up me-2 text-primary"></i>Evolution mensuelle</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Mois</th>
                                                <th class="text-end">Factures</th>
                                                <th class="text-end">Total TTC</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="m in statsData.monthly" :key="m.month">
                                                <td>{{ m.month }}</td>
                                                <td class="text-end">{{ m.count }}</td>
                                                <td class="text-end fw-semibold">{{ formatCurrency(m.total) }} CFA</td>
                                            </tr>
                                            <tr v-if="!statsData.monthly?.length">
                                                <td colspan="3" class="text-center text-muted py-3">Aucune donnee</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recentes -->
                    <div class="col-12">
                        <div class="card border-0 rounded-4 shadow-sm">
                            <div class="card-header bg-white border-0 pt-4 px-4">
                                <h6 class="fw-bold mb-0"><i class="bi-clock me-2 text-primary"></i>Dernieres factures</h6>
                            </div>
                            <div class="card-body p-4">
                                <div v-if="!statsData.recent?.length" class="text-center text-muted py-3">
                                    Aucune facture.
                                </div>
                                <div v-else class="list-group list-group-flush">
                                    <div v-for="inv in statsData.recent" :key="inv.id"
                                         class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ inv.number }}</strong>
                                            <span class="text-muted ms-2 small">{{ inv.recipient_name }}</span>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-semibold">{{ formatCurrency(inv.total_ttc) }} CFA</div>
                                            <span class="badge rounded-pill small"
                                                  :class="'bg-' + (statusColors[inv.status] || 'secondary') + ' bg-opacity-10 text-' + (statusColors[inv.status] || 'secondary')">
                                                {{ statusLabels[inv.status] || inv.status }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- ============================================================ -->
        <!-- MODAL: DETAIL FACTURE                                        -->
        <!-- ============================================================ -->
        <div v-if="showDetailModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content border-0 rounded-4">
                    <div v-if="detailLoading" class="modal-body text-center py-5">
                        <div class="spinner-border text-primary"></div>
                    </div>
                    <template v-else-if="detailInvoice">
                        <div class="modal-header border-0 px-4 pt-4">
                            <div>
                                <h5 class="fw-bold mb-1 font-heading">{{ detailInvoice.number }}</h5>
                                <span class="badge rounded-pill me-2"
                                      :class="'bg-' + (statusColors[detailInvoice.status] || 'secondary')">
                                    {{ statusLabels[detailInvoice.status] || detailInvoice.status }}
                                </span>
                                <span class="badge bg-light text-dark rounded-pill">
                                    {{ typeLabels[detailInvoice.type] || detailInvoice.type }}
                                </span>
                            </div>
                            <button type="button" class="btn-close" @click="closeDetail"></button>
                        </div>
                        <div class="modal-body p-4">
                            <!-- Infos -->
                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <small class="text-muted">Destinataire</small>
                                    <p class="fw-semibold mb-0">{{ detailInvoice.recipient_name }}</p>
                                    <small v-if="detailInvoice.recipient_address" class="text-muted"
                                           style="white-space: pre-line;">{{ detailInvoice.recipient_address }}</small>
                                </div>
                                <div class="col-3">
                                    <small class="text-muted">Date d'emission</small>
                                    <p class="fw-semibold mb-0">{{ formatDate(detailInvoice.issue_date) }}</p>
                                    <small v-if="detailInvoice.due_date" class="text-muted">
                                        Echeance: {{ formatDate(detailInvoice.due_date) }}
                                    </small>
                                </div>
                                <div class="col-3 text-end">
                                    <small class="text-muted">Cree par</small>
                                    <p class="fw-semibold mb-0">{{ detailInvoice.created_by?.name || 'N/A' }}</p>
                                </div>
                            </div>

                            <!-- Lignes -->
                            <h6 class="fw-bold mb-3">Lignes</h6>
                            <div class="table-responsive mb-4">
                                <table class="table table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Description</th>
                                            <th class="text-center">Qte</th>
                                            <th class="text-end">Prix unit.</th>
                                            <th class="text-center">TVA</th>
                                            <th class="text-end">Total HT</th>
                                            <th class="text-end">Total TTC</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in detailInvoice.items" :key="item.id">
                                            <td>{{ item.description }}</td>
                                            <td class="text-center">{{ item.quantity }}</td>
                                            <td class="text-end">{{ formatCurrency(item.unit_price) }}</td>
                                            <td class="text-center">{{ item.tax_rate }}%</td>
                                            <td class="text-end">{{ formatCurrency(item.total_ht) }}</td>
                                            <td class="text-end">{{ formatCurrency(item.total_ttc) }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="table-light fw-bold">
                                        <tr>
                                            <td colspan="4" class="text-end">Total HT</td>
                                            <td colspan="2" class="text-end">{{ formatCurrency(detailInvoice.total_ht) }} CFA</td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-end">TVA</td>
                                            <td colspan="2" class="text-end">{{ formatCurrency(detailInvoice.total_tva) }} CFA</td>
                                        </tr>
                                        <tr class="table-primary">
                                            <td colspan="4" class="text-end">Total TTC</td>
                                            <td colspan="2" class="text-end fs-5">{{ formatCurrency(detailInvoice.total_ttc) }} CFA</td>
                                        </tr>
                                        <tr v-if="detailInvoice.paid_amount > 0">
                                            <td colspan="4" class="text-end text-success">Deja paye</td>
                                            <td colspan="2" class="text-end text-success">{{ formatCurrency(detailInvoice.paid_amount) }} CFA</td>
                                        </tr>
                                        <tr v-if="detailInvoice.balance > 0">
                                            <td colspan="4" class="text-end text-danger">Reste du</td>
                                            <td colspan="2" class="text-end text-danger">{{ formatCurrency(detailInvoice.balance) }} CFA</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Paiements -->
                            <h6 class="fw-bold mb-3">
                                <i class="bi-credit-card me-2 text-primary"></i>Paiements
                            </h6>
                            <div v-if="!detailInvoice.payments?.length" class="text-muted small mb-3">
                                Aucun paiement enregistre.
                            </div>
                            <div v-else class="table-responsive mb-3">
                                <table class="table table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Montant</th>
                                            <th>Methode</th>
                                            <th>Reference</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="p in detailInvoice.payments" :key="p.id">
                                            <td>{{ formatDate(p.date) }}</td>
                                            <td class="fw-semibold">{{ formatCurrency(p.amount) }} CFA</td>
                                            <td>{{ methodLabels[p.method] || p.method }}</td>
                                            <td class="text-muted">{{ p.reference || '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Formulaire de paiement (si impayee ou emise) -->
                            <div v-if="detailInvoice.status !== 'payee' && detailInvoice.status !== 'annulee' && detailInvoice.balance > 0"
                                 class="bg-light rounded-3 p-3 mb-3">
                                <h6 class="fw-bold mb-3">
                                    <i class="bi-plus-circle me-2 text-success"></i>Enregistrer un paiement
                                </h6>
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label small">Date</label>
                                        <input type="date" class="form-control form-control-sm" v-model="paymentForm.date">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Montant</label>
                                        <input type="number" class="form-control form-control-sm"
                                               v-model="paymentForm.amount" step="0.01" min="0.01"
                                               :max="detailInvoice.balance">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Methode</label>
                                        <select class="form-select form-select-sm" v-model="paymentForm.method">
                                            <option value="cash">Especes</option>
                                            <option value="transfer">Virement</option>
                                            <option value="momo">Mobile Money</option>
                                            <option value="cheque">Cheque</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Reference</label>
                                        <input type="text" class="form-control form-control-sm" v-model="paymentForm.reference">
                                    </div>
                                    <div class="col-md-1 d-grid">
                                        <button class="btn btn-sm btn-success" @click="submitPayment"
                                                :disabled="paymentLoading">
                                            <span v-if="paymentLoading" class="spinner-border spinner-border-sm"></span>
                                            <i v-else class="bi-check-lg"></i>
                                        </button>
                                    </div>
                                </div>
                                <div v-if="paymentError" class="text-danger small mt-2">
                                    <i class="bi-exclamation-triangle me-1"></i>{{ paymentError }}
                                </div>
                            </div>

                            <!-- Notes -->
                            <div v-if="detailInvoice.notes" class="mb-3">
                                <small class="text-muted fw-semibold">Notes:</small>
                                <p class="mb-0" style="white-space: pre-line;">{{ detailInvoice.notes }}</p>
                            </div>
                        </div>

                        <!-- Actions footer -->
                        <div class="modal-footer border-0 px-4 pb-4">
                            <button class="btn btn-outline-secondary rounded-pill" @click="closeDetail">
                                <i class="bi-x me-1"></i>Fermer
                            </button>
                            <button class="btn btn-outline-primary rounded-pill" @click="printInvoice">
                                <i class="bi-printer me-1"></i>Imprimer
                            </button>

                            <template v-if="detailInvoice.status === 'brouillon'">
                                <button class="btn btn-primary rounded-pill" @click="updateStatus(detailInvoice.id, 'emise')">
                                    <i class="bi-send me-1"></i>Emettre
                                </button>
                                <button class="btn btn-outline-danger rounded-pill" @click="updateStatus(detailInvoice.id, 'annulee')">
                                    <i class="bi-x-circle me-1"></i>Annuler
                                </button>
                            </template>
                            <template v-if="detailInvoice.status === 'emise' || detailInvoice.status === 'impayee'">
                                <button class="btn btn-outline-danger rounded-pill" @click="updateStatus(detailInvoice.id, 'annulee')">
                                    <i class="bi-x-circle me-1"></i>Annuler
                                </button>
                            </template>
                            <button v-if="detailInvoice.status === 'brouillon'"
                                    class="btn btn-outline-danger rounded-pill"
                                    @click="confirmDelete(detailInvoice.id)">
                                <i class="bi-trash me-1"></i>Supprimer
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </CompanyLayout>
</template>
