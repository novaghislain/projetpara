<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import GelLayout from '../../Layouts/GelLayout.vue'

/* ══════════════════════════════════════════
   State
   ══════════════════════════════════════════ */
const state = ref('loading')
const errorMsg = ref('')
const products = ref([])
const categories = ref([])
const session = ref(null)
const cart = ref([])
const showPaymentModal = ref(false)
const showSessionModal = ref(false)
const submitting = ref(false)
const searchQuery = ref('')
const filterCat = ref('')
const selectedPayment = ref('especes')
const amountGiven = ref(0)
const saleResult = ref(null)
const receiptData = ref(null)
const barcodeBuffer = ref('')
const barcodeTimer = ref(null)
const showReceiptModal = ref(false)
const returnedSale = ref(null)

const paymentMethods = [
  { value: 'especes', label: 'Espèces', icon: 'bi-cash' },
  { value: 'momo', label: 'MTN MoMo', icon: 'bi-phone' },
  { value: 'moov', label: 'Moov Money', icon: 'bi-phone' },
  { value: 'carte', label: 'Carte bancaire', icon: 'bi-credit-card' },
  { value: 'autre', label: 'Autre', icon: 'bi-wallet2' },
]

/* ══════════════════════════════════════════
   Computed
   ══════════════════════════════════════════ */
const subtotal = computed(() =>
  cart.value.reduce((sum, item) => sum + (item.price_ttc * item.qty), 0)
)

const totalItems = computed(() =>
  cart.value.reduce((sum, item) => sum + item.qty, 0)
)

const changeAmount = computed(() => {
  if (selectedPayment.value !== 'especes') return 0
  return Math.max(0, Number(amountGiven.value) - subtotal.value)
})

const filteredProducts = computed(() => {
  let list = products.value
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(p =>
      p.name.toLowerCase().includes(q) ||
      (p.barcode && p.barcode.toLowerCase().includes(q)) ||
      (p.sku && p.sku.toLowerCase().includes(q))
    )
  }
  if (filterCat.value) {
    list = list.filter(p => p.category_id == filterCat.value)
  }
  return list.filter(p => p.stock_qty > 0)
})

/* ══════════════════════════════════════════
   Methods
   ══════════════════════════════════════════ */
const loadSession = async () => {
  try {
    const res = await window.axios.get('/api/commerce/pos/sessions')
    session.value = res.data
  } catch (e) { /* pas de session ouverte */ }
}

const loadProducts = async () => {
  try {
    const res = await window.axios.get('/api/commerce/products', { params: { per_page: 200, is_active: 1 } })
    products.value = res.data.data || []
  } catch (e) { /* */ }
}

const loadCategories = async () => {
  try {
    const res = await window.axios.get('/api/commerce/categories')
    categories.value = res.data
  } catch (e) { /* */ }
}

const openSession = async () => {
  try {
    const res = await window.axios.post('/api/commerce/pos/sessions/open')
    session.value = res.data
    showSessionModal.value = false
  } catch (e) {
    alert('Erreur: ' + (e.response?.data?.message || e.message))
  }
}

const closeSession = async () => {
  if (!confirm('Fermer la session de caisse ?')) return
  try {
    await window.axios.post('/api/commerce/pos/sessions/close/' + session.value.id)
    session.value = null
  } catch (e) {
    alert('Erreur: ' + (e.response?.data?.message || e.message))
  }
}

/* Cart */
const addToCart = (product) => {
  const existing = cart.value.find(item => item.id === product.id)
  if (existing) {
    if (existing.qty < product.stock_qty) existing.qty++
  } else {
    cart.value.push({ ...product, qty: 1 })
  }
  searchQuery.value = ''
}

const updateQty = (item, delta) => {
  const newQty = item.qty + delta
  if (newQty <= 0) {
    cart.value = cart.value.filter(i => i.id !== item.id)
  } else if (newQty <= item.stock_qty) {
    item.qty = newQty
  }
}

const removeFromCart = (itemId) => {
  cart.value = cart.value.filter(i => i.id !== itemId)
}

const clearCart = () => {
  if (cart.value.length && !confirm('Vider le panier ?')) return
  cart.value = []
}

/* Sale */
const openPayment = () => {
  if (!cart.value.length) return
  selectedPayment.value = 'especes'
  amountGiven.value = subtotal.value
  saleResult.value = null
  showPaymentModal.value = true
}

const submitSale = async () => {
  submitting.value = true
  try {
    const payload = {
      session_id: session.value?.id,
      items: cart.value.map(item => ({
        product_id: item.id,
        qty: item.qty,
        price_ttc: item.price_ttc,
      })),
      payments: [{ method: selectedPayment.value, amount: subtotal.value }],
    }
    const res = await window.axios.post('/api/commerce/pos/sell', payload)
    saleResult.value = res.data
    receiptData.value = res.data
    cart.value = []
    showPaymentModal.value = false
    showReceiptModal.value = true
    await loadProducts()
  } catch (e) {
    alert('Erreur: ' + (e.response?.data?.message || e.message))
  } finally {
    submitting.value = false
  }
}

/* Return */
const returnSale = async (reference) => {
  if (!confirm('Confirmer le retour de la vente ' + reference + ' ?')) return
  try {
    const res = await window.axios.post('/api/commerce/pos/sales/' + reference + '/return')
    returnedSale.value = res.data.sale
    await loadProducts()
  } catch (e) {
    alert('Erreur: ' + (e.response?.data?.message || e.message))
  }
}

/* Barcode scanner simulation */
const onBarcodeInput = (e) => {
  clearTimeout(barcodeTimer.value)
  barcodeBuffer.value += e.key
  if (e.key === 'Enter') {
    const code = barcodeBuffer.value.trim()
    barcodeBuffer.value = ''
    if (code) {
      const product = products.value.find(p => p.barcode === code)
      if (product) {
        addToCart(product)
      }
    }
  } else {
    barcodeTimer.value = setTimeout(() => { barcodeBuffer.value = '' }, 200)
  }
}

/* Format */
const fmtCurr = (n) => Number(n || 0).toLocaleString('fr-FR') + ' F'
const fmt = (n) => Number(n || 0).toLocaleString('fr-FR')

onMounted(async () => {
  await Promise.all([loadSession(), loadProducts(), loadCategories()])
  state.value = 'loaded'
  window.addEventListener('keydown', onBarcodeInput)
})

onUnmounted(() => {
  window.removeEventListener('keydown', onBarcodeInput)
})
</script>

<template>
  <GelLayout page-title="Caisse POS">
    <div v-if="state === 'loading'" class="d-flex justify-content-center py-5">
      <div class="spinner-border text-primary"></div>
    </div>

    <template v-else>
      <!-- Barre de session -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <span v-if="session" class="badge bg-success fs-6 px-3 py-2">
            <i class="bi-check-circle me-1"></i>Session ouverte
          </span>
          <span v-else class="badge bg-secondary fs-6 px-3 py-2">
            <i class="bi-x-circle me-1"></i>Aucune session ouverte
          </span>
        </div>
        <div class="d-flex gap-2">
          <button v-if="!session" class="btn btn-success btn-sm" @click="showSessionModal = true">
            <i class="bi-play-fill me-1"></i>Ouvrir session
          </button>
          <button v-if="session" class="btn btn-outline-danger btn-sm" @click="closeSession">
            <i class="bi-stop-fill me-1"></i>Fermer session
          </button>
        </div>
      </div>

      <div class="row g-3">
        <!-- Produits -->
        <div class="col-md-7">
          <!-- Filtres -->
          <div class="d-flex gap-2 mb-3">
            <div class="input-group input-group-sm flex-grow-1">
              <span class="input-group-text bg-white"><i class="bi-search"></i></span>
              <input v-model="searchQuery" type="text" class="form-control" placeholder="Rechercher produit, code-barres, SKU...">
            </div>
            <select v-model="filterCat" class="form-select form-select-sm" style="width:auto;">
              <option value="">Toutes</option>
              <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>

          <!-- Grille produits -->
          <div class="row g-2" style="max-height:calc(100vh - 300px); overflow-y:auto;">
            <div v-for="p in filteredProducts" :key="p.id" class="col-4 col-md-3">
              <button class="btn btn-outline-secondary p-2 w-100 text-start h-100" style="font-size:13px;" @click="addToCart(p)">
                <div class="fw-medium text-truncate">{{ p.name }}</div>
                <div class="small text-primary fw-bold">{{ fmtCurr(p.price_ttc) }}</div>
                <div class="small text-muted">Stock: {{ fmt(p.stock_qty) }}</div>
              </button>
            </div>
            <div v-if="!filteredProducts.length" class="col-12 text-center py-4 text-muted small">
              Aucun produit disponible.
            </div>
          </div>
        </div>

        <!-- Panier -->
        <div class="col-md-5">
          <div class="card card-dashboard">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
              <h6 class="fw-bold mb-0"><i class="bi-cart me-1"></i>Panier ({{ totalItems }})</h6>
              <button class="btn btn-sm btn-outline-danger" @click="clearCart" :disabled="!cart.length">
                <i class="bi-trash"></i>
              </button>
            </div>
            <div class="card-body p-2" style="max-height:350px; overflow-y:auto;">
              <div v-if="!cart.length" class="text-center py-4 text-muted small">
                <i class="bi-cart-plus fs-2 d-block mb-2"></i>
                Ajoutez des produits
              </div>
              <div v-for="(item, i) in cart" :key="i" class="d-flex align-items-center justify-content-between border-bottom py-2">
                <div class="flex-grow-1 me-2">
                  <div class="small fw-medium text-truncate">{{ item.name }}</div>
                  <div class="small text-muted">{{ fmtCurr(item.price_ttc) }} x {{ item.qty }}</div>
                </div>
                <div class="d-flex align-items-center gap-1">
                  <button class="btn btn-sm btn-outline-secondary px-1" @click="updateQty(item, -1)">−</button>
                  <span class="fw-bold mx-1" style="min-width:20px;text-align:center;">{{ item.qty }}</span>
                  <button class="btn btn-sm btn-outline-secondary px-1" @click="updateQty(item, 1)" :disabled="item.qty >= item.stock_qty">+</button>
                  <button class="btn btn-sm btn-outline-danger ms-2 px-1" @click="removeFromCart(item.id)"><i class="bi-x"></i></button>
                </div>
              </div>
            </div>
            <div class="card-footer bg-white">
              <div class="d-flex justify-content-between fw-bold fs-5 mb-2">
                <span>Total</span>
                <span class="text-primary">{{ fmtCurr(subtotal) }}</span>
              </div>
              <button class="btn btn-primary w-100" :disabled="!cart.length || !session" @click="openPayment">
                <i class="bi-cash-coin me-1"></i>Payer ({{ totalItems }} articles)
              </button>
            </div>
          </div>

          <!-- Retour vente -->
          <div class="mt-3">
            <div class="input-group input-group-sm">
              <input v-model="returnedSale" placeholder="Réf. vente à retourner" class="form-control">
              <button class="btn btn-outline-warning btn-sm" @click="returnSale(returnedSale)">
                <i class="bi-arrow-return-left me-1"></i>Retour
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal ouverture session -->
      <div v-if="showSessionModal" class="modal-overlay" @click.self="showSessionModal = false">
        <div class="modal-content-custom" style="max-width:400px;">
          <h5 class="fw-bold mb-3">Ouvrir une session</h5>
          <p class="small text-muted">Une nouvelle session de caisse va être ouverte.</p>
          <div class="d-flex justify-content-end gap-2">
            <button class="btn btn-sm btn-secondary" @click="showSessionModal = false">Annuler</button>
            <button class="btn btn-sm btn-success" @click="openSession"><i class="bi-play-fill me-1"></i>Ouvrir</button>
          </div>
        </div>
      </div>

      <!-- Modal paiement -->
      <div v-if="showPaymentModal" class="modal-overlay" @click.self="showPaymentModal = false">
        <div class="modal-content-custom" style="max-width:450px;">
          <h5 class="fw-bold mb-3"><i class="bi-cash-coin me-1"></i>Paiement</h5>
          <div class="mb-3">
            <label class="form-label small">Mode de paiement</label>
            <div class="d-flex flex-wrap gap-2">
              <button v-for="pm in paymentMethods" :key="pm.value"
                class="btn btn-sm" :class="selectedPayment === pm.value ? 'btn-primary' : 'btn-outline-secondary'"
                @click="selectedPayment = pm.value">
                <i :class="pm.icon + ' me-1'"></i>{{ pm.label }}
              </button>
            </div>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span class="fw-bold">Total à payer</span>
            <span class="fw-bold fs-5 text-primary">{{ fmtCurr(subtotal) }}</span>
          </div>
          <div v-if="selectedPayment === 'especes'" class="mb-3">
            <label class="form-label small">Montant reçu</label>
            <input v-model.number="amountGiven" type="number" class="form-control form-control-lg" min="0">
            <div v-if="changeAmount > 0" class="text-success fw-bold mt-1">
              Monnaie: {{ fmtCurr(changeAmount) }}
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2">
            <button class="btn btn-sm btn-secondary" @click="showPaymentModal = false">Annuler</button>
            <button class="btn btn-sm btn-success" :disabled="submitting" @click="submitSale">
              <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
              <i class="bi-check-lg me-1"></i>Confirmer paiement
            </button>
          </div>
        </div>
      </div>

      <!-- Modal reçu -->
      <div v-if="showReceiptModal && receiptData" class="modal-overlay" @click.self="showReceiptModal = false">
        <div class="modal-content-custom" style="max-width:380px;">
          <div class="text-center border-bottom pb-2 mb-3">
            <h6 class="fw-bold mb-0">Reçu de vente</h6>
            <div class="small text-muted">Réf: {{ receiptData.sale?.reference }}</div>
          </div>
          <div class="small">
            <div v-for="item in receiptData.sale?.items" :key="item.id" class="d-flex justify-content-between py-1 border-bottom">
              <span>{{ item.product?.name }} x{{ item.qty }}</span>
              <span class="fw-medium">{{ fmtCurr(item.total_ttc) }}</span>
            </div>
          </div>
          <div class="d-flex justify-content-between fw-bold fs-6 mt-2 pt-2 border-top">
            <span>Total</span>
            <span>{{ fmtCurr(receiptData.sale?.total_ttc) }}</span>
          </div>
          <div class="d-flex justify-content-between small text-muted mt-1">
            <span>Payé par</span>
            <span>{{ receiptData.sale?.payments?.[0]?.method || '-' }}</span>
          </div>
          <button class="btn btn-primary btn-sm w-100 mt-3" @click="showReceiptModal = false">
            <i class="bi-check-lg me-1"></i>Fermer
          </button>
        </div>
      </div>
    </template>
  </GelLayout>
</template>

<style scoped>
.modal-overlay {
  position: fixed; inset: 0; z-index: 1050; display: flex; align-items: center; justify-content: center;
  background: rgba(0,0,0,0.4); backdrop-filter: blur(2px);
}
.modal-content-custom {
  background: #fff; border-radius: 12px; padding: 1.5rem; width: 90%; max-height: 85vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}
</style>
