<template>
    <GelLayout>
        <div class="p-4" style="background:#f8f9fa;min-height:100vh">
            <div v-if="error" class="alert alert-danger d-flex align-items-center gap-3">
                <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                <div>
                    <h5 class="mb-1">Élément introuvable</h5>
                    <p class="mb-0">{{ error }}</p>
                    <a href="/juridique/contentieux" class="btn btn-outline-danger btn-sm mt-2">
                        <i class="bi bi-arrow-left me-1"></i> Retour à la liste
                    </a>
                </div>
            </div>
            <div v-else-if="!litige" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="text-muted mt-2">Chargement du litige...</p>
            </div>
            <div v-else class="row g-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="fw-bold mb-1">{{ litige.titre }}</h4>
                            <code>{{ litige.reference }}</code>
                        </div>
                        <ContratStatusBadge :statut="litige.statut" :label="litige.statut" />
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <small class="text-muted">Type</small>
                                    <div class="fw-semibold">{{ litige.type }}</div>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Nature</small>
                                    <div class="fw-semibold">{{ litige.nature }}</div>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Partie adverse</small>
                                    <div class="fw-semibold">{{ litige.partie_adverse }}</div>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Tribunal</small>
                                    <div>{{ litige.tribunal || '—' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">N° dossier</small>
                                    <div>{{ litige.numero_dossier || '—' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Avocat</small>
                                    <div>{{ litige.avocat || '—' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Date saisine</small>
                                    <div>{{ formatDate(litige.date_saisine) }}</div>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Prochaine audience</small>
                                    <div :class="litige.prochaine_audience ? 'fw-bold text-danger' : ''">{{ formatDate(litige.prochaine_audience) }}</div>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Montant du litige</small>
                                    <div class="fw-semibold">{{ litige.montant_litige ? formatCurrency(litige.montant_litige) : '—' }}</div>
                                </div>
                            </div>

                            <div v-if="litige.historique?.length" class="mt-4">
                                <h6 class="fw-semibold">Historique</h6>
                                <div v-for="(h, i) in litige.historique" :key="i" class="border-start border-3 border-primary ps-3 mb-2">
                                    <div class="small text-muted">{{ h.date }} — {{ h.action }}</div>
                                    <p class="mb-0 small">{{ h.details }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <h6 class="fw-semibold">Actions</h6>
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary btn-sm" @click="changerStatut">
                                    <i class="bi bi-arrow-repeat me-1"></i> Changer statut
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-if="litige.documents?.length" class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="fw-semibold">Documents</h6>
                            <div v-for="(d, i) in litige.documents" :key="i" class="d-flex align-items-center gap-2 border-bottom py-2">
                                <i class="bi bi-file-earmark-text"></i>
                                <span class="small">{{ d.nom || d }}</span>
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

const litige = ref(null);
const error = ref(null);
const id = window.location.pathname.split('/').pop();

function formatDate(date) {
    if (!date) return '—';
    return new Date(date + 'T00:00:00').toLocaleDateString('fr-FR');
}
function formatCurrency(val) {
    return Number(val).toLocaleString('fr-FR', { style: 'currency', currency: 'XOF', minimumFractionDigits: 0 });
}

async function load() {
    try {
        const res = await fetch('/juridique/contentieux/' + id);
        if (!res.ok) {
            if (res.status === 404) error.value = 'Ce litige n\'existe pas ou a été supprimé.';
            else error.value = 'Erreur lors du chargement (' + res.status + ').';
            return;
        }
        litige.value = await res.json();
    } catch (e) {
        console.error(e);
        error.value = 'Impossible de charger le litige. Vérifiez votre connexion.';
    }
}

async function changerStatut() {
    const nouveau = prompt('Nouveau statut (assignation, instruction, plaidoirie, jugement, gagné, perdu, transigé, classé) :');
    if (!nouveau) return;
    try {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content;
        const res = await fetch(`/juridique/contentieux/${id}/statut`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ statut: nouveau }),
        });
        if (res.ok) load();
    } catch (e) { console.error(e); }
}

onMounted(load);
</script>
