<script setup>
import { ref, computed, onMounted } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';
import { authStore } from '../../../../stores/auth';

const props = defineProps({
    employeeId: { type: [Number, String], default: null }
});

const isEdit = computed(() => !!props.employeeId);

const form = ref({
    matricule: '',
    civilite: '',
    nom: '',
    prenom: '',
    email: '',
    phone: '',
    adresse: '',
    date_naissance: '',
    poste: '',
    departement: '',
    date_embauche: '',
    date_depart: '',
    type_contrat: '',
    salaire_base: '',
    status: 'actif',
});

const loading = ref(true);
const submitting = ref(false);
const error = ref(null);
const successMsg = ref('');

const fetchEmployee = async () => {
    if (!props.employeeId) { loading.value = false; return; }
    loading.value = true;
    try {
        const res = await fetch('/rh/employees/' + props.employeeId);
        if (!res.ok) throw new Error('Erreur de chargement');
        const data = await res.json();
        Object.assign(form.value, {
            matricule: data.matricule || '',
            civilite: data.civilite || '',
            nom: data.nom || '',
            prenom: data.prenom || '',
            email: data.email || '',
            phone: data.phone || '',
            adresse: data.adresse || '',
            date_naissance: data.date_naissance || '',
            poste: data.poste || '',
            departement: data.departement || '',
            date_embauche: data.date_embauche || '',
            date_depart: data.date_depart || '',
            type_contrat: data.type_contrat || '',
            salaire_base: data.salaire_base || '',
            status: data.status || 'actif',
        });
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const submitForm = async () => {
    submitting.value = true;
    error.value = null;
    successMsg.value = '';
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const method = isEdit.value ? 'PUT' : 'POST';
        const url = isEdit.value ? '/rh/employees/' + props.employeeId : '/rh/employees';
        const res = await fetch(url, {
            method: method,
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(form.value),
        });
        if (!res.ok) {
            const errData = await res.json().catch(() => ({}));
            throw new Error(errData.message || 'Erreur lors de l\'enregistrement');
        }
        successMsg.value = isEdit.value ? 'Employé mis à jour avec succès.' : 'Employé créé avec succès.';
    } catch (e) {
        error.value = e.message;
    } finally {
        submitting.value = false;
    }
};

onMounted(fetchEmployee);
</script>

<template>
    <GelLayout :page-title="isEdit ? 'Modifier un employé' : 'Nouvel employé'">
        <div class="mb-3">
            <a href="/rh/employees" class="btn btn-sm btn-outline-secondary">
                <i class="bi-arrow-left me-1"></i>Retour à la liste
            </a>
        </div>

        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary"><span class="visually-hidden">Chargement...</span></div>
        </div>

        <div v-else class="card card-dashboard">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0">{{ isEdit ? 'Modifier l\'employé' : 'Créer un nouvel employé' }}</h6>
            </div>
            <div class="card-body">
                <div v-if="error" class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ error }}
                    <button class="btn-close" @click="error = null"></button>
                </div>
                <div v-if="successMsg" class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ successMsg }}
                    <button class="btn-close" @click="successMsg = ''"></button>
                </div>

                <form @submit.prevent="submitForm">
                    <div class="row g-3">
                        <!-- Section: Identité -->
                        <div class="col-12"><hr class="my-1"><h6 class="fw-semibold small text-muted mb-2">Identité</h6></div>
                        <div class="col-md-2">
                            <label class="form-label small">Matricule</label>
                            <input v-model="form.matricule" class="form-control form-control-sm" placeholder="EMP-001">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Civilité</label>
                            <select v-model="form.civilite" class="form-select form-select-sm">
                                <option value="">--</option>
                                <option value="M.">M.</option>
                                <option value="Mme">Mme</option>
                                <option value="Mlle">Mlle</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Nom *</label>
                            <input v-model="form.nom" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Prénom *</label>
                            <input v-model="form.prenom" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Email</label>
                            <input v-model="form.email" type="email" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Téléphone</label>
                            <input v-model="form.phone" type="tel" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Date de naissance</label>
                            <input v-model="form.date_naissance" type="date" class="form-control form-control-sm">
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Adresse</label>
                            <textarea v-model="form.adresse" class="form-control form-control-sm" rows="2"></textarea>
                        </div>

                        <!-- Section: Emploi -->
                        <div class="col-12"><hr class="my-1"><h6 class="fw-semibold small text-muted mb-2">Emploi</h6></div>
                        <div class="col-md-4">
                            <label class="form-label small">Poste</label>
                            <input v-model="form.poste" class="form-control form-control-sm" placeholder="Intitulé du poste">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Département</label>
                            <input v-model="form.departement" class="form-control form-control-sm" placeholder="Service">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Type de contrat</label>
                            <select v-model="form.type_contrat" class="form-select form-select-sm">
                                <option value="">--</option>
                                <option value="CDI">CDI</option>
                                <option value="CDD">CDD</option>
                                <option value="INTERIM">Intérim</option>
                                <option value="STAGE">Stage</option>
                                <option value="PRESTATION">Prestation</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Date d'embauche</label>
                            <input v-model="form.date_embauche" type="date" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Date de départ</label>
                            <input v-model="form.date_depart" type="date" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Salaire de base (FCFA)</label>
                            <input v-model="form.salaire_base" type="number" step="0.01" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Statut</label>
                            <select v-model="form.status" class="form-select form-select-sm">
                                <option value="actif">Actif</option>
                                <option value="inactif">Inactif</option>
                                <option value="suspendu">Suspendu</option>
                                <option value="conge">Congé</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <a href="/rh/employees" class="btn btn-sm btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-sm btn-primary" :disabled="submitting">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                            {{ isEdit ? 'Mettre à jour' : 'Créer l\'employé' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </GelLayout>
</template>
