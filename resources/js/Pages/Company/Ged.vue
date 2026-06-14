<script setup>
import { ref, computed, onMounted } from 'vue';
import CompanyLayout from '../../Layouts/CompanyLayout.vue';
import { authStore } from '../../stores/auth';

// ─── État ───────────────────────────────────────────────────────
const folders = ref([]);
const documents = ref([]);
const currentFolder = ref(null);
const breadcrumbs = ref([{ id: null, name: 'Racine' }]);
const selectedDoc = ref(null);
const viewMode = ref('list'); // 'list' | 'grid'

// Modals
const showCreateFolder = ref(false);
const showUpload = ref(false);
const showPreview = ref(false);
const showDeleteConfirm = ref(false);
const showRenameFolder = ref(false);
const deleteTarget = ref(null);
const renameTarget = ref(null);
const renameForm = ref({ name: '' });
const newFolder = ref({ name: '', parent_id: null });
const uploadForm = ref({ file: null, folder_id: null, description: '' });
const searchQuery = ref('');
const fileTypeFilter = ref('');

// Stats
const stats = ref({ total_documents: 0, total_folders: 0, total_size: 0, by_type: [], recent: [] });

// Loading
const loading = ref({ folders: true, documents: false, upload: false, stats: false });

// Toast
const toast = ref({ show: false, message: '', type: 'success' });
let toastTimer = null;

function showToast(message, type = 'success') {
    toast.value = { show: true, message, type };
    if (toastTimer) clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { toast.value.show = false; }, 3500);
}

// ─── Computed ───────────────────────────────────────────────────
const filteredDocuments = computed(() => {
    let list = documents.value;
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        list = list.filter(d => d.name.toLowerCase().includes(q) || (d.description || '').toLowerCase().includes(q));
    }
    if (fileTypeFilter.value) {
        list = list.filter(d => d.file_type === fileTypeFilter.value);
    }
    return list;
});

const fileTypes = computed(() => {
    const types = new Set();
    documents.value.forEach(d => { if (d.file_type) types.add(d.file_type); });
    return [...types].sort();
});

const totalSizeFormatted = computed(() => {
    const bytes = stats.value.total_size || documents.value.reduce((sum, d) => sum + (d.file_size || 0), 0);
    if (!bytes) return '0 o';
    const u = ['o', 'Ko', 'Mo', 'Go'];
    let i = 0;
    let s = bytes;
    while (s >= 1024 && i < 3) { s /= 1024; i++; }
    return s.toFixed(1) + ' ' + u[i];
});

// ─── API ────────────────────────────────────────────────────────
const apiBase = '/api/company/ged';
const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
const h = { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' };

async function apiGet(url) {
    const r = await fetch(url, { headers: h });
    if (!r.ok) throw new Error(`API ${r.status}`);
    return r.json();
}
async function apiPost(url, d) {
    const r = await fetch(url, { method: 'POST', headers: { ...h, 'Content-Type': 'application/json' }, body: JSON.stringify(d) });
    if (!r.ok) throw new Error(`API ${r.status}`);
    return r.json();
}
async function apiPut(url, d) {
    const r = await fetch(url, { method: 'PUT', headers: { ...h, 'Content-Type': 'application/json' }, body: JSON.stringify(d) });
    if (!r.ok) throw new Error(`API ${r.status}`);
    return r.json();
}
async function apiDelete(url) {
    const r = await fetch(url, { method: 'DELETE', headers: h });
    if (!r.ok) throw new Error(`API ${r.status}`);
    return r.json();
}
async function apiPatch(url) {
    const r = await fetch(url, { method: 'PATCH', headers: h });
    if (!r.ok) throw new Error(`API ${r.status}`);
    return r.json();
}

// ─── Chargement ─────────────────────────────────────────────────
async function loadFolders() {
    loading.value.folders = true;
    try {
        folders.value = await apiGet(`${apiBase}/folders`);
    } catch (e) {
        console.error('Erreur dossiers:', e);
        folders.value = [];
    } finally {
        loading.value.folders = false;
    }
}

async function loadDocuments(folderId = null) {
    loading.value.documents = true;
    try {
        const p = new URLSearchParams();
        if (folderId) p.set('folder_id', folderId);
        if (searchQuery.value) p.set('search', searchQuery.value);
        if (fileTypeFilter.value) p.set('file_type', fileTypeFilter.value);
        const data = await apiGet(`${apiBase}/documents?${p}`);
        documents.value = data.data || data;
    } catch (e) {
        console.error('Erreur documents:', e);
        documents.value = [];
    } finally {
        loading.value.documents = false;
    }
}

async function loadStats() {
    loading.value.stats = true;
    try {
        stats.value = await apiGet(`${apiBase}/stats`);
    } catch (e) {
        console.error('Erreur stats:', e);
    } finally {
        loading.value.stats = false;
    }
}

// ─── Actions Dossiers ───────────────────────────────────────────
function openCreateFolder(parentId = null) {
    newFolder.value = { name: '', parent_id: parentId };
    showCreateFolder.value = true;
    setTimeout(() => document.getElementById('folder-name-input')?.focus(), 100);
}

async function createFolder() {
    if (!newFolder.value.name.trim()) return;
    try {
        await apiPost(`${apiBase}/folders`, newFolder.value);
        showCreateFolder.value = false;
        newFolder.value = { name: '', parent_id: null };
        await loadFolders();
        await loadStats();
        showToast('Dossier créé avec succès');
    } catch (e) {
        showToast('Erreur lors de la création', 'danger');
    }
}

function openRenameFolder(folder) {
    renameTarget.value = folder;
    renameForm.value = { name: folder.name };
    showRenameFolder.value = true;
}

async function renameFolder() {
    if (!renameForm.value.name.trim() || !renameTarget.value) return;
    try {
        await apiPut(`${apiBase}/folders/${renameTarget.value.id}`, { name: renameForm.value.name });
        showRenameFolder.value = false;
        const tid = renameTarget.value.id;
        renameTarget.value = null;
        await loadFolders();
        if (currentFolder.value?.id === tid) currentFolder.value.name = renameForm.value.name;
        showToast('Dossier renommé');
    } catch (e) {
        showToast('Erreur lors du renommage', 'danger');
    }
}

async function deleteFolder(id) {
    try {
        await apiDelete(`${apiBase}/folders/${id}`);
        await loadFolders();
        await loadStats();
        showToast('Dossier supprimé');
    } catch (e) {
        showToast('Impossible de supprimer (non vide)', 'warning');
    }
}

function selectFolder(folder) {
    currentFolder.value = folder;
    breadcrumbs.value = [{ id: null, name: 'Racine' }, ...(folder ? [{ id: folder.id, name: folder.name }] : [])];
    loadDocuments(folder?.id || null);
}

function goToRoot() {
    currentFolder.value = null;
    breadcrumbs.value = [{ id: null, name: 'Racine' }];
    loadDocuments(null);
}

// ─── Actions Documents ──────────────────────────────────────────
function openUpload(folderId = null) {
    uploadForm.value = { file: null, folder_id: folderId || currentFolder.value?.id || null, description: '' };
    showUpload.value = true;
}

async function uploadFile() {
    if (!uploadForm.value.file) return;
    loading.value.upload = true;
    try {
        const fd = new FormData();
        fd.append('file', uploadForm.value.file);
        fd.append('folder_id', uploadForm.value.folder_id || '');
        fd.append('description', uploadForm.value.description);

        const r = await fetch(`${apiBase}/documents/upload`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: fd,
        });
        if (!r.ok) {
            const err = await r.json();
            if (r.status === 409 && err.existing) {
                if (!confirm('Ce fichier existe déjà. Voulez-vous l\'ouvrir ?')) return;
                showPreviewInfo(err.existing);
            } else {
                showToast(err.message || 'Erreur upload', 'danger');
            }
            return;
        }
        showUpload.value = false;
        uploadForm.value = { file: null, folder_id: null, description: '' };
        await loadDocuments(currentFolder.value?.id || null);
        await loadStats();
        showToast('Fichier uploadé avec succès');
    } catch (e) {
        showToast("Erreur lors de l'upload", 'danger');
    } finally {
        loading.value.upload = false;
    }
}

function downloadFile(doc) {
    window.open(`${apiBase}/documents/${doc.id}/download`, '_blank');
}

function showPreviewInfo(doc) {
    selectedDoc.value = doc;
    showPreview.value = true;
}

async function toggleArchive(doc) {
    try {
        await apiPatch(`${apiBase}/documents/${doc.id}/archive`);
        await loadDocuments(currentFolder.value?.id || null);
        showToast(doc.is_archived ? 'Document restauré' : 'Document archivé');
    } catch (e) {
        showToast("Erreur lors de l'archivage", 'danger');
    }
}

function confirmDelete(doc) {
    deleteTarget.value = doc;
    showDeleteConfirm.value = true;
}

async function deleteDocument() {
    if (!deleteTarget.value) return;
    try {
        await apiDelete(`${apiBase}/documents/${deleteTarget.value.id}`);
        const deletedId = deleteTarget.value.id;
        showDeleteConfirm.value = false;
        deleteTarget.value = null;
        if (selectedDoc.value?.id === deletedId) { selectedDoc.value = null; showPreview.value = false; }
        await loadDocuments(currentFolder.value?.id || null);
        await loadStats();
        showToast('Document supprimé');
    } catch (e) {
        showToast('Erreur lors de la suppression', 'danger');
    }
}

// ─── Helpers ────────────────────────────────────────────────────
function getFileIcon(type) {
    const icons = {
        pdf: 'bi-filetype-pdf text-danger', doc: 'bi-filetype-docx text-primary', docx: 'bi-filetype-docx text-primary',
        xls: 'bi-filetype-xlsx text-success', xlsx: 'bi-filetype-xlsx text-success',
        ppt: 'bi-filetype-pptx text-warning', pptx: 'bi-filetype-pptx text-warning',
        png: 'bi-filetype-png text-info', jpg: 'bi-filetype-jpg text-info', jpeg: 'bi-filetype-jpg text-info',
        gif: 'bi-filetype-gif text-warning', svg: 'bi-filetype-svg text-primary',
        mp4: 'bi-file-play text-danger', avi: 'bi-file-play text-danger', mov: 'bi-file-play text-danger',
        mp3: 'bi-file-music text-secondary', wav: 'bi-file-music text-secondary',
        zip: 'bi-file-zip text-secondary', rar: 'bi-file-zip text-secondary', '7z': 'bi-file-zip text-secondary',
        txt: 'bi-file-text text-secondary', json: 'bi-filetype-json text-warning', csv: 'bi-filetype-csv text-success',
        html: 'bi-filetype-html text-primary', css: 'bi-filetype-css text-info', js: 'bi-filetype-js text-warning',
    };
    return icons[type] || 'bi-file-earmark text-secondary';
}

function formatDate(d) {
    if (!d) return '—';
    try { return new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' }); }
    catch { return d; }
}

function formatBytes(bytes) {
    if (!bytes) return '—';
    const u = ['o', 'Ko', 'Mo', 'Go']; let i = 0, s = bytes;
    while (s >= 1024 && i < 3) { s /= 1024; i++; }
    return s.toFixed(1) + ' ' + u[i];
}

function fileBadge(type) {
    const c = {
        pdf: 'bg-danger bg-opacity-10 text-danger', doc: 'bg-primary bg-opacity-10 text-primary',
        docx: 'bg-primary bg-opacity-10 text-primary', xls: 'bg-success bg-opacity-10 text-success',
        xlsx: 'bg-success bg-opacity-10 text-success', png: 'bg-info bg-opacity-10 text-info',
        jpg: 'bg-info bg-opacity-10 text-info', jpeg: 'bg-info bg-opacity-10 text-info',
    };
    return c[type] || 'bg-secondary bg-opacity-10 text-secondary';
}

// ─── Init ───────────────────────────────────────────────────────
onMounted(() => {
    loadFolders();
    loadDocuments();
    loadStats();
});
</script>

<template>
    <CompanyLayout page-title="GED — Gestion Documentaire">
        <div class="ged-page">

            <!-- Toast -->
            <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
                <div v-if="toast.show" class="toast show align-items-center border-0 text-white"
                     :class="'bg-' + (toast.type === 'danger' ? 'danger' : toast.type === 'warning' ? 'warning text-dark' : 'success')"
                     role="alert">
                    <div class="d-flex">
                        <div class="toast-body small fw-medium">
                            <i class="bi me-2" :class="toast.type === 'danger' ? 'bi-exclamation-circle' : toast.type === 'warning' ? 'bi-exclamation-triangle' : 'bi-check-circle'"></i>
                            {{ toast.message }}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" @click="toast.show = false"></button>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card card-dashboard border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi-file-earmark-text"></i></div>
                            <div>
                                <div class="text-muted small fw-medium">Documents</div>
                                <div class="h4 mb-0 fw-bold">{{ stats.total_documents }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-dashboard border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi-folder2"></i></div>
                            <div>
                                <div class="text-muted small fw-medium">Dossiers</div>
                                <div class="h4 mb-0 fw-bold">{{ stats.total_folders }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-dashboard border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi-database"></i></div>
                            <div>
                                <div class="text-muted small fw-medium">Stockage</div>
                                <div class="h4 mb-0 fw-bold">{{ totalSizeFormatted }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-dashboard border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi-arrow-up-circle"></i></div>
                            <div>
                                <div class="text-muted small fw-medium">Dernier upload</div>
                                <div class="small fw-semibold text-truncate">{{ stats.recent?.[0]?.name || '—' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main row: Folder tree + Documents -->
            <div class="row g-3">

                <!-- LEFT: Folder tree -->
                <div class="col-lg-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi-folder2 text-warning"></i>
                                <span class="fw-semibold font-heading small">Dossiers</span>
                            </div>
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-outline-primary border-0" @click="loadFolders()" title="Rafraîchir"><i class="bi-arrow-clockwise"></i></button>
                                <button class="btn btn-sm btn-primary" @click="openCreateFolder(null)" title="Nouveau dossier"><i class="bi-plus-lg"></i></button>
                            </div>
                        </div>
                        <div class="card-body p-0 folder-scroll">
                            <!-- Root entry -->
                            <div class="folder-row d-flex align-items-center gap-2 px-3 py-2 border-bottom"
                                 :class="{ active: !currentFolder }" @click="goToRoot()">
                                <i class="bi-house-door-fill text-primary"></i>
                                <span class="small fw-medium flex-grow-1">Tous les documents</span>
                                <span class="badge bg-light text-dark rounded-pill small">{{ documents.length }}</span>
                            </div>
                            <div v-if="loading.folders" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>
                            <div v-else-if="!folders.length" class="text-center py-5 px-3">
                                <i class="bi-folder-plus d-block mb-2 text-muted" style="font-size: 2rem;"></i>
                                <p class="small text-muted mb-2">Aucun dossier</p>
                                <button class="btn btn-sm btn-outline-primary" @click="openCreateFolder(null)"><i class="bi-plus-lg me-1"></i>Créer</button>
                            </div>
                            <div v-else class="py-1">
                                <template v-for="folder in folders" :key="folder.id">
                                    <div class="folder-row d-flex align-items-center gap-1 px-2 py-1 rounded-2 mx-1"
                                         :class="{ active: currentFolder?.id === folder.id }"
                                         @click="selectFolder(folder)">
                                        <i class="bi-chevron-right text-muted" style="font-size: 10px;" v-if="folder.has_children"></i>
                                        <i style="font-size: 10px; visibility: hidden;" v-else></i>
                                        <i class="bi-folder2-fill text-warning" style="font-size: 16px;"></i>
                                        <span class="small flex-grow-1 text-truncate">{{ folder.name }}</span>
                                        <span class="badge bg-light text-dark rounded-pill small" v-if="folder.documents_count">{{ folder.documents_count }}</span>
                                    </div>
                                    <div v-if="folder.children?.length" class="ms-3 border-start ps-1">
                                        <div v-for="child in folder.children" :key="child.id"
                                             class="folder-row d-flex align-items-center gap-1 px-2 py-1 rounded-2"
                                             :class="{ active: currentFolder?.id === child.id }"
                                             @click="selectFolder(child)">
                                            <i class="bi-chevron-right text-muted" style="font-size: 10px;" v-if="child.has_children"></i>
                                            <i style="font-size: 10px; visibility: hidden;" v-else></i>
                                            <i class="bi-folder2-fill text-warning" style="font-size: 14px;"></i>
                                            <span class="small flex-grow-1 text-truncate">{{ child.name }}</span>
                                            <span class="badge bg-light text-dark rounded-pill small" v-if="child.documents_count">{{ child.documents_count }}</span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Documents -->
                <div class="col-lg-9">
                    <!-- Toolbar -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body py-2 px-3">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <nav aria-label="breadcrumb" class="flex-grow-1">
                                    <ol class="breadcrumb mb-0 small">
                                        <li class="breadcrumb-item" :class="{ active: !currentFolder }"
                                            style="cursor:pointer" @click="goToRoot()">
                                            <i class="bi-house-door me-1"></i> Racine
                                        </li>
                                        <li v-if="currentFolder" class="breadcrumb-item active">{{ currentFolder.name }}</li>
                                    </ol>
                                </nav>
                                <button class="btn btn-sm btn-outline-primary" @click="openCreateFolder(currentFolder?.id || null)">
                                    <i class="bi-folder-plus me-1"></i> Dossier
                                </button>
                                <button class="btn btn-sm btn-primary" @click="openUpload(null)">
                                    <i class="bi-upload me-1"></i> Upload
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Search & Filters -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body py-2 px-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="input-group input-group-sm flex-grow-1" style="max-width: 300px;">
                                    <span class="input-group-text bg-white border-end-0"><i class="bi-search text-muted"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0"
                                           placeholder="Rechercher..." v-model="searchQuery"
                                           @input="loadDocuments(currentFolder?.id || null)">
                                </div>
                                <select class="form-select form-select-sm" style="max-width: 130px;" v-model="fileTypeFilter"
                                        @change="loadDocuments(currentFolder?.id || null)">
                                    <option value="">Tous</option>
                                    <option v-for="t in fileTypes" :key="t" :value="t">{{ t.toUpperCase() }}</option>
                                </select>
                                <div class="vr"></div>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-secondary" :class="{ active: viewMode === 'list' }" @click="viewMode = 'list'" title="Liste"><i class="bi-list-ul"></i></button>
                                    <button class="btn btn-outline-secondary" :class="{ active: viewMode === 'grid' }" @click="viewMode = 'grid'" title="Grille"><i class="bi-grid-3x3-gap"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loading -->
                    <div v-if="loading.documents" class="text-center py-5">
                        <div class="spinner-border text-primary" style="width:3rem;height:3rem;"></div>
                    </div>

                    <!-- Empty State -->
                    <div v-else-if="!filteredDocuments.length" class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi-file-earmark-plus text-muted d-block mb-3" style="font-size:4rem;"></i>
                            <h5 class="fw-bold text-muted mb-1">{{ searchQuery ? 'Aucun résultat' : 'Aucun document' }}</h5>
                            <p class="text-muted small mb-3">
                                {{ searchQuery ? `Aucun résultat pour "${searchQuery}"` : 'Commencez par uploader un document.' }}
                            </p>
                            <button v-if="!searchQuery" class="btn btn-primary px-4" @click="openUpload(null)">
                                <i class="bi-upload me-2"></i>Uploader
                            </button>
                        </div>
                    </div>

                    <!-- List View -->
                    <div v-else-if="viewMode === 'list'" class="card border-0 shadow-sm">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 ged-table">
                                <thead class="table-light small text-muted">
                                    <tr>
                                        <th style="width:36px;"></th>
                                        <th>Nom</th>
                                        <th style="width:70px;">Type</th>
                                        <th style="width:80px;">Taille</th>
                                        <th style="width:55px;">Ver.</th>
                                        <th style="width:100px;">Date</th>
                                        <th style="width:120px;" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="doc in filteredDocuments" :key="doc.id"
                                        :class="{ 'table-active': selectedDoc?.id === doc.id }">
                                        <td @click="showPreviewInfo(doc)" class="text-center">
                                            <i :class="getFileIcon(doc.file_type)" style="font-size:1.4rem;"></i>
                                        </td>
                                        <td @click="showPreviewInfo(doc)">
                                            <div class="fw-medium text-dark text-truncate" style="max-width:280px;">{{ doc.name }}</div>
                                            <div v-if="doc.description" class="text-muted small text-truncate" style="max-width:280px;">{{ doc.description }}</div>
                                        </td>
                                        <td @click="showPreviewInfo(doc)">
                                            <span class="badge small fw-normal" :class="fileBadge(doc.file_type)">{{ doc.file_type || '?' }}</span>
                                        </td>
                                        <td @click="showPreviewInfo(doc)" class="small text-muted">{{ formatBytes(doc.file_size) }}</td>
                                        <td @click="showPreviewInfo(doc)">
                                            <span class="badge bg-info bg-opacity-10 text-info small fw-normal">v{{ doc.version }}</span>
                                        </td>
                                        <td @click="showPreviewInfo(doc)" class="small text-muted">{{ formatDate(doc.created_at) }}</td>
                                        <td>
                                            <div class="d-flex gap-1 justify-content-end">
                                                <button class="btn btn-sm btn-light border" @click.stop="downloadFile(doc)" title="Télécharger"><i class="bi-download text-primary"></i></button>
                                                <button class="btn btn-sm btn-light border" @click.stop="showPreviewInfo(doc)" title="Détails"><i class="bi-info-circle text-info"></i></button>
                                                <button class="btn btn-sm btn-light border" @click.stop="confirmDelete(doc)" title="Supprimer"><i class="bi-trash3 text-danger"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white text-muted small px-3 py-2 text-end border-top-0">
                            {{ filteredDocuments.length }} document(s)
                        </div>
                    </div>

                    <!-- Grid View -->
                    <div v-else class="row g-3">
                        <div v-for="doc in filteredDocuments" :key="doc.id" class="col-6 col-sm-4 col-md-3 col-xl-2">
                            <div class="card border-0 shadow-sm h-100 ged-grid-card" @click="showPreviewInfo(doc)">
                                <div class="card-body text-center py-4">
                                    <i :class="getFileIcon(doc.file_type)" style="font-size:2.8rem;"></i>
                                    <div class="small fw-medium text-truncate mt-2">{{ doc.name }}</div>
                                    <div class="d-flex justify-content-center gap-1 mt-1">
                                        <span class="badge small fw-normal" :class="fileBadge(doc.file_type)">{{ doc.file_type }}</span>
                                        <span class="badge bg-info bg-opacity-10 text-info small fw-normal">v{{ doc.version }}</span>
                                    </div>
                                    <div class="text-muted small mt-1">{{ formatBytes(doc.file_size) }}</div>
                                </div>
                                <div class="card-footer bg-transparent border-top-0 pt-0 text-center pb-2">
                                    <button class="btn btn-sm btn-light border" @click.stop="downloadFile(doc)" title="Télécharger"><i class="bi-download text-primary"></i></button>
                                    <button class="btn btn-sm btn-light border" @click.stop="confirmDelete(doc)" title="Supprimer"><i class="bi-trash3 text-danger"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══ MODALS ═══ -->

            <!-- Modal: Créer dossier -->
            <div class="modal fade" :class="{ show: showCreateFolder }"
                 :style="{ display: showCreateFolder ? 'block' : 'none' }" tabindex="-1" @click.self="showCreateFolder = false">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header border-bottom text-white" style="background:var(--gel-dark);">
                            <h6 class="modal-title fw-bold font-heading"><i class="bi-folder-plus me-2"></i>Nouveau dossier</h6>
                            <button type="button" class="btn-close btn-close-white" @click="showCreateFolder = false"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label small fw-medium">Nom du dossier</label>
                                <input id="folder-name-input" type="text" class="form-control form-control-lg"
                                       v-model="newFolder.name" @keyup.enter="createFolder" placeholder="Ex: Factures 2026">
                            </div>
                            <div v-if="currentFolder" class="small text-muted bg-light rounded p-2">
                                <i class="bi-folder me-1"></i> Dans : <strong>{{ currentFolder.name }}</strong>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0">
                            <button class="btn btn-sm btn-light border" @click="showCreateFolder = false">Annuler</button>
                            <button class="btn btn-sm btn-primary px-3" @click="createFolder" :disabled="!newFolder.name.trim()"><i class="bi-check-lg me-1"></i> Créer</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal: Renommer dossier -->
            <div class="modal fade" :class="{ show: showRenameFolder }"
                 :style="{ display: showRenameFolder ? 'block' : 'none' }" tabindex="-1" @click.self="showRenameFolder = false">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header border-bottom">
                            <h6 class="modal-title fw-bold font-heading"><i class="bi-pencil me-2"></i>Renommer</h6>
                            <button type="button" class="btn-close" @click="showRenameFolder = false"></button>
                        </div>
                        <div class="modal-body">
                            <input type="text" class="form-control" v-model="renameForm.name" @keyup.enter="renameFolder">
                        </div>
                        <div class="modal-footer border-top-0">
                            <button class="btn btn-sm btn-light border" @click="showRenameFolder = false">Annuler</button>
                            <button class="btn btn-sm btn-primary px-3" @click="renameFolder" :disabled="!renameForm.name.trim()"><i class="bi-check-lg me-1"></i> Renommer</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal: Upload -->
            <div class="modal fade" :class="{ show: showUpload }"
                 :style="{ display: showUpload ? 'block' : 'none' }" tabindex="-1" @click.self="showUpload = false">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header border-bottom text-white" style="background:var(--gel-dark);">
                            <h6 class="modal-title fw-bold font-heading"><i class="bi-upload me-2"></i>Uploader un document</h6>
                            <button type="button" class="btn-close btn-close-white" @click="showUpload = false"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label small fw-medium">Fichier</label>
                                <div class="upload-zone border-2 rounded-3 p-4 text-center"
                                     @dragover.prevent="e => e.currentTarget.classList.add('border-primary')"
                                     @dragleave.prevent="e => e.currentTarget.classList.remove('border-primary')"
                                     @drop.prevent="e => { e.currentTarget.classList.remove('border-primary'); uploadForm.file = e.dataTransfer.files[0]; }">
                                    <i class="bi-cloud-arrow-up text-primary" style="font-size:2.5rem;"></i>
                                    <p class="small text-muted mb-2">Glissez-déposez ou</p>
                                    <input type="file" id="file-upload-input" class="d-none" @change="e => uploadForm.file = e.target.files[0]">
                                    <button class="btn btn-sm btn-outline-primary" @click="document.getElementById('file-upload-input').click()">
                                        <i class="bi-folder2-open me-1"></i> Parcourir
                                    </button>
                                    <div v-if="uploadForm.file" class="mt-2 small text-success">
                                        <i class="bi-check-circle me-1"></i> {{ uploadForm.file.name }}
                                        <span class="text-muted">({{ formatBytes(uploadForm.file.size) }})</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-medium">Description</label>
                                <textarea class="form-control" rows="2" v-model="uploadForm.description" placeholder="Décrivez brièvement ce document..."></textarea>
                            </div>
                            <div v-if="currentFolder" class="small text-muted bg-light rounded p-2">
                                <i class="bi-folder me-1"></i> Destination : <strong>{{ currentFolder.name }}</strong>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0">
                            <button class="btn btn-sm btn-light border" @click="showUpload = false">Annuler</button>
                            <button class="btn btn-sm btn-primary px-3" @click="uploadFile" :disabled="!uploadForm.file || loading.upload">
                                <span v-if="loading.upload" class="spinner-border spinner-border-sm me-1"></span>
                                <i class="bi-cloud-arrow-up me-1"></i> Uploader
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal: Aperçu -->
            <div class="modal fade" :class="{ show: showPreview }"
                 :style="{ display: showPreview ? 'block' : 'none' }" tabindex="-1"
                 @click.self="showPreview = false" v-if="selectedDoc">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header border-bottom text-white" style="background:var(--gel-dark);">
                            <h6 class="modal-title fw-bold font-heading text-truncate">
                                <i :class="getFileIcon(selectedDoc.file_type)" class="me-2"></i>
                                {{ selectedDoc.name }}
                            </h6>
                            <button type="button" class="btn-close btn-close-white" @click="showPreview = false"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row g-4">
                                <div class="col-md-7">
                                    <div class="rounded-3 bg-light d-flex align-items-center justify-content-center border" style="min-height:250px;">
                                        <div class="text-center p-4">
                                            <i :class="getFileIcon(selectedDoc.file_type)" style="font-size:5rem;"></i>
                                            <p class="small text-muted mt-2 mb-0">
                                                <template v-if="['jpg','jpeg','png','gif','webp','svg'].includes(selectedDoc.file_type)">Aperçu image disponible</template>
                                                <template v-else-if="selectedDoc.file_type === 'pdf'">Aperçu PDF disponible</template>
                                                <template v-else>Aperçu non disponible</template>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="card bg-light border-0">
                                        <div class="card-body">
                                            <h6 class="small fw-bold font-heading mb-3"><i class="bi-info-circle me-1"></i> Informations</h6>
                                            <dl class="row small mb-0 g-2">
                                                <dt class="col-5 text-muted">Type</dt>
                                                <dd class="col-7"><span class="badge small fw-normal" :class="fileBadge(selectedDoc.file_type)">{{ selectedDoc.file_type || '—' }}</span></dd>
                                                <dt class="col-5 text-muted">Taille</dt>
                                                <dd class="col-7">{{ formatBytes(selectedDoc.file_size) }}</dd>
                                                <dt class="col-5 text-muted">Version</dt>
                                                <dd class="col-7"><span class="badge bg-info bg-opacity-10 text-info small fw-normal">v{{ selectedDoc.version }}</span></dd>
                                                <dt class="col-5 text-muted">Uploadé par</dt>
                                                <dd class="col-7">{{ selectedDoc.uploaded_by || '—' }}</dd>
                                                <dt class="col-5 text-muted">Date</dt>
                                                <dd class="col-7">{{ formatDate(selectedDoc.created_at) }}</dd>
                                                <dt class="col-5 text-muted">Dossier</dt>
                                                <dd class="col-7 text-truncate">{{ selectedDoc.folder_name || 'Racine' }}</dd>
                                            </dl>
                                            <div v-if="selectedDoc.description" class="mt-3 pt-2 border-top">
                                                <span class="small fw-bold">Description</span>
                                                <p class="small mb-0 text-muted mt-1">{{ selectedDoc.description }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-grid gap-2 mt-3">
                                        <button class="btn btn-primary" @click="downloadFile(selectedDoc)"><i class="bi-download me-2"></i> Télécharger</button>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-outline-warning flex-grow-1" @click="toggleArchive(selectedDoc)">
                                                <i :class="selectedDoc.is_archived ? 'bi-arrow-counterclockwise' : 'bi-archive'"></i>
                                                {{ selectedDoc.is_archived ? ' Restaurer' : ' Archiver' }}
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger flex-grow-1" @click="confirmDelete(selectedDoc); showPreview = false;">
                                                <i class="bi-trash3"></i> Supprimer
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal: Confirmer suppression -->
            <div class="modal fade" :class="{ show: showDeleteConfirm }"
                 :style="{ display: showDeleteConfirm ? 'block' : 'none' }" tabindex="-1" @click.self="showDeleteConfirm = false">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header border-bottom bg-danger text-white">
                            <h6 class="modal-title fw-bold font-heading"><i class="bi-exclamation-triangle me-2"></i>Confirmer</h6>
                            <button type="button" class="btn-close btn-close-white" @click="showDeleteConfirm = false"></button>
                        </div>
                        <div class="modal-body text-center py-4" v-if="deleteTarget">
                            <i class="bi-trash3 text-danger d-block mb-3" style="font-size:3rem;"></i>
                            <p class="mb-1">Supprimer <strong>{{ deleteTarget.name }}</strong> ?</p>
                            <p class="small text-muted mb-0">Le fichier sera placé dans la corbeille.</p>
                        </div>
                        <div class="modal-footer border-top-0 justify-content-center">
                            <button class="btn btn-sm btn-light border px-4" @click="showDeleteConfirm = false">Annuler</button>
                            <button class="btn btn-sm btn-danger px-4" @click="deleteDocument"><i class="bi-trash3 me-1"></i> Supprimer</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Backdrops -->
            <div v-if="showCreateFolder || showUpload || showPreview || showDeleteConfirm || showRenameFolder"
                 class="modal-backdrop fade show"></div>
        </div>
    </CompanyLayout>
</template>

<style scoped>
.ged-page { min-height: 400px; }

.ged-table > :not(caption) > * > * { padding: 0.6rem 0.75rem; vertical-align: middle; }

.folder-row {
    transition: all 0.15s ease;
    font-size: 13px;
    cursor: pointer;
}
.folder-row:hover { background: rgba(26,35,126,0.04); }
.folder-row.active { background: rgba(26,35,126,0.08); color: var(--gel-primary); font-weight: 500; }

.ged-grid-card { transition: transform 0.15s, box-shadow 0.15s; cursor: pointer; }
.ged-grid-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important; }

.upload-zone { border-style: dashed !important; border-color: #dee2e6 !important; transition: all 0.2s; cursor: pointer; }
.upload-zone:hover { border-color: var(--gel-primary) !important; background: rgba(26,35,126,0.02); }

.folder-scroll { max-height: calc(100vh - 380px); overflow-y: auto; }
.folder-scroll::-webkit-scrollbar { width: 4px; }
.folder-scroll::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }

.modal-backdrop { opacity: 0.5; }
.toast { min-width: 280px; }
</style>
