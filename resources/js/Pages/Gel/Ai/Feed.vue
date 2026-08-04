<!--
 * Feed.vue - Fil d'activité intelligent des agents IA
 * Timeline centralisée des événements, suggestions et alertes
 * Actions : approbation, rejet, exécution, marquage lecture
-->
<template>
  <GelLayout pageTitle="Fil d'Activité Intelligent">
    <div class="ia-activity-feed">
      <!-- En-tête : titre, compteur non-lus et boutons d'action globaux -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h4 class="mb-1">
            <i class="bi bi-activity me-2 text-primary"></i>Fil d'Activité Intelligent
          </h4>
          <p class="text-muted mb-0">
            Tous les événements des agents IA en un coup d'œil
            <span class="badge bg-danger ms-2" v-if="stats.unread > 0">
              {{ stats.unread }} non {{ stats.unread > 1 ? 'lus' : 'lu' }}
            </span>
          </p>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-primary btn-sm" @click="refreshAll">
            <i class="bi bi-arrow-clockwise"></i> Actualiser
          </button>
          <button class="btn btn-outline-secondary btn-sm" @click="markAllAsRead" v-if="stats.unread > 0">
            <i class="bi bi-check2-all"></i> Tout marquer lu
          </button>
        </div>
      </div>

      <!-- Statistiques -->
      <div class="row g-2 mb-4">
        <div class="col-md-2 col-6" v-for="card in statCards" :key="card.key">
          <div class="card border-0 shadow-sm text-center h-100" :class="card.bgClass">
            <div class="card-body py-3">
              <div class="h5 mb-0 fw-bold">{{ card.count }}</div>
              <small class="text-muted">{{ card.label }}</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Filtres -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
          <div class="row g-2 align-items-end">
            <div class="col-md-2">
              <label class="form-label small mb-1">Agent</label>
              <select class="form-select form-select-sm" v-model="filters.agent" @change="fetchFeed(1)">
                <option value="">Tous</option>
                <option value="accounting">Comptabilité</option>
                <option value="ohada">OHADA</option>
                <option value="customer">Customer</option>
                <option value="finance">Finance</option>
                <option value="cashflow">Trésorerie</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label small mb-1">Type</label>
              <select class="form-select form-select-sm" v-model="filters.type" @change="fetchFeed(1)">
                <option value="">Tous</option>
                <option value="suggestion">Suggestions</option>
                <option value="alerte">Alertes</option>
                <option value="analyse">Analyses</option>
                <option value="relance">Relances</option>
                <option value="opportunite">Opportunités</option>
                <option value="prediction">Prédictions</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label small mb-1">Statut</label>
              <select class="form-select form-select-sm" v-model="filters.status" @change="fetchFeed(1)">
                <option value="">Tous</option>
                <option value="pending">En attente</option>
                <option value="approved">Approuvé</option>
                <option value="rejected">Rejeté</option>
                <option value="executed">Exécuté</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label small mb-1">Priorité</label>
              <select class="form-select form-select-sm" v-model="filters.priority" @change="fetchFeed(1)">
                <option value="">Toutes</option>
                <option value="critical">Critique</option>
                <option value="high">Haute</option>
                <option value="normal">Normale</option>
                <option value="low">Basse</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label small mb-1">Recherche</label>
              <input type="text" class="form-control form-control-sm"
                v-model="filters.search" placeholder="Mots-clés..."
                @keyup.enter="fetchFeed(1)" />
            </div>
            <div class="col-md-2 d-flex gap-1">
              <button class="btn btn-outline-danger btn-sm flex-grow-1" @click="resetFilters">
                <i class="bi bi-x-lg"></i> Réinitialiser
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- État : indicateur de chargement -->
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Chargement...</span>
        </div>
        <p class="mt-2 text-muted small">Mise à jour du fil d'activité...</p>
      </div>

      <!-- État : liste vide -->
      <div v-else-if="items.length === 0" class="text-center py-5 text-muted">
        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
        <p>Aucun événement trouvé pour ces critères.</p>
      </div>

      <!-- État : affichage de la timeline avec les événements -->
      <template v-else>
        <div class="feed-timeline">
          <div v-for="item in items" :key="item.id" class="feed-item mb-3"
            :class="{
              'feed-item-critical': itemPriority(item) === 'critical',
              'feed-item-high': itemPriority(item) === 'high',
              'feed-item-unread': !item.read_at,
            }"
          >
            <div class="card border-0 shadow-sm">
              <div class="card-body py-3">
                <div class="d-flex align-items-start gap-3">
                  <!-- Icône agent -->
                  <div class="feed-icon flex-shrink-0" :class="'agent-' + item.agent">
                    <i :class="agentIcon(item.agent)"></i>
                  </div>

                  <!-- Contenu principal : titre, badges, description -->
                  <div class="flex-grow-1 min-w-0">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                      <div class="d-flex flex-wrap align-items-center gap-1">
                        <strong class="small">{{ item.title }}</strong>
                        <span class="badge" :class="priorityBadge(itemPriority(item))">
                          {{ itemPriority(item) }}
                        </span>
                        <span class="badge bg-light text-dark border">{{ item.agent }}</span>
                        <span class="badge bg-secondary">{{ item.type }}</span>
                      </div>
                      <small class="text-muted text-nowrap ms-2 flex-shrink-0">
                        {{ formatDate(item.created_at) }}
                      </small>
                    </div>

                    <p class="small text-muted mb-1">{{ item.description }}</p>

                    <!-- Métadonnées : client, confiance, date de traitement, motif de rejet -->
                    <div class="d-flex flex-wrap gap-3 small text-muted mt-1">
                      <span v-if="item.client">
                        <i class="bi bi-building me-1"></i>{{ item.client.company_name }}
                      </span>
                      <span v-if="itemConfidence(item)">
                        <i class="bi bi-bar-chart me-1"></i>Confiance : {{ itemConfidence(item) }}%
                      </span>
                      <span v-if="item.approved_at">
                        <i class="bi bi-check-circle me-1"></i>
                        {{ item.status === 'rejected' ? 'Rejeté' : 'Traité' }} le {{ formatDate(item.approved_at) }}
                      </span>
                      <span v-if="item.rejection_reason" class="text-danger">
                        <i class="bi bi-chat-quote me-1"></i>"{{ item.rejection_reason }}"
                      </span>
                    </div>

                    <!-- Actions one-click disponibles pour les suggestions en attente -->
                    <div v-if="item.status === 'pending'" class="mt-2 d-flex gap-1 flex-wrap">
                      <button class="btn btn-sm btn-success" @click="approveItem(item)"
                        :disabled="actionLoading === item.id">
                        <i class="bi bi-check-lg"></i> Approuver
                      </button>
                      <button class="btn btn-sm btn-outline-danger" @click="openRejectModal(item)"
                        :disabled="actionLoading === item.id">
                        <i class="bi bi-x-lg"></i> Rejeter
                      </button>
                      <button class="btn btn-sm btn-outline-primary" @click="executeItem(item)"
                        :disabled="actionLoading === item.id">
                        <i class="bi bi-play-fill"></i> Exécuter
                      </button>
                      <button class="btn btn-sm btn-outline-info" @click="markRead(item)"
                        :disabled="actionLoading === item.id || item.read_at">
                        <i class="bi bi-envelope-open"></i> Marquer lu
                      </button>
                      <button class="btn btn-sm btn-outline-danger" @click="deleteItem(item)"
                        :disabled="actionLoading === item.id">
                        <i class="bi bi-trash3"></i>
                      </button>
                    </div>

                    <!-- Badge de statut pour les items déjà traités -->
                    <div v-else class="mt-2">
                      <span class="badge" :class="statusBadge(item.status)">{{ translateStatus(item.status) }}</span>
                      <span v-if="item.approver" class="small text-muted ms-2">
                        par {{ item.approver.name }}
                      </span>
                    </div>
                  </div>

                  <!-- Indicateur visuel de non-lecture (pastille bleue) -->
                  <div v-if="!item.read_at" class="flex-shrink-0 align-self-center">
                    <span class="d-inline-block rounded-pill bg-primary"
                      style="width:10px;height:10px;"></span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      <!-- Pagination avec numérotation et navigation -->
        <div class="d-flex justify-content-between align-items-center mt-3">
          <small class="text-muted">
            Page {{ pagination.current_page }} / {{ pagination.last_page }}
            ({{ pagination.total }} résultat{{ pagination.total > 1 ? 's' : '' }})
          </small>
          <nav>
            <ul class="pagination pagination-sm mb-0">
              <li class="page-item" :class="{ disabled: pagination.current_page <= 1 }">
                <button class="page-link" @click="fetchFeed(pagination.current_page - 1)">
                  <i class="bi bi-chevron-left"></i>
                </button>
              </li>
              <li v-for="p in pageRange" :key="p" class="page-item"
                :class="{ active: p === pagination.current_page, disabled: p === '...' }">
                <button class="page-link" @click="p !== '...' && fetchFeed(p)">{{ p }}</button>
              </li>
              <li class="page-item" :class="{ disabled: pagination.current_page >= pagination.last_page }">
                <button class="page-link" @click="fetchFeed(pagination.current_page + 1)">
                  <i class="bi bi-chevron-right"></i>
                </button>
              </li>
            </ul>
          </nav>
        </div>
      </template>

      <!-- Modal de rejet : formulaire de motif obligatoire -->
      <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header border-0">
              <h6 class="modal-title">
                <i class="bi bi-x-circle text-danger me-1"></i>Rejeter la suggestion
              </h6>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <p class="small text-muted mb-2">{{ rejectTarget?.title }}</p>
              <div class="mb-2">
                <label class="form-label small">Motif du rejet <span class="text-danger">*</span></label>
                <textarea class="form-control form-control-sm" rows="3"
                  v-model="rejectReason" placeholder="Expliquez pourquoi vous rejetez cette suggestion..."></textarea>
              </div>
            </div>
            <div class="modal-footer border-0">
              <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Annuler</button>
              <button class="btn btn-sm btn-danger" @click="confirmReject" :disabled="!rejectReason.trim()">
                <i class="bi bi-x-lg"></i> Rejeter
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </GelLayout>
</template>

<script setup>
/* ===== Logique métier du composant ===== */
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'
import { Modal } from 'bootstrap'

// ─── État ──────────────────────────────────────────────────
const loading = ref(false)
const actionLoading = ref(null)
const items = ref([])
const stats = reactive({
  total: 0, unread: 0, pending: 0,
  approved: 0, rejected: 0, executed: 0,
})

const pagination = reactive({
  current_page: 1, last_page: 1, per_page: 20, total: 0,
})

const filters = reactive({
  agent: '', type: '', status: '', priority: '', search: '',
})

// Modal de rejet
let rejectModalInstance = null
const rejectTarget = ref(null)
const rejectReason = ref('')

// ─── Cycle de vie ──────────────────────────────────────────
onMounted(() => {
  fetchFeed(1)
  fetchStats()
  startPolling()
  rejectModalInstance = new Modal(document.getElementById('rejectModal'))
})

onBeforeUnmount(() => {
  stopPolling()
})

// ─── Polling 30s ───────────────────────────────────────────
let pollTimer = null

function startPolling() {
  pollTimer = setInterval(fetchStats, 30000)
}

function stopPolling() {
  if (pollTimer) {
    clearInterval(pollTimer)
    pollTimer = null
  }
}

// ─── API ───────────────────────────────────────────────────
async function fetchFeed(page) {
  loading.value = true
  try {
    const params = { page, per_page: pagination.per_page }
    if (filters.agent) params.agent = filters.agent
    if (filters.type) params.type = filters.type
    if (filters.status) params.status = filters.status
    if (filters.priority) params.priority = filters.priority
    if (filters.search) params.search = filters.search

    const { data } = await axios.get('/api/ia/feed', { params })
    items.value = data.data || []
    pagination.current_page = data.meta?.current_page || 1
    pagination.last_page = data.meta?.last_page || 1
    pagination.total = data.meta?.total || 0
  } catch (e) {
    console.error('Erreur chargement feed:', e)
  } finally {
    loading.value = false
  }
}

async function fetchStats() {
  try {
    const { data } = await axios.get('/api/ia/feed/stats')
    if (data.success) {
      Object.assign(stats, data.stats)
    }
  } catch (e) {
    // silencieux — ne pas bloquer l'interface
  }
}

async function refreshAll() {
  await Promise.all([fetchFeed(pagination.current_page), fetchStats()])
}

// ─── Actions ───────────────────────────────────────────────
async function approveItem(item) {
  actionLoading.value = item.id
  try {
    await axios.post(`/api/ia/feed/${item.id}/approve`)
    await refreshAll()
  } catch (e) {
    alert("Erreur lors de l'approbation.")
  } finally {
    actionLoading.value = null
  }
}

function openRejectModal(item) {
  rejectTarget.value = item
  rejectReason.value = ''
  rejectModalInstance?.show()
}

async function confirmReject() {
  if (!rejectTarget.value || !rejectReason.value.trim()) return
  const id = rejectTarget.value.id
  actionLoading.value = id
  try {
    await axios.post(`/api/ia/feed/${id}/reject`, { reason: rejectReason.value })
    rejectModalInstance?.hide()
    await refreshAll()
  } catch (e) {
    alert("Erreur lors du rejet.")
  } finally {
    actionLoading.value = null
    rejectTarget.value = null
  }
}

async function executeItem(item) {
  actionLoading.value = item.id
  try {
    await axios.post(`/api/ia/feed/${item.id}/execute`)
    await refreshAll()
  } catch (e) {
    alert("Erreur lors de l'exécution.")
  } finally {
    actionLoading.value = null
  }
}

async function markRead(item) {
  actionLoading.value = item.id
  try {
    await axios.post(`/api/ia/feed/${item.id}/read`)
    item.read_at = new Date().toISOString()
    await fetchStats()
  } catch (e) {
    console.error("Erreur marquage lu:", e)
  } finally {
    actionLoading.value = null
  }
}

async function markAllAsRead() {
  try {
    await axios.post('/api/ia/feed/read-all')
    items.value.forEach(i => { i.read_at = new Date().toISOString() })
    await fetchStats()
  } catch (e) {
    console.error("Erreur marquage tout lu:", e)
  }
}

async function deleteItem(item) {
  if (!confirm(`Supprimer définitivement "${item.title}" ?`)) return
  actionLoading.value = item.id
  try {
    await axios.delete(`/api/ia/feed/${item.id}`)
    items.value = items.value.filter(i => i.id !== item.id)
    await fetchStats()
  } catch (e) {
    alert("Erreur lors de la suppression.")
  } finally {
    actionLoading.value = null
  }
}

// ─── Helpers d'affichage ───────────────────────────────────

/** La priorité est stockée dans data (ou metadata) JSON */
function itemPriority(item) {
  return item.data?.priority || item.metadata?.priority || 'normal'
}

/** La confiance est stockée dans data (ou metadata) JSON */
function itemConfidence(item) {
  return item.data?.confidence || item.metadata?.confidence || 0
}

function agentIcon(agent) {
  const map = {
    accounting: 'bi bi-calculator',
    ohada:      'bi bi-file-earmark-text',
    customer:   'bi bi-people',
    finance:    'bi bi-graph-up',
    cashflow:   'bi bi-cash-stack',
  }
  return map[agent] || 'bi bi-robot'
}

function priorityBadge(priority) {
  const map = {
    critical: 'bg-danger',
    high:     'bg-warning text-dark',
    normal:   'bg-info text-dark',
    low:      'bg-secondary',
  }
  return map[priority] || 'bg-info text-dark'
}

function statusBadge(status) {
  const map = {
    pending:  'bg-warning text-dark',
    approved: 'bg-success',
    rejected: 'bg-danger',
    executed: 'bg-primary',
  }
  return map[status] || 'bg-secondary'
}

function translateStatus(status) {
  const map = {
    pending:  'En attente',
    approved: 'Approuvé',
    rejected: 'Rejeté',
    executed: 'Exécuté',
  }
  return map[status] || status
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('fr-FR', {
    day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit',
  })
}

function resetFilters() {
  filters.agent = ''
  filters.type = ''
  filters.status = ''
  filters.priority = ''
  filters.search = ''
  fetchFeed(1)
}

// ─── Pagination ─────────────────────────────────────────────
const pageRange = computed(() => {
  const { current_page: c, last_page: l } = pagination
  if (l <= 5) return Array.from({ length: l }, (_, i) => i + 1)
  if (c <= 3) return [1, 2, 3, 4, '...', l]
  if (c >= l - 2) return [1, '...', l - 3, l - 2, l - 1, l]
  return [1, '...', c - 1, c, c + 1, '...', l]
})

// ─── Stats cards ────────────────────────────────────────────
const statCards = computed(() => [
  { key: 'total',    label: 'Total',       count: stats.total,    bgClass: 'bg-light' },
  { key: 'unread',   label: 'Non lus',     count: stats.unread,   bgClass: 'bg-primary bg-opacity-10' },
  { key: 'pending',  label: 'En attente',  count: stats.pending,  bgClass: 'bg-warning bg-opacity-10' },
  { key: 'approved', label: 'Approuvés',   count: stats.approved, bgClass: 'bg-success bg-opacity-10' },
  { key: 'rejected', label: 'Rejetés',     count: stats.rejected, bgClass: 'bg-danger bg-opacity-10' },
  { key: 'executed', label: 'Exécutés',    count: stats.executed, bgClass: 'bg-info bg-opacity-10' },
])
</script>

<style scoped>
/* Icônes agents — cercle coloré */
.feed-icon {
  width: 40px; height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  color: #fff;
  flex-shrink: 0;
}
.agent-accounting { background: #6f42c1; }
.agent-ohada      { background: #0d6efd; }
.agent-customer   { background: #198754; }
.agent-finance    { background: #fd7e14; }
.agent-cashflow   { background: #20c997; }

/* Priorité critique — bordure rouge */
.feed-item-critical .card {
  border-left: 4px solid #dc3545 !important;
}

/* Priorité haute — bordure orange */
.feed-item-high .card {
  border-left: 4px solid #fd7e14 !important;
}

/* Non lu — fond légèrement bleuté */
.feed-item-unread .card {
  background-color: #f0f4ff;
}

/* Transition douce */
.feed-item {
  transition: opacity 0.2s;
}

/* Pagination */
.page-link {
  cursor: pointer;
}
</style>
