<template>
    <GelLayout page-title="Base de Connaissances">
        <div class="p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Base de Connaissances</h2>
                <p class="text-gray-500">Coming soon.</p>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
/*
 * Page : KnowledgeBase Index
 * Role : Liste des articles de la base de connaissances avec filtres par catégorie
 * Props :
 *   articles   (Object) — Données paginées des articles
 *   categories (Array)  — Liste des catégories pour le filtre
 */
import GelLayout from '../../../../Layouts/GelLayout.vue';
defineProps(['articles', 'categories'])
</script>
