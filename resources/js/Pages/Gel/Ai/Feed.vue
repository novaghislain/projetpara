<script setup>
import { ref } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';
import AiFeed from '../../../Components/AiFeed.vue';

const agents = [
    { key: 'ohada',          label: 'Agent OHADA/Compta',  icon: 'bi-calculator',        color: '#3B82F6', desc: 'Vérification SYSCOHADA, écritures comptables' },
    { key: 'fiscal',         label: 'Agent Fiscal',        icon: 'bi-file-earmark-text', color: '#8B5CF6', desc: 'Télédéclarations, TVA, IRPP/CNSS' },
    { key: 'reconciliation', label: 'Agent Rapprochement', icon: 'bi-arrow-left-right',  color: '#06B6D4', desc: 'Rapprochement bancaire automatique' },
    { key: 'relance',        label: 'Agent Relance',       icon: 'bi-send-check',        color: '#F59E0B', desc: 'Relances clients intelligentes' },
    { key: 'ocr',            label: 'Agent OCR',           icon: 'bi-upc-scan',          color: '#10B981', desc: 'Numérisation et extraction factures' },
    { key: 'cashflow',       label: 'Agent Cashflow',      icon: 'bi-graph-up-arrow',    color: '#EF4444', desc: 'Prévisions trésorerie et alertes' },
];
</script>

<template>
    <GelLayout pageTitle="GEL Intelligence — Fil IA">
        <div class="ai-page">
            <!-- Header -->
            <div class="ai-page-hero">
                <div class="ai-page-hero-content">
                    <div class="ai-page-hero-icon">
                        <i class="bi-robot"></i>
                    </div>
                    <div>
                        <h2 class="ai-page-hero-title">GEL Intelligence</h2>
                        <p class="ai-page-hero-sub">Hub central des suggestions et actions automatisées par vos agents IA</p>
                    </div>
                </div>
            </div>

            <!-- Agents grid -->
            <div class="ai-agents-overview">
                <h5 class="ai-section-title"><i class="bi-cpu me-2"></i>Agents Actifs</h5>
                <div class="ai-agents-grid">
                    <div v-for="agent in agents" :key="agent.key" class="ai-agent-card">
                        <div class="ai-agent-card-icon" :style="{ background: agent.color + '18', color: agent.color }">
                            <i :class="agent.icon"></i>
                        </div>
                        <div class="ai-agent-card-info">
                            <div class="ai-agent-card-name">{{ agent.label }}</div>
                            <div class="ai-agent-card-desc">{{ agent.desc }}</div>
                        </div>
                        <div class="ai-agent-card-status">
                            <span class="ai-status-dot"></span>
                            <span>Actif</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feed section -->
            <div class="ai-feed-section">
                <h5 class="ai-section-title"><i class="bi-activity me-2"></i>Fil d'Activité</h5>
                <div class="ai-feed-card">
                    <AiFeed :limit="20" :polling-interval="15000" />
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<style scoped>
.ai-page { max-width: 1200px; }
.ai-page-hero {
    background: linear-gradient(135deg, rgba(255,121,0,0.08) 0%, rgba(139,92,246,0.06) 100%);
    border: 1px solid rgba(255,121,0,0.12);
    border-radius: 14px;
    padding: 28px 32px;
    margin-bottom: 28px;
}
.ai-page-hero-content { display: flex; align-items: center; gap: 18px; }
.ai-page-hero-icon {
    width: 52px; height: 52px;
    background: linear-gradient(135deg, #FF7900, #8B5CF6);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 24px; flex-shrink: 0;
    box-shadow: 0 4px 16px rgba(255,121,0,0.25);
}
.ai-page-hero-title { font-family: 'Outfit', sans-serif; font-size: 22px; font-weight: 800; color: #1a202c; margin: 0; }
.ai-page-hero-sub { font-size: 14px; color: #718096; margin: 4px 0 0; }

.ai-section-title {
    font-size: 14px; font-weight: 700; color: #2d3748;
    text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 16px;
}
.ai-agents-overview { margin-bottom: 32px; }
.ai-agents-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 14px; }
.ai-agent-card {
    display: flex; align-items: center; gap: 14px;
    padding: 16px 18px;
    background: #fff; border-radius: 12px;
    border: 1px solid rgba(0,0,0,0.06);
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    transition: all 0.2s;
}
.ai-agent-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); transform: translateY(-1px); }
.ai-agent-card-icon {
    width: 42px; height: 42px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; flex-shrink: 0;
}
.ai-agent-card-info { flex: 1; min-width: 0; }
.ai-agent-card-name { font-size: 14px; font-weight: 700; color: #1a202c; }
.ai-agent-card-desc { font-size: 12px; color: #8896a6; margin-top: 2px; }
.ai-agent-card-status {
    display: flex; align-items: center; gap: 5px;
    font-size: 11px; font-weight: 600; color: #10B981;
    flex-shrink: 0;
}
.ai-status-dot {
    width: 7px; height: 7px; background: #10B981;
    border-radius: 50%; animation: pulse-dot 2s ease-in-out infinite;
}
@keyframes pulse-dot { 0%,100% { opacity:1; } 50% { opacity:0.4; } }

.ai-feed-card {
    background: #0F1A2E; border-radius: 14px;
    padding: 4px; overflow: hidden;
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
}
</style>
