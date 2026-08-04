<template>
    <div class="min-vh-100 d-flex align-items-center justify-content-center"
         style="background: linear-gradient(135deg, #000 0%, #1a1a2e 100%);">
        <div class="card shadow-lg border-0" style="max-width: 480px; width: 100%; border-radius: 16px;">
            <div class="card-body p-4 p-md-5">

                <!-- Logo -->
                <div class="text-center mb-4">
                    <div class="mx-auto d-flex align-items-center justify-content-center mb-3"
                         style="width: 64px; height: 64px; background: #FF7900; border-radius: 14px;">
                        <i class="bi bi-person-plus fs-2 text-dark"></i>
                    </div>
                    <h3 class="fw-bold mb-1">Inscription entreprise</h3>
                    <p class="text-muted small mb-0">
                        Renseignez le code fourni par votre entreprise
                    </p>
                </div>

                <!-- Success -->
                <div v-if="success" class="text-center py-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 fw-bold">Inscription réussie !</h5>
                    <p class="text-muted small">{{ success }}</p>
                    <a :href="redirectUrl" class="btn btn-primary btn-sm mt-2">
                        Accéder à mon espace
                    </a>
                </div>

                <!-- Form -->
                <form v-else @submit.prevent="submitForm">
                    <!-- Global error -->
                    <div v-if="error" class="alert alert-danger py-2 small">{{ error }}</div>

                    <!-- Company code -->
                    <div class="mb-3">
                        <label class="form-label fw-medium">Code entreprise *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-building"></i></span>
                            <input v-model="form.client_code"
                                   class="form-control"
                                   placeholder="Ex: ENT-A7K2M9"
                                   maxlength="20"
                                   style="text-transform: uppercase;"
                                   :class="{ 'is-invalid': errors.client_code }"
                                   @input="onCodeInput"
                                   required>
                            <button type="button"
                                    class="btn btn-outline-secondary"
                                    @click="lookupCode"
                                    :disabled="lookingUp || !form.client_code.trim()"
                                    title="Vérifier le code">
                                <i v-if="lookingUp" class="spinner-border spinner-border-sm"></i>
                                <i v-else class="bi bi-search"></i>
                            </button>
                        </div>
                        <div v-if="errors.client_code" class="invalid-feedback d-block">{{ errors.client_code }}</div>
                        <!-- Code lookup result -->
                        <div v-if="codeValid" class="mt-1 small text-success">
                            <i class="bi bi-check-circle-fill me-1"></i>Entreprise : <strong>{{ companyName }}</strong>
                        </div>
                        <div v-if="codeValid === false" class="mt-1 small text-danger">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>Code invalide
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- Name -->
                    <div class="mb-3">
                        <label class="form-label fw-medium">Nom complet *</label>
                        <input v-model="form.name" class="form-control" :class="{ 'is-invalid': errors.name }" required>
                        <div v-if="errors.name" class="invalid-feedback">{{ errors.name }}</div>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label class="form-label fw-medium">Email *</label>
                        <input v-model="form.email" type="email" class="form-control" :class="{ 'is-invalid': errors.email }" required>
                        <div v-if="errors.email" class="invalid-feedback">{{ errors.email }}</div>
                    </div>

                    <!-- Phone -->
                    <div class="mb-3">
                        <label class="form-label fw-medium">Téléphone</label>
                        <input v-model="form.phone" type="tel" class="form-control" placeholder="+229 01 XX XX XX XX">
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label class="form-label fw-medium">Mot de passe *</label>
                        <input v-model="form.password" type="password" class="form-control" :class="{ 'is-invalid': errors.password }" required minlength="8">
                        <div v-if="errors.password" class="invalid-feedback">{{ errors.password }}</div>
                    </div>

                    <!-- Password confirmation -->
                    <div class="mb-4">
                        <label class="form-label fw-medium">Confirmer le mot de passe *</label>
                        <input v-model="form.password_confirmation" type="password" class="form-control" required>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold" :disabled="sending">
                        <span v-if="sending" class="spinner-border spinner-border-sm me-2"></span>
                        <i v-else class="bi bi-check-lg me-2"></i>
                        {{ sending ? 'Inscription en cours...' : 'Créer mon compte' }}
                    </button>

                    <p class="text-center mt-3 mb-0 small text-muted">
                        Déjà un compte ?
                        <a href="/login" class="text-primary fw-medium">Connectez-vous</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</template>

/*
 * ClientRegister.vue - Page d'inscription entreprise (portail client).
 *
 * Role     : Permet a un nouvel utilisateur de creer un compte lie a une
 *            entreprise existante en utilisant un "code entreprise" fourni
 *            par son employeur. Inscription en une seule etape (pas de wizard).
 * Props    : Aucune (composant autonome).
 * Emits    : Aucun (redirection HTTP classique apres succes).
 * Store    : Aucun (requetes fetch directes).
 *
 * Fonctionnalites :
 * - Saisie du code entreprise avec verification instantanee via API
 *   (appel GET /api/company/code/lookup) et auto-recherche apres 500 ms
 * - Champs : nom, email, telephone, mot de passe + confirmation
 * - Validation cote client avec affichage des erreurs champ par champ
 * - Appel POST /inscription-entreprise avec jeton CSRF
 * - Affichage d'un etat de succes apres creation du compte
 * - Redirection vers le dashboard ou l'URL fournie par le serveur
 *
 * Flux type :
 *   1. L'utilisateur saisit le code entreprise -> lookup automatique
 *   2. Si le code est valide, le nom de l'entreprise s'affiche
 *   3. L'utilisateur remplit les champs du formulaire
 *   4. Soumission POST vers /inscription-entreprise
 *   5. Succes -> message de reussite + redirection
 *   6. Erreur -> affichage des erreurs de validation
 */

import { ref, reactive } from 'vue'

// --- Etat du formulaire ---
const form = reactive({
    client_code: '',
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
})

// --- Etats reactifs ---
const errors = reactive({})        // Erreurs de validation retournees par le serveur
const error = ref(null)            // Message d'erreur general
const sending = ref(false)         // Indicateur d'envoi du formulaire
const lookingUp = ref(false)       // Indicateur de recherche du code entreprise
const codeValid = ref(null)        // true = code valide, false = invalide, null = pas encore verifie
const companyName = ref('')        // Nom de l'entreprise associee au code
const success = ref(null)          // Message de succes apres inscription
const redirectUrl = ref('/dashboard')  // URL de redirection apres succes

let lookupTimeout = null           // Timer pour l'auto-recherche differee du code

/**
 * lookupCode - Verifie la validite du code entreprise aupres de l'API.
 * Appele manuellement via le bouton "Verifier" ou automatiquement apres
 * 500 ms d'inactivite sur le champ du code.
 */
async function lookupCode() {
    const code = form.client_code.trim().toUpperCase()
    if (!code) return
    lookingUp.value = true
    codeValid.value = null
    companyName.value = ''
    try {
        const res = await fetch(`/api/company/code/lookup?code=${encodeURIComponent(code)}`)
        const data = await res.json()
        if (data.valid) {
            codeValid.value = true
            companyName.value = data.company.name
            errors.client_code = null
        } else {
            codeValid.value = false
            errors.client_code = data.message || 'Code invalide'
        }
    } catch {
        codeValid.value = null
        errors.client_code = 'Erreur de verification du code'
    } finally {
        lookingUp.value = false
    }
}

/**
 * onCodeInput - Nettoie la saisie du code (majuscules) et declenche
 * une auto-recherche differee de 500 ms si la longueur >= 6 caracteres.
 */
function onCodeInput() {
    form.client_code = form.client_code.toUpperCase()
    codeValid.value = null
    companyName.value = ''
    clearTimeout(lookupTimeout)
    // Auto-recherche apres 500 ms d'inactivite
    if (form.client_code.trim().length >= 6) {
        lookupTimeout = setTimeout(lookupCode, 500)
    }
}

/**
 * submitForm - Soumet le formulaire d'inscription au serveur.
 * En cas de succes, affiche le message de confirmation et redirige.
 * En cas d'erreur, affiche les erreurs de validation champ par champ.
 */
async function submitForm() {
    sending.value = true
    error.value = null
    Object.keys(errors).forEach(k => errors[k] = null)

    try {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content
        const res = await fetch('/inscription-entreprise', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(form),
        })
        const data = await res.json()
        if (data.success) {
            success.value = data.message
            redirectUrl.value = data.redirect || '/dashboard'
        } else {
            if (data.errors) {
                Object.assign(errors, data.errors)
            }
            error.value = data.message || 'Erreur lors de l\'inscription'
        }
    } catch (e) {
        error.value = 'Erreur de connexion au serveur'
    } finally {
        sending.value = false
    }
}
</script>
