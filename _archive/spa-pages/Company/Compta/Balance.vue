<script setup>
import { computed } from 'vue';
import CompanyLayout from '../../../Layouts/CompanyLayout.vue';

const props = defineProps({
    balance: Array,
    totaux: Object,
});

const fmt = (val) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(val ?? 0);

const classeGroups = computed(() => {
    const groups = {};
    for (const ligne of (props.balance ?? [])) {
        const cl = ligne.classe ?? '?';
        if (!groups[cl]) groups[cl] = { classe: cl, lignes: [], total_debit: 0, total_credit: 0 };
        groups[cl].lignes.push(ligne);
        groups[cl].total_debit += ligne.total_debit;
        groups[cl].total_credit += ligne.total_credit;
    }
    return Object.values(groups).sort((a, b) => a.classe - b.classe);
});

const classeLabels = {
    1: 'Ressources durables', 2: 'Actif immobilisé', 3: 'Stocks',
    4: 'Tiers', 5: 'Trésorerie', 6: 'Charges', 7: 'Produits', 8: 'HAO', 9: 'Analytique'
};

const classeColor = (cl) => {
    const map = {1:'#163A5E',2:'#1a4a76',3:'#0f5132',4:'#92400e',5:'#065f46',6:'#7f1d1d',7:'#1e3a5f',8:'#44337a',9:'#1a2938'};
    return map[cl] ?? '#333';
};
</script>

<template>
    <CompanyLayout page-title="Balance des Comptes — SYSCOHADA">
        <div class="isup-balance-page">

            <!-- Header -->
            <div class="isup-page-header mb-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="isup-hdr-icon"><i class="bi bi-list-columns-reverse"></i></div>
                    <div>
                        <h1 class="isup-ttl mb-0">Balance des Comptes</h1>
                        <p class="isup-sub mb-0">Vue consolidée de tous les soldes — SYSCOHADA Révisé</p>
                    </div>
                </div>
            </div>

            <!-- Totaux globaux -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="isup-total-card" style="--c:#163A5E">
                        <div class="isup-total-label">Total Débits</div>
                        <div class="isup-total-value">{{ fmt(totaux?.total_debit) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="isup-total-card" style="--c:#FF7900">
                        <div class="isup-total-label">Total Crédits</div>
                        <div class="isup-total-value">{{ fmt(totaux?.total_credit) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="isup-total-card" style="--c:#065f46">
                        <div class="isup-total-label">Soldes Débiteurs</div>
                        <div class="isup-total-value">{{ fmt(totaux?.solde_debiteur) }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="isup-total-card" style="--c:#7f1d1d">
                        <div class="isup-total-label">Soldes Créditeurs</div>
                        <div class="isup-total-value">{{ fmt(totaux?.solde_crediteur) }}</div>
                    </div>
                </div>
            </div>

            <!-- Table par classe -->
            <div v-for="group in classeGroups" :key="group.classe" class="isup-card mb-3">
                <div class="isup-classe-header" :style="`background: ${classeColor(group.classe)}`">
                    <span class="isup-classe-num">Classe {{ group.classe }}</span>
                    <span class="isup-classe-name">{{ classeLabels[group.classe] }}</span>
                    <div class="isup-classe-totaux ms-auto">
                        <span>Débit : {{ fmt(group.total_debit) }}</span>
                        <span class="mx-3">|</span>
                        <span>Crédit : {{ fmt(group.total_credit) }}</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="isup-table">
                        <thead>
                            <tr>
                                <th>N° Compte</th>
                                <th>Intitulé</th>
                                <th class="text-end">Total Débit</th>
                                <th class="text-end">Total Crédit</th>
                                <th class="text-end">Solde Débiteur</th>
                                <th class="text-end">Solde Créditeur</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="ligne in group.lignes" :key="ligne.numero" class="isup-tr">
                                <td><code class="isup-code">{{ ligne.numero }}</code></td>
                                <td>{{ ligne.intitule }}</td>
                                <td class="text-end">{{ fmt(ligne.total_debit) }}</td>
                                <td class="text-end">{{ fmt(ligne.total_credit) }}</td>
                                <td class="text-end fw-bold text-success">
                                    {{ ligne.solde_debiteur > 0 ? fmt(ligne.solde_debiteur) : '—' }}
                                </td>
                                <td class="text-end fw-bold" style="color:#163A5E">
                                    {{ ligne.solde_crediteur > 0 ? fmt(ligne.solde_crediteur) : '—' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </CompanyLayout>
</template>

<style scoped>
.isup-balance-page { font-family: 'Inter', sans-serif; }
.isup-page-header { background: linear-gradient(135deg, #163A5E, #1f4d7a); border-radius: 16px; padding: 24px 28px; color: white; box-shadow: 0 4px 20px rgba(22,58,94,0.22); }
.isup-hdr-icon { width: 50px; height: 50px; background: rgba(255,121,0,0.18); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; color: #FF7900; flex-shrink: 0; }
.isup-ttl { font-size: 20px; font-weight: 800; }
.isup-sub { font-size: 13px; color: rgba(255,255,255,0.65); }
.isup-total-card { background: white; border-radius: 14px; padding: 20px; border-left: 5px solid var(--c, #163A5E); box-shadow: 0 2px 12px rgba(0,0,0,0.05); }
.isup-total-label { font-size: 12px; color: #7a8499; font-weight: 600; text-transform: uppercase; margin-bottom: 6px; }
.isup-total-value { font-size: 19px; font-weight: 800; color: var(--c, #163A5E); }
.isup-card { background: white; border-radius: 14px; border: 1px solid #eef0f4; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.05); }
.isup-classe-header { color: white; padding: 12px 18px; display: flex; align-items: center; gap: 12px; font-size: 13px; }
.isup-classe-num { background: rgba(255,255,255,0.2); padding: 3px 10px; border-radius: 20px; font-weight: 800; font-size: 12px; }
.isup-classe-name { font-weight: 700; }
.isup-classe-totaux { font-size: 12px; color: rgba(255,255,255,0.8); }
.isup-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.isup-table th { padding: 10px 16px; font-weight: 700; color: #4a5568; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #eef0f4; background: #f8f9fc; }
.isup-table td { padding: 10px 16px; border-bottom: 1px solid #f4f6fa; }
.isup-tr:hover { background: #fafbff; }
.isup-code { font-size: 12px; background: #edf2f7; padding: 3px 8px; border-radius: 6px; color: #163A5E; font-weight: 700; }
</style>
