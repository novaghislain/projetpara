<template>
    <GelLayout pageTitle="Flux d'Intelligence Artificielle">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-1 text-gray-800">
                        <i class="bi bi-robot text-primary me-2"></i>Flux IA (AiFeed)
                    </h2>
                    <p class="text-muted mb-0">Suggestions et alertes générées par les agents intelligents</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-8">
                    <!-- On utilise le composant AiFeed existant -->
                    <AiFeed :limit="50" :polling-interval="15000" />
                </div>
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <h5 class="card-title text-primary"><i class="bi bi-info-circle me-2"></i>Le saviez-vous ?</h5>
                            <p class="card-text text-muted small mt-3">
                                Les agents intelligents (comme l'Agent Fiscal Bénin ou l'Agent Rapprochement) analysent vos données en arrière-plan.
                                Dès qu'une anomalie est détectée ou qu'une optimisation est possible, une suggestion est publiée ici.
                            </p>
                            <p class="card-text text-muted small">
                                <strong>Règle d'Or :</strong> L'IA propose, l'expert dispose. Aucune écriture ou validation n'est effectuée sans votre accord explicite.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import GelLayout from '../../../Layouts/GelLayout.vue';
import AiFeed from '../../../Components/AiFeed.vue';
</script>
