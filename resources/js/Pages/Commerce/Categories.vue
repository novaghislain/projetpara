<script setup>
import { ref, onMounted } from 'vue'
import GelLayout from '../../Layouts/GelLayout.vue'

const state = ref('loading')
const categories = ref([])
const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref(null)
const submitting = ref(false)
const form = ref({ name: '', color: '#6c757d', parent_id: '', is_active: true })

const fetchCategories = async () => {
  try {
    const res = await window.axios.get('/api/commerce/categories')
    categories.value = res.data
  } catch (e) { /* */ }
  finally { state.value = 'loaded' }
}

const resetForm = () => { form.value = { name: '', color: '#6c757d', parent_id: '', is_active: true } }

const openCreate = () => { resetForm(); isEditing.value = false; editingId.value = null; showModal.value = true }

const openEdit = (cat) => {
  form.value = { name: cat.name, color: cat.color || '#6c757d', parent_id: cat.parent_id || '', is_active: cat.is_active }
  isEditing.value = true; editingId.value = cat.id; showModal.value = true
}

const submit = async () => {
  submitting.value = true
  try {
    const url = isEditing.value ? '/api/commerce/categories/' + editingId.value : '/api/commerce/categories'
    const method = isEditing.value ? 'put' : 'post'
    await window.axios[method](url, form.value)
    showModal.value = false
    await fetchCategories()
  } catch (e) {
    alert('Erreur: ' + (e.response?.data?.message || e.message))
  } finally { submitting.value = false }
}

const deleteCat = async (id) => {
  if (!confirm('Confirmer la suppression ?')) return
  try {
    await window.axios.delete('/api/commerce/categories/' + id)
    await fetchCategories()
  } catch (e) { alert('Erreur: ' + e.message) }
}

onMounted(fetchCategories)
</script>

<template>
  <GelLayout page-title="Catégories">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <p class="text-muted small mb-0">Gérez les catégories de produits</p>
      <button class="btn btn-primary btn-sm" @click="openCreate"><i class="bi-plus-lg me-1"></i>Nouvelle catégorie</button>
    </div>

    <div v-if="state === 'loading'" class="d-flex justify-content-center py-5">
      <div class="spinner-border text-primary"></div>
    </div>

    <div v-else class="row g-3">
      <div v-for="cat in categories" :key="cat.id" class="col-md-4 col-6">
        <div class="card card-dashboard p-3">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <span class="badge mb-2" :style="{ backgroundColor: cat.color || '#6c757d' }">{{ cat.name }}</span>
              <div v-if="cat.parent" class="small text-muted">Sous-catégorie de: {{ cat.parent.name }}</div>
              <div class="small text-muted mt-1">{{ cat.products_count || 0 }} produit(s)</div>
            </div>
            <div class="d-flex gap-1">
              <button class="btn btn-sm btn-outline-secondary" @click="openEdit(cat)"><i class="bi-pencil"></i></button>
              <button class="btn btn-sm btn-outline-danger" @click="deleteCat(cat.id)"><i class="bi-trash"></i></button>
            </div>
          </div>
        </div>
      </div>
      <div v-if="!categories.length" class="col-12 text-center py-4 text-muted">Aucune catégorie.</div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal-content-custom" style="max-width:450px;">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
          <h5 class="fw-bold mb-0">{{ isEditing ? 'Modifier' : 'Nouvelle catégorie' }}</h5>
          <button class="btn-close" @click="showModal = false"></button>
        </div>
        <form @submit.prevent="submit">
          <div class="mb-2">
            <label class="form-label small">Nom *</label>
            <input v-model="form.name" class="form-control form-control-sm" required>
          </div>
          <div class="mb-2">
            <label class="form-label small">Catégorie parente</label>
            <select v-model="form.parent_id" class="form-select form-select-sm">
              <option value="">Aucune (racine)</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label small">Couleur</label>
            <input v-model="form.color" type="color" class="form-control form-control-color form-control-sm">
          </div>
          <div class="mb-3 form-check">
            <input v-model="form.is_active" type="checkbox" class="form-check-input" id="cat-active">
            <label class="form-check-label small" for="cat-active">Active</label>
          </div>
          <div class="d-flex justify-content-end gap-2 border-top pt-2">
            <button type="button" class="btn btn-sm btn-secondary" @click="showModal = false">Annuler</button>
            <button type="submit" class="btn btn-sm btn-primary" :disabled="submitting">
              <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
              {{ isEditing ? 'Modifier' : 'Créer' }}
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
  background: #fff; border-radius: 12px; padding: 1.5rem; width: 90%; max-height: 85vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}
</style>
