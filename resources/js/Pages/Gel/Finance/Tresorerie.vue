<template>
    <GelLayout>
        <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h1 class="page-title">Trésorerie</h1>
                    <p class="page-subtitle">Suivi de la trésorerie et des flux financiers.</p>
                </div>
                <button class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nouvelle opération</button>
            </div>
            <div class="card-dashboard">
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-cash-stack" style="font-size:3rem;"></i>
                    <p class="mt-3">Module de trésorerie en cours de déploiement.</p>
                </div>
            </div>
        </div>
    </GelLayout>
</template>
<script setup>
/* ============================================
 * Tresorerie.vue — Module de suivi de trésorerie
 * Affiche les flux financiers et les opérations de caisse.
 * ============================================ */

/* Layout principal de l'interface GEL */
import GelLayout from '../../../Layouts/GelLayout.vue';
</script>
