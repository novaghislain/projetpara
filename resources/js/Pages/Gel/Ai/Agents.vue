<script setup>
import { ref, onMounted, computed } from 'vue';

const agents = ref({});
const recentSuggestions = ref([]);
const totalPending = ref(0);
const loading = ref(true);
const runningAgent = ref(null);
const error = ref(null);
const flashMessage = ref('');
const noContext = ref(false);
const availableClients = ref([]);
const selectingClient = ref(false);

const agentsList = computed(() => Object.values(agents.value));

async function selectClient(clientId) {
    selectingClient.value = true;
    try {
        await window.axios.post('/api/me/switch-context', { client_id: clientId });
        window.location.reload();
    } catch (e) {
        error.value = 'Impossible de sélectionner cette entreprise.';
        selectingClient.value = false;
    }
}

async function loadDashboard() {
    loading.value = true;
    error.value = null;
    noContext.value = false;
    try {
        const resp = await window.axios.get('/api/ai/agents/dashboard');
        const data = resp.data;
        if (data.no_context) {
            noContext.value = true;
            availableClients.value = data.clients || [];
            agents.value = {};
            recentSuggestions.value = [];
            totalPending.value = 0;
        } else {
            agents.value = data.agents || {};
            recentSuggestions.value = data.recent || [];
            totalPending.value = data.total_pending || 0;
        }
    } catch (e) {
        error.value = 'Erreur de chargement du dashboard IA';
        console.error(e);
    }
    loading.value = false;
}

async function runAgent(agentKey) {
    runningAgent.value = agentKey;
    flashMessage.value = '';
    error.value = null;
    try {
        const resp = await window.axios.post(`/api/ai/agents/run/${agentKey}`);
        const msg = resp.data?.message || 'Agent exécuté avec succès';
        flashMessage.value = msg;
        await loadDashboard();
        setTimeout(() => flashMessage.value = '', 4000);
    } catch (e) {
        const detail = e?.response?.data?.message || e?.message || 'Erreur inconnue';
        error.value = `Échec: ${detail}`;
        console.error('Agent run error:', detail);
        setTimeout(() => error.value = '', 6000);
    }
    runningAgent.value = null;
}

const statusColors = { pending: '#F59E0B', approved: '#10B981', rejected: '#EF4444', applied: '#3B82F6' };
const urgencyColors = { warning: '#F59E0B', urgent: '#EF4444', critical: '#DC2626', info: '#3B82F6' };

onMounted(loadDashboard);
</script>

<template>
    <div class="ai-agents-page">
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <h4 class="fw-bold mb-1" style="color:#1e293b;">
                    <i class="bi-robot me-2" style="color:#FF7900;"></i>Agents IA
                </h4>
                <p class="text-muted mb-0" style="font-size:13px;">
                    GEL Intelligence — {{ totalPending }} suggestion(s) en attente
                </p>
            </div>
            <button class="btn d-flex align-items-center gap-2" style="background:#FF7900;color:#fff;border:none;border-radius:8px;font-size:13px;padding:8px 16px;" @click="loadDashboard">
                <i class="bi-arrow-clockwise"></i> Rafraîchir
            </button>
        </div>

        <!-- Flash message -->
        <div v-if="flashMessage" class="alert alert-success d-flex align-items-center gap-2" style="border:none;border-radius:10px;background:#dcfce7;color:#166534;font-size:13px;padding:10px 14px;">
            <i class="bi-check-circle-fill"></i> {{ flashMessage }}
        </div>

        <!-- Loading -->
        <div v-if="loading && !Object.keys(agents).length" class="text-center py-5">
            <div class="spinner-border" style="color:#FF7900;" role="status"></div>
            <p class="text-muted mt-2">Chargement des agents...</p>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <!-- No context -->
        <div v-else-if="noContext" class="text-center py-5">
            <div style="font-size:48px;color:#FF7900;opacity:0.5;margin-bottom:16px;">
                <i class="bi-building"></i>
            </div>
            <h5 style="color:#1e293b;font-weight:600;">Aucune entreprise sélectionnée</h5>
            <p class="text-muted" style="font-size:14px;max-width:400px;margin:8px auto 16px;">
                Sélectionnez une entreprise pour utiliser les agents IA.
            </p>

            <!-- Liste des entreprises disponibles -->
            <div v-if="availableClients.length" class="d-flex flex-wrap gap-2 justify-content-center" style="max-width:500px;margin:0 auto;">
                <button
                    v-for="c in availableClients"
                    :key="c.id"
                    class="btn d-flex align-items-center gap-2 px-4 py-3"
                    style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;font-size:14px;color:#1e293b;font-weight:500;transition:all 0.15s;min-width:200px;"
                    :disabled="selectingClient"
                    @click="selectClient(c.id)"
                >
                    <i class="bi-building" style="color:#FF7900;"></i>
                    {{ c.company_name || c.name || 'Entreprise #' + c.id }}
                    <span v-if="c.domain_code" class="badge ms-auto" style="background:#f1f5f9;color:#64748b;font-weight:400;font-size:10px;">{{ c.domain_code }}</span>
                </button>
            </div>
        </div>

        <!-- Agent Cards Grid -->
        <div v-else class="row g-3">
            <div v-for="(agent, key) in agents" :key="key" class="col-12 col-md-6 col-xl-4">
                <div class="ai-agent-card">
                    <div class="ai-agent-header">
                        <div class="ai-agent-icon" :style="{ background: agent.color + '18', color: agent.color }">
                            <i :class="agent.icon"></i>
                        </div>
                        <div class="ai-agent-info">
                            <h6 class="ai-agent-name">{{ agent.name }}</h6>
                            <span class="ai-agent-desc">{{ agent.description }}</span>
                        </div>
                        <span class="ai-agent-status" :class="'ai-agent-status--' + agent.status">
                            {{ agent.status }}
                        </span>
                    </div>

                    <div class="ai-agent-metrics">
                        <div class="ai-agent-metric">
                            <span class="ai-metric-value">{{ agent.pending_suggestions ?? 0 }}</span>
                            <span class="ai-metric-label">En attente</span>
                        </div>
                        <div v-if="agent.anomalies_count !== undefined" class="ai-agent-metric">
                            <span class="ai-metric-value">{{ agent.anomalies_count }}</span>
                            <span class="ai-metric-label">Anomalies</span>
                        </div>
                        <div v-if="agent.overdue_invoices !== undefined" class="ai-agent-metric">
                            <span class="ai-metric-value">{{ agent.overdue_invoices }}</span>
                            <span class="ai-metric-label">Impayés</span>
                        </div>
                        <div v-if="agent.unscanned_documents !== undefined" class="ai-agent-metric">
                            <span class="ai-metric-value">{{ agent.unscanned_documents }}</span>
                            <span class="ai-metric-label">Docs</span>
                        </div>
                        <div v-if="agent.estimated_balance !== undefined" class="ai-agent-metric">
                            <span class="ai-metric-value">{{ (agent.estimated_balance / 1000000).toFixed(1) }}M</span>
                            <span class="ai-metric-label">Solde</span>
                        </div>
                        <div v-if="agent.open_reconciliations !== undefined" class="ai-agent-metric">
                            <span class="ai-metric-value">{{ agent.open_reconciliations }}</span>
                            <span class="ai-metric-label">En cours</span>
                        </div>
                    </div>

                    <div class="ai-agent-footer">
                        <button class="ai-agent-run-btn" :disabled="runningAgent === key" @click="runAgent(key)">
                            <i :class="runningAgent === key ? 'bi-arrow-clockwise spin' : 'bi-play-fill'"></i>
                            {{ runningAgent === key ? 'Analyse...' : 'Exécuter' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Suggestions -->
        <div v-if="recentSuggestions.length" class="mt-4">
            <h6 class="fw-bold mb-3" style="color:#1e293b;font-size:14px;">
                <i class="bi-clock-history me-2"></i>Suggestions récentes
            </h6>
            <div class="ai-recent-list">
                <div v-for="s in recentSuggestions" :key="s.id" class="ai-recent-item">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge" :style="{ background: (agents[s.agent]?.color || '#6B7280') + '20', color: agents[s.agent]?.color || '#6B7280' }">
                            {{ agents[s.agent]?.name || s.agent }}
                        </span>
                        <span class="fw-semibold" style="font-size:13px;">{{ s.title }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <span class="ai-recent-status" :style="{ color: statusColors[s.status] }">{{ s.status }}</span>
                        <span class="text-muted" style="font-size:11px;">{{ s.created_at ? new Date(s.created_at).toLocaleDateString('fr-FR') : '' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.ai-agents-page {
    padding: 8px 0;
}
.ai-agent-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 16px;
    transition: box-shadow 0.2s ease;
}
.ai-agent-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.06);
}
.ai-agent-header {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.ai-agent-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.ai-agent-info { flex: 1; min-width: 0; }
.ai-agent-name {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}
.ai-agent-desc {
    font-size: 11px;
    color: #94a3b8;
    display: block;
    margin-top: 1px;
}
.ai-agent-status {
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    padding: 3px 8px;
    border-radius: 6px;
}
.ai-agent-status--active { background: #dcfce7; color: #16a34a; }
.ai-agent-status--inactive { background: #fee2e2; color: #dc2626; }
.ai-agent-metrics {
    display: flex;
    gap: 12px;
    margin-top: 14px;
    padding-top: 12px;
    border-top: 1px solid #f1f5f9;
}
.ai-agent-metric {
    display: flex;
    flex-direction: column;
}
.ai-metric-value {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.2;
}
.ai-metric-label {
    font-size: 10px;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.ai-agent-footer {
    margin-top: 12px;
    padding-top: 10px;
    border-top: 1px solid #f1f5f9;
}
.ai-agent-run-btn {
    width: 100%;
    padding: 7px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #f8fafc;
    color: #475569;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.ai-agent-run-btn:hover:not(:disabled) {
    background: #FF7900;
    color: #fff;
    border-color: #FF7900;
}
.ai-agent-run-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.spin { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Recent list */
.ai-recent-list {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
}
.ai-recent-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    border-bottom: 1px solid #f1f5f9;
    flex-wrap: wrap;
    gap: 6px;
}
.ai-recent-item:last-child { border-bottom: none; }
.ai-recent-status {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
</style>
