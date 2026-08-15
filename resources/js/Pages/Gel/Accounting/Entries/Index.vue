<template>
    <GelLayout pageTitle="Saisie d'écritures">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-1 text-gray-800">Saisie d'écritures</h2>
                    <p class="text-muted mb-0">Enregistrement comptable rapide</p>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom p-3">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">Journal</label>
                            <select class="form-select form-select-sm" v-model="entry.journal">
                                <option value="ACH">ACH - Achats</option>
                                <option value="VTE">VTE - Ventes</option>
                                <option value="BQ1">BQ1 - Banque</option>
                                <option value="OD">OD - Opérations Diverses</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">Date comptable</label>
                            <input type="date" class="form-control form-control-sm" v-model="entry.date">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">Pièce justificative</label>
                            <input type="text" class="form-control form-control-sm" v-model="entry.piece" placeholder="Ex: FAC-2026-001">
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-dense mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th width="15%">Compte</th>
                                    <th width="15%">Tiers</th>
                                    <th width="40%">Libellé</th>
                                    <th width="12%">Débit</th>
                                    <th width="12%">Crédit</th>
                                    <th width="6%" class="text-center">Act.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(line, index) in lines" :key="index">
                                    <td><input type="text" class="form-control form-control-sm font-monospace border-0" v-model="line.account" placeholder="N° Compte"></td>
                                    <td><input type="text" class="form-control form-control-sm border-0" v-model="line.thirdParty" placeholder="Code Tiers"></td>
                                    <td><input type="text" class="form-control form-control-sm border-0" v-model="line.label" placeholder="Libellé de la ligne"></td>
                                    <td><input type="number" class="form-control form-control-sm border-0 text-end tabular-nums" v-model.number="line.debit"></td>
                                    <td><input type="number" class="form-control form-control-sm border-0 text-end tabular-nums" v-model.number="line.credit"></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-link text-danger" @click="removeLine(index)" tabindex="-1">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="6" class="p-1">
                                        <button class="btn btn-sm btn-outline-secondary border-0 w-100" @click="addLine">
                                            <i class="bi bi-plus"></i> Ajouter une ligne
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Totaux</td>
                                    <td class="text-end fw-bold tabular-nums" :class="isBalanced ? 'text-success' : 'text-danger'">{{ totalDebit }}</td>
                                    <td class="text-end fw-bold tabular-nums" :class="isBalanced ? 'text-success' : 'text-danger'">{{ totalCredit }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                
                <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted small" v-if="!isBalanced">
                        <i class="bi bi-exclamation-triangle text-danger me-1"></i>
                        L'écriture n'est pas équilibrée (Écart : {{ Math.abs(totalDebit - totalCredit) }})
                    </span>
                    <span class="text-success small" v-else>
                        <i class="bi bi-check-circle me-1"></i> Écriture équilibrée
                    </span>
                    
                    <div>
                        <button class="btn btn-light me-2">Annuler</button>
                        <button class="btn btn-primary" :disabled="!isBalanced || totalDebit === 0">
                            <i class="bi bi-save me-1"></i> Enregistrer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const entry = ref({
    journal: 'VTE',
    date: new Date().toISOString().split('T')[0],
    piece: ''
});

const lines = ref([
    { account: '4111', thirdParty: 'CLI001', label: 'Facture de vente', debit: 1180000, credit: 0 },
    { account: '7011', thirdParty: '', label: 'Vente HT', debit: 0, credit: 1000000 },
    { account: '4431', thirdParty: '', label: 'TVA facturée', debit: 0, credit: 180000 }
]);

const addLine = () => {
    lines.value.push({ account: '', thirdParty: '', label: '', debit: 0, credit: 0 });
};

const removeLine = (index) => {
    lines.value.splice(index, 1);
};

const totalDebit = computed(() => lines.value.reduce((sum, line) => sum + (line.debit || 0), 0));
const totalCredit = computed(() => lines.value.reduce((sum, line) => sum + (line.credit || 0), 0));
const isBalanced = computed(() => totalDebit.value === totalCredit.value);
</script>

<style scoped>
.table-dense th, .table-dense td {
    padding: 0; /* Plus dense pour les inputs */
}
.table-dense input {
    border-radius: 0;
    box-shadow: none !important;
}
.table-dense input:focus {
    background-color: #f8f9fa;
}
</style>
