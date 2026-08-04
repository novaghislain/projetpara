<!--
 * Composant : Liste des procès-verbaux de réunion
 * Description : Affiche la liste paginée des PV de réunion avec filtres (statut, date, client).
 *              Les PV sont présentés sous forme de cartes avec possibilité de création.
 * Utilisation : Page /dae/pv-reunions
-->
<template>
    <GelLayout>
        <div class="dae-courriers-index">
            <!-- ═══ MAIN CONTENT ═══ -->
            <div class="dae-content">
                <!-- Header -->
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <a href="/dae" class="btn btn-outline-secondary btn-sm" title="Retour">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <div>
                            <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">
                                <i class="bi bi-file-earmark-text me-2 text-primary"></i>Procès-Verbaux de Réunions
                            </h1>
                            <p class="text-muted small mb-0">
                                <i class="bi bi-journal-text me-1"></i>Rédaction, gestion et approbation des PV
                                <span class="mx-2">|</span>
                                <span v-if="totalItems > 0">{{ totalItems }} PV</span>
                            </p>
                        </div>
                    </div>
                    <div>
                        <button class="btn btn-outline-secondary btn-sm me-2" @click="fetchMinutes" title="Actualiser">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                        <a href="/dae/pv-reunions/create" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-lg me-1"></i>Nouveau PV
                        </a>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body py-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-6 col-md-3">
                                <label class="form-label small text-muted mb-1">Statut</label>
                                <select v-model="filters.statut" class="form-select form-select-sm" @change="fetchMinutes">
                                    <option value="">Tous</option>
                                    <option value="projet">Projet</option>
                                    <option value="final">Final</option>
                                    <option value="approuve">Approuvé</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small text-muted mb-1">Du</label>
                                <input v-model="filters.from" type="date" class="form-control form-control-sm" @change="fetchMinutes">
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small text-muted mb-1">Au</label>
                                <input v-model="filters.to" type="date" class="form-control form-control-sm" @change="fetchMinutes">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small text-muted mb-1">Client</label>
                                <select v-model="filters.client_id" class="form-select form-select-sm" @change="fetchMinutes">
                                    <option value="">Tous</option>
                                    <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company_name }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading -->
                <div v-if="loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0 small">Chargement des PV...</p>
                </div>

                <!-- Empty -->
                <div v-else-if="minutes.length === 0" class="text-center py-5 text-muted">
                    <i class="bi bi-file-earmark-text" style="font-size: 2rem;"></i>
                    <p class="mt-2 mb-1">Aucun procès-verbal trouvé</p>
                    <a href="/dae/pv-reunions/create" class="btn btn-primary btn-sm mt-2">
                        <i class="bi bi-plus-lg me-1"></i>Rédiger le premier PV
                    </a>
                </div>

                <!-- Cards -->
                <div v-else class="row g-3">
                    <div v-for="m in minutes" :key="m.id" class="col-12 col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100" @click="goToShow(m.id)" role="button">
                            <div class="card-body p-3 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="fw-bold mb-0 text-truncate me-2">{{ m.titre }}</h6>
                                    <span class="badge flex-shrink-0" :class="statutBadge(m.statut)">{{ statutLabel(m.statut) }}</span>
                                </div>
                                <div v-if="m.objet" class="small text-muted mb-2 text-truncate">{{ m.objet }}</div>
                                <div class="d-flex flex-wrap gap-2 small text-muted mb-2">
                                    <span><i class="bi bi-calendar me-1"></i>{{ formatDate(m.date_reunion) }}</span>
                                    <span v-if="m.lieu"><i class="bi bi-geo-alt me-1"></i>{{ m.lieu }}</span>
                                    <span v-if="m.redacteur"><i class="bi bi-person me-1"></i>{{ m.redacteur.name }}</span>
                                </div>
                                <div v-if="m.decisions?.length" class="mt-auto pt-2 border-top small">
                                    <span class="text-success">
                                        <i class="bi bi-check2-square me-1"></i>{{ m.decisions.length }} décision(s)
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="pagination.last_page > 1" class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted">Page {{ pagination.current_page }} / {{ pagination.last_page }}</small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item" :class="{ disabled: !pagination.prev_page_url }">
                                <button class="page-link" @click="goPage(pagination.current_page - 1)"><i class="bi bi-chevron-left"></i></button>
                            </li>
                            <li class="page-item" :class="{ disabled: !pagination.next_page_url }">
                                <button class="page-link" @click="goPage(pagination.current_page + 1)"><i class="bi bi-chevron-right"></i></button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import GelLayout from '../../../../Layouts/GelLayout.vue'

const loading = ref(false)
const minutes = ref([])           /* Liste des PV affichés */
const clients = ref([])           /* Liste des clients pour le filtre */
const totalItems = ref(0)

/* Filtres de recherche réactifs */
const filters = reactive({ statut: '', from: '', to: '', client_id: '' })
/* État de la pagination */
const pagination = reactive({ current_page: 1, last_page: 1, prev_page_url: null, next_page_url: null })

/* Formate une date au format français court */
function formatDate(d) {
    if (!d) return ''
    return new Date(d).toLocaleDateString('fr-FR', { year: 'numeric', month: 'short', day: 'numeric' })
}
/* Retourne la classe Bootstrap selon le statut du PV */
function statutBadge(s) {
    return { projet: 'bg-warning text-dark', final: 'bg-info', approuve: 'bg-success' }[s] || 'bg-secondary'
}
/* Libellé du statut du PV */
function statutLabel(s) {
    return { projet: 'Projet', final: 'Final', approuve: 'Approuvé' }[s] || s
}
/* Redirige vers la page de détail */
function goToShow(id) { window.location.href = '/dae/pv-reunions/' + id }

/* Récupère les PV avec filtres et pagination */
async function fetchMinutes(page = 1) {
    loading.value = true
    try {
        const params = { page, ...filters }
        const res = await window.axios.get('/dae/pv-reunions', { params })
        const data = res.data
        minutes.value = data.data || []
        totalItems.value = data.total || 0
        pagination.current_page = data.current_page
        pagination.last_page = data.last_page
        pagination.prev_page_url = data.prev_page_url
        pagination.next_page_url = data.next_page_url
    } catch (err) { console.error(err) }
    finally { loading.value = false }
}
/* Change de page dans la pagination */
function goPage(page) {
    if (page >= 1 && page <= pagination.last_page) fetchMinutes(page)
}
/* Charge la liste des clients pour le filtre */
async function fetchClients() {
    try { const res = await window.axios.get('/api/clients/list'); clients.value = res.data || [] }
    catch { /* ignore */ }
}

/* Initialisation au montage : charge les PV et les clients */
onMounted(() => { fetchMinutes(); fetchClients() })
</script>

<style scoped></style>
