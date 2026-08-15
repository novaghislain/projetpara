<template>
  <div class="legal-dashboard">
    <div class="sec-page-header">
      <h1 class="sec-page-title">Tableau de bord Juridique</h1>
      <div class="sec-page-subtitle">Vue d'ensemble de vos activités légales</div>
    </div>
    
    <div class="sec-kpi-grid">
      <div class="sec-kpi-card">
        <div class="sec-kpi-label">Dossiers Actifs</div>
        <div class="sec-kpi-value">{{ stats.dossiers_actifs }}</div>
      </div>
      <div class="sec-kpi-card">
        <div class="sec-kpi-label">Contrats Actifs</div>
        <div class="sec-kpi-value">{{ stats.contrats_actifs }}</div>
      </div>
      <div class="sec-kpi-card">
        <div class="sec-kpi-label">Assemblées Planifiées</div>
        <div class="sec-kpi-value">{{ stats.assemblees_planifiees }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  stats: Object,
  recent_contracts: Array
});
</script>