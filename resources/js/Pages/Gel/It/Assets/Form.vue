<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const props = defineProps({
    asset: { type: Object, default: null },
    clients: { type: Array, default: () => [] },
    technicians: { type: Array, default: () => [] },
});

const submitting = ref(false);
const saved = ref(false);
const error = ref(null);

const categoryOptions = ['computer', 'server', 'printer', 'network', 'mobile', 'software', 'other'];
const statusOptions = ['active', 'inactive', 'in_repair', 'disposed'];

const form = ref({
    client_id: '',
    asset_tag: '',
    name: '',
    category: 'computer',
    brand: '',
    model: '',
    serial_number: '',
    status: 'active',
    assigned_to_user: '',
    location: '',
    purchase_date: '',
    purchase_price: '',
    warranty_expires_at: '',
    os_version: '',
    ip_address: '',
    mac_address: '',
    notes: '',
});

const isEdit = !!props.asset;

const initForm = () => {
    if (props.asset) {
        form.value = {
            client_id: props.asset.client_id || '',
            asset_tag: props.asset.asset_tag || '',
            name: props.asset.name || '',
            category: props.asset.category || 'computer',
            brand: props.asset.brand || '',
            model: props.asset.model || '',
            serial_number: props.asset.serial_number || '',
            status: props.asset.status || 'active',
            assigned_to_user: props.asset.assigned_to_user || '',
            location: props.asset.location || '',
            purchase_date: props.asset.purchase_date || '',
            purchase_price: props.asset.purchase_price || '',
            warranty_expires_at: props.asset.warranty_expires_at || '',
            os_version: props.asset.os_version || '',
            ip_address: props.asset.ip_address || '',
            mac_address: props.asset.mac_address || '',
            notes: props.asset.notes || '',
        };
    }
};

const submitForm = async () => {
    submitting.value = true;
    saved.value = false;
    error.value = null;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const url = isEdit ? '/it/assets/' + props.asset.id : '/it/assets';
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
            window.location.href = isEdit ? '/it/assets/' + props.asset.id : '/it/assets';
            return;
        }

        if (!res.ok) {
            const errData = await res.json().catch(() => ({}));
            throw new Error(errData.message || Object.values(errData.errors || {}).flat().join(', ') || "Erreur lors de l'enregistrement");
        }

        saved.value = true;
        if (!isEdit) {
            const data = await res.json().catch(() => ({}));
            window.location.href = data && data.id ? '/it/assets/' + data.id : '/it/assets';
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
    <GelLayout :page-title="isEdit ? ('Modifier l\'équipement ' + (props.asset?.asset_tag || '')) : 'Nouvel équipement IT'">
        <div class="p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">
                    <i class="bi bi-laptop me-2" style="color:#FF7900;"></i>
                    {{ isEdit ? 'Modifier l\'équipement ' + (props.asset?.asset_tag || '') : 'Nouvel équipement IT' }}
                </h2>

                <div v-if="saved" class="alert alert-success d-flex align-items-center gap-2">
                    <i class="bi-check-circle-fill"></i> Équipement enregistré avec succès.
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

                        <div class="col-md-3">
                            <label class="form-label small fw-medium">Tag *</label>
                            <input
                                v-model="form.asset_tag"
                                type="text"
                                class="form-control"
                                placeholder="Ex: PC-001"
                                required
                            >
                        </div>

                        <div class="col-md-5">
                            <label class="form-label small fw-medium">Nom *</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="form-control"
                                placeholder="Ex: PC Portable Dell - Édouard"
                                required
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-medium">Catégorie *</label>
                            <select v-model="form.category" class="form-select" required>
                                <option v-for="c in categoryOptions" :key="c" :value="c">
                                    {{ c.charAt(0).toUpperCase() + c.slice(1) }}
                                </option>
                            </select>
                        </div>

                        <!-- Marque / Modèle / Série -->
                        <div class="col-12">
                            <h6 class="fw-bold border-bottom pb-2" style="color:#163A5E; border-color:#dce3ee !important;">Marque & modèle</h6>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-medium">Marque</label>
                            <input
                                v-model="form.brand"
                                type="text"
                                class="form-control"
                                placeholder="Ex: Dell, HP, Lenovo"
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-medium">Modèle</label>
                            <input
                                v-model="form.model"
                                type="text"
                                class="form-control"
                                placeholder="Ex: Latitude 5430"
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-medium">N° de série</label>
                            <input
                                v-model="form.serial_number"
                                type="text"
                                class="form-control"
                                placeholder="Ex: 1A2B3C4D5E6F"
                            >
                        </div>

                        <!-- Client & statut -->
                        <div class="col-12">
                            <h6 class="fw-bold border-bottom pb-2" style="color:#163A5E; border-color:#dce3ee !important;">Affectation & statut</h6>
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
                            <label class="form-label small fw-medium">Statut *</label>
                            <select v-model="form.status" class="form-select" required>
                                <option value="active">Actif</option>
                                <option value="inactive">Inactif</option>
                                <option value="in_repair">En réparation</option>
                                <option value="disposed">Reformé</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-medium">Assigné à</label>
                            <select v-model="form.assigned_to_user" class="form-select">
                                <option value="">Non assigné</option>
                                <option v-for="t in technicians" :key="t.id" :value="t.id">
                                    {{ t.name }}
                                </option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Localisation</label>
                            <input
                                v-model="form.location"
                                type="text"
                                class="form-control"
                                placeholder="Ex: Bâtiment A, Bureau 203"
                            >
                        </div>

                        <!-- Informations achat & garantie -->
                        <div class="col-12">
                            <h6 class="fw-bold border-bottom pb-2" style="color:#163A5E; border-color:#dce3ee !important;">Achat & garantie</h6>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-medium">Date d'achat</label>
                            <input
                                v-model="form.purchase_date"
                                type="date"
                                class="form-control"
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-medium">Prix d'achat (FCFA)</label>
                            <input
                                v-model="form.purchase_price"
                                type="number"
                                step="0.01"
                                class="form-control"
                                placeholder="Ex: 450000"
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-medium">Garantie jusqu'au</label>
                            <input
                                v-model="form.warranty_expires_at"
                                type="date"
                                class="form-control"
                            >
                        </div>

                        <!-- Informations système -->
                        <div class="col-12">
                            <h6 class="fw-bold border-bottom pb-2" style="color:#163A5E; border-color:#dce3ee !important;">Informations système</h6>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-medium">OS / Version</label>
                            <input
                                v-model="form.os_version"
                                type="text"
                                class="form-control"
                                placeholder="Ex: Windows 11 Pro 23H2"
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-medium">Adresse IP</label>
                            <input
                                v-model="form.ip_address"
                                type="text"
                                class="form-control"
                                placeholder="Ex: 192.168.1.100"
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-medium">Adresse MAC</label>
                            <input
                                v-model="form.mac_address"
                                type="text"
                                class="form-control"
                                placeholder="Ex: AA:BB:CC:DD:EE:FF"
                            >
                        </div>

                        <!-- Notes -->
                        <div class="col-12">
                            <h6 class="fw-bold border-bottom pb-2" style="color:#163A5E; border-color:#dce3ee !important;">Notes</h6>
                        </div>

                        <div class="col-12">
                            <textarea
                                v-model="form.notes"
                                class="form-control"
                                rows="3"
                                placeholder="Informations complémentaires..."
                            ></textarea>
                        </div>

                        <!-- Actions -->
                        <div class="col-12 mt-4 d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-secondary" @click="goBack">
                                <i class="bi-arrow-left me-1"></i>Retour
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary" :disabled="submitting">
                                <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                                <i v-else class="bi-save me-1"></i>
                                {{ isEdit ? 'Mettre à jour' : 'Créer l\'équipement' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </GelLayout>
</template>
