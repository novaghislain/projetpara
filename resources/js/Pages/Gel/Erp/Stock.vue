<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';
import { authStore } from '../../../stores/auth';

const stockData = ref([]);
const movements = ref([]);
const warehouses = ref([]);
const loading = ref(true);
const error = ref(null);
const activeTab = ref('stock');
const submitting = ref(false);

// Movement modal
const showMovementModal = ref(false);
const movementForm = ref({ item_id: '', warehouse_id: '', type: 'entry', quantity: 1, description: '' });

// Warehouse modal
const showWarehouseModal = ref(false);
const warehouseForm = ref({ name: '', location: '', description: '' });

const tabs = [
    { key: 'stock',      label: 'Stock',      icon: 'bi-boxes' },
    { key: 'movements',  label: 'Mouvements',  icon: 'bi-arrow-left-right' },
    { key: 'warehouses', label: 'Entrepôts',   icon: 'bi-building' },
];

const fetchData = async () => {
    loading.value = true;
    error.value = null;
    try {
        const res = await Promise.all([
            fetch('/api/erp/stock').then(r => r.ok ? r.json() : []),
            fetch('/api/erp/movements').then(r => r.ok ? r.json() : []),
            fetch('/api/erp/warehouses').then(r => r.ok ? r.json() : []),
        ]);
        stockData.value = res[0];
        movements.value = res[1];
        warehouses.value = res[2];
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const submitMovement = async () => {
    submitting.value = true;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/erp/stocks/movements', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(movementForm.value),
        });
        if (!res.ok) throw new Error('Erreur');
        showMovementModal.value = false;
        movementForm.value = { item_id: '', warehouse_id: '', type: 'entry', quantity: 1, description: '' };
        await fetchData();
    } catch (e) {
        alert('Erreur: ' + e.message);
    } finally {
        submitting.value = false;
    }
};

const submitWarehouse = async () => {
    submitting.value = true;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/erp/stocks/warehouses', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(warehouseForm.value),
        });
        if (!res.ok) throw new Error('Erreur');
        showWarehouseModal.value = false;
        warehouseForm.value = { name: '', location: '', description: '' };
        await fetchData();
    } catch (e) {
        alert('Erreur: ' + e.message);
    } finally {
        submitting.value = false;
    }
};

onMounted(fetchData);
</script>

<template>
    <GelLayout page-title="Gestion des Stocks">
        <div class="isup-shell">

            <!-- Header -->
            <div class="isup-portal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="isup-portal-logo">
                        <i class="bi-boxes" style="font-size:20px;"></i>
                    </div>
                    <div>
                        <div class="isup-portal-company">Gestion des Stocks</div>
                        <div class="isup-portal-sub">Suivi des stocks, mouvements et entrepôts</div>
                    </div>
                </div>
            </div>

            <!-- Tabs bar -->
            <div class="isup-tabs-bar">
                <button v-for="tab in tabs" :key="tab.key"
                        class="isup-tab"
                        :class="{ 'isup-tab-active': activeTab === tab.key }"
                        @click="activeTab = tab.key">
                    <i :class="tab.icon"></i>
                    {{ tab.label }}
                </button>
                <div class="ms-auto d-flex align-items-center px-2 gap-2">
                    <button v-if="activeTab === 'warehouses'" class="isup-btn-primary btn-sm" @click="showWarehouseModal = true">
                        <i class="bi-plus-lg me-1"></i>Entrepôt
                    </button>
                    <button class="isup-btn-orange btn-sm" @click="showMovementModal = true">
                        <i class="bi-arrow-left-right me-1"></i>Mouvement
                    </button>
                </div>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="d-flex align-items-center justify-content-center gap-3 py-5">
                <div class="isup-spinner"></div>
                <span style="color:#888; font-size:14px;">Chargement…</span>
            </div>

            <!-- Error -->
            <div v-else-if="error" class="isup-alert-error m-3">
                <i class="bi-exclamation-triangle-fill me-2"></i>{{ error }}
                <button @click="fetchData" class="isup-btn-primary ms-3" style="padding:4px 12px; font-size:11px;">
                    <i class="bi-arrow-clockwise me-1"></i>Réessayer
                </button>
            </div>

            <!-- ══ STOCK TAB ══ -->
            <div v-else-if="activeTab === 'stock'" class="p-3">
                <div class="isup-panel">
                    <div class="isup-panel-header">
                        <i class="bi-boxes me-2" style="color:#FF7900;"></i>État des Stocks
                    </div>
                    <div class="isup-panel-body p-0">
                        <div class="isup-table-wrap">
                            <table class="isup-table w-100">
                                <thead>
                                    <tr>
                                        <th>Réf.</th>
                                        <th>Désignation</th>
                                        <th class="text-end">Stock</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!stockData.length">
                                        <td colspan="4" class="text-center py-4 text-muted" style="font-size:13px;">
                                            <i class="bi-inbox" style="font-size:22px; display:block; margin-bottom:6px;"></i>Aucun article en stock
                                        </td>
                                    </tr>
                                    <tr v-for="item in stockData" :key="item.id">
                                        <td class="fw-semibold" style="color:#163A5E;">{{ item.reference }}</td>
                                        <td>{{ item.designation }}</td>
                                        <td class="text-end fw-bold" :class="item.stock <= 0 ? 'text-danger' : item.stock <= (item.alert || 0) ? 'text-warning' : 'text-success'">
                                            {{ item.stock }}
                                        </td>
                                        <td>
                                            <span v-if="item.stock <= 0" class="isup-status isup-status-red">Rupture</span>
                                            <span v-else-if="item.stock <= (item.alert || 0)" class="isup-status isup-status-warn">Stock bas</span>
                                            <span v-else class="isup-status isup-status-green">OK</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ MOVEMENTS TAB ══ -->
            <div v-else-if="activeTab === 'movements'" class="p-3">
                <div class="isup-panel">
                    <div class="isup-panel-header">
                        <i class="bi-arrow-left-right me-2" style="color:#FF7900;"></i>Mouvements
                    </div>
                    <div class="isup-panel-body p-0">
                        <div class="isup-table-wrap">
                            <table class="isup-table w-100">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Article</th>
                                        <th>Entrepôt</th>
                                        <th>Type</th>
                                        <th class="text-end">Qté</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!movements.length">
                                        <td colspan="6" class="text-center py-4 text-muted" style="font-size:13px;">
                                            <i class="bi-inbox" style="font-size:22px; display:block; margin-bottom:6px;"></i>Aucun mouvement
                                        </td>
                                    </tr>
                                    <tr v-for="m in movements" :key="m.id">
                                        <td style="font-size:12px; color:#888;">{{ m.created_at ? $formatDate(m.created_at) : '-' }}</td>
                                        <td style="font-size:12px;">{{ m.item?.reference }} — {{ m.item?.designation }}</td>
                                        <td style="font-size:12px;">{{ m.warehouse?.name || '-' }}</td>
                                        <td>
                                            <span class="isup-status" :class="m.type === 'entry' ? 'isup-status-green' : 'isup-status-red'">
                                                {{ m.type === 'entry' ? 'Entrée' : 'Sortie' }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold" style="font-size:13px;">{{ m.quantity }}</td>
                                        <td style="font-size:12px; color:#888;">{{ m.description || '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ WAREHOUSES TAB ══ -->
            <div v-else-if="activeTab === 'warehouses'" class="p-3">
                <div class="row g-3">
                    <div v-if="!warehouses.length" class="col-12">
                        <div class="text-center py-5 text-muted" style="font-size:13px;">
                            <i class="bi-building" style="font-size:28px; display:block; margin-bottom:8px; color:#dce3ee;"></i>
                            Aucun entrepôt
                        </div>
                    </div>
                    <div v-for="wh in warehouses" :key="wh.id" class="col-md-4 col-6">
                        <div class="isup-wh-card">
                            <div class="isup-wh-icon">
                                <i class="bi-building"></i>
                            </div>
                            <div class="isup-wh-name">{{ wh.name }}</div>
                            <div class="isup-wh-loc">{{ wh.location || 'Localisation non spécifiée' }}</div>
                            <div v-if="wh.description" class="isup-wh-desc">{{ wh.description }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Movement Modal -->
        <div v-if="showMovementModal" class="isup-modal-overlay" @click.self="showMovementModal = false">
            <div class="isup-modal">
                <div class="isup-modal-header">
                    <span>Nouveau mouvement de stock</span>
                    <button class="isup-modal-close" @click="showMovementModal = false">&times;</button>
                </div>
                <div class="isup-modal-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="isup-label">Type</label>
                            <select v-model="movementForm.type" class="isup-select">
                                <option value="entry">Entrée</option>
                                <option value="exit">Sortie</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="isup-label">Quantité</label>
                            <input v-model="movementForm.quantity" type="number" min="1" class="isup-input">
                        </div>
                        <div class="col-6">
                            <label class="isup-label">Article</label>
                            <select v-model="movementForm.item_id" class="isup-select">
                                <option value="">Sélectionner</option>
                                <option v-for="item in stockData" :key="item.id" :value="item.id">{{ item.reference }} — {{ item.designation }}</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="isup-label">Entrepôt</label>
                            <select v-model="movementForm.warehouse_id" class="isup-select">
                                <option value="">Sélectionner</option>
                                <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">{{ wh.name }}</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="isup-label">Description</label>
                            <input v-model="movementForm.description" class="isup-input" placeholder="Optionnelle">
                        </div>
                    </div>
                </div>
                <div class="isup-modal-footer">
                    <button class="isup-btn-grey" @click="showMovementModal = false">Annuler</button>
                    <button class="isup-btn-primary" :disabled="submitting" @click="submitMovement">
                        <span v-if="submitting" class="isup-spinner-sm me-1"></span>Enregistrer
                    </button>
                </div>
            </div>
        </div>

        <!-- Warehouse Modal -->
        <div v-if="showWarehouseModal" class="isup-modal-overlay" @click.self="showWarehouseModal = false">
            <div class="isup-modal">
                <div class="isup-modal-header">
                    <span>Nouvel entrepôt</span>
                    <button class="isup-modal-close" @click="showWarehouseModal = false">&times;</button>
                </div>
                <div class="isup-modal-body">
                    <div class="mb-2">
                        <label class="isup-label">Nom *</label>
                        <input v-model="warehouseForm.name" class="isup-input" required>
                    </div>
                    <div class="mb-2">
                        <label class="isup-label">Localisation</label>
                        <input v-model="warehouseForm.location" class="isup-input" placeholder="Optionnelle">
                    </div>
                    <div>
                        <label class="isup-label">Description</label>
                        <textarea v-model="warehouseForm.description" class="isup-input" rows="2" placeholder="Optionnelle"></textarea>
                    </div>
                </div>
                <div class="isup-modal-footer">
                    <button class="isup-btn-grey" @click="showWarehouseModal = false">Annuler</button>
                    <button class="isup-btn-primary" :disabled="submitting" @click="submitWarehouse">
                        <span v-if="submitting" class="isup-spinner-sm me-1"></span>Créer
                    </button>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<style scoped>
/* ══ ERP Stock — unique styles ══ */

.isup-tabs-bar { display:flex; background:#f5f7fb; border-bottom:1px solid #dce3ee; align-items:stretch; }
.isup-tab { background:transparent; border:none; color:#888; font-size:13px; font-weight:600; padding:10px 18px; cursor:pointer; white-space:nowrap; border-bottom:3px solid transparent; transition:all 0.15s; display:flex; align-items:center; gap:6px; }
.isup-tab:hover { color:#163A5E; background:#eef3f9; }
.isup-tab.isup-tab-active { color:#163A5E; border-bottom-color:#FF7900; background:#fff; font-weight:700; }
.isup-tab i { font-size:14px; }

.isup-status-warn { background:#fff8e1; color:#f57f17; }

.isup-wh-card { background:#fff; border:1px solid #dce3ee; border-radius:6px; padding:14px 16px; display:flex; align-items:center; gap:14px; }
.isup-wh-icon { width:44px; height:44px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; background:#e3f2fd; color:#1565c0; }

.isup-badge-sm { display:inline-flex; align-items:center; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; padding:2px 9px; border-radius:3px; }
</style>
