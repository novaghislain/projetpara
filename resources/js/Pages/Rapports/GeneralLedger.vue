<!--
 * Composant : Grand Livre
 * Description : Affiche le Grand Livre d'un compte comptable sélectionné : détail
 *              des écritures (date, référence, libellé, débit, crédit, solde cumulé)
 *              avec filtrage par période.
 * Utilisation : Page /rapports/grand-livre
-->
<template>
    <div class="container-fluid py-3">
        <div class="d-flex align-items-center gap-2 mb-3">
            <h4 class="fw-bold mb-0"><i class="bi bi-journal-text me-2"></i>Grand Livre</h4>
            <button class="btn btn-outline-primary btn-sm ms-auto" @click="refresh">
                <i class="bi bi-arrow-repeat me-1"></i>Actualiser
            </button>
            <button class="btn btn-outline-secondary btn-sm" @click="exportPdf">
                <i class="bi bi-file-pdf me-1"></i>PDF
            </button>
        </div>

        <!-- Filters -->
        <div class="card shadow-sm mb-3">
            <div class="card-body py-2">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Compte</label>
                        <select v-model="accountId" class="form-select form-select-sm">
                            <option value="">Sélectionner un compte</option>
                            <option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.code }} — {{ a.name }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Date début</label>
                        <input v-model="dateFrom" type="date" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Date fin</label>
                        <input v-model="dateTo" type="date" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button class="btn btn-primary btn-sm" :disabled="!accountId" @click="loadData">
                            <i class="bi bi-search me-1"></i>Charger
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <template v-else-if="data">
            <!-- Account Info -->
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <small class="text-muted d-block">Compte</small>
                            <span class="fw-bold"><code>{{ data.account_code || data.accountCode }}</code> — {{ data.account_name || data.accountLabel || data.accountName }}</span>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Solde débiteur</small>
                            <span class="fw-bold text-success">{{ fmt(data.debit_balance || data.debitBalance || 0) }}</span>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Solde créditeur</small>
                            <span class="fw-bold" style="color:#163A5E">{{ fmt(data.credit_balance || data.creditBalance || 0) }}</span>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Solde net</small>
                            <span class="fw-bold" :class="parseFloat(data.net_balance || data.netBalance || 0) >= 0 ? 'text-success' : 'text-danger'">
                                {{ fmt(data.net_balance || data.netBalance || 0) }}
                            </span>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Mouvements</small>
                            <span class="fw-bold">{{ data.total_transactions || data.totalTransactions || 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transactions -->
            <div class="card shadow-sm">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3">Date</th>
                                <th class="px-3">Réf.</th>
                                <th class="px-3">Libellé</th>
                                <th class="px-3">Journal</th>
                                <th class="px-3 text-end">Débit</th>
                                <th class="px-3 text-end">Crédit</th>
                                <th class="px-3 text-end">Solde cumulé</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(tx, i) in (data.transactions || data.lines || data.entries || [])" :key="tx.id || i">
                                <td class="px-3 small">{{ formatDate(tx.entry_date || tx.date || tx.transaction_date) }}</td>
                                <td class="px-3 small"><code>{{ tx.reference || '—' }}</code></td>
                                <td class="px-3 small">{{ tx.description || tx.libelle || tx.label }}</td>
                                <td class="px-3 small"><span class="badge bg-dark">{{ tx.journal_code || tx.journal?.code || '—' }}</span></td>
                                <td class="px-3 small text-end text-success fw-medium">{{ tx.debit > 0 ? fmt(tx.debit) : '—' }}</td>
                                <td class="px-3 small text-end fw-medium" style="color:#163A5E">{{ tx.credit > 0 ? fmt(tx.credit) : '—' }}</td>
                                <td class="px-3 small text-end fw-bold">{{ fmt(tx.running_balance || tx.cumul || tx.solde || 0) }}</td>
                            </tr>
                            <tr v-if="!data.transactions?.length && !data.lines?.length && !data.entries?.length">
                                <td colspan="7" class="text-center text-muted py-4">Aucune transaction</td>
                            </tr>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="4" class="px-3 text-end">TOTAUX</td>
                                <td class="px-3 text-end">{{ fmt(data.total_debit || data.totalDebit || 0) }}</td>
                                <td class="px-3 text-end">{{ fmt(data.total_credit || data.totalCredit || 0) }}</td>
                                <td class="px-3 text-end">{{ fmt(data.net_balance || data.netBalance || 0) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </template>

        <div v-else-if="!loading && !error" class="text-center text-muted py-5">
            <i class="bi bi-search fs-1 d-block mb-2"></i>
            <p>Sélectionnez un compte pour afficher le Grand Livre</p>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const accounts = ref([])     /* Liste des comptes comptables pour le select */
const data = ref(null)       /* Données du Grand Livre */
const loading = ref(false)
const error = ref(null)
const accountId = ref('')    /* Compte sélectionné */
const dateFrom = ref(new Date(new Date().getFullYear(), 0, 1).toISOString().split('T')[0])  /* Début d'année */
const dateTo = ref(new Date().toISOString().split('T')[0])                                   /* Aujourd'hui */

/* Formate un nombre en francs CFA */
const fmt = (v) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(v || 0) + ' F'
const csrf = computed(() => document.querySelector('meta[name=csrf-token]')?.content || '')
/* Fonction utilitaire pour les appels API avec en-têtes communs */
const api = (path, opts = {}) => fetch(path, {
    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf.value, ...opts.headers },
    ...opts,
})

/* Charge la liste des comptes comptables pour le sélecteur */
async function loadAccounts() {
    try {
        const r = await api('/api/chart-accounts')
        if (r.ok) accounts.value = await r.json()
    } catch (e) { console.warn(e) }
}

/* Charge les écritures du Grand Livre pour le compte et la période sélectionnés */
async function loadData() {
    if (!accountId.value) return
    loading.value = true; error.value = null
    try {
        const params = new URLSearchParams({ date_from: dateFrom.value, date_to: dateTo.value })
        const r = await api(`/api/reports/ledger/${accountId.value}?${params}`)
        if (!r.ok) throw new Error('Erreur chargement')
        data.value = await r.json()
    } catch (e) { error.value = e.message } finally { loading.value = false }
}

function refresh() { loadData() }
/* Exporte le Grand Livre au format PDF */
function exportPdf() {
    if (!accountId.value) return
    const params = new URLSearchParams({ account_id: accountId.value, date_from: dateFrom.value, date_to: dateTo.value })
    window.open(`/api/reports/ledger/${accountId.value}/pdf?${params}`, '_blank')
}

/* Formate une date au format français */
const formatDate = (d) => d ? new Date(d).toLocaleDateString('fr-FR') : '—'

onMounted(loadAccounts)
</script>
