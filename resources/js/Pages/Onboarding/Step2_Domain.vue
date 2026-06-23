<template>
    <div class="onboarding-wrapper">
        <div class="onboarding-card">
            <div class="text-center mb-4">
                <div class="step-indicator mb-3">
                    <span class="step-dot completed">1</span>
                    <span class="step-line active"></span>
                    <span class="step-dot active">2</span>
                    <span class="step-line"></span>
                    <span class="step-dot">3</span>
                    <span class="step-line"></span>
                    <span class="step-dot">4</span>
                    <span class="step-line"></span>
                    <span class="step-dot">5</span>
                </div>
                <h2 class="fw-bold" style="color:var(--gel-dark);">Domaine d'activité</h2>
                <p class="text-muted">Étape 2 sur 5 — Sélectionnez le domaine qui correspond à votre activité</p>
            </div>

            <div v-if="loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Chargement des domaines...</p>
            </div>

            <div v-else class="domain-grid">
                <div
                    v-for="d in domains"
                    :key="d.id"
                    class="domain-card"
                    :class="{ selected: selectedId === d.id }"
                    @click="selectDomain(d)"
                >
                    <div class="domain-icon">
                        <i :class="'bi ' + d.icon"></i>
                    </div>
                    <h5 class="domain-label">{{ d.label }}</h5>
                    <p class="domain-desc">{{ d.description }}</p>
                    <div class="domain-badge">
                        <i class="bi bi-check-lg"></i>
                    </div>
                </div>
            </div>

            <div v-if="error" class="alert alert-danger mt-3">{{ error }}</div>

            <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                <a :href="`/register/company/step/1`" class="btn btn-outline-secondary">← Retour</a>
                <button @click="submitStep2" class="btn btn-primary px-4" :disabled="!selectedId || submitting">
                    <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
                    Continuer →
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const domains = ref([]);
const selectedId = ref(null);
const selectedCode = ref(null);
const loading = ref(true);
const submitting = ref(false);
const error = ref(null);

const csrfToken = document.querySelector('meta[name=csrf-token]')?.content || '';

onMounted(async () => {
    try {
        const res = await fetch('/api/register/domains', { headers: { Accept: 'application/json' } });
        if (res.ok) domains.value = await res.json();
    } catch (e) {
        error.value = 'Erreur chargement';
    } finally {
        loading.value = false;
    }
});

function selectDomain(d) {
    selectedId.value = d.id;
    selectedCode.value = d.code;
}

async function submitStep2() {
    if (!selectedId.value) return;
    submitting.value = true;
    error.value = null;

    try {
        const res = await fetch('/register/company/step/2', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
            },
            body: JSON.stringify({
                domain_id: selectedId.value,
                domain_code: selectedCode.value,
            }),
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
    max-width: 820px;
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
    transition: all 0.3s;
}
.step-dot.active { background: var(--gel-primary); color: #fff; }
.step-dot.completed { background: #198754; color: #fff; }
.step-line {
    width: 60px; height: 3px;
    background: #e9ecef; margin: 0 4px; border-radius: 2px;
}
.step-line.active { background: var(--gel-primary); }
.domain-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
    gap: 16px;
}
.domain-card {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 20px 16px;
    cursor: pointer;
    transition: all 0.25s;
    text-align: center;
    position: relative;
}
.domain-card:hover { border-color: var(--gel-primary); box-shadow: 0 4px 20px rgba(255,121,0,0.12); }
.domain-card.selected { border-color: var(--gel-primary); background: rgba(255,121,0,0.04); }
.domain-card.selected .domain-badge { display: flex; }
.domain-icon { font-size: 32px; color: var(--gel-primary); margin-bottom: 12px; }
.domain-label { font-size: 14px; font-weight: 700; color: var(--gel-dark); margin-bottom: 6px; }
.domain-desc { font-size: 12px; color: #6c757d; margin: 0; line-height: 1.5; }
.domain-badge {
    display: none;
    position: absolute; top: 8px; right: 8px;
    width: 22px; height: 22px;
    border-radius: 50%;
    background: var(--gel-primary); color: #fff;
    align-items: center; justify-content: center;
    font-size: 12px;
}
</style>
