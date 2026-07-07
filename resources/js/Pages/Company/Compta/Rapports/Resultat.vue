<script setup>
import { ref, onMounted } from 'vue';
import CompanyLayout from '../../../../Layouts/CompanyLayout.vue';
import DateInput from '../../../../Components/Accounting/DateInput.vue';

const data = ref(null);
const sigData = ref(null);
const loading = ref(false);
const activeTab = ref('resultat');
const filters = ref({
    start_date: new Date().getFullYear() + '-01-01',
    end_date: new Date().toISOString().split('T')[0],
});

const fmt = (val) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(val ?? 0) + ' F';

async function loadResultat() {
    loading.value = true;
    try {
        const params = new URLSearchParams(filters.value);
        const [res, sigRes] = await Promise.all([
            fetch(`/api/reports/financial-statements/income-statement?${params}`, { headers: { Accept: 'application/json' } }),
            fetch(`/api/reports/financial-statements/sig?${params}`, { headers: { Accept: 'application/json' } }),
        ]);
        data.value = (await res.json()).data;
        sigData.value = (await sigRes.json()).data;
    } catch (e) {
        console.error('Erreur:', e);
    } finally {
        loading.value = false;
    }
}

onMounted(loadResultat);
</script>

<template>
    <CompanyLayout pageTitle="Compte de Résultat">
        <div class="container-fluid pt-3 pb-5">
            <div class="isup-card p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="fw-bold mb-0"><i class="bi bi-pie-chart me-2"></i>Compte de Résultat (SYSCOHADA)</h4>
                    <div class="d-flex gap-2 align-items-center">
                        <DateInput v-model="filters.start_date" label="Du" />
                        <DateInput v-model="filters.end_date" label="Au" />
                        <button @click="loadResultat" class="btn btn-primary btn-sm">
                            <i class="bi bi-arrow-repeat me-1"></i>Actualiser
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>

            <!-- Onglets -->
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                    <button class="nav-link small" :class="{ active: activeTab === 'resultat' }" @click="activeTab = 'resultat'">
                        Compte de Résultat
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link small" :class="{ active: activeTab === 'sig' }" @click="activeTab = 'sig'">
                        Soldes Intermédiaires de Gestion
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link small" :class="{ active: activeTab === 'details' }" @click="activeTab = 'details'">
                        Détail par compte
                    </button>
                </li>
            </ul>

            <!-- Compte de Résultat -->
            <div v-if="data && activeTab === 'resultat'" class="row g-4">
                <div class="col-md-6">
                    <div class="isup-card p-3">
                        <h6 class="fw-bold mb-3 text-success">Produits</h6>
                        <table class="table table-sm">
                            <tr><td class="small">Produits d'exploitation</td><td class="text-end small fw-bold">{{ fmt(data.resultat_exploitation.produits) }}</td></tr>
                            <tr><td class="small">Produits financiers</td><td class="text-end small fw-bold">{{ fmt(data.resultat_financier.produits) }}</td></tr>
                            <tr><td class="small">Produits exceptionnels</td><td class="text-end small fw-bold">{{ fmt(data.resultat_exceptionnel.produits) }}</td></tr>
                            <tr class="table-success"><th class="small">TOTAL PRODUITS</th><th class="text-end small">{{ fmt(data.resultat_exploitation.produits + data.resultat_financier.produits + data.resultat_exceptionnel.produits) }}</th></tr>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="isup-card p-3">
                        <h6 class="fw-bold mb-3 text-danger">Charges</h6>
                        <table class="table table-sm">
                            <tr><td class="small">Charges d'exploitation</td><td class="text-end small fw-bold">{{ fmt(data.resultat_exploitation.charges) }}</td></tr>
                            <tr><td class="small">Charges financières</td><td class="text-end small fw-bold">{{ fmt(data.resultat_financier.charges) }}</td></tr>
                            <tr><td class="small">Impôts</td><td class="text-end small fw-bold">{{ fmt(data.impots) }}</td></tr>
                            <tr><td class="small">Charges exceptionnelles</td><td class="text-end small fw-bold">{{ fmt(data.resultat_exceptionnel.charges) }}</td></tr>
                            <tr class="table-danger"><th class="small">TOTAL CHARGES</th><th class="text-end small">{{ fmt(data.resultat_exploitation.charges + data.resultat_financier.charges + data.impots + data.resultat_exceptionnel.charges) }}</th></tr>
                        </table>
                    </div>
                </div>
                <div class="col-12">
                    <div class="isup-card p-3" :class="data.resultat_net >= 0 ? 'border-success' : 'border-danger'">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">RÉSULTAT NET</h5>
                            <h3 class="fw-bold mb-0" :class="data.resultat_net >= 0 ? 'text-success' : 'text-danger'">
                                {{ fmt(data.resultat_net) }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SIG -->
            <div v-if="sigData && activeTab === 'sig'" class="row g-3">
                <div class="col-md-6">
                    <div class="isup-card p-3">
                        <h6 class="fw-bold mb-3">Soldes Intermédiaires de Gestion</h6>
                        <table class="table table-sm">
                            <tr><td class="small">Marge commerciale</td><td class="text-end fw-bold small">{{ fmt(sigData.sig.marge_commerciale) }}</td></tr>
                            <tr><td class="small">Production de l'exercice</td><td class="text-end fw-bold small">{{ fmt(sigData.sig.production_exercice) }}</td></tr>
                            <tr><td class="small">Consommation</td><td class="text-end small text-danger">-{{ fmt(sigData.sig.consommation) }}</td></tr>
                            <tr class="table-info"><th class="small">VALEUR AJOUTÉE</th><th class="text-end small">{{ fmt(sigData.sig.valeur_ajoutee) }}</th></tr>
                            <tr class="table-primary"><th class="small">EBE</th><th class="text-end small">{{ fmt(sigData.sig.excedent_brut_exploitation) }}</th></tr>
                            <tr><td class="small">Dotations amortissements</td><td class="text-end small text-danger">-{{ fmt(sigData.sig.dotations_amortissements) }}</td></tr>
                            <tr class="table-success"><th class="small">RÉSULTAT D'EXPLOITATION</th><th class="text-end small">{{ fmt(sigData.sig.resultat_exploitation) }}</th></tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Détail -->
            <div v-if="data && activeTab === 'details'" class="row g-4">
                <div class="col-md-6">
                    <div class="isup-card p-3">
                        <h6 class="fw-bold mb-3 text-success">Détail des Produits (Classe 7)</h6>
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr><th class="small">Compte</th><th class="small">Libellé</th><th class="small text-end">Montant</th></tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in (data.details_produits || [])" :key="item.id">
                                    <td class="small font-mono">{{ item.code }}</td>
                                    <td class="small">{{ item.name }}</td>
                                    <td class="text-end small">{{ fmt(item.amount) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="isup-card p-3">
                        <h6 class="fw-bold mb-3 text-danger">Détail des Charges (Classe 6)</h6>
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr><th class="small">Compte</th><th class="small">Libellé</th><th class="small text-end">Montant</th></tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in (data.details_charges || [])" :key="item.id">
                                    <td class="small font-mono">{{ item.code }}</td>
                                    <td class="small">{{ item.name }}</td>
                                    <td class="text-end small">{{ fmt(item.amount) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </CompanyLayout>
</template>

<style scoped>
.font-mono { font-family: 'Courier New', monospace; }
.nav-link.active { font-weight: 700; border-bottom: 2px solid var(--primary); }
</style>
