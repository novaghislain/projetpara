<template>
    <CompanyLayout pageTitle="Facturation">
        <div class="container-fluid pt-3 pb-5">
            <div class="isup-card p-3 mb-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h4 class="fw-bold mb-0"><i class="bi bi-receipt me-2"></i>Factures clients</h4>
                    <button class="btn isup-btn-primary" @click="openCreateModal">
                        <i class="bi bi-plus-lg me-1"></i>Nouvelle facture
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="isup-card p-3 mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small">Statut</label>
                        <select class="form-select" v-model="filter.status">
                            <option value="">Tous</option>
                            <option value="draft">Brouillon</option>
                            <option value="sent">Envoyée</option>
                            <option value="paid">Payée</option>
                            <option value="overdue">Impayée</option>
                            <option value="cancelled">Annulée</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Du</label>
                        <input type="date" class="form-control" v-model="filter.date_from">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Au</label>
                        <input type="date" class="form-control" v-model="filter.date_to">
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button class="btn isup-btn-outline flex-fill" @click="loadInvoices"><i class="bi bi-search"></i> Filtrer</button>
                        <button class="btn isup-btn-ghost flex-fill" @click="resetFilters"><i class="bi bi-x-circle"></i> Effacer</button>
                    </div>
                </div>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Chargement des factures…</p>
            </div>

            <!-- Error -->
            <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

            <!-- Table -->
            <div v-else class="isup-card p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table isup-table mb-0">
                        <thead>
                            <tr>
                                <th>N° Facture</th>
                                <th>Client</th>
                                <th>Date</th>
                                <th>Montant</th>
                                <th>Statut</th>
                                <th>Échéance</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="invoices.length === 0">
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    Aucune facture trouvée
                                </td>
                            </tr>
                            <tr v-for="inv in invoices" :key="inv.id">
                                <td class="fw-semibold">{{ inv.reference || inv.numero || 'N/A' }}</td>
                                <td>{{ inv.client_name || inv.client?.company_name || '—' }}</td>
                                <td>{{ inv.date ? formatDate(inv.date) : '—' }}</td>
                                <td class="fw-semibold">{{ formatCurrency(inv.total || inv.montant_ttc || 0) }}</td>
                                <td><span :class="'badge ' + statusBadge(inv.status || inv.statut)">{{ statusLabel(inv.status || inv.statut) }}</span></td>
                                <td>{{ inv.due_date ? formatDate(inv.due_date) : '—' }}</td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm isup-btn-ghost" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#" @click.prevent="viewInvoice(inv)"><i class="bi bi-eye me-2"></i>Voir</a></li>
                                            <li v-if="inv.status === 'draft' || !inv.status"><a class="dropdown-item" href="#" @click.prevent="editInvoice(inv)"><i class="bi bi-pencil me-2"></i>Modifier</a></li>
                                            <li v-if="inv.status === 'draft' || !inv.status"><a class="dropdown-item" href="#" @click.prevent="deleteInvoice(inv)"><i class="bi bi-trash me-2 text-danger"></i>Supprimer</a></li>
                                            <li v-if="inv.status === 'sent' || inv.status === 'overdue'"><a class="dropdown-item" href="#" @click.prevent="markPaid(inv)"><i class="bi bi-check-circle me-2 text-success"></i>Marquer payée</a></li>
                                            <li v-if="inv.status !== 'cancelled' && inv.status !== 'paid'"><a class="dropdown-item" href="#" @click.prevent="cancelInvoice(inv)"><i class="bi bi-x-circle me-2 text-warning"></i>Annuler</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="#" @click.prevent="printInvoice(inv)"><i class="bi bi-printer me-2"></i>Imprimer</a></li>
                                            <li><a class="dropdown-item" href="#" @click.prevent="sendInvoice(inv)"><i class="bi bi-envelope me-2"></i>Envoyer</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="pagination && pagination.last_page > 1" class="d-flex justify-content-center mt-3">
                <nav>
                    <ul class="pagination pagination-sm">
                        <li class="page-item" :class="{ disabled: pagination.current_page <= 1 }">
                            <a class="page-link" href="#" @click.prevent="goToPage(pagination.current_page - 1)">«</a>
                        </li>
                        <li v-for="p in pagination.last_page" :key="p" class="page-item" :class="{ active: p === pagination.current_page }">
                            <a class="page-link" href="#" @click.prevent="goToPage(p)">{{ p }}</a>
                        </li>
                        <li class="page-item" :class="{ disabled: pagination.current_page >= pagination.last_page }">
                            <a class="page-link" href="#" @click.prevent="goToPage(pagination.current_page + 1)">»</a>
                        </li>
                    </ul>
                </nav>
            </div>

            <!-- Create/Edit Modal -->
            <div class="modal fade" id="invoiceModal" tabindex="-1" ref="invoiceModalEl">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ editingInvoice ? 'Modifier' : 'Nouvelle' }} facture</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Client</label>
                                    <input v-model="form.client_name" class="form-control" placeholder="Nom du client">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Date</label>
                                    <input v-model="form.date" type="date" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Échéance</label>
                                    <input v-model="form.due_date" type="date" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Objet</label>
                                    <input v-model="form.description" class="form-control" placeholder="Description de la facture">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Lignes de facture</label>
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Description</th>
                                                <th style="width:100px">Qté</th>
                                                <th style="width:120px">Prix unitaire</th>
                                                <th style="width:120px">Total</th>
                                                <th style="width:40px"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(line, i) in form.lines" :key="i">
                                                <td><input v-model="line.description" class="form-control form-control-sm" placeholder="Désignation"></td>
                                                <td><input v-model.number="line.quantity" type="number" min="1" class="form-control form-control-sm" @input="calcLineTotal(i)"></td>
                                                <td><input v-model.number="line.unit_price" type="number" min="0" step="0.01" class="form-control form-control-sm" @input="calcLineTotal(i)"></td>
                                                <td class="text-end fw-semibold pt-2">{{ formatCurrency(line.total || 0) }}</td>
                                                <td><button class="btn btn-sm btn-outline-danger" @click="removeLine(i)"><i class="bi bi-x"></i></button></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <button class="btn btn-sm isup-btn-outline" @click="addLine"><i class="bi bi-plus"></i> Ajouter une ligne</button>
                                </div>
                                <div class="col-12 text-end">
                                    <hr>
                                    <h5>Total : <strong>{{ formatCurrency(formTotal) }}</strong></h5>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn isup-btn-ghost" data-bs-dismiss="modal">Annuler</button>
                            <button type="button" class="btn isup-btn-primary" :disabled="submitting" @click="saveInvoice">
                                <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                                {{ editingInvoice ? 'Mettre à jour' : 'Créer la facture' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </CompanyLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import CompanyLayout from '../../../../Layouts/CompanyLayout.vue'

const invoices = ref([])
const loading = ref(true)
const error = ref(null)
const submitting = ref(false)
const editingInvoice = ref(null)
const pagination = ref(null)
const successMsg = ref('')

const filter = reactive({
    status: '',
    date_from: '',
    date_to: '',
    page: 1,
})

const form = reactive({
    client_name: '',
    date: new Date().toISOString().split('T')[0],
    due_date: '',
    description: '',
    lines: [{ description: '', quantity: 1, unit_price: 0, total: 0 }],
})

const formTotal = computed(() =>
    form.lines.reduce((sum, l) => sum + (parseFloat(l.total) || 0), 0)
)

const csrfToken = computed(() =>
    document.querySelector('meta[name=csrf-token]')?.content || ''
)

const api = (path, opts = {}) => {
    return fetch(path, {
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.value,
            ...opts.headers,
        },
        ...opts,
    })
}

const loadInvoices = async () => {
    loading.value = true
    error.value = null
    try {
        const params = new URLSearchParams()
        if (filter.status) params.set('status', filter.status)
        if (filter.date_from) params.set('date_from', filter.date_from)
        if (filter.date_to) params.set('date_to', filter.date_to)
        if (filter.page > 1) params.set('page', filter.page)
        const r = await api(`/api/company/invoices?${params}`)
        if (!r.ok) throw new Error('Erreur chargement factures')
        const data = await r.json()
        invoices.value = data.data || data.invoices || data
        pagination.value = data.pagination || null
    } catch (e) {
        error.value = e.message
    } finally {
        loading.value = false
    }
}

const resetFilters = () => {
    filter.status = ''
    filter.date_from = ''
    filter.date_to = ''
    filter.page = 1
    loadInvoices()
}

const goToPage = (p) => {
    filter.page = p
    loadInvoices()
}

const openCreateModal = () => {
    editingInvoice.value = null
    form.client_name = ''
    form.date = new Date().toISOString().split('T')[0]
    form.due_date = ''
    form.description = ''
    form.lines = [{ description: '', quantity: 1, unit_price: 0, total: 0 }]
    const modal = new bootstrap.Modal(document.getElementById('invoiceModal'))
    modal.show()
}

const editInvoice = (inv) => {
    editingInvoice.value = inv
    form.client_name = inv.client_name || inv.client?.company_name || ''
    form.date = inv.date || inv.date_emission || ''
    form.due_date = inv.due_date || inv.date_echeance || ''
    form.description = inv.description || inv.objet || ''
    form.lines = (inv.lines || inv.lignes || []).length > 0
        ? (inv.lines || inv.lignes).map(l => ({
            description: l.description || l.designation || '',
            quantity: l.quantity || l.quantite || 1,
            unit_price: l.unit_price || l.prix_unitaire || 0,
            total: (l.quantity || l.quantite || 1) * (l.unit_price || l.prix_unitaire || 0),
        }))
        : [{ description: '', quantity: 1, unit_price: 0, total: 0 }]
    const modal = new bootstrap.Modal(document.getElementById('invoiceModal'))
    modal.show()
}

const addLine = () => {
    form.lines.push({ description: '', quantity: 1, unit_price: 0, total: 0 })
}

const removeLine = (i) => {
    if (form.lines.length > 1) form.lines.splice(i, 1)
}

const calcLineTotal = (i) => {
    const line = form.lines[i]
    line.total = (parseFloat(line.quantity) || 0) * (parseFloat(line.unit_price) || 0)
}

const saveInvoice = async () => {
    submitting.value = true
    try {
        const payload = {
            client_name: form.client_name,
            date: form.date,
            due_date: form.due_date,
            description: form.description,
            lines: form.lines.map(l => ({
                description: l.description,
                quantity: l.quantity,
                unit_price: l.unit_price,
                total: l.total,
            })),
        }
        const url = editingInvoice.value
            ? `/api/company/invoices/${editingInvoice.value.id}`
            : '/api/company/invoices'
        const method = editingInvoice.value ? 'PUT' : 'POST'
        const r = await api(url, { method, body: JSON.stringify(payload) })
        if (!r.ok) {
            const errData = await r.json().catch(() => ({}))
            throw new Error(errData.message || 'Erreur sauvegarde facture')
        }
        bootstrap.Modal.getInstance(document.getElementById('invoiceModal'))?.hide()
        await loadInvoices()
    } catch (e) {
        error.value = e.message
    } finally {
        submitting.value = false
    }
}

const deleteInvoice = async (inv) => {
    if (!confirm(`Supprimer la facture ${inv.reference || inv.numero || ''} ?`)) return
    try {
        const r = await api(`/api/company/invoices/${inv.id}`, { method: 'DELETE' })
        if (!r.ok) throw new Error('Erreur suppression')
        await loadInvoices()
    } catch (e) {
        error.value = e.message
    }
}

const markPaid = async (inv) => {
    try {
        const r = await api(`/api/company/invoices/${inv.id}/pay`, { method: 'POST' })
        if (!r.ok) throw new Error('Erreur encaissement')
        await loadInvoices()
    } catch (e) {
        error.value = e.message
    }
}

const cancelInvoice = async (inv) => {
    if (!confirm(`Annuler la facture ${inv.reference || inv.numero || ''} ?`)) return
    try {
        const r = await api(`/api/company/invoices/${inv.id}/cancel`, { method: 'POST' })
        if (!r.ok) throw new Error('Erreur annulation')
        await loadInvoices()
    } catch (e) {
        error.value = e.message
    }
}

const sendInvoice = async (inv) => {
    try {
        const r = await api(`/api/company/invoices/${inv.id}/send`, { method: 'POST' })
        if (!r.ok) throw new Error('Erreur envoi')
        await loadInvoices()
    } catch (e) {
        error.value = e.message
    }
}

const printInvoice = (inv) => {
    window.open(`/api/company/invoices/${inv.id}/pdf`, '_blank')
}

const viewInvoice = (inv) => {
    editInvoice(inv)
}

const formatCurrency = (v) => {
    if (v === null || v === undefined || isNaN(v)) return '0'
    return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(v) + ' FCFA'
}

const formatDate = (d) => {
    if (!d) return '—'
    try { return new Date(d).toLocaleDateString('fr-FR') } catch { return d }
}

const statusLabel = (s) => {
    const map = { draft: 'Brouillon', sent: 'Envoyée', paid: 'Payée', overdue: 'Impayée', cancelled: 'Annulée', '': 'Brouillon' }
    return map[s] || s
}

const statusBadge = (s) => {
    const map = { draft: 'bg-secondary', sent: 'bg-primary', paid: 'bg-success', overdue: 'bg-danger', cancelled: 'bg-secondary' }
    return map[s] || 'bg-secondary'
}

onMounted(() => { loadInvoices() })
</script>
