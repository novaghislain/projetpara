<template>
    <div class="balance-table">
        <div v-if="!items.length" class="text-muted small py-3 text-center">Aucune donnée.</div>
        <table v-else class="table table-sm table-hover mb-0">
            <thead class="small text-muted">
                <tr>
                    <th>Compte</th>
                    <th>Libellé</th>
                    <th class="text-end">Débit</th>
                    <th class="text-end">Crédit</th>
                    <th class="text-end">Solde</th>
                    <th v-if="showClass" class="text-center">Classe</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in items" :key="item.id || item.code">
                    <td><code>{{ item.code }}</code></td>
                    <td>{{ item.name || item.label }}</td>
                    <td class="text-end">{{ $formatCurrency(item.total_debit || item.debit || 0) }}</td>
                    <td class="text-end">{{ $formatCurrency(item.total_credit || item.credit || 0) }}</td>
                    <td class="text-end fw-semibold" :class="soldeClass(item)">
                        {{ $formatCurrency(item.solde || item.balance || 0) }}
                    </td>
                    <td v-if="showClass" class="text-center">
                        <span class="badge bg-secondary">{{ item.syscohada_class }}</span>
                    </td>
                </tr>
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="2">TOTAUX</td>
                    <td class="text-end">{{ $formatCurrency(totalDebit) }}</td>
                    <td class="text-end">{{ $formatCurrency(totalCredit) }}</td>
                    <td class="text-end">{{ $formatCurrency(totalSolde) }}</td>
                    <td v-if="showClass"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</template>

<script>
export default {
    name: 'BalanceTable',
    props: {
        items: { type: Array, default: () => [] },
        showClass: { type: Boolean, default: false },
    },
    computed: {
        totalDebit() { return this.items.reduce((s, i) => s + (parseFloat(i.total_debit || i.debit || 0)), 0); },
        totalCredit() { return this.items.reduce((s, i) => s + (parseFloat(i.total_credit || i.credit || 0)), 0); },
        totalSolde() { return this.items.reduce((s, i) => s + (parseFloat(i.solde || i.balance || 0)), 0); },
    },
    methods: {
        soldeClass(item) {
            const solde = item.solde || item.balance || 0;
            return solde >= 0 ? 'text-primary' : 'text-danger';
        },
    },
};
</script>
