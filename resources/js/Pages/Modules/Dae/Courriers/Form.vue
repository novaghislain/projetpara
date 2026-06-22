<template>
<GelLayout>
    <div class="dae-courriers-form">
        <!-- ═══ LOADING ═══ -->
        <div v-if="loading" class="dae-loading">
            <div class="dae-spinner"></div>
            <p class="dae-loading-text mt-3">Chargement...</p>
        </div>

        <!-- ═══ FORM CONTENT ═══ -->
        <div v-else class="dae-content">
            <!-- Page Header -->
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">
                        <i class="bi bi-envelope me-2 text-primary"></i>
                        {{ isEditing ? 'Modifier le courrier' : 'Nouveau courrier' }}
                    </h1>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-file-text me-1"></i>
                        {{ isEditing ? 'Mettez a jour les informations du courrier' : 'Remplissez les informations pour creer un nouveau courrier' }}
                    </p>
                </div>
                <div>
                    <button class="btn btn-outline-secondary btn-sm" @click="goBack">
                        <i class="bi bi-arrow-left me-1"></i>Retour a la liste
                    </button>
                </div>
            </div>

            <!-- ═══ ALERTS ═══ -->
            <div v-if="successMessage" class="alert alert-success d-flex align-items-center gap-2 py-2 px-3 mb-3">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ successMessage }}</span>
            </div>
            <div v-if="errorMessage" class="alert alert-danger d-flex align-items-center gap-2 py-2 px-3 mb-3">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>{{ errorMessage }}</span>
            </div>
            <div v-if="Object.keys(errors).length > 0" class="alert alert-danger py-2 px-3 mb-3">
                <ul class="mb-0 ps-3 small">
                    <template v-for="(errList, field) in errors" :key="field">
                        <li v-for="(err, idx) in errList" :key="idx">
                            {{ err }}
                        </li>
                    </template>
                </ul>
            </div>

            <form @submit.prevent="submitForm" enctype="multipart/form-data">
                <div class="row g-3">
                    <!-- ════════════════════════════════════════════ -->
                    <!-- CARD 1 : Informations generales              -->
                    <!-- ════════════════════════════════════════════ -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-transparent border-bottom d-flex align-items-center py-3">
                                <i class="bi bi-info-circle text-primary me-2"></i>
                                <span class="fw-semibold small">Informations generales</span>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <!-- Client -->
                                    <div class="col-md-6">
                                        <label class="form-label small fw-medium">Client <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm" v-model="form.client_id" required>
                                            <option value="">-- Selectionner un client --</option>
                                            <option v-for="client in clients" :key="client.id" :value="client.id">
                                                {{ client.company_name || client.name || client.raison_sociale || 'Client #' + client.id }}
                                            </option>
                                        </select>
                                    </div>
                                    <!-- Reference -->
                                    <div class="col-md-6">
                                        <label class="form-label small fw-medium">Reference</label>
                                        <input type="text" class="form-control form-control-sm" v-model="form.reference"
                                               placeholder="Ex: DAE-2026-001">
                                    </div>
                                    <!-- Objet -->
                                    <div class="col-12">
                                        <label class="form-label small fw-medium">Objet <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" v-model="form.objet"
                                               placeholder="Objet du courrier" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ════════════════════════════════════════════ -->
                        <!-- CARD 2 : Expéditeur & Destinataire          -->
                        <!-- ════════════════════════════════════════════ -->
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-transparent border-bottom d-flex align-items-center py-3">
                                <i class="bi bi-people text-primary me-2"></i>
                                <span class="fw-semibold small">Expediteur & Destinataire</span>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-medium">Expediteur</label>
                                        <input type="text" class="form-control form-control-sm" v-model="form.expediteur"
                                               placeholder="Nom de l'expediteur">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-medium">Destinataire</label>
                                        <input type="text" class="form-control form-control-sm" v-model="form.destinataire"
                                               placeholder="Nom du destinataire">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-medium">Type</label>
                                        <select class="form-select form-select-sm" v-model="form.type">
                                            <option value="entrant">Entrant</option>
                                            <option value="sortant">Sortant</option>
                                            <option value="interne">Interne</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-medium">Mode</label>
                                        <select class="form-select form-select-sm" v-model="form.mode">
                                            <option value="postal">Postal</option>
                                            <option value="email">Email</option>
                                            <option value="remise_main">Remise en main propre</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-medium">Urgence</label>
                                        <select class="form-select form-select-sm" v-model="form.urgence">
                                            <option value="normal">Normal</option>
                                            <option value="urgent">Urgent</option>
                                            <option value="tre_urgent">Tres urgent</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ════════════════════════════════════════════ -->
                        <!-- CARD 3 : Contenu du courrier (WYSIWYG)      -->
                        <!-- ════════════════════════════════════════════ -->
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-transparent border-bottom d-flex align-items-center py-3">
                                <i class="bi bi-pencil-square text-primary me-2"></i>
                                <span class="fw-semibold small">Contenu du courrier</span>
                            </div>
                            <div class="card-body">
                                <!-- WYSIWYG Toolbar -->
                                <div class="dae-editor-toolbar mb-1">
                                    <button type="button" class="dae-editor-btn" title="Gras" @click="execCmd('bold')">
                                        <i class="bi bi-type-bold"></i>
                                    </button>
                                    <button type="button" class="dae-editor-btn" title="Italique" @click="execCmd('italic')">
                                        <i class="bi bi-type-italic"></i>
                                    </button>
                                    <button type="button" class="dae-editor-btn" title="Souligne" @click="execCmd('underline')">
                                        <i class="bi bi-type-underline"></i>
                                    </button>
                                    <span class="dae-editor-sep"></span>
                                    <button type="button" class="dae-editor-btn" title="Liste a puces" @click="execCmd('insertUnorderedList')">
                                        <i class="bi bi-list-ul"></i>
                                    </button>
                                    <button type="button" class="dae-editor-btn" title="Liste numerotee" @click="execCmd('insertOrderedList')">
                                        <i class="bi bi-list-ol"></i>
                                    </button>
                                    <span class="dae-editor-sep"></span>
                                    <button type="button" class="dae-editor-btn" title="Annuler" @click="execCmd('undo')">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                    <button type="button" class="dae-editor-btn" title="Retablir" @click="execCmd('redo')">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                </div>
                                <!-- Editor Area -->
                                <div
                                    ref="editorRef"
                                    class="dae-editor-content"
                                    contenteditable="true"
                                    @input="syncEditorContent"
                                    v-html="form.contenu"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <!-- ════════════════════════════════════════════ -->
                    <!-- SIDEBAR COLUMN                              -->
                    <!-- ════════════════════════════════════════════ -->
                    <div class="col-lg-4">
                        <!-- CARD 4 : Dates -->
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-transparent border-bottom d-flex align-items-center py-3">
                                <i class="bi bi-calendar3 text-primary me-2"></i>
                                <span class="fw-semibold small">Dates</span>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label small fw-medium">Date du courrier</label>
                                    <input type="date" class="form-control form-control-sm" v-model="form.date_courrier">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small fw-medium">Date de reception</label>
                                    <input type="date" class="form-control form-control-sm" v-model="form.date_reception">
                                </div>
                            </div>
                        </div>

                        <!-- CARD 5 : Fichier joint -->
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-transparent border-bottom d-flex align-items-center py-3">
                                <i class="bi bi-paperclip text-primary me-2"></i>
                                <span class="fw-semibold small">Fichier joint</span>
                            </div>
                            <div class="card-body">
                                <div v-if="isEditing && courrier?.fichier_joint" class="mb-2">
                                    <a :href="courrier.fichier_joint" target="_blank" class="d-inline-flex align-items-center gap-1 small">
                                        <i class="bi bi-file-earmark"></i>
                                        Fichier actuel
                                    </a>
                                </div>
                                <input type="file" class="form-control form-control-sm" @change="handleFileChange"
                                       accept=".pdf,.doc,.docx,.png,.jpg,.jpeg">
                                <div class="form-text small">Formats acceptes : PDF, DOC, DOCX, PNG, JPG</div>
                            </div>
                        </div>

                        <!-- CARD 6 : Tags -->
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-transparent border-bottom d-flex align-items-center py-3">
                                <i class="bi bi-tags text-primary me-2"></i>
                                <span class="fw-semibold small">Tags</span>
                            </div>
                            <div class="card-body">
                                <input type="text" class="form-control form-control-sm" v-model="form.tags"
                                       placeholder="Ex: facture, urgence, DAE">
                                <div class="form-text small">Separez les tags par des virgules</div>
                            </div>
                        </div>

                        <!-- Quick info (when editing) -->
                        <div v-if="isEditing && courrier" class="card border-0 shadow-sm">
                            <div class="card-header bg-transparent border-bottom d-flex align-items-center py-3">
                                <i class="bi bi-clock-history text-primary me-2"></i>
                                <span class="fw-semibold small">Informations</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                        <small class="text-muted">Cree le</small>
                                        <small>{{ formatDate(courrier.created_at) }}</small>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                        <small class="text-muted">Statut</small>
                                        <span class="badge" :class="courrier.statut === 'traite' ? 'bg-success' : courrier.statut === 'en_attente' ? 'bg-warning text-dark' : 'bg-secondary'">
                                            {{ courrier.statut || 'Non defini' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ═══ FORM ACTIONS ═══ -->
                <div class="d-flex align-items-center justify-content-between mt-3 pt-3 border-top">
                    <button type="button" class="btn btn-outline-secondary btn-sm" @click="goBack">
                        <i class="bi bi-x-lg me-1"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" :disabled="submitting">
                        <span v-if="submitting" class="spinner-border spinner-border-sm me-1" role="status"></span>
                        <i v-else class="bi bi-check-lg me-1"></i>
                        {{ submitting ? 'Enregistrement...' : (isEditing ? 'Mettre a jour' : 'Creer le courrier') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</GelLayout>
</template>

<script>
import axios from 'axios';

export default {
    name: 'dae-courriers-form',

    props: {
        courrier: {
            type: Object,
            default: null,
        },
        isEditing: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            loading: true,
            submitting: false,
            successMessage: '',
            errorMessage: '',
            errors: {},
            clients: [],
            form: {
                client_id: '',
                reference: '',
                expediteur: '',
                destinataire: '',
                type: 'entrant',
                mode: 'postal',
                objet: '',
                contenu: '',
                urgence: 'normal',
                date_courrier: '',
                date_reception: '',
                fichier_joint: null,
                tags: '',
            },
        };
    },

    created() {
        this.initialize();
    },

    methods: {
        async initialize() {
            await this.fetchClients();
            if (this.isEditing && this.courrier) {
                this.populateForm();
            }
            this.loading = false;
        },

        async fetchClients() {
            try {
                const res = await axios.get('/api/clients');
                this.clients = res.data.clients || res.data.data || res.data || [];
            } catch (e) {
                console.warn('Erreur chargement clients:', e);
                this.clients = [];
            }
        },

        populateForm() {
            if (!this.courrier) return;
            const c = this.courrier;
            this.form.client_id = c.client_id || '';
            this.form.reference = c.reference || '';
            this.form.expediteur = c.expediteur || '';
            this.form.destinataire = c.destinataire || '';
            this.form.type = c.type || 'entrant';
            this.form.mode = c.mode || 'postal';
            this.form.objet = c.objet || '';
            this.form.contenu = c.contenu || '';
            this.form.urgence = c.urgence || 'normal';
            this.form.date_courrier = c.date_courrier ? this.toDateInputValue(c.date_courrier) : '';
            this.form.date_reception = c.date_reception ? this.toDateInputValue(c.date_reception) : '';
            this.form.tags = Array.isArray(c.tags) ? c.tags.join(', ') : (c.tags || '');
        },

        toDateInputValue(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            if (isNaN(d.getTime())) return '';
            return d.toISOString().split('T')[0];
        },

        handleFileChange(event) {
            const file = event.target.files[0];
            this.form.fichier_joint = file || null;
        },

        execCmd(command) {
            document.execCommand(command, false, null);
            this.$refs.editorRef?.focus();
        },

        syncEditorContent() {
            this.form.contenu = this.$refs.editorRef?.innerHTML || '';
        },

        async submitForm() {
            this.submitting = true;
            this.successMessage = '';
            this.errorMessage = '';
            this.errors = {};

            try {
                const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
                const hasFile = this.form.fichier_joint instanceof File;

                let payload;
                let headers = {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                };

                if (hasFile) {
                    payload = new FormData();
                    for (const key of Object.keys(this.form)) {
                        if (key === 'fichier_joint') {
                            if (this.form.fichier_joint) {
                                payload.append(key, this.form.fichier_joint);
                            }
                        } else {
                            payload.append(key, this.form[key]);
                        }
                    }
                    if (this.isEditing) {
                        payload.append('_method', 'PUT');
                    }
                } else {
                    headers['Content-Type'] = 'application/json';
                    payload = { ...this.form };
                    delete payload.fichier_joint;
                }

                let response;
                if (this.isEditing && this.courrier?.id) {
                    const url = `/dae/courriers/${this.courrier.id}`;
                    response = await axios.post(url, payload, { headers });
                } else {
                    response = await axios.post('/dae/courriers', payload, { headers });
                }

                this.successMessage = response.data.message || 'Courrier enregistre avec succes.';

                // Redirect after short delay
                setTimeout(() => {
                    if (typeof this.$inertia !== 'undefined') {
                        this.$inertia.visit('/dae/courriers');
                    } else {
                        window.location.href = '/dae/courriers';
                    }
                }, 800);
            } catch (e) {
                if (e.response?.status === 422 && e.response.data?.errors) {
                    this.errors = e.response.data.errors;
                    this.errorMessage = 'Veuillez corriger les erreurs ci-dessous.';
                } else {
                    this.errorMessage = e.response?.data?.message || e.message || 'Une erreur est survenue.';
                }
            } finally {
                this.submitting = false;
            }
        },

        goBack() {
            if (typeof this.$inertia !== 'undefined') {
                this.$inertia.visit('/dae/courriers');
            } else {
                window.location.href = '/dae/courriers';
            }
        },

        formatDate(dateStr) {
            if (!dateStr) return '-';
            return new Date(dateStr).toLocaleDateString('fr-FR', {
                day: '2-digit', month: 'short', year: 'numeric',
                hour: '2-digit', minute: '2-digit',
            });
        },
    },
};
</script>

<style scoped>
/* ═══════════════════════════════════════════════════════
   DAE Courriers Form Styles
   ═══════════════════════════════════════════════════════ */

.dae-courriers-form {
    padding: 20px;
    min-height: 80vh;
}

/* ── Loading State ── */
.dae-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 120px 20px;
}
.dae-spinner {
    width: 48px;
    height: 48px;
    border: 4px solid rgba(255, 121, 0, 0.1);
    border-top-color: #FF7900;
    border-radius: 50%;
    animation: dae-spin 0.7s linear infinite;
}
@keyframes dae-spin {
    to { transform: rotate(360deg); }
}
.dae-loading-text {
    color: #6c757d;
    font-size: 14px;
    font-weight: 500;
}

/* ── WYSIWYG Editor ── */
.dae-editor-toolbar {
    display: flex;
    align-items: center;
    gap: 2px;
    padding: 6px 8px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-bottom: none;
    border-radius: 4px 4px 0 0;
    flex-wrap: wrap;
}
.dae-editor-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border: none;
    background: transparent;
    border-radius: 3px;
    color: #495057;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.15s ease;
}
.dae-editor-btn:hover {
    background: #e9ecef;
}
.dae-editor-btn:active {
    background: #dee2e6;
}
.dae-editor-sep {
    display: inline-block;
    width: 1px;
    height: 20px;
    background: #dee2e6;
    margin: 0 4px;
}
.dae-editor-content {
    min-height: 180px;
    max-height: 400px;
    overflow-y: auto;
    padding: 10px 12px;
    border: 1px solid #dee2e6;
    border-top: none;
    border-radius: 0 0 4px 4px;
    font-size: 14px;
    line-height: 1.6;
    color: #212529;
    background: #fff;
    cursor: text;
    outline: none;
}
.dae-editor-content:focus {
    border-color: #FF7900;
    box-shadow: 0 0 0 2px rgba(255, 121, 0, 0.1);
}
.dae-editor-content:empty::before {
    content: attr(data-placeholder);
    color: #adb5bd;
}
.dae-editor-content ul,
.dae-editor-content ol {
    padding-left: 24px;
    margin-bottom: 0;
}

/* ── Form overrides for DAE theme ── */
.form-label {
    color: #163A5E;
    margin-bottom: 4px;
}
.form-control-sm:focus,
.form-select-sm:focus {
    border-color: #FF7900;
    box-shadow: 0 0 0 2px rgba(255, 121, 0, 0.1);
}
.btn-primary {
    background-color: #FF7900;
    border-color: #FF7900;
}
.btn-primary:hover,
.btn-primary:focus {
    background-color: #e66d00;
    border-color: #e66d00;
}
.btn-primary:disabled {
    background-color: #ff9f4a;
    border-color: #ff9f4a;
}
.btn-outline-primary {
    color: #FF7900;
    border-color: #FF7900;
}
.btn-outline-primary:hover {
    background-color: #FF7900;
    border-color: #FF7900;
    color: #fff;
}
.card {
    border-radius: 8px;
}
.card-header {
    border-radius: 8px 8px 0 0 !important;
}

/* ── Small badge helpers ── */
.badge {
    font-weight: 500;
    font-size: 11px;
}

/* ── Alert custom colors ── */
.alert-success {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
}
.alert-danger {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}
</style>
