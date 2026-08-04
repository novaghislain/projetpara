<template>
  <AuthNavbar is-register-page />
  <div class="min-h-screen bg-gradient-to-br from-orange-50 via-white to-orange-100 flex items-center justify-center pt-20 pb-12 px-4 relative overflow-hidden">
    <!-- Arrière-plan animé : orbes flottantes -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute -top-20 -left-20 w-72 h-72 bg-primary/5 rounded-full animate-blob"></div>
      <div class="absolute -bottom-20 -right-20 w-96 h-96 bg-primary/10 rounded-full animate-blob" style="animation-delay: -4s"></div>
      <div class="absolute top-1/3 right-1/4 w-48 h-48 bg-orange-200/30 rounded-full animate-blob" style="animation-delay: -8s"></div>
    </div>
    <div class="max-w-2xl w-full bg-white rounded-xl shadow-lg overflow-hidden animate-slide-up">
      <!-- Header -->
      <div class="bg-primary px-8 py-6 text-white text-center animate-slide-down">
        <h1 class="text-2xl font-bold">Créer votre espace ComptaSaaS</h1>
        <p class="text-orange-100 mt-2">Solution comptable SYSCOHADA pour entreprises</p>
      </div>

      <form @submit.prevent="register" class="px-8 py-6">
        <!-- Section Entreprise -->
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Votre entreprise</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Nom de l'entreprise *</label>
            <input v-model="form.company_name" type="text" required
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                   placeholder="SARL ABC" />
            <p v-if="errors.company_name" class="text-red-500 text-xs mt-1">{{ errors.company_name }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Identifiant (slug) *</label>
            <input v-model="form.company_slug" type="text" required
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                   placeholder="sarl-abc" />
            <p class="text-xs text-gray-400 mt-1">Utilisé dans l'URL : comptasaas.ci/<strong>{{ form.company_slug || 'sarl-abc' }}</strong></p>
            <p v-if="errors.company_slug" class="text-red-500 text-xs mt-1">{{ errors.company_slug }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Email de l'entreprise</label>
            <input v-model="form.company_email" type="email"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                   placeholder="contact@sarl-abc.com" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Téléphone</label>
            <input v-model="form.company_phone" type="text"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                   placeholder="+225 01 02 03 04 05" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Pays</label>
            <select v-model="form.company_country"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
              <option value="CI">Côte d'Ivoire</option>
              <option value="SN">Sénégal</option>
              <option value="ML">Mali</option>
              <option value="BF">Burkina Faso</option>
              <option value="BJ">Bénin</option>
              <option value="TG">Togo</option>
              <option value="CM">Cameroun</option>
              <option value="GA">Gabon</option>
              <option value="CG">Congo</option>
              <option value="CD">RDC</option>
            </select>
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Adresse</label>
            <textarea v-model="form.company_address" rows="2"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                      placeholder="Abidjan, Plateau, Immeuble XYZ"></textarea>
          </div>
        </div>

        <!-- Section Utilisateur -->
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Votre compte administrateur</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
          <div>
            <label class="block text-sm font-medium text-gray-700">Nom complet *</label>
            <input v-model="form.name" type="text" required
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                   placeholder="Jean Dupont" />
            <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Email *</label>
            <input v-model="form.email" type="email" required
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                   placeholder="jean@exemple.com" />
            <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Mot de passe *</label>
            <input v-model="form.password" type="password" required minlength="8"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                   placeholder="Au moins 8 caractères" />
            <p v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Confirmer le mot de passe *</label>
            <input v-model="form.password_confirmation" type="password" required
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary" />
          </div>
        </div>

        <!-- Conditions -->
        <div class="mb-6">
          <label class="flex items-start space-x-3">
            <input v-model="form.accept_terms" type="checkbox" required
                   class="mt-1 h-4 w-4 text-primary border-gray-300 rounded" />
            <span class="text-sm text-gray-600">
              J'accepte les
              <a href="/conditions" class="text-primary hover:underline" target="_blank">conditions d'utilisation</a>
              et la
              <a href="/confidentialite" class="text-primary hover:underline" target="_blank">politique de confidentialité</a>
            </span>
          </label>
          <p v-if="errors.accept_terms" class="text-red-500 text-xs mt-1">{{ errors.accept_terms }}</p>
        </div>

        <!-- Loading & Messages -->
        <Transition name="fade">
          <div v-if="loading" key="loading" class="text-center text-primary mb-4">
            Création de votre espace comptable...
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary mx-auto mt-2"></div>
          </div>
        </Transition>

        <Transition name="fade">
          <div v-if="errorMessage" key="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md mb-4 text-sm">
            {{ errorMessage }}
          </div>
        </Transition>

        <!-- Submit -->
        <button type="submit" :disabled="loading"
                class="w-full bg-primary text-white py-3 rounded-md hover:bg-brand-orange-dark disabled:opacity-50 font-medium text-lg transition-all duration-200 transform active:scale-[0.98]">
          Créer mon espace ComptaSaaS
        </button>

        <div class="text-center mt-4 text-sm text-gray-500">
          Déjà un compte ?
          <a href="/login" class="text-primary hover:underline">Se connecter</a>
        </div>
      </form>

      <!-- Success -->
      <Transition name="scale">
        <div v-if="success" key="success" class="bg-green-50 px-8 py-6 border-t border-green-200 animate-fade-in">
          <div class="text-center">
            <div class="text-4xl mb-3 animate-bounce">🎉</div>
            <h3 class="text-xl font-bold text-green-800 mb-2">Compte créé avec succès !</h3>
            <p class="text-green-700 mb-4">
              Votre espace <strong>{{ success.company_name }}</strong> est prêt.<br/>
              Plan comptable SYSCOHADA installé. Vous êtes connecté.
            </p>
            <a :href="success.dashboard_url"
               class="inline-block bg-green-600 text-white px-6 py-3 rounded-md hover:bg-green-700 transition-all duration-200">
              Accéder à mon tableau de bord
            </a>
          </div>
        </div>
      </Transition>
    </div>
  </div>
</template>

<style>
/* ─── Transitions Vue ────────────────────────────────── */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.scale-enter-active {
  transition: all 0.35s ease-out;
}
.scale-leave-active {
  transition: all 0.2s ease-in;
}
.scale-enter-from {
  opacity: 0;
  transform: scale(0.9);
}
.scale-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>

<script setup>
/*
 * Composant ComptaSaaS - Inscription
 * Page d'inscription permettant de créer un nouvel espace comptable
 * avec les informations de l'entreprise et du compte administrateur.
 * Gère :
 *   - Saisie des données entreprise (nom, slug, email, téléphone, pays, adresse)
 *   - Saisie des données administrateur (nom, email, mot de passe)
 *   - Auto-génération du slug à partir du nom de l'entreprise
 *   - Soumission via axios et stockage du token JWT
 *   - Affichage des erreurs de validation (422) et des messages de succès
 */

import { ref, watch } from 'vue';
import axios from 'axios';
import AuthNavbar from '../../Components/AuthNavbar.vue';

/* État réactif du formulaire avec toutes les données d'inscription */
const form = ref({
  company_name: '',
  company_slug: '',
  company_email: '',
  company_phone: '',
  company_address: '',
  company_country: 'CI',
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  accept_terms: false,
});

/* État réactif : erreurs de validation, chargement, message d'erreur et succès */
const errors = ref({});
const loading = ref(false);
const errorMessage = ref('');
const success = ref(null);

/* Surveillance du nom pour générer automatiquement le slug URL */
watch(() => form.value.company_name, (name) => {
  if (!name) return;
  form.value.company_slug = name
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-|-$/g, '');
});

/* Fonction d'inscription : envoie les données à l'API et stocke le token JWT */
async function register() {
  loading.value = true;
  errorMessage.value = '';
  errors.value = {};

  try {
    const response = await axios.post('/api/register', form.value);
    const data = response.data.data;

    /* Stockage du token d'accès dans localStorage pour les requêtes ultérieures */
    localStorage.setItem('token', data.access_token);
    axios.defaults.headers.common['Authorization'] = `Bearer ${data.access_token}`;

    /* Passage en mode succès avec les informations du tenant créé */
    success.value = {
      company_name: data.tenant.name,
      dashboard_url: '/company/dashboard',
    };

  } catch (e) {
    /* Gestion des erreurs de validation (422) et des erreurs serveur */
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors || {};
      const firstErrors = Object.values(errors.value).flat();
      errorMessage.value = firstErrors.join('. ');
    } else {
      errorMessage.value = e.response?.data?.message || 'Erreur de connexion au serveur.';
    }
  } finally {
    loading.value = false;
  }
}
</script>
