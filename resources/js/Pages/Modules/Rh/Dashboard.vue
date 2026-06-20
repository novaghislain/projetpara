<template>
    <GelLayout>
        <div class="rh-dashboard">
            <!-- ═══════════════ LOADING ═══════════════ -->
            <div v-if="state === 'loading'" class="rh-loading" role="status" aria-live="polite">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="rh-loading-text">Chargement du tableau de bord RH...</p>
            </div>

            <!-- ═══════════════ ERROR ═══════════════ -->
            <div v-else-if="state === 'error'" class="rh-error" role="alert">
                <div class="rh-error-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <h2 class="rh-error-title">Erreur de chargement</h2>
                <p class="rh-error-message">{{ errorMessage }}</p>
                <button class="btn btn-primary" @click="fetchStats">
                    <i class="bi bi-arrow-clockwise me-2"></i>Réessayer
                </button>
            </div>

            <!-- ═══════════════ DASHBOARD CONTENT ═══════════════ -->
            <div v-else class="rh-content">
                <!-- ─── En-tête ─── -->
                <header class="rh-header">
                    <div class="rh-header-left">
                        <h1 class="rh-title">
                            <i class="bi bi-people me-2 text-primary"></i>Ressources Humaines
                        </h1>
                        <p class="rh-subtitle">
                            <i class="bi bi-calendar3 me-1"></i>{{ formattedDate }}
                            <span class="mx-2" aria-hidden="true">•</span>
                            <i class="bi bi-person-badge me-1"></i>Vue d'ensemble RH
                        </p>
                    </div>
                    <div class="rh-header-right">
                        <a href="/rh/employees/create" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i>Nouvel employé
                        </a>
                    </div>
                </header>

                <!-- ─── Cartes statistiques ─── -->
                <div class="rh-stats-grid" role="list" aria-label="Indicateurs RH">
                    <div v-for="card in statCards" :key="card.key" class="rh-stats-item" role="listitem">
                        <RhStatCard
                            :icon="card.icon"
                            :label="card.label"
                            :value="stats[card.field] ?? 0"
                            :color="card.color"
                        />
                    </div>
                </div>

                <!-- ─── Grille de cartes ─── -->
                <div class="row g-3">
                    <!-- Derniers employés -->
                    <div class="col-md-6">
                        <div class="rh-card">
                            <div class="rh-card-header">
                                <h2 class="rh-card-title">
                                    <i class="bi bi-person-lines-fill me-2 text-primary"></i>Derniers employés
                                </h2>
                                <a href="/rh/employees" class="btn btn-sm btn-outline-primary">Voir tout</a>
                            </div>
                            <div class="rh-card-body rh-list">
                                <template v-if="stats.recent_employees?.length">
                                    <article
                                        v-for="emp in stats.recent_employees"
                                        :key="emp.id"
                                        class="rh-list-item"
                                    >
                                        <div class="rh-list-icon bg-primary-subtle">
                                            <i class="bi bi-person text-primary"></i>
                                        </div>
                                        <div class="rh-list-content">
                                            <div class="rh-list-name">{{ emp.prenom }} {{ emp.nom }}</div>
                                            <div class="rh-list-meta">{{ emp.poste || '—' }}</div>
                                        </div>
                                        <span
                                            :class="['rh-badge', employeeStatusClass(emp.status)]"
                                        >{{ emp.status }}</span>
                                    </article>
                                </template>
                                <div v-else class="rh-list-empty">
                                    <i class="bi bi-inbox"></i>
                                    <p>Aucun employé</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Congés en attente -->
                    <div class="col-md-6">
                        <div class="rh-card">
                            <div class="rh-card-header">
                                <h2 class="rh-card-title">
                                    <i class="bi bi-calendar-week me-2 text-warning"></i>Congés en attente
                                </h2>
                                <a href="/rh/leaves" class="btn btn-sm btn-outline-primary">Voir tout</a>
                            </div>
                            <div class="rh-card-body rh-list">
                                <template v-if="stats.pending_leaves_list?.length">
                                    <article
                                        v-for="leave in stats.pending_leaves_list"
                                        :key="leave.id"
                                        class="rh-list-item"
                                    >
                                        <div class="rh-list-icon bg-warning-subtle">
                                            <i class="bi bi-calendar text-warning"></i>
                                        </div>
                                        <div class="rh-list-content">
                                            <div class="rh-list-name">{{ leave.employee }}</div>
                                            <div class="rh-list-meta">
                                                {{ leave.type }} —
                                                {{ formatDateRange(leave.date_debut, leave.date_fin) }}
                                            </div>
                                        </div>
                                    </article>
                                </template>
                                <div v-else class="rh-list-empty">
                                    <i class="bi bi-inbox"></i>
                                    <p>Aucune demande en attente</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alertes actives -->
                    <div class="col-md-6">
                        <div class="rh-card">
                            <div class="rh-card-header">
                                <h2 class="rh-card-title">
                                    <i class="bi bi-bell me-2 text-danger"></i>Alertes actives
                                </h2>
                                <a href="/rh/alerts" class="btn btn-sm btn-outline-primary">Voir tout</a>
                            </div>
                            <div class="rh-card-body rh-list">
                                <template v-if="stats.recent_alerts?.length">
                                    <article
                                        v-for="alert in stats.recent_alerts"
                                        :key="alert.id"
                                        class="rh-list-item"
                                    >
                                        <div class="rh-list-icon bg-danger-subtle">
                                            <i class="bi bi-exclamation text-danger"></i>
                                        </div>
                                        <div class="rh-list-content">
                                            <div class="rh-list-name">{{ alert.titre }}</div>
                                            <div class="rh-list-meta">
                                                Échéance : {{ formatDate(alert.date_echeance) }}
                                            </div>
                                        </div>
                                        <span class="rh-alert-type">{{ alert.type }}</span>
                                    </article>
                                </template>
                                <div v-else class="rh-list-empty">
                                    <i class="bi bi-inbox"></i>
                                    <p>Aucune alerte active</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes de frais en attente -->
                    <div class="col-md-6">
                        <div class="rh-card">
                            <div class="rh-card-header">
                                <h2 class="rh-card-title">
                                    <i class="bi bi-cash me-2 text-info"></i>Frais en attente
                                </h2>
                                <a href="/rh/expenses" class="btn btn-sm btn-outline-primary">Voir tout</a>
                            </div>
                            <div class="rh-card-body rh-list">
                                <template v-if="stats.pending_expenses_list?.length">
                                    <article
                                        v-for="expense in stats.pending_expenses_list"
                                        :key="expense.id"
                                        class="rh-list-item"
                                    >
                                        <div class="rh-list-icon bg-info-subtle">
                                            <i class="bi bi-receipt text-info"></i>
                                        </div>
                                        <div class="rh-list-content">
                                            <div class="rh-list-name">{{ expense.employee }}</div>
                                            <div class="rh-list-meta">
                                                {{ expense.categorie }} —
                                                {{ formatCurrency(expense.montant) }}
                                            </div>
                                        </div>
                                    </article>
                                </template>
                                <div v-else class="rh-list-empty">
                                    <i class="bi bi-inbox"></i>
                                    <p>Aucune note de frais en attente</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import GelLayout from '../../../Layouts/GelLayout.vue'
import RhStatCard from '../../../Components/Rh/RhStatCard.vue'

// ─── Configuration des cartes statistiques ────────────────────
const statCards = [
    { key: 'employees',      icon: 'bi-people',              label: 'Employés',         field: 'total_employees',  color: 'primary' },
    { key: 'actifs',         icon: 'bi-person-check',        label: 'Actifs',           field: 'active_employees', color: 'success' },
    { key: 'contrats',       icon: 'bi-file-text',           label: 'Contrats',         field: 'total_contracts',  color: 'info' },
    { key: 'contrats-actifs',icon: 'bi-file-earmark-check',  label: 'Contrats actifs',  field: 'active_contracts', color: 'success' },
    { key: 'conges',         icon: 'bi-calendar-check',      label: 'Congés en attente',field: 'pending_leaves',   color: 'warning' },
    { key: 'frais',          icon: 'bi-cash-stack',          label: 'Frais en attente', field: 'pending_expenses', color: 'warning' },
    { key: 'paies',          icon: 'bi-calculator',          label: 'Paies en brouillon',field: 'pending_payrolls',color: 'info' },
    { key: 'alertes',        icon: 'bi-exclamation-triangle', label: 'Alertes',         field: 'active_alerts',    color: 'danger' },
]

// ─── État ─────────────────────────────────────────────────────
const state = ref('loading')       // 'loading' | 'loaded' | 'error'
const errorMessage = ref('')
const stats = ref({})

// ─── Date formatée ────────────────────────────────────────────
const formattedDate = computed(() =>
    new Date().toLocaleDateString('fr-FR', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    })
)

// ─── Formateurs métier ────────────────────────────────────────
function formatCurrency(value) {
    if (value == null) return '—'
    const num = Number(value)
    return Number.isNaN(num) ? '—' : `${num.toLocaleString('fr-FR')} FCFA`
}

function formatDate(dateStr) {
    if (!dateStr) return '—'
    try {
        return new Date(dateStr).toLocaleDateString('fr-FR', {
            day: 'numeric', month: 'short', year: 'numeric',
        })
    } catch {
        return dateStr
    }
}

function formatDateRange(start, end) {
    const d1 = formatDate(start)
    const d2 = formatDate(end)
    return d1 === d2 ? d1 : `du ${d1} au ${d2}`
}

function employeeStatusClass(status) {
    return (
        { actif: 'rh-badge--success', suspendu: 'rh-badge--warning' }[status]
        ?? 'rh-badge--secondary'
    )
}

// ─── Chargement des statistiques ──────────────────────────────
async function fetchStats() {
    state.value = 'loading'
    errorMessage.value = ''

    try {
        const { data } = await window.axios.get('/rh/api/stats')
        stats.value = data
        state.value = 'loaded'
    } catch (err) {
        console.error('[RH Dashboard]', err)
        errorMessage.value =
            err?.response?.data?.message
            || err?.message
            || 'Impossible de charger les données. Vérifiez votre connexion et réessayez.'
        state.value = 'error'
    }
}

onMounted(() => fetchStats())
</script>

<style scoped>
/* ═══════════════════════════════════════════════════════════════
   RH Dashboard — Design professionnel (institutions, ministères)
   ═══════════════════════════════════════════════════════════════ */

/* ─── Layout ─────────────────────────────────────────────────── */
.rh-dashboard {
    padding: 1.5rem;
    max-width: 1440px;
    margin: 0 auto;
}

/* ─── Loading ────────────────────────────────────────────────── */
.rh-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 400px;
    gap: 1rem;
}
.rh-loading .spinner-border { width: 3rem; height: 3rem; }
.rh-loading-text {
    color: var(--bs-secondary-color, #6c757d);
    font-size: 0.9rem;
    margin: 0;
}

/* ─── Error ──────────────────────────────────────────────────── */
.rh-error {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 400px;
    text-align: center;
    gap: 0.75rem;
    padding: 2rem;
}
.rh-error-icon { font-size: 3rem; color: var(--bs-danger); }
.rh-error-title { font-size: 1.25rem; font-weight: 600; margin: 0; }
.rh-error-message {
    color: var(--bs-secondary-color);
    max-width: 480px;
    margin: 0 0 0.5rem;
}

/* ─── En-tête ────────────────────────────────────────────────── */
.rh-header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    gap: 1rem;
}
.rh-header-left { flex: 1; min-width: 0; }
.rh-title {
    font-family: 'Outfit', sans-serif;
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 0.25rem;
    display: flex;
    align-items: center;
}
.rh-subtitle {
    color: var(--bs-secondary-color);
    font-size: 0.85rem;
    margin: 0;
}
.rh-header-right { flex-shrink: 0; }
.rh-header-right .btn {
    border-radius: 6px;
    padding: 0.5rem 1.25rem;
    font-weight: 600;
}

/* ─── Grille de statistiques (8 colonnes) ────────────────────── */
.rh-stats-grid {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}
.rh-stats-item { min-width: 0; }

@media (max-width: 1200px) { .rh-stats-grid { grid-template-columns: repeat(4, 1fr); } }
@media (max-width: 576px)  { .rh-stats-grid { grid-template-columns: repeat(2, 1fr); } }

/* ─── Carte générique ────────────────────────────────────────── */
.rh-card {
    background: #fff;
    border: 1px solid var(--bs-border-color, #e9ecef);
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    height: 100%;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: box-shadow 0.2s ease;
}
.rh-card:hover { box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); }
.rh-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--bs-border-color, #e9ecef);
    background: #fafafa;
}
.rh-card-title {
    font-size: 0.85rem;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
}
.rh-card-body { flex: 1; padding: 0; }

/* ─── Liste dans les cartes ──────────────────────────────────── */
.rh-list { display: flex; flex-direction: column; }
.rh-list-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.6rem 1rem;
    border-bottom: 1px solid var(--bs-border-color, #f0f0f0);
    transition: background 0.15s ease;
}
.rh-list-item:last-child { border-bottom: none; }
.rh-list-item:hover { background: rgba(0, 0, 0, 0.02); }
.rh-list-icon {
    width: 36px; height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1rem;
}
.rh-list-content { flex: 1; min-width: 0; }
.rh-list-name {
    font-size: 0.85rem;
    font-weight: 600;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.rh-list-meta {
    font-size: 0.78rem;
    color: var(--bs-secondary-color);
    line-height: 1.3;
}

/* ─── Badges ──────────────────────────────────────────────────── */
.rh-badge {
    display: inline-block;
    padding: 0.2em 0.65em;
    font-size: 0.72rem;
    font-weight: 600;
    line-height: 1.4;
    border-radius: 50px;
    white-space: nowrap;
    flex-shrink: 0;
}
.rh-badge--success { background: #d1fae5; color: #065f46; }
.rh-badge--warning { background: #fef3c7; color: #92400e; }
.rh-badge--secondary { background: #f3f4f6; color: #6b7280; }
.rh-alert-type {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 0.15em 0.5em;
    border-radius: 4px;
    background: #fee2e2;
    color: #b91c1c;
    flex-shrink: 0;
}

/* ─── État vide ───────────────────────────────────────────────── */
.rh-list-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    color: var(--bs-secondary-color);
    gap: 0.5rem;
}
.rh-list-empty i { font-size: 2rem; opacity: 0.4; }
.rh-list-empty p { font-size: 0.85rem; margin: 0; }

/* ─── Responsive ──────────────────────────────────────────────── */
@media (max-width: 768px) {
    .rh-dashboard { padding: 1rem; }
    .rh-header { flex-direction: column; align-items: stretch; }
    .rh-header-right { align-self: flex-end; }
    .rh-title { font-size: 1.25rem; }
}

/* ─── Print ───────────────────────────────────────────────────── */
@media print {
    .rh-dashboard { padding: 0; max-width: none; }
    .rh-header-right { display: none; }
    .rh-card { break-inside: avoid; box-shadow: none; border: 1px solid #ddd; }
}
</style>
