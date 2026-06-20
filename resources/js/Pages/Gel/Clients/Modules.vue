<script setup>
import { ref, onMounted, computed } from 'vue'
import GelLayout from '../../../Layouts/GelLayout.vue'
import { authStore } from '../../../stores/auth'

const props = defineProps({
    clientId: { type: [Number, String], default: null },
})

/* ══════════════════════════════════════════
   State
   ══════════════════════════════════════════ */
const state = ref('loading')  // 'loading' | 'error' | 'loaded'
const errorMsg = ref('')
const toast = ref('')
const toastType = ref('success')  // 'success' | 'error'
const saving = ref(false)

const availableModules = ref([])
const clientModules = ref({})
const client = ref(null)
let toastTimer = null

const activeClientId = computed(() =>
    props.clientId || authStore.user?.active_client_id || authStore.user?.client_id
)

/* ══════════════════════════════════════════
   Module config
   ══════════════════════════════════════════ */
const moduleIcons = {
    comptabilite: 'bi-calculator',
    facturation: 'bi-receipt',
    caisse: 'bi-cash-stack',
    juridique: 'bi-file-earmark-text',
    rh: 'bi-people',
    projets: 'bi-kanban',
    document: 'bi-folder2-open',
    dae: 'bi-envelope-paper',
    erp: 'bi-box-seam',
    crm: 'bi-person-lines-fill',
    it_helpdesk: 'bi-headset',
    it_assets: 'bi-laptop',
}

const moduleLabels = {
    comptabilite: 'Comptabilité',
    facturation: 'Facturation',
    caisse: 'Caisse',
    juridique: 'Juridique',
    rh: 'Ressources Humaines',
    projets: 'Projets',
    document: 'GED / Documents',
    dae: 'DAE (Secrétariat)',
    erp: 'ERP / Stock',
    crm: 'CRM',
    it_helpdesk: 'IT Helpdesk',
    it_assets: 'IT Assets',
}

const orderedModules = computed(() => {
    return [...availableModules.value].sort((a, b) => {
        const labelA = moduleLabels[a] || a
        const labelB = moduleLabels[b] || b
        return labelA.localeCompare(labelB, 'fr')
    })
})

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
async function loadData() {
    state.value = 'loading'
    errorMsg.value = ''
    try {
        // Load client info
        if (activeClientId.value) {
            const clientRes = await window.axios.get(`/api/clients/${activeClientId.value}`)
            client.value = clientRes.data
        }

        // Load permissions → available modules
        const permRes = await window.axios.get('/api/me/permissions')
        const mods = [...new Set((permRes.data.permissions || []).map(p => p.split(':')[0]))]
        availableModules.value = mods.sort()

        // Load client disabled_modules
        if (activeClientId.value) {
            const modRes = await window.axios.get(`/api/clients/${activeClientId.value}`)
            const disabled = modRes.data.disabled_modules || []
            const map = {}
            for (const mod of availableModules.value) {
                map[mod] = !disabled.includes(mod)
            }
            clientModules.value = map
        }

        state.value = 'loaded'
    } catch (err) {
        console.error('Erreur chargement modules:', err)
        errorMsg.value = err.response?.data?.message || err.message || 'Erreur de chargement des données'
        state.value = 'error'
    }
}

async function toggleModule(module) {
    saving.value = true
    errorMsg.value = ''
    const newStatus = !clientModules.value[module]
    try {
        await window.axios.put(`/api/clients/${activeClientId.value}/modules`, {
            module,
            is_active: newStatus,
        })
        clientModules.value[module] = newStatus
        showToast(`Module "${moduleLabels[module] || module}" ${newStatus ? 'activé' : 'désactivé'}`)
    } catch (err) {
        const msg = err.response?.data?.message || err.message || 'Erreur lors de la mise à jour'
        showToast(msg, 'error')
    } finally {
        saving.value = false
    }
}

onMounted(loadData)
</script>

<template>
    <GelLayout page-title="Modules Client">
        <!-- ═══ LOADING ═══ -->
        <div v-if="state === 'loading'" class="mod-state">
            <div class="mod-spinner"></div>
            <p class="mod-state-text">Chargement des modules...</p>
        </div>

        <!-- ═══ ERROR ═══ -->
        <div v-else-if="state === 'error'" class="mod-state">
            <i class="bi bi-exclamation-triangle mod-state-icon"></i>
            <p class="mod-state-text">{{ errorMsg }}</p>
            <button class="mod-retry-btn" @click="loadData">
                <i class="bi bi-arrow-clockwise me-1"></i>Réessayer
            </button>
        </div>

        <!-- ═══ CONTENT ═══ -->
        <template v-else>
            <div v-if="!activeClientId" class="mod-empty">
                <i class="bi bi-building"></i>
                <span>Aucun client sélectionné.</span>
            </div>

            <template v-else>
                <!-- ── Header ── -->
                <div class="mod-header">
                    <div>
                        <h1 class="mod-title">Gestion des Modules</h1>
                        <p class="mod-subtitle" v-if="client">
                            <i class="bi bi-building me-1"></i>{{ client.company_name || '#' + client.id }}
                        </p>
                    </div>
                    <span class="mod-count">{{ orderedModules.length }} module{{ orderedModules.length > 1 ? 's' : '' }}</span>
                </div>

                <!-- ── Toast notification ── -->
                <div v-if="toast" class="mod-toast" :class="toastType === 'error' ? 'mod-toast--error' : 'mod-toast--success'">
                    <i :class="toastType === 'error' ? 'bi bi-exclamation-circle' : 'bi bi-check-circle'" class="me-1"></i>
                    {{ toast }}
                </div>

                <!-- ── Module Grid ── -->
                <div class="mod-grid">
                    <div
                        v-for="module in orderedModules"
                        :key="module"
                        class="mod-card"
                        :class="{ 'mod-card--active': clientModules[module], 'mod-card--inactive': !clientModules[module] }"
                    >
                        <div class="mod-card-body">
                            <div class="mod-card-top">
                                <div class="mod-card-left">
                                    <div class="mod-icon" :class="clientModules[module] ? 'mod-icon--on' : 'mod-icon--off'">
                                        <i :class="moduleIcons[module] || 'bi-grid'"></i>
                                    </div>
                                    <div>
                                        <div class="mod-name">{{ moduleLabels[module] || module }}</div>
                                        <div class="mod-slug">{{ module }}</div>
                                    </div>
                                </div>
                                <label class="mod-switch">
                                    <input type="checkbox" :checked="clientModules[module]" :disabled="saving" @change="toggleModule(module)">
                                    <span class="mod-slider"></span>
                                </label>
                            </div>
                            <div class="mod-card-bottom">
                                <span class="mod-badge" :class="clientModules[module] ? 'mod-badge--on' : 'mod-badge--off'">
                                    {{ clientModules[module] ? 'Actif' : 'Inactif' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </template>
    </GelLayout>
</template>

<style scoped>
/* ═══════════════════════════════════════════════════════
   GEL Modules Client
   ═══════════════════════════════════════════════════════ */

/* ─── State ─── */
.mod-state {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 120px 20px; gap: 0.75rem;
}
.mod-spinner {
    width: 48px; height: 48px;
    border: 4px solid rgba(255,121,0,0.1); border-top-color: #ff7900;
    border-radius: 50%; animation: mod-spin 0.7s linear infinite;
}
@keyframes mod-spin { to { transform: rotate(360deg); } }
.mod-state-icon { font-size: 2.5rem; color: #ef4444; opacity: 0.6; }
.mod-state-text { color: #6c757d; font-size: 0.9rem; font-weight: 500; text-align: center; }
.mod-retry-btn {
    display: inline-flex; align-items: center;
    padding: 0.5rem 1.25rem; border: 1px solid #ff7900; border-radius: 8px;
    background: #fff; color: #ff7900; font-size: 0.82rem; font-weight: 600;
    cursor: pointer; transition: background 0.15s ease;
}
.mod-retry-btn:hover { background: rgba(255,121,0,0.08); }

/* ─── Empty ─── */
.mod-empty {
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; padding: 80px 20px; gap: 0.75rem;
    color: #6c757d; font-size: 0.95rem;
}
.mod-empty i { font-size: 2.5rem; opacity: 0.3; }

/* ─── Header ─── */
.mod-header {
    display: flex; flex-wrap: wrap; align-items: center;
    justify-content: space-between; gap: 0.75rem; margin-bottom: 1rem;
}
.mod-title { font-size: 1.35rem; font-weight: 700; margin: 0 0 0.15rem; color: #212529; }
.mod-subtitle { font-size: 0.82rem; color: #6c757d; margin: 0; }
.mod-count {
    font-size: 0.75rem; color: #6c757d; background: #f3f4f6;
    padding: 0.3rem 0.7rem; border-radius: 50px; font-weight: 600;
}

/* ─── Toast ─── */
.mod-toast {
    display: flex; align-items: center;
    padding: 0.65rem 1rem; border-radius: 8px;
    font-size: 0.82rem; margin-bottom: 1rem; gap: 0.3rem;
    animation: mod-slide-in 0.25s ease;
}
@keyframes mod-slide-in { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
.mod-toast--success { background: #d1fae5; color: #065f46; border-left: 3px solid #10b981; }
.mod-toast--error { background: #fee2e2; color: #b91c1c; border-left: 3px solid #ef4444; }

/* ─── Grid ─── */
.mod-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;
}

/* ─── Card ─── */
.mod-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    transition: all 0.2s ease;
    overflow: hidden;
}
.mod-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
}
.mod-card--active {
    border-left: 4px solid #ff7900;
}
.mod-card--inactive {
    opacity: 0.7;
}
.mod-card--inactive:hover {
    opacity: 0.9;
}
.mod-card-body {
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.mod-card-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
}
.mod-card-left {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    min-width: 0;
}

/* ─── Icon ─── */
.mod-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.mod-icon--on {
    background: rgba(255,121,0,0.12);
    color: #ff7900;
}
.mod-icon--off {
    background: #f3f4f6;
    color: #9ca3af;
}

/* ─── Name / Slug ─── */
.mod-name {
    font-size: 0.85rem;
    font-weight: 600;
    color: #212529;
    line-height: 1.3;
}
.mod-slug {
    font-size: 0.7rem;
    color: #9ca3af;
    margin-top: 0.05rem;
}

/* ─── Switch toggle ─── */
.mod-switch {
    position: relative;
    display: inline-block;
    width: 38px;
    height: 22px;
    flex-shrink: 0;
}
.mod-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.mod-slider {
    position: absolute;
    cursor: pointer;
    inset: 0;
    background: #d1d5db;
    border-radius: 22px;
    transition: 0.2s;
}
.mod-slider::before {
    content: '';
    position: absolute;
    height: 16px;
    width: 16px;
    left: 3px;
    bottom: 3px;
    background: #fff;
    border-radius: 50%;
    transition: 0.2s;
}
.mod-switch input:checked + .mod-slider {
    background: #ff7900;
}
.mod-switch input:checked + .mod-slider::before {
    transform: translateX(16px);
}
.mod-switch input:disabled + .mod-slider {
    opacity: 0.5;
    cursor: not-allowed;
}

/* ─── Status badge ─── */
.mod-card-bottom {
    display: flex;
    align-items: center;
}
.mod-badge {
    display: inline-block;
    padding: 0.2em 0.6em;
    font-size: 0.68rem;
    font-weight: 600;
    border-radius: 50px;
}
.mod-badge--on {
    background: #d1fae5;
    color: #065f46;
}
.mod-badge--off {
    background: #f3f4f6;
    color: #6b7280;
}

/* ─── Print ─── */
@media print {
    .mod-switch { display: none; }
}
</style>
