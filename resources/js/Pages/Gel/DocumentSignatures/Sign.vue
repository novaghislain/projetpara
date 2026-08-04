<template>
    <GelLayout page-title="Signature de Document">
        <div class="p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Signature de Document</h2>
                <p class="text-gray-500">Coming soon.</p>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
/* ============================================================
 * Composant : DocumentSignatures/Sign
 * Description : Page de signature electronique d'un document.
 *   Interface permettant a l'utilisateur d'apposer
 *   sa signature sur un document donne.
 * ============================================================ */

import GelLayout from '../../../Layouts/GelLayout.vue';

/* Propriete recue du controleur :
 *   signature - Objet contenant les donnees de la signature a apposer */
defineProps(['signature'])
</script>
