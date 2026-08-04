<template>
    <div class="container-fluid py-3">
        <div class="d-flex align-items-center gap-2 mb-3">
            <h4 class="fw-bold mb-0"><i class="bi bi-cash-stack me-2"></i>Tableau des flux de trésorerie</h4>
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
            <div class="row g-3">
                <!-- Exploitation -->
                <div class="col-md-4">
                    <div class="card shadow-sm h-100 border-success">
                        <div class="card-header bg-white py-2">
                            <h6 class="mb-0 fw-bold small"><i class="bi bi-gear me-1 text-success"></i>Exploitation</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <tbody>
                                    <tr v-for="item in (data.operating || data.exploitation || [])" :key="item.code || item.label">
                                        <td class="px-3 small">{{ item.label || item.name }}</td>
                                        <td class="px-3 text-end small fw-medium" :class="(item.amount || item.montant || 0) >= 0 ? 'text-success' : 'text-danger'">
                                            {{ fmt(item.amount || item.montant || 0) }}
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-success">
                                    <tr class="fw-bold">
                                        <td class="px-3 small">Total exploitation</td>
                                        <td class="px-3 text-end small">{{ fmt(data.total_operating || data.totalExploitation || 0) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Investissement -->
                <div class="col-md-4">
                    <div class="card shadow-sm h-100 border-warning">
                        <div class="card-header bg-white py-2">
                            <h6 class="mb-0 fw-bold small"><i class="bi bi-building me-1 text-warning"></i>Investissement</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <tbody>
                                    <tr v-for="item in (data.investing || data.investissement || [])" :key="item.code || item.label">
                                        <td class="px-3 small">{{ item.label || item.name }}</td>
                                        <td class="px-3 text-end small fw-medium" :class="(item.amount || item.montant || 0) >= 0 ? 'text-success' : 'text-danger'">
                                            {{ fmt(item.amount || item.montant || 0) }}
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-warning">
                                    <tr class="fw-bold">
                                        <td class="px-3 small">Total investissement</td>
                                        <td class="px-3 text-end small">{{ fmt(data.total_investing || data.totalInvestissement || 0) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Financement -->
                <div class="col-md-4">
                    <div class="card shadow-sm h-100 border-info">
                        <div class="card-header bg-white py-2">
                            <h6 class="mb-0 fw-bold small"><i class="bi bi-credit-card me-1 text-info"></i>Financement</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <tbody>
                                    <tr v-for="item in (data.financing || data.financement || [])" :key="item.code || item.label">
                                        <td class="px-3 small">{{ item.label || item.name }}</td>
                                        <td class="px-3 text-end small fw-medium" :class="(item.amount || item.montant || 0) >= 0 ? 'text-success' : 'text-danger'">
                                            {{ fmt(item.amount || item.montant || 0) }}
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-info">
                                    <tr class="fw-bold">
                                        <td class="px-3 small">Total financement</td>
                                        <td class="px-3 text-end small">{{ fmt(data.total_financing || data.totalFinancement || 0) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Variation nette -->
                <div class="col-12">
                    <div class="card shadow-sm border-0 bg-light">
                        <div class="card-body py-2">
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Trésorerie début</small>
                                    <span class="fw-bold">{{ fmt(data.opening_cash || data.tresorerieDebut || 0) }}</span>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Variation nette</small>
                                    <span class="fw-bold" :class="(data.net_change || data.variationNette || 0) >= 0 ? 'text-success' : 'text-danger'">
                                        {{ fmt(data.net_change || data.variationNette || 0) }}
                                    </span>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Trésorerie fin</small>
                                    <span class="fw-bold fs-5" :class="(data.closing_cash || data.tresorerieFin || 0) >= 0 ? 'text-success' : 'text-danger'">
                                        {{ fmt(data.closing_cash || data.tresorerieFin || 0) }}
                                    </span>
                                </div>
                                <div class="col-md-3 text-md-end">
                                    <span class="badge bg-secondary">Méthode indirecte</span>
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
/*
 * CashFlow.vue - Tableau des flux de trésorerie
 *
 * Affiche le tableau des flux de trésorerie (méthode indirecte)
 * avec les trois catégories : exploitation, investissement et
 * financement. Présente la trésorerie de début et de fin de période
 * ainsi que la variation nette. Permet le filtrage par période
 * et l'export PDF.
 */
import { ref, computed, onMounted } from 'vue'

// Données du rapport et états de chargement
const data = ref(null)
const loading = ref(true)
const error = ref(null)
// Filtres de période (par défaut : année en cours)
const dateFrom = ref(new Date(new Date().getFullYear(), 0, 1).toISOString().split('T')[0])
const dateTo = ref(new Date().toISOString().split('T')[0])

// Formateur monétaire en francs CFA
const fmt = (v) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(v || 0) + ' F'
// Jeton CSRF pour les requêtes sécurisées
const csrf = computed(() => document.querySelector('meta[name=csrf-token]')?.content || '')
// Fonction utilitaire d'appel API
const api = (path, opts = {}) => fetch(path, {
    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf.value, ...opts.headers },
    ...opts,
})

// Chargement des données du flux de trésorerie depuis l'API
async function loadData() {
    loading.value = true; error.value = null
    try {
        const params = new URLSearchParams({ date_from: dateFrom.value, date_to: dateTo.value })
        const r = await api(`/api/reports/financial-statements/cash-flow?${params}`)
        if (!r.ok) throw new Error('Erreur chargement')
        data.value = await r.json()
    } catch (e) { error.value = e.message } finally { loading.value = false }
}

// Export PDF du rapport (ouverture dans un nouvel onglet)
function exportPdf() {
    const params = new URLSearchParams({ date_from: dateFrom.value, date_to: dateTo.value })
    window.open(`/api/reports/financial-statements/cash-flow/pdf?${params}`, '_blank')
}

// Chargement automatique au montage du composant
onMounted(loadData)
</script>
