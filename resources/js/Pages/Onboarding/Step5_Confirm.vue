<template>
    <div class="onboarding-wrapper">
        <div class="onboarding-card">
            <div class="text-center mb-4">
                <div class="step-indicator mb-3">
                    <span class="step-dot completed">1</span>
                    <span class="step-line completed"></span>
                    <span class="step-dot completed">2</span>
                    <span class="step-line completed"></span>
                    <span class="step-dot completed">3</span>
                    <span class="step-line completed"></span>
                    <span class="step-dot completed">4</span>
                    <span class="step-line active"></span>
                    <span class="step-dot active">5</span>
                </div>
                <h2 class="fw-bold" style="color:var(--gel-dark);">Confirmation</h2>
                <p class="text-muted">Étape 5 sur 5 — Vérifiez vos informations avant de finaliser</p>
            </div>

            <div v-if="created" class="text-center py-4">
                <div class="success-icon mb-3">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 64px;"></i>
                </div>
                <h3 class="fw-bold text-success">Félicitations !</h3>
                <p class="mb-1">Votre entreprise <strong>{{ created.name }}</strong> a été créée avec succès.</p>
                <p class="text-muted">Vous allez être redirigé vers votre tableau de bord...</p>
                <div class="mt-3">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>

            <template v-else>
                <div class="summary-grid">
                    <div class="summary-section">
                        <h5><i class="bi bi-building me-2"></i>Entreprise</h5>
                        <div class="summary-row"><span>Nom</span><span class="fw-semibold">{{ data.step1?.company_name }}</span></div>
                        <div class="summary-row"><span>IFU</span><span>{{ data.step1?.ifu || '—' }}</span></div>
                        <div class="summary-row"><span>RCCM</span><span>{{ data.step1?.rccm || '—' }}</span></div>
                        <div class="summary-row"><span>Email</span><span>{{ data.step1?.email }}</span></div>
                        <div class="summary-row"><span>Ville</span><span>{{ data.step1?.city || '—' }}</span></div>
                    </div>

                    <div class="summary-section" v-if="domain">
                        <h5><i class="bi bi-grid me-2"></i>Domaine d'activité</h5>
                        <div class="summary-row">
                            <span>{{ domain.label }}</span>
                            <span class="badge bg-primary">{{ domain.modules_count }} modules</span>
                        </div>
                    </div>

                    <div class="summary-section">
                        <h5><i class="bi bi-credit-card me-2"></i>Abonnement</h5>
                        <div class="summary-row"><span>Plan</span><span class="fw-semibold">{{ data.step3?.plan === 'annuel' ? 'Annuel' : 'Mensuel' }}</span></div>
                    </div>

                    <div class="summary-section">
                        <h5><i class="bi bi-person me-2"></i>Administrateur</h5>
                        <div class="summary-row"><span>Nom</span><span class="fw-semibold">{{ data.step4?.admin_name }}</span></div>
                        <div class="summary-row"><span>Email</span><span>{{ data.step4?.admin_email }}</span></div>
                    </div>
                </div>

                <div v-if="error" class="alert alert-danger mt-3">{{ error }}</div>

                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                    <a href="/register/company/step/4" class="btn btn-outline-secondary">← Retour</a>
                    <button @click="submitStep5" class="btn btn-success px-5" :disabled="submitting">
                        <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
                        <i class="bi bi-check-lg me-1"></i> Créer mon espace
                    </button>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';

const submitting = ref(false);
const error = ref(null);
const created = ref(null);
const domain = ref(null);

const data = reactive({
    step1: null,
    step2: null,
    step3: null,
    step4: null,
});

const csrfToken = document.querySelector('meta[name=csrf-token]')?.content || '';

onMounted(async () => {
    // Charger les données de session
    try {
        const res = await fetch('/api/register/company/data', { headers: { Accept: 'application/json' } });
        if (res.ok) {
            const sessionData = await res.json();
            data.step1 = sessionData.step1 || null;
            data.step2 = sessionData.step2 || null;
            data.step3 = sessionData.step3 || null;
            data.step4 = sessionData.step4 || null;

            if (data.step2?.domain_id) {
                const dRes = await fetch(`/api/register/domains/${data.step2.domain_id}`, { headers: { Accept: 'application/json' } });
                if (dRes.ok) domain.value = await dRes.json();
            }
        }
    } catch (e) {
        console.warn('Erreur chargement données:', e);
    }
});

async function submitStep5() {
    submitting.value = true;
    error.value = null;

    try {
        const res = await fetch('/register/company/step/5', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
            },
        });

        const result = await res.json();

        if (!res.ok) {
            if (result.reset) {
                window.location.href = '/register/company/step/1';
                return;
            }
            error.value = result.message || 'Erreur lors de la création.';
            return;
        }

        if (result.success) {
            created.value = result.company;
            if (result.redirect) {
                setTimeout(() => { window.location.href = result.redirect; }, 2000);
            }
        }
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
    max-width: 680px;
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
.summary-grid { display: grid; gap: 20px; }
.summary-section {
    background: #f8fafc;
    border-radius: 12px;
    padding: 16px 20px;
}
.summary-section h5 { font-size: 14px; font-weight: 700; margin-bottom: 12px; color: var(--gel-dark); }
.summary-row {
    display: flex; justify-content: space-between;
    font-size: 13px; padding: 4px 0;
    border-bottom: 1px solid #e9ecef;
}
.summary-row:last-child { border-bottom: none; }
</style>
