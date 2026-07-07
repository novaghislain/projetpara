<template>
    <CompanyLayout pageTitle="TVA & Déclarations fiscales">
        <div class="container-fluid pt-3 pb-5">
            <div class="isup-card p-3 mb-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h4 class="fw-bold mb-0"><i class="bi bi-receipt me-2"></i>TVA & Déclarations fiscales</h4>
                    <button class="btn isup-btn-primary" @click="openDeclarationModal">
                        <i class="bi bi-plus-lg me-1"></i>Nouvelle déclaration
                    </button>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="isup-card p-3 text-center">
                        <div class="text-muted small">TVA collectée</div>
                        <div class="fs-4 fw-bold text-danger">{{ formatCurrency(tvaSummary.collected || 0) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="isup-card p-3 text-center">
                        <div class="text-muted small">TVA déductible</div>
                        <div class="fs-4 fw-bold text-success">{{ formatCurrency(tvaSummary.deductible || 0) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="isup-card p-3 text-center">
                        <div class="text-muted small">TVA nette à payer</div>
                        <div class="fs-4 fw-bold text-primary">{{ formatCurrency(tvaSummary.net || 0) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="isup-card p-3 text-center">
                        <div class="text-muted small">Crédit de TVA</div>
                        <div class="fs-4 fw-bold text-info">{{ formatCurrency(tvaSummary.credit || 0) }}</div>
                    </div>
                </div>
            </div>

            <!-- Taux TVA -->
            <div class="isup-card p-3 mb-3">
                <h5 class="fw-bold mb-3"><i class="bi bi-percent me-2"></i>Taux de TVA</h5>
                <div class="table-responsive">
                    <table class="table isup-table table-sm mb-0">
                        <thead>
                            <tr><th>Code</th><th>Libellé</th><th>Taux</th><th>Compte</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <tr v-for="t in tvaRates" :key="t.code">
                                <td class="fw-semibold">{{ t.code }}</td>
                                <td>{{ t.label }}</td>
                                <td>{{ t.rate }}%</td>
                                <td>{{ t.account || '—' }}</td>
                                <td>
                                    <button class="btn btn-sm isup-btn-ghost" @click="editTaux(t)"><i class="bi bi-pencil"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Déclarations -->
            <div class="isup-card p-3 mb-3">
                <h5 class="fw-bold mb-3"><i class="bi bi-file-text me-2"></i>Déclarations</h5>
                <div v-if="loading" class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>
                <div class="table-responsive" v-else>
                    <table class="table isup-table mb-0">
                        <thead>
                            <tr><th>Période</th><th>CA Brut</th><th>TVA due</th><th>TVA déd.</th><th>Net</th><th>Statut</th><th class="text-end">Actions</th></tr>
                        </thead>
                        <tbody>
                            <tr v-if="declarations.length === 0">
                                <td colspan="7" class="text-center text-muted py-3">Aucune déclaration</td>
                            </tr>
                            <tr v-for="d in declarations" :key="d.id || d.period">
                                <td class="fw-semibold">{{ d.period || d.periode }}</td>
                                <td>{{ formatCurrency(d.gross_total || d.ca_brut || 0) }}</td>
                                <td>{{ formatCurrency(d.tva_due || d.collected || 0) }}</td>
                                <td>{{ formatCurrency(d.tva_deductible || d.deductible || 0) }}</td>
                                <td class="fw-semibold">{{ formatCurrency(d.net || d.net_a_payer || 0) }}</td>
                                <td><span :class="'badge ' + (d.status === 'submitted' ? 'bg-success' : d.status === 'draft' ? 'bg-secondary' : 'bg-primary')">{{ d.status === 'submitted' ? 'Soumise' : d.status === 'draft' ? 'Brouillon' : 'En cours' }}</span></td>
                                <td class="text-end">
                                    <button class="btn btn-sm isup-btn-ghost" @click="viewDeclaration(d)"><i class="bi bi-eye"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </CompanyLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import CompanyLayout from '../../../../Layouts/CompanyLayout.vue'
import { computed } from 'vue'

const loading = ref(true)
const declarations = ref([])
const tvaRates = ref([{ code: 'TVA18', label: 'TVA 18%', rate: 18, account: '441' }, { code: 'TVA10', label: 'TVA 10%', rate: 10, account: '441' }])
const tvaSummary = reactive({ collected: 0, deductible: 0, net: 0, credit: 0 })

const csrfToken = computed(() => document.querySelector('meta[name=csrf-token]')?.content || '')

const api = (path, opts = {}) =>
    fetch(path, {
        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken.value, ...opts.headers },
        ...opts,
    })

const load = async () => {
    loading.value = true
    try {
        const r = await api('/api/company/accounting/stats')
        if (r.ok) {
            const data = await r.json()
            tvaSummary.collected = data.by_type?.vente?.credit || 0
            tvaSummary.deductible = data.by_type?.achat?.debit || 0
            tvaSummary.net = Math.max(0, tvaSummary.collected * 0.18 - tvaSummary.deductible * 0.18)
            tvaSummary.credit = Math.max(0, tvaSummary.deductible * 0.18 - tvaSummary.collected * 0.18)
        }
    } catch (e) { console.warn(e) } finally { loading.value = false }
}

const openDeclarationModal = () => { /* future implementation */ }
const editTaux = (t) => { /* future implementation */ }
const viewDeclaration = (d) => { /* future implementation */ }

const formatCurrency = (v) => {
    if (v === null || v === undefined || isNaN(v)) return '0'
    return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(v) + ' FCFA'
}

onMounted(load)
</script>
