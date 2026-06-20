<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';
import { authStore } from '../../../stores/auth';

const accounts = ref([]);
const transactions = ref([]);
const balances = ref([]);
const loading = ref(true);
const error = ref(null);
const activeTab = ref('balances');
const submitting = ref(false);

// Account modal
const showAccModal = ref(false);
const accForm = ref({ name: '', type: 'current', account_number: '', initial_balance: '', bank_name: '', is_active: true });

// Transaction modal
const showTxModal = ref(false);
const txForm = ref({ account_id: '', type: 'income', amount: '', description: '', date: new Date().toISOString().substring(0, 10), reference: '' });

const tabs = [
    { key: 'balances',     label: 'Soldes',        icon: 'bi-wallet2' },
    { key: 'transactions', label: 'Transactions',   icon: 'bi-arrow-left-right' },
    { key: 'accounts',     label: 'Comptes',        icon: 'bi-bank' },
];

const fetchData = async () => {
    loading.value = true;
    error.value = null;
    try {
        const [balRes, txRes, accRes] = await Promise.all([
            fetch('/api/erp/balances').then(r => r.ok ? r.json() : []),
            fetch('/api/erp/transactions').then(r => r.ok ? r.json() : []),
            fetch('/api/erp/accounts').then(r => r.ok ? r.json() : []),
        ]);
        balances.value = balRes;
        transactions.value = txRes;
        accounts.value = accRes;
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const submitAccount = async () => {
    submitting.value = true;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/erp/treasury/accounts', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(accForm.value),
        });
        if (!res.ok) throw new Error('Erreur');
        showAccModal.value = false;
        accForm.value = { name: '', type: 'current', account_number: '', initial_balance: '', bank_name: '', is_active: true };
        await fetchData();
    } catch (e) {
        alert('Erreur: ' + e.message);
    } finally {
        submitting.value = false;
    }
};

const submitTransaction = async () => {
    submitting.value = true;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/erp/treasury/transactions', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(txForm.value),
        });
        if (!res.ok) throw new Error('Erreur');
        showTxModal.value = false;
        txForm.value = { account_id: '', type: 'income', amount: '', description: '', date: new Date().toISOString().substring(0, 10), reference: '' };
        await fetchData();
    } catch (e) {
        alert('Erreur: ' + e.message);
    } finally {
        submitting.value = false;
    }
};

onMounted(fetchData);
</script>

<template>
    <GelLayout page-title="Trésorerie">
        <!-- Actions -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h5 class="fw-bold mb-1">Trésorerie</h5>
                <p class="text-muted small mb-0">Soldes, transactions et comptes bancaires</p>
            </div>
            <div class="d-flex gap-2">
                <button v-if="activeTab === 'accounts'" class="btn btn-outline-primary btn-sm" @click="showAccModal = true">
                    <i class="bi-plus-lg me-1"></i>Compte
                </button>
                <button class="btn btn-primary btn-sm" @click="showTxModal = true">
                    <i class="bi-cash me-1"></i>Transaction
                </button>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-4">
            <li v-for="t in tabs" :key="t.key" class="nav-item">
                <button class="nav-link" :class="{ active: activeTab === t.key }" @click="activeTab = t.key">
                    <i :class="t.icon" class="me-1"></i>{{ t.label }}
                </button>
            </li>
        </ul>

        <!-- Loading -->
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement…</span></div>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="alert alert-danger">
            <i class="bi-exclamation-triangle-fill me-2"></i>{{ error }}
            <button @click="fetchData" class="btn btn-primary btn-sm ms-3">
                <i class="bi-arrow-clockwise me-1"></i>Réessayer
            </button>
        </div>

        <!-- ══ BALANCES TAB ══ -->
        <div v-else-if="activeTab === 'balances'" class="bg-white rounded-lg shadow p-6">
            <h6 class="fw-bold mb-3"><i class="bi-wallet2 me-2" style="color:#FF7900;"></i>Soldes</h6>
            <div class="row g-3">
                <div v-if="!balances.length" class="col-12">
                    <div class="text-center py-5 text-muted small">
                        <i class="bi-wallet2 fs-1 d-block mb-2" style="color:#dce3ee;"></i>
                        Aucun compte
                    </div>
                </div>
                <div v-for="bal in balances" :key="bal.id" class="col-md-6 col-lg-4">
                    <div class="border rounded p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded p-2" style="background:#f0f4f8;">
                                    <i :class="bal.type === 'bank' ? 'bi-bank' : bal.type === 'mobile_money' ? 'bi-phone' : 'bi-cash'"></i>
                                </div>
                                <div>
                                    <div class="fw-medium small">{{ bal.name }}</div>
                                    <div>
                                        <span class="badge bg-info">{{ bal.type === 'bank' ? 'Banque' : bal.type === 'mobile_money' ? 'Mobile Money' : 'Caisse' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="small text-muted mb-2">{{ bal.account_number || '-' }}</div>
                        <div class="d-flex justify-content-between align-items-end pt-2" style="border-top:1px solid #f0f4f8;">
                            <span class="small text-muted">Solde</span>
                            <span class="fw-bold" :class="bal.balance >= 0 ? 'text-success' : 'text-danger'">
                                {{ $formatCurrency(bal.balance) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ TRANSACTIONS TAB ══ -->
        <div v-else-if="activeTab === 'transactions'" class="bg-white rounded-lg shadow p-6">
            <h6 class="fw-bold mb-3"><i class="bi-arrow-left-right me-2" style="color:#FF7900;"></i>Transactions</h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="small text-muted">
                        <tr><th>Date</th><th>Compte</th><th>Type</th><th>Réf.</th><th class="text-end">Montant</th><th>Description</th></tr>
                    </thead>
                    <tbody>
                        <tr v-if="!transactions.length">
                            <td colspan="6" class="text-center py-4 text-muted small">
                                <i class="bi-inbox fs-4 d-block mb-1"></i>Aucune transaction
                            </td>
                        </tr>
                        <tr v-for="tx in transactions" :key="tx.id">
                            <td class="small text-muted">{{ tx.date ? $formatDate(tx.date) : '-' }}</td>
                            <td class="small">{{ tx.account?.name || '-' }}</td>
                            <td><span class="badge" :class="tx.type === 'income' ? 'bg-success' : 'bg-danger'">{{ tx.type === 'income' ? 'Entrée' : 'Sortie' }}</span></td>
                            <td class="small text-muted">{{ tx.reference || '-' }}</td>
                            <td class="text-end fw-medium" :class="tx.type === 'income' ? 'text-success' : 'text-danger'">
                                {{ tx.type === 'income' ? '+' : '-' }}{{ $formatCurrency(tx.amount) }}
                            </td>
                            <td class="small text-muted">{{ tx.description || '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ══ ACCOUNTS TAB ══ -->
        <div v-else-if="activeTab === 'accounts'" class="bg-white rounded-lg shadow p-6">
            <h6 class="fw-bold mb-3"><i class="bi-bank me-2" style="color:#FF7900;"></i>Comptes</h6>
            <div class="row g-3">
                <div v-if="!accounts.length" class="col-12">
                    <div class="text-center py-5 text-muted small">
                        <i class="bi-bank fs-1 d-block mb-2" style="color:#dce3ee;"></i>
                        Aucun compte créé
                    </div>
                </div>
                <div v-for="acc in accounts" :key="acc.id" class="col-md-4 col-6">
                    <div class="border rounded p-3">
                        <div class="rounded p-2 mb-2 d-inline-block" style="background:#f0f4f8;">
                            <i class="bi-bank"></i>
                        </div>
                        <div class="fw-medium small">{{ acc.name }}</div>
                        <div class="small text-muted">{{ acc.bank_name || '' }} <span v-if="acc.bank_name">—</span> {{ acc.type }}</div>
                        <div class="small text-muted">{{ acc.account_number }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Modal -->
        <div v-if="showAccModal" class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold">Nouveau compte</h6>
                        <button type="button" class="btn-close" @click="showAccModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small">Nom *</label>
                                <input v-model="accForm.name" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Type</label>
                                <select v-model="accForm.type" class="form-select form-select-sm">
                                    <option value="current">Courant</option>
                                    <option value="savings">Épargne</option>
                                    <option value="cash">Caisse</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">N° de compte</label>
                                <input v-model="accForm.account_number" class="form-control form-control-sm">
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Banque</label>
                                <input v-model="accForm.bank_name" class="form-control form-control-sm">
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Solde initial</label>
                                <input v-model="accForm.initial_balance" type="number" step="0.01" class="form-control form-control-sm">
                            </div>
                            <div class="col-6 d-flex align-items-end">
                                <label class="d-flex align-items-center gap-2" style="font-size:12px; cursor:pointer;">
                                    <input v-model="accForm.is_active" type="checkbox" class="form-check-input">
                                    <span class="fw-medium">Actif</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-secondary" @click="showAccModal = false">Annuler</button>
                        <button class="btn btn-sm btn-primary" :disabled="submitting" @click="submitAccount">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>Créer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Modal -->
        <div v-if="showTxModal" class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold">Nouvelle transaction</h6>
                        <button type="button" class="btn-close" @click="showTxModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small">Compte</label>
                                <select v-model="txForm.account_id" class="form-select form-select-sm">
                                    <option value="">Sélectionner</option>
                                    <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.name }}</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Type</label>
                                <select v-model="txForm.type" class="form-select form-select-sm">
                                    <option value="income">Entrée</option>
                                    <option value="expense">Sortie</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Montant</label>
                                <input v-model="txForm.amount" type="number" step="0.01" class="form-control form-control-sm">
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Date</label>
                                <input v-model="txForm.date" type="date" class="form-control form-control-sm">
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Référence</label>
                                <input v-model="txForm.reference" class="form-control form-control-sm">
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Description</label>
                                <input v-model="txForm.description" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-secondary" @click="showTxModal = false">Annuler</button>
                        <button class="btn btn-sm btn-primary" :disabled="submitting" @click="submitTransaction">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>Enregistrer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>
