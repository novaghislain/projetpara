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
                    <span class="step-line active"></span>
                    <span class="step-dot active">4</span>
                    <span class="step-line"></span>
                    <span class="step-dot">5</span>
                </div>
                <h2 class="fw-bold" style="color:var(--gel-dark);">Compte administrateur</h2>
                <p class="text-muted">Étape 4 sur 5 — Créez le compte qui gérera votre entreprise sur la plateforme</p>
            </div>

            <form @submit.prevent="submitStep4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Nom complet <span class="text-danger">*</span></label>
                        <input v-model="form.admin_name" type="text" class="form-control" placeholder="Jean Kouassi" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input v-model="form.admin_email" type="email" class="form-control" placeholder="admin@entreprise.bj" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Téléphone</label>
                        <input v-model="form.admin_phone" type="tel" class="form-control" placeholder="+229 01 XX XX XX">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Mot de passe <span class="text-danger">*</span></label>
                        <input v-model="form.password" type="password" class="form-control" minlength="8" required>
                        <small class="text-muted">Minimum 8 caractères</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Confirmer le mot de passe <span class="text-danger">*</span></label>
                        <input v-model="form.password_confirmation" type="password" class="form-control" required>
                    </div>
                </div>

                <div v-if="error" class="alert alert-danger mt-3 mb-0">{{ error }}</div>

                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                    <a href="/register/company/step/3" class="btn btn-outline-secondary">← Retour</a>
                    <button type="submit" class="btn btn-primary px-4" :disabled="submitting">
                        <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
                        Continuer →
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';

const submitting = ref(false);
const error = ref(null);

const form = reactive({
    admin_name: '',
    admin_email: '',
    admin_phone: '',
    password: '',
    password_confirmation: '',
});

const csrfToken = document.querySelector('meta[name=csrf-token]')?.content || '';

async function submitStep4() {
    if (form.password !== form.password_confirmation) {
        error.value = 'Les mots de passe ne correspondent pas.';
        return;
    }

    submitting.value = true;
    error.value = null;

    try {
        const res = await fetch('/register/company/step/4', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
            },
            body: JSON.stringify(form),
        });

        const data = await res.json();
        if (!res.ok) { error.value = data.message || data?.errors?.admin_email?.[0] || 'Erreur'; return; }
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
    max-width: 640px;
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
</style>
