<script setup>
import { ref } from 'vue';
import CompanyLayout from '../../../Layouts/CompanyLayout.vue';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    }
});

const contenu = ref('');
const processing = ref(false);
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const sendMessage = async () => {
    if (!contenu.value.trim()) return;
    
    processing.value = true;
    try {
        const res = await fetch(`/mes-commandes/${props.order.id}/messages`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ contenu: contenu.value })
        });
        
        if (res.ok || res.redirected) {
            window.location.reload();
        }
    } catch (e) {
        console.error(e);
    } finally {
        processing.value = false;
    }
};

const getStatusColor = (status) => {
    const colors = {
        'Nouvelle Demande': 'bg-primary text-white',
        'En cours': 'bg-warning text-dark',
        'En attente client': 'bg-info text-white',
        'Livrée': 'bg-success text-white',
        'Annulée': 'bg-danger text-white',
    };
    return colors[status] || 'bg-secondary text-white';
};
</script>

<template>
    <CompanyLayout :page-title="`Commande ${order.reference}`">
        <div class="py-4">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="row mb-4 align-items-center">
                    <div class="col-auto">
                        <a href="/mes-commandes" class="btn btn-link text-decoration-none px-0">
                            <i class="bi-arrow-left me-2"></i>Retour
                        </a>
                    </div>
                </div>

                <!-- En-tête de la commande -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                    <div class="card-body p-4 p-md-5 border-bottom">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                            <div>
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <h2 class="h3 fw-bold mb-0 text-dark font-heading">Commande {{ order.reference }}</h2>
                                    <span class="badge rounded-pill" :class="getStatusColor(order.statut)">
                                        {{ order.statut }}
                                    </span>
                                </div>
                                <p class="mb-0 text-muted">Service : <span class="fw-bold text-dark">{{ order.service?.nom }}</span></p>
                            </div>
                            <div class="mt-3 mt-md-0 text-md-end">
                                <div class="small text-muted">Date de la commande</div>
                                <div class="fw-bold text-dark">{{ new Date(order.date_commande).toLocaleDateString('fr-FR') }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-light border-0 p-4">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <h6 class="text-uppercase text-muted small fw-bold mb-2">Détails financiers</h6>
                                <p class="mb-0">
                                    <span class="text-muted">Montant :</span> 
                                    <span class="fw-bold text-dark ms-1">{{ order.montant_estime_fcfa ? `${Number(order.montant_estime_fcfa).toLocaleString('fr-FR')} FCFA` : 'Sur devis' }}</span>
                                </p>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-uppercase text-muted small fw-bold mb-2">Délai estimé</h6>
                                <p class="mb-0 fw-bold text-dark">{{ order.delai_estime || 'Non défini' }}</p>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-uppercase text-muted small fw-bold mb-2">Responsable</h6>
                                <p class="mb-0 fw-bold text-dark">{{ order.responsable ? order.responsable.name : 'En attente d\'assignation' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    
                    <!-- Colonne de gauche: Historique & Informations -->
                    <div class="col-lg-5">
                        <!-- Données du formulaire -->
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-header bg-white border-bottom pt-4 pb-3 px-4">
                                <h5 class="mb-0 fw-bold font-heading">Informations fournies</h5>
                            </div>
                            <div class="card-body p-4">
                                <dl class="mb-0 row gy-3">
                                    <template v-for="(value, key) in order.form_data" :key="key">
                                        <dt class="col-sm-5 text-muted small text-capitalize">{{ key.replace(/_/g, ' ') }}</dt>
                                        <dd class="col-sm-7 fw-medium text-dark mb-0">{{ value || '-' }}</dd>
                                    </template>
                                </dl>
                                <div v-if="!order.form_data || Object.keys(order.form_data).length === 0" class="text-muted small fst-italic">
                                    Aucune information supplémentaire fournie.
                                </div>
                            </div>
                        </div>

                        <!-- Historique des statuts -->
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-header bg-white border-bottom pt-4 pb-3 px-4">
                                <h5 class="mb-0 fw-bold font-heading">Historique</h5>
                            </div>
                            <div class="card-body p-4">
                                <ul class="list-unstyled mb-0 position-relative">
                                    <li v-for="history in order.status_history" :key="history.id" class="d-flex align-items-start mb-4 position-relative">
                                        <div class="flex-shrink-0 mt-1">
                                            <div class="bg-primary rounded-circle" style="width: 12px; height: 12px;"></div>
                                        </div>
                                        <div class="ms-3">
                                            <p class="fw-bold text-dark mb-0">{{ history.statut_nouveau }}</p>
                                            <p class="small text-muted mb-1">{{ new Date(history.created_at).toLocaleString('fr-FR') }}</p>
                                            <p v-if="history.commentaire" class="small text-dark mt-1 bg-light p-2 rounded-3 border">{{ history.commentaire }}</p>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Documents (Factures & Résultats) -->
                        <div class="card border-0 shadow-sm rounded-4 mt-4">
                            <div class="card-header bg-white border-bottom pt-4 pb-3 px-4">
                                <h5 class="mb-0 fw-bold font-heading">Livrables & Factures</h5>
                            </div>
                            <div class="card-body p-4">
                                <div v-if="order.documents && order.documents.length">
                                    <ul class="list-group list-group-flush">
                                        <li v-for="doc in order.documents" :key="doc.id" class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="bi-file-earmark-pdf text-danger me-2"></i>
                                                <span class="fw-medium">{{ doc.nom_fichier }}</span>
                                                <span class="badge bg-light text-dark ms-2">{{ doc.type }}</span>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <a :href="`/storage/${doc.chemin_stockage}`" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                    Ouvrir <i class="bi-box-arrow-up-right ms-1"></i>
                                                </a>
                                                <a :href="`/mes-commandes/documents/${doc.id}/download`" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                    Télécharger <i class="bi-download ms-1"></i>
                                                </a>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div v-else class="text-muted small fst-italic">
                                    Aucun document n'a encore été mis à disposition par notre équipe.
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Colonne de droite: Messages & Documents -->
                    <div class="col-lg-7">
                        
                        <!-- Messagerie intégrée -->
                        <div class="card border-0 shadow-sm rounded-4 d-flex flex-column" style="height: 600px;">
                            <div class="card-header bg-white border-bottom pt-4 pb-3 px-4">
                                <h5 class="mb-0 fw-bold font-heading">Discussion avec l'équipe</h5>
                            </div>
                            
                            <div class="card-body flex-grow-1 overflow-auto bg-light p-4 d-flex flex-column gap-3">
                                <div v-for="message in order.messages" :key="message.id" 
                                     class="p-3 shadow-sm"
                                     :class="message.type === 'client' ? 'align-self-end bg-primary text-white rounded-4 rounded-bottom-end-0' : 'align-self-start bg-white text-dark rounded-4 rounded-bottom-start-0 border'"
                                     style="max-width: 80%;">
                                    <div class="small opacity-75 d-flex justify-content-between mb-1">
                                        <span>{{ message.type === 'client' ? 'Vous' : (message.expediteur?.name || 'Équipe GEL SABINET') }}</span>
                                        <span class="ms-3">{{ new Date(message.created_at).toLocaleTimeString('fr-FR', {hour: '2-digit', minute:'2-digit'}) }}</span>
                                    </div>
                                    <div class="whitespace-pre-wrap">{{ message.contenu }}</div>
                                </div>
                                <div v-if="order.messages.length === 0" class="text-center text-muted small fst-italic my-auto">
                                    Aucun message. Envoyez un message pour démarrer la discussion.
                                </div>
                            </div>

                            <div class="card-footer bg-white border-top p-3">
                                <form @submit.prevent="sendMessage" class="d-flex gap-2">
                                    <textarea v-model="contenu" rows="1" placeholder="Écrivez votre message..." required
                                        class="form-control rounded-pill px-4 py-2 resize-none"></textarea>
                                    <button type="submit" :disabled="processing" class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                        <i class="bi-send-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
                
            </div>
        </div>
    </CompanyLayout>
</template>
