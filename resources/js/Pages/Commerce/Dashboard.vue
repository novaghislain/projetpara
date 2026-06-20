<script setup>
import { ref, onMounted } from 'vue'
import GelLayout from '../../Layouts/GelLayout.vue'
import { authStore } from '../../stores/auth'

/* ══════════════════════════════════════════
   State
   ══════════════════════════════════════════ */
const state = ref('loading')
const errorMsg = ref('')
const data = ref(null)

const fmtNum = (n) => Number(n || 0).toLocaleString('fr-FR')
const fmtCurr = (n) => Number(n || 0).toLocaleString('fr-FR', { style: 'currency', currency: 'XOF', minimumFractionDigits: 0, maximumFractionDigits: 0 })

const barHeight = (val, allVals) => {
  const max = Math.max(...(allVals || []).map((r) => r.total || r.amount || 0), 1)
  return Math.max(4, Math.round((val / max) * 120)) + 'px'
}

const paymentLabels = { especes: 'Espèces', momo: 'MTN MoMo', moov: 'Moov Money', carte: 'Carte', autre: 'Autre' }

const fetchStats = async () => {
  state.value = 'loading'
  try {
    const res = await window.axios.get('/api/commerce/dashboard')
    data.value = res.data
    state.value = 'loaded'
  } catch (e) {
    errorMsg.value = e.response?.data?.message || e.message || 'Erreur de chargement'
    state.value = 'error'
  }
}

onMounted(fetchStats)
</script>

<template>
  <GelLayout page-title="Commerce / POS">
    <template v-if="state === 'loading'">
      <div class="d-flex justify-content-center py-5">
        <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
      </div>
    </template>

    <template v-else-if="state === 'error'">
      <div class="alert alert-danger">{{ errorMsg }}</div>
    </template>

    <template v-else>
      <!-- KPIs -->
      <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
          <div class="card card-dashboard p-3 text-center">
            <div class="fs-4 fw-bold text-primary">{{ fmtCurr(data.revenue.today) }}</div>
            <div class="small text-muted">CA Aujourd'hui</div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="card card-dashboard p-3 text-center">
            <div class="fs-4 fw-bold text-success">{{ fmtCurr(data.revenue.month) }}</div>
            <div class="small text-muted">CA Ce mois</div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="card card-dashboard p-3 text-center">
            <div class="fs-4 fw-bold text-info">{{ data.transactions.month }}</div>
            <div class="small text-muted">Ventes ce mois</div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="card card-dashboard p-3 text-center">
            <div class="fs-4 fw-bold text-warning">{{ fmtCurr(data.avg_ticket) }}</div>
            <div class="small text-muted">Ticket moyen</div>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
          <div class="card card-dashboard p-3 text-center">
            <div class="fs-4 fw-bold text-secondary">{{ data.total_products }}</div>
            <div class="small text-muted">Produits</div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="card card-dashboard p-3 text-center">
            <div class="fs-4 fw-bold" :class="data.stock_alerts > 0 ? 'text-danger' : 'text-success'">{{ data.stock_alerts }}</div>
            <div class="small text-muted">Alertes stock</div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="card card-dashboard p-3 text-center">
            <div class="fs-4 fw-bold" :class="data.stock_critical > 0 ? 'text-danger' : 'text-success'">{{ data.stock_critical }}</div>
            <div class="small text-muted">En rupture</div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="card card-dashboard p-3 text-center">
            <div class="fs-4 fw-bold text-info">{{ data.transactions.today }}</div>
            <div class="small text-muted">Ventes aujourd'hui</div>
          </div>
        </div>
      </div>

      <!-- Graphique journalier -->
      <div class="card card-dashboard p-3 mb-4">
        <h6 class="fw-bold mb-3">Évolution du CA (30 jours)</h6>
        <div v-if="data.daily_revenue?.length" class="d-flex align-items-end gap-1" style="height:140px;">
          <div v-for="(d, i) in data.daily_revenue" :key="i" class="flex-grow-1 d-flex flex-column align-items-center">
            <div class="rounded-1 w-100" :style="{ height: barHeight(d.total, data.daily_revenue), backgroundColor: '#ff7900', minHeight: '4px' }" :title="fmtCurr(d.total)"></div>
            <span class="small text-muted mt-1" style="font-size:9px; writing-mode: vertical-lr;" v-if="i % 5 === 0">{{ d.date?.slice(5) }}</span>
          </div>
        </div>
        <div v-else class="text-muted small py-3 text-center">Aucune donnée pour les 30 derniers jours.</div>
      </div>

      <div class="row g-3">
        <!-- Top produits -->
        <div class="col-md-6">
          <div class="card card-dashboard p-3">
            <h6 class="fw-bold mb-3">Top 10 produits</h6>
            <div v-if="data.top_products?.length">
              <div v-for="(p, i) in data.top_products" :key="i" class="d-flex justify-content-between align-items-center py-1 border-bottom">
                <span class="small"><span class="text-muted me-2">#{{ i + 1 }}</span>{{ p.name }}</span>
                <span class="small fw-medium">{{ p.total_qty }} vendus</span>
              </div>
            </div>
            <div v-else class="text-muted small py-2">Aucune vente ce mois.</div>
          </div>
        </div>

        <!-- Ventes par paiement -->
        <div class="col-md-6">
          <div class="card card-dashboard p-3">
            <h6 class="fw-bold mb-3">Ventes par mode de paiement</h6>
            <div v-if="data.sales_by_payment?.length">
              <div v-for="(p, i) in data.sales_by_payment" :key="i" class="d-flex justify-content-between align-items-center py-1 border-bottom">
                <span class="small"><span class="badge bg-secondary me-2">{{ paymentLabels[p.payment_method] || p.payment_method }}</span></span>
                <span class="small fw-medium">{{ fmtCurr(p.total) }}</span>
              </div>
            </div>
            <div v-else class="text-muted small py-2">Aucune donnée.</div>
          </div>
        </div>
      </div>

      <!-- Actions rapides -->
      <div class="d-flex flex-wrap gap-2 mt-4">
        <a href="/commerce/pos" class="btn btn-primary btn-sm"><i class="bi-cash-register me-1"></i>Ouvrir la caisse</a>
        <a href="/commerce/products" class="btn btn-outline-primary btn-sm"><i class="bi-box-seam me-1"></i>Produits</a>
        <a href="/commerce/inventory" class="btn btn-outline-secondary btn-sm"><i class="bi-clipboard-data me-1"></i>Inventaire</a>
      </div>
    </template>
  </GelLayout>
</template>
