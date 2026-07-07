<template>
    <div class="card border-0 shadow-sm h-100">
        <div class="card-body p-3">
            <h6 class="fw-bold mb-3" style="border-bottom: 2px solid #ff7900; display: inline-block; padding-bottom: 0.2rem;">
                <i class="bi bi-qr-code me-2" style="color:#FF7900;"></i>Code Entreprise
            </h6>

            <!-- Loading -->
            <div v-if="loading" class="text-center py-3">
                <div class="spinner-border spinner-border-sm text-primary"></div>
            </div>

            <!-- Error -->
            <div v-else-if="loadError" class="alert alert-danger py-2 small mb-0">{{ loadError }}</div>

            <!-- Content -->
            <div v-else-if="code" class="text-center">
                <!-- Code display -->
                <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                    <span class="badge bg-dark px-3 py-2 fs-6 fw-bold font-monospace" style="letter-spacing: 1px;">
                        {{ code }}
                    </span>
                    <button class="btn btn-sm btn-outline-secondary" @click="copyCode" title="Copier le code">
                        <i class="bi" :class="copied ? 'bi-check-lg text-success' : 'bi-clipboard'"></i>
                    </button>
                </div>

                <!-- QR code -->
                <div v-if="qrDataUri" class="mb-2">
                    <img :src="qrDataUri" alt="QR Code entreprise"
                         style="width: 140px; height: 140px; border-radius: 8px; border: 1px solid #eee;">
                </div>

                <p class="text-muted small mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Transmettez ce code à vos clients pour qu'ils puissent créer leur compte et être rattachés à votre entreprise.
                </p>

                <!-- Regenerate button (admin only) -->
                <button v-if="canRegenerate && !showConfirm"
                        class="btn btn-outline-warning btn-sm mt-3"
                        @click="showConfirm = true">
                    <i class="bi bi-arrow-repeat me-1"></i>Régénérer le code
                </button>

                <!-- Confirm regeneration -->
                <div v-if="showConfirm" class="mt-3 p-2 rounded-3" style="background: #fff3cd;">
                    <p class="small text-warning-emphasis mb-2 fw-semibold">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Attention : l'ancien code deviendra invalide immédiatement.
                        Les clients déjà rattachés ne seront pas affectés.
                    </p>
                    <div class="d-flex gap-2 justify-content-center">
                        <button class="btn btn-sm btn-warning" @click="regenerate" :disabled="regenerating">
                            <span v-if="regenerating" class="spinner-border spinner-border-sm me-1"></span>
                            Confirmer la régénération
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" @click="showConfirm = false">Annuler</button>
                    </div>
                </div>
            </div>

            <!-- No code (fallback) -->
            <div v-else class="text-center text-muted py-3">
                <i class="bi bi-qr-code fs-2 d-block mb-2"></i>
                <small>Aucun code attribué</small>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
    clientId: { type: [Number, String], required: true },
})

const code = ref(null)
const qrDataUri = ref(null)
const canRegenerate = ref(false)
const loading = ref(true)
const loadError = ref(null)
const copied = ref(false)
const showConfirm = ref(false)
const regenerating = ref(false)

async function fetchCode() {
    loading.value = true
    loadError.value = null
    try {
        const res = await fetch(`/api/company/${props.clientId}/code`, {
            headers: { 'Accept': 'application/json' },
        })
        if (!res.ok) throw new Error('Erreur lors du chargement')
        const data = await res.json()
        code.value = data.client_code
        qrDataUri.value = data.qr_data_uri
        canRegenerate.value = data.can_regenerate
    } catch (e) {
        loadError.value = e.message
    } finally {
        loading.value = false
    }
}

async function copyCode() {
    if (!code.value) return
    try {
        await navigator.clipboard.writeText(code.value)
        copied.value = true
        setTimeout(() => { copied.value = false }, 2000)
    } catch {
        // Fallback
        const ta = document.createElement('textarea')
        ta.value = code.value
        document.body.appendChild(ta)
        ta.select()
        document.execCommand('copy')
        document.body.removeChild(ta)
        copied.value = true
        setTimeout(() => { copied.value = false }, 2000)
    }
}

async function regenerate() {
    regenerating.value = true
    try {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content
        const res = await fetch(`/api/company/${props.clientId}/code/regenerate`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
        const data = await res.json()
        if (data.success) {
            code.value = data.client_code
            qrDataUri.value = data.qr_data_uri
            showConfirm.value = false
        } else {
            alert(data.message || 'Erreur')
        }
    } catch (e) {
        alert('Erreur lors de la régénération')
    } finally {
        regenerating.value = false
    }
}

onMounted(fetchCode)
</script>
