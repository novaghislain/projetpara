<template>
    <GelLayout>
        <div class="dae-dashboard">
            <!-- ═══ LOADING ═══ -->
            <div v-if="state === 'loading'" class="dae-state">
                <div class="dae-spinner"></div>
                <p class="dae-state-text">Chargement du tableau de bord...</p>
            </div>

            <!-- ═══ ERROR ═══ -->
            <div v-else-if="state === 'error'" class="dae-state">
                <i class="bi bi-exclamation-triangle dae-state-icon"></i>
                <p class="dae-state-text">{{ errorMsg }}</p>
                <button class="dae-retry-btn" @click="fetchStats">
                    <i class="bi bi-arrow-clockwise me-1"></i>Réessayer
                </button>
            </div>

            <!-- ═══ CONTENT ═══ -->
            <div v-else class="dae-content">
                <!-- ── Header ── -->
                <div class="dae-header">
                    <div>
                        <h1 class="dae-title">
                            <i class="bi bi-building me-2 text-primary"></i>Secrétariat DAE
                        </h1>
                        <p class="dae-subtitle">
                            <i class="bi bi-calendar3 me-1"></i>{{ today }}
                            <span class="mx-2">|</span>
                            <i class="bi bi-person-circle me-1"></i>Vue d'ensemble du secrétariat
                        </p>
                    </div>
                    <div class="dae-header-actions">
                        <a href="/dae/rapports" class="dae-btn dae-btn-outline">
                            <i class="bi bi-file-earmark-bar-graph me-1"></i>Rapports
                        </a>
                        <a href="/dae/courriers/create" class="dae-btn dae-btn-primary">
                            <i class="bi bi-plus-lg me-1"></i>Nouveau courrier
                        </a>
                    </div>
                </div>

                <!-- ── Stat Cards ── -->
                <div class="dae-stats-grid">
                    <div v-for="card in statCards" :key="card.key" class="dae-stat-col">
                        <DaeStatCard
                            :icon="card.icon"
                            :label="card.label"
                            :value="stats[card.key] ?? 0"
                            :color="card.color"
                        />
                    </div>
                </div>

                <!-- ── Second Row ── -->
                <div class="dae-cols">
                    <!-- Events -->
                    <div class="dae-col">
                        <div class="dae-card">
                            <div class="dae-card-header">
                                <span class="dae-card-title">
                                    <i class="bi bi-calendar-week text-primary me-2"></i>Événements du jour
                                </span>
                                <a href="/dae/agenda" class="dae-card-link">
                                    Voir tout <i class="bi bi-chevron-right"></i>
                                </a>
                            </div>
                            <div class="dae-card-body">
                                <DaeEventList :events="todayEvents" />
                            </div>
                        </div>
                    </div>

                    <!-- Activity -->
                    <div class="dae-col">
                        <div class="dae-card">
                            <div class="dae-card-header">
                                <span class="dae-card-title">
                                    <i class="bi bi-activity text-primary me-2"></i>Activité récente
                                </span>
                                <span class="dae-card-badge">15 dernières actions</span>
                            </div>
                            <div class="dae-card-body">
                                <DaeActivityFeed :activities="activities" />
                            </div>
                        </div>
                    </div>

                    <!-- Alerts + Assistant -->
                    <div class="dae-col">
                        <div class="dae-card dae-card--mb">
                            <div class="dae-card-header">
                                <span class="dae-card-title">
                                    <i class="bi bi-bell text-warning me-2"></i>Alertes
                                </span>
                            </div>
                            <div class="dae-card-body">
                                <DaeAlertBanner :alerts="alerts" />
                            </div>
                        </div>

                        <div class="dae-card">
                            <div class="dae-card-header">
                                <span class="dae-card-title">
                                    <i class="bi bi-robot text-primary me-2"></i>Assistant Secrétariat
                                </span>
                            </div>
                            <div class="dae-card-body dae-card-body--links">
                                <a
                                    v-for="link in assistantLinks"
                                    :key="link.href"
                                    :href="link.href"
                                    class="dae-link-item"
                                >
                                    <span class="dae-link-icon" :style="{ background: link.bg }">
                                        <i :class="`bi ${link.icon}`" :style="{ color: link.color }"></i>
                                    </span>
                                    <span class="dae-link-label">{{ link.label }}</span>
                                    <i class="bi bi-chevron-right dae-link-arrow"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import GelLayout from '../../../Layouts/GelLayout.vue'
import DaeStatCard from '../../../Components/Dae/DaeStatCard.vue'
import DaeActivityFeed from '../../../Components/Dae/DaeActivityFeed.vue'
import DaeEventList from '../../../Components/Dae/DaeEventList.vue'
import DaeAlertBanner from '../../../Components/Dae/DaeAlertBanner.vue'

/* ══════════════════════════════════════════
   State
   ══════════════════════════════════════════ */
const state = ref('loading')  // 'loading' | 'error' | 'loaded'
const errorMsg = ref('')
const stats = ref({
    courriers: 0,
    courriers_urgents: 0,
    emails: 0,
    emails_non_lus: 0,
    evenements: 0,
    contrats: 0,
    contrats_actifs: 0,
    documents: 0,
    taches: 0,
    taches_terminees: 0,
})
const activities = ref([])
const todayEvents = ref([])
const alerts = ref([])

/* ══════════════════════════════════════════
   Data-driven configs
   ══════════════════════════════════════════ */
const statCards = [
    { key: 'courriers',         icon: 'bi-envelope',              label: 'Courriers',      color: 'primary' },
    { key: 'courriers_urgents', icon: 'bi-exclamation-triangle',  label: 'Urgents',        color: 'danger' },
    { key: 'emails',            icon: 'bi-envelope-paper',        label: 'Emails',         color: 'info' },
    { key: 'emails_non_lus',    icon: 'bi-envelope-open',         label: 'Non lus',        color: 'warning' },
    { key: 'evenements',        icon: 'bi-calendar-event',        label: 'Événements',     color: 'success' },
    { key: 'contrats_actifs',   icon: 'bi-file-earmark-text',     label: 'Contrats actifs',color: 'primary' },
    { key: 'documents',         icon: 'bi-folder',                label: 'Documents',      color: 'secondary' },
    { key: 'taches',            icon: 'bi-check2-square',         label: 'Tâches en cours',color: 'info' },
]

const assistantLinks = [
    { href: '/dae/modeles',    icon: 'bi-file-text',              label: 'Modèles de courriers',  bg: 'rgba(255,121,0,0.1)',  color: '#ff7900' },
    { href: '/dae/rapports',   icon: 'bi-bar-chart-line',         label: 'Générer un rapport',     bg: 'rgba(0,0,0,0.04)',     color: '#6b7280' },
    { href: '/dae/conformite', icon: 'bi-shield-check',           label: 'Tableau de conformité',  bg: 'rgba(16,185,129,0.1)', color: '#10b981' },
    { href: '/dae/taches',     icon: 'bi-list-task',              label: 'Gestion des tâches',     bg: 'rgba(59,130,246,0.08)',color: '#3b82f6' },
    { href: '/dae/emails',     icon: 'bi-envelope-paper-heart',   label: 'Boîte email',            bg: 'rgba(245,158,11,0.1)', color: '#f59e0b' },
]

/* ══════════════════════════════════════════
   Helpers
   ══════════════════════════════════════════ */
const today = computed(() => {
    const d = new Date()
    return d.toLocaleDateString('fr-FR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
})

/* ══════════════════════════════════════════
   Fetch
   ══════════════════════════════════════════ */
async function fetchStats() {
    state.value = 'loading'
    errorMsg.value = ''
    try {
        const response = await window.axios.get('/dae/api/stats')
        const data = response.data
        if (data.stats) {
            stats.value = { ...stats.value, ...data.stats }
        }
        activities.value = data.activite || []
        todayEvents.value = data.evenements || []
        alerts.value = data.alertes || []
        state.value = 'loaded'
    } catch (err) {
        console.error('Erreur DAE stats:', err)
        errorMsg.value = err.response?.data?.message || err.message || 'Erreur de chargement des données'
        state.value = 'error'
    }
}

onMounted(fetchStats)
</script>

<style scoped>
/* ═══════════════════════════════════════════════════════
   DAE Dashboard — Refactored
   ═══════════════════════════════════════════════════════ */

.dae-dashboard {
    padding: 1.5rem 1.75rem;
    min-height: 80vh;
}

/* ─── State pages (loading / error) ─── */
.dae-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 120px 20px;
    gap: 0.75rem;
}
.dae-spinner {
    width: 48px;
    height: 48px;
    border: 4px solid rgba(255, 121, 0, 0.1);
    border-top-color: #ff7900;
    border-radius: 50%;
    animation: dae-spin 0.7s linear infinite;
}
@keyframes dae-spin {
    to { transform: rotate(360deg); }
}
.dae-state-icon {
    font-size: 2.5rem;
    color: #ef4444;
    opacity: 0.6;
}
.dae-state-text {
    color: var(--bs-secondary-color, #6c757d);
    font-size: 0.9rem;
    font-weight: 500;
    text-align: center;
}
.dae-retry-btn {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1.25rem;
    border: 1px solid #ff7900;
    border-radius: 8px;
    background: #fff;
    color: #ff7900;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s ease;
}
.dae-retry-btn:hover {
    background: rgba(255, 121, 0, 0.08);
}

/* ─── Header ─── */
.dae-header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
}
.dae-title {
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0 0 0.15rem;
    color: var(--bs-body-color, #212529);
}
.dae-subtitle {
    font-size: 0.82rem;
    color: var(--bs-secondary-color, #6c757d);
    margin: 0;
}
.dae-header-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.dae-btn {
    display: inline-flex;
    align-items: center;
    padding: 0.45rem 1rem;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.15s ease;
    white-space: nowrap;
}
.dae-btn-primary {
    background: #ff7900;
    color: #fff !important;
    border: 1px solid #ff7900;
}
.dae-btn-primary:hover {
    background: #e66c00;
    border-color: #e66c00;
}
.dae-btn-outline {
    background: transparent;
    color: #ff7900;
    border: 1px solid #ff7900;
}
.dae-btn-outline:hover {
    background: rgba(255, 121, 0, 0.08);
}

/* ─── Stats grid: 8 cards ─── */
.dae-stats-grid {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}
@media (max-width: 1200px) {
    .dae-stats-grid { grid-template-columns: repeat(4, 1fr); }
}
@media (max-width: 576px) {
    .dae-stats-grid { grid-template-columns: repeat(2, 1fr); }
}
.dae-stat-col {
    min-width: 0;
}

/* ─── Columns row (3 cols) ─── */
.dae-cols {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 1rem;
}
@media (max-width: 992px) {
    .dae-cols { grid-template-columns: 1fr; }
}
.dae-col {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* ─── Card ─── */
.dae-card {
    background: #fff;
    border: 1px solid var(--bs-border-color, #e9ecef);
    border-radius: 10px;
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.dae-card--mb {
    height: auto;
}
.dae-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.85rem 1rem;
    border-bottom: 1px solid var(--bs-border-color, #e9ecef);
    background: transparent;
}
.dae-card-title {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--bs-body-color, #212529);
    display: inline-flex;
    align-items: center;
}
.dae-card-link {
    font-size: 0.75rem;
    color: #ff7900;
    text-decoration: none;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
}
.dae-card-link:hover {
    text-decoration: underline;
}
.dae-card-badge {
    font-size: 0.7rem;
    color: var(--bs-secondary-color, #6c757d);
}
.dae-card-body {
    padding: 1rem;
    flex: 1;
}
.dae-card-body--links {
    padding: 0.5rem;
}

/* ─── Assistant links ─── */
.dae-link-item {
    display: flex;
    align-items: center;
    padding: 0.55rem 0.65rem;
    border-radius: 8px;
    text-decoration: none;
    color: var(--bs-body-color, #212529);
    transition: background 0.15s ease;
    gap: 0.65rem;
}
.dae-link-item:hover {
    background: #f8f9fa;
}
.dae-link-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 0.95rem;
}
.dae-link-label {
    font-size: 0.8rem;
    font-weight: 500;
    flex: 1;
    min-width: 0;
}
.dae-link-arrow {
    font-size: 0.7rem;
    color: var(--bs-secondary-color, #adb5bd);
    flex-shrink: 0;
}

/* ─── Print ─── */
@media print {
    .dae-dashboard { padding: 0.5in; }
    .dae-header-actions { display: none; }
    .dae-card { break-inside: avoid; border: 1px solid #dee2e6; }
}
</style>
