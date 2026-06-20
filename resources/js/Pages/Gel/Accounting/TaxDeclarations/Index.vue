<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';
import TaxSummaryCard from '../../../../Components/Accounting/TaxSummaryCard.vue';
import { authStore } from '../../../../stores/auth';

const props = defineProps({
    clientId: { type: [Number, String], default: null }
});

const declarations = ref([]);
const loading = ref(true);
const error = ref(null);
const activeTab = ref('tva');
const fiscalYears = ref([]);
const selectedFy = ref(null);
const selectedMonth = ref(new Date().getMonth() + 1);
const salaireInput = ref(0);
const masseSalariale = ref(0);

const fetchData = async () => {
    loading.value = true;
    const cid = props.clientId || authStore.user?.client_id;
    if (!cid) {
        error.value = 'Aucun client sélectionné. Veuillez accéder à cette page depuis le dossier d\'un client.';
        loading.value = false;
        return;
    }
    try {
        const [dRes, fyRes] = await Promise.all([
            fetch('/api/accounting/tax-declarations/' + cid),
            fetch('/api/accounting/fiscal-years?all=true')
        ]);
        if (!dRes.ok) throw new Error('Erreur API déclarations fiscales');
        declarations.value = await dRes.json();
        fiscalYears.value = fyRes.ok ? await fyRes.json() : [];
        selectedFy.value = fiscalYears.value[0]?.id;
    } catch (e) { error.value = e.message; }
    finally { loading.value = false; }
};

const compute = async (type) => {
    const cid = props.clientId || authStore.user?.client_id;
    if (!cid) { alert('Aucun client sélectionné.'); return; }
    let payload = { client_id: cid, fiscal_year_id: selectedFy.value };
    if (type === 'tva') payload.month = selectedMonth.value;
    if (type === 'its') payload.salaire_brut_annuel = salaireInput.value;
    if (type === 'cnss') payload.salaire_brut_mensuel = salaireInput.value;
    if (type === 'vps') payload.masse_salariale = masseSalariale.value;

    const endpoint = {
        tva: '/api/accounting/tax-declarations/tva',
        is: '/api/accounting/tax-declarations/is',
        its: '/api/accounting/tax-declarations/its',
        cnss: '/api/accounting/tax-declarations/cnss',
        vps: '/api/accounting/tax-declarations/vps',
    }[type];

    const res = await fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
        body: JSON.stringify(payload),
    });
    if (res.ok) await fetchData();
};

const updateStatus = async (id, status) => {
    const cid = props.clientId || authStore.user?.client_id;
    if (!cid) return;
    const res = await fetch(`/api/accounting/tax-declarations/${cid}/${id}/status`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
        body: JSON.stringify({ status }),
    });
    if (res.ok) await fetchData();
};

const remove = async (id) => {
    if (!confirm('Supprimer cette déclaration ?')) return;
    const cid = props.clientId || authStore.user?.client_id;
    if (!cid) return;
    await fetch(`/api/accounting/tax-declarations/${cid}/${id}`, { method: 'DELETE' });
    await fetchData();
};

const statusBadge = (s) => ({ brouillon: 'bg-secondary', calcule: 'bg-info', depose: 'bg-primary', paye: 'bg-success', en_retard: 'bg-danger' }[s] || 'bg-secondary');
const typeLabel = (t) => ({ tva: 'TVA', is: 'IS', its: 'ITS', cnss: 'CNSS', vps: 'VPS', aib: 'AIB' }[t] || t);

const filteredDeclarations = () => declarations.value.filter(d => d.tax_type === activeTab.value);

onMounted(fetchData);
</script>

<template>
    <GelLayout page-title="Déclarations Fiscales">
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <div v-else>
            <h5 class="fw-bold mb-4"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Déclarations Fiscales</h5>

            <!-- Onglets -->
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item" v-for="tab in ['tva', 'is', 'its', 'cnss', 'vps']" :key="tab">
                    <button class="nav-link" :class="{ active: activeTab === tab }" @click="activeTab = tab">{{ typeLabel(tab) }}</button>
                </li>
            </ul>

            <!-- Calcul -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4 d-flex gap-3 align-items-end flex-wrap">
                    <div>
                        <label class="form-label small">Exercice</label>
                        <select class="form-select form-select-sm" v-model="selectedFy">
                            <option v-for="fy in fiscalYears" :key="fy.id" :value="fy.id">{{ fy.year }}</option>
                        </select>
                    </div>
                    <div v-if="activeTab === 'tva'">
                        <label class="form-label small">Mois</label>
                        <select class="form-select form-select-sm" v-model="selectedMonth">
                            <option v-for="m in 12" :key="m" :value="m">{{ m }}</option>
                        </select>
                    </div>
                    <div v-if="activeTab === 'its' || activeTab === 'cnss'">
                        <label class="form-label small">{{ activeTab === 'its' ? 'Salaire brut annuel' : 'Salaire brut mensuel' }}</label>
                        <input class="form-control form-control-sm" type="number" v-model.number="salaireInput" style="width:180px;" />
                    </div>
                    <div v-if="activeTab === 'vps'">
                        <label class="form-label small">Masse salariale</label>
                        <input class="form-control form-control-sm" type="number" v-model.number="masseSalariale" style="width:180px;" />
                    </div>
                    <button class="btn btn-primary btn-sm" @click="compute(activeTab)">
                        <i class="bi bi-calculator me-1"></i>Calculer
                    </button>
                </div>
            </div>

            <!-- Liste -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div v-if="!filteredDeclarations().length" class="text-muted small py-4 text-center">Aucune déclaration {{ typeLabel(activeTab) }}.</div>
                    <table v-else class="table table-sm table-hover mb-0">
                        <thead class="small text-muted">
                            <tr>
                                <th>Réf.</th>
                                <th>Période</th>
                                <th class="text-end">Base</th>
                                <th class="text-end">Montant dû</th>
                                <th class="text-end">Payé</th>
                                <th>Statut</th>
                                <th>Échéance</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="d in filteredDeclarations()" :key="d.id">
                                <td><code>{{ d.reference }}</code></td>
                                <td>{{ d.period_month ? 'Mois ' + d.period_month + ' ' : '' }}{{ d.period_year }}</td>
                                <td class="text-end">{{ $formatCurrency(d.base_imposable) }}</td>
                                <td class="text-end fw-semibold">{{ $formatCurrency(d.montant_dut) }}</td>
                                <td class="text-end">{{ $formatCurrency(d.montant_paye) }}</td>
                                <td><span class="badge" :class="statusBadge(d.status)">{{ d.status }}</span></td>
                                <td class="small">{{ d.date_echeance ? new Date(d.date_echeance).toLocaleDateString('fr-FR') : '-' }}</td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary py-0" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><button class="dropdown-item small" @click="updateStatus(d.id, 'depose')"><i class="bi bi-check2 me-2"></i>Marquer déposée</button></li>
                                            <li><button class="dropdown-item small" @click="updateStatus(d.id, 'paye')"><i class="bi bi-cash me-2"></i>Marquer payée</button></li>
                                            <li><hr class="dropdown-divider" /></li>
                                            <li><button class="dropdown-item small text-danger" @click="remove(d.id)"><i class="bi bi-trash me-2"></i>Supprimer</button></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </GelLayout>
</template>
