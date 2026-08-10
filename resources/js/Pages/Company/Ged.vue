<script setup>
import { ref, computed, onMounted } from 'vue';

// ─── État ───────────────────────────────────────────────────────
const folders = ref([]);
const documents = ref([]);
const currentFolder = ref(null);
const breadcrumbs = ref([]);
const selectedDoc = ref(null);
const viewMode = ref('list');

// Modals
const showCreateFolder = ref(false);
const showUpload = ref(false);
const showPreview = ref(false);
const showVersions = ref(false);
const showAudit = ref(false);
const showDeleteConfirm = ref(false);
const deleteTarget = ref(null);

// Formulaires
const newFolder = ref({ name: '', parent_id: null });
const uploadForm = ref({ file: null, folder_id: null, description: '' });
const searchQuery = ref('');
const fileTypeFilter = ref('');

// Loading
const loading = ref({ folders: true, documents: false, upload: false });

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

const totalSize = computed(() => {
    const bytes = documents.value.reduce((sum, d) => sum + (d.file_size || 0), 0);
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
const apiHeaders = { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' };

async function apiGet(url) {
    const res = await fetch(url, { headers: apiHeaders });
    if (!res.ok) throw new Error(`API error: ${res.status}`);
    return res.json();
}

async function apiPost(url, data) {
    const res = await fetch(url, { method: 'POST', headers: { ...apiHeaders, 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
    if (!res.ok) throw new Error(`API error: ${res.status}`);
    return res.json();
}

async function apiPut(url, data) {
    const res = await fetch(url, { method: 'PUT', headers: { ...apiHeaders, 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
    if (!res.ok) throw new Error(`API error: ${res.status}`);
    return res.json();
}

async function apiDelete(url) {
    const res = await fetch(url, { method: 'DELETE', headers: apiHeaders });
    if (!res.ok) throw new Error(`API error: ${res.status}`);
    return res.json();
}

// ─── Chargement ─────────────────────────────────────────────────
async function loadFolders() {
    loading.value.folders = true;
    try {
        folders.value = await apiGet(`${apiBase}/folders`);
    } catch (e) {
        console.error('Erreur chargement dossiers:', e);
    } finally {
        loading.value.folders = false;
    }
}

async function loadDocuments(folderId = null) {
    loading.value.documents = true;
    try {
        const params = new URLSearchParams();
        if (folderId) params.set('folder_id', folderId);
        const url = `${apiBase}/documents?${params}`;
        const data = await apiGet(url);
        documents.value = data.data || data;
    } catch (e) {
        console.error('Erreur chargement documents:', e);
    } finally {
        loading.value.documents = false;
    }
}

// ─── Actions Dossiers ───────────────────────────────────────────
function openCreateFolder(parentId = null) {
    newFolder.value = { name: '', parent_id: parentId };
    showCreateFolder.value = true;
}

async function createFolder() {
    if (!newFolder.value.name.trim()) return;
    await apiPost(`${apiBase}/folders`, newFolder.value);
    showCreateFolder.value = false;
    newFolder.value = { name: '', parent_id: null };
    await loadFolders();
}

async function deleteFolder(id) {
    try {
        await apiDelete(`${apiBase}/folders/${id}`);
        await loadFolders();
    } catch (e) {
        alert('Impossible de supprimer ce dossier.');
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
        const formData = new FormData();
        formData.append('file', uploadForm.value.file);
        formData.append('folder_id', uploadForm.value.folder_id || '');
        formData.append('description', uploadForm.value.description);

        const res = await fetch(`${apiBase}/documents/upload`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: formData,
        });

        if (!res.ok) {
            const err = await res.json();
            if (res.status === 409 && err.existing) {
                if (!confirm('Ce fichier existe déjà. Voulez-vous l\'ouvrir ?')) return;
                showPreviewInfo(err.existing);
            } else {
                alert(err.message || 'Erreur upload');
            }
            return;
        }

        showUpload.value = false;
        uploadForm.value = { file: null, folder_id: null, description: '' };
        await loadDocuments(currentFolder.value?.id || null);
    } catch (e) {
        console.error('Upload error:', e);
        alert('Erreur lors de l\'upload.');
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
        const res = await fetch(`${apiBase}/documents/${doc.id}/archive`, { method: 'PATCH', headers: apiHeaders });
        if (!res.ok) throw new Error('API error');
        await loadDocuments(currentFolder.value?.id || null);
    } catch (e) {
        console.error('Archive error:', e);
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
        showDeleteConfirm.value = false;
        deleteTarget.value = null;
        if (selectedDoc.value?.id === deleteTarget.value?.id) {
            selectedDoc.value = null;
            showPreview.value = false;
        }
        await loadDocuments(currentFolder.value?.id || null);
    } catch (e) {
        console.error('Delete error:', e);
    }
}

// ─── Helpers ────────────────────────────────────────────────────
function getFileIcon(type) {
    const icons = {
        pdf: 'bi-filetype-pdf text-danger',
        doc: 'bi-filetype-docx text-primary',
        docx: 'bi-filetype-docx text-primary',
        xls: 'bi-filetype-xlsx text-success',
        xlsx: 'bi-filetype-xlsx text-success',
        png: 'bi-filetype-png text-info',
        jpg: 'bi-filetype-jpg text-info',
        jpeg: 'bi-filetype-jpg text-info',
        gif: 'bi-filetype-gif text-warning',
        mp4: 'bi-file-play text-danger',
        mp3: 'bi-file-music text-secondary',
        zip: 'bi-file-zip text-secondary',
        rar: 'bi-file-zip text-secondary',
    };
    return icons[type] || 'bi-file-earmark text-secondary';
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    return dateStr;
}

// ─── Init ───────────────────────────────────────────────────────
onMounted(() => {
    loadFolders();
    loadDocuments();
});
</script>

<template>
    <div class="ged-container">
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h5 class="fw-bold font-heading mb-1">GED — Gestion Électronique de Documents</h5>
                <p class="text-muted small mb-0">Secrétariat &amp; archivage numérique</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm" @click="openCreateFolder(null)" title="Nouveau dossier">
                    <i class="bi-folder-plus"></i> Dossier
                </button>
                <button class="btn btn-primary btn-sm" @click="openUpload(null)" title="Uploader un fichier">
                    <i class="bi-upload"></i> Upload
                </button>
            </div>
        </div>

        <div class="row g-3">
            <!-- Colonne gauche : Arborescence -->
            <div class="col-md-4 col-lg-3">
                <div class="card shadow-sm">
                    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                        <span class="small fw-semibold"><i class="bi-folder me-1"></i> Dossiers</span>
                        <button class="btn btn-sm btn-link p-0" @click="loadFolders()" title="Rafraîchir">
                            <i class="bi-arrow-clockwise"></i>
                        </button>
                    </div>
                    <div class="card-body p-0" style="max-height: calc(100vh - 280px); overflow-y: auto;">
                        <div v-if="loading.folders" class="text-center py-4">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                        </div>
                        <div v-else-if="!folders.length" class="text-center py-4 text-muted small">
                            <i class="bi-folder2-open d-block mb-2" style="font-size: 2rem;"></i>
                            Aucun dossier
                        </div>
                        <ul v-else class="list-unstyled mb-0">
                            <!-- Racine -->
                            <li class="p-2 border-bottom" :class="{'bg-primary bg-opacity-10': !currentFolder}" style="cursor: pointer;" @click="goToRoot()">
                                <i class="bi-house-door me-2"></i>
                                <span class="small fw-medium">Tous les documents</span>
                                <span class="badge bg-secondary float-end">{{ documents.length }}</span>
                            </li>
                            <!-- Dossiers racines -->
                            <li v-for="folder in folders" :key="folder.id">
                                <div class="p-2 border-bottom d-flex align-items-center gap-1"
                                     :class="{'bg-primary bg-opacity-10 text-primary fw-semibold': currentFolder?.id === folder.id}"
                                     style="cursor: pointer;"
                                     @click="selectFolder(folder)">
                                    <i class="bi-chevron-right small" v-if="folder.has_children"></i>
                                    <i class="bi-chevron-down small" v-else style="visibility: hidden;"></i>
                                    <i class="bi-folder2 text-warning me-1"></i>
                                    <span class="small flex-grow-1 text-truncate">{{ folder.name }}</span>
                                    <span class="badge bg-light text-dark small" v-if="folder.documents_count">{{ folder.documents_count }}</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Colonne droite : Documents -->
            <div class="col-md-8 col-lg-9">
                <!-- Barre d'outils -->
                <div class="card shadow-sm mb-3">
                    <div class="card-body py-2 d-flex align-items-center gap-2 flex-wrap">
                        <!-- Breadcrumb -->
                        <nav aria-label="breadcrumb" class="flex-grow-1">
                            <ol class="breadcrumb mb-0 small">
                                <li class="breadcrumb-item" :class="{ active: !currentFolder }" style="cursor: pointer;" @click="goToRoot()">
                                    <i class="bi-house-door me-1"></i>Racine
                                </li>
                                <li v-if="currentFolder" class="breadcrumb-item active" aria-current="page">
                                    {{ currentFolder.name }}
                                </li>
                            </ol>
                        </nav>
                        <!-- Search -->
                        <div class="input-group input-group-sm" style="max-width: 220px;">
                            <span class="input-group-text bg-white"><i class="bi-search"></i></span>
                            <input type="text" class="form-control" placeholder="Rechercher..." v-model="searchQuery">
                        </div>
                        <!-- Filtre type -->
                        <select class="form-select form-select-sm" style="max-width: 120px;" v-model="fileTypeFilter">
                            <option value="">Tous</option>
                            <option v-for="t in fileTypes" :key="t" :value="t">{{ t }}</option>
                        </select>
                        <!-- Vue -->
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary" :class="{ active: viewMode === 'list' }" @click="viewMode = 'list'" title="Liste">
                                <i class="bi-list-ul"></i>
                            </button>
                            <button class="btn btn-outline-secondary" :class="{ active: viewMode === 'grid' }" @click="viewMode = 'grid'" title="Grille">
                                <i class="bi-grid-3x3-gap"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Loading -->
                <div v-if="loading.documents" class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                </div>

                <!-- Liste vide -->
                <div v-else-if="!filteredDocuments.length" class="text-center py-5 text-muted">
                    <i class="bi-file-earmark-plus d-block mb-2" style="font-size: 3rem;"></i>
                    <p v-if="searchQuery" class="mb-0">Aucun résultat pour "{{ searchQuery }}"</p>
                    <p v-else class="mb-0">Aucun document dans ce dossier</p>
                    <button class="btn btn-sm btn-primary mt-2" @click="openUpload(null)">
                        <i class="bi-upload me-1"></i> Uploader un fichier
                    </button>
                </div>

                <!-- Vue Liste -->
                <div v-else-if="viewMode === 'list'" class="card shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th style="width: 40px;"></th>
                                    <th>Nom</th>
                                    <th style="width: 80px;">Type</th>
                                    <th style="width: 90px;">Taille</th>
                                    <th style="width: 80px;">Version</th>
                                    <th style="width: 100px;">Date</th>
                                    <th style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="doc in filteredDocuments" :key="doc.id"
                                    :class="{ 'table-active': selectedDoc?.id === doc.id }"
                                    style="cursor: pointer;">
                                    <td @click="showPreviewInfo(doc)">
                                        <i :class="getFileIcon(doc.file_type)" style="font-size: 1.2rem;"></i>
                                    </td>
                                    <td @click="showPreviewInfo(doc)">
                                        <div class="small fw-medium text-truncate" style="max-width: 250px;">{{ doc.name }}</div>
                                        <div v-if="doc.description" class="text-muted small text-truncate" style="max-width: 250px;">{{ doc.description }}</div>
                                    </td>
                                    <td @click="showPreviewInfo(doc)">
                                        <span class="badge bg-light text-dark small">{{ doc.file_type || '?' }}</span>
                                    </td>
                                    <td @click="showPreviewInfo(doc)" class="small">{{ doc.formatted_size }}</td>
                                    <td @click="showPreviewInfo(doc)">
                                        <span class="badge bg-info bg-opacity-10 text-info small">v{{ doc.version }}</span>
                                    </td>
                                    <td @click="showPreviewInfo(doc)" class="small text-muted">{{ formatDate(doc.created_at) }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary btn-sm" @click.stop="downloadFile(doc)" title="Télécharger">
                                                <i class="bi-download"></i>
                                            </button>
                                            <button class="btn btn-outline-info btn-sm" @click.stop="showPreviewInfo(doc)" title="Détails">
                                                <i class="bi-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-danger btn-sm" @click.stop="confirmDelete(doc)" title="Supprimer">
                                                <i class="bi-trash3"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Vue Grille -->
                <div v-else class="row g-3">
                    <div v-for="doc in filteredDocuments" :key="doc.id" class="col-6 col-md-4 col-lg-3">
                        <div class="card shadow-sm h-100" style="cursor: pointer;" @click="showPreviewInfo(doc)">
                            <div class="card-body text-center py-3">
                                <i :class="getFileIcon(doc.file_type)" style="font-size: 2.5rem;"></i>
                                <div class="small fw-medium text-truncate mt-2">{{ doc.name }}</div>
                                <div class="text-muted small">
                                    <span class="badge bg-light text-dark me-1">{{ doc.file_type }}</span>
                                    <span class="badge bg-info bg-opacity-10 text-info">v{{ doc.version }}</span>
                                </div>
                                <div class="text-muted small mt-1">{{ doc.formatted_size }}</div>
                            </div>
                            <div class="card-footer bg-transparent border-top-0 py-1 px-2 text-center">
                                <button class="btn btn-sm btn-link text-primary p-1" @click.stop="downloadFile(doc)" title="Télécharger">
                                    <i class="bi-download"></i>
                                </button>
                                <button class="btn btn-sm btn-link text-danger p-1" @click.stop="confirmDelete(doc)" title="Supprimer">
                                    <i class="bi-trash3"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal : Créer dossier -->
        <div class="modal fade" :class="{ show: showCreateFolder }" :style="{ display: showCreateFolder ? 'block' : 'none' }" tabindex="-1" @click.self="showCreateFolder = false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold">Nouveau dossier</h6>
                        <button type="button" class="btn-close" @click="showCreateFolder = false"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small">Nom du dossier</label>
                            <input type="text" class="form-control" v-model="newFolder.name" @keyup.enter="createFolder" placeholder="Ex: Factures 2026" autofocus>
                        </div>
                        <div v-if="currentFolder" class="small text-muted">
                            <i class="bi-folder me-1"></i> Dans : {{ currentFolder.name }}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-secondary" @click="showCreateFolder = false">Annuler</button>
                        <button class="btn btn-sm btn-primary" @click="createFolder" :disabled="!newFolder.name.trim()">Créer</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal : Upload -->
        <div class="modal fade" :class="{ show: showUpload }" :style="{ display: showUpload ? 'block' : 'none' }" tabindex="-1" @click.self="showUpload = false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold"><i class="bi-upload me-2"></i>Uploader un document</h6>
                        <button type="button" class="btn-close" @click="showUpload = false"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Fichier</label>
                            <input type="file" class="form-control form-control-sm" @change="e => uploadForm.file = e.target.files[0]">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Description (optionnelle)</label>
                            <textarea class="form-control form-control-sm" rows="2" v-model="uploadForm.description" placeholder="Description du document..."></textarea>
                        </div>
                        <div v-if="currentFolder" class="small text-muted">
                            <i class="bi-folder me-1"></i> Dossier : {{ currentFolder.name }}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-secondary" @click="showUpload = false">Annuler</button>
                        <button class="btn btn-sm btn-primary" @click="uploadFile" :disabled="!uploadForm.file || loading.upload">
                            <span v-if="loading.upload" class="spinner-border spinner-border-sm me-1"></span>
                            <i class="bi-cloud-arrow-up me-1"></i> Uploader
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal : Aperçu / Détails -->
        <div class="modal fade" :class="{ show: showPreview }" :style="{ display: showPreview ? 'block' : 'none' }" tabindex="-1" @click.self="showPreview = false" v-if="selectedDoc">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold text-truncate">
                            <i :class="getFileIcon(selectedDoc.file_type)" class="me-2"></i>
                            {{ selectedDoc.name }}
                        </h6>
                        <button type="button" class="btn-close" @click="showPreview = false"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <div v-if="selectedDoc.file_type === 'pdf'" class="border rounded p-3 text-center bg-light">
                                    <i class="bi-filetype-pdf" style="font-size: 4rem; color: #dc3545;"></i>
                                    <p class="small text-muted mt-2">Aperçu PDF disponible au téléchargement</p>
                                    <button class="btn btn-sm btn-danger" @click="downloadFile(selectedDoc)">
                                        <i class="bi-download me-1"></i> Télécharger le PDF
                                    </button>
                                </div>
                                <div v-else-if="['jpg','jpeg','png','gif','webp'].includes(selectedDoc.file_type)" class="border rounded p-2 text-center bg-light">
                                    <i class="bi-image" style="font-size: 4rem; color: #0d6efd;"></i>
                                    <p class="small text-muted mt-2">Image disponible au téléchargement</p>
                                    <button class="btn btn-sm btn-primary" @click="downloadFile(selectedDoc)">
                                        <i class="bi-download me-1"></i> Télécharger
                                    </button>
                                </div>
                                <div v-else class="border rounded p-3 text-center bg-light">
                                    <i :class="getFileIcon(selectedDoc.file_type)" style="font-size: 4rem;"></i>
                                    <p class="small text-muted mt-2">Aperçu non disponible pour ce type de fichier</p>
                                    <button class="btn btn-sm btn-outline-primary" @click="downloadFile(selectedDoc)">
                                        <i class="bi-download me-1"></i> Télécharger
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body py-2">
                                        <h6 class="small fw-bold mb-2">Informations</h6>
                                        <dl class="row small mb-0">
                                            <dt class="col-5 text-muted">Type</dt>
                                            <dd class="col-7">{{ selectedDoc.file_type || '—' }}</dd>
                                            <dt class="col-5 text-muted">Taille</dt>
                                            <dd class="col-7">{{ selectedDoc.formatted_size }}</dd>
                                            <dt class="col-5 text-muted">Version</dt>
                                            <dd class="col-7">v{{ selectedDoc.version }}</dd>
                                            <dt class="col-5 text-muted">Uploadé par</dt>
                                            <dd class="col-7">{{ selectedDoc.uploaded_by }}</dd>
                                            <dt class="col-5 text-muted">Date</dt>
                                            <dd class="col-7">{{ formatDate(selectedDoc.created_at) }}</dd>
                                            <dt class="col-5 text-muted">Dossier</dt>
                                            <dd class="col-7 text-truncate">{{ selectedDoc.folder_name || 'Racine' }}</dd>
                                        </dl>
                                        <div v-if="selectedDoc.description" class="mt-2">
                                            <span class="small fw-bold">Description</span>
                                            <p class="small mb-0 text-muted">{{ selectedDoc.description }}</p>
                                        </div>
                                        <div class="mt-3 d-grid gap-1">
                                            <button class="btn btn-sm btn-outline-primary" @click="downloadFile(selectedDoc)">
                                                <i class="bi-download me-1"></i> Télécharger
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning" @click="toggleArchive(selectedDoc)">
                                                <i :class="selectedDoc.is_archived ? 'bi-arrow-counterclockwise' : 'bi-archive'"></i>
                                                {{ selectedDoc.is_archived ? 'Restaurer' : 'Archiver' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal : Confirmer suppression -->
        <div class="modal fade" :class="{ show: showDeleteConfirm }" :style="{ display: showDeleteConfirm ? 'block' : 'none' }" tabindex="-1" @click.self="showDeleteConfirm = false">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold text-danger">Confirmer la suppression</h6>
                        <button type="button" class="btn-close" @click="showDeleteConfirm = false"></button>
                    </div>
                    <div class="modal-body small" v-if="deleteTarget">
                        Êtes-vous sûr de vouloir supprimer <strong>{{ deleteTarget.name }}</strong> ?
                        <p class="text-muted mt-1 mb-0">Le fichier sera placé dans la corbeille.</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-secondary" @click="showDeleteConfirm = false">Annuler</button>
                        <button class="btn btn-sm btn-danger" @click="deleteDocument">
                            <i class="bi-trash3 me-1"></i> Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backdrop for modals -->
        <div v-if="showCreateFolder || showUpload || showPreview || showDeleteConfirm" class="modal-backdrop fade show"></div>
    </div>
</template>

<style scoped>
.ged-container {
    min-height: 400px;
}
.table > :not(caption) > * > * {
    padding: 0.5rem 0.75rem;
    vertical-align: middle;
}
</style>
