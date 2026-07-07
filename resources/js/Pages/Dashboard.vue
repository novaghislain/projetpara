<template>
    <div class="container-fluid py-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0"><i class="bi bi-speedometer2 me-2"></i>Tableau de bord comptable</h4>
            <div class="d-flex align-items-center gap-2">
                <small class="text-muted">{{ currentPeriod }}</small>
                <button class="btn btn-outline-primary btn-sm" @click="loadStats">
                    <i class="bi bi-arrow-repeat me-1"></i>Actualiser
                </button>
            </div>
        </div>

        <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <template v-else-if="stats">
            <!-- KPIs -->
            <div class="row g-2 mb-3">
                <div class="col-md-3 col-6">
                    <div class="card shadow-sm border-0 isup-kpi-card">
                        <div class="card-body py-3 text-center">
                            <div class="isup-kpi-icon bg-primary bg-opacity-10 rounded-circle mx-auto mb-2" style="width:40px;height:40px;line-height:40px">
                                <i class="bi bi-currency-exchange text-primary"></i>
                            </div>
                            <small class="text-muted d-block">Total Produits</small>
                            <span class="fw-bold fs-5">{{ fmt(stats.total_revenue || stats.totalProduits || 0) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card shadow-sm border-0 isup-kpi-card">
                        <div class="card-body py-3 text-center">
                            <div class="isup-kpi-icon bg-danger bg-opacity-10 rounded-circle mx-auto mb-2" style="width:40px;height:40px;line-height:40px">
                                <i class="bi bi-cart-dash text-danger"></i>
                            </div>
                            <small class="text-muted d-block">Total Charges</small>
                            <span class="fw-bold fs-5">{{ fmt(stats.total_expenses || stats.totalCharges || 0) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card shadow-sm border-0 isup-kpi-card">
                        <div class="card-body py-3 text-center">
                            <div class="isup-kpi-icon bg-success bg-opacity-10 rounded-circle mx-auto mb-2" style="width:40px;height:40px;line-height:40px">
                                <i class="bi bi-graph-up-arrow text-success"></i>
                            </div>
                            <small class="text-muted d-block">Résultat net</small>
                            <span class="fw-bold fs-5" :class="(stats.net_income || stats.resultatNet || 0) >= 0 ? 'text-success' : 'text-danger'">
                                {{ fmt(stats.net_income || stats.resultatNet || 0) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card shadow-sm border-0 isup-kpi-card">
                        <div class="card-body py-3 text-center">
                            <div class="isup-kpi-icon bg-warning bg-opacity-10 rounded-circle mx-auto mb-2" style="width:40px;height:40px;line-height:40px">
                                <i class="bi bi-cash-stack text-warning"></i>
                            </div>
                            <small class="text-muted d-block">Trésorerie</small>
                            <span class="fw-bold fs-5" :class="(stats.cash_balance || stats.tresorerie || 0) >= 0 ? 'text-success' : 'text-danger'">
                                {{ fmt(stats.cash_balance || stats.tresorerie || 0) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <!-- Revenue chart -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-2">
                            <h6 class="mb-0 fw-semibold small"><i class="bi bi-bar-chart me-1"></i>Évolution mensuelle</h6>
                        </div>
                        <div class="card-body">
                            <canvas ref="chartCanvas" height="220"></canvas>
                            <div v-if="!chartData" class="text-center text-muted small py-5">
                                Données insuffisantes pour le graphique
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent entries -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-semibold small"><i class="bi bi-clock me-1"></i>Dernières écritures</h6>
                            <a :href="'/comptabilite/ecritures'" class="btn btn-sm btn-outline-primary py-0 px-2 small">Voir tout</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-3 small">Date</th>
                                        <th class="px-3 small">Réf.</th>
                                        <th class="px-3 small">Libellé</th>
                                        <th class="px-3 small text-end">Montant</th>
                                        <th class="px-3 small">Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="e in (stats.recent_entries || stats.recentEntries || [])" :key="e.id">
                                        <td class="px-3 small">{{ formatDate(e.entry_date || e.date) }}</td>
                                        <td class="px-3 small"><code>{{ e.reference }}</code></td>
                                        <td class="px-3 small text-truncate" style="max-width:150px">{{ e.description || e.libelle }}</td>
                                        <td class="px-3 small text-end fw-medium">{{ fmt(e.total_debit || e.totalDebit || 0) }}</td>
                                        <td class="px-3 small"><span :class="statusBadge(e.status)">{{ statusLabel(e.status) }}</span></td>
                                    </tr>
                                    <tr v-if="!stats.recent_entries?.length && !stats.recentEntries?.length">
                                        <td colspan="5" class="text-center text-muted small py-3">Aucune écriture récente</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Account balances -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-2">
                            <h6 class="mb-0 fw-semibold small"><i class="bi bi-wallet2 me-1"></i>Soldes bancaires</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <tbody>
                                    <tr v-for="acc in (stats.bank_accounts || stats.bankAccounts || [])" :key="acc.id">
                                        <td class="px-3 small">
                                            <span class="fw-semibold">{{ acc.bank_name }}</span>
                                            <small class="text-muted d-block">{{ acc.account_number }}</small>
                                        </td>
                                        <td class="px-3 text-end fw-bold" :class="parseFloat(acc.balance) >= 0 ? 'text-success' : 'text-danger'">
                                            {{ fmt(acc.balance) }}
                                        </td>
                                    </tr>
                                    <tr v-if="!stats.bank_accounts?.length && !stats.bankAccounts?.length">
                                        <td class="text-center text-muted small py-3">Aucun compte bancaire</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Quick actions -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-2">
                            <h6 class="mb-0 fw-semibold small"><i class="bi bi-lightning me-1"></i>Actions rapides</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-6">
                                    <a :href="'/comptabilite/ecritures/nouvelle'" class="btn btn-outline-primary btn-sm w-100 py-2">
                                        <i class="bi bi-pencil-square d-block fs-5 mb-1"></i>
                                        <small>Nouvelle écriture</small>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a :href="'/comptabilite/banque'" class="btn btn-outline-success btn-sm w-100 py-2">
                                        <i class="bi bi-bank d-block fs-5 mb-1"></i>
                                        <small>Banque</small>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a :href="'/comptabilite/rapports/bilan'" class="btn btn-outline-warning btn-sm w-100 py-2">
                                        <i class="bi bi-building d-block fs-5 mb-1"></i>
                                        <small>Bilan</small>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a :href="'/comptabilite/rapports/resultat'" class="btn btn-outline-info btn-sm w-100 py-2">
                                        <i class="bi bi-graph-up d-block fs-5 mb-1"></i>
                                        <small>Résultat</small>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const stats = ref(null)
const loading = ref(true)
const error = ref(null)
const chartCanvas = ref(null)
const chartData = ref(null)

const currentPeriod = computed(() => {
    return new Date().toLocaleDateString('fr-FR', { year: 'numeric', month: 'long' })
})

const fmt = (v) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(v || 0) + ' F'
const csrf = computed(() => document.querySelector('meta[name=csrf-token]')?.content || '')
const api = (path, opts = {}) => fetch(path, {
    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf.value, ...opts.headers },
    ...opts,
})

async function loadStats() {
    loading.value = true; error.value = null
    try {
        const r = await api('/api/reports/dashboard')
        if (!r.ok) throw new Error('Erreur chargement')
        const result = await r.json()
        stats.value = result.data || result
        chartData.value = stats.value.monthly_data || stats.value.monthlyData || null
        if (chartData.value) renderChart()
    } catch (e) { error.value = e.message } finally { loading.value = false }
}

function renderChart() {
    if (!chartCanvas.value || !chartData.value) return
    try {
        import('chart.js/auto').then(ChartJS => {
            const ctx = chartCanvas.value.getContext('2d')
            if (window._dashboardChart) window._dashboardChart.destroy()
            window._dashboardChart = new ChartJS.default(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.value.labels || chartData.value.map(d => d.month || d.label),
                    datasets: [
                        {
                            label: 'Produits',
                            data: chartData.value.datasets?.revenue || chartData.value.map(d => d.revenue || d.produits || 0),
                            backgroundColor: 'rgba(0,200,83,0.2)',
                            borderColor: '#00c853',
                            borderWidth: 2,
                        },
                        {
                            label: 'Charges',
                            data: chartData.value.datasets?.expenses || chartData.value.map(d => d.expense || d.charges || 0),
                            backgroundColor: 'rgba(244,67,54,0.2)',
                            borderColor: '#f44336',
                            borderWidth: 2,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: true, position: 'top' } },
                    scales: {
                        y: { beginAtZero: true, ticks: { callback: v => new Intl.NumberFormat('fr-FR').format(v) } },
                    },
                },
            })
        })
    } catch (e) { console.warn('Chart render skipped:', e) }
}

const formatDate = (d) => d ? new Date(d).toLocaleDateString('fr-FR') : '—'
const statusLabel = (s) => ({ posted: 'Validée', draft: 'Brouillon', cancelled: 'Annulée' })[s] || s
const statusBadge = (s) => ({ posted: 'badge bg-success', draft: 'badge bg-warning text-dark', cancelled: 'badge bg-secondary' })[s] || 'badge bg-secondary'

onMounted(loadStats)
</script>

<style scoped>
.isup-kpi-card { transition: transform 0.15s ease; }
.isup-kpi-card:hover { transform: translateY(-2px); }
</style>
