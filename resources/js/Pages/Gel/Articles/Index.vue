<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';

// ── Data ──
const articles = ref([]);
const categories = ref([]);
const loading = ref(true);
const error = ref(null);
const submitting = ref(false);

// Filters
const search = ref('');
const filterCategory = ref('');
const filterStatus = ref('');
const debounceTimer = ref(null);

// Pagination
const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
    from: 0,
    to: 0,
});

// Modal state
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const modalInstance = ref(null);
const modalEl = ref(null);

// Form
const form = ref({
    title: '',
    slug: '',
    excerpt: '',
    content: '',
    category_id: '',
    status: 'draft',
    featured_image: '',
    meta_title: '',
    meta_description: '',
    published_at: '',
});

const statuses = ['published', 'draft', 'archived'];
const statusLabels = { published: 'Publié', draft: 'Brouillon', archived: 'Archivé' };
const statusColors = { published: 'bg-success', draft: 'bg-warning text-dark', archived: 'bg-secondary' };

// ── Fetch articles ──
const fetchArticles = async (page) => {
    loading.value = true;
    error.value = null;
    try {
        const params = new URLSearchParams();
        if (search.value) params.append('search', search.value);
        if (filterCategory.value) params.append('category_id', filterCategory.value);
        if (filterStatus.value) params.append('status', filterStatus.value);
        params.append('page', page || pagination.value.current_page);
        params.append('per_page', pagination.value.per_page);

        const res = await fetch('/api/articles?' + params.toString());
        if (!res.ok) throw new Error('Erreur lors du chargement des articles');
        const data = await res.json();

        // Support both paginated API response and flat array
        if (data.data) {
            articles.value = data.data;
            pagination.value = {
                current_page: data.current_page || 1,
                last_page: data.last_page || 1,
                per_page: data.per_page || 15,
                total: data.total || 0,
                from: data.from || 0,
                to: data.to || 0,
            };
        } else {
            articles.value = Array.isArray(data) ? data : [];
            pagination.value.current_page = 1;
            pagination.value.last_page = 1;
        }
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

// ── Fetch categories ──
const fetchCategories = async () => {
    try {
        const res = await fetch('/api/categories');
        if (res.ok) {
            const data = await res.json();
            categories.value = Array.isArray(data) ? data : (data.data || []);
        }
    } catch (e) { /* non-critique */ }
};

// ── Search with debounce ──
watch(search, () => {
    clearTimeout(debounceTimer.value);
    debounceTimer.value = setTimeout(() => fetchArticles(1), 350);
});
watch([filterCategory, filterStatus], () => fetchArticles(1));

// ── Pagination helpers ──
const goToPage = (page) => {
    if (page < 1 || page > pagination.value.last_page) return;
    fetchArticles(page);
};

const pageRange = computed(() => {
    const { current_page, last_page } = pagination.value;
    const range = [];
    const start = Math.max(1, current_page - 2);
    const end = Math.min(last_page, current_page + 2);
    for (let i = start; i <= end; i++) range.push(i);
    return range;
});

// ── Modal ──
const openCreateModal = async () => {
    resetForm();
    isEditing.value = false;
    editingId.value = null;
    showModal.value = true;
    await nextTick();
    if (!modalInstance.value && modalEl.value) {
        modalInstance.value = new bootstrap.Modal(modalEl.value);
    }
    modalInstance.value?.show();
};

const openEditModal = async (id) => {
    try {
        const res = await fetch('/api/articles/' + id);
        if (!res.ok) throw new Error('Erreur');
        const data = await res.json();

        form.value = {
            title: data.title || '',
            slug: data.slug || '',
            excerpt: data.excerpt || '',
            content: data.content || '',
            category_id: data.category_id || '',
            status: data.status || 'draft',
            featured_image: data.featured_image || '',
            meta_title: data.meta_title || '',
            meta_description: data.meta_description || '',
            published_at: data.published_at || '',
        };
        isEditing.value = true;
        editingId.value = id;
        showModal.value = true;
        await nextTick();
        if (!modalInstance.value && modalEl.value) {
            modalInstance.value = new bootstrap.Modal(modalEl.value);
        }
        modalInstance.value?.show();
    } catch (e) {
        alert('Erreur lors du chargement de l\'article : ' + e.message);
    }
};

const closeModal = () => {
    modalInstance.value?.hide();
    showModal.value = false;
};

const resetForm = () => {
    form.value = {
        title: '',
        slug: '',
        excerpt: '',
        content: '',
        category_id: '',
        status: 'draft',
        featured_image: '',
        meta_title: '',
        meta_description: '',
        published_at: '',
    };
};

const submitForm = async () => {
    submitting.value = true;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const url = isEditing.value ? '/api/articles/' + editingId.value : '/api/articles';
        const method = isEditing.value ? 'PUT' : 'POST';

        const res = await fetch(url, {
            method,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(form.value),
        });

        if (!res.ok) {
            const errData = await res.json();
            throw new Error(errData.message || Object.values(errData.errors || {}).flat().join(', '));
        }
        closeModal();
        await fetchArticles(1);
    } catch (e) {
        alert('Erreur : ' + e.message);
    } finally {
        submitting.value = false;
    }
};

const deleteArticle = async (id) => {
    if (!confirm('Confirmer la suppression de cet article ?')) return;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/api/articles/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error('Erreur lors de la suppression');
        await fetchArticles(pagination.value.current_page);
    } catch (e) {
        alert('Erreur : ' + e.message);
    }
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' });
};

const truncate = (text, len = 80) => {
    if (!text) return '';
    return text.length > len ? text.substring(0, len) + '...' : text;
};

// ── Lifecycle ──
onMounted(async () => {
    await Promise.all([fetchArticles(1), fetchCategories()]);
});
</script>

<template>
    <GelLayout page-title="Articles">
        <!-- Filters bar -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="input-group input-group-sm" style="max-width:280px;">
                    <span class="input-group-text bg-white"><i class="bi-search"></i></span>
                    <input v-model="search" type="text" class="form-control" placeholder="Titre, contenu, auteur...">
                </div>
                <select v-model="filterCategory" class="form-select form-select-sm" style="width:auto;">
                    <option value="">Toutes catégories</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
                <select v-model="filterStatus" class="form-select form-select-sm" style="width:auto;">
                    <option value="">Tous statuts</option>
                    <option v-for="s in statuses" :key="s" :value="s">{{ statusLabels[s] }}</option>
                </select>
            </div>
            <button class="btn btn-primary btn-sm" @click="openCreateModal">
                <i class="bi-plus-lg me-1"></i>Nouvel article
            </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <!-- Table -->
        <div v-else class="bg-white rounded-lg shadow p-6">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="small text-muted">
                        <tr>
                            <th>Titre</th>
                            <th>Catégorie</th>
                            <th>Auteur</th>
                            <th>Statut</th>
                            <th>Publié le</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!articles.length">
                            <td colspan="6" class="text-center py-4 text-muted">Aucun article trouvé.</td>
                        </tr>
                        <tr v-for="article in articles" :key="article.id">
                            <td>
                                <span class="fw-medium">{{ article.title }}</span>
                                <div v-if="article.excerpt" class="small text-muted">{{ truncate(article.excerpt, 60) }}</div>
                            </td>
                            <td>
                                <span v-if="article.category_name" class="badge" style="background:#e8eaf6;color:#1a237e;">
                                    {{ article.category_name }}
                                </span>
                                <span v-else class="text-muted small">-</span>
                            </td>
                            <td class="small">{{ article.author_name || '-' }}</td>
                            <td>
                                <span class="badge" :class="statusColors[article.status] || 'bg-secondary'">
                                    {{ statusLabels[article.status] || article.status }}
                                </span>
                            </td>
                            <td class="small">{{ formatDate(article.published_at) }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary me-1" title="Modifier" @click="openEditModal(article.id)">
                                    <i class="bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" title="Supprimer" @click="deleteArticle(article.id)">
                                    <i class="bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1" class="d-flex flex-wrap align-items-center justify-content-between px-3 py-3 border-top">
                <small class="text-muted">
                    Affichage de {{ pagination.from }} à {{ pagination.to }} sur {{ pagination.total }} articles
                </small>
                <nav aria-label="Navigation des pages">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item" :class="{ disabled: pagination.current_page <= 1 }">
                            <button class="page-link" @click="goToPage(pagination.current_page - 1)" :disabled="pagination.current_page <= 1">
                                <i class="bi-chevron-left"></i>
                            </button>
                        </li>
                        <li v-for="page in pageRange" :key="page" class="page-item" :class="{ active: page === pagination.current_page }">
                            <button class="page-link" @click="goToPage(page)">{{ page }}</button>
                        </li>
                        <li class="page-item" :class="{ disabled: pagination.current_page >= pagination.last_page }">
                            <button class="page-link" @click="goToPage(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.last_page">
                                <i class="bi-chevron-right"></i>
                            </button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div ref="modalEl" class="modal fade" tabindex="-1" @hidden.self="showModal = false">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">{{ isEditing ? "Modifier l'article" : 'Nouvel article' }}</h5>
                        <button type="button" class="btn-close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submitForm">
                            <div class="row g-3">
                                <div class="col-12">
                                    <h6 class="fw-bold text-primary border-bottom pb-2">Informations générales</h6>
                                </div>

                                <div class="col-md-8">
                                    <label class="form-label small">Titre *</label>
                                    <input v-model="form.title" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Slug</label>
                                    <input v-model="form.slug" class="form-control form-control-sm" placeholder="titre-de-l-article">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small">Catégorie</label>
                                    <select v-model="form.category_id" class="form-select form-select-sm">
                                        <option value="">Sélectionner</option>
                                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Statut</label>
                                    <select v-model="form.status" class="form-select form-select-sm">
                                        <option v-for="s in statuses" :key="s" :value="s">{{ statusLabels[s] }}</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Date de publication</label>
                                    <input v-model="form.published_at" type="date" class="form-control form-control-sm">
                                </div>

                                <div class="col-12">
                                    <label class="form-label small">Extrait / Résumé</label>
                                    <textarea v-model="form.excerpt" class="form-control form-control-sm" rows="2" maxlength="300"></textarea>
                                </div>

                                <div class="col-12">
                                    <label class="form-label small">Contenu *</label>
                                    <textarea v-model="form.content" class="form-control form-control-sm" rows="8" required></textarea>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small">Image à la une (URL)</label>
                                    <input v-model="form.featured_image" class="form-control form-control-sm" placeholder="https://...">
                                </div>

                                <div class="col-12 mt-3">
                                    <h6 class="fw-bold text-primary border-bottom pb-2">SEO (optionnel)</h6>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Meta title</label>
                                    <input v-model="form.meta_title" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Meta description</label>
                                    <input v-model="form.meta_description" class="form-control form-control-sm">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" @click="closeModal">Annuler</button>
                        <button type="button" class="btn btn-sm btn-primary" :disabled="submitting" @click="submitForm">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                            {{ isEditing ? "Mettre à jour" : 'Créer l\'article' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>
