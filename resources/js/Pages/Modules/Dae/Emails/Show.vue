<template>
    <GelLayout>
        <div class="container-fluid py-4">
            <div class="d-flex align-items-center gap-3 mb-4">
                <a href="/dae/emails" class="btn btn-outline-secondary btn-sm" title="Retour à la boite email">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h4 class="mb-0 fw-bold"><i class="bi bi-chat-dots me-2 text-primary"></i>Email</h4>
                    <p class="text-muted small mb-0 mt-1">Détail de l'email</p>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-envelope-open" style="font-size:2rem;"></i>
                    <p class="mt-2">Affichage de l'email à implémenter</p>
                </div>
            </div>
        </div>
    </GelLayout>
</template>
<script>
import GelLayout from '../../../../Layouts/GelLayout.vue';
export default { components: { GelLayout } };
</script>
