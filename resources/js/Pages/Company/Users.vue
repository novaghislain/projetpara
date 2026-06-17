<script setup>
import { ref, onMounted, nextTick } from 'vue';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';

const users = ref([]);
const roles = ref([]);
const loading = ref(true);
const error = ref(null);
const submitting = ref(false);
const savingPermissions = ref(false);

const showModal = ref(false);
const showPermModal = ref(false);
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
        permIds.forEach(id => {
            if (!form.value.permissions.includes(id)) {
                form.value.permissions.push(id);
            }
        });
    } else {
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
    await fetchAvailablePermissions();
    showModal.value = true;
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
        await fetchAvailablePermissions();
        showModal.value = true;
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

const closeModal = () => {
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
    showPermModal.value = true;
};

const closePermModal = () => {
    showPermModal.value = false;
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
        closePermModal();
        await fetchUsers();
    } catch (e) {
        alert('Erreur: ' + e.message);
    } finally {
        savingPermissions.value = false;
    }
};

const moduleLabel = (mod) => {
    const labels = {
        comptabilite: 'Compta',
        facturation: 'Facturation',
        caisse: 'Caisse',
        rh: 'RH',
        juridique: 'Juridique',
        projets: 'Projets',
        document: 'GED',
    };
    return labels[mod] || mod;
};

onMounted(fetchUsers);
</script>

<template>
    <CompanyLayout page-title="Gestion des Utilisateurs">
        <!-- Loading -->
        <div v-if="loading" class="d-flex align-items-center justify-content-center gap-3 py-5">
            <div class="isup-spinner"></div>
            <span style="color:#888; font-size:14px;">Chargement…</span>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="isup-alert-error mb-3">
            <i class="bi-exclamation-triangle-fill me-2"></i>{{ error }}
        </div>

        <template v-else>
            <div class="isup-shell">
                <!-- ══ HEADER ══ -->
                <div class="isup-portal-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="isup-portal-logo">
                            <i class="bi-people" style="font-size:20px;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="isup-portal-company">Gestion des Utilisateurs</div>
                            <div class="isup-portal-sub">
                                Gérez les utilisateurs et leurs accès aux modules.
                                <strong>Ce qui n'est pas activé n'existe pas</strong> dans l'interface.
                            </div>
                        </div>
                        <button class="isup-btn-primary flex-shrink-0" @click="openCreateModal">
                            <i class="bi-plus-lg me-1"></i>Nouvel utilisateur
                        </button>
                    </div>
                </div>

                <!-- Contenu -->
                <div class="p-3">
                    <!-- Empty -->
                    <div v-if="!users.length" class="text-center py-5">
                        <i class="bi-people" style="font-size:48px; color:#dce3ee; display:block; margin-bottom:12px;"></i>
                        <p style="font-size:15px; color:#888; margin-bottom:16px;">Aucun utilisateur dans votre entreprise.</p>
                        <button class="isup-btn-primary" @click="openCreateModal">
                            <i class="bi-plus-lg me-1"></i>Créer un utilisateur
                        </button>
                    </div>

                    <!-- Table -->
                    <div v-else class="isup-panel">
                        <div class="isup-panel-header">
                            <i class="bi-people me-2" style="color:#FF7900;"></i>Liste des utilisateurs
                        </div>
                        <div class="isup-panel-body p-0">
                            <div class="isup-table-wrap">
                                <table class="isup-table w-100">
                                    <thead>
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
                                                    <div class="isup-user-avatar">{{ (u.name || '?').charAt(0).toUpperCase() }}</div>
                                                    <div>
                                                        <div class="isup-user-name">{{ u.name }}</div>
                                                        <div class="isup-user-email">{{ u.email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="font-size:12px;">{{ u.fonction || '-' }}</td>
                                            <td>
                                                <span class="isup-badge isup-badge-role">{{ u.role_name || 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-1">
                                                    <span v-if="!u.modules || !u.modules.length" class="text-muted small">Aucun</span>
                                                    <span v-for="mod in (u.modules || [])" :key="mod"
                                                          class="isup-mod-badge"
                                                          :class="'isup-mod-' + moduleColor(mod)">
                                                        <i :class="moduleIcon(mod)" class="me-1"></i>
                                                        {{ moduleLabel(mod) }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="isup-status" :class="u.is_active ? 'isup-status-green' : 'isup-status-grey'">
                                                    {{ statusLabel(u.is_active) }}
                                                </span>
                                            </td>
                                            <td style="font-size:11px; color:#888;">{{ shortDate(u.last_login) }}</td>
                                            <td class="text-end">
                                                <button class="isup-icon-btn" title="Permissions" @click="openPermissionsModal(u)">
                                                    <i class="bi-shield-lock"></i>
                                                </button>
                                                <button class="isup-icon-btn ms-1" :title="u.is_active ? 'Désactiver' : 'Activer'"
                                                        @click="toggleStatus(u.id, u.is_active)">
                                                    <i :class="u.is_active ? 'bi-pause-circle' : 'bi-play-circle'"></i>
                                                </button>
                                                <button class="isup-icon-btn ms-1" title="Modifier" @click="openEditModal(u.id)">
                                                    <i class="bi-pencil"></i>
                                                </button>
                                                <button class="isup-icon-btn isup-icon-danger ms-1" title="Supprimer" @click="deleteUser(u.id)">
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
            </div>
        </template>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- Create/Edit Modal -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div v-if="showModal" class="isup-modal-overlay" @click.self="closeModal">
            <div class="isup-modal isup-modal-lg">
                <div class="isup-modal-header">
                    <span>
                        <i :class="isEditing ? 'bi-pencil' : 'bi-person-plus'" class="me-2"></i>
                        {{ isEditing ? "Modifier l'utilisateur" : 'Nouvel utilisateur' }}
                    </span>
                    <button class="isup-modal-close" @click="closeModal">&times;</button>
                </div>
                <div class="isup-modal-body">
                    <!-- Basic Info -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="isup-label">Nom complet <span class="isup-req">*</span></label>
                            <input v-model="form.name" type="text" class="isup-input" required placeholder="Jean Martin">
                        </div>
                        <div class="col-md-6">
                            <label class="isup-label">Email <span class="isup-req">*</span></label>
                            <input v-model="form.email" type="email" class="isup-input" required placeholder="jean@entreprise.com">
                        </div>
                        <div class="col-md-6">
                            <label class="isup-label">
                                {{ isEditing ? 'Nouveau mot de passe (optionnel)' : 'Mot de passe *' }}
                            </label>
                            <input v-model="form.password" type="password" class="isup-input"
                                   :required="!isEditing" minlength="8" placeholder="Min. 8 caractères">
                        </div>
                        <div class="col-md-3">
                            <label class="isup-label">Rôle</label>
                            <select v-model="form.role_id" class="isup-select">
                                <option value="">Sélectionner</option>
                                <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="isup-label">Fonction</label>
                            <input v-model="form.fonction" type="text" class="isup-input" placeholder="Ex: Comptable">
                        </div>
                    </div>

                    <!-- Permissions -->
                    <div class="isup-perm-section">
                        <div class="isup-perm-title">
                            <i class="bi-shield-lock me-2"></i>
                            Permissions par module
                            <span class="isup-perm-sub">— Ce qui n'est pas coché n'existe pas</span>
                        </div>

                        <div v-if="!availableModules.length" class="isup-perm-empty">
                            <i class="bi-info-circle me-1"></i>
                            Aucun module disponible. Activez des services dans la section licences.
                        </div>

                        <div v-for="mod in availableModules" :key="mod.module" class="isup-mod-card">
                            <div class="isup-mod-header">
                                <label class="isup-switch">
                                    <input type="checkbox"
                                           :checked="isModuleActive(mod.module)"
                                           @change="toggleModule(mod.module, $event.target.checked)">
                                    <span class="isup-switch-slider"></span>
                                </label>
                                <i :class="'bi-' + moduleIcon(mod.module).replace('bi-','') + ' isup-mod-icon-' + moduleColor(mod.module)"></i>
                                <span class="isup-mod-label">{{ mod.label }}</span>
                                <span v-if="!isModuleActive(mod.module)" class="isup-mod-off">(aucun accès)</span>
                            </div>
                            <div v-if="isModuleActive(mod.module)" class="isup-perm-list">
                                <label v-for="perm in mod.permissions" :key="perm.id" class="isup-perm-item">
                                    <input type="checkbox"
                                           :checked="isPermissionActive(perm.id)"
                                           @change="togglePermission(perm.id)">
                                    <span>{{ perm.display_name }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="isup-modal-footer">
                    <button type="button" class="isup-btn-grey" @click="closeModal">Annuler</button>
                    <button type="button" class="isup-btn-primary" :disabled="submitting" @click="submitForm">
                        <span v-if="submitting" class="isup-spinner-sm me-1"></span>
                        <i v-else class="bi-check-lg me-1"></i>
                        {{ isEditing ? 'Mettre à jour' : "Créer l'utilisateur" }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- Permissions Modal (standalone) -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div v-if="showPermModal" class="isup-modal-overlay" @click.self="closePermModal">
            <div class="isup-modal">
                <div class="isup-modal-header">
                    <span><i class="bi-shield-lock me-2"></i>Permissions</span>
                    <button class="isup-modal-close" @click="closePermModal">&times;</button>
                </div>
                <div class="isup-modal-body">
                    <div v-if="!availableModules.length" class="text-muted small py-3">
                        Aucun module disponible.
                    </div>

                    <div v-for="mod in availableModules" :key="mod.module" class="isup-mod-card">
                        <div class="isup-mod-header">
                            <label class="isup-switch">
                                <input type="checkbox"
                                       :checked="isModuleActive(mod.module)"
                                       @change="toggleModule(mod.module, $event.target.checked)">
                                <span class="isup-switch-slider"></span>
                            </label>
                            <i :class="'bi-' + moduleIcon(mod.module).replace('bi-','') + ' isup-mod-icon-' + moduleColor(mod.module)"></i>
                            <span class="isup-mod-label">{{ mod.label }}</span>
                            <span v-if="!isModuleActive(mod.module)" class="isup-mod-off">(aucun accès)</span>
                        </div>
                        <div v-if="isModuleActive(mod.module)" class="isup-perm-list">
                            <label v-for="perm in mod.permissions" :key="perm.id" class="isup-perm-item">
                                <input type="checkbox"
                                       :checked="isPermissionActive(perm.id)"
                                       @change="togglePermission(perm.id)">
                                <span>{{ perm.display_name }}</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="isup-modal-footer">
                    <button type="button" class="isup-btn-grey" @click="closePermModal">Annuler</button>
                    <button type="button" class="isup-btn-primary" :disabled="savingPermissions" @click="savePermissions">
                        <span v-if="savingPermissions" class="isup-spinner-sm me-1"></span>
                        <i v-else class="bi-check-lg me-1"></i> Enregistrer
                    </button>
                </div>
            </div>
        </div>
    </CompanyLayout>
</template>

<style scoped>
/* ── Users-specific styles ── */
.isup-user-avatar { width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;color:#fff;flex-shrink:0; }
.isup-user-name { font-size:13px; font-weight:600; color:#163A5E; }
.isup-user-email { font-size:11px; color:#888; }
.isup-badge { display:inline-block; font-size:10px; font-weight:600; padding:2px 8px; border-radius:3px; }
.isup-badge-role { background:#f0f4f8; color:#555; }
.isup-mod-badge { display:inline-block; font-size:11px; font-weight:700; padding:2px 10px; border-radius:4px; text-transform:uppercase; letter-spacing:0.03em; }
.isup-mod-success { background:#e8f5e9; color:#2e7d32; }
.isup-mod-primary { background:#e3f2fd; color:#1565c0; }
.isup-mod-info { background:#e0f7fa; color:#00838f; }
.isup-mod-warning { background:#fff8e1; color:#e65100; }
.isup-mod-danger { background:#fdecea; color:#c62828; }
.isup-mod-secondary { background:#f5f5f5; color:#616161; }
.isup-mod-dark { background:#e8e8e8; color:#333; }
.isup-req { color:#e53935; }
.isup-perm-section { padding:16px; border:1px solid #eef2f7; border-radius:4px; margin-bottom:16px; }
.isup-perm-title { font-size:14px; font-weight:700; color:#163A5E; margin-bottom:8px; display:flex; align-items:center; gap:6px; }
.isup-perm-title i { color:#FF7900; }
.isup-perm-sub { font-size:11px; font-weight:400; color:#888; margin-left:8px; }
.isup-perm-empty { text-align:center; padding:20px; color:#aaa; font-size:13px; }
.isup-mod-card { display:flex; align-items:center; gap:10px; padding:10px 0; border-bottom:1px solid #f0f4f8; }
.isup-mod-header { display:flex; align-items:center; gap:8px; }
.isup-mod-label { font-size:13px; font-weight:600; color:#163A5E; }
.isup-mod-off { font-size:11px; color:#aaa; }
.isup-mod-icon-success { color:#2e7d32; font-size:16px; }
.isup-mod-icon-primary { color:#1565c0; font-size:16px; }
.isup-mod-icon-info { color:#00838f; font-size:16px; }
.isup-mod-icon-warning { color:#e65100; font-size:16px; }
.isup-mod-icon-danger { color:#c62828; font-size:16px; }
.isup-mod-icon-secondary { color:#616161; font-size:16px; }
.isup-mod-icon-dark { color:#333; font-size:16px; }
.isup-perm-list { display:flex; flex-direction:column; gap:4px; }
.isup-perm-item { display:flex; align-items:center; gap:8px; padding:4px 0; }
.isup-perm-item input[type="checkbox"] { width:16px; height:16px; accent-color:#FF7900; cursor:pointer; }
.isup-switch { position:relative; display:inline-block; width:32px; height:18px; margin-bottom:0; flex-shrink:0; }
.isup-switch input { opacity:0; width:0; height:0; }
.isup-switch-slider { position:absolute; cursor:pointer; top:0;left:0;right:0;bottom:0; background:#ccc; border-radius:18px; transition:0.2s; }
.isup-switch-slider::before { content:""; position:absolute; height:14px; width:14px; left:2px; bottom:2px; background:#fff; border-radius:50%; transition:0.2s; }
.isup-switch input:checked + .isup-switch-slider { background:#FF7900; }
.isup-switch input:checked + .isup-switch-slider::before { transform:translateX(14px); }
</style>
