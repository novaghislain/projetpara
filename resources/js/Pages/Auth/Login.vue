<template>
  <AuthNavbar />
  <div class="min-h-screen bg-gradient-to-br from-orange-50 via-white to-orange-100 flex items-center justify-center pt-20 pb-12 px-4 relative overflow-hidden">
    <!-- Arrière-plan animé : orbes flottantes -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute -top-20 -left-20 w-72 h-72 bg-primary/5 rounded-full animate-blob"></div>
      <div class="absolute -bottom-20 -right-20 w-96 h-96 bg-primary/10 rounded-full animate-blob" style="animation-delay: -4s"></div>
      <div class="absolute top-1/3 right-1/4 w-48 h-48 bg-orange-200/30 rounded-full animate-blob" style="animation-delay: -8s"></div>
    </div>
    <div class="max-w-md w-full bg-white rounded-xl shadow-lg overflow-hidden animate-slide-up">
      <!-- Header -->
      <div class="bg-primary px-8 py-6 text-white text-center animate-slide-down">
        <h1 class="text-2xl font-bold">Connexion</h1>
        <p class="text-orange-100 mt-2">Accédez à votre espace ComptaSaaS</p>
      </div>

      <form @submit.prevent="login" class="px-8 py-6">
        <!-- Error -->
        <Transition name="fade">
          <div v-if="errorMessage" key="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md mb-4 text-sm">
            {{ errorMessage }}
          </div>
        </Transition>

        <!-- Email -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Adresse email *</label>
          <input v-model="form.email" type="email" required
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                 placeholder="votre@email.com" />
          <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</p>
        </div>

        <!-- Password -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Mot de passe *</label>
          <input v-model="form.password" type="password" required
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                 placeholder="Votre mot de passe" />
          <p v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password }}</p>
        </div>

        <!-- Remember -->
        <div class="mb-6">
          <label class="flex items-center space-x-3">
            <input v-model="form.remember" type="checkbox"
                   class="h-4 w-4 text-primary border-gray-300 rounded" />
            <span class="text-sm text-gray-600">Se souvenir de moi</span>
          </label>
        </div>

        <!-- Loading -->
        <Transition name="fade">
          <div v-if="loading" key="loading" class="text-center text-primary mb-4">
            Connexion en cours...
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary mx-auto mt-2"></div>
          </div>
        </Transition>

        <!-- Submit -->
        <button type="submit" :disabled="loading"
                class="w-full bg-primary text-white py-3 rounded-md hover:bg-brand-orange-dark disabled:opacity-50 font-medium text-lg transition-all duration-200 transform active:scale-[0.98]">
          Se connecter
        </button>

        <div class="text-center mt-4 text-sm text-gray-500">
          Pas encore de compte ?
          <a href="/register" class="text-primary hover:underline">Créer un compte</a>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import AuthNavbar from '../../Components/AuthNavbar.vue';

const form = ref({
  email: '',
  password: '',
  remember: false,
});

const errors = ref({});
const loading = ref(false);
const errorMessage = ref('');

async function login() {
  loading.value = true;
  errorMessage.value = '';
  errors.value = {};

  try {
    const response = await axios.post('/api/login', {
      email: form.value.email,
      password: form.value.password,
    });

    const data = response.data.data || response.data;
    localStorage.setItem('token', data.access_token || data.token);
    axios.defaults.headers.common['Authorization'] = `Bearer ${data.access_token || data.token}`;

    // Rediriger vers le dashboard
    window.location.href = '/company/dashboard';
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors || {};
      const firstErrors = Object.values(errors.value).flat();
      errorMessage.value = firstErrors.join('. ');
    } else {
      errorMessage.value = e.response?.data?.message || 'Email ou mot de passe incorrect.';
    }
  } finally {
    loading.value = false;
  }
}
</script>

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
</style>
