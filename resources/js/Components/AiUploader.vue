<template>
    <div class="ai-uploader-container">
        <!-- Zone de Drag & Drop -->
        <div 
            class="upload-dropzone"
            :class="{ 'is-dragover': isDragover, 'is-analyzing': isAnalyzing }"
            @dragover.prevent="isDragover = true"
            @dragleave.prevent="isDragover = false"
            @drop.prevent="handleDrop"
            @click="triggerFileInput"
        >
            <input 
                type="file" 
                ref="fileInput" 
                class="d-none" 
                @change="handleFileSelect"
                accept=".pdf,.png,.jpg,.jpeg"
            >
            
            <div v-if="!isAnalyzing && !analysisResult" class="upload-content">
                <i class="bi bi-cloud-arrow-up display-4 text-primary mb-3"></i>
                <h5 class="mb-1">Glissez vos documents ici</h5>
                <p class="text-muted mb-0">ou cliquez pour parcourir (L'IA analysera le contenu)</p>
            </div>

            <!-- Loader d'analyse IA -->
            <div v-if="isAnalyzing" class="upload-content py-4">
                <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Analyse en cours...</span>
                </div>
                <h5 class="mb-1 text-primary">Analyse IA en cours...</h5>
                <p class="text-muted mb-0">Extraction des métadonnées par OCR</p>
            </div>
        </div>

        <!-- Résultat de l'analyse -->
        <div v-if="analysisResult && !isAnalyzing" class="ai-analysis-result mt-4">
            <div class="card border-primary shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-robot me-2"></i>Suggestions de l'IA (Score: {{ analysisResult.confidence_score }}%)</h6>
                    <button class="btn btn-sm btn-light" @click="resetUpload">Annuler</button>
                </div>
                <div class="card-body">
                    <form @submit.prevent="confirmUpload">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Catégorie suggérée</label>
                                <select v-model="formData.category" class="form-select">
                                    <option value="Facture">Facture</option>
                                    <option value="Bulletin de Paie">Bulletin de Paie</option>
                                    <option value="Document Juridique">Document Juridique</option>
                                    <option value="Autre">Autre</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date du document</label>
                                <input type="date" v-model="formData.document_date" class="form-control">
                            </div>
                            
                            <!-- Données extraites spécifiques -->
                            <div class="col-12" v-if="Object.keys(extractedData).length > 0">
                                <label class="form-label text-muted small mb-1">Données extraites par OCR</label>
                                <div class="bg-light p-3 rounded border">
                                    <div class="row">
                                        <div class="col-md-4 mb-2" v-for="(val, key) in extractedData" :key="key">
                                            <small class="text-uppercase text-muted d-block" style="font-size:0.7rem;">{{ key.replace('_', ' ') }}</small>
                                            <strong>{{ val }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Description (Générée)</label>
                                <textarea v-model="formData.description" class="form-control" rows="2"></textarea>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-success" :disabled="isUploading">
                                <span v-if="isUploading" class="spinner-border spinner-border-sm me-1"></span>
                                Confirmer et Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import axios from 'axios';

const props = defineProps({
    clientId: {
        type: [Number, String],
        required: true
    }
});

const emit = defineEmits(['document-uploaded']);

const fileInput = ref(null);
const isDragover = ref(false);
const isAnalyzing = ref(false);
const isUploading = ref(false);
const analysisResult = ref(null);
const extractedData = ref({});
const currentFile = ref(null);

const formData = reactive({
    category: '',
    document_date: '',
    description: '',
    tags: []
});

const triggerFileInput = () => {
    fileInput.value.click();
};

const handleDragOver = (e) => {
    e.preventDefault();
    isDragover.value = true;
};

const handleDrop = (e) => {
    isDragover.value = false;
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        processFile(files[0]);
    }
};

const handleFileSelect = (e) => {
    const files = e.target.files;
    if (files.length > 0) {
        processFile(files[0]);
    }
};

const processFile = async (file) => {
    currentFile.value = file;
    isAnalyzing.value = true;
    analysisResult.value = null;

    const data = new FormData();
    data.append('file', file);

    try {
        // Appel au endpoint d'analyse IA
        const url = typeof route !== 'undefined' ? route('documents.analyze') : '/gel/documents/analyze';
        const response = await axios.post(url, data, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        const result = response.data;
        analysisResult.value = result;
        extractedData.value = result.extracted_data || {};
        
        // Pré-remplir le formulaire avec les suggestions IA
        formData.category = result.suggested_category || '';
        formData.document_date = result.extracted_data?.date || '';
        formData.description = `Document auto-classé via IA (Score: ${result.confidence_score}%)`;
        formData.tags = result.suggested_tags || [];
        
    } catch (error) {
        console.error('Erreur lors de l\'analyse IA', error);
        alert("Erreur lors de l'analyse du document. Vous pouvez tout de même l'enregistrer manuellement.");
        // Mode manuel de fallback
        analysisResult.value = { confidence_score: 0 };
    } finally {
        isAnalyzing.value = false;
    }
};

const confirmUpload = async () => {
    if (!currentFile.value) return;
    
    isUploading.value = true;
    
    const data = new FormData();
    data.append('file', currentFile.value);
    data.append('client_id', props.clientId);
    data.append('category', formData.category);
    data.append('document_date', formData.document_date);
    data.append('description', formData.description);
    // data.append('tags', JSON.stringify(formData.tags));

    try {
        const url = typeof route !== 'undefined' ? route('documents.upload', props.clientId) : `/gel/documents/upload`;
        const response = await axios.post(url, data, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        
        emit('document-uploaded', response.data);
        resetUpload();
    } catch (error) {
        console.error('Erreur lors de l\'upload', error);
        alert("Erreur lors de l'enregistrement du document.");
    } finally {
        isUploading.value = false;
    }
};

const resetUpload = () => {
    currentFile.value = null;
    analysisResult.value = null;
    extractedData.value = {};
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};
</script>

<style scoped>
.ai-uploader-container {
    width: 100%;
}
.upload-dropzone {
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    padding: 3rem 2rem;
    text-align: center;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
    cursor: pointer;
}
.upload-dropzone:hover, .upload-dropzone.is-dragover {
    border-color: #0d6efd;
    background-color: #e9ecef;
}
.upload-dropzone.is-analyzing {
    border-color: #0d6efd;
    background-color: #f1f8ff;
    cursor: wait;
}
.upload-content {
    pointer-events: none;
}
</style>
