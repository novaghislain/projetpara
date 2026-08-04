<template>
    <GelLayout page-title="Nouveau Workflow d'Approbation">
        <div class="p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Nouveau Workflow d'Approbation</h2>
                <p class="text-gray-500">Coming soon.</p>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
/* ============================================================
 * Composant : ApprovalWorkflows/Form
 * Description : Formulaire de creation / modification
 *   d'un workflow d'approbation. Permet de definir
 *   les etapes et les approbateurs associes.
 * ============================================================ */

import GelLayout from '../../../Layouts/GelLayout.vue';

/* Propriete recue du controleur :
 *   clients - Liste des clients pour l'association */
defineProps(['clients'])
</script>
