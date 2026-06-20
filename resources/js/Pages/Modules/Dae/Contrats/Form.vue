<template>
    <GelLayout>
        <div class="container-fluid py-4">
            <div class="d-flex align-items-center gap-3 mb-4">
                <a href="/dae/contrats" class="btn btn-outline-secondary btn-sm" title="Retour à la liste">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h4 class="mb-0 fw-bold"><i class="bi bi-file-text me-2 text-primary"></i>{{ isEditing ? 'Modifier' : 'Nouveau' }} contrat</h4>
                    <p class="text-muted small mb-0 mt-1">{{ isEditing ? 'Mettez à jour les informations du contrat' : 'Remplissez les informations pour créer un nouveau contrat' }}</p>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form @submit.prevent="save">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Titre *</label>
                                <input v-model="form.titre" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Type *</label>
                                <input v-model="form.type_contrat" class="form-control" required placeholder="Ex: Maintenance">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Partie adverse</label>
                                <input v-model="form.partie_adverse" class="form-control" placeholder="Nom du cocontractant">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date début *</label>
                                <input v-model="form.date_debut" type="date" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date fin</label>
                                <input v-model="form.date_fin" type="date" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Durée (mois)</label>
                                <input v-model="form.duree_mois" type="number" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Montant</label>
                                <input v-model="form.montant" type="number" step="0.01" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Devise</label>
                                <select v-model="form.devise" class="form-select">
                                    <option value="EUR">EUR</option>
                                    <option value="USD">USD</option>
                                    <option value="XAF">XAF</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fichier</label>
                                <input ref="fileInput" type="file" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Tags</label>
                                <input v-model="form.tags" class="form-control" placeholder="tag1, tag2, tag3">
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input v-model="form.renouvelable" type="checkbox" class="form-check-input" id="renouvelable">
                                    <label class="form-check-label" for="renouvelable">Renouvelable automatiquement</label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary" :disabled="saving">
                                <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                                {{ isEditing ? 'Mettre à jour' : 'Créer' }}
                            </button>
                            <a :href="'/dae/contrats'" class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </GelLayout>
</template>

<script>
import GelLayout from '../../../../Layouts/GelLayout.vue';
import { authStore } from '../../../../stores/auth';
import axios from 'axios';

export default {
    components: { GelLayout },
    props: { contratId: { type: [Number, String], default: null } },
    data() {
        return {
            isEditing: !!this.contratId,
            saving: false,
            form: {
                titre: '', type_contrat: '', partie_adverse: '',
                date_debut: '', date_fin: '', duree_mois: null,
                montant: null, devise: 'EUR', renouvelable: false, tags: '',
            },
        };
    },
    created() {
        if (this.isEditing) this.loadContrat();
    },
    methods: {
        loadContrat() {
            axios.get(`/dae/contrats/${this.contratId}`).then(res => {
                const d = res.data;
                this.form = {
                    titre: d.titre, type_contrat: d.type_contrat, partie_adverse: d.partie_adverse || '',
                    date_debut: d.date_debut ? d.date_debut.slice(0,10) : '',
                    date_fin: d.date_fin ? d.date_fin.slice(0,10) : '',
                    duree_mois: d.duree_mois, montant: d.montant, devise: d.devise || 'EUR',
                    renouvelable: !!d.renouvelable, tags: Array.isArray(d.tags) ? d.tags.join(', ') : '',
                };
            });
        },
        save() {
            this.saving = true;
            const formData = new FormData();
            Object.keys(this.form).forEach(k => formData.append(k, this.form[k]));
            formData.append('client_id', authStore?.user?.client_id || 1);
            if (this.$refs.fileInput?.files?.[0]) formData.append('fichier', this.$refs.fileInput.files[0]);

            const method = this.isEditing ? 'put' : 'post';
            const url = this.isEditing ? `/dae/contrats/${this.contratId}` : '/dae/contrats';

            axios({ method, url, data: formData }).then(() => {
                window.location = '/dae/contrats';
            }).catch(() => this.saving = false);
        },
    },
};
</script>
