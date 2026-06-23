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
const viewMode = ref('list');

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
    let i = 0, s = bytes;
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
    try { folders.value = await apiGet(`${apiBase}/folders`); }
    catch (e) { console.error('Erreur dossiers:', e); folders.value = []; }
    finally { loading.value.folders = false; }
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
    } catch (e) { console.error('Erreur documents:', e); documents.value = []; }
    finally { loading.value.documents = false; }
}

async function loadStats() {
    loading.value.stats = true;
    try { stats.value = await apiGet(`${apiBase}/stats`); }
    catch (e) { console.error('Erreur stats:', e); }
    finally { loading.value.stats = false; }
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
        await loadFolders(); await loadStats();
    } catch (e) { alert('Erreur lors de la création'); }
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
        renameTarget.value = null;
        await loadFolders();
    } catch (e) { alert('Erreur lors du renommage'); }
}

async function deleteFolder(id) {
    try {
        await apiDelete(`${apiBase}/folders/${id}`);
        await loadFolders(); await loadStats();
    } catch (e) { alert('Impossible de supprimer (non vide)'); }
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
            } else { alert(err.message || "Erreur upload"); }
            return;
        }
        showUpload.value = false;
        uploadForm.value = { file: null, folder_id: null, description: '' };
        await loadDocuments(currentFolder.value?.id || null);
        await loadStats();
    } catch (e) { alert("Erreur lors de l'upload"); }
    finally { loading.value.upload = false; }
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
    } catch (e) { alert("Erreur lors de l'archivage"); }
}

function confirmDelete(doc) {
    deleteTarget.value = doc;
    showDeleteConfirm.value = true;
}

async function deleteDocument() {
    if (!deleteTarget.value) return;
    try {
        await apiDelete(`${apiBase}/documents/${deleteTarget.value.id}`);
        showDeleteConfirm.value = false;
        deleteTarget.value = null;
        await loadDocuments(currentFolder.value?.id || null);
        await loadStats();
    } catch (e) { alert('Erreur lors de la suppression'); }
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
        pdf: 'badge-pdf', doc: 'badge-doc', docx: 'badge-doc',
        xls: 'badge-xls', xlsx: 'badge-xls',
        png: 'badge-img', jpg: 'badge-img', jpeg: 'badge-img',
    };
    return c[type] || 'badge-other';
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
        <div class="ged-container">
            <!-- ══ HEADER ══ -->
            <div class="ged-header">
                <div>
                    <h1 class="ged-title"><i class="bi-folder2-open me-2 ged-title-icon"></i>Gestion documentaire</h1>
                    <p class="ged-subtitle">Documents, dossiers et archivage</p>
                </div>
            </div>

            <!-- ══ STATS CARDS ══ -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="ged-stat">
                        <div class="ged-stat-icon ged-stat-blue"><i class="bi-file-earmark-text"></i></div>
                        <div class="ged-stat-body">
                            <div class="ged-stat-label">Documents</div>
                            <div class="ged-stat-value">{{ stats.total_documents }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="ged-stat">
                        <div class="ged-stat-icon ged-stat-orange"><i class="bi-folder2"></i></div>
                        <div class="ged-stat-body">
                            <div class="ged-stat-label">Dossiers</div>
                            <div class="ged-stat-value">{{ stats.total_folders }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="ged-stat">
                        <div class="ged-stat-icon ged-stat-green"><i class="bi-database"></i></div>
                        <div class="ged-stat-body">
                            <div class="ged-stat-label">Stockage</div>
                            <div class="ged-stat-value">{{ totalSizeFormatted }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="ged-stat">
                        <div class="ged-stat-icon ged-stat-cyan"><i class="bi-arrow-up-circle"></i></div>
                        <div class="ged-stat-body">
                            <div class="ged-stat-label">Dernier ajout</div>
                            <div class="ged-stat-value text-truncate" style="font-size:13px;">{{ stats.recent?.[0]?.name || '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ MAIN LAYOUT ══ -->
            <div class="ged-layout">
                <!-- LEFT: Folder tree -->
                <div class="ged-sidebar">
                    <div class="ged-sidebar-header">
                        <span class="ged-sidebar-title"><i class="bi-folder2 me-2" style="color:#FF7900;"></i>Dossiers</span>
                        <div class="ged-sidebar-actions">
                            <button class="ged-btn-icon" @click="loadFolders()" title="Rafraîchir"><i class="bi-arrow-clockwise"></i></button>
                            <button class="ged-btn-icon" @click="openCreateFolder(null)" title="Nouveau dossier"><i class="bi-plus-lg"></i></button>
                        </div>
                    </div>
                    <div class="ged-folder-list">
                        <div class="ged-folder-item" :class="{ active: !currentFolder }" @click="goToRoot()">
                            <i class="bi-house-door-fill ged-folder-icon-home"></i>
                            <span class="ged-folder-name">Tous les documents</span>
                            <span class="ged-folder-badge">{{ documents.length }}</span>
                        </div>
                        <div v-if="loading.folders" class="ged-loading-center"><div class="ged-spinner"></div></div>
                        <div v-else-if="!folders.length" class="ged-empty-folder">
                            <i class="bi-folder-plus"></i>
                            <p>Aucun dossier</p>
                            <button class="ged-btn ged-btn-sm ged-btn-primary" @click="openCreateFolder(null)">Créer</button>
                        </div>
                        <div v-else>
                            <div v-for="folder in folders" :key="folder.id">
                                <div class="ged-folder-item" :class="{ active: currentFolder?.id === folder.id }"
                                     @click="selectFolder(folder)"
                                     @contextmenu.prevent="openRenameFolder(folder)">
                                    <i class="bi-chevron-right ged-folder-chevron" v-if="folder.has_children"></i>
                                    <i class="bi-folder2-fill ged-folder-icon"></i>
                                    <span class="ged-folder-name text-truncate">{{ folder.name }}</span>
                                    <span class="ged-folder-badge" v-if="folder.documents_count">{{ folder.documents_count }}</span>
                                </div>
                                <div v-if="folder.children?.length" class="ged-folder-children">
                                    <div v-for="child in folder.children" :key="child.id"
                                         class="ged-folder-item" :class="{ active: currentFolder?.id === child.id }"
                                         @click="selectFolder(child)"
                                         @contextmenu.prevent="openRenameFolder(child)">
                                        <i class="bi-folder2-fill ged-folder-icon" style="font-size:14px;margin-left:16px;"></i>
                                        <span class="ged-folder-name text-truncate">{{ child.name }}</span>
                                        <span class="ged-folder-badge" v-if="child.documents_count">{{ child.documents_count }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Documents area -->
                <div class="ged-main">
                    <!-- Toolbar -->
                    <div class="ged-toolbar">
                        <nav class="ged-breadcrumb">
                            <span class="ged-bcrumb-item" :class="{ 'ged-bcrumb-active': !currentFolder }" @click="goToRoot()">
                                <i class="bi-house-door me-1"></i> Racine
                            </span>
                            <i class="bi-chevron-right ged-bcrumb-sep"></i>
                            <span v-if="currentFolder" class="ged-bcrumb-item ged-bcrumb-active">{{ currentFolder.name }}</span>
                        </nav>
                        <div class="ged-toolbar-actions">
                            <button class="ged-btn ged-btn-sm ged-btn-outline" @click="openCreateFolder(currentFolder?.id || null)">
                                <i class="bi-folder-plus me-1"></i> Dossier
                            </button>
                            <button class="ged-btn ged-btn-sm ged-btn-primary" @click="openUpload(null)">
                                <i class="bi-upload me-1"></i> Upload
                            </button>
                        </div>
                    </div>

                    <!-- Search & Filters -->
                    <div class="ged-toolbar ged-toolbar-secondary">
                        <div class="ged-toolbar-group">
                            <div class="ged-search">
                                <i class="bi-search ged-search-icon"></i>
                                <input type="text" placeholder="Rechercher..." v-model="searchQuery"
                                       @input="loadDocuments(currentFolder?.id || null)">
                            </div>
                            <select class="ged-select" v-model="fileTypeFilter" @change="loadDocuments(currentFolder?.id || null)">
                                <option value="">Tous les types</option>
                                <option v-for="t in fileTypes" :key="t" :value="t">{{ t.toUpperCase() }}</option>
                            </select>
                        </div>
                        <div class="ged-view-toggle">
                            <button class="ged-view-btn" :class="{ active: viewMode === 'list' }" @click="viewMode = 'list'" title="Liste">
                                <i class="bi-list-ul"></i>
                            </button>
                            <button class="ged-view-btn" :class="{ active: viewMode === 'grid' }" @click="viewMode = 'grid'" title="Grille">
                                <i class="bi-grid-3x3-gap"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Loading -->
                    <div v-if="loading.documents" class="ged-loading-lg">
                        <div class="ged-spinner-lg"></div>
                    </div>

                    <!-- Empty -->
                    <div v-else-if="!filteredDocuments.length" class="ged-empty">
                        <i class="bi-file-earmark-plus"></i>
                        <h4>{{ searchQuery ? 'Aucun résultat' : 'Aucun document' }}</h4>
                        <p>{{ searchQuery ? `Aucun résultat pour "${searchQuery}"` : 'Commencez par uploader un document.' }}</p>
                        <button v-if="!searchQuery" class="ged-btn ged-btn-primary" @click="openUpload(null)">
                            <i class="bi-upload me-2"></i>Uploader
                        </button>
                    </div>

                    <!-- List View -->
                    <div v-else-if="viewMode === 'list'" class="ged-table-card">
                        <table class="ged-table">
                            <thead>
                                <tr>
                                    <th style="width:40px;"></th>
                                    <th>Nom</th>
                                    <th style="width:80px;">Type</th>
                                    <th style="width:90px;">Taille</th>
                                    <th style="width:60px;">Version</th>
                                    <th style="width:110px;">Date</th>
                                    <th style="width:120px;" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="doc in filteredDocuments" :key="doc.id"
                                    :class="{ 'ged-row-active': selectedDoc?.id === doc.id }"
                                    @dblclick="showPreviewInfo(doc)">
                                    <td>
                                        <i :class="getFileIcon(doc.file_type)" class="ged-file-icon"></i>
                                    </td>
                                    <td>
                                        <div class="ged-doc-name">{{ doc.name }}</div>
                                        <div v-if="doc.description" class="ged-doc-desc">{{ doc.description }}</div>
                                    </td>
                                    <td><span class="ged-badge" :class="fileBadge(doc.file_type)">{{ doc.file_type || '?' }}</span></td>
                                    <td class="ged-doc-meta">{{ formatBytes(doc.file_size) }}</td>
                                    <td><span class="ged-version">v{{ doc.version }}</span></td>
                                    <td class="ged-doc-meta">{{ formatDate(doc.created_at) }}</td>
                                    <td>
                                        <div class="ged-action-group">
                                            <button class="ged-btn-icon-sm" @click.stop="downloadFile(doc)" title="Télécharger"><i class="bi-download"></i></button>
                                            <button class="ged-btn-icon-sm" @click.stop="showPreviewInfo(doc)" title="Infos"><i class="bi-info-circle"></i></button>
                                            <button class="ged-btn-icon-sm ged-btn-icon-danger" @click.stop="confirmDelete(doc)" title="Supprimer"><i class="bi-trash3"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="ged-table-footer">
                            {{ filteredDocuments.length }} document(s)
                        </div>
                    </div>

                    <!-- Grid View -->
                    <div v-else class="row g-3">
                        <div v-for="doc in filteredDocuments" :key="doc.id" class="col-6 col-sm-4 col-md-3 col-xl-2">
                            <div class="ged-file-card" @click="showPreviewInfo(doc)">
                                <div class="ged-file-card-header">
                                    <i :class="getFileIcon(doc.file_type)"></i>
                                </div>
                                <div class="ged-file-card-body">
                                    <div class="ged-file-card-name text-truncate">{{ doc.name }}</div>
                                    <div class="ged-file-card-meta">
                                        <span class="ged-badge" :class="fileBadge(doc.file_type)">{{ doc.file_type }}</span>
                                        <span class="ged-version">v{{ doc.version }}</span>
                                    </div>
                                </div>
                                <div class="ged-file-card-actions">
                                    <button class="ged-btn-icon-sm" @click.stop="downloadFile(doc)" title="Télécharger"><i class="bi-download"></i></button>
                                    <button class="ged-btn-icon-sm ged-btn-icon-danger" @click.stop="confirmDelete(doc)" title="Supprimer"><i class="bi-trash3"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══ MODALS ═══ -->

            <!-- Create Folder Modal -->
            <div v-if="showCreateFolder" class="ged-modal-overlay" @click.self="showCreateFolder = false">
                <div class="ged-modal ged-modal-sm">
                    <div class="ged-modal-header">
                        <span><i class="bi-folder-plus me-2" style="color:#FF7900;"></i>Nouveau dossier</span>
                        <button class="ged-modal-close" @click="showCreateFolder = false">&times;</button>
                    </div>
                    <div class="ged-modal-body">
                        <label class="ged-label">Nom du dossier</label>
                        <input id="folder-name-input" type="text" class="ged-input"
                               v-model="newFolder.name" @keyup.enter="createFolder" placeholder="Ex: Factures 2026">
                        <div v-if="currentFolder" class="ged-folder-context mt-2">
                            <i class="bi-folder me-1"></i> Dans : <strong>{{ currentFolder.name }}</strong>
                        </div>
                    </div>
                    <div class="ged-modal-footer">
                        <button class="ged-btn ged-btn-sm ged-btn-ghost" @click="showCreateFolder = false">Annuler</button>
                        <button class="ged-btn ged-btn-sm ged-btn-primary" @click="createFolder" :disabled="!newFolder.name.trim()">
                            <i class="bi-check-lg me-1"></i> Créer
                        </button>
                    </div>
                </div>
            </div>

            <!-- Rename Folder Modal -->
            <div v-if="showRenameFolder" class="ged-modal-overlay" @click.self="showRenameFolder = false">
                <div class="ged-modal ged-modal-sm">
                    <div class="ged-modal-header">
                        <span><i class="bi-pencil me-2" style="color:#FF7900;"></i>Renommer</span>
                        <button class="ged-modal-close" @click="showRenameFolder = false">&times;</button>
                    </div>
                    <div class="ged-modal-body">
                        <input type="text" class="ged-input" v-model="renameForm.name" @keyup.enter="renameFolder">
                    </div>
                    <div class="ged-modal-footer">
                        <button class="ged-btn ged-btn-sm ged-btn-ghost" @click="showRenameFolder = false">Annuler</button>
                        <button class="ged-btn ged-btn-sm ged-btn-primary" @click="renameFolder" :disabled="!renameForm.name.trim()">
                            <i class="bi-check-lg me-1"></i> Renommer
                        </button>
                    </div>
                </div>
            </div>

            <!-- Upload Modal -->
            <div v-if="showUpload" class="ged-modal-overlay" @click.self="showUpload = false">
                <div class="ged-modal">
                    <div class="ged-modal-header">
                        <span><i class="bi-upload me-2" style="color:#FF7900;"></i>Uploader un document</span>
                        <button class="ged-modal-close" @click="showUpload = false">&times;</button>
                    </div>
                    <div class="ged-modal-body">
                        <div class="mb-3">
                            <label class="ged-label">Fichier</label>
                            <div class="ged-dropzone"
                                 @dragover.prevent="e => e.currentTarget.classList.add('ged-dropzone-active')"
                                 @dragleave.prevent="e => e.currentTarget.classList.remove('ged-dropzone-active')"
                                 @drop.prevent="e => { e.currentTarget.classList.remove('ged-dropzone-active'); uploadForm.file = e.dataTransfer.files[0]; }">
                                <i class="bi-cloud-arrow-up ged-dropzone-icon"></i>
                                <p class="ged-dropzone-text">Glissez-déposez ou</p>
                                <input type="file" id="file-upload-input" class="d-none" @change="e => uploadForm.file = e.target.files[0]">
                                <button class="ged-btn ged-btn-sm ged-btn-outline" type="button" @click="document.getElementById('file-upload-input').click()">
                                    <i class="bi-folder2-open me-1"></i> Parcourir
                                </button>
                                <div v-if="uploadForm.file" class="ged-file-selected">
                                    <i class="bi-check-circle-fill me-1" style="color:#10b981;"></i> {{ uploadForm.file.name }}
                                    <span class="ged-doc-meta">({{ formatBytes(uploadForm.file.size) }})</span>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="ged-label">Description</label>
                            <textarea class="ged-input ged-textarea" rows="2" v-model="uploadForm.description" placeholder="Décrivez brièvement ce document..."></textarea>
                        </div>
                        <div v-if="currentFolder" class="ged-folder-context">
                            <i class="bi-folder me-1"></i> Destination : <strong>{{ currentFolder.name }}</strong>
                        </div>
                    </div>
                    <div class="ged-modal-footer">
                        <button class="ged-btn ged-btn-sm ged-btn-ghost" @click="showUpload = false">Annuler</button>
                        <button class="ged-btn ged-btn-sm ged-btn-primary" @click="uploadFile" :disabled="!uploadForm.file || loading.upload">
                            <span v-if="loading.upload" class="ged-spinner-sm me-1"></span>
                            <i v-else class="bi-cloud-arrow-up me-1"></i>
                            Uploader
                        </button>
                    </div>
                </div>
            </div>

            <!-- Preview Modal -->
            <div v-if="showPreview && selectedDoc" class="ged-modal-overlay" @click.self="showPreview = false">
                <div class="ged-modal ged-modal-lg">
                    <div class="ged-modal-header">
                        <span class="text-truncate"><i :class="getFileIcon(selectedDoc.file_type)" class="me-2"></i>{{ selectedDoc.name }}</span>
                        <button class="ged-modal-close" @click="showPreview = false">&times;</button>
                    </div>
                    <div class="ged-modal-body">
                        <div class="row g-4">
                            <div class="col-md-7">
                                <div class="ged-preview-box">
                                    <i :class="getFileIcon(selectedDoc.file_type)" class="ged-preview-icon"></i>
                                    <p class="ged-preview-text">
                                        <template v-if="['jpg','jpeg','png','gif','webp','svg'].includes(selectedDoc.file_type)">
                                            <i class="bi-image me-1"></i> Aperçu image
                                        </template>
                                        <template v-else-if="selectedDoc.file_type === 'pdf'">
                                            <i class="bi-filetype-pdf me-1"></i> Document PDF
                                        </template>
                                        <template v-else>
                                            <i class="bi-file-earmark me-1"></i> Aperçu non disponible
                                        </template>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="ged-info-card">
                                    <div class="ged-info-title"><i class="bi-info-circle me-1"></i> Informations</div>
                                    <dl class="ged-info-grid">
                                        <dt>Type</dt>
                                        <dd><span class="ged-badge" :class="fileBadge(selectedDoc.file_type)">{{ selectedDoc.file_type || '—' }}</span></dd>
                                        <dt>Taille</dt>
                                        <dd>{{ formatBytes(selectedDoc.file_size) }}</dd>
                                        <dt>Version</dt>
                                        <dd><span class="ged-version">v{{ selectedDoc.version }}</span></dd>
                                        <dt>Uploadé par</dt>
                                        <dd>{{ selectedDoc.uploaded_by || '—' }}</dd>
                                        <dt>Date</dt>
                                        <dd>{{ formatDate(selectedDoc.created_at) }}</dd>
                                        <dt>Dossier</dt>
                                        <dd class="text-truncate">{{ selectedDoc.folder_name || 'Racine' }}</dd>
                                    </dl>
                                    <div v-if="selectedDoc.description" class="ged-info-desc">
                                        <strong>Description</strong>
                                        <p>{{ selectedDoc.description }}</p>
                                    </div>
                                </div>
                                <div class="d-grid gap-2 mt-3">
                                    <button class="ged-btn ged-btn-primary w-100" @click="downloadFile(selectedDoc)">
                                        <i class="bi-download me-2"></i> Télécharger
                                    </button>
                                    <div class="d-flex gap-2">
                                        <button class="ged-btn ged-btn-sm ged-btn-outline flex-grow-1" @click="toggleArchive(selectedDoc)">
                                            <i :class="selectedDoc.is_archived ? 'bi-arrow-counterclockwise' : 'bi-archive'"></i>
                                            {{ selectedDoc.is_archived ? ' Restaurer' : ' Archiver' }}
                                        </button>
                                        <button class="ged-btn ged-btn-sm ged-btn-danger flex-grow-1" @click="confirmDelete(selectedDoc); showPreview = false;">
                                            <i class="bi-trash3"></i> Supprimer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete Confirm Modal -->
            <div v-if="showDeleteConfirm" class="ged-modal-overlay" @click.self="showDeleteConfirm = false">
                <div class="ged-modal ged-modal-sm">
                    <div class="ged-modal-header ged-modal-header-danger">
                        <span><i class="bi-exclamation-triangle me-2"></i>Confirmer</span>
                        <button class="ged-modal-close" @click="showDeleteConfirm = false">&times;</button>
                    </div>
                    <div class="ged-modal-body text-center py-4" v-if="deleteTarget">
                        <i class="bi-trash3 ged-delete-icon"></i>
                        <p class="mb-1">Supprimer <strong>{{ deleteTarget.name }}</strong> ?</p>
                        <p class="ged-doc-meta">Le fichier sera placé dans la corbeille.</p>
                    </div>
                    <div class="ged-modal-footer justify-content-center">
                        <button class="ged-btn ged-btn-sm ged-btn-ghost px-4" @click="showDeleteConfirm = false">Annuler</button>
                        <button class="ged-btn ged-btn-sm ged-btn-danger px-4" @click="deleteDocument"><i class="bi-trash3 me-1"></i> Supprimer</button>
                    </div>
                </div>
            </div>
        </div>
    </CompanyLayout>
</template>

<style scoped>
/* ── Variables ── */
.ged-container {
    max-width: 1400px;
    margin: 0 auto;
    font-family: 'Inter', -apple-system, sans-serif;
}

/* ── Header ── */
.ged-header {
    margin-bottom: 24px;
}
.ged-title {
    font-size: 22px;
    font-weight: 700;
    color: #111;
    margin: 0 0 4px;
    display: flex;
    align-items: center;
}
.ged-title-icon { color: #FF7900; }
.ged-subtitle { font-size: 14px; color: #6b7280; margin: 0; }

/* ── Stats Cards ── */
.ged-stat {
    display: flex;
    align-items: center;
    gap: 14px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px 20px;
    transition: box-shadow 0.2s;
}
.ged-stat:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
.ged-stat-icon {
    width: 44px; height: 44px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; flex-shrink: 0;
}
.ged-stat-blue { background: #eff6ff; color: #2563eb; }
.ged-stat-orange { background: #fff7ed; color: #ea580c; }
.ged-stat-green { background: #ecfdf5; color: #10b981; }
.ged-stat-cyan { background: #ecfeff; color: #0891b2; }
.ged-stat-body { min-width: 0; }
.ged-stat-label { font-size: 12px; color: #6b7280; font-weight: 500; margin-bottom: 2px; }
.ged-stat-value { font-size: 20px; font-weight: 700; color: #111; }

/* ── Main Layout ── */
.ged-layout {
    display: flex;
    gap: 20px;
    align-items: flex-start;
}
.ged-sidebar {
    width: 260px;
    flex-shrink: 0;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
}
.ged-main {
    flex: 1;
    min-width: 0;
}

/* ── Sidebar ── */
.ged-sidebar-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 16px;
    border-bottom: 1px solid #f3f4f6;
}
.ged-sidebar-title { font-size: 13px; font-weight: 600; color: #374151; }
.ged-sidebar-actions { display: flex; gap: 2px; }
.ged-btn-icon {
    width: 28px; height: 28px;
    display: flex; align-items: center; justify-content: center;
    border: none; background: transparent; border-radius: 6px;
    color: #6b7280; cursor: pointer; font-size: 14px;
    transition: all 0.15s;
}
.ged-btn-icon:hover { background: #f3f4f6; color: #111; }

.ged-folder-list {
    max-height: calc(100vh - 480px);
    overflow-y: auto;
    padding: 6px 0;
}
.ged-folder-list::-webkit-scrollbar { width: 4px; }
.ged-folder-list::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }

.ged-folder-item {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 16px; cursor: pointer;
    font-size: 13px; color: #374151;
    border-left: 3px solid transparent;
    transition: all 0.12s;
}
.ged-folder-item:hover { background: #f9fafb; }
.ged-folder-item.active {
    background: #fff7ed;
    border-left-color: #FF7900;
    color: #c2410c;
}
.ged-folder-icon-home { color: #2563eb; font-size: 14px; width: 16px; }
.ged-folder-icon { color: #f59e0b; font-size: 16px; width: 16px; }
.ged-folder-chevron { font-size: 10px; color: #9ca3af; width: 12px; }
.ged-folder-name { flex: 1; font-size: 12px; }
.ged-folder-badge {
    font-size: 10px; font-weight: 600;
    color: #6b7280; background: #f3f4f6;
    padding: 0 6px; border-radius: 4px; line-height: 18px;
}
.ged-folder-children {
    border-left: 2px solid #f3f4f6; margin-left: 16px;
}
.ged-loading-center { padding: 24px; text-align: center; }
.ged-empty-folder { text-align: center; padding: 24px 16px; color: #9ca3af; }
.ged-empty-folder i { font-size: 28px; display: block; margin-bottom: 8px; }
.ged-empty-folder p { font-size: 12px; margin-bottom: 10px; }
.ged-folder-context {
    display: flex; align-items: center; gap: 10px;
    background: #f9fafb; border: 1px solid #f3f4f6;
    border-radius: 8px; padding: 10px 14px;
    font-size: 12px; color: #374151;
}

/* ── Toolbar ── */
.ged-toolbar {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    margin-bottom: 12px; flex-wrap: wrap;
}
.ged-toolbar-secondary {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 10px;
    padding: 10px 16px; margin-bottom: 16px;
}
.ged-toolbar-group { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.ged-toolbar-actions { display: flex; align-items: center; gap: 8px; }

.ged-breadcrumb { display: flex; align-items: center; gap: 6px; }
.ged-bcrumb-item { font-size: 13px; color: #6b7280; cursor: pointer; transition: color 0.12s; }
.ged-bcrumb-item:hover { color: #111; }
.ged-bcrumb-active { color: #111; font-weight: 600; }
.ged-bcrumb-sep { font-size: 10px; color: #d1d5db; }

.ged-search {
    display: flex; align-items: center; gap: 8px;
    background: #fff; border: 1px solid #e5e7eb; border-radius: 8px;
    padding: 6px 12px; transition: border-color 0.15s;
}
.ged-search:focus-within { border-color: #FF7900; box-shadow: 0 0 0 3px rgba(255,121,0,0.1); }
.ged-search-icon { color: #9ca3af; font-size: 14px; }
.ged-search input { border: none; outline: none; font-size: 13px; padding: 2px 0; width: 180px; background: transparent; }

.ged-select {
    padding: 6px 10px; font-size: 12px; border: 1px solid #e5e7eb;
    border-radius: 8px; background: #fff; color: #374151;
    outline: none; cursor: pointer;
}
.ged-select:focus { border-color: #FF7900; }

.ged-view-toggle {
    display: flex; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;
}
.ged-view-btn {
    padding: 6px 12px; border: none; background: #fff; color: #6b7280;
    font-size: 14px; cursor: pointer; transition: all 0.12s;
}
.ged-view-btn.active { background: #FF7900; color: #fff; }
.ged-view-btn:hover:not(.active) { background: #f3f4f6; }

/* ── Table ── */
.ged-table-card {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;
}
.ged-table { width: 100%; border-collapse: collapse; }
.ged-table th {
    font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px;
    padding: 10px 14px; background: #f9fafb; border-bottom: 1px solid #e5e7eb;
}
.ged-table td { padding: 10px 14px; border-bottom: 1px solid #f3f4f6; font-size: 13px; color: #374151; }
.ged-table tbody tr:hover { background: #f9fafb; }
.ged-table tbody tr:last-child td { border-bottom: none; }
.ged-row-active { background: #fff7ed !important; }
.ged-file-icon { font-size: 20px; vertical-align: middle; }
.ged-doc-name { font-weight: 500; color: #111; max-width: 280px; overflow: hidden; text-overflow: ellipsis; }
.ged-doc-desc { font-size: 11px; color: #9ca3af; max-width: 280px; overflow: hidden; text-overflow: ellipsis; }
.ged-doc-meta { font-size: 12px; color: #6b7280; }

.ged-table-footer {
    background: #f9fafb; border-top: 1px solid #e5e7eb;
    padding: 8px 16px; font-size: 12px; color: #6b7280; text-align: right;
}

/* ── Badges ── */
.ged-badge {
    display: inline-block; font-size: 10px; font-weight: 600;
    padding: 2px 8px; border-radius: 4px; text-transform: uppercase;
}
.badge-pdf { background: #fef2f2; color: #dc2626; }
.badge-doc { background: #eff6ff; color: #2563eb; }
.badge-xls { background: #f0fdf4; color: #16a34a; }
.badge-img { background: #ecfeff; color: #0891b2; }
.badge-other { background: #f3f4f6; color: #6b7280; }

.ged-version {
    display: inline-block; font-size: 10px; font-weight: 600;
    padding: 0 6px; border-radius: 4px;
    background: #f3f4f6; color: #6b7280; line-height: 20px;
}

/* ── Action Group ── */
.ged-action-group { display: flex; gap: 4px; justify-content: flex-end; }
.ged-btn-icon-sm {
    width: 30px; height: 30px;
    display: inline-flex; align-items: center; justify-content: center;
    border: none; background: transparent; border-radius: 6px;
    color: #6b7280; cursor: pointer; font-size: 14px;
    transition: all 0.12s;
}
.ged-btn-icon-sm:hover { background: #f3f4f6; color: #111; }
.ged-btn-icon-danger:hover { background: #fef2f2; color: #dc2626; }

/* ── File Cards (Grid View) ── */
.ged-file-card {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
    cursor: pointer; transition: all 0.2s; overflow: hidden;
}
.ged-file-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    border-color: #d1d5db; transform: translateY(-2px);
}
.ged-file-card-header {
    padding: 20px 12px 8px; text-align: center;
}
.ged-file-card-header i { font-size: 36px; }
.ged-file-card-body { padding: 0 12px 8px; text-align: center; }
.ged-file-card-name { font-size: 12px; font-weight: 500; color: #111; }
.ged-file-card-meta { display: flex; justify-content: center; gap: 4px; margin-top: 4px; align-items: center; }
.ged-file-card-actions {
    display: flex; justify-content: center; gap: 4px;
    padding: 6px; border-top: 1px solid #f3f4f6; background: #fafafa;
}

/* ── Empty State ── */
.ged-empty { text-align: center; padding: 60px 20px; color: #6b7280; }
.ged-empty i { font-size: 48px; color: #d1d5db; display: block; margin-bottom: 12px; }
.ged-empty h4 { font-size: 16px; font-weight: 600; margin: 0 0 4px; color: #111; }
.ged-empty p { font-size: 13px; margin: 0 0 16px; }

/* ── Loading ── */
.ged-loading-lg { text-align: center; padding: 60px 20px; }
.ged-spinner { width: 24px; height: 24px; border: 3px solid #e5e7eb; border-top-color: #FF7900; border-radius: 50%; animation: spin 0.6s linear infinite; display: inline-block; }
.ged-spinner-lg { width: 36px; height: 36px; border: 3px solid #e5e7eb; border-top-color: #FF7900; border-radius: 50%; animation: spin 0.6s linear infinite; display: inline-block; }
.ged-spinner-sm { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: spin 0.6s linear infinite; display: inline-block; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Buttons ── */
.ged-btn {
    display: inline-flex; align-items: center; justify-content: center;
    border: none; border-radius: 8px; font-weight: 600;
    cursor: pointer; transition: all 0.15s; text-decoration: none;
    font-size: 13px; padding: 8px 16px;
}
.ged-btn-sm { font-size: 12px; padding: 6px 14px; }
.ged-btn-primary { background: #111; color: #fff; }
.ged-btn-primary:hover { background: #000; }
.ged-btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
.ged-btn-outline { background: #fff; border: 1px solid #e5e7eb; color: #374151; }
.ged-btn-outline:hover { border-color: #d1d5db; background: #f9fafb; }
.ged-btn-ghost { background: transparent; color: #6b7280; }
.ged-btn-ghost:hover { background: #f3f4f6; color: #111; }
.ged-btn-danger { background: #dc2626; color: #fff; }
.ged-btn-danger:hover { background: #b91c1c; }

/* ── Dropzone ── */
.ged-dropzone {
    border: 2px dashed #d1d5db; border-radius: 10px;
    padding: 28px 16px; text-align: center; cursor: pointer;
    transition: all 0.15s;
}
.ged-dropzone:hover, .ged-dropzone-active { border-color: #FF7900; background: rgba(255,121,0,0.03); }
.ged-dropzone-icon { font-size: 32px; color: #FF7900; display: block; margin-bottom: 8px; }
.ged-dropzone-text { font-size: 13px; color: #6b7280; margin: 0 0 10px; }
.ged-file-selected { margin-top: 10px; font-size: 12px; color: #111; }

/* ── Modals ── */
.ged-modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.5);
    display: flex; align-items: center; justify-content: center;
    z-index: 1000; padding: 20px;
}
.ged-modal {
    background: #fff; border-radius: 16px; width: 100%;
    max-width: 520px; max-height: 90vh; overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}
.ged-modal-sm { max-width: 400px; }
.ged-modal-lg { max-width: 760px; }
.ged-modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px; border-bottom: 1px solid #f3f4f6;
    font-size: 14px; font-weight: 600; color: #111;
}
.ged-modal-header-danger { color: #dc2626; }
.ged-modal-close {
    border: none; background: none; font-size: 24px;
    color: #9ca3af; cursor: pointer; line-height: 1; padding: 0;
}
.ged-modal-close:hover { color: #111; }
.ged-modal-body { padding: 20px; }
.ged-modal-footer {
    display: flex; align-items: center; justify-content: flex-end; gap: 8px;
    padding: 14px 20px; border-top: 1px solid #f3f4f6;
}

/* ── Form Elements ── */
.ged-label { font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; display: block; }
.ged-input {
    width: 100%; padding: 8px 12px; font-size: 13px;
    border: 1px solid #e5e7eb; border-radius: 8px; outline: none;
    transition: border-color 0.15s; box-sizing: border-box;
}
.ged-input:focus { border-color: #FF7900; box-shadow: 0 0 0 3px rgba(255,121,0,0.1); }
.ged-textarea { resize: vertical; min-height: 60px; font-family: inherit; }

/* ── Preview ── */
.ged-preview-box {
    background: #f9fafb; border: 1px solid #e5e7eb;
    border-radius: 12px; padding: 40px 20px;
    text-align: center; min-height: 200px;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
}
.ged-preview-icon { font-size: 64px; margin-bottom: 12px; }
.ged-preview-text { font-size: 13px; color: #6b7280; margin: 0; }

.ged-info-card {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px;
}
.ged-info-title { font-size: 14px; font-weight: 600; color: #111; margin-bottom: 12px; }
.ged-info-grid {
    display: grid; grid-template-columns: auto 1fr; gap: 6px 12px; font-size: 12px; margin: 0;
}
.ged-info-grid dt { color: #6b7280; font-weight: 400; }
.ged-info-grid dd { margin: 0; color: #111; }
.ged-info-desc { margin-top: 12px; padding-top: 12px; border-top: 1px solid #f3f4f6; }
.ged-info-desc strong { font-size: 12px; color: #374151; }
.ged-info-desc p { font-size: 12px; color: #6b7280; margin: 4px 0 0; }

/* ── Delete Icon ── */
.ged-delete-icon { font-size: 48px; color: #dc2626; display: block; margin-bottom: 12px; }

/* ── Responsive ── */
@media (max-width: 768px) {
    .ged-sidebar { display: none; }
    .ged-layout { flex-direction: column; }
}
</style>
