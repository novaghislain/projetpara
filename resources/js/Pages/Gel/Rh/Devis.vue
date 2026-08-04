<template>
    <!-- Layout principal de l'interface GEL -->
    <GelLayout>
        <div class="container-fluid py-4">
            <!-- En-tete de la page : titre et sous-titre -->
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h1 class="page-title">Devis</h1>
                    <p class="page-subtitle">Gestion des devis clients.</p>
                </div>
                <!-- Bouton d'action pour creer un nouveau devis -->
                <button class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nouveau devis</button>
            </div>
            <!-- Contenu principal : message temporaire de deploiement -->
            <div class="card-dashboard">
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-file-earmark-text" style="font-size:3rem;"></i>
                    <p class="mt-3">Module de devis en cours de déploiement.</p>
                </div>
            </div>
        </div>
    </GelLayout>
</template>
<script setup>
/* ============================================================
 * Composant : Devis
 * Module GEL - Gestion des devis clients
 * Page d'accueil du module de devis (en cours de deploiement)
 * Permet de visualiser et creer des devis pour les clients
 * ============================================================ */

// Layout principal de l'interface GEL
import GelLayout from '../../../Layouts/GelLayout.vue';
</script>
