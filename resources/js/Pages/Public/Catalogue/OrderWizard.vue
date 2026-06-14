<script setup>
import { ref, computed, reactive } from 'vue';

const props = defineProps({
    service: {
        type: Object,
        required: true,
    },
    user: {
        type: Object,
        default: null,
    }
});

const currentStep = ref(1);
const processing = ref(false);

const form = reactive({
    form_data: {},
    documents: [] // Array of { file: File, label: String }
});

// Initialiser les champs dynamiques s'ils existent
if (props.service.champs_formulaire_json) {
    props.service.champs_formulaire_json.forEach(champ => {
        form.form_data[champ.name] = '';
    });
}

const totalSteps = computed(() => {
    // Étape 1 : Champs dynamiques + Étape 2 : Récap
    return (props.service.champs_formulaire_json?.length ? 1 : 0) + 1;
});

const nextStep = () => {
    currentStep.value++;
};

const prevStep = () => {
    currentStep.value--;
};

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const handleFileUploads = (e) => {
    const files = Array.from(e.target.files);
    files.forEach(file => {
        form.documents.push({
            file: file,
            label: 'Document joint'
        });
    });
    e.target.value = ''; // Réinitialiser le champ input
};

const handleRequiredFileUpload = (e, docName) => {
    const file = e.target.files[0];
    if (!file) return;
    
    // Remplacer si un fichier existe déjà pour ce label
    const existingIndex = form.documents.findIndex(d => d.label === docName);
    if (existingIndex !== -1) {
        form.documents.splice(existingIndex, 1);
    }
    
    form.documents.push({
        file: file,
        label: docName
    });
    e.target.value = '';
};

const hasUploadedDoc = (docName) => {
    return form.documents.some(d => d.label === docName);
};

const getUploadedDocName = (docName) => {
    const doc = form.documents.find(d => d.label === docName);
    return doc ? doc.file.name : '';
};

const removeDocument = (index) => {
    form.documents.splice(index, 1);
};

const submitOrder = async () => {
    processing.value = true;
    try {
        const formData = new FormData();
        // Ajouter les champs de formulaire (sérialisés en JSON car c'est un tableau dynamique côté serveur)
        for (const [key, value] of Object.entries(form.form_data)) {
            formData.append(`form_data[${key}]`, value);
        }
        // Ajouter les fichiers et types
        form.documents.forEach((doc, index) => {
            formData.append(`documents[${index}]`, doc.file);
            formData.append(`document_types[${index}]`, doc.label);
        });

        const res = await fetch('/commande/soumettre', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        });
        
        if (res.ok) {
            const data = await res.json();
            window.location.href = data.redirect || '/mes-commandes';
        } else if (res.redirected) {
            window.location.href = res.url;
        } else {
            const err = await res.json().catch(() => ({}));
            alert(err.message || 'Une erreur est survenue. Veuillez réessayer.');
            processing.value = false;
        }
    } catch (e) {
        console.error(e);
        processing.value = false;
    }
};
</script>

<template>
    <div class="wizard-wrapper bg-white min-vh-100 py-5" style="font-family: 'Inter', sans-serif;">
        <div class="container" style="max-width: 800px;">
            
            <div class="text-center mb-5">
                <h1 class="display-5 fw-bold text-dark font-heading">Commander en ligne</h1>
                <p class="lead text-muted">{{ service.nom }}</p>
            </div>

            <!-- Progress Bar -->
            <div class="mb-5 px-3">
                <div class="progress rounded-pill bg-light mb-2" style="height: 8px;">
                    <div class="progress-bar bg-primary" role="progressbar" :style="`width: ${(currentStep / totalSteps) * 100}%`" :aria-valuenow="(currentStep / totalSteps) * 100" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="d-flex justify-content-between text-muted small fw-medium px-1">
                    <span>Étape {{ currentStep }} sur {{ totalSteps }}</span>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <form @submit.prevent="submitOrder" class="p-4 p-md-5">
                    
                    <!-- Etape 1: Champs dynamiques -->
                    <div v-if="currentStep === 1 && totalSteps > 1">
                        <h2 class="h4 fw-bold text-dark mb-4">Détails de votre demande</h2>
                        <div v-if="service.champs_formulaire_json && service.champs_formulaire_json.length" class="row gy-4">
                            <div v-for="(champ, index) in service.champs_formulaire_json" :key="index" class="col-12">
                                <label class="form-label fw-medium text-dark">{{ champ.label }} <span v-if="champ.required" class="text-danger">*</span></label>
                                <input v-if="champ.type === 'text'" v-model="form.form_data[champ.name]" type="text" :required="champ.required" class="form-control form-control-lg bg-light border-0">
                                <textarea v-if="champ.type === 'textarea'" v-model="form.form_data[champ.name]" :required="champ.required" rows="4" class="form-control form-control-lg bg-light border-0"></textarea>
                            </div>
                        </div>
                        <div v-else class="text-muted fst-italic">
                            Aucun détail supplémentaire n'est requis pour ce service.
                        </div>
                    </div>
                    
                    <!-- Upload de documents -->
                    <div v-if="currentStep === 1" class="mt-5 border-top pt-4">
                        <h3 class="h5 fw-bold text-dark mb-3">Documents &amp; Pièces jointes</h3>
                        
                        <!-- Liste des documents requis/suggérés -->
                        <div v-if="service.documents_requis_json && service.documents_requis_json.length" class="mb-4">
                            <p class="text-muted small mb-3">Veuillez fournir les documents suivants nécessaires au traitement de ce service :</p>
                            <div class="row g-3">
                                <div v-for="(docName, idx) in service.documents_requis_json" :key="idx" class="col-12 col-md-6">
                                    <div class="p-3 border rounded-3 bg-light d-flex justify-content-between align-items-center">
                                        <div class="flex-grow-1 me-2" style="min-width: 0;">
                                            <div class="fw-semibold small text-dark text-truncate">{{ docName }}</div>
                                            <div class="text-muted text-truncate" style="font-size: 11px;">
                                                <span v-if="hasUploadedDoc(docName)" class="text-success fw-bold">
                                                    <i class="bi-check-circle-fill me-1"></i> {{ getUploadedDocName(docName) }}
                                                </span>
                                                <span v-else class="text-muted">
                                                    <i class="bi-info-circle me-1"></i> Non fourni (Recommandé)
                                                </span>
                                            </div>
                                        </div>
                                        <div>
                                            <label :for="`file-req-${idx}`" class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1 m-0 text-nowrap" style="cursor: pointer; font-size: 11px;">
                                                Déposer
                                            </label>
                                            <input type="file" :id="`file-req-${idx}`" class="d-none" @change="(e) => handleRequiredFileUpload(e, docName)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <p class="text-muted small mt-4">Autres fichiers ou pièces justificatives additionnelles :</p>
                        <div class="mb-3">
                            <input type="file" multiple @change="handleFileUploads" class="form-control form-control-lg bg-light border-0">
                        </div>
                        
                        <!-- Liste générale des documents ajoutés -->
                        <div v-if="form.documents.length">
                            <h6 class="fw-bold text-dark small mb-2">Fichiers à envoyer :</h6>
                            <ul class="list-group list-group-flush mb-4 border rounded-3 bg-white p-2">
                                <li v-for="(fileObj, i) in form.documents" :key="i" class="list-group-item bg-transparent px-3 py-2 d-flex justify-content-between align-items-center border-bottom-0">
                                    <span class="small text-truncate" style="max-width: 85%;">
                                        <i class="bi-file-earmark-text me-2 text-primary"></i>
                                        <strong>[{{ fileObj.label }}]</strong> {{ fileObj.file.name }}
                                    </span>
                                    <button type="button" @click="removeDocument(i)" class="btn btn-sm text-danger p-1 border-0 bg-transparent"><i class="bi-trash"></i></button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Etape Finale: Récapitulatif -->
                    <div v-if="currentStep === totalSteps">
                        <h2 class="h4 fw-bold text-dark mb-4">Récapitulatif de votre demande</h2>
                        <div class="bg-light p-4 rounded-4 mb-4 border border-light-subtle">
                            <dl class="row mb-0 gy-3">
                                <dt class="col-sm-4 text-muted fw-normal">Service</dt>
                                <dd class="col-sm-8 fw-bold text-dark mb-0">{{ service.nom }}</dd>
                                
                                <dt class="col-sm-4 text-muted fw-normal">Catégorie</dt>
                                <dd class="col-sm-8 text-dark mb-0">{{ service.category?.nom }}</dd>
                                
                                <dt class="col-sm-4 text-muted fw-normal mt-4">Tarif estimé</dt>
                                <dd class="col-sm-8 fw-bold text-primary fs-5 mt-4 mb-0">
                                    <span v-if="service.tarif_type === 'fixe'">{{ service.tarif_fcfa?.toLocaleString('fr-FR') }} FCFA</span>
                                    <span v-else>Sur devis</span>
                                </dd>
                            </dl>
                        </div>
                        <p class="small text-muted mb-0">En soumettant cette demande, vous acceptez nos conditions générales de service. Notre équipe vous contactera dans les plus brefs délais.</p>
                    </div>

                    <!-- Actions -->
                    <div class="mt-5 pt-4 border-top d-flex justify-content-between align-items-center">
                        <button v-if="currentStep > 1" type="button" @click="prevStep" class="btn btn-outline-secondary px-4 rounded-pill">
                            <i class="bi-arrow-left me-2"></i>Précédent
                        </button>
                        <div v-else></div> <!-- Spacer -->

                        <button v-if="currentStep < totalSteps" type="button" @click="nextStep" class="btn btn-primary btn-gel px-5 rounded-pill shadow-sm">
                            Suivant <i class="bi-arrow-right ms-2"></i>
                        </button>

                        <button v-if="currentStep === totalSteps" type="submit" :disabled="processing" class="btn btn-success px-5 rounded-pill shadow-sm bg-success border-success text-white">
                            <span v-if="processing"><i class="spinner-border spinner-border-sm me-2"></i>En cours...</span>
                            <span v-else>Soumettre la demande <i class="bi-check2-circle ms-2"></i></span>
                        </button>
                    </div>

                </form>
            </div>
            
            <div class="mt-5 text-center">
                <a href="/nos-services" class="text-decoration-none text-muted hover-primary transition-colors">
                    <i class="bi-arrow-left me-1"></i> Retour au catalogue
                </a>
            </div>
        </div>
    </div>
</template>

<style scoped>
.font-heading {
    font-family: 'Outfit', sans-serif;
}
.btn-gel {
    background: #FF7900 !important;
    border: 1px solid #FF7900 !important;
    color: white !important;
    transition: all 0.3s ease;
}
.btn-gel:hover {
    background: #e06700 !important;
    border-color: #e06700 !important;
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(255, 121, 0, 0.2) !important;
}
.transition-colors {
    transition: color 0.3s ease;
}
.hover-primary:hover {
    color: #FF7900 !important;
}
.form-control:focus {
    box-shadow: none;
    border-color: #FF7900;
    background-color: #fff !important;
    border: 1px solid #FF7900 !important;
}
.btn-outline-primary {
    color: #FF7900 !important;
    border-color: #FF7900 !important;
}
.btn-outline-primary:hover {
    background: #FF7900 !important;
    color: white !important;
}
.text-primary {
    color: #FF7900 !important;
}
.progress-bar {
    background-color: #FF7900 !important;
}
.btn-outline-secondary {
    border-color: #163A5E !important;
    color: #163A5E !important;
}
.btn-outline-secondary:hover {
    background-color: #163A5E !important;
    color: white !important;
}
</style>
