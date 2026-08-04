<!--
 * EntryLinesGrid.vue
 * Grille de saisie des lignes d'écriture comptable (partie double).
 * Permet d'ajouter / supprimer des lignes avec sélection de compte,
 * saisie du libellé, montant au débit ou au crédit, et affiche
 * le total ainsi qu'un avertissement si l'écriture est déséquilibrée.
-->
<template>
    <!-- En-tête avec label et bouton d'ajout de ligne -->
    <div class="entry-lines-grid">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="form-label small mb-0">{{ label }}</label>
            <button class="btn btn-sm btn-outline-primary" @click="addLine" :disabled="adding">
                <i class="bi bi-plus-lg"></i> Ajouter une ligne
            </button>
        </div>

        <!-- Message si aucune ligne saisie -->
        <div v-if="!lines.length" class="text-muted small py-3 text-center">
            Aucune ligne. Cliquez sur "Ajouter une ligne" pour commencer.
        </div>

        <!-- Tableau des lignes d'écriture -->
        <table v-else class="table table-sm table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th>Compte</th>
                    <th>Libellé</th>
                    <th class="text-end" style="width:150px;">Débit</th>
                    <th class="text-end" style="width:150px;">Crédit</th>
                    <th class="text-center" style="width:40px;"></th>
                </tr>
            </thead>
            <tbody>
                <!-- Chaque ligne : sélection de compte, libellé, débit/crédit mutuellement exclusifs, suppression -->
                <tr v-for="(line, idx) in lines" :key="idx">
                    <td>
                        <select class="form-select form-select-sm" v-model="line.account_id">
                            <option value="">—</option>
                            <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
                                {{ acc.code }} — {{ acc.name }}
                            </option>
                        </select>
                    </td>
                    <td><input class="form-control form-control-sm" v-model="line.label" placeholder="Libellé" /></td>
                    <td><input class="form-control form-control-sm text-end" type="number" step="0.01" min="0" v-model.number="line.debit" @input="line.credit = line.debit > 0 ? 0 : line.credit" /></td>
                    <td><input class="form-control form-control-sm text-end" type="number" step="0.01" min="0" v-model.number="line.credit" @input="line.debit = line.credit > 0 ? 0 : line.debit" /></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-danger py-0 px-1" @click="removeLine(idx)">
                            <i class="bi bi-x"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
            <!-- Pied de tableau : totaux débit/crédit et alerte d'équilibre -->
            <tfoot class="table-light fw-semibold">
                <tr>
                    <td colspan="2" class="text-end">Totaux :</td>
                    <td class="text-end">{{ $formatCurrency(totalDebit) }}</td>
                    <td class="text-end">{{ $formatCurrency(totalCredit) }}</td>
                    <td></td>
                </tr>
                <tr v-if="!isBalanced" class="table-warning">
                    <td colspan="5" class="small">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Écriture déséquilibrée : débit ({{ $formatCurrency(totalDebit) }}) ≠ crédit ({{ $formatCurrency(totalCredit) }})
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</template>

<script>
/* Composition de l'objet ligne : { account_id, label, debit, credit } */
export default {
    name: 'EntryLinesGrid',
    props: {
        modelValue: { type: Array, default: () => [] },   /* Tableau des lignes (v-model) */
        accounts: { type: Array, default: () => [] },     /* Liste des comptes disponibles */
        label: { type: String, default: 'Lignes d\'écriture' },
    },
    emits: ['update:modelValue'],
    data() {
        return { adding: false };  /* État du bouton "Ajouter" pour éviter les doubles clics */
    },
    computed: {
        /* Propriété calculée bidirectionnelle liée à modelValue */
        lines: {
            get() { return this.modelValue; },
            set(v) { this.$emit('update:modelValue', v); },
        },
        totalDebit() { return this.lines.reduce((s, l) => s + (parseFloat(l.debit) || 0), 0); },
        totalCredit() { return this.lines.reduce((s, l) => s + (parseFloat(l.credit) || 0), 0); },
        isBalanced() { return Math.abs(this.totalDebit - this.totalCredit) < 0.01; },  /* Seuil de tolérance 0,01 */
    },
    methods: {
        /* Ajoute une ligne vide au tableau */
        addLine() {
            const lines = [...this.lines];
            lines.push({ account_id: '', label: '', debit: 0, credit: 0 });
            this.$emit('update:modelValue', lines);
        },
        /* Supprime une ligne par son index */
        removeLine(idx) {
            const lines = this.lines.filter((_, i) => i !== idx);
            this.$emit('update:modelValue', lines);
        },
    },
};
</script>
