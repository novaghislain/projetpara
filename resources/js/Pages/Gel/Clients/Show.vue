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
    { key: 'info', label: 'Informations', icon: 'bi-info-circle' },
    { key: 'contacts', label: 'Contacts', icon: 'bi-people' },
    { key: 'missions', label: 'Missions', icon: 'bi-check2-square' },
    { key: 'services', label: 'Services', icon: 'bi-grid-3x3-gap' },
    { key: 'modules', label: 'Modules', icon: 'bi-toggle-on' },
    { key: 'documents', label: 'Documents', icon: 'bi-folder' },
];

const allModules = [
    { slug: 'comptabilite', name: 'Comptabilité', icon: 'bi-calculator' },
    { slug: 'facturation', name: 'Facturation', icon: 'bi-receipt' },
    { slug: 'caisse', name: 'Caisse', icon: 'bi-cash-stack' },
    { slug: 'juridique', name: 'Juridique', icon: 'bi-bank' },
    { slug: 'rh', name: 'Ressources Humaines', icon: 'bi-people' },
    { slug: 'projets', name: 'Projets', icon: 'bi-kanban' },
    { slug: 'document', name: 'Documents', icon: 'bi-folder' },
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
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <template v-else-if="client">
            <!-- Header -->
            <div class="card card-dashboard mb-4">
                <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi-building" style="font-size:28px;"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold">{{ client.company_name }}</h4>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge" :class="client.status === 'actif' ? 'bg-success' : client.status === 'inactif' ? 'bg-secondary' : 'bg-warning'">
                                    {{ client.status }}
                                </span>
                                <span v-for="pole in client.poles" :key="pole.id" class="badge" :style="{ backgroundColor: pole.color || '#6c757d' }">
                                    {{ pole.name }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <a :href="'/clients/' + client.id + '/edit'" class="btn btn-sm btn-outline-primary">
                            <i class="bi-pencil me-1"></i>Modifier
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs card-header-tabs mb-3">
                <li v-for="tab in tabs" :key="tab.key" class="nav-item">
                    <button class="nav-link" :class="{ active: activeTab === tab.key }" @click="activeTab = tab.key">
                        <i :class="tab.icon" class="me-1"></i>{{ tab.label }}
                    </button>
                </li>
            </ul>

            <!-- Info Tab -->
            <div v-if="activeTab === 'info'" class="card card-dashboard p-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="small text-muted d-block">Email</label>
                            <span class="fw-medium">{{ client.email || '-' }}</span>
                        </div>
                        <div class="mb-3">
                            <label class="small text-muted d-block">Téléphone</label>
                            <span class="fw-medium">{{ client.phone || '-' }}</span>
                        </div>
                        <div class="mb-3">
                            <label class="small text-muted d-block">Site web</label>
                            <span class="fw-medium">{{ client.website || '-' }}</span>
                        </div>
                        <div class="mb-3">
                            <label class="small text-muted d-block">Forme juridique</label>
                            <span class="fw-medium">{{ client.legal_form || '-' }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="small text-muted d-block">RCCM</label>
                            <span class="fw-medium">{{ client.rccm || '-' }}</span>
                        </div>
                        <div class="mb-3">
                            <label class="small text-muted d-block">IFU</label>
                            <span class="fw-medium">{{ client.ifu || '-' }}</span>
                        </div>
                        <div class="mb-3">
                            <label class="small text-muted d-block">Adresse</label>
                            <span class="fw-medium">{{ client.address || '-' }}{{ client.city ? ', ' + client.city : '' }}{{ client.country ? ', ' + client.country : '' }}</span>
                        </div>
                        <div class="mb-3">
                            <label class="small text-muted d-block">Contrat</label>
                            <span class="fw-medium">{{ client.contract_type || '-' }}</span>
                            <span v-if="client.contract_start" class="small text-muted ms-2">({{ $formatDate(client.contract_start) }}{{ client.contract_end ? ' - ' + $formatDate(client.contract_end) : '' }})</span>
                        </div>
                    </div>
                    <div v-if="client.notes" class="col-12">
                        <label class="small text-muted d-block">Notes</label>
                        <p class="mb-0">{{ client.notes }}</p>
                    </div>
                </div>
            </div>

            <!-- Contacts Tab -->
            <div v-if="activeTab === 'contacts'" class="card card-dashboard">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-medium">Contacts</span>
                    <button class="btn btn-sm btn-primary" @click="openContactModal()">
                        <i class="bi-plus-lg me-1"></i>Ajouter
                    </button>
                </div>
                <div class="card-body p-0">
                    <div v-if="!client.contacts?.length" class="text-muted small py-4 text-center">Aucun contact enregistré.</div>
                    <table v-else class="table table-hover align-middle mb-0">
                        <thead class="small text-muted">
                            <tr><th>Nom</th><th>Email</th><th>Téléphone</th><th>Fonction</th><th class="text-end">Actions</th></tr>
                        </thead>
                        <tbody>
                            <tr v-for="contact in client.contacts" :key="contact.id">
                                <td class="fw-medium">{{ contact.first_name }} {{ contact.last_name }}</td>
                                <td class="small">{{ contact.email }}</td>
                                <td class="small">{{ contact.phone }}</td>
                                <td class="small">{{ contact.function || '-' }}</td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-secondary me-1" @click="openContactModal(contact)" title="Modifier"><i class="bi-pencil"></i></button>
                                    <button class="btn btn-sm btn-outline-danger" @click="deleteContact(contact.id)" title="Supprimer"><i class="bi-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Missions Tab -->
            <div v-if="activeTab === 'missions'" class="card card-dashboard">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-medium">Missions</span>
                    <a :href="'/missions/create?client_id=' + client.id" class="btn btn-sm btn-primary">
                        <i class="bi-plus-lg me-1"></i>Nouvelle mission
                    </a>
                </div>
                <div class="card-body p-0">
                    <div v-if="!client.missions?.length" class="text-muted small py-4 text-center">Aucune mission.</div>
                    <table v-else class="table table-hover align-middle mb-0">
                        <thead class="small text-muted">
                            <tr><th>Titre</th><th>Pôle</th><th>Statut</th><th>Priorité</th><th>Progression</th><th>Échéance</th></tr>
                        </thead>
                        <tbody>
                            <tr v-for="m in client.missions" :key="m.id">
                                <td><a :href="'/missions/' + m.id" class="text-decoration-none fw-medium">{{ m.title }}</a></td>
                                <td><span class="badge" :style="{ backgroundColor: m.pole?.color || '#6c757d' }">{{ m.pole?.name || '-' }}</span></td>
                                <td>
                                    <span class="badge" :class="m.status === 'terminee' ? 'bg-success' : m.status === 'en_cours' ? 'bg-primary' : m.status === 'en_attente' ? 'bg-warning' : 'bg-secondary'">
                                        {{ m.status }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge" :class="m.priority === 'haute' ? 'bg-danger' : m.priority === 'moyenne' ? 'bg-warning' : 'bg-info'">
                                        {{ m.priority }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height:6px;">
                                            <div class="progress-bar" :class="m.progress >= 100 ? 'bg-success' : 'bg-primary'" :style="{ width: (m.progress||0)+'%' }"></div>
                                        </div>
                                        <span class="small">{{ m.progress || 0 }}%</span>
                                    </div>
                                </td>
                                <td class="small">{{ m.end_date ? $formatDate(m.end_date) : '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Services Tab -->
            <div v-if="activeTab === 'services'" class="card card-dashboard">
                <div class="card-header bg-white">
                    <span class="fw-medium">Services assignés</span>
                </div>
                <div class="card-body">
                    <div v-if="!client.services?.length" class="text-muted small py-3 text-center">Aucun service assigné.</div>
                    <div v-else class="d-flex flex-column gap-2">
                        <div v-for="svc in client.services" :key="svc.id" class="d-flex align-items-center justify-content-between p-3 border rounded">
                            <div class="d-flex align-items-center gap-3">
                                <i :class="svc.icon || 'bi-gear'" :style="{ color: svc.color || '#1a237e', fontSize: '24px' }"></i>
                                <div>
                                    <div class="fw-medium">{{ svc.name }}</div>
                                    <div class="small text-muted">{{ svc.description || '' }}</div>
                                </div>
                            </div>
                            <span class="badge" :class="svc.pivot?.status === 'active' ? 'bg-success' : 'bg-secondary'">
                                {{ svc.pivot?.status === 'active' ? 'Actif' : (svc.pivot?.status || 'N/A') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modules Tab (super admin only) -->
            <div v-if="activeTab === 'modules'" class="card card-dashboard">
                <div class="card-header bg-white">
                    <span class="fw-medium">Modules activés pour ce client</span>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">
                        Par défaut tous les modules sont activés. Le super admin peut désactiver un module
                        pour bloquer l'accès à l'entreprise cliente.
                    </p>
                    <div class="d-flex flex-column gap-2">
                        <div v-for="mod in allModules" :key="mod.slug"
                             class="d-flex align-items-center justify-content-between p-3 border rounded">
                            <div class="d-flex align-items-center gap-3">
                                <i :class="mod.icon" style="font-size: 20px; color: #1a237e;"></i>
                                <span class="fw-medium">{{ mod.name }}</span>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input type="checkbox" class="form-check-input" role="switch"
                                       :checked="isModuleEnabled(mod.slug)"
                                       :disabled="toggling === mod.slug"
                                       @change="toggleModule(mod.slug, !isModuleEnabled(mod.slug))">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents Tab -->
            <div v-if="activeTab === 'documents'" class="card card-dashboard">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-medium">Documents</span>
                    <a :href="'/dossiers/' + client.id" class="btn btn-sm btn-outline-primary">
                        <i class="bi-folder me-1"></i>Gérer les dossiers
                    </a>
                </div>
                <div class="card-body">
                    <div v-if="!client.folders?.length" class="text-muted small py-3 text-center">Aucun dossier.</div>
                    <div v-else class="row g-3">
                        <div v-for="folder in client.folders" :key="folder.id" class="col-md-4">
                            <div class="border rounded p-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi-folder-fill text-warning"></i>
                                    <span class="fw-medium small">{{ folder.name }}</span>
                                </div>
                                <div v-if="folder.documents?.length" class="small">
                                    <div v-for="doc in folder.documents" :key="doc.id" class="d-flex align-items-center gap-2 py-1">
                                        <i class="bi-file-text"></i>
                                        <a :href="'/documents/download/' + doc.id" class="text-decoration-none small">{{ doc.name }}</a>
                                    </div>
                                </div>
                                <div v-else class="small text-muted">Aucun document</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Modal -->
            <div v-if="showContactModal" class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title fw-bold">{{ editingContactId ? 'Modifier' : 'Ajouter' }} un contact</h6>
                            <button type="button" class="btn-close" @click="showContactModal = false"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label small">Prénom</label>
                                    <input v-model="contactForm.first_name" class="form-control form-control-sm">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small">Nom</label>
                                    <input v-model="contactForm.last_name" class="form-control form-control-sm">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small">Email</label>
                                    <input v-model="contactForm.email" type="email" class="form-control form-control-sm">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small">Téléphone</label>
                                    <input v-model="contactForm.phone" type="tel" class="form-control form-control-sm">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small">Fonction</label>
                                    <input v-model="contactForm.function" class="form-control form-control-sm">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small">Notes</label>
                                    <textarea v-model="contactForm.notes" class="form-control form-control-sm" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-sm btn-secondary" @click="showContactModal = false">Annuler</button>
                            <button class="btn btn-sm btn-primary" :disabled="saving" @click="saveContact">
                                <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                                {{ editingContactId ? 'Mettre à jour' : 'Ajouter' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </GelLayout>
</template>
