<template>
    <GelLayout page-title="Detail Ticket">
        <div class="p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Detail Ticket</h2>
                <p class="text-gray-500">Coming soon.</p>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
/*
 * Page : Ticket Show
 * Role : Affiche le détail d'un ticket IT support
 * Props :
 *   ticket (Object) — Données du ticket à afficher
 */
import GelLayout from '../../../../Layouts/GelLayout.vue'; // Layout principal du module GEL
defineProps(['ticket']) // Données du ticket à afficher (mode détails)
</script>
