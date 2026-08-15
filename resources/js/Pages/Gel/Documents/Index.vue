<template>
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0 text-gray-800">
                <i class="bi bi-folder2-open text-primary me-2"></i>
                GED Intelligente (Dossier Client #{{ clientId }})
            </h2>
            <button class="btn btn-primary" @click="showUploadModal = true">
                <i class="bi bi-cloud-arrow-up me-2"></i>Nouveau Document
            </button>
        </div>

        <div class="row">
            <!-- Explorateur (Dossiers / Filtres) -->
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h6 class="font-weight-bold text-primary mb-0">Catégories</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item border-0 px-0 py-2 d-flex justify-content-between align-items-center cursor-pointer" 
                                @click="filterCategory('')" :class="{'fw-bold text-primary': activeCategory === ''}">
                                <span><i class="bi bi-files me-2 text-muted"></i>Tous les documents</span>
                                <span class="badge bg-light text-dark rounded-pill">{{ documents.length }}</span>
                            </li>
                            <li class="list-group-item border-0 px-0 py-2 d-flex justify-content-between align-items-center cursor-pointer"
                                @click="filterCategory('Facture')" :class="{'fw-bold text-primary': activeCategory === 'Facture'}">
                                <span><i class="bi bi-receipt me-2 text-warning"></i>Factures</span>
                            </li>
                            <li class="list-group-item border-0 px-0 py-2 d-flex justify-content-between align-items-center cursor-pointer"
                                @click="filterCategory('Bulletin de Paie')" :class="{'fw-bold text-primary': activeCategory === 'Bulletin de Paie'}">
                                <span><i class="bi bi-person-lines-fill me-2 text-info"></i>Bulletins de Paie</span>
                            </li>
                            <li class="list-group-item border-0 px-0 py-2 d-flex justify-content-between align-items-center cursor-pointer"
                                @click="filterCategory('Document Juridique')" :class="{'fw-bold text-primary': activeCategory === 'Document Juridique'}">
                                <span><i class="bi bi-bank me-2 text-secondary"></i>Juridique</span>
                            </li>
                            <li class="list-group-item border-0 px-0 py-2 d-flex justify-content-between align-items-center cursor-pointer"
                                @click="filterCategory('Autre')" :class="{'fw-bold text-primary': activeCategory === 'Autre'}">
                                <span><i class="bi bi-folder2 me-2 text-dark"></i>Autres</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Liste des documents -->
            <div class="col-md-9">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-dense align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 border-0">Nom du document</th>
                                        <th class="border-0">Catégorie</th>
                                        <th class="border-0">Date doc.</th>
                                        <th class="border-0">Taille</th>
                                        <th class="border-0 text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="filteredDocuments.length === 0">
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="bi bi-folder-x display-4 d-block mb-3"></i>
                                            Aucun document trouvé.
                                        </td>
                                    </tr>
                                    <tr v-for="doc in filteredDocuments" :key="doc.id">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3 text-primary fs-3">
                                                    <i v-if="doc.file_type === 'pdf'" class="bi bi-file-earmark-pdf text-danger"></i>
                                                    <i v-else-if="['jpg','jpeg','png'].includes(doc.file_type)" class="bi bi-file-earmark-image text-info"></i>
                                                    <i v-else class="bi bi-file-earmark-text text-secondary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ doc.original_name || doc.name }}</h6>
                                                    <small class="text-muted text-truncate d-inline-block" style="max-width: 250px;" v-if="doc.description">
                                                        {{ doc.description }}
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ doc.category || 'Non classé' }}</span>
                                        </td>
                                        <td>{{ formatDate(doc.document_date) }}</td>
                                        <td>{{ formatBytes(doc.file_size) }}</td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-light me-2" @click="downloadDocument(doc.id)" title="Télécharger">
                                                <i class="bi bi-download"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" @click="deleteDocument(doc.id)" title="Supprimer">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Upload IA -->
        <div class="modal fade" :class="{'show d-block': showUploadModal}" tabindex="-1" style="background: rgba(0,0,0,0.5);" v-if="showUploadModal">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-robot me-2 text-primary"></i>Upload Intelligent (OCR)</h5>
                        <button type="button" class="btn-close" @click="showUploadModal = false"></button>
                    </div>
                    <div class="modal-body p-4">
                        <AiUploader :clientId="clientId" @document-uploaded="onDocumentUploaded" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import AiUploader from '../../../Components/AiUploader.vue';

const props = defineProps({
    clientId: {
        type: [Number, String],
        required: true
    }
});

const documents = ref([]);
const showUploadModal = ref(false);
const activeCategory = ref('');

const fetchDocuments = async () => {
    try {
        const response = await axios.get(`/api/documents/${props.clientId}`);
        documents.value = response.data;
    } catch (error) {
        console.error("Erreur lors du chargement des documents", error);
    }
};

onMounted(() => {
    fetchDocuments();
});

const filteredDocuments = computed(() => {
    if (!activeCategory.value) return documents.value;
    return documents.value.filter(doc => doc.category === activeCategory.value);
});

const filterCategory = (category) => {
    activeCategory.value = category;
};

const onDocumentUploaded = (newDoc) => {
    showUploadModal.value = false;
    documents.value.unshift(newDoc);
};

const downloadDocument = (id) => {
    // Le téléchargement se fait en GET
    window.open(`/gel/documents/download/${id}`, '_blank');
};

const deleteDocument = async (id) => {
    if (confirm("Êtes-vous sûr de vouloir supprimer ce document ?")) {
        try {
            await axios.delete(`/gel/documents/${id}`);
            documents.value = documents.value.filter(d => d.id !== id);
        } catch (error) {
            console.error("Erreur de suppression", error);
        }
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '—';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR');
};

const formatBytes = (bytes, decimals = 2) => {
    if (!+bytes) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
};
</script>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}
.list-group-item:hover {
    background-color: #f8f9fa;
}
</style>
