<template>
    <GelLayout page-title="Nouvelle Tontine">
        <div class="p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Nouvelle Tontine</h2>
                <p class="text-gray-500">Coming soon.</p>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
/*
 * Page : Tontines Form
 * Role : Formulaire de création et d'édition d'une tontine (épargne collective)
 * Props :
 *   tontine (Object|null) — Tontine existante (mode édition) ou null (mode création)
 *   clients (Array)       — Liste des clients pour le select des participants
 */
import GelLayout from '../../../Layouts/GelLayout.vue';
defineProps(['clients'])
</script>
