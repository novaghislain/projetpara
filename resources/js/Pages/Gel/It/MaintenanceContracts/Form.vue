<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const props = defineProps({
    contract: { type: Object, default: null },
    clients: { type: Array, default: () => [] },
});

const submitting = ref(false);
const saved = ref(false);
const error = ref(null);

const typeOptions = ['corrective', 'preventive', 'full_service', 'hotline'];
const statusOptions = ['active', 'suspended'];

const form = ref({
    client_id: '',
    reference: '',
    type: 'corrective',
    status: 'active',
    start_date: '',
    end_date: '',
    monthly_amount: '',
    included_hours: '',
    response_time_hours: '',
    coverage_hours: '',
    auto_renew: false,
});

const typeLabel = (t) =>
    ({ corrective: 'Corrective', preventive: 'Préventive', full_service: 'Service complet', hotline: 'Hotline' }[t] || t);

const statusLabel = (s) =>
    ({ active: 'Actif', suspended: 'Suspendu' }[s] || s);

const isEdit = !!props.contract;

const initForm = () => {
    if (props.contract) {
        form.value = {
            client_id: props.contract.client_id || '',
            reference: props.contract.reference || '',
            type: props.contract.type || 'corrective',
            status: props.contract.status || 'active',
            start_date: props.contract.start_date || '',
            end_date: props.contract.end_date || '',
            monthly_amount: props.contract.monthly_amount ?? '',
            included_hours: props.contract.included_hours ?? '',
            response_time_hours: props.contract.response_time_hours ?? '',
            coverage_hours: props.contract.coverage_hours ?? '',
            auto_renew: props.contract.auto_renew ?? false,
        };
    }
};

const submitForm = async () => {
    submitting.value = true;
    saved.value = false;
    error.value = null;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const url = isEdit ? '/it/maintenance-contracts/' + props.contract.id : '/it/maintenance-contracts';
        const method = isEdit ? 'PUT' : 'POST';

        const res = await fetch(url, {
            method,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(form.value),
        });

        if (res.status === 302) {
            window.location.href = isEdit ? '/it/maintenance-contracts/' + props.contract.id : '/it/maintenance-contracts';
            return;
        }

        if (!res.ok) {
            const errData = await res.json().catch(() => ({}));
            throw new Error(errData.message || Object.values(errData.errors || {}).flat().join(', ') || "Erreur lors de l'enregistrement");
        }

        saved.value = true;
        if (!isEdit) {
            const data = await res.json().catch(() => ({}));
            window.location.href = data && data.id ? '/it/maintenance-contracts/' + data.id : '/it/maintenance-contracts';
        }
    } catch (e) {
        error.value = e.message;
    } finally {
        submitting.value = false;
    }
};

const goBack = () => {
    window.history.back();
};

onMounted(initForm);
</script>

<template>
    <GelLayout :page-title="isEdit ? ('Modifier le contrat ' + (props.contract?.reference || '')) : 'Nouveau contrat de maintenance'">
        <div class="p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">
                    <i class="bi bi-file-earmark-check me-2" style="color:#FF7900;"></i>
                    {{ isEdit ? "Modifier le contrat " + (props.contract?.reference || '') : 'Nouveau contrat de maintenance' }}
                </h2>

                <div v-if="saved" class="alert alert-success d-flex align-items-center gap-2">
                    <i class="bi-check-circle-fill"></i> Contrat enregistré avec succès.
                </div>
                <div v-if="error" class="alert alert-danger d-flex align-items-center gap-2">
                    <i class="bi-exclamation-triangle-fill"></i> {{ error }}
                </div>

                <form @submit.prevent="submitForm">
                    <div class="row g-3">
                        <!-- Identification -->
                        <div class="col-12">
                            <h6 class="fw-bold border-bottom pb-2" style="color:#163A5E; border-color:#dce3ee !important;">Identification</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Client *</label>
                            <select v-model="form.client_id" class="form-select" required>
                                <option value="" disabled>Sélectionner un client</option>
                                <option v-for="c in clients" :key="c.id" :value="c.id">
                                    {{ c.company_name }}
                                </option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-medium">Référence *</label>
                            <input
                                v-model="form.reference"
                                type="text"
                                class="form-control"
                                placeholder="Ex: CMT-2025-001"
                                required
                            >
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-medium">Type *</label>
                            <select v-model="form.type" class="form-select" required>
                                <option v-for="t in typeOptions" :key="t" :value="t">
                                    {{ typeLabel(t) }}
                                </option>
                            </select>
                        </div>

                        <!-- Période & statut -->
                        <div class="col-12">
                            <h6 class="fw-bold border-bottom pb-2" style="color:#163A5E; border-color:#dce3ee !important;">Période & statut</h6>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-medium">Date de début *</label>
                            <input
                                v-model="form.start_date"
                                type="date"
                                class="form-control"
                                required
                            >
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-medium">Date de fin *</label>
                            <input
                                v-model="form.end_date"
                                type="date"
                                class="form-control"
                                required
                            >
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-medium">Statut *</label>
                            <select v-model="form.status" class="form-select" required>
                                <option v-for="s in statusOptions" :key="s" :value="s">
                                    {{ statusLabel(s) }}
                                </option>
                            </select>
                        </div>

                        <div class="col-md-3 d-flex align-items-end pb-2">
                            <div class="form-check">
                                <input
                                    v-model="form.auto_renew"
                                    type="checkbox"
                                    class="form-check-input"
                                    id="auto_renew"
                                >
                                <label class="form-check-label small fw-medium" for="auto_renew">
                                    Renouvellement automatique
                                </label>
                            </div>
                        </div>

                        <!-- Tarification -->
                        <div class="col-12">
                            <h6 class="fw-bold border-bottom pb-2" style="color:#163A5E; border-color:#dce3ee !important;">Tarification</h6>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-medium">Montant mensuel (FCFA)</label>
                            <input
                                v-model="form.monthly_amount"
                                type="number"
                                step="0.01"
                                class="form-control"
                                placeholder="Ex: 150000"
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-medium">Heures incluses / mois</label>
                            <input
                                v-model="form.included_hours"
                                type="number"
                                step="0.5"
                                class="form-control"
                                placeholder="Ex: 20"
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-medium">Délai d'intervention (heures)</label>
                            <input
                                v-model="form.response_time_hours"
                                type="number"
                                step="0.5"
                                class="form-control"
                                placeholder="Ex: 4"
                            >
                        </div>

                        <!-- Couverture -->
                        <div class="col-12">
                            <h6 class="fw-bold border-bottom pb-2" style="color:#163A5E; border-color:#dce3ee !important;">Couverture</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Plage horaire de couverture</label>
                            <input
                                v-model="form.coverage_hours"
                                type="text"
                                class="form-control"
                                placeholder="Ex: Lun-Ven 8h-18h"
                            >
                        </div>

                        <!-- Actions -->
                        <div class="col-12 mt-4 d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-secondary" @click="goBack">
                                <i class="bi-arrow-left me-1"></i>Retour
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary" :disabled="submitting">
                                <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                                <i v-else class="bi-save me-1"></i>
                                {{ isEdit ? 'Mettre à jour' : 'Créer le contrat' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </GelLayout>
</template>
