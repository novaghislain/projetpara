<template>
    <GelLayout>
        <div class="dae-courriers-index">
            <!-- ═══ LOADING ═══ -->
            <div v-if="loading" class="dae-loading">
                <div class="dae-spinner"></div>
                <p class="dae-loading-text mt-3">Chargement des messages...</p>
            </div>

            <!-- ═══ MAIN CONTENT ═══ -->
            <div v-else class="dae-content">
                <!-- Header -->
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <a href="/dae" class="btn btn-outline-secondary btn-sm" title="Retour au tableau de bord">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <div>
                            <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">
                                <i class="bi bi-chat-dots me-2 text-primary"></i>Messages & Prises d'appels
                            </h1>
                            <p class="text-muted small mb-0">
                                <i class="bi bi-telephone me-1"></i>Gestion des appels, messages et notes internes
                                <span class="mx-2">|</span>
                                <span v-if="totalItems > 0">{{ totalItems }} message(s)</span>
                            </p>
                        </div>
                    </div>
                    <div>
                        <button class="btn btn-outline-secondary btn-sm me-2" @click="fetchMessages" title="Actualiser">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                        <button class="btn btn-primary btn-sm" @click="openCreateModal">
                            <i class="bi bi-plus-lg me-1"></i>Nouveau message
                        </button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body py-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button
                                        v-for="f in typeFilters"
                                        :key="f.key"
                                        :class="['btn', activeTypeFilter === f.key ? 'btn-primary' : 'btn-outline-secondary']"
                                        @click="activeTypeFilter = f.key; fetchMessages()"
                                    >
                                        <i :class="f.icon"></i> {{ f.label }}
                                    </button>
                                </div>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small text-muted mb-1">Statut</label>
                                <select v-model="filters.statut" class="form-select form-select-sm" @change="fetchMessages">
                                    <option value="">Tous</option>
                                    <option value="recu">Reçu</option>
                                    <option value="lu">Lu</option>
                                    <option value="traite">Traité</option>
                                    <option value="archive">Archivé</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small text-muted mb-1">Urgence</label>
                                <select v-model="filters.urgence" class="form-select form-select-sm" @change="fetchMessages">
                                    <option value="">Toutes</option>
                                    <option value="urgent">Urgent</option>
                                    <option value="normal">Normal</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small text-muted mb-1">Client</label>
                                <select v-model="filters.client_id" class="form-select form-select-sm" @change="fetchMessages">
                                    <option value="">Tous</option>
                                    <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company_name }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Message List -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div v-if="messages.length === 0" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">Aucun message trouvé</p>
                        </div>
                        <div v-else class="list-group list-group-flush">
                            <div
                                v-for="msg in messages"
                                :key="msg.id"
                                class="list-group-item list-group-item-action px-3 py-3"
                                :class="{ 'border-start border-start-3 border-warning': msg.statut === 'recu', 'border-start border-danger': msg.urgence === 'urgent' && msg.statut === 'recu' }"
                                @click="openShowModal(msg)"
                                role="button"
                            >
                                <div class="d-flex align-items-center gap-3">
                                    <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                                        :style="{ width: '40px', height: '40px', background: typeBg(msg.type), color: typeColor(msg.type) }">
                                        <i :class="typeIcon(msg.type)"></i>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="badge" :class="'bg-' + typeBadgeBg(msg.type)">{{ typeLabel(msg.type) }}</span>
                                            <span v-if="msg.expediteur_name" class="fw-semibold small">{{ msg.expediteur_name }}</span>
                                            <span v-if="msg.expediteur_entreprise" class="text-muted small">· {{ msg.expediteur_entreprise }}</span>
                                            <span v-if="msg.urgence === 'urgent'" class="badge bg-danger" style="font-size: 0.6rem;">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i>URGENT
                                            </span>
                                            <span class="text-muted small ms-auto text-nowrap">{{ formatDate(msg.created_at) }}</span>
                                        </div>
                                        <div class="small fw-medium text-truncate mt-1">{{ msg.objet }}</div>
                                        <div v-if="msg.expediteur_contact" class="small text-muted mt-1">
                                            <i class="bi bi-telephone me-1"></i>{{ msg.expediteur_contact }}
                                        </div>
                                    </div>
                                    <div class="d-flex gap-1 flex-shrink-0">
                                        <button class="btn btn-sm btn-outline-secondary border-0" title="Marquer traité"
                                            @click.stop="marquerTraite(msg)" :disabled="msg.statut === 'traite' || msg.statut === 'archive'">
                                            <i class="bi bi-check2-circle"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary border-0" title="Archiver"
                                            @click.stop="archiver(msg)" :disabled="msg.statut === 'archive'">
                                            <i class="bi bi-archive"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Pagination -->
                    <div v-if="pagination.last_page > 1" class="card-footer border-0 d-flex justify-content-between align-items-center py-2">
                        <small class="text-muted">Page {{ pagination.current_page }} / {{ pagination.last_page }}</small>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item" :class="{ disabled: !pagination.prev_page_url }">
                                    <button class="page-link" @click="goPage(pagination.current_page - 1)"><i class="bi bi-chevron-left"></i></button>
                                </li>
                                <li class="page-item" :class="{ disabled: !pagination.next_page_url }">
                                    <button class="page-link" @click="goPage(pagination.current_page + 1)"><i class="bi bi-chevron-right"></i></button>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- ═══ MODAL ═══ -->
            <div class="modal fade" id="messageModal" tabindex="-1" aria-hidden="true" ref="modalEl">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold">
                                <i :class="editing?.id ? 'bi-eye' : 'bi-plus-circle'" class="text-primary me-2"></i>
                                {{ editing?.id ? 'Détails du message' : 'Nouveau message' }}
                            </h5>
                            <button type="button" class="btn-close" @click="closeModal"></button>
                        </div>
                        <div class="modal-body pt-3">
                            <!-- Détails -->
                            <div v-if="editing?.id">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3">
                                            <small class="text-muted text-uppercase fw-semibold d-block mb-1">Type</small>
                                            <span class="badge" :class="'bg-' + typeBadgeBg(editing.type)">{{ typeLabel(editing.type) }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3">
                                            <small class="text-muted text-uppercase fw-semibold d-block mb-1">Statut</small>
                                            <span class="badge" :class="statutBadge(editing.statut)">{{ editing.statut }}</span>
                                        </div>
                                    </div>
                                    <div v-if="editing.expediteur_name" class="col-md-6">
                                        <div class="p-3 bg-light rounded-3">
                                            <small class="text-muted text-uppercase fw-semibold d-block mb-1">Expéditeur</small>
                                            <span class="fw-medium">{{ editing.expediteur_name }}</span>
                                        </div>
                                    </div>
                                    <div v-if="editing.expediteur_contact" class="col-md-6">
                                        <div class="p-3 bg-light rounded-3">
                                            <small class="text-muted text-uppercase fw-semibold d-block mb-1">Contact</small>
                                            <span class="fw-medium">{{ editing.expediteur_contact }}</span>
                                        </div>
                                    </div>
                                    <div v-if="editing.destinataire" class="col-md-6">
                                        <div class="p-3 bg-light rounded-3">
                                            <small class="text-muted text-uppercase fw-semibold d-block mb-1">Destinataire</small>
                                            <span class="fw-medium">{{ editing.destinataire.name }}</span>
                                        </div>
                                    </div>
                                    <div v-if="editing.appel_rappele" class="col-md-6">
                                        <div class="p-3 bg-light rounded-3">
                                            <span class="badge bg-info"><i class="bi bi-telephone-forward me-1"></i>Rappel effectué</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <h6 class="fw-bold">Objet</h6>
                                    <p class="mb-0">{{ editing.objet }}</p>
                                </div>
                                <div v-if="editing.contenu" class="mb-3">
                                    <h6 class="fw-bold">Contenu</h6>
                                    <p class="mb-0 p-3 bg-light rounded-3" style="white-space: pre-wrap;">{{ editing.contenu }}</p>
                                </div>
                                <div class="d-flex gap-2 mt-3">
                                    <button v-if="editing.statut !== 'traite' && editing.statut !== 'archive'" class="btn btn-primary btn-sm" @click="marquerTraite(editing)">
                                        <i class="bi bi-check2-circle me-1"></i>Marquer traité
                                    </button>
                                    <button v-if="editing.statut !== 'archive'" class="btn btn-outline-secondary btn-sm" @click="archiver(editing)">
                                        <i class="bi bi-archive me-1"></i>Archiver
                                    </button>
                                </div>
                            </div>
                            <!-- Formulaire -->
                            <form v-else @submit.prevent="saveMessage">
                                <div class="mb-3">
                                    <label class="form-label fw-medium">Type *</label>
                                    <select v-model="form.type" class="form-select" required>
                                        <option value="message">Message</option>
                                        <option value="appel">Prise d'appel</option>
                                        <option value="note">Note interne</option>
                                        <option value="info">Information</option>
                                    </select>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-medium">Client *</label>
                                        <select v-model="form.client_id" class="form-select" required>
                                            <option value="">Sélectionner un client</option>
                                            <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company_name }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-medium">Destinataire</label>
                                        <select v-model="form.destinataire_id" class="form-select">
                                            <option value="">— Aucun —</option>
                                            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium">Expéditeur (nom)</label>
                                        <input v-model="form.expediteur_name" class="form-control" placeholder="Nom de l'appelant">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium">Entreprise</label>
                                        <input v-model="form.expediteur_entreprise" class="form-control" placeholder="Entreprise">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium">Contact</label>
                                        <input v-model="form.expediteur_contact" class="form-control" placeholder="Téléphone / Email">
                                    </div>
                                </div>
                                <div class="mb-3 mt-3">
                                    <label class="form-label fw-medium">Objet *</label>
                                    <input v-model="form.objet" class="form-control" required maxlength="500">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-medium">Contenu</label>
                                    <textarea v-model="form.contenu" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-medium">Urgence</label>
                                        <select v-model="form.urgence" class="form-select">
                                            <option value="normal">Normal</option>
                                            <option value="urgent">Urgent</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 d-flex align-items-end">
                                        <div class="form-check">
                                            <input v-model="form.appel_rappele" type="checkbox" class="form-check-input" id="appelRappele">
                                            <label class="form-check-label fw-medium" for="appelRappele">Rappel effectué</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 justify-content-end mt-4">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" @click="closeModal">Annuler</button>
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="bi bi-save me-1"></i>Enregistrer
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick } from 'vue'
import GelLayout from '../../../../Layouts/GelLayout.vue'

/* ══════════════════════════════════════════
   State
   ══════════════════════════════════════════ */
const loading = ref(false)
const messages = ref([])
const clients = ref([])
const users = ref([])
const showModal = ref(false)
const editing = ref(null)
const totalItems = ref(0)

const filters = reactive({ statut: '', urgence: '', client_id: '', type: '' })
const pagination = reactive({ current_page: 1, last_page: 1, prev_page_url: null, next_page_url: null })
const activeTypeFilter = ref('')

const typeFilters = [
    { key: '',      label: 'Tous',      icon: 'bi-chat-dots' },
    { key: 'appel', label: 'Appels',    icon: 'bi-telephone' },
    { key: 'message', label: 'Messages', icon: 'bi-chat' },
    { key: 'note',  label: 'Notes',     icon: 'bi-sticky' },
    { key: 'info',  label: 'Infos',     icon: 'bi-info-circle' },
]

const form = reactive({
    client_id: '', type: 'message', expediteur_name: '', expediteur_entreprise: '',
    expediteur_contact: '', destinataire_id: '', objet: '', contenu: '',
    urgence: 'normal', appel_rappele: false,
})

/* ══════════════════════════════════════════
   Helpers
   ══════════════════════════════════════════ */
function formatDate(d) {
    if (!d) return ''
    return new Date(d).toLocaleDateString('fr-FR', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}
function typeIcon(type) {
    return { appel: 'bi-telephone', message: 'bi-chat', note: 'bi-sticky', info: 'bi-info-circle' }[type] || 'bi-chat-dots'
}
function typeLabel(type) {
    return { appel: 'Appel', message: 'Message', note: 'Note', info: 'Info' }[type] || type
}
function typeBadgeBg(type) {
    return { appel: 'success', message: 'primary', note: 'warning', info: 'secondary' }[type] || 'secondary'
}
function typeBg(type) {
    return { appel: 'rgba(16,185,129,0.1)', message: 'rgba(59,130,246,0.1)', note: 'rgba(245,158,11,0.1)', info: 'rgba(107,114,128,0.1)' }[type] || '#f3f4f6'
}
function typeColor(type) {
    return { appel: '#10b981', message: '#3b82f6', note: '#f59e0b', info: '#6b7280' }[type] || '#6b7280'
}
function statutBadge(s) {
    const map = { recu: 'bg-warning text-dark', lu: 'bg-info', traite: 'bg-success', archive: 'bg-secondary' }
    return map[s] || 'bg-secondary'
}

/* ══════════════════════════════════════════
   Fetch
   ══════════════════════════════════════════ */
async function fetchMessages(page = 1) {
    loading.value = true
    try {
        const params = { page, ...filters }
        if (activeTypeFilter.value) params.type = activeTypeFilter.value
        const res = await window.axios.get('/dae/messages', { params })
        const data = res.data
        messages.value = data.data || []
        totalItems.value = data.total || 0
        pagination.current_page = data.current_page
        pagination.last_page = data.last_page
        pagination.prev_page_url = data.prev_page_url
        pagination.next_page_url = data.next_page_url
    } catch (err) { console.error('Erreur messages:', err) }
    finally { loading.value = false }
}

async function fetchClients() {
    try { const res = await window.axios.get('/api/clients/list'); clients.value = res.data || [] }
    catch { /* ignore */ }
}
async function fetchUsers() {
    try { const res = await window.axios.get('/api/users/list'); users.value = res.data || [] }
    catch { /* ignore */ }
}

function goPage(page) {
    if (page >= 1 && page <= pagination.last_page) fetchMessages(page)
}

/* ══════════════════════════════════════════
   Modal
   ══════════════════════════════════════════ */
async function openCreateModal() {
    editing.value = null
    Object.assign(form, {
        client_id: '', type: 'message', expediteur_name: '', expediteur_entreprise: '',
        expediteur_contact: '', destinataire_id: '', objet: '', contenu: '',
        urgence: 'normal', appel_rappele: false,
    })
    showModal.value = true
    await nextTick()
    const el = document.getElementById('messageModal')
    if (el) { const bs = new bootstrap.Modal(el); bs.show() }
}

function openShowModal(msg) {
    editing.value = msg
    showModal.value = true
    nextTick(() => {
        const el = document.getElementById('messageModal')
        if (el) { const bs = new bootstrap.Modal(el); bs.show() }
    })
}

function closeModal() {
    const el = document.getElementById('messageModal')
    if (el) { const bs = bootstrap.Modal.getInstance(el); if (bs) bs.hide() }
    showModal.value = false
    editing.value = null
}

async function saveMessage() {
    try {
        await window.axios.post('/dae/messages', form)
        closeModal()
        fetchMessages()
    } catch (err) {
        console.error('Erreur:', err)
        alert("Erreur lors de l'enregistrement.")
    }
}

async function marquerTraite(msg) {
    try {
        await window.axios.patch(`/dae/messages/${msg.id}/traiter`)
        msg.statut = 'traite'
        fetchMessages()
    } catch (err) { console.error('Erreur:', err) }
}

async function archiver(msg) {
    try {
        await window.axios.patch(`/dae/messages/${msg.id}/archiver`)
        msg.statut = 'archive'
        fetchMessages()
    } catch (err) { console.error('Erreur:', err) }
}

onMounted(() => { fetchMessages(); fetchClients(); fetchUsers() })
</script>

<style scoped>
.dae-courriers-index :deep(.list-group-item) { cursor: pointer; transition: background 0.15s; }
.dae-courriers-index :deep(.list-group-item:hover) { background: #fffcfa; }
.border-start-3 { border-left-width: 3px !important; }
</style>
