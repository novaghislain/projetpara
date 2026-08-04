<template>
    <div class="container-fluid py-3">
        <div class="d-flex align-items-center gap-2 mb-3">
            <a :href="'/comptabilite/banque'" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
            <h4 class="fw-bold mb-0"><i class="bi bi-bank me-2"></i>Compte bancaire</h4>
        </div>

        <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <template v-else-if="account">
            <!-- Account Header -->
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="fw-bold mb-1">{{ account.bank_name }}</h5>
                            <p class="text-muted mb-1 small">{{ account.account_number }} · {{ account.account_type_label || account.account_type }}</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <small class="text-muted d-block">Solde actuel</small>
                            <span class="fw-bold fs-4" :class="parseFloat(account.balance) >= 0 ? 'text-success' : 'text-danger'">
                                {{ fmt(account.balance) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                    <button class="nav-link" :class="{ active: tab === 'transactions' }" @click="tab = 'transactions'">
                        <i class="bi bi-list-ul me-1"></i>Transactions
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" :class="{ active: tab === 'reconciliations' }" @click="tab = 'reconciliations'">
                        <i class="bi bi-arrow-repeat me-1"></i>Rapprochements
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" :class="{ active: tab === 'import' }" @click="tab = 'import'">
                        <i class="bi bi-upload me-1"></i>Import
                    </button>
                </li>
            </ul>

            <!-- Transactions Tab -->
            <div v-if="tab === 'transactions'" class="card shadow-sm">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">Transactions</h6>
                    <div class="d-flex gap-2">
                        <input v-model="txFilters.date_from" type="date" class="form-control form-control-sm" style="width:140px">
                        <input v-model="txFilters.date_to" type="date" class="form-control form-control-sm" style="width:140px">
                        <button class="btn btn-sm btn-outline-primary" @click="loadTransactions">Filtrer</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3">Date</th>
                                <th class="px-3">Libellé</th>
                                <th class="px-3">Réf.</th>
                                <th class="px-3 text-end">Débit</th>
                                <th class="px-3 text-end">Crédit</th>
                                <th class="px-3 text-end">Solde</th>
                                <th class="px-3">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="tx in transactions" :key="tx.id">
                                <td class="px-3 small">{{ formatDate(tx.transaction_date || tx.date) }}</td>
                                <td class="px-3 small">{{ tx.description || tx.libelle }}</td>
                                <td class="px-3 small"><code>{{ tx.reference || '—' }}</code></td>
                                <td class="px-3 small text-end text-danger">{{ tx.type === 'debit' || tx.type === 'sortie' ? fmt(tx.amount) : '—' }}</td>
                                <td class="px-3 small text-end text-success">{{ tx.type === 'credit' || tx.type === 'entree' ? fmt(tx.amount) : '—' }}</td>
                                <td class="px-3 small text-end fw-bold">{{ fmt(tx.running_balance || tx.balance_after) }}</td>
                                <td class="px-3 small">
                                    <span v-if="tx.reconciled" class="badge bg-success">Rapproché</span>
                                    <span v-else class="badge bg-warning text-dark">En attente</span>
                                </td>
                            </tr>
                            <tr v-if="transactions.length === 0">
                                <td colspan="7" class="text-center text-muted py-4">Aucune transaction</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Reconciliations Tab -->
            <div v-if="tab === 'reconciliations'" class="card shadow-sm">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">Rapprochements bancaires</h6>
                    <button class="btn btn-primary btn-sm" @click="startReconciliation">
                        <i class="bi bi-plus-lg me-1"></i>Nouveau rapprochement
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3">Période</th>
                                <th class="px-3">Solde comptable</th>
                                <th class="px-3">Solde bancaire</th>
                                <th class="px-3">Différence</th>
                                <th class="px-3">Statut</th>
                                <th class="px-3 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="r in reconciliations" :key="r.id">
                                <td class="px-3 small">{{ r.period || r.period_label }}</td>
                                <td class="px-3 small text-end">{{ fmt(r.book_balance || r.comptable_balance) }}</td>
                                <td class="px-3 small text-end">{{ fmt(r.bank_balance || r.bancaire_balance) }}</td>
                                <td class="px-3 small text-end" :class="parseFloat(r.difference || r.ecart) === 0 ? 'text-success' : 'text-danger'">
                                    {{ fmt(r.difference || r.ecart) }}
                                </td>
                                <td class="px-3 small">
                                    <span v-if="r.status === 'completed' || r.status === 'approuvé'" class="badge bg-success">Approuvé</span>
                                    <span v-else-if="r.status === 'in_progress' || r.status === 'en_cours'" class="badge bg-warning text-dark">En cours</span>
                                    <span v-else class="badge bg-secondary">{{ r.status }}</span>
                                </td>
                                <td class="px-3 text-end">
                                    <button class="btn btn-sm btn-outline-secondary" title="Voir" @click="viewReconciliation(r)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="reconciliations.length === 0">
                                <td colspan="6" class="text-center text-muted py-4">Aucun rapprochement</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Import Tab -->
            <div v-if="tab === 'import'" class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-upload fs-1 text-muted d-block mb-3"></i>
                    <h6>Importer un relevé bancaire</h6>
                    <p class="text-muted small">Formats supportés : CSV, OFX, QIF</p>
                    <div class="mb-3">
                        <input type="file" class="form-control form-control-sm" style="max-width:300px;margin:0 auto" accept=".csv,.ofx,.qif" @change="importFile">
                    </div>
                    <button class="btn btn-primary btn-sm" :disabled="!selectedFile || importing" @click="uploadFile">
                        <span v-if="importing" class="spinner-border spinner-border-sm me-1"></span>
                        <i class="bi bi-upload me-1"></i>Importer
                    </button>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
/*
 * BankAccountDetail.vue - Détail d'un compte bancaire
 *
 * Affiche les informations détaillées d'un compte bancaire avec
 * trois onglets : Transactions (liste des opérations), Rapprochements
 * (historique des rapprochements) et Import (import de relevé bancaire).
 * Permet de filtrer les transactions par période et d'importer
 * des fichiers CSV/OFX/QIF.
 */
import { ref, reactive, computed, onMounted } from 'vue'

// Identifiant du compte extrait de l'URL
const accountId = ref(null)
const loading = ref(true)
const error = ref(null)
const account = ref(null)
const transactions = ref([])
const reconciliations = ref([])
// Onglet actif : transactions, reconciliations ou import
const tab = ref('transactions')
const selectedFile = ref(null)
const importing = ref(false)

// Filtres de date pour les transactions
const txFilters = reactive({ date_from: '', date_to: '' })

// Formateur monétaire en francs CFA
const fmt = (v) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(v || 0) + ' F'
// Jeton CSRF pour les requêtes sécurisées
const csrf = computed(() => document.querySelector('meta[name=csrf-token]')?.content || '')
// Fonction utilitaire d'appel API avec en-têtes JSON par défaut
const api = (path, opts = {}) => fetch(path, {
    headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf.value, ...opts.headers },
    ...opts,
})

// Chargement des données du compte bancaire depuis l'API
async function loadAccount() {
    if (!accountId.value) return
    loading.value = true
    try {
        const r = await api(`/api/banking/accounts/${accountId.value}`)
        if (r.ok) account.value = await r.json()
        else throw new Error('Compte introuvable')
    } catch (e) { error.value = e.message } finally { loading.value = false }
}

// Chargement des transactions avec filtres optionnels (date début/fin)
async function loadTransactions() {
    try {
        const params = new URLSearchParams()
        if (txFilters.date_from) params.set('date_from', txFilters.date_from)
        if (txFilters.date_to) params.set('date_to', txFilters.date_to)
        if (accountId.value) params.set('bank_account_id', accountId.value)
        const r = await api(`/api/banking/transactions?${params}`)
        if (r.ok) {
            const result = await r.json()
            transactions.value = result.data || result
        }
    } catch (e) { console.warn(e) }
}

// Chargement de l'historique des rapprochements bancaires
async function loadReconciliations() {
    try {
        const params = new URLSearchParams()
        if (accountId.value) params.set('bank_account_id', accountId.value)
        const r = await api(`/api/banking/reconciliations?${params}`)
        if (r.ok) {
            const result = await r.json()
            reconciliations.value = result.data || result
        }
    } catch (e) { console.warn(e) }
}

// Redirection vers l'assistant de rapprochement pour ce compte
function startReconciliation() {
    window.location.href = `/comptabilite/banque/rapprochement?account=${accountId.value}`
}

// Redirection vers le détail d'un rapprochement existant
function viewReconciliation(r) {
    window.location.href = `/comptabilite/banque/rapprochement/${r.id}`
}

// Sélection du fichier de relevé bancaire à importer
function importFile(e) {
    selectedFile.value = e.target.files[0] || null
}

// Import du fichier de relevé bancaire sélectionné (CSV/OFX/QIF)
async function uploadFile() {
    if (!selectedFile.value) return
    importing.value = true
    try {
        const fd = new FormData()
        fd.append('file', selectedFile.value)
        fd.append('bank_account_id', accountId.value)
        const r = await fetch('/api/banking/transactions/import', {
            method: 'POST',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf.value },
            body: fd,
        })
        if (r.ok) {
            selectedFile.value = null
            await loadTransactions()
        } else throw new Error('Erreur import')
    } catch (e) { error.value = e.message } finally { importing.value = false }
}

// Formatage d'une date au format français JJ/MM/AAAA
const formatDate = (d) => d ? new Date(d).toLocaleDateString('fr-FR') : '—'

// Initialisation : extraction de l'ID du compte depuis l'URL et chargement des données
onMounted(() => {
    const pathParts = window.location.pathname.split('/')
    const lastSegment = pathParts[pathParts.length - 1]
    accountId.value = lastSegment && !isNaN(lastSegment) ? lastSegment : null
    loadAccount()
    loadTransactions()
    loadReconciliations()
})
</script>
