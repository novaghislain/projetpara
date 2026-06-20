<template>
    <GelLayout>
        <div class="legal-dashboard">
            <!-- ═══════════════ LOADING ═══════════════ -->
            <div v-if="state === 'loading'" class="legal-loading" role="status" aria-live="polite">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="legal-loading-text">Chargement du tableau de bord juridique...</p>
            </div>

            <!-- ═══════════════ ERROR ═══════════════ -->
            <div v-else-if="state === 'error'" class="legal-error" role="alert">
                <div class="legal-error-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <h2 class="legal-error-title">Erreur de chargement</h2>
                <p class="legal-error-message">{{ errorMessage }}</p>
                <button class="btn btn-primary" @click="loadStats">
                    <i class="bi bi-arrow-clockwise me-2"></i>Réessayer
                </button>
            </div>

            <!-- ═══════════════ CONTENT ═══════════════ -->
            <div v-else class="legal-content">
                <!-- ─── En-tête ─── -->
                <header class="legal-header">
                    <div class="legal-header-left">
                        <h1 class="legal-title">
                            <i class="bi bi-briefcase me-2 text-primary"></i>Secrétariat Juridique
                        </h1>
                        <p class="legal-subtitle">
                            Gestion des contrats, contentieux, conformité et vie sociétaire
                        </p>
                    </div>
                </header>

                <!-- ─── Cartes KPI ─── -->
                <div class="legal-kpi-grid" role="list" aria-label="Indicateurs clés">
                    <div v-for="kpi in kpiCards" :key="kpi.key" class="legal-kpi-card" role="listitem">
                        <div class="legal-kpi-icon" :class="`legal-kpi-icon--${kpi.color}`">
                            <i :class="`bi ${kpi.icon}`" aria-hidden="true"></i>
                        </div>
                        <div class="legal-kpi-value">{{ stats[kpi.field] ?? 0 }}</div>
                        <div class="legal-kpi-label">{{ kpi.label }}</div>
                        <div
                            v-if="kpi.alertField && (stats[kpi.alertField] ?? 0) > 0"
                            class="legal-kpi-alert"
                        >
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            {{ stats[kpi.alertField] }} {{ kpi.alertLabel }}
                        </div>
                        <div v-if="kpi.secondaryField" class="legal-kpi-secondary">
                            <span class="legal-kpi-value legal-kpi-value--sm">{{ stats[kpi.secondaryField] ?? 0 }}</span>
                            <span class="legal-kpi-label">{{ kpi.secondaryLabel }}</span>
                        </div>
                    </div>
                </div>

                <!-- ─── Grille principale ─── -->
                <div class="row g-4">
                    <!-- Contrats expirant bientôt -->
                    <div class="col-md-6">
                        <div class="legal-card">
                            <div class="legal-card-header">
                                <h2 class="legal-card-title">
                                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>Contrats expirant bientôt
                                </h2>
                            </div>
                            <div class="legal-card-body legal-list">
                                <template v-if="contratsUrgents.length">
                                    <article
                                        v-for="c in contratsUrgents"
                                        :key="c.id"
                                        class="legal-list-item"
                                    >
                                        <div class="legal-list-content">
                                            <div class="legal-list-name">{{ c.titre }}</div>
                                            <div class="legal-list-meta">{{ c.parties?.[0]?.nom || '—' }}</div>
                                        </div>
                                        <div class="legal-list-end">
                                            <div class="legal-days-left">{{ daysLeft(c.date_fin) }} jours</div>
                                            <div class="legal-list-meta">fin {{ formatDate(c.date_fin) }}</div>
                                        </div>
                                    </article>
                                </template>
                                <div v-else class="legal-empty">
                                    <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                                    <p>Aucun contrat urgent</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Prochaines AG -->
                    <div class="col-md-6">
                        <div class="legal-card">
                            <div class="legal-card-header">
                                <h2 class="legal-card-title">
                                    <i class="bi bi-calendar-event me-2"></i>Prochaines Assemblées Générales
                                </h2>
                            </div>
                            <div class="legal-card-body legal-list">
                                <template v-if="prochainesAG.length">
                                    <article
                                        v-for="ag in prochainesAG"
                                        :key="ag.id"
                                        class="legal-list-item"
                                    >
                                        <div class="legal-list-content">
                                            <div class="legal-list-name">{{ ag.type }}</div>
                                            <div class="legal-list-meta">{{ ag.lieu }}</div>
                                        </div>
                                        <div class="legal-list-end">
                                            <div class="fw-semibold small">{{ formatDate(ag.date_tenue) }}</div>
                                            <ContratStatusBadge :statut="ag.statut" :label="ag.statut" />
                                        </div>
                                    </article>
                                </template>
                                <div v-else class="legal-empty">
                                    <i class="bi bi-calendar3" style="font-size: 2rem; opacity:0.4;"></i>
                                    <p>Aucune AG planifiée</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alertes Conformité -->
                    <div class="col-md-6">
                        <div class="legal-card">
                            <div class="legal-card-header">
                                <h2 class="legal-card-title">
                                    <i class="bi bi-shield-exclamation text-danger me-2"></i>Alertes Conformité
                                </h2>
                            </div>
                            <div class="legal-card-body legal-list">
                                <template v-if="conformitesAlertes.length">
                                    <article
                                        v-for="c in conformitesAlertes"
                                        :key="c.id"
                                        class="legal-list-item"
                                    >
                                        <div class="legal-list-content">
                                            <div class="legal-list-name">{{ c.intitule }}</div>
                                            <div class="legal-list-meta">{{ c.organisme }}</div>
                                        </div>
                                        <div class="legal-list-end">
                                            <ContratStatusBadge :statut="c.statut" :label="c.statut" />
                                            <div class="legal-list-meta">échéance {{ formatDate(c.date_echeance) }}</div>
                                        </div>
                                    </article>
                                </template>
                                <div v-else class="legal-empty">
                                    <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                                    <p>Tout est conforme</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contentieux récents -->
                    <div class="col-md-6">
                        <div class="legal-card">
                            <div class="legal-card-header">
                                <h2 class="legal-card-title">
                                    <i class="bi bi-bank text-danger me-2"></i>Contentieux récents
                                </h2>
                            </div>
                            <div class="legal-card-body legal-list">
                                <template v-if="contentieuxRecents.length">
                                    <article
                                        v-for="l in contentieuxRecents"
                                        :key="l.id"
                                        class="legal-list-item"
                                    >
                                        <div class="legal-list-content">
                                            <div class="legal-list-name">{{ l.titre }}</div>
                                            <div class="legal-list-meta">
                                                {{ l.partie_adverse }} — {{ l.tribunal }}
                                            </div>
                                        </div>
                                        <div class="legal-list-end">
                                            <ContratStatusBadge :statut="l.statut" :label="l.statut" />
                                            <div v-if="l.prochaine_audience" class="legal-list-meta">
                                                audience {{ formatDate(l.prochaine_audience) }}
                                            </div>
                                        </div>
                                    </article>
                                </template>
                                <div v-else class="legal-empty">
                                    <i class="bi bi-folder2-open" style="font-size: 2rem; opacity:0.4;"></i>
                                    <p>Aucun contentieux en cours</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ─── Accès rapides ─── -->
                <div class="legal-quick-links">
                    <div class="legal-card">
                        <div class="legal-card-body">
                            <h2 class="legal-card-title mb-3">
                                <i class="bi bi-link-45deg me-2"></i>Accès rapides
                            </h2>
                            <div class="legal-links-grid">
                                <a
                                    v-for="link in quickLinks"
                                    :key="link.href"
                                    :href="link.href"
                                    class="legal-quick-link"
                                >
                                    <i :class="link.icon" class="legal-quick-link-icon"></i>
                                    <span>{{ link.label }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import GelLayout from '../../../Layouts/GelLayout.vue'
import ContratStatusBadge from '../../../Components/Legal/ContratStatusBadge.vue'

// ─── Configuration ─────────────────────────────────────────────
const kpiCards = [
    { key: 'contrats',       icon: 'bi-file-text',        color: 'primary',
      field: 'contrats_actifs',      label: 'Contrats actifs',
      alertField: 'contrats_expiration', alertLabel: 'expirent bientôt' },
    { key: 'contentieux',    icon: 'bi-bank',             color: 'danger',
      field: 'contentieux_en_cours',  label: 'Contentieux en cours' },
    { key: 'conformite',     icon: 'bi-shield-check',     color: 'success',
      field: 'conformites_ok',        label: 'Conformité OK',
      alertField: 'conformites_alertes', alertLabel: 'alertes' },
    { key: 'societe',        icon: 'bi-people',           color: 'warning',
      field: 'ag_planifiees',         label: 'AG planifiées',
      secondaryField: 'dossiers_ouverts', secondaryLabel: 'Dossiers ouverts' },
]

const quickLinks = [
    { href: '/juridique/societe',      icon: 'bi-building',     label: 'Fiche société' },
    { href: '/juridique/assemblees',   icon: 'bi-file-text',    label: 'Assemblées' },
    { href: '/juridique/contrats',     icon: 'bi-file-earmark', label: 'Contrats' },
    { href: '/juridique/contentieux',  icon: 'bi-bank',         label: 'Contentieux' },
    { href: '/juridique/conformite',   icon: 'bi-shield-check', label: 'Conformité' },
    { href: '/juridique/bibliotheque', icon: 'bi-book',         label: 'Bibliothèque' },
    { href: '/juridique/registres',    icon: 'bi-folder',       label: 'Registres' },
    { href: '/juridique/dossiers',     icon: 'bi-folder2-open', label: 'Dossiers' },
]

// ─── État ─────────────────────────────────────────────────────
const state = ref('loading')     // 'loading' | 'loaded' | 'error'
const errorMessage = ref('')
const stats = ref({})
const contratsUrgents = ref([])
const prochainesAG = ref([])
const conformitesAlertes = ref([])
const contentieuxRecents = ref([])

// ─── Formateurs ────────────────────────────────────────────────
function formatDate(date) {
    if (!date) return '—'
    try {
        const d = new Date(date + (date.includes('T') ? '' : 'T00:00:00'))
        return Number.isNaN(d.getTime()) ? date
            : d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' })
    } catch { return date }
}

function daysLeft(date) {
    if (!date) return '—'
    try {
        const d = new Date(date + 'T23:59:59')
        if (Number.isNaN(d.getTime())) return '—'
        const diff = Math.ceil((d - new Date()) / 86400000)
        return diff > 0 ? diff : 0
    } catch { return '—' }
}

// ─── Chargement ────────────────────────────────────────────────
async function loadStats() {
    state.value = 'loading'
    errorMessage.value = ''

    try {
        const { data } = await window.axios.get('/juridique/api/stats')
        stats.value = data.stats
        contratsUrgents.value = data.contrats_urgents || []
        prochainesAG.value = data.prochaines_ag || []
        conformitesAlertes.value = data.conformites_alertes || []
        contentieuxRecents.value = data.contentieux_recents || []
        state.value = 'loaded'
    } catch (err) {
        console.error('[Legal Dashboard]', err)
        errorMessage.value =
            err?.response?.data?.message
            || err?.message
            || 'Impossible de charger les données juridiques.'
        state.value = 'error'
    }
}

onMounted(() => loadStats())
</script>

<style scoped>
/* ═══════════════════════════════════════════════════════════════
   Legal Dashboard — Design professionnel
   ═══════════════════════════════════════════════════════════════ */

.legal-dashboard {
    padding: 1.5rem;
    max-width: 1440px;
    margin: 0 auto;
}

/* ─── Loading / Error ────────────────────────────────────────── */
.legal-loading, .legal-error {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 400px;
    gap: 0.75rem;
    text-align: center;
    padding: 2rem;
}
.legal-loading .spinner-border { width: 3rem; height: 3rem; }
.legal-loading-text { color: var(--bs-secondary-color); font-size: 0.9rem; margin: 0; }
.legal-error-icon { font-size: 3rem; color: var(--bs-danger); }
.legal-error-title { font-size: 1.25rem; font-weight: 600; margin: 0; }
.legal-error-message { color: var(--bs-secondary-color); max-width: 480px; margin: 0 0 0.5rem; }

/* ─── En-tête ────────────────────────────────────────────────── */
.legal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
}
.legal-title {
    font-family: 'Outfit', sans-serif;
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 0.25rem;
    display: flex;
    align-items: center;
}
.legal-subtitle { color: var(--bs-secondary-color); font-size: 0.85rem; margin: 0; }

/* ─── Grille KPI ─────────────────────────────────────────────── */
.legal-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.legal-kpi-card {
    background: #fff;
    border: 1px solid var(--bs-border-color, #e9ecef);
    border-radius: 12px;
    padding: 1.25rem 1rem;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    transition: box-shadow 0.2s ease, transform 0.2s ease;
}
.legal-kpi-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transform: translateY(-2px);
}
.legal-kpi-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.75rem;
    font-size: 1.35rem;
}
.legal-kpi-icon--primary  { background: rgba(255,121,0,0.12); color: #ff7900; }
.legal-kpi-icon--danger   { background: rgba(239,68,68,0.12);  color: #ef4444; }
.legal-kpi-icon--success  { background: rgba(16,185,129,0.12); color: #10b981; }
.legal-kpi-icon--warning  { background: rgba(245,158,11,0.12); color: #f59e0b; }
.legal-kpi-value {
    font-size: 1.75rem;
    font-weight: 700;
    line-height: 1.2;
}
.legal-kpi-value--sm { font-size: 1.15rem; }
.legal-kpi-label {
    font-size: 0.8rem;
    color: var(--bs-secondary-color);
    margin-top: 0.2rem;
}
.legal-kpi-alert {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--bs-danger);
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
}
.legal-kpi-secondary {
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid var(--bs-border-color);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.1rem;
}

@media (max-width: 992px) { .legal-kpi-grid { grid-template-columns: repeat(2,1fr); } }
@media (max-width: 576px) { .legal-kpi-grid { grid-template-columns: 1fr; } }

/* ─── Carte générique ────────────────────────────────────────── */
.legal-card {
    background: #fff;
    border: 1px solid var(--bs-border-color, #e9ecef);
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    height: 100%;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: box-shadow 0.2s ease;
}
.legal-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.legal-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--bs-border-color, #e9ecef);
    background: #fafafa;
}
.legal-card-title {
    font-size: 0.85rem;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
}
.legal-card-body { flex: 1; padding: 0; }

/* ─── Listes ─────────────────────────────────────────────────── */
.legal-list { display: flex; flex-direction: column; }
.legal-list-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.6rem 1rem;
    border-bottom: 1px solid var(--bs-border-color, #f0f0f0);
    transition: background 0.15s ease;
}
.legal-list-item:last-child { border-bottom: none; }
.legal-list-item:hover { background: rgba(0,0,0,0.02); }
.legal-list-content { flex: 1; min-width: 0; }
.legal-list-name {
    font-size: 0.85rem;
    font-weight: 600;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.legal-list-meta {
    font-size: 0.78rem;
    color: var(--bs-secondary-color);
    line-height: 1.3;
}
.legal-list-end { text-align: right; flex-shrink: 0; }
.legal-days-left {
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--bs-danger);
}

/* ─── Empty state ────────────────────────────────────────────── */
.legal-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    color: var(--bs-secondary-color);
    gap: 0.5rem;
}
.legal-empty p { font-size: 0.85rem; margin: 0; }

/* ─── Accès rapides ──────────────────────────────────────────── */
.legal-quick-links { margin-top: 1.5rem; }
.legal-quick-links .legal-card-body { padding: 1rem; }
.legal-links-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.legal-quick-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.9rem;
    border: 1px solid var(--bs-border-color, #e9ecef);
    border-radius: 8px;
    color: var(--bs-body-color);
    font-size: 0.82rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.15s ease;
}
.legal-quick-link:hover {
    background: rgba(255,121,0,0.06);
    border-color: #ff7900;
    color: #ff7900;
}
.legal-quick-link-icon { font-size: 1rem; }

/* ─── Responsive ─────────────────────────────────────────────── */
@media (max-width: 768px) {
    .legal-dashboard { padding: 1rem; }
    .legal-title { font-size: 1.25rem; }
}

/* ─── Print ──────────────────────────────────────────────────── */
@media print {
    .legal-dashboard { padding: 0; max-width: none; }
    .legal-quick-links { display: none; }
    .legal-kpi-card { break-inside: avoid; }
}
</style>
