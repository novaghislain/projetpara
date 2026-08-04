<template>
    <div class="container-fluid py-3">
        <div class="d-flex align-items-center gap-2 mb-3">
            <a :href="'/comptabilite/ecritures'" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
            <h4 class="fw-bold mb-0"><i class="bi bi-pencil-square me-2"></i>Détail écriture</h4>
        </div>

        <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <template v-else-if="entry">
            <!-- Info cards -->
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <small class="text-muted d-block">Référence</small>
                            <code class="fw-bold fs-6">{{ entry.reference }}</code>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Date</small>
                            <span class="fw-semibold">{{ formatDate(entry.entry_date || entry.date) }}</span>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">Journal</small>
                            <span class="badge bg-dark">{{ entry.journal?.code }}</span>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">N° Pièce</small>
                            <span>{{ entry.numero_piece || '—' }}</span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Statut</small>
                            <span :class="statusBadge(entry.status)">{{ statusLabel(entry.status) }}</span>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">Libellé</small>
                            <span>{{ entry.description || entry.libelle }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lines -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-list-columns me-2"></i>Lignes d'écriture</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3">Compte</th>
                                <th class="px-3">Libellé</th>
                                <th class="px-3 text-end">Débit</th>
                                <th class="px-3 text-end">Crédit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="line in (entry.lines || entry.lignes || [])" :key="line.id">
                                <td class="px-3">
                                    <code>{{ line.account?.code || line.account_code }}</code>
                                    — {{ line.account?.name || line.account_label }}
                                </td>
                                <td class="px-3">{{ line.label || line.libelle }}</td>
                                <td class="px-3 text-end text-success fw-medium">{{ line.debit > 0 ? fmt(line.debit) : '—' }}</td>
                                <td class="px-3 text-end fw-medium" style="color:#163A5E">{{ line.credit > 0 ? fmt(line.credit) : '—' }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="2" class="px-3 text-end">TOTAUX</td>
                                <td class="px-3 text-end">{{ fmt(entry.total_debit || 0) }}</td>
                                <td class="px-3 text-end">{{ fmt(entry.total_credit || 0) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-flex gap-2">
                <button v-if="entry.status === 'draft'" class="btn btn-success btn-sm" @click="postEntry">
                    <i class="bi bi-check-circle me-1"></i>Valider l'écriture
                </button>
                <button class="btn btn-outline-primary btn-sm" @click="printEntry">
                    <i class="bi bi-printer me-1"></i>Imprimer
                </button>
            </div>
        </template>
    </div>
</template>

<script setup>
/*
 * EntryDetail.vue - Détail d'une écriture comptable
 *
 * Affiche les informations détaillées d'une écriture comptable :
 * référence, date, journal, statut, libellé et lignes d'écriture
 * (compte, libellé, débit, crédit). Permet de valider une écriture
 * en statut "brouillon" ou de l'imprimer.
 */
import { ref, computed, onMounted } from 'vue'

// Propriété : identifiant de l'écriture à afficher (depuis l'URL)
const props = defineProps({ entryId: [String, Number] })

// État réactif : données de l'écriture, chargement, erreur
const entry = ref(null)
const loading = ref(true)
const error = ref('')

// Formateur monétaire en francs CFA (sans décimales)
const fmt = (v) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(v || 0) + ' F'
// Jeton CSRF pour les requêtes sécurisées
const csrf = computed(() => document.querySelector('meta[name=csrf-token]')?.content || '')
// Fonction utilitaire d'appel API avec en-têtes par défaut
const api = (path, opts = {}) => fetch(path, { headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf.value, ...opts.headers }, ...opts })

// Chargement des données de l'écriture depuis l'API
async function loadEntry() {
    loading.value = true
    try {
        const r = await api(`/api/entries/${props.entryId}`)
        if (!r.ok) throw new Error('Écriture introuvable')
        entry.value = (await r.json()).data || await r.json()
    } catch (e) { error.value = e.message } finally { loading.value = false }
}

// Validation de l'écriture (passage du statut à "posté")
async function postEntry() {
    if (!confirm('Valider cette écriture ?')) return
    try {
        const r = await api(`/api/entries/${props.entryId}/post`, { method: 'POST' })
        if (!r.ok) throw new Error('Erreur validation')
        entry.value.status = 'posted'
    } catch (e) { error.value = e.message }
}

// Impression de la page via le navigateur
function printEntry() { window.print() }

// Formatage d'une date au format français JJ/MM/AAAA
const formatDate = (d) => d ? new Date(d).toLocaleDateString('fr-FR') : '—'
// Libellé lisible du statut de l'écriture
const statusLabel = (s) => ({ posted: 'Validée', draft: 'Brouillon', cancelled: 'Annulée' })[s] || s
// Classe CSS du badge selon le statut de l'écriture
const statusBadge = (s) => ({ posted: 'badge bg-success', draft: 'badge bg-warning text-dark', cancelled: 'badge bg-secondary' })[s] || 'badge bg-secondary'

// Chargement automatique au montage du composant
onMounted(loadEntry)
</script>
