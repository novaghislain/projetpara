<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';

const staff = ref([]);
const roles = ref([]);
const loading = ref(true);
const error = ref(null);
const submitting = ref(false);

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = ref({
    name: '',
    email: '',
    password: '',
    role_id: '',
    fonction: '',
    phone: '',
});

function resetForm() {
    form.value = { name: '', email: '', password: '', role_id: '', fonction: '', phone: '' };
}

const roleBadgeClass = (roleSlug) => {
    const map = {
        director: 'gel-badge--danger',
        pole_responsible: 'gel-badge--warning',
        collaborator: 'gel-badge--info',
        secretaire: 'gel-badge--purple',
        comptable: 'gel-badge--primary',
    };
    return map[roleSlug] || 'gel-badge--grey';
};

const roleIcon = (roleSlug) => {
    const map = {
        director: 'bi-person-fill-gear',
        pole_responsible: 'bi-diagram-3',
        collaborator: 'bi-person',
        secretaire: 'bi-file-text',
        comptable: 'bi-calculator',
    };
    return map[roleSlug] || 'bi-person-badge';
};

// ─── API ──
async function fetchStaff() {
    loading.value = true; error.value = null;
    try {
        const res = await fetch('/api/gel/personnel');
        if (!res.ok) throw new Error('Erreur chargement');
        const data = await res.json();
        staff.value = data.staff || [];
        roles.value = data.roles || [];
    } catch (e) { error.value = e.message; }
    finally { loading.value = false; }
}

async function submitForm() {
    submitting.value = true;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const url = isEditing.value ? `/api/gel/personnel/${editingId.value}` : '/api/gel/personnel';
        const method = isEditing.value ? 'PUT' : 'POST';
        const payload = { ...form.value };
        if (isEditing.value && !payload.password) delete payload.password;

        const res = await fetch(url, {
            method,
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload),
        });
        if (!res.ok) {
            const errData = await res.json();
            throw new Error(errData.message || 'Erreur');
        }
        showModal.value = false;
        await fetchStaff();
    } catch (e) { alert('Erreur: ' + e.message); }
    finally { submitting.value = false; }
}

async function deleteStaff(id) {
    if (!confirm('Confirmer la suppression ?')) return;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch(`/api/gel/personnel/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error('Erreur suppression');
        await fetchStaff();
    } catch (e) { alert('Erreur: ' + e.message); }
}

async function toggleStatus(id, currentStatus) {
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        await fetch(`/api/gel/personnel/${id}`, {
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ is_active: !currentStatus }),
        });
        await fetchStaff();
    } catch (e) { alert('Erreur: ' + e.message); }
}

function openCreateModal() {
    resetForm(); isEditing.value = false; editingId.value = null; showModal.value = true;
}

function openEditModal(user) {
    form.value = {
        name: user.name, email: user.email, password: '',
        role_id: '', fonction: user.fonction || '', phone: user.phone || '',
    };
    isEditing.value = true; editingId.value = user.id; showModal.value = true;
}

onMounted(fetchStaff);
</script>

<template>
    <GelLayout page-title="Personnel GEL">
        <div class="gel-shell">
            <!-- ══ HEADER ══ -->
            <div class="gel-header">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="gel-header-logo"><i class="bi-people-fill"></i></div>
                    <div class="flex-grow-1">
                        <div class="gel-header-title">Personnel GEL</div>
                        <div class="gel-header-sub">Gérez les comptes de votre équipe (comptables, secrétaires, collaborateurs)</div>
                    </div>
                    <button class="gel-btn gel-btn-primary" @click="openCreateModal">
                        <i class="bi-plus-lg me-1"></i>Nouveau membre
                    </button>
                </div>
            </div>

            <div class="p-3">
                <!-- Loading -->
                <div v-if="loading" class="text-center py-5">
                    <div class="gel-spinner" style="margin:0 auto 12px;"></div>
                    <span style="color:#888;font-size:13px;">Chargement...</span>
                </div>

                <!-- Error -->
                <div v-else-if="error" class="gel-alert-error">{{ error }}</div>

                <!-- Empty -->
                <div v-else-if="!staff.length" class="gel-empty">
                    <i class="bi-people" style="font-size:48px;color:#dce3ee;"></i>
                    <div style="font-weight:700;color:#888;margin-top:8px;">Aucun membre du personnel</div>
                    <p style="font-size:13px;color:#aaa;margin:4px 0 12px;">Créez votre premier compte comptable ou secrétaire.</p>
                    <button class="gel-btn gel-btn-primary" @click="openCreateModal">
                        <i class="bi-plus-lg me-1"></i>Créer un compte
                    </button>
                </div>

                <!-- Table -->
                <div v-else class="gel-card">
                    <div class="gel-card-header">
                        <i class="bi-list-ul me-2" style="color:#FF7900;"></i>
                        Équipe GEL ({{ staff.length }})
                    </div>
                    <div class="gel-card-body p-0">
                        <div class="gel-table-wrap">
                            <table class="gel-table">
                                <thead>
                                    <tr>
                                        <th>Membre</th>
                                        <th>Rôle</th>
                                        <th>Fonction</th>
                                        <th>Statut</th>
                                        <th>Dernière connexion</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="u in staff" :key="u.id">
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="gel-avatar gel-avatar--sm">{{ (u.name || '?').charAt(0).toUpperCase() }}</div>
                                                <div>
                                                    <div class="gel-user-name">{{ u.name }}</div>
                                                    <div class="gel-user-email">{{ u.email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="gel-role-badge" :class="roleBadgeClass(u.role)">
                                                <i :class="roleIcon(u.role)" class="me-1"></i>
                                                {{ u.role_name || u.role }}
                                            </span>
                                        </td>
                                        <td style="font-size:12px;color:#666;">{{ u.fonction || '-' }}</td>
                                        <td>
                                            <span class="gel-status" :class="u.is_active ? 'gel-status--active' : 'gel-status--inactive'">
                                                {{ u.is_active ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </td>
                                        <td style="font-size:11px;color:#888;">{{ u.last_login || 'Jamais' }}</td>
                                        <td class="text-end">
                                            <button class="gel-icon-btn" :title="u.is_active ? 'Désactiver' : 'Activer'"
                                                    @click="toggleStatus(u.id, u.is_active)">
                                                <i :class="u.is_active ? 'bi-pause-circle' : 'bi-play-circle'"></i>
                                            </button>
                                            <button class="gel-icon-btn ms-1" title="Modifier" @click="openEditModal(u)">
                                                <i class="bi-pencil"></i>
                                            </button>
                                            <button class="gel-icon-btn gel-icon-btn--danger ms-1" title="Supprimer" @click="deleteStaff(u.id)">
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

        <!-- ══ MODAL ══ -->
        <div v-if="showModal" class="gel-overlay" @click.self="showModal = false">
            <div class="gel-modal">
                <div class="gel-modal-header">
                    <span><i class="bi-person-plus me-2"></i>{{ isEditing ? 'Modifier' : 'Nouveau membre' }} du personnel</span>
                    <button class="gel-modal-close" @click="showModal = false">&times;</button>
                </div>
                <form @submit.prevent="submitForm" class="gel-modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="gel-label">Nom complet <span class="gel-req">*</span></label>
                            <input v-model="form.name" type="text" required class="gel-input" placeholder="Jean Martin">
                        </div>
                        <div class="col-md-6">
                            <label class="gel-label">Email <span class="gel-req">*</span></label>
                            <input v-model="form.email" type="email" required class="gel-input" placeholder="jean@gelcabinet.com">
                        </div>
                        <div class="col-md-6">
                            <label class="gel-label">
                                {{ isEditing ? 'Nouveau mot de passe (optionnel)' : 'Mot de passe *' }}
                            </label>
                            <input v-model="form.password" type="password" class="gel-input"
                                   :required="!isEditing" minlength="8" placeholder="Min. 8 caractères">
                        </div>
                        <div class="col-md-3">
                            <label class="gel-label">Rôle</label>
                            <select v-model="form.role_id" class="gel-select">
                                <option value="">-- Choisir --</option>
                                <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="gel-label">Téléphone</label>
                            <input v-model="form.phone" type="text" class="gel-input" placeholder="+229 01 02 03 04">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="gel-label">Fonction</label>
                        <input v-model="form.fonction" type="text" class="gel-input" placeholder="Ex: Comptable principal, Secrétaire de direction">
                    </div>

                    <div class="gel-info-box">
                        <i class="bi-info-circle me-2"></i>
                        <span>Les permissions détaillées seront configurées après la création, dans la section dédiée.</span>
                    </div>
                </form>
                <div class="gel-modal-footer">
                    <button type="button" class="gel-btn gel-btn-grey" @click="showModal = false">Annuler</button>
                    <button type="button" class="gel-btn gel-btn-primary" :disabled="submitting" @click="submitForm">
                        <span v-if="submitting" class="gel-spinner-sm me-1"></span>
                        <i v-else class="bi-check-lg me-1"></i>
                        {{ isEditing ? 'Mettre à jour' : "Créer le compte" }}
                    </button>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<style scoped>
/* ── Shell ── */
.gel-shell { min-height:100%; }
.gel-header {
    background:#163A5E; color:#fff; padding:16px 20px;
    display:flex; flex-direction:column; gap:10px;
}
.gel-header-logo {
    width:44px; height:44px; background:rgba(255,255,255,0.12);
    border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0;
}
.gel-header-title { font-family:'Outfit',sans-serif; font-size:18px; font-weight:700; }
.gel-header-sub { font-size:12px; color:rgba(255,255,255,.65); }
.gel-btn {
    display:inline-flex; align-items:center; gap:4px;
    padding:8px 16px; font-size:12.5px; font-weight:700; border-radius:6px;
    border:none; cursor:pointer; text-decoration:none; transition:all 0.15s; white-space:nowrap;
}
.gel-btn-primary { background:#FF7900; color:#fff; }
.gel-btn-primary:hover { background:#e06700; }
.gel-btn-grey { background:#f3f4f6; color:#374151; }
.gel-btn-grey:hover { background:#e5e7eb; }
.gel-card {
    border:1px solid #dce3ee; border-radius:8px; background:#fff;
    box-shadow:0 1px 3px rgba(0,0,0,.04);
}
.gel-card-header {
    padding:14px 18px; border-bottom:1px solid #dce3ee;
    font-size:14px; font-weight:700; color:#163A5E;
}
.gel-card-body { border-radius:0 0 8px 8px; }
.gel-table-wrap { overflow-x:auto; }
.gel-table { width:100%; border-collapse:collapse; font-size:13px; }
.gel-table th {
    background:#f8f9fc; color:#4a5568; font-weight:600; font-size:11px;
    text-transform:uppercase; letter-spacing:0.04em;
    border-bottom:1px solid #dce3ee; padding:10px 14px; text-align:left; white-space:nowrap;
}
.gel-table td { padding:10px 14px; border-bottom:1px solid #f0f4f8; color:#2d3748; }
.gel-table tbody tr:hover { background:#fafbfe; }
.gel-avatar {
    width:36px; height:36px; border-radius:50%; display:flex; align-items:center;
    justify-content:center; font-weight:700; font-size:14px; color:#fff; flex-shrink:0;
    background:linear-gradient(135deg,#FF7900,#FF9A3E);
}
.gel-avatar--sm { width:32px; height:32px; font-size:12px; }
.gel-user-name { font-size:13px; font-weight:600; color:#163A5E; }
.gel-user-email { font-size:11px; color:#888; }
.gel-role-badge {
    display:inline-flex; align-items:center; gap:4px;
    font-size:11px; font-weight:600; padding:4px 10px; border-radius:4px;
    white-space:nowrap;
}
.gel-badge--danger { background:#fdecea; color:#c62828; }
.gel-badge--warning { background:#fff8e1; color:#e65100; }
.gel-badge--info { background:#e3f2fd; color:#1565c0; }
.gel-badge--purple { background:#f3e8ff; color:#6b21a8; }
.gel-badge--primary { background:#e8f5e9; color:#2e7d32; }
.gel-badge--grey { background:#f5f5f5; color:#616161; }
.gel-status {
    display:inline-block; font-size:11px; font-weight:600; padding:2px 10px; border-radius:4px;
}
.gel-status--active { background:#d1fae5; color:#065f46; }
.gel-status--inactive { background:#f3f4f6; color:#6b7280; }
.gel-icon-btn {
    width:30px; height:30px; border:none; border-radius:6px;
    background:transparent; color:#64748b; cursor:pointer;
    display:inline-flex; align-items:center; justify-content:center;
    font-size:14px; transition:all 0.15s;
}
.gel-icon-btn:hover { background:#eef3f9; color:#163A5E; }
.gel-icon-btn--danger:hover { background:#fef2f2; color:#dc2626; }
.gel-empty {
    display:flex; flex-direction:column; align-items:center;
    justify-content:center; padding:60px 20px;
}
.gel-alert-error {
    background:#fef2f2; color:#dc2626; border:1px solid #fecaca;
    border-radius:6px; padding:12px 16px; font-size:13px;
}
.gel-spinner {
    width:32px; height:32px; border:3px solid #dce3ee;
    border-top-color:#FF7900; border-radius:50%; animation:gelSpin 0.6s linear infinite;
}
@keyframes gelSpin { to { transform:rotate(360deg); } }
.gel-spinner-sm {
    width:14px; height:14px; border:2px solid rgba(255,255,255,.3);
    border-top-color:#fff; border-radius:50%; animation:gelSpin 0.6s linear infinite;
    display:inline-block; vertical-align:middle;
}
.gel-overlay {
    position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:1060;
    display:flex; align-items:center; justify-content:center; padding:20px;
}
.gel-modal {
    background:#fff; border-radius:12px; width:100%; max-width:560px;
    max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,.15);
}
.gel-modal-header {
    display:flex; align-items:center; justify-content:space-between;
    padding:16px 20px; border-bottom:1px solid #dce3ee;
    font-size:15px; font-weight:700; color:#163A5E;
}
.gel-modal-close { background:none; border:none; font-size:24px; color:#94a3b8; cursor:pointer; }
.gel-modal-close:hover { color:#163A5E; }
.gel-modal-body { padding:20px; }
.gel-modal-footer {
    display:flex; justify-content:flex-end; gap:8px;
    padding:12px 20px; border-top:1px solid #dce3ee;
}
.gel-label { display:block; font-size:11.5px; font-weight:600; color:#163A5E; margin-bottom:4px; }
.gel-req { color:#e53935; }
.gel-input {
    width:100%; padding:8px 12px; font-size:13px;
    border:1.5px solid #dce3ee; border-radius:6px; outline:none;
    background:#fff; transition:border-color 0.15s; color:#1e293b;
}
.gel-input:focus { border-color:#FF7900; box-shadow:0 0 0 3px rgba(255,121,0,.1); }
.gel-select {
    width:100%; padding:8px 12px; font-size:13px;
    border:1.5px solid #dce3ee; border-radius:6px; outline:none;
    background:#fff; color:#1e293b; cursor:pointer;
}
.gel-select:focus { border-color:#FF7900; box-shadow:0 0 0 3px rgba(255,121,0,.1); }
.gel-info-box {
    display:flex; align-items:center; gap:6px;
    background:#f0f7ff; border:1px solid #d0e3ff;
    border-radius:6px; padding:10px 14px; font-size:12px; color:#2563eb;
}

@media (max-width:767px) {
    .gel-header { padding:12px 14px; }
}
</style>
