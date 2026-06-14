<script setup>
import { ref, computed, onMounted } from 'vue';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';
import { authStore } from '../../stores/auth';

const registers = ref([]);
const transactions = ref([]);
const currentRegister = ref(null);
const loading = ref(true);
const error = ref(null);
const activeTab = ref('dashboard');
const csrf = document.querySelector('meta[name=csrf-token]')?.content;

// Stats
const todayStats = ref({ count: 0, total_in: 0, total_out: 0 });

// Modals
const showOpenModal = ref(false);
const showCloseModal = ref(false);
const showTransactionModal = ref(false);
const showRegisterModal = ref(false);
const registerForm = ref({ name: '', type: 'principal' });
const transactionForm = ref({
    cash_register_id: '',
    type: 'encaissement',
    amount: '',
    payment_method: 'especes',
    category: '',
    description: '',
    reference: ''
});
const closeForm = ref({ observed_balance: '', notes: '' });

// ─── Helpers ──────────────────────────────────────────────
function formatCurrency(val) {
    if (val === null || val === undefined || isNaN(val)) return '0,00';
    return Number(val).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('fr-FR', {
        day: '2-digit', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
}

function formatShortDate(dateStr) {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('fr-FR', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
}

const paymentMethodLabel = (m) => ({
    especes: 'Espèces',
    momo: 'Mobile Money',
    carte: 'Carte bancaire',
    cheqye: 'Chèque',
    virement: 'Virement',
}[m] || m);

const paymentMethodIcon = (m) => ({
    especes: 'bi-cash',
    momo: 'bi-phone',
    carte: 'bi-credit-card',
    cheque: 'bi-file-text',
    virement: 'bi-arrow-left-right',
}[m] || 'bi-circle');

const transactionTypeIcon = (t) => t === 'encaissement' ? 'bi-arrow-down-circle text-success' : 'bi-arrow-up-circle text-danger';

// ─── API ───────────────────────────────────────────────────
async function loadData() {
    loading.value = true;
    error.value = null;
    try {
        const [regRes, txRes, statsRes] = await Promise.all([
            fetch('/api/company/caisse/registers', { headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf } }),
            fetch('/api/company/caisse/transactions?today=1', { headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf } }),
            fetch('/api/company/caisse/stats', { headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf } }),
        ]);
        if (regRes.ok) registers.value = (await regRes.json()).registers || [];
        if (txRes.ok) transactions.value = (await txRes.json()).transactions || [];
        if (statsRes.ok) todayStats.value = (await statsRes.json()).stats || { count: 0, total_in: 0, total_out: 0 };

        if (registers.value.length > 0 && !currentRegister.value) {
            currentRegister.value = registers.value[0];
            transactionForm.value.cash_register_id = currentRegister.value.id;
        }
    } catch (e) {
        error.value = 'Erreur chargement des données';
        console.error(e);
    } finally {
        loading.value = false;
    }
}

async function openRegister() {
    try {
        const res = await fetch(`/api/company/caisse/${currentRegister.value.id}/open`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
            body: JSON.stringify({ opened_balance: 0 }),
        });
        if (res.ok) {
            showOpenModal.value = false;
            await loadData();
        } else {
            const d = await res.json();
            error.value = d.message || 'Erreur ouverture';
        }
    } catch (e) {
        error.value = 'Erreur ouverture caisse';
    }
}

async function closeRegister() {
    try {
        const res = await fetch(`/api/company/caisse/${currentRegister.value.id}/close`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
            body: JSON.stringify(closeForm.value),
        });
        if (res.ok) {
            showCloseModal.value = false;
            closeForm.value = { observed_balance: '', notes: '' };
            await loadData();
        } else {
            const d = await res.json();
            error.value = d.message || 'Erreur clôture';
        }
    } catch (e) {
        error.value = 'Erreur clôture caisse';
    }
}

async function createTransaction() {
    try {
        const res = await fetch('/api/company/caisse/transactions', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
            body: JSON.stringify(transactionForm.value),
        });
        if (res.ok) {
            showTransactionModal.value = false;
            transactionForm.value = {
                cash_register_id: currentRegister.value?.id || '',
                type: 'encaissement',
                amount: '',
                payment_method: 'especes',
                category: '',
                description: '',
                reference: ''
            };
            await loadData();
        } else {
            const d = await res.json();
            error.value = d.message || "Erreur d'enregistrement";
        }
    } catch (e) {
        error.value = "Erreur d'enregistrement";
    }
}

async function createRegister() {
    try {
        const res = await fetch('/api/company/caisse/registers', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
            body: JSON.stringify(registerForm.value),
        });
        if (res.ok) {
            showRegisterModal.value = false;
            registerForm.value = { name: '', type: 'principal' };
            await loadData();
        } else {
            const d = await res.json();
            error.value = d.message || "Erreur création caisse";
        }
    } catch (e) {
        error.value = "Erreur création caisse";
    }
}

function selectRegister(reg) {
    currentRegister.value = reg;
    transactionForm.value.cash_register_id = reg.id;
}

function openCloseModal() {
    closeForm.value.observed_balance = currentRegister.value?.balance || 0;
    showCloseModal.value = true;
}

// ─── Lifecycle ────────────────────────────────────────────
onMounted(() => {
    loadData();
});
</script>

<template>
    <CompanyLayout page-title="Caisse">
        <!-- Loading -->
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
        </div>

        <template v-else>
            <!-- Error Alert -->
            <div v-if="error" class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm" role="alert">
                <i class="bi-exclamation-triangle me-2"></i> {{ error }}
                <button type="button" class="btn-close" @click="error = null"></button>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs border-0 mb-4 gap-2">
                <li class="nav-item" v-for="tab in [
                    { key: 'dashboard', label: 'Tableau de bord', icon: 'bi-speedometer2' },
                    { key: 'transactions', label: 'Transactions', icon: 'bi-list-ul' },
                    { key: 'registers', label: 'Caisses', icon: 'bi-cash-stack' },
                ]" :key="tab.key">
                    <button class="nav-link rounded-3" :class="{ active: activeTab === tab.key }"
                            @click="activeTab = tab.key">
                        <i :class="tab.icon" class="me-1"></i> {{ tab.label }}
                    </button>
                </li>
            </ul>

            <!-- ═══════ DASHBOARD ═══════ -->
            <div v-if="activeTab === 'dashboard'">
                <!-- Stats Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card card-dashboard h-100 border-0 rounded-4 shadow-sm">
                            <div class="card-body p-3 d-flex align-items-center gap-3">
                                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="bi-cash-stack"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Solde actuel</div>
                                    <div class="fw-bold fs-5">{{ formatCurrency(currentRegister?.balance || 0) }} FCFA</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-dashboard h-100 border-0 rounded-4 shadow-sm">
                            <div class="card-body p-3 d-flex align-items-center gap-3">
                                <div class="stat-icon bg-success bg-opacity-10 text-success">
                                    <i class="bi-arrow-down-circle"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Encaissements (auj.)</div>
                                    <div class="fw-bold fs-5">{{ formatCurrency(todayStats.total_in) }} FCFA</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-dashboard h-100 border-0 rounded-4 shadow-sm">
                            <div class="card-body p-3 d-flex align-items-center gap-3">
                                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                                    <i class="bi-arrow-up-circle"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Décaissements (auj.)</div>
                                    <div class="fw-bold fs-5">{{ formatCurrency(todayStats.total_out) }} FCFA</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-dashboard h-100 border-0 rounded-4 shadow-sm">
                            <div class="card-body p-3 d-flex align-items-center gap-3">
                                <div class="stat-icon bg-info bg-opacity-10 text-info">
                                    <i class="bi-arrow-left-right"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Transactions (auj.)</div>
                                    <div class="fw-bold fs-5">{{ todayStats.count }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Register Selector & Actions -->
                <div class="card border-0 rounded-4 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <label class="fw-semibold">Caisse :</label>
                            <select class="form-select w-auto" v-model="currentRegister" @change="selectRegister(currentRegister)">
                                <option v-for="r in registers" :key="r.id" :value="r">
                                    {{ r.name }} ({{ r.code }})
                                </option>
                            </select>
                            <span class="badge" :class="currentRegister?.is_open ? 'bg-success' : 'bg-secondary'">
                                {{ currentRegister?.is_open ? 'Ouverte' : 'Fermée' }}
                            </span>
                            <div class="ms-auto d-flex gap-2">
                                <button v-if="currentRegister && !currentRegister.is_open"
                                        class="btn btn-success rounded-3" @click="showOpenModal = true">
                                    <i class="bi-unlock me-1"></i> Ouvrir
                                </button>
                                <button v-if="currentRegister && currentRegister.is_open"
                                        class="btn btn-warning rounded-3" @click="openCloseModal()">
                                    <i class="bi-lock me-1"></i> Clôturer
                                </button>
                                <button class="btn btn-primary rounded-3" @click="showTransactionModal = true">
                                    <i class="bi-plus-lg me-1"></i> Nouvelle transaction
                                </button>
                            </div>
                        </div>
                        <div v-if="currentRegister" class="mt-3 text-muted small">
                            <i class="bi-clock me-1"></i>
                            Dernière ouverture : {{ formatShortDate(currentRegister.last_opened_at) }}
                            <span v-if="currentRegister.last_closed_at" class="ms-3">
                                <i class="bi-clock-history me-1"></i>
                                Dernière clôture : {{ formatShortDate(currentRegister.last_closed_at) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="card border-0 rounded-4 shadow-sm">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0">Dernières transactions</h5>
                    </div>
                    <div class="card-body p-0">
                        <div v-if="transactions.length === 0" class="text-center py-4 text-muted">
                            <i class="bi-inbox" style="font-size: 2rem;"></i>
                            <p class="mt-2">Aucune transaction aujourd'hui</p>
                        </div>
                        <div v-else class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Heure</th>
                                        <th>Type</th>
                                        <th>Montant</th>
                                        <th>Mode</th>
                                        <th>Catégorie</th>
                                        <th>Référence</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="t in transactions.slice(0, 10)" :key="t.id">
                                        <td class="text-nowrap small">{{ formatShortDate(t.transaction_date || t.created_at) }}</td>
                                        <td>
                                            <span class="badge" :class="t.type === 'encaissement' ? 'bg-success' : 'bg-danger'">
                                                <i :class="transactionTypeIcon(t.type)" class="me-1"></i>
                                                {{ t.type === 'encaissement' ? 'Encaissement' : 'Décaissement' }}
                                            </span>
                                        </td>
                                        <td class="fw-bold">{{ formatCurrency(t.amount) }}</td>
                                        <td><i :class="paymentMethodIcon(t.payment_method)" class="me-1"></i>{{ paymentMethodLabel(t.payment_method) }}</td>
                                        <td>{{ t.category || '-' }}</td>
                                        <td class="small">{{ t.reference || '-' }}</td>
                                        <td class="small text-muted">{{ t.description || '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══════ TRANSACTIONS ═══════ -->
            <div v-if="activeTab === 'transactions'">
                <div class="card border-0 rounded-4 shadow-sm">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Historique des transactions</h5>
                        <button class="btn btn-primary rounded-3" @click="showTransactionModal = true">
                            <i class="bi-plus-lg me-1"></i> Nouvelle transaction
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div v-if="transactions.length === 0" class="text-center py-5 text-muted">
                            <i class="bi-inbox" style="font-size: 3rem;"></i>
                            <p class="mt-2">Aucune transaction</p>
                        </div>
                        <div v-else class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Caisse</th>
                                        <th>Type</th>
                                        <th>Montant</th>
                                        <th>Mode</th>
                                        <th>Catégorie</th>
                                        <th>Référence</th>
                                        <th>Description</th>
                                        <th>Opérateur</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="t in transactions" :key="t.id">
                                        <td class="text-nowrap small">{{ formatShortDate(t.transaction_date || t.created_at) }}</td>
                                        <td>{{ t.cash_register_name || '-' }}</td>
                                        <td>
                                            <span class="badge" :class="t.type === 'encaissement' ? 'bg-success' : 'bg-danger'">
                                                {{ t.type === 'encaissement' ? 'Encaissement' : 'Décaissement' }}
                                            </span>
                                        </td>
                                        <td class="fw-bold">{{ formatCurrency(t.amount) }} FCFA</td>
                                        <td><i :class="paymentMethodIcon(t.payment_method)" class="me-1"></i>{{ paymentMethodLabel(t.payment_method) }}</td>
                                        <td>{{ t.category || '-' }}</td>
                                        <td class="small">{{ t.reference || '-' }}</td>
                                        <td class="small text-muted">{{ t.description || '-' }}</td>
                                        <td class="small">{{ t.user_name || '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══════ REGISTERS ═══════ -->
            <div v-if="activeTab === 'registers'">
                <div class="card border-0 rounded-4 shadow-sm">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Caisses</h5>
                        <button v-if="authStore.can('caisse', 'gerer')" class="btn btn-primary rounded-3" @click="showRegisterModal = true">
                            <i class="bi-plus-lg me-1"></i> Nouvelle caisse
                        </button>
                    </div>
                    <div class="card-body p-4">
                        <div v-if="registers.length === 0" class="text-center py-4 text-muted">
                            <i class="bi-cash-stack" style="font-size: 2rem;"></i>
                            <p class="mt-2">Aucune caisse configurée</p>
                        </div>
                        <div v-else class="row g-3">
                            <div v-for="r in registers" :key="r.id" class="col-md-6">
                                <div class="card border rounded-4 h-100"
                                     :class="r.is_open ? 'border-success' : ''"
                                     @click="selectRegister(r); activeTab = 'dashboard'"
                                     style="cursor: pointer;">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="fw-bold mb-1">{{ r.name }}</h6>
                                                <span class="small text-muted">{{ r.code }}</span>
                                            </div>
                                            <span class="badge rounded-pill" :class="r.is_open ? 'bg-success' : 'bg-secondary'">
                                                {{ r.is_open ? 'Ouverte' : 'Fermée' }}
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between mt-3">
                                            <div>
                                                <div class="text-muted small">Type</div>
                                                <div class="fw-semibold">{{ r.type === 'principal' ? 'Principale' : 'Auxiliaire' }}</div>
                                            </div>
                                            <div class="text-end">
                                                <div class="text-muted small">Solde</div>
                                                <div class="fw-bold fs-5">{{ formatCurrency(r.balance) }} FCFA</div>
                                            </div>
                                        </div>
                                        <div class="mt-2 text-muted small">
                                            <i class="bi-clock me-1"></i>
                                            {{ r.last_opened_at ? 'Ouvert le ' + formatShortDate(r.last_opened_at) : 'Jamais ouvert' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- ─── MODALS ──────────────────────────────────────────── -->

        <!-- Open Register Modal -->
        <div class="modal fade" :class="{ show: showOpenModal }" :style="{ display: showOpenModal ? 'block' : 'none' }"
             tabindex="-1" @click.self="showOpenModal = false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold"><i class="bi-unlock me-2 text-success"></i>Ouverture de caisse</h5>
                        <button type="button" class="btn-close" @click="showOpenModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <p>Voulez-vous ouvrir la caisse <strong>{{ currentRegister?.name }}</strong> ?</p>
                        <div class="alert alert-info rounded-3">
                            <i class="bi-info-circle me-2"></i> Le solde d'ouverture sera de 0 FCFA.
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button class="btn btn-light rounded-3" @click="showOpenModal = false">Annuler</button>
                        <button class="btn btn-success rounded-3" @click="openRegister">
                            <i class="bi-unlock me-1"></i> Ouvrir la caisse
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Close Register Modal -->
        <div class="modal fade" :class="{ show: showCloseModal }" :style="{ display: showCloseModal ? 'block' : 'none' }"
             tabindex="-1" @click.self="showCloseModal = false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold"><i class="bi-lock me-2 text-warning"></i>Clôture de caisse</h5>
                        <button type="button" class="btn-close" @click="showCloseModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Solde théorique</label>
                            <div class="form-control bg-light fw-bold fs-5">{{ formatCurrency(currentRegister?.balance || 0) }} FCFA</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Solde réel observé <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" v-model.number="closeForm.observed_balance"
                                   placeholder="Saisir le solde compté dans la caisse" step="0.01">
                        </div>
                        <div v-if="closeForm.observed_balance !== '' && closeForm.observed_balance !== null" class="mb-3">
                            <div class="alert" :class="(closeForm.observed_balance - (currentRegister?.balance || 0)) === 0 ? 'alert-success' : 'alert-warning'">
                                <strong>Écart :</strong>
                                {{ formatCurrency(closeForm.observed_balance - (currentRegister?.balance || 0)) }} FCFA
                                <span v-if="(closeForm.observed_balance - (currentRegister?.balance || 0)) === 0">
                                    <i class="bi-check-circle ms-2"></i> Aucun écart
                                </span>
                                <span v-else>
                                    <i class="bi-exclamation-triangle ms-2"></i> Écart détecté
                                </span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Notes / Observations</label>
                            <textarea class="form-control" v-model="closeForm.notes" rows="2"
                                      placeholder="Commentaires éventuels..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button class="btn btn-light rounded-3" @click="showCloseModal = false">Annuler</button>
                        <button class="btn btn-warning rounded-3" @click="closeRegister">
                            <i class="bi-lock me-1"></i> Clôturer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Modal -->
        <div class="modal fade" :class="{ show: showTransactionModal }" :style="{ display: showTransactionModal ? 'block' : 'none' }"
             tabindex="-1" @click.self="showTransactionModal = false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi-plus-circle me-2 text-primary"></i>Nouvelle transaction
                        </h5>
                        <button type="button" class="btn-close" @click="showTransactionModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" id="tx-in" value="encaissement" v-model="transactionForm.type">
                                    <label class="btn btn-outline-success rounded-3 me-2" for="tx-in">
                                        <i class="bi-arrow-down-circle me-1"></i> Encaissement
                                    </label>
                                    <input type="radio" class="btn-check" id="tx-out" value="decaissement" v-model="transactionForm.type">
                                    <label class="btn btn-outline-danger rounded-3" for="tx-out">
                                        <i class="bi-arrow-up-circle me-1"></i> Décaissement
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Caisse <span class="text-danger">*</span></label>
                                <select class="form-select" v-model="transactionForm.cash_register_id">
                                    <option v-for="r in registers" :key="r.id" :value="r.id">{{ r.name }}</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Montant <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" v-model="transactionForm.amount"
                                           placeholder="0" step="0.01" min="0">
                                    <span class="input-group-text">FCFA</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Mode de paiement</label>
                                <select class="form-select" v-model="transactionForm.payment_method">
                                    <option value="especes">Espèces</option>
                                    <option value="momo">Mobile Money</option>
                                    <option value="carte">Carte bancaire</option>
                                    <option value="cheque">Chèque</option>
                                    <option value="virement">Virement</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Catégorie</label>
                                <select class="form-select" v-model="transactionForm.category">
                                    <option value="">-- Choisir --</option>
                                    <option value="vente">Vente</option>
                                    <option value="service">Prestation de service</option>
                                    <option value="avance">Avance / Acompte</option>
                                    <option value="remboursement">Remboursement</option>
                                    <option value="fournisseur">Paiement fournisseur</option>
                                    <option value="salaire">Salaire</option>
                                    <option value="frais">Frais généraux</option>
                                    <option value="autre">Autre</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Référence</label>
                                <input type="text" class="form-control" v-model="transactionForm.reference"
                                       placeholder="N° facture, N° reçu...">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea class="form-control" v-model="transactionForm.description" rows="2"
                                          placeholder="Motif de la transaction..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button class="btn btn-light rounded-3" @click="showTransactionModal = false">Annuler</button>
                        <button class="btn btn-primary rounded-3" @click="createTransaction">
                            <i class="bi-check-lg me-1"></i> Enregistrer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Register Modal -->
        <div class="modal fade" :class="{ show: showRegisterModal }" :style="{ display: showRegisterModal ? 'block' : 'none' }"
             tabindex="-1" @click.self="showRegisterModal = false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold"><i class="bi-plus-circle me-2 text-primary"></i>Nouvelle caisse</h5>
                        <button type="button" class="btn-close" @click="showRegisterModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" v-model="registerForm.name"
                                   placeholder="Ex: Caisse principale, Caisse auxiliaire...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Type</label>
                            <select class="form-select" v-model="registerForm.type">
                                <option value="principal">Principale</option>
                                <option value="auxiliaire">Auxiliaire</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button class="btn btn-light rounded-3" @click="showRegisterModal = false">Annuler</button>
                        <button class="btn btn-primary rounded-3" @click="createRegister">
                            <i class="bi-check-lg me-1"></i> Créer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backdrop for modals -->
        <div v-if="showOpenModal || showCloseModal || showTransactionModal || showRegisterModal"
             class="modal-backdrop fade show"></div>
    </CompanyLayout>
</template>

<style scoped>
.nav-tabs .nav-link {
    border: 1px solid #dee2e6;
    color: #495057;
    padding: 0.5rem 1.25rem;
    font-weight: 500;
    transition: all 0.2s;
}
.nav-tabs .nav-link:hover {
    background: #f0f0f0;
}
.nav-tabs .nav-link.active {
    background: var(--gel-primary, #1a237e);
    color: white;
    border-color: var(--gel-primary, #1a237e);
}
.card-dashboard {
    transition: transform 0.2s, box-shadow 0.2s;
}
.card-dashboard:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}
.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}
.table th {
    font-weight: 600;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6c757d;
}
</style>
