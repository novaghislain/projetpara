<template>
    <GelLayout page-title="Nouveau Centre de Cout">
        <div class="p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Nouveau Centre de Cout</h2>
                <p class="text-gray-500">Coming soon.</p>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
/* ============================================================
 * Composant : CostCenters/Form
 * Description : Formulaire de creation / modification
 *   d'un centre de cout. Permet de choisir un client
 *   et un centre parent. (En cours de construction)
 * ============================================================ */

import GelLayout from '../../../Layouts/GelLayout.vue';

/* Proprietes recues du controleur :
 *   clients  - Liste des clients pour l'association
 *   parents  - Liste des centres parents disponibles */
defineProps(['clients', 'parents'])
</script>
