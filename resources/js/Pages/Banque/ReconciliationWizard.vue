<template>
    <div class="container-fluid py-3">
        <div class="d-flex align-items-center gap-2 mb-3">
            <a :href="'/comptabilite/banque'" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
            <h4 class="fw-bold mb-0"><i class="bi bi-arrow-repeat me-2"></i>Rapprochement bancaire</h4>
        </div>

        <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <template v-else>
            <!-- Account Info -->
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small">Compte bancaire</label>
                            <select v-model="selectedAccount" class="form-select form-select-sm" @change="loadData">
                                <option value="">Sélectionner</option>
                                <option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.bank_name }} — {{ a.account_number }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Date d'arrêté</label>
                            <input v-model="asOfDate" type="date" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Solde bancaire (relevé)</label>
                            <input v-model.number="bankBalance" type="number" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2 d-grid">
                            <button class="btn btn-primary btn-sm" @click="loadData" :disabled="!selectedAccount">
                                <i class="bi bi-arrow-repeat me-1"></i>Charger
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="selectedAccount && transactions.length > 0" class="row g-3">
                <!-- Book transactions -->
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-semibold"><i class="bi bi-journal-text me-1"></i>Transactions comptables</h6>
                            <span class="badge bg-secondary">{{ bookTransactions.length }} lignes</span>
                        </div>
                        <div class="table-responsive" style="max-height:400px">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width:30px">
                                            <input type="checkbox" @change="selectAll('book', $event.target.checked)">
                                        </th>
                                        <th>Date</th>
                                        <th>Libellé</th>
                                        <th class="text-end">Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="tx in bookTransactions" :key="tx.id" :class="tx.matched ? 'table-success' : ''">
                                        <td>
                                            <input type="checkbox" :checked="tx.selected || tx.matched" :disabled="tx.matched" @change="toggleSelect('book', tx.id)">
                                        </td>
                                        <td class="small">{{ formatDate(tx.transaction_date || tx.date) }}</td>
                                        <td class="small">{{ tx.description || tx.libelle }}</td>
                                        <td class="small text-end fw-medium">{{ fmt(Math.abs(tx.amount)) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white py-2 d-flex justify-content-between">
                            <span class="small text-muted">Solde : {{ fmt(bookBalance) }}</span>
                            <span class="small fw-bold">{{ bookSelectedCount }} sélectionnée(s)</span>
                        </div>
                    </div>
                </div>

                <!-- Bank transactions -->
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-semibold"><i class="bi bi-bank me-1"></i>Relevé bancaire</h6>
                            <span class="badge bg-secondary">{{ bankTransactions.length }} lignes</span>
                        </div>
                        <div class="table-responsive" style="max-height:400px">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width:30px">
                                            <input type="checkbox" @change="selectAll('bank', $event.target.checked)">
                                        </th>
                                        <th>Date</th>
                                        <th>Libellé</th>
                                        <th class="text-end">Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="tx in bankTransactions" :key="tx.id" :class="tx.matched ? 'table-success' : ''">
                                        <td>
                                            <input type="checkbox" :checked="tx.selected || tx.matched" :disabled="tx.matched" @change="toggleSelect('bank', tx.id)">
                                        </td>
                                        <td class="small">{{ formatDate(tx.transaction_date || tx.date) }}</td>
                                        <td class="small">{{ tx.description || tx.libelle }}</td>
                                        <td class="small text-end fw-medium">{{ fmt(Math.abs(tx.amount)) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white py-2 d-flex justify-content-between">
                            <span class="small text-muted">Solde : {{ fmt(bankBalance) }}</span>
                            <span class="small fw-bold">{{ bankSelectedCount }} sélectionnée(s)</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <span class="small text-muted me-3">
                                    <i class="bi bi-check-circle-fill text-success me-1"></i>Matché : {{ matchedCount }}
                                </span>
                                <span class="small" :class="isBalancedRecon ? 'text-success' : 'text-danger'">
                                    <i :class="isBalancedRecon ? 'bi bi-check-circle-fill' : 'bi bi-exclamation-triangle-fill'"></i>
                                    Différence : {{ fmt(Math.abs(bookBalanceLab - bankBalanceLab)) }}
                                </span>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-primary btn-sm" @click="autoSuggest" :disabled="!selectedAccount">
                                    <i class="bi bi-magic me-1"></i>Suggestion auto
                                </button>
                                <button class="btn btn-success btn-sm" :disabled="!isBalancedRecon || saving" @click="completeReconciliation">
                                    <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                                    <i class="bi bi-check-lg me-1"></i>Finaliser le rapprochement
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else-if="selectedAccount && transactions.length === 0" class="text-center text-muted py-5">
                <p>Aucune transaction trouvée pour cette période</p>
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'

const accounts = ref([])
const transactions = ref([])
const loading = ref(true)
const error = ref(null)
const saving = ref(false)
const selectedAccount = ref('')
const asOfDate = ref(new Date().toISOString().split('T')[0])
const bankBalance = ref(0)

const bookTransactions = computed(() => transactions.value.filter(tx => tx.source !== 'bank'))
const bankTransactions = computed(() => transactions.value.filter(tx => tx.source === 'bank'))
const bookBalance = computed(() => bookTransactions.value.reduce((s, t) => s + parseFloat(t.amount || 0), 0))
const bookSelectedCount = computed(() => bookTransactions.value.filter(t => t.selected).length)
const bankSelectedCount = computed(() => bankTransactions.value.filter(t => t.selected).length)
const matchedCount = computed(() => transactions.value.filter(t => t.matched).length)
const bookBalanceLab = computed(() => bookTransactions.value.filter(t => t.selected || t.matched).reduce((s, t) => s + parseFloat(t.amount || 0), 0))
const bankBalanceLab = computed(() => bankTransactions.value.filter(t => t.selected || t.matched).reduce((s, t) => s + parseFloat(t.amount || 0), 0))
const isBalancedRecon = computed(() => Math.abs(bookBalanceLab.value - bankBalanceLab.value) < 0.01)

const fmt = (v) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(v || 0) + ' F'
const csrf = computed(() => document.querySelector('meta[name=csrf-token]')?.content || '')
const api = (path, opts = {}) => fetch(path, {
    headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf.value, ...opts.headers },
    ...opts,
})

async function loadAccounts() {
    try {
        const r = await api('/api/banking/accounts')
        if (r.ok) accounts.value = await r.json()
    } catch (e) { console.warn(e) }
}

async function loadData() {
    if (!selectedAccount.value) return
    loading.value = true; error.value = null
    try {
        const params = new URLSearchParams({
            bank_account_id: selectedAccount.value,
            unreconciled: 'true',
        })
        if (asOfDate.value) {
            params.set('date_to', asOfDate.value)
        }
        const r = await api(`/api/banking/transactions?${params}`)
        if (r.ok) {
            const result = await r.json()
            const txList = result.data || result
            transactions.value = (Array.isArray(txList) ? txList : []).map(tx => ({
                ...tx,
                selected: false,
                matched: false,
                source: tx.bank_account_id ? 'bank' : 'book',
            }))
        } else throw new Error('Erreur chargement')
    } catch (e) { error.value = e.message } finally { loading.value = false }
}

function selectAll(source, checked) {
    transactions.value.forEach(tx => {
        if ((source === 'book' && tx.source !== 'bank') || (source === 'bank' && tx.source === 'bank')) {
            if (!tx.matched) tx.selected = checked
        }
    })
}

function toggleSelect(source, id) {
    const tx = transactions.value.find(t => t.id === id)
    if (tx && !tx.matched) tx.selected = !tx.selected
}

async function autoSuggest() {
    if (!selectedAccount.value) return
    try {
        // Create a temporary reconciliation first, then auto-suggest
        const create = await api('/api/banking/reconciliations', {
            method: 'POST',
            body: JSON.stringify({
                bank_account_id: selectedAccount.value,
                start_date: asOfDate.value,
                end_date: asOfDate.value,
            }),
        })
        if (create.ok) {
            const created = await create.json()
            const id = created.data?.id || created.id
            const suggest = await api(`/api/banking/reconciliations/${id}/auto-suggest`, { method: 'POST' })
            if (suggest.ok) {
                const result = await suggest.json()
                const matchedIds = result.data?.matched_ids || result.matched_ids || []
                transactions.value.forEach(tx => {
                    if (matchedIds.includes(tx.id)) tx.matched = true
                })
            }
        }
    } catch (e) { console.warn(e) }
}

async function completeReconciliation() {
    if (!isBalancedRecon.value || !selectedAccount.value) return
    saving.value = true
    try {
        const selectedIds = transactions.value.filter(t => t.selected || t.matched).map(t => t.id)
        const r = await api('/api/banking/reconciliations', {
            method: 'POST',
            body: JSON.stringify({
                bank_account_id: selectedAccount.value,
                start_date: asOfDate.value,
                end_date: asOfDate.value,
                book_balance: bookBalanceLab.value,
                bank_balance: bankBalance.value,
                transaction_ids: selectedIds,
            }),
        })
        if (r.ok) {
            const created = await r.json()
            const recId = created.data?.id || created.id
            // Complete the reconciliation
            await api(`/api/banking/reconciliations/${recId}/complete`, { method: 'POST' })
            alert('Rapprochement finalisé avec succès !')
            window.location.href = '/comptabilite/banque'
        } else throw new Error('Erreur finalisation')
    } catch (e) { error.value = e.message } finally { saving.value = false }
}

const formatDate = (d) => d ? new Date(d).toLocaleDateString('fr-FR') : '—'

onMounted(() => {
    loadAccounts()
    // Check URL params for pre-selected account
    const params = new URLSearchParams(window.location.search)
    if (params.get('account')) selectedAccount.value = params.get('account')
    if (selectedAccount.value) loadData()
})
</script>
