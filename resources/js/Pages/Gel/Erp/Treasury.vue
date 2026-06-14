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

const typeBadge = (type) => {
    return type === 'income' ? 'bg-success' : 'bg-danger';
};

onMounted(fetchData);
</script>

<template>
    <GelLayout page-title="Trésorerie">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <ul class="nav nav-pills">
                <li class="nav-item"><button class="nav-link" :class="{ active: activeTab === 'balances' }" @click="activeTab = 'balances'">Soldes</button></li>
                <li class="nav-item"><button class="nav-link" :class="{ active: activeTab === 'transactions' }" @click="activeTab = 'transactions'">Transactions</button></li>
                <li class="nav-item"><button class="nav-link" :class="{ active: activeTab === 'accounts' }" @click="activeTab = 'accounts'">Comptes</button></li>
            </ul>
            <div class="d-flex gap-2">
                <button v-if="activeTab === 'accounts'" class="btn btn-outline-primary btn-sm" @click="showAccModal = true"><i class="bi-plus-lg me-1"></i>Compte</button>
                <button class="btn btn-primary btn-sm" @click="showTxModal = true"><i class="bi-cash me-1"></i>Transaction</button>
            </div>
        </div>

        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <!-- Balances -->
        <div v-else-if="activeTab === 'balances'" class="row g-3">
            <div v-if="!balances.length" class="col-12 text-center py-5 text-muted">Aucun compte bancaire.</div>
            <div v-for="bal in balances" :key="bal.id" class="col-md-6 col-lg-4">
                <div class="card card-dashboard">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="fw-bold mb-0">{{ bal.name }}</h6>
                            <span class="badge bg-secondary">{{ bal.type }}</span>
                        </div>
                        <div class="small text-muted mb-2">{{ bal.account_number }}</div>
                        <div class="d-flex justify-content-between align-items-end">
                            <span class="small text-muted">Solde</span>
                            <span class="h5 mb-0 fw-bold" :class="bal.balance >= 0 ? 'text-success' : 'text-danger'">
                                {{ $formatCurrency(bal.balance) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions -->
        <div v-else-if="activeTab === 'transactions'" class="card card-dashboard">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="small text-muted"><tr><th>Date</th><th>Compte</th><th>Type</th><th>Réf.</th><th class="text-end">Montant</th><th>Description</th></tr></thead>
                    <tbody>
                        <tr v-if="!transactions.length"><td colspan="6" class="text-center py-4 text-muted">Aucune transaction.</td></tr>
                        <tr v-for="tx in transactions" :key="tx.id">
                            <td class="small">{{ tx.date ? $formatDate(tx.date) : '-' }}</td>
                            <td class="small">{{ tx.account?.name || '-' }}</td>
                            <td><span class="badge" :class="typeBadge(tx.type)">{{ tx.type === 'income' ? 'Entrée' : 'Sortie' }}</span></td>
                            <td class="small">{{ tx.reference || '-' }}</td>
                            <td class="text-end fw-medium" :class="tx.type === 'income' ? 'text-success' : 'text-danger'">
                                {{ tx.type === 'income' ? '+' : '-' }}{{ $formatCurrency(tx.amount) }}
                            </td>
                            <td class="small text-muted">{{ tx.description || '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Accounts list -->
        <div v-else class="row g-3">
            <div v-if="!accounts.length" class="col-12 text-center py-5 text-muted">Aucun compte créé.</div>
            <div v-for="acc in accounts" :key="acc.id" class="col-md-4">
                <div class="card card-dashboard">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi-bank"></i>
                            <h6 class="fw-bold mb-0">{{ acc.name }}</h6>
                        </div>
                        <div class="small text-muted">{{ acc.bank_name || '' }} - {{ acc.type }}</div>
                        <div class="small">{{ acc.account_number }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Modal -->
        <div v-if="showAccModal" class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header"><h6 class="fw-bold">Nouveau compte</h6><button class="btn-close" @click="showAccModal = false"></button></div>
                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col-6"><label class="form-label small">Nom *</label><input v-model="accForm.name" class="form-control form-control-sm" required></div>
                            <div class="col-6"><label class="form-label small">Type</label>
                                <select v-model="accForm.type" class="form-select form-select-sm">
                                    <option value="current">Courant</option><option value="savings">Épargne</option><option value="cash">Caisse</option>
                                </select>
                            </div>
                            <div class="col-6"><label class="form-label small">Numéro de compte</label><input v-model="accForm.account_number" class="form-control form-control-sm"></div>
                            <div class="col-6"><label class="form-label small">Banque</label><input v-model="accForm.bank_name" class="form-control form-control-sm"></div>
                            <div class="col-6"><label class="form-label small">Solde initial</label><input v-model="accForm.initial_balance" type="number" step="0.01" class="form-control form-control-sm"></div>
                            <div class="col-6 d-flex align-items-end">
                                <div class="form-check">
                                    <input v-model="accForm.is_active" type="checkbox" id="treasAccActive" class="form-check-input">
                                    <label for="treasAccActive" class="form-check-label small">Actif</label>
                                </div>
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
                    <div class="modal-header"><h6 class="fw-bold">Nouvelle transaction</h6><button class="btn-close" @click="showTxModal = false"></button></div>
                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col-6"><label class="form-label small">Compte</label>
                                <select v-model="txForm.account_id" class="form-select form-select-sm">
                                    <option value="">Sélectionner</option>
                                    <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.name }}</option>
                                </select>
                            </div>
                            <div class="col-6"><label class="form-label small">Type</label>
                                <select v-model="txForm.type" class="form-select form-select-sm">
                                    <option value="income">Entrée</option><option value="expense">Sortie</option>
                                </select>
                            </div>
                            <div class="col-6"><label class="form-label small">Montant</label><input v-model="txForm.amount" type="number" step="0.01" class="form-control form-control-sm"></div>
                            <div class="col-6"><label class="form-label small">Date</label><input v-model="txForm.date" type="date" class="form-control form-control-sm"></div>
                            <div class="col-6"><label class="form-label small">Référence</label><input v-model="txForm.reference" class="form-control form-control-sm"></div>
                            <div class="col-12"><label class="form-label small">Description</label><input v-model="txForm.description" class="form-control form-control-sm"></div>
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
