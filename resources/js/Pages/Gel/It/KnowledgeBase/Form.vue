<template>
    <GelLayout page-title="Nouvel Article">
        <div class="p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Nouvel Article</h2>
                <p class="text-gray-500">Coming soon.</p>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
/*
 * Page : KnowledgeBase Form
 * Role : Formulaire de création et d'édition d'un article de la base de connaissances
 * Props :
 *   article    (Object|null) — Article existant (mode édition) ou null (mode création)
 *   categories (Array)       — Liste des catégories disponibles
 */
import GelLayout from '../../../../Layouts/GelLayout.vue';
defineProps([])
</script>
