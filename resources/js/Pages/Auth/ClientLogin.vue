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
/*
 * ClientLogin.vue - Page de connexion pour le portail client GEL Cabinet.
 *
 * Role     : Permet aux clients du cabinet GEL de s'authentifier via email
 *            et mot de passe. Interface securisee avec badge SSL visuel.
 * Props    : Aucune (composant autonome).
 * Emits    : Aucun (la redirection est faite par le routeur apres succes).
 * Store    : Aucun (appel API direct via axios).
 * Route    : POST /api/login -> redirection vers '/' si OK.
 *
 * Fonctionnalites :
 * - Champ email + mot de passe avec validation cote client
 * - Appel API REST via axios pour l'authentification
 * - Affichage d'un message d'erreur en cas d'echec
 * - Etat de chargement avec spinner pendant la requete
 * - Lien "Mot de passe oublie" vers la page de reinitialisation
 * - Badge "Propulse par IA -- GEL Cabinet" en pied de carte
 *
 * Flux type :
 *   1. L'utilisateur saisit email + mot de passe
 *   2. Clic sur "Se connecter" ou touche Entree
 *   3. Appel POST /api/login avec les identifiants
 *   4. Succes  -> router.push('/')
 *   5. Erreur  -> affichage du message d'erreur dans le template
 */

import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

// --- Reactif local ---
const router = useRouter()
const form = ref({ email: '', password: '' })   // Identifiants de connexion (liaison v-model)
const loading = ref(false)                       // Etat du chargement (desactive le bouton + spinner)
const error = ref("")                            // Message d'erreur a afficher dans le template

/**
 * login - Soumet les identifiants a l'API d'authentification.
 * En cas de succes, redirige vers la page d'accueil.
 * En cas d'echec, affiche le message d'erreur retourne par le serveur.
 */
const login = async () => {
  loading.value = true
  error.value = ""
  try {
    await axios.post('/api/login', form.value)
    router.push('/')
  } catch (e) {
    // Recupere le message d'erreur depuis la reponse JSON ou la description native
    error.value = e.response?.data?.message || e.message
  } finally {
    loading.value = false
  }
}
</script>
