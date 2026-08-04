<template>
    <GelLayout page-title="Politiques SLA">
        <div class="p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Politiques SLA</h2>
                <p class="text-gray-500">Coming soon.</p>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
/*
 * Page : SlaPolicies Index
 * Role : Liste des politiques SLA (Service Level Agreements) avec recherche et pagination
 * Props :
 *   policies (Object) — Données paginées des politiques SLA
 */
import GelLayout from '../../../../Layouts/GelLayout.vue';
defineProps(['policies'])
</script>
