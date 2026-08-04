<template>
    <GelLayout page-title="Detail Workflow d'Approbation">
        <div class="p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Detail Workflow d'Approbation</h2>
                <p class="text-gray-500">Coming soon.</p>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
/* ============================================================
 * Composant : ApprovalWorkflows/Show
 * Description : Page de detail d'un workflow d'approbation.
 *   Affiche les etapes, les approbateurs, les conditions
 *   et le statut actuel du workflow.
 * ============================================================ */

import GelLayout from '../../../Layouts/GelLayout.vue';

/* Propriete recue du controleur :
 *   workflow - Objet contenant les donnees du workflow
 *              (id, name, steps, approvers, status) */
defineProps(['workflow'])
</script>
