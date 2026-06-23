<script setup>
import GelLayout from '../../../Layouts/GelLayout.vue';
</script>

<template>
    <GelLayout pageTitle="Relances Intelligentes IA">
        <div class="ai-module-page">
            <div class="ai-module-hero" style="--accent: #F59E0B">
                <div class="ai-module-hero-icon"><i class="bi-send-check"></i></div>
                <div>
                    <h2 class="ai-module-title">Relances Intelligentes</h2>
                    <p class="ai-module-sub">Automatisation des relances clients avec scoring et personnalisation IA</p>
                </div>
            </div>

            <div class="ai-module-cards">
                <div class="ai-feature-card">
                    <div class="ai-feature-icon" style="background: rgba(245,158,11,0.1); color: #F59E0B"><i class="bi-speedometer2"></i></div>
                    <h6>Scoring Client</h6>
                    <p>Évaluation automatique du risque d'impayé et priorisation des relances</p>
                    <button class="btn btn-sm btn-primary" disabled>Bientôt disponible</button>
                </div>
                <div class="ai-feature-card">
                    <div class="ai-feature-icon" style="background: rgba(139,92,246,0.1); color: #8B5CF6"><i class="bi-envelope-paper"></i></div>
                    <h6>Rédaction Automatique</h6>
                    <p>Génération de courriers de relance personnalisés selon l'historique client</p>
                    <button class="btn btn-sm btn-primary" disabled>Bientôt disponible</button>
                </div>
                <div class="ai-feature-card">
                    <div class="ai-feature-icon" style="background: rgba(16,185,129,0.1); color: #10B981"><i class="bi-calendar-check"></i></div>
                    <h6>Planification</h6>
                    <p>Programmation automatique des escalades (email → courrier → mise en demeure)</p>
                    <button class="btn btn-sm btn-primary" disabled>Bientôt disponible</button>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<style scoped>
.ai-module-page { max-width: 1100px; }
.ai-module-hero {
    display: flex; align-items: center; gap: 18px;
    background: linear-gradient(135deg, color-mix(in srgb, var(--accent) 8%, transparent), color-mix(in srgb, var(--accent) 3%, transparent));
    border: 1px solid color-mix(in srgb, var(--accent) 15%, transparent);
    border-radius: 14px; padding: 28px 32px; margin-bottom: 28px;
}
.ai-module-hero-icon {
    width: 52px; height: 52px; background: var(--accent);
    border-radius: 14px; display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 24px; flex-shrink: 0;
    box-shadow: 0 4px 16px color-mix(in srgb, var(--accent) 30%, transparent);
}
.ai-module-title { font-family: 'Outfit', sans-serif; font-size: 22px; font-weight: 800; color: #1a202c; margin: 0; }
.ai-module-sub { font-size: 14px; color: #718096; margin: 4px 0 0; }
.ai-module-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 18px; }
.ai-feature-card {
    background: #fff; border-radius: 14px; padding: 24px;
    border: 1px solid rgba(0,0,0,0.06);
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    transition: all 0.2s;
}
.ai-feature-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.08); transform: translateY(-2px); }
.ai-feature-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 14px; }
.ai-feature-card h6 { font-size: 15px; font-weight: 700; color: #1a202c; margin-bottom: 8px; }
.ai-feature-card p { font-size: 13px; color: #718096; line-height: 1.5; margin-bottom: 16px; }
</style>
