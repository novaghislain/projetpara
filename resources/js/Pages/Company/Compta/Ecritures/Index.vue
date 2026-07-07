<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import CompanyLayout from '../../../../Layouts/CompanyLayout.vue';

const props = defineProps({
    ecritures: Object,
    filters: Object,
});

const showModal = ref(false);

const form = ref({
    journal_id: '',
    date_ecriture: new Date().toISOString().split('T')[0],
    libelle: '',
    numero_piece: '',
    lignes: [
        { compte_id: '', libelle: '', debit: 0, credit: 0 },
        { compte_id: '', libelle: '', debit: 0, credit: 0 },
    ],
});

const totalDebit = () => form.value.lignes.reduce((s, l) => s + parseFloat(l.debit || 0), 0);
const totalCredit = () => form.value.lignes.reduce((s, l) => s + parseFloat(l.credit || 0), 0);
const isBalanced = () => Math.abs(totalDebit() - totalCredit()) < 0.01;

const addLigne = () => form.value.lignes.push({ compte_id: '', libelle: '', debit: 0, credit: 0 });
const removeLigne = (i) => { if (form.value.lignes.length > 2) form.value.lignes.splice(i, 1); };

const submitEcriture = () => {
    if (!isBalanced()) return alert("Erreur : Le total des débits doit être égal au total des crédits.");
    router.post('/company/comptabilite/ecritures', form.value, {
        onSuccess: () => { showModal.value = false; },
    });
};

const validateEntry = (id) => {
    if (confirm('Valider cette écriture ? Elle ne pourra plus être modifiée.')) {
        router.post(`/company/comptabilite/ecritures/${id}/validate`);
    }
};

const deleteEntry = (id) => {
    if (confirm('Supprimer cette écriture ?')) {
        router.delete(`/company/comptabilite/ecritures/${id}`);
    }
};

const formatMontant = (val) => new Intl.NumberFormat('fr-FR').format(val ?? 0);
</script>

<template>
    <CompanyLayout page-title="Écritures Comptables">
        <div class="isup-ecritures-page">

            <!-- Header -->
            <div class="isup-page-header mb-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="isup-hdr-icon"><i class="bi bi-pencil-square"></i></div>
                        <div>
                            <h1 class="isup-ttl mb-0">Écritures Comptables</h1>
                            <p class="isup-sub mb-0">Partie double — Débit = Crédit obligatoire</p>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="/company/comptabilite/grand-livre" class="isup-btn isup-btn-outline">
                            <i class="bi bi-layers me-1"></i> Grand Livre
                        </a>
                        <a href="/company/comptabilite/balance" class="isup-btn isup-btn-outline">
                            <i class="bi bi-list-columns me-1"></i> Balance
                        </a>
                        <button @click="showModal = true" class="isup-btn isup-btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Nouvelle écriture
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="isup-card">
                <div class="table-responsive">
                    <table class="isup-table">
                        <thead>
                            <tr>
                                <th>Référence</th>
                                <th>Date</th>
                                <th>Journal</th>
                                <th>Libellé</th>
                                <th class="text-end">Total Débit</th>
                                <th class="text-end">Total Crédit</th>
                                <th>Statut</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!ecritures?.data?.length">
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Aucune écriture. Créez votre première écriture comptable.
                                </td>
                            </tr>
                            <tr v-for="e in ecritures?.data" :key="e.id" class="isup-tr">
                                <td><code class="isup-code">{{ e.reference }}</code></td>
                                <td>{{ new Date(e.date_ecriture).toLocaleDateString('fr-FR') }}</td>
                                <td><span class="isup-badge-journal">{{ e.journal?.code }}</span></td>
                                <td>{{ e.libelle }}</td>
                                <td class="text-end fw-bold text-success">{{ formatMontant(e.total_debit) }}</td>
                                <td class="text-end fw-bold" style="color:#163A5E">{{ formatMontant(e.total_credit) }}</td>
                                <td>
                                    <span v-if="e.is_validee" class="isup-badge-success">Validée</span>
                                    <span v-else class="isup-badge-warning">Brouillon</span>
                                </td>
                                <td>
                                    <div class="isup-actions">
                                        <button v-if="!e.is_validee" @click="validateEntry(e.id)"
                                            class="isup-action-btn success" title="Valider">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                        <button v-if="!e.is_validee" @click="deleteEntry(e.id)"
                                            class="isup-action-btn delete" title="Supprimer">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Écriture -->
        <teleport to="body">
            <div v-if="showModal" class="isup-modal-overlay" @click.self="showModal = false">
                <div class="isup-modal isup-modal-lg">
                    <div class="isup-modal-header">
                        <h3 class="mb-0"><i class="bi bi-pencil-square me-2" style="color:#FF7900"></i>Nouvelle Écriture</h3>
                        <button @click="showModal = false" class="isup-close-btn"><i class="bi bi-x-lg"></i></button>
                    </div>
                    <div class="isup-modal-body">
                        <form @submit.prevent="submitEcriture">
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="isup-label">Journal *</label>
                                    <input v-model="form.journal_id" type="number" class="isup-input" placeholder="ID Journal" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="isup-label">Date *</label>
                                    <input v-model="form.date_ecriture" type="date" class="isup-input" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="isup-label">N° Pièce</label>
                                    <input v-model="form.numero_piece" class="isup-input" placeholder="Optionnel">
                                </div>
                                <div class="col-12">
                                    <label class="isup-label">Libellé *</label>
                                    <input v-model="form.libelle" class="isup-input" placeholder="Description de l'écriture" required>
                                </div>
                            </div>

                            <!-- Lignes -->
                            <div class="isup-lignes-header">
                                <span>Lignes d'écriture</span>
                                <button type="button" @click="addLigne" class="isup-btn-sm">
                                    <i class="bi bi-plus"></i> Ajouter une ligne
                                </button>
                            </div>
                            <div class="isup-lignes-table">
                                <table class="isup-table-lignes">
                                    <thead>
                                        <tr>
                                            <th>Compte</th>
                                            <th>Libellé</th>
                                            <th class="text-end">Débit</th>
                                            <th class="text-end">Crédit</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(ligne, i) in form.lignes" :key="i">
                                            <td><input v-model="ligne.compte_id" class="isup-input-sm" type="number" placeholder="ID Compte"></td>
                                            <td><input v-model="ligne.libelle" class="isup-input-sm" placeholder="Libellé ligne"></td>
                                            <td><input v-model="ligne.debit" class="isup-input-sm text-end" type="number" step="0.01" min="0"></td>
                                            <td><input v-model="ligne.credit" class="isup-input-sm text-end" type="number" step="0.01" min="0"></td>
                                            <td>
                                                <button type="button" @click="removeLigne(i)" class="isup-del-btn">
                                                    <i class="bi bi-dash-circle text-danger"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="2" class="fw-bold text-end pe-3">Totaux</td>
                                            <td class="text-end fw-bold" :class="isBalanced() ? 'text-success' : 'text-danger'">
                                                {{ totalDebit().toFixed(2) }}
                                            </td>
                                            <td class="text-end fw-bold" :class="isBalanced() ? 'text-success' : 'text-danger'">
                                                {{ totalCredit().toFixed(2) }}
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Balance indicator -->
                            <div class="isup-balance-indicator mt-2" :class="isBalanced() ? 'balanced' : 'unbalanced'">
                                <i :class="isBalanced() ? 'bi bi-check-circle-fill' : 'bi bi-exclamation-triangle-fill'"></i>
                                {{ isBalanced() ? 'Équilibrée ✓' : 'Déséquilibre : ' + Math.abs(totalDebit() - totalCredit()).toFixed(2) }}
                            </div>

                            <div class="d-flex gap-2 justify-content-end mt-4">
                                <button type="button" @click="showModal = false" class="isup-btn isup-btn-outline">Annuler</button>
                                <button type="submit" class="isup-btn isup-btn-primary" :disabled="!isBalanced()">
                                    <i class="bi bi-check-lg me-1"></i> Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </teleport>
    </CompanyLayout>
</template>

<style scoped>
.isup-ecritures-page { font-family: 'Inter', sans-serif; }
.isup-page-header { background: linear-gradient(135deg, #163A5E, #1f4d7a); border-radius: 16px; padding: 24px 28px; color: white; box-shadow: 0 4px 20px rgba(22,58,94,0.22); }
.isup-hdr-icon { width: 50px; height: 50px; background: rgba(255,121,0,0.18); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; color: #FF7900; flex-shrink: 0; }
.isup-ttl { font-size: 20px; font-weight: 800; }
.isup-sub { font-size: 13px; color: rgba(255,255,255,0.65); }
.isup-card { background: white; border-radius: 16px; border: 1px solid #eef0f4; overflow: hidden; box-shadow: 0 2px 14px rgba(0,0,0,0.05); }
.isup-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.isup-table thead tr { background: #f8f9fc; }
.isup-table th { padding: 12px 16px; font-weight: 700; color: #4a5568; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #eef0f4; }
.isup-table td { padding: 11px 16px; border-bottom: 1px solid #f4f6fa; }
.isup-tr:hover { background: #fafbff; }
.isup-code { font-size: 12px; background: #edf2f7; padding: 3px 8px; border-radius: 6px; color: #163A5E; font-weight: 700; }
.isup-badge-journal { background: #163A5E; color: white; font-size: 11px; padding: 2px 8px; border-radius: 20px; font-weight: 600; }
.isup-badge-success { background: #d1fae5; color: #065f46; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.isup-badge-warning { background: #fef3c7; color: #92400e; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.isup-actions { display: flex; gap: 4px; }
.isup-action-btn { width: 30px; height: 30px; border: none; border-radius: 8px; cursor: pointer; font-size: 13px; transition: all 0.2s; }
.isup-action-btn.success { background: #d1fae5; color: #065f46; }
.isup-action-btn.success:hover { background: #065f46; color: white; }
.isup-action-btn.delete { background: #fee2e2; color: #dc2626; }
.isup-action-btn.delete:hover { background: #dc2626; color: white; }

/* Button styles */
.isup-btn { padding: 10px 18px; border-radius: 10px; font-size: 13.5px; font-weight: 600; cursor: pointer; border: none; transition: all 0.2s; display: inline-flex; align-items: center; }
.isup-btn-primary { background: #FF7900; color: white; }
.isup-btn-primary:hover { background: #e06b00; }
.isup-btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
.isup-btn-outline { background: transparent; border: 2px solid #163A5E; color: #163A5E; }
.isup-btn-outline:hover { background: #163A5E; color: white; }
.isup-btn-sm { background: #FF7900; color: white; border: none; padding: 5px 12px; border-radius: 8px; font-size: 12px; cursor: pointer; font-weight: 600; }

/* Modal */
.isup-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1050; backdrop-filter: blur(3px); }
.isup-modal { background: white; border-radius: 20px; width: 90%; max-width: 580px; box-shadow: 0 24px 64px rgba(0,0,0,0.2); overflow: hidden; animation: slideUp 0.25s ease; }
.isup-modal-lg { max-width: 780px; }
@keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.isup-modal-header { display: flex; align-items: center; justify-content: space-between; padding: 20px 24px; background: #f8f9fc; border-bottom: 1px solid #eef0f4; }
.isup-modal-header h3 { font-size: 17px; font-weight: 700; color: #1a2938; }
.isup-close-btn { background: none; border: none; font-size: 18px; cursor: pointer; color: #94a3b8; }
.isup-modal-body { padding: 24px; max-height: 75vh; overflow-y: auto; }
.isup-input { width: 100%; padding: 10px 14px; border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: 13.5px; outline: none; transition: border 0.2s; }
.isup-input:focus { border-color: #FF7900; }
.isup-input-sm { width: 100%; padding: 6px 10px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 12.5px; outline: none; }
.isup-label { font-size: 12px; font-weight: 700; color: #4a5568; display: block; margin-bottom: 5px; }
.isup-lignes-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; font-weight: 700; font-size: 13px; color: #1a2938; }
.isup-lignes-table { border: 1px solid #eef0f4; border-radius: 10px; overflow: hidden; }
.isup-table-lignes { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.isup-table-lignes th { background: #f8f9fc; padding: 8px 12px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #4a5568; font-weight: 700; }
.isup-table-lignes td { padding: 6px 8px; border-bottom: 1px solid #f4f6fa; }
.isup-table-lignes tfoot td { padding: 10px 8px; background: #f8f9fc; font-size: 13px; }
.isup-del-btn { background: none; border: none; cursor: pointer; }
.isup-balance-indicator { padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
.isup-balance-indicator.balanced { background: #d1fae5; color: #065f46; }
.isup-balance-indicator.unbalanced { background: #fee2e2; color: #dc2626; }
</style>
