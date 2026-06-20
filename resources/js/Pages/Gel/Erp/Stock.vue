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
        <!-- Actions -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h5 class="fw-bold mb-1">Gestion des Stocks</h5>
                <p class="text-muted small mb-0">Suivi des stocks, mouvements et entrepôts</p>
            </div>
            <div class="d-flex gap-2">
                <button v-if="activeTab === 'warehouses'" class="btn btn-primary btn-sm" @click="showWarehouseModal = true">
                    <i class="bi-plus-lg me-1"></i>Entrepôt
                </button>
                <button class="btn btn-outline-primary btn-sm" @click="showMovementModal = true">
                    <i class="bi-arrow-left-right me-1"></i>Mouvement
                </button>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-4">
            <li v-for="tab in tabs" :key="tab.key" class="nav-item">
                <button class="nav-link" :class="{ active: activeTab === tab.key }" @click="activeTab = tab.key">
                    <i :class="tab.icon" class="me-1"></i>{{ tab.label }}
                </button>
            </li>
        </ul>

        <!-- Loading -->
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement…</span></div>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="alert alert-danger">
            <i class="bi-exclamation-triangle-fill me-2"></i>{{ error }}
            <button @click="fetchData" class="btn btn-primary btn-sm ms-3">
                <i class="bi-arrow-clockwise me-1"></i>Réessayer
            </button>
        </div>

        <!-- ══ STOCK TAB ══ -->
        <div v-else-if="activeTab === 'stock'" class="bg-white rounded-lg shadow p-6">
            <h6 class="fw-bold mb-3"><i class="bi-boxes me-2" style="color:#FF7900;"></i>État des Stocks</h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="small text-muted">
                        <tr><th>Réf.</th><th>Désignation</th><th class="text-end">Stock</th><th>Statut</th></tr>
                    </thead>
                    <tbody>
                        <tr v-if="!stockData.length">
                            <td colspan="4" class="text-center py-4 text-muted small">
                                <i class="bi-inbox fs-4 d-block mb-1"></i>Aucun article en stock
                            </td>
                        </tr>
                        <tr v-for="item in stockData" :key="item.id">
                            <td class="fw-medium">{{ item.reference }}</td>
                            <td>{{ item.designation }}</td>
                            <td class="text-end fw-bold" :class="item.stock <= 0 ? 'text-danger' : item.stock <= (item.alert || 0) ? 'text-warning' : 'text-success'">
                                {{ item.stock }}
                            </td>
                            <td>
                                <span v-if="item.stock <= 0" class="badge bg-danger">Rupture</span>
                                <span v-else-if="item.stock <= (item.alert || 0)" class="badge bg-warning text-dark">Stock bas</span>
                                <span v-else class="badge bg-success">OK</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ══ MOVEMENTS TAB ══ -->
        <div v-else-if="activeTab === 'movements'" class="bg-white rounded-lg shadow p-6">
            <h6 class="fw-bold mb-3"><i class="bi-arrow-left-right me-2" style="color:#FF7900;"></i>Mouvements</h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="small text-muted">
                        <tr><th>Date</th><th>Article</th><th>Entrepôt</th><th>Type</th><th class="text-end">Qté</th><th>Description</th></tr>
                    </thead>
                    <tbody>
                        <tr v-if="!movements.length">
                            <td colspan="6" class="text-center py-4 text-muted small">
                                <i class="bi-inbox fs-4 d-block mb-1"></i>Aucun mouvement
                            </td>
                        </tr>
                        <tr v-for="m in movements" :key="m.id">
                            <td class="small text-muted">{{ m.created_at ? $formatDate(m.created_at) : '-' }}</td>
                            <td class="small">{{ m.item?.reference }} — {{ m.item?.designation }}</td>
                            <td class="small">{{ m.warehouse?.name || '-' }}</td>
                            <td><span class="badge" :class="m.type === 'entry' ? 'bg-success' : 'bg-danger'">{{ m.type === 'entry' ? 'Entrée' : 'Sortie' }}</span></td>
                            <td class="text-end fw-medium small">{{ m.quantity }}</td>
                            <td class="small text-muted">{{ m.description || '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ══ WAREHOUSES TAB ══ -->
        <div v-else-if="activeTab === 'warehouses'" class="bg-white rounded-lg shadow p-6">
            <h6 class="fw-bold mb-3"><i class="bi-building me-2" style="color:#FF7900;"></i>Entrepôts</h6>
            <div class="row g-3">
                <div v-if="!warehouses.length" class="col-12">
                    <div class="text-center py-5 text-muted small">
                        <i class="bi-building fs-1 d-block mb-2" style="color:#dce3ee;"></i>
                        Aucun entrepôt
                    </div>
                </div>
                <div v-for="wh in warehouses" :key="wh.id" class="col-md-4 col-6">
                    <div class="border rounded p-3 d-flex align-items-center gap-3">
                        <div class="rounded p-2" style="background:#e3f2fd; color:#1565c0; font-size:20px;">
                            <i class="bi-building"></i>
                        </div>
                        <div>
                            <div class="fw-medium small">{{ wh.name }}</div>
                            <div class="small text-muted">{{ wh.location || 'Localisation non spécifiée' }}</div>
                            <div v-if="wh.description" class="small text-muted">{{ wh.description }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Movement Modal -->
        <div v-if="showMovementModal" class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold">Nouveau mouvement de stock</h6>
                        <button type="button" class="btn-close" @click="showMovementModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small">Type</label>
                                <select v-model="movementForm.type" class="form-select form-select-sm">
                                    <option value="entry">Entrée</option>
                                    <option value="exit">Sortie</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Quantité</label>
                                <input v-model="movementForm.quantity" type="number" min="1" class="form-control form-control-sm">
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Article</label>
                                <select v-model="movementForm.item_id" class="form-select form-select-sm">
                                    <option value="">Sélectionner</option>
                                    <option v-for="item in stockData" :key="item.id" :value="item.id">{{ item.reference }} — {{ item.designation }}</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Entrepôt</label>
                                <select v-model="movementForm.warehouse_id" class="form-select form-select-sm">
                                    <option value="">Sélectionner</option>
                                    <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">{{ wh.name }}</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Description</label>
                                <input v-model="movementForm.description" class="form-control form-control-sm" placeholder="Optionnelle">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-secondary" @click="showMovementModal = false">Annuler</button>
                        <button class="btn btn-sm btn-primary" :disabled="submitting" @click="submitMovement">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>Enregistrer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Warehouse Modal -->
        <div v-if="showWarehouseModal" class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold">Nouvel entrepôt</h6>
                        <button type="button" class="btn-close" @click="showWarehouseModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label small">Nom *</label>
                            <input v-model="warehouseForm.name" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Localisation</label>
                            <input v-model="warehouseForm.location" class="form-control form-control-sm" placeholder="Optionnelle">
                        </div>
                        <div>
                            <label class="form-label small">Description</label>
                            <textarea v-model="warehouseForm.description" class="form-control form-control-sm" rows="2" placeholder="Optionnelle"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-secondary" @click="showWarehouseModal = false">Annuler</button>
                        <button class="btn btn-sm btn-primary" :disabled="submitting" @click="submitWarehouse">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>Créer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>
