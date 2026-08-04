<template>
    <GelLayout page-title="Actifs IT">
        <div class="p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Actifs IT</h2>
                <p class="text-gray-500">Coming soon.</p>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
/*
 * Page : Assets Index
 * Role : Liste des actifs IT avec filtres
 * Props :
 *   assets  (Object) — Données paginées des actifs
 *   clients (Array)  — Liste des clients pour le filtre
 */
import GelLayout from '../../../../Layouts/GelLayout.vue'; // Layout principal du module GEL
defineProps(['assets', 'clients']) // assets (Object) — Données paginées ; clients (Array) — pour le filtre
</script>
