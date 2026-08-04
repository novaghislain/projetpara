<template>
    <div style="padding: 40px; text-align: center; background: #f8f9fc; min-height: 100vh;">
        <div style="background: white; border-radius: 10px; padding: 30px; box-shadow: 0 2px 12px rgba(0,0,0,0.06);">
            <h1 style="color: #FF7900; font-weight: 700; font-family: Outfit, sans-serif;">TEST SANS CPA LAYOUT</h1>
            <p>Rôle: <strong>{{ role }}</strong></p>
            <p>isSuperAdmin: <strong>{{ isSuperAdmin }}</strong></p>
            <p>Utilisateur: <strong>{{ authStore.user?.name }}</strong></p>
            <p>isAuthenticated: <strong>{{ authStore.isAuthenticated }}</strong></p>
        </div>
    </div>
</template>

<script setup>
/*
 * Test.vue - Page de test basique sans le layout CPA
 *
 * Utilisée pour vérifier l'authentification et le rôle de l'utilisateur
 * connecté en affichant les valeurs brutes du store. Utile pour le
 * débogage rapide des permissions sans la surcharge du layout principal.
 *
 * NOTE : Ce composant n'utilise PAS CpaLayout et n'a pas de styles
 * scoped — tout est en inline pour un rendu minimaliste.
 */

import { computed } from 'vue';
import { authStore } from '../../stores/auth';

// Récupération du rôle utilisateur depuis le store d'authentification
const role = computed(() => authStore.user?.role || 'inconnu');
const isSuperAdmin = computed(() => role.value === 'super_admin');
</script>
