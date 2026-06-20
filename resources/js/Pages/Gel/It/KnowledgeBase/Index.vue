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
import GelLayout from '../../../../Layouts/GelLayout.vue';
defineProps(['articles', 'categories'])
</script>
