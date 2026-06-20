<script setup>
import { ref, onMounted, nextTick } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';
import { authStore } from '../../../stores/auth';

const props = defineProps({
    clientId: { type: [Number, String], default: null }
});

const client = ref(null);
const folders = ref([]);
const documents = ref([]);
const loading = ref(true);
const error = ref(null);
const submitting = ref(false);
const uploading = ref(false);
const selectedFolder = ref(null);

// Folder modal
const showFolderModal = ref(false);
const isEditingFolder = ref(false);
const editingFolderId = ref(null);
const folderForm = ref({ name: '', slug: '' });

// Document upload
const showUploadModal = ref(false);
const uploadFolderId = ref(null);

const fetchData = async () => {
    loading.value = true;
    error.value = null;
    const cid = props.clientId || authStore.user?.client_id;
    if (!cid) { error.value = 'Aucun client sélectionné.'; loading.value = false; return; }
    try {
        const [foldersRes, docsRes, clientRes] = await Promise.all([
            fetch('/api/dossiers/' + cid),
            fetch('/api/documents/' + cid),
            fetch('/api/clients/' + cid),
        ]);
        if (foldersRes.ok) folders.value = await foldersRes.json();
        if (docsRes.ok) documents.value = await docsRes.json();
        if (clientRes.ok) client.value = await clientRes.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const openCreateFolder = () => {
    folderForm.value = { name: '', slug: '' };
    isEditingFolder.value = false;
    editingFolderId.value = null;
    showFolderModal.value = true;
};

const openEditFolder = (folder) => {
    folderForm.value = { name: folder.name, slug: folder.slug };
    isEditingFolder.value = true;
    editingFolderId.value = folder.id;
    showFolderModal.value = true;
};

const submitFolder = async () => {
    const effectiveClientId = props.clientId || authStore.user?.client_id;
    if (!effectiveClientId) {
        alert('Aucun client sélectionné. Veuillez d\'abord sélectionner un client.');
        return;
    }
    submitting.value = true;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const url = isEditingFolder.value ? '/dossiers/' + editingFolderId.value : '/dossiers';
        const method = isEditingFolder.value ? 'PUT' : 'POST';

        const res = await fetch(url, {
            method,
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ ...folderForm.value, client_id: effectiveClientId }),
        });
        if (!res.ok) {
            const body = await res.text();
            throw new Error(body || res.statusText);
        }
        showFolderModal.value = false;
        await fetchData();
    } catch (e) {
        alert('Erreur: ' + e.message);
    } finally {
        submitting.value = false;
    }
};

const deleteFolder = async (id) => {
    if (!confirm('Supprimer ce dossier et tous ses documents ?')) return;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/dossiers/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error('Erreur');
        await fetchData();
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

// Upload
const openUpload = (folderId = null) => {
    uploadFolderId.value = folderId;
    showUploadModal.value = true;
};

const handleFileUpload = async (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const cid = props.clientId || authStore.user?.client_id;
    if (!cid) { alert('Aucun client sélectionné.'); uploading.value = false; return; }
    uploading.value = true;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const formData = new FormData();
        formData.append('file', file);
        formData.append('client_id', cid);
        if (uploadFolderId.value) formData.append('folder_id', uploadFolderId.value);

        const res = await fetch('/documents/upload', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: formData,
        });
        if (!res.ok) throw new Error('Erreur upload');
        showUploadModal.value = false;
        await fetchData();
    } catch (e) {
        alert('Erreur: ' + e.message);
    } finally {
        uploading.value = false;
        e.target.value = '';
    }
};

const deleteDocument = async (id) => {
    if (!confirm('Supprimer ce document ?')) return;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch('/documents/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error('Erreur');
        await fetchData();
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
};

const getFolderDocuments = (folderId) => {
    return documents.value.filter(d => d.folder_id === folderId);
};

const getUnfiledDocuments = () => {
    return documents.value.filter(d => !d.folder_id);
};

onMounted(fetchData);
</script>

<template>
    <GelLayout :page-title="'Dossiers - ' + (client?.company_name || 'Client')">
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <template v-else>
            <!-- Toolbar -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div>
                    <h5 class="fw-bold mb-1">{{ client?.company_name || 'Client' }}</h5>
                    <span class="small text-muted">{{ folders.length }} dossier(s), {{ documents.length }} document(s)</span>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary btn-sm" @click="openCreateFolder">
                        <i class="bi-folder-plus me-1"></i>Nouveau dossier
                    </button>
                    <button class="btn btn-outline-primary btn-sm" @click="openUpload(null)">
                        <i class="bi-upload me-1"></i>Uploader
                    </button>
                </div>
            </div>

            <!-- Folder Grid -->
            <div v-if="!folders.length && !documents.length" class="text-center py-5 text-muted">
                <i class="bi-folder" style="font-size:48px;"></i>
                <p class="mt-2">Aucun dossier ou document. Créez un dossier pour commencer.</p>
            </div>

            <div class="row g-3">
                <div v-for="folder in folders" :key="folder.id" class="col-md-6 col-lg-4">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi-folder-fill text-warning" style="font-size:24px;"></i>
                                <h6 class="mb-0 fw-medium">{{ folder.name }}</h6>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light" data-bs-toggle="dropdown"><i class="bi-three-dots"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                    <li><button class="dropdown-item small" @click="openUpload(folder.id)"><i class="bi-upload me-2"></i>Uploader ici</button></li>
                                    <li><button class="dropdown-item small" @click="openEditFolder(folder)"><i class="bi-pencil me-2"></i>Renommer</button></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><button class="dropdown-item small text-danger" @click="deleteFolder(folder.id)"><i class="bi-trash me-2"></i>Supprimer</button></li>
                                </ul>
                            </div>
                        </div>
                        <div class="small text-muted mb-2">{{ folder.slug }}</div>

                        <!-- Documents in folder -->
                        <div v-if="getFolderDocuments(folder.id).length">
                            <div v-for="doc in getFolderDocuments(folder.id)" :key="doc.id" class="d-flex align-items-center justify-content-between py-1 border-top">
                                <div class="d-flex align-items-center gap-2 small">
                                    <i class="bi-file-earmark-text text-primary"></i>
                                    <a :href="'/documents/download/' + doc.id" class="text-decoration-none">{{ doc.name }}</a>
                                </div>
                                <button class="btn btn-sm btn-link text-danger p-0" @click="deleteDocument(doc.id)" title="Supprimer">
                                    <i class="bi-x"></i>
                                </button>
                            </div>
                        </div>
                        <div v-else class="small text-muted fst-italic py-2">Dossier vide</div>
                    </div>
                </div>
            </div>

            <!-- Unfiled Documents -->
            <div v-if="getUnfiledDocuments().length" class="mt-4">
                <h6 class="fw-bold mb-3"><i class="bi-file-earmark me-2"></i>Documents non classés ({{ getUnfiledDocuments().length }})</h6>
                <div class="bg-white rounded-lg shadow p-6">
                    <div v-for="doc in getUnfiledDocuments()" :key="doc.id" class="d-flex align-items-center justify-content-between py-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi-file-earmark-text text-primary"></i>
                            <a :href="'/documents/download/' + doc.id" class="text-decoration-none small">{{ doc.name }}</a>
                            <span class="small text-muted">{{ Math.round(doc.size/1024) }} Ko</span>
                        </div>
                        <button class="btn btn-sm btn-link text-danger p-0" @click="deleteDocument(doc.id)"><i class="bi-trash"></i></button>
                    </div>
                </div>
            </div>
        </template>

        <!-- Folder Modal -->
        <div v-if="showFolderModal" class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold">{{ isEditingFolder ? 'Renommer' : 'Nouveau' }} dossier</h6>
                        <button type="button" class="btn-close" @click="showFolderModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label small">Nom *</label>
                            <input v-model="folderForm.name" class="form-control form-control-sm" required>
                        </div>
                        <div>
                            <label class="form-label small">Slug</label>
                            <input v-model="folderForm.slug" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-secondary" @click="showFolderModal = false">Annuler</button>
                        <button class="btn btn-sm btn-primary" :disabled="submitting" @click="submitFolder">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                            {{ isEditingFolder ? 'Mettre à jour' : 'Créer' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Modal -->
        <div v-if="showUploadModal" class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold">Uploader un document</h6>
                        <button type="button" class="btn-close" @click="showUploadModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted">Sélectionnez un fichier à uploader{{ uploadFolderId ? ' dans le dossier sélectionné' : '' }}.</p>
                        <input type="file" class="form-control form-control-sm" @change="handleFileUpload" :disabled="uploading">
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>
