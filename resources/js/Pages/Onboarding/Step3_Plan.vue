<template>
    <div class="onboarding-wrapper">
        <div class="onboarding-card">
            <div class="text-center mb-4">
                <div class="step-indicator mb-3">
                    <span class="step-dot completed">1</span>
                    <span class="step-line completed"></span>
                    <span class="step-dot completed">2</span>
                    <span class="step-line active"></span>
                    <span class="step-dot active">3</span>
                    <span class="step-line"></span>
                    <span class="step-dot">4</span>
                    <span class="step-line"></span>
                    <span class="step-dot">5</span>
                </div>
                <h2 class="fw-bold" style="color:var(--gel-dark);">Plan d'abonnement</h2>
                <p class="text-muted">Étape 3 sur 5 — Choisissez la formule adaptée à vos besoins</p>
            </div>

            <div class="row g-4 justify-content-center">
                <div class="col-md-6">
                    <div class="plan-card" :class="{ selected: plan === 'mensuel' }" @click="plan = 'mensuel'; contractType = 'standard'">
                        <div class="plan-icon"><i class="bi bi-calendar-month"></i></div>
                        <h4>Mensuel</h4>
                        <div class="plan-price">Paiement mensuel</div>
                        <ul class="plan-features">
                            <li><i class="bi bi-check-lg text-success"></i> Tous les modules du domaine</li>
                            <li><i class="bi bi-check-lg text-success"></i> Support prioritaire</li>
                            <li><i class="bi bi-check-lg text-success"></i> Mises à jour incluses</li>
                            <li><i class="bi bi-x-lg text-secondary"></i> Sans engagement</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="plan-card plan-popular" :class="{ selected: plan === 'annuel' }" @click="plan = 'annuel'; contractType = 'premium'">
                        <div class="plan-badge">Populaire</div>
                        <div class="plan-icon"><i class="bi bi-calendar-check"></i></div>
                        <h4>Annuel</h4>
                        <div class="plan-price">Économisez 20%</div>
                        <ul class="plan-features">
                            <li><i class="bi bi-check-lg text-success"></i> Tous les modules du domaine</li>
                            <li><i class="bi bi-check-lg text-success"></i> Support prioritaire 24/7</li>
                            <li><i class="bi bi-check-lg text-success"></i> Mises à jour + formations</li>
                            <li><i class="bi bi-check-lg text-success"></i> Accès anticipé aux nouveautés</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div v-if="error" class="alert alert-danger mt-3">{{ error }}</div>

            <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                <a href="/register/company/step/2" class="btn btn-outline-secondary">← Retour</a>
                <button @click="submitStep3" class="btn btn-primary px-4" :disabled="!plan || submitting">
                    <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
                    Continuer →
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const plan = ref(null);
const contractType = ref('standard');
const submitting = ref(false);
const error = ref(null);

const csrfToken = document.querySelector('meta[name=csrf-token]')?.content || '';

async function submitStep3() {
    if (!plan.value) return;
    submitting.value = true;
    error.value = null;

    try {
        const res = await fetch('/register/company/step/3', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
            },
            body: JSON.stringify({ plan: plan.value, contract_type: contractType.value }),
        });

        const data = await res.json();
        if (!res.ok) { error.value = data.message; return; }
        if (data.success) window.location.href = `/register/company/step/${data.next_step}`;
    } catch (e) {
        error.value = 'Erreur réseau';
    } finally {
        submitting.value = false;
    }
}
</script>

<style scoped>
.onboarding-wrapper {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%);
    padding: 40px 20px;
}
.onboarding-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.08);
    padding: 40px;
    max-width: 720px;
    width: 100%;
}
.step-indicator { display: flex; align-items: center; justify-content: center; }
.step-dot {
    width: 36px; height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700;
    background: #e9ecef; color: #6c757d;
}
.step-dot.active { background: var(--gel-primary); color: #fff; }
.step-dot.completed { background: #198754; color: #fff; }
.step-line { width: 60px; height: 3px; background: #e9ecef; margin: 0 4px; border-radius: 2px; }
.step-line.active { background: var(--gel-primary); }
.step-line.completed { background: #198754; }
.plan-card {
    border: 2px solid #e9ecef;
    border-radius: 16px;
    padding: 28px 20px;
    cursor: pointer;
    transition: all 0.25s;
    position: relative;
    text-align: center;
}
.plan-card:hover:not(.selected) { border-color: var(--gel-primary); transform: translateY(-2px); }
.plan-card.selected { border-color: var(--gel-primary); background: rgba(255,121,0,0.04); box-shadow: 0 4px 20px rgba(255,121,0,0.12); }
.plan-popular { border-color: #ffd700; }
.plan-popular.selected { border-color: var(--gel-primary); }
.plan-badge {
    position: absolute; top: -10px; left: 50%; transform: translateX(-50%);
    background: #ffd700; color: #000; font-size: 11px; font-weight: 800;
    padding: 2px 14px; border-radius: 20px; text-transform: uppercase;
}
.plan-icon { font-size: 36px; color: var(--gel-primary); margin-bottom: 12px; }
.plan-card h4 { font-size: 18px; font-weight: 800; }
.plan-price { font-size: 14px; color: var(--gel-primary); font-weight: 700; margin-bottom: 16px; }
.plan-features { list-style: none; padding: 0; margin: 0; text-align: left; }
.plan-features li { font-size: 13px; padding: 5px 0; display: flex; align-items: center; gap: 8px; }
</style>
