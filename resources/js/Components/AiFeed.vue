<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';

const emit = defineEmits(['navigate']);
const props = defineProps({
    limit: { type: Number, default: 10 },
    showHeader: { type: Boolean, default: true },
    pollingInterval: { type: Number, default: 30000 }, // ms
});

const suggestions = ref([]);
const loading = ref(false);
const error = ref(null);

const agents = {
    ohada:          { label: 'Agent OHADA',        icon: 'bi-calculator',     color: '#3B82F6' },
    fiscal:         { label: 'Agent Fiscal',       icon: 'bi-file-earmark-text', color: '#8B5CF6' },
    reconciliation: { label: 'Agent Réconc.',      icon: 'bi-arrow-left-right', color: '#06B6D4' },
    relance:        { label: 'Agent Relance',      icon: 'bi-bell',           color: '#F59E0B' },
    ocr:            { label: 'Agent OCR',          icon: 'bi-scanner',        color: '#10B981' },
    cashflow:       { label: 'Agent Cashflow',     icon: 'bi-cash-stack',     color: '#EF4444' },
};

const groupedByDate = computed(() => {
    const groups = {};
    for (const s of suggestions.value) {
        const date = s.created_at ? new Date(s.created_at).toLocaleDateString('fr-FR') : 'Inconnue';
        if (!groups[date]) groups[date] = [];
        groups[date].push(s);
    }
    return groups;
});

const statusColors = {
    pending:  '#F59E0B',
    approved: '#10B981',
    rejected: '#EF4444',
    applied:  '#3B82F6',
};

async function fetchSuggestions() {
    loading.value = true;
    error.value = null;
    try {
        const resp = await window.axios.get('/api/ai/suggestions', {
            params: { status: 'pending', per_page: props.limit }
        });
        suggestions.value = resp.data.data || [];
    } catch (e) {
        if (e?.response?.status !== 401 && e?.response?.status !== 403) {
            error.value = 'Erreur de chargement';
        }
        suggestions.value = [];
    }
    loading.value = false;
}

async function approve(id) {
    try {
        await window.axios.post(`/api/ai/suggestions/${id}/approve`);
        suggestions.value = suggestions.value.filter(s => s.id !== id);
    } catch (e) { console.error(e); }
}

async function reject(id) {
    const reason = prompt('Motif du rejet :');
    if (!reason) return;
    try {
        await window.axios.post(`/api/ai/suggestions/${id}/reject`, { reason });
        suggestions.value = suggestions.value.filter(s => s.id !== id);
    } catch (e) { console.error(e); }
}

let pollInterval = null;

onMounted(() => {
    fetchSuggestions();
    if (props.pollingInterval > 0) {
        pollInterval = setInterval(fetchSuggestions, props.pollingInterval);
    }
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});
</script>

<template>
    <div class="ai-feed">
        <!-- Header -->
        <div v-if="showHeader" class="ai-feed-header">
            <div class="ai-feed-header-left">
                <i class="bi-robot"></i>
                <span>Fil IA</span>
                <span v-if="suggestions.length" class="ai-feed-badge">{{ suggestions.length }}</span>
            </div>
            <button v-if="suggestions.length" class="ai-feed-clear" @click="fetchSuggestions" title="Rafraîchir">
                <i class="bi-arrow-clockwise"></i>
            </button>
        </div>

        <!-- Loading -->
        <div v-if="loading && !suggestions.length" class="ai-feed-loading">
            <div class="ai-feed-spinner"></div>
            <span>Analyse en cours...</span>
        </div>

        <!-- Empty -->
        <div v-else-if="!suggestions.length && !loading" class="ai-feed-empty">
            <i class="bi-check-circle"></i>
            <span>Aucune suggestion en attente</span>
        </div>

        <!-- Suggestions list -->
        <div v-else class="ai-feed-list">
            <template v-for="(items, date) in groupedByDate" :key="date">
                <div class="ai-feed-date">{{ date }}</div>
                <div v-for="s in items" :key="s.id" class="ai-feed-item">
                    <div class="ai-feed-item-icon" :style="{ background: (agents[s.agent]?.color || '#6B7280') + '22', color: agents[s.agent]?.color || '#6B7280' }">
                        <i :class="agents[s.agent]?.icon || 'bi-robot'"></i>
                    </div>
                    <div class="ai-feed-item-content">
                        <div class="ai-feed-item-header">
                            <span class="ai-feed-item-agent">{{ agents[s.agent]?.label || s.agent }}</span>
                            <span class="ai-feed-item-status" :style="{ color: statusColors[s.status] || '#6B7280' }">{{ s.status }}</span>
                        </div>
                        <div class="ai-feed-item-title">{{ s.title }}</div>
                        <div v-if="s.description" class="ai-feed-item-desc">{{ s.description }}</div>
                        <div v-if="s.status === 'pending'" class="ai-feed-item-actions">
                            <button class="ai-feed-btn ai-feed-btn-approve" @click="approve(s.id)" title="Approuver">
                                <i class="bi-check-lg"></i> Approuver
                            </button>
                            <button class="ai-feed-btn ai-feed-btn-reject" @click="reject(s.id)" title="Rejeter">
                                <i class="bi-x-lg"></i> Rejeter
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<style scoped>
.ai-feed {
    background: rgba(255,255,255,0.03);
    border-radius: 10px;
    overflow: hidden;
}
.ai-feed-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 12px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}
.ai-feed-header-left {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 600;
    color: rgba(255,255,255,0.6);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.ai-feed-header-left i { font-size: 14px; color: #FF7900; }
.ai-feed-badge {
    background: #FF7900;
    color: #fff;
    font-size: 9px;
    border-radius: 10px;
    padding: 1px 7px;
    font-weight: 700;
}
.ai-feed-clear {
    border: none;
    background: none;
    color: rgba(255,255,255,0.3);
    font-size: 12px;
    cursor: pointer;
    padding: 2px 4px;
    border-radius: 4px;
    transition: all 0.2s;
}
.ai-feed-clear:hover {
    color: #FF7900;
    background: rgba(255,121,0,0.15);
}
.ai-feed-loading {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 20px 12px;
    justify-content: center;
    color: rgba(255,255,255,0.4);
    font-size: 12px;
}
.ai-feed-spinner {
    width: 14px;
    height: 14px;
    border: 2px solid rgba(255,255,255,0.1);
    border-top-color: #FF7900;
    border-radius: 50%;
    animation: aifeed-spin 0.6s linear infinite;
}
@keyframes aifeed-spin { to { transform: rotate(360deg); } }
.ai-feed-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 20px 12px;
    color: rgba(255,255,255,0.3);
    font-size: 12px;
}
.ai-feed-empty i { font-size: 22px; opacity: 0.5; }
.ai-feed-list {
    max-height: 360px;
    overflow-y: auto;
}
.ai-feed-date {
    padding: 6px 12px 2px;
    font-size: 10px;
    font-weight: 600;
    color: rgba(255,255,255,0.25);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.ai-feed-item {
    display: flex;
    gap: 10px;
    padding: 8px 12px;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    transition: background 0.15s;
}
.ai-feed-item:last-child { border-bottom: none; }
.ai-feed-item:hover { background: rgba(255,255,255,0.03); }
.ai-feed-item-icon {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
}
.ai-feed-item-content { flex: 1; min-width: 0; }
.ai-feed-item-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
}
.ai-feed-item-agent {
    font-size: 11px;
    font-weight: 600;
    color: rgba(255,255,255,0.55);
}
.ai-feed-item-status {
    font-size: 9px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.ai-feed-item-title {
    font-size: 13px;
    font-weight: 500;
    color: rgba(255,255,255,0.85);
    margin-top: 1px;
}
.ai-feed-item-desc {
    font-size: 11px;
    color: rgba(255,255,255,0.45);
    margin-top: 2px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.ai-feed-item-actions {
    display: flex;
    gap: 6px;
    margin-top: 6px;
}
.ai-feed-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    border: none;
    border-radius: 5px;
    font-size: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.ai-feed-btn-approve {
    background: rgba(16,185,129,0.15);
    color: #34D399;
}
.ai-feed-btn-approve:hover { background: rgba(16,185,129,0.25); }
.ai-feed-btn-reject {
    background: rgba(239,68,68,0.15);
    color: #F87171;
}
.ai-feed-btn-reject:hover { background: rgba(239,68,68,0.25); }
</style>
