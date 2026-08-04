<template>
    <GelLayout page-title="Centres de Cout">
        <div class="p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Centres de Cout</h2>
                <p class="text-gray-500">Coming soon.</p>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
/* ============================================================
 * Composant : CostCenters/Index
 * Description : Page de liste des centres de cout.
 *   Affiche la liste complete des centres de cout avec
 *   les donnees clients associees. (En cours de construction)
 * ============================================================ */

import GelLayout from '../../../Layouts/GelLayout.vue';

/* Proprietes recues du controleur :
 *   centers  - Liste des centres de cout
 *   clients  - Liste des clients pour le filtrage */
defineProps(['centers', 'clients'])
</script>
