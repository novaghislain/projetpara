<script setup>
import { ref, onMounted, nextTick, computed } from 'vue';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';

const users = ref([]);
const roles = ref([]);
const loading = ref(true);
const error = ref(null);
const submitting = ref(false);
const savingPermissions = ref(false);

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

// ─── Modules disponibles (depuis API) ─────────────────────
const availableModules = ref([]);
const allPermissions = ref([]);

const form = ref({
    name: '',
    email: '',
    password: '',
    role_id: '',
    fonction: '',
    permissions: [],
});

// ─── Helpers ──────────────────────────────────────────────
const statusClass = (active) => active ? 'bg-success' : 'bg-secondary';
const statusLabel = (active) => active ? 'Actif' : 'Inactif';

const moduleIcon = (mod) => {
    const icons = {
        caisse: 'bi-cash-stack',
        comptabilite: 'bi-calculator',
        facturation: 'bi-receipt',
        rh: 'bi-people',
        juridique: 'bi-file-earmark-text',
        projets: 'bi-kanban',
        document: 'bi-folder2-open',
    };
    return icons[mod] || 'bi-grid-3x3-gap';
};

const moduleColor = (mod) => {
    const colors = {
        caisse: 'success',
        comptabilite: 'primary',
        facturation: 'info',
        rh: 'warning',
        juridique: 'danger',
        projets: 'secondary',
        document: 'dark',
    };
    return colors[mod] || 'secondary';
};

function formatDate(d) {
    if (!d) return '-';
    return d;
}

function shortDate(dateStr) {
    if (!dateStr) return 'Jamais';
    return new Date(dateStr).toLocaleDateString('fr-FR', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
}

// ─── Gestion des permissions par module ───────────────────
function isModuleActive(moduleSlug) {
    const modPerms = availableModules.value.find(m => m.module === moduleSlug);
    if (!modPerms) return false;
    return modPerms.permissions.some(p => form.value.permissions.includes(p.id));
}

function toggleModule(moduleSlug, active) {
    const modPerms = availableModules.value.find(m => m.module === moduleSlug);
    if (!modPerms) return;
    const permIds = modPerms.permissions.map(p => p.id);
    if (active) {
        // Activer toutes les permissions du module
        permIds.forEach(id => {
            if (!form.value.permissions.includes(id)) {
                form.value.permissions.push(id);
            }
        });
    } else {
        // Désactiver toutes les permissions du module
        form.value.permissions = form.value.permissions.filter(id => !permIds.includes(id));
    }
}

function togglePermission(permId) {
    const idx = form.value.permissions.indexOf(permId);
    if (idx === -1) {
        form.value.permissions.push(permId);
    } else {
        form.value.permissions.splice(idx, 1);
    }
}

function isPermissionActive(permId) {
    return form.value.permissions.includes(permId);
}

function modulePermissions(moduleSlug) {
    const modPerms = availableModules.value.find(m => m.module === moduleSlug);
    return modPerms ? modPerms.permissions : [];
}

// ─── API ──────────────────────────────────────────────────
async function fetchUsers() {
    loading.value = true;
    error.value = null;
    try {
        const res = await fetch('/api/company/users');
        if (!res.ok) throw new Error('Erreur lors du chargement');
        const data = await res.json();
        users.value = data.users || [];
        roles.value = data.roles || [];
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
}

async function fetchAvailablePermissions() {
    try {
        const res = await fetch('/api/company/permissions/available');
        if (res.ok) {
            const data = await res.json();
            availableModules.value = data.modules || [];
            allPermissions.value = data.all_permissions || [];
        }
    } catch (e) {
        console.warn('Erreur chargement permissions:', e);
    }
}

const resetForm = () => {
    form.value = {
        name: '', email: '', password: '', role_id: '', fonction: '', permissions: [],
    };
};

const openCreateModal = async () => {
    resetForm();
    isEditing.value = false;
    editingId.value = null;
    showModal.value = true;
    await fetchAvailablePermissions();
    nextTick(() => {
        const el = document.getElementById('user-modal');
        if (el) {
            const modal = bootstrap.Modal.getOrCreateInstance(el);
            modal.show();
        }
    });
};

const openEditModal = async (id) => {
    try {
        const res = await fetch('/api/company/users/' + id);
        if (!res.ok) throw new Error('Erreur de chargement');
        const data = await res.json();
        form.value = {
            name: data.name || '',
            email: data.email || '',
            password: '',
            role_id: data.role_id || '',
            fonction: data.fonction || '',
            permissions: data.permissions || [],
        };
        isEditing.value = true;
        editingId.value = id;
        showModal.value = true;
        await fetchAvailablePermissions();
        nextTick(() => {
            const el = document.getElementById('user-modal');
            if (el) {
                const modal = bootstrap.Modal.getOrCreateInstance(el);
                modal.show();
            }
        });
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

const closeModal = () => {
    const el = document.getElementById('user-modal');
    if (el) {
        const modal = bootstrap.Modal.getOrCreateInstance(el);
        modal.hide();
    }
    showModal.value = false;
};

const submitForm = async () => {
    submitting.value = true;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const url = isEditing.value ? '/api/company/users/' + editingId.value : '/api/company/users';
        const method = isEditing.value ? 'PUT' : 'POST';

        const payload = { ...form.value };
        if (isEditing.value && !payload.password) {
            delete payload.password;
        }

        const res = await fetch(url, {
            method,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        });

        if (!res.ok) {
            const errData = await res.json();
            throw new Error(errData.message || Object.values(errData.errors || {}).flat().join(', '));
        }
        closeModal();
        await fetchUsers();
    } catch (e) {
        alert('Erreur: ' + e.message);
    } finally {
        submitting.value = false;
    }
};

const toggleStatus = async (id, currentStatus) => {
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/api/company/users/' + id, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ is_active: !currentStatus }),
        });
        if (!res.ok) throw new Error('Erreur de mise à jour');
        await fetchUsers();
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

const deleteUser = async (id) => {
    if (!confirm('Confirmer la suppression de cet utilisateur ?')) return;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/api/company/users/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error('Erreur lors de la suppression');
        await fetchUsers();
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

const openPermissionsModal = async (user) => {
    editingId.value = user.id;
    form.value.permissions = [...(user.permissions || [])];
    await fetchAvailablePermissions();
    const el = document.getElementById('permissions-modal');
    if (el) {
        const modal = bootstrap.Modal.getOrCreateInstance(el);
        modal.show();
    }
};

const savePermissions = async () => {
    savingPermissions.value = true;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/api/company/users/' + editingId.value + '/permissions', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ permissions: form.value.permissions }),
        });
        if (!res.ok) throw new Error('Erreur de mise à jour');
        const el = document.getElementById('permissions-modal');
        if (el) {
            const modal = bootstrap.Modal.getOrCreateInstance(el);
            modal.hide();
        }
        await fetchUsers();
    } catch (e) {
        alert('Erreur: ' + e.message);
    } finally {
        savingPermissions.value = false;
    }
};

onMounted(fetchUsers);
</script>

<template>
    <CompanyLayout page-title="Gestion des Utilisateurs">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <p class="text-muted mb-0 small">
                <i class="bi-people me-1"></i>
                Gérez les utilisateurs et leurs accès aux modules.
                <span class="fw-semibold text-dark">Ce qui n'est pas activé n'existe pas</span> dans l'interface de l'utilisateur.
            </p>
            <button class="btn btn-primary rounded-3" @click="openCreateModal">
                <i class="bi-plus-lg me-1"></i> Nouvel utilisateur
            </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="alert alert-danger rounded-3 border-0 shadow-sm">
            <i class="bi-exclamation-triangle me-2"></i>{{ error }}
        </div>

        <!-- Empty -->
        <div v-else-if="!users.length" class="text-center py-5 text-muted">
            <i class="bi-people" style="font-size: 48px;"></i>
            <p class="mt-2 fs-5">Aucun utilisateur dans votre entreprise.</p>
            <button class="btn btn-primary rounded-3" @click="openCreateModal">
                <i class="bi-plus-lg me-1"></i> Créer un utilisateur
            </button>
        </div>

        <!-- Users Table -->
        <div v-else class="card border-0 rounded-4 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Utilisateur</th>
                            <th>Fonction</th>
                            <th>Rôle</th>
                            <th>Modules</th>
                            <th>Statut</th>
                            <th>Dernière connexion</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="u in users" :key="u.id">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 36px; height: 36px; font-size: 14px; font-weight: 600; flex-shrink: 0;">
                                        {{ (u.name || '?').charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold small">{{ u.name }}</div>
                                        <div class="text-muted small" style="font-size: 11px;">{{ u.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="small">{{ u.fonction || '-' }}</td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill">
                                    {{ u.role_name || 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <span v-if="!u.modules || !u.modules.length" class="text-muted small">Aucun</span>
                                    <span v-for="mod in (u.modules || [])" :key="mod"
                                          class="badge rounded-pill"
                                          :class="'bg-' + moduleColor(mod) + ' bg-opacity-10 text-' + moduleColor(mod)">
                                        <i :class="moduleIcon(mod)" class="me-1"></i>
                                        {{ mod === 'comptabilite' ? 'Compta' :
                                           mod === 'facturation' ? 'Facturation' :
                                           mod === 'caisse' ? 'Caisse' :
                                           mod === 'rh' ? 'RH' :
                                           mod === 'juridique' ? 'Juridique' :
                                           mod === 'projets' ? 'Projets' :
                                           mod === 'document' ? 'GED' : mod }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill" :class="statusClass(u.is_active)">
                                    {{ statusLabel(u.is_active) }}
                                </span>
                            </td>
                            <td class="small text-muted">{{ u.last_login || 'Jamais' }}</td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <button class="btn btn-sm btn-outline-primary" title="Permissions"
                                            @click="openPermissionsModal(u)">
                                        <i class="bi-shield-lock"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary"
                                            :title="u.is_active ? 'Désactiver' : 'Activer'"
                                            @click="toggleStatus(u.id, u.is_active)">
                                        <i :class="u.is_active ? 'bi-pause-circle' : 'bi-play-circle'"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-info" title="Modifier"
                                            @click="openEditModal(u.id)">
                                        <i class="bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" title="Supprimer"
                                            @click="deleteUser(u.id)">
                                        <i class="bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- Create/Edit Modal -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div id="user-modal" class="modal fade" tabindex="-1" @hidden.bs.modal="showModal = false">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content border-0 rounded-4 shadow">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">
                            <i :class="isEditing ? 'bi-pencil' : 'bi-person-plus'" class="me-2 text-primary"></i>
                            {{ isEditing ? "Modifier l'utilisateur" : 'Nouvel utilisateur' }}
                        </h5>
                        <button type="button" class="btn-close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Basic Info -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Nom complet <span class="text-danger">*</span></label>
                                <input v-model="form.name" type="text" class="form-control" required placeholder="Jean Martin">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Email <span class="text-danger">*</span></label>
                                <input v-model="form.email" type="email" class="form-control" required placeholder="jean@entreprise.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">
                                    {{ isEditing ? 'Nouveau mot de passe (optionnel)' : 'Mot de passe *' }}
                                </label>
                                <input v-model="form.password" type="password" class="form-control"
                                       :required="!isEditing" minlength="8" placeholder="Minimum 8 caractères">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Rôle</label>
                                <select v-model="form.role_id" class="form-select">
                                    <option value="">Sélectionner</option>
                                    <option v-for="r in roles" :key="r.id" :value="r.id">
                                        {{ r.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Fonction</label>
                                <input v-model="form.fonction" type="text" class="form-control"
                                       placeholder="Ex: Comptable">
                            </div>
                        </div>

                        <!-- Permissions -->
                        <div class="border-top pt-3">
                            <h6 class="fw-bold mb-3">
                                <i class="bi-shield-lock me-2 text-primary"></i>
                                Permissions par module
                                <span class="small text-muted fw-normal">— Ce qui n'est pas coché n'existe pas pour cet utilisateur</span>
                            </h6>

                            <div v-if="!availableModules.length" class="text-muted small py-3">
                                <i class="bi-info-circle me-1"></i>
                                Aucun module disponible. Activez des services dans la section licences.
                            </div>

                            <div v-for="mod in availableModules" :key="mod.module"
                                 class="card border rounded-3 mb-2 overflow-hidden">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox"
                                                   :id="'mod-switch-' + mod.module"
                                                   :checked="isModuleActive(mod.module)"
                                                   @change="toggleModule(mod.module, $event.target.checked)">
                                        </div>
                                        <i :class="moduleIcon(mod.module) + ' text-' + moduleColor(mod.module)"
                                           style="font-size: 18px;"></i>
                                        <label class="form-check-label fw-semibold"
                                               :for="'mod-switch-' + mod.module">
                                            {{ mod.label }}
                                        </label>
                                        <span v-if="!isModuleActive(mod.module)" class="text-muted" style="font-size: 11px;">
                                            (aucun accès)
                                        </span>
                                    </div>
                                    <div v-if="isModuleActive(mod.module)" class="d-flex flex-wrap gap-2 ms-4 ps-3">
                                        <div v-for="perm in mod.permissions" :key="perm.id"
                                             class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox"
                                                   :id="'perm-' + perm.id"
                                                   :checked="isPermissionActive(perm.id)"
                                                   @change="togglePermission(perm.id)">
                                            <label class="form-check-label small" :for="'perm-' + perm.id">
                                                {{ perm.display_name }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-3" @click="closeModal">Annuler</button>
                        <button type="button" class="btn btn-primary rounded-3" :disabled="submitting" @click="submitForm">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                            <i class="bi-check-lg me-1"></i>
                            {{ isEditing ? 'Mettre à jour' : "Créer l'utilisateur" }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- Permissions Modal (standalone) -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div id="permissions-modal" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content border-0 rounded-4 shadow">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi-shield-lock me-2 text-primary"></i>
                            Permissions
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="!availableModules.length" class="text-muted small py-3">
                            Aucun module disponible.
                        </div>

                        <div v-for="mod in availableModules" :key="mod.module"
                             class="card border rounded-3 mb-2 overflow-hidden">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox"
                                               :id="'perm-mod-switch-' + mod.module"
                                               :checked="isModuleActive(mod.module)"
                                               @change="toggleModule(mod.module, $event.target.checked)">
                                    </div>
                                    <i :class="moduleIcon(mod.module) + ' text-' + moduleColor(mod.module)" style="font-size: 18px;"></i>
                                    <label class="form-check-label fw-semibold" :for="'perm-mod-switch-' + mod.module">
                                        {{ mod.label }}
                                    </label>
                                    <span v-if="!isModuleActive(mod.module)" class="text-muted" style="font-size: 11px;">
                                        (aucun accès)
                                    </span>
                                </div>
                                <div v-if="isModuleActive(mod.module)" class="d-flex flex-wrap gap-2 ms-4 ps-3">
                                    <div v-for="perm in mod.permissions" :key="perm.id"
                                         class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox"
                                               :id="'perm-only-' + perm.id"
                                               :checked="isPermissionActive(perm.id)"
                                               @change="togglePermission(perm.id)">
                                        <label class="form-check-label small" :for="'perm-only-' + perm.id">
                                            {{ perm.display_name }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-primary rounded-3" :disabled="savingPermissions" @click="savePermissions">
                            <span v-if="savingPermissions" class="spinner-border spinner-border-sm me-1"></span>
                            <i class="bi-check-lg me-1"></i> Enregistrer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </CompanyLayout>
</template>

<style scoped>
.form-check-input:checked {
    background-color: var(--gel-primary, #1a237e);
    border-color: var(--gel-primary, #1a237e);
}
.form-switch .form-check-input:checked {
    background-color: var(--gel-primary, #1a237e);
    border-color: var(--gel-primary, #1a237e);
}
.card {
    transition: all 0.2s;
}
.table th {
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6c757d;
}
</style>
