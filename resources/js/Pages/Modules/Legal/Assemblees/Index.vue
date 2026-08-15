<template>
  <div>
    <div class="sec-page-header">
      <h1 class="sec-page-title">Assemblées</h1>
    </div>
    <div class="sec-card">
      <div class="sec-card-body p-0">
        <table class="sec-table">
          <thead>
            <tr>
              <th>Type</th>
              <th>Date</th>
              <th>Lieu</th>
              <th>Statut</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="assembly in assemblies.data" :key="assembly.id">
              <td>{{ assembly.type }}</td>
              <td>{{ assembly.date_assemblee }}</td>
              <td>{{ assembly.lieu }}</td>
              <td><span class="sec-badge sec-badge-warning">{{ assembly.statut }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  assemblies: Object
});
</script>