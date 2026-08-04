<template>
    <GelLayout>
        <div class="p-4" style="background:#f8f9fa;min-height:100vh">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">✅ Conformité Réglementaire</h4>
                <div class="d-flex gap-2">
                    <a href="/juridique/conformite/calendrier" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-calendar-week me-1"></i> Calendrier
                    </a>
                    <a href="/juridique/conformite/create" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Nouvelle obligation
                    </a>
                </div>
            </div>

            <div v-if="error" class="alert alert-danger">⚠️ {{ error }}</div>
            <div class="row g-3">
                <div v-for="c in conformites" :key="c.id" class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge" :class="c.statut === 'conforme' ? 'bg-success' : c.statut === 'en_retard' ? 'bg-danger' : c.statut === 'a_venir' ? 'bg-warning text-dark' : 'bg-secondary'">{{ c.statut }}</span>
                                <small class="text-muted">{{ c.type }}</small>
                            </div>
                            <h6 class="fw-semibold">{{ c.intitule }}</h6>
                            <div class="small text-muted mb-2">
                                <i class="bi bi-building me-1"></i>{{ c.organisme }}
                            </div>
                            <div class="d-flex justify-content-between small border-top pt-2">
                                <span>
                                    <i class="bi bi-calendar me-1"></i>
                                    Échéance : <strong :class="isOverdue(c.date_echeance) ? 'text-danger' : ''">{{ formatDate(c.date_echeance) }}</strong>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="!conformites.length" class="col-12 text-center text-muted py-4">Aucune obligation de conformité</div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
/*
 * Composant : Index.vue (Conformite)
 * Description : Liste des obligations de conformité réglementaire avec affichage
 *               du statut (conforme, en retard, à venir) et de l'échéance.
 *               Permet d'accéder au calendrier et de créer une nouvelle obligation.
 * Route       : /juridique/conformite
 */
import { ref, onMounted } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

/* Liste des obligations et message d'erreur éventuel */
const conformites = ref([]);
const error = ref(null);

/* Formate une date ISO au format français */
function formatDate(date) {
    if (!date) return '—';
    return new Date(date + 'T00:00:00').toLocaleDateString('fr-FR');
}

/* Vérifie si la date d'échéance est dépassée */
function isOverdue(date) {
    if (!date) return false;
    return new Date(date + 'T00:00:00') < new Date();
}

/* Charge les obligations depuis l'API */
async function load() {
    error.value = null;
    try {
        const res = await fetch('/juridique/conformite');
        if (!res.ok) throw new Error('Erreur ' + res.status);
        conformites.value = await res.json();
    } catch (e) {
        console.error(e);
        error.value = 'Impossible de charger les obligations.';
    }
}
onMounted(load);
</script>
