<template>
    <GelLayout page-title="Nouvelle Regle de Relance">
        <div class="p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Nouvelle Regle de Relance</h2>
                <p class="text-gray-500">Coming soon.</p>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
/* ============================================================
 * Composant : RelanceRules/Form
 * Description : Formulaire de creation / modification
 *   d'une regle de relance. Permet de configurer
 *   le declencheur, le canal et le destinataire.
 * ============================================================ */

import GelLayout from '../../../Layouts/GelLayout.vue';

/* Propriete recue du controleur :
 *   clients - Liste des clients pour l'association */
defineProps(['clients'])
</script>
