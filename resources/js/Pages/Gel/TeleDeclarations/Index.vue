<template>
    <GelLayout page-title="Télédéclarations">
        <div class="p-6">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="text-xl fw-bold mb-0">Déclarations Fiscales</h2>
                <a href="/tele-declarations/create" class="btn btn-primary btn-sm">
                    <i class="bi-plus-circle me-1"></i> Nouvelle déclaration
                </a>
            </div>

            <!-- Filtres -->
            <div class="bg-white rounded-lg shadow p-3 mb-3">
                <form class="row g-2 align-items-end" method="GET">
                    <div class="col-md-4">
                        <label class="form-label small">Client</label>
                        <select name="client_id" class="form-select form-select-sm" @change="this.form.submit()">
                            <option value="">Tous</option>
                            <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company_name }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Type</label>
                        <select name="tax_type" class="form-select form-select-sm" @change="this.form.submit()">
                            <option value="">Tous</option>
                            <option value="tva">TVA</option>
                            <option value="is">Impôt Sociétés</option>
                            <option value="its">ITS</option>
                            <option value="cnss">CNSS</option>
                            <option value="vps">VPS</option>
                            <option value="aib">AIB</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Statut</label>
                        <select name="status" class="form-select form-select-sm" @change="this.form.submit()">
                            <option value="">Tous</option>
                            <option value="brouillon">Brouillon</option>
                            <option value="calcule">Calculé</option>
                            <option value="depose">Déposé</option>
                            <option value="paye">Payé</option>
                        </select>
                    </div>
                </form>
            </div>

            <!-- Liste -->
            <div class="bg-white rounded-lg shadow">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Réf.</th>
                                <th>Client</th>
                                <th>Type</th>
                                <th>Période</th>
                                <th>Base</th>
                                <th>Montant dû</th>
                                <th>Statut</th>
                                <th>Échéance</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="d in declarations.data" :key="d.id">
                                <td><code>{{ d.reference }}</code></td>
                                <td>{{ d.client?.company_name || '—' }}</td>
                                <td>{{ d.libelle_type }}</td>
                                <td>{{ d.period_month ? 'M' + d.period_month + '/' : '' }}{{ d.period_quarter ? 'T' + d.period_quarter + '/' : '' }}{{ d.period_year }}</td>
                                <td>{{ formatMille(d.base_imposable) }}</td>
                                <td class="fw-semibold">{{ formatMille(d.montant_dut) }}</td>
                                <td>
                                    <span :class="'badge ' + statusClass(d.status)">{{ d.status }}</span>
                                </td>
                                <td :class="{ 'text-danger fw-bold': d.est_en_retard }">{{ d.date_echeance }}</td>
                                <td>
                                    <a :href="`/tele-declarations/${d.id}`" class="btn btn-outline-primary btn-sm" title="Voir">
                                        <i class="bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr v-if="!declarations.data.length">
                                <td colspan="9" class="text-center text-muted py-4">Aucune déclaration.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="p-2" v-if="declarations.last_page > 1">
                    <nav>
                        <ul class="pagination pagination-sm mb-0 justify-content-center">
                            <li class="page-item" :class="{ disabled: declarations.current_page === 1 }">
                                <a class="page-link" :href="declarations.path + '?page=' + (declarations.current_page - 1)">‹</a>
                            </li>
                            <li class="page-item" v-for="p in declarations.last_page" :key="p" :class="{ active: p === declarations.current_page }">
                                <a class="page-link" :href="declarations.path + '?page=' + p">{{ p }}</a>
                            </li>
                            <li class="page-item" :class="{ disabled: declarations.current_page === declarations.last_page }">
                                <a class="page-link" :href="declarations.path + '?page=' + (declarations.current_page + 1)">›</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script setup>
import GelLayout from '../../../Layouts/GelLayout.vue';
defineProps(['declarations', 'clients'])

const formatMille = (v) => {
    if (v == null || v === '') return '—';
    return Number(v).toLocaleString('fr-FR', { minimumFractionDigits: 0 }) + ' FCFA';
}

const statusClass = (s) => ({
    brouillon: 'bg-secondary',
    calcule: 'bg-info text-dark',
    depose: 'bg-primary',
    paye: 'bg-success',
    en_retard: 'bg-danger',
}[s] || 'bg-secondary')
</script>
