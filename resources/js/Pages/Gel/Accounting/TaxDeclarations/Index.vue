<template>
    <GelLayout pageTitle="Télédéclarations Fiscales">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-1 text-gray-800">Télédéclarations Fiscales & Sociales</h2>
                    <p class="text-muted mb-0">Gestion, validation et télétransmission des liasses</p>
                </div>
                <div>
                    <button class="btn btn-outline-secondary me-2">
                        <i class="bi bi-clock-history me-1"></i> Historique
                    </button>
                    <button class="btn btn-primary">
                        <i class="bi bi-magic me-1"></i> Générer Liasse Fiscale
                    </button>
                </div>
            </div>

            <!-- Calendrier Fiscal / Alertes -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card bg-gradient-danger text-white border-0 shadow-sm" style="background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%);">
                        <div class="card-body d-flex justify-content-between align-items-center p-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-exclamation-triangle-fill fs-2 me-3"></i>
                                <div>
                                    <h6 class="mb-0 fw-bold">Échéance Fiscale Proche</h6>
                                    <p class="mb-0 small">La déclaration TVA de Juillet 2026 doit être validée et transmise avant le 15 Août 2026 (dans 2 jours).</p>
                                </div>
                            </div>
                            <button class="btn btn-light btn-sm text-danger fw-bold px-3">Traiter l'échéance</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des déclarations -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-gray-800">Déclarations en cours (Exercice 2026)</h6>
                    <div class="d-flex">
                        <select class="form-select form-select-sm w-auto me-2">
                            <option value="">Tous types</option>
                            <option value="tva">TVA Mensuelle</option>
                            <option value="is">Impôt sur les Sociétés (IS)</option>
                            <option value="vps">VPS (Salaires)</option>
                            <option value="cnss">CNSS (Social)</option>
                        </select>
                        <select class="form-select form-select-sm w-auto">
                            <option value="">Statut</option>
                            <option value="draft">Brouillon</option>
                            <option value="review">En Révision</option>
                            <option value="validated">Validé</option>
                            <option value="submitted">Transmis (DGI)</option>
                        </select>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-dense mb-0 align-middle">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th width="12%">Période</th>
                                    <th width="20%">Type de Déclaration</th>
                                    <th width="15%" class="text-end">Base Imposable</th>
                                    <th width="15%" class="text-end">Montant à Payer</th>
                                    <th width="12%">Date Limite</th>
                                    <th width="12%" class="text-center">Statut</th>
                                    <th width="14%" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="tax in taxDeclarations" :key="tax.id">
                                    <td class="fw-bold">{{ tax.period }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i :class="getTypeIcon(tax.type)" class="me-2 fs-5 text-muted"></i>
                                            <div>
                                                <div class="fw-medium">{{ tax.title }}</div>
                                                <small class="text-muted">{{ tax.reference }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end tabular-nums">{{ formatCurrency(tax.base) }}</td>
                                    <td class="text-end tabular-nums fw-bold" :class="{'text-danger': tax.status !== 'Transmis', 'text-success': tax.status === 'Transmis'}">
                                        {{ formatCurrency(tax.amount) }}
                                    </td>
                                    <td>
                                        <span :class="{'text-danger fw-bold': isLate(tax.deadline, tax.status)}">
                                            {{ tax.deadline }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge" :class="getStatusBadgeClass(tax.status)">
                                            {{ tax.status }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-light border me-1" title="Visualiser le brouillon">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-primary" v-if="tax.status === 'Brouillon' || tax.status === 'En Révision'" title="Valider & Transmettre">
                                            <i class="bi bi-check-all"></i>
                                        </button>
                                        <button class="btn btn-sm btn-success" v-if="tax.status === 'Transmis'" title="Télécharger l'attestation">
                                            <i class="bi bi-download"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light p-2 text-center text-muted small">
                    Synchronisation active avec le portail e-MECeF / DGI
                </div>
            </div>

            <!-- Assistant Fiscal IA -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm bg-light">
                        <div class="card-body p-4 d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-4 text-primary">
                                <i class="bi bi-robot fs-1"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-gray-800">Contrôle de Cohérence IA</h5>
                                <p class="text-muted mb-2">Notre IA a analysé les écritures du mois de Juillet 2026. La TVA déductible correspond parfaitement aux factures fournisseurs scannées. Aucune anomalie détectée sur le CA déclaré.</p>
                                <button class="btn btn-sm btn-primary"><i class="bi bi-file-earmark-check me-1"></i> Voir le rapport de contrôle</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </GelLayout>
</template>

<script setup>
import { ref } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const taxDeclarations = ref([
    { id: 1, period: 'Juillet 2026', title: 'TVA Mensuelle', type: 'tva', reference: 'DEC-TVA-2607', base: 12500000, amount: 2250000, deadline: '15/08/2026', status: 'En Révision' },
    { id: 2, period: 'Juillet 2026', title: 'AIB (Acompte sur IS)', type: 'is', reference: 'DEC-AIB-2607', base: 12500000, amount: 125000, deadline: '15/08/2026', status: 'Brouillon' },
    { id: 3, period: 'Juillet 2026', title: 'VPS (Salaires)', type: 'rh', reference: 'DEC-VPS-2607', base: 3500000, amount: 140000, deadline: '10/08/2026', status: 'Transmis' },
    { id: 4, period: 'Juin 2026', title: 'TVA Mensuelle', type: 'tva', reference: 'DEC-TVA-2606', base: 11200000, amount: 2016000, deadline: '15/07/2026', status: 'Transmis' },
    { id: 5, period: 'Trimestre 2', title: 'Acompte IS (2ème)', type: 'is', reference: 'DEC-IS-Q2', base: 0, amount: 1500000, deadline: '30/07/2026', status: 'Transmis' },
]);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF', maximumFractionDigits: 0 }).format(value).replace('XOF', 'FCFA');
};

const getTypeIcon = (type) => {
    switch(type) {
        case 'tva': return 'bi-receipt';
        case 'is': return 'bi-building';
        case 'rh': return 'bi-people';
        default: return 'bi-file-text';
    }
};

const getStatusBadgeClass = (status) => {
    switch(status) {
        case 'Brouillon': return 'bg-secondary';
        case 'En Révision': return 'bg-warning text-dark';
        case 'Validé': return 'bg-primary';
        case 'Transmis': return 'bg-success';
        default: return 'bg-dark';
    }
};

const isLate = (deadlineStr, status) => {
    if (status === 'Transmis') return false;
    const parts = deadlineStr.split('/');
    if (parts.length === 3) {
        const deadlineDate = new Date(parts[2], parts[1] - 1, parts[0]);
        const now = new Date('2026-08-15'); // Current system date mock
        return deadlineDate < now;
    }
    return false;
};
</script>

<style scoped>
.table-dense th, .table-dense td {
    padding: 0.6rem;
    font-size: 0.85rem;
}
.tabular-nums {
    font-variant-numeric: tabular-nums;
}
</style>
