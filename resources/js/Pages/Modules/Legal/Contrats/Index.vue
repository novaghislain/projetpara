<template>
  <div>
    <div class="sec-page-header">
      <h1 class="sec-page-title">Contrats</h1>
    </div>
    <div class="sec-card">
      <div class="sec-card-body p-0">
        <table class="sec-table">
          <thead>
            <tr>
              <th>Titre</th>
              <th>Type</th>
              <th>Date Début</th>
              <th>Statut</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="contract in contracts.data" :key="contract.id">
              <td>{{ contract.titre }}</td>
              <td>{{ contract.type }}</td>
              <td>{{ contract.date_debut }}</td>
              <td><span class="sec-badge sec-badge-success">{{ contract.statut }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  contracts: Object
});
</script>