<template>
    <GelLayout>
        <div class="rh-payroll-show">
            <div v-if="loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
            <div v-else-if="payroll" class="container-fluid">
                <div class="d-flex align-items-center mb-4">
                    <a href="/rh/payrolls" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
                    <div>
                        <h1 class="h3 fw-bold mb-0">Fiche de paie</h1>
                        <p class="text-muted mb-0">{{ payroll.employee?.prenom }} {{ payroll.employee?.nom }} · {{ payroll.periode }}</p>
                    </div>
                    <span :class="`badge bg-${statusBadge(payroll.statut)} ms-auto fs-6`">{{ payroll.statut }}</span>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-bottom"><h6 class="mb-0 fw-semibold">Rémunération</h6></div>
                            <div class="card-body">
                                <dl class="row mb-0 small">
                                    <dt class="col-sm-6 text-muted">Salaire de base</dt><dd class="col-sm-6 text-end">{{ Number(payroll.salaire_base).toLocaleString('fr-FR') }} FCFA</dd>
                                    <dt class="col-sm-6 text-muted">Primes</dt><dd class="col-sm-6 text-end">{{ sumArray(payroll.primes) }} FCFA</dd>
                                    <dt class="col-sm-6 text-muted">Indemnités</dt><dd class="col-sm-6 text-end">{{ sumArray(payroll.indemnites) }} FCFA</dd>
                                    <hr class="my-1">
                                    <dt class="col-sm-6 text-muted">Avance</dt><dd class="col-sm-6 text-end">{{ Number(payroll.avance || 0).toLocaleString('fr-FR') }} FCFA</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-bottom"><h6 class="mb-0 fw-semibold">Retenues</h6></div>
                            <div class="card-body">
                                <dl class="row mb-0 small">
                                    <dt class="col-sm-6 text-muted">Cotisations</dt><dd class="col-sm-6 text-end">{{ sumArray(payroll.cotisations) }} FCFA</dd>
                                    <dt class="col-sm-6 text-muted">Retenues diverses</dt><dd class="col-sm-6 text-end">{{ sumArray(payroll.retenues) }} FCFA</dd>
                                    <hr class="my-1">
                                    <dt class="col-sm-6 fw-bold">Net à payer</dt><dd class="col-sm-6 text-end fw-bold fs-5 text-success">{{ Number(payroll.net_a_payer).toLocaleString('fr-FR') }} FCFA</dd>
                                    <hr>
                                    <dt class="col-sm-6 text-muted">Statut</dt><dd class="col-sm-6 text-end">{{ payroll.statut }}</dd>
                                    <dt class="col-sm-6 text-muted">Date paiement</dt><dd class="col-sm-6 text-end">{{ payroll.date_paiement || '—' }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="text-center py-5 text-muted">Fiche de paie introuvable.</div>
        </div>
    </GelLayout>
</template>

<script>
import GelLayout from '../../../../Layouts/GelLayout.vue';
export default {
    components: { GelLayout },
    props: { id: [String, Number] },
    data() { return { loading: true, payroll: null }; },
    async mounted() {
        try {
            const res = await fetch(`/rh/payrolls/${this.id}`);
            if (res.ok) this.payroll = await res.json();
        } catch (e) { console.error(e); }
        finally { this.loading = false; }
    },
    methods: {
        statusBadge(s) { return { brouillon: 'secondary', calcule: 'info', valide: 'success', paye: 'primary', annule: 'danger' }[s] || 'secondary'; },
        sumArray(arr) {
            if (!arr) return '0';
            const vals = Array.isArray(arr) ? arr : Object.values(arr);
            return Number(vals.reduce((a, b) => Number(a) + Number(b), 0)).toLocaleString('fr-FR');
        }
    }
};
</script>
