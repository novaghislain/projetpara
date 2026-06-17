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
        <div class="isup-shell">

            <!-- Header -->
            <div class="isup-portal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="isup-portal-logo">
                        <i class="bi-wallet2" style="font-size:20px;"></i>
                    </div>
                    <div>
                        <div class="isup-portal-company">Trésorerie</div>
                        <div class="isup-portal-sub">Soldes, transactions et comptes bancaires</div>
                    </div>
                </div>
            </div>

            <!-- Tabs bar -->
            <div class="isup-tabs-bar">
                <button v-for="t in tabs" :key="t.key"
                        class="isup-tab"
                        :class="{ 'isup-tab-active': activeTab === t.key }"
                        @click="activeTab = t.key">
                    <i :class="t.icon"></i>
                    {{ t.label }}
                </button>
                <div class="ms-auto d-flex align-items-center px-2 gap-2">
                    <button v-if="activeTab === 'accounts'" class="isup-btn-orange btn-sm" @click="showAccModal = true">
                        <i class="bi-plus-lg me-1"></i>Compte
                    </button>
                    <button class="isup-btn-primary btn-sm" @click="showTxModal = true">
                        <i class="bi-cash me-1"></i>Transaction
                    </button>
                </div>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="d-flex align-items-center justify-content-center gap-3 py-5">
                <div class="isup-spinner"></div>
                <span style="color:#888; font-size:14px;">Chargement…</span>
            </div>

            <!-- Error -->
            <div v-else-if="error" class="isup-alert-error m-3">
                <i class="bi-exclamation-triangle-fill me-2"></i>{{ error }}
                <button @click="fetchData" class="isup-btn-primary ms-3" style="padding:4px 12px; font-size:11px;">
                    <i class="bi-arrow-clockwise me-1"></i>Réessayer
                </button>
            </div>

            <!-- ══ BALANCES TAB ══ -->
            <div v-else-if="activeTab === 'balances'" class="p-3">
                <div class="row g-3">
                    <div v-if="!balances.length" class="col-12">
                        <div class="text-center py-5 text-muted" style="font-size:13px;">
                            <i class="bi-wallet2" style="font-size:28px; display:block; margin-bottom:8px; color:#dce3ee;"></i>
                            Aucun compte
                        </div>
                    </div>
                    <div v-for="bal in balances" :key="bal.id" class="col-md-6 col-lg-4">
                        <div class="isup-bal-card">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="isup-bal-icon">
                                        <i :class="bal.type === 'bank' ? 'bi-bank' : bal.type === 'mobile_money' ? 'bi-phone' : 'bi-cash'"></i>
                                    </div>
                                    <div>
                                        <div class="isup-bal-name">{{ bal.name }}</div>
                                        <div class="isup-bal-type">
                                            <span class="isup-status" :class="bal.type === 'bank' ? 'isup-status-blue' : bal.type === 'mobile_money' ? 'isup-status-warn' : 'isup-status-green'">
                                                {{ bal.type === 'bank' ? 'Banque' : bal.type === 'mobile_money' ? 'Mobile Money' : 'Caisse' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="isup-bal-number">{{ bal.account_number || '-' }}</div>
                            <div class="d-flex justify-content-between align-items-end mt-2 pt-2" style="border-top:1px solid #f0f4f8;">
                                <span class="isup-bal-label">Solde</span>
                                <span class="isup-bal-amount" :class="bal.balance >= 0 ? 'isup-bal-positive' : 'isup-bal-negative'">
                                    {{ $formatCurrency(bal.balance) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ TRANSACTIONS TAB ══ -->
            <div v-else-if="activeTab === 'transactions'" class="p-3">
                <div class="isup-panel">
                    <div class="isup-panel-header">
                        <i class="bi-arrow-left-right me-2" style="color:#FF7900;"></i>Transactions
                    </div>
                    <div class="isup-panel-body p-0">
                        <div class="isup-table-wrap">
                            <table class="isup-table w-100">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Compte</th>
                                        <th>Type</th>
                                        <th>Réf.</th>
                                        <th class="text-end">Montant</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!transactions.length">
                                        <td colspan="6" class="text-center py-4 text-muted" style="font-size:13px;">
                                            <i class="bi-inbox" style="font-size:22px; display:block; margin-bottom:6px;"></i>Aucune transaction
                                        </td>
                                    </tr>
                                    <tr v-for="tx in transactions" :key="tx.id">
                                        <td style="font-size:12px; color:#888;">{{ tx.date ? $formatDate(tx.date) : '-' }}</td>
                                        <td style="font-size:12px;">{{ tx.account?.name || '-' }}</td>
                                        <td>
                                            <span class="isup-status" :class="tx.type === 'income' ? 'isup-status-green' : 'isup-status-red'">
                                                {{ tx.type === 'income' ? 'Entrée' : 'Sortie' }}
                                            </span>
                                        </td>
                                        <td style="font-size:12px; color:#888;">{{ tx.reference || '-' }}</td>
                                        <td class="text-end fw-bold" :class="tx.type === 'income' ? 'text-success' : 'text-danger'" style="font-size:13px;">
                                            {{ tx.type === 'income' ? '+' : '-' }}{{ $formatCurrency(tx.amount) }}
                                        </td>
                                        <td style="font-size:12px; color:#888;">{{ tx.description || '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ ACCOUNTS TAB ══ -->
            <div v-else-if="activeTab === 'accounts'" class="p-3">
                <div class="row g-3">
                    <div v-if="!accounts.length" class="col-12">
                        <div class="text-center py-5 text-muted" style="font-size:13px;">
                            <i class="bi-bank" style="font-size:28px; display:block; margin-bottom:8px; color:#dce3ee;"></i>
                            Aucun compte créé
                        </div>
                    </div>
                    <div v-for="acc in accounts" :key="acc.id" class="col-md-4 col-6">
                        <div class="isup-acc-card">
                            <div class="isup-acc-icon">
                                <i class="bi-bank"></i>
                            </div>
                            <div class="isup-acc-name">{{ acc.name }}</div>
                            <div class="isup-acc-detail">{{ acc.bank_name || '' }} <span v-if="acc.bank_name">—</span> {{ acc.type }}</div>
                            <div class="isup-acc-number">{{ acc.account_number }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Modal -->
        <div v-if="showAccModal" class="isup-modal-overlay" @click.self="showAccModal = false">
            <div class="isup-modal">
                <div class="isup-modal-header">
                    <span>Nouveau compte</span>
                    <button class="isup-modal-close" @click="showAccModal = false">&times;</button>
                </div>
                <div class="isup-modal-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="isup-label">Nom *</label>
                            <input v-model="accForm.name" class="isup-input" required>
                        </div>
                        <div class="col-6">
                            <label class="isup-label">Type</label>
                            <select v-model="accForm.type" class="isup-select">
                                <option value="current">Courant</option>
                                <option value="savings">Épargne</option>
                                <option value="cash">Caisse</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="isup-label">N° de compte</label>
                            <input v-model="accForm.account_number" class="isup-input">
                        </div>
                        <div class="col-6">
                            <label class="isup-label">Banque</label>
                            <input v-model="accForm.bank_name" class="isup-input">
                        </div>
                        <div class="col-6">
                            <label class="isup-label">Solde initial</label>
                            <input v-model="accForm.initial_balance" type="number" step="0.01" class="isup-input">
                        </div>
                        <div class="col-6 d-flex align-items-end">
                            <label class="d-flex align-items-center gap-2" style="font-size:12px; cursor:pointer;">
                                <input v-model="accForm.is_active" type="checkbox" class="isup-checkbox">
                                <span style="font-weight:600; color:#163A5E;">Actif</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="isup-modal-footer">
                    <button class="isup-btn-grey" @click="showAccModal = false">Annuler</button>
                    <button class="isup-btn-primary" :disabled="submitting" @click="submitAccount">
                        <span v-if="submitting" class="isup-spinner-sm me-1"></span>Créer
                    </button>
                </div>
            </div>
        </div>

        <!-- Transaction Modal -->
        <div v-if="showTxModal" class="isup-modal-overlay" @click.self="showTxModal = false">
            <div class="isup-modal">
                <div class="isup-modal-header">
                    <span>Nouvelle transaction</span>
                    <button class="isup-modal-close" @click="showTxModal = false">&times;</button>
                </div>
                <div class="isup-modal-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="isup-label">Compte</label>
                            <select v-model="txForm.account_id" class="isup-select">
                                <option value="">Sélectionner</option>
                                <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.name }}</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="isup-label">Type</label>
                            <select v-model="txForm.type" class="isup-select">
                                <option value="income">Entrée</option>
                                <option value="expense">Sortie</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="isup-label">Montant</label>
                            <input v-model="txForm.amount" type="number" step="0.01" class="isup-input">
                        </div>
                        <div class="col-6">
                            <label class="isup-label">Date</label>
                            <input v-model="txForm.date" type="date" class="isup-input">
                        </div>
                        <div class="col-6">
                            <label class="isup-label">Référence</label>
                            <input v-model="txForm.reference" class="isup-input">
                        </div>
                        <div class="col-12">
                            <label class="isup-label">Description</label>
                            <input v-model="txForm.description" class="isup-input">
                        </div>
                    </div>
                </div>
                <div class="isup-modal-footer">
                    <button class="isup-btn-grey" @click="showTxModal = false">Annuler</button>
                    <button class="isup-btn-primary" :disabled="submitting" @click="submitTransaction">
                        <span v-if="submitting" class="isup-spinner-sm me-1"></span>Enregistrer
                    </button>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<style scoped>
/* ══ ERP Treasury — unique styles ══ */

.isup-tabs-bar { display:flex; background:#f5f7fb; border-bottom:1px solid #dce3ee; align-items:stretch; }
.isup-tab { background:transparent; border:none; color:#888; font-size:13px; font-weight:600; padding:10px 18px; cursor:pointer; white-space:nowrap; border-bottom:3px solid transparent; transition:all 0.15s; display:flex; align-items:center; gap:6px; }
.isup-tab:hover { color:#163A5E; background:#eef3f9; }
.isup-tab.isup-tab-active { color:#163A5E; border-bottom-color:#FF7900; background:#fff; font-weight:700; }
.isup-tab i { font-size:14px; }

.isup-status-warn { background:#fff8e1; color:#f57f17; }
.isup-status-blue { background:#e3f2fd; color:#1565c0; }

.isup-bal-card { background:#fff; border:1px solid #dce3ee; border-radius:6px; padding:14px 16px; display:flex; align-items:center; gap:14px; }
.isup-bal-icon { width:44px; height:44px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }

.isup-badge-sm { display:inline-flex; align-items:center; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; padding:2px 9px; border-radius:3px; }
</style>
