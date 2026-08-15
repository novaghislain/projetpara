<template>
  <div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0 text-gray-800">
            <i class="bi bi-people text-primary me-2"></i>Tableau de bord RH
        </h2>
        <div>
            <button class="btn btn-outline-primary me-2"><i class="bi bi-file-earmark-bar-graph me-2"></i>Rapport Mensuel</button>
            <button class="btn btn-primary"><i class="bi bi-person-plus me-2"></i>Nouvel Employé</button>
        </div>
    </div>

    <!-- KPIs -->
    <div class="row g-4 mb-4">
      <div class="col-md-4">
        <div class="card border-0 shadow-sm border-start border-primary border-4 h-100">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Employés Actifs</div>
                <div class="h3 mb-0 font-weight-bold text-gray-800">{{ stats.employes_actifs || 0 }}</div>
              </div>
              <div class="text-gray-300">
                <i class="bi bi-person-check fs-1 text-primary opacity-50"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card border-0 shadow-sm border-start border-warning border-4 h-100">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Congés en attente</div>
                <div class="h3 mb-0 font-weight-bold text-gray-800">{{ stats.conges_en_attente || 0 }}</div>
              </div>
              <div class="text-gray-300">
                <i class="bi bi-calendar-event fs-1 text-warning opacity-50"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card border-0 shadow-sm border-start border-success border-4 h-100">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Paies à valider</div>
                <div class="h3 mb-0 font-weight-bold text-gray-800">{{ stats.paies_a_valider || 0 }}</div>
              </div>
              <div class="text-gray-300">
                <i class="bi bi-cash-coin fs-1 text-success opacity-50"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Contenu Additionnel (Widgets) -->
    <div class="row g-4">
        <!-- Raccourcis Rapides -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h6 class="font-weight-bold text-primary mb-0"><i class="bi bi-lightning-charge me-2"></i>Actions Rapides</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/rh/employees" class="btn btn-light text-start py-3 shadow-sm-hover border"><i class="bi bi-people me-3 text-primary"></i>Gérer les employés</a>
                        <a href="/rh/leaves" class="btn btn-light text-start py-3 shadow-sm-hover border"><i class="bi bi-airplane me-3 text-warning"></i>Valider les congés</a>
                        <a href="/rh/payroll" class="btn btn-light text-start py-3 shadow-sm-hover border"><i class="bi bi-receipt me-3 text-success"></i>Générer les fiches de paie</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Prochains congés -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h6 class="font-weight-bold text-warning mb-0"><i class="bi bi-calendar-week me-2"></i>Congés à venir (30 jours)</h6>
                </div>
                <div class="card-body">
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-cup-hot display-4 d-block mb-3 opacity-25"></i>
                        Aucun congé prévu prochainement.
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
    stats: {
        type: Object,
        default: () => ({
            employes_actifs: 0,
            conges_en_attente: 0,
            paies_a_valider: 0
        })
    }
});
</script>

<style scoped>
.shadow-sm-hover:hover {
    box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important;
    background-color: #f8f9fa;
}
.text-xs {
    font-size: .8rem;
}
</style>