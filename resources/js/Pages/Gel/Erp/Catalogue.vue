<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';
import { authStore } from '../../../stores/auth';

const items = ref([]);
const categories = ref([]);
const loading = ref(true);
const error = ref(null);
const searchTerm = ref('');
const filterCategory = ref('');
const submitting = ref(false);

// Item modal
const showItemModal = ref(false);
const itemForm = ref({ reference: '', designation: '', description: '', price: '', category_id: '', stock_alert: 10 });

// Category modal
const showCatModal = ref(false);
const catForm = ref({ name: '', description: '' });

const fetchData = async () => {
    loading.value = true;
    error.value = null;
    try {
        const [itemsRes, catsRes] = await Promise.all([
            fetch('/api/erp/items'),
            fetch('/api/erp/categories'),
        ]);
        if (itemsRes.ok) items.value = await itemsRes.json();
        if (catsRes.ok) categories.value = await catsRes.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const filteredItems = () => {
    return items.value.filter(item => {
        const matchesSearch = !searchTerm.value ||
            item.reference?.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
            item.designation?.toLowerCase().includes(searchTerm.value.toLowerCase());
        const matchesCat = !filterCategory.value || item.category_id == filterCategory.value;
        return matchesSearch && matchesCat;
    });
};

const submitItem = async () => {
    submitting.value = true;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/erp/catalogue/items', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(itemForm.value),
        });
        if (!res.ok) throw new Error('Erreur');
        showItemModal.value = false;
        itemForm.value = { reference: '', designation: '', description: '', price: '', category_id: '', stock_alert: 10 };
        await fetchData();
    } catch (e) {
        alert('Erreur: ' + e.message);
    } finally {
        submitting.value = false;
    }
};

const submitCategory = async () => {
    submitting.value = true;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/erp/catalogue/categories', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(catForm.value),
        });
        if (!res.ok) throw new Error('Erreur');
        showCatModal.value = false;
        catForm.value = { name: '', description: '' };
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
    <GelLayout page-title="Catalogue">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="input-group input-group-sm" style="max-width:250px;">
                    <span class="input-group-text bg-white"><i class="bi-search"></i></span>
                    <input v-model="searchTerm" class="form-control" placeholder="Rechercher...">
                </div>
                <select v-model="filterCategory" class="form-select form-select-sm" style="width:auto;">
                    <option value="">Toutes catégories</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm" @click="showCatModal = true">
                    <i class="bi-tag me-1"></i>Catégories
                </button>
                <button class="btn btn-primary btn-sm" @click="showItemModal = true">
                    <i class="bi-plus-lg me-1"></i>Nouvel article
                </button>
            </div>
        </div>

        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <div v-else class="card card-dashboard">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="small text-muted">
                        <tr><th>Réf.</th><th>Désignation</th><th>Catégorie</th><th class="text-end">Prix</th><th>Alerte stock</th></tr>
                    </thead>
                    <tbody>
                        <tr v-if="!filteredItems().length">
                            <td colspan="5" class="text-center py-4 text-muted">Aucun article trouvé.</td>
                        </tr>
                        <tr v-for="item in filteredItems()" :key="item.id">
                            <td class="fw-medium">{{ item.reference }}</td>
                            <td>{{ item.designation }} <span v-if="item.description" class="small text-muted">- {{ item.description }}</span></td>
                            <td><span class="badge badge-eden">{{ item.category?.name || '-' }}</span></td>
                            <td class="text-end">{{ item.price ? $formatCurrency(item.price) : '-' }}</td>
                            <td><span class="badge" :class="(item.stock_alert || 0) > 0 ? 'bg-warning' : 'bg-secondary'">{{ item.stock_alert || 0 }}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Item Modal -->
        <div v-if="showItemModal" class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header"><h6 class="fw-bold">Nouvel article</h6><button class="btn-close" @click="showItemModal = false"></button></div>
                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small">Référence *</label>
                                <input v-model="itemForm.reference" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Catégorie</label>
                                <select v-model="itemForm.category_id" class="form-select form-select-sm">
                                    <option value="">Sélectionner</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Désignation *</label>
                                <input v-model="itemForm.designation" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Description</label>
                                <textarea v-model="itemForm.description" class="form-control form-control-sm" rows="2"></textarea>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Prix (FCFA)</label>
                                <input v-model="itemForm.price" type="number" step="0.01" class="form-control form-control-sm">
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Seuil d'alerte</label>
                                <input v-model="itemForm.stock_alert" type="number" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-secondary" @click="showItemModal = false">Annuler</button>
                        <button class="btn btn-sm btn-primary" :disabled="submitting" @click="submitItem">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>Créer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Modal -->
        <div v-if="showCatModal" class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header"><h6 class="fw-bold">Nouvelle catégorie</h6><button class="btn-close" @click="showCatModal = false"></button></div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label small">Nom *</label>
                            <input v-model="catForm.name" class="form-control form-control-sm" required>
                        </div>
                        <div>
                            <label class="form-label small">Description</label>
                            <textarea v-model="catForm.description" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-secondary" @click="showCatModal = false">Annuler</button>
                        <button class="btn btn-sm btn-primary" :disabled="submitting" @click="submitCategory">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>Créer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>
