<script setup>
import { ref, reactive } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const props = defineProps({
    order: { type: Object, required: true },
    team: { type: Array, required: true },
    statuts: { type: Array, required: true },
});

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// État local réactif
const contenu = ref('');
const responsable_id = ref(props.order.responsable_id || '');
const statut = ref(props.order.statut);
const montant = ref(props.order.montant_estime_fcfa || '');
const delai = ref(props.order.delai_estime || '');
const notes = ref(props.order.notes_internes || '');
const msgProcessing = ref(false);
const statusProcessing = ref(false);
const assignProcessing = ref(false);
const detailsProcessing = ref(false);

async function sendMessage() {
    if (!contenu.value.trim()) return;
    msgProcessing.value = true;
    try {
        await fetch(`/admin/catalogue/orders/${props.order.id}/messages`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ contenu: contenu.value })
        });
        window.location.reload();
    } catch (e) { console.error(e); msgProcessing.value = false; }
}

async function updateStatus() {
    statusProcessing.value = true;
    try {
        await fetch(`/admin/catalogue/orders/${props.order.id}/status`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ statut: statut.value })
        });
        window.location.reload();
    } catch (e) { console.error(e); statusProcessing.value = false; }
}

async function quickChangeStatus(newStatus, comment = '') {
    if (!confirm(`Confirmer le passage au statut: ${newStatus} ?`)) return;
    statusProcessing.value = true;
    try {
        await fetch(`/admin/catalogue/orders/${props.order.id}/status`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ statut: newStatus, commentaire: comment })
        });
        window.location.reload();
    } catch (e) { console.error(e); statusProcessing.value = false; }
}

async function updateAssignation() {
    assignProcessing.value = true;
    try {
        await fetch(`/admin/catalogue/orders/${props.order.id}/assign`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ responsable_id: responsable_id.value })
        });
        window.location.reload();
    } catch (e) { console.error(e); assignProcessing.value = false; }
}

async function updateDetails() {
    detailsProcessing.value = true;
    try {
        await fetch(`/admin/catalogue/orders/${props.order.id}/details`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ montant_estime_fcfa: montant.value, delai_estime: delai.value, notes_internes: notes.value })
        });
        window.location.reload();
    } catch (e) { console.error(e); detailsProcessing.value = false; }
}

const getStatusBadge = (s) => {
    const colors = {
        'Nouvelle Demande': 'bg-primary text-white',
        'En cours': 'bg-warning text-dark',
        'En attente client': 'bg-info text-white',
        'Livrée': 'bg-success text-white',
        'Annulée': 'bg-danger text-white',
    };
    return colors[s] || 'bg-secondary text-white';
};
const docUpload = reactive({
    type: '',
    titre: '',
    file: null
});
const docProcessing = ref(false);

const onFileChange = (e) => {
    docUpload.file = e.target.files[0];
};

async function uploadDocument() {
    if (!docUpload.file) return;
    docProcessing.value = true;
    
    const formData = new FormData();
    formData.append('fichier', docUpload.file);
    formData.append('type', docUpload.type);
    formData.append('titre', docUpload.titre);
    
    try {
        await fetch(`/admin/catalogue/orders/${props.order.id}/documents`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        });
        window.location.reload();
    } catch (e) {
        console.error(e);
        docProcessing.value = false;
    }
}
</script>

<template>
    <GelLayout :page-title="`Commande ${order.reference}`">
        <div class="py-4 px-2">

            <!-- En-tête Global -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="/admin/catalogue/orders" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
                    <i class="bi-arrow-left me-2"></i> Retour aux commandes
                </a>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted small">Créée le {{ new Date(order.created_at).toLocaleString('fr-FR') }}</span>
                    <span class="badge rounded-pill fs-6 px-3 py-2" :class="getStatusBadge(order.statut)">{{ order.statut }}</span>
                </div>
            </div>

            <div class="row g-4">
                <!-- COLONNE GAUCHE (Contenu principal) -->
                <div class="col-lg-8">

                    <!-- Résumé de la demande -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4 p-md-5">
                            <h2 class="h5 text-primary mb-1 fw-bold">{{ order.service?.nom }}</h2>
                            <p class="text-muted small mb-4">{{ order.category?.nom }}</p>
                            
                            <div class="row g-4 p-4 bg-light rounded-4">
                                <div class="col-md-6 border-end-md">
                                    <h6 class="text-muted small fw-bold mb-3 text-uppercase">Informations Client</h6>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            <i class="bi-person-fill fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ order.client?.name }}</div>
                                            <div class="small text-muted">{{ order.client?.email }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 pl-md-4">
                                    <h6 class="text-muted small fw-bold mb-3 text-uppercase">Détails Commerciaux</h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small">Montant estimé:</span>
                                        <span class="fw-bold text-dark">{{ order.montant_estime_fcfa ? Number(order.montant_estime_fcfa).toLocaleString('fr-FR') + ' FCFA' : 'Sur devis' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-0">
                                        <span class="text-muted small">Délai estimé:</span>
                                        <span class="fw-bold text-dark">{{ order.delai_estime || 'Non défini' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulaire dynamique -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-bottom pt-4 pb-3 px-4">
                            <h5 class="mb-0 fw-bold font-heading"><i class="bi-ui-radios me-2 text-primary"></i> Formulaire rempli par le client</h5>
                        </div>
                        <div class="card-body p-4">
                            <div v-if="order.form_data && Object.keys(order.form_data).length > 0" class="row gy-4">
                                <div v-for="(value, key) in order.form_data" :key="key" class="col-md-6">
                                    <div class="text-muted small text-capitalize mb-1">{{ key.replace(/_/g, ' ') }}</div>
                                    <div class="fw-medium text-dark bg-light p-3 rounded-3">{{ value || '-' }}</div>
                                </div>
                            </div>
                            <div v-else class="text-muted fst-italic text-center py-4">Aucune donnée de formulaire fournie.</div>
                        </div>
                    </div>

                    <!-- Documents & Livrables -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-bottom pt-4 pb-3 px-4">
                            <h5 class="mb-0 fw-bold font-heading"><i class="bi-folder2-open me-2 text-primary"></i> Documents & Livrables</h5>
                        </div>
                        <div class="card-body p-4">
                            
                            <!-- Liste des documents -->
                            <div class="mb-4" v-if="order.documents && order.documents.length">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light text-muted small">
                                            <tr>
                                                <th>Nom du fichier</th>
                                                <th>Type</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <li v-for="doc in order.documents" :key="doc.id" style="display:contents">
                                            <tr>
                                                <td>
                                                    <i class="bi-file-earmark-pdf text-danger me-2"></i>
                                                    <span class="fw-medium">{{ doc.nom_fichier }}</span>
                                                </td>
                                                <td><span class="badge bg-secondary">{{ doc.type }}</span></td>
                                                <td class="text-end">
                                                    <div class="d-flex gap-2 justify-content-end">
                                                        <a :href="`/storage/${doc.chemin_stockage}`" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Ouvrir</a>
                                                        <a :href="`/storage/${doc.chemin_stockage}`" download class="btn btn-sm btn-outline-primary rounded-pill px-3"><i class="bi-download"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            </li>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div v-else class="text-center text-muted fst-italic py-4 mb-4 bg-light rounded-4">
                                Aucun document rattaché à cette commande.
                            </div>

                            <!-- Upload de document -->
                            <form @submit.prevent="uploadDocument" enctype="multipart/form-data" class="bg-primary bg-opacity-10 p-4 rounded-4 border border-primary border-opacity-25">
                                <h6 class="fw-bold mb-3 text-primary"><i class="bi-cloud-arrow-up me-2"></i> Ajouter un document</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <select v-model="docUpload.type" class="form-select border-0 shadow-sm" required>
                                            <option value="" disabled>Type de document...</option>
                                            <option value="facture">Facture</option>
                                            <option value="resultat">Résultat / Livrable</option>
                                            <option value="autre">Autre document</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" v-model="docUpload.titre" placeholder="Titre (optionnel)" class="form-control border-0 shadow-sm">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="file" @change="onFileChange" class="form-control border-0 shadow-sm bg-white" required>
                                    </div>
                                    <div class="col-12 mt-3 text-end">
                                        <button type="submit" :disabled="docProcessing || !docUpload.file" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                            <span v-if="docProcessing"><i class="spinner-border spinner-border-sm me-2"></i> Envoi...</span>
                                            <span v-else>Envoyer le fichier</span>
                                        </button>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>

                    <!-- Messagerie -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-bottom pt-4 pb-3 px-4">
                            <h5 class="mb-0 fw-bold font-heading"><i class="bi-chat-dots me-2 text-primary"></i> Messagerie Client</h5>
                        </div>
                        <div class="card-body p-0 d-flex flex-column" style="height: 500px;">
                            <div class="flex-grow-1 overflow-auto p-4 d-flex flex-column gap-3 bg-light">
                                <div v-for="message in order.messages" :key="message.id"
                                     class="p-3 shadow-sm"
                                     :class="message.type === 'equipe' ? 'align-self-end bg-primary bg-opacity-10 text-dark rounded-4 rounded-bottom-end-0' : 'align-self-start bg-white text-dark rounded-4 rounded-bottom-start-0 border border-light'"
                                     style="max-width: 80%;">
                                    <div class="d-flex justify-content-between mb-2 small opacity-75">
                                        <span class="fw-bold">{{ message.type === 'equipe' ? (message.expediteur?.name || 'Moi') : order.client?.name }}</span>
                                        <span class="ms-4">{{ new Date(message.created_at).toLocaleTimeString('fr-FR', {hour:'2-digit', minute:'2-digit'}) }}</span>
                                    </div>
                                    <div class="small whitespace-pre-wrap">{{ message.contenu }}</div>
                                </div>
                                <div v-if="order.messages.length === 0" class="text-center text-muted small fst-italic my-auto">
                                    Aucun message échangé pour le moment.
                                </div>
                            </div>
                            <div class="p-4 bg-white border-top">
                                <form @submit.prevent="sendMessage" class="d-flex gap-3">
                                    <textarea v-model="contenu" rows="1" placeholder="Écrivez votre message au client..." required
                                        class="form-control bg-light border-0 rounded-pill px-4 py-2" style="resize:none; overflow-y:hidden;"></textarea>
                                    <button type="submit" :disabled="msgProcessing" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                        <i class="bi-send-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- COLONNE DROITE (Gestion et Historique) -->
                <div class="col-lg-4">

                    <!-- Actions Administratives -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-bottom pt-4 pb-3 px-4">
                            <h5 class="mb-0 fw-bold font-heading"><i class="bi-gear me-2 text-primary"></i> Pilotage</h5>
                        </div>
                        <div class="card-body p-4">
                            <!-- Statut -->
                            <form @submit.prevent="updateStatus" class="mb-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Statut de la commande</label>
                                <div class="d-flex gap-2">
                                    <select v-model="statut" class="form-select bg-light border-0">
                                        <option v-for="st in statuts" :key="st" :value="st">{{ st }}</option>
                                    </select>
                                    <button type="submit" :disabled="statusProcessing" class="btn btn-dark rounded-3 px-3">OK</button>
                                </div>
                            </form>

                            <!-- Assignation -->
                            <form @submit.prevent="updateAssignation" class="mb-4 pt-4 border-top">
                                <label class="form-label small fw-bold text-muted text-uppercase">Responsable</label>
                                <div class="d-flex gap-2">
                                    <select v-model="responsable_id" class="form-select bg-light border-0">
                                        <option value="">-- Non assigné --</option>
                                        <option v-for="user in team" :key="user.id" :value="user.id">{{ user.name }}</option>
                                    </select>
                                    <button type="submit" :disabled="assignProcessing" class="btn btn-dark rounded-3 px-3">OK</button>
                                </div>
                            </form>

                            <!-- Actions Rapides -->
                            <div class="pt-4 border-top">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Actions rapides</label>
                                <div class="d-flex flex-column gap-2">
                                    <button v-if="order.statut !== 'Livrée' && order.statut !== 'Annulée'"
                                            type="button"
                                            @click="quickChangeStatus('Livrée', 'Projet finalisé et livré')"
                                            :disabled="statusProcessing"
                                            class="btn btn-sm btn-success text-white fw-bold py-2 w-100"
                                            style="font-size: 12px; background-color: #2e7d32; border-color: #2e7d32;">
                                        <i class="bi-check-circle me-1"></i>Finaliser &amp; Livrer
                                    </button>
                                    <button v-if="order.statut !== 'Annulée' && order.statut !== 'Livrée'"
                                            type="button"
                                            @click="quickChangeStatus('Annulée', 'Projet arrêté / annulé')"
                                            :disabled="statusProcessing"
                                            class="btn btn-sm btn-danger text-white fw-bold py-2 w-100"
                                            style="font-size: 12px; background-color: #c62828; border-color: #c62828;">
                                        <i class="bi-x-circle me-1"></i>Mettre fin (Annuler)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes & Finance -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-bottom pt-4 pb-3 px-4">
                            <h5 class="mb-0 fw-bold font-heading"><i class="bi-currency-exchange me-2 text-primary"></i> Commercial</h5>
                        </div>
                        <form @submit.prevent="updateDetails" class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Montant Facturé (FCFA)</label>
                                <input type="number" v-model="montant" class="form-control bg-light border-0">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Délai estimé</label>
                                <input type="text" v-model="delai" placeholder="ex: 72 heures" class="form-control bg-light border-0">
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Notes internes</label>
                                <textarea v-model="notes" rows="3" class="form-control bg-warning bg-opacity-10 border-warning" placeholder="Notes invisibles au client..."></textarea>
                            </div>
                            <button type="submit" :disabled="detailsProcessing" class="btn btn-outline-primary w-100 rounded-pill">
                                Mettre à jour
                            </button>
                        </form>
                    </div>

                    <!-- Historique (Vertical Timeline) -->
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-bottom pt-4 pb-3 px-4">
                            <h5 class="mb-0 fw-bold font-heading"><i class="bi-clock-history me-2 text-primary"></i> Historique</h5>
                        </div>
                        <div class="card-body p-4 position-relative">
                            <!-- Barre verticale -->
                            <div class="position-absolute bg-light" style="width: 2px; top: 2rem; bottom: 2rem; left: 2.25rem;"></div>
                            
                            <ul class="list-unstyled mb-0 position-relative z-1">
                                <li v-for="history in order.status_history" :key="history.id" class="d-flex mb-4">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-primary border border-white border-3 rounded-circle" style="width: 16px; height: 16px; margin-top: 4px;"></div>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark small">{{ history.statut_nouveau }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            Par {{ history.user?.name || 'Système' }} le {{ new Date(history.created_at).toLocaleString('fr-FR') }}
                                        </div>
                                        <div v-if="history.commentaire" class="mt-2 small text-dark fst-italic p-2 bg-light rounded-3">
                                            "{{ history.commentaire }}"
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </GelLayout>
</template>


