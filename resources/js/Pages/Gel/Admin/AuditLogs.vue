<template>
    <GelLayout>
        <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h1 class="page-title">Journal d'audit</h1>
                    <p class="page-subtitle">Consultation des logs d'audit et de l'activité.</p>
                </div>
            </div>
            <div class="card-dashboard">
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-journal-check" style="font-size:3rem;"></i>
                    <p class="mt-3">Journal d'audit en cours de déploiement.</p>
                </div>
            </div>
        </div>
    </GelLayout>
</template>
<script setup>
/* ═══════════════════════════════════════════════════════
   Admin / AuditLogs — Journal d'audit pour la
   consultation des logs et de l'activité système.
   ═══════════════════════════════════════════════════════ */

import GelLayout from '../../../Layouts/GelLayout.vue';
</script>
