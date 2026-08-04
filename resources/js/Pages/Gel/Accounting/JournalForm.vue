<script setup>
/*
 * JournalForm.vue - Formulaire de saisie d'écriture comptable (GEL)
 *
 * Permet de créer une nouvelle écriture comptable avec :
 *   - Type de journal (recette, dépense, banque, OD, achat, vente)
 *   - Date, référence et description
 *   - Lignes d'écriture (compte, libellé, débit, crédit) dynamiques
 * Le formulaire valide que le total des débits égale le total des crédits
 * avant soumission (principe de la partie double).
 */
import { ref, computed, onMounted } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';
import { authStore } from '../../../stores/auth';

const props = defineProps({
    clientId: { type: [Number, String], default: null },
    journalId: { type: [Number, String], default: null }
});

const accounts = ref([]);
const loading = ref(true);
const submitting = ref(false);
const error = ref(null);

// ID client résolu : priorité à la prop, fallback sur le store auth
const effectiveClientId = computed(() => props.clientId || authStore.user?.client_id || null);

// Formulaire principal avec lignes d'écriture
const form = ref({
    client_id: effectiveClientId.value,
    journal_type: 'od',
    entry_date: new Date().toISOString().substring(0, 10),
    description: '',
    reference: '',
    lines: [{ account_id: '', label: '', debit: '', credit: '' }],
});

// Chargement des comptes pour le sélecteur de ligne
const fetchAccounts = async () => {
    if (!effectiveClientId.value) { error.value = 'Aucun client sélectionné.'; loading.value = false; return; }
    try {
        const res = await fetch('/api/accounting/accounts/' + effectiveClientId.value);
        if (res.ok) accounts.value = await res.json();
    } catch (e) { /* */ }
};

// Ajout d'une ligne d'écriture supplémentaire
const addLine = () => {
    form.value.lines.push({ account_id: '', label: '', debit: '', credit: '' });
};

// Suppression d'une ligne (min 1 ligne obligatoire)
const removeLine = (idx) => {
    if (form.value.lines.length <= 1) return;
    form.value.lines.splice(idx, 1);
};

// Total des débits de toutes les lignes
const totalDebit = () => {
    return form.value.lines.reduce((sum, l) => sum + (parseFloat(l.debit) || 0), 0);
};

// Total des crédits de toutes les lignes
const totalCredit = () => {
    return form.value.lines.reduce((sum, l) => sum + (parseFloat(l.credit) || 0), 0);
};

// Vérification d'équilibre débit = crédit (tolérance 0.01)
const isBalanced = () => {
    return Math.abs(totalDebit() - totalCredit()) < 0.01;
};

// Soumission de l'écriture comptable avec validation d'équilibre
const submitForm = async () => {
    if (!isBalanced()) {
        alert('Le total des débits doit être égal au total des crédits.');
        return;
    }
    submitting.value = true;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const payload = {
            client_id: effectiveClientId.value,
            journal_type: form.value.journal_type,
            entry_date: form.value.entry_date,
            description: form.value.description,
            reference: form.value.reference,
            lines: form.value.lines.map(l => ({
                account_id: l.account_id,
                label: l.label,
                debit: l.debit ? parseFloat(l.debit) : 0,
                credit: l.credit ? parseFloat(l.credit) : 0,
            })),
        };

        const res = await fetch('/api/accounting/journals', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload),
        });
        if (!res.ok) {
            const errData = await res.json();
            throw new Error(errData.message || Object.values(errData.errors || {}).flat().join(', '));
        }
        window.location.href = '/accounting/journals/' + effectiveClientId.value;
    } catch (e) {
        alert('Erreur: ' + e.message);
    } finally {
        submitting.value = false;
    }
};

// Retour à la page précédente
const goBack = () => { window.history.back(); };

onMounted(async () => {
    await fetchAccounts();
    loading.value = false;
});
</script>

<template>
    <GelLayout page-title="Nouvelle écriture comptable">
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>

        <div v-else class="bg-white rounded-lg shadow p-6">
            <form @submit.prevent="submitForm">
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label small">Type *</label>
                        <select v-model="form.journal_type" class="form-select form-select-sm" required>
                            <option value="recette">Recette</option>
                            <option value="depense">Dépense</option>
                            <option value="banque">Banque</option>
                            <option value="od" selected>OD (Opération diverse)</option>
                            <option value="achat">Achat</option>
                            <option value="vente">Vente</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Date *</label>
                        <input v-model="form.entry_date" type="date" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Référence</label>
                        <input v-model="form.reference" class="form-control form-control-sm" placeholder="ex: FAC-2024-001">
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Description *</label>
                        <input v-model="form.description" class="form-control form-control-sm" required placeholder="Description de l'écriture">
                    </div>
                </div>

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">Lignes d'écriture</h6>

                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead class="small text-muted">
                            <tr>
                                <th style="min-width:200px;">Compte</th>
                                <th style="min-width:180px;">Libellé</th>
                                <th class="text-end" style="width:130px;">Débit</th>
                                <th class="text-end" style="width:130px;">Crédit</th>
                                <th style="width:40px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(line, idx) in form.lines" :key="idx">
                                <td>
                                    <select v-model="line.account_id" class="form-select form-select-sm" required>
                                        <option value="">Sélectionner</option>
                                        <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
                                            {{ acc.code }} - {{ acc.name }}
                                        </option>
                                    </select>
                                </td>
                                <td><input v-model="line.label" class="form-control form-control-sm" placeholder="Libellé ligne"></td>
                                <td><input v-model="line.debit" type="number" step="0.01" min="0" class="form-control form-control-sm text-end"></td>
                                <td><input v-model="line.credit" type="number" step="0.01" min="0" class="form-control form-control-sm text-end"></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-danger" @click="removeLine(idx)" :disabled="form.lines.length <= 1">
                                        <i class="bi-x"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary" @click="addLine">
                                        <i class="bi-plus-lg me-1"></i>Ajouter une ligne
                                    </button>
                                </td>
                                <td></td>
                                <td class="text-end fw-bold">{{ $formatCurrency(totalDebit()) }}</td>
                                <td class="text-end fw-bold">{{ $formatCurrency(totalCredit()) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div v-if="!isBalanced() && form.lines.length > 0" class="alert alert-warning py-2 small">
                    <i class="bi-exclamation-triangle me-1"></i> Le total des débits ({{ $formatCurrency(totalDebit()) }}) doit être égal au total des crédits ({{ $formatCurrency(totalCredit()) }}).
                </div>
                <div v-else-if="isBalanced() && totalDebit() > 0" class="alert alert-success py-2 small">
                    <i class="bi-check-circle me-1"></i> Écriture équilibrée : {{ $formatCurrency(totalDebit()) }}
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-sm btn-secondary" @click="goBack">
                        <i class="bi-arrow-left me-1"></i>Retour
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary" :disabled="submitting || !isBalanced()">
                        <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                        Enregistrer l'écriture
                    </button>
                </div>
            </form>
        </div>
    </GelLayout>
</template>
