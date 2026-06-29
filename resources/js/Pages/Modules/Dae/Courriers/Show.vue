<script setup>
import { ref, computed, onMounted } from 'vue';

const statusSteps = ['brouillon', 'envoye', 'recu', 'traite', 'archive'];
const statusLabels = {
    brouillon: 'Brouillon',
    envoye: 'Envoye',
    recu: 'Recu',
    traite: 'Traite',
    archive: 'Archive'
};

const courrierId = computed(() => {
    const match = window.location.pathname.match(/\/dae\/courriers\/(\d+)/);
    return match ? match[1] : null;
});

const courrier = ref(null);
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

function getStatusColor(status) {
    const map = {
        brouillon: 'secondary',
        envoye: 'info',
        recu: 'primary',
        traite: 'success',
        archive: 'dark'
    };
    return map[status] || 'secondary';
}

function getStatusIcon(status) {
    const map = {
        brouillon: 'bi-pencil-square',
        envoye: 'bi-send',
        recu: 'bi-envelope-open',
        traite: 'bi-check2-all',
        archive: 'bi-archive'
    };
    return map[status] || 'bi-circle';
}

function statusIndex(step) {
    return statusSteps.indexOf(step);
}

async function fetchCourrier() {
    loading.value = true;
    error.value = null;
    try {
        const res = await fetch(`/dae/courriers/${courrierId.value}`, {
            headers: { 'Accept': 'application/json' }
        });
        if (!res.ok) throw new Error('Erreur lors du chargement du courrier.');
        courrier.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
}

async function traiter() {
    if (!confirm('Confirmer le traitement de ce courrier ?')) return;
    processing.value = true;
    try {
        const res = await fetch(`/dae/courriers/${courrierId.value}/traiter`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        if (!res.ok) {
            const errData = await res.json().catch(() => ({}));
            throw new Error(errData.message || 'Erreur lors du traitement.');
        }
        await fetchCourrier();
        showToast('Courrier traite avec succes.');
    } catch (e) {
        showToast(e.message, 'danger');
    } finally {
        processing.value = false;
    }
}

async function archiver() {
    if (!confirm('Confirmer l\'archivage de ce courrier ?')) return;
    processing.value = true;
    try {
        const res = await fetch(`/dae/courriers/${courrierId.value}/archiver`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        if (!res.ok) {
            const errData = await res.json().catch(() => ({}));
            throw new Error(errData.message || 'Erreur lors de l\'archivage.');
        }
        await fetchCourrier();
        showToast('Courrier archive avec succes.');
    } catch (e) {
        showToast(e.message, 'danger');
    } finally {
        processing.value = false;
    }
}

async function dupliquer() {
    processing.value = true;
    try {
        const res = await fetch(`/dae/courriers/${courrierId.value}/dupliquer`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        if (!res.ok) {
            const errData = await res.json().catch(() => ({}));
            throw new Error(errData.message || 'Erreur lors de la duplication.');
        }
        const data = await res.json();
        showToast('Courrier duplique avec succes. Redirection...');
        setTimeout(() => {
            window.location.href = `/dae/courriers/${data.id || data.courrier?.id}`;
        }, 800);
    } catch (e) {
        showToast(e.message, 'danger');
    } finally {
        processing.value = false;
    }
}

function getDateForStatus(step) {
    if (!courrier.value) return null;
    const map = {
        brouillon: courrier.value.created_at,
        envoye: courrier.value.date_envoi || courrier.value.envoye_at,
        recu: courrier.value.recu_at,
        traite: courrier.value.date_traitement || courrier.value.traite_at,
        archive: courrier.value.archive_at || courrier.value.date_archivage,
    };
    return map[step] || null;
}

onMounted(fetchCourrier);
</script>

<template>
<GelLayout>
    <div class="dae-courriers-show">
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
            <p class="mt-3 text-muted small">Chargement du courrier...</p>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="dae-error">
            <i class="bi-exclamation-triangle-fill me-2"></i>
            {{ error }}
            <button class="btn btn-sm btn-outline-secondary ms-3" @click="fetchCourrier">
                <i class="bi-arrow-clockwise me-1"></i>Reessayer
            </button>
        </div>

        <!-- Content -->
        <template v-else-if="courrier">
            <!-- Header -->
            <div class="dae-header">
                <div class="dae-header-left">
                    <a href="/dae/courriers" class="dae-back-btn">
                        <i class="bi-arrow-left"></i>
                        <span>Retour</span>
                    </a>
                    <div>
                        <h1 class="dae-title">
                            Courrier {{ courrier.reference || 'Sans reference' }}
                        </h1>
                        <span class="text-muted small">
                            <i class="bi-calendar3 me-1"></i>
                            Cree le {{ courrier.created_at ? new Date(courrier.created_at).toLocaleDateString('fr-FR', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-' }}
                        </span>
                    </div>
                </div>
                <div class="dae-header-right">
                    <span class="dae-status-badge"
                          :class="'dae-status-' + (courrier.statut || 'brouillon')">
                        <i :class="getStatusIcon(courrier.statut)" class="me-1"></i>
                        {{ statusLabels[courrier.statut] || courrier.statut }}
                    </span>
                </div>
            </div>

            <!-- Action Buttons Row -->
            <div class="dae-actions-bar">
                <a :href="'/dae/courriers/' + courrier.id + '/edit'"
                   class="btn btn-outline-primary btn-sm">
                    <i class="bi-pencil me-1"></i>Modifier
                </a>
                <button v-if="courrier.statut !== 'archive' && courrier.statut !== 'traite'"
                        class="btn btn-success btn-sm"
                        :disabled="processing"
                        @click="traiter">
                    <i class="bi-check2-all me-1"></i>Traiter
                </button>
                <button v-if="courrier.statut !== 'archive'"
                        class="btn btn-dark btn-sm"
                        :disabled="processing"
                        @click="archiver">
                    <i class="bi-archive me-1"></i>Archiver
                </button>
                <button class="btn btn-outline-secondary btn-sm"
                        :disabled="processing"
                        @click="dupliquer">
                    <i class="bi-files me-1"></i>Dupliquer
                </button>
            </div>

            <!-- Main Grid -->
            <div class="row g-4">
                <!-- Left Column: Details -->
                <div class="col-lg-8">
                    <!-- Info Card -->
                    <div class="dae-card">
                        <div class="dae-card-header">
                            <i class="bi-info-circle me-2"></i>Informations generales
                        </div>
                        <div class="dae-card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="dae-field">
                                        <span class="dae-field-label">Reference</span>
                                        <span class="dae-field-value">{{ courrier.reference || '-' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="dae-field">
                                        <span class="dae-field-label">Type</span>
                                        <span class="dae-field-value">{{ courrier.type || '-' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="dae-field">
                                        <span class="dae-field-label">Mode d'envoi</span>
                                        <span class="dae-field-value">{{ courrier.mode || '-' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="dae-field">
                                        <span class="dae-field-label">Date d'envoi</span>
                                        <span class="dae-field-value">{{ courrier.date_envoi ? new Date(courrier.date_envoi).toLocaleDateString('fr-FR') : '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Object & Content Card -->
                    <div class="dae-card">
                        <div class="dae-card-header">
                            <i class="bi-file-text me-2"></i>Objet et contenu
                        </div>
                        <div class="dae-card-body">
                            <div class="dae-field mb-4">
                                <span class="dae-field-label">Objet</span>
                                <span class="dae-field-value dae-field-value-large">{{ courrier.objet || 'Aucun objet' }}</span>
                            </div>
                            <div class="dae-field">
                                <span class="dae-field-label">Contenu</span>
                                <div class="dae-field-content">{{ courrier.contenu || 'Aucun contenu' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Participants Card -->
                    <div class="dae-card">
                        <div class="dae-card-header">
                            <i class="bi-people me-2"></i>Participants
                        </div>
                        <div class="dae-card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="dae-field">
                                        <span class="dae-field-label">Expediteur</span>
                                        <div class="dae-field-value d-flex align-items-center gap-2">
                                            <i class="bi-person-circle text-primary"></i>
                                            {{ courrier.expediteur || 'Non specifie' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="dae-field">
                                        <span class="dae-field-label">Destinataire</span>
                                        <div class="dae-field-value d-flex align-items-center gap-2">
                                            <i class="bi-person text-info"></i>
                                            {{ courrier.destinataire || 'Non specifie' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tags Card -->
                    <div class="dae-card" v-if="courrier.tags && courrier.tags.length">
                        <div class="dae-card-header">
                            <i class="bi-tags me-2"></i>Tags
                        </div>
                        <div class="dae-card-body">
                            <span v-for="tag in courrier.tags" :key="tag"
                                  class="dae-tag">
                                {{ tag }}
                            </span>
                            <span v-if="!courrier.tags.length" class="text-muted small fst-italic">
                                Aucun tag
                            </span>
                        </div>
                    </div>

                    <!-- Fichier Joint Card -->
                    <div class="dae-card" v-if="courrier.fichier_joint">
                        <div class="dae-card-header">
                            <i class="bi-paperclip me-2"></i>Fichier joint
                        </div>
                        <div class="dae-card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="dae-file-icon">
                                        <i class="bi-file-earmark"></i>
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ courrier.fichier_joint.nom || courrier.fichier_joint.original_name || 'Fichier' }}</div>
                                        <span class="small text-muted">
                                            {{ courrier.fichier_joint.taille ? (courrier.fichier_joint.taille / 1024).toFixed(1) + ' Ko' : '' }}
                                        </span>
                                    </div>
                                </div>
                                <a :href="courrier.fichier_joint.url || '/storage/' + courrier.fichier_joint.chemin"
                                   target="_blank"
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="bi-download me-1"></i>Telecharger
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Timeline -->
                <div class="col-lg-4">
                    <div class="dae-card">
                        <div class="dae-card-header">
                            <i class="bi-clock-history me-2"></i>Statut
                        </div>
                        <div class="dae-card-body">
                            <div class="dae-timeline">
                                <div v-for="(step, index) in statusSteps"
                                     :key="step"
                                     class="dae-timeline-item"
                                     :class="{
                                        'dae-timeline-completed': statusIndex(courrier.statut) > index,
                                        'dae-timeline-active': statusIndex(courrier.statut) === index,
                                        'dae-timeline-pending': statusIndex(courrier.statut) < index
                                     }">
                                    <div class="dae-timeline-marker">
                                        <i v-if="statusIndex(courrier.statut) > index"
                                           class="bi-check-circle-fill"></i>
                                        <i v-else-if="statusIndex(courrier.statut) === index"
                                           class="bi-circle-fill"></i>
                                        <i v-else class="bi-circle"></i>
                                    </div>
                                    <div class="dae-timeline-content">
                                        <div class="dae-timeline-label">
                                            {{ statusLabels[step] }}
                                        </div>
                                        <div v-if="getDateForStatus(step)" class="dae-timeline-date">
                                            {{ new Date(getDateForStatus(step)).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informations supplementaires -->
                    <div class="dae-card">
                        <div class="dae-card-header">
                            <i class="bi-card-text me-2"></i>Informations
                        </div>
                        <div class="dae-card-body">
                            <div class="dae-field mb-3">
                                <span class="dae-field-label">Cree par</span>
                                <span class="dae-field-value">{{ courrier.user?.name || courrier.created_by || '-' }}</span>
                            </div>
                            <div class="dae-field mb-3" v-if="courrier.traite_par">
                                <span class="dae-field-label">Traite par</span>
                                <span class="dae-field-value">{{ courrier.traite_par }}</span>
                            </div>
                            <div class="dae-field mb-3" v-if="courrier.date_traitement">
                                <span class="dae-field-label">Date traitement</span>
                                <span class="dae-field-value">{{ new Date(courrier.date_traitement).toLocaleDateString('fr-FR') }}</span>
                            </div>
                            <div class="dae-field">
                                <span class="dae-field-label">Priorite</span>
                                <span class="dae-field-value">
                                    <span v-if="courrier.priorite"
                                          class="badge"
                                          :class="courrier.priorite === 'haute' ? 'bg-danger' : courrier.priorite === 'normale' ? 'bg-info' : 'bg-secondary'">
                                        {{ courrier.priorite }}
                                    </span>
                                    <span v-else class="text-muted">Non definie</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</GelLayout>
</template>

<script>
export default {
    name: 'dae-courriers-show'
};
</script>

<style scoped>
/* ════════════════════════════════════════════════
   DAE Courriers Show Styles
   ════════════════════════════════════════════════ */

.dae-courriers-show {
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
.dae-status-brouillon { background: #f3f4f6; color: #6b7280; }
.dae-status-envoye { background: #e3f2fd; color: #1565c0; }
.dae-status-recu { background: #e8f5e9; color: #2e7d32; }
.dae-status-traite { background: #e8f5e9; color: #1b5e20; }
.dae-status-archive { background: #eef3f9; color: #163A5E; }

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

/* ── Timeline ── */
.dae-timeline {
    position: relative;
    padding-left: 8px;
}
.dae-timeline::before {
    content: '';
    position: absolute;
    left: 18px;
    top: 10px;
    bottom: 10px;
    width: 2px;
    background: #eef0f4;
}
.dae-timeline-item {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding-bottom: 28px;
    position: relative;
}
.dae-timeline-item:last-child {
    padding-bottom: 0;
}
.dae-timeline-marker {
    flex-shrink: 0;
    width: 38px;
    display: flex;
    justify-content: center;
    position: relative;
    z-index: 1;
    background: #fff;
    font-size: 18px;
}
.dae-timeline-completed .dae-timeline-marker {
    color: #2e7d32;
}
.dae-timeline-active .dae-timeline-marker {
    color: #FF7900;
}
.dae-timeline-pending .dae-timeline-marker {
    color: #ccc;
}
.dae-timeline-content {
    flex-grow: 1;
    padding-top: 1px;
}
.dae-timeline-label {
    font-size: 14px;
    font-weight: 600;
    color: #333;
}
.dae-timeline-completed .dae-timeline-label {
    color: #2e7d32;
}
.dae-timeline-active .dae-timeline-label {
    color: #FF7900;
}
.dae-timeline-pending .dae-timeline-label {
    color: #aaa;
}
.dae-timeline-date {
    font-size: 12px;
    color: #888;
    margin-top: 2px;
}

/* ── Responsive ── */
@media (max-width: 767.98px) {
    .dae-courriers-show {
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
