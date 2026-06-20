<script setup>
import { ref, computed, onMounted } from 'vue'
import GelLayout from '../../Layouts/GelLayout.vue'

const state = ref('loading')
const sessions = ref([])
const currentSession = ref(null)
const products = ref([])
const lines = ref([])
const showNewModal = ref(false)
const submitting = ref(false)
const newSessionName = ref('')
const selectedSessionId = ref('')

/* Stock status */
const stockStatus = ref([])
const filterStockType = ref('')

const fmtCurr = (n) => Number(n || 0).toLocaleString('fr-FR') + ' F'
const fmt = (n) => Number(n || 0).toLocaleString('fr-FR')

const fetchSessions = async () => {
  try {
    const r = await window.axios.get('/api/commerce/inventory/sessions')
    sessions.value = r.data
  } catch (e) { /* */ }
}

const fetchStockStatus = async () => {
  try {
    const r = await window.axios.get('/api/commerce/stock/status')
    stockStatus.value = r.data
  } catch (e) { /* */ }
}

const fetchProducts = async () => {
  try {
    const r = await window.axios.get('/api/commerce/products', { params: { per_page: 500 } })
    products.value = r.data.data || []
  } catch (e) { /* */ }
}

const startSession = async () => {
  submitting.value = true
  try {
    await window.axios.post('/api/commerce/inventory/sessions/start', { name: newSessionName.value || 'Inventaire ' + new Date().toLocaleDateString('fr-FR') })
    newSessionName.value = ''
    showNewModal.value = false
    await fetchSessions()
  } catch (e) {
    alert('Erreur: ' + (e.response?.data?.message || e.message))
  } finally { submitting.value = false }
}

const openSession = async (session) => {
  selectedSessionId.value = session.id
  currentSession.value = session
  try {
    const r = await window.axios.get('/api/commerce/inventory/sessions/' + session.id)
    lines.value = r.data.lines || []
  } catch (e) {
    alert('Erreur: ' + e.message)
  }
}

const closeSessionView = () => {
  currentSession.value = null
  lines.value = []
  selectedSessionId.value = ''
}

const updateLine = async (line, field) => {
  try {
    await window.axios.put('/api/commerce/inventory/lines/' + line.id, { [field]: line[field] })
  } catch (e) {
    alert('Erreur: ' + e.message)
  }
}

const validateSession = async () => {
  if (!confirm('Valider cet inventaire ? Les stocks seront mis à jour.')) return
  try {
    await window.axios.post('/api/commerce/inventory/sessions/' + currentSession.value.id + '/validate')
    await fetchSessions()
    await fetchProducts()
    await fetchStockStatus()
    closeSessionView()
  } catch (e) {
    alert('Erreur: ' + (e.response?.data?.message || e.message))
  }
}

const filteredStatus = computed(() => {
  if (!filterStockType.value) return stockStatus.value
  return stockStatus.value.filter(s => s.status === filterStockType.value)
})

onMounted(async () => {
  await Promise.all([fetchSessions(), fetchStockStatus(), fetchProducts()])
  state.value = 'loaded'
})
</script>

<template>
  <GelLayout page-title="Inventaire">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
      <p class="text-muted small mb-0">Gérez les inventaires et le suivi des stocks</p>
      <button class="btn btn-primary btn-sm" @click="showNewModal = true"><i class="bi-plus-lg me-1"></i>Nouvel inventaire</button>
    </div>

    <div v-if="state === 'loading'" class="d-flex justify-content-center py-5">
      <div class="spinner-border text-primary"></div>
    </div>

    <template v-else>
      <!-- Stock status -->
      <div class="card card-dashboard p-3 mb-4">
        <h6 class="fw-bold mb-3">État des stocks</h6>
        <div class="d-flex gap-2 mb-3">
          <button class="btn btn-sm" :class="filterStockType === '' ? 'btn-dark' : 'btn-outline-secondary'" @click="filterStockType = ''">Tous</button>
          <button class="btn btn-sm" :class="filterStockType === 'ok' ? 'btn-success' : 'btn-outline-success'" @click="filterStockType = 'ok'">OK</button>
          <button class="btn btn-sm" :class="filterStockType === 'alerte' ? 'btn-warning' : 'btn-outline-warning'" @click="filterStockType = 'alerte'">Alerte</button>
          <button class="btn btn-sm" :class="filterStockType === 'critique' ? 'btn-danger' : 'btn-outline-danger'" @click="filterStockType = 'critique'">Critique</button>
          <button class="btn btn-sm" :class="filterStockType === 'rupture' ? 'btn-dark' : 'btn-outline-dark'" @click="filterStockType = 'rupture'">Rupture</button>
        </div>
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead class="small text-muted">
              <tr><th>Produit</th><th>Stock actuel</th><th>Alerte</th><th>Critique</th><th>Statut</th></tr>
            </thead>
            <tbody>
              <tr v-if="!filteredStatus.length"><td colspan="5" class="text-center py-3 text-muted small">Aucun produit.</td></tr>
              <tr v-for="s in filteredStatus" :key="s.id">
                <td class="small">{{ s.name }}</td>
                <td class="fw-medium">{{ fmt(s.current_stock) }}</td>
                <td class="small">{{ fmt(s.stock_alert) }}</td>
                <td class="small">{{ fmt(s.stock_critical) }}</td>
                <td>
                  <span v-if="s.status === 'ok'" class="badge bg-success">OK</span>
                  <span v-else-if="s.status === 'alerte'" class="badge bg-warning text-dark">Alerte</span>
                  <span v-else-if="s.status === 'critique'" class="badge bg-danger">Critique</span>
                  <span v-else class="badge bg-dark">Rupture</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="row g-3">
        <!-- Sessions d'inventaire -->
        <div class="col-md-5">
          <div class="card card-dashboard p-3">
            <h6 class="fw-bold mb-3">Sessions d'inventaire</h6>
            <div v-if="!sessions.length" class="text-muted small py-3 text-center">Aucune session.</div>
            <div v-for="s in sessions" :key="s.id"
              class="d-flex justify-content-between align-items-center p-2 border-bottom cursor-pointer"
              :class="{ 'bg-light rounded': selectedSessionId === s.id }"
              @click="openSession(s)">
              <div>
                <div class="small fw-medium">{{ s.name }}</div>
                <div class="small text-muted">{{ new Date(s.created_at).toLocaleDateString('fr-FR') }}</div>
              </div>
              <div>
                <span v-if="s.validated_at" class="badge bg-success">Validé</span>
                <span v-else class="badge bg-warning text-dark">En cours</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Détail session -->
        <div class="col-md-7">
          <div v-if="!currentSession" class="card card-dashboard p-3">
            <div class="text-center text-muted small py-5">
              <i class="bi-clipboard-data fs-1 d-block mb-2"></i>
              Sélectionnez une session d'inventaire
            </div>
          </div>

          <div v-else class="card card-dashboard p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="fw-bold mb-0">{{ currentSession.name }}</h6>
              <div class="d-flex gap-2">
                <button v-if="!currentSession.validated_at" class="btn btn-sm btn-success" @click="validateSession">
                  <i class="bi-check-lg me-1"></i>Valider
                </button>
                <button class="btn btn-sm btn-outline-secondary" @click="closeSessionView">
                  <i class="bi-x-lg"></i>
                </button>
              </div>
            </div>

            <div class="table-responsive" style="max-height:400px; overflow-y:auto;">
              <table class="table table-sm align-middle mb-0">
                <thead class="small text-muted">
                  <tr>
                    <th>Produit</th>
                    <th>Stock théorique</th>
                    <th>Stock réel</th>
                    <th>Écart</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="line in lines" :key="line.id">
                    <td class="small">{{ line.product?.name || '—' }}</td>
                    <td class="small">{{ fmt(line.expected_qty) }}</td>
                    <td>
                      <input v-if="!currentSession.validated_at"
                        v-model.number="line.actual_qty" type="number" class="form-control form-control-sm" style="width:90px;"
                        min="0" @change="updateLine(line, 'actual_qty')">
                      <span v-else class="fw-medium">{{ fmt(line.actual_qty) }}</span>
                    </td>
                    <td>
                      <span v-if="line.difference !== undefined" :class="line.difference < 0 ? 'text-danger' : line.difference > 0 ? 'text-success' : ''">
                        {{ line.difference > 0 ? '+' : '' }}{{ fmt(line.difference) }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Modal nouvel inventaire -->
    <div v-if="showNewModal" class="modal-overlay" @click.self="showNewModal = false">
      <div class="modal-content-custom" style="max-width:400px;">
        <h5 class="fw-bold mb-3">Nouvel inventaire</h5>
        <div class="mb-3">
          <label class="form-label small">Nom de l'inventaire</label>
          <input v-model="newSessionName" class="form-control form-control-sm" placeholder="Inventaire mensuel...">
        </div>
        <div class="d-flex justify-content-end gap-2">
          <button class="btn btn-sm btn-secondary" @click="showNewModal = false">Annuler</button>
          <button class="btn btn-sm btn-primary" :disabled="submitting" @click="startSession">
            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
            Démarrer
          </button>
        </div>
      </div>
    </div>
  </GelLayout>
</template>

<style scoped>
.cursor-pointer { cursor: pointer; }
.modal-overlay {
  position: fixed; inset: 0; z-index: 1050; display: flex; align-items: center; justify-content: center;
  background: rgba(0,0,0,0.4); backdrop-filter: blur(2px);
}
.modal-content-custom {
  background: #fff; border-radius: 12px; padding: 1.5rem; width: 90%; max-height: 85vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}
</style>
