<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: { type: String, default: 'GEL Cabinet' },
    type: { type: String, default: 'cabinet' } // 'cabinet' or 'client'
});

const currentYear = computed(() => new Date().getFullYear());

const accentColor = computed(() => {
    return props.type === 'client' ? '#10b981' : '#2563eb';
});
</script>

<template>
    <div class="auth-layout">
        <!-- Section Image / Marque -->
        <div class="auth-brand" :style="{ background: type === 'client' ? 'linear-gradient(135deg, #064e3b, #059669)' : 'linear-gradient(135deg, #0f172a, #1e3a8a)' }">
            <div class="brand-content">
                <a href="/" class="brand-logo">
                    <span class="logo-icon" :style="{ color: accentColor }">G</span>
                    <span class="logo-text">GEL Cabinet</span>
                </a>
                
                <div class="brand-msg">
                    <h1 class="brand-title">
                        {{ type === 'client' ? 'Votre portail entreprise, centralisé.' : 'L\'ERP nouvelle génération pour votre cabinet.' }}
                    </h1>
                    <p class="brand-desc">
                        {{ type === 'client' ? 'Accédez à votre comptabilité, vos bulletins de paie et vos contrats en un seul endroit.' : 'Gérez vos dossiers, votre facturation et vos obligations fiscales avec fluidité et sécurité.' }}
                    </p>
                </div>

                <div class="brand-footer">
                    &copy; {{ currentYear }} GEL Cabinet. Tous droits réservés.
                </div>
            </div>
            <!-- Effet visuel -->
            <div class="auth-shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
            </div>
        </div>

        <!-- Section Formulaire -->
        <div class="auth-form-section">
            <div class="auth-container">
                <div class="auth-header">
                    <a href="/" class="back-home"><i class="bi bi-arrow-left"></i> Retour à l'accueil</a>
                </div>
                
                <div class="auth-content">
                    <slot />
                </div>
                
                <div class="auth-links">
                    <a href="/legal/terms">Conditions d'utilisation</a>
                    <a href="/legal/privacy">Politique de confidentialité</a>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.auth-layout {
    display: flex;
    min-height: 100vh;
    font-family: 'Inter', -apple-system, sans-serif;
    background: #f8fafc;
}

/* Partie Marque (Gauche) */
.auth-brand {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 4rem;
    position: relative;
    overflow: hidden;
    color: white;
}

.brand-content {
    position: relative;
    z-index: 2;
    max-width: 480px;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.brand-logo {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
    color: white;
    margin-bottom: auto;
}

.logo-icon {
    width: 2.5rem;
    height: 2.5rem;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
    font-weight: 800;
    font-size: 1.25rem;
}

.logo-text {
    font-weight: 700;
    font-size: 1.25rem;
    letter-spacing: -0.025em;
}

.brand-msg {
    margin-bottom: auto;
}

.brand-title {
    font-size: 2.5rem;
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 1.5rem;
}

.brand-desc {
    font-size: 1.125rem;
    color: rgba(255, 255, 255, 0.8);
    line-height: 1.6;
}

.brand-footer {
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.6);
}

/* Shapes Background */
.auth-shapes {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    overflow: hidden;
    z-index: 1;
}

.shape {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
}

.shape-1 {
    width: 400px;
    height: 400px;
    background: rgba(255, 255, 255, 0.1);
    top: -100px;
    right: -100px;
}

.shape-2 {
    width: 300px;
    height: 300px;
    background: rgba(255, 255, 255, 0.05);
    bottom: -50px;
    left: -50px;
}

/* Partie Formulaire (Droite) */
.auth-form-section {
    width: 500px;
    display: flex;
    flex-direction: column;
    background: white;
    box-shadow: -10px 0 30px rgba(0, 0, 0, 0.05);
    z-index: 10;
}

.auth-container {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 2rem 3rem;
}

.auth-header {
    margin-bottom: 2rem;
}

.back-home {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #64748b;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: color 0.2s;
}

.back-home:hover {
    color: #0f172a;
}

.auth-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.auth-links {
    margin-top: 2rem;
    display: flex;
    justify-content: center;
    gap: 1.5rem;
}

.auth-links a {
    color: #94a3b8;
    text-decoration: none;
    font-size: 0.85rem;
    transition: color 0.2s;
}

.auth-links a:hover {
    color: #3b82f6;
}

/* Responsive */
@media (max-width: 900px) {
    .auth-brand {
        display: none;
    }
    .auth-form-section {
        width: 100%;
        box-shadow: none;
    }
}
</style>
