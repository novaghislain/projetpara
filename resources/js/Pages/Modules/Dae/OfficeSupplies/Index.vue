<template>
    <GelLayout>
        <div class="dae-courriers-index">
            <!-- ═══ MAIN CONTENT ═══ -->
            <div class="dae-content">
                <!-- Header -->
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <a href="/dae" class="btn btn-outline-secondary btn-sm" title="Retour">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <div>
                            <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">
                                <i class="bi bi-box-seam me-2 text-primary"></i>Fournitures de bureau
                            </h1>
                            <p class="text-muted small mb-0">
                                <i class="bi bi-boxes me-1"></i>Gestion des stocks et demandes
                                <span class="mx-2">|</span>
                                <span v-if="!showRequestTab">{{ supplies.length }} article(s)</span>
                                <span v-else>{{ requests.length }} demande(s)</span>
                            </p>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary btn-sm" @click="showRequestTab = !showRequestTab">
                            <i :class="showRequestTab ? 'bi-box-seam' : 'bi-basket'" class="me-1"></i>
                            {{ showRequestTab ? 'Voir le stock' : 'Voir les demandes' }}
                        </button>
                        <button v-if="!showRequestTab" class="btn btn-primary btn-sm" @click="openCreateModal">
                            <i class="bi bi-plus-lg me-1"></i>Nouvelle fourniture
                        </button>
                        <button v-else class="btn btn-primary btn-sm" @click="openRequestModal">
                            <i class="bi bi-plus-lg me-1"></i>Nouvelle demande
                        </button>
                    </div>
                </div>

                <!-- ════════════ STOCK VIEW ════════════ -->
                <template v-if="!showRequestTab">
                    <!-- Filters -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body py-3">
                            <div class="row g-2 align-items-end">
                                <div class="col-6 col-md-3">
                                    <label class="form-label small text-muted mb-1">Catégorie</label>
                                    <select v-model="filters.categorie" class="form-select form-select-sm" @change="fetchSupplies">
                                        <option value="">Toutes</option>
                                        <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small text-muted mb-1">Client</label>
                                    <select v-model="filters.client_id" class="form-select form-select-sm" @change="fetchSupplies">
                                        <option value="">Tous</option>
                                        <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company_name }}</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="form-check">
                                        <input v-model="filters.alerte" type="checkbox" class="form-check-input" id="alerteFilter" @change="fetchSupplies">
                                        <label class="form-check-label small" for="alerteFilter">Stock bas seulement</label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3 text-end">
                                    <button class="btn btn-outline-secondary btn-sm" @click="fetchSupplies">
                                        <i class="bi bi-arrow-clockwise me-1"></i>Actualiser
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loading -->
                    <div v-if="loading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <p class="text-muted mt-2 mb-0 small">Chargement des fournitures...</p>
                    </div>

                    <!-- Empty -->
                    <div v-else-if="supplies.length === 0" class="text-center py-5 text-muted">
                        <i class="bi bi-box" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">Aucune fourniture enregistrée</p>
                    </div>

                    <!-- Stock Grid -->
                    <div v-else class="row g-3">
                        <div v-for="s in supplies" :key="s.id" class="col-12 col-sm-6 col-lg-4 col-xl-3">
                            <div class="card border-0 shadow-sm h-100"
                                :class="{ 'border-start border-start-3 border-danger': s.quantite_stock <= s.seuil_alerte }"
                                @click="openShowModal(s)" role="button">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold mb-0 text-truncate">{{ s.nom }}</h6>
                                        <span v-if="s.reference" class="small text-muted flex-shrink-0 ms-2">{{ s.reference }}</span>
                                    </div>
                                    <div v-if="s.categorie" class="small text-muted mb-2">
                                        <i class="bi bi-tag me-1"></i>{{ s.categorie }}
                                    </div>
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="text-muted">Stock</span>
                                            <span :class="s.quantite_stock <= s.seuil_alerte ? 'text-danger fw-bold' : 'text-success fw-bold'">
                                                {{ s.quantite_stock }} {{ s.unite }}
                                            </span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar"
                                                :class="stockBarClass(s)"
                                                :style="{ width: stockPercent(s) + '%' }"
                                                role="progressbar"
                                                :aria-valuenow="stockPercent(s)"
                                                aria-valuemin="0"
                                                aria-valuemax="100">
                                            </div>
                                        </div>
                                        <small class="text-muted">Seuil: {{ s.seuil_alerte }} {{ s.unite }}</small>
                                    </div>
                                    <div class="d-flex justify-content-between small text-muted border-top pt-2">
                                        <span v-if="s.fournisseur"><i class="bi bi-person me-1"></i>{{ s.fournisseur }}</span>
                                        <span v-if="s.prix_unitaire" class="fw-medium">{{ formatPrix(s.prix_unitaire) }}/{{ s.unite }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- ════════════ REQUESTS VIEW ════════════ -->
                <template v-else>
                    <!-- Filters -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body py-3">
                            <div class="row g-2 align-items-end">
                                <div class="col-6 col-md-4">
                                    <label class="form-label small text-muted mb-1">Statut</label>
                                    <select v-model="reqFilters.statut" class="form-select form-select-sm" @change="fetchRequests">
                                        <option value="">Tous</option>
                                        <option value="en_attente">En attente</option>
                                        <option value="approuvee">Approuvée</option>
                                        <option value="refusee">Refusée</option>
                                        <option value="livree">Livrée</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-4">
                                    <button class="btn btn-outline-secondary btn-sm mt-3" @click="fetchRequests">
                                        <i class="bi bi-arrow-clockwise me-1"></i>Actualiser
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loading -->
                    <div v-if="reqLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <p class="text-muted mt-2 mb-0 small">Chargement des demandes...</p>
                    </div>

                    <!-- Empty -->
                    <div v-else-if="requests.length === 0" class="text-center py-5 text-muted">
                        <i class="bi bi-basket" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">Aucune demande</p>
                    </div>

                    <!-- Requests List -->
                    <div v-else class="card border-0 shadow-sm">
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <div v-for="r in requests" :key="r.id" class="list-group-item px-3 py-3">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <strong>{{ r.supply?.nom || 'Fourniture' }}</strong>
                                        <span class="badge" :class="reqStatusBadge(r.statut)">{{ r.statut }}</span>
                                    </div>
                                    <div class="small text-muted d-flex flex-wrap gap-2">
                                        <span>Qté: <strong>{{ r.quantite_demandee }}</strong></span>
                                        <span v-if="r.demandeur">· {{ r.demandeur.name }}</span>
                                        <span class="ms-auto">{{ formatDate(r.created_at) }}</span>
                                    </div>
                                    <div v-if="r.statut === 'en_attente'" class="mt-2 d-flex gap-2">
                                        <button class="btn btn-sm btn-success" @click="approuverDemande(r)">
                                            <i class="bi bi-check-lg me-1"></i>Approuver
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" @click="refuserDemande(r)">
                                            <i class="bi bi-x-lg me-1"></i>Refuser
                                        </button>
                                    </div>
                                    <div v-else-if="r.statut === 'approuvee' && r.statut !== 'livree'" class="mt-2">
                                        <button class="btn btn-sm btn-outline-primary" @click="livrerDemande(r)">
                                            <i class="bi bi-truck me-1"></i>Marquer livrée
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- ═══ MODAL SUPPLY ═══ -->
            <div class="modal fade" id="supplyModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold">
                                <i :class="editing?.id ? 'bi-pencil' : 'bi-plus-circle'" class="text-primary me-2"></i>
                                {{ editing?.id ? 'Modifier' : 'Nouvelle fourniture' }}
                            </h5>
                            <button type="button" class="btn-close" @click="closeModal"></button>
                        </div>
                        <form @submit.prevent="saveSupply">
                            <div class="modal-body pt-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-medium">Client *</label>
                                        <select v-model="form.client_id" class="form-select" required>
                                            <option value="">Sélectionner</option>
                                            <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company_name }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-medium">Catégorie</label>
                                        <input v-model="form.categorie" class="form-control" list="catList">
                                        <datalist id="catList">
                                            <option v-for="c in categories" :key="c" :value="c"></option>
                                        </datalist>
                                    </div>
                                </div>
                                <div class="mb-3 mt-3">
                                    <label class="form-label fw-medium">Nom *</label>
                                    <input v-model="form.nom" class="form-control" required>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium">Référence</label>
                                        <input v-model="form.reference" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium">Unité</label>
                                        <select v-model="form.unite" class="form-select">
                                            <option value="piece">Pièce</option>
                                            <option value="ramette">Ramette</option>
                                            <option value="cartouche">Cartouche</option>
                                            <option value="boite">Boîte</option>
                                            <option value="rouleau">Rouleau</option>
                                            <option value="litre">Litre</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium">Prix unitaire (FCFA)</label>
                                        <input v-model="form.prix_unitaire" type="number" min="0" class="form-control">
                                    </div>
                                </div>
                                <div class="row g-3 mt-1">
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium">Stock actuel</label>
                                        <input v-model="form.quantite_stock" type="number" min="0" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium">Seuil d'alerte</label>
                                        <input v-model="form.seuil_alerte" type="number" min="0" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium">Fournisseur</label>
                                        <input v-model="form.fournisseur" class="form-control">
                                    </div>
                                </div>
                                <div class="mb-3 mt-3">
                                    <label class="form-label fw-medium">Emplacement</label>
                                    <input v-model="form.emplacement" class="form-control" placeholder="Armoire, étagère...">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-medium">Description</label>
                                    <textarea v-model="form.description" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="closeModal">Annuler</button>
                                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i>Enregistrer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ═══ MODAL REQUEST ═══ -->
            <div class="modal fade" id="requestModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold">
                                <i class="bi bi-basket text-primary me-2"></i>Nouvelle demande
                            </h5>
                            <button type="button" class="btn-close" @click="closeModal"></button>
                        </div>
                        <form @submit.prevent="saveRequest">
                            <div class="modal-body pt-3">
                                <div class="mb-3">
                                    <label class="form-label fw-medium">Client *</label>
                                    <select v-model="reqForm.client_id" class="form-select" required>
                                        <option value="">Sélectionner</option>
                                        <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company_name }}</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-medium">Fourniture *</label>
                                    <select v-model="reqForm.supply_id" class="form-select" required>
                                        <option value="">Sélectionner</option>
                                        <option v-for="s in supplies" :key="s.id" :value="s.id">{{ s.nom }} (stock: {{ s.quantite_stock }})</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-medium">Quantité *</label>
                                    <input v-model="reqForm.quantite_demandee" type="number" min="1" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-medium">Motif</label>
                                    <textarea v-model="reqForm.motif" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="closeModal">Annuler</button>
                                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i>Envoyer</button>
                            </div>
                        </form>
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
const reqLoading = ref(false)
const supplies = ref([])
const requests = ref([])
const clients = ref([])
const categories = ref([])
const editing = ref(null)
const editingRequest = ref(false)
const showRequestTab = ref(false)

const filters = reactive({ categorie: '', alerte: false, client_id: '' })
const reqFilters = reactive({ statut: '' })

const form = reactive({
    client_id: '', nom: '', description: '', reference: '', categorie: '',
    quantite_stock: 0, seuil_alerte: 5, unite: 'piece',
    prix_unitaire: '', fournisseur: '', emplacement: '',
})

const reqForm = reactive({
    client_id: '', supply_id: '', quantite_demandee: 1, motif: '',
})

/* ══════════════════════════════════════════
   Helpers
   ══════════════════════════════════════════ */
function stockBarClass(s) {
    const ratio = s.quantite_stock / Math.max(s.seuil_alerte, 1)
    if (ratio <= 0.5) return 'bg-danger'
    if (ratio <= 1) return 'bg-warning'
    return 'bg-success'
}
function stockPercent(s) {
    const max = Math.max(s.seuil_alerte * 2, 1)
    return Math.min(100, (s.quantite_stock / max) * 100)
}
function formatPrix(v) { return v ? Number(v).toLocaleString('fr-FR') + ' FCFA' : '' }
function formatDate(d) {
    if (!d) return ''
    return new Date(d).toLocaleDateString('fr-FR', { year: 'numeric', month: 'short', day: 'numeric' })
}
function reqStatusBadge(s) {
    const badges = { en_attente: 'bg-warning text-dark', approuvee: 'bg-success', refusee: 'bg-danger', livree: 'bg-info' }
    return badges[s] || 'bg-secondary'
}

/* ══════════════════════════════════════════
   Fetch
   ══════════════════════════════════════════ */
async function fetchSupplies() {
    loading.value = true
    try {
        const params = { ...filters }
        const res = await window.axios.get('/dae/fournitures', { params })
        supplies.value = res.data.data || res.data
    } catch (err) { console.error(err) }
    finally { loading.value = false }
}

async function fetchRequests() {
    reqLoading.value = true
    try {
        const params = { ...reqFilters }
        const res = await window.axios.get('/dae/fournitures/demandes', { params })
        requests.value = res.data.data || res.data
    } catch (err) { console.error(err) }
    finally { reqLoading.value = false }
}

async function fetchCategories() {
    try { const res = await window.axios.get('/dae/fournitures/categories'); categories.value = res.data || [] }
    catch { /* ignore */ }
}
async function fetchClients() {
    try { const res = await window.axios.get('/api/clients/list'); clients.value = res.data || [] }
    catch { /* ignore */ }
}

/* ══════════════════════════════════════════
   Modals
   ══════════════════════════════════════════ */
function openModal(id) {
    nextTick(() => { const el = document.getElementById(id); if (el) new bootstrap.Modal(el).show() })
}
function hideModal(id) {
    const el = document.getElementById(id)
    if (el) { const bs = bootstrap.Modal.getInstance(el); if (bs) bs.hide() }
}

function openCreateModal() {
    editing.value = null; editingRequest.value = false
    Object.assign(form, { client_id: '', nom: '', description: '', reference: '', categorie: '', quantite_stock: 0, seuil_alerte: 5, unite: 'piece', prix_unitaire: '', fournisseur: '', emplacement: '' })
    openModal('supplyModal')
}

function openShowModal(s) {
    editing.value = s; editingRequest.value = false
    Object.assign(form, s)
    openModal('supplyModal')
}

function openRequestModal() {
    editing.value = null; editingRequest.value = true
    Object.assign(reqForm, { client_id: '', supply_id: '', quantite_demandee: 1, motif: '' })
    openModal('requestModal')
}

function closeModal() {
    hideModal('supplyModal'); hideModal('requestModal')
    editing.value = null; editingRequest.value = false
}

async function saveSupply() {
    try {
        const data = { ...form }
        if (editing.value?.id) {
            await window.axios.put(`/dae/fournitures/${editing.value.id}`, data)
        } else {
            await window.axios.post('/dae/fournitures', data)
        }
        closeModal(); fetchSupplies(); fetchCategories()
    } catch (err) { console.error('Erreur:', err); alert("Erreur lors de l'enregistrement.") }
}

async function saveRequest() {
    try {
        await window.axios.post('/dae/fournitures/demandes', reqForm)
        closeModal(); fetchRequests()
    } catch (err) { console.error('Erreur:', err); alert('Erreur lors de la création.') }
}

async function approuverDemande(r) {
    try { await window.axios.patch(`/dae/fournitures/demandes/${r.id}/approuver`, { statut: 'approuvee' }); fetchRequests(); fetchSupplies() }
    catch (err) { console.error(err) }
}
async function refuserDemande(r) {
    try { await window.axios.patch(`/dae/fournitures/demandes/${r.id}/approuver`, { statut: 'refusee' }); fetchRequests() }
    catch (err) { console.error(err) }
}
async function livrerDemande(r) {
    try { await window.axios.patch(`/dae/fournitures/demandes/${r.id}/livrer`); fetchRequests() }
    catch (err) { console.error(err) }
}

onMounted(() => { fetchSupplies(); fetchRequests(); fetchCategories(); fetchClients() })
</script>

<style scoped>
.border-start-3 { border-left-width: 3px !important; }
</style>
