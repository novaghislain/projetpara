<script setup>
import { ref, onMounted } from 'vue'
import GelLayout from '../../Layouts/GelLayout.vue'

const state = ref('loading')
const users = ref([])
const roles = ref([])
const availableUsers = ref([])
const showModal = ref(false)
const editingId = ref(null)
const submitting = ref(false)

const form = ref({ user_id: '', business_role_id: '', can_login: true, can_sell: true })

const fetchUsers = async () => {
  try {
    const res = await window.axios.get('/api/commerce/business-users')
    users.value = res.data
  } catch (e) { /* */ }
  finally { state.value = 'loaded' }
}

const fetchRoles = async () => {
  try {
    const res = await window.axios.get('/api/commerce/business-roles')
    roles.value = res.data
  } catch (e) { /* */ }
}

const fetchAvailable = async () => {
  try {
    const res = await window.axios.get('/api/commerce/business-users/available')
    availableUsers.value = res.data
  } catch (e) { /* */ }
}

const openCreate = () => {
  form.value = { user_id: '', business_role_id: '', can_login: true, can_sell: true }
  editingId.value = null
  showModal.value = true
  fetchAvailable()
}

const editUser = (u) => {
  form.value = { user_id: u.user_id, business_role_id: u.business_role_id || '', can_login: u.can_login, can_sell: u.can_sell }
  editingId.value = u.id
  showModal.value = true
}

const submit = async () => {
  submitting.value = true
  try {
    if (editingId.value) {
      await window.axios.put('/api/commerce/business-users/' + editingId.value, form.value)
    } else {
      await window.axios.post('/api/commerce/business-users', form.value)
    }
    showModal.value = false
    await fetchUsers()
  } catch (e) {
    alert('Erreur: ' + (e.response?.data?.message || e.message))
  } finally { submitting.value = false }
}

const deleteUser = async (id) => {
  if (!confirm('Confirmer la suppression ?')) return
  try {
    await window.axios.delete('/api/commerce/business-users/' + id)
    await fetchUsers()
  } catch (e) { alert('Erreur: ' + e.message) }
}

const roleName = (roleId) => roles.value.find(r => r.id === roleId)?.name || '—'

onMounted(async () => {
  await Promise.all([fetchUsers(), fetchRoles()])
})
</script>

<template>
  <GelLayout page-title="Utilisateurs POS">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <p class="text-muted small mb-0">Gérez les utilisateurs autorisés à utiliser la caisse</p>
      <button class="btn btn-primary btn-sm" @click="openCreate"><i class="bi-plus-lg me-1"></i>Ajouter utilisateur</button>
    </div>

    <div v-if="state === 'loading'" class="d-flex justify-content-center py-5">
      <div class="spinner-border text-primary"></div>
    </div>

    <div v-else class="card card-dashboard">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="small text-muted">
            <tr>
              <th>Utilisateur</th>
              <th>Email</th>
              <th>Rôle</th>
              <th>Connexion</th>
              <th>Vente</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!users.length"><td colspan="6" class="text-center py-4 text-muted">Aucun utilisateur.</td></tr>
            <tr v-for="u in users" :key="u.id">
              <td class="fw-medium">{{ u.user?.name || '—' }}</td>
              <td class="small">{{ u.user?.email || '—' }}</td>
              <td><span class="badge bg-light text-dark">{{ roleName(u.business_role_id) }}</span></td>
              <td>
                <span v-if="u.can_login" class="text-success"><i class="bi-check-circle"></i></span>
                <span v-else class="text-muted"><i class="bi-x-circle"></i></span>
              </td>
              <td>
                <span v-if="u.can_sell" class="text-success"><i class="bi-check-circle"></i></span>
                <span v-else class="text-muted"><i class="bi-x-circle"></i></span>
              </td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-secondary me-1" @click="editUser(u)"><i class="bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger" @click="deleteUser(u.id)"><i class="bi-trash"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal-content-custom" style="max-width:450px;">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
          <h5 class="fw-bold mb-0">{{ editingId ? 'Modifier' : 'Ajouter un utilisateur' }}</h5>
          <button class="btn-close" @click="showModal = false"></button>
        </div>
        <form @submit.prevent="submit">
          <div class="mb-2" v-if="!editingId">
            <label class="form-label small">Utilisateur *</label>
            <select v-model="form.user_id" class="form-select form-select-sm" required>
              <option value="">Sélectionner...</option>
              <option v-for="u in availableUsers" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label small">Rôle commercial</label>
            <select v-model="form.business_role_id" class="form-select form-select-sm">
              <option value="">Aucun</option>
              <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
            </select>
          </div>
          <div class="mb-2 form-check">
            <input v-model="form.can_login" type="checkbox" class="form-check-input" id="bu-login">
            <label class="form-check-label small" for="bu-login">Peut se connecter à la caisse</label>
          </div>
          <div class="mb-3 form-check">
            <input v-model="form.can_sell" type="checkbox" class="form-check-input" id="bu-sell">
            <label class="form-check-label small" for="bu-sell">Peut effectuer des ventes</label>
          </div>
          <div class="d-flex justify-content-end gap-2 border-top pt-2">
            <button type="button" class="btn btn-sm btn-secondary" @click="showModal = false">Annuler</button>
            <button type="submit" class="btn btn-sm btn-primary" :disabled="submitting">
              <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
              {{ editingId ? 'Modifier' : 'Ajouter' }}
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
