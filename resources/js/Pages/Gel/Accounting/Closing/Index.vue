<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';
import { authStore } from '../../../../stores/auth';

const props = defineProps({
    clientId: { type: [Number, String], default: null }
});

const entries = ref([]);
const stats = ref([]);
const loading = ref(true);
const error = ref(null);
const selectedFy = ref(null);
const fiscalYears = ref([]);
const message = ref('');

const fetchData = async () => {
    loading.value = true;
    const cid = props.clientId || authStore.user?.client_id;
    if (!cid) {
        error.value = 'Aucun client sélectionné. Veuillez accéder à cette page depuis le dossier d\'un client.';
        loading.value = false;
        return;
    }
    try {
        const [eRes, sRes, fyRes] = await Promise.all([
            fetch('/api/accounting/closing/' + cid),
            fetch('/api/accounting/closing/stats/' + cid),
            fetch('/api/accounting/fiscal-years?all=true')
        ]);
        if (!eRes.ok) throw new Error('Erreur API clôture');
        if (!sRes.ok) throw new Error('Erreur API statistiques');
        entries.value = await eRes.json();
        stats.value = await sRes.json();
        fiscalYears.value = fyRes.ok ? await fyRes.json() : [];
        selectedFy.value = fiscalYears.value[0]?.id;
    } catch (e) { error.value = e.message; }
    finally { loading.value = false; }
};

const closeYear = async () => {
    if (!selectedFy.value) return;
    if (!confirm('Confirmer la clôture de cet exercice ? Cette action est irréversible.')) return;

    const res = await fetch('/api/accounting/closing/cloturer', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
        body: JSON.stringify({ fiscal_year_id: selectedFy.value }),
    });
    const data = await res.json();
    if (res.ok) {
        message.value = 'Exercice clôturé avec succès.';
        await fetchData();
        setTimeout(() => message.value = '', 5000);
    } else {
        message.value = data.message || 'Erreur lors de la clôture.';
    }
};

const reopenYear = async (fyId) => {
    if (!confirm('Réouvrir cet exercice ?')) return;

    const res = await fetch('/api/accounting/closing/rouvrir', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
        body: JSON.stringify({ fiscal_year_id: fyId }),
    });
    const data = await res.json();
    if (res.ok) {
        message.value = 'Exercice rouvert.';
        await fetchData();
    } else {
        message.value = data.message || 'Erreur.';
    }
};

const statusBadge = (s) => ({ open: 'bg-success', closed: 'bg-secondary', locked: 'bg-dark' }[s] || 'bg-secondary');
const typeLabel = (t) => ({ inventaire: 'Inventaire', amortissement: 'Amort.', provision: 'Provision', regularisation: 'Régul.', resultat: 'Résultat', affectation: 'Affectation' }[t] || t);

onMounted(fetchData);
</script>

<template>
    <GelLayout page-title="Clôture d'Exercice">
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <div v-else>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0"><i class="bi bi-lock me-2 text-primary"></i>Clôture d'Exercice</h5>
            </div>

            <div v-if="message" class="alert alert-info alert-dismissible fade show" role="alert">
                {{ message }}<button class="btn-close" @click="message = ''"></button>
            </div>

            <!-- Exercices -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="fw-semibold mb-0">Exercices comptables</h6>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" v-model="selectedFy" style="width:auto;">
                            <option v-for="fy in fiscalYears" :key="fy.id" :value="fy.id">{{ fy.year }} ({{ fy.status }})</option>
                        </select>
                        <button class="btn btn-dark btn-sm" @click="closeYear">
                            <i class="bi bi-lock me-1"></i>Clôturer l'exercice
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="small text-muted">
                            <tr>
                                <th>Année</th>
                                <th>Période</th>
                                <th>Statut</th>
                                <th class="text-center">Balance</th>
                                <th class="text-center">TVA</th>
                                <th class="text-center">CNSS</th>
                                <th class="text-center">Rappro.</th>
                                <th>Clôturé le</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="s in stats" :key="s.year">
                                <td class="fw-semibold">{{ s.year }}</td>
                                <td class="small">{{ s.date_start }} → {{ s.date_end }}</td>
                                <td><span class="badge" :class="statusBadge(s.status)">{{ s.status }}</span></td>
                                <td class="text-center"><i class="bi" :class="s.check_balance ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted'"></i></td>
                                <td class="text-center"><i class="bi" :class="s.check_tva ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted'"></i></td>
                                <td class="text-center"><i class="bi" :class="s.check_cnss ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted'"></i></td>
                                <td class="text-center"><i class="bi" :class="s.check_reconciliation ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted'"></i></td>
                                <td class="small">{{ s.closed_at ? new Date(s.closed_at).toLocaleDateString('fr-FR') : '-' }}</td>
                                <td>
                                    <button v-if="s.status === 'closed'" class="btn btn-sm btn-outline-warning py-0" @click="reopenYear(fiscalYears.find(fy => fy.year === s.year)?.id)" title="Réouvrir">
                                        <i class="bi bi-unlock"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Écritures de clôture -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h6 class="fw-semibold mb-0">Écritures de clôture</h6>
                </div>
                <div class="card-body p-0">
                    <div v-if="!entries.length" class="text-muted small py-4 text-center">Aucune écriture de clôture.</div>
                    <table v-else class="table table-sm table-hover mb-0">
                        <thead class="small text-muted">
                            <tr>
                                <th>Réf.</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Statut</th>
                                <th>Exercice</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="e in entries" :key="e.id">
                                <td><code>{{ e.reference }}</code></td>
                                <td><span class="badge bg-info">{{ typeLabel(e.type) }}</span></td>
                                <td class="small">{{ e.description || '-' }}</td>
                                <td><span class="badge" :class="e.status === 'comptabilise' ? 'bg-success' : e.status === 'valide' ? 'bg-primary' : 'bg-secondary'">{{ e.status }}</span></td>
                                <td class="small">{{ e.fiscal_year?.year || '-' }}</td>
                                <td class="small">{{ e.created_at ? new Date(e.created_at).toLocaleDateString('fr-FR') : '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </GelLayout>
</template>
