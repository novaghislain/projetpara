<template>
    <GelLayout>
        <div class="rh-employee-show">
            <div v-if="loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
            <div v-else-if="employee" class="container-fluid">
                <div class="d-flex align-items-center mb-4">
                    <a href="/rh/employees" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
                    <div>
                        <h1 class="h3 fw-bold mb-0">{{ employee.prenom }} {{ employee.nom }}</h1>
                        <p class="text-muted mb-0">{{ employee.poste }} · {{ employee.departement }}</p>
                    </div>
                    <span :class="`badge bg-${employee.status === 'actif' ? 'success' : 'warning'} ms-auto fs-6`">{{ employee.status }}</span>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-bottom"><h6 class="mb-0 fw-semibold">Informations personnelles</h6></div>
                            <div class="card-body">
                                <dl class="row mb-0 small">
                                    <dt class="col-sm-5 text-muted">Matricule</dt><dd class="col-sm-7">{{ employee.matricule || '—' }}</dd>
                                    <dt class="col-sm-5 text-muted">Civilité</dt><dd class="col-sm-7">{{ employee.civilite || '—' }}</dd>
                                    <dt class="col-sm-5 text-muted">Email</dt><dd class="col-sm-7">{{ employee.email || '—' }}</dd>
                                    <dt class="col-sm-5 text-muted">Téléphone</dt><dd class="col-sm-7">{{ employee.phone || '—' }}</dd>
                                    <dt class="col-sm-5 text-muted">Date naissance</dt><dd class="col-sm-7">{{ employee.date_naissance || '—' }}</dd>
                                    <dt class="col-sm-5 text-muted">Situation</dt><dd class="col-sm-7">{{ employee.situation_matrimoniale || '—' }}</dd>
                                    <dt class="col-sm-5 text-muted">Nationalité</dt><dd class="col-sm-7">{{ employee.nationalite || '—' }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-bottom"><h6 class="mb-0 fw-semibold">Informations professionnelles</h6></div>
                            <div class="card-body">
                                <dl class="row mb-0 small">
                                    <dt class="col-sm-5 text-muted">Poste</dt><dd class="col-sm-7">{{ employee.poste || '—' }}</dd>
                                    <dt class="col-sm-5 text-muted">Département</dt><dd class="col-sm-7">{{ employee.departement || '—' }}</dd>
                                    <dt class="col-sm-5 text-muted">Type contrat</dt><dd class="col-sm-7">{{ employee.type_contrat || '—' }}</dd>
                                    <dt class="col-sm-5 text-muted">Date embauche</dt><dd class="col-sm-7">{{ employee.date_embauche || '—' }}</dd>
                                    <dt class="col-sm-5 text-muted">Salaire base</dt><dd class="col-sm-7">{{ employee.salaire_base ? Number(employee.salaire_base).toLocaleString('fr-FR') + ' FCFA' : '—' }}</dd>
                                    <dt class="col-sm-5 text-muted">CNSS</dt><dd class="col-sm-7">{{ employee.cnss_number || '—' }}</dd>
                                    <dt class="col-sm-5 text-muted">IFU</dt><dd class="col-sm-7">{{ employee.ifu_number || '—' }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-bottom"><h6 class="mb-0 fw-semibold">Contrats</h6></div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 small">
                                    <thead class="table-light"><tr><th>Réf.</th><th>Type</th><th>Début</th><th>Fin</th><th>Salaire</th><th>Statut</th></tr></thead>
                                    <tbody>
                                        <tr v-for="c in employee.contracts" :key="c.id">
                                            <td>{{ c.reference || '—' }}</td>
                                            <td>{{ c.type }}</td>
                                            <td>{{ c.date_debut }}</td>
                                            <td>{{ c.date_fin || '—' }}</td>
                                            <td>{{ c.salaire ? Number(c.salaire).toLocaleString('fr-FR') : '—' }}</td>
                                            <td><span :class="`badge bg-${c.statut === 'actif' ? 'success' : 'secondary'}`">{{ c.statut }}</span></td>
                                        </tr>
                                        <tr v-if="!employee.contracts?.length"><td colspan="6" class="text-center text-muted">Aucun contrat</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="text-center py-5 text-muted">Employé introuvable.</div>
        </div>
    </GelLayout>
</template>

<script>
import GelLayout from '../../../../Layouts/GelLayout.vue';
export default {
    components: { GelLayout },
    props: { id: [String, Number] },
    data() { return { loading: true, employee: null }; },
    async mounted() {
        try {
            const res = await fetch(`/rh/employees/${this.id}`);
            if (res.ok) this.employee = await res.json();
        } catch (e) { console.error(e); }
        finally { this.loading = false; }
    },
};
</script>
