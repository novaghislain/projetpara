<template>
    <div class="container-fluid py-3">
        <div class="d-flex align-items-center gap-2 mb-3">
            <h4 class="fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>Balance Âgée</h4>
            <button class="btn btn-outline-primary btn-sm ms-auto" @click="loadData">
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
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Type</label>
                        <select v-model="type" class="form-select form-select-sm">
                            <option value="customer">Clients</option>
                            <option value="supplier">Fournisseurs</option>
                        </select>
                    </div>
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
            <!-- Summary -->
            <div class="row g-2 mb-3">
                <div class="col-md-2">
                    <div class="card shadow-sm border-0 bg-light">
                        <div class="card-body py-2 text-center">
                            <small class="text-muted d-block">Solde total</small>
                            <span class="fw-bold">{{ fmt(data.total_balance || data.totalBalance || 0) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card shadow-sm border-0 bg-light">
                        <div class="card-body py-2 text-center">
                            <small class="text-muted d-block">0-30 jours</small>
                            <span class="fw-bold text-success">{{ fmt(data.total_0_30 || 0) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card shadow-sm border-0 bg-light">
                        <div class="card-body py-2 text-center">
                            <small class="text-muted d-block">31-60 jours</small>
                            <span class="fw-bold text-warning">{{ fmt(data.total_31_60 || 0) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card shadow-sm border-0 bg-light">
                        <div class="card-body py-2 text-center">
                            <small class="text-muted d-block">61-90 jours</small>
                            <span class="fw-bold" style="color:#df6c00">{{ fmt(data.total_61_90 || 0) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card shadow-sm border-0 bg-light">
                        <div class="card-body py-2 text-center">
                            <small class="text-muted d-block">91+ jours</small>
                            <span class="fw-bold text-danger">{{ fmt(data.total_91_plus || 0) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card shadow-sm border-0 bg-light">
                        <div class="card-body py-2 text-center">
                            <small class="text-muted d-block">Nombre</small>
                            <span class="fw-bold">{{ data.total_accounts || data.totalAccounts || data.accounts?.length || 0 }}</span>
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
                                <th class="px-3 text-end">Solde</th>
                                <th class="px-3 text-end">0-30 j</th>
                                <th class="px-3 text-end">31-60 j</th>
                                <th class="px-3 text-end">61-90 j</th>
                                <th class="px-3 text-end">91+ j</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="acc in (data.accounts || data.rows || [])" :key="acc.account_code || acc.accountCode || acc.code">
                                <td class="px-3"><code>{{ acc.account_code || acc.accountCode || acc.code }}</code></td>
                                <td class="px-3">{{ acc.account_name || acc.accountLabel || acc.name }}</td>
                                <td class="px-3 text-end fw-bold">{{ fmt(acc.balance || 0) }}</td>
                                <td class="px-3 text-end" :class="(acc['0_30'] || acc['0-30'] || 0) > 0 ? 'text-success' : ''">
                                    {{ fmt(acc['0_30'] || acc['0-30'] || 0) }}
                                </td>
                                <td class="px-3 text-end" :class="(acc['31_60'] || acc['31-60'] || 0) > 0 ? 'text-warning' : ''">
                                    {{ fmt(acc['31_60'] || acc['31-60'] || 0) }}
                                </td>
                                <td class="px-3 text-end" :class="(acc['61_90'] || acc['61-90'] || 0) > 0 ? 'text-warning' : ''">
                                    {{ fmt(acc['61_90'] || acc['61-90'] || 0) }}
                                </td>
                                <td class="px-3 text-end fw-bold" :class="(acc['91_plus'] || acc['91+'] || 0) > 0 ? 'text-danger' : ''">
                                    {{ fmt(acc['91_plus'] || acc['91+'] || 0) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="(!data.accounts?.length && !data.rows?.length)" class="text-center text-muted py-4">
                    Aucun compte {{ type === 'customer' ? 'client' : 'fournisseur' }} trouvé
                </div>
            </div>

            <div class="mt-2">
                <small class="text-muted">{{ data.total_accounts || data.totalAccounts || data.accounts?.length || 0 }} comptes · Arrêté au {{ asOfDate }}</small>
            </div>
        </template>
    </div>
</template>

<script setup>
/*
 * AgingReport.vue - Balance âgée (clients / fournisseurs)
 *
 * Affiche la balance âgée des comptes clients ou fournisseurs avec
 * le découpage par tranches d'âge : 0-30 jours, 31-60 jours,
 * 61-90 jours et 91+ jours. Permet le filtrage par type
 * (client/fournisseur) et par date d'arrêté, ainsi que l'export PDF.
 * Des cartes récapitulatives présentent les totaux par tranche.
 */
import { ref, computed, onMounted } from 'vue'

// Propriété : type par défaut (client ou fournisseur)
const props = defineProps({
    type: { type: String, default: 'customer' },
})

// Données du rapport et états de chargement
const data = ref(null)
const loading = ref(false)
const type = ref(props.type)
const asOfDate = ref(new Date().toISOString().split('T')[0])
const error = ref(null)

// Formateur monétaire en francs CFA
const fmt = (v) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(v ?? 0) + ' F'
// Jeton CSRF pour les requêtes sécurisées
const csrf = computed(() => document.querySelector('meta[name=csrf-token]')?.content || '')
// Fonction utilitaire d'appel API
const api = (path, opts = {}) => fetch(path, {
    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf.value, ...opts.headers },
    ...opts,
})

// Chargement des données de la balance âgée depuis l'API
async function loadData() {
    loading.value = true; error.value = null
    try {
        const params = new URLSearchParams({ type: type.value, as_of_date: asOfDate.value })
        const r = await api(`/api/reports/financial-statements/aging?${params}`)
        if (!r.ok) throw new Error('Erreur chargement')
        data.value = await r.json()
    } catch (e) { error.value = e.message } finally { loading.value = false }
}

// Export PDF du rapport (ouverture dans un nouvel onglet)
function exportPdf() {
    const params = new URLSearchParams({ type: type.value, as_of_date: asOfDate.value })
    window.open(`/api/reports/financial-statements/aging/pdf?${params}`, '_blank')
}

// Chargement automatique au montage du composant
onMounted(loadData)
</script>
