<script setup>
/* ═══════════════════════════════════════════════════════════
   Suppliers.vue - Gestion des fournisseurs
   CRUD complet : nom, contact, téléphone, email, adresse
   et délai de livraison.
   ═══════════════════════════════════════════════════════════ */
import { ref, onMounted } from 'vue'
import GelLayout from '../../Layouts/GelLayout.vue'

/* ══════════════════════════════════════════
   État réactif du composant
   ══════════════════════════════════════════ */
const state = ref('loading')          /* 'loading' | 'loaded' | 'error' */
const suppliers = ref([])             /* Liste des fournisseurs */
const showModal = ref(false)          /* Visibilité de la modale */
const isEditing = ref(false)          /* Mode édition (true) ou création (false) */
const editingId = ref(null)           /* ID du fournisseur en cours d'édition */
const submitting = ref(false)         /* État de soumission du formulaire */
const form = ref({ name: '', contact_name: '', phone: '', email: '', address: '', delivery_delay: 0, is_active: true })  /* Formulaire */

/* ══════════════════════════════════════════
   Requêtes API
   ══════════════════════════════════════════ */
/* Charge la liste des fournisseurs */
const fetch = async () => {
  try {
    const res = await window.axios.get('/api/commerce/suppliers')
    suppliers.value = res.data
  } catch (e) { /* */ }
  finally { state.value = 'loaded' }
}

/* ══════════════════════════════════════════
   Gestion du formulaire (création / édition)
   ══════════════════════════════════════════ */
/* Réinitialise le formulaire à ses valeurs par défaut */
const resetForm = () => { form.value = { name: '', contact_name: '', phone: '', email: '', address: '', delivery_delay: 0, is_active: true } }

/* Ouvre la modale en mode création */
const openCreate = () => { resetForm(); isEditing.value = false; editingId.value = null; showModal.value = true }

/* Ouvre la modale en mode édition avec les données pré-remplies */
const openEdit = (s) => {
  form.value = { name: s.name, contact_name: s.contact_name || '', phone: s.phone || '', email: s.email || '', address: s.address || '', delivery_delay: s.delivery_delay || 0, is_active: s.is_active }
  isEditing.value = true; editingId.value = s.id; showModal.value = true
}

/* Soumet le formulaire (création ou mise à jour) */
const submit = async () => {
  submitting.value = true
  try {
    const url = isEditing.value ? '/api/commerce/suppliers/' + editingId.value : '/api/commerce/suppliers'
    const method = isEditing.value ? 'put' : 'post'
    await window.axios[method](url, form.value)
    showModal.value = false
    await fetch()
  } catch (e) {
    alert('Erreur: ' + (e.response?.data?.message || e.message))
  } finally { submitting.value = false }
}

/* Supprime un fournisseur après confirmation */
const deleteItem = async (id) => {
  if (!confirm('Confirmer la suppression ?')) return
  try { await window.axios.delete('/api/commerce/suppliers/' + id); await fetch() }
  catch (e) { alert('Erreur: ' + e.message) }
}

/* ══════════════════════════════════════════
   Cycle de vie
   ══════════════════════════════════════════ */
onMounted(fetch)
</script>

<template>
  <GelLayout page-title="Fournisseurs">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <p class="text-muted small mb-0">Gérez vos fournisseurs</p>
      <button class="btn btn-primary btn-sm" @click="openCreate"><i class="bi-plus-lg me-1"></i>Nouveau fournisseur</button>
    </div>

    <div v-if="state === 'loading'" class="d-flex justify-content-center py-5">
      <div class="spinner-border text-primary"></div>
    </div>

    <div v-else class="card card-dashboard">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="small text-muted">
            <tr><th>Nom</th><th>Contact</th><th>Téléphone</th><th>Email</th><th>Délai</th><th class="text-end">Actions</th></tr>
          </thead>
          <tbody>
            <tr v-if="!suppliers.length"><td colspan="6" class="text-center py-4 text-muted">Aucun fournisseur.</td></tr>
            <tr v-for="s in suppliers" :key="s.id">
              <td class="fw-medium">{{ s.name }}</td>
              <td class="small">{{ s.contact_name || '-' }}</td>
              <td class="small">{{ s.phone || '-' }}</td>
              <td class="small">{{ s.email || '-' }}</td>
              <td class="small">{{ s.delivery_delay ? s.delivery_delay + ' jours' : '-' }}</td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-secondary me-1" @click="openEdit(s)"><i class="bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger" @click="deleteItem(s.id)"><i class="bi-trash"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal-content-custom" style="max-width:500px;">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
          <h5 class="fw-bold mb-0">{{ isEditing ? 'Modifier' : 'Nouveau fournisseur' }}</h5>
          <button class="btn-close" @click="showModal = false"></button>
        </div>
        <form @submit.prevent="submit">
          <div class="row g-2">
            <div class="col-12">
              <label class="form-label small">Nom *</label>
              <input v-model="form.name" class="form-control form-control-sm" required>
            </div>
            <div class="col-6">
              <label class="form-label small">Contact</label>
              <input v-model="form.contact_name" class="form-control form-control-sm">
            </div>
            <div class="col-6">
              <label class="form-label small">Téléphone</label>
              <input v-model="form.phone" class="form-control form-control-sm">
            </div>
            <div class="col-6">
              <label class="form-label small">Email</label>
              <input v-model="form.email" type="email" class="form-control form-control-sm">
            </div>
            <div class="col-6">
              <label class="form-label small">Délai livraison (jours)</label>
              <input v-model="form.delivery_delay" type="number" class="form-control form-control-sm" min="0">
            </div>
            <div class="col-12">
              <label class="form-label small">Adresse</label>
              <input v-model="form.address" class="form-control form-control-sm">
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-3 pt-2 border-top">
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
