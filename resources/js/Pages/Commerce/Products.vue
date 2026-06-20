<script setup>
import { ref, onMounted, watch } from 'vue'
import GelLayout from '../../Layouts/GelLayout.vue'

const state = ref('loading')
const errorMsg = ref('')
const products = ref([])
const categories = ref([])
const meta = ref(null)
const search = ref('')
const filterCategory = ref('')
const filterStock = ref('')
const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref(null)
const submitting = ref(false)
const debounce = ref(null)

const form = ref({
  name: '', category_id: '', barcode: '', sku: '', brand: '',
  price_ht: 0, price_ttc: 0, price_purchase: 0, tva_rate: 18,
  unit: 'piece', stock_qty: 0, stock_alert: 10, stock_critical: 0,
  description: '', location: '', is_active: true,
})

const fetchProducts = async () => {
  const params = { per_page: 50 }
  if (search.value) params.search = search.value
  if (filterCategory.value) params.category_id = filterCategory.value
  if (filterStock.value) params.stock = filterStock.value
  try {
    const res = await window.axios.get('/api/commerce/products', { params })
    products.value = res.data.data
    meta.value = res.data
  } catch (e) {
    errorMsg.value = e.message
  } finally {
    state.value = 'loaded'
  }
}

const fetchCategories = async () => {
  try {
    const res = await window.axios.get('/api/commerce/categories')
    categories.value = res.data
  } catch (e) { /* */ }
}

const resetForm = () => {
  form.value = { name: '', category_id: '', barcode: '', sku: '', brand: '', price_ht: 0, price_ttc: 0, price_purchase: 0, tva_rate: 18, unit: 'piece', stock_qty: 0, stock_alert: 10, stock_critical: 0, description: '', location: '', is_active: true }
}

const openCreate = () => { resetForm(); isEditing.value = false; editingId.value = null; showModal.value = true }

const openEdit = async (id) => {
  try {
    const res = await window.axios.get('/api/commerce/products/' + id)
    const p = res.data
    form.value = { name: p.name, category_id: p.category_id || '', barcode: p.barcode || '', sku: p.sku || '', brand: p.brand || '', price_ht: p.price_ht, price_ttc: p.price_ttc, price_purchase: p.price_purchase || 0, tva_rate: p.tva_rate, unit: p.unit, stock_qty: p.stock_qty, stock_alert: p.stock_alert, stock_critical: p.stock_critical, description: p.description || '', location: p.location || '', is_active: p.is_active }
    isEditing.value = true; editingId.value = id; showModal.value = true
  } catch (e) {
    alert('Erreur: ' + e.message)
  }
}

const submit = async () => {
  submitting.value = true
  try {
    const url = isEditing.value ? '/api/commerce/products/' + editingId.value : '/api/commerce/products'
    const method = isEditing.value ? 'put' : 'post'
    await window.axios[method](url, form.value)
    showModal.value = false
    await fetchProducts()
  } catch (e) {
    alert('Erreur: ' + (e.response?.data?.message || Object.values(e.response?.data?.errors || {}).flat().join(', ') || e.message))
  } finally {
    submitting.value = false
  }
}

const deleteProduct = async (id) => {
  if (!confirm('Confirmer la suppression ?')) return
  try {
    await window.axios.delete('/api/commerce/products/' + id)
    await fetchProducts()
  } catch (e) {
    alert('Erreur: ' + e.message)
  }
}

const stockClass = (p) => {
  if (p.stock_qty <= 0) return 'text-danger fw-bold'
  if (p.stock_qty <= p.stock_critical) return 'text-danger'
  if (p.stock_qty <= p.stock_alert) return 'text-warning'
  return 'text-success'
}

watch(search, () => {
  clearTimeout(debounce.value)
  debounce.value = setTimeout(fetchProducts, 300)
})
watch([filterCategory, filterStock], fetchProducts)

onMounted(async () => {
  await Promise.all([fetchProducts(), fetchCategories()])
})
</script>

<template>
  <GelLayout page-title="Produits">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div class="d-flex flex-wrap align-items-center gap-2">
        <div class="input-group input-group-sm" style="max-width:260px;">
          <span class="input-group-text bg-white"><i class="bi-search"></i></span>
          <input v-model="search" type="text" class="form-control" placeholder="Nom, code-barres, SKU...">
        </div>
        <select v-model="filterCategory" class="form-select form-select-sm" style="width:auto;">
          <option value="">Toutes catégories</option>
          <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
        <select v-model="filterStock" class="form-select form-select-sm" style="width:auto;">
          <option value="">Tous stocks</option>
          <option value="alert">Stock alerte</option>
          <option value="out">En rupture</option>
        </select>
      </div>
      <div class="d-flex gap-2">
        <a href="/api/commerce/products/export/csv" class="btn btn-outline-secondary btn-sm"><i class="bi-download me-1"></i>Export</a>
        <button class="btn btn-primary btn-sm" @click="openCreate"><i class="bi-plus-lg me-1"></i>Nouveau produit</button>
      </div>
    </div>

    <div v-if="state === 'loading'" class="d-flex justify-content-center py-5">
      <div class="spinner-border text-primary"></div>
    </div>

    <div v-else class="card card-dashboard">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="small text-muted">
            <tr>
              <th>Produit</th>
              <th>Code-barres</th>
              <th>Catégorie</th>
              <th>Prix vente</th>
              <th>Stock</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!products.length">
              <td colspan="6" class="text-center py-4 text-muted">Aucun produit.</td>
            </tr>
            <tr v-for="p in products" :key="p.id">
              <td>
                <span class="fw-medium">{{ p.name }}</span>
                <div class="small text-muted">{{ p.brand ? p.brand + ' | ' : '' }}{{ p.sku ? 'SKU: ' + p.sku : '' }}</div>
              </td>
              <td class="small">{{ p.barcode || '-' }}</td>
              <td><span class="badge bg-light text-dark">{{ p.category?.name || '-' }}</span></td>
              <td class="small fw-medium">{{ Number(p.price_ttc).toLocaleString('fr-FR') }} F</td>
              <td>
                <span :class="stockClass(p)">{{ Number(p.stock_qty).toLocaleString('fr-FR') }} {{ p.unit }}</span>
                <div v-if="p.stock_qty <= p.stock_alert" class="small text-danger">
                  <i class="bi-exclamation-triangle me-1"></i>Alerte: {{ Number(p.stock_alert) }}
                </div>
              </td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-secondary me-1" @click="openEdit(p.id)"><i class="bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger" @click="deleteProduct(p.id)"><i class="bi-trash"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal-content-custom">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
          <h5 class="fw-bold mb-0">{{ isEditing ? 'Modifier' : 'Nouveau produit' }}</h5>
          <button class="btn-close" @click="showModal = false"></button>
        </div>
        <form @submit.prevent="submit">
          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label small">Nom *</label>
              <input v-model="form.name" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-3">
              <label class="form-label small">Catégorie</label>
              <select v-model="form.category_id" class="form-select form-select-sm">
                <option value="">Sélectionner</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label small">Marque</label>
              <input v-model="form.brand" class="form-control form-control-sm">
            </div>
            <div class="col-md-4">
              <label class="form-label small">Code-barres</label>
              <input v-model="form.barcode" class="form-control form-control-sm">
            </div>
            <div class="col-md-4">
              <label class="form-label small">SKU</label>
              <input v-model="form.sku" class="form-control form-control-sm">
            </div>
            <div class="col-md-4">
              <label class="form-label small">Unité</label>
              <select v-model="form.unit" class="form-select form-select-sm">
                <option value="piece">Pièce</option>
                <option value="kg">Kg</option>
                <option value="litre">Litre</option>
                <option value="mètre">Mètre</option>
                <option value="carton">Carton</option>
                <option value="lot">Lot</option>
              </select>
            </div>
            <div class="col-12"><hr class="my-1"></div>
            <div class="col-md-3">
              <label class="form-label small">Prix HT</label>
              <input v-model="form.price_ht" type="number" class="form-control form-control-sm" min="0" step="1">
            </div>
            <div class="col-md-3">
              <label class="form-label small">Prix TTC</label>
              <input v-model="form.price_ttc" type="number" class="form-control form-control-sm" min="0" step="1">
            </div>
            <div class="col-md-3">
              <label class="form-label small">Prix achat</label>
              <input v-model="form.price_purchase" type="number" class="form-control form-control-sm" min="0" step="1">
            </div>
            <div class="col-md-3">
              <label class="form-label small">TVA %</label>
              <input v-model="form.tva_rate" type="number" class="form-control form-control-sm" min="0" max="100">
            </div>
            <div class="col-12"><hr class="my-1"></div>
            <div class="col-md-3">
              <label class="form-label small">Stock initial</label>
              <input v-model="form.stock_qty" type="number" class="form-control form-control-sm" min="0">
            </div>
            <div class="col-md-3">
              <label class="form-label small">Stock d'alerte</label>
              <input v-model="form.stock_alert" type="number" class="form-control form-control-sm" min="0">
            </div>
            <div class="col-md-3">
              <label class="form-label small">Stock critique</label>
              <input v-model="form.stock_critical" type="number" class="form-control form-control-sm" min="0">
            </div>
            <div class="col-md-3">
              <label class="form-label small">Emplacement</label>
              <input v-model="form.location" class="form-control form-control-sm" placeholder="Rayon/Étagère">
            </div>
            <div class="col-12">
              <label class="form-label small">Description</label>
              <textarea v-model="form.description" class="form-control form-control-sm" rows="2"></textarea>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-3 pt-2 border-top">
            <button type="button" class="btn btn-sm btn-secondary" @click="showModal = false">Annuler</button>
            <button type="submit" class="btn btn-sm btn-primary" :disabled="submitting">
              <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
              {{ isEditing ? 'Mettre à jour' : 'Créer' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </GelLayout>
</template>

<style scoped>
.modal-overlay {
  position: fixed; inset: 0; z-index: 1050; display: flex; align-items: center; justify-content: center;
  background: rgba(0,0,0,0.4); backdrop-filter: blur(2px);
}
.modal-content-custom {
  background: #fff; border-radius: 12px; padding: 1.5rem; width: 90%; max-width: 700px;
  max-height: 85vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}
</style>
