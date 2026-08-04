<template>
    <GelLayout page-title="Detail Article">
        <div class="p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Detail Article</h2>
                <p class="text-gray-500">Coming soon.</p>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
/*
 * Page : KnowledgeBase Show
 * Role : Affiche le contenu complet d'un article de la base de connaissances
 * Props :
 *   article (Object) — Données de l'article à afficher
 */
import GelLayout from '../../../../Layouts/GelLayout.vue';
defineProps(['article'])
</script>
