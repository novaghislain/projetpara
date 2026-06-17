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
        pdf: 'isup-file-pdf', doc: 'isup-file-doc', docx: 'isup-file-doc',
        xls: 'isup-file-xls', xlsx: 'isup-file-xls',
        png: 'isup-file-img', jpg: 'isup-file-img', jpeg: 'isup-file-img',
    };
    return c[type] || 'isup-file-other';
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
        <div class="isup-shell ged-shell">
            <!-- ══ HEADER ══ -->
            <div class="isup-portal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="isup-portal-logo">
                        <i class="bi-folder2-open" style="font-size:20px;"></i>
                    </div>
                    <div>
                        <div class="isup-portal-company">GED — Gestion Documentaire</div>
                        <div class="isup-portal-sub">Documents, dossiers et archivage</div>
                    </div>
                </div>
            </div>

            <div class="p-3">
                <!-- ══ STATS CARDS ══ -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="isup-stat-card">
                            <div class="isup-stat-icon isup-stat-blue"><i class="bi-file-earmark-text"></i></div>
                            <div>
                                <div class="isup-stat-label">Documents</div>
                                <div class="isup-stat-value">{{ stats.total_documents }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="isup-stat-card">
                            <div class="isup-stat-icon isup-stat-orange"><i class="bi-folder2"></i></div>
                            <div>
                                <div class="isup-stat-label">Dossiers</div>
                                <div class="isup-stat-value">{{ stats.total_folders }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="isup-stat-card">
                            <div class="isup-stat-icon isup-stat-cyan"><i class="bi-database"></i></div>
                            <div>
                                <div class="isup-stat-label">Stockage</div>
                                <div class="isup-stat-value">{{ totalSizeFormatted }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="isup-stat-card">
                            <div class="isup-stat-icon isup-stat-green"><i class="bi-arrow-up-circle"></i></div>
                            <div>
                                <div class="isup-stat-label">Dernier upload</div>
                                <div class="isup-stat-subvalue text-truncate">{{ stats.recent?.[0]?.name || '—' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══ MAIN LAYOUT ══ -->
                <div class="row g-3">
                    <!-- LEFT: Folder tree -->
                    <div class="col-lg-3">
                        <div class="isup-panel">
                            <div class="isup-panel-header d-flex align-items-center justify-content-between">
                                <span><i class="bi-folder2 me-2" style="color:#FF7900;"></i>Dossiers</span>
                                <div class="d-flex gap-1">
                                    <button class="isup-panel-icon-btn" @click="loadFolders()" title="Rafraîchir"><i class="bi-arrow-clockwise"></i></button>
                                    <button class="isup-panel-icon-btn" @click="openCreateFolder(null)" title="Nouveau dossier"><i class="bi-plus-lg"></i></button>
                                </div>
                            </div>
                            <div class="isup-folder-scroll">
                                <!-- Root -->
                                <div class="isup-folder-row" :class="{ active: !currentFolder }" @click="goToRoot()">
                                    <i class="bi-house-door-fill isup-folder-icon-home"></i>
                                    <span class="isup-folder-name">Tous les documents</span>
                                    <span class="isup-folder-count">{{ documents.length }}</span>
                                </div>
                                <div v-if="loading.folders" class="text-center py-4"><div class="isup-spinner-sm"></div></div>
                                <div v-else-if="!folders.length" class="text-center py-5 px-3">
                                    <i class="bi-folder-plus d-block mb-2" style="font-size:2rem;color:#dce3ee;"></i>
                                    <p class="small" style="color:#888;">Aucun dossier</p>
                                    <button class="isup-btn-primary" style="padding:4px 12px;font-size:11px;" @click="openCreateFolder(null)">
                                        <i class="bi-plus-lg me-1"></i>Créer
                                    </button>
                                </div>
                                <div v-else class="py-1">
                                    <template v-for="folder in folders" :key="folder.id">
                                        <div class="isup-folder-row" :class="{ active: currentFolder?.id === folder.id }"
                                             @click="selectFolder(folder)">
                                            <i class="bi-chevron-right" style="font-size:10px;color:#aaa;width:12px;" v-if="folder.has_children"></i>
                                            <i style="font-size:10px;width:12px;" v-else></i>
                                            <i class="bi-folder2-fill isup-folder-icon"></i>
                                            <span class="isup-folder-name text-truncate">{{ folder.name }}</span>
                                            <span class="isup-folder-count" v-if="folder.documents_count">{{ folder.documents_count }}</span>
                                        </div>
                                        <div v-if="folder.children?.length" class="ms-3 border-start ps-1" style="border-color:#dce3ee !important;">
                                            <div v-for="child in folder.children" :key="child.id"
                                                 class="isup-folder-row" :class="{ active: currentFolder?.id === child.id }"
                                                 @click="selectFolder(child)">
                                                <i class="bi-chevron-right" style="font-size:10px;color:#aaa;width:12px;" v-if="child.has_children"></i>
                                                <i style="font-size:10px;width:12px;" v-else></i>
                                                <i class="bi-folder2-fill isup-folder-icon" style="font-size:14px;"></i>
                                                <span class="isup-folder-name text-truncate">{{ child.name }}</span>
                                                <span class="isup-folder-count" v-if="child.documents_count">{{ child.documents_count }}</span>
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
                        <div class="isup-ged-toolbar">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <nav class="isup-breadcrumb">
                                    <span class="isup-bcrumb-item" :class="{ active: !currentFolder }" @click="goToRoot()">
                                        <i class="bi-house-door me-1"></i> Racine
                                    </span>
                                    <i class="bi-chevron-right isup-bcrumb-sep"></i>
                                    <span v-if="currentFolder" class="isup-bcrumb-item active">{{ currentFolder.name }}</span>
                                </nav>
                                <div class="d-flex gap-1 ms-auto">
                                    <button class="isup-btn-outline" @click="openCreateFolder(currentFolder?.id || null)">
                                        <i class="bi-folder-plus me-1"></i> Dossier
                                    </button>
                                    <button class="isup-btn-primary" @click="openUpload(null)">
                                        <i class="bi-upload me-1"></i> Upload
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Search & Filters -->
                        <div class="isup-ged-toolbar">
                            <div class="d-flex align-items-center gap-2">
                                <div class="isup-search-box">
                                    <i class="bi-search"></i>
                                    <input type="text" placeholder="Rechercher..." v-model="searchQuery"
                                           @input="loadDocuments(currentFolder?.id || null)">
                                </div>
                                <select class="isup-select" style="max-width:130px;" v-model="fileTypeFilter"
                                        @change="loadDocuments(currentFolder?.id || null)">
                                    <option value="">Tous</option>
                                    <option v-for="t in fileTypes" :key="t" :value="t">{{ t.toUpperCase() }}</option>
                                </select>
                                <div class="isup-ged-view-toggle">
                                    <button class="isup-view-btn" :class="{ active: viewMode === 'list' }" @click="viewMode = 'list'" title="Liste"><i class="bi-list-ul"></i></button>
                                    <button class="isup-view-btn" :class="{ active: viewMode === 'grid' }" @click="viewMode = 'grid'" title="Grille"><i class="bi-grid-3x3-gap"></i></button>
                                </div>
                            </div>
                        </div>

                        <!-- Loading -->
                        <div v-if="loading.documents" class="text-center py-5">
                            <div class="isup-spinner"></div>
                        </div>

                        <!-- Empty -->
                        <div v-else-if="!filteredDocuments.length" class="isup-ged-empty">
                            <i class="bi-file-earmark-plus"></i>
                            <h5>{{ searchQuery ? 'Aucun résultat' : 'Aucun document' }}</h5>
                            <p>{{ searchQuery ? `Aucun résultat pour "${searchQuery}"` : 'Commencez par uploader un document.' }}</p>
                            <button v-if="!searchQuery" class="isup-btn-primary" @click="openUpload(null)">
                                <i class="bi-upload me-2"></i>Uploader
                            </button>
                        </div>

                        <!-- List View -->
                        <div v-else-if="viewMode === 'list'" class="isup-panel">
                            <div class="isup-table-wrap">
                                <table class="isup-table w-100">
                                    <thead>
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
                                            :class="{ 'isup-row-active': selectedDoc?.id === doc.id }">
                                            <td @click="showPreviewInfo(doc)" class="text-center">
                                                <i :class="getFileIcon(doc.file_type)" style="font-size:1.4rem;"></i>
                                            </td>
                                            <td @click="showPreviewInfo(doc)">
                                                <div class="isup-doc-name">{{ doc.name }}</div>
                                                <div v-if="doc.description" class="isup-doc-desc">{{ doc.description }}</div>
                                            </td>
                                            <td @click="showPreviewInfo(doc)"><span class="isup-file-badge" :class="fileBadge(doc.file_type)">{{ doc.file_type || '?' }}</span></td>
                                            <td @click="showPreviewInfo(doc)" class="isup-doc-meta">{{ formatBytes(doc.file_size) }}</td>
                                            <td @click="showPreviewInfo(doc)"><span class="isup-version-badge">v{{ doc.version }}</span></td>
                                            <td @click="showPreviewInfo(doc)" class="isup-doc-meta">{{ formatDate(doc.created_at) }}</td>
                                            <td>
                                                <div class="d-flex gap-1 justify-content-end">
                                                    <button class="isup-icon-btn" @click.stop="downloadFile(doc)" title="Télécharger"><i class="bi-download" style="color:#1565c0;"></i></button>
                                                    <button class="isup-icon-btn" @click.stop="showPreviewInfo(doc)" title="Détails"><i class="bi-info-circle" style="color:#00838f;"></i></button>
                                                    <button class="isup-icon-btn isup-icon-danger" @click.stop="confirmDelete(doc)" title="Supprimer"><i class="bi-trash3"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="isup-panel-footer text-end">
                                {{ filteredDocuments.length }} document(s)
                            </div>
                        </div>

                        <!-- Grid View -->
                        <div v-else class="row g-3">
                            <div v-for="doc in filteredDocuments" :key="doc.id" class="col-6 col-sm-4 col-md-3 col-xl-2">
                                <div class="isup-grid-card" @click="showPreviewInfo(doc)">
                                    <div class="text-center py-3">
                                        <i :class="getFileIcon(doc.file_type)" style="font-size:2.8rem;"></i>
                                        <div class="isup-grid-name text-truncate mt-2">{{ doc.name }}</div>
                                        <div class="d-flex justify-content-center gap-1 mt-1">
                                            <span class="isup-file-badge" :class="fileBadge(doc.file_type)">{{ doc.file_type }}</span>
                                            <span class="isup-version-badge">v{{ doc.version }}</span>
                                        </div>
                                        <div class="isup-doc-meta mt-1">{{ formatBytes(doc.file_size) }}</div>
                                    </div>
                                    <div class="text-center pb-2">
                                        <button class="isup-icon-btn" @click.stop="downloadFile(doc)" title="Télécharger"><i class="bi-download" style="color:#1565c0;"></i></button>
                                        <button class="isup-icon-btn isup-icon-danger ms-1" @click.stop="confirmDelete(doc)" title="Supprimer"><i class="bi-trash3"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══ MODALS ═══ -->

            <!-- Create Folder -->
            <div v-if="showCreateFolder" class="isup-modal-overlay" @click.self="showCreateFolder = false">
                <div class="isup-modal isup-modal-sm">
                    <div class="isup-modal-header">
                        <span><i class="bi-folder-plus me-2"></i>Nouveau dossier</span>
                        <button class="isup-modal-close" @click="showCreateFolder = false">&times;</button>
                    </div>
                    <div class="isup-modal-body">
                        <label class="isup-label">Nom du dossier</label>
                        <input id="folder-name-input" type="text" class="isup-input"
                               v-model="newFolder.name" @keyup.enter="createFolder" placeholder="Ex: Factures 2026">
                        <div v-if="currentFolder" class="mt-2 isup-folder-context">
                            <i class="bi-folder me-1"></i> Dans : <strong>{{ currentFolder.name }}</strong>
                        </div>
                    </div>
                    <div class="isup-modal-footer">
                        <button class="isup-btn-grey" @click="showCreateFolder = false">Annuler</button>
                        <button class="isup-btn-primary" @click="createFolder" :disabled="!newFolder.name.trim()">
                            <i class="bi-check-lg me-1"></i> Créer
                        </button>
                    </div>
                </div>
            </div>

            <!-- Rename Folder -->
            <div v-if="showRenameFolder" class="isup-modal-overlay" @click.self="showRenameFolder = false">
                <div class="isup-modal isup-modal-sm">
                    <div class="isup-modal-header">
                        <span><i class="bi-pencil me-2"></i>Renommer</span>
                        <button class="isup-modal-close" @click="showRenameFolder = false">&times;</button>
                    </div>
                    <div class="isup-modal-body">
                        <input type="text" class="isup-input" v-model="renameForm.name" @keyup.enter="renameFolder">
                    </div>
                    <div class="isup-modal-footer">
                        <button class="isup-btn-grey" @click="showRenameFolder = false">Annuler</button>
                        <button class="isup-btn-primary" @click="renameFolder" :disabled="!renameForm.name.trim()">
                            <i class="bi-check-lg me-1"></i> Renommer
                        </button>
                    </div>
                </div>
            </div>

            <!-- Upload -->
            <div v-if="showUpload" class="isup-modal-overlay" @click.self="showUpload = false">
                <div class="isup-modal">
                    <div class="isup-modal-header">
                        <span><i class="bi-upload me-2"></i>Uploader un document</span>
                        <button class="isup-modal-close" @click="showUpload = false">&times;</button>
                    </div>
                    <div class="isup-modal-body">
                        <div class="mb-3">
                            <label class="isup-label">Fichier</label>
                            <div class="isup-upload-zone"
                                 @dragover.prevent="e => e.currentTarget.classList.add('isup-zone-active')"
                                 @dragleave.prevent="e => e.currentTarget.classList.remove('isup-zone-active')"
                                 @drop.prevent="e => { e.currentTarget.classList.remove('isup-zone-active'); uploadForm.file = e.dataTransfer.files[0]; }">
                                <i class="bi-cloud-arrow-up" style="font-size:2.5rem;color:#FF7900;"></i>
                                <p class="small" style="color:#888;">Glissez-déposez ou</p>
                                <input type="file" id="file-upload-input" class="d-none" @change="e => uploadForm.file = e.target.files[0]">
                                <button class="isup-btn-outline" type="button" @click="document.getElementById('file-upload-input').click()">
                                    <i class="bi-folder2-open me-1"></i> Parcourir
                                </button>
                                <div v-if="uploadForm.file" class="mt-2 isup-file-selected">
                                    <i class="bi-check-circle me-1"></i> {{ uploadForm.file.name }}
                                    <span style="color:#888;">({{ formatBytes(uploadForm.file.size) }})</span>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="isup-label">Description</label>
                            <textarea class="isup-input" rows="2" v-model="uploadForm.description" placeholder="Décrivez brièvement ce document..."></textarea>
                        </div>
                        <div v-if="currentFolder" class="isup-folder-context">
                            <i class="bi-folder me-1"></i> Destination : <strong>{{ currentFolder.name }}</strong>
                        </div>
                    </div>
                    <div class="isup-modal-footer">
                        <button class="isup-btn-grey" @click="showUpload = false">Annuler</button>
                        <button class="isup-btn-primary" @click="uploadFile" :disabled="!uploadForm.file || loading.upload">
                            <span v-if="loading.upload" class="isup-spinner-sm me-1"></span>
                            <i v-else class="bi-cloud-arrow-up me-1"></i>
                            Uploader
                        </button>
                    </div>
                </div>
            </div>

            <!-- Preview -->
            <div v-if="showPreview && selectedDoc" class="isup-modal-overlay" @click.self="showPreview = false">
                <div class="isup-modal isup-modal-lg">
                    <div class="isup-modal-header">
                        <span class="text-truncate"><i :class="getFileIcon(selectedDoc.file_type)" class="me-2"></i>{{ selectedDoc.name }}</span>
                        <button class="isup-modal-close" @click="showPreview = false">&times;</button>
                    </div>
                    <div class="isup-modal-body">
                        <div class="row g-4">
                            <div class="col-md-7">
                                <div class="isup-preview-box">
                                    <div class="text-center p-4">
                                        <i :class="getFileIcon(selectedDoc.file_type)" style="font-size:5rem;"></i>
                                        <p class="small" style="color:#888;margin-top:8px;">
                                            <template v-if="['jpg','jpeg','png','gif','webp','svg'].includes(selectedDoc.file_type)">Aperçu image disponible</template>
                                            <template v-else-if="selectedDoc.file_type === 'pdf'">Aperçu PDF disponible</template>
                                            <template v-else>Aperçu non disponible</template>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="isup-preview-info">
                                    <div class="isup-info-title"><i class="bi-info-circle me-1"></i> Informations</div>
                                    <dl class="isup-info-grid">
                                        <dt>Type</dt>
                                        <dd><span class="isup-file-badge" :class="fileBadge(selectedDoc.file_type)">{{ selectedDoc.file_type || '—' }}</span></dd>
                                        <dt>Taille</dt>
                                        <dd>{{ formatBytes(selectedDoc.file_size) }}</dd>
                                        <dt>Version</dt>
                                        <dd><span class="isup-version-badge">v{{ selectedDoc.version }}</span></dd>
                                        <dt>Uploadé par</dt>
                                        <dd>{{ selectedDoc.uploaded_by || '—' }}</dd>
                                        <dt>Date</dt>
                                        <dd>{{ formatDate(selectedDoc.created_at) }}</dd>
                                        <dt>Dossier</dt>
                                        <dd class="text-truncate">{{ selectedDoc.folder_name || 'Racine' }}</dd>
                                    </dl>
                                    <div v-if="selectedDoc.description" class="mt-3 pt-2" style="border-top:1px solid #dce3ee;">
                                        <span style="font-size:12px;font-weight:700;color:#163A5E;">Description</span>
                                        <p class="small mt-1" style="color:#888;">{{ selectedDoc.description }}</p>
                                    </div>
                                </div>
                                <div class="d-grid gap-2 mt-3">
                                    <button class="isup-btn-primary w-100" @click="downloadFile(selectedDoc)"><i class="bi-download me-2"></i> Télécharger</button>
                                    <div class="d-flex gap-2">
                                        <button class="isup-btn-outline flex-grow-1" @click="toggleArchive(selectedDoc)">
                                            <i :class="selectedDoc.is_archived ? 'bi-arrow-counterclockwise' : 'bi-archive'"></i>
                                            {{ selectedDoc.is_archived ? ' Restaurer' : ' Archiver' }}
                                        </button>
                                        <button class="isup-btn-outline-red flex-grow-1" @click="confirmDelete(selectedDoc); showPreview = false;">
                                            <i class="bi-trash3"></i> Supprimer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete Confirm -->
            <div v-if="showDeleteConfirm" class="isup-modal-overlay" @click.self="showDeleteConfirm = false">
                <div class="isup-modal isup-modal-sm">
                    <div class="isup-modal-header" style="color:#c62828;">
                        <span><i class="bi-exclamation-triangle me-2"></i>Confirmer</span>
                        <button class="isup-modal-close" @click="showDeleteConfirm = false">&times;</button>
                    </div>
                    <div class="isup-modal-body text-center py-4" v-if="deleteTarget">
                        <i class="bi-trash3" style="font-size:3rem;color:#e53935;display:block;margin-bottom:12px;"></i>
                        <p class="mb-1">Supprimer <strong>{{ deleteTarget.name }}</strong> ?</p>
                        <p class="small" style="color:#888;">Le fichier sera placé dans la corbeille.</p>
                    </div>
                    <div class="isup-modal-footer justify-content-center">
                        <button class="isup-btn-grey px-4" @click="showDeleteConfirm = false">Annuler</button>
                        <button class="isup-btn-danger px-4" @click="deleteDocument"><i class="bi-trash3 me-1"></i> Supprimer</button>
                    </div>
                </div>
            </div>
        </div>
    </CompanyLayout>
</template>

<style scoped>
/* ── GED-specific styles ── */
.ged-shell { border:1px solid #dce3ee; border-radius:6px; overflow:hidden; background:#fff; }
.isup-panel-footer { background:linear-gradient(90deg,#eef2f7,#f5f7fb); border-top:1px solid #dce3ee; padding:8px 14px; font-size:11px; color:#888; }
.isup-panel-icon-btn { background:none; border:none; color:#fff; font-size:15px; cursor:pointer; padding:0 4px; line-height:1; opacity:0.8; transition:opacity .15s; }
.isup-panel-icon-btn:hover { background:rgba(255,255,255,0.3); }
.isup-folder-scroll { max-height:calc(100vh - 420px); overflow-y:auto; }
.isup-folder-scroll::-webkit-scrollbar { width:4px; }
.isup-folder-scroll::-webkit-scrollbar-thumb { background:#ccc; border-radius:4px; }
.isup-folder-row { display:flex; align-items:center; gap:8px; padding:6px 12px; cursor:pointer; font-size:13px; border-bottom:1px solid #f0f4f8; }
.isup-folder-row:hover { background:#f8fbff; }
.isup-folder-row.active { background:rgba(22,58,94,0.06); }
.isup-folder-icon-home { color:#1565c0; font-size:14px; width:16px; }
.isup-folder-icon { color:#f9a825; font-size:16px; width:16px; }
.isup-folder-name { flex-grow:1; font-size:12px; color:#444; }
.isup-folder-count { font-size:11px; color:#aaa; background:#f0f4f8; padding:0 6px; border-radius:3px; font-weight:600; }
.isup-ged-toolbar { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.isup-breadcrumb { display:flex; align-items:center; gap:4px; }
.isup-bcrumb-item { font-size:12px; color:#888; cursor:pointer; }
.isup-bcrumb-item:hover { color:#163A5E; }
.isup-bcrumb-item.active { color:#163A5E; font-weight:600; }
.isup-bcrumb-sep { font-size:10px; color:#ccc; }
.isup-search-box { display:flex; align-items:center; gap:6px; background:#fff; border:1px solid #dce3ee; border-radius:4px; padding:4px 10px; }
.isup-search-box i { color:#aaa; font-size:13px; }
.isup-search-box input { border:none; outline:none; font-size:12px; padding:3px 0; width:160px; }
.isup-ged-view-toggle { display:flex; border:1px solid #dce3ee; border-radius:4px; overflow:hidden; }
.isup-view-btn { background:#fff; border:none; padding:6px 12px; font-size:12px; color:#555; cursor:pointer; border-right:1px solid #dce3ee; }
.isup-view-btn.active { background:#163A5E; color:#fff; }
.isup-view-btn:hover:not(.active) { background:#f5f5f5; }
.isup-table tbody tr.isup-row-active { background:rgba(22,58,94,0.04); }
.isup-doc-name { font-size:13px; font-weight:500; color:#333; max-width:280px; overflow:hidden; text-overflow:ellipsis; }
.isup-doc-desc { font-size:11px; color:#888; max-width:280px; overflow:hidden; text-overflow:ellipsis; }
.isup-doc-meta { font-size:12px; color:#888; }
.isup-file-badge { display:inline-block; font-size:10px; font-weight:600; padding:1px 6px; border-radius:3px; text-transform:uppercase; }
.isup-file-pdf { background:#fdecea; color:#c62828; }
.isup-file-doc { background:#e3f2fd; color:#1565c0; }
.isup-file-xls { background:#e8f5e9; color:#2e7d32; }
.isup-file-img { background:#e0f7fa; color:#00838f; }
.isup-file-other { background:#f5f5f5; color:#757575; }
.isup-version-badge { display:inline-block; font-size:10px; font-weight:600; padding:0 5px; border-radius:3px; background:#f0f4f8; color:#888; line-height:18px; }
.isup-btn-danger { background:#c62828; color:#fff; border:none; border-radius:4px; padding:7px 14px; font-size:12px; font-weight:600; cursor:pointer; }
.isup-btn-danger:hover { background:#c62828; }
.isup-btn-outline { background:#fff; border:1px solid #dce3ee; border-radius:4px; padding:7px 14px; font-size:12px; font-weight:600; color:#555; cursor:pointer; }
.isup-btn-outline:hover { border-color:#FF7900; color:#FF7900; }
.isup-btn-outline-red { background:#fff; border:1px solid #f5c6c0; border-radius:4px; padding:7px 14px; font-size:12px; font-weight:600; color:#c62828; cursor:pointer; }
.isup-btn-outline-red:hover { background:#fdecea; }
.isup-select-sm { padding:4px 8px; font-size:11px; max-width:130px; border:1px solid #dce3ee; border-radius:4px; }
.isup-grid-card { display:flex; flex-direction:column; align-items:center; gap:6px; background:#fff; border:1px solid #dce3ee; border-radius:6px; padding:18px 10px; text-align:center; cursor:pointer; text-decoration:none; transition:box-shadow .15s, transform .12s; }
.isup-grid-card:hover { box-shadow:0 3px 12px rgba(0,0,0,0.08); transform:translateY(-2px); }
.isup-grid-name { font-size:12px; font-weight:500; color:#333; }
.isup-ged-empty { text-align:center; padding:50px 20px; color:#888; }
.isup-ged-empty i { font-size:48px; color:#dce3ee; display:block; margin-bottom:12px; }
.isup-ged-empty h5 { font-size:16px; font-weight:600; margin-bottom:4px; }
.isup-ged-empty p { font-size:13px; margin-bottom:16px; }
.isup-modal-sm { max-width:400px; }
.isup-modal-footer.justify-content-center { justify-content:center; }
.isup-upload-zone { border:2px dashed #dce3ee; border-radius:4px; padding:32px 16px; text-align:center; cursor:pointer; transition:border-color .15s, background .15s; }
.isup-upload-zone:hover, .isup-zone-active { border-color:#FF7900; background:rgba(255,121,0,0.03); }
.isup-file-selected { color:#2e7d32; font-size:12px; }
.isup-folder-context { display:flex; align-items:center; gap:10px; background:#f8fafc; border:1px solid #eef2f7; border-radius:4px; padding:10px 14px; flex-wrap:wrap; }
.isup-preview-box { background:#f5f7fb; border:1px solid #eef2f7; border-radius:4px; padding:20px; text-align:center; min-height:180px; display:flex; align-items:center; justify-content:center; }
.isup-preview-info { background:#fff; border:1px solid #dce3ee; border-radius:4px; padding:14px; }
.isup-info-title { font-size:14px; font-weight:700; color:#163A5E; margin-bottom:10px; }
.isup-info-grid { display:grid; grid-template-columns:auto 1fr; gap:6px 12px; font-size:12px; margin:0; }
.isup-info-grid dt { color:#888; font-weight:400; }
.isup-info-grid dd { margin:0; color:#333; }
.isup-stat-subvalue { font-size:12px; font-weight:500; color:#555; max-width:120px; }
</style>
