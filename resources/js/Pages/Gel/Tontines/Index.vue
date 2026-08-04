<template>
    <GelLayout page-title="Tontines">
        <div class="p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Tontines</h2>
                <p class="text-gray-500">Coming soon.</p>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
/*
 * Page : Tontines Index
 * Role : Liste des tontines (épargne collective) avec recherche, filtres et pagination
 * Props :
 *   tontines (Object) — Données paginées des tontines
 *   clients  (Array)  — Liste des clients pour le filtre
 */
import GelLayout from '../../../Layouts/GelLayout.vue';
defineProps(['tontines', 'clients'])
</script>
