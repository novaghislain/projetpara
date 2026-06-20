<template>
    <GelLayout page-title="Détail Déclaration">
        <div class="p-6">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="text-xl fw-bold mb-0">Déclaration {{ declaration.reference }}</h2>
                <div class="d-flex gap-2">
                    <form v-if="declaration.status === 'brouillon'" method="POST" :action="`/tele-declarations/${declaration.id}/submit`" style="display:inline">
                        <input type="hidden" name="_token" :value="csrf" />
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="bi-send me-1"></i> Déposer
                        </button>
                    </form>
                    <a href="/tele-declarations" class="btn btn-outline-secondary btn-sm">
                        <i class="bi-arrow-left me-1"></i> Retour
                    </a>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="bg-white rounded-lg shadow p-3">
                        <h6 class="fw-bold mb-3 small text-uppercase text-muted">Informations</h6>
                        <dl class="row mb-0 small">
                            <dt class="col-sm-5">Client</dt>
                            <dd class="col-sm-7">{{ declaration.client?.company_name || '—' }}</dd>
                            <dt class="col-sm-5">Type</dt>
                            <dd class="col-sm-7">{{ declaration.libelle_type }}</dd>
                            <dt class="col-sm-5">Référence</dt>
                            <dd class="col-sm-7"><code>{{ declaration.reference }}</code></dd>
                            <dt class="col-sm-5">Période</dt>
                            <dd class="col-sm-7">
                                {{ declaration.period_month ? 'Mois ' + declaration.period_month + ' / ' : '' }}
                                {{ declaration.period_quarter ? 'Trimestre ' + declaration.period_quarter + ' / ' : '' }}
                                {{ declaration.period_year }}
                            </dd>
                            <dt class="col-sm-5">Date début</dt>
                            <dd class="col-sm-7">{{ declaration.date_debut }}</dd>
                            <dt class="col-sm-5">Date fin</dt>
                            <dd class="col-sm-7">{{ declaration.date_fin }}</dd>
                            <dt class="col-sm-5">Échéance</dt>
                            <dd class="col-sm-7" :class="{ 'text-danger fw-bold': declaration.est_en_retard }">{{ declaration.date_echeance }}</dd>
                            <dt class="col-sm-5">Date dépôt</dt>
                            <dd class="col-sm-7">{{ declaration.date_depot || '—' }}</dd>
                            <dt class="col-sm-5">Statut</dt>
                            <dd class="col-sm-7">
                                <span :class="'badge ' + statusClass(declaration.status)">{{ declaration.status }}</span>
                            </dd>
                        </dl>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="bg-white rounded-lg shadow p-3">
                        <h6 class="fw-bold mb-3 small text-uppercase text-muted">Montants</h6>
                        <dl class="row mb-0 small">
                            <dt class="col-sm-5">Base imposable</dt>
                            <dd class="col-sm-7 fw-semibold">{{ formatMille(declaration.base_imposable) }}</dd>
                            <dt class="col-sm-5">Taux</dt>
                            <dd class="col-sm-7">{{ declaration.taux }}%</dd>
                            <dt class="col-sm-5">Montant dû</dt>
                            <dd class="col-sm-7 fw-bold text-primary">{{ formatMille(declaration.montant_dut) }}</dd>
                            <dt class="col-sm-5">Montant payé</dt>
                            <dd class="col-sm-7">{{ formatMille(declaration.montant_paye) }}</dd>
                            <dt class="col-sm-5">Pénalités</dt>
                            <dd class="col-sm-7">{{ formatMille(declaration.penalites) }}</dd>
                            <dt class="col-sm-5">Solde</dt>
                            <dd class="col-sm-7" :class="{ 'text-danger fw-bold': declaration.solde > 0 }">{{ formatMille(declaration.solde) }}</dd>
                            <template v-if="declaration.tax_type === 'tva'">
                                <dt class="col-sm-5">TVA collectée</dt>
                                <dd class="col-sm-7">{{ formatMille(declaration.tva_collectee) }}</dd>
                                <dt class="col-sm-5">TVA récupérable</dt>
                                <dd class="col-sm-7">{{ formatMille(declaration.tva_recuperable) }}</dd>
                                <dt class="col-sm-5">TVA nette</dt>
                                <dd class="col-sm-7 fw-semibold">{{ formatMille(declaration.tva_net) }}</dd>
                            </template>
                            <template v-if="declaration.tax_type === 'cnss'">
                                <dt class="col-sm-5">Part employeur</dt>
                                <dd class="col-sm-7">{{ formatMille(declaration.part_employeur) }}</dd>
                                <dt class="col-sm-5">Part salarié</dt>
                                <dd class="col-sm-7">{{ formatMille(declaration.part_salarie) }}</dd>
                            </template>
                        </dl>
                    </div>
                </div>
                <div class="col-12" v-if="declaration.notes">
                    <div class="bg-white rounded-lg shadow p-3">
                        <h6 class="fw-bold mb-2 small text-uppercase text-muted">Notes</h6>
                        <p class="small mb-0">{{ declaration.notes }}</p>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import GelLayout from '../../../Layouts/GelLayout.vue';
defineProps(['declaration'])
const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const formatMille = (v) => {
    if (v == null || v === '') return '—';
    return Number(v).toLocaleString('fr-FR', { minimumFractionDigits: 0 }) + ' FCFA';
}

const statusClass = (s) => ({
    brouillon: 'bg-secondary',
    calcule: 'bg-info text-dark',
    depose: 'bg-primary',
    paye: 'bg-success',
    en_retard: 'bg-danger',
}[s] || 'bg-secondary')
</script>
