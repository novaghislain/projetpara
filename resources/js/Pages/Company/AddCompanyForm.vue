<template>
    <div class="d-flex align-items-center justify-content-center py-5"
         style="min-height: 80vh; background: linear-gradient(135deg, #000 0%, #1a1a2e 100%);">
        <div class="card shadow-lg border-0" style="max-width: 460px; width: 100%; border-radius: 16px;">
            <div class="card-body p-4 p-md-5">

                <div class="text-center mb-4">
                    <div class="mx-auto d-flex align-items-center justify-content-center mb-3"
                         style="width: 64px; height: 64px; background: #FF7900; border-radius: 14px;">
                        <i class="bi bi-building-add fs-2 text-dark"></i>
                    </div>
                    <h3 class="fw-bold mb-1">Ajouter une entreprise</h3>
                    <p class="text-muted small mb-0">
                        Saisissez le code entreprise fourni par votre nouveau client
                    </p>
                </div>

                <!-- Success -->
                <div v-if="success" class="text-center py-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 fw-bold">Entreprise ajoutée !</h5>
                    <p class="text-muted small mb-3">{{ success }}</p>
                    <a href="/context" class="btn btn-primary btn-sm">
                        <i class="bi bi-arrow-right me-1"></i>Choisir une entreprise
                    </a>
                </div>

                <!-- Form -->
                <form v-else @submit.prevent="submitForm">
                    <div v-if="error" class="alert alert-danger py-2 small">{{ error }}</div>

                    <div class="mb-4">
                        <label class="form-label fw-medium">Code entreprise *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-building"></i></span>
                            <input v-model="code"
                                   class="form-control"
                                   placeholder="Ex: ENT-A7K2M9"
                                   maxlength="20"
                                   style="text-transform: uppercase;"
                                   required>
                            <button type="button"
                                    class="btn btn-outline-secondary"
                                    @click="lookupCode"
                                    :disabled="lookingUp || !code.trim()"
                                    title="Vérifier">
                                <i v-if="lookingUp" class="spinner-border spinner-border-sm"></i>
                                <i v-else class="bi bi-search"></i>
                            </button>
                        </div>
                        <div v-if="lookupResult" class="mt-1 small" :class="lookupResult.valid ? 'text-success' : 'text-danger'">
                            <i v-if="lookupResult.valid" class="bi bi-check-circle-fill me-1"></i>
                            <i v-else class="bi bi-exclamation-triangle-fill me-1"></i>
                            {{ lookupResult.message }}
                            <strong v-if="lookupResult.companyName">({{ lookupResult.companyName }})</strong>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold" :disabled="sending">
                        <span v-if="sending" class="spinner-border spinner-border-sm me-2"></span>
                        <i v-else class="bi bi-plus-lg me-2"></i>
                        {{ sending ? 'Ajout en cours...' : 'Ajouter cette entreprise' }}
                    </button>

                    <p class="text-center mt-3 mb-0">
                        <a href="/context" class="small text-muted">
                            <i class="bi bi-arrow-left me-1"></i>Retour à la sélection
                        </a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'

const code = ref('')
const error = ref(null)
const sending = ref(false)
const lookingUp = ref(false)
const lookupResult = ref(null)
const success = ref(null)

async function lookupCode() {
    const c = code.value.trim().toUpperCase()
    if (!c) return
    lookingUp.value = true
    lookupResult.value = null
    try {
        const res = await fetch(`/api/company/code/lookup?code=${encodeURIComponent(c)}`)
        const data = await res.json()
        if (data.valid) {
            lookupResult.value = { valid: true, message: 'Code valide', companyName: data.company.name }
        } else {
            lookupResult.value = { valid: false, message: data.message || 'Code invalide', companyName: null }
        }
    } catch {
        lookupResult.value = { valid: false, message: 'Erreur de connexion', companyName: null }
    } finally {
        lookingUp.value = false
    }
}

async function submitForm() {
    sending.value = true
    error.value = null
    try {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content
        const res = await fetch('/api/company/add-company', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ client_code: code.value.toUpperCase() }),
        })
        const data = await res.json()
        if (data.success) {
            success.value = data.message
        } else {
            error.value = data.message || data.errors?.client_code?.[0] || 'Erreur'
        }
    } catch {
        error.value = 'Erreur de connexion au serveur'
    } finally {
        sending.value = false
    }
}
</script>
