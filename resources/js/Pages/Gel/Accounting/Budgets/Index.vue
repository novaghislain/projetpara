<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';
import { authStore } from '../../../../stores/auth';

const props = defineProps({
    clientId: { type: [Number, String], default: null }
});

const budgets = ref([]);
const loading = ref(true);
const error = ref(null);
const showModal = ref(false);
const editing = ref(null);
const form = ref({ name: '', type: 'depense', fiscal_year_id: '', montant_prevu: 0, notes: '' });
const fiscalYears = ref([]);

const withCsrf = () => document.querySelector('meta[name=csrf-token]')?.content;

const fetchData = async () => {
    loading.value = true;
    const cid = props.clientId || authStore.user?.client_id;
    if (!cid) {
        error.value = 'Aucun client sélectionné. Veuillez accéder à cette page depuis le dossier d\'un client.';
        loading.value = false;
        return;
    }
    try {
        const [bRes, fyRes] = await Promise.all([
            fetch('/api/accounting/budgets/' + cid),
            fetch('/api/accounting/fiscal-years?all=true')
        ]);
        if (!bRes.ok) throw new Error('Erreur API budgets');
        budgets.value = await bRes.json();
        fiscalYears.value = fyRes.ok ? await fyRes.json() : [];
    } catch (e) { error.value = e.message; }
    finally { loading.value = false; }
};

const openCreate = () => {
    editing.value = null;
    form.value = { name: '', type: 'depense', fiscal_year_id: fiscalYears.value[0]?.id || '', montant_prevu: 0, notes: '' };
    showModal.value = true;
};

const save = async () => {
    const cid = props.clientId || authStore.user?.client_id;
    if (!cid) { alert('Aucun client sélectionné.'); return; }
    const res = await fetch('/api/accounting/budgets', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
        body: JSON.stringify({ ...form.value, client_id: cid }),
    });
    if (res.ok) { showModal.value = false; await fetchData(); }
};

const validate = async (id) => {
    const cid = props.clientId || authStore.user?.client_id;
    if (!cid) return;
    await fetch(`/api/accounting/budgets/${cid}/${id}/valider`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': withCsrf() },
    });
    await fetchData();
};

const remove = async (id) => {
    if (!confirm('Supprimer ce budget ?')) return;
    const cid = props.clientId || authStore.user?.client_id;
    if (!cid) return;
    await fetch(`/api/accounting/budgets/${cid}/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': withCsrf() },
    });
    await fetchData();
};

const typeColor = (t) => ({ recette: 'success', depense: 'danger', tresorerie: 'info', investissement: 'warning' }[t] || 'secondary');
const statusColor = (s) => ({ brouillon: 'secondary', actif: 'success', verrouille: 'danger', archive: 'dark' }[s] || 'secondary');

onMounted(fetchData);
</script>

<template>
    <GelLayout page-title="Budgets Prévisionnels">
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <div v-else>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0"><i class="bi bi-pie-chart me-2 text-primary"></i>Budgets Prévisionnels</h5>
                <button class="btn btn-primary btn-sm" @click="openCreate"><i class="bi bi-plus-lg me-1"></i>Nouveau budget</button>
            </div>

            <div v-if="!budgets.length" class="text-muted text-center py-5">
                <i class="bi bi-pie-chart" style="font-size:3rem;"></i>
                <p class="mt-2">Aucun budget créé.</p>
            </div>

            <div v-else class="row g-3">
                <div v-for="b in budgets" :key="b.id" class="col-12 col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-semibold mb-0">{{ b.name }}</h6>
                                <span class="badge" :class="'bg-' + statusColor(b.status)">{{ b.status }}</span>
                            </div>
                            <div class="small text-muted mb-2">{{ b.fiscal_year?.year || '-' }} · <span class="badge bg-soft" :class="'text-' + typeColor(b.type)">{{ b.type }}</span></div>
                            <div class="mb-1"><span class="small text-muted">Prévu :</span> <span class="fw-semibold">{{ $formatCurrency(b.montant_prevu) }}</span></div>
                            <div class="mb-1"><span class="small text-muted">Réalisé :</span> <span class="fw-semibold">{{ $formatCurrency(b.montant_realise) }}</span></div>
                            <div class="progress" style="height:6px;">
                                <div class="progress-bar" :class="b.taux_realisation > 100 ? 'bg-danger' : 'bg-success'" :style="{ width: Math.min(b.taux_realisation, 100) + '%' }"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <span class="small text-muted">{{ b.taux_realisation }}% réalisé</span>
                                <div class="d-flex gap-1">
                                    <button v-if="b.status === 'brouillon'" class="btn btn-sm btn-outline-success py-0 px-1" @click="validate(b.id)" title="Valider"><i class="bi bi-check-lg"></i></button>
                                    <button class="btn btn-sm btn-outline-danger py-0 px-1" @click="remove(b.id)" title="Supprimer"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="modal-backdrop show" @click="showModal = false"></div>
        <div v-if="showModal" class="modal d-block" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title">Nouveau Budget</h6>
                        <button class="btn-close" @click="showModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small">Nom</label>
                            <input class="form-control" v-model="form.name" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Type</label>
                            <select class="form-select" v-model="form.type">
                                <option value="depense">Dépense</option>
                                <option value="recette">Recette</option>
                                <option value="tresorerie">Trésorerie</option>
                                <option value="investissement">Investissement</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Exercice</label>
                            <select class="form-select" v-model="form.fiscal_year_id">
                                <option v-for="fy in fiscalYears" :key="fy.id" :value="fy.id">{{ fy.year }}</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Montant prévu</label>
                            <input class="form-control" type="number" step="0.01" v-model.number="form.montant_prevu" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Notes</label>
                            <textarea class="form-control" v-model="form.notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-secondary" @click="showModal = false">Annuler</button>
                        <button class="btn btn-sm btn-primary" @click="save">Créer</button>
                    </div>
                </div>
            </div>
        </div>
    </GelLayout>
</template>
