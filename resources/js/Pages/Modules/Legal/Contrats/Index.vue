<template>
    <GelLayout>
        <div class="p-4" style="background:#f8f9fa;min-height:100vh">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">📄 Contrats Juridiques</h4>
                <a href="/juridique/contrats/create" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Nouveau contrat
                </a>
            </div>

            <div v-if="urgentCount > 0" class="alert alert-warning py-2 mb-3">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>{{ urgentCount }} contrat(s)</strong> expirent dans moins de 30 jours.
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Référence</th>
                                    <th>Titre</th>
                                    <th>Type</th>
                                    <th>Parties</th>
                                    <th>Montant</th>
                                    <th>Fin</th>
                                    <th>Jours rest.</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="c in contrats" :key="c.id" :class="{ 'table-warning': isExpiringSoon(c.date_fin) }">
                                    <td><code>{{ c.reference }}</code></td>
                                    <td><strong>{{ c.titre }}</strong></td>
                                    <td>{{ typeLabel(c.type) }}</td>
                                    <td>{{ c.parties?.[0]?.nom || '—' }}</td>
                                    <td>{{ c.montant ? formatCurrency(c.montant) : '—' }}</td>
                                    <td>{{ formatDate(c.date_fin) }}</td>
                                    <td>
                                        <span :class="daysLeft(c.date_fin) < 15 ? 'text-danger' : 'text-muted'">
                                            {{ daysLeft(c.date_fin) }}j
                                        </span>
                                    </td>
                                    <td><ContratStatusBadge :statut="c.statut" :label="c.statut" /></td>
                                    <td>
                                        <a :href="'/juridique/contrats/' + c.id" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <tr v-if="!contrats.length">
                                    <td colspan="9" class="text-center text-muted py-4">Aucun contrat</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
/*
 * Composant : LegalContratsIndex
 * Role : Page listant les contrats juridiques.
 * Affiche la reference, le titre, le type, les parties, le montant et le statut.
 * Met en evidence les contrats expirant dans moins de 30 jours.
 * Props : aucune
 */

import { ref, computed, onMounted } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';
import ContratStatusBadge from '../../../../Components/Legal/ContratStatusBadge.vue';

// Liste des contrats chargee depuis l'API
const contrats = ref([]);

/* Nombre de contrats actifs expirant dans moins de 30 jours */
const urgentCount = computed(() =>
    contrats.value.filter(c => ['signé', 'actif'].includes(c.statut) && c.date_fin && daysLeft(c.date_fin) <= 30).length
);

/* Mapping des types techniques vers leurs libelles */
const typeLabels = {
    prestation_service: 'Prestation', vente: 'Vente', bail_commercial: 'Bail commercial',
    travail: 'Travail', partenariat: 'Partenariat', confidentialite_nda: 'NDA',
    pret: 'Prêt', cautionnement: 'Cautionnement',
};

/* Convertit le type technique en libelle lisible */
function typeLabel(type) { return typeLabels[type] || type; }

/* Formate une date au format francais court */
function formatDate(date) {
    if (!date) return '—';
    return new Date(date + 'T00:00:00').toLocaleDateString('fr-FR');
}

/* Formate un montant en devise XOF */
function formatCurrency(val) {
    return Number(val).toLocaleString('fr-FR', { style: 'currency', currency: 'XOF', minimumFractionDigits: 0, maximumFractionDigits: 0 });
}

/* Calcule le nombre de jours restants avant une date */
function daysLeft(date) {
    if (!date) return 999;
    return Math.ceil((new Date(date + 'T00:00:00') - new Date()) / (1000 * 60 * 60 * 24));
}

/* Verifie si un contrat expire dans les 30 jours */
function isExpiringSoon(date) { return date && daysLeft(date) <= 30 && daysLeft(date) > 0; }

/* Charge la liste des contrats depuis l'API */
async function loadContrats() {
    try {
        const res = await fetch('/juridique/contrats');
        contrats.value = await res.json();
    } catch (e) { console.error(e); }
}

onMounted(loadContrats);
</script>
