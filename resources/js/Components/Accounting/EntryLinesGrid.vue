<template>
    <div class="entry-lines-grid">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="form-label small mb-0">{{ label }}</label>
            <button class="btn btn-sm btn-outline-primary" @click="addLine" :disabled="adding">
                <i class="bi bi-plus-lg"></i> Ajouter une ligne
            </button>
        </div>

        <div v-if="!lines.length" class="text-muted small py-3 text-center">
            Aucune ligne. Cliquez sur "Ajouter une ligne" pour commencer.
        </div>

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
export default {
    name: 'EntryLinesGrid',
    props: {
        modelValue: { type: Array, default: () => [] },
        accounts: { type: Array, default: () => [] },
        label: { type: String, default: 'Lignes d\'écriture' },
    },
    emits: ['update:modelValue'],
    data() {
        return { adding: false };
    },
    computed: {
        lines: {
            get() { return this.modelValue; },
            set(v) { this.$emit('update:modelValue', v); },
        },
        totalDebit() { return this.lines.reduce((s, l) => s + (parseFloat(l.debit) || 0), 0); },
        totalCredit() { return this.lines.reduce((s, l) => s + (parseFloat(l.credit) || 0), 0); },
        isBalanced() { return Math.abs(this.totalDebit - this.totalCredit) < 0.01; },
    },
    methods: {
        addLine() {
            const lines = [...this.lines];
            lines.push({ account_id: '', label: '', debit: 0, credit: 0 });
            this.$emit('update:modelValue', lines);
        },
        removeLine(idx) {
            const lines = this.lines.filter((_, i) => i !== idx);
            this.$emit('update:modelValue', lines);
        },
    },
};
</script>
