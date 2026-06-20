<script setup>
import { ref, onMounted } from 'vue'
import GelLayout from '../../../Layouts/GelLayout.vue'

/* ══════════════════════════════════════════
   Props
   ══════════════════════════════════════════ */
const props = defineProps({
    missionId: { type: [Number, String], default: null },
})

/* ══════════════════════════════════════════
   State
   ══════════════════════════════════════════ */
const state = ref('loading')  // 'loading' | 'error' | 'loaded'
const errorMsg = ref('')
const toast = ref('')
const toastType = ref('success')
const submitting = ref(false)
let toastTimer = null

const mission = ref(null)
const clients = ref([])
const poles = ref([])

const form = ref({
    title: '', description: '', type: 'mission', status: 'en_attente', priority: 'moyenne',
    client_id: '', pole_id: '', assigned_to: '', start_date: '', end_date: '', budget: '', progress: 0,
})

/* ══════════════════════════════════════════
   Options
   ══════════════════════════════════════════ */
const statusOptions = ['en_attente', 'en_cours', 'terminee', 'annulee']
const priorityOptions = ['basse', 'moyenne', 'haute', 'critique']
const typeOptions = ['mission', 'tache', 'projet']

/* ══════════════════════════════════════════
   Toast helper
   ══════════════════════════════════════════ */
function showToast(msg, type = 'success') {
    toast.value = msg
    toastType.value = type
    clearTimeout(toastTimer)
    toastTimer = setTimeout(() => { toast.value = '' }, 3500)
}

/* ══════════════════════════════════════════
   Data loading
   ══════════════════════════════════════════ */
async function fetchData() {
    state.value = 'loading'
    errorMsg.value = ''
    try {
        const [clientsRes, polesRes] = await Promise.all([
            window.axios.get('api/clients'),
            window.axios.get('api/poles'),
        ])
        clients.value = clientsRes.data || []
        poles.value = polesRes.data || []

        if (props.missionId) {
            const res = await window.axios.get('api/missions/' + props.missionId)
            const data = res.data
            mission.value = data
            form.value = {
                title: data.title || '',
                description: data.description || '',
                type: data.type || 'mission',
                status: data.status || 'en_attente',
                priority: data.priority || 'moyenne',
                client_id: data.client_id || '',
                pole_id: data.pole_id || '',
                assigned_to: data.assigned_to || '',
                start_date: data.start_date || '',
                end_date: data.end_date || '',
                budget: data.budget || '',
                progress: data.progress || 0,
            }
        }

        state.value = 'loaded'
    } catch (err) {
        console.error('Erreur chargement formulaire mission:', err)
        errorMsg.value = err.response?.data?.message || err.message || 'Erreur de chargement des données'
        state.value = 'error'
    }
}

/* ══════════════════════════════════════════
   Submit
   ══════════════════════════════════════════ */
async function submitForm() {
    submitting.value = true
    try {
        const isEdit = !!props.missionId
        const url = isEdit ? 'missions/' + props.missionId : 'missions'
        const method = isEdit ? 'put' : 'post'
        const payload = { ...form.value, budget: form.value.budget ? Number(form.value.budget) : null }

        const res = await window.axios[method](url, payload)
        showToast('Mission enregistrée avec succès')
        if (!isEdit && res.data?.id) {
            setTimeout(() => {
                window.location.href = '/missions/' + res.data.id
            }, 600)
        }
    } catch (err) {
        const msg = err.response?.data?.message
            || (err.response?.data?.errors ? Object.values(err.response.data.errors).flat().join(', ') : '')
            || err.message
            || "Erreur lors de l'enregistrement"
        showToast(msg, 'error')
    } finally {
        submitting.value = false
    }
}

const goBack = () => {
    window.history.back()
}

onMounted(fetchData)
</script>

<template>
    <GelLayout :page-title="missionId ? (mission?.title || 'Modifier la mission') : 'Nouvelle mission'">
        <!-- ═══ LOADING ═══ -->
        <div v-if="state === 'loading'" class="mf-state">
            <div class="mf-spinner"></div>
            <p class="mf-state-text">Chargement du formulaire...</p>
        </div>

        <!-- ═══ ERROR ═══ -->
        <div v-else-if="state === 'error'" class="mf-state">
            <i class="bi bi-exclamation-triangle mf-state-icon"></i>
            <p class="mf-state-text">{{ errorMsg }}</p>
            <button class="mf-retry-btn" @click="fetchData">
                <i class="bi bi-arrow-clockwise me-1"></i>Réessayer
            </button>
        </div>

        <!-- ═══ CONTENT ═══ -->
        <div v-else class="mf-card">
            <!-- ── Toast ── -->
            <div v-if="toast" class="mf-toast" :class="toastType === 'error' ? 'mf-toast--error' : 'mf-toast--success'">
                <i :class="toastType === 'error' ? 'bi bi-exclamation-circle' : 'bi bi-check-circle'" class="me-1"></i>
                {{ toast }}
            </div>

            <form @submit.prevent="submitForm">
                <!-- ══ Section 1: Informations générales ══ -->
                <div class="mf-section">
                    <h6 class="mf-section-title">Informations générales</h6>
                    <div class="mf-grid">
                        <div class="mf-field mf-field--full">
                            <label class="mf-label">Titre <span class="mf-req">*</span></label>
                            <input v-model="form.title" class="mf-input" required placeholder="Titre de la mission">
                        </div>
                        <div class="mf-field mf-field--full">
                            <label class="mf-label">Description</label>
                            <textarea v-model="form.description" class="mf-input mf-textarea" rows="3" placeholder="Description..."></textarea>
                        </div>
                        <div class="mf-field">
                            <label class="mf-label">Type</label>
                            <select v-model="form.type" class="mf-input">
                                <option v-for="t in typeOptions" :key="t" :value="t">{{ t }}</option>
                            </select>
                        </div>
                        <div class="mf-field">
                            <label class="mf-label">Statut</label>
                            <select v-model="form.status" class="mf-input">
                                <option v-for="s in statusOptions" :key="s" :value="s">{{ s }}</option>
                            </select>
                        </div>
                        <div class="mf-field">
                            <label class="mf-label">Priorité</label>
                            <select v-model="form.priority" class="mf-input">
                                <option v-for="p in priorityOptions" :key="p" :value="p">{{ p }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- ══ Section 2: Affectation ══ -->
                <div class="mf-section">
                    <h6 class="mf-section-title">Affectation</h6>
                    <div class="mf-grid">
                        <div class="mf-field">
                            <label class="mf-label">Client</label>
                            <select v-model="form.client_id" class="mf-input">
                                <option value="">Sélectionner un client</option>
                                <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company_name }}</option>
                            </select>
                        </div>
                        <div class="mf-field">
                            <label class="mf-label">Pôle</label>
                            <select v-model="form.pole_id" class="mf-input">
                                <option value="">Sélectionner un pôle</option>
                                <option v-for="p in poles" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                        </div>
                        <div class="mf-field">
                            <label class="mf-label">Assigné à</label>
                            <input v-model="form.assigned_to" class="mf-input" placeholder="Nom du responsable">
                        </div>
                    </div>
                </div>

                <!-- ══ Section 3: Planning & Budget ══ -->
                <div class="mf-section">
                    <h6 class="mf-section-title">Planning &amp; Budget</h6>
                    <div class="mf-grid">
                        <div class="mf-field">
                            <label class="mf-label">Date début</label>
                            <input v-model="form.start_date" type="date" class="mf-input">
                        </div>
                        <div class="mf-field">
                            <label class="mf-label">Date fin</label>
                            <input v-model="form.end_date" type="date" class="mf-input">
                        </div>
                        <div class="mf-field">
                            <label class="mf-label">Budget (FCFA)</label>
                            <input v-model="form.budget" type="number" class="mf-input" min="0" step="0.01" placeholder="0">
                        </div>
                        <div class="mf-field mf-field--full">
                            <label class="mf-label">Progression : {{ form.progress }}%</label>
                            <input v-model="form.progress" type="range" class="mf-range" min="0" max="100" step="5">
                        </div>
                    </div>
                </div>

                <!-- ══ Actions ══ -->
                <div class="mf-actions">
                    <button type="button" class="mf-btn mf-btn--secondary" @click="goBack">
                        <i class="bi bi-arrow-left me-1"></i>Retour
                    </button>
                    <button type="submit" class="mf-btn mf-btn--primary" :disabled="submitting">
                        <span v-if="submitting" class="mf-spinner-sm"></span>
                        <i v-else class="bi bi-check-lg me-1"></i>
                        {{ missionId ? 'Mettre à jour' : 'Créer la mission' }}
                    </button>
                </div>
            </form>
        </div>
    </GelLayout>
</template>

<style scoped>
/* ═══════════════════════════════════════════════════════
   Mission Form — Refactored
   ═══════════════════════════════════════════════════════ */

/* ─── State ─── */
.mf-state {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 120px 20px; gap: 0.75rem;
}
.mf-spinner {
    width: 48px; height: 48px;
    border: 4px solid rgba(255,121,0,0.1); border-top-color: #ff7900;
    border-radius: 50%; animation: mf-spin 0.7s linear infinite;
}
@keyframes mf-spin { to { transform: rotate(360deg); } }
.mf-state-icon { font-size: 2.5rem; color: #ef4444; opacity: 0.6; }
.mf-state-text { color: #6c757d; font-size: 0.9rem; font-weight: 500; text-align: center; }
.mf-retry-btn {
    display: inline-flex; align-items: center;
    padding: 0.5rem 1.25rem; border: 1px solid #ff7900; border-radius: 8px;
    background: #fff; color: #ff7900; font-size: 0.82rem; font-weight: 600;
    cursor: pointer; transition: background 0.15s ease;
}
.mf-retry-btn:hover { background: rgba(255,121,0,0.08); }

/* ─── Toast ─── */
.mf-toast {
    display: flex; align-items: center;
    padding: 0.65rem 1rem; border-radius: 8px;
    font-size: 0.82rem; margin-bottom: 1rem; gap: 0.3rem;
    animation: mf-slide-in 0.25s ease;
}
@keyframes mf-slide-in { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
.mf-toast--success { background: #d1fae5; color: #065f46; border-left: 3px solid #10b981; }
.mf-toast--error { background: #fee2e2; color: #b91c1c; border-left: 3px solid #ef4444; }

/* ─── Card ─── */
.mf-card {
    background: #fff; border: 1px solid #e9ecef;
    border-radius: 12px; padding: 1.5rem;
}

/* ─── Sections ─── */
.mf-section {
    margin-bottom: 1.5rem;
}
.mf-section-title {
    font-size: 0.78rem; font-weight: 700; color: #ff7900;
    text-transform: uppercase; letter-spacing: 0.04em;
    padding-bottom: 0.5rem; border-bottom: 2px solid #f0f0f0;
    margin: 0 0 1rem;
}

/* ─── Grid ─── */
.mf-grid {
    display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.85rem;
}
@media (max-width: 768px) { .mf-grid { grid-template-columns: 1fr; } }
.mf-field--full { grid-column: 1 / -1; }

/* ─── Labels ─── */
.mf-label {
    display: block; font-size: 0.72rem; font-weight: 600;
    color: #374151; margin-bottom: 0.3rem;
}
.mf-req { color: #ef4444; }

/* ─── Inputs ─── */
.mf-input {
    width: 100%; padding: 0.5rem 0.7rem; font-size: 0.82rem;
    border: 1px solid #d1d5db; border-radius: 6px;
    color: #212529; background: #fff;
    transition: border-color 0.15s ease; outline: none;
    box-sizing: border-box;
    font-family: inherit;
}
.mf-input:focus { border-color: #ff7900; box-shadow: 0 0 0 3px rgba(255,121,0,0.1); }
.mf-input::placeholder { color: #adb5bd; }
.mf-textarea { resize: vertical; min-height: 70px; }
select.mf-input { cursor: pointer; }

/* ─── Range ─── */
.mf-range {
    -webkit-appearance: none; appearance: none;
    width: 100%; height: 6px; border-radius: 3px;
    background: #e9ecef; outline: none;
    margin-top: 0.35rem;
}
.mf-range::-webkit-slider-thumb {
    -webkit-appearance: none; appearance: none;
    width: 18px; height: 18px; border-radius: 50%;
    background: #ff7900; border: 2px solid #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    cursor: pointer;
}
.mf-range::-moz-range-thumb {
    width: 18px; height: 18px; border-radius: 50%;
    background: #ff7900; border: 2px solid #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    cursor: pointer;
}

/* ─── Actions ─── */
.mf-actions {
    display: flex; gap: 0.75rem; margin-top: 1.5rem;
    padding-top: 1.25rem; border-top: 1px solid #e9ecef;
}
.mf-btn {
    display: inline-flex; align-items: center;
    padding: 0.5rem 1.1rem; border-radius: 8px;
    font-size: 0.8rem; font-weight: 600;
    cursor: pointer; transition: all 0.15s ease;
    white-space: nowrap; border: 1px solid transparent;
    text-decoration: none;
}
.mf-btn--primary {
    background: #ff7900; color: #fff; border-color: #ff7900;
}
.mf-btn--primary:hover { background: #e66c00; border-color: #e66c00; }
.mf-btn--primary:disabled { opacity: 0.6; cursor: not-allowed; }
.mf-btn--secondary {
    background: #f3f4f6; color: #374151; border-color: #d1d5db;
}
.mf-btn--secondary:hover { background: #e5e7eb; }

/* ─── Spinner sm ─── */
.mf-spinner-sm {
    display: inline-block; width: 14px; height: 14px;
    border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff;
    border-radius: 50%; animation: mf-spin 0.6s linear infinite;
    margin-right: 0.35rem;
}

/* ─── Print ─── */
@media print {
    .mf-actions { display: none; }
    .mf-card { border: 1px solid #dee2e6; }
}
</style>
