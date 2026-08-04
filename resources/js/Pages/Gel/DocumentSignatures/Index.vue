<template>
    <GelLayout page-title="Signatures Electroniques">
        <div class="p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Signatures Electroniques</h2>
                <p class="text-gray-500">Coming soon.</p>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
/* ============================================================
 * Composant : DocumentSignatures/Index
 * Description : Page de liste des signatures electroniques.
 *   Affiche l'ensemble des signatures et leur statut.
 *   (En cours de construction)
 * ============================================================ */

import GelLayout from '../../../Layouts/GelLayout.vue';

/* Propriete recue du controleur :
 *   signatures - Liste des signatures electroniques */
defineProps(['signatures'])
</script>
