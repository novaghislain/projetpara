<template>
    <GelLayout page-title="Workflows d'Approbation">
        <div class="p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Workflows d'Approbation</h2>
                <p class="text-gray-500">Coming soon.</p>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
/* ============================================================
 * Composant : ApprovalWorkflows/Index
 * Description : Page de liste des workflows d'approbation.
 *   Affiche l'ensemble des workflows configures avec
 *   leurs etapes et leur statut.
 * ============================================================ */

import GelLayout from '../../../Layouts/GelLayout.vue';

/* Proprietes recues du controleur :
 *   workflows - Liste des workflows d'approbation
 *   clients   - Liste des clients pour le filtrage */
defineProps(['workflows', 'clients'])
</script>
