<template>
    <GelLayout>
        <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h1 class="page-title">Rapprochement bancaire</h1>
                    <p class="page-subtitle">Rapprochement des relevés bancaires.</p>
                </div>
                <button class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nouveau rapprochement</button>
            </div>
            <div class="card-dashboard">
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-arrow-left-right" style="font-size:3rem;"></i>
                    <p class="mt-3">Module de rapprochement bancaire en cours de déploiement.</p>
                </div>
            </div>
        </div>
    </GelLayout>
</template>
<script setup>
/* ============================================
 * Rapprochement.vue — Module de rapprochement bancaire
 * Permet le rapprochement des relevés bancaires avec la comptabilité.
 * ============================================ */

/* Layout principal de l'interface GEL */
import GelLayout from '../../../Layouts/GelLayout.vue';
</script>
