<template>
    <CompanyLayout pageTitle="Journaux comptables">
        <div class="container-fluid pt-3 pb-5">
            <div class="isup-card p-3 mb-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h4 class="fw-bold mb-0"><i class="bi bi-journal-text me-2"></i>Journaux comptables</h4>
                    <button class="btn isup-btn-primary" @click="openCreateModal">
                        <i class="bi bi-plus-lg me-1"></i>Nouvelle écriture
                    </button>
                </div>
            </div>

            <!-- Journal type filter tabs -->
            <div class="isup-card p-2 mb-3">
                <div class="d-flex flex-wrap gap-2">
                    <button v-for="jt in journalTypes" :key="jt.type"
                        class="btn btn-sm" :class="filter.type === jt.type ? 'btn-primary' : 'btn-outline-secondary'"
                        @click="filter.type = jt.type; loadJournals()">
                        {{ jt.label }}
                    </button>
                    <button class="btn btn-sm" :class="!filter.type ? 'btn-primary' : 'btn-outline-secondary'"
                        @click="filter.type = ''; loadJournals()">
                        Tous
                    </button>
                </div>
            </div>

            <div v-if="loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
            <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

            <div v-else class="isup-card p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table isup-table mb-0">
                        <thead>
                            <tr>
                                <th>Référence</th>
                                <th>N° Pièce</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th class="text-end">Débit</th>
                                <th class="text-end">Crédit</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="journals.length === 0">
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    Aucune écriture trouvée
                                </td>
                            </tr>
                            <tr v-for="j in journals" :key="j.id">
                                <td class="fw-semibold">{{ j.reference }}</td>
                                <td>{{ j.numero_piece || '—' }}</td>
                                <td>{{ formatDate(j.entry_date) }}</td>
                                <td><span class="badge bg-info">{{ journalTypeLabel(j.journal_type) }}</span></td>
                                <td class="text-truncate" style="max-width:250px">{{ j.description || '—' }}</td>
                                <td class="text-end">{{ formatCurrency(j.debit_total) }}</td>
                                <td class="text-end">{{ formatCurrency(j.credit_total) }}</td>
                                <td><span :class="'badge ' + statusBadge(j.status)">{{ statusLabel(j.status) }}</span></td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm isup-btn-ghost" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#" @click.prevent="viewJournal(j)"><i class="bi bi-eye me-2"></i>Voir</a></li>
                                            <li v-if="j.status === 'draft'"><a class="dropdown-item" href="#" @click.prevent="editJournal(j)"><i class="bi bi-pencil me-2"></i>Modifier</a></li>
                                            <li v-if="j.status === 'draft'"><a class="dropdown-item" href="#" @click.prevent="deleteJournal(j)"><i class="bi bi-trash me-2 text-danger"></i>Supprimer</a></li>
                                            <li v-if="j.status === 'draft'"><a class="dropdown-item" href="#" @click.prevent="postJournal(j)"><i class="bi bi-check-circle me-2 text-success"></i>Poster</a></li>
                                            <li v-if="j.status === 'posted' && !j.is_reversal"><a class="dropdown-item" href="#" @click.prevent="reverseJournal(j)"><i class="bi bi-arrow-return-left me-2 text-warning"></i>Extourner</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Journal Detail Modal -->
            <div class="modal fade" id="journalModal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ viewingJournal?.reference || 'Détail écriture' }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" v-if="viewingJournal">
                            <div class="row mb-3">
                                <div class="col-md-3"><small class="text-muted">Date :</small><br>{{ formatDate(viewingJournal.entry_date) }}</div>
                                <div class="col-md-3"><small class="text-muted">Type :</small><br>{{ journalTypeLabel(viewingJournal.journal_type) }}</div>
                                <div class="col-md-3"><small class="text-muted">N° Pièce :</small><br>{{ viewingJournal.numero_piece || '—' }}</div>
                                <div class="col-md-3"><small class="text-muted">Statut :</small><br><span :class="'badge ' + statusBadge(viewingJournal.status)">{{ statusLabel(viewingJournal.status) }}</span></div>
                            </div>
                            <p v-if="viewingJournal.description"><small class="text-muted">Description :</small><br>{{ viewingJournal.description }}</p>
                            <table class="table table-sm">
                                <thead><tr><th>Compte</th><th>Libellé</th><th class="text-end">Débit</th><th class="text-end">Crédit</th></tr></thead>
                                <tbody>
                                    <tr v-for="line in viewingJournal.lines" :key="line.id">
                                        <td>{{ line.account_code || line.account?.code }} - {{ line.account_name || line.account?.name }}</td>
                                        <td>{{ line.label }}</td>
                                        <td class="text-end">{{ line.debit > 0 ? formatCurrency(line.debit) : '—' }}</td>
                                        <td class="text-end">{{ line.credit > 0 ? formatCurrency(line.credit) : '—' }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold">
                                        <td colspan="2">TOTAUX</td>
                                        <td class="text-end">{{ formatCurrency(viewingJournal.debit_total) }}</td>
                                        <td class="text-end">{{ formatCurrency(viewingJournal.credit_total) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Create/Edit Modal -->
            <div class="modal fade" id="journalFormModal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ editingJournal ? 'Modifier' : 'Nouvelle' }} écriture</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Type de journal</label>
                                    <select v-model="form.journal_type" class="form-select">
                                        <option v-for="jt in journalTypes" :key="jt.type" :value="jt.type">{{ jt.label }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Date</label>
                                    <input v-model="form.entry_date" type="date" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Référence</label>
                                    <input v-model="form.reference" class="form-control" placeholder="Auto si vide">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <input v-model="form.description" class="form-control" placeholder="Libellé de l'écriture">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Lignes d'écriture (partie double)</label>
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th style="width:200px">Compte</th>
                                                <th>Libellé</th>
                                                <th style="width:130px" class="text-end">Débit</th>
                                                <th style="width:130px" class="text-end">Crédit</th>
                                                <th style="width:40px"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(line, i) in form.lines" :key="i">
                                                <td>
                                                    <select v-model="line.account_id" class="form-select form-select-sm">
                                                        <option value="">Sélectionner</option>
                                                        <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.code }} - {{ acc.name }}</option>
                                                    </select>
                                                </td>
                                                <td><input v-model="line.label" class="form-control form-control-sm" placeholder="Libellé ligne"></td>
                                                <td><input v-model.number="line.debit" type="number" min="0" step="100" class="form-control form-control-sm text-end" @input="line.credit = 0"></td>
                                                <td><input v-model.number="line.credit" type="number" min="0" step="100" class="form-control form-control-sm text-end" @input="line.debit = 0"></td>
                                                <td><button class="btn btn-sm btn-outline-danger" @click="removeFormLine(i)"><i class="bi bi-x"></i></button></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <button class="btn btn-sm isup-btn-outline" @click="addFormLine"><i class="bi bi-plus"></i> Ajouter ligne</button>
                                    <span class="ms-3" :class="isBalanced ? 'text-success' : 'text-danger'">
                                        <i :class="isBalanced ? 'bi bi-check-circle' : 'bi bi-exclamation-circle'"></i>
                                        {{ isBalanced ? 'Équilibré' : `Déséquilibré (${formatCurrency(Math.abs(formTotalDebit - formTotalCredit))})` }}
                                    </span>
                                    <div class="mt-1 small text-muted">Total débit : {{ formatCurrency(formTotalDebit) }} | Total crédit : {{ formatCurrency(formTotalCredit) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn isup-btn-ghost" data-bs-dismiss="modal">Annuler</button>
                            <button type="button" class="btn isup-btn-primary" :disabled="submitting || !isBalanced" @click="saveJournal">
                                <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                                {{ editingJournal ? 'Mettre à jour' : 'Créer' }}
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

const journals = ref([])
const accounts = ref([])
const loading = ref(true)
const error = ref(null)
const submitting = ref(false)
const editingJournal = ref(null)
const viewingJournal = ref(null)

const filter = reactive({ type: '' })

const journalTypes = ref([
    { type: 'achat', label: 'Achats', prefix: 'HA' },
    { type: 'vente', label: 'Ventes', prefix: 'VT' },
    { type: 'banque', label: 'Banque', prefix: 'BQ' },
    { type: 'caisse', label: 'Caisse', prefix: 'CA' },
    { type: 'operations_diverses', label: 'Op. Diverses', prefix: 'OD' },
    { type: 'od', label: 'OD (À nouveau)', prefix: 'AN' },
    { type: 'salaire', label: 'Salaires', prefix: 'PA' },
    { type: 'investissement', label: 'Investissements', prefix: 'IN' },
    { type: 'paie', label: 'Paie', prefix: 'PA' },
])

const form = reactive({
    journal_type: 'achat',
    entry_date: new Date().toISOString().split('T')[0],
    reference: '',
    description: '',
    lines: [{ account_id: '', label: '', debit: 0, credit: 0 }],
})

const formTotalDebit = computed(() => form.lines.reduce((s, l) => s + (parseFloat(l.debit) || 0), 0))
const formTotalCredit = computed(() => form.lines.reduce((s, l) => s + (parseFloat(l.credit) || 0), 0))
const isBalanced = computed(() => Math.abs(formTotalDebit.value - formTotalCredit.value) < 0.01 && formTotalDebit.value > 0)

const csrfToken = computed(() => document.querySelector('meta[name=csrf-token]')?.content || '')

const api = (path, opts = {}) =>
    fetch(path, {
        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken.value, ...opts.headers },
        ...opts,
    })

const loadJournals = async () => {
    loading.value = true
    error.value = null
    try {
        const params = new URLSearchParams()
        if (filter.type) params.set('journal_type', filter.type)
        const r = await api(`/api/company/accounting/journals?${params}`)
        if (!r.ok) throw new Error('Erreur chargement journaux')
        journals.value = await r.json()
    } catch (e) { error.value = e.message } finally { loading.value = false }
}

const loadAccounts = async () => {
    try {
        const r = await api('/api/company/accounting/accounts')
        if (r.ok) accounts.value = await r.json()
    } catch (e) { console.warn('Erreur chargement comptes:', e) }
}

const viewJournal = async (j) => {
    try {
        const r = await api(`/api/company/accounting/journals/${j.id}`)
        if (r.ok) {
            const data = await r.json()
            viewingJournal.value = data.journal || data
            const modal = new bootstrap.Modal(document.getElementById('journalModal'))
            modal.show()
        }
    } catch (e) { error.value = e.message }
}

const openCreateModal = () => {
    editingJournal.value = null
    form.journal_type = 'achat'
    form.entry_date = new Date().toISOString().split('T')[0]
    form.reference = ''
    form.description = ''
    form.lines = [{ account_id: '', label: '', debit: 0, credit: 0 }]
    const modal = new bootstrap.Modal(document.getElementById('journalFormModal'))
    modal.show()
}

const editJournal = async (j) => {
    editingJournal.value = j
    form.journal_type = j.journal_type
    form.entry_date = j.entry_date
    form.reference = j.reference || ''
    form.description = j.description || ''
    form.lines = j.lines?.length > 0
        ? j.lines.map(l => ({ account_id: l.account_id, label: l.label || l.libelle || '', debit: l.debit || 0, credit: l.credit || 0 }))
        : [{ account_id: '', label: '', debit: 0, credit: 0 }]
    const modal = new bootstrap.Modal(document.getElementById('journalFormModal'))
    modal.show()
}

const addFormLine = () => form.lines.push({ account_id: '', label: '', debit: 0, credit: 0 })
const removeFormLine = (i) => { if (form.lines.length > 1) form.lines.splice(i, 1) }

const saveJournal = async () => {
    submitting.value = true
    try {
        const payload = {
            journal_type: form.journal_type,
            entry_date: form.entry_date,
            reference: form.reference || null,
            description: form.description,
            lines: form.lines.map(l => ({
                account_id: l.account_id,
                label: l.label,
                debit: l.debit || 0,
                credit: l.credit || 0,
            })),
        }
        const url = editingJournal.value
            ? `/api/company/accounting/journals/${editingJournal.value.id}`
            : '/api/company/accounting/journals'
        const method = editingJournal.value ? 'PUT' : 'POST'
        const r = await api(url, { method, body: JSON.stringify(payload) })
        if (!r.ok) {
            const err = await r.json().catch(() => ({}))
            throw new Error(err.message || 'Erreur sauvegarde')
        }
        bootstrap.Modal.getInstance(document.getElementById('journalFormModal'))?.hide()
        await loadJournals()
    } catch (e) { error.value = e.message } finally { submitting.value = false }
}

const deleteJournal = async (j) => {
    if (!confirm('Supprimer cette écriture ?')) return
    try {
        const r = await api(`/api/company/accounting/journals/${j.id}`, { method: 'DELETE' })
        if (!r.ok) throw new Error('Erreur suppression')
        await loadJournals()
    } catch (e) { error.value = e.message }
}

const postJournal = async (j) => {
    if (!confirm('Poster cette écriture ? Elle ne pourra plus être modifiée.')) return
    try {
        const r = await api(`/api/company/accounting/journals/${j.id}/post`, { method: 'POST' })
        if (!r.ok) {
            const err = await r.json().catch(() => ({}))
            throw new Error(err.message || 'Erreur validation')
        }
        await loadJournals()
    } catch (e) { error.value = e.message }
}

const reverseJournal = async (j) => {
    if (!confirm('Créer une extourne de cette écriture ?')) return
    try {
        const r = await api(`/api/company/accounting/journals/${j.id}/reverse`, { method: 'POST' })
        if (!r.ok) throw new Error('Erreur extourne')
        await loadJournals()
    } catch (e) { error.value = e.message }
}

const formatCurrency = (v) => {
    if (v === null || v === undefined || isNaN(v)) return '0'
    return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(v) + ' FCFA'
}
const formatDate = (d) => d ? new Date(d).toLocaleDateString('fr-FR') : '—'
const journalTypeLabel = (t) => journalTypes.value.find(jt => jt.type === t)?.label || t
const statusLabel = (s) => ({ draft: 'Brouillon', posted: 'Posté', closed: 'Clôturé' }[s] || s)
const statusBadge = (s) => ({ draft: 'bg-secondary', posted: 'bg-success', closed: 'bg-dark' }[s] || 'bg-secondary')

onMounted(() => { loadJournals(); loadAccounts() })
</script>
