<script setup>
import { ref, computed, onMounted } from 'vue';

const statutLabels = {
    brouillon: 'Brouillon',
    actif: 'Actif',
    expire: 'Expiré',
    resilie: 'Résilié',
    renouvele: 'Renouvelé'
};

const contratId = computed(() => {
    const match = window.location.pathname.match(/\/dae\/contrats\/(\d+)/);
    return match ? match[1] : null;
});

const contrat = ref(null);
const loading = ref(true);
const error = ref(null);
const processing = ref(false);

const toast = ref({ show: false, message: '', type: 'success' });
let toastTimer = null;

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

function showToast(message, type = 'success') {
    clearTimeout(toastTimer);
    toast.value = { show: true, message, type };
    toastTimer = setTimeout(() => { toast.value.show = false; }, 4000);
}

function getStatutColor(statut) {
    const map = {
        brouillon: 'secondary',
        actif: 'success',
        expire: 'danger',
        resilie: 'dark',
        renouvele: 'info'
    };
    return map[statut] || 'secondary';
}

function getStatutIcon(statut) {
    const map = {
        brouillon: 'bi-pencil-square',
        actif: 'bi-check-circle',
        expire: 'bi-clock-history',
        resilie: 'bi-x-circle',
        renouvele: 'bi-arrow-repeat'
    };
    return map[statut] || 'bi-circle';
}

async function fetchContrat() {
    loading.value = true;
    error.value = null;
    try {
        const res = await fetch(`/dae/contrats/${contratId.value}`, {
            headers: { 'Accept': 'application/json' }
        });
        if (!res.ok) throw new Error('Erreur lors du chargement du contrat.');
        contrat.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
}

async function renouveler() {
    if (!confirm('Confirmer le renouvellement de ce contrat ? Un nouveau contrat sera créé à partir de celui-ci.')) return;
    processing.value = true;
    try {
        const res = await fetch(`/dae/contrats/${contratId.value}/renouveler`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        if (!res.ok) {
            const errData = await res.json().catch(() => ({}));
            throw new Error(errData.message || 'Erreur lors du renouvellement.');
        }
        const data = await res.json();
        showToast('Contrat renouvelé avec succès. Redirection...');
        setTimeout(() => {
            window.location.href = `/dae/contrats/${data.id || data.contrat?.id}`;
        }, 800);
    } catch (e) {
        showToast(e.message, 'danger');
    } finally {
        processing.value = false;
    }
}

function formatMontant(montant, devise) {
    if (montant === null || montant === undefined) return '-';
    const formatter = new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    return `${formatter.format(montant)} ${devise || 'EUR'}`;
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('fr-FR', { year: 'numeric', month: 'long', day: 'numeric' });
}

function formatDateTime(dateStr) {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('fr-FR', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}

onMounted(fetchContrat);
</script>

<template>
    <div class="dae-contrats-show">
        <!-- Toast -->
        <div v-if="toast.show"
             class="dae-toast"
             :class="'dae-toast-' + toast.type">
            <i :class="toast.type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'"
               class="me-2"></i>
            <span>{{ toast.message }}</span>
            <button class="dae-toast-close" @click="toast.show = false">&times;</button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="dae-loading">
            <div class="dae-spinner"></div>
            <p class="mt-3 text-muted small">Chargement du contrat...</p>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="dae-error">
            <i class="bi-exclamation-triangle-fill me-2"></i>
            {{ error }}
            <button class="btn btn-sm btn-outline-secondary ms-3" @click="fetchContrat">
                <i class="bi-arrow-clockwise me-1"></i>Réessayer
            </button>
        </div>

        <!-- Content -->
        <template v-else-if="contrat">
            <!-- Header -->
            <div class="dae-header">
                <div class="dae-header-left">
                    <a href="/dae/contrats" class="dae-back-btn">
                        <i class="bi-arrow-left"></i>
                        <span>Retour</span>
                    </a>
                    <div>
                        <h1 class="dae-title">
                            {{ contrat.titre || 'Sans titre' }}
                        </h1>
                        <span class="text-muted small">
                            <i class="bi-hash me-1"></i>
                            {{ contrat.reference || 'Sans référence' }}
                            <span class="mx-2">|</span>
                            <i class="bi-calendar3 me-1"></i>
                            Créé le {{ contrat.created_at ? formatDateTime(contrat.created_at) : '-' }}
                        </span>
                    </div>
                </div>
                <div class="dae-header-right">
                    <span class="dae-status-badge"
                          :class="'dae-status-' + (contrat.statut || 'brouillon')">
                        <i :class="getStatutIcon(contrat.statut)" class="me-1"></i>
                        {{ statutLabels[contrat.statut] || contrat.statut }}
                    </span>
                    <span v-if="contrat.renouvelable"
                          class="dae-badge dae-badge-renouvelable">
                        <i class="bi-arrow-repeat me-1"></i>Renouvelable
                    </span>
                </div>
            </div>

            <!-- Action Buttons Row -->
            <div class="dae-actions-bar">
                <a :href="'/dae/contrats/' + contrat.id + '/edit'"
                   class="btn btn-outline-primary btn-sm">
                    <i class="bi-pencil me-1"></i>Modifier
                </a>
                <button v-if="contrat.statut === 'actif' || contrat.statut === 'brouillon'"
                        class="btn btn-success btn-sm"
                        :disabled="processing"
                        @click="renouveler">
                    <i class="bi-arrow-repeat me-1"></i>Renouveler
                </button>
                <a v-if="contrat.fichier"
                   :href="'/dae/contrats/' + contrat.id + '/telecharger'"
                   class="btn btn-outline-secondary btn-sm">
                    <i class="bi-download me-1"></i>Télécharger
                </a>
            </div>

            <!-- Main Grid -->
            <div class="row g-4">
                <!-- Left Column -->
                <div class="col-lg-8">
                    <!-- Informations générales Card -->
                    <div class="dae-card">
                        <div class="dae-card-header">
                            <i class="bi-info-circle me-2"></i>Informations générales
                        </div>
                        <div class="dae-card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="dae-field">
                                        <span class="dae-field-label">Référence</span>
                                        <span class="dae-field-value">{{ contrat.reference || '-' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="dae-field">
                                        <span class="dae-field-label">Type de contrat</span>
                                        <span class="dae-field-value">{{ contrat.type_contrat || '-' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="dae-field">
                                        <span class="dae-field-label">Partie adverse</span>
                                        <span class="dae-field-value">{{ contrat.partie_adverse || '-' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="dae-field">
                                        <span class="dae-field-label">Client</span>
                                        <span class="dae-field-value">{{ contrat.client?.nom || contrat.client?.raison_sociale || contrat.client_id || '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Période et montant Card -->
                    <div class="dae-card">
                        <div class="dae-card-header">
                            <i class="bi-calendar-range me-2"></i>Période et montant
                        </div>
                        <div class="dae-card-body">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="dae-field">
                                        <span class="dae-field-label">Date de début</span>
                                        <span class="dae-field-value">{{ formatDate(contrat.date_debut) }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="dae-field">
                                        <span class="dae-field-label">Date de fin</span>
                                        <span class="dae-field-value">{{ formatDate(contrat.date_fin) }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="dae-field">
                                        <span class="dae-field-label">Durée (mois)</span>
                                        <span class="dae-field-value">{{ contrat.duree_mois ? contrat.duree_mois + ' mois' : '-' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="dae-field">
                                        <span class="dae-field-label">Montant</span>
                                        <span class="dae-field-value dae-field-value-large">{{ formatMontant(contrat.montant, contrat.devise) }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="dae-field">
                                        <span class="dae-field-label">Date de signature</span>
                                        <span class="dae-field-value">{{ formatDate(contrat.date_signature) }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="dae-field">
                                        <span class="dae-field-label">Date de préavis</span>
                                        <span class="dae-field-value">{{ formatDate(contrat.date_preavis) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Objet et conditions Card -->
                    <div class="dae-card" v-if="contrat.objet || contrat.conditions">
                        <div class="dae-card-header">
                            <i class="bi-file-text me-2"></i>Objet et conditions
                        </div>
                        <div class="dae-card-body">
                            <div class="dae-field mb-4" v-if="contrat.objet">
                                <span class="dae-field-label">Objet</span>
                                <span class="dae-field-value dae-field-value-large">{{ contrat.objet }}</span>
                            </div>
                            <div class="dae-field" v-if="contrat.conditions">
                                <span class="dae-field-label">Conditions</span>
                                <div class="dae-field-content">{{ contrat.conditions }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Tags Card -->
                    <div class="dae-card" v-if="contrat.tags && contrat.tags.length">
                        <div class="dae-card-header">
                            <i class="bi-tags me-2"></i>Tags
                        </div>
                        <div class="dae-card-body">
                            <span v-for="tag in contrat.tags" :key="tag"
                                  class="dae-tag">
                                {{ tag }}
                            </span>
                            <span v-if="!contrat.tags.length" class="text-muted small fst-italic">
                                Aucun tag
                            </span>
                        </div>
                    </div>

                    <!-- Fichier Card -->
                    <div class="dae-card" v-if="contrat.fichier">
                        <div class="dae-card-header">
                            <i class="bi-paperclip me-2"></i>Fichier
                        </div>
                        <div class="dae-card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="dae-file-icon">
                                        <i class="bi-file-earmark-text"></i>
                                    </div>
                                    <div>
                                        <div class="fw-medium">
                                            {{ contrat.fichier_original_name || contrat.fichier_nom || contrat.fichier.split('/').pop() || 'Fichier' }}
                                        </div>
                                        <span class="small text-muted">{{ contrat.fichier_taille ? (contrat.fichier_taille / 1024).toFixed(1) + ' Ko' : '' }}</span>
                                    </div>
                                </div>
                                <a :href="'/dae/contrats/' + contrat.id + '/telecharger'"
                                   target="_blank"
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="bi-download me-1"></i>Télécharger
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <!-- Statut Card -->
                    <div class="dae-card">
                        <div class="dae-card-header">
                            <i class="bi-info-square me-2"></i>Statut
                        </div>
                        <div class="dae-card-body">
                            <div class="dae-field mb-3">
                                <span class="dae-field-label">Statut actuel</span>
                                <span class="dae-field-value">
                                    <span class="dae-status-badge dae-status-badge-sm"
                                          :class="'dae-status-' + (contrat.statut || 'brouillon')">
                                        <i :class="getStatutIcon(contrat.statut)" class="me-1"></i>
                                        {{ statutLabels[contrat.statut] || contrat.statut }}
                                    </span>
                                </span>
                            </div>
                            <div class="dae-field mb-3" v-if="contrat.renouvelable">
                                <span class="dae-field-label">Renouvelable</span>
                                <span class="dae-field-value">
                                    <span class="badge bg-success">
                                        <i class="bi-check-circle me-1"></i>Oui
                                    </span>
                                </span>
                            </div>
                            <div class="dae-field mb-3" v-if="contrat.date_renouvellement">
                                <span class="dae-field-label">Date de renouvellement</span>
                                <span class="dae-field-value">{{ formatDate(contrat.date_renouvellement) }}</span>
                            </div>
                            <div class="dae-field" v-if="contrat.renouvele_le">
                                <span class="dae-field-label">Renouvelé le</span>
                                <span class="dae-field-value">{{ formatDate(contrat.renouvele_le) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Informations supplémentaires Card -->
                    <div class="dae-card">
                        <div class="dae-card-header">
                            <i class="bi-card-text me-2"></i>Informations
                        </div>
                        <div class="dae-card-body">
                            <div class="dae-field mb-3">
                                <span class="dae-field-label">Créé par</span>
                                <span class="dae-field-value">{{ contrat.user?.name || contrat.created_by || '-' }}</span>
                            </div>
                            <div class="dae-field mb-3" v-if="contrat.updated_by">
                                <span class="dae-field-label">Modifié par</span>
                                <span class="dae-field-value">{{ contrat.updated_by }}</span>
                            </div>
                            <div class="dae-field mb-3">
                                <span class="dae-field-label">Créé le</span>
                                <span class="dae-field-value">{{ formatDateTime(contrat.created_at) }}</span>
                            </div>
                            <div class="dae-field" v-if="contrat.updated_at && contrat.updated_at !== contrat.created_at">
                                <span class="dae-field-label">Mis à jour le</span>
                                <span class="dae-field-value">{{ formatDateTime(contrat.updated_at) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
export default {
    name: 'dae-contrats-show'
};
</script>

<style scoped>
/* ════════════════════════════════════════════════
   DAE Contrats Show Styles
   ════════════════════════════════════════════════ */

.dae-contrats-show {
    padding: 24px;
    min-height: 80vh;
    position: relative;
    font-family: 'Inter', sans-serif;
}

/* ── Loading ── */
.dae-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 120px 20px;
}
.dae-spinner {
    width: 44px;
    height: 44px;
    border: 4px solid rgba(255, 121, 0, 0.12);
    border-top-color: #FF7900;
    border-radius: 50%;
    animation: dae-spin 0.7s linear infinite;
}
@keyframes dae-spin {
    to { transform: rotate(360deg); }
}

/* ── Error ── */
.dae-error {
    background: #fdecea;
    color: #c62828;
    padding: 16px 20px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    font-size: 14px;
}

/* ── Toast ── */
.dae-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    padding: 14px 20px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 500;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    animation: dae-toast-in 0.3s ease;
    max-width: 420px;
}
@keyframes dae-toast-in {
    from { opacity: 0; transform: translateX(40px); }
    to { opacity: 1; transform: translateX(0); }
}
.dae-toast-success {
    background: #e8f5e9;
    color: #2e7d32;
    border-left: 4px solid #2e7d32;
}
.dae-toast-danger {
    background: #fdecea;
    color: #c62828;
    border-left: 4px solid #c62828;
}
.dae-toast-close {
    background: none;
    border: none;
    font-size: 20px;
    line-height: 1;
    cursor: pointer;
    margin-left: auto;
    padding: 0 0 0 12px;
    opacity: 0.6;
}
.dae-toast-close:hover { opacity: 1; }

/* ── Header ── */
.dae-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.dae-header-left {
    display: flex;
    align-items: flex-start;
    gap: 16px;
}
.dae-back-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    background: #fff;
    border: 1px solid #dce3ee;
    border-radius: 6px;
    color: #555;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.15s;
    white-space: nowrap;
}
.dae-back-btn:hover {
    background: #f8f9fa;
    border-color: #ccc;
    color: #FF7900;
}
.dae-title {
    font-family: 'Outfit', sans-serif;
    font-size: 22px;
    font-weight: 700;
    color: #163A5E;
    margin: 0 0 4px 0;
}
.dae-header-right {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.dae-status-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
}
.dae-status-badge-sm {
    padding: 4px 12px;
    font-size: 12px;
}
.dae-status-brouillon { background: #f3f4f6; color: #6b7280; }
.dae-status-actif { background: #e8f5e9; color: #2e7d32; }
.dae-status-expire { background: #fdecea; color: #c62828; }
.dae-status-resilie { background: #eef3f9; color: #163A5E; }
.dae-status-renouvele { background: #e3f2fd; color: #1565c0; }

.dae-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}
.dae-badge-renouvelable {
    background: #e8f5e9;
    color: #1b5e20;
    border: 1px solid #a5d6a7;
}

/* ── Actions Bar ── */
.dae-actions-bar {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 24px;
    padding: 12px 16px;
    background: #fff;
    border: 1px solid #dce3ee;
    border-radius: 6px;
}

/* ── Cards ── */
.dae-card {
    background: #fff;
    border: 1px solid #dce3ee;
    border-radius: 6px;
    margin-bottom: 20px;
    box-shadow: 0 1px 4px rgba(22, 58, 94, 0.06);
}
.dae-card-header {
    background: linear-gradient(90deg, #163A5E, #1e4d7a);
    color: #fff;
    padding: 12px 18px;
    font-size: 13px;
    font-weight: 700;
    border-radius: 6px 6px 0 0;
    display: flex;
    align-items: center;
}
.dae-card-body {
    padding: 20px;
}

/* ── Fields ── */
.dae-field {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.dae-field-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #888;
}
.dae-field-value {
    font-size: 14px;
    color: #222;
    font-weight: 500;
}
.dae-field-value-large {
    font-size: 16px;
    font-weight: 600;
}
.dae-field-content {
    font-size: 14px;
    color: #333;
    line-height: 1.7;
    white-space: pre-wrap;
    background: #f8f9fa;
    padding: 14px;
    border-radius: 6px;
    border: 1px solid #eef0f4;
}

/* ── Tags ── */
.dae-tag {
    display: inline-block;
    padding: 4px 12px;
    margin: 0 6px 6px 0;
    background: #FFF3E0;
    color: #e06700;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

/* ── File ── */
.dae-file-icon {
    width: 44px;
    height: 44px;
    background: #FFF3E0;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: #FF7900;
}

/* ── Responsive ── */
@media (max-width: 767.98px) {
    .dae-contrats-show {
        padding: 12px;
    }
    .dae-header {
        flex-direction: column;
    }
    .dae-header-left {
        flex-direction: column;
        width: 100%;
    }
    .dae-title {
        font-size: 18px;
    }
    .dae-actions-bar {
        flex-wrap: wrap;
    }
}
</style>
