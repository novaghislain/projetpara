<script setup>
import { ref, reactive, computed, watch, onBeforeUnmount } from 'vue';
import {
    Sparkles, Camera, Upload, ChevronRight, ChevronLeft,
    CheckCircle2, AlertCircle, Loader2, Star, Droplets,
    Sun, Shield, Activity, Heart, Leaf, Scan, Zap,
    ShoppingCart, ArrowRight, Award, Target,
    User, Eye, Search, AlertTriangle, Download,
    Info, FileText, ShoppingBag, X, Plus, Minus,
    Smile, Frown, Meh
} from 'lucide-vue-next';
import PremiumLayout from '../Components/PremiumLayout.vue';
import AIVisualBackground from '../Components/AIVisualBackground.vue';
import { usePage } from '@inertiajs/vue3';

// Auth check — redirect to login if not authenticated
const { auth } = usePage().props;
if (!auth || !auth.user) {
    window.location.href = '/login?redirect=/consultation';
}

// ========== ÉTAT GÉNÉRAL ==========
const step = ref(1);
const isProcessing = ref(false);
const result = ref(null);
const error = ref(null);
const cameraStream = ref(null);
const videoElement = ref(null);
const capturedImage = ref(null);
const fileInput = ref(null);
const searchResultInput = ref(null);
const processingMessage = ref('Analyse en cours...');

// ========== AUTO-CAPTURE ==========
const autoCapture = ref(false);
const countdown = ref(0);
const countdownInterval = ref(null);
const scanningOverlay = ref(false);
const detectedArea = ref('');

const form = reactive({
    analysis_type: '',
    visage_brille: '',
    visage_tiraillements: '',
    visage_boutons: '',
    visage_teint_terne: '',
    visage_sensible: '',
    visage_probleme: '',
    corps_type: '',
    corps_teint: '',
    corps_terne: '',
    corps_rugueux: '',
    corps_problemes: '',
    corps_objectif: '',
    photo: null
});

// ========== RECHERCHE PRODUITS ==========
const showSearchPanel = ref(false);
const searchQuery = ref('');
const searchResults = ref([]);
const isSearching = ref(false);
const cartQuantities = ref({});

// ========== DOWNLOAD ==========
const showDownloadPanel = ref(false);

// ========== OPTIONS ==========
const analysisTypes = [
    { id: 'visage', label: 'Visage', icon: Eye, desc: 'Analyse détaillée du visage' },
    { id: 'corps', label: 'Corps', icon: Activity, desc: 'Analyse de la peau du corps' },
    { id: 'les_deux', label: 'Les deux', icon: User, desc: 'Analyse complète visage + corps' }
];

const visageProblemes = [
    { id: 'acne', label: 'Acné / Boutons' },
    { id: 'taches', label: 'Taches brunes' },
    { id: 'teint_irregulier', label: 'Teint irrégulier' },
    { id: 'peau_seche', label: 'Peau sèche' },
    { id: 'rides', label: 'Rides' },
];

const corpsTypes = [
    { id: 'normale', label: 'Normale' },
    { id: 'seche', label: 'Sèche' },
    { id: 'grasse', label: 'Grasse' },
];

const corpsTeints = [
    { id: 'uniforme', label: 'Uniforme' },
    { id: 'irregulier', label: 'Irrégulier' },
    { id: 'taches', label: 'Avec taches' },
];

const corpsProblemes = [
    { id: 'taches', label: 'Taches' },
    { id: 'vergetures', label: 'Vergetures' },
    { id: 'boutons', label: 'Boutons' },
    { id: 'terne', label: 'Teint terne' },
];

const corpsObjectifs = [
    { id: 'eclaircir', label: 'Éclaircir' },
    { id: 'hydrater', label: 'Hydrater en profondeur' },
    { id: 'lisser', label: 'Lisser la peau' },
];

// ========== COMPUTED ==========
const canProceed = computed(() => {
    switch (step.value) {
        case 1: return !!form.analysis_type;
        case 2:
            if (showsVisage.value) return form.visage_brille && form.visage_tiraillements;
            return true;
        case 3:
            if (showsCorps.value) return !!form.corps_type;
            return true;
        case 4: return true;
        default: return false;
    }
});

const showsVisage = computed(() => form.analysis_type === 'visage' || form.analysis_type === 'les_deux');
const showsCorps = computed(() => form.analysis_type === 'corps' || form.analysis_type === 'les_deux');

// ========== CAMÉRA AVEC AUTO-CAPTURE ==========
const startCamera = async () => {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } }
        });
        cameraStream.value = stream;
        if (videoElement.value) {
            videoElement.value.srcObject = stream;
            videoElement.value.onloadedmetadata = () => {
                videoElement.value.play();
                startAutoDetection();
            };
        }
    } catch (err) {
        clearInterval(phaseInterval);
        error.value = "Impossible d'accéder à la caméra. Veuillez télécharger une photo.";
    }
};

const startAutoDetection = () => {
    scanningOverlay.value = true;
    detectedArea.value = 'Détection en cours...';

    // Simule une détection de zone après 2 secondes
    let detectionTimer = setTimeout(() => {
        const areas = ['Visage détecté', 'Zone peau détectée', 'Épiderme analysé'];
        detectedArea.value = areas[Math.floor(Math.random() * areas.length)];
        scanningOverlay.value = false;

        // Auto-capture après détection
        autoCaptureCountdown();
    }, 2000);

    // Stocker le timer pour le cleanup
    countdownInterval.value = detectionTimer;
};

const autoCaptureCountdown = () => {
    autoCapture.value = true;
    countdown.value = 3;

    countdownInterval.value = setInterval(() => {
        countdown.value--;
        if (countdown.value <= 0) {
            clearInterval(countdownInterval.value);
            countdownInterval.value = null;
            takeAutoPhoto();
        }
    }, 1000);
};

const takeAutoPhoto = () => {
    if (!videoElement.value) return;

    const canvas = document.createElement('canvas');
    canvas.width = videoElement.value.videoWidth;
    canvas.height = videoElement.value.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(videoElement.value, 0, 0);

    canvas.toBlob((blob) => {
        form.photo = blob;
        capturedImage.value = URL.createObjectURL(blob);
        stopCamera();
        autoCapture.value = false;
    }, 'image/jpeg');
};

const stopCamera = () => {
    if (countdownInterval.value) {
        clearInterval(countdownInterval.value);
        countdownInterval.value = null;
    }
    if (cameraStream.value) {
        cameraStream.value.getTracks().forEach(track => track.stop());
        cameraStream.value = null;
    }
    scanningOverlay.value = false;
    autoCapture.value = false;
};

const capturePhoto = () => {
    if (!videoElement.value) return;

    if (countdownInterval.value) {
        clearInterval(countdownInterval.value);
        countdownInterval.value = null;
    }

    const canvas = document.createElement('canvas');
    canvas.width = videoElement.value.videoWidth;
    canvas.height = videoElement.value.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(videoElement.value, 0, 0);

    canvas.toBlob((blob) => {
        form.photo = blob;
        capturedImage.value = URL.createObjectURL(blob);
        stopCamera();
    }, 'image/jpeg');
};

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.photo = file;
        capturedImage.value = URL.createObjectURL(file);
    }
};

onBeforeUnmount(() => {
    stopCamera();
});

// ========== SOUMISSION ==========
const submitConsultation = async () => {
    isProcessing.value = true;
    error.value = null;

    const formData = new FormData();
    const answers = {};

    if (form.analysis_type === 'visage' || form.analysis_type === 'les_deux') {
        answers.visage_brille = form.visage_brille;
        answers.visage_tiraillements = form.visage_tiraillements;
        answers.visage_boutons = form.visage_boutons;
        answers.visage_teint_terne = form.visage_teint_terne;
        answers.visage_sensible = form.visage_sensible;
        answers.visage_probleme = form.visage_probleme;
    }
    if (form.analysis_type === 'corps' || form.analysis_type === 'les_deux') {
        answers.corps_type = form.corps_type;
        answers.corps_teint = form.corps_teint;
        answers.corps_terne = form.corps_terne;
        answers.corps_rugueux = form.corps_rugueux;
        answers.corps_problemes = form.corps_problemes;
        answers.corps_objectif = form.corps_objectif;
    }
    answers.probleme_principal = form.visage_probleme || form.corps_problemes || '';

    formData.append('analysis_type', form.analysis_type);
    formData.append('answers', JSON.stringify(answers));
    if (form.photo) formData.append('photo', form.photo);

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    // Phase tracking for processing UI
    const processingPhases = [
        "Analyse du questionnaire en cours...",
        "Detection des conditions cutanees...",
        "Analyse photo par Intelligence Artificielle...",
        "Calcul du score cutane...",
        "Generation des recommandations...",
    ];
    let phaseIndex = 0;
    const phaseInterval = setInterval(() => {
        phaseIndex = (phaseIndex + 1) % processingPhases.length;
        processingMessage.value = processingPhases[phaseIndex];
    }, 800);

    try {
        const response = await fetch('/api/consultations', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            }
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || 'Échec de l\'analyse');
        }

        const data = await response.json();
        result.value = data;

        // Initialiser les quantités pour les produits recommandés
        if (data.products) {
            data.products.forEach(p => {
                cartQuantities.value[p.id] = 1;
            });
        }

        clearInterval(phaseInterval);
        step.value = 6;
    } catch (err) {
        clearInterval(phaseInterval);
        error.value = "Une erreur est survenue : " + err.message;
    } finally {
        isProcessing.value = false;
    }
};

// ========== NAVIGATION ==========
const nextStep = () => { if (step.value < 5) step.value++; };
const prevStep = () => { if (step.value > 1) step.value--; };
const navigateTo = (url) => { window.location.href = url; };

// ========== RECHERCHE PRODUITS ==========
const toggleSearchPanel = () => {
    showSearchPanel.value = !showSearchPanel.value;
    if (showSearchPanel.value) {
        searchQuery.value = '';
        searchResults.value = [];
    }
};

const searchProducts = async () => {
    if (!searchQuery.value.trim()) return;
    isSearching.value = true;

    try {
        const response = await fetch(`/api/products/search?q=${encodeURIComponent(searchQuery.value)}`, {
            headers: { 'Accept': 'application/json' }
        });
        const data = await response.json();
        searchResults.value = data;

        data.forEach(p => {
            if (!cartQuantities.value[p.id]) cartQuantities.value[p.id] = 1;
        });
    } catch (err) {
        clearInterval(phaseInterval);
        console.error('Search error:', err);
    } finally {
        isSearching.value = false;
    }
};

const searchRecommendedProduct = (productName) => {
    showSearchPanel.value = true;
    searchQuery.value = productName;
    setTimeout(() => searchProducts(), 300);
};

const getProductLink = (productId) => `/product/${productId}`;
const getCheckoutLink = (productId, qty = 1) => `/checkout?id=${productId}&qty=${qty}`;

// ========== DOWNLOAD ==========
const generateDiagnosticText = () => {
    if (!result.value) return '';

    let text = '=== DIAGNOSTIC VICTOIRE PARAPHARMACIE ===\n\n';
    text += `Date : ${new Date().toLocaleDateString('fr-FR')}\n`;
    text += `Zone analysée : ${result.diagnostic.zone}\n`;
    text += `Type de peau : ${result.diagnostic.type_peau}\n`;
    text += `État : ${result.diagnostic.etat.join(', ') || 'Normal'}\n`;
    text += `Problème principal : ${result.diagnostic.probleme_principal}\n`;
    text += `Condition : ${result.condition}\n`;
    text += `Score global : ${result.skin_score.global}/10\n\n`;

    if (result.conditions && result.conditions.length > 0) {
        text += '--- CONDITIONS DÉTECTÉES ---\n\n';
        result.conditions.forEach((c, i) => {
            text += `${i + 1}. ${c.nom}\n`;
            text += `   ${c.description}\n`;
            text += `   CAUSES :\n`;
            c.causes.forEach(cause => text += `   • ${cause}\n`);
            text += `   REMÈDES :\n`;
            c.remedes.forEach(rem => text += `   • ${rem}\n`);
            text += '\n';
        });
    }

    text += '--- ROUTINE RECOMMANDÉE ---\n\n';
    result.routine.forEach((r, i) => {
        text += `${i + 1}. ${r}\n`;
    });

    if (result.products && result.products.length > 0) {
        text += '\n--- PRODUITS RECOMMANDÉS ---\n\n';
        result.products.forEach(p => {
            text += `• ${p.name} - ${p.price.toLocaleString()} FCFA\n`;
        });
    }

    text += '\n\nCe diagnostic est un conseil cosmétique. Il ne remplace pas l\'avis d\'un dermatologue.\n';
    text += '© Victoire Parapharmacie - https://victoirepara.bj\n';

    return text;
};

const downloadDiagnostic = () => {
    const text = generateDiagnosticText();
    const blob = new Blob([text], { type: 'text/plain;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `diagnostic-victoire-${Date.now()}.txt`;
    a.click();
    URL.revokeObjectURL(url);
};

const printDiagnostic = () => {
    const w = window.open('', '_blank');
    if (!w) return;
    w.document.write(`<html><head><title>Diagnostic Victoire</title>
        <style>body{font-family:sans-serif;padding:40px;max-width:800px;margin:auto;}
        h1{color:#1b5e20;}h2{color:#333;border-bottom:2px solid #1b5e20;padding-bottom:5px;}
        .score{display:inline-block;background:#1b5e20;color:white;padding:10px 20px;border-radius:50px;font-size:24px;}
        .condition{border-left:4px solid #1b5e20;padding-left:15px;margin:20px 0;}
        .product{border:1px solid #ddd;padding:10px;margin:5px 0;border-radius:5px;}
        footer{margin-top:40px;color:#999;font-size:12px;}
        @media print{body{padding:20px;}}</style></head><body>`);
    w.document.write(`<h1>Diagnostic Victoire Parapharmacie</h1>`);
    w.document.write(`<p>Date : ${new Date().toLocaleDateString('fr-FR')} | Zone : ${result.value.diagnostic.zone}</p>`);
    w.document.write(`<p><strong>Condition :</strong> ${result.value.condition}</p>`);
    w.document.write(`<p><span class="score">${result.value.skin_score.global}/10</span> Score global</p>`);
    w.document.write(`<h2>Profil</h2><ul>`);
    w.document.write(`<li>Type : ${result.value.diagnostic.type_peau}</li>`);
    w.document.write(`<li>État : ${result.value.diagnostic.etat.join(', ') || 'Normal'}</li>`);
    w.document.write(`<li>Problème : ${result.value.diagnostic.probleme_principal}</li></ul>`);

    if (result.value.conditions) {
        w.document.write(`<h2>Conditions Détectées</h2>`);
        result.value.conditions.forEach(c => {
            w.document.write(`<div class="condition"><h3>${c.nom}</h3><p>${c.description}</p>`);
            w.document.write(`<h4>Causes :</h4><ul>${c.causes.map(ca => `<li>${ca}</li>`).join('')}</ul>`);
            w.document.write(`<h4>Remèdes :</h4><ul>${c.remedes.map(r => `<li>${r}</li>`).join('')}</ul></div>`);
        });
    }

    w.document.write(`<h2>Routine</h2><ol>`);
    result.value.routine.forEach(r => w.document.write(`<li>${r}</li>`));
    w.document.write(`</ol>`);

    w.document.write(`<footer>Diagnostic cosmétique - © Victoire Parapharmacie</footer>`);
    w.document.write(`</body></html>`);
    w.document.close();
    w.print();
};

// ========== SCORE VISUALS ==========
const scoreColor = (val) => {
    if (val >= 8) return '#1b5e20';
    if (val >= 6) return '#689f38';
    if (val >= 4) return '#f9a825';
    return '#e53935';
};

const scoreEmoji = (val) => {
    if (val >= 8) return 'Excellent';
    if (val >= 6) return 'Bon';
    if (val >= 4) return 'Moyen';
    return 'A améliorer';
};

const scoreLabel = (val) => {
    if (val >= 8) return 'Excellent';
    if (val >= 6) return 'Bon';
    if (val >= 4) return 'Moyen';
    return 'À améliorer';
};

const formatTypePeau = (type) => {
    const map = { grasse: 'Grasse', seche: 'Sèche', mixte: 'Mixte', normale: 'Normale' };
    return map[type] || type;
};

const severityColor = (sev) => {
    const map = { 'Excellent': 'success', 'Normal': 'success', 'Léger': 'warning', 'Nécessite attention': 'danger' };
    return map[sev] || 'secondary';
};
</script>

<template>
  <PremiumLayout title="Diagnostic IA - Victoire Parapharmacie">
    <div class="min-h-screen bg-light position-relative pt-5 pb-5">
      <AIVisualBackground v-if="step === 5 && isProcessing" />

      <div class="container py-5 mt-5">
        <div class="row justify-content-center">
          <div class="col-lg-8 col-xl-7">

            <!-- ===== HEADER ===== -->
            <div class="text-center mb-5">
              <div class="d-inline-flex align-items-center gap-2 bg-primary bg-opacity-10 text-primary px-4 py-2 rounded-pill fw-bold small mb-3">
                <Sparkles :size="16" /> Diagnostic Intelligent Victoire
              </div>
              <h1 class="display-6 fw-bold text-dark mb-2">Analyse de Peau</h1>
              <p class="text-muted">Répondez aux questions pour obtenir votre diagnostic personnalisé</p>
              <div class="d-flex justify-content-center align-items-center gap-1 mt-3">
                <template v-for="s in 5" :key="s">
                  <div class="rounded-pill transition-all"
                       :style="{ width: step === s ? '32px' : step > s ? '12px' : '8px', height: step === s ? '12px' : '8px', backgroundColor: step >= s ? 'var(--bs-primary)' : '#dee2e6', transition: 'all 0.3s ease' }">
                  </div>
                  <div v-if="s < 5" class="mx-1" style="width: 16px; height: 2px; background: step > s ? 'var(--bs-primary)' : '#dee2e6'"></div>
                </template>
              </div>
            </div>

            <!-- ===== STEP 1: CHOIX ===== -->
            <div v-if="step === 1" class="card border-0 shadow-premium rounded-5 p-4 p-md-5 animate-slide-up">
              <div class="text-center mb-4">
                <h3 class="fw-bold">Que souhaitez-vous analyser ?</h3>
                <p class="text-muted small">Choisissez la zone à diagnostiquer</p>
              </div>
              <div class="row g-3">
                <div v-for="at in analysisTypes" :key="at.id" class="col-md-4 col-12">
                  <div @click="form.analysis_type = at.id"
                       :class="['card h-100 border-2 rounded-4 p-4 text-center cursor-pointer transition-all hover-shadow', form.analysis_type === at.id ? 'border-primary bg-primary bg-opacity-5' : 'border-light bg-white']">
                    <div class="d-flex justify-content-center mb-3">
                      <div class="rounded-circle d-flex align-items-center justify-content-center"
                           :class="form.analysis_type === at.id ? 'bg-primary' : 'bg-light'" style="width: 64px; height: 64px">
                        <component :is="at.icon" :size="28" :class="form.analysis_type === at.id ? 'text-white' : 'text-primary'" />
                      </div>
                    </div>
                    <h5 class="fw-bold mb-1">{{ at.label }}</h5>
                    <p class="text-muted small mb-0">{{ at.desc }}</p>
                  </div>
                </div>
              </div>
              <div class="mt-5 d-flex justify-content-end">
                <button @click="nextStep" :disabled="!canProceed"
                        class="btn btn-primary px-5 py-3 rounded-pill fw-bold d-flex align-items-center gap-2 shadow-lg">
                  Continuer <ChevronRight :size="20" />
                </button>
              </div>
            </div>

            <!-- ===== STEP 2: VISAGE ===== -->
            <div v-if="step === 2 && showsVisage" class="card border-0 shadow-premium rounded-5 p-4 p-md-5 animate-slide-up">
              <div class="d-flex align-items-center gap-3 mb-4">
                <div class="bg-primary bg-opacity-10 p-3 rounded-4 text-primary"><Eye :size="28" /></div>
                <div><h3 class="fw-bold mb-1">Analyse Visage</h3><p class="text-muted small mb-0">Parlez-nous de votre peau</p></div>
              </div>

              <div class="mb-4">
                <h5 class="fw-bold mb-3">Type de peau</h5>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold small">Ta peau brille-t-elle ?</label>
                    <div class="d-flex gap-2">
                      <button @click="form.visage_brille = 'oui'" :class="['btn flex-fill rounded-pill border-2 px-4 py-2 fw-semibold transition-all', form.visage_brille === 'oui' ? 'btn-primary' : 'btn-outline-secondary']">Oui</button>
                      <button @click="form.visage_brille = 'non'" :class="['btn flex-fill rounded-pill border-2 px-4 py-2 fw-semibold transition-all', form.visage_brille === 'non' ? 'btn-primary' : 'btn-outline-secondary']">Non</button>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold small">Ressens-tu des tiraillements ?</label>
                    <div class="d-flex gap-2">
                      <button @click="form.visage_tiraillements = 'oui'" :class="['btn flex-fill rounded-pill border-2 px-4 py-2 fw-semibold transition-all', form.visage_tiraillements === 'oui' ? 'btn-primary' : 'btn-outline-secondary']">Oui</button>
                      <button @click="form.visage_tiraillements = 'non'" :class="['btn flex-fill rounded-pill border-2 px-4 py-2 fw-semibold transition-all', form.visage_tiraillements === 'non' ? 'btn-primary' : 'btn-outline-secondary']">Non</button>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mb-4">
                <h5 class="fw-bold mb-3">Etat de la peau</h5>
                <div class="row g-3">
                  <div v-for="(item, key) in {visage_boutons: 'Boutons?', visage_teint_terne: 'Teint terne?', visage_sensible: 'Sensible?'}" :key="key" class="col-md-4">
                    <div @click="form[key] = form[key] === 'oui' ? 'non' : 'oui'"
                         :class="['card border-2 rounded-4 p-3 text-center cursor-pointer transition-all', form[key] === 'oui' ? 'border-primary bg-primary bg-opacity-5' : 'border-light']">
                      <span class="fs-1">{{ item.split(' ')[0] }}</span>
                      <p class="fw-bold small mb-0 mt-1">{{ item.split(' ').slice(1).join(' ') }}</p>
                      <span class="x-small" :class="form[key] === 'oui' ? 'text-primary' : 'text-muted'">{{ form[key] === 'oui' ? 'Oui' : 'Non' }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div>
                <h5 class="fw-bold mb-3">Problème principal</h5>
                <div class="row g-2">
                  <div v-for="p in visageProblemes" :key="p.id" class="col-6 col-md-4">
                    <div @click="form.visage_probleme = p.id"
                         :class="['card border-2 rounded-4 p-3 text-center cursor-pointer transition-all h-100 d-flex align-items-center justify-content-center', form.visage_probleme === p.id ? 'border-primary bg-primary bg-opacity-10 shadow-sm' : 'border-light bg-white']">
                      <span class="fs-3 d-block mb-1">{{ p.icon }}</span>
                      <p class="fw-bold small mb-0">{{ p.label }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-5 d-flex justify-content-between">
                <button @click="prevStep" class="btn btn-outline-dark px-4 py-3 rounded-pill fw-bold">Retour</button>
                <button @click="nextStep" :disabled="!canProceed"
                        class="btn btn-primary px-5 py-3 rounded-pill fw-bold d-flex align-items-center gap-2 shadow-lg">
                  {{ showsCorps ? 'Suite Corps' : 'Photo' }} <ChevronRight :size="20" />
                </button>
              </div>
            </div>

            <!-- ===== STEP 3: CORPS ===== -->
            <div v-if="step === 3 && showsCorps" class="card border-0 shadow-premium rounded-5 p-4 p-md-5 animate-slide-up">
              <div class="d-flex align-items-center gap-3 mb-4">
                <div class="bg-primary bg-opacity-10 p-3 rounded-4 text-primary"><Activity :size="28" /></div>
                <div><h3 class="fw-bold mb-1">Analyse Corps</h3><p class="text-muted small mb-0">État de votre peau corporelle</p></div>
              </div>

              <div class="mb-4">
                <h5 class="fw-bold mb-3">Type de peau du corps</h5>
                <div class="row g-2">
                  <div v-for="ct in corpsTypes" :key="ct.id" class="col-md-4">
                    <div @click="form.corps_type = ct.id"
                         :class="['card border-2 rounded-4 p-3 text-center cursor-pointer transition-all', form.corps_type === ct.id ? 'border-primary bg-primary bg-opacity-5' : 'border-light']">
                      <span class="fs-2">{{ ct.icon }}</span>
                      <p class="fw-bold mb-0">{{ ct.label }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mb-4">
                <h5 class="fw-bold mb-3">Teint de la peau</h5>
                <div class="row g-2">
                  <div v-for="t in corpsTeints" :key="t.id" class="col-md-4">
                    <div @click="form.corps_teint = t.id"
                         :class="['card border-2 rounded-4 p-3 text-center cursor-pointer transition-all', form.corps_teint === t.id ? 'border-primary bg-primary bg-opacity-5' : 'border-light']">
                      <span class="fs-2">{{ t.icon }}</span>
                      <p class="fw-bold mb-0">{{ t.label }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mb-4">
                <h5 class="fw-bold mb-3">Etat de la peau</h5>
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="d-flex gap-2">
                      <button @click="form.corps_terne = form.corps_terne === 'oui' ? 'non' : 'oui'"
                              :class="['btn flex-fill rounded-pill border-2 px-4 py-2 fw-semibold transition-all', form.corps_terne === 'oui' ? 'btn-primary' : 'btn-outline-secondary']">
                        {{ form.corps_terne === 'oui' ? 'Terne' : 'Peau terne ?' }}
                      </button>
                      <button @click="form.corps_rugueux = form.corps_rugueux === 'oui' ? 'non' : 'oui'"
                              :class="['btn flex-fill rounded-pill border-2 px-4 py-2 fw-semibold transition-all', form.corps_rugueux === 'oui' ? 'btn-primary' : 'btn-outline-secondary']">
                        {{ form.corps_rugueux === 'oui' ? 'Rugueuse' : 'Rugueuse ?' }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mb-4">
                <h5 class="fw-bold mb-3">Problèmes & Objectif</h5>
                <div class="row g-2">
                  <div v-for="p in corpsProblemes" :key="p.id" class="col-6 col-md-3">
                    <div @click="form.corps_problemes = form.corps_problemes === p.id ? '' : p.id"
                         :class="['card border-2 rounded-4 p-3 text-center cursor-pointer transition-all h-100', form.corps_problemes === p.id ? 'border-primary bg-primary bg-opacity-5' : 'border-light']">
                      <span class="fs-2">{{ p.icon }}</span>
                      <p class="fw-bold small mb-0">{{ p.label }}</p>
                    </div>
                  </div>
                </div>
              </div>
              <div>
                <h5 class="fw-bold mb-3">Objectif</h5>
                <div class="row g-2">
                  <div v-for="o in corpsObjectifs" :key="o.id" class="col-md-4">
                    <div @click="form.corps_objectif = form.corps_objectif === o.id ? '' : o.id"
                         :class="['card border-2 rounded-4 p-3 text-center cursor-pointer transition-all', form.corps_objectif === o.id ? 'border-primary bg-primary bg-opacity-5' : 'border-light']">
                      <span class="fs-2">{{ o.icon }}</span>
                      <p class="fw-bold mb-0">{{ o.label }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-5 d-flex justify-content-between">
                <button @click="prevStep" class="btn btn-outline-dark px-4 py-3 rounded-pill fw-bold">Retour</button>
                <button @click="nextStep" :disabled="!canProceed"
                        class="btn btn-primary px-5 py-3 rounded-pill fw-bold d-flex align-items-center gap-2 shadow-lg">
                  Photo <ChevronRight :size="20" />
                </button>
              </div>
            </div>

            <!-- ===== STEP 4: PHOTO AVEC AUTO-CAPTURE ===== -->
            <div v-if="step === 4" class="card border-0 shadow-premium rounded-5 p-4 p-md-5 animate-slide-up">
              <div class="text-center mb-4">
                <div class="d-flex justify-content-center mb-3">
                  <div class="bg-primary bg-opacity-10 p-3 rounded-4 text-primary d-inline-flex"><Camera :size="32" /></div>
                </div>
                <h3 class="fw-bold">Photo de la zone</h3>
                <p class="text-muted small">Prenez ou importez une photo pour une analyse précise</p>
              </div>

              <!-- Instructions -->
              <div class="bg-light rounded-4 p-3 mb-4">
                <p class="small text-muted mb-1"><strong>Instructions :</strong></p>
                <ul class="small text-muted mb-0 ps-3">
                  <li>Sans filtre ni maquillage</li>
                  <li>Lumière naturelle de préférence</li>
                  <li>Zone bien dégagée et visible</li>
                </ul>
              </div>

              <!-- Zone caméra / photo -->
              <div class="bg-light rounded-5 p-4 text-center border-dashed border-2 position-relative overflow-hidden mb-4" style="min-height: 350px">
                <!-- Caméra active -->
                <div v-if="cameraStream" class="w-100 h-100 position-relative">
                  <video ref="videoElement" autoplay playsinline class="w-100 rounded-4 shadow-sm" style="max-height: 400px; object-fit: cover;"></video>

                  <!-- Overlay de détection -->
                  <div v-if="scanningOverlay" class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-dark bg-opacity-50 rounded-4">
                    <div class="spinner-border text-white mb-3" role="status" style="width: 48px; height: 48px;"></div>
                    <p class="text-white fw-bold mb-2">Analyse de la zone...</p>
                    <p class="text-white-50 small">{{ detectedArea }}</p>
                    <!-- Scanner line animation -->
                    <div class="position-absolute scanner-line"></div>
                  </div>

                  <!-- Compte à rebours auto-capture -->
                  <div v-if="autoCapture" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                    <div class="bg-dark bg-opacity-60 rounded-circle d-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
                      <span class="text-white display-3 fw-bold">{{ countdown }}</span>
                    </div>
                  </div>

                  <!-- Contrôles caméra -->
                  <div class="d-flex justify-content-center gap-3 mt-3">
                    <button @click="capturePhoto" class="btn btn-primary rounded-circle p-3 shadow-lg" :disabled="autoCapture">
                      <Camera :size="24" />
                    </button>
                    <button @click="stopCamera" class="btn btn-outline-danger rounded-pill px-3 small">Annuler</button>
                  </div>
                </div>

                <!-- Image capturée -->
                <div v-else-if="capturedImage" class="w-100">
                  <img :src="capturedImage" class="img-fluid rounded-4 shadow-sm" style="max-height: 350px; object-fit: contain;" />
                  <div class="d-flex justify-content-center gap-2 mt-3">
                    <button @click="capturedImage = null; form.photo = null" class="btn btn-outline-danger btn-sm rounded-pill">Effacer</button>
                    <button @click="startCamera" class="btn btn-outline-primary btn-sm rounded-pill">Reprendre</button>
                  </div>
                </div>

                <!-- État initial -->
                <div v-else class="d-flex flex-column align-items-center justify-content-center h-100 pt-5 mt-4">
                  <Camera :size="48" class="text-muted opacity-50 mb-3" />
                  <p class="text-muted mb-1">Activez la caméra pour une capture automatique</p>
                  <p class="text-muted small mb-4">Le système détectera automatiquement la zone et prendra la photo</p>
                  <div class="d-flex gap-3 flex-wrap justify-content-center">
                    <button @click="startCamera" class="btn btn-primary px-5 py-3 rounded-pill d-flex align-items-center gap-2 shadow-lg">
                      <Camera :size="20" /> Activer Caméra + Auto-Détection
                    </button>
                    <button @click="$refs.fileInput.click()" class="btn btn-outline-primary px-4 py-3 rounded-pill d-flex align-items-center gap-2">
                      <Upload :size="18" /> Importer une photo
                    </button>
                    <input ref="fileInput" type="file" class="d-none" @change="handleFileUpload" accept="image/*" />
                  </div>
                </div>
              </div>

              <div v-if="error" class="alert alert-danger rounded-4 d-flex align-items-center gap-2 mb-4">
                <AlertCircle :size="18" />
                <span class="small">{{ error }}</span>
              </div>

              <div class="d-flex justify-content-between">
                <button @click="prevStep" class="btn btn-outline-dark px-4 py-3 rounded-pill fw-bold">Retour</button>
                <button @click="submitConsultation" :disabled="isProcessing"
                        class="btn btn-success px-5 py-3 rounded-pill fw-bold d-flex align-items-center gap-2 shadow-lg">
                  <Loader2 v-if="isProcessing" class="animate-spin" :size="20" />
                  <span v-else>Lancer l'Analyse IA <Sparkles :size="20" /></span>
                </button>
              </div>
            </div>

            <!-- ===== STEP 5: PROCESSING ===== -->
            <div v-if="step === 5 && isProcessing" class="card border-0 shadow-premium rounded-5 p-4 p-md-5 animate-slide-up">
              <div class="text-center py-5">
                <div class="position-relative d-inline-block mb-4">
                  <div class="spinner-grow text-primary" style="width: 100px; height: 100px"></div>
                  <div class="position-absolute top-50 start-50 translate-middle"><Sparkles :size="40" class="text-primary animate-pulse" /></div>
                </div>
                <h3 class="fw-bold text-dark mb-2">Analyse en cours...</h3>
                <p class="text-muted mb-3">{{ processingMessage }}</p>
                <div class="d-flex justify-content-center gap-2 mt-3">
                  <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                  <span class="small text-muted">Perfect Corp : 16 HD categories </span>
                </div>
                <div class="mt-4 d-flex justify-content-center gap-2 flex-wrap">
                  <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 small">
                    Perfect Corp Skin Analysis
                  </span>
                  <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3 py-2 small">
                    HD : rides, pores, acne, rougeurs, eclat, ...
                  </span>
                </div>
              </div>
            </div>

            <!-- ===== STEP 6: RÉSULTATS ===== -->
            <div v-if="step === 6 && result" class="animate-slide-up">
              <!-- Bannière résultat -->
              <div class="card border-0 shadow-premium rounded-5 overflow-hidden mb-4">
                <div class="bg-primary p-4 p-md-5 text-white position-relative">
                  <div class="position-relative z-1">
                    <p class="text-white text-opacity-75 text-uppercase fw-bold small mb-2">
                      <CheckCircle2 :size="16" class="me-1" /> Analyse Terminée
                    </p>
                    <h2 class="fw-bold mb-1">Votre Diagnostic</h2>
                    <p class="text-white text-opacity-75 small mb-0">{{ result.condition }}</p>
                    <div v-if="result.ai_analysis && result.ai_analysis.ai_used" class="mt-2 d-flex gap-1 flex-wrap">
                      <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1" style="font-size: 9px;">
                        IA Perfect Corp
                      </span>
                      <span v-if="result.ai_analysis.ai_confidence" class="badge bg-gold text-white rounded-pill px-3 py-1" style="font-size: 9px;">
                        Confiance: {{ result.ai_analysis.ai_confidence }}%
                      </span>
                    </div>
                  </div>
                  <div class="position-absolute end-0 bottom-0 p-4 opacity-25"><Shield :size="120" /></div>
                </div>

                <div class="card-body p-4 p-md-5">
                  <div class="row g-4">
                    <div class="col-md-6">
                      <div class="p-4 bg-light rounded-4 h-100">
                        <h6 class="fw-bold text-uppercase small mb-3 text-primary"><User :size="14" class="me-1" /> Profil</h6>
                        <div class="d-flex align-items-center mb-2">
                          <span class="badge bg-primary rounded-pill me-2 text-uppercase" style="font-size: 9px">Type</span>
                          <span class="fw-bold text-dark">{{ formatTypePeau(result.diagnostic.type_peau) }}</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                          <span class="badge bg-success rounded-pill me-2 text-uppercase" style="font-size: 9px">État</span>
                          <span class="fw-bold text-dark text-capitalize">{{ result.diagnostic.etat.join(', ') || 'Normal' }}</span>
                        </div>
                        <div class="d-flex align-items-center">
                          <span class="badge bg-warning rounded-pill me-2 text-uppercase" style="font-size: 9px">Problème</span>
                          <span class="fw-bold text-dark text-capitalize">{{ result.diagnostic.probleme_principal }}</span>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="p-4 bg-light rounded-4 h-100">
                        <h6 class="fw-bold text-uppercase small mb-3 text-primary"><Target :size="14" class="me-1" /> Score Global</h6>
                        <div class="d-flex align-items-center gap-3">
                          <div class="rounded-circle d-flex align-items-center justify-content-center bg-white shadow-sm" style="width: 72px; height: 72px">
                            <span class="fw-bold h3 mb-0" :style="{ color: scoreColor(result.skin_score.global || 7) }">
                              {{ result.skin_score.global }}/10
                            </span>
                          </div>
                          <div>
                            <p class="fw-bold mb-1 h5">
                              <span :class="'badge bg-' + severityColor(result.severity) + ' rounded-pill'">{{ result.severity }}</span>
                            </p>
                            <p class="text-muted small mb-0">Santé cutanée globale</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- SCORES -->
              <div class="card border-0 shadow-premium rounded-5 p-4 p-md-5 mb-4">
                <h4 class="fw-bold mb-4 d-flex align-items-center gap-2">
                  <Award :size="24" class="text-primary" /> Score de Peau
                </h4>
                <div class="row g-3">
                  <div v-for="(score, key) in result.skin_score" :key="key" v-if="key !== 'global'"
                       class="col-6 col-md-3">
                    <div class="card border-0 bg-light rounded-4 p-3 text-center h-100">
                      <p class="small text-uppercase fw-bold text-muted mb-2" style="font-size: 10px; letter-spacing: 0.05em">
                        <template v-if="key === 'hydratation'">Hydratation</template>
                        <template v-else-if="key === 'eclat'">Eclat</template>
                        <template v-else-if="key === 'sensibilite'">Sensibilité</template>
                        <template v-else-if="key === 'imperfections'">Imperfections</template>
                      </p>
                      <div class="mb-2">
                        <span class="display-6 fw-bold" :style="{ color: scoreColor(score) }">{{ score }}</span>
                      </div>
                      <div class="progress rounded-pill" style="height: 6px;">
                        <div class="progress-bar rounded-pill" :style="{ width: score * 10 + '%', backgroundColor: scoreColor(score) }"></div>
                      </div>
                      <p class="x-small text-muted mt-2 mb-0">{{ scoreLabel(score) }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- CONDITIONS DÉTECTÉES (MALADIES/INFECTIONS) -->
              <div v-if="result.conditions && result.conditions.length > 0" class="card border-0 shadow-premium rounded-5 p-4 p-md-5 mb-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                  <div class="bg-danger bg-opacity-10 p-3 rounded-4 text-danger">
                    <AlertTriangle :size="24" />
                  </div>
                  <div>
                    <h4 class="fw-bold mb-1">Conditions Détectées</h4>
                    <p class="text-muted small mb-0">Notre analyse a identifié les éléments suivants</p>
                  </div>
                </div>

                <div class="d-grid gap-4">
                  <div v-for="(condition, ci) in result.conditions" :key="ci"
                       class="card border-0 bg-light rounded-4 overflow-hidden">
                    <!-- En-tête condition -->
                    <div class="p-4 pb-3 border-bottom border-white">
                      <div class="d-flex align-items-center justify-content-between">
                        <div>
                          <span class="badge rounded-pill mb-2"
                               :class="condition.type === 'infection' ? 'bg-danger' : condition.type === 'inflammation' ? 'bg-warning text-dark' : condition.type === 'pigmentation' ? 'bg-primary' : condition.type === 'deshydratation' ? 'bg-info text-dark' : 'bg-secondary'"
                               style="font-size: 8px; letter-spacing: 0.05em">
                            {{ condition.type === 'infection' ? 'INFECTION' : condition.type === 'inflammation' ? 'INFLAMMATION' : condition.type === 'pigmentation' ? 'PIGMENTATION' : condition.type === 'deshydratation' ? 'DESHYDRATATION' : condition.type === 'vasculaire' ? 'VASCULAIRE' : condition.type === 'sebum' ? 'SEBUM' : condition.type === 'cicatrice' ? 'CICATRICE' : condition.type === 'saine' ? 'SAINE' : 'AUTRE' }}
                          </span>
                          <h5 class="fw-bold mb-1">{{ condition.nom }}</h5>
                          <p class="small text-muted mb-0">{{ condition.description }}</p>
                        </div>
                        <div class="text-end flex-shrink-0">
                          <div class="rounded-circle bg-white d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px">
                            <span class="fw-bold h5 mb-0" :class="condition.score_confiance >= 70 ? 'text-danger' : condition.score_confiance >= 50 ? 'text-warning' : 'text-muted'">
                              {{ condition.score_confiance }}%
                            </span>
                          </div>
                          <p class="x-small text-muted mt-1 mb-0">Confiance</p>
                        </div>
                      </div>
                    </div>

                    <!-- Causes -->
                    <div class="p-4 border-bottom border-white">
                      <h6 class="fw-bold small text-uppercase mb-3 d-flex align-items-center gap-2">
                        <Info :size="14" class="text-primary" /> Pourquoi ce problème ?
                      </h6>
                      <div class="row g-2">
                        <div v-for="(cause, idx) in condition.causes" :key="idx" class="col-md-6">
                          <div class="d-flex align-items-start gap-2 p-2 bg-white rounded-3">
                            <span class="text-danger flex-shrink-0">•</span>
                            <span class="small text-dark">{{ cause }}</span>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Remèdes -->
                    <div class="p-4">
                      <h6 class="fw-bold small text-uppercase mb-3 d-flex align-items-center gap-2">
                        <Heart :size="14" class="text-success" /> Remèdes et Solutions
                      </h6>
                      <div class="row g-2">
                        <div v-for="(rem, idx) in condition.remedes" :key="idx" class="col-md-6">
                          <div class="d-flex align-items-start gap-2 p-2 bg-white rounded-3">
                            <span class="text-success flex-shrink-0">-</span>
                            <span class="small text-dark">{{ rem }}</span>
                          </div>
                        </div>
                      </div>

                      <!-- Lien vers les produits -->
                      <div class="mt-3 d-flex gap-2 flex-wrap">
                        <button @click="searchRecommendedProduct(condition.nom)"
                                class="btn btn-outline-primary btn-sm rounded-pill d-flex align-items-center gap-1">
                          <Search :size="12" /> Chercher des produits pour "{{ condition.nom }}"
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ROUTINE -->
              <div class="card border-0 shadow-premium rounded-5 p-4 p-md-5 mb-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                  <div class="bg-success bg-opacity-10 p-3 rounded-4 text-success"><Droplets :size="24" /></div>
                  <div><h4 class="fw-bold mb-1">Routine Recommandée</h4><p class="text-muted small mb-0">Par Victoire Parapharmacie</p></div>
                </div>
                <div class="d-grid gap-3">
                  <div v-for="(rec, i) in result.routine" :key="i"
                       class="d-flex gap-3 p-3 bg-light rounded-4 align-items-start">
                    <div class="bg-success d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 shadow-sm" style="width: 32px; height: 32px">
                      <span class="text-white fw-bold small">{{ i + 1 }}</span>
                    </div>
                    <p class="mb-0 fw-medium text-dark">{{ rec }}</p>
                  </div>
                </div>
              </div>

              <!-- PRODUITS RECOMMANDÉS AVEC RECHERCHE INTÉGRÉE -->
              <div class="card border-0 shadow-premium rounded-5 p-4 p-md-5 mb-4">
                <div class="d-flex align-items-center justify-content-between mb-4">
                  <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-4 text-primary"><ShoppingCart :size="24" /></div>
                    <div><h4 class="fw-bold mb-1">Produits Recommandés</h4><p class="text-muted small mb-0">Pour votre profil</p></div>
                  </div>
                  <button @click="toggleSearchPanel"
                          class="btn btn-outline-primary btn-sm rounded-pill d-flex align-items-center gap-1">
                    <Search :size="14" /> Rechercher
                  </button>
                </div>

                <!-- Panneau de recherche intégré -->
                <div v-if="showSearchPanel" class="bg-light rounded-4 p-4 mb-4 animate-slide-up">
                  <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0">Rechercher un produit</h6>
                    <button @click="showSearchPanel = false" class="btn btn-link btn-sm text-muted p-0"><X :size="18" /></button>
                  </div>
                  <div class="input-group mb-3">
                    <input v-model="searchQuery" type="text"
                           class="form-control rounded-start-pill border-2"
                           placeholder="Ex: Sérum Vitamine C, gel nettoyant..."
                           @keypress.enter="searchProducts" />
                    <button @click="searchProducts" :disabled="isSearching" class="btn btn-primary rounded-end-pill px-4">
                      <Loader2 v-if="isSearching" class="animate-spin" :size="16" />
                      <Search v-else :size="16" />
                    </button>
                  </div>

                  <!-- Résultats recherche -->
                  <div v-if="searchResults.length > 0" class="d-grid gap-2">
                    <div v-for="p in searchResults" :key="p.id"
                         class="d-flex align-items-center gap-3 p-2 bg-white rounded-3 shadow-sm">
                      <img :src="p.img" :alt="p.name" class="rounded-3" style="width: 48px; height: 48px; object-fit: cover;" />
                      <div class="flex-grow-1">
                        <p class="fw-bold small mb-0">{{ p.name }}</p>
                        <p class="text-primary fw-bold small mb-0">{{ p.price.toLocaleString() }} FCFA</p>
                      </div>
                      <div class="d-flex gap-1">
                        <a :href="getProductLink(p.id)" class="btn btn-outline-primary btn-sm rounded-pill">
                          <Eye :size="14" />
                        </a>
                        <a :href="getCheckoutLink(p.id, cartQuantities[p.id] || 1)" class="btn btn-primary btn-sm rounded-pill">
                          <ShoppingCart :size="14" />
                        </a>
                      </div>
                    </div>
                  </div>

                  <div v-if="searchQuery && !isSearching && searchResults.length === 0" class="text-center py-3">
                    <p class="text-muted small mb-2">Aucun produit trouvé pour "{{ searchQuery }}"</p>
                    <p class="text-muted small mb-0">Laissez vos coordonnées ou téléchargez votre diagnostic pour nous contacter</p>
                  </div>
                </div>

                <!-- Grille produits -->
                <div class="row g-3">
                  <div v-for="p in result.products" :key="p.id" class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 bg-white rounded-4 shadow-sm overflow-hidden hover-shadow transition-all">
                      <div class="position-relative">
                        <img :src="p.img" :alt="p.name" class="img-fluid w-100" style="height: 160px; object-fit: cover;" />
                        <div v-if="p.is_victoire" class="position-absolute top-0 start-0 m-2">
                          <span class="badge bg-gold text-white rounded-pill shadow-sm" style="font-size: 8px;">VICTOIRE</span>
                        </div>
                      </div>
                      <div class="card-body p-3">
                        <h6 class="fw-bold small mb-1 text-truncate">{{ p.name }}</h6>
                        <p class="text-primary fw-bold mb-1">{{ p.price.toLocaleString() }} FCFA</p>
                        <div class="d-flex align-items-center text-warning mb-2 small">
                          <Star v-for="s in 5" :key="s" :size="10" :fill="s <= p.rating ? 'currentColor' : 'none'" />
                        </div>
                        <div class="d-flex flex-wrap gap-1">
                          <a :href="getProductLink(p.id)" class="btn btn-outline-primary btn-sm rounded-pill flex-fill">Détails</a>
                          <a :href="getCheckoutLink(p.id, cartQuantities[p.id] || 1)" class="btn btn-primary btn-sm rounded-pill d-flex align-items-center justify-content-center gap-1 flex-fill">
                            <ShoppingCart :size="14" /> Acheter
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- MENTION LÉGALE + DOWNLOAD -->
              <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-4">
                <div class="card border-0 bg-warning bg-opacity-10 rounded-4 p-3 flex-grow-1">
                  <p class="small text-muted mb-0 d-flex align-items-center gap-2">
                    <Shield :size="14" class="flex-shrink-0" />
                    Ce diagnostic est un conseil cosmétique basé sur vos réponses. Il ne remplace pas l'avis d'un dermatologue.
                  </p>
                </div>
              </div>

              <!-- Boutons d'action -->
              <div class="d-flex flex-wrap gap-3 justify-content-center mb-5">
                <!-- Télécharger -->
                <button @click="downloadDiagnostic"
                        class="btn btn-outline-dark px-4 py-3 rounded-pill fw-bold d-flex align-items-center gap-2">
                  <Download :size="20" /> Télécharger (.txt)
                </button>

                <!-- Imprimer -->
                <button @click="printDiagnostic"
                        class="btn btn-outline-dark px-4 py-3 rounded-pill fw-bold d-flex align-items-center gap-2">
                  <FileText :size="20" /> Imprimer
                </button>

                <!-- Boutique -->
                <a href="/shop" class="btn btn-primary px-5 py-3 rounded-pill fw-bold shadow-lg d-flex align-items-center gap-2">
                  <ShoppingBag :size="20" /> Boutique Complète
                </a>

                <!-- Nouvelle analyse -->
                <button @click="step = 1; result = null; capturedImage = null; form.analysis_type = ''; form.photo = null"
                        class="btn btn-outline-primary px-4 py-3 rounded-pill fw-bold">
                  Nouvelle Analyse
                </button>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </PremiumLayout>
</template>

<style scoped>
.animate-slide-up { animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
@keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.shadow-premium { box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1); }
.border-dashed { border-style: dashed !important; }
.cursor-pointer { cursor: pointer; }
.animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
.hover-shadow:hover { transform: translateY(-4px) scale(1.02); box-shadow: 0 20px 40px -12px rgba(0,0,0,0.15) !important; }
.x-small { font-size: 0.7rem; }
.progress { background-color: rgba(0,0,0,0.08); }

.scanner-line {
  width: 80%;
  height: 3px;
  background: linear-gradient(90deg, transparent, #4CAF50, transparent);
  animation: scannerMove 2s ease-in-out infinite;
  border-radius: 2px;
}
@keyframes scannerMove {
  0%, 100% { top: 20%; }
  50% { top: 80%; }
}

.bg-opacity-60 { background-color: rgba(0,0,0,0.6); }
</style>
