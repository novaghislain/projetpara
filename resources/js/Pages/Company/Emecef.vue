<script setup>
import { ref, computed, onMounted } from 'vue'
import CompanyLayout from '../../Layouts/CompanyLayout.vue'

const loading = ref(true)
const saving = ref(false)
const testing = ref(false)
const error = ref('')
const success = ref('')

const config = ref({
    configured: false,
    emecef_nim: '',
    emecef_is_active: false,
    has_password: false,
    updated_at: null,
})

const form = ref({
    emecef_nim: '',
    emecef_password: '',
    emecef_password_confirmation: '',
    emecef_is_active: true,
})

const showPassword = ref(false)
const showRemoveConfirm = ref(false)

const csrfToken = computed(() =>
    document.querySelector('meta[name=csrf-token]')?.content || ''
)

async function loadStatus() {
    loading.value = true
    error.value = ''
    try {
        const res = await fetch('/api/company/emecef/status', {
            headers: { Accept: 'application/json' },
        })
        if (!res.ok) throw new Error('Erreur chargement')
        config.value = await res.json()
        // Pré-remplir le formulaire
        form.value.emecef_nim = config.value.emecef_nim || ''
        form.value.emecef_is_active = config.value.emecef_is_active
    } catch (e) {
        error.value = e.message
    } finally {
        loading.value = false
    }
}

async function saveConfig() {
    error.value = ''
    success.value = ''

    if (form.value.emecef_password !== form.value.emecef_password_confirmation) {
        error.value = 'Les mots de passe ne correspondent pas.'
        return
    }

    saving.value = true
    try {
        const res = await fetch('/api/company/emecef/configure', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.value,
                Accept: 'application/json',
            },
            body: JSON.stringify({
                emecef_nim: form.value.emecef_nim,
                emecef_password: form.value.emecef_password,
                emecef_is_active: form.value.emecef_is_active,
            }),
        })
        const data = await res.json()
        if (!res.ok) throw new Error(data.message || 'Erreur sauvegarde')
        success.value = data.message
        form.value.emecef_password = ''
        form.value.emecef_password_confirmation = ''
        await loadStatus()
        setTimeout(() => { success.value = '' }, 4000)
    } catch (e) {
        error.value = e.message
    } finally {
        saving.value = false
    }
}

async function testConnection() {
    testing.value = true
    error.value = ''
    success.value = ''
    try {
        const res = await fetch('/api/company/emecef/test', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken.value,
                Accept: 'application/json',
            },
        })
        const data = await res.json()
        if (!res.ok) throw new Error(data.message || 'Erreur')
        success.value = data.message
        setTimeout(() => { success.value = '' }, 4000)
    } catch (e) {
        error.value = e.message
    } finally {
        testing.value = false
    }
}

async function removeConfig() {
    error.value = ''
    success.value = ''
    try {
        const res = await fetch('/api/company/emecef', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken.value,
                Accept: 'application/json',
            },
        })
        const data = await res.json()
        if (!res.ok) throw new Error(data.message || 'Erreur')
        success.value = data.message
        showRemoveConfirm.value = false
        await loadStatus()
        setTimeout(() => { success.value = '' }, 4000)
    } catch (e) {
        error.value = e.message
    }
}

onMounted(loadStatus)
</script>

<template>
    <CompanyLayout page-title="e-MECeF - DGI">
        <div class="isup-shell">
            <!-- ══ HEADER ══ -->
            <div class="isup-portal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="isup-portal-logo" style="background:#e8f5e9;">
                        <i class="bi-shield-check" style="color:#2e7d32;font-size:20px;"></i>
                    </div>
                    <div>
                        <div class="isup-portal-company">e-MECeF (DGI)</div>
                        <div class="isup-portal-sub">Configuration du compte fiscal électronique</div>
                    </div>
                </div>
            </div>

            <div class="p-3">
                <!-- Messages -->
                <div v-if="success" class="isup-alert-success mb-3">
                    <i class="bi-check-circle-fill me-2"></i>{{ success }}
                    <button class="isup-alert-close" @click="success = ''">&times;</button>
                </div>
                <div v-if="error" class="isup-alert-error mb-3">
                    <i class="bi-exclamation-triangle-fill me-2"></i>{{ error }}
                    <button class="isup-alert-close" @click="error = ''">&times;</button>
                </div>

                <!-- Loading -->
                <div v-if="loading" class="text-center py-5">
                    <div class="isup-spinner"></div>
                    <p style="font-size:13px;color:#888;margin-top:10px;">Chargement de la configuration...</p>
                </div>

                <template v-else>
                    <!-- ═══ STATUT CARD ═══ -->
                    <div class="emecef-status-card mb-4" :class="config.configured ? 'emecef-status--ok' : 'emecef-status--empty'">
                        <div class="emecef-status-icon">
                            <i :class="config.configured ? 'bi-shield-check' : 'bi-shield-exclamation'"></i>
                        </div>
                        <div class="emecef-status-text">
                            <div class="emecef-status-label">
                                {{ config.configured ? 'Compte e-MECeF configuré' : 'Compte e-MECeF non configuré' }}
                            </div>
                            <div class="emecef-status-desc">
                                <template v-if="config.configured">
                                    NIM : <strong>{{ config.emecef_nim }}</strong>
                                    <span v-if="config.updated_at" class="emecef-date">
                                        — Dernière mise à jour : {{ new Date(config.updated_at).toLocaleDateString('fr-FR') }}
                                    </span>
                                </template>
                                <template v-else>
                                    Configurez votre compte e-MECeF pour permettre l'émission de factures normalisées à la DGI.
                                </template>
                            </div>
                        </div>
                        <div v-if="config.configured" class="emecef-status-badge">
                            <span class="emecef-badge emecef-badge--active">Actif</span>
                        </div>
                    </div>

                    <!-- ═══ FORMULAIRE DE CONFIGURATION ═══ -->
                    <div class="isup-panel">
                        <div class="isup-panel-header">
                            <i class="bi-gear me-2" style="color:#FF7900;"></i>
                            {{ config.configured ? 'Modifier la configuration' : 'Configurer votre compte e-MECeF' }}
                        </div>
                        <div class="isup-panel-body">
                            <form @submit.prevent="saveConfig">
                                <div class="row g-3">
                                    <!-- NIM -->
                                    <div class="col-md-6">
                                        <label class="isup-label">
                                            <i class="bi-upc-scan me-1"></i>NIM e-MECeF
                                            <span style="color:#e53935;">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            class="isup-input"
                                            v-model="form.emecef_nim"
                                            placeholder="NIM fourni par la DGI"
                                            required
                                            maxlength="50"
                                        />
                                        <div style="font-size:11px;color:#888;margin-top:4px;">
                                            Numéro d'Identification Machine attribué par la DGI.
                                        </div>
                                    </div>

                                    <!-- Actif -->
                                    <div class="col-md-6 d-flex align-items-end pb-3">
                                        <label class="emecef-switch-label">
                                            <span class="isup-label d-inline-block me-3">Activer e-MECeF</span>
                                            <label class="emecef-switch">
                                                <input type="checkbox" v-model="form.emecef_is_active" />
                                                <span class="emecef-slider"></span>
                                            </label>
                                        </label>
                                    </div>

                                    <!-- Mot de passe -->
                                    <div class="col-md-6">
                                        <label class="isup-label">
                                            <i class="bi-lock me-1"></i>Mot de passe e-MECeF
                                            <span style="color:#e53935;">*</span>
                                        </label>
                                        <div class="emecef-password-wrap">
                                            <input
                                                :type="showPassword ? 'text' : 'password'"
                                                class="isup-input"
                                                v-model="form.emecef_password"
                                                placeholder="Mot de passe du compte DGI"
                                                required
                                                minlength="4"
                                            />
                                            <button type="button" class="emecef-password-toggle" @click="showPassword = !showPassword">
                                                <i :class="showPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
                                            </button>
                                        </div>
                                        <div style="font-size:11px;color:#888;margin-top:4px;">
                                            Le mot de passe est chiffré et ne peut pas être récupéré.
                                        </div>
                                    </div>

                                    <!-- Confirmation mot de passe -->
                                    <div class="col-md-6">
                                        <label class="isup-label">
                                            <i class="bi-lock-fill me-1"></i>Confirmer le mot de passe
                                            <span style="color:#e53935;">*</span>
                                        </label>
                                        <div class="emecef-password-wrap">
                                            <input
                                                :type="showPassword ? 'text' : 'password'"
                                                class="isup-input"
                                                v-model="form.emecef_password_confirmation"
                                                placeholder="Confirmer le mot de passe"
                                                required
                                                minlength="4"
                                                :class="{ 'isup-input--error': form.emecef_password && form.emecef_password_confirmation && form.emecef_password !== form.emecef_password_confirmation }"
                                            />
                                        </div>
                                    </div>

                                    <!-- Info sécurité -->
                                    <div class="col-12">
                                        <div class="emecef-security-note">
                                            <i class="bi-shield-lock me-2"></i>
                                            Ces identifiants sont utilisés exclusivement pour l'authentification auprès du système e-MECeF de la DGI.
                                            Ils sont chiffrés et ne sont jamais visibles par les comptables.
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex gap-2 mt-4 flex-wrap">
                                    <button type="submit" class="isup-btn-primary" :disabled="saving">
                                        <span v-if="saving" class="isup-spinner-sm me-1"></span>
                                        <i v-else class="bi-check-lg me-1"></i>
                                        {{ config.configured ? 'Mettre à jour' : 'Enregistrer' }}
                                    </button>

                                    <button
                                        v-if="config.configured"
                                        type="button"
                                        class="isup-btn-outline"
                                        :disabled="testing"
                                        @click="testConnection"
                                    >
                                        <span v-if="testing" class="isup-spinner-sm me-1" style="border-color:rgba(255,121,0,0.2);border-top-color:#FF7900;"></span>
                                        <i v-else class="bi-plug me-1"></i>
                                        Tester la connexion
                                    </button>

                                    <button
                                        v-if="config.configured"
                                        type="button"
                                        class="isup-btn-danger-outline"
                                        @click="showRemoveConfirm = true"
                                    >
                                        <i class="bi-trash me-1"></i>
                                        Supprimer la configuration
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- ═══ INFORMATIONS ═══ -->
                    <div class="isup-panel mt-3">
                        <div class="isup-panel-header">
                            <i class="bi-info-circle me-2" style="color:#FF7900;"></i>
                            À propos d'e-MECeF
                        </div>
                        <div class="isup-panel-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="emecef-info-card">
                                        <div class="emecef-info-icon" style="background:#e3f2fd;">
                                            <i class="bi-1-circle" style="color:#1565c0;"></i>
                                        </div>
                                        <div class="emecef-info-text">
                                            <strong>1. Obtenez votre NIM</strong>
                                            <p>Votre NIM (Numéro d'Identification Machine) est fourni par la DGI du Bénin lors de votre inscription au système e-MECeF.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="emecef-info-card">
                                        <div class="emecef-info-icon" style="background:#fff3e0;">
                                            <i class="bi-2-circle" style="color:#e65100;"></i>
                                        </div>
                                        <div class="emecef-info-text">
                                            <strong>2. Configurez votre mot de passe</strong>
                                            <p>Définissez un mot de passe sécurisé. Il sera utilisé pour autoriser les émissions de factures normalisées par votre comptable.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="emecef-info-card">
                                        <div class="emecef-info-icon" style="background:#e8f5e9;">
                                            <i class="bi-3-circle" style="color:#2e7d32;"></i>
                                        </div>
                                        <div class="emecef-info-text">
                                            <strong>3. Factures normalisées</strong>
                                            <p>Une fois configuré, votre comptable peut émettre des factures normalisées à la DGI en un clic depuis son tableau de bord.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- ═══ MODAL SUPPRESSION ═══ -->
            <div v-if="showRemoveConfirm" class="isup-modal-overlay" @click.self="showRemoveConfirm = false">
                <div class="isup-modal">
                    <div class="isup-modal-header" style="background:#b71c1c;">
                        <span><i class="bi-exclamation-triangle me-2"></i>Confirmer la suppression</span>
                        <button class="isup-modal-close" @click="showRemoveConfirm = false">&times;</button>
                    </div>
                    <div class="isup-modal-body">
                        <p>Êtes-vous sûr de vouloir supprimer la configuration e-MECeF ?</p>
                        <p style="font-size:13px;color:#888;">Les comptables ne pourront plus émettre de factures normalisées à la DGI tant que la configuration n'est pas rétablie.</p>
                    </div>
                    <div class="isup-modal-footer">
                        <button class="isup-btn-grey" @click="showRemoveConfirm = false">Annuler</button>
                        <button class="isup-btn-primary" style="background:#b71c1c;border-color:#b71c1c;" @click="removeConfig">
                            <i class="bi-trash me-1"></i>Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </CompanyLayout>
</template>

<style scoped>
/* ── Status card ── */
.emecef-status-card {
    display:flex; align-items:center; gap:16px;
    border-radius:12px; padding:20px 24px;
}
.emecef-status--ok {
    background:#e8f5e9; border:1px solid #a5d6a7;
}
.emecef-status--empty {
    background:#fff8e1; border:1px solid #ffe082;
}
.emecef-status-icon {
    width:48px; height:48px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-size:24px; flex-shrink:0;
}
.emecef-status--ok .emecef-status-icon { background:#c8e6c9; color:#2e7d32; }
.emecef-status--empty .emecef-status-icon { background:#ffecb3; color:#e65100; }
.emecef-status-text { flex:1; }
.emecef-status-label {
    font-size:15px; font-weight:700; color:#163A5E; margin-bottom:4px;
}
.emecef-status-desc { font-size:13px; color:#555; }
.emecef-date { color:#888; font-size:12px; }
.emecef-status-badge { flex-shrink:0; }
.emecef-badge {
    display:inline-block; padding:4px 12px; border-radius:50px;
    font-size:12px; font-weight:600;
}
.emecef-badge--active { background:#2e7d32; color:#fff; }

/* ── Password field ── */
.emecef-password-wrap {
    position:relative;
}
.emecef-password-toggle {
    position:absolute; right:8px; top:50%; transform:translateY(-50%);
    background:none; border:none; color:#888; cursor:pointer;
    padding:4px 8px; font-size:16px;
}
.emecef-password-toggle:hover { color:#FF7900; }

/* ── Toggle switch (reuse style from GEL) ── */
.emecef-switch-label {
    display:flex; align-items:center; gap:12px;
}
.emecef-switch {
    position:relative; display:inline-block;
    width:44px; height:24px; flex-shrink:0;
}
.emecef-switch input { opacity:0; width:0; height:0; }
.emecef-slider {
    position:absolute; cursor:pointer; inset:0;
    background:#d1d5db; border-radius:24px; transition:0.2s;
}
.emecef-slider::before {
    content:''; position:absolute; height:18px; width:18px;
    left:3px; bottom:3px; background:#fff; border-radius:50%; transition:0.2s;
}
.emecef-switch input:checked + .emecef-slider { background:#FF7900; }
.emecef-switch input:checked + .emecef-slider::before { transform:translateX(20px); }
.emecef-switch input:disabled + .emecef-slider { opacity:0.5; cursor:not-allowed; }

/* ── Security note ── */
.emecef-security-note {
    display:flex; align-items:center;
    background:#e3f2fd; border:1px solid #bbdefb; border-radius:8px;
    padding:12px 16px; font-size:12px; color:#1565c0;
}

/* ── Info cards ── */
.emecef-info-card {
    display:flex; gap:12px; padding:16px;
    background:#f8f9fa; border-radius:10px; border:1px solid #e9ecef;
    height:100%;
}
.emecef-info-icon {
    width:40px; height:40px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-size:18px; flex-shrink:0;
}
.emecef-info-text strong {
    display:block; font-size:13px; color:#163A5E; margin-bottom:4px;
}
.emecef-info-text p {
    font-size:12px; color:#666; margin:0; line-height:1.5;
}

/* ── Buttons ── */
.isup-btn-outline {
    display:inline-flex; align-items:center; gap:4px;
    padding:10px 20px; border:2px solid #FF7900; border-radius:0px;
    background:transparent; color:#FF7900; font-weight:700; font-size:13px;
    cursor:pointer; transition:all 0.15s;
}
.isup-btn-outline:hover { background:rgba(255,121,0,0.08); }
.isup-btn-outline:disabled { opacity:0.6; cursor:not-allowed; }
.isup-btn-danger-outline {
    display:inline-flex; align-items:center; gap:4px;
    padding:10px 20px; border:2px solid #e53935; border-radius:0px;
    background:transparent; color:#e53935; font-weight:700; font-size:13px;
    cursor:pointer; transition:all 0.15s;
}
.isup-btn-danger-outline:hover { background:rgba(229,57,53,0.08); }
.isup-input--error { border-color:#e53935 !important; }

/* ── Spinner sm ── */
.isup-spinner-sm {
    display:inline-block; width:14px; height:14px;
    border:2px solid rgba(255,255,255,0.3); border-top-color:#fff;
    border-radius:50%; animation:isup-spin 0.6s linear infinite;
}
@keyframes isup-spin { to { transform:rotate(360deg); } }
</style>
