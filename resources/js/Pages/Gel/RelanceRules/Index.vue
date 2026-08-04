<template>
    <GelLayout page-title="Regles de Relance">
        <div class="p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Regles de Relance</h2>
                <p class="text-gray-500">Coming soon.</p>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
/* ============================================================
 * Composant : RelanceRules/Index
 * Description : Page de liste des regles de relance.
 *   Affiche l'ensemble des regles de relance configurees
 *   avec leur statut et leur declencheur.
 * ============================================================ */

import GelLayout from '../../../Layouts/GelLayout.vue';

/* Proprietes recues du controleur :
 *   rules   - Liste des regles de relance
 *   clients - Liste des clients pour le filtrage */
defineProps(['rules', 'clients'])
</script>
