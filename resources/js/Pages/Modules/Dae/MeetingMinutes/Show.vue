<!--
 * Composant : Détail d'un procès-verbal de réunion
 * Description : Affiche le contenu complet d'un PV : objet, participants, ordre du jour,
 *              discussions, décisions, prochaine réunion. Permet la modification,
 *              la finalisation et l'approbation du PV.
 * Utilisation : Page /dae/pv-reunions/{id}
-->
<template>
    <GelLayout>
        <div class="dae-courriers-index">
            <!-- ═══ LOADING ═══ -->
            <div v-if="loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="text-muted mt-2 mb-0 small">Chargement du PV...</p>
            </div>

            <div v-else-if="!minute" class="text-center py-5 text-muted">
                <i class="bi bi-file-earmark-text" style="font-size: 2rem;"></i>
                <p class="mt-2 mb-0">PV introuvable</p>
                <a href="/dae/pv-reunions" class="btn btn-primary btn-sm mt-2">Retour aux PV</a>
            </div>

            <!-- ═══ MAIN CONTENT ═══ -->
            <div v-else class="dae-content" style="max-width: 900px;">
                <!-- Header -->
                <div class="mb-3">
                    <a href="/dae/pv-reunions" class="btn btn-outline-secondary btn-sm mb-3">
                        <i class="bi bi-arrow-left me-1"></i>Retour aux PV
                    </a>
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
                        <div>
                            <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">
                                {{ minute.titre }}
                            </h1>
                            <div class="d-flex flex-wrap gap-3 small text-muted align-items-center">
                                <span><i class="bi bi-calendar me-1"></i>{{ formatDate(minute.date_reunion) }}</span>
                                <span v-if="minute.lieu"><i class="bi bi-geo-alt me-1"></i>{{ minute.lieu }}</span>
                                <span v-if="minute.heure_debut"><i class="bi bi-clock me-1"></i>{{ minute.heure_debut }}<template v-if="minute.heure_fin"> - {{ minute.heure_fin }}</template></span>
                                <span class="badge" :class="statutBadge(minute.statut)">{{ statutLabel(minute.statut) }}</span>
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-shrink-0">
                            <a :href="'/dae/pv-reunions/' + minute.id + '/edit'" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-pencil me-1"></i>Modifier
                            </a>
                            <button v-if="minute.statut === 'projet'" class="btn btn-primary btn-sm" @click="finaliser">
                                <i class="bi bi-check-lg me-1"></i>Finaliser
                            </button>
                            <button v-if="minute.statut === 'final'" class="btn btn-success btn-sm" @click="approuver">
                                <i class="bi bi-check2-all me-1"></i>Approuver
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Objet -->
                <div v-if="minute.objet" class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-3">
                        <h6 class="fw-bold mb-2" style="border-bottom: 2px solid #ff7900; display: inline-block; padding-bottom: 0.2rem;">Objet</h6>
                        <p class="mb-0">{{ minute.objet }}</p>
                    </div>
                </div>

                <!-- Participants -->
                <div v-if="minute.participants?.length" class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-3">
                        <h6 class="fw-bold mb-3" style="border-bottom: 2px solid #ff7900; display: inline-block; padding-bottom: 0.2rem;">Participants</h6>
                        <div class="d-flex flex-column gap-2">
                            <div v-for="(p, i) in minute.participants" :key="i"
                                class="d-flex align-items-center gap-3 p-2 rounded-3" style="background: #f9fafb;">
                                <div style="font-size: 1.5rem; color: var(--bs-secondary-color);">
                                    <i class="bi bi-person-circle"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-medium small">{{ p.nom }}</div>
                                    <div v-if="p.email" class="small text-muted">{{ p.email }}</div>
                                </div>
                                <span v-if="p.present === true" class="badge bg-success flex-shrink-0">Présent</span>
                                <span v-else-if="p.present === false" class="badge bg-secondary flex-shrink-0">Absent</span>
                                <span v-else class="badge bg-light text-muted flex-shrink-0">Non spécifié</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ordre du jour -->
                <div v-if="minute.ordre_du_jour?.length" class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-3">
                        <h6 class="fw-bold mb-3" style="border-bottom: 2px solid #ff7900; display: inline-block; padding-bottom: 0.2rem;">Ordre du jour & Discussion</h6>
                        <div v-for="(point, i) in minute.ordre_du_jour" :key="i" class="d-flex gap-3 py-2" style="border-bottom: 1px solid var(--bs-border-color);">
                            <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                                style="width: 28px; height: 28px; background: #ff7900; color: #fff; font-size: 0.75rem; font-weight: 700;">
                                {{ i + 1 }}
                            </div>
                            <div class="flex-grow-1">
                                <strong class="small d-block">{{ typeof point === 'string' ? point : point.titre || point }}</strong>
                                <p v-if="minute.discussion?.[i]" class="small text-muted mb-0 mt-1">
                                    {{ typeof minute.discussion[i] === 'string' ? minute.discussion[i] : minute.discussion[i]?.contenu }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Décisions -->
                <div v-if="minute.decisions?.length" class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-0">
                        <div class="p-3 pb-0">
                            <h6 class="fw-bold mb-0" style="border-bottom: 2px solid #ff7900; display: inline-block; padding-bottom: 0.2rem;">Décisions</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Décision</th>
                                        <th>Responsable</th>
                                        <th>Échéance</th>
                                        <th class="pe-3">Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(d, i) in minute.decisions" :key="i">
                                        <td class="ps-3">{{ d.decision }}</td>
                                        <td>{{ d.responsable || '—' }}</td>
                                        <td>{{ d.echeance ? formatDate(d.echeance) : '—' }}</td>
                                        <td class="pe-3">
                                            <span class="badge" :class="decisionBadge(d.statut)">
                                                {{ decisionLabel(d.statut) }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Prochaine réunion -->
                <div v-if="minute.prochaine_reunion" class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-3">
                        <h6 class="fw-bold mb-2" style="border-bottom: 2px solid #ff7900; display: inline-block; padding-bottom: 0.2rem;">Prochaine réunion</h6>
                        <p class="mb-0"><i class="bi bi-calendar me-1"></i>{{ formatDate(minute.prochaine_reunion) }}</p>
                    </div>
                </div>

                <!-- Signature blocks -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <div class="d-flex flex-wrap gap-4">
                            <div v-if="minute.redacteur">
                                <small class="text-muted text-uppercase fw-semibold d-block mb-1" style="font-size: 0.7rem;">Rédigé par</small>
                                <span class="fw-medium">{{ minute.redacteur.name }}</span>
                            </div>
                            <div v-if="minute.approbateur">
                                <small class="text-muted text-uppercase fw-semibold d-block mb-1" style="font-size: 0.7rem;">Approuvé par</small>
                                <span class="fw-medium">{{ minute.approbateur.name }}</span>
                                <small v-if="minute.approuve_at" class="text-muted d-block" style="font-size: 0.72rem;">le {{ formatDate(minute.approuve_at) }}</small>
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
import GelLayout from '../../../../Layouts/GelLayout.vue'

/* Extrait l'ID du PV depuis l'URL */
const minuteId = computed(() => {
    const match = window.location.pathname.match(/\/dae\/pv-reunions\/(\d+)/)
    return match ? match[1] : null
})
const loading = ref(false)
const minute = ref(null)           /* Données du PV chargées depuis l'API */

/* Formate une date au format français long */
function formatDate(d) {
    if (!d) return ''
    return new Date(d).toLocaleDateString('fr-FR', { year: 'numeric', month: 'long', day: 'numeric' })
}
/* Classe Bootstrap et libellé pour le statut du PV */
function statutBadge(s) {
    return { projet: 'bg-warning text-dark', final: 'bg-info', approuve: 'bg-success' }[s] || 'bg-secondary'
}
function statutLabel(s) {
    return { projet: 'Projet', final: 'Final', approuve: 'Approuvé' }[s] || s
}
/* Classe Bootstrap et libellé pour le statut d'une décision */
function decisionBadge(s) {
    return { a_faire: 'bg-warning text-dark', en_cours: 'bg-info', terminee: 'bg-success' }[s] || 'bg-secondary'
}
function decisionLabel(s) {
    return { a_faire: 'À faire', en_cours: 'En cours', terminee: 'Terminée' }[s] || s
}

/* Charge le PV depuis l'API */
async function fetchMinute() {
    loading.value = true
    try {
        const res = await window.axios.get(`/dae/pv-reunions/${minuteId.value}`)
        minute.value = res.data
    } catch (err) { console.error(err) }
    finally { loading.value = false }
}

/* Passe le PV en statut "Final" */
async function finaliser() {
    try {
        const res = await window.axios.patch(`/dae/pv-reunions/${minute.value.id}/finaliser`)
        minute.value = res.data
        window.location.reload()
    } catch (err) { console.error(err) }
}

/* Passe le PV en statut "Approuvé" */
async function approuver() {
    try {
        const res = await window.axios.patch(`/dae/pv-reunions/${minute.value.id}/approuver`)
        minute.value = res.data
        window.location.reload()
    } catch (err) { console.error(err) }
}

onMounted(fetchMinute)
</script>

<style scoped></style>
