<template>
    <div class="container-fluid py-3">
        <div class="d-flex align-items-center gap-2 mb-3">
            <h4 class="fw-bold mb-0"><i class="bi bi-building me-2"></i>Bilan comptable</h4>
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
                        <label class="form-label small mb-1">Date d'arrêté</label>
                        <input v-model="asOfDate" type="date" class="form-control form-control-sm">
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
                <!-- ACTIF -->
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white py-2">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-box me-1"></i>ACTIF</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-3">Rubrique</th>
                                        <th class="px-3 text-end" style="width:130px">Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-for="section in (data.actif || data.assets || [])" :key="section.code || section.label">
                                        <tr class="table-secondary">
                                            <td class="px-3 fw-semibold small">{{ section.code }} - {{ section.label || section.name }}</td>
                                            <td class="px-3 text-end fw-bold">{{ fmt(section.total || 0) }}</td>
                                        </tr>
                                        <tr v-for="item in (section.items || section.accounts || [])" :key="item.code">
                                            <td class="px-3 ps-4 small"><code>{{ item.code }}</code> {{ item.label || item.name }}</td>
                                            <td class="px-3 text-end small">{{ fmt(item.balance || item.amount || 0) }}</td>
                                        </tr>
                                    </template>
                                </tbody>
                                <tfoot class="table-light fw-bold">
                                    <tr>
                                        <td class="px-3">TOTAL ACTIF</td>
                                        <td class="px-3 text-end fs-6" style="color:#FF7900">{{ fmt(data.total_actif || data.totalAssets || 0) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- PASSIF -->
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white py-2">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-credit-card me-1"></i>PASSIF</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-3">Rubrique</th>
                                        <th class="px-3 text-end" style="width:130px">Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-for="section in (data.passif || data.liabilities || [])" :key="section.code || section.label">
                                        <tr class="table-secondary">
                                            <td class="px-3 fw-semibold small">{{ section.code }} - {{ section.label || section.name }}</td>
                                            <td class="px-3 text-end fw-bold">{{ fmt(section.total || 0) }}</td>
                                        </tr>
                                        <tr v-for="item in (section.items || section.accounts || [])" :key="item.code">
                                            <td class="px-3 ps-4 small"><code>{{ item.code }}</code> {{ item.label || item.name }}</td>
                                            <td class="px-3 text-end small">{{ fmt(item.balance || item.amount || 0) }}</td>
                                        </tr>
                                    </template>
                                </tbody>
                                <tfoot class="table-light fw-bold">
                                    <tr>
                                        <td class="px-3">TOTAL PASSIF</td>
                                        <td class="px-3 text-end fs-6" style="color:#FF7900">{{ fmt(data.total_passif || data.totalLiabilities || 0) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Equality check -->
                <div class="col-12">
                    <div class="card shadow-sm border-0 bg-light">
                        <div class="card-body py-2 text-center">
                            <span class="fw-bold fs-5" :class="isBalancedBilan ? 'text-success' : 'text-danger'">
                                <i :class="isBalancedBilan ? 'bi bi-check-circle-fill' : 'bi bi-exclamation-triangle-fill'" class="me-2"></i>
                                {{ isBalancedBilan ? 'Bilan équilibré ✓' : `Déséquilibre : ${fmt(Math.abs(totalActif - totalPassif))}` }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-2">
                <small class="text-muted">Arrêté au {{ asOfDate }}</small>
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const data = ref(null)
const loading = ref(true)
const error = ref(null)
const asOfDate = ref(new Date().toISOString().split('T')[0])

const totalActif = computed(() => data.value?.total_actif || data.value?.totalAssets || 0)
const totalPassif = computed(() => data.value?.total_passif || data.value?.totalLiabilities || 0)
const isBalancedBilan = computed(() => Math.abs(totalActif.value - totalPassif.value) < 0.01)

const fmt = (v) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(v || 0) + ' F'
const csrf = computed(() => document.querySelector('meta[name=csrf-token]')?.content || '')
const api = (path, opts = {}) => fetch(path, {
    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf.value, ...opts.headers },
    ...opts,
})

async function loadData() {
    loading.value = true; error.value = null
    try {
        const params = new URLSearchParams({ as_of_date: asOfDate.value })
        const r = await api(`/api/reports/financial-statements/balance-sheet?${params}`)
        if (!r.ok) throw new Error('Erreur chargement')
        data.value = await r.json()
    } catch (e) { error.value = e.message } finally { loading.value = false }
}

function exportPdf() {
    window.open(`/api/reports/financial-statements/balance-sheet/pdf?as_of_date=${asOfDate.value}`, '_blank')
}

onMounted(loadData)
</script>
