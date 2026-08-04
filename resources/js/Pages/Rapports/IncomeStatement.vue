<!--
 * Composant : Compte de résultat
 * Description : Affiche le compte de résultat (produits et charges) sur une période donnée.
 *              Présente les totaux, le résultat net et la marge nette avec le détail des comptes.
 * Utilisation : Page /rapports/compte-de-resultat
-->
<template>
    <div class="container-fluid py-3">
        <div class="d-flex align-items-center gap-2 mb-3">
            <h4 class="fw-bold mb-0"><i class="bi bi-graph-up me-2"></i>Compte de résultat</h4>
            <button class="btn btn-outline-primary btn-sm ms-auto" @click="loadData">
                <i class="bi bi-arrow-repeat me-1"></i>Actualiser
            </button>
            <button class="btn btn-outline-secondary btn-sm" @click="exportPdf">
                <i class="bi bi-file-pdf me-1"></i>PDF
            </button>
        </div>

        <!-- Period -->
        <div class="card shadow-sm mb-3">
            <div class="card-body py-2">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Du</label>
                        <input v-model="dateFrom" type="date" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Au</label>
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
            <!-- Summary -->
            <div class="row g-2 mb-3">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-light">
                        <div class="card-body py-2 text-center">
                            <small class="text-muted d-block">Total Produits (classe 7)</small>
                            <span class="fw-bold fs-5 text-success">{{ fmt(data.total_revenue || data.totalProduits || 0) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-light">
                        <div class="card-body py-2 text-center">
                            <small class="text-muted d-block">Total Charges (classe 6)</small>
                            <span class="fw-bold fs-5 text-danger">{{ fmt(data.total_expenses || data.totalCharges || 0) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-light">
                        <div class="card-body py-2 text-center">
                            <small class="text-muted d-block">Résultat net</small>
                            <span class="fw-bold fs-5" :class="(data.net_income || data.resultatNet || 0) >= 0 ? 'text-success' : 'text-danger'">
                                {{ fmt(data.net_income || data.resultatNet || 0) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-light">
                        <div class="card-body py-2 text-center">
                            <small class="text-muted d-block">Marge nette</small>
                            <span class="fw-bold fs-5" :class="netMargin >= 0 ? 'text-success' : 'text-danger'">
                                {{ netMargin.toFixed(1) }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Details table -->
            <div class="card shadow-sm">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-semibold">Détail des comptes</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3">Type</th>
                                <th class="px-3">Compte</th>
                                <th class="px-3">Libellé</th>
                                <th class="px-3 text-end" style="width:140px">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in (data.details || data.lines || data.rows || [])" :key="row.code || row.account_code"
                                :class="{ 'table-secondary fw-bold': row.is_total || row.total, 'table-success': row.type === 'produit' || row.type === 'revenue', 'table-danger': row.type === 'charge' || row.type === 'expense' }">
                                <td class="px-3 small">
                                    <span v-if="row.type === 'produit' || row.type === 'revenue'" class="badge bg-success">Produit</span>
                                    <span v-else-if="row.type === 'charge' || row.type === 'expense'" class="badge bg-danger">Charge</span>
                                    <span v-else class="badge bg-secondary">—</span>
                                </td>
                                <td class="px-3"><code>{{ row.code || row.account_code || row.accountCode }}</code></td>
                                <td class="px-3">{{ row.label || row.name || row.account_name || row.accountLabel }}</td>
                                <td class="px-3 text-end fw-medium">{{ fmt(row.balance || row.amount || row.montant || 0) }}</td>
                            </tr>
                            <tr v-if="(!data.details?.length && !data.lines?.length && !data.rows?.length) || data.details === undefined">
                                <td colspan="4" class="text-center text-muted py-4">Aucune donnée</td>
                            </tr>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="3" class="px-3 text-end">RÉSULTAT NET</td>
                                <td class="px-3 text-end fs-6" :class="(data.net_income || data.resultatNet || 0) >= 0 ? 'text-success' : 'text-danger'">
                                    {{ fmt(data.net_income || data.resultatNet || 0) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const data = ref(null)            /* Données du compte de résultat */
const loading = ref(true)
const error = ref(null)
const dateFrom = ref(new Date(new Date().getFullYear(), 0, 1).toISOString().split('T')[0])  /* Début d'année */
const dateTo = ref(new Date().toISOString().split('T')[0])                                   /* Aujourd'hui */

/* Calcule la marge nette en pourcentage */
const netMargin = computed(() => {
    const total = data.value?.total_revenue || data.value?.totalProduits || 1
    const net = data.value?.net_income || data.value?.resultatNet || 0
    return total ? (net / total) * 100 : 0
})

/* Formate un nombre en francs CFA */
const fmt = (v) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(v || 0) + ' F'
const csrf = computed(() => document.querySelector('meta[name=csrf-token]')?.content || '')
const api = (path, opts = {}) => fetch(path, {
    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf.value, ...opts.headers },
    ...opts,
})

/* Charge les données du compte de résultat pour la période sélectionnée */
async function loadData() {
    loading.value = true; error.value = null
    try {
        const params = new URLSearchParams({ date_from: dateFrom.value, date_to: dateTo.value })
        const r = await api(`/api/reports/financial-statements/income-statement?${params}`)
        if (!r.ok) throw new Error('Erreur chargement')
        data.value = await r.json()
    } catch (e) { error.value = e.message } finally { loading.value = false }
}

/* Exporte le compte de résultat au format PDF */
function exportPdf() {
    const params = new URLSearchParams({ date_from: dateFrom.value, date_to: dateTo.value })
    window.open(`/api/reports/financial-statements/income-statement/pdf?${params}`, '_blank')
}

onMounted(loadData)
</script>
