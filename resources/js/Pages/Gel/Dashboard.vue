<script setup>
import { ref, computed, onMounted } from 'vue'
import GelLayout from '../../Layouts/GelLayout.vue'
import { authStore } from '../../stores/auth'

/* ══════════════════════════════════════════
   State
   ══════════════════════════════════════════ */
const state = ref('loading')  // 'loading' | 'error' | 'loaded'
const errorMsg = ref('')
const stats = ref(null)

/* ══════════════════════════════════════════
   Helpers
   ══════════════════════════════════════════ */
const fmtNum = (n) => Number(n || 0).toLocaleString('fr-FR')

const statusLabel = (s) => ({
    en_attente: 'En attente',
    en_cours: 'En cours',
    a_faire: 'À faire',
    terminee: 'Terminée',
    annulee: 'Annulée',
}[s] || s || '—')

const today = computed(() =>
    new Date().toLocaleDateString('fr-FR', {
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
    })
)

const barHeight = (val, allVals) => {
    const max = Math.max(...allVals.map((r) => r.total || 0), 1)
    return Math.max(4, Math.round((val / max) * 130)) + 'px'
}

/* ══════════════════════════════════════════
   Data-driven configs
   ══════════════════════════════════════════ */
const headerBadges = [
    { key: 'total_clients', label: 'Clients',    color: 'primary' },
    { key: 'total_missions',label: 'Missions',   color: 'primary' },
    { key: 'pending_missions',label: 'En attente', color: 'warning' },
    { key: 'total_poles',   label: 'Pôles',      color: 'primary' },
]

const kpiCards = [
    {
        key: 'total_clients',
        icon: 'bi-building',
        label: 'Total Clients',
        cardBg: 'rgba(255,121,0,0.1)',
        cardColor: '#ff7900',
    },
    {
        key: 'active_clients',
        icon: 'bi-person-check',
        label: 'Clients Actifs',
        cardBg: 'rgba(21,101,192,0.1)',
        cardColor: '#1565c0',
    },
    {
        key: 'completed_missions',
        icon: 'bi-check-circle',
        label: 'Missions terminées',
        cardBg: 'rgba(46,125,50,0.1)',
        cardColor: '#2e7d32',
    },
    {
        key: 'pending_missions',
        icon: 'bi-hourglass-split',
        label: 'En attente',
        cardBg: 'rgba(245,127,23,0.1)',
        cardColor: '#f57f17',
    },
]

const quickActions = [
    { href: '/clients/create', icon: 'bi-plus-circle', label: 'Nouveau client', variant: 'primary' },
    { href: '/missions/create', icon: 'bi-check2-square', label: 'Nouvelle mission', variant: 'outline' },
    { href: '/admin/catalogue/orders', icon: 'bi-cart', label: 'Voir commandes', variant: 'outline' },
]

/* ══════════════════════════════════════════
   Fetch
   ══════════════════════════════════════════ */
const fetchStats = async () => {
    state.value = 'loading'
    errorMsg.value = ''
    try {
        const res = await window.axios.get('/api/stats')
        stats.value = res.data
        state.value = 'loaded'
    } catch (err) {
        console.error('Erreur stats GEL:', err)
        errorMsg.value = err.response?.data?.message || err.message || 'Erreur de chargement des données'
        state.value = 'error'
    }
}

onMounted(fetchStats)
</script>

<template>
    <GelLayout page-title="Accueil">
        <!-- ═══ LOADING ═══ -->
        <div v-if="state === 'loading'" class="gel-state">
            <div class="gel-spinner"></div>
            <p class="gel-state-text">Chargement du tableau de bord...</p>
        </div>

        <!-- ═══ ERROR ═══ -->
        <div v-else-if="state === 'error'" class="gel-state">
            <i class="bi bi-exclamation-triangle gel-state-icon"></i>
            <p class="gel-state-text">{{ errorMsg }}</p>
            <button class="gel-retry-btn" @click="fetchStats">
                <i class="bi bi-arrow-clockwise me-1"></i>Réessayer
            </button>
        </div>

        <!-- ═══ CONTENT ═══ -->
        <template v-else-if="stats">
            <!-- ── Header Card ── -->
            <div class="gel-header-card">
                <div class="gel-header">
                    <div>
                        <h1 class="gel-title">GEL Cabinet — Super Administration</h1>
                        <p class="gel-subtitle">
                            <i class="bi bi-person-circle me-1"></i>{{ authStore.user?.name }}
                            <span class="mx-2">|</span>
                            <i class="bi bi-calendar3 me-1"></i>{{ today }}
                        </p>
                    </div>
                    <div class="gel-header-badges">
                        <div v-for="b in headerBadges" :key="b.key" class="gel-header-badge">
                            <div class="gel-header-badge-value" :class="`gel-text--${b.color}`">
                                {{ fmtNum(stats[b.key]) }}
                            </div>
                            <div class="gel-header-badge-label">{{ b.label }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Quick actions ── -->
            <div class="gel-actions">
                <a
                    v-for="act in quickActions"
                    :key="act.href"
                    :href="act.href"
                    class="gel-btn"
                    :class="act.variant === 'primary' ? 'gel-btn--primary' : 'gel-btn--outline'"
                >
                    <i :class="`bi ${act.icon} me-1`"></i>{{ act.label }}
                </a>
            </div>

            <!-- ── KPI Cards ── -->
            <div class="gel-kpi-grid">
                <div v-for="kpi in kpiCards" :key="kpi.key" class="gel-kpi-col">
                    <div class="gel-kpi-card">
                        <div class="gel-kpi-icon" :style="{ background: kpi.cardBg, color: kpi.cardColor }">
                            <i :class="`bi ${kpi.icon}`"></i>
                        </div>
                        <div class="gel-kpi-value">{{ fmtNum(stats[kpi.key]) }}</div>
                        <div class="gel-kpi-label">{{ kpi.label }}</div>
                    </div>
                </div>
            </div>

            <!-- ── Tables row ── -->
            <div class="gel-cols">
                <!-- Recent clients -->
                <div class="gel-col">
                    <div class="gel-card">
                        <div class="gel-card-header">
                            <span class="gel-card-title">
                                <i class="bi bi-building me-2" style="color:#ff7900;"></i>Clients récents
                            </span>
                            <a href="/clients" class="gel-card-link">
                                Voir tout <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        <div class="gel-card-body">
                            <div v-if="!stats.recent_clients?.length" class="gel-empty">
                                <i class="bi bi-inbox"></i>
                                <span>Aucun client récent</span>
                            </div>
                            <table v-else class="gel-table">
                                <thead>
                                    <tr><th>Société</th><th>Email</th><th class="text-center">Statut</th></tr>
                                </thead>
                                <tbody>
                                    <tr v-for="c in stats.recent_clients" :key="c.id">
                                        <td>
                                            <a :href="'/clients/' + c.id" class="gel-table-link">{{ c.company_name }}</a>
                                        </td>
                                        <td class="gel-table-muted">{{ c.email }}</td>
                                        <td class="text-center">
                                            <span class="gel-badge" :class="c.status === 'actif' ? 'gel-badge--success' : 'gel-badge--muted'">
                                                {{ c.status }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent missions -->
                <div class="gel-col">
                    <div class="gel-card">
                        <div class="gel-card-header">
                            <span class="gel-card-title">
                                <i class="bi bi-check2-square me-2" style="color:#ff7900;"></i>Missions récentes
                            </span>
                            <a href="/missions" class="gel-card-link">
                                Voir tout <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        <div class="gel-card-body">
                            <div v-if="!stats.recent_missions?.length" class="gel-empty">
                                <i class="bi bi-inbox"></i>
                                <span>Aucune mission récente</span>
                            </div>
                            <table v-else class="gel-table">
                                <thead>
                                    <tr><th>Titre</th><th>Statut</th><th>Avancement</th></tr>
                                </thead>
                                <tbody>
                                    <tr v-for="m in stats.recent_missions" :key="m.id">
                                        <td>
                                            <div class="gel-table-title">{{ m.title }}</div>
                                            <div class="gel-table-muted">{{ m.client?.company_name || '—' }}</div>
                                        </td>
                                        <td>
                                            <span class="gel-badge" :class="`gel-badge--${m.status}`">{{ statusLabel(m.status) }}</span>
                                        </td>
                                        <td>
                                            <div class="gel-progress-wrap">
                                                <div class="gel-progress-bar">
                                                    <div
                                                        class="gel-progress-fill"
                                                        :class="(m.progress||0) >= 100 ? 'gel-progress-fill--done' : ''"
                                                        :style="{ width: (m.progress||0) + '%' }"
                                                    ></div>
                                                </div>
                                                <span class="gel-progress-text">{{ m.progress || 0 }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Pole distribution + Revenue chart ── -->
            <div class="gel-cols">
                <div class="gel-col gel-col--narrow">
                    <div class="gel-card">
                        <div class="gel-card-header">
                            <span class="gel-card-title">
                                <i class="bi bi-pie-chart me-2" style="color:#ff7900;"></i>Répartition par Pôle
                            </span>
                        </div>
                        <div class="gel-card-body">
                            <div v-if="!stats.pole_distribution?.length" class="gel-empty">
                                <i class="bi bi-bar-chart"></i>
                                <span>Aucune donnée</span>
                            </div>
                            <div v-else class="gel-pole-list">
                                <div v-for="p in stats.pole_distribution" :key="p.name" class="gel-pole-item">
                                    <span class="gel-pole-name">{{ p.name }}</span>
                                    <div class="gel-pole-track">
                                        <div
                                            class="gel-pole-fill"
                                            :style="{ width: p.pourcentage + '%', background: p.color || '#ff7900' }"
                                        ></div>
                                    </div>
                                    <span class="gel-pole-count">{{ p.count }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="gel-col gel-col--wide">
                    <div class="gel-card">
                        <div class="gel-card-header">
                            <span class="gel-card-title">
                                <i class="bi bi-bar-chart me-2" style="color:#ff7900;"></i>Revenus Mensuels
                            </span>
                        </div>
                        <div class="gel-card-body">
                            <div v-if="!stats.monthly_revenue?.length" class="gel-empty">
                                <i class="bi bi-bar-chart"></i>
                                <span>Aucune donnée</span>
                            </div>
                            <div v-else class="gel-chart">
                                <div v-for="(item,i) in stats.monthly_revenue" :key="i" class="gel-chart-col">
                                    <span class="gel-chart-value">{{ fmtNum(item.total) }}</span>
                                    <div
                                        class="gel-chart-bar"
                                        :style="{ height: barHeight(item.total, stats.monthly_revenue) }"
                                    ></div>
                                    <span class="gel-chart-label">{{ (item.month||'').substring(5,7) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </GelLayout>
</template>

<style scoped>
/* ═══════════════════════════════════════════════════════
   GEL Dashboard — Super Administration
   ═══════════════════════════════════════════════════════ */

.gel-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 120px 20px;
    gap: 0.75rem;
}
.gel-spinner {
    width: 48px;
    height: 48px;
    border: 4px solid rgba(255, 121, 0, 0.1);
    border-top-color: #ff7900;
    border-radius: 50%;
    animation: gel-spin 0.7s linear infinite;
}
@keyframes gel-spin { to { transform: rotate(360deg); } }
.gel-state-icon {
    font-size: 2.5rem;
    color: #ef4444;
    opacity: 0.6;
}
.gel-state-text {
    color: var(--bs-secondary-color, #6c757d);
    font-size: 0.9rem;
    font-weight: 500;
    text-align: center;
}
.gel-retry-btn {
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
.gel-retry-btn:hover {
    background: rgba(255, 121, 0, 0.08);
}

/* ─── Header Card ─── */
.gel-header-card {
    background: #163A5E;
    border: 1px solid #1e4d7a;
    border-radius: 10px;
    padding: 1rem 1.25rem;
    margin-bottom: 1rem;
}
.gel-header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}
.gel-title {
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0 0 0.15rem;
    color: #fff;
}
.gel-subtitle {
    font-size: 0.82rem;
    color: rgba(255,255,255,0.7);
    margin: 0;
}
.gel-header-badges {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.gel-header-badge {
    background: #fff;
    border: 1px solid var(--bs-border-color, #e9ecef);
    border-radius: 8px;
    padding: 0.4rem 0.85rem;
    text-align: center;
}
.gel-header-badge-value {
    font-size: 1.1rem;
    font-weight: 700;
    line-height: 1.3;
}
.gel-text--primary { color: #ff7900; }
.gel-text--warning { color: #f59e0b; }
.gel-header-badge-label {
    font-size: 0.68rem;
    color: var(--bs-secondary-color, #6c757d);
}

/* ─── Quick actions ─── */
.gel-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
}
.gel-btn {
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
.gel-btn--primary {
    background: #ff7900;
    color: #fff !important;
    border: 1px solid #ff7900;
}
.gel-btn--primary:hover {
    background: #e66c00;
    border-color: #e66c00;
}
.gel-btn--outline {
    background: transparent;
    color: #6b7280;
    border: 1px solid #d1d5db;
}
.gel-btn--outline:hover {
    background: #f8f9fa;
    border-color: #b0b5bb;
}

/* ─── KPI Grid ─── */
.gel-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}
@media (max-width: 768px) {
    .gel-kpi-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 480px) {
    .gel-kpi-grid { grid-template-columns: 1fr; }
}
.gel-kpi-card {
    background: #fff;
    border: 1px solid var(--bs-border-color, #e9ecef);
    border-radius: 10px;
    padding: 1.25rem;
    transition: box-shadow 0.2s ease, transform 0.2s ease;
    height: 100%;
}
.gel-kpi-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.07);
    transform: translateY(-1px);
}
.gel-kpi-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    margin-bottom: 0.65rem;
}
.gel-kpi-value {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1.2;
    color: var(--bs-body-color, #212529);
}
.gel-kpi-label {
    font-size: 0.78rem;
    color: var(--bs-secondary-color, #6c757d);
    margin-top: 0.1rem;
}

/* ─── Columns ─── */
.gel-cols {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
@media (max-width: 992px) {
    .gel-cols { grid-template-columns: 1fr; }
}
.gel-col--narrow {
    grid-column: span 1;
}
.gel-col--wide {
    grid-column: span 1;
}

/* ─── Card ─── */
.gel-card {
    background: #fff;
    border: 1px solid var(--bs-border-color, #e9ecef);
    border-radius: 10px;
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.gel-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.85rem 1rem;
    border-bottom: 1px solid var(--bs-border-color, #e9ecef);
}
.gel-card-title {
    font-size: 0.82rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
}
.gel-card-link {
    font-size: 0.75rem;
    color: #ff7900;
    text-decoration: none;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
}
.gel-card-link:hover {
    text-decoration: underline;
}
.gel-card-body {
    padding: 0.5rem 0;
    flex: 1;
}

/* ─── Table ─── */
.gel-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.82rem;
}
.gel-table thead th {
    padding: 0.5rem 1rem;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: var(--bs-secondary-color, #6c757d);
    border-bottom: 1px solid var(--bs-border-color, #e9ecef);
    background: #fafbfc;
}
.gel-table tbody td {
    padding: 0.65rem 1rem;
    border-bottom: 1px solid var(--bs-border-color, #f0f0f0);
    vertical-align: middle;
}
.gel-table tbody tr:last-child td {
    border-bottom: none;
}
.gel-table tbody tr:hover {
    background: #f8fafb;
}
.gel-table-link {
    color: var(--bs-body-color, #212529);
    text-decoration: none;
    font-weight: 500;
}
.gel-table-link:hover {
    color: #ff7900;
}
.gel-table-muted {
    font-size: 0.75rem;
    color: var(--bs-secondary-color, #6c757d);
}
.gel-table-title {
    font-weight: 500;
    font-size: 0.82rem;
    line-height: 1.3;
}

/* ─── Badge ─── */
.gel-badge {
    display: inline-block;
    padding: 0.15em 0.5em;
    font-size: 0.68rem;
    font-weight: 600;
    border-radius: 50px;
    white-space: nowrap;
}
.gel-badge--success  { background: #d1fae5; color: #065f46; }
.gel-badge--muted    { background: #f3f4f6; color: #6b7280; }
.gel-badge--en_attente,
.gel-badge--a_faire  { background: #fef3c7; color: #92400e; }
.gel-badge--en_cours { background: #dbeafe; color: #1e40af; }
.gel-badge--terminee { background: #d1fae5; color: #065f46; }
.gel-badge--annulee  { background: #fee2e2; color: #b91c1c; }

/* ─── Progress bar ─── */
.gel-progress-wrap {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.gel-progress-bar {
    flex: 1;
    height: 6px;
    background: #eef3f9;
    border-radius: 3px;
    overflow: hidden;
    min-width: 60px;
}
.gel-progress-fill {
    height: 100%;
    background: #ff7900;
    border-radius: 3px;
    transition: width 0.4s ease;
}
.gel-progress-fill--done {
    background: #10b981;
}
.gel-progress-text {
    font-size: 0.72rem;
    color: var(--bs-secondary-color, #6c757d);
    min-width: 32px;
    text-align: right;
}

/* ─── Empty state ─── */
.gel-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    gap: 0.5rem;
    color: var(--bs-secondary-color, #6c757d);
    font-size: 0.82rem;
}
.gel-empty i {
    font-size: 1.5rem;
    opacity: 0.4;
}

/* ─── Pole distribution ─── */
.gel-pole-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
}
.gel-pole-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.gel-pole-name {
    font-size: 0.78rem;
    font-weight: 600;
    min-width: 100px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.gel-pole-track {
    flex: 1;
    height: 8px;
    background: #eef3f9;
    border-radius: 4px;
    overflow: hidden;
}
.gel-pole-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.5s ease;
}
.gel-pole-count {
    font-size: 0.8rem;
    font-weight: 700;
    min-width: 22px;
    text-align: right;
    color: var(--bs-body-color, #212529);
}

/* ─── Revenue bar chart ─── */
.gel-chart {
    display: flex;
    align-items: flex-end;
    gap: 2px;
    height: 160px;
    padding: 1rem 1rem 28px;
}
.gel-chart-col {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    min-width: 0;
    position: relative;
}
.gel-chart-value {
    font-size: 0.65rem;
    color: var(--bs-secondary-color, #888);
    margin-bottom: 3px;
    white-space: nowrap;
}
.gel-chart-bar {
    width: 100%;
    background: linear-gradient(180deg, #ff7900 0%, #e06700 100%);
    border-radius: 3px 3px 0 0;
    min-height: 4px;
    transition: height 0.4s ease;
}
.gel-chart-label {
    position: absolute;
    bottom: -22px;
    font-size: 0.72rem;
    color: var(--bs-secondary-color, #888);
    font-weight: 600;
}

/* ─── Print ─── */
@media print {
    .gel-actions { display: none; }
    .gel-card { break-inside: avoid; border: 1px solid #dee2e6; }
}
</style>
