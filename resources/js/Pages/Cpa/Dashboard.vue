<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import CpaLayout from '../../Layouts/CpaLayout.vue';
import { authStore } from '../../stores/auth';

const loading = ref(true);
const section = ref('dashboard');

// ── Détection du rôle ────────────────────────────────────────────
const role = computed(() => authStore.user?.role || 'client');
const isSuperAdmin = computed(() => role.value === 'super_admin');
const isClient = computed(() => role.value === 'client');
const isCompanyAdmin = computed(() => role.value === 'company_admin');
const isComptable = computed(() => role.value === 'comptable');

// ── Toast Notification state ──────────────────────────────────────
const toast = ref({ show: false, message: '', type: 'success' });
const showToast = (msg, type = 'success') => {
    toast.value.message = msg;
    toast.value.type = type;
    toast.value.show = true;
    setTimeout(() => {
        toast.value.show = false;
    }, 3000);
};

// ── Données réactives ─────────────────────────────────────────────
// Admin / Clients
const clientQuery = ref('');
const clientStatusFilter = ref('all');
const showNewClientModal = ref(false);
const newClient = ref({ name: '', city: '', status: 'actif', email: '' });

const adminClients = ref([
    { id: 1, name: 'TechInnov SARL', city: 'Cotonou', status: 'actif', date: '12/06/2026', email: 'contact@techinnov.bj', dossiers_count: 3 },
    { id: 2, name: 'Services Pro Bénin', city: 'Cotonou', status: 'actif', date: '10/06/2026', email: 'contact@servicespro.bj', dossiers_count: 5 },
    { id: 3, name: 'Martin Dupuis', city: 'Cotonou', status: 'actif', date: '05/06/2026', email: 'client1@test.com', dossiers_count: 2 },
    { id: 4, name: 'Groupe AFRIQUE', city: 'Parakou', status: 'en_attente', date: '01/06/2026', email: 'info@groupeafrique.com', dossiers_count: 1 },
    { id: 5, name: 'Agence Nova', city: 'Porto-Novo', status: 'actif', date: '28/05/2026', email: 'contact@agencenova.com', dossiers_count: 4 },
]);

const usersList = ref([
    { name: 'Admin GEL', email: 'admin@monprojet.com', role: 'super_admin', status: 'actif' },
    { name: 'Martin Dupuis', email: 'client1@test.com', role: 'client', status: 'actif' },
    { name: 'Aminata Diallo', email: 'entreprise@test.com', role: 'company_admin', status: 'actif' },
    { name: 'Fatoumata Koné', email: 'comptable@monprojet.com', role: 'comptable', status: 'actif' },
]);

// Client particulier
const clientDeclarations = ref([
    { year: 2025, federal: 'En cours', provincial: 'En cours', progress: 45, date: 'En attente' },
    { year: 2024, federal: 'Terminé', provincial: 'Terminé', progress: 100, date: '15/05/2025' },
    { year: 2023, federal: 'Terminé', provincial: 'Terminé', progress: 100, date: '12/05/2024' },
]);

const clientDocumentChecklist = ref([
    { label: 'Relevé bancaire 2025', status: 'fourni' },
    { label: 'Frais médicaux', status: 'fourni' },
    { label: 'Reçu loyer', status: 'attendu' },
    { label: 'Dons et contributions', status: 'attendu' },
    { label: 'Relevé REER', status: 'optionnel' },
]);

const clientMessages = ref([
    { from: 'Fatoumata Koné', msg: 'Bonjour, j\'ai bien reçu vos documents. Je traite votre déclaration cette semaine.', date: new Date(Date.now() - 3600000 * 2).toISOString(), unread: false },
    { from: 'Fatoumata Koné', msg: 'Pourriez-vous fournir le reçu de loyer s\'il vous plaît ?', date: new Date(Date.now() - 3600000 * 24).toISOString(), unread: true },
    { from: 'Support GEL', msg: 'Votre dossier 2025 a été initialisé.', date: new Date(Date.now() - 3600000 * 48).toISOString(), unread: false },
]);

// Client entreprise
const companyAlerts = ref([
    { id: 1, label: 'Déclaration TVA due dans 15 jours', type: 'warning', date: '30/06/2026', resolved: false },
    { id: 2, label: 'Facture #FAC-2026-0042 en retard', type: 'danger', date: '10/06/2026', resolved: false },
    { id: 3, label: 'Paie mensuelle à préparer', type: 'info', date: '25/06/2026', resolved: false },
    { id: 4, label: 'Renouvellement licence comptabilité', type: 'info', date: '15/07/2026', resolved: false },
]);

// Comptable
const comptableDossiers = ref([
    { id: 1, client: 'TechInnov SARL', mission: 'Révision annuelle', status: 'en_cours', deadline: '30/06/2026', progress: 60 },
    { id: 2, client: 'Services Pro Bénin', mission: 'Comptabilité mensuelle', status: 'en_cours', deadline: '25/06/2026', progress: 40 },
    { id: 3, client: 'Martin Dupuis', mission: 'Déclaration 2025', status: 'en_attente', deadline: '15/07/2026', progress: 20 },
    { id: 4, client: 'Agence Nova', mission: 'Bilan intermédiaire', status: 'termine', deadline: '10/06/2026', progress: 100 },
    { id: 5, client: 'Groupe AFRIQUE', mission: 'Fiscalité 2025', status: 'en_attente', deadline: '31/07/2026', progress: 10 },
]);

const comptableTasks = ref([
    { id: 1, task: 'Saisir écritures avril - TechInnov', priority: 'haute', due: '18/06', done: false },
    { id: 2, task: 'Préparer déclaration TVA - Services Pro', priority: 'haute', due: '20/06', done: false },
    { id: 3, task: 'Vérifier balance - Agence Nova', priority: 'moyenne', due: '22/06', done: false },
    { id: 4, task: 'Rapprochement bancaire - Groupe AFRIQUE', priority: 'basse', due: '30/06', done: false },
]);

const comptableChats = ref({
    'Martin Dupuis': [
        { from: 'Martin Dupuis', msg: 'Bonsoir, avez-vous mes relevés bancaires ?', date: new Date(Date.now() - 3600000 * 18).toISOString() },
        { from: 'Moi', msg: 'Bonjour Martin, non je ne les vois pas dans vos documents fournis. Pouvez-vous les charger ?', date: new Date(Date.now() - 3600000 * 5).toISOString() }
    ],
    'TechInnov SARL': [
        { from: 'TechInnov SARL', msg: 'La balance est prête pour vérification.', date: new Date(Date.now() - 3600000 * 24).toISOString() },
        { from: 'Moi', msg: 'Parfait, j\'examine cela aujourd\'hui.', date: new Date(Date.now() - 3600000 * 6).toISOString() }
    ]
});

// ── States secondaires interactifs ────────────────────────────────
const fileUploadProgress = ref(0);
const uploadingFileName = ref('');
const isUploading = ref(false);

const activeChatClient = ref('Martin Dupuis');
const newMessageText = ref('');
const isTyping = ref(false);

const newTaskText = ref('');
const newTaskPriority = ref('moyenne');
const newTaskDue = ref('');

// ── Helpers de formatage ──────────────────────────────────────────
const statusClass = (s) => ({
    actif: 'cpa-badge-success', en_attente: 'cpa-badge-warning',
    en_cours: 'cpa-badge-info', termine: 'cpa-badge-success',
    'Terminé': 'cpa-badge-success', 'En cours': 'cpa-badge-info',
    'En attente': 'cpa-badge-warning',
}[s] || 'cpa-badge-neutral');

const statusLabel = (s) => ({
    actif: 'Actif', en_attente: 'En attente', en_cours: 'En cours',
    termine: 'Terminé', 'Terminé': 'Terminé', 'En cours': 'En cours',
    'En attente': 'En attente',
}[s] || s);

const priorityClass = (p) => ({
    haute: 'cpa-badge-danger', moyenne: 'cpa-badge-warning', basse: 'cpa-badge-neutral',
}[p] || 'cpa-badge-neutral');

const fmtCurrency = (v) => {
    if (v === null || v === undefined) return '0';
    return v.toLocaleString('fr-FR');
};

const formatDateStr = (dateStr) => {
    if (!dateStr) return '';
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
    } catch (e) {
        return dateStr;
    }
};

// ── Actions Interactives ─────────────────────────────────────────

// Admin - gestion des statuts
const filteredClients = computed(() => {
    return adminClients.value.filter(c => {
        const matchesQuery = c.name.toLowerCase().includes(clientQuery.value.toLowerCase()) || 
                             c.city.toLowerCase().includes(clientQuery.value.toLowerCase());
        const matchesStatus = clientStatusFilter.value === 'all' || c.status === clientStatusFilter.value;
        return matchesQuery && matchesStatus;
    });
});

const toggleClientStatus = (c) => {
    c.status = c.status === 'actif' ? 'en_attente' : 'actif';
    showToast(`Statut de ${c.name} modifié en : ${statusLabel(c.status)}`);
};

const addClient = () => {
    if (!newClient.value.name.trim() || !newClient.value.city.trim()) {
        showToast('Veuillez remplir tous les champs', 'warning');
        return;
    }
    adminClients.value.push({
        id: Date.now(),
        name: newClient.value.name,
        city: newClient.value.city,
        status: newClient.value.status,
        date: new Date().toLocaleDateString('fr-FR'),
        email: newClient.value.name.toLowerCase().replace(/\s+/g, '') + '@test.com',
        dossiers_count: 1
    });
    newClient.value = { name: '', city: '', status: 'actif', email: '' };
    showNewClientModal.value = false;
    showToast('Nouveau client ajouté au cabinet !');
};

// Client - simulation d'upload
const triggerFileUpload = () => {
    document.getElementById('cpa-file-input').click();
};

const handleFileUpload = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    
    uploadingFileName.value = file.name;
    isUploading.value = true;
    fileUploadProgress.value = 0;
    
    const interval = setInterval(() => {
        fileUploadProgress.value += 10;
        if (fileUploadProgress.value >= 100) {
            clearInterval(interval);
            setTimeout(() => {
                isUploading.value = false;
                clientDocumentChecklist.value.push({
                    label: file.name,
                    status: 'fourni'
                });
                showToast(`Document "${file.name}" téléversé avec succès !`);
            }, 500);
        }
    }, 120);
};

// Client - Messagerie interactive
const sendClientMessage = () => {
    if (!newMessageText.value.trim()) return;
    
    clientMessages.value.unshift({
        from: 'Moi',
        msg: newMessageText.value,
        date: new Date().toISOString(),
        unread: false
    });
    const msg = newMessageText.value;
    newMessageText.value = '';
    
    // Auto scroll chat
    nextTick(() => {
        const chatBox = document.getElementById('chat-scroll-box');
        if (chatBox) chatBox.scrollTop = 0;
    });

    // Comptable rep
    isTyping.value = true;
    setTimeout(() => {
        isTyping.value = false;
        clientMessages.value.unshift({
            from: 'Fatoumata Koné',
            msg: `J'ai bien reçu votre message concernant : "${msg}". Je vérifie votre dossier et je reviens vers vous rapidement.`,
            date: new Date().toISOString(),
            unread: false
        });
        showToast('Nouveau message de Fatoumata Koné !', 'info');
        nextTick(() => {
            const chatBox = document.getElementById('chat-scroll-box');
            if (chatBox) chatBox.scrollTop = 0;
        });
    }, 2000);
};

// Entreprise - résolution alertes
const resolveAlert = (a) => {
    a.resolved = true;
    showToast('Alerte résolue avec succès !');
};

// Comptable - mise à jour statut dossier
const updateDossierStatus = (d, newStatus) => {
    d.status = newStatus;
    d.progress = newStatus === 'termine' ? 100 : newStatus === 'en_cours' ? 60 : 15;
    showToast(`Dossier ${d.client} passé en : ${statusLabel(newStatus)}`);
};

// Comptable - gestionnaire de tâches
const addTask = () => {
    if (!newTaskText.value.trim()) return;
    comptableTasks.value.push({
        id: Date.now(),
        task: newTaskText.value,
        priority: newTaskPriority.value,
        due: newTaskDue.value || 'À définir',
        done: false
    });
    newTaskText.value = '';
    newTaskDue.value = '';
    showToast('Tâche ajoutée au tableau !');
};

const toggleTask = (t) => {
    t.done = !t.done;
    if (t.done) {
        showToast('Tâche complétée !');
    }
};

const deleteTask = (id) => {
    comptableTasks.value = comptableTasks.value.filter(t => t.id !== id);
    showToast('Tâche supprimée', 'warning');
};

// Comptable - messagerie bi-colonne
const currentComptableChat = computed(() => {
    return comptableChats.value[activeChatClient.value] || [];
});

const sendComptableMessage = () => {
    if (!newMessageText.value.trim()) return;
    const client = activeChatClient.value;
    if (!comptableChats.value[client]) {
        comptableChats.value[client] = [];
    }
    comptableChats.value[client].push({
        from: 'Moi',
        msg: newMessageText.value,
        date: new Date().toISOString()
    });
    newMessageText.value = '';

    nextTick(() => {
        const chatBox = document.getElementById('comptable-chat-box');
        if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
    });

    isTyping.value = true;
    setTimeout(() => {
        isTyping.value = false;
        comptableChats.value[client].push({
            from: client,
            msg: `Entendu Fatoumata, je vais faire le nécessaire pour cela. Merci !`,
            date: new Date().toISOString()
        });
        showToast(`Nouveau message de ${client} !`, 'info');
        nextTick(() => {
            const chatBox = document.getElementById('comptable-chat-box');
            if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
        });
    }, 2000);
};

// ── Synchronisation URL Section ───────────────────────────────────
const setSection = (s) => {
    section.value = s;
    const url = new URL(window.location.href);
    if (s === 'dashboard') {
        url.searchParams.delete('section');
    } else {
        url.searchParams.set('section', s);
    }
    window.history.pushState({}, '', url.toString());
};

const checkUrlSection = () => {
    const params = new URLSearchParams(window.location.search);
    const sec = params.get('section');
    if (sec) {
        section.value = sec;
    } else {
        section.value = 'dashboard';
    }
};

onMounted(async () => {
    checkUrlSection();
    window.addEventListener('popstate', checkUrlSection);
    // Simuler un chargement progressif
    await new Promise(r => setTimeout(r, 400));
    loading.value = false;
});

onUnmounted(() => {
    window.removeEventListener('popstate', checkUrlSection);
});
</script>

<template>
    <CpaLayout page-title="Portail Client CPA">
        <!-- ═══ LOADING ═══ -->
        <div v-if="loading" class="cpa-loading">
            <div class="cpa-loading-spinner-wrap">
                <div class="cpa-spinner"></div>
            </div>
            <span class="cpa-loading-text">Chargement sécurisé de vos donnees...</span>
        </div>

        <!-- ═══ APP CONTENT ═══ -->
        <div v-else class="cpa-fade-in">

            <!-- ═══ WAVE DECORATION ═══ -->
            <div class="cpa-wave-hero">
                <svg viewBox="0 0 1440 80" preserveAspectRatio="none" class="cpa-wave-svg">
                    <defs>
                        <linearGradient id="cpa-wave-grad" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#FF7900" stop-opacity="0.07"/>
                            <stop offset="50%" stop-color="#163A5E" stop-opacity="0.04"/>
                            <stop offset="100%" stop-color="#FF7900" stop-opacity="0.07"/>
                        </linearGradient>
                    </defs>
                    <path d="M0,30 C240,70 480,10 720,40 C960,70 1200,15 1440,35 L1440,0 L0,0 Z" fill="url(#cpa-wave-grad)"/>
                </svg>
            </div>

            <!-- Toast alert -->
            <transition name="toast-slide">
                <div v-if="toast.show" :class="['cpa-toast', 'cpa-toast-' + toast.type]">
                    <i :class="toast.type === 'success' ? 'bi-check-circle-fill' : 
                               toast.type === 'warning' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill'"></i>
                    <span>{{ toast.message }}</span>
                </div>
            </transition>

            <!-- ======================================================= -->
            <!-- ═══ ADMIN (Super Admin) ═══ -->
            <!-- ======================================================= -->
            <template v-if="isSuperAdmin">
                <!-- A1. Vue d'ensemble (Dashboard) -->
                <div v-if="section === 'dashboard'">
                    <div class="cpa-page-header">
                        <h1 class="cpa-page-title">Tableau de bord Administrateur</h1>
                        <p class="cpa-page-subtitle">Vue globale d'activité du cabinet GEL</p>
                    </div>

                    <!-- Stats Cards Grid -->
                    <div class="cpa-grid-4">
                        <div class="cpa-card">
                            <div class="cpa-card-body d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="cpa-stat-value">{{ adminClients.length }}</div>
                                    <div class="cpa-stat-label">Clients totaux</div>
                                </div>
                                <div class="cpa-stat-icon-box bg-primary-soft text-primary">
                                    <i class="bi-people-fill"></i>
                                </div>
                            </div>
                        </div>
                        <div class="cpa-card">
                            <div class="cpa-card-body d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="cpa-stat-value">{{ adminClients.filter(c => c.status === 'actif').length }}</div>
                                    <div class="cpa-stat-label">Clients actifs</div>
                                </div>
                                <div class="cpa-stat-icon-box bg-success-soft text-success">
                                    <i class="bi-check-circle-fill"></i>
                                </div>
                            </div>
                        </div>
                        <div class="cpa-card">
                            <div class="cpa-card-body d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="cpa-stat-value">{{ adminClients.filter(c => c.status === 'en_attente').length }}</div>
                                    <div class="cpa-stat-label">Dossiers en attente</div>
                                </div>
                                <div class="cpa-stat-icon-box bg-warning-soft text-warning">
                                    <i class="bi-hourglass-split"></i>
                                </div>
                            </div>
                        </div>
                        <div class="cpa-card">
                            <div class="cpa-card-body d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="cpa-stat-value">{{ fmtCurrency(1250000) }} F</div>
                                    <div class="cpa-stat-label">Revenus ce mois</div>
                                </div>
                                <div class="cpa-stat-icon-box bg-info-soft text-info">
                                    <i class="bi-cash-coin"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Grid Activité & Alertes -->
                    <div class="row g-3 mt-2">
                        <div class="col-lg-7">
                            <div class="cpa-card">
                                <div class="cpa-card-header">
                                    <span><i class="bi-graph-up me-2 text-primary"></i>Activité mensuelle (Missions terminées)</span>
                                </div>
                                <div class="cpa-card-body">
                                    <div class="cpa-chart-bars">
                                        <div class="cpa-chart-col"><div class="cpa-chart-bar-wrap"><div class="cpa-chart-bar" style="height: 60px;"><span class="cpa-chart-val">8</span></div></div><div class="cpa-chart-label">Jan</div></div>
                                        <div class="cpa-chart-col"><div class="cpa-chart-bar-wrap"><div class="cpa-chart-bar" style="height: 90px;"><span class="cpa-chart-val">12</span></div></div><div class="cpa-chart-label">Fév</div></div>
                                        <div class="cpa-chart-col"><div class="cpa-chart-bar-wrap"><div class="cpa-chart-bar" style="height: 110px;"><span class="cpa-chart-val">15</span></div></div><div class="cpa-chart-label">Mar</div></div>
                                        <div class="cpa-chart-col"><div class="cpa-chart-bar-wrap"><div class="cpa-chart-bar" style="height: 80px;"><span class="cpa-chart-val">10</span></div></div><div class="cpa-chart-label">Avr</div></div>
                                        <div class="cpa-chart-col"><div class="cpa-chart-bar-wrap"><div class="cpa-chart-bar" style="height: 130px;"><span class="cpa-chart-val">18</span></div></div><div class="cpa-chart-label">Mai</div></div>
                                        <div class="cpa-chart-col"><div class="cpa-chart-bar-wrap"><div class="cpa-chart-bar" style="height: 100px;"><span class="cpa-chart-val">14</span></div></div><div class="cpa-chart-label">Juin</div></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="cpa-card">
                                <div class="cpa-card-header">
                                    <span><i class="bi-bell-fill me-2 text-warning"></i>Alertes globales</span>
                                </div>
                                <div class="cpa-card-body">
                                    <div class="cpa-alert-item cpa-alert-warning">
                                        <i class="bi-exclamation-triangle me-2"></i>
                                        <span>4 dossiers en retard sur la CNSS</span>
                                    </div>
                                    <div class="cpa-alert-item cpa-alert-danger">
                                        <i class="bi-shield-exclamation me-2"></i>
                                        <span>Demande d'approbation d'accès en attente</span>
                                    </div>
                                    <div class="cpa-alert-item cpa-alert-info">
                                        <i class="bi-info-circle me-2"></i>
                                        <span>Sauvegarde système effectuée ce matin</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- A2. Clients & Utilisateurs -->
                <div v-else-if="section === 'clients'">
                    <div class="cpa-page-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <h1 class="cpa-page-title">Gestion des clients et utilisateurs</h1>
                            <p class="cpa-page-subtitle">Visualisez et administrez les comptes du cabinet</p>
                        </div>
                        <button class="cpa-btn cpa-btn-primary" @click="showNewClientModal = true">
                            <i class="bi-plus-lg me-1"></i>Nouveau client
                        </button>
                    </div>

                    <!-- Filtre et Recherche -->
                    <div class="cpa-card p-3 mb-3">
                        <div class="row g-2">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="bi-search text-muted"></i></span>
                                    <input v-model="clientQuery" type="text" class="form-control border-start-0 shadow-none" placeholder="Rechercher par nom, ville ou email...">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select v-model="clientStatusFilter" class="form-select shadow-none">
                                    <option value="all">Tous les statuts</option>
                                    <option value="actif">Actif</option>
                                    <option value="en_attente">En attente</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Client Table -->
                    <div class="cpa-card">
                        <div class="cpa-card-header">
                            <span><i class="bi-people-fill me-2 text-primary"></i>Liste des clients</span>
                        </div>
                        <div class="cpa-card-body p-0 table-responsive">
                            <table class="cpa-table">
                                <thead>
                                    <tr>
                                        <th>Nom du Client</th>
                                        <th>Ville</th>
                                        <th>E-mail</th>
                                        <th>Créé le</th>
                                        <th>Dossiers</th>
                                        <th>Statut</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="c in filteredClients" :key="c.id" class="align-middle">
                                        <td class="fw-bold text-dark">{{ c.name }}</td>
                                        <td>{{ c.city }}</td>
                                        <td class="text-muted">{{ c.email }}</td>
                                        <td>{{ c.date }}</td>
                                        <td class="text-center"><span class="badge bg-light text-dark rounded-pill px-2.5">{{ c.dossiers_count }}</span></td>
                                        <td><span :class="['cpa-badge', statusClass(c.status)]">{{ statusLabel(c.status) }}</span></td>
                                        <td class="text-end">
                                            <button class="cpa-btn cpa-btn-outline btn-sm me-1" @click="toggleClientStatus(c)">
                                                {{ c.status === 'actif' ? 'Désactiver' : 'Activer' }}
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="filteredClients.length === 0">
                                        <td colspan="7" class="text-center text-muted py-4">Aucun client ne correspond aux critères.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Users Section -->
                    <div class="cpa-card mt-3">
                        <div class="cpa-card-header">
                            <span><i class="bi-person-badge-fill me-2 text-primary"></i>Utilisateurs système</span>
                        </div>
                        <div class="cpa-card-body p-0 table-responsive">
                            <table class="cpa-table">
                                <thead>
                                    <tr>
                                        <th>Nom de l'utilisateur</th>
                                        <th>Adresse e-mail</th>
                                        <th>Rôle système</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="u in usersList" :key="u.email">
                                        <td class="fw-semibold">{{ u.name }}</td>
                                        <td class="text-muted">{{ u.email }}</td>
                                        <td>
                                            <span class="badge bg-light text-primary border border-primary-subtle px-2 py-1">
                                                {{ u.role === 'super_admin' ? 'Administrateur' : 
                                                   u.role === 'comptable' ? 'Comptable' : 
                                                   u.role === 'company_admin' ? 'Admin Entreprise' : 'Client particulier' }}
                                            </span>
                                        </td>
                                        <td><span class="cpa-badge cpa-badge-success">Actif</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Add Client Modal -->
                    <div v-if="showNewClientModal" class="cpa-modal-backdrop">
                        <div class="cpa-modal cpa-animate">
                            <div class="cpa-modal-header d-flex justify-content-between align-items-center">
                                <h5 class="m-0 fw-bold">Ajouter un nouveau client</h5>
                                <button class="btn-close" @click="showNewClientModal = false"></button>
                            </div>
                            <div class="cpa-modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Nom de l'entreprise ou particulier</label>
                                    <input v-model="newClient.name" type="text" class="form-control shadow-none" placeholder="Ex: Cabinet Horizon, Jean Dupont">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Ville</label>
                                    <input v-model="newClient.city" type="text" class="form-control shadow-none" placeholder="Ex: Cotonou, Porto-Novo">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Statut initial</label>
                                    <select v-model="newClient.status" class="form-select shadow-none">
                                        <option value="actif">Actif</option>
                                        <option value="en_attente">En attente</option>
                                    </select>
                                </div>
                            </div>
                            <div class="cpa-modal-footer d-flex justify-content-end gap-2">
                                <button class="cpa-btn cpa-btn-outline" @click="showNewClientModal = false">Annuler</button>
                                <button class="cpa-btn cpa-btn-primary" @click="addClient">Créer le client</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- A3. Statistiques Avancées -->
                <div v-else-if="section === 'stats'">
                    <div class="cpa-page-header">
                        <h1 class="cpa-page-title">Analyses & Statistiques</h1>
                        <p class="cpa-page-subtitle">Statistiques clés sur l'activité du cabinet</p>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="cpa-card h-100">
                                <div class="cpa-card-header">
                                    <span>Répartition des dossiers clients</span>
                                </div>
                                <div class="cpa-card-body d-flex flex-column align-items-center py-4">
                                    <!-- Circular Donut SVG Chart -->
                                    <svg width="180" height="180" viewBox="0 0 36 36" class="donut">
                                        <circle cx="18" cy="18" r="15.915" fill="transparent" stroke="#f3f4f6" stroke-width="3"></circle>
                                        <!-- Actifs (75%) -->
                                        <circle cx="18" cy="18" r="15.915" fill="transparent" stroke="var(--cpa-primary)" stroke-width="3" stroke-dasharray="75 25" stroke-dashoffset="25"></circle>
                                        <!-- En attente (25%) -->
                                        <circle cx="18" cy="18" r="15.915" fill="transparent" stroke="#163A5E" stroke-width="3" stroke-dasharray="25 75" stroke-dashoffset="50"></circle>
                                    </svg>
                                    <div class="mt-4 w-100">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="d-flex align-items-center gap-2"><span style="width:12px;height:12px;background:var(--cpa-primary);border-radius:3px;"></span>Dossiers Actifs</span>
                                            <span class="fw-bold">75%</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="d-flex align-items-center gap-2"><span style="width:12px;height:12px;background:#163A5E;border-radius:3px;"></span>Dossiers En attente</span>
                                            <span class="fw-bold">25%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="cpa-card h-100">
                                <div class="cpa-card-header">
                                    <span>Progression des revenus (Anuel N vs N-1)</span>
                                </div>
                                <div class="cpa-card-body">
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="fw-semibold">Année en cours (N)</span>
                                            <span class="text-primary fw-bold">12,5M F</span>
                                        </div>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="fw-semibold">Année précédente (N-1)</span>
                                            <span class="text-dark fw-bold">10,2M F</span>
                                        </div>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar bg-dark" role="progressbar" style="width: 68%" aria-valuenow="68" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- ======================================================= -->
            <!-- ═══ CLIENT PARTICULIER ═══ -->
            <!-- ======================================================= -->
            <template v-else-if="isClient">
                <!-- B1. Vue d'ensemble (Dashboard) -->
                <div v-if="section === 'dashboard'">
                    <div class="cpa-page-header">
                        <h1 class="cpa-page-title">Bonjour, {{ authStore.user?.name }}</h1>
                        <p class="cpa-page-subtitle">Bienvenue sur votre espace fiscal sécurisé GEL Cabinet</p>
                    </div>

                    <!-- Stats -->
                    <div class="cpa-grid-3">
                        <div class="cpa-card cursor-pointer" @click="setSection('declarations')">
                            <div class="cpa-card-body d-flex align-items-center gap-3">
                                <div class="cpa-stat-icon-box bg-primary-soft text-primary">
                                    <i class="bi-file-earmark-text"></i>
                                </div>
                                <div>
                                    <div class="cpa-stat-value" style="font-size:20px;">2025</div>
                                    <div class="cpa-stat-label">Déclarations en cours</div>
                                </div>
                            </div>
                        </div>
                        <div class="cpa-card cursor-pointer" @click="setSection('documents')">
                            <div class="cpa-card-body d-flex align-items-center gap-3">
                                <div class="cpa-stat-icon-box bg-success-soft text-success">
                                    <i class="bi-folder-check"></i>
                                </div>
                                <div>
                                    <div class="cpa-stat-value" style="font-size:20px;">2 / 5</div>
                                    <div class="cpa-stat-label">Documents fournis</div>
                                </div>
                            </div>
                        </div>
                        <div class="cpa-card cursor-pointer" @click="setSection('messages')">
                            <div class="cpa-card-body d-flex align-items-center gap-3">
                                <div class="cpa-stat-icon-box bg-warning-soft text-warning">
                                    <i class="bi-chat-dots-fill"></i>
                                </div>
                                <div>
                                    <div class="cpa-stat-value" style="font-size:20px;">1</div>
                                    <div class="cpa-stat-label">Message en attente</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        <!-- Declarations recap -->
                        <div class="col-lg-7">
                            <div class="cpa-card">
                                <div class="cpa-card-header">
                                    <span><i class="bi-file-earmark-check me-2 text-primary"></i>Statut de vos déclarations</span>
                                </div>
                                <div class="cpa-card-body p-0">
                                    <table class="cpa-table">
                                        <thead>
                                            <tr>
                                                <th>Année</th>
                                                <th>Fédérale</th>
                                                <th>Provinciale</th>
                                                <th>Avancement</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="d in clientDeclarations" :key="d.year">
                                                <td class="fw-bold">{{ d.year }}</td>
                                                <td><span :class="['cpa-badge', statusClass(d.federal)]">{{ d.federal }}</span></td>
                                                <td><span :class="['cpa-badge', statusClass(d.provincial)]">{{ d.provincial }}</span></td>
                                                <td>
                                                    <div class="cpa-progress" style="width:100px;">
                                                        <div class="cpa-progress-bar" :class="d.progress === 100 ? 'cpa-progress-bar-green' : ''" :style="{ width: d.progress + '%' }"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Accountant Profile -->
                        <div class="col-lg-5">
                            <div class="cpa-card">
                                <div class="cpa-card-header">
                                    <span>Votre expert comptable assigné</span>
                                </div>
                                <div class="cpa-card-body text-center py-4">
                                    <div class="cpa-avatar-lg">
                                        <i class="bi-person-fill text-muted"></i>
                                    </div>
                                    <h4 class="mt-2 text-dark fw-bold" style="font-size:16px;">Fatoumata Koné</h4>
                                    <p class="text-muted" style="font-size:12px;margin-bottom:12px;">Comptable Senior - Cabinet GEL</p>
                                    <button class="cpa-btn cpa-btn-primary btn-sm" @click="setSection('messages')">
                                        <i class="bi-envelope me-1"></i>Envoyer un message
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- B2. Déclarations de Revenus -->
                <div v-else-if="section === 'declarations'">
                    <div class="cpa-page-header">
                        <h1 class="cpa-page-title">Mes déclarations de revenus</h1>
                        <p class="cpa-page-subtitle">Suivez l'historique et le statut de vos soumissions fiscales</p>
                    </div>

                    <!-- Active Declarations -->
                    <div class="cpa-card">
                        <div class="cpa-card-header">
                            <span>Déclarations fiscales en cours (2025)</span>
                        </div>
                        <div class="cpa-card-body">
                            <div class="row align-items-center py-3 border-bottom">
                                <div class="col-md-3">
                                    <div class="fw-bold" style="font-size: 16px;">Impôt Fédéral 2025</div>
                                    <span class="text-muted" style="font-size: 12px;">Canada Revenue Agency</span>
                                </div>
                                <div class="col-md-5">
                                    <div class="d-flex justify-content-between mb-1" style="font-size: 12px;">
                                        <span>Traitement du dossier par le cabinet</span>
                                        <span class="fw-bold">45%</span>
                                    </div>
                                    <div class="cpa-progress">
                                        <div class="cpa-progress-bar" style="width: 45%;"></div>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <span class="cpa-badge cpa-badge-info">En cours de traitement</span>
                                </div>
                                <div class="col-md-2 text-end">
                                    <button class="cpa-btn cpa-btn-outline btn-sm" @click="setSection('documents')">Ajouter pièces</button>
                                </div>
                            </div>

                            <div class="row align-items-center py-3">
                                <div class="col-md-3">
                                    <div class="fw-bold" style="font-size: 16px;">Impôt Provincial 2025</div>
                                    <span class="text-muted" style="font-size: 12px;">Revenu Québec</span>
                                </div>
                                <div class="col-md-5">
                                    <div class="d-flex justify-content-between mb-1" style="font-size: 12px;">
                                        <span>Collecte de vos documents d'impôt</span>
                                        <span class="fw-bold">25%</span>
                                    </div>
                                    <div class="cpa-progress">
                                        <div class="cpa-progress-bar" style="width: 25%;"></div>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <span class="cpa-badge cpa-badge-warning">Documents manquants</span>
                                </div>
                                <div class="col-md-2 text-end">
                                    <button class="cpa-btn cpa-btn-primary btn-sm" @click="setSection('documents')">Fournir</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- History -->
                    <div class="cpa-card mt-3">
                        <div class="cpa-card-header">
                            <span>Historique des années précédentes</span>
                        </div>
                        <div class="cpa-card-body p-0">
                            <table class="cpa-table">
                                <thead>
                                    <tr>
                                        <th>Année fiscale</th>
                                        <th>Date de dépôt</th>
                                        <th>Statut fédéral</th>
                                        <th>Statut provincial</th>
                                        <th class="text-end">Téléchargements</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="d in clientDeclarations.filter(d => d.year < 2025)" :key="d.year">
                                        <td class="fw-bold">{{ d.year }}</td>
                                        <td class="text-muted">{{ d.date }}</td>
                                        <td><span class="cpa-badge cpa-badge-success">Terminé</span></td>
                                        <td><span class="cpa-badge cpa-badge-success">Terminé</span></td>
                                        <td class="text-end">
                                            <button class="cpa-btn cpa-btn-outline btn-sm me-1" @click="showToast('Téléchargement de l\'avis fédéral ' + d.year + '...')">
                                                <i class="bi-file-earmark-arrow-down me-1"></i>Fédéral
                                            </button>
                                            <button class="cpa-btn cpa-btn-outline btn-sm" @click="showToast('Téléchargement de l\'avis provincial ' + d.year + '...')">
                                                <i class="bi-file-earmark-arrow-down me-1"></i>Provincial
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- B3. Documents (Upload & Checklist) -->
                <div v-else-if="section === 'documents'">
                    <div class="cpa-page-header">
                        <h1 class="cpa-page-title">Mes documents justificatifs</h1>
                        <p class="cpa-page-subtitle">Transmettez vos pièces justificatives en toute sécurité</p>
                    </div>

                    <!-- Upload Zone -->
                    <div class="cpa-card p-4 mb-3 text-center cpa-file-dropzone" @click="triggerFileUpload">
                        <input id="cpa-file-input" type="file" style="display:none;" @change="handleFileUpload">
                        <i class="bi-cloud-arrow-up text-primary" style="font-size: 38px;"></i>
                        <h5 class="fw-bold mt-2">Cliquez ici pour téléverser vos documents d'impôts</h5>
                        <p class="text-muted" style="font-size:12px;">Formats acceptés : PDF, PNG, JPG (Max. 10 Mo)</p>

                        <div v-if="isUploading" class="mt-3 w-50 mx-auto">
                            <div class="d-flex justify-content-between mb-1" style="font-size:11px;">
                                <span class="fw-semibold">Envoi de {{ uploadingFileName }}...</span>
                                <span>{{ fileUploadProgress }}%</span>
                            </div>
                            <div class="cpa-progress">
                                <div class="cpa-progress-bar" :style="{ width: fileUploadProgress + '%' }"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <!-- Checklist -->
                        <div class="col-md-6">
                            <div class="cpa-card h-100">
                                <div class="cpa-card-header">
                                    <span><i class="bi-list-task me-2 text-primary"></i>Checklist de documents requis</span>
                                </div>
                                <div class="cpa-card-body">
                                    <div v-for="item in clientDocumentChecklist" :key="item.label" class="cpa-checklist-item">
                                        <i :class="item.status === 'fourni' ? 'bi-check-circle-fill text-success' :
                                                  item.status === 'attendu' ? 'bi-exclamation-circle-fill text-warning' :
                                                  'bi-question-circle-fill text-muted'"></i>
                                        <span :class="{ 'text-muted text-decoration-line-through': item.status === 'fourni' }">{{ item.label }}</span>
                                        <button v-if="item.status !== 'fourni'" class="cpa-btn cpa-btn-primary btn-sm ms-auto py-1 px-2.5" @click="triggerFileUpload" style="font-size:11px;">
                                            Fournir
                                        </button>
                                        <span v-else class="cpa-badge cpa-badge-success ms-auto">Reçu</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Documents Received from cabinet -->
                        <div class="col-md-6">
                            <div class="cpa-card h-100">
                                <div class="cpa-card-header">
                                    <span><i class="bi-file-earmark-pdf me-2 text-danger"></i>Documents reçus du cabinet</span>
                                </div>
                                <div class="cpa-card-body p-0">
                                    <div class="cpa-doc-item">
                                        <i class="bi-file-earmark-pdf-fill text-danger" style="font-size:20px;"></i>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold" style="font-size:13px;">Avis de cotisation Fédéral 2024.pdf</div>
                                            <span class="text-muted" style="font-size:11px;">15/06/2025 • 240 ko</span>
                                        </div>
                                        <i class="bi-download cpa-doc-dl" @click="showToast('Téléchargement du document...')"></i>
                                    </div>
                                    <div class="cpa-doc-item">
                                        <i class="bi-file-earmark-pdf-fill text-danger" style="font-size:20px;"></i>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold" style="font-size:13px;">Avis de cotisation Provincial 2024.pdf</div>
                                            <span class="text-muted" style="font-size:11px;">18/06/2025 • 220 ko</span>
                                        </div>
                                        <i class="bi-download cpa-doc-dl" @click="showToast('Téléchargement du document...')"></i>
                                    </div>
                                    <div class="cpa-doc-item">
                                        <i class="bi-file-earmark-pdf-fill text-danger" style="font-size:20px;"></i>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold" style="font-size:13px;">Rapport de vérification GEL.pdf</div>
                                            <span class="text-muted" style="font-size:11px;">10/05/2025 • 1,2 Mo</span>
                                        </div>
                                        <i class="bi-download cpa-doc-dl" @click="showToast('Téléchargement du document...')"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- B4. Messagerie (Interactive Chat) -->
                <div v-else-if="section === 'messages'">
                    <div class="cpa-page-header">
                        <h1 class="cpa-page-title">Messagerie avec Fatoumata Koné</h1>
                        <p class="cpa-page-subtitle">Discutez directement avec votre comptable attitré</p>
                    </div>

                    <div class="cpa-card p-0 overflow-hidden d-flex flex-column" style="height: 480px;">
                        <!-- Chat header -->
                        <div class="cpa-card-header bg-light border-bottom d-flex align-items-center gap-2">
                            <div class="cpa-avatar" style="width:32px;height:32px;background:var(--cpa-dark);color:#fff;font-weight:bold;">FK</div>
                            <div>
                                <div class="fw-bold" style="font-size:13px;">Fatoumata Koné</div>
                                <span class="d-flex align-items-center gap-1 text-success" style="font-size:11px;">
                                    <span style="width:6px;height:6px;background:#2e7d32;border-radius:50%;"></span>En ligne
                                </span>
                            </div>
                        </div>

                        <!-- Chat Messages area -->
                        <div id="chat-scroll-box" class="flex-grow-1 p-3 overflow-y-auto d-flex flex-column-reverse" style="background:#f8f9fc;">
                            <div v-if="isTyping" class="cpa-chat-message-wrap rec mt-2">
                                <div class="cpa-chat-bubble typing-dots">
                                    <span></span><span></span><span></span>
                                </div>
                            </div>

                            <div v-for="(msg, i) in clientMessages" :key="i" class="cpa-chat-message-wrap mt-2" :class="msg.from === 'Moi' ? 'sent' : 'rec'">
                                <div class="cpa-chat-meta mb-0.5 text-muted">{{ msg.from }} • {{ formatDateStr(msg.date) }}</div>
                                <div class="cpa-chat-bubble">{{ msg.msg }}</div>
                            </div>
                        </div>

                        <!-- Chat input footer -->
                        <div class="p-3 bg-white border-top">
                            <form @submit.prevent="sendClientMessage" class="input-group">
                                <input v-model="newMessageText" type="text" class="form-control shadow-none border" placeholder="Écrire votre message ici..." style="font-size:13px;">
                                <button type="submit" class="cpa-btn cpa-btn-primary">
                                    <i class="bi-send"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </template>

            <!-- ======================================================= -->
            <!-- ═══ CLIENT ENTREPRISE ═══ -->
            <!-- ======================================================= -->
            <template v-else-if="isCompanyAdmin">
                <!-- C1. Vue d'ensemble (Dashboard) -->
                <div v-if="section === 'dashboard'">
                    <div class="cpa-page-header">
                        <h1 class="cpa-page-title">Tableau de bord Entreprise</h1>
                        <p class="cpa-page-subtitle">Suivez et pilotez la gestion comptable de votre société</p>
                    </div>

                    <!-- Financial KPIs -->
                    <div class="cpa-grid-4">
                        <div class="cpa-card">
                            <div class="cpa-card-body">
                                <div class="cpa-stat-label">Chiffre d'affaires (N)</div>
                                <div class="cpa-stat-value">{{ fmtCurrency(4580000) }} F</div>
                                <div class="cpa-stat-trend up mt-2"><i class="bi-arrow-up-right me-1"></i>+8% vs N-1</div>
                            </div>
                        </div>
                        <div class="cpa-card">
                            <div class="cpa-card-body">
                                <div class="cpa-stat-label">Charges totales</div>
                                <div class="cpa-stat-value text-danger">{{ fmtCurrency(3210000) }} F</div>
                                <div class="cpa-stat-trend down mt-2"><i class="bi-arrow-down-left me-1"></i>-3% d'économies</div>
                            </div>
                        </div>
                        <div class="cpa-card">
                            <div class="cpa-card-body">
                                <div class="cpa-stat-label">Bénéfice net</div>
                                <div class="cpa-stat-value text-success">{{ fmtCurrency(1370000) }} F</div>
                                <div class="cpa-stat-trend up mt-2"><i class="bi-arrow-up-right me-1"></i>+15% de rentabilité</div>
                            </div>
                        </div>
                        <div class="cpa-card">
                            <div class="cpa-card-body">
                                <div class="cpa-stat-label">Effectif salariés</div>
                                <div class="cpa-stat-value">8 salariés</div>
                                <div class="cpa-stat-trend up mt-2">Masse salariale stable</div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        <!-- Alertes list -->
                        <div class="col-lg-6">
                            <div class="cpa-card">
                                <div class="cpa-card-header d-flex justify-content-between align-items-center">
                                    <span><i class="bi-bell-fill text-warning me-2"></i>Alertes et obligations urgentes</span>
                                    <button class="cpa-btn cpa-btn-outline btn-sm py-1 px-2" @click="setSection('echeances')" style="font-size:11px;">Tout voir</button>
                                </div>
                                <div class="cpa-card-body p-0">
                                    <div v-for="a in companyAlerts.filter(a => !a.resolved).slice(0, 3)" :key="a.id" 
                                         :class="['cpa-alert-item', a.type === 'danger' ? 'cpa-alert-danger' : a.type === 'warning' ? 'cpa-alert-warning' : 'cpa-alert-info']">
                                        <i class="bi-exclamation-circle me-2"></i>
                                        <span class="flex-grow-1 fw-medium" style="font-size:12px;">{{ a.label }}</span>
                                        <button class="cpa-btn cpa-btn-primary btn-sm py-0.5 px-2" style="font-size:10px;" @click="resolveAlert(a)">Régler</button>
                                    </div>
                                    <div v-if="companyAlerts.filter(a => !a.resolved).length === 0" class="text-center py-4 text-muted">Toutes vos obligations sont à jour !</div>
                                </div>
                            </div>
                        </div>

                        <!-- Documents récents -->
                        <div class="col-lg-6">
                            <div class="cpa-card">
                                <div class="cpa-card-header">
                                    <span><i class="bi-folder2-open text-primary me-2"></i>Documents financiers partagés</span>
                                </div>
                                <div class="cpa-card-body p-0">
                                    <div class="cpa-doc-item">
                                        <i class="bi-file-earmark-pdf text-danger" style="font-size:18px;"></i>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold" style="font-size:13px;">Bilan Comptable Simplifié 2025.pdf</div>
                                            <span class="text-muted" style="font-size:11px;">Déposé le 12/06/2026</span>
                                        </div>
                                        <i class="bi-download cpa-doc-dl" @click="showToast('Téléchargement du bilan...')"></i>
                                    </div>
                                    <div class="cpa-doc-item">
                                        <i class="bi-file-earmark-pdf text-danger" style="font-size:18px;"></i>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold" style="font-size:13px;">Déclaration TVA Mai 2026.pdf</div>
                                            <span class="text-muted" style="font-size:11px;">Déposé le 10/06/2026</span>
                                        </div>
                                        <i class="bi-download cpa-doc-dl" @click="showToast('Téléchargement de la TVA...')"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Liens rapides / Modules -->
                    <div class="cpa-card mt-3">
                        <div class="cpa-card-header">
                            <span>Accès rapide aux modules de l'entreprise</span>
                        </div>
                        <div class="cpa-card-body">
                            <div class="row g-2">
                                <div class="col-6 col-md-3">
                                    <a href="/company/invoices" class="cpa-quick-link">
                                        <i class="bi-receipt"></i>
                                        <span>Facturation & Devis</span>
                                    </a>
                                </div>
                                <div class="col-6 col-md-3">
                                    <a href="/company/hr" class="cpa-quick-link">
                                        <i class="bi-people"></i>
                                        <span>RH & Paie</span>
                                    </a>
                                </div>
                                <div class="col-6 col-md-3">
                                    <a href="/company/ged" class="cpa-quick-link">
                                        <i class="bi-folder2-open"></i>
                                        <span>GED (Documents)</span>
                                    </a>
                                </div>
                                <div class="col-6 col-md-3">
                                    <a href="/company/accounting" class="cpa-quick-link">
                                        <i class="bi-calculator-fill"></i>
                                        <span>Comptabilité analytique</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- C2. Échéances fiscales détaillées -->
                <div v-else-if="section === 'echeances'">
                    <div class="cpa-page-header">
                        <h1 class="cpa-page-title">Échéancier fiscal & social</h1>
                        <p class="cpa-page-subtitle">Suivez le calendrier de vos déclarations obligatoires</p>
                    </div>

                    <div class="cpa-card">
                        <div class="cpa-card-header">
                            <span>Calendrier des déclarations</span>
                        </div>
                        <div class="cpa-card-body p-0">
                            <div class="table-responsive">
                                <table class="cpa-table">
                                    <thead>
                                        <tr>
                                            <th>Déclaration</th>
                                            <th>Date limite</th>
                                            <th>Catégorie</th>
                                            <th>Statut</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="a in companyAlerts" :key="a.id" :class="{'opacity-50': a.resolved}">
                                            <td class="fw-semibold">{{ a.label }}</td>
                                            <td class="text-muted">{{ a.date }}</td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    {{ a.type === 'danger' ? 'Urgent' : a.type === 'warning' ? 'Impôts' : 'Social' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span :class="['cpa-badge', a.resolved ? 'cpa-badge-success' : a.type === 'danger' ? 'cpa-badge-danger' : 'cpa-badge-warning']">
                                                    {{ a.resolved ? 'Réglé' : 'En attente' }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <button v-if="!a.resolved" class="cpa-btn cpa-btn-primary btn-sm py-1 px-2.5" @click="resolveAlert(a)" style="font-size:11px;">
                                                    Marquer réglé
                                                </button>
                                                <span v-else class="text-success fw-bold" style="font-size:12px;"><i class="bi-check2-all me-1"></i>Traité</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- ======================================================= -->
            <!-- ═══ COMPTABLE ═══ -->
            <!-- ======================================================= -->
            <template v-else-if="isComptable">
                <!-- D1. Vue d'ensemble (Dashboard) -->
                <div v-if="section === 'dashboard'">
                    <div class="cpa-page-header">
                        <h1 class="cpa-page-title">Tableau de bord Comptable</h1>
                        <p class="cpa-page-subtitle">Suivi et avancement de vos dossiers clients assignés</p>
                    </div>

                    <!-- Stats -->
                    <div class="cpa-grid-4">
                        <div class="cpa-card">
                            <div class="cpa-card-body">
                                <div class="cpa-stat-value">{{ comptableDossiers.length }}</div>
                                <div class="cpa-stat-label">Dossiers assignés</div>
                            </div>
                        </div>
                        <div class="cpa-card">
                            <div class="cpa-card-body">
                                <div class="cpa-stat-value text-primary">{{ comptableDossiers.filter(d => d.status === 'en_cours').length }}</div>
                                <div class="cpa-stat-label">Dossiers en cours</div>
                            </div>
                        </div>
                        <div class="cpa-card">
                            <div class="cpa-card-body">
                                <div class="cpa-stat-value text-warning">{{ comptableDossiers.filter(d => d.status === 'en_attente').length }}</div>
                                <div class="cpa-stat-label">Dossiers en attente</div>
                            </div>
                        </div>
                        <div class="cpa-card">
                            <div class="cpa-card-body">
                                <div class="cpa-stat-value text-success">{{ comptableDossiers.filter(d => d.status === 'termine').length }}</div>
                                <div class="cpa-stat-label">Dossiers terminés</div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        <!-- Dossiers brief -->
                        <div class="col-lg-7">
                            <div class="cpa-card">
                                <div class="cpa-card-header d-flex justify-content-between align-items-center">
                                    <span><i class="bi-briefcase me-2 text-primary"></i>Statut de mes dossiers clients</span>
                                    <button class="cpa-btn cpa-btn-outline btn-sm py-1 px-2" @click="setSection('dossiers')" style="font-size:11px;">Gérer</button>
                                </div>
                                <div class="cpa-card-body p-0">
                                    <table class="cpa-table">
                                        <thead>
                                            <tr>
                                                <th>Client</th>
                                                <th>Mission</th>
                                                <th>Statut</th>
                                                <th>Avancement</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="d in comptableDossiers.slice(0, 4)" :key="d.id">
                                                <td class="fw-bold">{{ d.client }}</td>
                                                <td class="text-muted" style="font-size:12px;">{{ d.mission }}</td>
                                                <td><span :class="['cpa-badge', statusClass(d.status)]">{{ statusLabel(d.status) }}</span></td>
                                                <td>
                                                    <div class="cpa-progress" style="width:80px;">
                                                        <div class="cpa-progress-bar" :class="d.progress === 100 ? 'cpa-progress-bar-green' : ''" :style="{ width: d.progress + '%' }"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Tâches brief -->
                        <div class="col-lg-5">
                            <div class="cpa-card">
                                <div class="cpa-card-header d-flex justify-content-between align-items-center">
                                    <span><i class="bi-check-square me-2 text-primary"></i>Tâches urgentes</span>
                                    <button class="cpa-btn cpa-btn-outline btn-sm py-1 px-2" @click="setSection('taches')" style="font-size:11px;">Gérer</button>
                                </div>
                                <div class="cpa-card-body p-0">
                                    <div v-for="t in comptableTasks.filter(t => !t.done).slice(0, 3)" :key="t.id" class="cpa-task-item d-flex align-items-center justify-content-between py-2.5 px-3 border-bottom">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi-circle cursor-pointer text-muted" @click="toggleTask(t)"></i>
                                            <span style="font-size:12.5px;">{{ t.task }}</span>
                                        </div>
                                        <span :class="['cpa-badge', priorityClass(t.priority)]">{{ t.priority }}</span>
                                    </div>
                                    <div v-if="comptableTasks.filter(t => !t.done).length === 0" class="text-center py-4 text-muted">Aucune tâche en attente !</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- D2. Gestion des Dossiers Clients -->
                <div v-else-if="section === 'dossiers'">
                    <div class="cpa-page-header">
                        <h1 class="cpa-page-title">Mes Dossiers Clients</h1>
                        <p class="cpa-page-subtitle">Pilotez le cycle de vie des missions du cabinet</p>
                    </div>

                    <div class="cpa-card">
                        <div class="cpa-card-body p-0 table-responsive">
                            <table class="cpa-table">
                                <thead>
                                    <tr>
                                        <th>Nom du client</th>
                                        <th>Type de mission</th>
                                        <th>Date d'échéance</th>
                                        <th>Statut actuel</th>
                                        <th>Progression</th>
                                        <th class="text-end">Mettre à jour le statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="d in comptableDossiers" :key="d.id" class="align-middle">
                                        <td class="fw-bold">{{ d.client }}</td>
                                        <td>{{ d.mission }}</td>
                                        <td class="text-muted">{{ d.deadline }}</td>
                                        <td><span :class="['cpa-badge', statusClass(d.status)]">{{ statusLabel(d.status) }}</span></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="cpa-progress" style="width:100px;">
                                                    <div class="cpa-progress-bar" :class="d.progress === 100 ? 'cpa-progress-bar-green' : ''" :style="{ width: d.progress + '%' }"></div>
                                                </div>
                                                <span class="fw-semibold" style="font-size:11px;">{{ d.progress }}%</span>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <button class="cpa-btn cpa-btn-outline" @click="updateDossierStatus(d, 'en_cours')">En cours</button>
                                                <button class="cpa-btn cpa-btn-outline" @click="updateDossierStatus(d, 'en_attente')">Attente</button>
                                                <button class="cpa-btn cpa-btn-primary" @click="updateDossierStatus(d, 'termine')">Terminer</button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- D3. Gestionnaire de Tâches Interactif -->
                <div v-else-if="section === 'taches'">
                    <div class="cpa-page-header">
                        <h1 class="cpa-page-title">Mes Tâches Comptables</h1>
                        <p class="cpa-page-subtitle">Organisez votre charge de travail quotidienne</p>
                    </div>

                    <div class="row g-3">
                        <!-- Add Task Form -->
                        <div class="col-md-4">
                            <div class="cpa-card">
                                <div class="cpa-card-header">
                                    <span>Ajouter une tâche</span>
                                </div>
                                <div class="cpa-card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold" style="font-size:12px;">Description de la tâche</label>
                                        <input v-model="newTaskText" type="text" class="form-control shadow-none" placeholder="Ex: Saisir les notes de frais...">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold" style="font-size:12px;">Priorité</label>
                                        <select v-model="newTaskPriority" class="form-select shadow-none">
                                            <option value="haute">Haute</option>
                                            <option value="moyenne">Moyenne</option>
                                            <option value="basse">Basse</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold" style="font-size:12px;">Échéance</label>
                                        <input v-model="newTaskDue" type="text" class="form-control shadow-none" placeholder="Ex: 22/06">
                                    </div>
                                    <button class="cpa-btn cpa-btn-primary w-100 mt-2" @click="addTask">
                                        <i class="bi-plus-lg me-1"></i>Créer la tâche
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Task List -->
                        <div class="col-md-8">
                            <div class="cpa-card">
                                <div class="cpa-card-header">
                                    <span>Liste des tâches</span>
                                </div>
                                <div class="cpa-card-body p-0">
                                    <div v-for="t in comptableTasks" :key="t.id" class="d-flex align-items-center justify-content-between p-3 border-bottom" :style="t.done ? {opacity: '0.6'} : {}">
                                        <div class="d-flex align-items-center gap-3">
                                            <i :class="[t.done ? 'bi-check-circle-fill text-success' : 'bi-circle', 'cursor-pointer']" style="font-size:18px;" @click="toggleTask(t)"></i>
                                            <div>
                                                <div :class="['fw-semibold', t.done ? 'text-decoration-line-through text-muted' : 'text-dark']" style="font-size:13px;">{{ t.task }}</div>
                                                <span class="text-muted" style="font-size:11px;">Échéance : {{ t.due }}</span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span :class="['cpa-badge', priorityClass(t.priority)]">{{ t.priority }}</span>
                                            <button class="btn btn-link text-danger p-0 border-0" @click="deleteTask(t.id)"><i class="bi-trash"></i></button>
                                        </div>
                                    </div>
                                    <div v-if="comptableTasks.length === 0" class="text-center py-5 text-muted">Aucune tâche répertoriée !</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- D4. Calendrier Fiscal -->
                <div v-else-if="section === 'calendrier'">
                    <div class="cpa-page-header">
                        <h1 class="cpa-page-title">Calendrier Fiscal</h1>
                        <p class="cpa-page-subtitle">Suivez et anticipez les dates limites administratives</p>
                    </div>

                    <div class="cpa-card">
                        <div class="cpa-card-header">
                            <span>Échéances fiscales imminentes (2026)</span>
                        </div>
                        <div class="cpa-card-body p-0">
                            <div class="cpa-echeance-item">
                                <div class="cpa-echeance-dot cpa-dot-important"></div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold" style="font-size:13px;">Déclaration mensuelle de TVA (Mai)</div>
                                    <span class="text-muted" style="font-size:11px;">Date limite : 20 Juin 2026</span>
                                </div>
                                <span class="cpa-badge cpa-badge-danger">Important</span>
                            </div>
                            <div class="cpa-echeance-item">
                                <div class="cpa-echeance-dot cpa-dot-important"></div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold" style="font-size:13px;">Transmission des fiches de paie et CNSS</div>
                                    <span class="text-muted" style="font-size:11px;">Date limite : 25 Juin 2026</span>
                                </div>
                                <span class="cpa-badge cpa-badge-danger">Important</span>
                            </div>
                            <div class="cpa-echeance-item">
                                <div class="cpa-echeance-dot"></div>
                                <div class="flex-grow-1">
                                    <div class="fw-medium" style="font-size:13px;">Acompte d'impôt sur les sociétés (IS)</div>
                                    <span class="text-muted" style="font-size:11px;">Date limite : 15 Juillet 2026</span>
                                </div>
                                <span class="cpa-badge cpa-badge-neutral">Standard</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- D5. Messagerie Clients Comptable -->
                <div v-else-if="section === 'messages'">
                    <div class="cpa-page-header">
                        <h1 class="cpa-page-title">Messagerie Clients</h1>
                        <p class="cpa-page-subtitle">Gérez vos conversations avec les clients assignés</p>
                    </div>

                    <div class="cpa-card p-0 overflow-hidden row g-0" style="height: 500px;">
                        <!-- Sidebar clients list -->
                        <div class="col-md-4 border-end h-100 overflow-y-auto" style="background:#f8f9fc;">
                            <div class="p-3 border-bottom fw-bold" style="font-size: 13px;">Mes conversations</div>
                            <div v-for="(msgs, name) in comptableChats" :key="name" 
                                 :class="['p-3', 'border-bottom', 'cursor-pointer', activeChatClient === name ? 'bg-white border-start border-3 border-primary' : '']"
                                 @click="activeChatClient = name">
                                <div class="fw-bold text-dark" style="font-size:12.5px;">{{ name }}</div>
                                <div class="text-muted text-truncate mt-1" style="font-size:11px;">{{ msgs[msgs.length - 1]?.msg }}</div>
                            </div>
                        </div>

                        <!-- Active Chat Window -->
                        <div class="col-md-8 h-100 d-flex flex-column bg-white">
                            <!-- Header -->
                            <div class="p-3 border-bottom bg-light d-flex align-items-center gap-2">
                                <div class="cpa-avatar" style="width:32px;height:32px;background:var(--cpa-dark);color:#fff;font-weight:bold;">
                                    {{ activeChatClient.charAt(0) }}
                                </div>
                                <div class="fw-bold text-dark" style="font-size: 13px;">{{ activeChatClient }}</div>
                            </div>

                            <!-- Chat messages -->
                            <div id="comptable-chat-box" class="flex-grow-1 p-3 overflow-y-auto d-flex flex-column gap-3" style="background:#fafafa;">
                                <div v-for="(msg, i) in currentComptableChat" :key="i" 
                                     :class="['d-flex', 'flex-column', msg.from === 'Moi' ? 'align-items-end' : 'align-items-start']">
                                    <div class="text-muted" style="font-size:10px;margin-bottom:2px;">{{ msg.from }} • {{ formatDateStr(msg.date) }}</div>
                                    <div :class="['cpa-chat-bubble', msg.from === 'Moi' ? 'bg-primary text-white' : 'bg-light text-dark']" 
                                         style="max-width:80%;border-radius:10px;padding:8px 12px;font-size:12.5px;box-shadow: 0 1px 2px rgba(0,0,0,0.05)">
                                        {{ msg.msg }}
                                    </div>
                                </div>
                                <div v-if="isTyping" class="d-flex flex-column align-items-start">
                                    <div class="cpa-chat-bubble bg-light typing-dots">
                                        <span></span><span></span><span></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Input -->
                            <div class="p-3 border-top bg-white">
                                <form @submit.prevent="sendComptableMessage" class="input-group">
                                    <input v-model="newMessageText" type="text" class="form-control shadow-none border" placeholder="Écrire votre message ici..." style="font-size:13px;">
                                    <button type="submit" class="cpa-btn cpa-btn-primary">
                                        <i class="bi-send"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- ======================================================= -->
            <!-- ═══ FALLBACK ═══ -->
            <!-- ======================================================= -->
            <template v-else>
                <div class="cpa-card p-4 text-center">
                    <i class="bi-exclamation-triangle" style="font-size:48px;color:var(--cpa-primary);"></i>
                    <p class="mt-3 text-muted">Votre profil utilisateur ne dispose pas de tableau de bord CPA.</p>
                    <a href="/" class="cpa-btn cpa-btn-primary mt-2">Retourner à l'accueil</a>
                </div>
            </template>

        </div>
    </CpaLayout>
</template>

<style scoped>
/* ═══════════════════════════════════════════════════════
   CPA DASHBOARD STYLE SCOPED
   Fonds blancs dominants, transitions & micro-interactions
   ═══════════════════════════════════════════════════════ */

/* Fade-in animation page */
.cpa-fade-in {
    animation: cpa-fadeIn 0.35s ease-out forwards;
}
@keyframes cpa-fadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ── Spinner de chargement premium ── */
.cpa-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 120px 20px;
    gap: 16px;
    color: var(--cpa-text-muted);
    background: radial-gradient(circle at center, rgba(255,121,0,0.03) 0%, transparent 70%);
}
.cpa-loading-spinner-wrap {
    position: relative;
    width: 56px;
    height: 56px;
}
.cpa-spinner {
    width: 56px;
    height: 56px;
    border: 4px solid rgba(255, 121, 0, 0.08);
    border-top-color: var(--cpa-primary);
    border-bottom-color: rgba(22, 58, 94, 0.25);
    border-radius: 50%;
    animation: cpa-spin 0.8s cubic-bezier(0.4, 0, 0.2, 1) infinite;
    box-shadow: 0 0 20px rgba(255, 121, 0, 0.08);
}
.cpa-loading-text {
    font-size: 13px;
    font-weight: 500;
    letter-spacing: 0.02em;
    animation: cpa-pulse-text 1.5s ease-in-out infinite;
}
@keyframes cpa-spin { to { transform: rotate(360deg); } }
@keyframes cpa-pulse-text {
    0%, 100% { opacity: 0.6; }
    50% { opacity: 1; }
}

/* ── Wave decoration ── */
.cpa-wave-hero {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 80px;
    overflow: hidden;
    pointer-events: none;
    z-index: 0;
}
.cpa-wave-svg {
    width: 100%;
    height: 100%;
}
.cpa-fade-in {
    position: relative;
}

/* ── Background dot pattern ── */
.cpa-fade-in::before {
    content: '';
    position: absolute;
    top: 80px;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: radial-gradient(circle, rgba(0,0,0,0.025) 1px, transparent 1px);
    background-size: 24px 24px;
    pointer-events: none;
    z-index: 0;
}

/* ── Page Header ── */
.cpa-page-header {
    margin-bottom: 24px;
    position: relative;
    z-index: 1;
    padding-bottom: 12px;
}
.cpa-page-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 40px;
    height: 3px;
    background: var(--cpa-primary);
    border-radius: 2px;
    opacity: 0.3;
}
.cpa-page-title {
    font-size: 21px;
    font-weight: 800;
    color: var(--cpa-dark);
    margin: 0;
    letter-spacing: -0.01em;
}
.cpa-page-subtitle {
    font-size: 13px;
    color: var(--cpa-text-muted);
    margin: 4px 0 0;
}

/* ── Grilles ── */
.cpa-grid-4 {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    position: relative;
    z-index: 1;
}
.cpa-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    position: relative;
    z-index: 1;
}
.row {
    position: relative;
    z-index: 1;
}

/* ── Cartes ── */
.cpa-card {
    background: #ffffff;
    border: 1px solid var(--cpa-border);
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    animation: cpa-card-in 0.4s ease-out both;
}
.cpa-card:nth-child(1) { animation-delay: 0.02s; }
.cpa-card:nth-child(2) { animation-delay: 0.06s; }
.cpa-card:nth-child(3) { animation-delay: 0.10s; }
.cpa-card:nth-child(4) { animation-delay: 0.14s; }
.cpa-card:nth-child(5) { animation-delay: 0.18s; }
.cpa-card:nth-child(6) { animation-delay: 0.22s; }
@keyframes cpa-card-in {
    from { opacity: 0; transform: translateY(12px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.cpa-card:hover {
    box-shadow: 0 8px 28px rgba(0,0,0,0.07);
    transform: translateY(-2px);
    border-color: rgba(255, 121, 0, 0.12);
}
.cpa-card-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--cpa-border);
    font-weight: 700;
    font-size: 13.5px;
    color: var(--cpa-dark);
}
.cpa-card-body {
    padding: 20px;
}

/* ── Icon Box & Stats ── */
.cpa-stat-icon-box {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
}
.cpa-card:hover .cpa-stat-icon-box {
    transform: scale(1.08) rotate(-3deg);
}
.bg-primary-soft { background: rgba(255, 121, 0, 0.08); }
.bg-success-soft { background: rgba(46, 125, 50, 0.08); }
.bg-warning-soft { background: rgba(230, 81, 0, 0.08); }
.bg-info-soft { background: rgba(21, 101, 192, 0.08); }

.cpa-stat-value {
    font-size: 26px;
    font-weight: 800;
    color: var(--cpa-dark);
    line-height: 1.2;
}
.cpa-stat-label {
    font-size: 11.5px;
    color: var(--cpa-text-muted);
    font-weight: 500;
}
.cpa-stat-trend {
    font-size: 11px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
}
.cpa-stat-trend.up { color: #2e7d32; }
.cpa-stat-trend.down { color: #c62828; }

/* ── Boutons premium ── */
.cpa-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 12.5px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4,0,0.2,1);
    text-decoration: none;
    letter-spacing: 0.01em;
}
.cpa-btn-primary {
    background: var(--cpa-primary);
    color: #ffffff;
    position: relative;
    overflow: hidden;
}
.cpa-btn-primary::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, transparent 50%);
    pointer-events: none;
}
.cpa-btn-primary:hover {
    background: var(--cpa-primary-hover);
    box-shadow: 0 4px 16px rgba(255, 121, 0, 0.25);
    transform: translateY(-1px);
}
.cpa-btn-primary:active {
    transform: translateY(0);
    box-shadow: 0 2px 8px rgba(255, 121, 0, 0.2);
}
.cpa-btn-outline {
    background: #ffffff;
    color: var(--cpa-text);
    border: 1.5px solid var(--cpa-border);
}
.cpa-btn-outline:hover {
    border-color: var(--cpa-primary);
    color: var(--cpa-primary);
    background: rgba(255,121,0,0.02);
    box-shadow: 0 2px 8px rgba(255,121,0,0.08);
}

/* ── Badges ── */
.cpa-badge {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 10.5px;
    font-weight: 600;
    letter-spacing: 0.02em;
}
.cpa-badge-success { background: #e8f5e9; color: #2e7d32; }
.cpa-badge-warning { background: #fff3e0; color: #e65100; }
.cpa-badge-danger { background: #fdecea; color: #c62828; }
.cpa-badge-info { background: #e3f2fd; color: #1565c0; }
.cpa-badge-neutral { background: #f3f4f6; color: #6b7280; }

/* ── Tableaux ── */
.cpa-table {
    width: 100%;
    border-collapse: collapse;
}
.cpa-table th {
    text-align: left;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--cpa-text-muted);
    padding: 12px 16px;
    border-bottom: 1px solid var(--cpa-border);
    background: #fafbfc;
}
.cpa-table td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--cpa-border);
    font-size: 12.5px;
    color: var(--cpa-text);
}
.cpa-table tr:hover td {
    background: #f8f9fc;
}

/* ── Messagerie Bulles ── */
.cpa-chat-message-wrap {
    display: flex;
    flex-direction: column;
    width: 100%;
}
.cpa-chat-message-wrap.sent {
    align-items: flex-end;
}
.cpa-chat-message-wrap.rec {
    align-items: flex-start;
}
.cpa-chat-meta {
    font-size: 9.5px;
}
.cpa-chat-bubble {
    background: #ffffff;
    border: 1px solid var(--cpa-border);
    border-radius: 10px;
    padding: 8px 12px;
    font-size: 12.5px;
    max-width: 80%;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}
.cpa-chat-message-wrap.sent .cpa-chat-bubble {
    background: var(--cpa-primary);
    color: #ffffff;
    border: none;
}
.typing-dots {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 10px 14px;
}
.typing-dots span {
    width: 6px;
    height: 6px;
    background: #bbb;
    border-radius: 50%;
    animation: typing 1s infinite alternate;
}
.typing-dots span:nth-child(2) { animation-delay: 0.2s; }
.typing-dots span:nth-child(3) { animation-delay: 0.4s; }
@keyframes typing {
    from { transform: translateY(0); }
    to { transform: translateY(-4px); }
}

/* ── Checklist ── */
.cpa-checklist-item {
    display: flex;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f5f6f8;
    gap: 10px;
    font-size: 12.5px;
}
.cpa-checklist-item:last-child {
    border-bottom: none;
}

/* ── Drag & Drop Area ── */
.cpa-file-dropzone {
    border: 2px dashed #d1d5db;
    border-radius: 10px;
    cursor: pointer;
    background: #fafbfc;
    transition: all 0.2s;
}
.cpa-file-dropzone:hover {
    border-color: var(--cpa-primary);
    background: rgba(255, 121, 0, 0.02);
}

/* ── Modals / Popups ── */
.cpa-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1100;
}
.cpa-modal {
    background: #ffffff;
    border-radius: 12px;
    width: 90%;
    max-width: 440px;
    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
}
.cpa-modal-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--cpa-border);
}
.cpa-modal-body {
    padding: 20px;
}
.cpa-modal-footer {
    padding: 12px 20px;
    border-top: 1px solid var(--cpa-border);
    background: #fafbfc;
    border-radius: 0 0 12px 12px;
}

/* ── Toast premium animation ── */
.cpa-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1200;
    display: flex;
    align-items: center;
    gap: 10px;
    background: #1e293b;
    color: #ffffff;
    padding: 12px 20px;
    border-radius: 8px;
    box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
    font-size: 13px;
    font-weight: 500;
}
.cpa-toast-success i { color: #10b981; }
.cpa-toast-warning i { color: #f59e0b; }
.cpa-toast-info i { color: #3b82f6; }

.toast-slide-enter-active,
.toast-slide-leave-active {
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.toast-slide-enter-from {
    transform: translateY(-20px) scale(0.9);
    opacity: 0;
}
.toast-slide-leave-to {
    transform: translateY(-20px) scale(0.9);
    opacity: 0;
}

/* SVG Chart */
.donut {
    transform: rotate(-90deg);
}

/* ── Responsive ── */
@media (max-width: 991.98px) {
    .cpa-grid-4 { grid-template-columns: repeat(2, 1fr); }
    .cpa-grid-3 { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 767.98px) {
    .cpa-grid-4 { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .cpa-grid-3 { grid-template-columns: 1fr; gap: 10px; }
    .cpa-page-title { font-size: 18px; }
    .cpa-stat-value { font-size: 22px; }
}
@media (max-width: 480px) {
    .cpa-grid-4 { grid-template-columns: 1fr; }
}
</style>
