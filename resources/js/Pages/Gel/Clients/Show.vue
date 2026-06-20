<script setup>
import { ref, computed, onMounted } from 'vue'
import GelLayout from '../../../Layouts/GelLayout.vue'

/* ══════════════════════════════════════════
   Props
   ══════════════════════════════════════════ */
const props = defineProps({
    clientId: { type: [Number, String], default: null },
})

/* ══════════════════════════════════════════
   State
   ══════════════════════════════════════════ */
const state = ref('loading')  // 'loading' | 'error' | 'loaded'
const errorMsg = ref('')
const toast = ref('')
const toastType = ref('success')
const client = ref(null)
const activeTab = ref('info')
const saving = ref(false)
let toastTimer = null

const effectiveClientId = computed(() =>
    props.clientId || null
)

/* ══════════════════════════════════════════
   Tab config
   ══════════════════════════════════════════ */
const tabs = [
    { key: 'info',      label: 'Informations', icon: 'bi-info-circle' },
    { key: 'contacts',  label: 'Contacts',     icon: 'bi-people' },
    { key: 'missions',  label: 'Missions',     icon: 'bi-check2-square' },
    { key: 'services',  label: 'Services',     icon: 'bi-grid-3x3-gap' },
    { key: 'modules',   label: 'Modules',      icon: 'bi-toggle-on' },
    { key: 'documents', label: 'Documents',    icon: 'bi-folder' },
]

/* ══════════════════════════════════════════
   Modules config
   ══════════════════════════════════════════ */
const allModules = [
    { slug: 'comptabilite', name: 'Comptabilité',      icon: 'bi-calculator' },
    { slug: 'facturation',  name: 'Facturation',       icon: 'bi-receipt' },
    { slug: 'caisse',       name: 'Caisse',             icon: 'bi-cash-stack' },
    { slug: 'juridique',    name: 'Juridique',          icon: 'bi-bank' },
    { slug: 'rh',           name: 'Ressources Humaines', icon: 'bi-people' },
    { slug: 'projets',      name: 'Projets',            icon: 'bi-kanban' },
    { slug: 'document',     name: 'Documents',          icon: 'bi-folder' },
]

const toggling = ref(null)

function isModuleEnabled(slug) {
    return !client.value?.disabled_modules?.includes(slug)
}

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
async function fetchClient() {
    state.value = 'loading'
    errorMsg.value = ''
    const cid = effectiveClientId.value
    if (!cid) {
        errorMsg.value = 'Aucun client sélectionné.'
        state.value = 'error'
        return
    }
    try {
        const res = await window.axios.get(`/api/clients/${cid}`)
        client.value = res.data
        state.value = 'loaded'
    } catch (err) {
        console.error('Erreur chargement client:', err)
        errorMsg.value = err.response?.data?.message || err.message || 'Erreur de chargement'
        state.value = 'error'
    }
}

/* ══════════════════════════════════════════
   Toast helper
   ══════════════════════════════════════════ */
/* ══════════════════════════════════════════
   Modules
   ══════════════════════════════════════════ */
async function toggleModule(slug, enabled) {
    const cid = effectiveClientId.value
    if (!cid) return
    toggling.value = slug
    try {
        const res = await window.axios.put(`/api/clients/${cid}/modules`, {
            module: slug,
            is_active: enabled,
        })
        client.value.disabled_modules = res.data.disabled_modules
        showToast(`Module "${slug}" ${enabled ? 'activé' : 'désactivé'}`)
    } catch (err) {
        const msg = err.response?.data?.message || err.message || 'Erreur'
        showToast(msg, 'error')
    } finally {
        toggling.value = null
    }
}

/* ══════════════════════════════════════════
   Contact CRUD
   ══════════════════════════════════════════ */
const showContactModal = ref(false)
const editingContactId = ref(null)
const contactForm = ref({ name: '', email: '', phone: '', position: '', is_primary: false })

function openContactModal(contact = null) {
    if (contact) {
        contactForm.value = { ...contact, is_primary: !!contact.is_primary }
        editingContactId.value = contact.id
    } else {
        contactForm.value = { name: '', email: '', phone: '', position: '', is_primary: false }
        editingContactId.value = null
    }
    showContactModal.value = true
}

function closeContactModal() {
    showContactModal.value = false
}

async function saveContact() {
    const cid = effectiveClientId.value
    if (!cid) return
    saving.value = true
    try {
        const isEdit = !!editingContactId.value
        const url = isEdit ? `/contacts/${editingContactId.value}` : `/clients/${cid}/contacts`
        const method = isEdit ? 'put' : 'post'
        await window.axios[method](url, contactForm.value)
        showContactModal.value = false
        showToast(isEdit ? 'Contact mis à jour' : 'Contact ajouté')
        await fetchClient()
    } catch (err) {
        const msg = err.response?.data?.message || err.message || 'Erreur lors de la sauvegarde'
        showToast(msg, 'error')
    } finally {
        saving.value = false
    }
}

async function deleteContact(id) {
    if (!confirm('Supprimer ce contact ?')) return
    try {
        await window.axios.delete(`/contacts/${id}`)
        showToast('Contact supprimé')
        await fetchClient()
    } catch (err) {
        const msg = err.response?.data?.message || err.message || 'Erreur lors de la suppression'
        showToast(msg, 'error')
    }
}

/* ══════════════════════════════════════════
   Helpers
   ══════════════════════════════════════════ */
function statusBadgeClass(status) {
    const map = {
        actif: 'sc-badge--success',
        inactif: 'sc-badge--muted',
        en_attente: 'sc-badge--warning',
    }
    return map[status] || 'sc-badge--muted'
}

function missionStatusBadge(status) {
    const map = {
        terminee: 'sc-badge--success',
        en_cours: 'sc-badge--info',
        en_attente: 'sc-badge--warning',
    }
    return map[status] || 'sc-badge--muted'
}

function priorityBadge(priority) {
    const map = {
        haute: 'sc-badge--danger',
        moyenne: 'sc-badge--warning',
        basse: 'sc-badge--info',
    }
    return map[priority] || 'sc-badge--muted'
}

function formatDate(d) {
    if (!d) return '-'
    const dt = new Date(d)
    return dt.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' })
}

onMounted(fetchClient)
</script>

<template>
    <GelLayout :page-title="client?.company_name || 'Client'">
        <!-- ═══ LOADING ═══ -->
        <div v-if="state === 'loading'" class="sc-state">
            <div class="sc-spinner"></div>
            <p class="sc-state-text">Chargement du client...</p>
        </div>

        <!-- ═══ ERROR ═══ -->
        <div v-else-if="state === 'error'" class="sc-state">
            <i class="bi bi-exclamation-triangle sc-state-icon"></i>
            <p class="sc-state-text">{{ errorMsg }}</p>
            <button class="sc-retry-btn" @click="fetchClient">
                <i class="bi bi-arrow-clockwise me-1"></i>Réessayer
            </button>
        </div>

        <!-- ═══ CONTENT ═══ -->
        <template v-else-if="client">
            <!-- ── Toast ── -->
            <div v-if="toast" class="sc-toast" :class="toastType === 'error' ? 'sc-toast--error' : 'sc-toast--success'">
                <i :class="toastType === 'error' ? 'bi bi-exclamation-circle' : 'bi bi-check-circle'" class="me-1"></i>
                {{ toast }}
            </div>

            <!-- ── Header ── -->
            <div class="sc-header">
                <div class="sc-header-left">
                    <div class="sc-header-icon">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="sc-header-info">
                        <h1 class="sc-title">{{ client.company_name }}</h1>
                        <div class="sc-header-badges">
                            <span class="sc-badge" :class="statusBadgeClass(client.status)">{{ client.status }}</span>
                            <span
                                v-for="pole in client.poles"
                                :key="pole.id"
                                class="sc-pole-tag"
                                :style="{ background: pole.color || '#6c757d' }"
                            >{{ pole.name }}</span>
                        </div>
                    </div>
                </div>
                <a :href="`/clients/${client.id}/edit`" class="sc-btn sc-btn-primary">
                    <i class="bi bi-pencil me-1"></i>Modifier
                </a>
            </div>

            <!-- ── Tabs ── -->
            <div class="sc-tabs">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    class="sc-tab"
                    :class="{ 'sc-tab--active': activeTab === tab.key }"
                    @click="activeTab = tab.key"
                >
                    <i :class="tab.icon" class="sc-tab-icon"></i>
                    <span>{{ tab.label }}</span>
                </button>
            </div>

            <!-- ══ INFO TAB ══ -->
            <div v-if="activeTab === 'info'" class="sc-card">
                <div class="sc-card-header">
                    <span class="sc-card-title">
                        <i class="bi bi-info-circle sc-title-icon"></i>Informations générales
                    </span>
                </div>
                <div class="sc-card-body">
                    <div class="sc-info-grid">
                        <div class="sc-info-group">
                            <div class="sc-info-item">
                                <span class="sc-info-label">Email</span>
                                <span class="sc-info-value">{{ client.email || '-' }}</span>
                            </div>
                            <div class="sc-info-item">
                                <span class="sc-info-label">Téléphone</span>
                                <span class="sc-info-value">{{ client.phone || '-' }}</span>
                            </div>
                            <div class="sc-info-item">
                                <span class="sc-info-label">Site web</span>
                                <span class="sc-info-value">{{ client.website || '-' }}</span>
                            </div>
                            <div class="sc-info-item">
                                <span class="sc-info-label">Forme juridique</span>
                                <span class="sc-info-value">{{ client.legal_form || '-' }}</span>
                            </div>
                        </div>
                        <div class="sc-info-group">
                            <div class="sc-info-item">
                                <span class="sc-info-label">RCCM</span>
                                <span class="sc-info-value">{{ client.rccm || '-' }}</span>
                            </div>
                            <div class="sc-info-item">
                                <span class="sc-info-label">IFU</span>
                                <span class="sc-info-value">{{ client.ifu || '-' }}</span>
                            </div>
                            <div class="sc-info-item">
                                <span class="sc-info-label">Adresse</span>
                                <span class="sc-info-value">{{ client.address || '-' }}{{ client.city ? ', ' + client.city : '' }}{{ client.country ? ', ' + client.country : '' }}</span>
                            </div>
                            <div class="sc-info-item">
                                <span class="sc-info-label">Contrat</span>
                                <span class="sc-info-value">
                                    {{ client.contract_type || '-' }}
                                    <span v-if="client.contract_start" class="sc-info-sub">
                                        ({{ formatDate(client.contract_start) }}{{ client.contract_end ? ' — ' + formatDate(client.contract_end) : '' }})
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="client.notes" class="sc-info-notes">
                        <span class="sc-info-label">Notes</span>
                        <p class="sc-info-value">{{ client.notes }}</p>
                    </div>
                </div>
            </div>

            <!-- ══ CONTACTS TAB ══ -->
            <div v-if="activeTab === 'contacts'" class="sc-card">
                <div class="sc-card-header">
                    <span class="sc-card-title">
                        <i class="bi bi-people sc-title-icon"></i>Contacts
                    </span>
                    <button class="sc-btn sc-btn-primary sc-btn-sm" @click="openContactModal()">
                        <i class="bi bi-plus-lg me-1"></i>Ajouter
                    </button>
                </div>
                <div class="sc-card-body sc-card-body--flush">
                    <div v-if="!client.contacts?.length" class="sc-empty">
                        <i class="bi bi-people sc-empty-icon"></i>
                        <span>Aucun contact enregistré.</span>
                    </div>
                    <table v-else class="sc-table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Fonction</th>
                                <th class="sc-th-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="contact in client.contacts" :key="contact.id">
                                <td>{{ contact.name }}</td>
                                <td>{{ contact.email }}</td>
                                <td>{{ contact.phone }}</td>
                                <td>{{ contact.position }}</td>
                                <td class="sc-th-actions">
                                    <button class="sc-action-btn" @click="openContactModal(contact)" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="sc-action-btn sc-action-btn--danger" @click="deleteContact(contact.id)" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ══ MISSIONS TAB ══ -->
            <div v-if="activeTab === 'missions'" class="sc-card">
                <div class="sc-card-header">
                    <span class="sc-card-title">
                        <i class="bi bi-check2-square sc-title-icon"></i>Missions
                    </span>
                    <a :href="`/missions/create?client_id=${client.id}`" class="sc-btn sc-btn-primary sc-btn-sm">
                        <i class="bi bi-plus-lg me-1"></i>Nouvelle mission
                    </a>
                </div>
                <div class="sc-card-body sc-card-body--flush">
                    <div v-if="!client.missions?.length" class="sc-empty">
                        <i class="bi bi-check2-square sc-empty-icon"></i>
                        <span>Aucune mission.</span>
                    </div>
                    <table v-else class="sc-table">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Pôle</th>
                                <th>Statut</th>
                                <th>Priorité</th>
                                <th>Progression</th>
                                <th>Échéance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="m in client.missions" :key="m.id">
                                <td>
                                    <a :href="`/missions/${m.id}`" class="sc-cell-link">{{ m.title }}</a>
                                </td>
                                <td>
                                    <span class="sc-pole-tag sc-pole-tag--sm" :style="{ background: m.pole?.color || '#6c757d' }">{{ m.pole?.name || '-' }}</span>
                                </td>
                                <td>
                                    <span class="sc-badge" :class="missionStatusBadge(m.status)">{{ m.status }}</span>
                                </td>
                                <td>
                                    <span class="sc-badge" :class="priorityBadge(m.priority)">{{ m.priority }}</span>
                                </td>
                                <td>
                                    <div class="sc-progress">
                                        <div class="sc-progress-track">
                                            <div class="sc-progress-fill" :class="(m.progress||0) >= 100 ? 'sc-progress-fill--done' : ''" :style="{ width: (m.progress||0) + '%' }"></div>
                                        </div>
                                        <span class="sc-progress-label">{{ m.progress || 0 }}%</span>
                                    </div>
                                </td>
                                <td class="sc-cell-date">{{ m.end_date ? formatDate(m.end_date) : '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ══ SERVICES TAB ══ -->
            <div v-if="activeTab === 'services'" class="sc-card">
                <div class="sc-card-header">
                    <span class="sc-card-title">
                        <i class="bi bi-grid-3x3-gap sc-title-icon"></i>Services assignés
                    </span>
                </div>
                <div class="sc-card-body">
                    <div v-if="!client.services?.length" class="sc-empty">
                        <i class="bi bi-grid-3x3-gap sc-empty-icon"></i>
                        <span>Aucun service assigné.</span>
                    </div>
                    <div v-else class="sc-service-list">
                        <div v-for="svc in client.services" :key="svc.id" class="sc-service-item">
                            <div class="sc-service-left">
                                <div class="sc-service-icon" :style="{ background: (svc.color || '#1a237e') + '18', color: svc.color || '#1a237e' }">
                                    <i :class="svc.icon || 'bi-gear'"></i>
                                </div>
                                <div>
                                    <div class="sc-service-name">{{ svc.name }}</div>
                                    <div class="sc-service-desc">{{ svc.description || '' }}</div>
                                </div>
                            </div>
                            <span class="sc-badge" :class="svc.pivot?.status === 'active' ? 'sc-badge--success' : 'sc-badge--muted'">
                                {{ svc.pivot?.status === 'active' ? 'Actif' : (svc.pivot?.status || 'N/A') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ MODULES TAB ══ -->
            <div v-if="activeTab === 'modules'" class="sc-card">
                <div class="sc-card-header">
                    <span class="sc-card-title">
                        <i class="bi bi-toggle-on sc-title-icon"></i>Modules activés
                    </span>
                </div>
                <div class="sc-card-body">
                    <p class="sc-modules-hint">
                        Par défaut tous les modules sont activés. Le super admin peut désactiver un module
                        pour bloquer l'accès à l'entreprise cliente.
                    </p>
                    <div class="sc-module-list">
                        <div v-for="mod in allModules" :key="mod.slug" class="sc-module-item">
                            <div class="sc-module-left">
                                <div class="sc-module-icon">
                                    <i :class="mod.icon"></i>
                                </div>
                                <span class="sc-module-name">{{ mod.name }}</span>
                            </div>
                            <label class="sc-switch">
                                <input
                                    type="checkbox"
                                    :checked="isModuleEnabled(mod.slug)"
                                    :disabled="toggling === mod.slug"
                                    @change="toggleModule(mod.slug, !isModuleEnabled(mod.slug))"
                                >
                                <span class="sc-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ DOCUMENTS TAB ══ -->
            <div v-if="activeTab === 'documents'" class="sc-card">
                <div class="sc-card-header">
                    <span class="sc-card-title">
                        <i class="bi bi-folder sc-title-icon"></i>Documents
                    </span>
                    <a :href="`/dossiers/${client.id}`" class="sc-btn sc-btn-outline sc-btn-sm">
                        <i class="bi bi-folder me-1"></i>Gérer les dossiers
                    </a>
                </div>
                <div class="sc-card-body">
                    <div v-if="!client.folders?.length" class="sc-empty">
                        <i class="bi bi-folder sc-empty-icon"></i>
                        <span>Aucun dossier.</span>
                    </div>
                    <div v-else class="sc-doc-grid">
                        <div v-for="folder in client.folders" :key="folder.id" class="sc-doc-card">
                            <div class="sc-doc-card-header">
                                <i class="bi bi-folder-fill sc-doc-folder-icon"></i>
                                <span class="sc-doc-folder-name">{{ folder.name }}</span>
                            </div>
                            <div v-if="folder.documents?.length" class="sc-doc-list">
                                <div v-for="doc in folder.documents" :key="doc.id" class="sc-doc-item">
                                    <i class="bi bi-file-text sc-doc-file-icon"></i>
                                    <a :href="`/documents/download/${doc.id}`" class="sc-doc-link">{{ doc.name }}</a>
                                </div>
                            </div>
                            <div v-else class="sc-doc-empty">Aucun document</div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- ══ CONTACT MODAL ══ -->
        <div v-if="showContactModal" class="sc-modal-overlay" @click.self="closeContactModal">
            <div class="sc-modal">
                <div class="sc-modal-header">
                    <h3 class="sc-modal-title">{{ editingContactId ? 'Modifier' : 'Ajouter' }} un contact</h3>
                    <button class="sc-modal-close" @click="closeContactModal">&times;</button>
                </div>
                <div class="sc-modal-body">
                    <div class="sc-form-grid">
                        <div class="sc-form-group sc-form-group--full">
                            <label class="sc-form-label">Nom complet</label>
                            <input v-model="contactForm.name" class="sc-input" placeholder="Nom complet">
                        </div>
                        <div class="sc-form-group">
                            <label class="sc-form-label">Email</label>
                            <input v-model="contactForm.email" type="email" class="sc-input" placeholder="email@exemple.com">
                        </div>
                        <div class="sc-form-group">
                            <label class="sc-form-label">Téléphone</label>
                            <input v-model="contactForm.phone" type="tel" class="sc-input" placeholder="+226 XX XX XX XX">
                        </div>
                        <div class="sc-form-group">
                            <label class="sc-form-label">Fonction</label>
                            <input v-model="contactForm.position" class="sc-input" placeholder="Fonction">
                        </div>
                        <div class="sc-form-group">
                            <label class="sc-switch" style="display: flex; align-items: center; gap: 10px;">
                                <input v-model="contactForm.is_primary" type="checkbox">
                                <span class="sc-slider"></span>
                                <span style="font-size: 0.72rem; font-weight: 600;">Contact principal</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="sc-modal-footer">
                    <button class="sc-btn sc-btn-secondary" @click="closeContactModal">Annuler</button>
                    <button class="sc-btn sc-btn-primary" :disabled="saving" @click="saveContact">
                        <span v-if="saving" class="sc-spinner-sm"></span>
                        {{ editingContactId ? 'Mettre à jour' : 'Ajouter' }}
                    </button>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<style scoped>
/* ═══════════════════════════════════════════════════════
   Client Show — Refactored
   ═══════════════════════════════════════════════════════ */

/* ─── State ─── */
.sc-state {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 120px 20px; gap: 0.75rem;
}
.sc-spinner {
    width: 48px; height: 48px;
    border: 4px solid rgba(255,121,0,0.1); border-top-color: #ff7900;
    border-radius: 50%; animation: sc-spin 0.7s linear infinite;
}
@keyframes sc-spin { to { transform: rotate(360deg); } }
.sc-state-icon { font-size: 2.5rem; color: #ef4444; opacity: 0.6; }
.sc-state-text { color: #6c757d; font-size: 0.9rem; font-weight: 500; text-align: center; }
.sc-retry-btn {
    display: inline-flex; align-items: center;
    padding: 0.5rem 1.25rem; border: 1px solid #ff7900; border-radius: 8px;
    background: #fff; color: #ff7900; font-size: 0.82rem; font-weight: 600;
    cursor: pointer; transition: background 0.15s ease;
}
.sc-retry-btn:hover { background: rgba(255,121,0,0.08); }

/* ─── Toast ─── */
.sc-toast {
    display: flex; align-items: center;
    padding: 0.65rem 1rem; border-radius: 8px;
    font-size: 0.82rem; margin-bottom: 1rem; gap: 0.3rem;
    animation: sc-slide-in 0.25s ease;
}
@keyframes sc-slide-in { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
.sc-toast--success { background: #d1fae5; color: #065f46; border-left: 3px solid #10b981; }
.sc-toast--error { background: #fee2e2; color: #b91c1c; border-left: 3px solid #ef4444; }

/* ─── Header ─── */
.sc-header {
    display: flex; flex-wrap: wrap; align-items: center;
    justify-content: space-between; gap: 1rem; margin-bottom: 1.25rem;
}
.sc-header-left {
    display: flex; align-items: center; gap: 1rem; min-width: 0;
}
.sc-header-icon {
    width: 48px; height: 48px; border-radius: 12px;
    background: rgba(22,58,94,0.08);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; color: #163A5E; flex-shrink: 0;
}
.sc-header-info { min-width: 0; }
.sc-title {
    font-size: 1.35rem; font-weight: 700; margin: 0 0 0.3rem;
    color: #212529; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.sc-header-badges {
    display: flex; flex-wrap: wrap; gap: 0.35rem;
}
.sc-header-badges .sc-badge,
.sc-header-badges .sc-pole-tag {
    font-size: 0.68rem;
}

/* ─── Badges ─── */
.sc-badge {
    display: inline-block; padding: 0.2em 0.6em;
    font-size: 0.7rem; font-weight: 600; border-radius: 50px;
    line-height: 1.4;
}
.sc-badge--success { background: #d1fae5; color: #065f46; }
.sc-badge--info { background: #dbeafe; color: #1e40af; }
.sc-badge--warning { background: #fef3c7; color: #92400e; }
.sc-badge--danger { background: #fee2e2; color: #b91c1c; }
.sc-badge--muted { background: #f3f4f6; color: #6b7280; }

/* ─── Pole tags ─── */
.sc-pole-tag {
    display: inline-block; padding: 0.15em 0.55em;
    font-size: 0.7rem; font-weight: 600; border-radius: 50px;
    color: #fff; line-height: 1.4;
}
.sc-pole-tag--sm { font-size: 0.65rem; padding: 0.1em 0.45em; }

/* ─── Buttons ─── */
.sc-btn {
    display: inline-flex; align-items: center;
    padding: 0.5rem 1.1rem; border-radius: 8px;
    font-size: 0.8rem; font-weight: 600;
    text-decoration: none; border: 1px solid transparent;
    cursor: pointer; transition: all 0.15s ease; white-space: nowrap;
}
.sc-btn-sm { padding: 0.35rem 0.85rem; font-size: 0.75rem; }
.sc-btn-primary {
    background: #ff7900; color: #fff; border-color: #ff7900;
}
.sc-btn-primary:hover { background: #e66c00; border-color: #e66c00; color: #fff; }
.sc-btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.sc-btn-secondary {
    background: #f3f4f6; color: #374151; border-color: #e5e7eb;
}
.sc-btn-secondary:hover { background: #e5e7eb; }
.sc-btn-outline {
    background: transparent; color: #ff7900; border-color: #ff7900;
}
.sc-btn-outline:hover { background: rgba(255,121,0,0.08); }

/* ─── Tabs ─── */
.sc-tabs {
    display: flex; gap: 0.25rem; margin-bottom: 1.25rem;
    border-bottom: 2px solid #e9ecef; padding-bottom: 0;
    overflow-x: auto; -webkit-overflow-scrolling: touch;
}
.sc-tab {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.6rem 1rem; border: none; background: transparent;
    font-size: 0.8rem; font-weight: 500; color: #6c757d;
    cursor: pointer; white-space: nowrap;
    border-bottom: 2px solid transparent; margin-bottom: -2px;
    transition: all 0.15s ease;
}
.sc-tab:hover { color: #ff7900; background: rgba(255,121,0,0.04); }
.sc-tab--active {
    color: #ff7900; border-bottom-color: #ff7900; font-weight: 600;
}
.sc-tab-icon { font-size: 0.9rem; }

/* ─── Card ─── */
.sc-card {
    background: #fff; border: 1px solid #e9ecef;
    border-radius: 12px; overflow: hidden;
}
.sc-card-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.85rem 1.25rem;
    border-bottom: 1px solid #e9ecef; gap: 0.75rem;
}
.sc-card-title {
    font-size: 0.85rem; font-weight: 700; color: #212529;
    display: inline-flex; align-items: center;
}
.sc-title-icon { color: #ff7900; margin-right: 0.5rem; }
.sc-card-body { padding: 1.25rem; }
.sc-card-body--flush { padding: 0; }

/* ─── Empty state ─── */
.sc-empty {
    display: flex; flex-direction: column; align-items: center;
    padding: 2.5rem 1rem; gap: 0.5rem;
    color: #6c757d; font-size: 0.82rem;
}
.sc-empty-icon { font-size: 2rem; opacity: 0.3; }

/* ─── Info grid ─── */
.sc-info-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;
}
@media (max-width: 768px) { .sc-info-grid { grid-template-columns: 1fr; } }
.sc-info-group { display: flex; flex-direction: column; gap: 1rem; }
.sc-info-item { display: flex; flex-direction: column; gap: 0.15rem; }
.sc-info-label { font-size: 0.72rem; color: #6c757d; font-weight: 500; }
.sc-info-value { font-size: 0.85rem; color: #212529; font-weight: 500; }
.sc-info-sub { font-size: 0.78rem; color: #6c757d; font-weight: 400; }
.sc-info-notes {
    margin-top: 1.25rem; padding-top: 1.25rem;
    border-top: 1px solid #f0f0f0;
}
.sc-info-notes .sc-info-label { display: block; margin-bottom: 0.35rem; }
.sc-info-notes .sc-info-value { font-weight: 400; line-height: 1.5; }

/* ─── Table ─── */
.sc-table {
    width: 100%; border-collapse: collapse; font-size: 0.8rem;
}
.sc-table thead { background: #f8f9fa; }
.sc-table th {
    padding: 0.6rem 1rem; font-size: 0.7rem; font-weight: 600;
    color: #6c757d; text-transform: uppercase; letter-spacing: 0.03em;
    text-align: left; border-bottom: 1px solid #e9ecef;
}
.sc-table td {
    padding: 0.65rem 1rem; border-bottom: 1px solid #f0f0f0;
    color: #212529; vertical-align: middle;
}
.sc-table tbody tr:hover { background: #fafbfc; }
.sc-table tbody tr:last-child td { border-bottom: none; }
.sc-th-actions { text-align: right; width: 100px; }
.sc-cell-name { font-weight: 600; }
.sc-cell-subtle { color: #6c757d; }
.sc-cell-actions { text-align: right; white-space: nowrap; }
.sc-cell-link {
    color: #163A5E; text-decoration: none; font-weight: 500;
}
.sc-cell-link:hover { color: #ff7900; text-decoration: underline; }
.sc-cell-date { color: #6c757d; font-size: 0.78rem; }

/* ─── Action buttons ─── */
.sc-action-btn {
    width: 30px; height: 30px; border-radius: 8px;
    border: 1px solid #e9ecef; background: #fff;
    display: inline-flex; align-items: center; justify-content: center;
    color: #6c757d; font-size: 0.78rem;
    cursor: pointer; transition: all 0.15s ease;
}
.sc-action-btn:hover { border-color: #ff7900; color: #ff7900; background: rgba(255,121,0,0.05); }
.sc-action-btn--danger:hover { border-color: #ef4444; color: #ef4444; background: rgba(239,68,68,0.05); }

/* ─── Progress bars ─── */
.sc-progress {
    display: flex; align-items: center; gap: 0.5rem;
}
.sc-progress-track {
    flex: 1; height: 6px; background: #e9ecef; border-radius: 3px;
    overflow: hidden; min-width: 60px;
}
.sc-progress-fill {
    height: 100%; border-radius: 3px;
    background: #ff7900; transition: width 0.3s ease;
}
.sc-progress-fill--done { background: #10b981; }
.sc-progress-label { font-size: 0.72rem; font-weight: 600; color: #6c757d; min-width: 32px; text-align: right; }

/* ─── Services list ─── */
.sc-service-list { display: flex; flex-direction: column; gap: 0.5rem; }
.sc-service-item {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.85rem 1rem; background: #f8f9fa; border-radius: 8px; gap: 1rem;
}
.sc-service-left {
    display: flex; align-items: center; gap: 0.75rem; min-width: 0;
}
.sc-service-icon {
    width: 36px; height: 36px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
}
.sc-service-name { font-size: 0.82rem; font-weight: 600; color: #212529; }
.sc-service-desc { font-size: 0.7rem; color: #6c757d; }

/* ─── Modules ─── */
.sc-modules-hint {
    font-size: 0.78rem; color: #6c757d; margin: 0 0 1rem; line-height: 1.5;
}
.sc-module-list { display: flex; flex-direction: column; gap: 0.4rem; }
.sc-module-item {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.7rem 1rem; background: #f8f9fa; border-radius: 8px; gap: 1rem;
}
.sc-module-left {
    display: flex; align-items: center; gap: 0.65rem; min-width: 0;
}
.sc-module-icon {
    width: 32px; height: 32px; border-radius: 8px;
    background: #f0f4f8; display: flex; align-items: center; justify-content: center;
    color: #163A5E; font-size: 0.9rem; flex-shrink: 0;
}
.sc-module-name { font-size: 0.8rem; font-weight: 500; color: #212529; }

/* ─── Toggle switch ─── */
.sc-switch {
    position: relative; display: inline-block;
    width: 38px; height: 22px; flex-shrink: 0;
}
.sc-switch input { opacity: 0; width: 0; height: 0; }
.sc-slider {
    position: absolute; cursor: pointer; inset: 0;
    background: #d1d5db; border-radius: 22px; transition: 0.2s;
}
.sc-slider::before {
    content: ''; position: absolute; height: 16px; width: 16px;
    left: 3px; bottom: 3px; background: #fff;
    border-radius: 50%; transition: 0.2s;
}
.sc-switch input:checked + .sc-slider { background: #ff7900; }
.sc-switch input:checked + .sc-slider::before { transform: translateX(16px); }
.sc-switch input:disabled + .sc-slider { opacity: 0.5; cursor: not-allowed; }

/* ─── Documents grid ─── */
.sc-doc-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;
}
.sc-doc-card {
    border: 1px solid #e9ecef; border-radius: 10px; padding: 1rem;
}
.sc-doc-card-header {
    display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;
}
.sc-doc-folder-icon { color: #ff7900; font-size: 1.1rem; }
.sc-doc-folder-name { font-size: 0.85rem; font-weight: 600; color: #212529; }
.sc-doc-list { display: flex; flex-direction: column; gap: 0.35rem; }
.sc-doc-item {
    display: flex; align-items: center; gap: 0.4rem; padding: 0.25rem 0;
}
.sc-doc-file-icon { font-size: 0.7rem; color: #9ca3af; flex-shrink: 0; }
.sc-doc-link {
    font-size: 0.75rem; color: #163A5E; text-decoration: none;
}
.sc-doc-link:hover { color: #ff7900; text-decoration: underline; }
.sc-doc-empty { font-size: 0.75rem; color: #9ca3af; font-style: italic; }

/* ─── Modal ─── */
.sc-modal-overlay {
    position: fixed; inset: 0; z-index: 1050;
    background: rgba(0,0,0,0.5);
    display: flex; align-items: center; justify-content: center;
    padding: 1rem;
}
.sc-modal {
    background: #fff; border-radius: 12px;
    width: 100%; max-width: 520px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    animation: sc-modal-in 0.2s ease;
}
@keyframes sc-modal-in { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
.sc-modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1rem 1.25rem; border-bottom: 1px solid #e9ecef;
    background: #163A5E; border-radius: 12px 12px 0 0;
}
.sc-modal-title { font-size: 0.9rem; font-weight: 700; color: #fff; margin: 0; }
.sc-modal-close {
    background: none; border: none; color: rgba(255,255,255,0.7);
    font-size: 1.5rem; cursor: pointer; line-height: 1; padding: 0;
}
.sc-modal-close:hover { color: #fff; }
.sc-modal-body { padding: 1.25rem; }
.sc-modal-footer {
    display: flex; justify-content: flex-end; gap: 0.5rem;
    padding: 0.85rem 1.25rem; border-top: 1px solid #e9ecef;
}

/* ─── Form ─── */
.sc-form-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;
}
@media (max-width: 576px) { .sc-form-grid { grid-template-columns: 1fr; } }
.sc-form-group--full { grid-column: 1 / -1; }
.sc-form-label {
    display: block; font-size: 0.72rem; font-weight: 600;
    color: #374151; margin-bottom: 0.3rem;
}
.sc-input {
    width: 100%; padding: 0.5rem 0.7rem; font-size: 0.8rem;
    border: 1px solid #d1d5db; border-radius: 6px;
    color: #212529; background: #fff;
    transition: border-color 0.15s ease; outline: none;
    box-sizing: border-box;
}
.sc-input:focus { border-color: #ff7900; box-shadow: 0 0 0 3px rgba(255,121,0,0.1); }
.sc-textarea { resize: vertical; min-height: 60px; }

/* ─── Spinner sm ─── */
.sc-spinner-sm {
    display: inline-block; width: 14px; height: 14px;
    border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff;
    border-radius: 50%; animation: sc-spin 0.6s linear infinite;
    margin-right: 0.35rem;
}

/* ─── Print ─── */
@media print {
    .sc-tabs { border-bottom-color: #dee2e6; }
    .sc-tab { display: none; }
    .sc-tab--active { display: inline-flex; }
    .sc-card { break-inside: avoid; border: 1px solid #dee2e6; }
    .sc-action-btn { display: none; }
    .sc-modal-overlay { display: none; }
}
</style>
