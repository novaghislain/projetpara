<template>
    <!-- Layout principal de l'interface GEL -->
    <GelLayout>
        <div class="container-fluid py-4">
            <!-- En-tete de la page : titre et sous-titre -->
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h1 class="page-title">Employés</h1>
                    <p class="page-subtitle">Gestion des employés et collaborateurs.</p>
                </div>
                <!-- Bouton d'action pour ajouter un employe -->
                <button class="btn btn-primary"><i class="bi bi-plus-lg"></i> Ajouter un employé</button>
            </div>
            <!-- Contenu principal : message temporaire de deploiement -->
            <div class="card-dashboard">
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-people" style="font-size:3rem;"></i>
                    <p class="mt-3">Gestion des employés en cours de déploiement.</p>
                </div>
            </div>
        </div>
    </GelLayout>
</template>
<script setup>
/* ============================================================
 * Composant : Employes
 * Module GEL - Gestion des employes et collaborateurs
 * Page d'accueil du module RH employes (en cours de deploiement)
 * Affiche la liste des employes avec option d'ajout
 * ============================================================ */

// Layout principal de l'interface GEL
import GelLayout from '../../../Layouts/GelLayout.vue';
</script>
