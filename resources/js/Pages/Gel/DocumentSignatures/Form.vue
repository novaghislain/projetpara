<template>
    <GelLayout page-title="Nouvelle Signature">
        <div class="p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Nouvelle Signature</h2>
                <p class="text-gray-500">Coming soon.</p>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
/* ============================================================
 * Composant : DocumentSignatures/Form
 * Description : Formulaire de creation d'une nouvelle
 *   signature electronique. Permet de selectionner
 *   le document a signer. (En cours de construction)
 * ============================================================ */

import GelLayout from '../../../Layouts/GelLayout.vue';

/* Propriete recue du controleur :
 *   documents - Liste des documents disponibles a la signature */
defineProps(['documents'])
</script>
