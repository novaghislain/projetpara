<template>
  <div class="gel-client-portal min-vh-100 d-flex align-items-center justify-content-center">
    <div class="portal-card p-5 rounded-4 shadow-lg" style="max-width:440px; width:100%;">
      <div class="text-center mb-4">
        <img src="/images/gel-logo.svg" alt="GEL Cabinet" height="60" />
      </div>
      <div class="text-center mb-3">
        <span class="badge rounded-pill px-3 py-2">🔒 Espace sécurisé</span>
      </div>
      <h1 class="text-center fs-5 fw-semibold mb-1">Espace Client GEL Cabinet</h1>
      <p class="text-center text-muted small mb-4">Tous vos documents et services dans un espace simplifié et sécurisé.</p>
      <div v-if="error" class="alert alert-danger rounded-3 small">⚠️ {{ error }}</div>
      <div class="mb-3">
        <label class="form-label small fw-semibold">Adresse email</label>
        <input v-model="form.email" type="email" class="form-control form-control-lg rounded-3" placeholder="votre@email.com" />
      </div>
      <div class="mb-2">
        <label class="form-label small fw-semibold">Mot de passe</label>
        <input v-model="form.password" type="password" class="form-control form-control-lg rounded-3" placeholder="••••••••" />
      </div>
      <div class="text-end mb-4">
        <a href="/mot-de-passe-oublie" class="small text-decoration-none">Mot de passe oublié ?</a>
      </div>
      <button @click="login" class="btn btn-lg w-100 rounded-3 fw-semibold" :disabled="loading">
        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
        {{ loading ? 'Connexion…' : 'Se connecter' }}
      </button>
      <div class="text-center mt-4">
        <span class="badge rounded-pill px-3 py-2 small">✨ Propulsé par IA — GEL Cabinet</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const form = ref({ email: '', password: '' })
const loading = ref(false)
const error = ref("")

const login = async () => {
  loading.value = true
  error.value = ""
  try {
    await axios.post('/api/login', form.value)
    router.push('/')
  } catch (e) {
    error.value = e.response?.data?.message || e.message
  } finally {
    loading.value = false
  }
}
</script>
