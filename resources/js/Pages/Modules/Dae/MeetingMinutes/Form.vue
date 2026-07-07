<template>
    <GelLayout>
        <div class="dae-courriers-index">
            <div class="dae-content" style="max-width: 900px;">
                <!-- Header -->
                <div class="mb-4">
                    <a href="/dae/pv-reunions" class="btn btn-outline-secondary btn-sm mb-3">
                        <i class="bi bi-arrow-left me-1"></i>Retour
                    </a>
                    <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">
                        <i class="bi bi-file-earmark-text me-2 text-primary"></i>
                        {{ isEdit ? 'Modifier le PV' : 'Nouveau procès-verbal' }}
                    </h1>
                </div>

                <form @submit.prevent="save">
                    <!-- Infos générales -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-3" style="border-bottom: 2px solid #ff7900; display: inline-block; padding-bottom: 0.2rem;">Informations générales</h6>
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-medium">Titre *</label>
                                    <input v-model="form.titre" class="form-control" required maxlength="500">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Date de réunion *</label>
                                    <input v-model="form.date_reunion" type="date" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Client *</label>
                                    <select v-model="form.client_id" class="form-select" required>
                                        <option value="">Sélectionner</option>
                                        <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company_name }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Lieu</label>
                                    <input v-model="form.lieu" class="form-control" placeholder="Salle, visio...">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Heure début</label>
                                    <input v-model="form.heure_debut" type="time" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Heure fin</label>
                                    <input v-model="form.heure_fin" type="time" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-medium">Objet</label>
                                    <textarea v-model="form.objet" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Participants -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="border-bottom: 2px solid #ff7900; display: inline-block; padding-bottom: 0.2rem;">Participants</h6>
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="addParticipant">
                                    <i class="bi bi-plus-lg me-1"></i>Ajouter
                                </button>
                            </div>
                            <div v-for="(p, i) in form.participants" :key="i" class="row g-2 align-items-end mb-2 pb-2" style="border-bottom: 1px solid var(--bs-border-color);">
                                <div class="col-md-4">
                                    <input v-model="p.nom" class="form-control form-control-sm" placeholder="Nom">
                                </div>
                                <div class="col-md-4">
                                    <input v-model="p.email" class="form-control form-control-sm" placeholder="Email">
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input v-model="p.present" type="checkbox" class="form-check-input" :id="'present-' + i">
                                        <label class="form-check-label small" :for="'present-' + i">Présent</label>
                                    </div>
                                </div>
                                <div class="col-md-1 text-end">
                                    <button type="button" class="btn btn-sm btn-outline-danger" @click="form.participants.splice(i, 1)" title="Supprimer">&times;</button>
                                </div>
                            </div>
                            <small v-if="!form.participants.length" class="text-muted">Aucun participant ajouté</small>
                        </div>
                    </div>

                    <!-- Ordre du jour -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="border-bottom: 2px solid #ff7900; display: inline-block; padding-bottom: 0.2rem;">Ordre du jour</h6>
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="form.ordre_du_jour.push('')">
                                    <i class="bi bi-plus-lg me-1"></i>Ajouter un point
                                </button>
                            </div>
                            <div v-for="(_, i) in form.ordre_du_jour" :key="i" class="mb-3 pb-3" style="border-bottom: 1px solid var(--bs-border-color);">
                                <div class="input-group input-group-sm mb-1">
                                    <span class="input-group-text">{{ i + 1 }}</span>
                                    <input v-model="form.ordre_du_jour[i]" class="form-control" :placeholder="'Point n°' + (i + 1)">
                                    <button type="button" class="btn btn-outline-danger" @click="form.ordre_du_jour.splice(i, 1); if (form.discussion) form.discussion.splice(i, 1)">&times;</button>
                                </div>
                                <textarea v-model="form.discussion[i]" class="form-control form-control-sm" rows="2" placeholder="Discussion / conclusion sur ce point"></textarea>
                            </div>
                            <small v-if="!form.ordre_du_jour.length" class="text-muted">Aucun point à l'ordre du jour</small>
                        </div>
                    </div>

                    <!-- Décisions -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0" style="border-bottom: 2px solid #ff7900; display: inline-block; padding-bottom: 0.2rem;">Décisions</h6>
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="addDecision">
                                    <i class="bi bi-plus-lg me-1"></i>Ajouter
                                </button>
                            </div>
                            <div v-for="(d, i) in form.decisions" :key="i" class="p-2 mb-2 rounded-3" style="background: #fafafa;">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <input v-model="d.decision" class="form-control form-control-sm" placeholder="Décision">
                                    </div>
                                    <div class="col-md-4">
                                        <input v-model="d.responsable" class="form-control form-control-sm" placeholder="Responsable">
                                    </div>
                                    <div class="col-md-3">
                                        <input v-model="d.echeance" type="date" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-4">
                                        <select v-model="d.statut" class="form-select form-select-sm">
                                            <option value="a_faire">À faire</option>
                                            <option value="en_cours">En cours</option>
                                            <option value="terminee">Terminée</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-sm btn-outline-danger w-100" @click="form.decisions.splice(i, 1)" title="Supprimer">&times;</button>
                                    </div>
                                </div>
                            </div>
                            <small v-if="!form.decisions.length" class="text-muted">Aucune décision</small>
                        </div>
                    </div>

                    <!-- Prochaine réunion -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-3" style="border-bottom: 2px solid #ff7900; display: inline-block; padding-bottom: 0.2rem;">Prochaine réunion</h6>
                            <input v-model="form.prochaine_reunion" type="date" class="form-control" style="max-width: 300px;">
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <a href="/dae/pv-reunions" class="btn btn-outline-secondary btn-sm">Annuler</a>
                        <button type="submit" class="btn btn-primary btn-sm" :disabled="saving">
                            <i class="bi bi-save me-1"></i>{{ saving ? 'Enregistrement...' : 'Enregistrer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import GelLayout from '../../../../Layouts/GelLayout.vue'

const id = computed(() => {
    const match = window.location.pathname.match(/\/dae\/pv-reunions\/(\d+)(?:\/edit)?/)
    return match ? match[1] : null
})
const isEdit = computed(() => !!id.value)
const saving = ref(false)
const clients = ref([])

const form = reactive({
    client_id: '', titre: '', objet: '', lieu: '', date_reunion: '',
    heure_debut: '', heure_fin: '', participants: [],
    ordre_du_jour: [], discussion: [], decisions: [], prochaine_reunion: '',
})

function addParticipant() { form.participants.push({ nom: '', email: '', present: true }) }
function addDecision() { form.decisions.push({ decision: '', responsable: '', echeance: '', statut: 'a_faire' }) }

async function fetchMinute() {
    if (!isEdit.value) return
    try {
        const res = await window.axios.get(`/dae/pv-reunions/${id.value}`)
        const data = res.data
        Object.assign(form, {
            client_id: data.client_id || '', titre: data.titre || '', objet: data.objet || '',
            lieu: data.lieu || '', date_reunion: data.date_reunion || '',
            heure_debut: data.heure_debut || '', heure_fin: data.heure_fin || '',
            participants: data.participants || [], ordre_du_jour: data.ordre_du_jour || [],
            discussion: data.discussion || [], decisions: data.decisions || [],
            prochaine_reunion: data.prochaine_reunion || '',
        })
    } catch (err) { console.error(err) }
}

async function fetchClients() {
    try { const res = await window.axios.get('/api/clients/list'); clients.value = res.data || [] }
    catch { /* ignore */ }
}

async function save() {
    saving.value = true
    try {
        const payload = { ...form }
        if (isEdit.value) {
            await window.axios.put(`/dae/pv-reunions/${id.value}`, payload)
        } else {
            await window.axios.post('/dae/pv-reunions', payload)
        }
        window.location.href = '/dae/pv-reunions'
    } catch (err) {
        console.error('Erreur:', err)
        alert("Erreur lors de l'enregistrement.")
    } finally { saving.value = false }
}

onMounted(() => { fetchClients(); fetchMinute() })
</script>

<style scoped></style>
