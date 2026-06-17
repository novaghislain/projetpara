<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../Layouts/GelLayout.vue';
import { authStore } from '../../stores/auth';

const stats   = ref(null);
const loading = ref(true);
const error   = ref(null);

const fetchStats = async () => {
    loading.value = true; error.value = null;
    try {
        const res = await fetch('/api/stats');
        if (!res.ok) throw new Error('Erreur lors du chargement des statistiques');
        stats.value = await res.json();
    } catch (e) { error.value = e.message; }
    finally { loading.value = false; }
};

onMounted(fetchStats);

// ── Helpers ───────────────────────────────────────────────────────
const fmtNum = (n) => Number(n || 0).toLocaleString('fr-FR');
const statusLabel = (s) => ({ en_attente:'En attente', en_cours:'En cours', terminee:'Terminée', annulee:'Annulée' }[s] || s);
const statusBadge = (s) => ({
    en_attente: 'isup-status isup-status-warn',
    en_cours:   'isup-status isup-status-blue',
    terminee:   'isup-status isup-status-green',
    annulee:    'isup-status isup-status-red',
}[s] || 'isup-status isup-status-grey');

const barHeight = (val, allVals) => {
    const max = Math.max(...allVals.map(r => r.total || 0), 1);
    return Math.max(4, Math.round((val / max) * 130)) + 'px';
};
</script>

<template>
    <GelLayout page-title="Accueil">

        <!-- ── Loading ── -->
        <div v-if="loading" class="d-flex align-items-center justify-content-center py-5 gap-3">
            <div class="isup-spinner"></div>
            <span style="color:#888; font-size:14px;">Chargement en cours…</span>
        </div>

        <!-- ── Error ── -->
        <div v-else-if="error" class="isup-alert-error mb-3">
            <i class="bi-exclamation-triangle-fill me-2"></i>{{ error }}
            <button @click="fetchStats" class="isup-btn-primary ms-3" style="padding:4px 12px; font-size:12px;">
                <i class="bi-arrow-clockwise me-1"></i>Réessayer
            </button>
        </div>

        <!-- ── iSupplier Shell ── -->
        <template v-else-if="stats">
        <div class="isup-shell">

            <!-- ══ HEADER BLEU (style Oracle iSupplier) ══ -->
            <div class="isup-portal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="isup-portal-logo">
                        <i class="bi-gem" style="font-size:20px;"></i>
                    </div>
                    <div>
                        <div class="isup-portal-company">GEL Cabinet — Super Administration</div>
                        <div class="isup-portal-sub">
                            Connecté en tant que : {{ authStore.user?.name }} &nbsp;|&nbsp;
                            {{ new Date().toLocaleDateString('fr-FR', { weekday:'long', day:'numeric', month:'long', year:'numeric' }) }}
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <div class="isup-stat-pill">
                        <span class="isup-stat-num">{{ fmtNum(stats.total_clients) }}</span>
                        <span class="isup-stat-lbl">Clients</span>
                    </div>
                    <div class="isup-stat-pill">
                        <span class="isup-stat-num">{{ fmtNum(stats.total_missions) }}</span>
                        <span class="isup-stat-lbl">Missions</span>
                    </div>
                    <div class="isup-stat-pill">
                        <span class="isup-stat-num">{{ fmtNum(stats.pending_missions) }}</span>
                        <span class="isup-stat-lbl">En attente</span>
                    </div>
                    <div class="isup-stat-pill">
                        <span class="isup-stat-num">{{ fmtNum(stats.total_poles) }}</span>
                        <span class="isup-stat-lbl">Pôles</span>
                    </div>
                </div>
            </div>

            <!-- ══ VUE D'ENSEMBLE ══ -->
            <div class="isup-dashboard">

                <!-- Actions rapides -->
                <div class="d-flex gap-2 flex-wrap mb-4">
                    <a href="/clients/create" class="isup-btn-primary">
                        <i class="bi-plus-circle me-1"></i>Nouveau client
                    </a>
                    <a href="/missions/create" class="isup-btn-outline-navy">
                        <i class="bi-check2-square me-1"></i>Nouvelle mission
                    </a>
                    <a href="/admin/catalogue/orders" class="isup-btn-outline-navy">
                        <i class="bi-cart me-1"></i>Voir commandes
                    </a>
                </div>

                <!-- KPI row -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="isup-kpi-card">
                            <div class="isup-kpi-icon" style="background:#fff3e0; color:#FF7900;"><i class="bi-building"></i></div>
                            <div class="isup-kpi-val">{{ fmtNum(stats.total_clients) }}</div>
                            <div class="isup-kpi-lbl">Total Clients</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="isup-kpi-card">
                            <div class="isup-kpi-icon" style="background:#e3f2fd; color:#1565c0;"><i class="bi-person-check"></i></div>
                            <div class="isup-kpi-val">{{ fmtNum(stats.active_clients) }}</div>
                            <div class="isup-kpi-lbl">Clients Actifs</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="isup-kpi-card">
                            <div class="isup-kpi-icon" style="background:#e8f5e9; color:#2e7d32;"><i class="bi-check-circle"></i></div>
                            <div class="isup-kpi-val">{{ fmtNum(stats.completed_missions) }}</div>
                            <div class="isup-kpi-lbl">Missions terminées</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="isup-kpi-card">
                            <div class="isup-kpi-icon" style="background:#fff8e1; color:#f57f17;"><i class="bi-hourglass-split"></i></div>
                            <div class="isup-kpi-val">{{ fmtNum(stats.pending_missions) }}</div>
                            <div class="isup-kpi-lbl">En attente</div>
                        </div>
                    </div>
                </div>

                <!-- Tableaux -->
                <div class="row g-3 mb-4">
                    <div class="col-lg-6">
                        <div class="isup-panel">
                            <div class="isup-panel-header">
                                <i class="bi-building me-2" style="color:#FF7900;"></i>Clients récents
                                <a href="/clients" class="isup-panel-link ms-auto">Voir tout <i class="bi-arrow-right"></i></a>
                            </div>
                            <div class="isup-panel-body p-0">
                                <div v-if="!stats.recent_clients?.length" class="text-center py-4 text-muted" style="font-size:13px;">
                                    <i class="bi-inbox" style="font-size:22px; display:block; margin-bottom:6px;"></i>Aucun client récent
                                </div>
                                <div v-else class="isup-table-wrap">
                                    <table class="isup-table w-100">
                                        <thead><tr>
                                            <th>Société</th><th>Email</th><th class="text-center">Statut</th>
                                        </tr></thead>
                                        <tbody>
                                            <tr v-for="c in stats.recent_clients" :key="c.id">
                                                <td><a :href="'/clients/' + c.id" class="isup-link fw-semibold">{{ c.company_name }}</a></td>
                                                <td class="text-muted" style="font-size:12px;">{{ c.email }}</td>
                                                <td class="text-center">
                                                    <span :class="c.status === 'actif' ? 'isup-status isup-status-green' : 'isup-status isup-status-grey'">{{ c.status }}</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="isup-panel">
                            <div class="isup-panel-header">
                                <i class="bi-check2-square me-2" style="color:#FF7900;"></i>Missions récentes
                                <a href="/missions" class="isup-panel-link ms-auto">Voir tout <i class="bi-arrow-right"></i></a>
                            </div>
                            <div class="isup-panel-body p-0">
                                <div v-if="!stats.recent_missions?.length" class="text-center py-4 text-muted" style="font-size:13px;">
                                    <i class="bi-inbox" style="font-size:22px; display:block; margin-bottom:6px;"></i>Aucune mission récente
                                </div>
                                <div v-else class="isup-table-wrap">
                                    <table class="isup-table w-100">
                                        <thead><tr>
                                            <th>Titre</th><th>Statut</th><th>Avancement</th>
                                        </tr></thead>
                                        <tbody>
                                            <tr v-for="m in stats.recent_missions" :key="m.id">
                                                <td>
                                                    <div class="fw-semibold" style="font-size:13px;">{{ m.title }}</div>
                                                    <div class="text-muted" style="font-size:11px;">{{ m.client?.company_name || '—' }}</div>
                                                </td>
                                                <td><span :class="statusBadge(m.status)">{{ statusLabel(m.status) }}</span></td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="isup-progress-bar">
                                                            <div class="isup-progress-fill" :style="{ width: (m.progress||0)+'%', background: m.progress>=100?'#2e7d32':'#FF7900' }"></div>
                                                        </div>
                                                        <span style="font-size:11px; color:#888;">{{ m.progress||0 }}%</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Répartition pôles + Revenus -->
                <div class="row g-3">
                    <div class="col-lg-5">
                        <div class="isup-panel">
                            <div class="isup-panel-header">
                                <i class="bi-pie-chart me-2" style="color:#FF7900;"></i>Répartition par Pôle
                            </div>
                            <div class="isup-panel-body">
                                <div v-if="!stats.pole_distribution?.length" class="text-center py-3 text-muted" style="font-size:13px;">Aucune donnée</div>
                                <div v-else class="d-flex flex-column gap-3">
                                    <div v-for="p in stats.pole_distribution" :key="p.name" class="d-flex align-items-center gap-2">
                                        <span style="font-size:12px; font-weight:600; color:#163A5E; min-width:90px;">{{ p.name }}</span>
                                        <div style="flex-grow:1; height:8px; background:#eef3f9; border-radius:4px; overflow:hidden;">
                                            <div :style="{ width: p.pourcentage+'%', height:'100%', background: p.color || '#FF7900', borderRadius:'4px' }"></div>
                                        </div>
                                        <span style="font-size:12px; font-weight:700; color:#163A5E; min-width:20px;">{{ p.count }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="isup-panel">
                            <div class="isup-panel-header">
                                <i class="bi-bar-chart me-2" style="color:#FF7900;"></i>Revenus Mensuels
                            </div>
                            <div class="isup-panel-body">
                                <div v-if="!stats.monthly_revenue?.length" class="text-center py-3 text-muted" style="font-size:13px;">Aucune donnée</div>
                                <div v-else class="isup-bar-chart">
                                    <div v-for="(item,i) in stats.monthly_revenue" :key="i" class="isup-bar-col">
                                        <div class="isup-bar-val">{{ fmtNum(item.total) }}</div>
                                        <div class="isup-bar" :style="{ height: barHeight(item.total, stats.monthly_revenue) }"></div>
                                        <div class="isup-bar-label">{{ (item.month||'').substring(5,7) }}</div>
                                    </div>
                                </div>
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
/* ══ GEL Dashboard — unique styles ══ */

.isup-stat-pill { background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.2); border-radius:4px; padding:7px 16px; text-align:center; }
.isup-stat-num { display:block; font-size:20px; font-weight:800; color:#FF7900; font-family:'Outfit',sans-serif; }
.isup-stat-lbl { display:block; font-size:10px; color:rgba(255,255,255,.6); }

.isup-kpi-card { background:#fff; border:1px solid #dce3ee; border-radius:4px; padding:16px 14px; transition:box-shadow 0.15s, border-color 0.15s; }
.isup-kpi-card:hover { box-shadow:0 4px 14px rgba(22,58,94,.10); border-color:#FF7900; }
.isup-kpi-icon { width:40px; height:40px; border-radius:4px; display:flex; align-items:center; justify-content:center; font-size:18px; margin-bottom:10px; }
.isup-kpi-val { font-family:'Outfit',sans-serif; font-size:26px; font-weight:800; color:#163A5E; line-height:1; margin-bottom:4px; }
.isup-kpi-lbl { font-size:12px; color:#888; font-weight:600; }

.isup-panel-link { font-size:12px; color:rgba(255,255,255,.7); text-decoration:none; font-weight:600; transition:color 0.15s; }
.isup-panel-link:hover { color:#FF7900; }
.isup-link { color:#163A5E; text-decoration:none; transition:color 0.12s; }
.isup-link:hover { color:#FF7900; }

.isup-status-warn { background:#fff8e1; color:#f57f17; }
.isup-status-blue { background:#e3f2fd; color:#1565c0; }

.isup-progress-bar { flex-grow:1; height:6px; background:#eee; border-radius:3px; overflow:hidden; }
.isup-progress-fill { height:100%; border-radius:3px; }

.isup-bar-chart { display:flex; align-items:flex-end; gap:6px; height:150px; padding-bottom:22px; }
.isup-bar-col { display:flex; flex-direction:column; align-items:center; flex:1; justify-content:flex-end; position:relative; }
.isup-bar-val { font-size:9px; color:#888; margin-bottom:3px; white-space:nowrap; overflow:hidden; max-width:100%; }
.isup-bar { width:100%; background:linear-gradient(180deg,#FF7900 0%,#e06700 100%); border-radius:3px 3px 0 0; min-height:4px; }
.isup-bar-label { position:absolute; bottom:-18px; font-size:11px; color:#888; font-weight:600; }

.isup-btn-outline-navy { background:transparent; color:#163A5E; border:1px solid #163A5E; border-radius:4px; padding:7px 15px; font-size:13px; font-weight:600; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; transition:all 0.15s; }
.isup-btn-outline-navy:hover { background:#163A5E; color:#fff; }

.isup-shell { box-shadow:0 2px 12px rgba(0,0,0,0.08); }
</style>
