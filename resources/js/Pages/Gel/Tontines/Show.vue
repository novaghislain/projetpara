<template>
    <GelLayout page-title="Detail Tontine">
        <div class="p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Detail Tontine</h2>
                <p class="text-gray-500">Coming soon.</p>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
/*
 * Page : Tontines Show
 * Role : Affiche le détail d'une tontine (participants, échéances, cotisations)
 * Props :
 *   tontine (Object) — Données de la tontine à afficher
 */
import GelLayout from '../../../Layouts/GelLayout.vue';
defineProps(['tontine'])
</script>
