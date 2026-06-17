<script setup>
import { ref, computed, onMounted } from 'vue';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';
import { authStore } from '../../stores/auth';

const registers = ref([]);
const transactions = ref([]);
const currentRegister = ref(null);
const loading = ref(true);
const error = ref(null);
const successMsg = ref('');
const activeTab = ref('dashboard');
const csrf = document.querySelector('meta[name=csrf-token]')?.content;

const todayStats = ref({ count: 0, total_in: 0, total_out: 0 });

const showOpenModal = ref(false);
const showCloseModal = ref(false);
const showTransactionModal = ref(false);
const showRegisterModal = ref(false);
const registerForm = ref({ name: '', type: 'principal' });
const transactionForm = ref({ cash_register_id: '', type: 'encaissement', amount: '', payment_method: 'especes', category: '', description: '', reference: '' });
const closeForm = ref({ observed_balance: '', notes: '' });

function formatCurrency(val) {
    if (val === null || val === undefined || isNaN(val)) return '0,00';
    return Number(val).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatShortDate(dateStr) {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

const paymentMethodLabel = (m) => ({ especes:'Espèces', momo:'Mobile Money', carte:'Carte bancaire', cheque:'Chèque', virement:'Virement' }[m]||m);
const paymentMethodIcon = (m) => ({ especes:'bi-cash', momo:'bi-phone', carte:'bi-credit-card', cheque:'bi-file-text', virement:'bi-arrow-left-right' }[m]||'bi-circle');
const categoryLabel = (c) => ({ vente:'Vente', service:'Prestation', avance:'Avance', remboursement:'Remboursement', fournisseur:'Fournisseur', salaire:'Salaire', frais:'Frais généraux', autre:'Autre' }[c]||c);

async function loadData() {
    loading.value = true; error.value = null;
    try {
        const [regRes, txRes, statsRes] = await Promise.all([
            fetch('/api/company/caisse/registers', { headers: { Accept:'application/json', 'X-CSRF-TOKEN':csrf } }),
            fetch('/api/company/caisse/transactions?today=1', { headers: { Accept:'application/json', 'X-CSRF-TOKEN':csrf } }),
            fetch('/api/company/caisse/stats', { headers: { Accept:'application/json', 'X-CSRF-TOKEN':csrf } }),
        ]);
        if (regRes.ok) registers.value = (await regRes.json()).registers || [];
        if (txRes.ok) transactions.value = (await txRes.json()).transactions || [];
        if (statsRes.ok) todayStats.value = (await statsRes.json()).stats || { count:0, total_in:0, total_out:0 };
        if (registers.value.length > 0 && !currentRegister.value) {
            currentRegister.value = registers.value[0];
            transactionForm.value.cash_register_id = currentRegister.value.id;
        }
    } catch (e) { error.value = 'Erreur chargement des données'; console.error(e); }
    finally { loading.value = false; }
}

async function openRegister() {
    try {
        const res = await fetch(`/api/company/caisse/${currentRegister.value.id}/open`, {
            method:'POST', headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN':csrf, Accept:'application/json' },
            body: JSON.stringify({ opened_balance:0 }),
        });
        if (res.ok) { showOpenModal.value = false; successMsg.value = 'Caisse ouverte'; await loadData(); setTimeout(()=>successMsg.value='',3000); }
        else { const d=await res.json(); error.value = d.message||'Erreur ouverture'; }
    } catch (e) { error.value = 'Erreur ouverture caisse'; }
}

async function closeRegister() {
    try {
        const res = await fetch(`/api/company/caisse/${currentRegister.value.id}/close`, {
            method:'POST', headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN':csrf, Accept:'application/json' },
            body: JSON.stringify(closeForm.value),
        });
        if (res.ok) { showCloseModal.value = false; closeForm.value = { observed_balance:'', notes:'' }; successMsg.value = 'Caisse clôturée'; await loadData(); setTimeout(()=>successMsg.value='',3000); }
        else { const d=await res.json(); error.value = d.message||'Erreur clôture'; }
    } catch (e) { error.value = 'Erreur clôture caisse'; }
}

async function createTransaction() {
    try {
        const res = await fetch('/api/company/caisse/transactions', {
            method:'POST', headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN':csrf, Accept:'application/json' },
            body: JSON.stringify(transactionForm.value),
        });
        if (res.ok) {
            showTransactionModal.value = false;
            transactionForm.value = { cash_register_id:currentRegister.value?.id||'', type:'encaissement', amount:'', payment_method:'especes', category:'', description:'', reference:'' };
            successMsg.value = 'Transaction enregistrée'; await loadData(); setTimeout(()=>successMsg.value='',3000);
        } else { const d=await res.json(); error.value = d.message||"Erreur d'enregistrement"; }
    } catch (e) { error.value = "Erreur d'enregistrement"; }
}

async function createRegister() {
    try {
        const res = await fetch('/api/company/caisse/registers', {
            method:'POST', headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN':csrf, Accept:'application/json' },
            body: JSON.stringify(registerForm.value),
        });
        if (res.ok) { showRegisterModal.value = false; registerForm.value = { name:'', type:'principal' }; successMsg.value = 'Caisse créée'; await loadData(); setTimeout(()=>successMsg.value='',3000); }
        else { const d=await res.json(); error.value = d.message||"Erreur création"; }
    } catch (e) { error.value = "Erreur création"; }
}

function selectRegister(reg) { currentRegister.value = reg; transactionForm.value.cash_register_id = reg.id; }

function openCloseModal() { closeForm.value.observed_balance = currentRegister.value?.balance || 0; showCloseModal.value = true; }

const ecart = computed(() => {
    if (closeForm.value.observed_balance === '' || closeForm.value.observed_balance === null) return null;
    return Number(closeForm.value.observed_balance) - Number(currentRegister.value?.balance || 0);
});

onMounted(() => { loadData(); });
</script>

<template>
    <CompanyLayout page-title="Caisse">
        <div v-if="loading" class="d-flex align-items-center justify-content-center gap-3 py-5">
            <div class="isup-spinner"></div>
            <span style="color:#888;font-size:14px;">Chargement…</span>
        </div>

        <template v-else>
            <div v-if="successMsg" class="isup-alert-success mb-2">{{ successMsg }}</div>

            <div class="isup-shell">
                <div class="isup-portal-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="isup-portal-logo"><i class="bi-cash-stack" style="font-size:20px;"></i></div>
                        <div>
                            <div class="isup-portal-company">Caisse</div>
                            <div class="isup-portal-sub">Gestion des encaissements et décaissements</div>
                        </div>
                    </div>
                </div>

                <div class="p-3">
                    <div class="isup-tabs mb-3">
                        <button class="isup-tab" :class="{ active: activeTab === 'dashboard' }" @click="activeTab='dashboard'"><i class="bi-speedometer2 me-1"></i> Tableau de bord</button>
                        <button class="isup-tab" :class="{ active: activeTab === 'transactions' }" @click="activeTab='transactions'"><i class="bi-list-ul me-1"></i> Transactions</button>
                        <button class="isup-tab" :class="{ active: activeTab === 'registers' }" @click="activeTab='registers'"><i class="bi-cash-stack me-1"></i> Caisses</button>
                    </div>

                    <!-- ═══════ DASHBOARD ═══════ -->
                    <div v-if="activeTab === 'dashboard'">
                        <div class="row g-3 mb-4">
                            <div class="col-md-3"><div class="isup-stat-card"><div class="isup-stat-icon isup-stat-blue"><i class="bi-cash-stack"></i></div><div><div class="isup-stat-label">Solde actuel</div><div class="isup-stat-num">{{ formatCurrency(currentRegister?.balance||0) }}</div></div></div></div>
                            <div class="col-md-3"><div class="isup-stat-card"><div class="isup-stat-icon isup-stat-green"><i class="bi-arrow-down-circle"></i></div><div><div class="isup-stat-label">Encaissements (auj.)</div><div class="isup-stat-num">{{ formatCurrency(todayStats.total_in) }}</div></div></div></div>
                            <div class="col-md-3"><div class="isup-stat-card"><div class="isup-stat-icon isup-stat-red"><i class="bi-arrow-up-circle"></i></div><div><div class="isup-stat-label">Décaissements (auj.)</div><div class="isup-stat-num">{{ formatCurrency(todayStats.total_out) }}</div></div></div></div>
                            <div class="col-md-3"><div class="isup-stat-card"><div class="isup-stat-icon isup-stat-cyan"><i class="bi-arrow-left-right"></i></div><div><div class="isup-stat-label">Transactions (auj.)</div><div class="isup-stat-num">{{ todayStats.count }}</div></div></div></div>
                        </div>

                        <div class="isup-panel mb-4">
                            <div class="isup-panel-header"><i class="bi-gear me-2" style="color:#FF7900;"></i>Sélection de caisse</div>
                            <div class="isup-panel-body">
                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    <label class="isup-label" style="margin:0;">Caisse :</label>
                                    <select class="isup-select" style="width:auto;min-width:220px;" v-model="currentRegister" @change="selectRegister(currentRegister)">
                                        <option v-for="r in registers" :key="r.id" :value="r">{{ r.name }} ({{ r.code }})</option>
                                    </select>
                                    <span class="isup-status" :class="currentRegister?.is_open ? 'isup-status-green' : 'isup-status-red'"><i :class="currentRegister?.is_open?'bi-unlock':'bi-lock'" class="me-1"></i>{{ currentRegister?.is_open ? 'Ouverte' : 'Fermée' }}</span>
                                    <div class="ms-auto d-flex gap-2">
                                        <button v-if="currentRegister && !currentRegister.is_open" class="isup-btn-primary" style="background:#2e7d32;" @click="showOpenModal=true"><i class="bi-unlock me-1"></i> Ouvrir</button>
                                        <button v-if="currentRegister && currentRegister.is_open" class="isup-btn-primary" style="background:#e65100;" @click="openCloseModal()"><i class="bi-lock me-1"></i> Clôturer</button>
                                        <button class="isup-btn-primary" @click="showTransactionModal=true"><i class="bi-plus-lg me-1"></i> Transaction</button>
                                    </div>
                                </div>
                                <div v-if="currentRegister" class="small mt-2" style="color:#888;font-size:11px;">
                                    <i class="bi-clock me-1"></i>Dernière ouverture : {{ formatShortDate(currentRegister.last_opened_at) }}
                                    <span v-if="currentRegister.last_closed_at" class="ms-3"><i class="bi-clock-history me-1"></i>Dernière clôture : {{ formatShortDate(currentRegister.last_closed_at) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="isup-panel">
                            <div class="isup-panel-header"><i class="bi-clock-history me-2" style="color:#FF7900;"></i>Dernières transactions</div>
                            <div class="isup-panel-body p-0">
                                <div v-if="transactions.length === 0" class="text-center py-4 isup-empty-cell"><i class="bi-inbox" style="font-size:2rem;color:#dce3ee;display:block;margin-bottom:8px;"></i>Aucune transaction aujourd'hui</div>
                                <div v-else class="isup-table-wrap">
                                    <table class="isup-table w-100">
                                        <thead><tr><th>Heure</th><th>Type</th><th>Montant</th><th>Mode</th><th>Catégorie</th><th>Référence</th><th>Description</th></tr></thead>
                                        <tbody>
                                            <tr v-for="t in transactions.slice(0,10)" :key="t.id">
                                                <td style="font-size:11px;white-space:nowrap;">{{ formatShortDate(t.transaction_date||t.created_at) }}</td>
                                                <td><span class="isup-status" :class="t.type==='encaissement'?'isup-status-green':'isup-status-red'"><i :class="t.type==='encaissement'?'bi-arrow-down-circle':'bi-arrow-up-circle'" class="me-1"></i>{{ t.type==='encaissement'?'Encaissement':'Décaissement' }}</span></td>
                                                <td style="font-weight:700;">{{ formatCurrency(t.amount) }}</td>
                                                <td style="font-size:12px;"><i :class="paymentMethodIcon(t.payment_method)" class="me-1"></i>{{ paymentMethodLabel(t.payment_method) }}</td>
                                                <td style="font-size:12px;">{{ t.category||'-' }}</td>
                                                <td style="font-size:11px;">{{ t.reference||'-' }}</td>
                                                <td style="font-size:11px;color:#888;">{{ t.description||'-' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ═══════ TRANSACTIONS ═══════ -->
                    <div v-if="activeTab === 'transactions'">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span style="font-size:12px;color:#888;">{{ transactions.length }} transaction(s)</span>
                            <button class="isup-btn-primary" @click="showTransactionModal=true"><i class="bi-plus-lg me-1"></i> Nouvelle transaction</button>
                        </div>
                        <div class="isup-panel">
                            <div class="isup-panel-header"><i class="bi-list-ul me-2" style="color:#FF7900;"></i>Historique</div>
                            <div class="isup-panel-body p-0">
                                <div v-if="transactions.length === 0" class="text-center py-5 isup-empty-cell"><i class="bi-inbox" style="font-size:3rem;color:#dce3ee;display:block;margin-bottom:8px;"></i>Aucune transaction</div>
                                <div v-else class="isup-table-wrap">
                                    <table class="isup-table w-100">
                                        <thead><tr><th>Date</th><th>Caisse</th><th>Type</th><th>Montant</th><th>Mode</th><th>Catégorie</th><th>Référence</th><th>Description</th><th>Opérateur</th></tr></thead>
                                        <tbody>
                                            <tr v-for="t in transactions" :key="t.id">
                                                <td style="font-size:11px;white-space:nowrap;">{{ formatShortDate(t.transaction_date||t.created_at) }}</td>
                                                <td style="font-size:12px;">{{ t.cash_register_name||'-' }}</td>
                                                <td><span class="isup-status" :class="t.type==='encaissement'?'isup-status-green':'isup-status-red'">{{ t.type==='encaissement'?'Encaissement':'Décaissement' }}</span></td>
                                                <td style="font-weight:700;">{{ formatCurrency(t.amount) }}</td>
                                                <td style="font-size:12px;"><i :class="paymentMethodIcon(t.payment_method)" class="me-1"></i>{{ paymentMethodLabel(t.payment_method) }}</td>
                                                <td style="font-size:12px;">{{ t.category||'-' }}</td>
                                                <td style="font-size:11px;">{{ t.reference||'-' }}</td>
                                                <td style="font-size:11px;color:#888;">{{ t.description||'-' }}</td>
                                                <td style="font-size:11px;">{{ t.user_name||'-' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ═══════ REGISTERS ═══════ -->
                    <div v-if="activeTab === 'registers'">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span style="font-size:12px;color:#888;">{{ registers.length }} caisse(s)</span>
                            <button v-if="authStore.can('caisse','gerer')" class="isup-btn-primary" @click="showRegisterModal=true"><i class="bi-plus-lg me-1"></i> Nouvelle caisse</button>
                        </div>
                        <div v-if="registers.length === 0" class="isup-panel"><div class="isup-panel-body text-center py-4" style="color:#aaa;"><i class="bi-cash-stack" style="font-size:2rem;display:block;margin-bottom:8px;"></i>Aucune caisse configurée</div></div>
                        <div v-else class="row g-3">
                            <div v-for="r in registers" :key="r.id" class="col-md-6">
                                <div class="isup-reg-card" :class="{ 'isup-reg-open': r.is_open }" @click="selectRegister(r); activeTab='dashboard'" style="cursor:pointer;">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div><div style="font-weight:700;color:#163A5E;font-size:14px;">{{ r.name }}</div><small style="color:#888;">{{ r.code }}</small></div>
                                        <span class="isup-status" :class="r.is_open ? 'isup-status-green' : 'isup-status-red'"><i :class="r.is_open?'bi-unlock':'bi-lock'" class="me-1"></i>{{ r.is_open?'Ouverte':'Fermée' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mt-3">
                                        <div><div class="isup-stat-label">Type</div><div style="font-size:13px;font-weight:600;color:#333;">{{ r.type==='principal'?'Principale':'Auxiliaire' }}</div></div>
                                        <div class="text-end"><div class="isup-stat-label">Solde</div><div style="font-size:17px;font-weight:800;color:#163A5E;">{{ formatCurrency(r.balance) }}</div></div>
                                    </div>
                                    <div class="small mt-2" style="color:#aaa;font-size:11px;"><i class="bi-clock me-1"></i>{{ r.last_opened_at?'Ouvert le '+formatShortDate(r.last_opened_at):'Jamais ouvert' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ─── MODALS ─── -->

            <!-- Open Register -->
            <div v-if="showOpenModal" class="isup-modal-overlay" @click.self="showOpenModal=false">
                <div class="isup-modal">
                    <div class="isup-modal-header"><span><i class="bi-unlock me-2" style="color:#2e7d32;"></i>Ouverture de caisse</span><button class="isup-modal-close" @click="showOpenModal=false">&times;</button></div>
                    <div class="isup-modal-body">
                        <p>Voulez-vous ouvrir la caisse <strong>{{ currentRegister?.name }}</strong> ?</p>
                        <div class="isup-info-box"><i class="bi-info-circle me-2"></i>Le solde d'ouverture sera de 0 FCFA.</div>
                    </div>
                    <div class="isup-modal-footer">
                        <button class="isup-btn-grey" @click="showOpenModal=false">Annuler</button>
                        <button class="isup-btn-primary" style="background:#2e7d32;" @click="openRegister"><i class="bi-unlock me-1"></i> Ouvrir la caisse</button>
                    </div>
                </div>
            </div>

            <!-- Close Register -->
            <div v-if="showCloseModal" class="isup-modal-overlay" @click.self="showCloseModal=false">
                <div class="isup-modal isup-modal-lg">
                    <div class="isup-modal-header"><span><i class="bi-lock me-2" style="color:#e65100;"></i>Clôture de caisse</span><button class="isup-modal-close" @click="showCloseModal=false">&times;</button></div>
                    <div class="isup-modal-body">
                        <div class="mb-3"><label class="isup-label">Solde théorique</label><div class="isup-input" style="background:#f5f5f5;font-weight:700;font-size:18px;color:#163A5E;">{{ formatCurrency(currentRegister?.balance||0) }} FCFA</div></div>
                        <div class="mb-3"><label class="isup-label">Solde réel observé <span style="color:#c62828;">*</span></label><input type="number" class="isup-input" v-model.number="closeForm.observed_balance" placeholder="Saisir le solde compté dans la caisse" step="0.01"></div>
                        <div v-if="ecart !== null" class="mb-3"><div class="isup-alert" :class="ecart===0?'isup-alert-success':'isup-alert-warning'"><strong>Écart :</strong> {{ formatCurrency(ecart) }} FCFA <span v-if="ecart===0"><i class="bi-check-circle ms-2"></i>Aucun écart</span><span v-else><i class="bi-exclamation-triangle ms-2"></i>Écart détecté</span></div></div>
                        <div class="mb-3"><label class="isup-label">Notes / Observations</label><textarea class="isup-input" rows="2" v-model="closeForm.notes" placeholder="Commentaires éventuels..."></textarea></div>
                    </div>
                    <div class="isup-modal-footer">
                        <button class="isup-btn-grey" @click="showCloseModal=false">Annuler</button>
                        <button class="isup-btn-primary" style="background:#e65100;" @click="closeRegister"><i class="bi-lock me-1"></i> Clôturer</button>
                    </div>
                </div>
            </div>

            <!-- Transaction Modal -->
            <div v-if="showTransactionModal" class="isup-modal-overlay" @click.self="showTransactionModal=false">
                <div class="isup-modal isup-modal-lg">
                    <div class="isup-modal-header"><span><i class="bi-plus-circle me-2"></i>Nouvelle transaction</span><button class="isup-modal-close" @click="showTransactionModal=false">&times;</button></div>
                    <div class="isup-modal-body">
                        <div v-if="error" class="isup-alert-error mb-2">{{ error }}</div>
                        <div class="row g-2">
                            <div class="col-12"><label class="isup-label">Type <span style="color:#c62828;">*</span></label>
                                <div class="d-flex gap-2">
                                    <label class="isup-radio-btn" :class="{ active: transactionForm.type==='encaissement' }"><input type="radio" value="encaissement" v-model="transactionForm.type" class="d-none"><i class="bi-arrow-down-circle me-1"></i> Encaissement</label>
                                    <label class="isup-radio-btn" :class="{ active: transactionForm.type==='decaissement' }"><input type="radio" value="decaissement" v-model="transactionForm.type" class="d-none"><i class="bi-arrow-up-circle me-1"></i> Décaissement</label>
                                </div>
                            </div>
                            <div class="col-md-6"><label class="isup-label">Caisse <span style="color:#c62828;">*</span></label><select class="isup-select" v-model="transactionForm.cash_register_id"><option v-for="r in registers" :key="r.id" :value="r.id">{{ r.name }}</option></select></div>
                            <div class="col-md-6"><label class="isup-label">Montant <span style="color:#c62828;">*</span></label><input type="number" class="isup-input" v-model="transactionForm.amount" placeholder="0" step="0.01" min="0"><small style="color:#888;">FCFA</small></div>
                            <div class="col-md-6"><label class="isup-label">Mode de paiement</label><select class="isup-select" v-model="transactionForm.payment_method"><option value="especes">Espèces</option><option value="momo">Mobile Money</option><option value="carte">Carte bancaire</option><option value="cheque">Chèque</option><option value="virement">Virement</option></select></div>
                            <div class="col-md-6"><label class="isup-label">Catégorie</label><select class="isup-select" v-model="transactionForm.category"><option value="">-- Choisir --</option><option value="vente">Vente</option><option value="service">Prestation</option><option value="avance">Avance</option><option value="remboursement">Remboursement</option><option value="fournisseur">Fournisseur</option><option value="salaire">Salaire</option><option value="frais">Frais généraux</option><option value="autre">Autre</option></select></div>
                            <div class="col-md-6"><label class="isup-label">Référence</label><input type="text" class="isup-input" v-model="transactionForm.reference" placeholder="N° facture, N° reçu..."></div>
                            <div class="col-12"><label class="isup-label">Description</label><textarea class="isup-input" rows="2" v-model="transactionForm.description" placeholder="Motif de la transaction..."></textarea></div>
                        </div>
                    </div>
                    <div class="isup-modal-footer">
                        <button class="isup-btn-grey" @click="showTransactionModal=false">Annuler</button>
                        <button class="isup-btn-primary" @click="createTransaction"><i class="bi-check-lg me-1"></i> Enregistrer</button>
                    </div>
                </div>
            </div>

            <!-- New Register Modal -->
            <div v-if="showRegisterModal" class="isup-modal-overlay" @click.self="showRegisterModal=false">
                <div class="isup-modal">
                    <div class="isup-modal-header"><span><i class="bi-plus-circle me-2"></i>Nouvelle caisse</span><button class="isup-modal-close" @click="showRegisterModal=false">&times;</button></div>
                    <div class="isup-modal-body">
                        <div class="mb-3"><label class="isup-label">Nom <span style="color:#c62828;">*</span></label><input type="text" class="isup-input" v-model="registerForm.name" placeholder="Ex: Caisse principale"></div>
                        <div class="mb-3"><label class="isup-label">Type</label><select class="isup-select" v-model="registerForm.type"><option value="principal">Principale</option><option value="auxiliaire">Auxiliaire</option></select></div>
                    </div>
                    <div class="isup-modal-footer">
                        <button class="isup-btn-grey" @click="showRegisterModal=false">Annuler</button>
                        <button class="isup-btn-primary" @click="createRegister"><i class="bi-check-lg me-1"></i> Créer</button>
                    </div>
                </div>
            </div>
        </template>
    </CompanyLayout>
</template>

<style scoped>
/* ── Caisse-specific styles ── */
.isup-radio-btn { display:inline-flex;align-items:center;padding:8px 16px;border:1px solid #dce3ee;border-radius:4px;font-size:12px;font-weight:600;color:#888;cursor:pointer;transition:all .12s; }
.isup-radio-btn.active { background:#e8f5e9;color:#2e7d32;border-color:#c8e6c9; }
.isup-radio-btn:has(input[value="decaissement"]).active { background:#fdecea;color:#c62828;border-color:#f5c6c0; }
.isup-reg-card { background:#fff;border:1px solid #dce3ee;border-radius:6px;padding:16px;transition:box-shadow .12s; }
.isup-reg-card:hover { box-shadow:0 2px 10px rgba(0,0,0,.06); }
.isup-reg-open { border-left:3px solid #2e7d32; }
.isup-alert-warning { background:#fff3e0;border:1px solid #ffe0b2;color:#e65100;border-radius:4px;padding:8px 12px;font-size:12px; }
.isup-info-box { background:#e3f2fd;border:1px solid #bbdefb;color:#1565c0;border-radius:4px;padding:8px 12px;font-size:12px; }
</style>
