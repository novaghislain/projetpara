<template>
    <div class="onboarding-wrapper">
        <div class="onboarding-card">
            <div class="text-center mb-4">
                <div class="step-indicator mb-3">
                    <span class="step-dot active">1</span>
                    <span class="step-line"></span>
                    <span class="step-dot">2</span>
                    <span class="step-line"></span>
                    <span class="step-dot">3</span>
                    <span class="step-line"></span>
                    <span class="step-dot">4</span>
                    <span class="step-line"></span>
                    <span class="step-dot">5</span>
                </div>
                <h2 class="fw-bold" style="color:var(--gel-dark);">Informations de l'entreprise</h2>
                <p class="text-muted">Étape 1 sur 5 — Renseignez les informations de votre structure</p>
            </div>

            <form @submit.prevent="submitStep1">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Nom de l'entreprise <span class="text-danger">*</span></label>
                        <input v-model="form.company_name" type="text" class="form-control" placeholder="Ex: Ets. Koudjo & Fils" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Forme juridique</label>
                        <input v-model="form.legal_form" type="text" class="form-control" placeholder="SARL, SA, EURL...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">RCCM</label>
                        <input v-model="form.rccm" type="text" class="form-control" placeholder="RB/XXX/2025...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">IFU</label>
                        <input v-model="form.ifu" type="text" class="form-control" placeholder="0202123456789">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Adresse</label>
                        <input v-model="form.address" type="text" class="form-control" placeholder="Adresse complète">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ville</label>
                        <input v-model="form.city" type="text" class="form-control" placeholder="Cotonou">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Téléphone</label>
                        <input v-model="form.phone" type="tel" class="form-control" placeholder="+229 01 XX XX XX">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input v-model="form.email" type="email" class="form-control" placeholder="contact@entreprise.bj" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Site web</label>
                        <input v-model="form.website" type="url" class="form-control" placeholder="https://">
                    </div>
                </div>

                <div v-if="error" class="alert alert-danger mt-3 mb-0">{{ error }}</div>

                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                    <a href="/login" class="btn btn-outline-secondary">Retour</a>
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
    company_name: '',
    legal_form: '',
    rccm: '',
    ifu: '',
    address: '',
    city: '',
    phone: '',
    email: '',
    website: '',
});

const csrfToken = document.querySelector('meta[name=csrf-token]')?.content || '';

async function submitStep1() {
    submitting.value = true;
    error.value = null;

    try {
        const res = await fetch('/register/company/step/1', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
            },
            body: JSON.stringify(form),
        });

        const data = await res.json();

        if (!res.ok) {
            error.value = data.message || 'Erreur lors de l\'enregistrement.';
            return;
        }

        if (data.success) {
            window.location.href = `/register/company/step/${data.next_step}`;
        }
    } catch (e) {
        error.value = 'Une erreur réseau est survenue.';
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
.step-indicator {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0;
}
.step-dot {
    width: 36px; height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
    background: #e9ecef;
    color: #6c757d;
    transition: all 0.3s;
}
.step-dot.active { background: var(--gel-primary); color: #fff; }
.step-dot.completed { background: #198754; color: #fff; }
.step-line {
    width: 60px; height: 3px;
    background: #e9ecef;
    margin: 0 4px;
    border-radius: 2px;
}
.step-line.active { background: var(--gel-primary); }
</style>
