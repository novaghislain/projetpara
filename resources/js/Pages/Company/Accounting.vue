<script setup>
import { ref, computed, onMounted } from 'vue';
import { authStore } from '../../stores/auth';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';

const props = defineProps({
    pageTitle: { type: String, default: 'Comptabilite' }
});

// ─── State ───────────────────────────────────────────────────────
const activeTab = ref('plan');
const loading = ref(false);
const error = ref(null);

// Data
const accounts = ref([]);
const journals = ref([]);
const currentJournal = ref(null);
const balanceData = ref(null);
const grandLivreData = ref([]);
const bilanData = ref(null);
const resultatData = ref(null);
const stats = ref(null);

// Account form
const showAccountModal = ref(false);
const editingAccount = ref(null);
const accountForm = ref({ code: '', name: '', type: 'actif', is_active: true });

// Journal form
const showJournalModal = ref(false);
const showJournalDetail = ref(false);
const journalForm = ref({
    journal_type: 'od',
    entry_date: new Date().toISOString().split('T')[0],
    reference: '',
    description: '',
    lines: []
});

// Delete confirm
const showDeleteConfirm = ref(false);
const deleteTarget = ref(null);

// ─── Computed ────────────────────────────────────────────────────

const accountTypes = [
    { value: 'actif', label: 'Actif' },
    { value: 'passif', label: 'Passif' },
    { value: 'charge', label: 'Charge' },
    { value: 'produit', label: 'Produit' },
    { value: 'tresorerie', label: 'Trésorerie' },
];

const journalTypes = [
    { value: 'recette', label: 'Recette' },
    { value: 'depense', label: 'Dépense' },
    { value: 'banque', label: 'Banque' },
    { value: 'od', label: 'Opération diverse' },
    { value: 'achat', label: 'Achat' },
    { value: 'vente', label: 'Vente' },
];

const totalDebit = computed(() => {
    return journalForm.value.lines.reduce((s, l) => s + (parseFloat(l.debit) || 0), 0);
});

const totalCredit = computed(() => {
    return journalForm.value.lines.reduce((s, l) => s + (parseFloat(l.credit) || 0), 0);
});

const isBalanced = computed(() => {
    return Math.abs(totalDebit.value - totalCredit.value) < 0.01;
});

const balanceDiff = computed(() => {
    return (totalDebit.value - totalCredit.value).toFixed(2);
});

const activeAccounts = computed(() => {
    return accounts.value.filter(a => a.is_active);
});

// ─── API Helpers ──────────────────────────────────────────────────

const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
const apiHeaders = { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' };
const apiBase = '/api/company/accounting';

async function apiGet(url) {
    const res = await fetch(url, { headers: apiHeaders });
    if (!res.ok) throw new Error(`API error: ${res.status}`);
    return res.json();
}

async function apiPost(url, data) {
    const res = await fetch(url, {
        method: 'POST',
        headers: { ...apiHeaders, 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
    });
    if (!res.ok) {
        const err = await res.json();
        throw new Error(err.message || `Erreur ${res.status}`);
    }
    return res.json();
}

async function apiPut(url, data) {
    const res = await fetch(url, {
        method: 'PUT',
        headers: { ...apiHeaders, 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
    });
    if (!res.ok) {
        const err = await res.json();
        throw new Error(err.message || `Erreur ${res.status}`);
    }
    return res.json();
}

async function apiDelete(url) {
    const res = await fetch(url, { method: 'DELETE', headers: apiHeaders });
    if (!res.ok) {
        const err = await res.json();
        throw new Error(err.message || `Erreur ${res.status}`);
    }
    return res.json();
}

// ─── Data Loading ─────────────────────────────────────────────────

async function loadAccounts() {
    try {
        accounts.value = await apiGet(`${apiBase}/accounts`);
    } catch (e) {
        error.value = 'Erreur chargement des comptes: ' + e.message;
    }
}

async function loadJournals() {
    try {
        journals.value = await apiGet(`${apiBase}/journals`);
    } catch (e) {
        error.value = 'Erreur chargement des journaux: ' + e.message;
    }
}

async function loadBalance() {
    loading.value = true;
    try {
        balanceData.value = await apiGet(`${apiBase}/reports/balance`);
    } catch (e) {
        error.value = 'Erreur chargement de la balance: ' + e.message;
    } finally {
        loading.value = false;
    }
}

async function loadGrandLivre() {
    loading.value = true;
    try {
        grandLivreData.value = await apiGet(`${apiBase}/reports/grand-livre`);
    } catch (e) {
        error.value = 'Erreur chargement du grand livre: ' + e.message;
    } finally {
        loading.value = false;
    }
}

async function loadBilan() {
    loading.value = true;
    try {
        bilanData.value = await apiGet(`${apiBase}/reports/bilan`);
    } catch (e) {
        error.value = 'Erreur chargement du bilan: ' + e.message;
    } finally {
        loading.value = false;
    }
}

async function loadResultat() {
    loading.value = true;
    try {
        resultatData.value = await apiGet(`${apiBase}/reports/resultat`);
    } catch (e) {
        error.value = 'Erreur chargement du resultat: ' + e.message;
    } finally {
        loading.value = false;
    }
}

async function loadStats() {
    try {
        stats.value = await apiGet(`${apiBase}/stats`);
    } catch (e) {
        console.error('Stats error:', e);
    }
}

// ─── Tab switching ───────────────────────────────────────────────

function switchTab(tab) {
    activeTab.value = tab;
    error.value = null;

    switch (tab) {
        case 'plan':
            loadAccounts();
            break;
        case 'journals':
            loadJournals();
            break;
        case 'balance':
            loadBalance();
            break;
        case 'grand-livre':
            loadGrandLivre();
            break;
        case 'bilan':
            loadBilan();
            break;
        case 'resultat':
            loadResultat();
            break;
    }
}

// ─── Account Actions ─────────────────────────────────────────────

function openNewAccount() {
    editingAccount.value = null;
    accountForm.value = { code: '', name: '', type: 'actif', is_active: true };
    showAccountModal.value = true;
}

function openEditAccount(account) {
    editingAccount.value = account;
    accountForm.value = {
        code: account.code,
        name: account.name,
        type: account.type,
        is_active: account.is_active,
    };
    showAccountModal.value = true;
}

async function saveAccount() {
    try {
        if (editingAccount.value) {
            await apiPut(`${apiBase}/accounts/${editingAccount.value.id}`, accountForm.value);
        } else {
            await apiPost(`${apiBase}/accounts`, accountForm.value);
        }
        showAccountModal.value = false;
        await loadAccounts();
    } catch (e) {
        alert(e.message);
    }
}

function confirmDeleteAccount(account) {
    deleteTarget.value = { type: 'account', id: account.id, label: `${account.code} - ${account.name}` };
    showDeleteConfirm.value = true;
}

// ─── Journal Actions ─────────────────────────────────────────────

function openNewJournal() {
    journalForm.value = {
        journal_type: 'od',
        entry_date: new Date().toISOString().split('T')[0],
        reference: '',
        description: '',
        lines: [
            { account_id: '', label: '', debit: '', credit: '' },
            { account_id: '', label: '', debit: '', credit: '' },
        ]
    };
    showJournalModal.value = true;
}

function addJournalLine() {
    journalForm.value.lines.push({ account_id: '', label: '', debit: '', credit: '' });
}

function removeJournalLine(index) {
    if (journalForm.value.lines.length <= 2) return;
    journalForm.value.lines.splice(index, 1);
}

async function saveJournal() {
    try {
        await apiPost(`${apiBase}/journals`, journalForm.value);
        showJournalModal.value = false;
        await loadJournals();
    } catch (e) {
        alert(e.message);
    }
}

async function viewJournal(journal) {
    try {
        currentJournal.value = await apiGet(`${apiBase}/journals/${journal.id}`);
        showJournalDetail.value = true;
    } catch (e) {
        alert(e.message);
    }
}

async function postJournal(id) {
    if (!confirm('Valider cette écriture ? Elle ne pourra plus être modifiée.')) return;
    try {
        await apiPost(`${apiBase}/journals/${id}/post`, {});
        await loadJournals();
    } catch (e) {
        alert(e.message);
    }
}

function confirmDeleteJournal(journal) {
    deleteTarget.value = { type: 'journal', id: journal.id, label: `${journal.reference || 'Sans ref'} - ${journal.description}` };
    showDeleteConfirm.value = true;
}

// ─── Delete handler ──────────────────────────────────────────────

async function executeDelete() {
    try {
        if (deleteTarget.value.type === 'account') {
            await apiDelete(`${apiBase}/accounts/${deleteTarget.value.id}`);
            await loadAccounts();
        } else if (deleteTarget.value.type === 'journal') {
            await apiDelete(`${apiBase}/journals/${deleteTarget.value.id}`);
            await loadJournals();
        }
        showDeleteConfirm.value = false;
        deleteTarget.value = null;
    } catch (e) {
        alert(e.message);
    }
}

// ─── Format helpers ──────────────────────────────────────────────

function formatCurrency(value) {
    if (value === null || value === undefined || isNaN(value)) return '0,00';
    return Number(value).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    return d.toLocaleDateString('fr-FR');
}

function getAccountTypeLabel(type) {
    const labels = { actif: 'Actif', passif: 'Passif', charge: 'Charge', produit: 'Produit', tresorerie: 'Trésorerie' };
    return labels[type] || type;
}

function getAccountTypeBadge(type) {
    const classes = {
        actif: 'bg-primary bg-opacity-10 text-primary',
        passif: 'bg-success bg-opacity-10 text-success',
        charge: 'bg-danger bg-opacity-10 text-danger',
        produit: 'bg-warning bg-opacity-10 text-warning',
        tresorerie: 'bg-info bg-opacity-10 text-info',
    };
    return classes[type] || 'bg-secondary bg-opacity-10 text-secondary';
}

function getJournalTypeBadge(type) {
    const classes = {
        recette: 'bg-success bg-opacity-10 text-success',
        depense: 'bg-danger bg-opacity-10 text-danger',
        banque: 'bg-info bg-opacity-10 text-info',
        od: 'bg-secondary bg-opacity-10 text-secondary',
        achat: 'bg-warning bg-opacity-10 text-warning',
        vente: 'bg-primary bg-opacity-10 text-primary',
    };
    return classes[type] || 'bg-secondary bg-opacity-10 text-secondary';
}

function getStatusBadge(status) {
    if (status === 'posted') return 'bg-success bg-opacity-10 text-success';
    return 'bg-warning bg-opacity-10 text-warning';
}

function getStatusLabel(status) {
    return status === 'posted' ? 'Validée' : 'Brouillon';
}

// ─── Init ────────────────────────────────────────────────────────

onMounted(() => {
    loadStats();
    loadAccounts();
});
</script>

<template>
    <CompanyLayout :page-title="pageTitle">
        <!-- En-tete avec stats -->
        <div v-if="stats" class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="card border-0 rounded-3 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="text-muted small">Comptes actifs</div>
                        <div class="fw-bold fs-4">{{ stats.active_accounts }}<small class="fs-6 text-muted">/{{ stats.total_accounts }}</small></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 rounded-3 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="text-muted small">Ecritures</div>
                        <div class="fw-bold fs-4">{{ stats.total_journals }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 rounded-3 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="text-muted small">Validees</div>
                        <div class="fw-bold fs-4 text-success">{{ stats.posted_journals }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 rounded-3 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="text-muted small">Brouillons</div>
                        <div class="fw-bold fs-4 text-warning">{{ stats.draft_journals }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert erreur -->
        <div v-if="error" class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
            <i class="bi-exclamation-triangle me-2"></i>{{ error }}
            <button type="button" class="btn-close" @click="error = null"></button>
        </div>

        <!-- Onglets -->
        <div class="card border-0 rounded-3 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 px-3">
                <ul class="nav nav-tabs card-header-tabs gap-1">
                    <li class="nav-item">
                        <button class="nav-link" :class="{ active: activeTab === 'plan' }" @click="switchTab('plan')">
                            <i class="bi-journal-text me-1"></i>Plan comptable
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" :class="{ active: activeTab === 'journals' }" @click="switchTab('journals')">
                            <i class="bi-journal me-1"></i>Journaux
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" :class="{ active: activeTab === 'balance' }" @click="switchTab('balance')">
                            <i class="bi-scale me-1"></i>Balance
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" :class="{ active: activeTab === 'grand-livre' }" @click="switchTab('grand-livre')">
                            <i class="bi-book me-1"></i>Grand livre
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" :class="{ active: activeTab === 'bilan' }" @click="switchTab('bilan')">
                            <i class="bi-file-earmark-bar-graph me-1"></i>Bilan
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" :class="{ active: activeTab === 'resultat' }" @click="switchTab('resultat')">
                            <i class="bi-graph-up-arrow me-1"></i>Resultat
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body p-4">

                <!-- ════════════════════════════════════════════════ -->
                <!-- PLAN COMPTABLE                                   -->
                <!-- ════════════════════════════════════════════════ -->
                <div v-if="activeTab === 'plan'">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0"><i class="bi-journal-text me-2 text-primary"></i>Plan comptable</h6>
                        <button class="btn btn-primary btn-sm" @click="openNewAccount">
                            <i class="bi-plus-lg me-1"></i>Nouveau compte
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th>Code</th>
                                    <th>Nom</th>
                                    <th>Type</th>
                                    <th class="text-center">Statut</th>
                                    <th class="text-end">Ecritures</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="account in accounts" :key="account.id">
                                    <td class="fw-medium font-monospace">{{ account.code }}</td>
                                    <td>{{ account.name }}</td>
                                    <td>
                                        <span class="badge rounded-pill small" :class="getAccountTypeBadge(account.type)">
                                            {{ getAccountTypeLabel(account.type) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill small" :class="account.is_active ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary'">
                                            {{ account.is_active ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </td>
                                    <td class="text-end text-muted small">{{ account.journal_lines_count || 0 }}</td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary btn-sm" @click="openEditAccount(account)" title="Modifier">
                                                <i class="bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-outline-danger btn-sm" @click="confirmDeleteAccount(account)" title="Supprimer" :disabled="(account.journal_lines_count || 0) > 0">
                                                <i class="bi-trash3"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!accounts.length">
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi-inbox d-block mb-2" style="font-size: 2rem;"></i>
                                        Aucun compte comptable. <a href="#" @click.prevent="openNewAccount">Creer le premier compte</a>.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ════════════════════════════════════════════════ -->
                <!-- JOURNAUX                                        -->
                <!-- ════════════════════════════════════════════════ -->
                <div v-if="activeTab === 'journals'">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0"><i class="bi-journal me-2 text-primary"></i>Journaux / Ecritures</h6>
                        <button class="btn btn-primary btn-sm" @click="openNewJournal">
                            <i class="bi-plus-lg me-1"></i>Nouvelle ecriture
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Reference</th>
                                    <th>Description</th>
                                    <th class="text-end">Debit</th>
                                    <th class="text-end">Credit</th>
                                    <th>Statut</th>
                                    <th>Creé par</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="journal in journals" :key="journal.id">
                                    <td class="small">{{ formatDate(journal.entry_date) }}</td>
                                    <td>
                                        <span class="badge rounded-pill small" :class="getJournalTypeBadge(journal.journal_type)">
                                            {{ journal.journal_type }}
                                        </span>
                                    </td>
                                    <td class="font-monospace small">{{ journal.reference || '—' }}</td>
                                    <td class="small text-truncate" style="max-width: 200px;">{{ journal.description }}</td>
                                    <td class="text-end small">{{ formatCurrency(journal.debit_total) }}</td>
                                    <td class="text-end small">{{ formatCurrency(journal.credit_total) }}</td>
                                    <td>
                                        <span class="badge rounded-pill small" :class="getStatusBadge(journal.status)">
                                            {{ getStatusLabel(journal.status) }}
                                        </span>
                                    </td>
                                    <td class="small text-muted">{{ journal.created_by?.name || '—' }}</td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-info btn-sm" @click="viewJournal(journal)" title="Voir">
                                                <i class="bi-eye"></i>
                                            </button>
                                            <button v-if="journal.status === 'draft'" class="btn btn-outline-success btn-sm" @click="postJournal(journal.id)" title="Valider">
                                                <i class="bi-check-lg"></i>
                                            </button>
                                            <button v-if="journal.status === 'draft'" class="btn btn-outline-danger btn-sm" @click="confirmDeleteJournal(journal)" title="Supprimer">
                                                <i class="bi-trash3"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!journals.length">
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="bi-journal-plus d-block mb-2" style="font-size: 2rem;"></i>
                                        Aucune ecriture comptable. <a href="#" @click.prevent="openNewJournal">Creer la premiere ecriture</a>.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ════════════════════════════════════════════════ -->
                <!-- BALANCE                                         -->
                <!-- ════════════════════════════════════════════════ -->
                <div v-if="activeTab === 'balance'">
                    <div v-if="loading" class="text-center py-5">
                        <div class="spinner-border text-primary"></div>
                    </div>
                    <div v-else-if="balanceData">
                        <h6 class="fw-bold mb-3"><i class="bi-scale me-2 text-primary"></i>Balance des comptes</h6>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light small">
                                    <tr>
                                        <th>Compte</th>
                                        <th>Nom</th>
                                        <th>Type</th>
                                        <th class="text-end">Debit</th>
                                        <th class="text-end">Credit</th>
                                        <th class="text-end">Solde</th>
                                        <th>Sens</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="acc in balanceData.accounts" :key="acc.id">
                                        <td class="font-monospace small">{{ acc.code }}</td>
                                        <td class="small">{{ acc.name }}</td>
                                        <td>
                                            <span class="badge rounded-pill small" :class="getAccountTypeBadge(acc.type)">
                                                {{ getAccountTypeLabel(acc.type) }}
                                            </span>
                                        </td>
                                        <td class="text-end small">{{ formatCurrency(acc.debit) }}</td>
                                        <td class="text-end small">{{ formatCurrency(acc.credit) }}</td>
                                        <td class="text-end small fw-medium">{{ formatCurrency(acc.balance) }}</td>
                                        <td>
                                            <span class="badge rounded-pill small" :class="acc.sens === 'Debiteur' ? 'bg-primary bg-opacity-10 text-primary' : 'bg-success bg-opacity-10 text-success'">
                                                {{ acc.sens }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="3" class="text-end small">Totaux</th>
                                        <th class="text-end small">{{ formatCurrency(balanceData.totals.debit) }}</th>
                                        <th class="text-end small">{{ formatCurrency(balanceData.totals.credit) }}</th>
                                        <th colspan="2"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div v-if="!balanceData.accounts.length" class="text-center text-muted py-4">
                            <i class="bi-inbox d-block mb-2" style="font-size: 2rem;"></i>
                            Aucune donnee de balance. Saisissez et validez des ecritures.
                        </div>
                    </div>
                </div>

                <!-- ════════════════════════════════════════════════ -->
                <!-- GRAND LIVRE                                     -->
                <!-- ════════════════════════════════════════════════ -->
                <div v-if="activeTab === 'grand-livre'">
                    <div v-if="loading" class="text-center py-5">
                        <div class="spinner-border text-primary"></div>
                    </div>
                    <div v-else-if="grandLivreData.length">
                        <h6 class="fw-bold mb-3"><i class="bi-book me-2 text-primary"></i>Grand livre</h6>
                        <div v-for="item in grandLivreData" :key="item.account.id" class="mb-4">
                            <div class="card bg-light border-0 rounded-3">
                                <div class="card-body p-3">
                                    <div class="fw-bold small mb-2">
                                        <span class="font-monospace">{{ item.account.code }}</span> — {{ item.account.name }}
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0">
                                            <thead class="small text-muted">
                                                <tr>
                                                    <th style="width: 100px;">Date</th>
                                                    <th style="width: 100px;">Ref</th>
                                                    <th>Libelle</th>
                                                    <th class="text-end" style="width: 120px;">Debit</th>
                                                    <th class="text-end" style="width: 120px;">Credit</th>
                                                    <th class="text-end" style="width: 120px;">Solde</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(line, li) in item.lines" :key="li" class="small">
                                                    <td>{{ line.date }}</td>
                                                    <td class="font-monospace">{{ line.reference || '—' }}</td>
                                                    <td>{{ line.label }}</td>
                                                    <td class="text-end">{{ line.debit ? formatCurrency(line.debit) : '—' }}</td>
                                                    <td class="text-end">{{ line.credit ? formatCurrency(line.credit) : '—' }}</td>
                                                    <td class="text-end fw-medium">{{ formatCurrency(line.balance) }}</td>
                                                </tr>
                                            </tbody>
                                            <tfoot class="table-light small">
                                                <tr>
                                                    <th colspan="3" class="text-end">Totaux</th>
                                                    <th class="text-end">{{ formatCurrency(item.total_debit) }}</th>
                                                    <th class="text-end">{{ formatCurrency(item.total_credit) }}</th>
                                                    <th class="text-end">{{ formatCurrency(item.balance) }}</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center text-muted py-4">
                        <i class="bi-inbox d-block mb-2" style="font-size: 2rem;"></i>
                        Aucune donnee. Saisissez et validez des ecritures.
                    </div>
                </div>

                <!-- ════════════════════════════════════════════════ -->
                <!-- BILAN                                           -->
                <!-- ════════════════════════════════════════════════ -->
                <div v-if="activeTab === 'bilan'">
                    <div v-if="loading" class="text-center py-5">
                        <div class="spinner-border text-primary"></div>
                    </div>
                    <div v-else-if="bilanData">
                        <h6 class="fw-bold mb-3"><i class="bi-file-earmark-bar-graph me-2 text-primary"></i>Bilan comptable</h6>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="card border-0 bg-light rounded-3 h-100">
                                    <div class="card-header bg-transparent border-bottom-0 pt-3 px-3">
                                        <h6 class="fw-bold mb-0 text-primary">Actif</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <table class="table table-sm mb-0">
                                            <thead class="small text-muted">
                                                <tr><th>Compte</th><th>Libelle</th><th class="text-end">Montant</th></tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="item in bilanData.actif" :key="item.code" class="small">
                                                    <td class="font-monospace">{{ item.code }}</td>
                                                    <td>{{ item.name }}</td>
                                                    <td class="text-end fw-medium">{{ formatCurrency(item.balance) }}</td>
                                                </tr>
                                            </tbody>
                                            <tfoot class="table-light small">
                                                <tr>
                                                    <th colspan="2" class="text-end">Total Actif</th>
                                                    <th class="text-end">{{ formatCurrency(bilanData.total_actif) }}</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 bg-light rounded-3 h-100">
                                    <div class="card-header bg-transparent border-bottom-0 pt-3 px-3">
                                        <h6 class="fw-bold mb-0 text-success">Passif</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <table class="table table-sm mb-0">
                                            <thead class="small text-muted">
                                                <tr><th>Compte</th><th>Libelle</th><th class="text-end">Montant</th></tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="item in bilanData.passif" :key="item.code" class="small">
                                                    <td class="font-monospace">{{ item.code }}</td>
                                                    <td>{{ item.name }}</td>
                                                    <td class="text-end fw-medium">{{ formatCurrency(item.balance) }}</td>
                                                </tr>
                                            </tbody>
                                            <tfoot class="table-light small">
                                                <tr>
                                                    <th colspan="2" class="text-end">Total Passif</th>
                                                    <th class="text-end">{{ formatCurrency(bilanData.total_passif) }}</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="!bilanData.actif.length && !bilanData.passif.length" class="text-center text-muted py-4">
                            <i class="bi-inbox d-block mb-2" style="font-size: 2rem;"></i>
                            Aucune donnee de bilan.
                        </div>
                    </div>
                </div>

                <!-- ════════════════════════════════════════════════ -->
                <!-- RESULTAT                                        -->
                <!-- ════════════════════════════════════════════════ -->
                <div v-if="activeTab === 'resultat'">
                    <div v-if="loading" class="text-center py-5">
                        <div class="spinner-border text-primary"></div>
                    </div>
                    <div v-else-if="resultatData">
                        <h6 class="fw-bold mb-3"><i class="bi-graph-up-arrow me-2 text-primary"></i>Compte de resultat</h6>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="card border-0 bg-light rounded-3 h-100">
                                    <div class="card-header bg-transparent border-bottom-0 pt-3 px-3">
                                        <h6 class="fw-bold mb-0 text-danger">Charges</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <table class="table table-sm mb-0">
                                            <thead class="small text-muted">
                                                <tr><th>Compte</th><th>Libelle</th><th class="text-end">Montant</th></tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="item in resultatData.charges" :key="item.code" class="small">
                                                    <td class="font-monospace">{{ item.code }}</td>
                                                    <td>{{ item.name }}</td>
                                                    <td class="text-end fw-medium">{{ formatCurrency(item.montant) }}</td>
                                                </tr>
                                            </tbody>
                                            <tfoot class="table-light small">
                                                <tr>
                                                    <th colspan="2" class="text-end">Total Charges</th>
                                                    <th class="text-end">{{ formatCurrency(resultatData.total_charges) }}</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 bg-light rounded-3 h-100">
                                    <div class="card-header bg-transparent border-bottom-0 pt-3 px-3">
                                        <h6 class="fw-bold mb-0 text-success">Produits</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <table class="table table-sm mb-0">
                                            <thead class="small text-muted">
                                                <tr><th>Compte</th><th>Libelle</th><th class="text-end">Montant</th></tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="item in resultatData.produits" :key="item.code" class="small">
                                                    <td class="font-monospace">{{ item.code }}</td>
                                                    <td>{{ item.name }}</td>
                                                    <td class="text-end fw-medium">{{ formatCurrency(item.montant) }}</td>
                                                </tr>
                                            </tbody>
                                            <tfoot class="table-light small">
                                                <tr>
                                                    <th colspan="2" class="text-end">Total Produits</th>
                                                    <th class="text-end">{{ formatCurrency(resultatData.total_produits) }}</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card border-0 rounded-3 mt-3"
                             :class="resultatData.resultat >= 0 ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10'">
                            <div class="card-body text-center py-3">
                                <h6 class="fw-bold mb-0" :class="resultatData.resultat >= 0 ? 'text-success' : 'text-danger'">
                                    Resultat : {{ formatCurrency(resultatData.resultat) }}
                                    <small class="fw-normal">{{ resultatData.resultat >= 0 ? '(benefice)' : '(perte)' }}</small>
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ─── MODAL: Compte (Nouveau / Edit) ─────────────────────── -->
        <div class="modal fade" :class="{ show: showAccountModal }" :style="{ display: showAccountModal ? 'block' : 'none' }" tabindex="-1" @click.self="showAccountModal = false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold">
                            <i class="bi-journal-text me-2"></i>
                            {{ editingAccount ? 'Modifier le compte' : 'Nouveau compte' }}
                        </h6>
                        <button type="button" class="btn-close" @click="showAccountModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Code comptable</label>
                            <input type="text" class="form-control" v-model="accountForm.code" placeholder="Ex: 401000">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Nom du compte</label>
                            <input type="text" class="form-control" v-model="accountForm.name" placeholder="Ex: Fournisseurs">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Type de compte</label>
                            <select class="form-select" v-model="accountForm.type">
                                <option v-for="t in accountTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                            </select>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="accountActive" v-model="accountForm.is_active">
                            <label class="form-check-label small" for="accountActive">Compte actif</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-secondary" @click="showAccountModal = false">Annuler</button>
                        <button class="btn btn-sm btn-primary" @click="saveAccount" :disabled="!accountForm.code || !accountForm.name">
                            <i class="bi-check-lg me-1"></i>{{ editingAccount ? 'Modifier' : 'Creer' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── MODAL: Journal (Nouvelle ecriture) ────────────────── -->
        <div class="modal fade" :class="{ show: showJournalModal }" :style="{ display: showJournalModal ? 'block' : 'none' }" tabindex="-1" @click.self="showJournalModal = false">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold">
                            <i class="bi-journal-plus me-2"></i>Nouvelle ecriture comptable
                        </h6>
                        <button type="button" class="btn-close" @click="showJournalModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-medium">Type</label>
                                <select class="form-select form-select-sm" v-model="journalForm.journal_type">
                                    <option v-for="t in journalTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-medium">Date</label>
                                <input type="date" class="form-control form-control-sm" v-model="journalForm.entry_date">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-medium">Reference</label>
                                <input type="text" class="form-control form-control-sm" v-model="journalForm.reference" placeholder="Facultative">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Description</label>
                            <textarea class="form-control form-control-sm" rows="2" v-model="journalForm.description" placeholder="Description de l'ecriture..."></textarea>
                        </div>

                        <label class="form-label small fw-medium">Lignes d'ecriture</label>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light small">
                                    <tr>
                                        <th style="width: 120px;">Compte</th>
                                        <th>Libelle</th>
                                        <th style="width: 130px;" class="text-end">Debit</th>
                                        <th style="width: 130px;" class="text-end">Credit</th>
                                        <th style="width: 40px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(line, index) in journalForm.lines" :key="index">
                                        <td>
                                            <select class="form-select form-select-sm" v-model="line.account_id">
                                                <option value="">---</option>
                                                <option v-for="a in activeAccounts" :key="a.id" :value="a.id">{{ a.code }} - {{ a.name }}</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm" v-model="line.label" placeholder="Libelle">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end" v-model="line.debit" placeholder="0,00">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end" v-model="line.credit" placeholder="0,00">
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-danger" @click="removeJournalLine(index)" :disabled="journalForm.lines.length <= 2">
                                                <i class="bi-x"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light small">
                                    <tr>
                                        <td colspan="2" class="text-end fw-medium">Totaux</td>
                                        <td class="text-end fw-medium">{{ formatCurrency(totalDebit) }}</td>
                                        <td class="text-end fw-medium">{{ formatCurrency(totalCredit) }}</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" :class="isBalanced ? 'text-success' : 'text-danger'">
                                            <small>
                                                <i :class="isBalanced ? 'bi-check-circle' : 'bi-exclamation-circle'"></i>
                                                {{ isBalanced ? 'Ecriture equilibree' : `Difference : ${balanceDiff}` }}
                                            </small>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <button class="btn btn-sm btn-outline-primary mt-2" @click="addJournalLine">
                            <i class="bi-plus-lg me-1"></i>Ajouter une ligne
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-secondary" @click="showJournalModal = false">Annuler</button>
                        <button class="btn btn-sm btn-primary" @click="saveJournal" :disabled="!isBalanced || !journalForm.description || !journalForm.lines[0]?.account_id">
                            <i class="bi-check-lg me-1"></i>Enregistrer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── MODAL: Detail Journal ──────────────────────────────── -->
        <div class="modal fade" :class="{ show: showJournalDetail }" :style="{ display: showJournalDetail ? 'block' : 'none' }" tabindex="-1" @click.self="showJournalDetail = false" v-if="currentJournal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold">
                            <i class="bi-journal-text me-2"></i>Detail de l'ecriture
                        </h6>
                        <button type="button" class="btn-close" @click="showJournalDetail = false"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <span class="small text-muted d-block">Date</span>
                                <span class="small fw-medium">{{ formatDate(currentJournal.entry_date) }}</span>
                            </div>
                            <div class="col-md-3">
                                <span class="small text-muted d-block">Type</span>
                                <span class="badge rounded-pill small" :class="getJournalTypeBadge(currentJournal.journal_type)">{{ currentJournal.journal_type }}</span>
                            </div>
                            <div class="col-md-3">
                                <span class="small text-muted d-block">Reference</span>
                                <span class="small fw-medium font-monospace">{{ currentJournal.reference || '—' }}</span>
                            </div>
                            <div class="col-md-3">
                                <span class="small text-muted d-block">Statut</span>
                                <span class="badge rounded-pill small" :class="getStatusBadge(currentJournal.status)">{{ getStatusLabel(currentJournal.status) }}</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <span class="small text-muted d-block">Description</span>
                            <p class="small mb-0">{{ currentJournal.description }}</p>
                        </div>
                        <div v-if="currentJournal.created_by" class="mb-3">
                            <span class="small text-muted d-block">Creé par</span>
                            <span class="small">{{ currentJournal.created_by.name }}</span>
                        </div>

                        <label class="form-label small fw-medium">Lignes d'ecriture</label>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light small">
                                    <tr>
                                        <th>Compte</th>
                                        <th>Libelle</th>
                                        <th class="text-end">Debit</th>
                                        <th class="text-end">Credit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="line in currentJournal.lines" :key="line.id" class="small">
                                        <td>
                                            <span class="font-monospace">{{ line.account?.code }}</span> — {{ line.account?.name }}
                                        </td>
                                        <td>{{ line.label }}</td>
                                        <td class="text-end">{{ line.debit ? formatCurrency(line.debit) : '—' }}</td>
                                        <td class="text-end">{{ line.credit ? formatCurrency(line.credit) : '—' }}</td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light small">
                                    <tr>
                                        <th colspan="2" class="text-end">Totaux</th>
                                        <th class="text-end">{{ formatCurrency(currentJournal.debit_total) }}</th>
                                        <th class="text-end">{{ formatCurrency(currentJournal.credit_total) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-secondary" @click="showJournalDetail = false">Fermer</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── MODAL: Confirmer suppression ────────────────────────── -->
        <div class="modal fade" :class="{ show: showDeleteConfirm }" :style="{ display: showDeleteConfirm ? 'block' : 'none' }" tabindex="-1" @click.self="showDeleteConfirm = false">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold text-danger">Confirmer la suppression</h6>
                        <button type="button" class="btn-close" @click="showDeleteConfirm = false"></button>
                    </div>
                    <div class="modal-body small" v-if="deleteTarget">
                        Etes-vous sur de vouloir supprimer <strong>{{ deleteTarget.label }}</strong> ?
                        <p class="text-muted mt-1 mb-0">Cette action est irreversible.</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-secondary" @click="showDeleteConfirm = false">Annuler</button>
                        <button class="btn btn-sm btn-danger" @click="executeDelete">
                            <i class="bi-trash3 me-1"></i> Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backdrops for modals -->
        <div v-if="showAccountModal || showJournalModal || showJournalDetail || showDeleteConfirm" class="modal-backdrop fade show"></div>
    </CompanyLayout>
</template>

<style scoped>
.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    border-radius: 0.5rem;
    transition: all 0.2s;
}
.nav-tabs .nav-link:hover {
    color: var(--bs-primary);
    background: var(--gel-light);
}
.nav-tabs .nav-link.active {
    color: var(--bs-primary);
    background: var(--gel-light);
    font-weight: 600;
}
.table > :not(caption) > * > * {
    padding: 0.5rem 0.75rem;
    vertical-align: middle;
}
</style>
