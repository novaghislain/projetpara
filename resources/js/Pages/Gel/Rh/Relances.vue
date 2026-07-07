<template>
    <GelLayout>
        <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h1 class="page-title">Relances</h1>
                    <p class="page-subtitle">Gestion des relances clients et règles de relance.</p>
                </div>
                <button class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nouvelle relance</button>
            </div>
            <div class="card-dashboard">
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-bell" style="font-size:3rem;"></i>
                    <p class="mt-3">Module de relances en cours de déploiement.</p>
                </div>
            </div>
        </div>
    </GelLayout>
</template>
<script setup>
import GelLayout from '../../../Layouts/GelLayout.vue';
</script>
