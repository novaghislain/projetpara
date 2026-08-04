<template>
  <div class="customer-ia-dashboard">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h5 class="mb-1">Analyse IA : {{ client?.raison_sociale }}</h5>
        <p class="text-muted mb-0">Analyse complète du client par l'agent Customer AI</p>
      </div>
      <button class="btn btn-outline-primary" @click="refreshAnalysis">
        <i class="bi bi-arrow-clockwise"></i> Actualiser
      </button>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Analyse en cours...</span>
      </div>
      <p class="mt-2 text-muted">L'IA analyse ce client...</p>
    </div>

    <div v-else-if="error" class="alert alert-danger">
      <i class="bi bi-exclamation-triangle"></i> {{ error }}
    </div>

    <template v-else>
      <!-- Row 1 : Score + Churn -->
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <h6 class="card-title text-muted mb-3">
                <i class="bi bi-graph-up-arrow text-primary"></i> Score Lead
              </h6>
              <div class="text-center">
                <div class="display-4 fw-bold" :class="scoreClass">{{ analysis?.scoring?.score }}/100</div>
                <span class="badge rounded-pill mt-2" :class="levelBadge">
                  {{ analysis?.scoring?.level }}
                </span>
              </div>
              <hr>
              <div v-for="factor in analysis?.scoring?.factors" :key="factor.name" class="mb-2">
                <div class="d-flex justify-content-between small">
                  <span>{{ factor.name }}</span>
                  <span class="text-muted">{{ factor.detail }}</span>
                </div>
                <div class="progress" style="height: 4px;">
                  <div class="progress-bar" :style="{ width: (factor.points/factor.max*100) + '%' }"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <h6 class="card-title text-muted mb-3">
                <i class="bi bi-exclamation-triangle" :class="churnColor"></i> Risque de Perte
              </h6>
              <div class="text-center">
                <div class="display-4 fw-bold" :class="churnColor">{{ analysis?.churn_risk?.probability }}</div>
                <div class="mt-2">
                  <span class="badge rounded-pill" :class="churnLevelBadge">
                    {{ analysis?.churn_risk?.risk_level }}
                  </span>
                </div>
              </div>
              <hr>
              <p class="small mb-0" :class="churnTextColor">
                <i class="bi bi-chat-quote"></i> {{ analysis?.churn_risk?.recommendation }}
              </p>
              <div v-if="analysis?.churn_risk?.signals?.length" class="mt-2">
                <div v-for="signal in analysis.churn_risk.signals" :key="signal.signal" class="d-flex justify-content-between small text-muted">
                  <span>{{ signal.signal }}</span>
                  <span>+{{ signal.weight }} pts</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Row 2 : Actions de relance + Cross-sell -->
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0">
              <h6 class="mb-0"><i class="bi bi-bell text-warning"></i> Actions de relance</h6>
            </div>
            <div class="card-body pt-0">
              <div v-if="!analysis?.follow_up_actions?.length" class="text-muted small text-center py-3">
                Aucune action de relance suggérée.
              </div>
              <div v-for="action in analysis?.follow_up_actions" :key="action.title" class="action-item p-3 mb-2 rounded border">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <strong class="small">{{ action.title }}</strong>
                    <span class="badge bg-warning text-dark ms-2">{{ action.priority }}</span>
                    <p class="small text-muted mt-1 mb-0">{{ action.message }}</p>
                  </div>
                  <span class="badge bg-light text-dark">{{ action.channel }}</span>
                </div>
                <div class="mt-2 bg-light p-2 rounded small">
                  <em>{{ action.script }}</em>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0">
              <h6 class="mb-0"><i class="bi bi-cart-plus text-success"></i> Opportunités Cross-Sell</h6>
            </div>
            <div class="card-body pt-0">
              <div v-if="!analysis?.cross_sell?.length" class="text-muted small text-center py-3">
                Aucune opportunité détectée.
              </div>
              <div v-for="opp in analysis?.cross_sell" :key="opp.title" class="opportunity-item p-3 mb-2 rounded border">
                <div class="d-flex justify-content-between">
                  <div>
                    <strong class="small">{{ opp.title }}</strong>
                    <span class="badge" :class="opp.priority === 'haute' ? 'bg-success' : 'bg-info'">{{ opp.priority }}</span>
                  </div>
                  <span class="fw-bold text-success small">{{ opp.estimated_value?.toLocaleString() }} FCFA</span>
                </div>
                <p class="small text-muted mt-1 mb-1">{{ opp.message }}</p>
                <div class="small text-success"><i class="bi bi-star"></i> {{ opp.benefit }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

/*
 * Composant : CustomerAnalysis
 * Role : Analyse IA complete d'un client : scoring lead, risque de perte
 *        (churn), actions de relance recommandees et opportunites cross-sell.
 *        Les donnees sont generees par l'agent Customer AI.
 */
<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

/* Proprietes : identifiant du client a analyser */
const props = defineProps({
  clientId: { type: Number, required: true }
})

/* Donnees reactives */
const loading = ref(true)
const error = ref(null)
const analysis = ref(null)
const client = computed(() => analysis.value?.client)

/* Couleur du score lead selon le seuil */
const scoreClass = computed(() => {
  if (!analysis.value?.scoring?.score) return 'text-muted'
  const s = analysis.value.scoring.score
  return s >= 80 ? 'text-success' : s >= 50 ? 'text-warning' : 'text-danger'
})

/* Badge du niveau du lead */
const levelBadge = computed(() => {
  const l = analysis.value?.scoring?.level
  return l === 'chaud' ? 'bg-success' : l === 'tiède' ? 'bg-warning text-dark' : 'bg-secondary'
})

/* Couleur du risque de perte */
const churnColor = computed(() => {
  const l = analysis.value?.churn_risk?.risk_level
  return l === 'critique' ? 'text-danger' : l === 'élevé' ? 'text-warning' : 'text-success'
})

/* Badge du niveau de risque */
const churnLevelBadge = computed(() => {
  const l = analysis.value?.churn_risk?.risk_level
  return l === 'critique' ? 'bg-danger' : l === 'élevé' ? 'bg-warning text-dark' : 'bg-success'
})

/* Couleur du texte de recommandation */
const churnTextColor = computed(() => {
  const l = analysis.value?.churn_risk?.risk_level
  return l === 'critique' ? 'text-danger' : l === 'élevé' ? 'text-warning' : 'text-success'
})

/* Recupere l'analyse complete du client depuis l'API */
async function fetchAnalysis() {
  loading.value = true
  error.value = null
  try {
    const { data } = await axios.get(`/api/ia/customer/full-analysis/${props.clientId}`)
    analysis.value = data
  } catch (e) {
    error.value = e.response?.data?.message || "Erreur lors de l'analyse"
  } finally {
    loading.value = false
  }
}

/* Relance la requete d'analyse */
function refreshAnalysis() {
  fetchAnalysis()
}

onMounted(fetchAnalysis)
</script>
