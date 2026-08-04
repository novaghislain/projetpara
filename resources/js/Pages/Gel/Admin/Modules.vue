<template>
    <GelLayout>
        <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h1 class="page-title">Modules</h1>
                    <p class="page-subtitle">Gestion des modules et fonctionnalités du cabinet.</p>
                </div>
            </div>
            <div class="card-dashboard">
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-puzzle" style="font-size:3rem;"></i>
                    <p class="mt-3">Gestion des modules en cours de déploiement.</p>
                </div>
            </div>
        </div>
    </GelLayout>
</template>
<script setup>
/* ============================================================
 * Modules — Gestion des modules et fonctionnalités du cabinet
 * Page d'administration des modules disponibles. Affiche un
 * placeholder en attendant le déploiement complet.
 * ============================================================ */
import GelLayout from '../../../Layouts/GelLayout.vue';
</script>
