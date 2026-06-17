<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';
import { authStore } from '../../../stores/auth';

const props = defineProps({
    clientId: { type: [Number, String], required: true }
});

const client = ref(null);
const loading = ref(true);
const error = ref(null);
const activeTab = ref('info');
const saving = ref(false);

// Contact modal
const showContactModal = ref(false);
const contactForm = ref({ first_name: '', last_name: '', email: '', phone: '', function: '', notes: '' });
const editingContactId = ref(null);

const tabs = [
    { key: 'info',      label: 'Informations', icon: 'bi-info-circle' },
    { key: 'contacts',  label: 'Contacts',     icon: 'bi-people' },
    { key: 'missions',  label: 'Missions',     icon: 'bi-check2-square' },
    { key: 'services',  label: 'Services',     icon: 'bi-grid-3x3-gap' },
    { key: 'modules',   label: 'Modules',      icon: 'bi-toggle-on' },
    { key: 'documents', label: 'Documents',    icon: 'bi-folder' },
];

const allModules = [
    { slug: 'comptabilite', name: 'Comptabilité',      icon: 'bi-calculator' },
    { slug: 'facturation',  name: 'Facturation',       icon: 'bi-receipt' },
    { slug: 'caisse',       name: 'Caisse',             icon: 'bi-cash-stack' },
    { slug: 'juridique',    name: 'Juridique',          icon: 'bi-bank' },
    { slug: 'rh',           name: 'Ressources Humaines', icon: 'bi-people' },
    { slug: 'projets',      name: 'Projets',            icon: 'bi-kanban' },
    { slug: 'document',     name: 'Documents',          icon: 'bi-folder' },
];

// Module toggles
const toggling = ref(null);

const isModuleEnabled = (slug) => {
    return !client.value?.disabled_modules?.includes(slug);
};

const toggleModule = async (slug, enabled) => {
    toggling.value = slug;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/api/clients/' + props.clientId + '/modules', {
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ module: slug, enabled }),
        });
        if (!res.ok) throw new Error('Erreur');
        const data = await res.json();
        client.value.disabled_modules = data.disabled_modules;
    } catch (e) {
        alert('Erreur: ' + e.message);
    } finally {
        toggling.value = null;
    }
};

const fetchClient = async () => {
    loading.value = true;
    error.value = null;
    try {
        const res = await fetch('/api/clients/' + props.clientId);
        if (!res.ok) throw new Error('Erreur lors du chargement du client');
        client.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

// Contact CRUD
const openContactModal = (contact = null) => {
    if (contact) {
        contactForm.value = { ...contact };
        editingContactId.value = contact.id;
    } else {
        contactForm.value = { first_name: '', last_name: '', email: '', phone: '', function: '', notes: '' };
        editingContactId.value = null;
    }
    showContactModal.value = true;
};

const saveContact = async () => {
    saving.value = true;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const isEdit = !!editingContactId.value;
        const url = isEdit ? '/contacts/' + editingContactId.value : '/clients/' + props.clientId + '/contacts';
        const method = isEdit ? 'PUT' : 'POST';

        const res = await fetch(url, {
            method,
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(contactForm.value),
        });
        if (!res.ok) throw new Error('Erreur lors de la sauvegarde du contact');
        showContactModal.value = false;
        await fetchClient();
    } catch (e) {
        alert('Erreur: ' + e.message);
    } finally {
        saving.value = false;
    }
};

const deleteContact = async (id) => {
    if (!confirm('Supprimer ce contact ?')) return;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/contacts/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error('Erreur lors de la suppression');
        await fetchClient();
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

onMounted(fetchClient);
</script>

<template>
    <GelLayout :page-title="client?.company_name || 'Client'">
        <!-- Loading -->
        <div v-if="loading" class="d-flex align-items-center justify-content-center gap-3 py-5">
            <div class="isup-spinner"></div>
            <span style="color:#888; font-size:14px;">Chargement…</span>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="isup-alert-error">
            <i class="bi-exclamation-triangle-fill me-2"></i>{{ error }}
            <button @click="fetchClient" class="isup-btn-primary ms-3" style="padding:4px 12px; font-size:11px;">
                <i class="bi-arrow-clockwise me-1"></i>Réessayer
            </button>
        </div>

        <template v-else-if="client">
            <div class="isup-shell">

                <!-- ══ PORTAL HEADER ══ -->
                <div class="isup-portal-header">
                    <div class="d-flex align-items-center gap-3 w-100">
                        <div class="isup-portal-logo">
                            <i class="bi-building" style="font-size:22px;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="isup-portal-company">{{ client.company_name }}</div>
                            <div class="isup-portal-sub d-flex flex-wrap gap-2 mt-1">
                                <span class="isup-badge-sm" :class="client.status === 'actif' ? 'isup-badge-green' : client.status === 'inactif' ? 'isup-badge-grey' : 'isup-badge-warn'">
                                    {{ client.status }}
                                </span>
                                <span v-for="pole in client.poles" :key="pole.id"
                                      class="isup-badge-sm"
                                      :style="{ background: pole.color || '#6c757d', color: '#fff' }">
                                    {{ pole.name }}
                                </span>
                            </div>
                        </div>
                        <a :href="'/clients/' + client.id + '/edit'" class="isup-btn-orange btn-sm flex-shrink-0">
                            <i class="bi-pencil me-1"></i>Modifier
                        </a>
                    </div>
                </div>

                <!-- ══ TABS BAR ══ -->
                <div class="isup-tabs-bar">
                    <button v-for="tab in tabs" :key="tab.key"
                            class="isup-tab"
                            :class="{ 'isup-tab-active': activeTab === tab.key }"
                            @click="activeTab = tab.key">
                        <i :class="tab.icon"></i>
                        {{ tab.label }}
                    </button>
                </div>

                <!-- ══ INFO TAB ══ -->
                <div v-if="activeTab === 'info'" class="p-3">
                    <div class="isup-panel">
                        <div class="isup-panel-header">
                            <i class="bi-info-circle me-2" style="color:#FF7900;"></i>Informations générales
                        </div>
                        <div class="isup-panel-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="isup-field-row">
                                        <span class="isup-field-label">Email</span>
                                        <span class="isup-field-val">{{ client.email || '-' }}</span>
                                    </div>
                                    <div class="isup-field-row">
                                        <span class="isup-field-label">Téléphone</span>
                                        <span class="isup-field-val">{{ client.phone || '-' }}</span>
                                    </div>
                                    <div class="isup-field-row">
                                        <span class="isup-field-label">Site web</span>
                                        <span class="isup-field-val">{{ client.website || '-' }}</span>
                                    </div>
                                    <div class="isup-field-row">
                                        <span class="isup-field-label">Forme juridique</span>
                                        <span class="isup-field-val">{{ client.legal_form || '-' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="isup-field-row">
                                        <span class="isup-field-label">RCCM</span>
                                        <span class="isup-field-val">{{ client.rccm || '-' }}</span>
                                    </div>
                                    <div class="isup-field-row">
                                        <span class="isup-field-label">IFU</span>
                                        <span class="isup-field-val">{{ client.ifu || '-' }}</span>
                                    </div>
                                    <div class="isup-field-row">
                                        <span class="isup-field-label">Adresse</span>
                                        <span class="isup-field-val">
                                            {{ client.address || '-' }}{{ client.city ? ', ' + client.city : '' }}{{ client.country ? ', ' + client.country : '' }}
                                        </span>
                                    </div>
                                    <div class="isup-field-row">
                                        <span class="isup-field-label">Contrat</span>
                                        <span class="isup-field-val">
                                            {{ client.contract_type || '-' }}
                                            <span v-if="client.contract_start" class="text-muted" style="font-size:11px;">
                                                ({{ $formatDate(client.contract_start) }}{{ client.contract_end ? ' — ' + $formatDate(client.contract_end) : '' }})
                                            </span>
                                        </span>
                                    </div>
                                </div>
                                <div v-if="client.notes" class="col-12">
                                    <div class="isup-field-row">
                                        <span class="isup-field-label">Notes</span>
                                        <span class="isup-field-val">{{ client.notes }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══ CONTACTS TAB ══ -->
                <div v-if="activeTab === 'contacts'" class="p-3">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center">
                            <span><i class="bi-people me-2" style="color:#FF7900;"></i>Contacts</span>
                            <button class="isup-btn-primary btn-sm" @click="openContactModal()">
                                <i class="bi-plus-lg me-1"></i>Ajouter
                            </button>
                        </div>
                        <div class="isup-panel-body p-0">
                            <div v-if="!client.contacts?.length" class="text-muted small py-4 text-center">
                                Aucun contact enregistré.
                            </div>
                            <div v-else class="isup-table-wrap">
                                <table class="isup-table w-100">
                                    <thead>
                                        <tr>
                                            <th>Nom</th>
                                            <th>Email</th>
                                            <th>Téléphone</th>
                                            <th>Fonction</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="contact in client.contacts" :key="contact.id">
                                            <td class="fw-semibold" style="color:#163A5E;">{{ contact.first_name }} {{ contact.last_name }}</td>
                                            <td style="font-size:12px;">{{ contact.email }}</td>
                                            <td style="font-size:12px;">{{ contact.phone }}</td>
                                            <td style="font-size:12px;">{{ contact.function || '-' }}</td>
                                            <td class="text-end">
                                                <button class="isup-icon-btn" @click="openContactModal(contact)" title="Modifier">
                                                    <i class="bi-pencil"></i>
                                                </button>
                                                <button class="isup-icon-btn isup-icon-danger ms-1" @click="deleteContact(contact.id)" title="Supprimer">
                                                    <i class="bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══ MISSIONS TAB ══ -->
                <div v-if="activeTab === 'missions'" class="p-3">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center">
                            <span><i class="bi-check2-square me-2" style="color:#FF7900;"></i>Missions</span>
                            <a :href="'/missions/create?client_id=' + client.id" class="isup-btn-primary btn-sm">
                                <i class="bi-plus-lg me-1"></i>Nouvelle mission
                            </a>
                        </div>
                        <div class="isup-panel-body p-0">
                            <div v-if="!client.missions?.length" class="text-muted small py-4 text-center">
                                Aucune mission.
                            </div>
                            <div v-else class="isup-table-wrap">
                                <table class="isup-table w-100">
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
                                                <a :href="'/missions/' + m.id" class="isup-table-link">{{ m.title }}</a>
                                            </td>
                                            <td>
                                                <span class="isup-pole-badge" :style="{ background: m.pole?.color || '#6c757d' }">
                                                    {{ m.pole?.name || '-' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="isup-status" :class="m.status === 'terminee' ? 'isup-status-green' : m.status === 'en_cours' ? 'isup-status-blue' : m.status === 'en_attente' ? 'isup-status-warn' : 'isup-status-grey'">
                                                    {{ m.status }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="isup-status" :class="m.priority === 'haute' ? 'isup-status-red' : m.priority === 'moyenne' ? 'isup-status-warn' : 'isup-status-blue'">
                                                    {{ m.priority }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="isup-progress-bar">
                                                        <div class="isup-progress-fill" :class="m.progress >= 100 ? 'isup-progress-done' : ''" :style="{ width: (m.progress||0)+'%' }"></div>
                                                    </div>
                                                    <span style="font-size:11px; font-weight:600; color:#163A5E; min-width:32px; text-align:right;">{{ m.progress || 0 }}%</span>
                                                </div>
                                            </td>
                                            <td style="font-size:12px; color:#888;">{{ m.end_date ? $formatDate(m.end_date) : '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══ SERVICES TAB ══ -->
                <div v-if="activeTab === 'services'" class="p-3">
                    <div class="isup-panel">
                        <div class="isup-panel-header">
                            <i class="bi-grid-3x3-gap me-2" style="color:#FF7900;"></i>Services assignés
                        </div>
                        <div class="isup-panel-body">
                            <div v-if="!client.services?.length" class="text-muted small py-3 text-center">
                                Aucun service assigné.
                            </div>
                            <div v-else class="d-flex flex-column gap-2">
                                <div v-for="svc in client.services" :key="svc.id" class="isup-service-row">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="isup-svc-icon" :style="{ background: (svc.color || '#1a237e') + '18', color: svc.color || '#1a237e' }">
                                            <i :class="svc.icon || 'bi-gear'"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold" style="color:#163A5E; font-size:13px;">{{ svc.name }}</div>
                                            <div class="small text-muted">{{ svc.description || '' }}</div>
                                        </div>
                                    </div>
                                    <span class="isup-status" :class="svc.pivot?.status === 'active' ? 'isup-status-green' : 'isup-status-grey'">
                                        {{ svc.pivot?.status === 'active' ? 'Actif' : (svc.pivot?.status || 'N/A') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══ MODULES TAB ══ -->
                <div v-if="activeTab === 'modules'" class="p-3">
                    <div class="isup-panel">
                        <div class="isup-panel-header">
                            <i class="bi-toggle-on me-2" style="color:#FF7900;"></i>Modules activés
                        </div>
                        <div class="isup-panel-body">
                            <p class="small text-muted mb-3">
                                Par défaut tous les modules sont activés. Le super admin peut désactiver un module
                                pour bloquer l'accès à l'entreprise cliente.
                            </p>
                            <div class="d-flex flex-column gap-2">
                                <div v-for="mod in allModules" :key="mod.slug" class="isup-module-row">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="isup-svc-icon" style="background:#f0f4f8; color:#163A5E;">
                                            <i :class="mod.icon"></i>
                                        </div>
                                        <span class="fw-medium" style="font-size:13px; color:#163A5E;">{{ mod.name }}</span>
                                    </div>
                                    <label class="isup-switch" :class="{ 'isup-switch-disabled': toggling === mod.slug }">
                                        <input type="checkbox"
                                               :checked="isModuleEnabled(mod.slug)"
                                               :disabled="toggling === mod.slug"
                                               @change="toggleModule(mod.slug, !isModuleEnabled(mod.slug))">
                                        <span class="isup-switch-slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══ DOCUMENTS TAB ══ -->
                <div v-if="activeTab === 'documents'" class="p-3">
                    <div class="isup-panel">
                        <div class="isup-panel-header d-flex justify-content-between align-items-center">
                            <span><i class="bi-folder me-2" style="color:#FF7900;"></i>Documents</span>
                            <a :href="'/dossiers/' + client.id" class="isup-btn-orange btn-sm">
                                <i class="bi-folder me-1"></i>Gérer les dossiers
                            </a>
                        </div>
                        <div class="isup-panel-body">
                            <div v-if="!client.folders?.length" class="text-muted small py-3 text-center">
                                Aucun dossier.
                            </div>
                            <div v-else class="row g-3">
                                <div v-for="folder in client.folders" :key="folder.id" class="col-md-4">
                                    <div class="isup-doc-folder">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i class="bi-folder-fill" style="color:#FF7900; font-size:18px;"></i>
                                            <span class="fw-semibold small" style="color:#163A5E;">{{ folder.name }}</span>
                                        </div>
                                        <div v-if="folder.documents?.length" class="small">
                                            <div v-for="doc in folder.documents" :key="doc.id" class="d-flex align-items-center gap-2 py-1">
                                                <i class="bi-file-text" style="color:#888; font-size:12px;"></i>
                                                <a :href="'/documents/download/' + doc.id" class="isup-doc-link">{{ doc.name }}</a>
                                            </div>
                                        </div>
                                        <div v-else class="small text-muted fst-italic">Aucun document</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══ CONTACT MODAL ══ -->
                <div v-if="showContactModal" class="isup-modal-overlay" @click.self="showContactModal = false">
                    <div class="isup-modal">
                        <div class="isup-modal-header">
                            <span>{{ editingContactId ? 'Modifier' : 'Ajouter' }} un contact</span>
                            <button class="isup-modal-close" @click="showContactModal = false">&times;</button>
                        </div>
                        <div class="isup-modal-body">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="isup-label">Prénom</label>
                                    <input v-model="contactForm.first_name" class="isup-input">
                                </div>
                                <div class="col-6">
                                    <label class="isup-label">Nom</label>
                                    <input v-model="contactForm.last_name" class="isup-input">
                                </div>
                                <div class="col-6">
                                    <label class="isup-label">Email</label>
                                    <input v-model="contactForm.email" type="email" class="isup-input">
                                </div>
                                <div class="col-6">
                                    <label class="isup-label">Téléphone</label>
                                    <input v-model="contactForm.phone" type="tel" class="isup-input">
                                </div>
                                <div class="col-6">
                                    <label class="isup-label">Fonction</label>
                                    <input v-model="contactForm.function" class="isup-input">
                                </div>
                                <div class="col-12">
                                    <label class="isup-label">Notes</label>
                                    <textarea v-model="contactForm.notes" class="isup-input" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="isup-modal-footer">
                            <button class="isup-btn-grey" @click="showContactModal = false">Annuler</button>
                            <button class="isup-btn-primary" :disabled="saving" @click="saveContact">
                                <span v-if="saving" class="isup-spinner-sm me-1"></span>
                                {{ editingContactId ? 'Mettre à jour' : 'Ajouter' }}
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </template>
    </GelLayout>
</template>

<style scoped>
/* ══ Client Show — unique styles ══ */

.isup-badge-sm { display:inline-flex; align-items:center; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; padding:2px 9px; border-radius:3px; }
.isup-badge-green { background:#4caf50; color:#fff; }
.isup-badge-grey  { background:#9e9e9e; color:#fff; }
.isup-badge-warn  { background:#ff9800; color:#fff; }

.isup-tabs-bar { display:flex; background:#f5f7fb; border-bottom:1px solid #dce3ee; align-items:stretch; }
.isup-tab { background:transparent; border:none; color:#888; font-size:13px; font-weight:600; padding:10px 18px; cursor:pointer; white-space:nowrap; border-bottom:3px solid transparent; transition:all 0.15s; display:flex; align-items:center; gap:6px; }
.isup-tab:hover { color:#163A5E; background:#eef3f9; }
.isup-tab.isup-tab-active { color:#163A5E; border-bottom-color:#FF7900; background:#fff; font-weight:700; }
.isup-tab i { font-size:14px; }

.isup-table-link { color:#FF7900; text-decoration:none; font-weight:500; font-size:13px; }
.isup-table-link:hover { text-decoration:underline; }

.isup-status-warn { background:#fff8e1; color:#f57f17; }
.isup-status-blue { background:#e3f2fd; color:#1565c0; }

.isup-pole-badge { display:inline-flex; align-items:center; font-size:10px; font-weight:700; padding:2px 8px; border-radius:3px; color:#fff; text-transform:uppercase; letter-spacing:.04em; }
</style>
