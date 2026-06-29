<script setup>
import { ref, onMounted, computed } from 'vue';
import { authStore } from '../../stores/auth';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';

const props = defineProps({
    userId: { type: [Number, String], default: null },
});

const user = ref(null);
const roles = ref([]);
const allPermissions = ref([]);
const userPermissions = ref([]);
const loading = ref(true);
const saving = ref(false);
const success = ref('');
const error = ref('');

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
};

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
};

// Grouper les permissions par module
const groupedPermissions = computed(() => {
    const groups = {};
    for (const perm of allPermissions.value) {
        if (!groups[perm.module]) {
            groups[perm.module] = {
                module: perm.module,
                label: moduleLabels[perm.module] || perm.module,
                permissions: []
            };
        }
        groups[perm.module].permissions.push({
            id: perm.id,
            action: perm.action,
            display_name: perm.display_name,
            granted: userPermissions.value.includes(perm.id)
        });
    }
    return Object.values(groups);
});

async function loadData() {
    loading.value = true;
    error.value = '';
    try {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content;

        // Load roles
        if (props.userId) {
            const userRes = await fetch(`/api/company/users/${props.userId}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
            });
            if (userRes.ok) {
                const data = await userRes.json();
                user.value = data.user || data;
                userPermissions.value = data.permission_ids || data.direct_permission_ids || [];
            }
        }

        // Load all available permissions
        const permRes = await fetch('/api/company/permissions/available', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
        });
        if (permRes.ok) {
            const data = await permRes.json();
            allPermissions.value = data.permissions || data;
        }
    } catch (e) {
        console.error('Erreur chargement:', e);
        error.value = 'Erreur lors du chargement.';
    } finally {
        loading.value = false;
    }
}

function togglePermission(permId) {
    const idx = userPermissions.value.indexOf(permId);
    if (idx === -1) {
        userPermissions.value.push(permId);
    } else {
        userPermissions.value.splice(idx, 1);
    }
}

function selectAllModule(moduleName) {
    const group = groupedPermissions.value.find(g => g.module === moduleName);
    if (!group) return;
    const allGranted = group.permissions.every(p => p.granted);
    for (const perm of group.permissions) {
        const idx = userPermissions.value.indexOf(perm.id);
        if (allGranted && idx !== -1) userPermissions.value.splice(idx, 1);
        if (!allGranted && idx === -1) userPermissions.value.push(perm.id);
    }
}

async function savePermissions() {
    saving.value = true;
    error.value = '';
    success.value = '';
    try {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch(`/api/company/users/${props.userId}/permissions`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify({ permissions: userPermissions.value })
        });
        if (res.ok) {
            success.value = 'Permissions mises à jour avec succès.';
            setTimeout(() => success.value = '', 3000);
        } else {
            const errData = await res.json();
            error.value = errData.message || 'Erreur lors de la sauvegarde.';
        }
    } catch (e) {
        error.value = 'Erreur de communication.';
    } finally {
        saving.value = false;
    }
}

onMounted(loadData);
</script>

<template>
<CompanyLayout>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Permissions Utilisateur</h3>
                <p class="text-muted mb-0" v-if="user">
                    Utilisateur : <strong>{{ user.name }}</strong>
                    <span class="badge bg-secondary-subtle text-secondary ms-2">{{ user.email }}</span>
                </p>
            </div>
            <button class="btn btn-primary" :disabled="saving || loading" @click="savePermissions">
                <i class="bi bi-check-lg me-1"></i>
                {{ saving ? 'Enregistrement...' : 'Enregistrer' }}
            </button>
        </div>

        <!-- Messages -->
        <div v-if="success" class="alert alert-success alert-dismissible fade show py-2">
            <i class="bi bi-check-circle me-2"></i>{{ success }}
        </div>
        <div v-if="error" class="alert alert-danger py-2">{{ error }}</div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
            <p class="mt-2 text-muted">Chargement des permissions...</p>
        </div>

        <!-- Permissions Grid -->
        <div v-else class="row g-3">
            <div v-for="group in groupedPermissions" :key="group.module" class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center py-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="d-flex align-items-center justify-content-center"
                                 style="width: 36px; height: 36px; background: #f5f5f5; border-radius: 10px;">
                                <i :class="moduleIcons[group.module] || 'bi-grid'"
                                   style="color: #FF7900; font-size: 18px;"></i>
                            </div>
                            <h6 class="mb-0 fw-semibold">{{ group.label }}</h6>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary" @click="selectAllModule(group.module)"
                                :title="group.permissions.every(p => p.granted) ? 'Tout désélectionner' : 'Tout sélectionner'">
                            <i class="bi bi-check-all"></i>
                        </button>
                    </div>
                    <div class="card-body pt-0">
                        <div v-for="perm in group.permissions" :key="perm.id"
                             class="form-check py-1 permission-item">
                            <input class="form-check-input" type="checkbox"
                                   :id="'perm-' + perm.id"
                                   :checked="perm.granted"
                                   @change="togglePermission(perm.id)">
                            <label class="form-check-label small cursor-pointer" :for="'perm-' + perm.id">
                                {{ perm.display_name }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- No User -->
        <div v-if="!props.userId && !loading" class="text-center py-5 text-muted">
            <i class="bi bi-person fs-1 d-block mb-3"></i>
            <p>Aucun utilisateur sélectionné.</p>
        </div>
    </div>
</CompanyLayout>
</template>

<style scoped>
.permission-item {
    border-radius: 6px;
    transition: background 0.15s;
    padding-left: 1.5rem;
}
.permission-item:hover {
    background: #f8f9fa;
}
.cursor-pointer {
    cursor: pointer;
}
.form-check-input:checked {
    background-color: #FF7900;
    border-color: #FF7900;
}
</style>
