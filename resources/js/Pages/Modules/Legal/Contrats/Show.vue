<template>
    <GelLayout>
        <div class="p-4" style="background:#f8f9fa;min-height:100vh">
            <div v-if="error" class="alert alert-danger d-flex align-items-center gap-3">
                <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                <div>
                    <h5 class="mb-1">Contrat introuvable</h5>
                    <p class="mb-0">{{ error }}</p>
                    <a href="/juridique/contrats" class="btn btn-outline-danger btn-sm mt-2">
                        <i class="bi bi-arrow-left me-1"></i> Retour à la liste
                    </a>
                </div>
            </div>
            <div v-else-if="!contrat" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="text-muted mt-2">Chargement du contrat...</p>
            </div>
            <div v-else class="row g-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="fw-bold mb-1">{{ contrat.titre }}</h4>
                            <code>{{ contrat.reference }}</code>
                        </div>
                        <ContratStatusBadge :statut="contrat.statut" :label="contrat.statut" />
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <small class="text-muted">Type</small>
                                    <div class="fw-semibold">{{ typeLabel(contrat.type) }}</div>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Montant</small>
                                    <div class="fw-semibold">{{ contrat.montant ? formatCurrency(contrat.montant) : '—' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Devise</small>
                                    <div class="fw-semibold">{{ contrat.devise }}</div>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Date de début</small>
                                    <div>{{ formatDate(contrat.date_debut) }}</div>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Date de fin</small>
                                    <div>{{ formatDate(contrat.date_fin) }}</div>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Renouvellement</small>
                                    <div>{{ contrat.renouvellement_auto ? 'Automatique' : 'Manuel' }}</div>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">Objet</small>
                                    <p class="mb-0">{{ contrat.objet || '—' }}</p>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">Droit applicable</small>
                                    <div>{{ contrat.droit_applicable }}</div>
                                </div>
                            </div>

                            <hr />
                            <h6 class="fw-semibold mb-3">Parties contractantes</h6>
                            <PartiesTable :parties="contrat.parties" />
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <h6 class="fw-semibold">Actions</h6>
                            <div class="d-grid gap-2">
                                <button v-if="contrat.statut === 'brouillon'" class="btn btn-success btn-sm" @click="signer">
                                    <i class="bi bi-pen me-1"></i> Signer
                                </button>
                                <button v-if="['signé', 'actif'].includes(contrat.statut)" class="btn btn-outline-primary btn-sm" @click="renouveler">
                                    <i class="bi bi-arrow-repeat me-1"></i> Renouveler
                                </button>
                                <button v-if="['signé', 'actif'].includes(contrat.statut)" class="btn btn-outline-danger btn-sm" @click="resilier">
                                    <i class="bi bi-x-circle me-1"></i> Résilier
                                </button>
                                <a :href="'/juridique/contrats/' + contrat.id + '/edit'" v-if="contrat.statut === 'brouillon'" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-pencil me-1"></i> Modifier
                                </a>
                            </div>
                        </div>
                    </div>

                    <div v-if="contrat.signatures?.length" class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="fw-semibold">Signatures</h6>
                            <div v-for="s in contrat.signatures" :key="s.id" class="d-flex justify-content-between border-bottom py-2">
                                <div>
                                    <strong>{{ s.signataire_nom }}</strong>
                                    <div class="small text-muted">{{ s.signataire_role }}</div>
                                </div>
                                <ContratStatusBadge :statut="s.statut" :label="s.statut" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';
import ContratStatusBadge from '../../../../Components/Legal/ContratStatusBadge.vue';
import PartiesTable from '../../../../Components/Legal/PartiesTable.vue';

const contrat = ref(null);
const error = ref(null);
const contratId = window.location.pathname.split('/').pop();

const typeLabels = {
    prestation_service: 'Prestation', vente: 'Vente', bail_commercial: 'Bail commercial',
    travail: 'Travail', partenariat: 'Partenariat', confidentialite_nda: 'NDA',
};

function typeLabel(type) { return typeLabels[type] || type; }
function formatDate(date) {
    if (!date) return '—';
    return new Date(date + 'T00:00:00').toLocaleDateString('fr-FR');
}
function formatCurrency(val) {
    return Number(val).toLocaleString('fr-FR', { style: 'currency', currency: 'XOF', minimumFractionDigits: 0 });
}

async function loadContrat() {
    try {
        const res = await fetch('/juridique/contrats/' + contratId);
        if (!res.ok) {
            if (res.status === 404) error.value = 'Ce contrat n\'existe pas ou a été supprimé.';
            else error.value = 'Erreur lors du chargement (' + res.status + ').';
            return;
        }
        contrat.value = await res.json();
    } catch (e) {
        console.error(e);
        error.value = 'Impossible de charger le contrat. Vérifiez votre connexion.';
    }
}

async function signer() {
    if (!confirm('Confirmer la signature ?')) return;
    try {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch(`/juridique/contrats/${contratId}/signer`, {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ signataire_nom: 'GEL Cabinet', signataire_email: '', signataire_role: 'Cabinet' }),
        });
        if (res.ok) { alert('Contrat signé !'); loadContrat(); }
    } catch (e) { console.error(e); }
}

async function renouveler() {
    try {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch(`/juridique/contrats/${contratId}/renouveler`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf } });
        if (res.ok) { alert('Contrat renouvelé !'); loadContrat(); }
    } catch (e) { console.error(e); }
}

async function resilier() {
    if (!confirm('Résilier ce contrat ?')) return;
    try {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch(`/juridique/contrats/${contratId}/resilier`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf } });
        if (res.ok) { alert('Contrat résilié'); loadContrat(); }
    } catch (e) { console.error(e); }
}

onMounted(loadContrat);
</script>
