<template>
    <CompanyLayout pageTitle="Banque & Rapprochement">
        <div class="container-fluid pt-3 pb-5">
            <div class="isup-card p-3 mb-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h4 class="fw-bold mb-0"><i class="bi bi-bank me-2"></i>Banque & Rapprochement bancaire</h4>
                    <div class="d-flex gap-2">
                        <button class="btn isup-btn-outline" @click="openImportModal">
                            <i class="bi bi-upload me-1"></i>Importer relevé
                        </button>
                        <button class="btn isup-btn-primary" @click="newReconciliation">
                            <i class="bi bi-plus-lg me-1"></i>Nouveau rapprochement
                        </button>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="isup-card p-3 text-center">
                        <div class="text-muted small">Solde comptable</div>
                        <div class="fs-4 fw-bold text-primary">{{ formatCurrency(bankSummary.bookBalance || 0) }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="isup-card p-3 text-center">
                        <div class="text-muted small">Solde relevé</div>
                        <div class="fs-4 fw-bold text-success">{{ formatCurrency(bankSummary.statementBalance || 0) }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="isup-card p-3 text-center">
                        <div class="text-muted small">Écart</div>
                        <div class="fs-4" :class="diff === 0 ? 'fw-bold text-success' : 'fw-bold text-danger'">{{ formatCurrency(diff) }}</div>
                    </div>
                </div>
            </div>

            <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
            <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

            <div v-else class="isup-card p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table isup-table mb-0">
                        <thead>
                            <tr>
                                <th>Date</th><th>Compte bancaire</th><th>Référence</th><th>Libellé</th>
                                <th class="text-end">Débit</th><th class="text-end">Crédit</th>
                                <th>Rapproché</th><th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="transactions.length === 0">
                                <td colspan="8" class="text-center py-4 text-muted">Aucune transaction bancaire</td>
                            </tr>
                            <tr v-for="tx in transactions" :key="tx.id">
                                <td>{{ formatDate(tx.date || tx.date_operation) }}</td>
                                <td>{{ tx.bank_account || tx.compte_bancaire || '—' }}</td>
                                <td>{{ tx.reference || tx.reference || '—' }}</td>
                                <td>{{ tx.label || tx.libelle || '—' }}</td>
                                <td class="text-end text-success">{{ tx.type === 'deposit' || tx.type === 'credit' ? formatCurrency(tx.amount || tx.montant) : '—' }}</td>
                                <td class="text-end text-danger">{{ tx.type === 'withdrawal' || tx.type === 'debit' ? formatCurrency(tx.amount || tx.montant) : '—' }}</td>
                                <td>
                                    <span :class="'badge ' + (tx.is_matched || tx.rapproche ? 'bg-success' : 'bg-secondary')">
                                        {{ tx.is_matched || tx.rapproche ? 'Oui' : 'Non' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm isup-btn-ghost" @click="toggleMatch(tx)">
                                        <i :class="tx.is_matched || tx.rapproche ? 'bi bi-arrow-counterclockwise' : 'bi bi-check-circle'"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Import Statement Modal -->
            <div class="modal fade" id="importModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Importer un relevé bancaire</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Compte bancaire</label>
                                <select class="form-select" v-model="importForm.account_id">
                                    <option value="">Sélectionner</option>
                                    <option v-for="acc in bankAccounts" :key="acc.id" :value="acc.id">{{ acc.name || acc.account_name }} ({{ acc.account_number || acc.numero }})</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fichier relevé (CSV/OFX)</label>
                                <input type="file" class="form-control" @change="onFileChange" accept=".csv,.ofx">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Solde de fin de période</label>
                                <input v-model.number="importForm.closing_balance" type="number" class="form-control">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn isup-btn-ghost" data-bs-dismiss="modal">Annuler</button>
                            <button type="button" class="btn isup-btn-primary" :disabled="importing" @click="importStatement">
                                <span v-if="importing" class="spinner-border spinner-border-sm me-1"></span>
                                Importer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </CompanyLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import CompanyLayout from '../../../../Layouts/CompanyLayout.vue'

const transactions = ref([])
const bankAccounts = ref([])
const loading = ref(true)
const error = ref(null)
const importing = ref(false)

const bankSummary = reactive({ bookBalance: 0, statementBalance: 0 })
const importForm = reactive({ account_id: '', closing_balance: 0, file: null })

const diff = computed(() => bankSummary.bookBalance - bankSummary.statementBalance)

const csrfToken = computed(() => document.querySelector('meta[name=csrf-token]')?.content || '')

const api = (path, opts = {}) =>
    fetch(path, {
        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken.value, ...opts.headers },
        ...opts,
    })

const load = async () => {
    loading.value = true
    error.value = null
    try {
        const r = await api('/api/company/bank-reconciliations')
        if (r.ok) {
            const data = await r.json()
            transactions.value = data.transactions || data.data || []
            bankSummary.bookBalance = data.book_balance || data.solde_comptable || 0
            bankSummary.statementBalance = data.statement_balance || data.solde_releve || 0
        }
    } catch (e) { error.value = e.message } finally { loading.value = false }
}

const openImportModal = () => {
    importForm.account_id = ''
    importForm.closing_balance = 0
    importForm.file = null
    const modal = new bootstrap.Modal(document.getElementById('importModal'))
    modal.show()
}

const onFileChange = (e) => { importForm.file = e.target.files[0] }

const importStatement = async () => {
    if (!importForm.file || !importForm.account_id) return
    importing.value = true
    try {
        const formData = new FormData()
        formData.append('file', importForm.file)
        formData.append('account_id', importForm.account_id)
        formData.append('closing_balance', importForm.closing_balance)
        const r = await fetch('/api/company/banque/import-statement', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken.value },
            body: formData,
        })
        if (!r.ok) throw new Error('Erreur import')
        bootstrap.Modal.getInstance(document.getElementById('importModal'))?.hide()
        await load()
    } catch (e) { error.value = e.message } finally { importing.value = false }
}

const newReconciliation = async () => {
    try {
        const r = await api('/api/company/bank-reconciliations', { method: 'POST', body: '{}' })
        if (r.ok) await load()
    } catch (e) { error.value = e.message }
}

const toggleMatch = async (tx) => {
    try {
        const r = await api(`/api/company/bank-reconciliations/${tx.id}/toggle`, { method: 'POST' })
        if (r.ok) await load()
    } catch (e) { error.value = e.message }
}

const formatCurrency = (v) => {
    if (v === null || v === undefined || isNaN(v)) return '0'
    return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(v) + ' FCFA'
}
const formatDate = (d) => d ? new Date(d).toLocaleDateString('fr-FR') : '—'

onMounted(load)
</script>
