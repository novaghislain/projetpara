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
/*
 * Composant : DaeEmailsShow
 * Role : Page de detail d'un email dans le module DAE.
 * Affiche les informations de l'email avec retour a la boite email.
 * Props : aucune (lecture depuis l'URL et l'API)
 * Evenements : aucun
 * Note : Composant minimal utilisant GelLayout pour l'affichage.
 * Le contenu detaille est charge via une modale dans le composant Index.
 * Ce composant sert principalement de page de transition/affichage statique.
 */
import GelLayout from '../../../../Layouts/GelLayout.vue'; /* Layout principal GEL avec menu lateral et header */
export default {
    components: { GelLayout }, /* Enregistrement du layout pour utilisation dans le template */
};
</script>
