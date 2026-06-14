<script setup>
import { ref, computed, onMounted } from 'vue';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';
import { authStore } from '../../stores/auth';

// ─── État général ─────────────────────────────────────────────────
const activeTab = ref('dashboard');
const loading = ref(false);
const error = ref(null);
const successMsg = ref('');

const csrfToken = computed(() => {
    return document.querySelector('meta[name=csrf-token]')?.content || '';
});

const tabs = [
    { key: 'dashboard', label: 'Dashboard', icon: 'bi-speedometer2' },
    { key: 'accounts', label: 'Plan comptable', icon: 'bi-book' },
    { key: 'journals', label: 'Journaux', icon: 'bi-journal-text' },
    { key: 'balance', label: 'Balance', icon: 'bi-table' },
    { key: 'grand-livre', label: 'Grand livre', icon: 'bi-list-columns' },
    { key: 'bilan', label: 'Bilan', icon: 'bi-bar-chart' },
    { key: 'resultat', label: 'Résultat', icon: 'bi-pie-chart' },
    { key: 'tva', label: 'TVA', icon: 'bi-receipt' },
    { key: 'assets', label: 'Immobilisations', icon: 'bi-building' },
    { key: 'fiscal-years', label: 'Exercices', icon: 'bi-calendar3' },
    { key: 'reconciliation', label: 'Réconciliation', icon: 'bi-arrow-left-right' },
];

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

const journalTypeLabel = (type) => {
    const map = {
        achat: 'Achats', vente: 'Ventes', banque: 'Banque', caisse: 'Caisse',
        operations_diverses: 'Op. diverses', od: 'OD', salaire: 'Salaires',
        investissement: 'Investissement', paie: 'Paie', hav: 'Hav', anouveaux: 'A Nouveaux',
    };
    return map[type] || type;
};

const statusLabel = (status) => {
    const map = { draft: 'Brouillon', posted: 'Posté', closed: 'Clôturé', open: 'Ouvert', locked: 'Verrouillé' };
    return map[status] || status;
};

const statusBadge = (status) => {
    const map = { draft: 'bg-warning text-dark', posted: 'bg-success', closed: 'bg-secondary',
        open: 'bg-primary', locked: 'bg-dark', submitted: 'bg-info text-dark', approved: 'bg-success',
        paid: 'bg-primary', matched: 'bg-primary' };
    return map[status] || 'bg-secondary';
};

// ─── 0. DASHBOARD ────────────────────────────────────────────────
const stats = ref(null);
const statsLoading = ref(false);

const loadStats = async () => {
    statsLoading.value = true;
    try {
        const res = await fetch('/api/company/accounting/stats');
        if (res.ok) stats.value = await res.json();
    } catch (e) { console.error('Erreur stats', e); }
    finally { statsLoading.value = false; }
};

// ─── 1. Plan comptable ────────────────────────────────────────────
const accounts = ref([]);
const accountsLoading = ref(false);
const showAccountModal = ref(false);
const editingAccount = ref(null);
const accountForm = ref({ code: '', name: '', type: '1', syscohada_class: '1', is_active: true, has_tva: false, tva_rate: 18 });
const accountSubmitting = ref(false);

const accountTypes = [
    { value: '1', label: 'Capitaux propres (classe 1)' },
    { value: '2', label: 'Immobilisations (classe 2)' },
    { value: '3', label: 'Stocks (classe 3)' },
    { value: '4', label: 'Tiers (classe 4)' },
    { value: '5', label: 'Financier (classe 5)' },
    { value: '6', label: 'Charges (classe 6)' },
    { value: '7', label: 'Produits (classe 7)' },
    { value: '8', label: 'Comptes spéciaux (classe 8)' },
    { value: '9', label: 'Comptes analytiques (classe 9)' },
];

const loadAccounts = async () => {
    accountsLoading.value = true;
    try {
        const res = await fetch('/api/company/accounting/accounts');
        if (!res.ok) throw new Error('Erreur chargement comptes');
        accounts.value = await res.json();
    } catch (e) { error.value = e.message; }
    finally { accountsLoading.value = false; }
};

const openAccountModal = (account = null) => {
    error.value = null;
    if (account) {
        editingAccount.value = account;
        accountForm.value = {
            code: account.code, name: account.name, type: account.type,
            syscohada_class: account.syscohada_class || account.type,
            is_active: account.is_active, has_tva: account.has_tva || false, tva_rate: account.tva_rate || 18,
        };
    } else {
        editingAccount.value = null;
        accountForm.value = { code: '', name: '', type: '1', syscohada_class: '1', is_active: true, has_tva: false, tva_rate: 18 };
    }
    showAccountModal.value = true;
};

const submitAccount = async () => {
    accountSubmitting.value = true; error.value = null;
    try {
        const isEdit = !!editingAccount.value;
        const url = isEdit ? `/api/company/accounting/accounts/${editingAccount.value.id}` : '/api/company/accounting/accounts';
        const res = await fetch(url, {
            method: isEdit ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken.value, Accept: 'application/json' },
            body: JSON.stringify(accountForm.value),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur');
        successMsg.value = data.message;
        showAccountModal.value = false;
        await loadAccounts();
        setTimeout(() => { successMsg.value = ''; }, 3000);
    } catch (e) { error.value = e.message; }
    finally { accountSubmitting.value = false; }
};

const deleteAccount = async (id) => {
    if (!confirm('Supprimer ce compte ?')) return;
    try {
        const res = await fetch(`/api/company/accounting/accounts/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken.value, Accept: 'application/json' },
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur suppression');
        successMsg.value = data.message;
        await loadAccounts();
        setTimeout(() => { successMsg.value = ''; }, 3000);
    } catch (e) { error.value = e.message; }
};

// CSV Import
const importFile = ref(null);
const importError = ref('');
const importSuccess = ref('');

const importAccounts = async () => {
    if (!importFile.value) { importError.value = 'Sélectionnez un fichier CSV.'; return; }
    importError.value = ''; importSuccess.value = '';
    const formData = new FormData();
    formData.append('csv', importFile.value);
    try {
        const res = await fetch('/api/company/accounting/accounts/import', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken.value, Accept: 'application/json' },
            body: formData,
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur');
        importSuccess.value = data.message;
        await loadAccounts();
    } catch (e) { importError.value = e.message; }
};

// ─── 2. Journaux ──────────────────────────────────────────────────
const journals = ref([]);
const journalsLoading = ref(false);
const showJournalModal = ref(false);
const showJournalDetailModal = ref(false);
const selectedJournal = ref(null);
const journalSubmitting = ref(false);
const journalForm = ref({
    journal_type: 'operations_diverses',
    entry_date: new Date().toISOString().split('T')[0],
    reference: '', description: '',
    lines: [
        { account_id: '', label: '', debit: '', credit: '' },
        { account_id: '', label: '', debit: '', credit: '' },
    ],
});

const journalTotalDebit = computed(() => journalForm.value.lines.reduce((sum, l) => sum + (parseFloat(l.debit) || 0), 0));
const journalTotalCredit = computed(() => journalForm.value.lines.reduce((sum, l) => sum + (parseFloat(l.credit) || 0), 0));
const journalIsBalanced = computed(() => Math.abs(journalTotalDebit.value - journalTotalCredit.value) < 0.01);

const addJournalLine = () => journalForm.value.lines.push({ account_id: '', label: '', debit: '', credit: '' });
const removeJournalLine = (index) => { if (journalForm.value.lines.length > 2) journalForm.value.lines.splice(index, 1); };

const loadJournals = async () => {
    journalsLoading.value = true;
    try {
        const res = await fetch('/api/company/accounting/journals');
        if (!res.ok) throw new Error('Erreur chargement journaux');
        journals.value = await res.json();
    } catch (e) { error.value = e.message; }
    finally { journalsLoading.value = false; }
};

const openJournalModal = () => {
    error.value = null;
    journalForm.value = {
        journal_type: 'operations_diverses',
        entry_date: new Date().toISOString().split('T')[0],
        reference: '', description: '',
        lines: [
            { account_id: '', label: '', debit: '', credit: '' },
            { account_id: '', label: '', debit: '', credit: '' },
        ],
    };
    showJournalModal.value = true;
};

const submitJournal = async () => {
    journalSubmitting.value = true; error.value = null;
    try {
        const res = await fetch('/api/company/accounting/journals', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken.value, Accept: 'application/json' },
            body: JSON.stringify(journalForm.value),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur création');
        successMsg.value = data.message;
        showJournalModal.value = false;
        await loadJournals();
        setTimeout(() => { successMsg.value = ''; }, 3000);
    } catch (e) { error.value = e.message; }
    finally { journalSubmitting.value = false; }
};

const loadJournalDetail = async (id) => {
    try {
        const res = await fetch(`/api/company/accounting/journals/${id}`);
        if (!res.ok) throw new Error('Erreur chargement détail');
        const data = await res.json();
        selectedJournal.value = data.journal;
        showJournalDetailModal.value = true;
    } catch (e) { error.value = e.message; }
};

const postJournal = async (id) => {
    if (!confirm('Poster cette écriture ? Elle ne pourra plus être modifiée.')) return;
    try {
        const res = await fetch(`/api/company/accounting/journals/${id}/post`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken.value, Accept: 'application/json' },
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur');
        successMsg.value = data.message;
        await loadJournals();
        setTimeout(() => { successMsg.value = ''; }, 3000);
    } catch (e) { error.value = e.message; }
};

const reverseJournal = async (id) => {
    if (!confirm('Créer une extourne de cette écriture ?')) return;
    try {
        const res = await fetch(`/api/company/accounting/journals/${id}/reverse`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken.value, Accept: 'application/json' },
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur');
        successMsg.value = data.message;
        await loadJournals();
        setTimeout(() => { successMsg.value = ''; }, 3000);
    } catch (e) { error.value = e.message; }
};

const deleteJournal = async (id) => {
    if (!confirm('Supprimer ce brouillon ?')) return;
    try {
        const res = await fetch(`/api/company/accounting/journals/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken.value, Accept: 'application/json' },
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur suppression');
        successMsg.value = data.message;
        await loadJournals();
        setTimeout(() => { successMsg.value = ''; }, 3000);
    } catch (e) { error.value = e.message; }
};

// ─── 3. Balance ───────────────────────────────────────────────────
const balanceData = ref(null);
const balanceLoading = ref(false);
const showBalanceByClass = ref(false);
const fiscalYearFilter = ref('');

const loadBalance = async () => {
    balanceLoading.value = true;
    try {
        const params = new URLSearchParams();
        if (fiscalYearFilter.value) params.append('fiscal_year_id', fiscalYearFilter.value);
        const qs = params.toString();
        const res = await fetch('/api/company/accounting/reports/balance' + (qs ? '?' + qs : ''));
        if (!res.ok) throw new Error('Erreur chargement balance');
        balanceData.value = await res.json();
    } catch (e) { error.value = e.message; }
    finally { balanceLoading.value = false; }
};

// ─── 4. Grand livre ───────────────────────────────────────────────
const grandLivreLines = ref([]);
const grandLivreTotals = ref(null);
const grandLivreAccounts = ref([]);
const grandLivreFilter = ref({ account_id: '', date_from: '', date_to: '', journal_type: '' });
const grandLivreLoading = ref(false);

const loadGrandLivre = async () => {
    grandLivreLoading.value = true;
    try {
        const params = new URLSearchParams();
        if (grandLivreFilter.value.account_id) params.append('account_id', grandLivreFilter.value.account_id);
        if (grandLivreFilter.value.date_from) params.append('date_from', grandLivreFilter.value.date_from);
        if (grandLivreFilter.value.date_to) params.append('date_to', grandLivreFilter.value.date_to);
        if (grandLivreFilter.value.journal_type) params.append('journal_type', grandLivreFilter.value.journal_type);
        const qs = params.toString();
        const url = '/api/company/accounting/reports/grand-livre' + (qs ? '?' + qs : '');
        const res = await fetch(url);
        if (!res.ok) throw new Error('Erreur chargement grand livre');
        const data = await res.json();
        grandLivreLines.value = data.lines;
        grandLivreTotals.value = data.totals;
        grandLivreAccounts.value = data.accounts;
    } catch (e) { error.value = e.message; }
    finally { grandLivreLoading.value = false; }
};

// ─── 5. Bilan ─────────────────────────────────────────────────────
const bilanData = ref(null);
const bilanLoading = ref(false);

const loadBilan = async () => {
    bilanLoading.value = true;
    try {
        const params = new URLSearchParams();
        if (fiscalYearFilter.value) params.append('fiscal_year_id', fiscalYearFilter.value);
        const qs = params.toString();
        const res = await fetch('/api/company/accounting/reports/bilan' + (qs ? '?' + qs : ''));
        if (!res.ok) throw new Error('Erreur chargement bilan');
        bilanData.value = await res.json();
    } catch (e) { error.value = e.message; }
    finally { bilanLoading.value = false; }
};

// ─── 6. Résultat ──────────────────────────────────────────────────
const resultatData = ref(null);
const resultatLoading = ref(false);

const loadResultat = async () => {
    resultatLoading.value = true;
    try {
        const params = new URLSearchParams();
        if (fiscalYearFilter.value) params.append('fiscal_year_id', fiscalYearFilter.value);
        const qs = params.toString();
        const res = await fetch('/api/company/accounting/reports/resultat' + (qs ? '?' + qs : ''));
        if (!res.ok) throw new Error('Erreur chargement résultat');
        resultatData.value = await res.json();
    } catch (e) { error.value = e.message; }
    finally { resultatLoading.value = false; }
};

// ─── 7. TVA ───────────────────────────────────────────────────────
const tvaDeclarations = ref([]);
const tvaLoading = ref(false);
const showTvaModal = ref(false);
const tvaComputed = ref(null);
const tvaPeriod = ref('');
const tvaSubmitting = ref(false);
const tvaForm = ref({ period: '', type: 'monthly', tva_collected: 0, tva_deductible: 0, tva_net: 0, notes: '' });

const loadTvaDeclarations = async () => {
    tvaLoading.value = true;
    try {
        const res = await fetch('/api/company/tva/declarations');
        if (!res.ok) throw new Error('Erreur chargement TVA');
        tvaDeclarations.value = await res.json();
    } catch (e) { error.value = e.message; }
    finally { tvaLoading.value = false; }
};

const computeTva = async () => {
    if (!tvaPeriod.value) return;
    tvaSubmitting.value = true;
    try {
        const res = await fetch('/api/company/tva/compute', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken.value, Accept: 'application/json' },
            body: JSON.stringify({ period: tvaPeriod.value }),
        });
        if (!res.ok) throw new Error('Erreur calcul TVA');
        tvaComputed.value = await res.json();
        tvaForm.value.tva_collected = tvaComputed.value.tva_collected;
        tvaForm.value.tva_deductible = tvaComputed.value.tva_deductible;
        tvaForm.value.tva_net = tvaComputed.value.tva_net;
    } catch (e) { error.value = e.message; }
    finally { tvaSubmitting.value = false; }
};

const openTvaModal = () => {
    error.value = null;
    const now = new Date();
    tvaPeriod.value = now.toISOString().slice(0, 7);
    tvaComputed.value = null;
    tvaForm.value = { period: tvaPeriod.value, type: 'monthly', tva_collected: 0, tva_deductible: 0, tva_net: 0, notes: '' };
    showTvaModal.value = true;
};

const submitTva = async () => {
    tvaSubmitting.value = true; error.value = null;
    try {
        const res = await fetch('/api/company/tva/declarations', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken.value, Accept: 'application/json' },
            body: JSON.stringify({ ...tvaForm.value, period: tvaPeriod.value }),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur');
        successMsg.value = data.message;
        showTvaModal.value = false;
        await loadTvaDeclarations();
        setTimeout(() => { successMsg.value = ''; }, 3000);
    } catch (e) { error.value = e.message; }
    finally { tvaSubmitting.value = false; }
};

const submitTvaDeclaration = async (id) => {
    try {
        const res = await fetch(`/api/company/tva/declarations/${id}/submit`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken.value, Accept: 'application/json' },
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur');
        successMsg.value = data.message;
        await loadTvaDeclarations();
        setTimeout(() => { successMsg.value = ''; }, 3000);
    } catch (e) { error.value = e.message; }
};

const approveTvaDeclaration = async (id) => {
    try {
        const res = await fetch(`/api/company/tva/declarations/${id}/approve`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken.value, Accept: 'application/json' },
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur');
        successMsg.value = data.message;
        await loadTvaDeclarations();
        setTimeout(() => { successMsg.value = ''; }, 3000);
    } catch (e) { error.value = e.message; }
};

const deleteTvaDeclaration = async (id) => {
    if (!confirm('Supprimer cette déclaration ?')) return;
    try {
        const res = await fetch(`/api/company/tva/declarations/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken.value, Accept: 'application/json' },
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur');
        successMsg.value = data.message;
        await loadTvaDeclarations();
        setTimeout(() => { successMsg.value = ''; }, 3000);
    } catch (e) { error.value = e.message; }
};

// ─── 8. Immobilisations ───────────────────────────────────────────
const assets = ref([]);
const assetsLoading = ref(false);
const showAssetModal = ref(false);
const showAssetDetailModal = ref(false);
const selectedAsset = ref(null);
const assetSubmitting = ref(false);
const assetForm = ref({
    designation: '', category: 'informatique', acquisition_date: '',
    gross_value: '', residual_value: '0', depreciation_months: '60',
    depreciation_method: 'linear', notes: '',
});

const loadAssets = async () => {
    assetsLoading.value = true;
    try {
        const res = await fetch('/api/company/fixed-assets');
        if (!res.ok) throw new Error('Erreur chargement immobilisations');
        assets.value = await res.json();
    } catch (e) { error.value = e.message; }
    finally { assetsLoading.value = false; }
};

const openAssetModal = () => {
    error.value = null;
    assetForm.value = {
        designation: '', category: 'informatique', acquisition_date: new Date().toISOString().split('T')[0],
        gross_value: '', residual_value: '0', depreciation_months: '60',
        depreciation_method: 'linear', notes: '',
    };
    showAssetModal.value = true;
};

const submitAsset = async () => {
    assetSubmitting.value = true; error.value = null;
    try {
        const res = await fetch('/api/company/fixed-assets', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken.value, Accept: 'application/json' },
            body: JSON.stringify(assetForm.value),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur');
        successMsg.value = data.message;
        showAssetModal.value = false;
        await loadAssets();
        setTimeout(() => { successMsg.value = ''; }, 3000);
    } catch (e) { error.value = e.message; }
    finally { assetSubmitting.value = false; }
};

const loadAssetDetail = async (id) => {
    try {
        const res = await fetch(`/api/company/fixed-assets/${id}`);
        if (!res.ok) throw new Error('Erreur chargement détail');
        const data = await res.json();
        selectedAsset.value = data;
        showAssetDetailModal.value = true;
    } catch (e) { error.value = e.message; }
};

const generateSchedule = async (id) => {
    if (!confirm('Générer/régénérer le plan d\'amortissement ?')) return;
    try {
        const res = await fetch(`/api/company/fixed-assets/${id}/schedule`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken.value, Accept: 'application/json' },
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur');
        successMsg.value = 'Plan d\'amortissement généré.';
        if (selectedAsset.value?.asset?.id === id) await loadAssetDetail(id);
        setTimeout(() => { successMsg.value = ''; }, 3000);
    } catch (e) { error.value = e.message; }
};

const deleteAsset = async (id) => {
    if (!confirm('Supprimer cette immobilisation ?')) return;
    try {
        const res = await fetch(`/api/company/fixed-assets/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken.value, Accept: 'application/json' },
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur');
        successMsg.value = data.message;
        await loadAssets();
        setTimeout(() => { successMsg.value = ''; }, 3000);
    } catch (e) { error.value = e.message; }
};

// ─── 9. Exercices fiscaux ─────────────────────────────────────────
const fiscalYears = ref([]);
const fyLoading = ref(false);
const showFyModal = ref(false);
const showFyCloseModal = ref(false);
const selectedFy = ref(null);
const fySubmitting = ref(false);
const fyForm = ref({ year: new Date().getFullYear(), date_start: '', date_end: '', notes: '' });
const fyCloseForm = ref({
    check_balance: false, check_tva: false, check_cnss: false,
    check_reconciliation: false, check_inventory: false, notes: '',
});

const loadFiscalYears = async () => {
    fyLoading.value = true;
    try {
        const res = await fetch('/api/company/fiscal-years');
        if (!res.ok) throw new Error('Erreur chargement exercices');
        fiscalYears.value = await res.json();
    } catch (e) { error.value = e.message; }
    finally { fyLoading.value = false; }
};

const openFyModal = () => {
    error.value = null;
    const year = new Date().getFullYear();
    fyForm.value = {
        year, date_start: `${year}-01-01`, date_end: `${year}-12-31`, notes: '',
    };
    showFyModal.value = true;
};

const submitFy = async () => {
    fySubmitting.value = true; error.value = null;
    try {
        const res = await fetch('/api/company/fiscal-years', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken.value, Accept: 'application/json' },
            body: JSON.stringify(fyForm.value),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur');
        successMsg.value = data.message;
        showFyModal.value = false;
        await loadFiscalYears();
        setTimeout(() => { successMsg.value = ''; }, 3000);
    } catch (e) { error.value = e.message; }
    finally { fySubmitting.value = false; }
};

const openFyCloseModal = (fy) => {
    selectedFy.value = fy;
    fyCloseForm.value = {
        check_balance: false, check_tva: false, check_cnss: false,
        check_reconciliation: false, check_inventory: false, notes: '',
    };
    showFyCloseModal.value = true;
};

const submitFyClose = async () => {
    fySubmitting.value = true; error.value = null;
    try {
        const res = await fetch(`/api/company/fiscal-years/${selectedFy.value.id}/close`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken.value, Accept: 'application/json' },
            body: JSON.stringify(fyCloseForm.value),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur');
        successMsg.value = data.message;
        showFyCloseModal.value = false;
        await loadFiscalYears();
        setTimeout(() => { successMsg.value = ''; }, 3000);
    } catch (e) { error.value = e.message; }
    finally { fySubmitting.value = false; }
};

const reopenFy = async (id) => {
    if (!confirm('Réouvrir cet exercice ?')) return;
    try {
        const res = await fetch(`/api/company/fiscal-years/${id}/reopen`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken.value, Accept: 'application/json' },
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur');
        successMsg.value = data.message;
        await loadFiscalYears();
        setTimeout(() => { successMsg.value = ''; }, 3000);
    } catch (e) { error.value = e.message; }
};

const lockFy = async (id) => {
    if (!confirm('Verrouiller cet exercice ?')) return;
    try {
        const res = await fetch(`/api/company/fiscal-years/${id}/lock`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken.value, Accept: 'application/json' },
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur');
        successMsg.value = data.message;
        await loadFiscalYears();
        setTimeout(() => { successMsg.value = ''; }, 3000);
    } catch (e) { error.value = e.message; }
};

// ─── 10. Réconciliation bancaire ──────────────────────────────────
const reconciliations = ref([]);
const recoLoading = ref(false);
const showRecoModal = ref(false);
const recoSubmitting = ref(false);
const recoForm = ref({
    bank_account: '', bank_name: '', period: '',
    statement_date: '', balance_per_statement: 0, balance_per_books: 0,
});

const loadReconciliations = async () => {
    recoLoading.value = true;
    try {
        const res = await fetch('/api/company/bank-reconciliations');
        if (!res.ok) throw new Error('Erreur chargement réconciliations');
        reconciliations.value = await res.json();
    } catch (e) { error.value = e.message; }
    finally { recoLoading.value = false; }
};

const openRecoModal = () => {
    error.value = null;
    const now = new Date();
    recoForm.value = {
        bank_account: '', bank_name: '', period: now.toISOString().slice(0, 7),
        statement_date: now.toISOString().split('T')[0],
        balance_per_statement: 0, balance_per_books: 0,
    };
    showRecoModal.value = true;
};

const submitReco = async () => {
    recoSubmitting.value = true; error.value = null;
    try {
        const res = await fetch('/api/company/bank-reconciliations', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken.value, Accept: 'application/json' },
            body: JSON.stringify(recoForm.value),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur');
        successMsg.value = data.message;
        showRecoModal.value = false;
        await loadReconciliations();
        setTimeout(() => { successMsg.value = ''; }, 3000);
    } catch (e) { error.value = e.message; }
    finally { recoSubmitting.value = false; }
};

const matchReco = async (id) => {
    if (!confirm('Marquer comme rapprochée ?')) return;
    try {
        const res = await fetch(`/api/company/bank-reconciliations/${id}/match`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken.value, Accept: 'application/json' },
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur');
        successMsg.value = data.message;
        await loadReconciliations();
        setTimeout(() => { successMsg.value = ''; }, 3000);
    } catch (e) { error.value = e.message; }
};

const approveReco = async (id) => {
    try {
        const res = await fetch(`/api/company/bank-reconciliations/${id}/approve`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken.value, Accept: 'application/json' },
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur');
        successMsg.value = data.message;
        await loadReconciliations();
        setTimeout(() => { successMsg.value = ''; }, 3000);
    } catch (e) { error.value = e.message; }
};

const deleteReco = async (id) => {
    if (!confirm('Supprimer cette réconciliation ?')) return;
    try {
        const res = await fetch(`/api/company/bank-reconciliations/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken.value, Accept: 'application/json' },
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Erreur');
        successMsg.value = data.message;
        await loadReconciliations();
        setTimeout(() => { successMsg.value = ''; }, 3000);
    } catch (e) { error.value = e.message; }
};

// ─── Changement d'onglet ─────────────────────────────────────────
const onTabChange = (tab) => {
    activeTab.value = tab;
    error.value = null;
    if (tab === 'dashboard') loadStats();
    if (tab === 'accounts' && !accounts.value.length) loadAccounts();
    if (tab === 'journals' && !journals.value.length) loadJournals();
    if (tab === 'balance') loadBalance();
    if (tab === 'grand-livre') { if (!grandLivreAccounts.value.length) loadGrandLivre(); }
    if (tab === 'bilan') loadBilan();
    if (tab === 'resultat') loadResultat();
    if (tab === 'tva' && !tvaDeclarations.value.length) loadTvaDeclarations();
    if (tab === 'assets' && !assets.value.length) loadAssets();
    if (tab === 'fiscal-years' && !fiscalYears.value.length) loadFiscalYears();
    if (tab === 'reconciliation' && !reconciliations.value.length) loadReconciliations();
};

// ─── Initialisation ───────────────────────────────────────────────
onMounted(() => {
    loadStats();
    loadAccounts();
    loadJournals();
});
</script>

<template>
    <CompanyLayout page-title="Comptabilité">
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
        <ul class="nav nav-tabs border-0 mb-4 flex-nowrap overflow-auto">
            <li class="nav-item" v-for="tab in tabs" :key="tab.key">
                <button class="nav-link rounded-top-3 px-4 py-3 fw-semibold text-nowrap"
                        :class="activeTab === tab.key ? 'active bg-white shadow-sm border-bottom-0' : 'text-muted'"
                        @click="onTabChange(tab.key)">
                    <i :class="tab.icon + ' me-2'"></i>{{ tab.label }}
                </button>
            </li>
        </ul>

        <!-- ==================== DASHBOARD ==================== -->
        <div v-if="activeTab === 'dashboard'">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="card border-0 rounded-4 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="bi-book fs-1 text-primary"></i>
                            <h3 class="fw-bold mt-2">{{ stats?.total_accounts || 0 }}</h3>
                            <small class="text-muted">Comptes actifs: {{ stats?.active_accounts || 0 }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 rounded-4 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="bi-journal-text fs-1 text-success"></i>
                            <h3 class="fw-bold mt-2">{{ stats?.total_journals || 0 }}</h3>
                            <small class="text-muted">Postés: {{ stats?.posted_journals || 0 }} · Brouillons: {{ stats?.draft_journals || 0 }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 rounded-4 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="bi-arrow-up-circle fs-1 text-danger"></i>
                            <h3 class="fw-bold mt-2">{{ formatCurrency(stats?.total_debit) }}</h3>
                            <small class="text-muted">Total débit</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 rounded-4 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="bi-arrow-down-circle fs-1 text-info"></i>
                            <h3 class="fw-bold mt-2">{{ formatCurrency(stats?.total_credit) }}</h3>
                            <small class="text-muted">Total crédit</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card border-0 rounded-4 shadow-sm mt-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0 font-heading">Répartition par type de journal</h5>
                </div>
                <div class="card-body p-4">
                    <div v-if="!stats?.by_type" class="text-muted">Aucune donnée</div>
                    <div v-else class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Type</th>
                                    <th class="text-end">Nombre</th>
                                    <th class="text-end">Débit</th>
                                    <th class="text-end">Crédit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(v, k) in stats.by_type" :key="k">
                                    <td class="fw-semibold">{{ journalTypeLabel(k) }}</td>
                                    <td class="text-end">{{ v.count }}</td>
                                    <td class="text-end">{{ formatCurrency(v.debit) }}</td>
                                    <td class="text-end">{{ formatCurrency(v.credit) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== PLAN COMPTABLE ==================== -->
        <div v-if="activeTab === 'accounts'">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h5 class="fw-bold mb-0 font-heading">Plan comptable SYSCOHADA</h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#importCsv">
                            <i class="bi-upload me-1"></i>Import CSV
                        </button>
                        <button class="btn btn-sm btn-primary" @click="openAccountModal()">
                            <i class="bi-plus-lg me-1"></i>Nouveau compte
                        </button>
                    </div>
                </div>
                <!-- CSV Import -->
                <div class="collapse px-4" id="importCsv">
                    <div class="border rounded-3 p-3 mb-3 bg-light">
                        <p class="small text-muted mb-2">Fichier CSV avec colonnes: code, name, type</p>
                        <div class="input-group">
                            <input type="file" class="form-control form-control-sm" accept=".csv" @change="e => importFile = e.target.files[0]">
                            <button class="btn btn-sm btn-primary" @click="importAccounts">
                                <i class="bi-upload me-1"></i>Importer
                            </button>
                        </div>
                        <div v-if="importError" class="text-danger small mt-1">{{ importError }}</div>
                        <div v-if="importSuccess" class="text-success small mt-1">{{ importSuccess }}</div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div v-if="accountsLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                    </div>
                    <div v-else-if="!accounts.length" class="text-center py-5 text-muted">
                        <i class="bi-book" style="font-size: 3rem;"></i>
                        <p class="mt-3 fs-5">Aucun compte comptable.</p>
                        <button class="btn btn-primary" @click="openAccountModal()">Créer un compte</button>
                    </div>
                    <div v-else class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Code</th>
                                    <th>Libellé</th>
                                    <th>Classe</th>
                                    <th>TVA</th>
                                    <th class="text-end">Débit</th>
                                    <th class="text-end">Crédit</th>
                                    <th class="text-end">Solde</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="acc in accounts" :key="acc.id">
                                    <td class="ps-4 fw-semibold">{{ acc.code }}</td>
                                    <td>{{ acc.name }}</td>
                                    <td><span class="badge bg-light text-dark">{{ acc.syscohada_class || acc.type }}</span></td>
                                    <td>
                                        <span v-if="acc.has_tva" class="badge bg-info">{{ acc.tva_rate }}%</span>
                                        <span v-else class="text-muted">-</span>
                                    </td>
                                    <td class="text-end">{{ formatCurrency(acc.debit) }}</td>
                                    <td class="text-end">{{ formatCurrency(acc.credit) }}</td>
                                    <td class="text-end fw-semibold" :class="acc.balance < 0 ? 'text-danger' : 'text-success'">
                                        {{ formatCurrency(acc.balance) }}
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-secondary me-1" title="Modifier" @click="openAccountModal(acc)">
                                            <i class="bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" title="Supprimer" @click="deleteAccount(acc.id)">
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

        <!-- ==================== JOURNAUX ==================== -->
        <div v-if="activeTab === 'journals'">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h5 class="fw-bold mb-0 font-heading">Journaux comptables</h5>
                    <button class="btn btn-sm btn-primary" @click="openJournalModal()">
                        <i class="bi-plus-lg me-1"></i>Nouvelle écriture
                    </button>
                </div>
                <div class="card-body p-0">
                    <div v-if="journalsLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                    </div>
                    <div v-else-if="!journals.length" class="text-center py-5 text-muted">
                        <i class="bi-journal-text" style="font-size: 3rem;"></i>
                        <p class="mt-3 fs-5">Aucune écriture.</p>
                        <button class="btn btn-primary" @click="openJournalModal()">Créer une écriture</button>
                    </div>
                    <div v-else class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>Réf.</th>
                                    <th>N° Pièce</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Statut</th>
                                    <th class="text-end">Débit</th>
                                    <th class="text-end">Crédit</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="j in journals" :key="j.id">
                                    <td class="ps-4">{{ formatDate(j.entry_date) }}</td>
                                    <td class="fw-semibold">{{ j.reference }}</td>
                                    <td><small class="text-muted">{{ j.numero_piece || '-' }}</small></td>
                                    <td><span class="badge bg-light text-dark">{{ journalTypeLabel(j.journal_type) }}</span></td>
                                    <td class="text-truncate" style="max-width: 150px;">{{ j.description || '-' }}</td>
                                    <td>
                                        <span class="badge rounded-pill" :class="statusBadge(j.status)">
                                            {{ statusLabel(j.status) }}
                                        </span>
                                        <span v-if="j.is_reversal" class="badge bg-secondary ms-1">Extourne</span>
                                    </td>
                                    <td class="text-end">{{ formatCurrency(j.debit_total) }}</td>
                                    <td class="text-end">{{ formatCurrency(j.credit_total) }}</td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-info me-1" title="Détail" @click="loadJournalDetail(j.id)">
                                            <i class="bi-eye"></i>
                                        </button>
                                        <button v-if="j.status === 'draft'" class="btn btn-sm btn-outline-success me-1" title="Poster" @click="postJournal(j.id)">
                                            <i class="bi-check2-circle"></i>
                                        </button>
                                        <button v-if="j.status === 'posted' && !j.is_reversal" class="btn btn-sm btn-outline-warning me-1" title="Extourner" @click="reverseJournal(j.id)">
                                            <i class="bi-arrow-return-left"></i>
                                        </button>
                                        <button v-if="j.status === 'draft'" class="btn btn-sm btn-outline-danger" title="Supprimer" @click="deleteJournal(j.id)">
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

        <!-- ==================== BALANCE ==================== -->
        <div v-if="activeTab === 'balance'">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h5 class="fw-bold mb-0 font-heading">Balance des comptes</h5>
                    <div class="d-flex gap-2 align-items-center">
                        <select class="form-select form-select-sm" v-model="fiscalYearFilter" style="width: auto;">
                            <option value="">Tous les exercices</option>
                            <option v-for="fy in fiscalYears" :key="fy.id" :value="fy.id">{{ fy.year }}</option>
                        </select>
                        <button class="btn btn-sm btn-primary" @click="loadBalance">
                            <i class="bi-search me-1"></i>Actualiser
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" @click="showBalanceByClass = !showBalanceByClass">
                            <i class="bi-layers me-1"></i>{{ showBalanceByClass ? 'Détail' : 'Par classe' }}
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div v-if="balanceLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                    </div>
                    <div v-else-if="!balanceData" class="text-center py-5 text-muted">
                        <i class="bi-table" style="font-size: 3rem;"></i>
                        <p class="mt-3 fs-5">Aucune donnée.</p>
                    </div>
                    <div v-else-if="showBalanceByClass && balanceData.by_class" class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Classe</th>
                                    <th>Libellé</th>
                                    <th class="text-end">Débit</th>
                                    <th class="text-end">Crédit</th>
                                    <th class="text-end pe-4">Solde</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="cls in balanceData.by_class" :key="cls.class">
                                    <td class="ps-4 fw-bold">{{ cls.class }}</td>
                                    <td>{{ cls.label }}</td>
                                    <td class="text-end">{{ formatCurrency(cls.debit) }}</td>
                                    <td class="text-end">{{ formatCurrency(cls.credit) }}</td>
                                    <td class="text-end pe-4 fw-semibold">{{ formatCurrency(cls.solde) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Compte</th>
                                    <th>Libellé</th>
                                    <th class="text-end">Total débit</th>
                                    <th class="text-end">Total crédit</th>
                                    <th class="text-end pe-4">Solde</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="acc in balanceData.accounts" :key="acc.code">
                                    <td class="ps-4 fw-semibold">{{ acc.code }}</td>
                                    <td>{{ acc.name }}</td>
                                    <td class="text-end">{{ formatCurrency(acc.debit) }}</td>
                                    <td class="text-end">{{ formatCurrency(acc.credit) }}</td>
                                    <td class="text-end pe-4 fw-semibold" :class="acc.solde < 0 ? 'text-danger' : 'text-success'">
                                        {{ formatCurrency(acc.solde) }}
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="2" class="ps-4">Totaux</td>
                                    <td class="text-end">{{ formatCurrency(balanceData.totals.total_debit) }}</td>
                                    <td class="text-end">{{ formatCurrency(balanceData.totals.total_credit) }}</td>
                                    <td class="text-end pe-4">{{ formatCurrency(balanceData.totals.total_debit - balanceData.totals.total_credit) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== GRAND LIVRE ==================== -->
        <div v-if="activeTab === 'grand-livre'">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0 font-heading">Grand livre</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Compte</label>
                            <select class="form-select" v-model="grandLivreFilter.account_id">
                                <option value="">Tous les comptes</option>
                                <option v-for="acc in grandLivreAccounts" :key="acc.id" :value="acc.id">{{ acc.code }} - {{ acc.name }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Date début</label>
                            <input type="date" class="form-control" v-model="grandLivreFilter.date_from">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Date fin</label>
                            <input type="date" class="form-control" v-model="grandLivreFilter.date_to">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-primary w-100" @click="loadGrandLivre">
                                <i class="bi-search me-1"></i>Filtrer
                            </button>
                        </div>
                    </div>

                    <div v-if="grandLivreLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                    </div>
                    <div v-else-if="!grandLivreLines.length" class="text-center py-5 text-muted">
                        <i class="bi-list-columns" style="font-size: 3rem;"></i>
                        <p class="mt-3 fs-5">Aucune écriture trouvée.</p>
                    </div>
                    <div v-else class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Réf.</th>
                                    <th>Compte</th>
                                    <th>Libellé</th>
                                    <th class="text-end">Débit</th>
                                    <th class="text-end">Crédit</th>
                                    <th class="text-end">Solde cumulé</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="l in grandLivreLines" :key="l.id">
                                    <td>{{ formatDate(l.date) }}</td>
                                    <td class="fw-semibold">{{ l.reference }}</td>
                                    <td>{{ l.account_code }} - {{ l.account_name }}</td>
                                    <td>{{ l.label }}</td>
                                    <td class="text-end">{{ l.debit ? formatCurrency(l.debit) : '-' }}</td>
                                    <td class="text-end">{{ l.credit ? formatCurrency(l.credit) : '-' }}</td>
                                    <td class="text-end fw-semibold">{{ formatCurrency(l.solde_cumule) }}</td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="4" class="ps-4">Totaux</td>
                                    <td class="text-end">{{ formatCurrency(grandLivreTotals.total_debit) }}</td>
                                    <td class="text-end">{{ formatCurrency(grandLivreTotals.total_credit) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== BILAN ==================== -->
        <div v-if="activeTab === 'bilan'">
            <div class="d-flex justify-content-end mb-3">
                <select class="form-select form-select-sm" v-model="fiscalYearFilter" style="width: auto;">
                    <option value="">Tous les exercices</option>
                    <option v-for="fy in fiscalYears" :key="fy.id" :value="fy.id">{{ fy.year }}</option>
                </select>
                <button class="btn btn-sm btn-primary ms-2" @click="loadBilan">
                    <i class="bi-search me-1"></i>Actualiser
                </button>
            </div>
            <div v-if="bilanLoading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
            </div>
            <div v-else-if="!bilanData" class="text-center py-5 text-muted">
                <i class="bi-bar-chart" style="font-size: 3rem;"></i>
                <p class="mt-3 fs-5">Aucune donnée pour le bilan.</p>
            </div>
            <div v-else class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 rounded-4 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 pt-4 px-4">
                            <h5 class="fw-bold mb-0 font-heading text-primary">Actif</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr><th class="ps-4">Compte</th><th>Libellé</th><th class="text-end pe-4">Montant</th></tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="acc in bilanData.actif.accounts" :key="acc.code">
                                            <td class="ps-4 fw-semibold">{{ acc.code }}</td>
                                            <td>{{ acc.name }}</td>
                                            <td class="text-end pe-4">{{ formatCurrency(acc.solde) }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="table-light fw-bold">
                                        <tr><td colspan="2" class="ps-4">Total Actif</td><td class="text-end pe-4">{{ formatCurrency(bilanData.actif.total) }}</td></tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 rounded-4 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 pt-4 px-4">
                            <h5 class="fw-bold mb-0 font-heading text-success">Passif</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr><th class="ps-4">Compte</th><th>Libellé</th><th class="text-end pe-4">Montant</th></tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="acc in bilanData.passif.accounts" :key="acc.code">
                                            <td class="ps-4 fw-semibold">{{ acc.code }}</td>
                                            <td>{{ acc.name }}</td>
                                            <td class="text-end pe-4">{{ formatCurrency(acc.solde) }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="table-light fw-bold">
                                        <tr><td colspan="2" class="ps-4">Total Passif</td><td class="text-end pe-4">{{ formatCurrency(bilanData.passif.total) }}</td></tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== RÉSULTAT ==================== -->
        <div v-if="activeTab === 'resultat'">
            <div class="d-flex justify-content-end mb-3">
                <select class="form-select form-select-sm" v-model="fiscalYearFilter" style="width: auto;">
                    <option value="">Tous les exercices</option>
                    <option v-for="fy in fiscalYears" :key="fy.id" :value="fy.id">{{ fy.year }}</option>
                </select>
                <button class="btn btn-sm btn-primary ms-2" @click="loadResultat">
                    <i class="bi-search me-1"></i>Actualiser
                </button>
            </div>
            <div v-if="resultatLoading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
            </div>
            <div v-else-if="!resultatData" class="text-center py-5 text-muted">
                <i class="bi-pie-chart" style="font-size: 3rem;"></i>
                <p class="mt-3 fs-5">Aucune donnée pour le compte de résultat.</p>
            </div>
            <div v-else>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card border-0 rounded-4 shadow-sm h-100">
                            <div class="card-header bg-transparent border-0 pt-4 px-4">
                                <h5 class="fw-bold mb-0 font-heading text-danger">Charges (classe 6)</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead class="table-light">
                                            <tr><th class="ps-4">Compte</th><th>Libellé</th><th class="text-end pe-4">Montant</th></tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="acc in resultatData.charges.accounts" :key="acc.code">
                                                <td class="ps-4 fw-semibold">{{ acc.code }}</td>
                                                <td>{{ acc.name }}</td>
                                                <td class="text-end pe-4">{{ formatCurrency(acc.solde) }}</td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="table-light fw-bold">
                                            <tr><td colspan="2" class="ps-4">Total charges</td><td class="text-end pe-4">{{ formatCurrency(resultatData.charges.total) }}</td></tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 rounded-4 shadow-sm h-100">
                            <div class="card-header bg-transparent border-0 pt-4 px-4">
                                <h5 class="fw-bold mb-0 font-heading text-success">Produits (classe 7)</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead class="table-light">
                                            <tr><th class="ps-4">Compte</th><th>Libellé</th><th class="text-end pe-4">Montant</th></tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="acc in resultatData.produits.accounts" :key="acc.code">
                                                <td class="ps-4 fw-semibold">{{ acc.code }}</td>
                                                <td>{{ acc.name }}</td>
                                                <td class="text-end pe-4">{{ formatCurrency(acc.solde) }}</td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="table-light fw-bold">
                                            <tr><td colspan="2" class="ps-4">Total produits</td><td class="text-end pe-4">{{ formatCurrency(resultatData.produits.total) }}</td></tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card border-0 rounded-4 shadow-sm mt-4">
                    <div class="card-body p-4 text-center">
                        <h5 class="fw-bold mb-1">Résultat net</h5>
                        <div class="fs-2 fw-bold" :class="resultatData.resultat >= 0 ? 'text-success' : 'text-danger'">
                            {{ formatCurrency(resultatData.resultat) }} F
                        </div>
                        <small class="text-muted" v-if="resultatData.resultat >= 0">Bénéfice</small>
                        <small class="text-muted" v-else>Perte</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== TVA ==================== -->
        <div v-if="activeTab === 'tva'">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h5 class="fw-bold mb-0 font-heading">Déclarations TVA</h5>
                    <button class="btn btn-sm btn-primary" @click="openTvaModal()">
                        <i class="bi-plus-lg me-1"></i>Nouvelle déclaration
                    </button>
                </div>
                <div class="card-body p-0">
                    <div v-if="tvaLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                    </div>
                    <div v-else-if="!tvaDeclarations.length" class="text-center py-5 text-muted">
                        <i class="bi-receipt" style="font-size: 3rem;"></i>
                        <p class="mt-3 fs-5">Aucune déclaration TVA.</p>
                        <button class="btn btn-primary" @click="openTvaModal()">Créer une déclaration</button>
                    </div>
                    <div v-else class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Période</th>
                                    <th>Type</th>
                                    <th class="text-end">Collectée</th>
                                    <th class="text-end">Déductible</th>
                                    <th class="text-end">Net à payer</th>
                                    <th>Statut</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="d in tvaDeclarations" :key="d.id">
                                    <td class="ps-4 fw-semibold">{{ d.period }}</td>
                                    <td><span class="badge bg-light text-dark">{{ d.type }}</span></td>
                                    <td class="text-end">{{ formatCurrency(d.tva_collected) }}</td>
                                    <td class="text-end">{{ formatCurrency(d.tva_deductible) }}</td>
                                    <td class="text-end fw-bold" :class="d.tva_net > 0 ? 'text-danger' : 'text-success'">
                                        {{ formatCurrency(d.tva_net) }}
                                    </td>
                                    <td><span class="badge rounded-pill" :class="statusBadge(d.status)">{{ statusLabel(d.status) }}</span></td>
                                    <td class="text-end pe-4">
                                        <button v-if="d.status === 'draft'" class="btn btn-sm btn-outline-success me-1" title="Soumettre" @click="submitTvaDeclaration(d.id)">
                                            <i class="bi-send"></i>
                                        </button>
                                        <button v-if="d.status === 'submitted'" class="btn btn-sm btn-outline-primary me-1" title="Approuver" @click="approveTvaDeclaration(d.id)">
                                            <i class="bi-check2-circle"></i>
                                        </button>
                                        <button v-if="d.status === 'draft'" class="btn btn-sm btn-outline-danger" title="Supprimer" @click="deleteTvaDeclaration(d.id)">
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

        <!-- ==================== IMMOBILISATIONS ==================== -->
        <div v-if="activeTab === 'assets'">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h5 class="fw-bold mb-0 font-heading">Immobilisations</h5>
                    <button class="btn btn-sm btn-primary" @click="openAssetModal()">
                        <i class="bi-plus-lg me-1"></i>Nouveau bien
                    </button>
                </div>
                <div class="card-body p-0">
                    <div v-if="assetsLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                    </div>
                    <div v-else-if="!assets.length" class="text-center py-5 text-muted">
                        <i class="bi-building" style="font-size: 3rem;"></i>
                        <p class="mt-3 fs-5">Aucune immobilisation.</p>
                        <button class="btn btn-primary" @click="openAssetModal()">Ajouter un bien</button>
                    </div>
                    <div v-else class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Désignation</th>
                                    <th>Catégorie</th>
                                    <th>Date acquisition</th>
                                    <th class="text-end">Valeur brute</th>
                                    <th class="text-end">Dotation/mois</th>
                                    <th class="text-end">VCN</th>
                                    <th>Statut</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="a in assets" :key="a.id">
                                    <td class="ps-4 fw-semibold">{{ a.designation }}</td>
                                    <td><span class="badge bg-light text-dark">{{ a.category }}</span></td>
                                    <td>{{ formatDate(a.acquisition_date) }}</td>
                                    <td class="text-end">{{ formatCurrency(a.gross_value) }}</td>
                                    <td class="text-end">{{ formatCurrency(a.monthly_depr) }}</td>
                                    <td class="text-end fw-semibold">{{ formatCurrency(a.net_book_value) }}</td>
                                    <td><span class="badge rounded-pill" :class="statusBadge(a.status)">{{ a.status }}</span></td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-info me-1" title="Détail" @click="loadAssetDetail(a.id)">
                                            <i class="bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary me-1" title="Plan amort." @click="loadAssetDetail(a.id)">
                                            <i class="bi-calendar-range"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" title="Supprimer" @click="deleteAsset(a.id)">
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

        <!-- ==================== EXERCICES FISCAUX ==================== -->
        <div v-if="activeTab === 'fiscal-years'">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h5 class="fw-bold mb-0 font-heading">Exercices comptables</h5>
                    <button class="btn btn-sm btn-primary" @click="openFyModal()">
                        <i class="bi-plus-lg me-1"></i>Nouvel exercice
                    </button>
                </div>
                <div class="card-body p-0">
                    <div v-if="fyLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                    </div>
                    <div v-else-if="!fiscalYears.length" class="text-center py-5 text-muted">
                        <i class="bi-calendar3" style="font-size: 3rem;"></i>
                        <p class="mt-3 fs-5">Aucun exercice.</p>
                        <button class="btn btn-primary" @click="openFyModal()">Créer un exercice</button>
                    </div>
                    <div v-else class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Année</th>
                                    <th>Début</th>
                                    <th>Fin</th>
                                    <th>Statut</th>
                                    <th>Clôturé le</th>
                                    <th>Par</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="fy in fiscalYears" :key="fy.id">
                                    <td class="ps-4 fw-bold">{{ fy.year }}</td>
                                    <td>{{ formatDate(fy.date_start) }}</td>
                                    <td>{{ formatDate(fy.date_end) }}</td>
                                    <td><span class="badge rounded-pill" :class="statusBadge(fy.status)">{{ statusLabel(fy.status) }}</span></td>
                                    <td>{{ fy.closed_at || '-' }}</td>
                                    <td>{{ fy.closed_by || '-' }}</td>
                                    <td class="text-end pe-4">
                                        <button v-if="fy.status === 'open'" class="btn btn-sm btn-outline-warning me-1" title="Clôturer" @click="openFyCloseModal(fy)">
                                            <i class="bi-lock"></i>
                                        </button>
                                        <button v-if="fy.status === 'closed'" class="btn btn-sm btn-outline-success me-1" title="Réouvrir" @click="reopenFy(fy.id)">
                                            <i class="bi-unlock"></i>
                                        </button>
                                        <button v-if="fy.status === 'open' || fy.status === 'closed'" class="btn btn-sm btn-outline-dark me-1" title="Verrouiller" @click="lockFy(fy.id)">
                                            <i class="bi-shield-lock"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== RÉCONCILIATION BANCAIRE ==================== -->
        <div v-if="activeTab === 'reconciliation'">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h5 class="fw-bold mb-0 font-heading">Réconciliation bancaire</h5>
                    <button class="btn btn-sm btn-primary" @click="openRecoModal()">
                        <i class="bi-plus-lg me-1"></i>Nouvelle réconciliation
                    </button>
                </div>
                <div class="card-body p-0">
                    <div v-if="recoLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                    </div>
                    <div v-else-if="!reconciliations.length" class="text-center py-5 text-muted">
                        <i class="bi-arrow-left-right" style="font-size: 3rem;"></i>
                        <p class="mt-3 fs-5">Aucune réconciliation.</p>
                        <button class="btn btn-primary" @click="openRecoModal()">Créer une réconciliation</button>
                    </div>
                    <div v-else class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Période</th>
                                    <th>Compte bancaire</th>
                                    <th>Banque</th>
                                    <th class="text-end">Solde relevé</th>
                                    <th class="text-end">Solde livres</th>
                                    <th class="text-end">Écart</th>
                                    <th>Statut</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="r in reconciliations" :key="r.id">
                                    <td class="ps-4 fw-semibold">{{ r.period }}</td>
                                    <td>{{ r.bank_account }}</td>
                                    <td>{{ r.bank_name || '-' }}</td>
                                    <td class="text-end">{{ formatCurrency(r.balance_per_statement) }}</td>
                                    <td class="text-end">{{ formatCurrency(r.balance_per_books) }}</td>
                                    <td class="text-end fw-semibold" :class="r.difference !== 0 ? 'text-danger' : 'text-success'">
                                        {{ formatCurrency(r.difference) }}
                                    </td>
                                    <td><span class="badge rounded-pill" :class="statusBadge(r.status)">{{ statusLabel(r.status) }}</span></td>
                                    <td class="text-end pe-4">
                                        <button v-if="r.status === 'draft'" class="btn btn-sm btn-outline-success me-1" title="Rapprocher" @click="matchReco(r.id)">
                                            <i class="bi-check2"></i>
                                        </button>
                                        <button v-if="r.status === 'matched'" class="btn btn-sm btn-outline-primary me-1" title="Approuver" @click="approveReco(r.id)">
                                            <i class="bi-check2-all"></i>
                                        </button>
                                        <button v-if="r.status === 'draft'" class="btn btn-sm btn-outline-danger" title="Supprimer" @click="deleteReco(r.id)">
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

        <!-- ==================== MODAL COMPTE ==================== -->
        <div class="modal fade" :class="{ show: showAccountModal }" tabindex="-1"
             :style="{ display: showAccountModal ? 'block' : 'none' }" @click.self="showAccountModal = false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold font-heading">
                            <i class="bi-book me-2"></i>{{ editingAccount ? 'Modifier le compte' : 'Nouveau compte' }}
                        </h5>
                        <button type="button" class="btn-close" @click="showAccountModal = false"></button>
                    </div>
                    <form @submit.prevent="submitAccount">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Code</label>
                                    <input type="text" class="form-control" v-model="accountForm.code" placeholder="Ex: 401000" required>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold small">Libellé</label>
                                    <input type="text" class="form-control" v-model="accountForm.name" placeholder="Nom du compte" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Classe SYSCOHADA</label>
                                    <select class="form-select" v-model="accountForm.type">
                                        <option v-for="t in accountTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="accActive" v-model="accountForm.is_active">
                                        <label class="form-check-label" for="accActive">Compte actif</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="accHasTva" v-model="accountForm.has_tva">
                                        <label class="form-check-label" for="accHasTva">Assujetti à la TVA</label>
                                    </div>
                                </div>
                                <div class="col-md-6" v-if="accountForm.has_tva">
                                    <label class="form-label fw-semibold small">Taux TVA (%)</label>
                                    <input type="number" step="0.1" class="form-control" v-model="accountForm.tva_rate">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light rounded-3" @click="showAccountModal = false">Annuler</button>
                            <button type="submit" class="btn btn-primary rounded-3" :disabled="accountSubmitting">
                                <span v-if="accountSubmitting" class="spinner-border spinner-border-sm me-1"></span>
                                <i v-else class="bi-check-lg me-1"></i>{{ editingAccount ? 'Enregistrer' : 'Créer' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div v-if="showAccountModal" class="modal-backdrop fade show"></div>

        <!-- ==================== MODAL JOURNAL ==================== -->
        <div class="modal fade" :class="{ show: showJournalModal }" tabindex="-1"
             :style="{ display: showJournalModal ? 'block' : 'none' }" @click.self="showJournalModal = false">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 rounded-4">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold font-heading"><i class="bi-journal-text me-2"></i>Nouvelle écriture comptable</h5>
                        <button type="button" class="btn-close" @click="showJournalModal = false"></button>
                    </div>
                    <form @submit.prevent="submitJournal">
                        <div class="modal-body">
                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small">Type de journal</label>
                                    <select class="form-select" v-model="journalForm.journal_type">
                                        <option value="achat">Achats</option>
                                        <option value="vente">Ventes</option>
                                        <option value="banque">Banque</option>
                                        <option value="caisse">Caisse</option>
                                        <option value="operations_diverses">Opérations diverses</option>
                                        <option value="od">OD</option>
                                        <option value="salaire">Salaires</option>
                                        <option value="investissement">Investissement</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small">Date d'écriture</label>
                                    <input type="date" class="form-control" v-model="journalForm.entry_date" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small">Référence</label>
                                    <input type="text" class="form-control" v-model="journalForm.reference" placeholder="Auto si vide">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small">Équilibré</label>
                                    <div>
                                        <span class="badge rounded-pill fs-6" :class="journalIsBalanced ? 'bg-success' : 'bg-warning text-dark'">
                                            {{ journalIsBalanced ? 'Equilibré' : 'Déséquilibré' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Description</label>
                                    <textarea class="form-control" rows="2" v-model="journalForm.description" placeholder="Description de l'écriture..."></textarea>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h6 class="fw-bold mb-0">Lignes d'écriture</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" @click="addJournalLine">
                                    <i class="bi-plus-lg me-1"></i>Ajouter ligne
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 25%;">Compte</th>
                                            <th style="width: 30%;">Libellé</th>
                                            <th style="width: 15%;">Débit</th>
                                            <th style="width: 15%;">Crédit</th>
                                            <th style="width: 2%;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(line, index) in journalForm.lines" :key="index">
                                            <td>
                                                <select class="form-select form-select-sm" v-model="line.account_id" required>
                                                    <option value="" disabled>Sélectionner un compte</option>
                                                    <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.code }} - {{ acc.name }}</option>
                                                </select>
                                            </td>
                                            <td><input type="text" class="form-control form-control-sm" v-model="line.label" placeholder="Libellé" required></td>
                                            <td><input type="number" step="0.01" min="0" class="form-control form-control-sm" v-model.number="line.debit" placeholder="0,00"></td>
                                            <td><input type="number" step="0.01" min="0" class="form-control form-control-sm" v-model.number="line.credit" placeholder="0,00"></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-danger" @click="removeJournalLine(index)" :disabled="journalForm.lines.length <= 2">
                                                    <i class="bi-x-lg"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="table-light fw-bold">
                                        <tr>
                                            <td colspan="2" class="text-end">Totaux :</td>
                                            <td class="text-end">{{ formatCurrency(journalTotalDebit) }}</td>
                                            <td class="text-end">{{ formatCurrency(journalTotalCredit) }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light rounded-3" @click="showJournalModal = false">Annuler</button>
                            <button type="submit" class="btn btn-primary rounded-3" :disabled="journalSubmitting || !journalIsBalanced">
                                <span v-if="journalSubmitting" class="spinner-border spinner-border-sm me-1"></span>
                                <i v-else class="bi-check-lg me-1"></i>Créer l'écriture
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div v-if="showJournalModal" class="modal-backdrop fade show"></div>

        <!-- ==================== MODAL DÉTAIL JOURNAL ==================== -->
        <div class="modal fade" :class="{ show: showJournalDetailModal }" tabindex="-1"
             :style="{ display: showJournalDetailModal ? 'block' : 'none' }" @click.self="showJournalDetailModal = false">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 rounded-4">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold font-heading">Écriture {{ selectedJournal?.reference }}</h5>
                        <button type="button" class="btn-close" @click="showJournalDetailModal = false"></button>
                    </div>
                    <div class="modal-body" v-if="selectedJournal">
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <small class="text-muted d-block">Type</small>
                                <span class="fw-semibold">{{ journalTypeLabel(selectedJournal.journal_type) }}</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Date</small>
                                <span>{{ formatDate(selectedJournal.entry_date) }}</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Statut</small>
                                <span class="badge rounded-pill" :class="statusBadge(selectedJournal.status)">{{ statusLabel(selectedJournal.status) }}</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Créé par</small>
                                <span class="fw-semibold">{{ selectedJournal.created_by }}</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">N° Pièce</small>
                                <span class="fw-semibold">{{ selectedJournal.numero_piece || '-' }}</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Extourne</small>
                                <span class="fw-semibold">{{ selectedJournal.is_reversal ? 'Oui' : 'Non' }}</span>
                            </div>
                            <div v-if="selectedJournal.description" class="col-12">
                                <small class="text-muted d-block">Description</small>
                                <span>{{ selectedJournal.description }}</span>
                            </div>
                        </div>
                        <h6 class="fw-bold mb-3">Lignes d'écriture</h6>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Compte</th>
                                        <th>Libellé</th>
                                        <th class="text-end">Débit</th>
                                        <th class="text-end">Crédit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="l in selectedJournal.lines" :key="l.id">
                                        <td class="fw-semibold">{{ l.account_code }} - {{ l.account_name }}</td>
                                        <td>{{ l.label }}</td>
                                        <td class="text-end">{{ l.debit ? formatCurrency(l.debit) : '-' }}</td>
                                        <td class="text-end">{{ l.credit ? formatCurrency(l.credit) : '-' }}</td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light fw-bold">
                                    <tr>
                                        <td colspan="2" class="text-end">Totaux :</td>
                                        <td class="text-end">{{ formatCurrency(selectedJournal.debit_total) }}</td>
                                        <td class="text-end">{{ formatCurrency(selectedJournal.credit_total) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-3" @click="showJournalDetailModal = false">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="showJournalDetailModal" class="modal-backdrop fade show"></div>

        <!-- ==================== MODAL TVA ==================== -->
        <div class="modal fade" :class="{ show: showTvaModal }" tabindex="-1"
             :style="{ display: showTvaModal ? 'block' : 'none' }" @click.self="showTvaModal = false">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 rounded-4">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold font-heading"><i class="bi-receipt me-2"></i>Nouvelle déclaration TVA</h5>
                        <button type="button" class="btn-close" @click="showTvaModal = false"></button>
                    </div>
                    <form @submit.prevent="submitTva">
                        <div class="modal-body">
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Période</label>
                                    <input type="month" class="form-control" v-model="tvaPeriod">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Type</label>
                                    <select class="form-select" v-model="tvaForm.type">
                                        <option value="monthly">Mensuelle</option>
                                        <option value="quarterly">Trimestrielle</option>
                                        <option value="annual">Annuelle</option>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-primary w-100" @click="computeTva" :disabled="tvaSubmitting">
                                        <i class="bi-calculator me-1"></i>Calculer auto
                                    </button>
                                </div>
                            </div>
                            <div v-if="tvaComputed" class="alert alert-info rounded-3">
                                <i class="bi-info-circle me-2"></i>
                                {{ tvaComputed.lines_count }} lignes de TVA trouvées.
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">TVA collectée</label>
                                    <input type="number" step="0.01" class="form-control" v-model="tvaForm.tva_collected" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">TVA déductible</label>
                                    <input type="number" step="0.01" class="form-control" v-model="tvaForm.tva_deductible" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">TVA nette</label>
                                    <input type="number" step="0.01" class="form-control" :value="parseFloat(tvaForm.tva_collected) - parseFloat(tvaForm.tva_deductible)" readonly>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Notes</label>
                                    <textarea class="form-control" rows="2" v-model="tvaForm.notes" placeholder="Notes..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light rounded-3" @click="showTvaModal = false">Annuler</button>
                            <button type="submit" class="btn btn-primary rounded-3" :disabled="tvaSubmitting">
                                <span v-if="tvaSubmitting" class="spinner-border spinner-border-sm me-1"></span>
                                <i v-else class="bi-check-lg me-1"></i>Créer la déclaration
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div v-if="showTvaModal" class="modal-backdrop fade show"></div>

        <!-- ==================== MODAL IMMOBILISATION ==================== -->
        <div class="modal fade" :class="{ show: showAssetModal }" tabindex="-1"
             :style="{ display: showAssetModal ? 'block' : 'none' }" @click.self="showAssetModal = false">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 rounded-4">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold font-heading"><i class="bi-building me-2"></i>Nouvelle immobilisation</h5>
                        <button type="button" class="btn-close" @click="showAssetModal = false"></button>
                    </div>
                    <form @submit.prevent="submitAsset">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Désignation</label>
                                    <input type="text" class="form-control" v-model="assetForm.designation" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Catégorie</label>
                                    <select class="form-select" v-model="assetForm.category">
                                        <option value="informatique">Informatique</option>
                                        <option value="mobilier">Mobilier</option>
                                        <option value="vehicule">Véhicule</option>
                                        <option value="batiment">Bâtiment</option>
                                        <option value="logiciel">Logiciel</option>
                                        <option value="other">Autre</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Date d'acquisition</label>
                                    <input type="date" class="form-control" v-model="assetForm.acquisition_date" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Méthode d'amortissement</label>
                                    <select class="form-select" v-model="assetForm.depreciation_method">
                                        <option value="linear">Linéaire</option>
                                        <option value="declining">Dégressif</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Valeur brute (FCFA)</label>
                                    <input type="number" step="0.01" min="0" class="form-control" v-model="assetForm.gross_value" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Valeur résiduelle</label>
                                    <input type="number" step="0.01" min="0" class="form-control" v-model="assetForm.residual_value">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Durée (mois)</label>
                                    <input type="number" min="1" class="form-control" v-model="assetForm.depreciation_months" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Notes</label>
                                    <textarea class="form-control" rows="2" v-model="assetForm.notes"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light rounded-3" @click="showAssetModal = false">Annuler</button>
                            <button type="submit" class="btn btn-primary rounded-3" :disabled="assetSubmitting">
                                <span v-if="assetSubmitting" class="spinner-border spinner-border-sm me-1"></span>
                                <i v-else class="bi-check-lg me-1"></i>Créer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div v-if="showAssetModal" class="modal-backdrop fade show"></div>

        <!-- ==================== MODAL DÉTAIL IMMOBILISATION ==================== -->
        <div class="modal fade" :class="{ show: showAssetDetailModal }" tabindex="-1"
             :style="{ display: showAssetDetailModal ? 'block' : 'none' }" @click.self="showAssetDetailModal = false">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 rounded-4">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold font-heading">{{ selectedAsset?.asset?.designation || 'Détail' }}</h5>
                        <button type="button" class="btn-close" @click="showAssetDetailModal = false"></button>
                    </div>
                    <div class="modal-body" v-if="selectedAsset">
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <small class="text-muted d-block">Catégorie</small>
                                <span class="fw-semibold">{{ selectedAsset.asset.category }}</span>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Valeur brute</small>
                                <span class="fw-semibold">{{ formatCurrency(selectedAsset.asset.gross_value) }}</span>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">VCN</small>
                                <span class="fw-semibold">{{ formatCurrency(selectedAsset.asset.net_book_value) }}</span>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Statut</small>
                                <span class="badge rounded-pill" :class="statusBadge(selectedAsset.asset.status)">{{ selectedAsset.asset.status }}</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0">Plan d'amortissement</h6>
                            <button class="btn btn-sm btn-primary" @click="generateSchedule(selectedAsset.asset.id)">
                                <i class="bi-calendar-range me-1"></i>Générer / Régénérer
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>N°</th>
                                        <th>Date</th>
                                        <th class="text-end">Dotation</th>
                                        <th class="text-end">Cumul</th>
                                        <th class="text-end">VCN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="s in selectedAsset.schedule" :key="s.id">
                                        <td>{{ s.period_number }}</td>
                                        <td>{{ formatDate(s.period_date) }}</td>
                                        <td class="text-end">{{ formatCurrency(s.amount) }}</td>
                                        <td class="text-end">{{ formatCurrency(s.accumulated) }}</td>
                                        <td class="text-end fw-semibold">{{ formatCurrency(s.net_value) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-3" @click="showAssetDetailModal = false">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="showAssetDetailModal" class="modal-backdrop fade show"></div>

        <!-- ==================== MODAL EXERCICE ==================== -->
        <div class="modal fade" :class="{ show: showFyModal }" tabindex="-1"
             :style="{ display: showFyModal ? 'block' : 'none' }" @click.self="showFyModal = false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold font-heading"><i class="bi-calendar3 me-2"></i>Nouvel exercice</h5>
                        <button type="button" class="btn-close" @click="showFyModal = false"></button>
                    </div>
                    <form @submit.prevent="submitFy">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Année</label>
                                    <input type="number" min="2000" max="2100" class="form-control" v-model="fyForm.year" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Date début</label>
                                    <input type="date" class="form-control" v-model="fyForm.date_start" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Date fin</label>
                                    <input type="date" class="form-control" v-model="fyForm.date_end" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Notes</label>
                                    <textarea class="form-control" rows="2" v-model="fyForm.notes"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light rounded-3" @click="showFyModal = false">Annuler</button>
                            <button type="submit" class="btn btn-primary rounded-3" :disabled="fySubmitting">
                                <span v-if="fySubmitting" class="spinner-border spinner-border-sm me-1"></span>
                                <i v-else class="bi-check-lg me-1"></i>Créer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div v-if="showFyModal" class="modal-backdrop fade show"></div>

        <!-- ==================== MODAL CLÔTURE EXERCICE ==================== -->
        <div class="modal fade" :class="{ show: showFyCloseModal }" tabindex="-1"
             :style="{ display: showFyCloseModal ? 'block' : 'none' }" @click.self="showFyCloseModal = false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold font-heading">
                            <i class="bi-lock me-2"></i>Clôture exercice {{ selectedFy?.year }}
                        </h5>
                        <button type="button" class="btn-close" @click="showFyCloseModal = false"></button>
                    </div>
                    <form @submit.prevent="submitFyClose">
                        <div class="modal-body">
                            <p class="text-muted small">Cochez les vérifications effectuées avant de clôturer :</p>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="checkBalance" v-model="fyCloseForm.check_balance">
                                <label class="form-check-label" for="checkBalance">Balance vérifiée (débit = crédit)</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="checkTva" v-model="fyCloseForm.check_tva">
                                <label class="form-check-label" for="checkTva">TVA déclarée et payée</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="checkCnss" v-model="fyCloseForm.check_cnss">
                                <label class="form-check-label" for="checkCnss">CNSS à jour</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="checkReconciliation" v-model="fyCloseForm.check_reconciliation">
                                <label class="form-check-label" for="checkReconciliation">Réconciliation bancaire effectuée</label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="checkInventory" v-model="fyCloseForm.check_inventory">
                                <label class="form-check-label" for="checkInventory">Inventaire validé</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Notes de clôture</label>
                                <textarea class="form-control" rows="2" v-model="fyCloseForm.notes"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light rounded-3" @click="showFyCloseModal = false">Annuler</button>
                            <button type="submit" class="btn btn-warning rounded-3" :disabled="fySubmitting">
                                <span v-if="fySubmitting" class="spinner-border spinner-border-sm me-1"></span>
                                <i v-else class="bi-lock me-1"></i>Clôturer l'exercice
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div v-if="showFyCloseModal" class="modal-backdrop fade show"></div>

        <!-- ==================== MODAL RÉCONCILIATION ==================== -->
        <div class="modal fade" :class="{ show: showRecoModal }" tabindex="-1"
             :style="{ display: showRecoModal ? 'block' : 'none' }" @click.self="showRecoModal = false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold font-heading"><i class="bi-arrow-left-right me-2"></i>Nouvelle réconciliation</h5>
                        <button type="button" class="btn-close" @click="showRecoModal = false"></button>
                    </div>
                    <form @submit.prevent="submitReco">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Compte bancaire</label>
                                    <input type="text" class="form-control" v-model="recoForm.bank_account" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Banque</label>
                                    <input type="text" class="form-control" v-model="recoForm.bank_name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Période</label>
                                    <input type="month" class="form-control" v-model="recoForm.period" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Date relevé</label>
                                    <input type="date" class="form-control" v-model="recoForm.statement_date" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Solde relevé bancaire</label>
                                    <input type="number" step="0.01" class="form-control" v-model="recoForm.balance_per_statement" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Solde comptable</label>
                                    <input type="number" step="0.01" class="form-control" v-model="recoForm.balance_per_books" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light rounded-3" @click="showRecoModal = false">Annuler</button>
                            <button type="submit" class="btn btn-primary rounded-3" :disabled="recoSubmitting">
                                <span v-if="recoSubmitting" class="spinner-border spinner-border-sm me-1"></span>
                                <i v-else class="bi-check-lg me-1"></i>Créer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div v-if="showRecoModal" class="modal-backdrop fade show"></div>
    </CompanyLayout>
</template>

<style>
@media print {
    body {
        background: white !important;
    }
    .sidebar, header, .btn, .nav-tabs, .modal, .modal-backdrop,
    .no-print, .btn-close, .alert, nav, footer {
        display: none !important;
    }
    main, .content, .col-md-6, .col-md-3, .col-md-4, .col-md-12, .col-12 {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    .card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
        break-inside: avoid;
    }
    .table {
        font-size: 12px !important;
    }
    .print-header {
        display: block !important;
    }
    .print-header h1 {
        font-size: 18px;
        margin-bottom: 4px;
    }
    .print-header p {
        font-size: 12px;
        color: #666;
        margin-bottom: 16px;
    }
    .row {
        display: block;
    }
    .col-md-6, .col-md-3, .col-md-4 {
        width: 100% !important;
        max-width: 100% !important;
    }
    table {
        page-break-inside: avoid;
    }
    tr {
        page-break-inside: avoid;
    }
}
.print-header {
    display: none;
}
</style>
