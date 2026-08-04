<!--
 * Composant : Balance de vérification
 * Description : Affiche la balance de vérification avec les totaux débiteurs et créditeurs
 *              par compte comptable sur une période donnée. Vérifie l'équilibre
 *              entre débit et crédit.
 * Utilisation : Page /rapports/balance-de-verification
-->
<template>
    <div class="container-fluid py-3">
        <div class="d-flex align-items-center gap-2 mb-3">
            <h4 class="fw-bold mb-0"><i class="bi bi-bar-chart-line me-2"></i>Balance de vérification</h4>
            <button class="btn btn-outline-primary btn-sm ms-auto" @click="refresh">
                <i class="bi bi-arrow-repeat me-1"></i>Actualiser
            </button>
            <button class="btn btn-outline-secondary btn-sm" @click="exportPdf">
                <i class="bi bi-file-pdf me-1"></i>PDF
            </button>
        </div>

        <!-- Period selector -->
        <div class="card shadow-sm mb-3">
            <div class="card-body py-2">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Date début</label>
                        <input v-model="dateFrom" type="date" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Date fin</label>
                        <input v-model="dateTo" type="date" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button class="btn btn-primary btn-sm" @click="loadData">
                            <i class="bi bi-search me-1"></i>Charger
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <template v-else-if="data">
            <!-- Summary cards -->
            <div class="row g-2 mb-3">
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 bg-light">
                        <div class="card-body py-2 text-center">
                            <small class="text-muted d-block">Total Débit</small>
                            <span class="fw-bold fs-5 text-success">{{ fmt(data.total_debit || data.totalDebit) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 bg-light">
                        <div class="card-body py-2 text-center">
                            <small class="text-muted d-block">Total Crédit</small>
                            <span class="fw-bold fs-5" style="color:#163A5E">{{ fmt(data.total_credit || data.totalCredit) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 bg-light">
                        <div class="card-body py-2 text-center">
                            <small class="text-muted d-block">Différence</small>
                            <span class="fw-bold fs-5" :class="isBalanced ? 'text-success' : 'text-danger'">
                                {{ fmt(data.difference || 0) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="card shadow-sm">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3">Compte</th>
                                <th class="px-3">Libellé</th>
                                <th class="px-3 text-end">Débit</th>
                                <th class="px-3 text-end">Crédit</th>
                                <th class="px-3 text-end">Solde débiteur</th>
                                <th class="px-3 text-end">Solde créditeur</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in (data.rows || data.accounts || data.lines || [])" :key="row.account_code || row.accountCode">
                                <td class="px-3"><code>{{ row.account_code || row.accountCode }}</code></td>
                                <td class="px-3">{{ row.account_name || row.accountLabel || row.name }}</td>
                                <td class="px-3 text-end">{{ fmt(row.debit || 0) }}</td>
                                <td class="px-3 text-end">{{ fmt(row.credit || 0) }}</td>
                                <td class="px-3 text-end fw-medium text-success">{{ fmt(row.debit_balance || row.debitBalance || 0) }}</td>
                                <td class="px-3 text-end fw-medium" style="color:#163A5E">{{ fmt(row.credit_balance || row.creditBalance || 0) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="2" class="px-3 text-end">TOTAUX</td>
                                <td class="px-3 text-end">{{ fmt(data.total_debit || data.totalDebit) }}</td>
                                <td class="px-3 text-end">{{ fmt(data.total_credit || data.totalCredit) }}</td>
                                <td class="px-3 text-end">{{ fmt(data.total_debit_balance || data.totalDebitBalance || 0) }}</td>
                                <td class="px-3 text-end">{{ fmt(data.total_credit_balance || data.totalCreditBalance || 0) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="mt-2">
                <small class="text-muted">{{ data.total_accounts || data.totalAccounts || data.rows?.length || 0 }} comptes</small>
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const data = ref(null)            /* Données de la balance de vérification */
const loading = ref(true)
const error = ref(null)
const dateFrom = ref(new Date(new Date().getFullYear(), 0, 1).toISOString().split('T')[0])  /* Début d'année */
const dateTo = ref(new Date().toISOString().split('T')[0])                                   /* Aujourd'hui */

/* Vérifie si la balance est équilibrée (différence inférieure à 0,01) */
const isBalanced = computed(() => {
    if (!data.value) return true
    return Math.abs((data.value.difference || 0)) < 0.01
})

/* Formate un nombre en francs CFA */
const fmt = (v) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(v || 0) + ' F'
const csrf = computed(() => document.querySelector('meta[name=csrf-token]')?.content || '')
const api = (path, opts = {}) => fetch(path, {
    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf.value, ...opts.headers },
    ...opts,
})

/* Charge les données de la balance de vérification pour la période sélectionnée */
async function loadData() {
    loading.value = true; error.value = null
    try {
        const params = new URLSearchParams({ date_from: dateFrom.value, date_to: dateTo.value })
        const r = await api(`/api/reports/financial-statements/trial-balance?${params}`)
        if (!r.ok) throw new Error('Erreur chargement')
        data.value = await r.json()
    } catch (e) { error.value = e.message } finally { loading.value = false }
}

function refresh() { loadData() }
/* Exporte la balance de vérification au format PDF */
function exportPdf() {
    const params = new URLSearchParams({ date_from: dateFrom.value, date_to: dateTo.value })
    window.open(`/api/reports/financial-statements/trial-balance/pdf?${params}`, '_blank')
}

onMounted(loadData)
</script>
