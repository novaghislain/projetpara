<template>
    <GelLayout page-title="Detail Signature">
        <div class="p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Detail Signature</h2>
                <p class="text-gray-500">Coming soon.</p>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
/* ============================================================
 * Composant : DocumentSignatures/Show
 * Description : Page de detail d'une signature electronique.
 *   Affiche les informations de la signature ainsi que
 *   son statut actuel.
 * ============================================================ */

import GelLayout from '../../../Layouts/GelLayout.vue';

/* Propriete recue du controleur :
 *   signature - Objet contenant les donnees de la signature */
defineProps(['signature'])
</script>
