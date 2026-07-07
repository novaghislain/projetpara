<template>
    <div class="container-fluid py-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0"><i class="bi bi-pencil-square me-2"></i>Écritures comptables</h4>
            <button class="btn btn-primary btn-sm" @click="showCreateModal = true">
                <i class="bi bi-plus-lg me-1"></i>Nouvelle écriture
            </button>
        </div>

        <!-- Filters -->
        <div class="card shadow-sm mb-3">
            <div class="card-body py-2">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Journal</label>
                        <select v-model="filters.journal_id" class="form-select form-select-sm" @change="loadEntries">
                            <option value="">Tous</option>
                            <option v-for="j in journals" :key="j.id" :value="j.id">{{ j.code }} — {{ j.label }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Du</label>
                        <input v-model="filters.date_from" type="date" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Au</label>
                        <input v-model="filters.date_to" type="date" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Statut</label>
                        <select v-model="filters.status" class="form-select form-select-sm">
                            <option value="">Tous</option>
                            <option value="draft">Brouillon</option>
                            <option value="posted">Validé</option>
                            <option value="cancelled">Annulé</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-1">
                        <button class="btn btn-outline-primary btn-sm flex-fill" @click="loadEntries">
                            <i class="bi bi-search"></i> Filtrer
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" @click="resetFilters">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary"></div>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <!-- Table -->
        <div v-else class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3 py-2">Date</th>
                            <th class="px-3 py-2">Référence</th>
                            <th class="px-3 py-2">Journal</th>
                            <th class="px-3 py-2">Libellé</th>
                            <th class="px-3 py-2 text-end">Débit</th>
                            <th class="px-3 py-2 text-end">Crédit</th>
                            <th class="px-3 py-2">Statut</th>
                            <th class="px-3 py-2 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="entries.length === 0">
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Aucune écriture trouvée
                            </td>
                        </tr>
                        <tr v-for="e in entries" :key="e.id" class="align-middle" style="cursor:pointer" @click="viewEntry(e)">
                            <td class="px-3 py-2">{{ formatDate(e.entry_date || e.date) }}</td>
                            <td class="px-3 py-2"><code class="bg-light px-1 rounded">{{ e.reference }}</code></td>
                            <td class="px-3 py-2"><span class="badge bg-dark">{{ e.journal?.code || e.journal_code }}</span></td>
                            <td class="px-3 py-2 text-truncate" style="max-width:200px">{{ e.description || e.libelle }}</td>
                            <td class="px-3 py-2 text-end fw-medium text-success">{{ fmt(e.total_debit || 0) }}</td>
                            <td class="px-3 py-2 text-end fw-medium" style="color:#163A5E">{{ fmt(e.total_credit || 0) }}</td>
                            <td class="px-3 py-2"><span :class="statusBadge(e.status)">{{ statusLabel(e.status) }}</span></td>
                            <td class="px-3 py-2 text-end">
                                <button v-if="e.status === 'draft'" class="btn btn-sm btn-outline-success" title="Valider" @click.stop="postEntry(e)">
                                    <i class="bi bi-check-circle"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary ms-1" title="Voir" @click.stop="viewEntry(e)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination" class="d-flex justify-content-between align-items-center px-3 py-2 border-top small">
                <span class="text-muted">Page {{ pagination.current_page }} / {{ pagination.last_page }} ({{ pagination.total }})</span>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item" :class="{ disabled: !pagination.prev_page_url }">
                            <button class="page-link" @click="goPage(pagination.current_page - 1)">←</button>
                        </li>
                        <li class="page-item" :class="{ disabled: !pagination.next_page_url }">
                            <button class="page-link" @click="goPage(pagination.current_page + 1)">→</button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- Create Entry Modal -->
        <div v-if="showCreateModal" class="modal d-block" tabindex="-1" style="background:rgba(0,0,0,0.5)">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil-square me-2" style="color:#FF7900"></i>Nouvelle écriture</h5>
                        <button type="button" class="btn-close" @click="showCreateModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Header fields -->
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label small">Journal *</label>
                                <select v-model="form.journal_id" class="form-select form-select-sm" required>
                                    <option value="">Sélectionner</option>
                                    <option v-for="j in journals" :key="j.id" :value="j.id">{{ j.code }} — {{ j.label }}</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Date *</label>
                                <input v-model="form.entry_date" type="date" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Référence</label>
                                <input v-model="form.reference" class="form-control form-control-sm" placeholder="Auto si vide">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">N° Pièce</label>
                                <input v-model="form.numero_piece" class="form-control form-control-sm" placeholder="Optionnel">
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Libellé *</label>
                                <input v-model="form.description" class="form-control form-control-sm" placeholder="Description de l'écriture" required>
                            </div>
                        </div>

                        <!-- Lines table -->
                        <label class="form-label small fw-semibold">Lignes d'écriture (partie double)</label>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:220px">Compte</th>
                                        <th>Libellé ligne</th>
                                        <th style="width:130px" class="text-end">Débit</th>
                                        <th style="width:130px" class="text-end">Crédit</th>
                                        <th style="width:36px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(line, i) in form.lines" :key="i">
                                        <td>
                                            <select v-model="line.account_id" class="form-select form-select-sm">
                                                <option value="">Sélectionner</option>
                                                <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.code }} — {{ acc.name }}</option>
                                            </select>
                                        </td>
                                        <td><input v-model="line.label" class="form-control form-control-sm" placeholder="Libellé"></td>
                                        <td><input v-model.number="line.debit" type="number" min="0" class="form-control form-control-sm text-end" @input="line.credit = 0" placeholder="0"></td>
                                        <td><input v-model.number="line.credit" type="number" min="0" class="form-control form-control-sm text-end" @input="line.debit = 0" placeholder="0"></td>
                                        <td>
                                            <button v-if="form.lines.length > 2" class="btn btn-sm btn-outline-danger py-0 px-1" @click="removeLine(i)">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold">
                                        <td colspan="2" class="text-end">TOTAUX</td>
                                        <td class="text-end" :class="isBalanced ? 'text-success' : 'text-danger'">{{ fmt(formTotalDebit) }}</td>
                                        <td class="text-end" :class="isBalanced ? 'text-success' : 'text-danger'">{{ fmt(formTotalCredit) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <button class="btn btn-sm btn-outline-primary" @click="addLine"><i class="bi bi-plus"></i> Ajouter ligne</button>
                            <span class="small" :class="isBalanced ? 'text-success' : 'text-danger'">
                                <i :class="isBalanced ? 'bi bi-check-circle-fill' : 'bi bi-exclamation-triangle-fill'"></i>
                                {{ isBalanced ? 'Équilibrée ✓' : `Déséquilibre : ${fmt(Math.abs(formTotalDebit - formTotalCredit))}` }}
                            </span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light btn-sm" @click="showCreateModal = false">Annuler</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" :disabled="!isBalanced || submitting" @click="saveEntry('draft')">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                            <i class="bi bi-save me-1"></i>Brouillon
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" :disabled="!isBalanced || submitting" @click="saveEntry('posted')">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                            <i class="bi bi-check-lg me-1"></i>Valider & Poster
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Entry Modal -->
        <div v-if="viewingEntry" class="modal d-block" tabindex="-1" style="background:rgba(0,0,0,0.5)">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Détail écriture — {{ viewingEntry.reference }}</h5>
                        <button type="button" class="btn-close" @click="viewingEntry = null"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2 mb-3 small">
                            <div class="col-md-3"><strong>Date :</strong> {{ formatDate(viewingEntry.entry_date || viewingEntry.date) }}</div>
                            <div class="col-md-3"><strong>Journal :</strong> {{ viewingEntry.journal?.code }}</div>
                            <div class="col-md-3"><strong>Statut :</strong> <span :class="statusBadge(viewingEntry.status)">{{ statusLabel(viewingEntry.status) }}</span></div>
                            <div class="col-md-3"><strong>N° Pièce :</strong> {{ viewingEntry.numero_piece || '—' }}</div>
                            <div class="col-12"><strong>Libellé :</strong> {{ viewingEntry.description || viewingEntry.libelle }}</div>
                        </div>
                        <table class="table table-sm table-bordered small">
                            <thead class="table-light">
                                <tr><th>Compte</th><th>Libellé</th><th class="text-end">Débit</th><th class="text-end">Crédit</th></tr>
                            </thead>
                            <tbody>
                                <tr v-for="line in (viewingEntry.lines || viewingEntry.lignes || [])" :key="line.id">
                                    <td>{{ line.account?.code || line.account_code }} — {{ line.account?.name || line.account_label }}</td>
                                    <td>{{ line.label || line.libelle }}</td>
                                    <td class="text-end">{{ line.debit > 0 ? fmt(line.debit) : '—' }}</td>
                                    <td class="text-end">{{ line.credit > 0 ? fmt(line.credit) : '—' }}</td>
                                </tr>
                            </tbody>
                            <tfoot class="fw-bold">
                                <tr>
                                    <td colspan="2" class="text-end">TOTAUX</td>
                                    <td class="text-end">{{ fmt(viewingEntry.total_debit || 0) }}</td>
                                    <td class="text-end">{{ fmt(viewingEntry.total_credit || 0) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button v-if="viewingEntry.status === 'draft'" class="btn btn-sm btn-success" @click="postEntry(viewingEntry)">
                            <i class="bi bi-check-circle me-1"></i>Valider
                        </button>
                        <button class="btn btn-sm btn-light" @click="viewingEntry = null">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'

const entries = ref([])
const journals = ref([])
const accounts = ref([])
const loading = ref(true)
const error = ref(null)
const submitting = ref(false)
const showCreateModal = ref(false)
const viewingEntry = ref(null)
const pagination = ref(null)

const filters = reactive({
    journal_id: '',
    date_from: '',
    date_to: '',
    status: '',
    page: 1,
})

const form = reactive({
    journal_id: '',
    entry_date: new Date().toISOString().split('T')[0],
    reference: '',
    numero_piece: '',
    description: '',
    lines: [
        { account_id: '', label: '', debit: 0, credit: 0 },
        { account_id: '', label: '', debit: 0, credit: 0 },
    ],
})

const formTotalDebit = computed(() => form.lines.reduce((s, l) => s + (parseFloat(l.debit) || 0), 0))
const formTotalCredit = computed(() => form.lines.reduce((s, l) => s + (parseFloat(l.credit) || 0), 0))
const isBalanced = computed(() => Math.abs(formTotalDebit.value - formTotalCredit.value) < 0.01 && formTotalDebit.value > 0)

const fmt = (v) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(v || 0) + ' F'

const csrf = computed(() => document.querySelector('meta[name=csrf-token]')?.content || '')

const api = (path, opts = {}) =>
    fetch(path, {
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf.value, ...opts.headers },
        ...opts,
    })

async function loadEntries() {
    loading.value = true; error.value = null
    try {
        const params = new URLSearchParams()
        if (filters.journal_id) params.set('journal_id', filters.journal_id)
        if (filters.date_from) params.set('date_from', filters.date_from)
        if (filters.date_to) params.set('date_to', filters.date_to)
        if (filters.status) params.set('status', filters.status)
        if (filters.page > 1) params.set('page', filters.page)
        const r = await api(`/api/entries?${params}`)
        if (!r.ok) throw new Error('Erreur chargement')
        const data = await r.json()
        entries.value = data.data || data.entries || []
        pagination.value = data.pagination || null
    } catch (e) { error.value = e.message } finally { loading.value = false }
}

async function loadJournals() {
    try {
        const r = await api('/api/journals')
        if (r.ok) journals.value = await r.json()
    } catch (e) { console.warn(e) }
}

async function loadAccounts() {
    try {
        const r = await api('/api/chart-accounts')
        if (r.ok) accounts.value = await r.json()
    } catch (e) { console.warn(e) }
}

function resetFilters() {
    filters.journal_id = ''; filters.date_from = ''; filters.date_to = ''; filters.status = ''; filters.page = 1
    loadEntries()
}

function goPage(p) { filters.page = p; loadEntries() }

function addLine() { form.lines.push({ account_id: '', label: '', debit: 0, credit: 0 }) }
function removeLine(i) { if (form.lines.length > 2) form.lines.splice(i, 1) }

async function saveEntry(status) {
    submitting.value = true
    try {
        const payload = {
            journal_id: form.journal_id,
            entry_date: form.entry_date,
            reference: form.reference || null,
            numero_piece: form.numero_piece || null,
            description: form.description,
            status,
            lines: form.lines.map(l => ({
                account_id: l.account_id,
                label: l.label,
                debit: l.debit || 0,
                credit: l.credit || 0,
            })),
        }
        const r = await api('/api/entries', { method: 'POST', body: JSON.stringify(payload) })
        if (!r.ok) { const e = await r.json().catch(() => ({})); throw new Error(e.message || 'Erreur') }
        showCreateModal.value = false
        resetForm()
        await loadEntries()
    } catch (e) { error.value = e.message } finally { submitting.value = false }
}

function resetForm() {
    form.journal_id = ''
    form.entry_date = new Date().toISOString().split('T')[0]
    form.reference = ''; form.numero_piece = ''; form.description = ''
    form.lines = [
        { account_id: '', label: '', debit: 0, credit: 0 },
        { account_id: '', label: '', debit: 0, credit: 0 },
    ]
}

async function postEntry(e) {
    if (!confirm('Valider cette écriture ? Elle ne pourra plus être modifiée.')) return
    try {
        const r = await api(`/api/entries/${e.id}/post`, { method: 'POST' })
        if (!r.ok) throw new Error('Erreur validation')
        await loadEntries()
        if (viewingEntry.value?.id === e.id) viewingEntry.value.status = 'posted'
    } catch (e) { error.value = e.message }
}

async function viewEntry(e) {
    try {
        const r = await api(`/api/entries/${e.id}`)
        if (r.ok) viewingEntry.value = (await r.json()).data || await r.json()
    } catch (e) { error.value = e.message }
}

const formatDate = (d) => d ? new Date(d).toLocaleDateString('fr-FR') : '—'
const statusLabel = (s) => ({ posted: 'Validée', draft: 'Brouillon', cancelled: 'Annulée' })[s] || s
const statusBadge = (s) => ({ posted: 'badge bg-success', draft: 'badge bg-warning text-dark', cancelled: 'badge bg-secondary' })[s] || 'badge bg-secondary'

onMounted(() => { loadEntries(); loadJournals(); loadAccounts() })
</script>
